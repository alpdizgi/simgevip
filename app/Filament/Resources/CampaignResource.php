<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Kampanyalar';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Kampanya';

    protected static ?string $pluralModelLabel = 'Kampanyalar';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    // Sol Kolon: 2 Birim Genişlik
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Kampanya Detayları')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Kampanya Başlığı')
                                        ->placeholder('Örn: Yaz Sezonu İndirimi')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($set, $state, $context) {
                                            if ($context === 'create') {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug (Bağlantı)')
                                        ->placeholder('yaz-sezonu-indirimi')
                                        ->required()
                                        ->unique(ignoreRecord: true),
                                ]),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('discount_type')
                                        ->label('Kampanya Tipi')
                                        ->options([
                                            'percentage' => 'Ürüne Direkt Yüzde İndirim',
                                            'nth_item' => 'X. Üründe Yüzde İndirim (2. veya 3. ürün)',
                                        ])
                                        ->default('percentage')
                                        ->reactive()
                                        ->required(),

                                    Forms\Components\TextInput::make('discount_percentage')
                                        ->label('İndirim Oranı (%)')
                                        ->numeric()
                                        ->suffix('%')
                                        ->required(),
                                ]),

                                Forms\Components\TextInput::make('nth_item')
                                    ->label('Kaçıncı Üründe İndirim Uygulansın?')
                                    ->placeholder('2, 3 vb.')
                                    ->numeric()
                                    ->minValue(2)
                                    ->visible(fn ($get) => $get('discount_type') === 'nth_item')
                                    ->required(fn ($get) => $get('discount_type') === 'nth_item'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Açıklama')
                                    ->placeholder('Kampanya koşulları ve detay açıklaması...')
                                    ->rows(3),
                            ]),

                        Forms\Components\Section::make('Kampanyaya Dahil Ürünler (Hızlı Seçim)')
                            ->schema([
                                Forms\Components\Select::make('products')
                                    ->label('Dahil Edilen Ürünler')
                                    ->helperText('Buradan topluca ürün seçebilir veya sayfanın altındaki "Kampanyaya Dahil Ürünler" sekmesinden liste olarak yönetebilirsiniz.')
                                    ->relationship('products', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan('full'),
                            ]),
                    ])->columnSpan(2),

                    // Sağ Kolon: 1 Birim Genişlik
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Yayın ve Süreç')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Kampanya Aktif')
                                    ->default(true)
                                    ->helperText('Pasif duruma getirilirse sitede görüntülenmez.'),

                                Forms\Components\DatePicker::make('starts_at')
                                    ->label('Başlangıç Tarihi')
                                    ->displayFormat('d.m.Y'),

                                Forms\Components\DatePicker::make('ends_at')
                                    ->label('Bitiş Tarihi')
                                    ->displayFormat('d.m.Y'),

                                Forms\Components\Placeholder::make('status_summary')
                                    ->label('Anlık Durum')
                                    ->content(function (?Campaign $record) {
                                        if (! $record) {
                                            return '✨ Yeni Kampanya Kaydı';
                                        }
                                        if (! $record->is_active) {
                                            return '⚪ Pasif (Devre Dışı)';
                                        }
                                        if ($record->starts_at && $record->starts_at->isFuture()) {
                                            return '🔵 Planlandı (' . $record->starts_at->format('d.m.Y') . ' tarihinde başlayacak)';
                                        }
                                        if ($record->ends_at && $record->ends_at->copy()->endOfDay()->isPast()) {
                                            return '🔴 Süresi Doldu (' . $record->ends_at->format('d.m.Y') . ')';
                                        }
                                        return '🟢 Sitede Yayında ve Aktif';
                                    }),
                            ]),

                        Forms\Components\Section::make('Kampanya Görseli / Afiş')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Görsel')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('campaigns')
                                    ->helperText('Kampanya banner veya tanıtım görseli yükleyin.'),
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
                    ->label('Görsel')
                    ->disk('public')
                    ->rounded(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Kampanya Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('discount_type')
                    ->label('Tip')
                    ->formatStateUsing(fn (string $state) => $state === 'nth_item' ? 'X. Üründe İndirim' : 'Direkt İndirim')
                    ->colors([
                        'primary' => 'nth_item',
                        'success' => 'percentage',
                    ]),

                Tables\Columns\TextColumn::make('discount_display')
                    ->label('İndirim Kuralı')
                    ->getStateUsing(function (Campaign $record) {
                        $pct = (int) $record->discount_percentage;
                        if ($record->discount_type === 'nth_item') {
                            return ($record->nth_item ?? 2) . ". Ürüne %{$pct}";
                        }
                        return "%{$pct} İndirim";
                    }),

                Tables\Columns\BadgeColumn::make('products_count')
                    ->label('Ürün Sayısı')
                    ->counts('products')
                    ->color('secondary')
                    ->suffix(' Ürün'),

                Tables\Columns\TextColumn::make('dates')
                    ->label('Geçerlilik')
                    ->getStateUsing(function (Campaign $record) {
                        $s = $record->starts_at ? $record->starts_at->format('d.m.Y') : 'Başlangıç Yok';
                        $e = $record->ends_at ? $record->ends_at->format('d.m.Y') : 'Süresiz';
                        return "{$s} — {$e}";
                    }),

                Tables\Columns\BadgeColumn::make('live_status')
                    ->label('Durum')
                    ->getStateUsing(function (Campaign $record) {
                        if (! $record->is_active) {
                            return 'Pasif';
                        }
                        if ($record->starts_at && $record->starts_at->isFuture()) {
                            return 'Planlandı';
                        }
                        if ($record->ends_at && $record->ends_at->copy()->endOfDay()->isPast()) {
                            return 'Süresi Bitti';
                        }
                        return 'Aktif';
                    })
                    ->colors([
                        'success' => 'Aktif',
                        'primary' => 'Planlandı',
                        'danger' => 'Süresi Bitti',
                        'secondary' => 'Pasif',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum Filtresi')
                    ->options([
                        'active' => 'Aktif (Yayında)',
                        'upcoming' => 'Planlanan (Gelecek)',
                        'expired' => 'Süresi Bitenler',
                        'inactive' => 'Pasif Edilenler',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $val = $data['value'] ?? null;
                        $today = now()->toDateString();

                        if ($val === 'active') {
                            $query->where('is_active', true)
                                ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
                                ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
                        } elseif ($val === 'upcoming') {
                            $query->where('is_active', true)->whereNotNull('starts_at')->whereDate('starts_at', '>', $today);
                        } elseif ($val === 'expired') {
                            $query->whereNotNull('ends_at')->whereDate('ends_at', '<', $today);
                        } elseif ($val === 'inactive') {
                            $query->where('is_active', false);
                        }
                    }),

                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Kampanya Tipi')
                    ->options([
                        'percentage' => 'Direkt Yüzde İndirim',
                        'nth_item' => 'X. Üründe Yüzde İndirim',
                    ]),
            ])
            ->actions([
                // 1. Detay Göz İkonu (Modal)
                Tables\Actions\Action::make('view_details')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Kampanya Detayı')
                    ->modalHeading(fn (Campaign $record) => "Kampanya Detayı: {$record->title}")
                    ->modalSubheading(fn (Campaign $record) => "Slug: {$record->slug} · Oluşturulma: " . ($record->created_at ? $record->created_at->format('d.m.Y H:i') : '—'))
                    ->modalWidth('3xl')
                    ->modalActions([
                        Tables\Actions\Modal\Actions\Action::make('edit')
                            ->label('Kampanyayı Düzenle')
                            ->url(fn (Campaign $record) => static::getUrl('edit', ['record' => $record]))
                            ->button()
                            ->color('primary'),
                        Tables\Actions\Modal\Actions\Action::make('close')
                            ->label('Kapat')
                            ->cancel(),
                    ])
                    ->form([
                        Forms\Components\Placeholder::make('campaign_summary_card')
                            ->label('')
                            ->content(function (?Campaign $record) {
                                if (! $record) return '';

                                $imgHtml = '';
                                if ($record->image_path) {
                                    $imgUrl = asset('storage/' . ltrim($record->image_path, '/'));
                                    $imgHtml = "
                                        <div style='margin-bottom: 12px; border-radius: 8px; overflow: hidden; max-height: 180px; border: 1px solid #e5e7eb;'>
                                            <img src='{$imgUrl}' alt='{$record->title}' style='width: 100%; height: 180px; object-fit: cover;' />
                                        </div>
                                    ";
                                }

                                $typeLabel = $record->discount_type === 'nth_item' ? 'X. Üründe Yüzde İndirim' : 'Direkt Yüzde İndirim';
                                $ruleLabel = $record->discount_type === 'nth_item'
                                    ? (($record->nth_item ?? 2) . ". Ürüne %" . (int) $record->discount_percentage)
                                    : ("%" . (int) $record->discount_percentage . " İndirim");

                                $startStr = $record->starts_at ? $record->starts_at->format('d.m.Y') : 'Belirtilmedi';
                                $endStr = $record->ends_at ? $record->ends_at->format('d.m.Y') : 'Süresiz';

                                $statusBadge = '';
                                if (! $record->is_active) {
                                    $statusBadge = "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#f3f4f6; color:#4b5563; border-radius:6px;'>⚪ Pasif</span>";
                                } elseif ($record->starts_at && $record->starts_at->isFuture()) {
                                    $statusBadge = "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#eff6ff; color:#1d4ed8; border-radius:6px;'>🔵 Planlandı</span>";
                                } elseif ($record->ends_at && $record->ends_at->copy()->endOfDay()->isPast()) {
                                    $statusBadge = "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#fef2f2; color:#b91c1c; border-radius:6px;'>🔴 Süresi Bitti</span>";
                                } else {
                                    $statusBadge = "<span style='display:inline-block; padding:3px 8px; font-size:12px; font-weight:600; background:#f0fdf4; color:#15803d; border-radius:6px;'>🟢 Sitede Aktif</span>";
                                }

                                $descHtml = '';
                                if (filled($record->description)) {
                                    $desc = nl2br(e($record->description));
                                    $descHtml = "
                                        <div style='margin-top: 12px; padding: 10px 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #f3f4f6; font-size: 13px; color: #374151;'>
                                            <strong>Açıklama:</strong> {$desc}
                                        </div>
                                    ";
                                }

                                return new HtmlString("
                                    {$imgHtml}
                                    <div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 12px; background: #fafafa; border-radius: 8px; border: 1px solid #eee;'>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Kampanya Tipi</div>
                                            <div style='font-size: 13px; font-weight: 600; color: #111827; margin-top: 2px;'>{$typeLabel}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>İndirim Kuralı</div>
                                            <div style='font-size: 14px; font-weight: 700; color: #059669; margin-top: 2px;'>{$ruleLabel}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Yayın Durumu</div>
                                            <div style='margin-top: 2px;'>{$statusBadge}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Başlangıç Tarihi</div>
                                            <div style='font-size: 13px; font-weight: 500; color: #374151; margin-top: 2px;'>{$startStr}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Bitiş Tarihi</div>
                                            <div style='font-size: 13px; font-weight: 500; color: #374151; margin-top: 2px;'>{$endStr}</div>
                                        </div>
                                        <div>
                                            <div style='font-size: 11px; color: #6b7280; font-weight: 500; text-transform: uppercase;'>Dahil Ürün Sayısı</div>
                                            <div style='font-size: 13px; font-weight: 700; color: #4338ca; margin-top: 2px;'>{$record->products()->count()} Ürün</div>
                                        </div>
                                    </div>
                                    {$descHtml}
                                ");
                            })
                            ->columnSpan('full'),

                        Forms\Components\Placeholder::make('campaign_products_preview')
                            ->label('Kampanyaya Dahil Ürünler')
                            ->content(function (?Campaign $record) {
                                if (! $record) return '—';

                                $products = $record->products()->with(['category'])->take(15)->get();

                                if ($products->isEmpty()) {
                                    return new HtmlString("<div style='color: #6b7280; font-size: 13px; padding: 8px 0;'>Bu kampanyaya henüz ürün bağlanmamış.</div>");
                                }

                                $totalCount = $record->products()->count();
                                $extraNote = $totalCount > 15 ? "<div style='font-size:12px; color:#6b7280; margin-top:8px;'>... ve " . ($totalCount - 15) . " ürün daha. Tümünü görmek için kampanyayı düzenleyin.</div>" : "";

                                $rows = '';
                                foreach ($products as $p) {
                                    $imgUrl = $p->category_cover_image_url;
                                    if ($p->cover_image) {
                                        $imgUrl = asset('storage/' . ltrim($p->cover_image, '/'));
                                    }
                                    $pName = e($p->name);
                                    $catName = e($p->category?->name ?? '—');
                                    $price = number_format((float) $p->price, 2, ',', '.') . ' TL';

                                    $rows .= "
                                        <tr style='border-bottom: 1px solid #f3f4f6;'>
                                            <td style='padding: 8px 10px; width: 44px;'>
                                                <img src='{$imgUrl}' alt='{$pName}' style='width: 36px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;' />
                                            </td>
                                            <td style='padding: 8px 10px; font-size: 13px; font-weight: 500; color: #111827;'>{$pName}</td>
                                            <td style='padding: 8px 10px; font-size: 12px; color: #6b7280;'>{$catName}</td>
                                            <td style='padding: 8px 10px; font-size: 13px; font-weight: 600; color: #047857; text-align: right;'>{$price}</td>
                                        </tr>
                                    ";
                                }

                                return new HtmlString("
                                    <div style='max-height: 240px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px;'>
                                        <table style='width: 100%; border-collapse: collapse; text-align: left;'>
                                            <thead style='background: #f9fafb; font-size: 11px; text-transform: uppercase; color: #6b7280; position: sticky; top: 0;'>
                                                <tr>
                                                    <th style='padding: 8px 10px;'>Görsel</th>
                                                    <th style='padding: 8px 10px;'>Ürün</th>
                                                    <th style='padding: 8px 10px;'>Kategori</th>
                                                    <th style='padding: 8px 10px; text-align: right;'>Fiyat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                        </table>
                                    </div>
                                    {$extraNote}
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
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CampaignResource\Widgets\CampaignStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
