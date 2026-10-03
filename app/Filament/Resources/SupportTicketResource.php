<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2';

    protected static ?string $navigationLabel = 'Destek Talepleri';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Destek Talebi';

    protected static ?string $pluralModelLabel = 'Destek Talepleri';

    protected static ?int $navigationSort = 8;

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) SupportTicket::where('status', 'open')->whereNull('admin_read_at')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                // Sol Bölüm: Yazışma ve Yanıt Alanı (2 Kolon)
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Görüşme ve Mesajlar')
                        ->schema([
                            Forms\Components\Placeholder::make('subject_title')
                                ->label('')
                                ->content(function (?SupportTicket $record) {
                                    if (! $record) return '';
                                    $catMap = [
                                        'order' => 'Sipariş',
                                        'product' => 'Ürün',
                                        'reservation' => 'Mağazada Ayırma',
                                        'account' => 'Hesap',
                                        'general' => 'Genel',
                                    ];
                                    $cat = $catMap[$record->category] ?? $record->category;
                                    return new HtmlString("
                                        <div style='display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;'>
                                            <div style='font-size: 15px; font-weight: 700; color: #1e293b;'>{$record->subject}</div>
                                            <span style='display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background: #e0f2fe; color: #0369a1;'>Kategori: {$cat}</span>
                                        </div>
                                    ");
                                })
                                ->columnSpan('full'),

                            Forms\Components\Placeholder::make('conversation')
                                ->label('Yazışma Geçmişi')
                                ->content(fn (?SupportTicket $record) => new HtmlString(
                                    $record ? view('filament.support-conversation', ['messages' => $record->messages()->oldest()->get()])->render() : '—'
                                ))
                                ->columnSpan('full'),

                            Forms\Components\Textarea::make('reply')
                                ->label('Müşteriye Yanıt Yaz')
                                ->rows(5)
                                ->maxLength(10000)
                                ->placeholder('Müşteriye iletmek istediğiniz yanıtı buraya yazınız...')
                                ->helperText('Kaydettiğinizde yanıt anında müşterinin "Hesabım > Destek Taleplerim" sayfasında görünür ve talep durumu "Müşteri Bekleniyor" olarak güncellenir.')
                                ->columnSpan('full'),
                        ]),
                ])->columnSpan(2),

                // Sağ Bölüm: Yönetim ve Müşteri Özeti (1 Kolon)
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Talep Yönetimi')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Talep Durumu')
                                ->options([
                                    'open' => 'Açık (İşlem Bekliyor)',
                                    'waiting_customer' => 'Müşteri Yanıtı Bekleniyor',
                                    'resolved' => 'Çözüldü',
                                    'closed' => 'Kapatıldı',
                                ])
                                ->required(),

                            Forms\Components\Select::make('priority')
                                ->label('Öncelik')
                                ->options([
                                    'low' => 'Düşük',
                                    'normal' => 'Normal',
                                    'high' => 'Yüksek (Acil)',
                                ])
                                ->required(),

                            Forms\Components\Placeholder::make('customer_info')
                                ->label('Müşteri Bilgisi')
                                ->content(function (?SupportTicket $record) {
                                    if (! $record || ! $record->customer) return '—';
                                    $c = $record->customer;
                                    $name = e($c->name);
                                    $email = e($c->email ?: $c->login_email ?: '—');
                                    $phone = e($c->phone ?: $c->login_phone ?: '—');
                                    $customerUrl = CustomerResource::getUrl('view', ['record' => $c]);

                                    return new HtmlString("
                                        <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 13px; line-height: 1.5;'>
                                            <div style='font-weight: 700; color: #0f172a; margin-bottom: 4px;'>{$name}</div>
                                            <div style='color: #64748b;'>✉️ {$email}</div>
                                            <div style='color: #64748b;'>📞 {$phone}</div>
                                            <div style='margin-top: 8px;'>
                                                <a href='{$customerUrl}' target='_blank' style='font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: underline;'>
                                                    Müşteri Profilini Görüntüle →
                                                </a>
                                            </div>
                                        </div>
                                    ");
                                }),

                            Forms\Components\Placeholder::make('dates_summary')
                                ->label('Zaman Çizelgesi')
                                ->content(function (?SupportTicket $record) {
                                    if (! $record) return '—';
                                    $created = $record->created_at ? $record->created_at->format('d.m.Y H:i') : '—';
                                    $lastReply = $record->last_reply_at ? $record->last_reply_at->format('d.m.Y H:i') : '—';

                                    return new HtmlString("
                                        <div style='font-size: 12px; color: #475569; display: grid; gap: 4px;'>
                                            <div><strong>Oluşturulma:</strong> {$created}</div>
                                            <div><strong>Son Hareket:</strong> {$lastReply}</div>
                                        </div>
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
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('unread_badge')
                    ->label('')
                    ->getStateUsing(function (SupportTicket $record): ?string {
                        if ($record->status === 'open' && $record->admin_read_at === null) {
                            return 'Yeni';
                        }
                        return null;
                    })
                    ->colors([
                        'danger' => 'Yeni',
                    ]),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Konu')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => [
                        'order' => 'Sipariş',
                        'product' => 'Ürün',
                        'reservation' => 'Ayırma',
                        'account' => 'Hesap',
                        'general' => 'Genel',
                    ][$state] ?? $state)
                    ->colors([
                        'primary' => 'order',
                        'secondary' => 'general',
                        'success' => 'product',
                        'warning' => 'reservation',
                        'danger' => 'account',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => [
                        'open' => 'Açık',
                        'waiting_customer' => 'Müşteri Bekleniyor',
                        'resolved' => 'Çözüldü',
                        'closed' => 'Kapalı',
                    ][$state] ?? $state)
                    ->colors([
                        'warning' => 'open',
                        'primary' => 'waiting_customer',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ]),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Öncelik')
                    ->formatStateUsing(fn ($state) => [
                        'low' => 'Düşük',
                        'normal' => 'Normal',
                        'high' => 'Yüksek',
                    ][$state] ?? $state)
                    ->colors([
                        'danger' => 'high',
                        'primary' => 'normal',
                        'secondary' => 'low',
                    ]),

                Tables\Columns\TextColumn::make('last_reply_at')
                    ->label('Son Hareket')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_reply_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum Filtresi')
                    ->options([
                        'open' => 'Açık (İşlem Bekleyenler)',
                        'waiting_customer' => 'Müşteri Bekleniyor',
                        'resolved' => 'Çözüldü',
                        'closed' => 'Kapalı',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Öncelik Seviyesi')
                    ->options([
                        'high' => 'Yüksek (Acil)',
                        'normal' => 'Normal',
                        'low' => 'Düşük',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'order' => 'Sipariş',
                        'product' => 'Ürün',
                        'reservation' => 'Mağazada Ayırma',
                        'account' => 'Hesap',
                        'general' => 'Genel',
                    ]),
            ])
            ->actions([
                // Detay İkonu (Görüntüleme, Durum Güncelleme ve Yanıtlama Tek Yerde)
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Talebi Görüntüle ve Yanıtla')
                    ->modalHeading(fn (SupportTicket $record) => "Destek Talebi #{$record->id} — {$record->subject}")
                    ->modalSubheading(fn (SupportTicket $record) => "Müşteri: " . ($record->customer?->name ?? 'Kayıtlı Müşteri') . " · Son Mesaj: " . ($record->last_reply_at ? $record->last_reply_at->format('d.m.Y H:i') : '—'))
                    ->modalWidth('3xl')
                    ->modalButton('Kaydet ve Yanıtla')
                    ->mountUsing(function (Forms\ComponentContainer $form, SupportTicket $record) {
                        if ($record->admin_read_at === null) {
                            $record->update(['admin_read_at' => now()]);
                        }
                        $form->fill([
                            'status' => $record->status,
                            'priority' => $record->priority,
                        ]);
                    })
                    ->action(function (SupportTicket $record, array $data): void {
                        $reply = trim((string) ($data['quick_reply'] ?? ''));
                        $status = $data['status'] ?? $record->status;
                        if ($reply !== '' && $status === 'open') {
                            $status = 'waiting_customer';
                        }

                        $record->update([
                            'status' => $status,
                            'priority' => $data['priority'] ?? $record->priority,
                            'admin_read_at' => now(),
                        ]);

                        if ($reply !== '') {
                            $record->messages()->create([
                                'author_type' => 'admin',
                                'author_id' => auth()->id(),
                                'body' => $reply,
                            ]);
                            $record->update([
                                'last_reply_at' => now(),
                                'customer_read_at' => null,
                            ]);
                        }

                        Notification::make()
                            ->title('Destek talebi başarıyla güncellendi.')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\Placeholder::make('ticket_info')
                            ->label('')
                            ->content(function (?SupportTicket $record) {
                                if (! $record) return '';
                                $catMap = [
                                    'order' => 'Sipariş',
                                    'product' => 'Ürün',
                                    'reservation' => 'Mağazada Ayırma',
                                    'account' => 'Hesap',
                                    'general' => 'Genel',
                                ];
                                $cat = $catMap[$record->category] ?? $record->category;
                                $c = $record->customer;
                                $cName = e($c?->name ?? 'Kayıtlı Müşteri');
                                $cEmail = e($c?->email ?: $c?->login_email ?: '—');
                                $cPhone = e($c?->phone ?: $c?->login_phone ?: '—');
                                $created = $record->created_at ? $record->created_at->format('d.m.Y H:i') : '—';

                                return new HtmlString("
                                    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; font-size: 12.5px; color: #475569;'>
                                        <div><strong>👤 Müşteri:</strong> <span style='font-weight: 600; color: #1e293b;'>{$cName}</span></div>
                                        <div><strong>✉️ E-posta:</strong> {$cEmail}</div>
                                        <div><strong>📞 Telefon:</strong> {$cPhone}</div>
                                        <div><strong>🏷️ Kategori:</strong> <span style='font-weight: 600;'>{$cat}</span></div>
                                        <div><strong>🕒 Tarih:</strong> {$created}</div>
                                    </div>
                                ");
                            })
                            ->columnSpan('full'),

                        Forms\Components\Placeholder::make('conversation_preview')
                            ->label('Yazışma Geçmişi')
                            ->content(fn (?SupportTicket $record) => new HtmlString(
                                $record ? view('filament.support-conversation', ['messages' => $record->messages()->oldest()->get()])->render() : '—'
                            ))
                            ->columnSpan('full'),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('status')
                                ->label('Talep Durumu')
                                ->options([
                                    'open' => 'Açık (İşlem Bekliyor)',
                                    'waiting_customer' => 'Müşteri Yanıtı Bekleniyor',
                                    'resolved' => 'Çözüldü',
                                    'closed' => 'Kapalı',
                                ])
                                ->required(),

                            Forms\Components\Select::make('priority')
                                ->label('Öncelik')
                                ->options([
                                    'low' => 'Düşük',
                                    'normal' => 'Normal',
                                    'high' => 'Yüksek',
                                ])
                                ->required(),
                        ]),

                        Forms\Components\Textarea::make('quick_reply')
                            ->label('Müşteriye Yanıt Yaz')
                            ->rows(4)
                            ->placeholder('Müşteriye iletilecek yanıtı buraya yazabilirsiniz (boş bırakırsanız sadece durum/öncelik güncellenir)...')
                            ->helperText('Yanıt kaydedildiğinde müşterinin destek sayfasında anında görünür.')
                            ->columnSpan('full'),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_resolved')
                    ->label('Seçilenleri Çözüldü Yap')
                    ->icon('heroicon-o-check')
                    ->action(function (Collection $records): void {
                        $records->each->update(['status' => 'resolved']);
                        Notification::make()->title('Seçilen talepler çözüldü olarak işaretlendi.')->success()->send();
                    }),

                Tables\Actions\BulkAction::make('mark_closed')
                    ->label('Seçilenleri Kapat')
                    ->icon('heroicon-o-lock-closed')
                    ->action(function (Collection $records): void {
                        $records->each->update(['status' => 'closed']);
                        Notification::make()->title('Seçilen talepler kapatıldı.')->success()->send();
                    }),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            SupportTicketResource\Widgets\SupportTicketStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
