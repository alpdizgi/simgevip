<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['category_id'])) {
            $category = Category::query()->find($data['category_id']);

            if ($category) {
                // Alt kategori ise ana = parent; kök ise ana = kendisi
                $data['parent_category_id'] = $category->parent_id ?: $category->id;
            }
        }

        $values = $data['attribute_values'] ?? null;

        if (! is_array($values) || $values === []) {
            $values = [];

            foreach (\App\Models\ProductAttributeField::LEGACY_KEYS as $key) {
                if (filled($data[$key] ?? null)) {
                    $values[$key] = $data[$key];
                }
            }

            $data['attribute_values'] = $values;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['parent_category_id']);

        return $data;
    }
}
