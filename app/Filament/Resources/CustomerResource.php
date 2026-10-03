<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Müşteri Yönetimi';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Müşteri';

    protected static ?string $pluralModelLabel = 'Müşteriler';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Section::make('Kişisel Bilgiler')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Ad Soyad')
                                ->required(),
                            Forms\Components\TextInput::make('email')
                                ->label('E-posta')
                                ->email(),
                            Forms\Components\TextInput::make('phone')
                                ->label('Telefon'),
                            Forms\Components\TextInput::make('login_email')
                                ->label('Giriş E-postası')
                                ->email(),
                            Forms\Components\TextInput::make('login_phone')
                                ->label('Giriş Telefonu'),
                            Forms\Components\Placeholder::make('membership')
                                ->label('Üyelik Durumu')
                                ->content(fn (?Customer $record) => $record && $record->password ? '✅ Aktif Üyelik (Giriş Yapabilir)' : '👤 Kayıtlı Müşteri (Üyelik Şifresi Yok)'),
                        ])
                        ->columns(2)
                        ->columnSpan(2),

                    Forms\Components\Section::make('Müşteri Özeti')
                        ->schema([
                            Forms\Components\Placeholder::make('created_at_info')
                                ->label('Kayıt Tarihi')
                                ->content(fn (?Customer $record) => $record?->created_at ? $record->created_at->format('d.m.Y H:i') : '—'),
                            Forms\Components\Placeholder::make('orders_count')
                                ->label('Toplam Sipariş Talebi')
                                ->content(fn (?Customer $record) => $record ? ($record->orderRequests()->count() . ' Adet') : '—'),
                            Forms\Components\Placeholder::make('total_spent')
                                ->label('Toplam Sipariş Tutarı')
                                ->content(function (?Customer $record) {
                                    if (! $record) return '—';
                                    $total = $record->orderRequests()->sum('total');
                                    return number_format((float) $total, 2, ',', '.') . ' TL';
                                }),
                            Forms\Components\Placeholder::make('cart_summary')
                                ->label('Sepet Durumu')
                                ->content(function (?Customer $record) {
                                    if (! $record || empty($record->cart_items)) return 'Sepet boş';
                                    $count = count($record->cart_items);
                                    return "🛒 Sepette {$count} ürün var";
                                }),
                        ])
                        ->columnSpan(1),

                    Forms\Components\Section::make('Adres ve Notlar')
                        ->schema([
                            Forms\Components\Textarea::make('address')
                                ->label('Adres')
                                ->rows(3),
                            Forms\Components\Textarea::make('notes')
                                ->label('Yönetici Notları')
                                ->rows(3),
                        ])
                        ->columns(2)
                        ->columnSpan('full'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['orderRequests', 'reservations']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Posta')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->wrap()
                    ->icon('heroicon-o-mail'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->icon('heroicon-o-phone'),

                Tables\Columns\BadgeColumn::make('membership')
                    ->label('Üyelik')
                    ->alignCenter()
                    ->getStateUsing(fn (Customer $record): string => filled($record->password) ? 'Üye' : 'Misafir')
                    ->colors([
                        'success' => 'Üye',
                        'secondary' => 'Misafir',
                    ]),

                Tables\Columns\TextColumn::make('order_requests_count')
                    ->label('Siparişler')
                    ->counts('orderRequests')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('reservations_count')
                    ->label('Randevular')
                    ->counts('reservations')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kayıt Tarihi')
                    ->date('d.m.Y')
                    ->description(fn (Customer $record): ?string => $record->created_at?->format('H:i'))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('membership')
                    ->label('Üyelik Durumu')
                    ->placeholder('Tümü')
                    ->trueLabel('Sadece Kayıtlı Üyeler')
                    ->falseLabel('Sadece Misafir Müşteriler')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('password')->where('password', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('password')->orWhere('password', '')),
                    ),
                Tables\Filters\Filter::make('has_orders')
                    ->label('Sipariş Talebi Olanlar')
                    ->query(fn ($query) => $query->has('orderRequests')),
                Tables\Filters\Filter::make('has_reservations')
                    ->label('Randevusu Olanlar')
                    ->query(fn ($query) => $query->has('reservations')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detay')->icon('heroicon-o-eye')->color('secondary')->tooltip('Müşteri detayı ve geçmişi'),
                Tables\Actions\EditAction::make()->label('Düzenle')->icon('heroicon-o-pencil-alt')->iconButton()->tooltip('Düzenle'),
                Tables\Actions\DeleteAction::make()->label('Sil')->icon('heroicon-o-trash')->iconButton()->tooltip('Sil'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerStatsWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            CustomerResource\RelationManagers\OrderRequestsRelationManager::class,
            CustomerResource\RelationManagers\ReservationsRelationManager::class,
            CustomerResource\RelationManagers\FavoritesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
