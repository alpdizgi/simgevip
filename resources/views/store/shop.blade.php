@extends('store.layout')

@php
    $shopSeoTitle = request()->filled('favoriler')
        ? 'Favorilerim'
        : (isset($category) ? $category->name : (filled($search ?? null) ? 'Arama Sonuçları' : 'Tüm Ürünler'));
    $shopCategoryText = isset($category) ? \App\Support\SeoText::plain($category->description, 500) : '';
    $shopSeoDescription = $shopCategoryText !== ''
        ? $shopCategoryText
        : ($siteName . ' koleksiyonu — seçilmiş giyim parçaları.');
    $shopCanonicalBase = isset($category) ? route('shop.category', $category->slug) : route('shop.index');
    $shopPage = max(1, (int) request()->query('page', 1));
    $shopNoisy = request()->filled('q')
        || request()->filled('favoriler')
        || request()->filled('kategori')
        || (filled($sort ?? null) && ($sort ?? 'yeni') !== 'yeni');
    $shopCanonical = (! $shopNoisy && $shopPage > 1)
        ? $shopCanonicalBase . '?page=' . $shopPage
        : $shopCanonicalBase;
@endphp
@section('title', $shopSeoTitle)
@section('meta_description', $shopSeoDescription)
@section('canonical', $shopCanonical)
@if ($shopNoisy)
    @section('robots', 'noindex, follow')
@endif
@if (! $shopNoisy && $products->hasPages())
    @push('head')
        @if ($products->currentPage() > 1)
            <link rel="prev" href="{{ $products->currentPage() === 2 ? $shopCanonicalBase : $shopCanonicalBase . '?page=' . ($products->currentPage() - 1) }}">
        @endif
        @if ($products->hasMorePages())
            <link rel="next" href="{{ $shopCanonicalBase . '?page=' . ($products->currentPage() + 1) }}">
        @endif
    @endpush
@endif

@php
    $formAction = isset($category)
        ? route('shop.category', $category->slug)
        : route('shop.index');

    $sortLabels = [
        'yeni' => 'En yeni',
        'fiyat_asc' => 'Fiyat: düşükten yükseğe',
        'fiyat_desc' => 'Fiyat: yüksekten düşüğe',
        'ad' => 'İsme göre',
    ];
    $currentSortLabel = $sortLabels[$sort] ?? 'En yeni';
    $hasActiveFilter = filled($search) || (!isset($category) && filled($activeCategory));
@endphp

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <h1>{{ request()->filled('favoriler') ? 'Favorilerim' : (isset($category) ? $category->name : ($search ? 'Arama Sonuçları' : ($activeCategory && ($ac = $categories->firstWhere('slug', $activeCategory)) ? $ac->name : 'Tüm Ürünler'))) }}</h1>
        <p>
            @if(request()->filled('favoriler'))
                Favori listenizdeki seçilmiş ürünler.
            @elseif(isset($category) && !empty($category->description))
                {{ $category->description }}
            @elseif($activeCategory && ($ac = $categories->firstWhere('slug', $activeCategory)) && !empty($ac->description))
                {{ $ac->description }}
            @elseif($search)
                "{{ $search }}" aramanız için listelenen kadın butik koleksiyon ürünleri.
            @else
                Modern kadın siluetini yansıtan zamansız parçalar, seçkin kumaşlar ve özgün kesimler.
            @endif
        </p>
    </div>
</section>

@include('store.partials.campaign-banner')

<section class="section section--tight">
    <div class="container shop-layout">
        <div class="shop-toolbar" data-shop-toolbar>
            {{-- Sol: Filtre --}}
            <div class="shop-toolbar__side shop-toolbar__side--left">
                <div class="shop-toolbar__dropdown" data-shop-dropdown>
                    <button
                        type="button"
                        class="shop-toolbar__btn {{ $hasActiveFilter ? 'is-active' : '' }}"
                        data-shop-dropdown-toggle
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <x-store-icon name="sliders" :size="18" />
                        <span>Filtre</span>
                        @if ($hasActiveFilter)
                            <span class="shop-toolbar__dot" aria-hidden="true"></span>
                        @endif
                    </button>

                    <div class="shop-toolbar__panel" data-shop-dropdown-panel hidden>
                        <form method="get" action="{{ $formAction }}" class="shop-toolbar__form" data-shop-filter-form>
                            @if (request()->filled('favoriler'))
                                <input type="hidden" name="favoriler" value="1">
                            @endif
                            @if (filled($sort) && $sort !== 'yeni')
                                <input type="hidden" name="siralama" value="{{ $sort }}">
                            @endif

                            <label class="shop-toolbar__field">
                                <span>Ara</span>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Ürün adı veya kod...">
                            </label>

                            @unless (isset($category))
                                <label class="shop-toolbar__field">
                                    <span>Kategori</span>
                                    <select name="kategori">
                                        <option value="">Tümü</option>
                                        @foreach ($categories->whereNull('parent_id') as $cat)
                                            <option value="{{ $cat->slug }}" @selected($activeCategory === $cat->slug)>{{ $cat->name }}</option>
                                            @foreach ($categories->where('parent_id', $cat->id) as $child)
                                                <option value="{{ $child->slug }}" @selected($activeCategory === $child->slug)>— {{ $child->name }}</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </label>
                            @endunless

                            <div class="shop-toolbar__form-actions">
                                <button type="submit" class="shop-toolbar__apply">Uygula</button>
                                @if ($hasActiveFilter)
                                    <a href="{{ $formAction }}" class="shop-toolbar__clear">Temizle</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Orta: ürün sayısı --}}
            <p class="shop-toolbar__count">{{ $products->total() }} ürün</p>

            {{-- Sağ: Sıralama --}}
            <div class="shop-toolbar__side shop-toolbar__side--right">
                <div class="shop-toolbar__dropdown shop-toolbar__dropdown--end" data-shop-dropdown>
                    <button
                        type="button"
                        class="shop-toolbar__btn"
                        data-shop-dropdown-toggle
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span class="shop-toolbar__btn-label">{{ $currentSortLabel }}</span>
                        <x-store-icon name="sort" :size="18" />
                    </button>

                    <div class="shop-toolbar__panel shop-toolbar__panel--end" data-shop-dropdown-panel hidden>
                        <form method="get" action="{{ $formAction }}" class="shop-toolbar__sort-form">
                            @if (request()->filled('favoriler'))
                                <input type="hidden" name="favoriler" value="1">
                            @endif
                            @if (filled($search))
                                <input type="hidden" name="q" value="{{ $search }}">
                            @endif
                            @if (!isset($category) && filled($activeCategory))
                                <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                            @endif

                            <p class="shop-toolbar__panel-title">Sıralama</p>
                            <div class="shop-toolbar__sort-list" role="listbox" aria-label="Sıralama">
                                @foreach ($sortLabels as $value => $label)
                                    <button
                                        type="submit"
                                        name="siralama"
                                        value="{{ $value }}"
                                        class="shop-toolbar__sort-option {{ $sort === $value ? 'is-selected' : '' }}"
                                        role="option"
                                        aria-selected="{{ $sort === $value ? 'true' : 'false' }}"
                                    >
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="shop-results">
            @if ($products->count())
                <div class="product-grid">
                    @foreach ($products as $product)
                        @include('store.partials.product-card', [
                            'product' => $product,
                            'loading' => $loop->index < 4 ? 'eager' : 'lazy',
                            'fetchPriority' => $loop->index < 2 ? 'high' : 'auto'
                        ])
                    @endforeach
                </div>
                <div class="pagination-wrap">
                    {{ $products->links('vendor.pagination.store') }}
                </div>
            @else
                <div class="empty-state">
                    <p>Bu kriterlere uygun ürün bulunamadı.</p>
                    <a href="{{ $formAction }}" class="btn btn--dark">Filtreleri temizle</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
