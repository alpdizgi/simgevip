<?php

namespace App\Filament\Resources\ReservationResource\Widgets;

use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ReservationStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $groups = Reservation::query()->selectRaw('status, is_admin_hold, COUNT(*) as aggregate')
            ->groupBy('status', 'is_admin_hold')->get();
        $confirmedCount = (int) $groups->where('status', 'confirmed')->sum('aggregate');
        $pendingCount = (int) $groups->where('status', 'pending')->sum('aggregate');
        $adminHoldCount = (int) $groups->where('is_admin_hold', true)->whereIn('status', ['pending', 'confirmed'])->sum('aggregate');
        $cancelledCount = (int) $groups->where('status', 'cancelled')->sum('aggregate');

        return [
            Card::make('Onaylanan', $confirmedCount)
                ->description('Onaylanan randevu ve ayırmalar')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Card::make('Bekleyen Talepler', $pendingCount)
                ->description('İşlem bekleyen talepler')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Card::make('Mağaza Ayırması', $adminHoldCount)
                ->description('İptal edilmemiş mağaza ayırmaları')
                ->icon('heroicon-o-tag')
                ->color('primary'),

            Card::make('İptal Edilen', $cancelledCount)
                ->description('İptal edilen rezervasyonlar')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }

}
