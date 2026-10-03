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
                // 1. Detay Göz İkonu (Modal)
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Mesajı Oku')
                    ->modalHeading(fn (Message $record) => "İletişim Mesajı")
                    ->modalSubheading(fn (Message $record) => "Gönderen: {$record->sender_name} · " . ($record->created_at ? $record->created_at->format('d.m.Y H:i') : ''))
                    ->modalWidth('2xl')
                    ->mountUsing(function (Forms\ComponentContainer $form, Message $record) {
                        if (! $record->is_read) {
                            $record->update(['is_read' => true]);
                        }
                    })
                    ->modalActions([
                        Tables\Actions\Modal\Actions\Action::make('reply')
                            ->label('E-posta ile Yanıtla')
                            ->url(fn (Message $record) => "mailto:{$record->email}?subject=" . urlencode("Re: " . ($record->subject ?? 'Mesajınız hk.')))
                            ->openUrlInNewTab()
                            ->button()
                            ->color('primary'),
                        Tables\Actions\Modal\Actions\Action::make('close')
                            ->label('Kapat')
                            ->cancel(),
                    ])
                    ->form([
                        Forms\Components\Placeholder::make('message_detail_card')
                            ->label('')
                            ->content(function (?Message $record) {
                                if (! $record) return '';

                                $name = e($record->sender_name);
                                $email = e($record->email);
                                $subject = e($record->subject ?? 'Konu Belirtilmemiş');
                                $message = nl2br(e($record->message));
                                $date = $record->created_at ? $record->created_at->format('d.m.Y H:i') : '—';
                                $mailto = "mailto:{$email}?subject=" . urlencode("Re: {$subject}");

                                return new HtmlString("
                                    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 14px;'>
                                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>
                                            <div>
                                                <span style='font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;'>Gönderen Kişi:</span>
                                                <div style='font-size: 14px; font-weight: 600; color: #0f172a;'>{$name}</div>
                                            </div>
                                            <div>
                                                <span style='font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;'>E-posta Adresi:</span>
                                                <div style='font-size: 14px; font-weight: 500; color: #2563eb;'>
                                                    <a href='{$mailto}' style='text-decoration: underline;'>{$email}</a>
                                                </div>
                                            </div>
                                            <div>
                                                <span style='font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;'>Konu:</span>
                                                <div style='font-size: 13px; font-weight: 600; color: #334155;'>{$subject}</div>
                                            </div>
                                            <div>
                                                <span style='font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;'>Tarih:</span>
                                                <div style='font-size: 13px; color: #475569;'>{$date}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style='background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;'>
                                        <div style='font-size: 11px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 8px;'>Mesaj İçeriği</div>
                                        <div style='font-size: 14px; line-height: 1.6; color: #1e293b; white-space: pre-wrap;'>{$message}</div>
                                    </div>
                                ");
                            })
                            ->columnSpan('full'),
                    ]),

                // 2. Hızlı Okundu / Okunmadı Yap Aksiyonu
                Tables\Actions\Action::make('toggle_read')
                    ->icon(fn (Message $record): string => $record->is_read ? 'heroicon-o-mail' : 'heroicon-o-mail-open')
                    ->iconButton()
                    ->tooltip(fn (Message $record): string => $record->is_read ? 'Okunmadı Olarak İşaretle' : 'Okundu Olarak İşaretle')
                    ->action(function (Message $record): void {
                        $record->update(['is_read' => ! $record->is_read]);
                        Notification::make()
                            ->title($record->is_read ? 'Mesaj okundu olarak işaretlendi.' : 'Mesaj okunmadı olarak işaretlendi.')
                            ->success()
                            ->send();
                    }),

                // 3. Düzenle İkonu (Kalem)
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Düzenle'),

                // 4. Silme İkonu (Çöp Kutusu)
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
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
