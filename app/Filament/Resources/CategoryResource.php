<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Kategoriler / Menü';

    protected static ?string $navigationGroup = 'İşlemler';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategoriler';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['parent'])
            ->withCount(['products', 'children'])
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('tree_help')
                            ->label('')
                            ->content('Örnek: “Üst Giyim” ana kategori. “Sweat”, “Gömlek” ise Üst Giyim’in alt kategorileridir.'),
                        Forms\Components\Radio::make('menu_level')
                            ->label('Ne eklemek istiyorsunuz?')
                            ->options([
                                'root' => 'Ana kategori (navbar üst menü)',
                                'child' => 'Alt kategori (açılır menü maddesi)',
                            ])
                            ->default('root')
                            ->inline()
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === 'root') {
                                    $set('parent_id', null);
                                }
                            }),
                        Forms\Components\Select::make('parent_id')
                            ->label('Hangi ana kategorinin altına eklensin?')
                            ->helperText('Seçtiğiniz ana kategorinin açılır menüsünde görünür.')
                            ->options(function ($livewire) {
                                $ignoreId = $livewire->record->id ?? null;

                                return Category::query()
                                    ->roots()
                                    ->whereNotIn('slug', ['indirimli-urunler', 'en-yeniler', 'yeni-gelenler', 'yeni-sezon'])
                                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required(fn ($get) => $get('menu_level') === 'child')
                            ->visible(fn ($get) => $get('menu_level') === 'child')
                            ->nullable(),
                        Forms\Components\TextInput::make('name')
                            ->label(fn ($get) => $get('menu_level') === 'child'
                                ? 'Alt kategori adı'
                                : 'Ana kategori adı')
                            ->placeholder(fn ($get) => $get('menu_level') === 'child'
                                ? 'Örn: Sweat, Gömlek, Tunik'
                                : 'Örn: Üst Giyim, Alt Giyim')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),
                        Forms\Components\Hidden::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Anasayfa kart görseli')
                            ->helperText('Anasayfada dikey kart olarak görünür. Dikey (portre) görsel yükleyin; kartın ortasında kategori adı bir şerit içinde yer alır. Yalnızca JPG, PNG veya WebP.')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imageCropAspectRatio('9:16')
                            ->disk('public')
                            ->directory('categories')
                            ->maxSize(4096)
                            ->visible(fn ($get) => $get('menu_level') !== 'child'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sıra')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Aynı seviyede küçük sayı önce gelir.'),
                                Forms\Components\Toggle::make('show_in_nav')
                                    ->label('Navbar’da göster')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Açıklama (opsiyonel)')
                            ->rows(3)
                            ->columnSpan('full'),
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
                    ->height(52)
                    ->width(40)
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: 4px;']),
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Seviye')
                    ->getStateUsing(fn (Category $record): string => $record->parent_id ? 'Alt' : 'Ana')
                    ->colors([
                        'primary' => 'Ana',
                        'secondary' => 'Alt',
                    ]),
                Tables\Columns\ViewColumn::make('name')
                    ->label('Menü yapısı')
                    ->view('filament.tables.columns.category-tree')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('show_in_nav')
                    ->label('Navbar')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Alt menü')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, Category $record): string => $record->parent_id
                        ? '—'
                        : (string) $state),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Ürün')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Ana kategoriye göre')
                    ->options(fn () => Category::rootOptions())
                    ->placeholder('Tümü'),
                Tables\Filters\TernaryFilter::make('is_root')
                    ->label('Seviye')
                    ->placeholder('Tümü')
                    ->trueLabel('Sadece ana kategoriler')
                    ->falseLabel('Sadece alt kategoriler')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNull('parent_id'),
                        false: fn (Builder $q) => $q->whereNotNull('parent_id'),
                    ),
                Tables\Filters\TernaryFilter::make('show_in_nav')
                    ->label('Navbar')
                    ->placeholder('Tümü')
                    ->trueLabel('Navbar’da görünenler')
                    ->falseLabel('Navbar’da gizli'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Düzenle'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Seçilenleri sil'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
