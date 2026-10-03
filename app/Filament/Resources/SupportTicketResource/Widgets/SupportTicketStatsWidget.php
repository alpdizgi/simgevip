<?php

namespace App\Filament\Resources\SupportTicketResource\Widgets;

use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SupportTicketStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $openCount = SupportTicket::where('status', 'open')->count();
        $waitingCount = SupportTicket::where('status', 'waiting_customer')->count();
        $resolvedCount = SupportTicket::where('status', 'resolved')->count();
        $totalCount = SupportTicket::count();

        return [
            Card::make('Açık / Yeni Talepler', $openCount)
                ->description('İnceleme ve yanıt bekleyenler')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->chart([2, 4, 3, 5, 4, 6, max(1, $openCount)])
                ->color('warning'),

            Card::make('Müşteri Bekleniyor', $waitingCount)
                ->description('Yanıtlandı, müşteriden cevap bekleniyor')
                ->descriptionIcon('heroicon-s-clock')
                ->chart([1, 2, 3, 2, 4, 3, max(1, $waitingCount)])
                ->color('primary'),

            Card::make('Çözülen Talepler', $resolvedCount)
                ->description('Sonuçlandırılan destekler')
                ->descriptionIcon('heroicon-s-check-circle')
                ->chart([3, 5, 7, 6, 8, 10, max(1, $resolvedCount)])
                ->color('success'),

            Card::make('Toplam Talep', $totalCount)
                ->description('Tüm destek kayıtları')
                ->descriptionIcon('heroicon-s-chat-alt-2')
                ->chart([4, 6, 8, 10, 12, 15, max(1, $totalCount)])
                ->color('secondary'),
        ];
    }
}
