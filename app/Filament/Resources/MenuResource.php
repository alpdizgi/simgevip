<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-menu';

    protected static ?string $navigationLabel = 'Menü Yönetimi';

    protected static ?string $navigationGroup = 'Site Ayarları';

    protected static ?string $modelLabel = 'Menü Bağlantısı';

    protected static ?string $pluralModelLabel = 'Menüler';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('sort_order')->orderBy('id');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Bağlantı Bilgileri')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Menü Başlığı')
                                    ->placeholder('Örn: Kampanyalar, Blog, Beden Rehberi')
                                    ->required()
                                    ->maxLength(120),

                                Forms\Components\TextInput::make('url')
                                    ->label('Hedef Bağlantı (URL)')
                                    ->placeholder('Örn: /kampanyalar veya https://...')
                                    ->helperText('Site içi yol (/hakkimizda) veya tam harici adres yazabilirsiniz.')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])->columnSpan(2),

                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Yayın ve Sıralama')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Navbar’da Göster (Aktif)')
                                    ->default(true)
                                    ->helperText('Açık olduğunda üst menüde görüntülenir.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sıralama Numarası')
                                    ->numeric()
                                    ->default(fn () => (int) Menu::query()->max('sort_order') + 1)
                                    ->helperText('Küçük numara önce gelir. Menü listesinde sürükleyerek de sıralayabilirsiniz.'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Menü Başlığı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('url')
                    ->label('Hedef URL')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Son Güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Yayın Durumu')
                    ->placeholder('Tümü')
                    ->trueLabel('Aktif Olanlar')
                    ->falseLabel('Pasif Olanlar'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Düzenle'),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Sil'),
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
            MenuResource\Widgets\MenuStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
