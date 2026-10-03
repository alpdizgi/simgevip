<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_path',
        'sort_order',
        'show_in_nav',
    ];

    protected $casts = [
        'show_in_nav' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function (Category $category) {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = static::uniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function navChildren()
    {
        return $this->children()->where('show_in_nav', true);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInNav($query)
    {
        return $query->where('show_in_nav', true);
    }

    public function hasChildren(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->isNotEmpty();
        }

        return $this->children()->exists();
    }

    public function getTreeLabelAttribute(): string
    {
        if ($this->parent_id) {
            return '— ' . $this->name;
        }

        return $this->name;
    }

    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' › ' . $this->name;
        }

        return $this->name;
    }

    public function getUrlAttribute(): string
    {
        return route('shop.category', $this->slug);
    }

    public function getEffectiveImagePathAttribute(): ?string
    {
        if (filled($this->image_path)) {
            return $this->image_path;
        }

        if ($this->parent && filled($this->parent->image_path)) {
            return $this->parent->image_path;
        }

        return null;
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->effective_image_path;

        if (blank($path)) {
            return asset('images/store/placeholder.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public static function rootOptions(): array
    {
        return static::query()
            ->roots()
            ->whereNotIn('slug', ['indirimli-urunler', 'en-yeniler', 'yeni-gelenler', 'yeni-sezon'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function childOptions(?int $parentId): array
    {
        if (! $parentId) {
            return [];
        }

        $children = static::query()
            ->where('parent_id', $parentId)
            ->whereNotIn('slug', ['indirimli-urunler', 'en-yeniler', 'yeni-gelenler', 'yeni-sezon'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        if ($children === []) {
            return static::query()
                ->whereKey($parentId)
                ->whereNotIn('slug', ['indirimli-urunler', 'en-yeniler', 'yeni-gelenler', 'yeni-sezon'])
                ->pluck('name', 'id')
                ->all();
        }

        return $children;
    }

    public static function parentHasChildren(?int $parentId): bool
    {
        if (! $parentId) {
            return false;
        }

        return static::query()->where('parent_id', $parentId)->exists();
    }
}
