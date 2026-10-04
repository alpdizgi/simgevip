@php($product = $getRecord())
<div class="sv-product-card-badges flex flex-col gap-1 items-start" aria-label="Ürün etiketleri">
    @if ($product->is_new_season)
        <span class="sv-product-badge sv-product-badge--season whitespace-nowrap text-[10px] px-1.5 py-0.5 rounded bg-green-100 text-green-700">Yeni Sezon</span>
    @endif
    @if ($product->is_featured)
        <span class="sv-product-badge sv-product-badge--featured whitespace-nowrap text-[10px] px-1.5 py-0.5 rounded bg-orange-100 text-orange-700">Çok Satan</span>
    @endif
    @if (filled($product->campaign_badge))
        <span class="sv-product-badge sv-product-badge--campaign whitespace-nowrap text-[10px] px-1.5 py-0.5 rounded bg-red-100 text-red-700">{{ $product->campaign_badge }}</span>
    @endif
</div>
