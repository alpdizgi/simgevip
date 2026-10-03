@php
    $image = $product->image_url;
    $badge = $product->campaign_badge;
    $sale = $product->formatted_discounted_price;
    $isNew = $product->is_new;
    $isFeatured = $product->is_featured;
    $isNewSeason = (bool) ($product->is_new_season ?? false);
    $hasVariants = isset($product->variants_count)
        ? (int) $product->variants_count > 0
        : ($product->relationLoaded('variants') && $product->variants->isNotEmpty());
@endphp
@php
    $cardLoading = $loading ?? 'lazy';
    $cardFetchPriority = $fetchPriority ?? 'auto';
@endphp
<article class="product-card">
    <div class="product-card__media">
        @if (Auth::guard('customer')->check())
            @php $isFavorite = in_array((int) $product->id, $favoriteProductIds ?? [], true); @endphp
            <form class="product-card__favorite {{ $isFavorite ? 'is-favorite' : '' }}" method="POST" action="{{ route('customer.favorites.toggle', $product) }}" data-favorite-form data-favorite-product-id="{{ $product->id }}" data-favorite-product-name="{{ $product->name }}">
                @csrf
                <button type="submit" title="{{ $isFavorite ? 'Favorilerden kaldır' : 'Favorilere ekle' }}" aria-label="{{ $product->name }} {{ $isFavorite ? 'favorilerden kaldır' : 'favorilere ekle' }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"><x-store-icon name="heart" :size="18" /></button>
            </form>
        @else
            <a class="product-card__favorite" href="{{ route('customer.login') }}" title="Favorilere eklemek için giriş yap" aria-label="Favorilere eklemek için giriş yap"><x-store-icon name="heart" :size="18" /></a>
        @endif
        <a href="{{ route('product.show', $product->slug) }}" class="product-card__media-link">
            <img src="{{ $image }}" alt="{{ $product->name }}" width="450" height="800" loading="{{ $cardLoading }}" fetchpriority="{{ $cardFetchPriority }}" decoding="async">
        </a>

        {{-- Rozetler (Sol Üst) - PC'de tam rozet (ikon + yazı), Mobilde sadece şık ikon --}}
        @if ($badge || $isNewSeason || $isFeatured)
            <div class="product-card__badges-wrap">
                @if ($badge)
                    <span class="product-card__micro-badge product-card__micro-badge--discount" title="{{ $badge }}">
                        <x-store-icon name="tag" :size="11" />
                        <span class="product-card__badge-label">{{ $badge }}</span>
                    </span>
                @endif
                @if ($isNewSeason)
                    <span class="product-card__micro-badge product-card__micro-badge--season" title="Yeni Sezon">
                        <x-store-icon name="spark" :size="11" />
                        <span class="product-card__badge-label">Yeni Sezon</span>
                    </span>
                @endif
                @if ($isFeatured)
                    <span class="product-card__micro-badge product-card__micro-badge--featured" title="En Çok Satan">
                        <x-store-icon name="star" :size="11" />
                        <span class="product-card__badge-label">En Çok Satan</span>
                    </span>
                @endif
            </div>
        @endif

        {{-- Mobil Satın Alma İkonu (Sağ Alt Yuvarlak Floating Buton) --}}
        <div class="product-card__quick-buy"
             data-product-actions
             data-product-id="{{ $product->id }}"
             data-product-name="{{ $product->name }}"
             data-product-url="{{ route('product.show', $product->slug) }}"
             data-product-sku="{{ $product->sku }}"
             data-product-price="{{ $sale ?: $product->formatted_price }}"
             data-has-variants="{{ $hasVariants ? '1' : '0' }}">
            @if ($hasVariants)
                <a href="{{ route('product.show', $product->slug) }}"
                   class="product-card__quick-buy-btn"
                   aria-label="{{ $product->name }} Seçenekleri ve Satın Al"
                   title="Satın Al">
                    <x-store-icon name="bag" :size="15" />
                </a>
            @else
                <button type="button"
                        class="product-card__quick-buy-btn"
                        data-add-to-cart
                        aria-label="{{ $product->name }} Sepete Ekle"
                        title="Sepete Ekle">
                    <x-store-icon name="bag" :size="15" />
                </button>
            @endif
        </div>

        {{-- Masaüstü Hover Satın Al Çubuğu --}}
        <div class="product-card__hover-add"
             data-product-actions
             data-product-id="{{ $product->id }}"
             data-product-name="{{ $product->name }}"
             data-product-url="{{ route('product.show', $product->slug) }}"
             data-product-sku="{{ $product->sku }}"
             data-product-price="{{ $sale ?: $product->formatted_price }}"
             data-has-variants="{{ $hasVariants ? '1' : '0' }}">
            @if ($hasVariants)
                <a href="{{ route('product.show', $product->slug) }}" class="btn btn--dark btn--block">
                    <x-store-icon name="bag" :size="13" /><span class="btn__label">Sepete Ekle</span>
                </a>
            @else
                <button type="button" class="btn btn--dark btn--block" data-add-to-cart>
                    <x-store-icon name="bag" :size="13" /><span class="btn__label">Sepete Ekle</span>
                </button>
            @endif
        </div>
    </div>

    <div class="product-card__body">
        @if ($product->category)
            <p class="product-card__cat">{{ $product->category->name }}</p>
        @endif

        <h3 class="product-card__title">
            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>

        <div class="product-card__price-box">
            @if ($sale)
                <div class="product-card__price-row">
                    <span class="product-card__price-new">{{ $sale }}</span>
                    <span class="product-card__price-old">{{ $product->formatted_price }}</span>
                </div>
            @else
                <div class="product-card__price-row">
                    <span class="product-card__price-regular">{{ $product->formatted_price }}</span>
                </div>
            @endif
        </div>

        <div class="product-card__actions"
             data-product-actions
             data-product-id="{{ $product->id }}"
             data-product-name="{{ $product->name }}"
             data-product-url="{{ route('product.show', $product->slug) }}"
             data-product-sku="{{ $product->sku }}"
             data-product-price="{{ $sale ?: $product->formatted_price }}"
             data-has-variants="{{ $hasVariants ? '1' : '0' }}">

            <a href="#" class="btn btn--whatsapp btn--sm" data-whatsapp-order target="_blank" rel="noopener noreferrer">
                <x-store-icon name="whatsapp" :size="13" /><span class="btn__label">WhatsApp Sipariş</span>
            </a>
            <button type="button" class="btn btn--outline btn--sm" data-store-hold>
                <x-store-icon name="store" :size="13" /><span class="btn__label">Mağazadan Ayır</span>
            </button>
        </div>
    </div>
</article>
