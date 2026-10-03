<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photograph';

    protected static ?string $navigationLabel = 'Slider Yönetimi';

    protected static ?string $navigationGroup = 'Site Ayarları';

    protected static ?string $modelLabel = 'Slider';

    protected static ?string $pluralModelLabel = 'Sliderlar';

    protected static ?int $navigationSort = 0;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    // Sol Kolon: Görsel ve İçerik (2 Kolon)
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Slider Görseli ve Bağlantı')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Slider Görseli')
                                    ->helperText('Önerilen boyut: 1920×900 px (yatay). Görsel tüm ekranlarda kırpılmadan, kendi oranıyla tam genişlikte gösterilir. Yalnızca JPG, PNG veya WebP.')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('sliders')
                                    ->maxSize(5120)
                                    ->required()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('title')
                                    ->label('Slider Başlığı / Yönetim Notu')
                                    ->placeholder('Örn: Yeni Sezon İndirim Bannerı')
                                    ->helperText('Yönetim panelinde kolay ayırt etmek için başlık veya not.')
                                    ->maxLength(180),

                                Forms\Components\TextInput::make('link')
                                    ->label('Tıklanınca Gidilecek Link')
                                    ->helperText('Örn: /koleksiyon , /kategori/elbise veya https://...')
                                    ->placeholder('/koleksiyon')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])->columnSpan(2),

                    // Sağ Kolon: Yayın ve Sıralama (1 Kolon)
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Yayın ve Sıralama')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Yayında (Aktif)')
                                    ->default(true)
                                    ->helperText('Açık olduğunda anasayfada gösterilir.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Görüntülenme Sırası')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Küçük sayı (0, 1, 2...) önce gelir. İlk sıradaki aktif slider anasayfa açılışında gösterilir.'),

                                Forms\Components\Placeholder::make('slider_preview_info')
                                    ->label('Durum Özeti')
                                    ->content(function (?Slider $record) {
                                        if (! $record) {
                                            return '✨ Yeni Slider Kaydı';
                                        }
                                        if (! $record->is_active) {
                                            return '⚪ Pasif (Yayında Değil)';
                                        }
                                        return '🟢 Anasayfada Yayında (' . $record->sort_order . '. Sırada)';
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
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Afiş Önizleme')
                    ->disk('public')
                    ->width(130)
                    ->height(60)
                    ->rounded(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Başlık / Not')
                    ->placeholder('Başlık Belirtilmemiş')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('link')
                    ->label('Hedef Bağlantı')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn (Slider $record): string => $record->link),

                Tables\Columns\TextInputColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Yayında')
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Yayın Durumu')
                    ->trueLabel('Aktif Olanlar')
                    ->falseLabel('Pasif Olanlar'),
            ])
            ->actions([
                // 1. Önizleme Modalı (Göz İkonu)
                Tables\Actions\Action::make('preview')
                    ->label('Önizleme')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Sliderı İncele')
                    ->modalHeading(fn (Slider $record) => "Slider Önizleme: " . ($record->title ?: "Sıra #{$record->sort_order}"))
                    ->modalWidth('3xl')
                    ->modalActions([
                        Tables\Actions\Modal\Actions\Action::make('edit')
                            ->label('Düzenle')
                            ->url(fn (Slider $record) => static::getUrl('edit', ['record' => $record]))
                            ->button()
                            ->color('primary'),
                        Tables\Actions\Modal\Actions\Action::make('close')
                            ->label('Kapat')
                            ->cancel(),
                    ])
                    ->form([
                        Forms\Components\Placeholder::make('slider_preview')
                            ->label('')
                            ->content(function (?Slider $record) {
                                if (! $record) return '';

                                $imgUrl = asset('storage/' . ltrim($record->image_path, '/'));
                                $linkUrl = $record->link;
                                $statusHtml = $record->is_active
                                    ? "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#dcfce7; color:#15803d; border-radius:6px;'>🟢 Sitede Yayında</span>"
                                    : "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#f3f4f6; color:#4b5563; border-radius:6px;'>⚪ Pasif</span>";

                                return new HtmlString("
                                    <div style='margin-bottom: 14px; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb; background: #000;'>
                                        <img src='{$imgUrl}' alt='{$record->title}' style='width: 100%; max-height: 320px; object-fit: contain; display: block;' />
                                    </div>
                                    <div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 12px; background: #fafafa; border-radius: 8px; border: 1px solid #eee;'>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Sıralama</div>
                                            <div style='font-size: 14px; font-weight: 700; color: #111827; margin-top: 2px;'>{$record->sort_order}. Sıra</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Durum</div>
                                            <div style='margin-top: 2px;'>{$statusHtml}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Hedef Link</div>
                                            <div style='margin-top: 2px;'>
                                                <a href='{$linkUrl}' target='_blank' style='font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: underline;'>
                                                    Linke Git ↗
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                ");
                            })
                            ->columnSpan('full'),
                    ]),

                // 2. Düzenle İkonu (Kalem)
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Düzenle'),

                // 3. Silme İkonu (Çöp Kutusu)
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Sil'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            SliderResource\Widgets\SliderStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
