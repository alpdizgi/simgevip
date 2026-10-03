<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Kampanyaya Dahil Ürünler';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Görsel')
                    ->rounded(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('try', true)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('stock_status')
                    ->label('Stok Durumu')
                    ->getStateUsing(function (Product $record) {
                        $stock = $record->total_stock;
                        return $stock > 0 ? "Stokta ({$stock})" : 'Tükendi';
                    })
                    ->colors([
                        'success' => fn ($state) => str_contains($state, 'Stokta'),
                        'danger' => fn ($state) => str_contains($state, 'Tükendi'),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Kampanyaya Ürün Ekle')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Çıkar')
                    ->iconButton()
                    ->tooltip('Kampanyadan Çıkar'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Seçilenleri Kampanyadan Çıkar'),
            ]);
    }
}
