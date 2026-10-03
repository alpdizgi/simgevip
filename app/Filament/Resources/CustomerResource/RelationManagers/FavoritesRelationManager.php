<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class FavoritesRelationManager extends RelationManager
{
    protected static string $relationship = 'favorites';

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $title = 'Favori Ürünleri';

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Favori ürün bulunamadı';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Müşterinin favorilerine eklediği ürünler burada listelenir.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-heart';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('Görsel'),
                Tables\Columns\TextColumn::make('name')->label('Ürün Adı')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Fiyat')->money('try'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('Ürünü İncele')
                    ->icon('heroicon-o-external-link')
                    ->url(fn (\App\Models\Product $record) => route('product.show', $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }
}
