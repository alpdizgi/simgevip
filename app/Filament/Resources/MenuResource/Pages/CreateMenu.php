<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeading(): string | Htmlable
    {
        return 'Yeni menü linki';
    }

    protected function getSubheading(): string | Htmlable | null
    {
        return 'Navbar’da kategori menülerinden sonra görünecek sabit bir link ekleyin.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
