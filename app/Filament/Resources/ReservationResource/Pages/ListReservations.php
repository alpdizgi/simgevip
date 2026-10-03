<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn () => null;
    }

    protected function getTableRecordActionUsing(): ?\Closure
    {
        return fn ($record) => ReservationResource::canEdit($record) ? 'view_details' : null;
    }

    protected function getTableActionsColumnLabel(): ?string
    {
        return 'İşlemler';
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ReservationResource\Widgets\ReservationStatsWidget::class,
        ];
    }
}
