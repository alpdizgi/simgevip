<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'color', 'images', 'sizes'];

    protected $casts = [
        'sizes' => 'array',
        'images' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getHasUploadedImageAttribute(): bool
    {
        $real = collect($this->images ?? [])
            ->filter(fn ($path) => filled($path) && ! Product::isDummyOrPlaceholderPath($path));

        return $real->isNotEmpty();
    }

    public function getImageUrlsAttribute(): array
    {
        $real = collect($this->images ?? [])
            ->filter(fn ($path) => filled($path) && ! Product::isDummyOrPlaceholderPath($path))
            ->map(function ($path) {
                if (Str::startsWith($path, ['http://', 'https://'])) {
                    return $path;
                }

                return asset('storage/' . ltrim($path, '/'));
            })
            ->values()
            ->all();

        if (! empty($real)) {
            return $real;
        }

        // Varyanta özel görsel yüklenmemişse ürünün görsellerini kullan
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->product_image_urls;
        }

        if ($this->product) {
            return $this->product->product_image_urls;
        }

        return [asset('images/store/placeholder.svg')];
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? ($this->product?->image_url ?? asset('images/store/placeholder.svg'));
    }
}
