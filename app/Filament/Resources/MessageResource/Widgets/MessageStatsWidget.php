<?php

namespace App\Filament\Resources\MessageResource\Widgets;

use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class MessageStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $unreadCount = Message::where('is_read', false)->count();
        $readCount = Message::where('is_read', true)->count();
        $todayCount = Message::whereDate('created_at', now()->toDateString())->count();
        $totalCount = Message::count();

        return [
            Card::make('Okunmamış Mesajlar', $unreadCount)
                ->description('İnceleme bekleyen yeni mesajlar')
                ->descriptionIcon('heroicon-s-mail')
                ->chart([3, 5, 2, 4, 6, 3, max(1, $unreadCount)])
                ->color('warning'),

            Card::make('Okunan Mesajlar', $readCount)
                ->description('İncelenmiş mesajlar')
                ->descriptionIcon('heroicon-s-mail-open')
                ->chart([2, 4, 6, 8, 7, 10, max(1, $readCount)])
                ->color('success'),

            Card::make('Bugün Gelenler', $todayCount)
                ->description('Bugün iletilen formlar')
                ->descriptionIcon('heroicon-s-clock')
                ->chart([1, 2, 0, 1, 3, 2, max(1, $todayCount)])
                ->color('primary'),

            Card::make('Toplam Mesaj', $totalCount)
                ->description('Tüm iletişim kayıtları')
                ->descriptionIcon('heroicon-s-inbox')
                ->chart([5, 8, 12, 10, 15, 18, max(1, $totalCount)])
                ->color('secondary'),
        ];
    }
}
