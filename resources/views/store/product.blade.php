@extends('store.layout')

@section('title', $product->name)
@section('meta_description', \App\Support\SeoText::plain($product->description ?: $product->name, 500))
@section('canonical', route('product.show', $product->slug))

@section('og')
@php
    $productSeoText = \App\Support\SeoText::plain($product->description ?: $product->name, 160);
    $productImages = collect($product->has_uploaded_image ? $product->product_image_urls : [])
        ->filter()
        ->values();
    $ogImage = $productImages->first() ?: $product->image_url;
    if ($ogImage && !\Illuminate\Support\Str::startsWith($ogImage, ['http://', 'https://'])) {
        $ogImage = url($ogImage);
    }
@endphp
<meta property="og:type" content="product">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $product->name }} — {{ $siteName }}">
<meta property="og:description" content="{{ $productSeoText }}">
<meta property="og:url" content="{{ route('product.show', $product->slug) }}">
<meta property="og:locale" content="tr_TR">
@if ($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $product->name }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $product->name }} — {{ $siteName }}">
<meta name="twitter:description" content="{{ $productSeoText }}">
@if ($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif
<meta property="product:price:amount" content="{{ $product->discounted_price ?? $product->price }}">
<meta property="product:price:currency" content="TRY">
@endsection

@push('head')
@php
    $productSchemaPrice = $product->discounted_price ?? $product->price;
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => \App\Support\SeoText::plain($product->description ?: $product->name, 500),
        'brand' => [
            '@type' => 'Brand',
            'name' => $siteName,
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('product.show', $product->slug),
            'priceCurrency' => 'TRY',
            'price' => number_format((float) $productSchemaPrice, 2, '.', ''),
            'availability' => 'https://schema.org/InStock',
        ],
    ];
    if (filled($product->sku)) {
        $productSchema['sku'] = $product->sku;
    }
    if ($productImages->isNotEmpty()) {
        $productSchema['image'] = $productImages->all();
    }
    if ($product->category) {
        $productSchema['category'] = $product->category->name;
    }
    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Ana Sayfa',
            'item' => route('home'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Koleksiyon',
            'item' => route('shop.index'),
        ],
    ];
    if ($product->category) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $product->category->name,
            'item' => $product->category->url,
        ];
    }
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => count($breadcrumbItems) + 1,
        'name' => $product->name,
        'item' => route('product.show', $product->slug),
    ];
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($productSchema, \App\Support\SeoText::jsonFlags()) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, \App\Support\SeoText::jsonFlags()) !!}</script>
@endpush

@section('content')
@php
    $productImages = $product->product_image_urls;

    $variantsPayload = $product->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'color' => $variant->color,
            'images' => $variant->image_urls,
            'cover' => $variant->cover_image_url,
            'sizes' => $variant->sizes ?? [],
        ];
    })->values();

    $initialImages = $variantsPayload->first()['images'] ?? $productImages;
    if (empty($initialImages)) {
        $initialImages = $productImages;
    }
@endphp

<section class="section">
    <div class="container product-detail" data-product-detail
         data-product-images='@json($productImages)'
         data-variants='@json($variantsPayload)'>
        <div class="product-detail__gallery">
            <div class="gallery-main">
                {{-- Ürün Rozetleri (Sol Üst) - İndirim, Yeni Sezon, En Çok Satan --}}
                @php
                    $detailCampaignBadge = $product->campaign_badge;
                @endphp
                @if ($detailCampaignBadge || $product->is_new_season || $product->is_featured)
                    <div class="gallery-badges-wrap">
                        @if ($detailCampaignBadge)
                            <span class="gallery-badge gallery-badge--discount" title="{{ $detailCampaignBadge }}">
                                <x-store-icon name="tag" :size="12" />
                                <span>{{ $detailCampaignBadge }}</span>
                            </span>
                        @endif
                        @if ($product->is_new_season)
                            <span class="gallery-badge gallery-badge--season" title="Yeni Sezon">
                                <x-store-icon name="spark" :size="12" />
                                <span>Yeni Sezon</span>
                            </span>
                        @endif
                        @if ($product->is_featured)
                            <span class="gallery-badge gallery-badge--featured" title="En Çok Satan">
                                <x-store-icon name="star" :size="12" />
                                <span>En Çok Satan</span>
                            </span>
                        @endif
                    </div>
                @endif

                <div class="gallery-slider" data-gallery-slider>
                    <div class="gallery-track" data-gallery-track>
                        @foreach ($initialImages as $index => $img)
                            <div class="gallery-slide {{ $index === 0 ? 'is-active' : '' }}" data-gallery-slide data-index="{{ $index }}">
                                <img src="{{ $img }}" alt="{{ $product->name }}" width="600" height="1067" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}" decoding="async">
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="gallery-nav-btn gallery-nav-btn--prev" data-gallery-prev aria-label="Önceki görsel" @if (count($initialImages) <= 1) style="display: none;" @endif>
                    <x-store-icon name="chevron-left" :size="18" />
                </button>
                <button type="button" class="gallery-nav-btn gallery-nav-btn--next" data-gallery-next aria-label="Sonraki görsel" @if (count($initialImages) <= 1) style="display: none;" @endif>
                    <x-store-icon name="chevron-right" :size="18" />
                </button>

                <div class="gallery-dots" data-gallery-dots @if (count($initialImages) <= 1) style="display: none;" @endif>
                    @foreach ($initialImages as $index => $img)
                        <button type="button" class="gallery-dot {{ $index === 0 ? 'is-active' : '' }}" data-gallery-dot="{{ $index }}" aria-label="Görsel {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                <button type="button" class="gallery-zoom-badge" data-gallery-zoom-trigger aria-label="Görseli tam ekran incele" title="Detaylı incelemek için tıklayın">
                    <x-store-icon name="zoom-in" :size="14" />
                    <span>Büyüt</span>
                </button>
            </div>
        </div>

        <div class="product-detail__info">
            <nav class="product-detail__crumbs" aria-label="Sayfa yolu">
                <a href="{{ route('home') }}">Ana Sayfa</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('shop.index') }}">Koleksiyon</a>
                @if ($product->category)
                    <span aria-hidden="true">/</span>
                    <a href="{{ $product->category->url }}">{{ $product->category->name }}</a>
                @endif
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $product->name }}</span>
            </nav>
            {{-- Kategori & Stok Kodu Başlığı --}}
            <div class="product-detail__head-meta">
                @if ($product->category)
                    <a href="{{ route('shop.category', $product->category->slug) }}" class="product-detail__cat-badge">
                        {{ $product->category->name }}
                    </a>
                @endif
                @if ($product->sku)
                    <span class="product-detail__sku-badge">STOK KODU: {{ $product->sku }}</span>
                @endif
            </div>

            {{-- Ürün Başlığı --}}
            <h1 class="product-detail__title">{{ $product->name }}</h1>

            {{-- Fiyat ve Kampanya Kartı --}}
            <div class="product-detail__price-card">
                @if ($product->formatted_discounted_price)
                    <div class="product-detail__price-group">
                        <span class="product-detail__price-new">{{ $product->formatted_discounted_price }}</span>
                        <span class="product-detail__price-old">{{ $product->formatted_price }}</span>
                    </div>
                    @if ($product->campaign_badge)
                        <span class="product-detail__discount-pill" title="{{ $product->campaign_badge }}">
                            <x-store-icon name="tag" :size="11" />
                            <span>{{ $product->campaign_badge }}</span>
                        </span>
                    @endif
                @else
                    <div class="product-detail__price-group">
                        <span class="product-detail__price-regular">{{ $product->formatted_price }}</span>
                    </div>
                @endif

                <div class="product-detail__stock-indicator">
                    <span class="stock-pulse-dot"></span>
                    <span>Stokta Mevcut · Hızlı Kargo</span>
                </div>
            </div>

            {{-- Açıklama Alanı --}}
            @if ($product->description)
                <div class="product-detail__desc-box">
                    <div class="product-detail__desc-label">Ürün Detayı</div>
                    <div class="product-detail__desc-content">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Varyant Seçimleri (Renk ve Beden) --}}
            @if ($product->variants->isNotEmpty())
                <div class="variant-block">
                    <div class="variant-label-row">
                        <span class="variant-label">Renk Seçenekleri</span>
                        <span class="variant-current-label" data-active-color-name></span>
                    </div>
                    <div class="color-options" data-color-options>
                        @foreach ($product->variants as $index => $variant)
                            <button
                                type="button"
                                class="color-option {{ $index === 0 ? 'is-active' : '' }}"
                                data-variant-id="{{ $variant->id }}"
                                title="{{ $variant->color }}"
                            >
                                @if ($variant->cover_image_url)
                                    <img src="{{ $variant->cover_image_url }}" alt="{{ $variant->color }}">
                                @else
                                    <span class="color-option__fallback">{{ \Illuminate\Support\Str::substr($variant->color, 0, 1) }}</span>
                                @endif
                                <span class="color-option__name">{{ $variant->color }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="variant-label-row" style="margin-top: 1.15rem;">
                        <span class="variant-label">Beden Seçimi</span>
                        <span class="variant-current-label" data-active-size-name></span>
                    </div>
                    <div class="size-pills" data-size-pills>
                        @php $firstSizes = $product->variants->first()->sizes ?? []; @endphp
                        @foreach ($firstSizes as $size => $stock)
                            <button
                                type="button"
                                class="size-pill {{ (int) $stock <= 0 ? 'is-out' : '' }}"
                                data-size="{{ $size }}"
                                title="Stok: {{ $stock }}"
                                {{ (int) $stock <= 0 ? 'disabled' : '' }}
                            >{{ $size }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $attrs = $product->display_attributes;
                $hasVariants = $product->variants->isNotEmpty();
            @endphp

            @if (!empty($attrs))
                <details class="product-accordion">
                    <summary>Ürün Özellikleri</summary>
                    <div class="product-accordion__content">
                        <dl class="spec-list">
                            @foreach ($attrs as $label => $value)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd>{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </details>
            @endif

            {{-- Eylem Butonları (Masaüstü ve Sayfa İçi) --}}
            <div class="product-actions"
                 data-product-actions
                 data-product-id="{{ $product->id }}"
                 data-product-name="{{ $product->name }}"
                 data-product-url="{{ route('product.show', $product->slug) }}"
                 data-product-sku="{{ $product->sku }}"
                 data-product-price="{{ $product->formatted_discounted_price ?: $product->formatted_price }}"
                 data-has-variants="{{ $hasVariants ? '1' : '0' }}">
                <button type="button" class="btn btn--dark btn--add-cart" data-add-to-cart>
                    <x-store-icon name="bag" :size="16" />
                    <span>Sepete Ekle</span>
                </button>
                <a href="#" class="btn btn--whatsapp" data-whatsapp-order target="_blank" rel="noopener noreferrer">
                    <x-store-icon name="whatsapp" :size="16" />
                    <span>WhatsApp Siparişi</span>
                </a>
                <button type="button" class="btn btn--outline" data-store-hold>
                    <x-store-icon name="store" :size="16" />
                    <span>Mağazada Ayır</span>
                </button>
            </div>
            <p class="product-actions__hint" data-product-action-hint hidden></p>
            @if (Auth::guard('customer')->check())
                @php $isFavorite = in_array((int) $product->id, $favoriteProductIds ?? [], true); @endphp
                <form class="product-detail__favorite {{ $isFavorite ? 'is-favorite' : '' }}" method="POST" action="{{ route('customer.favorites.toggle', $product) }}" data-favorite-form data-favorite-product-id="{{ $product->id }}" data-favorite-product-name="{{ $product->name }}">
                    @csrf
                    <button type="submit" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"><x-store-icon name="heart" :size="16" /><span data-favorite-label>{{ $isFavorite ? 'Favorilerimden Kaldır' : 'Favorilerime Ekle' }}</span></button>
                </form>
            @else
                <a class="product-detail__favorite-link" href="{{ route('customer.login') }}"><x-store-icon name="heart" :size="16" /> Favorilere eklemek için giriş yap</a>
            @endif

            {{-- Butik Güvenceleri & Hizmet Rozetleri --}}
            <div class="product-detail__trust-badges">
                <div class="trust-badge">
                    <x-store-icon name="truck" :size="18" />
                    <div class="trust-badge__content">
                        <strong>Hızlı Teslimat</strong>
                        <span>Aynı gün kargo imkanı</span>
                    </div>
                </div>
                <div class="trust-badge">
                    <x-store-icon name="shield" :size="18" />
                    <div class="trust-badge__content">
                        <strong>Özel Butik Tasarım</strong>
                        <span>Seçkin kumaş & özenli dikiş</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Mobil Sabit Satın Alma Çubuğu (Sayfa Altında Yapışık - Modern Quick Buy Deneyimi) --}}
<div class="product-detail__mobile-bar"
     data-product-actions
     data-product-id="{{ $product->id }}"
     data-product-name="{{ $product->name }}"
     data-product-url="{{ route('product.show', $product->slug) }}"
     data-product-sku="{{ $product->sku }}"
     data-product-price="{{ $product->formatted_discounted_price ?: $product->formatted_price }}"
     data-has-variants="{{ $hasVariants ? '1' : '0' }}">
    <div class="product-detail__mobile-bar-info">
        <span class="mobile-bar-title">{{ $product->name }}</span>
        <span class="mobile-bar-price">{{ $product->formatted_discounted_price ?: $product->formatted_price }}</span>
    </div>
    <div class="product-detail__mobile-bar-actions">
        <a href="#" class="mobile-bar-wa-btn" data-whatsapp-order target="_blank" rel="noopener noreferrer" title="WhatsApp ile Sipariş Ver" aria-label="WhatsApp">
            <x-store-icon name="whatsapp" :size="20" />
        </a>
        <button type="button" class="mobile-bar-store-btn" data-store-hold title="Mağazada Ayır" aria-label="Mağazada Ayır">
            <x-store-icon name="store" :size="20" />
        </button>
        <button type="button" class="mobile-bar-buy-btn" data-add-to-cart title="Satın Al / Sepete Ekle" aria-label="Sepete Ekle">
            <x-store-icon name="bag" :size="18" />
            <span>Satın Al</span>
        </button>
    </div>
</div>

@if ($related->isNotEmpty())
<section class="section section--tight">
    <div class="container">
        <div class="section-head">
            <h2>Benzer ürünler</h2>
        </div>
        <div class="product-grid product-grid--4">
            @foreach ($related as $item)
                @include('store.partials.product-card', ['product' => $item])
            @endforeach
        </div>
    </div>
</section>
@endif

@include('store.partials.product-lightbox')
@endsection
