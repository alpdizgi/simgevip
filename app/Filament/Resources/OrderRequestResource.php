<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderRequestResource\Pages;
use App\Models\CustomerOrderRequest;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class OrderRequestResource extends Resource
{
    protected static ?string $model = CustomerOrderRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Sipariş Talepleri';
    protected static ?string $navigationGroup = 'İşlemler';
    protected static ?string $modelLabel = 'Sipariş Talebi';
    protected static ?string $pluralModelLabel = 'Sipariş Talepleri';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('customer');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::detailsSchema());
    }

    public static function detailsSchema(): array
    {
        return [
            Forms\Components\Placeholder::make('order_details')->label('')
                ->content(fn (?CustomerOrderRequest $record) => view('filament.resources.order-request-resource.details', ['record' => $record]))
                ->columnSpan('full'),
            Forms\Components\Section::make('Talep İşlemleri')->schema([
                Forms\Components\Select::make('status')->label('Talep Durumu')
                    ->options(CustomerOrderRequest::STATUS_LABELS)
                    ->rules([\Illuminate\Validation\Rule::in(array_keys(CustomerOrderRequest::STATUS_LABELS))])
                    ->required(),
                Forms\Components\Textarea::make('cancel_reason')->label('İptal Gerekçesi')
                    ->helperText('Mevcut gerekçe durum değiştiğinde geçmiş bilgi olarak korunur.')
                    ->maxLength(1000)->rows(3),
            ])->columns(['default' => 1, 'md' => 2])->columnSpan('full'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('Talep No')->sortable()->searchable()
                ->formatStateUsing(fn ($state) => '#' . $state),
            Tables\Columns\TextColumn::make('customer.name')->label('Müşteri')->searchable()->wrap()->placeholder('—'),
            Tables\Columns\TextColumn::make('source')->label('Kaynak')
                ->formatStateUsing(fn ($state) => ['cart' => 'Sepet', 'product' => 'Ürün Sayfası'][$state] ?? '—'),
            Tables\Columns\TextColumn::make('total')->label('Toplam')->alignRight()->sortable()
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.') . ' TL'),
            Tables\Columns\BadgeColumn::make('status')->label('Durum')
                ->formatStateUsing(fn ($state) => CustomerOrderRequest::STATUS_LABELS[$state] ?? $state)
                ->colors([
                    'warning' => fn ($state) => in_array($state, ['pending', 'cancel_requested'], true),
                    'success' => 'confirmed', 'primary' => 'shipped', 'danger' => 'cancelled',
                ]),
            Tables\Columns\TextColumn::make('created_at')->label('Talep Tarihi')->dateTime('d.m.Y H:i')->sortable(),
        ])->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('status')->label('Talep Durumu')->options(CustomerOrderRequest::STATUS_LABELS),
        ])->actions([
            Tables\Actions\Action::make('view_details')->label('Detay ve İşlem')->icon('heroicon-o-eye')
                ->visible(fn (CustomerOrderRequest $record) => static::canEdit($record))
                ->modalHeading(fn (CustomerOrderRequest $record) => "Sipariş Talebi #{$record->id}")
                ->modalWidth('4xl')->modalButton('Değişiklikleri Kaydet')
                ->mountUsing(fn (Forms\ComponentContainer $form, CustomerOrderRequest $record) => $form->fill([
                    'status' => $record->status, 'cancel_reason' => $record->cancel_reason,
                ]))
                ->action(function (CustomerOrderRequest $record, array $data): void {
                    abort_unless(static::canEdit($record), 403);
                    $record->update(['status' => $data['status'], 'cancel_reason' => $data['cancel_reason'] ?? null]);
                    Notification::make()->title('Sipariş talebi güncellendi.')->success()->send();
                })->form(static::detailsSchema()),
        ]);
    }

    public static function getWidgets(): array
    {
        return [OrderRequestResource\Widgets\OrderRequestStatsWidget::class];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListOrderRequests::route('/'), 'edit' => Pages\EditOrderRequest::route('/{record}/edit')];
    }
}
