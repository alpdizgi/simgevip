<?php

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Sil'),
        ];
    }

    protected function getHeading(): string | Htmlable
    {
        return 'Slider düzenle';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['title'] ?? null)) {
            $data['title'] = filled($data['link'] ?? null)
                ? 'Slider: ' . $data['link']
                : 'Slider';
        }

        return $data;
    }
}
