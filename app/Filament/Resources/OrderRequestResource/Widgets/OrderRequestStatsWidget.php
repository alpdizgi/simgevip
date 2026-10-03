<?php

namespace App\Filament\Resources\OrderRequestResource\Widgets;

use App\Models\CustomerOrderRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class OrderRequestStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getCards(): array
    {
        $counts = CustomerOrderRequest::query()->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')->pluck('aggregate', 'status');
        $count = fn (array $statuses): int => (int) collect($statuses)->sum(fn ($status) => $counts[$status] ?? 0);

        return [
            Card::make('Bekleyen', $count(['pending', 'beklemede']))
                ->description('Onay bekleyen talepler')->icon('heroicon-o-clock')->color('warning'),
            Card::make('Onaylanan', $count(['confirmed', 'approved', 'onaylandi', 'onaylandı']))
                ->description('Onaylanan talepler')->icon('heroicon-o-check-circle')->color('success'),
            Card::make('Gönderilen', $count(['shipped', 'delivered', 'gonderildi', 'gönderildi', 'kargoda']))
                ->description('Kargolanan / teslim edilen')->icon('heroicon-o-truck')->color('primary'),
            Card::make('İptal İsteği', $count(['cancel_requested']))
                ->description('Karar bekleyen iptaller')->icon('heroicon-o-exclamation-circle')->color('warning'),
            Card::make('İptal Edilen', $count(['cancelled', 'iptal', 'iptal_edildi']))
                ->description('İptali tamamlanan talepler')->icon('heroicon-o-x-circle')->color('danger'),
        ];
    }
}
