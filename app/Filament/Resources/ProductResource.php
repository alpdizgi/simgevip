<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeField;
use App\Models\ProductSize;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Ürün Yönetimi';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Ürün';

    protected static ?string $pluralModelLabel = 'Ürünler';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['variants', 'category.parent', 'campaigns']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Ürün')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Genel')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('parent_category_id')
                                            ->label('Ana Kategori')
                                            ->helperText('Örn: Üst Giyim, Alt Giyim, Dış Giyim')
                                            ->options(fn () => Category::rootOptions())
                                            ->searchable()
                                            ->reactive()
                                            ->required()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(function ($set, $state) {
                                                $children = Category::childOptions($state ? (int) $state : null);
                                                if (count($children) === 1) {
                                                    $set('category_id', array_key_first($children));
                                                } else {
                                                    $set('category_id', null);
                                                }
                                            }),
                                        Forms\Components\Select::make('category_id')
                                            ->label(fn ($get) => Category::parentHasChildren($get('parent_category_id') ? (int) $get('parent_category_id') : null)
                                                ? 'Alt Kategori'
                                                : 'Kategori')
                                            ->helperText(fn ($get) => Category::parentHasChildren($get('parent_category_id') ? (int) $get('parent_category_id') : null)
                                                ? 'Ana kategoriye bağlı alt menüden birini seçin.'
                                                : 'Bu ana kategorinin alt menüsü yok; doğrudan bu kategoriye kaydedilir.')
                                            ->options(fn ($get) => Category::childOptions($get('parent_category_id') ? (int) $get('parent_category_id') : null))
                                            ->searchable()
                                            ->required()
                                            ->visible(fn ($get) => filled($get('parent_category_id'))),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Ürün Adı')
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(2)
                                            ->afterStateUpdated(function ($set, $state, $get, $context = null) {
                                                if ($context === 'create' || blank($get('slug'))) {
                                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                                }
                                            }),
                                        Forms\Components\TextInput::make('price')
                                            ->label('Fiyat (₺)')
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(1),
                                    ]),
                                Forms\Components\Hidden::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('description')
                                    ->label('Açıklama')
                                    ->rows(4)
                                    ->columnSpan('full'),
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('En Çok Satan'),
                                        Forms\Components\Toggle::make('is_new_season')
                                            ->label('Yeni Sezon'),
                                        Forms\Components\Toggle::make('has_campaign')
                                            ->label('Özel kampanya')
                                            ->reactive(),
                                        Forms\Components\TextInput::make('campaign_discount_percentage')
                                            ->label('İndirim %')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(100)
                                            ->visible(fn ($get) => $get('has_campaign'))
                                            ->required(fn ($get) => $get('has_campaign')),
                                    ]),
                                Forms\Components\Hidden::make('campaign_nth_item')
                                    ->default(null),
                            ]),

                        Forms\Components\Tabs\Tab::make('Görseller')
                            ->schema([
                                Forms\Components\FileUpload::make('images')
                                    ->label('Ürün Görselleri')
                                    ->helperText('Önerilen: 1080×1920 px (9:16), en fazla 10 görsel. Yalnızca JPG, PNG veya WebP.')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->multiple()
                                    ->maxFiles(10)
                                    ->disk('public')
                                    ->directory('products')
                                    ->enableReordering()
                                    ->columnSpan('full'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Özellikler')
                            ->schema([
                                Forms\Components\ViewField::make('manage_attributes_header')
                                    ->label('Ürün Özellikleri')
                                    ->view('forms.components.attributes-header')
                                    ->dehydrated(false)
                                    ->hintAction(fn (): Forms\Components\Actions\Action => static::getManageAttributesAction()),
                                Forms\Components\Grid::make(3)
                                    ->schema(fn (): array => static::getAttributeInputs()),
                            ]),

                        Forms\Components\Tabs\Tab::make('Renk & Beden')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->label('Renk ve Bedenler')
                                    ->relationship('variants')
                                    ->schema([
                                        Forms\Components\TextInput::make('color')
                                            ->label('Renk Adı')
                                            ->placeholder('Örn: Siyah, Bordo, Bej...')
                                            ->required(),
                                        Forms\Components\FileUpload::make('images')
                                            ->label('Bu renge ait görseller')
                                            ->helperText('Önerilen: 1080×1920 px (9:16), en fazla 5 görsel. Yalnızca JPG, PNG veya WebP.')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->multiple()
                                            ->maxFiles(5)
                                            ->disk('public')
                                            ->directory('products/variants')
                                            ->enableReordering()
                                            ->columnSpan('full'),
                                        Forms\Components\Group::make()
                                            ->schema([
                                                Forms\Components\ViewField::make('manage_sizes_header')
                                                    ->label('Beden Stokları')
                                                    ->view('forms.components.size-stocks-header')
                                                    ->dehydrated(false)
                                                    ->hintAction(fn (): Forms\Components\Actions\Action => static::getManageSizesAction()),
                                                Forms\Components\Grid::make([
                                                    'default' => 4,
                                                    'md' => 8,
                                                ])
                                                    ->schema(fn (): array => static::getSizeStockInputs()),
                                            ])
                                            ->columnSpan('full')
                                            ->extraAttributes([
                                                'class' => 'rounded-xl border border-gray-300 bg-white p-4 dark:border-gray-600 dark:bg-gray-800',
                                            ]),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['color'] ?? null)
                                    ->collapsible()
                                    ->createItemButtonLabel('Yeni Renk Ekle')
                                    ->columnSpan('full'),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            // KART (GRID) GÖRÜNÜMÜ SÜTUNLARI
            Tables\Columns\Layout\Split::make([
                Tables\Columns\ImageColumn::make('cover_image_grid')
                    ->getStateUsing(fn(\App\Models\Product $record) => $record->cover_image)
                    ->label('Görsel')
                    ->defaultImageUrl(asset('images/store/placeholder.svg'))
                    ->width(92)
                    ->height(124)
                    ->grow(false)
                    ->extraAttributes(['class' => 'sv-product-card-image'])
                    ->extraImgAttributes(['class' => 'sv-product-card-image-element']),
                Tables\Columns\ViewColumn::make('product_badges')
                    ->view('filament.resources.product-resource.columns.badges')
                    ->grow(false)
                    ->visible(fn (?\App\Models\Product $record): bool => $record === null || $record->is_featured || $record->is_new_season || filled($record->campaign_badge)),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name_grid')
                        ->label('Ürün adı')
                        ->getStateUsing(fn(\App\Models\Product $record) => $record->name)
                        ->weight('bold')
                        ->searchable(['name'])
                        ->sortable(['name'])
                        ->description(fn (\App\Models\Product $record): string => $record->category?->name ?? 'Kategorisiz')
                        ->extraAttributes(['class' => 'sv-product-card-name']),
                        
                    Tables\Columns\TextColumn::make('sku_grid')
                        ->getStateUsing(fn(\App\Models\Product $record) => $record->sku)
                        ->formatStateUsing(fn ($state) => 'Kod: ' . ($state ?: '—'))
                        ->size('sm')
                        ->color('secondary')
                        ->searchable(['sku'])
                        ->extraAttributes(['class' => 'sv-product-card-sku']),
                        
                    Tables\Columns\TextColumn::make('price_grid')
                        ->label('Fiyat')
                        ->getStateUsing(function (\App\Models\Product $record) {
                            if ($record->discounted_price) {
                                return number_format($record->discounted_price, 2, ',', '.') . ' ₺';
                            }
                            return number_format($record->price, 2, ',', '.') . ' ₺';
                        })
                        ->description(function (\App\Models\Product $record) {
                            if ($record->discounted_price) {
                                return 'Normal: ' . number_format($record->price, 2, ',', '.') . ' ₺';
                            }
                            return null;
                        })
                        ->color(fn (\App\Models\Product $record) => $record->discounted_price ? 'danger' : 'primary')
                        ->sortable(['price'])
                        ->extraAttributes(['class' => 'sv-product-card-price']),
                        
                    Tables\Columns\TextColumn::make('total_stock_grid')
                        ->getStateUsing(fn(\App\Models\Product $record) => $record->total_stock)
                        ->formatStateUsing(fn (\App\Models\Product $record) => 'Stok: ' . $record->total_stock)
                        ->color(fn (\App\Models\Product $record) => $record->total_stock > 0 ? 'success' : 'danger'),
                ])->space(0)->extraAttributes(['class' => 'sv-product-card-details']),
            ])->extraAttributes(['class' => 'sv-product-card-layout']),
        ];

        $columns = array_merge($columns, [
            // KLASİK TABLO GÖRÜNÜMÜ SÜTUNLARI
            Tables\Columns\TextColumn::make('sku')->label('Stok Kodu')->searchable()->sortable(),
            Tables\Columns\ImageColumn::make('cover_image')->label('Görsel'),
            Tables\Columns\TextColumn::make('name')->label('Ad')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category_path')
                ->label('Kategori')
                ->getStateUsing(fn (\App\Models\Product $record): string => $record->category?->full_path ?? '—'),
            Tables\Columns\TextColumn::make('price')
                ->label('Fiyat')
                ->getStateUsing(function (\App\Models\Product $record) {
                    if ($record->discounted_price) {
                        return number_format($record->discounted_price, 2, ',', '.') . ' ₺';
                    }
                    return number_format($record->price, 2, ',', '.') . ' ₺';
                })
                ->description(function (\App\Models\Product $record) {
                    if ($record->discounted_price) {
                        return 'Normal: ' . number_format($record->price, 2, ',', '.') . ' ₺';
                    }
                    return null;
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('total_stock')->label('Toplam Stok')
                ->getStateUsing(fn(\App\Models\Product $record) => $record->total_stock),
            Tables\Columns\TextColumn::make('campaign_label')->label('Kampanya'),
            Tables\Columns\BooleanColumn::make('is_featured')->label('En Çok Satan'),
            Tables\Columns\BooleanColumn::make('is_new_season')->label('Yeni Sezon'),
        ]);

        return $table
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn() => \App\Models\Category::whereNotIn('slug', ['indirimli-urunler', 'en-yeniler', 'yeni-gelenler', 'yeni-sezon'])->pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Öne Çıkan (Çok Satan)')
                    ->placeholder('Tümü')
                    ->trueLabel('Çok Satanlar')
                    ->falseLabel('Normal Ürünler'),

                Tables\Filters\TernaryFilter::make('is_new_season')
                    ->label('Yeni Sezon')
                    ->placeholder('Tümü')
                    ->trueLabel('Yeni Sezon Ürünleri')
                    ->falseLabel('Eski Sezon'),

                Tables\Filters\TernaryFilter::make('is_discounted')
                    ->label('İndirim Durumu')
                    ->placeholder('Tümü')
                    ->trueLabel('Sadece İndirimli Ürünler')
                    ->falseLabel('İndirimsiz Ürünler')
                    ->queries(
                        true: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where(function ($q) {
                            $q->where(function ($inner) {
                                $inner->where(function ($p) {
                                    $p->where('has_campaign', true)
                                      ->orWhere('campaign_discount_percentage', '>', 0);
                                })->where('campaign_discount_percentage', '>', 0);
                            })->orWhereHas('campaigns', function ($c) {
                                $c->active();
                            });
                        }),
                        false: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where(function ($q) {
                            $q->where(function ($inner) {
                                $inner->where('has_campaign', false)
                                      ->orWhereNull('campaign_discount_percentage')
                                      ->orWhere('campaign_discount_percentage', '<=', 0);
                            })->whereDoesntHave('campaigns', function ($c) {
                                $c->active();
                            });
                        }),
                    ),

                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stok Durumu')
                    ->options([
                        'in_stock' => 'Stokta Var',
                        'out_of_stock' => 'Stok Bitenler (Stok Yok)',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (! isset($data['value']) || blank($data['value'])) {
                            return $query;
                        }

                        // Optimized stock query to prevent memory exhaustion
                        $inStockProductIds = \Illuminate\Support\Facades\DB::table('product_variants')
                            ->get(['product_id', 'sizes'])
                            ->filter(function ($variant) {
                                $sizes = json_decode($variant->sizes, true) ?? [];
                                return array_sum((array)$sizes) > 0;
                            })
                            ->pluck('product_id')
                            ->unique();
                            
                        if ($data['value'] === 'in_stock') {
                            return $query->whereIn('id', $inStockProductIds);
                        } else {
                            return $query->whereNotIn('id', $inStockProductIds);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            ProductResource\Widgets\ProductStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, Forms\Components\TextInput>
     */
    public static function getSizeStockInputs(): array
    {
        $sizes = ProductSize::orderedNames();

        if ($sizes === []) {
            $sizes = ['38', '40', '42', '44', '46', '48', '50', '52'];
        }

        return collect($sizes)
            ->map(fn (string $size) => Forms\Components\TextInput::make("sizes.{$size}")
                ->label($size)
                ->numeric()
                ->minValue(0)
                ->default(0))
            ->all();
    }

    public static function getManageSizesAction(): Forms\Components\Actions\Action
    {
        return Forms\Components\Actions\Action::make('manageSizes')
            ->label('Bedenler')
            ->icon('heroicon-o-adjustments')
            ->color('secondary')
            ->view('forms.components.actions.button-action')
            ->modalHeading('Beden Yönetimi')
            ->modalSubheading('Ürün formunda görünecek bedenleri ekleyin, düzenleyin veya silin.')
            ->modalButton('Kaydet')
            ->modalWidth('lg')
            ->mountUsing(function (Forms\ComponentContainer $form): void {
                $form->fill([
                    'sizes' => ProductSize::formRows(),
                ]);
            })
            ->form([
                Forms\Components\Repeater::make('sizes')
                    ->label('Bedenler')
                    ->schema([
                        Forms\Components\Hidden::make('id'),
                        Forms\Components\TextInput::make('name')
                            ->label('Beden')
                            ->placeholder('Örn: 38, S, M, L...')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sıra')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2)
                    ->createItemButtonLabel('Beden Ekle')
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->required()
                    ->minItems(1),
            ])
            ->action(function (array $data, Forms\Components\Actions\Action $action): void {
                $rows = collect($data['sizes'] ?? [])
                    ->map(function (array $row) {
                        $row['name'] = trim((string) ($row['name'] ?? ''));

                        return $row;
                    })
                    ->filter(fn (array $row) => $row['name'] !== '')
                    ->values();

                if ($rows->isEmpty()) {
                    Notification::make()
                        ->title('En az bir beden gerekli')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                if ($rows->pluck('name')->map(fn ($name) => mb_strtolower($name))->unique()->count() !== $rows->count()) {
                    Notification::make()
                        ->title('Beden adları tekrar edemez')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $keptIds = [];
                $existing = ProductSize::query()->get()->keyBy('id');

                foreach ($rows as $index => $row) {
                    $name = $row['name'];
                    $sortOrder = (int) ($row['sort_order'] ?? (($index + 1) * 10));
                    $rawId = $row['id'] ?? null;
                    $id = ($rawId !== null && $rawId !== '') ? (int) $rawId : null;

                    if ($id && $existing->has($id)) {
                        /** @var ProductSize $size */
                        $size = $existing->get($id);
                        $oldName = $size->name;

                        $size->update([
                            'name' => $name,
                            'sort_order' => $sortOrder,
                        ]);

                        if ($oldName !== $name) {
                            ProductSize::renameInVariants($oldName, $name);
                        }

                        $keptIds[] = $size->id;

                        continue;
                    }

                    $size = ProductSize::query()->create([
                        'name' => $name,
                        'sort_order' => $sortOrder,
                    ]);

                    $keptIds[] = $size->id;
                }

                ProductSize::query()
                    ->whereNotIn('id', $keptIds)
                    ->get()
                    ->each(function (ProductSize $size): void {
                        ProductSize::removeFromVariants($size->name);
                        $size->delete();
                    });

                Notification::make()
                    ->title('Bedenler güncellendi')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<int, Forms\Components\TextInput>
     */
    public static function getAttributeInputs(): array
    {
        $fields = ProductAttributeField::orderedFields();

        if ($fields === []) {
            return [
                Forms\Components\Placeholder::make('no_attributes')
                    ->label('')
                    ->content('Henüz özellik alanı yok. Sağ üstteki Özellikler butonu ile ekleyin.')
                    ->columnSpan('full'),
            ];
        }

        return collect($fields)
            ->map(fn (array $field) => Forms\Components\TextInput::make("attribute_values.{$field['key']}")
                ->label($field['label'])
                ->maxLength(255))
            ->all();
    }

    public static function getManageAttributesAction(): Forms\Components\Actions\Action
    {
        return Forms\Components\Actions\Action::make('manageAttributes')
            ->label('Özellikler')
            ->icon('heroicon-o-collection')
            ->color('secondary')
            ->view('forms.components.actions.button-action')
            ->modalHeading('Özellik Alanı Yönetimi')
            ->modalSubheading('Ürün formunda görünecek özellik alanlarını ekleyin, düzenleyin veya silin.')
            ->modalButton('Kaydet')
            ->modalWidth('2xl')
            ->mountUsing(function (Forms\ComponentContainer $form): void {
                $form->fill([
                    'attributes' => ProductAttributeField::formRows(),
                ]);
            })
            ->form([
                Forms\Components\Repeater::make('attributes')
                    ->label('Özellik Alanları')
                    ->schema([
                        Forms\Components\Hidden::make('id'),
                        Forms\Components\TextInput::make('label')
                            ->label('Başlık')
                            ->placeholder('Örn: Ürün Kalıbı')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('key')
                            ->label('Anahtar')
                            ->placeholder('Boş bırakılırsa otomatik oluşur')
                            ->helperText('Sadece küçük harf, rakam ve alt çizgi.')
                            ->maxLength(80)
                            ->regex('/^[a-z][a-z0-9_]*$/'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sıra')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(3)
                    ->createItemButtonLabel('Özellik Ekle')
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->required()
                    ->minItems(1),
            ])
            ->action(function (array $data, Forms\Components\Actions\Action $action): void {
                $rows = collect($data['attributes'] ?? [])
                    ->map(function (array $row) {
                        $row['label'] = trim((string) ($row['label'] ?? ''));
                        $row['key'] = trim((string) ($row['key'] ?? ''));

                        return $row;
                    })
                    ->filter(fn (array $row) => $row['label'] !== '')
                    ->values();

                if ($rows->isEmpty()) {
                    Notification::make()
                        ->title('En az bir özellik alanı gerekli')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $resolvedKeys = [];

                foreach ($rows as $index => $row) {
                    $key = $row['key'];

                    if ($key === '') {
                        $key = ProductAttributeField::makeKeyFromLabel($row['label']);
                        $rows[$index]['key'] = $key;
                    }

                    $resolvedKeys[] = mb_strtolower($key);
                }

                if (count($resolvedKeys) !== count(array_unique($resolvedKeys))) {
                    Notification::make()
                        ->title('Özellik anahtarları tekrar edemez')
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $keptIds = [];
                $existing = ProductAttributeField::query()->get()->keyBy('id');

                foreach ($rows as $index => $row) {
                    $label = $row['label'];
                    $key = $row['key'];
                    $sortOrder = (int) ($row['sort_order'] ?? (($index + 1) * 10));
                    $rawId = $row['id'] ?? null;
                    $id = ($rawId !== null && $rawId !== '') ? (int) $rawId : null;

                    if ($id && $existing->has($id)) {
                        /** @var ProductAttributeField $field */
                        $field = $existing->get($id);
                        $oldKey = $field->key;

                        $field->update([
                            'key' => $key,
                            'label' => $label,
                            'sort_order' => $sortOrder,
                        ]);

                        if ($oldKey !== $key) {
                            ProductAttributeField::renameInProducts($oldKey, $key);
                        }

                        $keptIds[] = $field->id;

                        continue;
                    }

                    $field = ProductAttributeField::query()->create([
                        'key' => $key,
                        'label' => $label,
                        'sort_order' => $sortOrder,
                    ]);

                    $keptIds[] = $field->id;
                }

                ProductAttributeField::query()
                    ->whereNotIn('id', $keptIds)
                    ->get()
                    ->each(function (ProductAttributeField $field): void {
                        ProductAttributeField::removeFromProducts($field->key);
                        $field->delete();
                    });

                Notification::make()
                    ->title('Özellik alanları güncellendi')
                    ->success()
                    ->send();
            });
    }
}
