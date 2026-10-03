<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected static string $view = 'filament.resources.category-resource.pages.create-category';

    protected function getHeading(): string | Htmlable
    {
        return 'Kategori / menü ekle';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getViewData(): array
    {
        return [
            'categories' => \App\Models\Category::query()
                ->withCount(['products', 'children'])
                ->orderByRaw('COALESCE(parent_id, id)')
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ];
    }
}
