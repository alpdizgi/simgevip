@php($product = $getRecord())
<div class="sv-product-card-badges" aria-label="Ürün etiketleri">
    @if ($product->is_new_season)
        <span class="sv-product-badge sv-product-badge--season">Yeni Sezon</span>
    @endif
    @if ($product->is_featured)
        <span class="sv-product-badge sv-product-badge--featured">Çok Satan</span>
    @endif
    @if (filled($product->campaign_badge))
        <span class="sv-product-badge sv-product-badge--campaign">{{ $product->campaign_badge }}</span>
    @endif
</div>
