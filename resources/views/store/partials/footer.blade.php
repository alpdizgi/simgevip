<footer class="site-footer">
    <style>
        .footer-fluid-container { width: 100%; max-width: 100%; padding: 0 4%; }
        .footer-grid-responsive { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4rem; padding-top: 3rem; padding-bottom: 2rem; }
        .footer-bottom-responsive { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; padding-top: 1.5rem; padding-bottom: 1.5rem; font-size: 0.85rem; border-top: 1px solid rgba(255, 255, 255, 0.05); }
        @media (max-width: 992px) {
            .footer-grid-responsive { gap: 2rem; }
        }
        @media (max-width: 768px) {
            .footer-fluid-container { padding: 0 5%; }
            .footer-grid-responsive { flex-direction: column; text-align: center; gap: 2.5rem; }
            .footer-grid-responsive > div { flex: 100% !important; min-width: 100% !important; }
            .footer-social { justify-content: center !important; }
            .footer-links li { justify-content: center !important; }
            .footer-bottom-responsive { flex-direction: column; text-align: center; justify-content: center; }
            .footer-bottom-responsive > div { justify-content: center !important; }
        }
    </style>
    <div class="footer-fluid-container footer-grid-responsive">
        <div class="footer-brand" style="flex: 1.5; min-width: 250px;">
            <a href="{{ url('/') }}" aria-label="{{ $siteName }} Anasayfa" style="display: block; margin-bottom: 1rem; text-align: center;">
                @if(!empty($siteSettings?->logo_path))
                    <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="{{ $siteName }}" class="footer-logo" style="max-height: 80px; width: auto; object-fit: contain; filter: brightness(0) invert(1); display: inline-block;">
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="{{ $siteName }}" class="footer-logo" style="max-height: 80px; width: auto; object-fit: contain; filter: brightness(0) invert(1); display: inline-block;">
                @endif
            </a>
            <p class="footer-brand__text" style="text-align: center;">Zamansız kesimler, seçilmiş kumaşlar ve sade bir stil dili. Kurumsal görünümle günlük konforu bir araya getiriyoruz.</p>
            
            <div class="footer-social" style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center;">
                @if (is_array($siteSettings?->social_media) && count($siteSettings->social_media) > 0)
                    @foreach ($siteSettings->social_media as $sm)
                        @if(!empty($sm['url']))
                            @php
                                $platform = strtolower($sm['platform'] ?? '');
                            @endphp
                            <x-store-social-badge :platform="$platform" :url="$sm['url']" size="36" />
                        @endif
                    @endforeach
                @else
                    <x-store-social-badge platform="instagram" url="#" size="36" />
                    <x-store-social-badge platform="facebook" url="#" size="36" />
                    <x-store-social-badge platform="whatsapp" url="https://wa.me/905550000000" size="36" />
                @endif
            </div>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <p class="footer-title">Kategoriler</p>
            <ul class="footer-links">
                @forelse ($navCategories as $cat)
                    <li><a href="{{ $cat->url }}">{{ $cat->name }}</a></li>
                @empty
                    <li><a href="{{ route('shop.index') }}">Tüm Ürünler</a></li>
                @endforelse
            </ul>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <p class="footer-title">Müşteri Hizmetleri</p>
            <ul class="footer-links">
                <li><a href="{{ route('about') }}">Hakkımızda</a></li>
                <li><a href="{{ route('contact.show') }}">İletişim</a></li>
                <li><a href="{{ route('faq.index') }}">Sıkça Sorulan Sorular</a></li>
                <li><a href="{{ route('page.show', 'iade-ve-degisim') }}">İade ve Değişim</a></li>
                <li><a href="{{ route('page.show', 'kvkk') }}">KVKK Aydınlatma Metni</a></li>
                <li><a href="{{ route('page.show', 'kullanim-kosullari') }}">Kullanım Koşulları</a></li>
                <li><a href="{{ route('page.show', 'gizlilik-politikasi') }}">Gizlilik Politikası</a></li>
                <li><a href="{{ route('page.show', 'cerez-politikasi') }}">Çerez Politikası</a></li>
                <li><a href="{{ route('page.show', 'mesafeli-satis-sozlesmesi') }}">Mesafeli Satış Sözleşmesi</a></li>
            </ul>
        </div>

        <div style="flex: 1.2; min-width: 200px;">
            <p class="footer-title">Bize Ulaşın</p>
            @if (is_array($siteSettings?->contact_info) && count($siteSettings->contact_info) > 0)
                @php $firstBranch = $siteSettings->contact_info[0]; @endphp
                <ul class="footer-links">
                    @if(!empty($firstBranch['branch_name']))
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <x-store-icon name="store" size="16" style="flex-shrink: 0; margin-top: 2px;" />
                            <span>{{ $firstBranch['branch_name'] }}</span>
                        </li>
                    @endif
                    @if(!empty($firstBranch['address']))
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <x-store-icon name="map-pin" size="16" style="flex-shrink: 0; margin-top: 2px;" />
                            <span>{{ $firstBranch['address'] }}</span>
                        </li>
                    @endif
                    @if(!empty($firstBranch['phone']))
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-store-icon name="phone" size="16" style="flex-shrink: 0;" />
                            <a href="tel:{{ str_replace(' ', '', $firstBranch['phone']) }}" style="color:inherit; text-decoration:none;">{{ $firstBranch['phone'] }}</a>
                        </li>
                    @endif
                    @if(!empty($firstBranch['email']))
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-store-icon name="mail" size="16" style="flex-shrink: 0;" />
                            <a href="mailto:{{ $firstBranch['email'] }}" style="color:inherit; text-decoration:none;">{{ $firstBranch['email'] }}</a>
                        </li>
                    @endif
                </ul>
            @elseif (is_string($siteSettings?->contact_info) && !empty($siteSettings->contact_info))
                <div class="footer-links">
                    <span>{!! nl2br(e($siteSettings->contact_info)) !!}</span>
                </div>
            @else
                <ul class="footer-links">
                    <li style="display: flex; align-items: flex-start; gap: 0.5rem;">
                        <x-store-icon name="store" size="16" style="flex-shrink: 0; margin-top: 2px;" />
                        <span>Merkez Ofis</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.5rem;">
                        <x-store-icon name="map-pin" size="16" style="flex-shrink: 0; margin-top: 2px;" />
                        <span>İstanbul, Türkiye</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-store-icon name="phone" size="16" style="flex-shrink: 0;" />
                        <a href="tel:+905550000000" style="color:inherit; text-decoration:none;">+90 555 000 00 00</a>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-store-icon name="mail" size="16" style="flex-shrink: 0;" />
                        <a href="mailto:info@simgevip.com" style="color:inherit; text-decoration:none;">info@simgevip.com</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    <div class="footer-fluid-container footer-bottom-responsive">
        <p>&copy; {{ date('Y') }} {{ $siteName }}. Tüm hakları saklıdır.</p>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <!-- Secure Shopping Shield -->
            <div class="footer-trust-badge">
                <x-store-icon name="shield" size="16" />
                <strong>Güvenli Alışveriş</strong>
            </div>

            <div style="width: 1px; height: 24px; background: currentColor; opacity: 0.2; margin: 0 0.5rem;"></div>

            <div class="footer-payment-methods">
                <img
                    src="{{ asset('images/payments.svg') }}"
                    alt="Visa, Mastercard, Troy, havale/EFT ve QR ile ödeme"
                    width="220"
                    height="25"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</footer>
