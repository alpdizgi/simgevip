<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Customer;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Rezervasyonlar';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Rezervasyon / Mağaza Ayırma';

    protected static ?string $pluralModelLabel = 'Rezervasyonlar / Mağazada Ayırma';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema(static::reservationSchema());
    }

    public static function reservationSchema(): array
    {
        return [
                Forms\Components\Placeholder::make('reservation_summary')
                    ->label('Rezervasyon Özeti')
                    ->visible(fn (?Reservation $record) => $record !== null)
                    ->content(fn (?Reservation $record) => view('filament.resources.reservation-resource.details', ['record' => $record]))
                    ->columnSpan('full'),
                Forms\Components\Section::make('Müşteri ve Ürün Seçimi')
                    ->description('Kayıtlı bir müşteri ve müşterinin talep ettiği ürünü seçerek mağazada ayırma yapabilirsiniz.')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Müşteri Seçin (Opsiyonel)')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($customer = Customer::find($state)) {
                                    $set('customer_name', $customer->name);
                                    $set('phone', $customer->phone ?: $customer->login_phone);
                                    $set('email', $customer->email ?: $customer->login_email);
                                }
                            })
                            ->helperText('Kayıtlı müşteri seçildiğinde Ad, Telefon ve E-posta otomatik doldurulur.'),

                        Forms\Components\Select::make('product_id')
                            ->label('Mağazada Ayrılacak Ürün (Opsiyonel)')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} (" . number_format((float) ($record->discounted_price ?? $record->price), 2, ',', '.') . " TL)")
                            ->helperText('Müşterinin talep ettiği veya mağazada görmek istediği ürünü seçiniz.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Rezervasyon & Ayırma Detayları')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Müşteri Adı Soyadı')
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon Numarası')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('E-posta Adresi')
                            ->email(),

                        Forms\Components\DateTimePicker::make('reservation_date')
                            ->label('Ayırma / Randevu Tarihi')
                            ->default(now()->addDays(2))
                            ->required(),

                        Forms\Components\TextInput::make('guests_count')
                            ->label('Kişi Sayısı')
                            ->numeric()
                            ->rules(['integer'])
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Durum')
                            ->options([
                                'pending' => 'Beklemede',
                                'confirmed' => 'Onaylandı',
                                'cancelled' => 'İptal Edildi',
                            ])
                            ->rules([\Illuminate\Validation\Rule::in(['pending', 'confirmed', 'cancelled'])])
                            ->default('confirmed')
                            ->required(),

                        Forms\Components\Toggle::make('is_admin_hold')
                            ->label('Yönetici / Mağaza Tarafından Ayrıldı')
                            ->default(true)
                            ->helperText('Bu seçenek aktif olduğunda rezervasyon tablosunda "Yönetici Ayrımı" olarak etiketlenir.')
                            ->columnSpan('full'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Rezervasyon Notları')
                            ->helperText('Bu not müşteri hesabında da görünür. Eski kayıtlardaki Renk, Beden ve SKU satırlarını koruyun.')
                            ->placeholder('Örn: Siyah renk 38 beden rezerve edildi, Cumartesi saat 15:00e kadar tutulacak...')
                            ->rows(3)
                            ->columnSpan('full'),
                    ])
                    ->columns(3),
            ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'product']);
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
                    ->formatStateUsing(fn ($state) => $state ? 'Mağaza Ayırması' : 'Web Talebi')
                    ->colors([
                        'primary' => fn ($state) => (bool) $state,
                        'secondary' => fn ($state) => ! (bool) $state,
                    ]),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Ayrılan Ürün')
                    ->default('Genel Randevu / Ayırma')
                    ->searchable()
                    ->wrap(),

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

                Tables\Filters\SelectFilter::make('is_admin_hold')
                    ->label('Ayırma Türü')
                    ->options([
                        '1' => 'Mağaza Ayırması',
                        '0' => 'Web Talebi',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Reservation $record) => static::canEdit($record))
                    ->tooltip('Rezervasyon Detayı ve İşlem')
                    ->modalHeading(fn (Reservation $record) => "Rezervasyon / Ürün Ayırma #{$record->id} - Detay")
                    ->modalSubheading(fn (Reservation $record) => "{$record->customer_name} · " . ($record->reservation_date ? $record->reservation_date->format('d.m.Y H:i') : ''))
                    ->modalWidth('3xl')
                    ->modalButton('Değişiklikleri Kaydet')
                    ->mountUsing(fn (Forms\ComponentContainer $form, Reservation $record) => $form->fill($record->attributesToArray()))
                    ->action(function (Reservation $record, array $data): void {
                        abort_unless(static::canEdit($record), 403);
                        $record->update($data);
                        Notification::make()->title('Rezervasyon bilgileri güncellendi.')->success()->send();
                    })
                    ->form(static::reservationSchema()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ReservationResource\Widgets\ReservationStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
