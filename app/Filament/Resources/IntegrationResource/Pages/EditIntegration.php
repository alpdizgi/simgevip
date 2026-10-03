<?php

namespace App\Filament\Resources\IntegrationResource\Pages;

use App\Filament\Resources\IntegrationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntegration extends EditRecord
{
    protected static string $resource = IntegrationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Secrets are never re-displayed in the form.
        unset($data['api_key'], $data['api_secret'], $data['config']);

        return $data;
    }
}
