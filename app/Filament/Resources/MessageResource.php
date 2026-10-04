<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-mail';

    protected static ?string $navigationLabel = 'Gelen Mesajlar';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Mesaj';

    protected static ?string $pluralModelLabel = 'Mesajlar';

    protected static ?int $navigationSort = 7;

    public static function getNavigationBadge(): ?string
    {
        $unread = (int) static::getModel()::where('is_read', false)->count();
        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Mesaj İçeriği')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('sender_name')
                                        ->label('Gönderen Adı Soyadı')
                                        ->required(),

                                    Forms\Components\TextInput::make('email')
                                        ->label('E-posta Adresi')
                                        ->email()
                                        ->required(),
                                ]),

                                Forms\Components\TextInput::make('subject')
                                    ->label('Mesaj Konusu')
                                    ->required(),

                                Forms\Components\Textarea::make('message')
                                    ->label('Mesaj Metni')
                                    ->rows(8)
                                    ->required(),
                            ]),
                    ])->columnSpan(2),

                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Durum & İşlem')
                            ->schema([
                                Forms\Components\Toggle::make('is_read')
                                    ->label('Okundu Olarak İşaretle')
                                    ->helperText('Mesaj okundu mu?'),

                                Forms\Components\Placeholder::make('created_at_info')
                                    ->label('Gönderim Zamanı')
                                    ->content(fn (?Message $record) => $record?->created_at ? $record->created_at->format('d.m.Y H:i') : '—'),

                                Forms\Components\Placeholder::make('quick_reply')
                                    ->label('Hızlı İletişim')
                                    ->content(function (?Message $record) {
                                        if (! $record || blank($record->email)) {
                                            return '—';
                                        }
                                        $mailto = 'mailto:' . e($record->email) . '?subject=' . urlencode('Re: ' . ($record->subject ?? 'Mesajınız hk.'));
                                        return new HtmlString("
                                            <a href='{$mailto}' target='_blank' style='display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: #2563eb; color: #ffffff; border-radius: 6px; font-weight: 500; font-size: 13px; text-decoration: none;'>
                                                <span>✉️</span> E-posta ile Yanıtla
                                            </a>
                                        ");
                                    }),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('is_read')
                    ->label('Durum')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Okundu' : 'Yeni / Okunmadı')
                    ->colors([
                        'warning' => fn ($state): bool => ! (bool) $state,
                        'success' => fn ($state): bool => (bool) $state,
                    ]),

                Tables\Columns\TextColumn::make('sender_name')
                    ->label('Gönderen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Kopyalamak için tıklayın'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Konu')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mesaj Özeti')
                    ->limit(45)
                    ->tooltip(fn (Message $record): string => $record->message),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_read')
                    ->label('Okunma Durumu')
                    ->options([
                        '0' => 'Okunmamış (Yeni) Mesajlar',
                        '1' => 'Okunmuş Mesajlar',
                    ]),
            ])
            ->actions([
                // 1. Görüntüleme İkonu (Yerleşik Modal)
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('Mesajı Oku')
                    ->modalHeading('İletişim Mesajı')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Placeholder::make('sender_name')
                                ->label('Gönderen Kişi')
                                ->extraAttributes(['autofocus' => true, 'tabindex' => '-1'])
                                ->content(fn (Message $record) => $record->sender_name),

                            Forms\Components\Placeholder::make('email')
                                ->label('E-posta Adresi')
                                ->content(fn (Message $record) => new HtmlString("<a href='mailto:{$record->email}' style='color:var(--primary-500);text-decoration:underline;'>{$record->email}</a>")),

                            Forms\Components\Placeholder::make('subject')
                                ->label('Konu')
                                ->content(fn (Message $record) => $record->subject ?? '—'),

                            Forms\Components\Placeholder::make('created_at')
                                ->label('Tarih')
                                ->content(fn (Message $record) => $record->created_at ? $record->created_at->format('d.m.Y H:i') : '—'),
                        ]),

                        Forms\Components\Placeholder::make('message')
                            ->label('Mesaj İçeriği')
                            ->content(fn (Message $record) => new HtmlString('<div style="white-space: pre-wrap; padding: 16px; border-radius: 8px; background-color: rgba(150, 150, 150, 0.1); font-size: 14px;">' . nl2br(e($record->message)) . '</div>'))
                            ->columnSpan('full'),
                    ])
                    ->mutateRecordDataUsing(function (array $data, Message $record): array {
                        if (! $record->is_read) {
                            $record->update(['is_read' => true]);
                        }
                        return $data;
                    }),

                // 2. Silme İkonu (Çöp Kutusu)
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Sil'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_as_read')
                    ->label('Seçilenleri Okundu Yap')
                    ->icon('heroicon-o-check')
                    ->action(function (Collection $records): void {
                        $records->each->update(['is_read' => true]);
                        Notification::make()
                            ->title('Seçilen mesajlar okundu olarak işaretlendi.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('mark_as_unread')
                    ->label('Seçilenleri Okunmadı Yap')
                    ->icon('heroicon-o-mail')
                    ->action(function (Collection $records): void {
                        $records->each->update(['is_read' => false]);
                        Notification::make()
                            ->title('Seçilen mesajlar okunmadı olarak işaretlendi.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            MessageResource\Widgets\MessageStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }
}
