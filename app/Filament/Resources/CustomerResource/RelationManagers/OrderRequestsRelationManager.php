<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\CustomerOrderRequest;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;


class OrderRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderRequests';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Sipariş Talepleri';

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Sipariş talebi bulunamadı';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Bu müşteriye ait siparişler ve seçili filtreye uyan talepler burada listelenir.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Durum')
                ->options([
                    'pending' => 'Beklemede',
                    'confirmed' => 'Onaylandı',
                    'shipped' => 'Gönderildi',
                    'cancel_requested' => 'İptal İsteği',
                    'cancelled' => 'İptal Edildi',
                ])
                ->required(),
            Forms\Components\Textarea::make('cancel_reason')
                ->label('İptal Nedeni / Gerekçesi')
                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Talep No')
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Kaynak')
                    ->formatStateUsing(fn ($state) => $state === 'cart' ? '🛒 Sepet' : '🛍️ Ürün'),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Ürünler')
                    ->wrap()
                    ->getStateUsing(function (CustomerOrderRequest $record) {
                        if (empty($record->items)) return '—';
                        $count = count($record->items);
                        $first = $record->items[0]['name'] ?? 'Ürün';
                        return $count > 1 ? "{$first} (+ " . ($count - 1) . " ürün)" : $first;
                    })
                    ->tooltip(function (CustomerOrderRequest $record) {
                        return collect($record->items)->map(fn ($i) => ($i['name'] ?? 'Ürün') . ' × ' . ($i['qty'] ?? 1))->implode(', ');
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Tutar')
                    ->money('try')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => [
                        'pending' => 'Beklemede',
                        'confirmed' => 'Onaylandı',
                        'shipped' => 'Gönderildi',
                        'cancel_requested' => 'İptal İsteği',
                        'cancelled' => 'İptal Edildi',
                    ][$state] ?? $state)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'primary' => 'shipped',
                        'danger' => fn ($state) => in_array($state, ['cancel_requested', 'cancelled']),
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum Filtresi')
                    ->options([
                        'pending' => 'Beklemede',
                        'confirmed' => 'Onaylandı',
                        'shipped' => 'Gönderildi',
                        'cancel_requested' => 'İptal İsteği',
                        'cancelled' => 'İptal Edildi',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Sipariş Detayı ve İşlem')
                    ->modalHeading(fn (CustomerOrderRequest $record) => "Sipariş Talebi #{$record->id} - Detay")
                    ->modalSubheading(fn (CustomerOrderRequest $record) => $record->created_at ? $record->created_at->format('d.m.Y H:i') : '')
                    ->modalWidth('3xl')
                    ->modalButton('Değişiklikleri Kaydet')
                    ->mountUsing(fn (Forms\ComponentContainer $form, CustomerOrderRequest $record) => $form->fill([
                        'status' => $record->status,
                        'cancel_reason' => $record->cancel_reason,
                    ]))
                    ->action(function (CustomerOrderRequest $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'cancel_reason' => $data['cancel_reason'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Sipariş talebi güncellendi.')
                            ->success()
                            ->send();
                    })
                    ->form(\App\Filament\Resources\OrderRequestResource::detailsSchema()),
            ])
            ->bulkActions([]);
    }
}
