<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class RecentProductsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }
    
    protected static ?string $heading = 'Son Eklenen Ürünler';

    protected function getTableQuery(): Builder
    {
        return Product::query()->latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('cover_image')->label('Görsel'),
            Tables\Columns\TextColumn::make('name')->label('Ürün Adı'),
            Tables\Columns\TextColumn::make('price')->label('Fiyat')->money('try'),
            Tables\Columns\TextColumn::make('created_at')->label('Eklenme')->date('d.m.Y'),
        ];
    }
    
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
