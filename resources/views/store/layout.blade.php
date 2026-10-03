<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoTitleRaw = trim($__env->yieldContent('title'));
        $seoTitle = $seoTitleRaw !== '' ? $seoTitleRaw : $siteName;
        if (! \Illuminate\Support\Str::endsWith($seoTitle, $siteName)) {
            $seoTitle .= ' — ' . $siteName;
        }

        $seoFallbackDescription = filled(optional($siteSettings)->meta_description)
            ? $siteSettings->meta_description
            : ($siteName . ' — seçilmiş giyim koleksiyonu. Sade, kurumsal ve zamansız parçalar.');
        $seoDescriptionRaw = trim($__env->yieldContent('meta_description'));
        $seoDescription = \App\Support\SeoText::plain(
            $seoDescriptionRaw !== '' ? $seoDescriptionRaw : $seoFallbackDescription,
            160
        );

        $seoCanonical = trim($__env->yieldContent('canonical'));
        if ($seoCanonical === '') {
            $seoCanonical = url()->current();
        }

        $seoRobots = trim($__env->yieldContent('robots'));
        if ($seoRobots === '') {
            $seoRobots = 'index, follow';
        }

        $seoImagePath = optional($siteSettings)->og_image_path;
        $seoLogoPath = optional($siteSettings)->logo_path;
        $seoImage = null;
        $seoLogo = null;
        if (filled($seoImagePath)) {
            $seoImage = \Illuminate\Support\Str::startsWith($seoImagePath, ['http://', 'https://'])
                ? $seoImagePath
                : asset('storage/' . ltrim($seoImagePath, '/'));
        }
        if (filled($seoLogoPath)) {
            $seoLogo = \Illuminate\Support\Str::startsWith($seoLogoPath, ['http://', 'https://'])
                ? $seoLogoPath
                : asset('storage/' . ltrim($seoLogoPath, '/'));
        }

        $seoSameAs = collect(optional($siteSettings)->social_media ?? [])
            ->pluck('url')
            ->filter()
            ->values();
        $seoOrganization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => route('home'),
        ];
        if ($seoLogo ?: $seoImage) {
            $seoOrganization['logo'] = $seoLogo ?: $seoImage;
        }
        if ($seoSameAs->isNotEmpty()) {
            $seoOrganization['sameAs'] = $seoSameAs->all();
        }
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta name="robots" content="{{ $seoRobots }}">
    @hasSection('og')
        @yield('og')
    @else
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $seoCanonical }}">
        <meta property="og:locale" content="tr_TR">
        @if ($seoImage)
            <meta property="og:image" content="{{ $seoImage }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ $seoImage }}">
        @else
            <meta name="twitter:card" content="summary">
        @endif
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
    @endif
    <script type="application/ld+json">{!! json_encode($seoOrganization, \App\Support\SeoText::jsonFlags()) !!}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}?v={{ filemtime(public_path('css/utilities.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/customer-account.css') }}?v={{ filemtime(public_path('css/customer-account.css')) }}">
    @stack('head')
</head>

<body class="@yield('body_class')">
    @include('store.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('store.partials.footer')
    @include('store.partials.cart-drawer')

    @php
        $initialToast = session('success') ?: (session('error') ?: ($errors->any() ? $errors->first() : null));
        $initialToastType = session('success') ? 'success' : 'error';
    @endphp
    <div class="store-toast" data-store-toast data-initial-message="{{ $initialToast }}" data-initial-type="{{ $initialToastType }}" role="status" aria-live="polite" aria-atomic="true" hidden>
        <span class="store-toast__symbol" data-toast-symbol aria-hidden="true">✓</span>
        <span class="store-toast__message" data-toast-message></span>
        <button class="store-toast__close" type="button" data-toast-close aria-label="Bildirimi kapat">×</button>
    </div>

    <!-- Scroll To Top Button -->
    <button type="button" class="scroll-top" data-scroll-top aria-label="Yukarı çık" title="Yukarı çık">
        <x-store-icon name="chevron-up" size="20" />
    </button>

    <script id="simge-store-data" type="application/json">
        {!! json_encode([
            'csrf' => csrf_token(),
            'customer' => Auth::guard('customer')->check(),
            'routes' => [
                'login' => route('customer.login'),
                'cartIndex' => route('cart.index'),
                'cartStore' => route('cart.store'),
                'cartUpdate' => route('cart.update'),
                'cartDestroy' => route('cart.destroy'),
                'storeHold' => route('store.hold'),
                'customerOrderRequest' => Auth::guard('customer')->check() ? route('customer.orders.create') : null,
            ],
            'whatsapp' => $storeWhatsapp ?? '905550000000',
            'cartCount' => (int) ($cartCount ?? 0),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script>
        window.SimgeStore = JSON.parse(document.getElementById('simge-store-data').textContent);
    </script>
    <script src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}" defer></script>
    @stack('scripts')
    <!-- Cookie Consent Banner -->
    <div id="cookie-consent-banner" style="display: none; position: fixed; bottom: 1.5rem; left: 1.5rem; max-width: 380px; background: #ffffff; box-shadow: 0 12px 40px rgba(0,0,0,0.12); border-radius: 12px; z-index: 9999; padding: 1.5rem; flex-direction: column; gap: 1rem; border: 1px solid #f0f0f0;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; font-size: 0.95rem; color: #111;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/></svg>
            Çerez (Cookie) Tercihleri
        </div>
        <p style="margin: 0; font-size: 0.8rem; color: #555; line-height: 1.6;">
            Size daha iyi bir alışveriş deneyimi sunmak ve site trafiğimizi analiz etmek için çerezler kullanıyoruz. Detaylı bilgi için <a href="{{ route('page.show', 'cerez-politikasi') }}" style="color: #000; font-weight: 500; text-decoration: underline;">Çerez Politikamızı</a> inceleyebilirsiniz.
        </p>
        <div style="display: flex; gap: 0.5rem; width: 100%; margin-top: 0.25rem;">
            <button id="reject-cookies" style="flex: 1; padding: 0.6rem; font-size: 0.8rem; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500;" onmouseover="this.style.background='#ebebeb'" onmouseout="this.style.background='#f5f5f5'">Sadece Gerekli</button>
            <button id="accept-cookies" style="flex: 1; padding: 0.6rem; font-size: 0.8rem; background: #000; color: #fff; border: 1px solid #000; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#000'">Tümünü Kabul Et</button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (!localStorage.getItem('cookie_consent')) {
                document.getElementById('cookie-consent-banner').style.display = 'flex';
            }
            
            document.getElementById('accept-cookies').addEventListener('click', function() {
                localStorage.setItem('cookie_consent', 'accepted');
                document.getElementById('cookie-consent-banner').style.display = 'none';
            });

            document.getElementById('reject-cookies').addEventListener('click', function() {
                localStorage.setItem('cookie_consent', 'rejected');
                document.getElementById('cookie-consent-banner').style.display = 'none';
            });
        });
    </script>
</body>

</html>
