@extends('store.layout')

@section('title', filled(optional($siteSettings)->meta_title) ? $siteSettings->meta_title : $siteName)
@section('canonical', route('home'))
@section('body_class', $sliders->first() && $sliders->first()->image_path ? 'page-home has-hero' : 'page-home')

@push('head')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $siteName,
    'url' => route('home'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => route('shop.index') . '?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], \App\Support\SeoText::jsonFlags()) !!}</script>
@endpush

@section('content')
<h1 class="seo-only">{{ filled(optional($siteSettings)->meta_title) ? $siteSettings->meta_title : $siteName }}</h1>
@if ($sliders->isNotEmpty())
    <div class="hero-slider js-hero-slider" data-autoplay="5000">
        <div class="hero-slider__track">
            @foreach($sliders as $slide)
                @php
                    $img = $slide->image_path ? asset('storage/' . ltrim($slide->image_path, '/')) : null;
                    $link = null;
                    if (filled($slide->link)) {
                        $rawLink = trim((string) $slide->link);
                        $link = \Illuminate\Support\Str::startsWith($rawLink, ['http://', 'https://', '//'])
                            ? $rawLink
                            : url('/' . ltrim($rawLink, '/'));
                    }
                @endphp
                @if ($img)
                    <div class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-slide-index="{{ $loop->index }}">
                        @if ($link)
                            <a href="{{ $link }}" class="hero hero--image hero--link" aria-label="{{ $slide->title ?? 'Kampanya / koleksiyon' }}">
                                <img src="{{ $img }}" alt="{{ $slide->title ?? 'SimgeVIP Vitrin' }}" class="hero__img" decoding="async" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                            </a>
                        @else
                            <div class="hero hero--image" aria-label="{{ $slide->title ?? 'Vitrin' }}">
                                <img src="{{ $img }}" alt="{{ $slide->title ?? 'SimgeVIP Vitrin' }}" class="hero__img" decoding="async" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        @if ($sliders->count() > 1)
            <button type="button" class="hero-slider__arrow hero-slider__arrow--prev" data-hero-prev aria-label="Önceki Slayt">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" class="hero-slider__arrow hero-slider__arrow--next" data-hero-next aria-label="Sonraki Slayt">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>

            <div class="hero-slider__dots" data-hero-dots>
                @foreach ($sliders as $index => $slide)
                    <button type="button" class="hero-slider__dot {{ $loop->first ? 'is-active' : '' }}" data-hero-dot="{{ $loop->index }}" aria-label="Slayt {{ $loop->iteration }}"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif

@include('store.partials.campaign-banner')

@if ($navCategories->isNotEmpty())
<section class="section section--tight">
    <div class="container">
        <div class="section-head">
            <h2>Kategoriler</h2>
            <p>Günlük zarafetten özel davetlere uzanan zengin koleksiyon dünyamızı keşfedin.</p>
        </div>
        <div class="category-grid">
            @foreach ($navCategories as $category)
                <a href="{{ $category->url }}" class="category-card">
                    <img
                        src="{{ $category->image_url }}"
                        alt="{{ $category->name }}"
                        class="category-card__image"
                        width="300"
                        height="533"
                        loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}"
                        decoding="async"
                    >
                    <span class="category-card__band">
                        <span class="category-card__name">{{ $category->name }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if (!empty($categoryRows))
    {{-- Hızlı Kategori Gezinti Şeridi --}}
    <nav class="category-quick-nav" aria-label="Kategoriler">
        <div class="container">
            <div class="category-quick-nav__inner">
                <span class="category-quick-nav__label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <span>Kategoriler:</span>
                </span>
                <div class="category-quick-nav__scroll">
                    @foreach ($categoryRows as $i => $row)
                        @php
                            $cat = $row['category'];
                        @endphp
                        <a href="#kategori-{{ $cat->slug }}" class="category-quick-nav__chip">
                            <span>{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    {{-- Kategori Sırasına Göre 4'lü Ürün Satırları (Her Kategoriden 8 Ürün, Tıklamayla Sağa/Sola Kaydırma) --}}
    <div class="category-sections-feed">
        @foreach ($categoryRows as $index => $row)
            @if (!$loop->first)
                <div class="home-section-divider" style="margin:0; border-left:none; border-right:none;"></div>
            @endif
            @php
                $cat = $row['category'];
                $products = $row['products'];
                $isAlt = ($index % 2 === 1);
            @endphp
            <section id="kategori-{{ $cat->slug }}"
                     class="section section--category-carousel {{ $isAlt ? 'section--category-carousel--alt' : '' }}"
                     data-category-carousel>
                @if ($cat->image_url)
                    <div class="category-carousel__bg" aria-hidden="true">
                        <img src="{{ $cat->image_url }}" alt="" class="category-carousel__bg-img" loading="lazy" decoding="async" fetchpriority="low">
                        <div class="category-carousel__bg-overlay"></div>
                    </div>
                @endif
                <div class="container">
                    <div class="category-carousel__head">
                        <div class="category-carousel__title-area">
                            <h2 class="category-carousel__title">
                                <a href="{{ $cat->url }}">{{ $cat->name }}</a>
                            </h2>
                            @if ($cat->description)
                                <p class="category-carousel__sub">{{ $cat->description }}</p>
                            @endif
                        </div>

                        <div class="category-carousel__actions">
                            <a href="{{ $cat->url }}" class="category-carousel__all-link">
                                <span>Tümünü Gör</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- 4'lü Ürün Vitrini (Tıklamayla Sağa / Sola Kayar) --}}
                    <div class="category-carousel__wrap">
                        <button type="button"
                                class="category-carousel__side-arrow category-carousel__side-arrow--prev"
                                data-carousel-prev
                                aria-label="{{ $cat->name }} önceki ürünler">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>

                        <div class="category-carousel__viewport" data-carousel-viewport>
                            <div class="category-carousel__track" data-carousel-track>
                                @foreach ($products as $product)
                                    <div class="category-carousel__item" data-carousel-item>
                                        @include('store.partials.product-card', [
                                            'product' => $product,
                                            'loading' => ($index === 0 && $loop->index < 4) ? 'eager' : 'lazy',
                                            'fetchPriority' => ($index === 0 && $loop->index < 2) ? 'high' : 'auto'
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="button"
                                class="category-carousel__side-arrow category-carousel__side-arrow--next"
                                data-carousel-next
                                aria-label="{{ $cat->name }} sonraki ürünler">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>

                    {{-- Sayfalama Noktaları --}}
                    <div class="category-carousel__dots" data-carousel-dots>
                        <button type="button" class="category-carousel__dot is-active" data-page="0" aria-label="Sayfa 1"></button>
                        <button type="button" class="category-carousel__dot" data-page="1" aria-label="Sayfa 2"></button>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
    <div class="home-section-divider" style="margin:0; border-left:none; border-right:none;"></div>
@endif

<section class="section section--editorial">
    <div class="container editorial">
        <div class="editorial__media">
            <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80" alt="SimgeVIP stil">
        </div>
        <div class="editorial__copy">
            <p class="eyebrow">Marka</p>
            <h2>Sade çizgi, güçlü duruş</h2>
            <p>{{ $siteName }}; günlük ve kurumsal yaşamın kesişiminde, abartısız ama etkili bir giyim dili sunar. Her parça uzun ömürlü kullanım ve net bir siluet için seçilir.</p>
            <a href="{{ route('about') }}" class="text-link">Hikayemizi okuyun</a>
        </div>
    </div>
</section>
@endsection
