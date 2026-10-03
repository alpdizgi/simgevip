<?php

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class CampaignStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $today = now()->toDateString();

        // 1. Aktif Kampanyalar: is_active = true, starts_at <= today or null, ends_at >= today or null
        $activeCount = Campaign::query()
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->count();

        // 2. Planlanan / Yaklaşan: is_active = true, starts_at > today
        $upcomingCount = Campaign::query()
            ->where('is_active', true)
            ->whereNotNull('starts_at')
            ->whereDate('starts_at', '>', $today)
            ->count();

        // 3. Süresi Dolan / Pasif: is_active = false VEYA ends_at < today
        $expiredOrInactiveCount = Campaign::query()
            ->where(function ($q) use ($today) {
                $q->where('is_active', false)
                    ->orWhere(function ($sub) use ($today) {
                        $sub->whereNotNull('ends_at')->whereDate('ends_at', '<', $today);
                    });
            })
            ->count();

        // 4. Kampanyalı tekil ürün sayısı
        $campaignProductsCount = (int) DB::table('campaign_product')->distinct('product_id')->count('product_id');

        return [
            Card::make('Aktif Kampanyalar', $activeCount)
                ->description('Sitede şu anda yayında')
                ->descriptionIcon('heroicon-s-check-circle')
                ->chart([2, 3, 4, 3, 5, 4, max(1, $activeCount)])
                ->color('success'),

            Card::make('Planlanan Kampanyalar', $upcomingCount)
                ->description('Başlangıç tarihi bekleyenler')
                ->descriptionIcon('heroicon-s-calendar')
                ->chart([1, 2, 1, 2, 2, 3, max(1, $upcomingCount)])
                ->color('primary'),

            Card::make('Süresi Dolan / Pasif', $expiredOrInactiveCount)
                ->description('Sona ermiş veya durdurulmuş')
                ->descriptionIcon('heroicon-s-clock')
                ->chart([3, 2, 4, 1, 2, 1, max(1, $expiredOrInactiveCount)])
                ->color('danger'),

            Card::make('Kampanyalı Ürün Sayısı', $campaignProductsCount)
                ->description('Kampanyaya dahil toplam ürün')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([5, 8, 12, 10, 15, 18, max(1, $campaignProductsCount)])
                ->color('warning'),
        ];
    }
}
