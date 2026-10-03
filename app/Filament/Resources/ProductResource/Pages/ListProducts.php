<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProductResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Actions as TableActions;
use Closure;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public $viewMode = 'grid'; // Varsayılan olarak Grid görünümü

    protected $queryString = [
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function boot()
    {
        // Livewire boot happens before table initialization.
        // We force-read the view from the URL so getTable() builds the correct layout immediately!
        if (request()->has('view')) {
            $this->viewMode = request('view');
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('addCategory')
                ->label('Kategori Ekle')
                ->icon('heroicon-o-collection')
                ->color('secondary')
                ->url(CategoryResource::getUrl('create')),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductResource\Widgets\ProductStatsWidget::class,
        ];
    }

    protected function getTableContentGrid(): ?array
    {
        if ($this->viewMode === 'grid') {
            return [
                'xl' => 2,
                '2xl' => 3,
            ];
        }

        return null;
    }

    protected function getTableColumns(): array
    {
        $columns = parent::getTableColumns();

        // Filament chooses its card renderer whenever a layout column exists,
        // even when that column is hidden. Register only the active layout.
        return array_values(array_filter($columns, function ($column): bool {
            $isCardLayout = $column instanceof LayoutComponent;

            return $this->viewMode === 'grid' ? $isCardLayout : ! $isCardLayout;
        }));
    }

    protected function getTableRecordClassesUsing(): ?Closure
    {
        return fn () => $this->viewMode === 'grid' ? 'sv-product-card-record' : '';
    }

    protected function getTableActions(): array
    {
        if ($this->viewMode === 'grid') {
            return [
                TableActions\EditAction::make()->iconButton()->tooltip('Düzenle'),
                TableActions\DeleteAction::make()->iconButton()->tooltip('Sil'),
            ];
        }

        return parent::getTableActions();
    }
}
