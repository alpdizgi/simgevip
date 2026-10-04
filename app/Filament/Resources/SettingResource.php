<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Genel Ayarlar';

    protected static ?string $navigationGroup = 'Site Ayarları';

    protected static ?string $modelLabel = 'Ayar';

    protected static ?string $pluralModelLabel = 'Ayarlar';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Tabs::make('Ayarlar')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Genel Ayarlar & SEO')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Group::make([
                                            Forms\Components\TextInput::make('site_name')
                                                ->label('Site / Marka Adı')
                                                ->placeholder('Örn: SimgeVIP Butik')
                                                ->helperText('Tarayıcı sekmelerinde ve sayfa başlıklarında görünen marka ismi.')
                                                ->maxLength(120)
                                                ->required(),
                                            Forms\Components\TextInput::make('meta_title')
                                                ->label('Ana sayfa başlığı (SEO)')
                                                ->placeholder('Seçilmiş giyim koleksiyonu')
                                                ->helperText('Marka adı başlığın sonunda yoksa otomatik eklenir.')
                                                ->maxLength(120),
                                            Forms\Components\Textarea::make('meta_description')
                                                ->label('Varsayılan meta açıklama (SEO)')
                                                ->placeholder('Seçilmiş giyim koleksiyonu. Sade, kurumsal ve zamansız parçalar.')
                                                ->helperText('Arama sonucunda görünen açıklama. En fazla 160 karakter.')
                                                ->rows(3)
                                                ->maxLength(160),
                                        ])->columnSpan(1),
                                        
                                        Forms\Components\Group::make([
                                            Forms\Components\FileUpload::make('logo_path')
                                                ->label('Site Logosu')
                                                ->image()
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                ->disk('public')
                                                ->directory('settings')
                                                ->imagePreviewHeight('120')
                                                ->maxSize(4096),
                                            Forms\Components\FileUpload::make('og_image_path')
                                                ->label('Paylaşım görseli (SEO)')
                                                ->image()
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                ->disk('public')
                                                ->directory('settings')
                                                ->imagePreviewHeight('120')
                                                ->maxSize(4096)
                                                ->helperText('WhatsApp, Instagram ve Google önizlemesi. Önerilen 1200×630 px. Boşsa logo kullanılır.'),
                                        ])->columnSpan(1),
                                    ])
                            ]),

                        Forms\Components\Tabs\Tab::make('İletişim & Sosyal Medya')
                            ->schema([
                                Forms\Components\Repeater::make('contact_info')
                                    ->label('Mağaza Şubeleri')
                                    ->schema([
                                        Forms\Components\TextInput::make('branch_name')
                                            ->label('Şube / Mağaza Adı')
                                            ->placeholder('Örn: Nişantaşı Mağazası veya Merkez')
                                            ->required()
                                            ->lazy()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('phone')
                                            ->label('Telefon Numarası')
                                            ->tel()
                                            ->placeholder('+90 212 000 00 00')
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('email')
                                            ->label('E-Posta Adresi')
                                            ->email()
                                            ->placeholder('iletisim@simgevip.com')
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('whatsapp')
                                            ->label('WhatsApp Numarası (Sipariş & İletişim)')
                                            ->placeholder('Örn: https://wa.me/905550000000')
                                            ->url()
                                            ->columnSpan(1),

                                        Forms\Components\Textarea::make('address')
                                            ->label('Açık Adres')
                                            ->placeholder('Örn: Harbiye Mah. Abdi İpekçi Cad. No: 15, Şişli / İstanbul')
                                            ->rows(2)
                                            ->columnSpan('full'),

                                        Forms\Components\TextInput::make('map_embed')
                                            ->label('Google Harita Embed URL')
                                            ->url()
                                            ->helperText('Yalnızca https://www.google.com/maps/embed?... adresini yapıştırın. Ham iframe HTML kabul edilmez; güvenlik için temizlenir.')
                                            ->placeholder('https://www.google.com/maps/embed?pb=...')
                                            ->dehydrateStateUsing(fn (?string $state) => \App\Support\SafeMapEmbed::url($state))
                                            ->columnSpan('full'),
                                    ])
                                    ->columns(['default' => 1, 'xl' => 3])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => !empty($state['branch_name']) ? ('📍 ' . $state['branch_name']) : 'Yeni Şube')
                                    ->createItemButtonLabel('Yeni Şube / Adres Ekle')
                                    ->columnSpan('full'),

                                Forms\Components\Repeater::make('social_media')
                                    ->label('Sosyal Ağlar')
                                    ->schema([
                                        Forms\Components\Select::make('platform')
                                            ->label('Platform')
                                            ->options([
                                                'instagram' => 'Instagram',
                                                'facebook' => 'Facebook',
                                                'tiktok' => 'TikTok',
                                                'whatsapp' => 'WhatsApp',
                                                'youtube' => 'YouTube',
                                                'twitter' => 'Twitter / X',
                                                'pinterest' => 'Pinterest',
                                                'linkedin' => 'LinkedIn',
                                            ])
                                            ->searchable()
                                            ->reactive()
                                            ->required()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('url')
                                            ->label('Profil Linki (URL)')
                                            ->placeholder('https://instagram.com/simgevip')
                                            ->url()
                                            ->required()
                                            ->columnSpan(1),
                                    ])
                                    ->columns(['default' => 1, 'lg' => 2])
                                    ->defaultItems(0)
                                    ->helperText('İsteğe bağlıdır. Yalnızca aktif olarak kullandığınız hesapları ekleyin.')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => !empty($state['platform']) ? (strtoupper($state['platform']) . (!empty($state['url']) ? ' - ' . $state['url'] : '')) : 'Yeni Profil')
                                    ->createItemButtonLabel('Yeni Sosyal Medya Hesabı Ekle')
                                    ->columnSpan('full'),
                            ]),
                            
                        Forms\Components\Tabs\Tab::make('E-Posta (SMTP)')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('mail_settings.host')
                                        ->label('SMTP Sunucusu (Host)')
                                        ->placeholder('mail.simgegiyim.com.tr')
                                        ->required(),
                                    Forms\Components\TextInput::make('mail_settings.port')
                                        ->label('SMTP Port')
                                        ->numeric()
                                        ->placeholder('465')
                                        ->required(),
                                    Forms\Components\Select::make('mail_settings.encryption')
                                        ->label('Şifreleme Türü')
                                        ->options([
                                            'ssl' => 'SSL',
                                            'tls' => 'TLS',
                                            '' => 'Yok',
                                        ])
                                        ->default('ssl'),
                                ])->columns(3),
                                
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('mail_settings.username')
                                        ->label('E-Posta Adresi (Kullanıcı Adı)')
                                        ->email()
                                        ->placeholder('info@simgegiyim.com.tr')
                                        ->required(),
                                    Forms\Components\TextInput::make('mail_settings.password')
                                        ->label('E-Posta Şifresi')
                                        ->password()
                                        ->required(),
                                ])->columns(2),
                                
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('mail_settings.from_address')
                                        ->label('Gönderici Adresi (E-postalarda görünecek)')
                                        ->email()
                                        ->placeholder('info@simgegiyim.com.tr')
                                        ->required(),
                                    Forms\Components\TextInput::make('mail_settings.from_name')
                                        ->label('Gönderici Adı (E-postalarda görünecek)')
                                        ->placeholder('Simge VIP Giyim')
                                        ->required(),
                                ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Sistem & Bakım')
                            ->schema([
                                Forms\Components\Toggle::make('maintenance_mode')
                                    ->label('Yapım aşamasına al')
                                    ->helperText('Açık olduğunda site ziyaretçilere bakım sayfası gösterilir. Admin panel erişimi etkilenmez.')
                                    ->onIcon('heroicon-o-lock-closed')
                                    ->offIcon('heroicon-o-lock-open')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('maintenance_message', 'Site şu anda bakım aşamasında. Kısa süre içinde tekrar hizmet vermeye başlayacağız.');
                                        }
                                    }),

                                Forms\Components\Textarea::make('maintenance_message')
                                    ->label('Bakım Mesajı')
                                    ->placeholder('Site şu anda bakım aşamasında. Kısa süre içinde tekrar hizmet vermeye başlayacağız.')
                                    ->helperText('Ziyaretçilere gösterilecek bakım mesajı.')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->visible(fn (callable $get) => $get('maintenance_mode')),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->height(36),
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Site / Marka Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('contact_info')
                    ->label('Kayıtlı Şubeler')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' Şube' : '-'),
                Tables\Columns\TextColumn::make('social_media')
                    ->label('Sosyal Ağlar')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' Hesap' : '-'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Son Güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Düzenle'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            //
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
