<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class CartService
{
    public const SESSION_KEY = 'store_cart';

    public function items(): array
    {
        $raw = $this->rawItems();
        $productIds = collect($raw)->pluck('product_id')->filter()->unique()->values();
        $products = $productIds->isEmpty()
            ? collect()
            : Product::query()->with('campaigns')->whereIn('id', $productIds)->get()->keyBy('id');

        $lines = [];

        foreach ($raw as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $product = $products->get($item['product_id'] ?? null);
            if (! $product) {
                continue;
            }

            $item['name'] = $product->name;
            $item['slug'] = $product->slug;
            $item['sku'] = $product->sku;
            $item['original_price'] = (float) $product->price;
            $item['price'] = $product->discounted_price !== null
                ? (float) $product->discounted_price
                : (float) $product->price;
            $item['campaign_badge'] = $product->campaign_badge;
            $item['discount_percent'] = $product->direct_discount_percent;
            $item['key'] = $item['key'] ?? $key;
            $item['qty'] = min(20, max(1, (int) ($item['qty'] ?? 1)));
            $lines[$key] = $item;
        }

        $lines = $this->applyNthItemDiscounts($lines, $products);

        $items = [];
        foreach ($lines as $key => $item) {
            $items[$key] = $this->normalizeItem($item);
        }

        return $items;
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum('qty');
    }

    public function originalTotal(): float
    {
        return (float) collect($this->items())->sum(fn ($item) => (float) ($item['line_original_total'] ?? 0));
    }

    public function discountTotal(): float
    {
        return max(0, round($this->originalTotal() - $this->subtotal(), 2));
    }

    public function subtotal(): float
    {
        return (float) collect($this->items())->sum(fn ($item) => (float) ($item['line_total'] ?? 0));
    }

    public function normalizeItem(array $item): array
    {
        $price = (float) ($item['price'] ?? 0);
        $originalPrice = isset($item['original_price']) ? (float) $item['original_price'] : $price;
        if ($originalPrice < $price) {
            $originalPrice = $price;
        }

        $qty = max(1, (int) ($item['qty'] ?? 1));
        $nthQty = min($qty, max(0, (int) ($item['nth_discount_qty'] ?? 0)));
        $nthPercent = max(0, min(100, (float) ($item['nth_discount_percent'] ?? 0)));
        $nthUnit = round($price * (1 - ($nthPercent / 100)), 2);
        $lineTotal = round((($qty - $nthQty) * $price) + ($nthQty * $nthUnit), 2);
        $lineOriginalTotal = round($originalPrice * $qty, 2);
        $discountAmount = max(0, round($lineOriginalTotal - $lineTotal, 2));
        $hasDiscount = $discountAmount > 0.009;
        $unitDiscount = max(0, round($originalPrice - $price, 2));
        $discountPercent = $item['discount_percent'] ?? ($unitDiscount > 0 && $originalPrice > 0 ? round(($unitDiscount / $originalPrice) * 100) : null);

        $item['original_price'] = $originalPrice;
        $item['price'] = $price;
        $item['has_discount'] = $hasDiscount;
        $item['discount_amount'] = $discountAmount;
        $item['discount_percent'] = $discountPercent;
        $item['original_price_formatted'] = number_format($originalPrice, 2, ',', '.') . ' TL';
        $item['price_formatted'] = number_format($price, 2, ',', '.') . ' TL';
        $item['line_total'] = $lineTotal;
        $item['line_total_formatted'] = number_format($lineTotal, 2, ',', '.') . ' TL';
        $item['line_original_total'] = $lineOriginalTotal;
        $item['line_original_total_formatted'] = number_format($lineOriginalTotal, 2, ',', '.') . ' TL';
        $item['nth_discount_qty'] = $nthQty;
        $item['nth_discount_percent'] = $nthQty > 0 ? $nthPercent : ($item['nth_discount_percent'] ?? null);

        return $item;
    }

    /**
     * Kampanyadaki ürünlerin sepet sırasına göre 2., 4., 6. … adedine yüzde indirim yazar.
     *
     * @param  array<string, array>  $lines
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return array<string, array>
     */
    private function applyNthItemDiscounts(array $lines, $products): array
    {
        foreach ($lines as $key => $line) {
            $lines[$key]['nth_discount_qty'] = 0;
            $lines[$key]['nth_discount_percent'] = null;
            $lines[$key]['nth_discount_note'] = null;
            $lines[$key]['nth_pending_note'] = null;
        }

        $groups = [];

        foreach ($lines as $key => $line) {
            $product = $products->get($line['product_id'] ?? null);
            $campaign = $product?->activeNthCampaign();
            if (! $campaign instanceof Campaign) {
                continue;
            }

            $id = $campaign->id;
            if (! isset($groups[$id])) {
                $groups[$id] = [
                    'nth' => (int) $campaign->nth_item,
                    'percent' => (float) $campaign->discount_percentage,
                    'units' => [],
                ];
            }

            for ($i = 0; $i < (int) $line['qty']; $i++) {
                $groups[$id]['units'][] = $key;
            }
        }

        foreach ($groups as $group) {
            $nth = max(2, (int) $group['nth']);
            $percent = (float) $group['percent'];
            $counts = [];

            foreach ($group['units'] as $index => $key) {
                if ((($index + 1) % $nth) === 0) {
                    $counts[$key] = ($counts[$key] ?? 0) + 1;
                }
            }

            $label = Product::formatPercentLabel($percent);

            foreach ($counts as $key => $discountedQty) {
                $lines[$key]['nth_discount_qty'] = $discountedQty;
                $lines[$key]['nth_discount_percent'] = $percent;
                $lines[$key]['nth_discount_note'] = $discountedQty . ' adede %' . $label . ' (' . $nth . '. ürün)';
            }

            $totalUnits = count($group['units']);
            if ($totalUnits > 0 && $totalUnits < $nth) {
                $lastKey = $group['units'][$totalUnits - 1];
                $missing = $nth - $totalUnits;
                $lines[$lastKey]['nth_pending_note'] = $missing . ' ürün daha ekleyin; ' . $nth . '. ürüne %' . $label . ' uygulanır';
            }
        }

        return $lines;
    }

    public function add(Product $product, ?int $variantId = null, ?string $size = null, int $qty = 1): array
    {
        $qty = max(1, $qty);
        $variant = null;
        $color = null;

        if ($variantId) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey($variantId)
                ->first();

            if (! $variant) {
                throw new InvalidArgumentException('Renk seçimi geçersiz.');
            }

            $color = $variant->color;
            $sizes = $variant->sizes ?? [];

            if ($size !== null && $size !== '' && (! array_key_exists($size, $sizes) || (int) $sizes[$size] <= 0)) {
                throw new InvalidArgumentException('Seçilen beden stokta yok.');
            }
        } elseif ($product->variants()->exists()) {
            throw new InvalidArgumentException('Lütfen renk ve beden seçin.');
        }

        $key = $this->lineKey($product->id, $variantId, $size);
        $items = $this->rawItems();
        $originalPrice = (float) $product->price;
        $unitPrice = $product->discounted_price !== null ? (float) $product->discounted_price : $originalPrice;
        $discountAmount = max(0, round($originalPrice - $unitPrice, 2));
        $hasDiscount = $discountAmount > 0;
        $discountPercent = $product->direct_discount_percent ?: ($hasDiscount && $originalPrice > 0 ? round(($discountAmount / $originalPrice) * 100) : null);

        if (isset($items[$key])) {
            $items[$key]['qty'] = min(20, (int) $items[$key]['qty'] + $qty);
        } else {
            $items[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'slug' => $product->slug,
                'name' => $product->name,
                'sku' => $product->sku,
                'color' => $color,
                'size' => $size,
                'qty' => min(20, $qty),
                'original_price' => $originalPrice,
                'price' => $unitPrice,
                'has_discount' => $hasDiscount,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'campaign_badge' => $product->campaign_badge,
                'image' => $variant?->cover_image_url ?: $product->image_url,
                'url' => route('product.show', $product->slug),
            ];
        }

        $this->putRawItems($items);

        $priced = $this->items();

        return $priced[$key] ?? $this->normalizeItem($items[$key]);
    }

    public function updateQty(string $key, int $qty): void
    {
        $items = $this->items();

        if (! isset($items[$key])) {
            return;
        }

        if ($qty <= 0) {
            unset($items[$key]);
        } else {
            $items[$key]['qty'] = $qty;
        }

        $this->putRawItems($items);
    }

    public function remove(string $key): void
    {
        $items = $this->items();
        unset($items[$key]);
        $this->putRawItems($items);
    }

    public function clear(): void
    {
        $this->putRawItems([]);
    }

    public function mergeSessionForCustomer(): void
    {
        $customer = Auth::guard('customer')->user();
        if (! $customer) {
            return;
        }

        $items = $customer->cart_items ?: [];
        foreach (Session::get(self::SESSION_KEY, []) as $key => $item) {
            if (! is_array($item)) {
                continue;
            }
            if (isset($items[$key])) {
                $items[$key]['qty'] = min(20, (int) ($items[$key]['qty'] ?? 0) + (int) ($item['qty'] ?? 0));
            } else {
                $items[$key] = $item;
            }
        }

        $this->putRawItems($items);
    }

    private function rawItems(): array
    {
        $customer = Auth::guard('customer')->user();
        return $customer ? ($customer->cart_items ?: []) : Session::get(self::SESSION_KEY, []);
    }

    private function putRawItems(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
        $customer = Auth::guard('customer')->user();
        if ($customer) {
            $customer->cart_items = $items;
            $customer->save();
        }
    }

    public function summary(): array
    {
        $items = array_values($this->items());
        $count = $this->count();
        $originalTotal = $this->originalTotal();
        $discountTotal = $this->discountTotal();
        $grandTotal = $this->subtotal();

        return [
            'items' => $items,
            'count' => $count,
            'original_total' => $originalTotal,
            'original_total_formatted' => number_format($originalTotal, 2, ',', '.') . ' TL',
            'discount_total' => $discountTotal,
            'discount_total_formatted' => '-' . number_format($discountTotal, 2, ',', '.') . ' TL',
            'savings_formatted' => number_format($discountTotal, 2, ',', '.') . ' TL',
            'has_discount' => $discountTotal > 0,
            'grand_total' => $grandTotal,
            'grand_total_formatted' => number_format($grandTotal, 2, ',', '.') . ' TL',
            'subtotal' => $grandTotal,
            'subtotal_formatted' => number_format($grandTotal, 2, ',', '.') . ' TL',
            'whatsapp_url' => $this->whatsappCheckoutUrl(),
        ];
    }

    public function whatsappCheckoutUrl(): ?string
    {
        $items = $this->items();
        if ($items === []) {
            return null;
        }

        $lines = ["🛍️ *Sipariş Talebi - SimgeVIP*\n"];
        $lines[] = "Merhaba, sepetimdeki ürünler için sipariş vermek istiyorum:\n";

        foreach ($items as $item) {
            $meta = collect([
                ! empty($item['color']) ? 'Renk: ' . $item['color'] : null,
                ! empty($item['size']) ? 'Beden: ' . $item['size'] : null,
                'Adet: ' . ($item['qty'] ?? 1),
            ])->filter()->implode(' · ');

            $priceDetail = $item['line_total_formatted'];
            if (! empty($item['has_discount'])) {
                $priceDetail .= " (İndirimli - Liste: {$item['line_original_total_formatted']})";
            }

            $lines[] = '• ' . $item['name'] . ($meta ? " ({$meta})" : '') . " — {$priceDetail}";
        }

        $originalTotal = $this->originalTotal();
        $discountTotal = $this->discountTotal();
        $grandTotal = $this->subtotal();

        $lines[] = '';
        if ($discountTotal > 0) {
            $lines[] = 'Toplam Tutar: ' . number_format($originalTotal, 2, ',', '.') . ' TL';
            $lines[] = 'Toplam İndirim: -' . number_format($discountTotal, 2, ',', '.') . ' TL';
            $lines[] = '*Genel Toplam: ' . number_format($grandTotal, 2, ',', '.') . ' TL*';
        } else {
            $lines[] = '*Toplam Tutar: ' . number_format($grandTotal, 2, ',', '.') . ' TL*';
        }

        return $this->whatsappUrl(implode("\n", $lines));
    }

    public function whatsappProductUrl(Product $product, ?string $color = null, ?string $size = null): string
    {
        $parts = [
            'Merhaba, bu ürünü sipariş etmek istiyorum:',
            $product->name,
            $product->sku ? 'Stok kodu: ' . $product->sku : null,
            $color ? 'Renk: ' . $color : null,
            $size ? 'Beden: ' . $size : null,
            'Fiyat: ' . ($product->formatted_discounted_price ?: $product->formatted_price),
            route('product.show', $product->slug),
        ];

        return $this->whatsappUrl(collect($parts)->filter()->implode("\n"));
    }

    public function whatsappUrl(string $message): string
    {
        $phone = preg_replace('/\D+/', '', (string) config('store.whatsapp', '905550000000'));

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    private function lineKey(int $productId, ?int $variantId, ?string $size): string
    {
        return implode(':', [
            $productId,
            $variantId ?: 0,
            $size ?: '-',
        ]);
    }
}
