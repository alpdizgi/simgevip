<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /** Yeni ürün etiketi: eklenme tarihinden itibaren kaç gün geçerli */
    public const NEW_FOR_DAYS = 30;

    protected $fillable = [
        'sku',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'images',
        'is_featured',
        'is_new_season',
        'has_campaign',
        'campaign_discount_percentage',
        'campaign_nth_item',
        'pattern',
        'thickness',
        'length',
        'fabric_content',
        'fabric_type',
        'lining',
        'collar_type',
        'sleeve_type',
        'sleeve_length',
        'closure_type',
        'pocket',
        'season',
        'attribute_values',
    ];

    protected $casts = [
        'images' => 'array',
        'attribute_values' => 'array',
        'is_featured' => 'boolean',
        'is_new_season' => 'boolean',
        'has_campaign' => 'boolean',
        'price' => 'decimal:2',
        'campaign_discount_percentage' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = static::generateSku();
            }
        });

        // Ürün kampanyası her zaman yalnızca bu ürüne; nth_item buradan kaldırıldı
        static::saving(function (Product $product) {
            if (! $product->has_campaign) {
                $product->campaign_discount_percentage = null;
                $product->campaign_nth_item = null;
            } else {
                $product->campaign_nth_item = null;
            }

            $values = $product->attribute_values;

            if (! is_array($values)) {
                $values = [];
            }

            foreach (ProductAttributeField::LEGACY_KEYS as $key) {
                if (array_key_exists($key, $values)) {
                    $product->setAttribute($key, $values[$key] !== '' ? $values[$key] : null);
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function getDisplayAttributesAttribute(): array
    {
        return ProductAttributeField::displayPairsFor($this);
    }

    public static function generateSku(): string
    {
        do {
            $sku = 'URN-' . strtoupper(Str::random(6));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class);
    }

    public function getIsNewAttribute(): bool
    {
        if (! $this->created_at) {
            return false;
        }

        return $this->created_at->gte(now()->subDays(static::NEW_FOR_DAYS));
    }

    /**
     * Verilen dosya yolunun seeder veya placeholder (kukla) görsel olup olmadığını belirler.
     */
    public static function isDummyOrPlaceholderPath(?string $path): bool
    {
        if (blank($path)) {
            return true;
        }

        $p = strtolower(trim($path));

        if (str_contains($p, 'placeholder')) {
            return true;
        }

        // Seeder SVG dosyaları: product-img-*.svg, variant-img-*.svg, kadin-*.svg, erkek-*.svg
        if (preg_match('/product-img-\d+\.svg$/i', $p)) {
            return true;
        }

        if (preg_match('/variant-img-\d+\.svg$/i', $p)) {
            return true;
        }

        if (preg_match('/(kadin|erkek)-[\w-]+\.svg$/i', $p)) {
            return true;
        }

        return false;
    }

    /**
     * Ürüne veya varyantlarına kullanıcı tarafından gerçek bir görsel yüklenip yüklenmediği.
     */
    public function getHasUploadedImageAttribute(): bool
    {
        $images = collect($this->images ?? [])
            ->filter(fn ($path) => filled($path) && ! static::isDummyOrPlaceholderPath($path));

        if ($images->isNotEmpty()) {
            return true;
        }

        if ($this->relationLoaded('variants')) {
            foreach ($this->variants as $variant) {
                $vImages = collect($variant->images ?? [])
                    ->filter(fn ($path) => filled($path) && ! static::isDummyOrPlaceholderPath($path));
                if ($vImages->isNotEmpty()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ürünün bağlı olduğu kategorinin (veya üst kategorisinin) kapak görseli yolu.
     */
    public function getCategoryCoverImagePathAttribute(): ?string
    {
        $cat = $this->category;
        if ($cat) {
            if (filled($cat->image_path)) {
                return $cat->image_path;
            }
            if ($cat->parent && filled($cat->parent->image_path)) {
                return $cat->parent->image_path;
            }
        }

        return \Illuminate\Support\Facades\Cache::remember('default_category_cover_image_path', 3600, function () {
            $first = \App\Models\Category::whereNotNull('image_path')->where('image_path', '!=', '')->first();
            return $first ? $first->image_path : null;
        });
    }

    public function getCategoryCoverImageUrlAttribute(): string
    {
        $path = $this->category_cover_image_path;

        if (blank($path)) {
            return asset('images/store/placeholder.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getCoverImageAttribute(): ?string
    {
        // 1. Ürünün kendi yüklenmiş gerçek görseli var mı?
        $realImages = collect($this->images ?? [])
            ->filter(fn ($path) => filled($path) && ! static::isDummyOrPlaceholderPath($path))
            ->values();

        if ($realImages->isNotEmpty()) {
            return $realImages->first();
        }

        // 2. Varyantlarda yüklenmiş gerçek görsel var mı?
        if ($this->relationLoaded('variants')) {
            foreach ($this->variants as $variant) {
                $vImages = collect($variant->images ?? [])
                    ->filter(fn ($path) => filled($path) && ! static::isDummyOrPlaceholderPath($path))
                    ->values();
                if ($vImages->isNotEmpty()) {
                    return $vImages->first();
                }
            }
        }

        // 3. Üründe görsel yoksa: o kategorinin kapak resmi
        return $this->category_cover_image_path;
    }

    public function getTotalStockAttribute(): int
    {
        return $this->variants->sum(fn(ProductVariant $variant) => array_sum($variant->sizes ?? []));
    }

    /**
     * Sepette X. ürüne uygulanan aktif kampanya. Ürün kampanyaya eklenmiş olmalıdır.
     */
    public function activeNthCampaign(): ?Campaign
    {
        $linked = $this->relationLoaded('campaigns')
            ? $this->campaigns
            : $this->campaigns()->active()->get();

        return $linked->first(function (Campaign $campaign) {
            return $campaign->isCurrentlyActive()
                && ($campaign->discount_type ?? 'percentage') === 'nth_item'
                && (int) $campaign->nth_item >= 2
                && (float) $campaign->discount_percentage > 0;
        });
    }

    /**
     * Bu ürüne özel (products tablosu) veya bağlı global kampanyadan direkt indirim yüzdesi.
     * X. ürün kuralı birim fiyata yazılmaz; sepette adet sayısına göre uygulanır.
     */
    public function getDirectDiscountPercentAttribute(): ?float
    {
        // Ürüne özel kampanya = her zaman bu ürünün fiyatına direkt %
        if (($this->has_campaign || $this->campaign_discount_percentage > 0) && $this->campaign_discount_percentage > 0) {
            return (float) $this->campaign_discount_percentage;
        }

        $linked = $this->relationLoaded('campaigns')
            ? $this->campaigns
            : $this->campaigns()->active()->get();

        $campaign = $linked->first(function (Campaign $campaign) {
            return $campaign->isCurrentlyActive()
                && ($campaign->discount_type ?? 'percentage') === 'percentage';
        });

        return $campaign ? (float) $campaign->discount_percentage : null;
    }

    public function getCampaignBadgeAttribute(): ?string
    {
        // Ürüne özel kampanya: her zaman doğrudan bu ürüne % indirim
        if (($this->has_campaign || $this->campaign_discount_percentage > 0) && $this->campaign_discount_percentage > 0) {
            return '%' . static::formatPercentLabel((float) $this->campaign_discount_percentage) . ' indirim';
        }

        $linked = $this->relationLoaded('campaigns')
            ? $this->campaigns
            : $this->campaigns()->active()->get();

        $campaign = $linked->first(fn(Campaign $c) => $c->isCurrentlyActive());

        if (! $campaign) {
            return null;
        }

        $pct = static::formatPercentLabel((float) $campaign->discount_percentage);

        // Genel kampanyadaki "X. ürün" kuralı (sepet mantığı) — ürün özelinden ayrı
        if (($campaign->discount_type ?? 'percentage') === 'nth_item' && $campaign->nth_item) {
            return '%' . $pct . ' · sepetin ' . $campaign->nth_item . '. ürününe';
        }

        return '%' . $pct . ' indirim';
    }

    public static function formatPercentLabel(float $value): string
    {
        if (floor($value) == $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    public function getCampaignLabelAttribute(): ?string
    {
        return $this->campaign_badge;
    }

    public function getDiscountedPriceAttribute(): ?float
    {
        $percent = $this->direct_discount_percent;

        if (! $percent) {
            return null;
        }

        return round((float) $this->price * (1 - ($percent / 100)), 2);
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->cover_image;

        if (blank($path)) {
            return $this->category_cover_image_url;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Ürün detay galerisinde gösterilecek tüm görseller.
     * Ürüne özel görsel yüklenmişse onlar, yoksa kategorinin kapak görseli döner.
     */
    public function getProductImageUrlsAttribute(): array
    {
        $realImages = collect($this->images ?? [])
            ->filter(fn ($path) => filled($path) && ! static::isDummyOrPlaceholderPath($path))
            ->map(function ($path) {
                if (Str::startsWith($path, ['http://', 'https://'])) {
                    return $path;
                }
                return asset('storage/' . ltrim($path, '/'));
            })
            ->values()
            ->all();

        if (! empty($realImages)) {
            return $realImages;
        }

        return [$this->category_cover_image_url];
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, ',', '.') . ' TL';
    }

    public function getFormattedDiscountedPriceAttribute(): ?string
    {
        if ($this->discounted_price === null) {
            return null;
        }

        return number_format($this->discounted_price, 2, ',', '.') . ' TL';
    }
}
