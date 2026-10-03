<?php

namespace App\Filament\Resources\SliderResource\Widgets;

use App\Models\Slider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SliderStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getCards(): array
    {
        $activeCount = Slider::where('is_active', true)->count();
        $inactiveCount = Slider::where('is_active', false)->count();
        $totalCount = Slider::count();

        return [
            Card::make('Aktif Sliderlar', $activeCount)
                ->description('Anasayfada şu anda yayında')
                ->descriptionIcon('heroicon-s-photograph')
                ->chart([2, 3, 4, 3, 5, 4, max(1, $activeCount)])
                ->color('success'),

            Card::make('Pasif Sliderlar', $inactiveCount)
                ->description('Yayında olmayan sliderlar')
                ->descriptionIcon('heroicon-s-eye-off')
                ->chart([1, 2, 1, 0, 1, 1, max(1, $inactiveCount)])
                ->color('danger'),

            Card::make('Toplam Slider', $totalCount)
                ->description('Kayıtlı tüm görseller')
                ->descriptionIcon('heroicon-s-collection')
                ->chart([3, 4, 5, 5, 6, 7, max(1, $totalCount)])
                ->color('primary'),
        ];
    }
}
