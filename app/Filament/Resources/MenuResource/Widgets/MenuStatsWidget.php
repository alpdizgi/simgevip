<?php

namespace App\Filament\Resources\MenuResource\Widgets;

use App\Models\Category;
use App\Models\Menu;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class MenuStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $rootCategoriesCount = Category::roots()->count();
        $subCategoriesCount = Category::whereNotNull('parent_id')->count();
        $extraMenusCount = Menu::count();
        $activeInNavCount = Category::roots()->inNav()->count() + Menu::where('is_active', true)->count();

        return [
            Card::make('Ana Menüler', $rootCategoriesCount)
                ->description('Üst menü ana kategorileri')
                ->descriptionIcon('heroicon-s-view-boards')
                ->chart([3, 4, 4, 5, 6, 6, 7])
                ->color('primary'),

            Card::make('Alt Menüler', $subCategoriesCount)
                ->description('Açılır alt menü başlıkları')
                ->descriptionIcon('heroicon-s-view-list')
                ->chart([2, 3, 5, 4, 6, 8, 9])
                ->color('warning'),

            Card::make('Ekstra Sabit Linkler', $extraMenusCount)
                ->description('Özel sayfa / kampanya linkleri')
                ->descriptionIcon('heroicon-s-link')
                ->chart([1, 1, 2, 2, 3, 3, 4])
                ->color('danger'),

            Card::make('Navbar’da Yayında', $activeInNavCount)
                ->description('Sitede şu anda görünen menüler')
                ->descriptionIcon('heroicon-s-check-circle')
                ->chart([4, 5, 5, 6, 7, 8, 8])
                ->color('success'),
        ];
    }
}
