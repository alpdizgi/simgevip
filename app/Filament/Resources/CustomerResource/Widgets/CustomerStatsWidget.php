<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class CustomerStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $totalCustomers = Customer::count();
        $activeMembers = Customer::whereNotNull('password')->where('password', '!=', '')->count();
        $withOrders = Customer::has('orderRequests')->count();
        $withReservations = Customer::has('reservations')->count();

        return [
            Card::make('Toplam Müşteri', $totalCustomers)
                ->description('Kayıtlı tüm müşteri portföyü')
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([4, 6, 5, 8, 7, 11, 13])
                ->color('primary'),

            Card::make('Aktif Üyeler', $activeMembers)
                ->description('Şifreli giriş yapabilen hesaplar')
                ->descriptionIcon('heroicon-s-badge-check')
                ->chart([2, 3, 4, 4, 6, 7, 9])
                ->color('success'),

            Card::make('Siparişi Olanlar', $withOrders)
                ->description('Sipariş talebi vermiş müşteriler')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([1, 2, 3, 3, 5, 6, 8])
                ->color('warning'),

            Card::make('Rezervasyonu Olanlar', $withReservations)
                ->description('Mağaza randevusu / ürün ayırtanlar')
                ->descriptionIcon('heroicon-s-calendar')
                ->chart([1, 1, 2, 3, 2, 4, 5])
                ->color('danger'),
        ];
    }
}
