<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Customer;
use App\Models\CustomerOrderRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DashboardStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }

    // Kartların 3 üstte 3 altta düzenli durması için resmi desteklenen 3 sütun.
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getCards(): array
    {
        return [
            Card::make('Aktif Müşteri', Customer::count())
                ->description('Kayıtlı kullanıcılar')
                ->descriptionIcon('heroicon-s-users')
                ->chart([3, 5, 4, 8, 12, 10, 14])
                ->color('primary'),

            Card::make('Aktif Ürün', Product::count())
                ->description('Yayındaki ürünler')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([10, 12, 15, 14, 18, 20, 22])
                ->color('success'),

            Card::make('Bekleyen Sipariş', CustomerOrderRequest::where('status', 'pending')->count())
                ->description('Onay bekleyen siparişler')
                ->descriptionIcon('heroicon-s-shopping-cart')
                ->chart([2, 3, 1, 4, 2, 5, 3])
                ->color('warning'),

            Card::make('Bekleyen Talep', Reservation::where('status', 'pending')->count())
                ->description('Rezervasyonlar')
                ->descriptionIcon('heroicon-s-clock')
                ->chart([1, 0, 2, 1, 3, 1, 2])
                ->color('warning'),

            Card::make('Bekleyen Destek', Message::where('is_read', false)->where('subject', 'like', '%destek%')->count())
                ->description('Okunmamış destek talepleri')
                ->descriptionIcon('heroicon-s-support')
                ->chart([0, 1, 0, 1, 0, 2, 1])
                ->color('danger'),

            Card::make('Bekleyen Mesaj', Message::where('is_read', false)->where(function($q) {
                    $q->whereNull('subject')->orWhere('subject', 'not like', '%destek%');
                })->count())
                ->description('Diğer okunmamış mesajlar')
                ->descriptionIcon('heroicon-s-mail')
                ->chart([0, 1, 0, 2, 1, 3, 1])
                ->color('danger'),
        ];
    }
}
