<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $setting = \App\Models\Setting::first();
        if ($setting) {
            $this->redirect(SettingResource::getUrl('edit', ['record' => $setting]));
        } else {
            $this->redirect(SettingResource::getUrl('create'));
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
