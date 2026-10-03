<?php

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSliders extends ListRecords
{
    protected static string $resource = SliderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Yeni Slider Ekle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\SliderResource\Widgets\SliderStatsWidget::class,
        ];
    }

    protected function getHeading(): string | Htmlable
    {
        return 'Slider Yönetimi';
    }

    protected function getSubheading(): string | Htmlable | null
    {
        return 'Anasayfa slider görseli ve tıklanınca açılacak linki buradan yönetin. Önerilen boyut: 1920×900 px.';
    }
}
