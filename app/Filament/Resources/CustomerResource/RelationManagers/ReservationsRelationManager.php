<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Reservation;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;


class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Mağaza Talepleri / Randevular';

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Mağaza talebi veya randevu bulunamadı';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Müşterinin ürün ayırma talepleri ve mağaza randevuları burada listelenir.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DateTimePicker::make('reservation_date')
                ->label('Tarih ve Saat')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Durum')
                ->options([
                    'pending' => 'Beklemede',
                    'confirmed' => 'Onaylandı',
                    'cancelled' => 'İptal Edildi',
                ])
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->label('Notlar')
                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('is_admin_hold')
                    ->label('Ayırma Türü')
                    ->formatStateUsing(fn ($state) => $state ? '👑 Yönetici Ayrımı' : '🌐 Web Talebi')
                    ->colors([
                        'primary' => fn ($state) => (bool) $state,
                        'secondary' => fn ($state) => ! (bool) $state,
                    ]),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('İlgili Ürün')
                    ->default('Genel Randevu / Ayırma')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Randevu Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => [
                        'pending' => 'Beklemede',
                        'confirmed' => 'Onaylandı',
                        'cancelled' => 'İptal Edildi',
                    ][$state] ?? $state)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('clean_notes')
                    ->label('Müşteri Notu')
                    ->limit(35),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum Filtresi')
                    ->options([
                        'pending' => 'Beklemede',
                        'confirmed' => 'Onaylandı',
                        'cancelled' => 'İptal Edildi',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Rezervasyon Detayı ve İşlem')
                    ->modalHeading(fn (Reservation $record) => "Rezervasyon / Ürün Ayırma #{$record->id} - Detay")
                    ->modalSubheading(fn (Reservation $record) => $record->reservation_date ? $record->reservation_date->format('d.m.Y H:i') : '')
                    ->modalWidth('3xl')
                    ->modalButton('Değişiklikleri Kaydet')
                    ->mountUsing(fn (Forms\ComponentContainer $form, Reservation $record) => $form->fill([
                        'status' => $record->status,
                        'is_admin_hold' => (bool) $record->is_admin_hold,
                        'notes' => $record->clean_notes,
                    ]))
                    ->action(function (Reservation $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'is_admin_hold' => (bool) ($data['is_admin_hold'] ?? false),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Rezervasyon bilgileri güncellendi.')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\Placeholder::make('product_overview')
                            ->label('Mağazada Ayrılan Ürün')
                            ->content(fn (?Reservation $record) => view('filament.resources.reservation-resource.details', ['record' => $record]))
                            ->columnSpan('full'),

                        Forms\Components\Placeholder::make('customer_notes_display')
                            ->label('Müşteri Talebi / Notu')
                            ->visible(fn (?Reservation $record) => filled($record?->clean_notes))
                            ->content(fn (?Reservation $record) => $record?->clean_notes)
                            ->columnSpan('full'),

                        Forms\Components\Section::make('Durum ve Yönetici Notları')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Rezervasyon / Ayırma Durumu')
                                    ->options([
                                        'pending' => 'Beklemede',
                                        'confirmed' => 'Onaylandı',
                                        'cancelled' => 'İptal Edildi',
                                    ])
                                    ->required(),

                                Forms\Components\Toggle::make('is_admin_hold')
                                    ->label('Yönetici Tarafından Ayrıldı Olarak İşaretle'),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Yönetici Notu')
                                    ->placeholder('Bu ürün ayırma ile ilgili yönetici notu...')
                                    ->rows(2)
                                    ->columnSpan('full'),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->bulkActions([]);
    }
}
