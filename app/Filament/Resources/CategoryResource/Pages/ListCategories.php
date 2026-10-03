<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected int $defaultTableRecordsPerPageSelectOption = -1;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Yeni kategori / menü'),
        ];
    }

    protected function getHeading(): string | Htmlable
    {
        return 'Kategoriler / Menü ağacı';
    }

    protected function getSubheading(): string | Htmlable | null
    {
        return 'Ana kategoriler navbar’da üst menü olarak görünür. Altındakiler açılır menü maddeleridir. Ürün eklerken önce ana, sonra alt kategori seçilir.';
    }

    protected function getTableQuery(): Builder
    {
        return CategoryResource::getEloquentQuery();
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, -1];
    }

    protected function isTablePaginationEnabled(): bool
    {
        // Ağaç bozulmasın diye varsayılan olarak tüm satırlar birlikte listelenir.
        return false;
    }
}
