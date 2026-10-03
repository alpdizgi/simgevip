<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderRequests extends ListRecords
{
    protected static string $resource = OrderRequestResource::class;

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn () => null;
    }

    protected function getTableRecordActionUsing(): ?\Closure
    {
        return fn ($record) => OrderRequestResource::canEdit($record) ? 'view_details' : null;
    }

    protected function getTableActionsColumnLabel(): ?string
    {
        return 'İşlemler';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderRequestResource\Widgets\OrderRequestStatsWidget::class,
        ];
    }
}
