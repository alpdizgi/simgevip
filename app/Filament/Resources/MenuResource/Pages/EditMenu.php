<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeading(): string | Htmlable
    {
        return 'Menü linkini düzenle';
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Sil'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
