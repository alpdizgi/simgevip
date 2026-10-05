<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static string $view = 'filament.resources.product-resource.pages.create-product';

    protected function beforeValidate(): void
    {
        // The slug is hidden, so resolve collisions before validating the form.
        $base = Str::slug((string) ($this->data['name'] ?? '')) ?: 'urun';
        $slug = $base;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        $this->data['slug'] = $slug;
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Ürün eklenemedi')
            ->body(implode(' ', array_unique($exception->validator->errors()->all())))
            ->danger()
            ->send();
    }
}
