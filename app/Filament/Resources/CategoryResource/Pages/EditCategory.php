<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['menu_level'] = ! empty($data['parent_id']) ? 'child' : 'root';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['menu_level'] ?? null) === 'root') {
            $data['parent_id'] = null;
        }

        unset($data['menu_level']);

        return $data;
    }
}
