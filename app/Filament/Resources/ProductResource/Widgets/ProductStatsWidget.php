<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ProductStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $discountedCount = Product::where(function ($q) {
            $q->where(function ($inner) {
                $inner->where(function ($p) {
                    $p->where('has_campaign', true)
                      ->orWhere('campaign_discount_percentage', '>', 0);
                })->where('campaign_discount_percentage', '>', 0);
            })->orWhereHas('campaigns', function ($c) {
                $c->active();
            });
        })->count();

        return [
            Card::make('Toplam Ürün', Product::count())
                ->description('Katalogdaki ürünler')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Card::make('Çok Satan (Öne Çıkan)', Product::where('is_featured', true)->count())
                ->description('Vitrin ürünleri')
                ->descriptionIcon('heroicon-s-star')
                ->chart([3, 5, 4, 8, 12, 10, 14])
                ->color('warning'),

            Card::make('Yeni Sezon', Product::where('is_new_season', true)->count())
                ->description('Yeni sezon ürünleri')
                ->descriptionIcon('heroicon-s-sparkles')
                ->chart([1, 4, 2, 7, 5, 9, 8])
                ->color('success'),

            Card::make('İndirimli Ürünler', $discountedCount)
                ->description('Kampanyalı ürünler')
                ->descriptionIcon('heroicon-s-receipt-tax')
                ->chart([5, 3, 8, 4, 10, 6, 12])
                ->color('danger'),
        ];
    }
}
