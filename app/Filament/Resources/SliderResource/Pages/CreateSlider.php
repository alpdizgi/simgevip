<?php

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateSlider extends CreateRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeading(): string | Htmlable
    {
        return 'Yeni slider ekle';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['title'] ?? null)) {
            $data['title'] = filled($data['link'] ?? null)
                ? 'Slider: ' . $data['link']
                : 'Slider';
        }

        return $data;
    }
}
