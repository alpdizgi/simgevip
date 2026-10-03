<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use Filament\Resources\Pages\ListRecords;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\SupportTicketResource\Widgets\SupportTicketStatsWidget::class,
        ];
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return null;
    }

    protected function getTableRecordActionUsing(): ?\Closure
    {
        return fn (): ?string => 'view_details';
    }
}
