<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected static string $view = 'filament.resources.customer-resource.pages.view-customer';

    protected static ?string $title = 'Müşteri Detayı';

    protected function getActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Müşterilere Dön')
                ->icon('heroicon-o-arrow-left')
                ->color('secondary')
                ->url(CustomerResource::getUrl('index')),
            Actions\EditAction::make()->label('Bilgileri Düzenle'),
        ];
    }
}
