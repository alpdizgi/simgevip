<header class="site-header-wrapper" data-header>
    <!-- 1. En Üst: Premium Topbar (Duyuru & İletişim Çubuğu) -->
    <div class="site-topbar">
        <div class="container site-topbar__inner">
            <div class="site-topbar__left">
                @php
                    $firstBranch = (is_array($siteSettings?->contact_info) && count($siteSettings->contact_info) > 0) 
                        ? $siteSettings->contact_info[0] 
                        : null;
                    $phone = $firstBranch['phone'] ?? '+90 555 000 00 00';
                    $email = $firstBranch['email'] ?? 'info@simgevip.com';
                @endphp
                <a href="tel:{{ str_replace(' ', '', $phone) }}" class="topbar-link topbar-link--phone" aria-label="Bizi Arayın">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>Müşteri Hizmetleri: <strong>{{ $phone }}</strong></span>
                </a>
                <span class="topbar-sep hidden-mobile"></span>
                <a href="mailto:{{ $email }}" class="topbar-link topbar-link--email hidden-mobile" aria-label="E-Posta Gönder">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>{{ $email }}</span>
                </a>
            </div>

            <div class="site-topbar__right">
                <div class="topbar-socials">
                @if (is_array($siteSettings?->social_media) && count($siteSettings->social_media) > 0)
                    @foreach ($siteSettings->social_media as $sm)
                        @if(!empty($sm['url']))
                            @php $platform = strtolower($sm['platform'] ?? ''); @endphp
                            <x-store-social-badge :platform="$platform" :url="$sm['url']" size="26" />
                        @endif
                    @endforeach
                @else
                    <a href="#" target="_blank" rel="noopener noreferrer" class="topbar-social-link" title="Instagram">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" target="_blank" rel="noopener noreferrer" class="topbar-social-link" title="Facebook">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    @php
                        $waHeaderUrl = "https://wa.me/905550000000";
                        if (isset($siteSettings) && !empty($siteSettings->contact_info[0]['whatsapp'])) {
                            $waHeaderUrl = $siteSettings->contact_info[0]['whatsapp'];
                        } elseif (isset($siteSettings) && is_array($siteSettings->social_media)) {
                            foreach ($siteSettings->social_media as $sm) {
                                if (strtolower($sm['platform'] ?? '') === 'whatsapp' && !empty($sm['url'])) {
                                    $waHeaderUrl = $sm['url'];
                                    break;
                                }
                            }
                        }
                    @endphp
                    <a href="{{ $waHeaderUrl }}" target="_blank" rel="noopener noreferrer" class="topbar-social-link topbar-social-link--whatsapp" title="WhatsApp">
                        <x-store-icon name="whatsapp" size="13" />
                    </a>
                @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2. İlk Üst Header: Ortada Logo — PC: solda Menü+Arama / Mobil: solda Menü, sağda Arama+Sepet -->
    <div class="site-main-header">
        <div class="container site-main-header__inner">
            <!-- Sol Alan: Menü & (masaüstünde) Arama -->
            <div class="site-main-header__left">
                <button type="button" class="nav-toggle" data-nav-toggle aria-label="Menüyü aç/kapat">
                    <div class="svgIcon menu-svg" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 7L4 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path><path d="M15 12L4 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path><path d="M9 17H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>
                    </div>
                    <span class="nav-toggle__label">Menü</span>
                </button>

                <div class="header-search-container">
                    <button type="button" class="header-search-toggle" aria-label="Arama yap" data-search-toggle>
                        <x-store-icon name="search" :size="24" />
                        <span class="header-search-toggle__label">Ürün Ara</span>
                    </button>
                    <div class="header-search-backdrop" data-search-close aria-hidden="true"></div>
                    <div class="header-search-dropdown-panel" aria-hidden="true" data-search-panel>
                        <form action="{{ route('shop.index') }}" method="GET" class="header-search" data-live-search>
                            <p class="header-search__kicker">Ürün Ara</p>
                            <div class="header-search__input-wrapper">
                                <x-store-icon name="search" :size="16" class="header-search__icon" />
                                <input type="search" name="q" value="{{ request('q') }}" placeholder="Koleksiyonda ara..." class="header-search__input" autocomplete="off" data-search-endpoint="{{ route('search.live') }}">
                                <button type="button" class="header-search__close" data-search-close aria-label="Aramayı Kapat">
                                    <x-store-icon name="x" :size="20" />
                                </button>
                            </div>
                            <div class="search-dropdown" data-search-results aria-hidden="true"></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Orta Alan: Ortalanmış Logo -->
            <div class="site-main-header__center">
                <a href="{{ route('home') }}" class="brand-center" aria-label="{{ $siteName }}">
                    @if (!empty($siteSettings?->logo_path))
                        <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="{{ $siteName }}" class="site-logo" style="max-height: 46px; width: auto; object-fit: contain;" fetchpriority="high" decoding="async">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="{{ $siteName }}" class="site-logo" width="180" height="42" fetchpriority="high" decoding="async">
                    @endif
                </a>
            </div>

            <!-- Sağ Alan: Hesap ve Sepet -->
            <div class="site-main-header__right">
                <a class="header-account-btn" href="{{ Auth::guard('customer')->check() ? route('customer.account') : route('customer.login') }}" aria-label="{{ Auth::guard('customer')->check() ? 'Hesabım' : 'Giriş yap veya üye ol' }}">
                    <x-store-icon name="user" :size="23" />
                    <span>{{ Auth::guard('customer')->check() ? 'Hesabım' : 'Giriş / Üye Ol' }}</span>
                </a>
                <button type="button" class="header-cart-btn" data-cart-open aria-label="Alışveriş sepeti">
                    <x-store-icon name="cart" :size="24" />
                    <span class="header-cart-btn__count" data-cart-count>{{ $cartCount ?? 0 }}</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Sol Taraftan Açılan Menü Draweri (header dışında — fixed konum viewport'a bağlı kalsın) -->
<nav class="site-nav-bar" data-nav aria-hidden="true">
    <div class="site-nav-bar__backdrop" data-nav-close></div>
    <div class="site-nav-bar__inner">
        <div class="site-nav-bar__head">
            <span class="site-nav-bar__title">Menü</span>
            <button type="button" class="site-nav-bar__close" data-nav-close aria-label="Kapat">
                <x-store-icon name="x" :size="18" />
            </button>
        </div>

        <ul class="nav-menu">
            <li class="nav-menu__item">
                <a href="{{ route('home') }}" class="nav-menu__link nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                    Ana Sayfa
                </a>
            </li>

            @foreach ($navCategories as $navItem)
                @if ($navItem->navChildren->isNotEmpty())
                    <li class="nav-menu__item has-dropdown {{ request()->is('kategori/'.$navItem->slug) || $navItem->navChildren->contains(fn ($c) => request()->is('kategori/'.$c->slug)) ? 'is-active' : '' }}" data-dropdown>
                        <a href="{{ $navItem->url }}" class="nav-menu__link nav-link">
                            <span>{{ $navItem->name }}</span>
                            <x-store-icon name="chevron-down" :size="12" class="nav-caret" />
                        </a>
                        <div class="nav-dropdown">
                            <a href="{{ $navItem->url }}" class="nav-dropdown__item nav-dropdown__item--all">
                                <span>Tüm {{ $navItem->name }} Modelleri</span>
                                <x-store-icon name="arrow-right" :size="14" />
                            </a>
                            @foreach ($navItem->navChildren as $child)
                                <a href="{{ $child->url }}" class="nav-dropdown__item {{ request()->is('kategori/'.$child->slug) ? 'is-active' : '' }}">
                                    {{ $child->name }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                @else
                    <li class="nav-menu__item">
                        <a href="{{ $navItem->url }}" class="nav-menu__link nav-link {{ request()->is('kategori/'.$navItem->slug) ? 'is-active' : '' }}">
                            {{ $navItem->name }}
                        </a>
                    </li>
                @endif
            @endforeach

            @foreach ($siteMenus as $menu)
                <li class="nav-menu__item">
                    <a href="{{ url($menu->url ?: '/') }}" class="nav-menu__link nav-link">
                        {{ $menu->title }}
                    </a>
                </li>
            @endforeach

            <li class="nav-menu__item">
                <a href="{{ route('about') }}" class="nav-menu__link nav-link {{ request()->routeIs('about') ? 'is-active' : '' }}">
                    Hakkımızda
                </a>
            </li>

            <li class="nav-menu__item">
                <a href="{{ route('contact.show') }}" class="nav-menu__link nav-link {{ request()->routeIs('contact.*') ? 'is-active' : '' }}">
                    İletişim
                </a>
            </li>
            <li class="nav-menu__item"><a href="{{ Auth::guard('customer')->check() ? route('customer.account') : route('customer.login') }}" class="nav-menu__link nav-link">{{ Auth::guard('customer')->check() ? 'Hesabım' : 'Giriş / Üye Ol' }}</a></li>
        </ul>
    </div>
</nav>
