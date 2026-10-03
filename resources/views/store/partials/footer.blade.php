<footer class="site-footer">
    <div class="container footer-grid" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4rem;">
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
                    @if(!empty($firstBranch['branch_name']))<li><strong>{{ $firstBranch['branch_name'] }}</strong></li>@endif
                    @if(!empty($firstBranch['address']))<li><span>{{ $firstBranch['address'] }}</span></li>@endif
                    @if(!empty($firstBranch['phone']))<li><span>Tel: <a href="tel:{{ str_replace(' ', '', $firstBranch['phone']) }}" style="color:inherit; text-decoration:none;">{{ $firstBranch['phone'] }}</a></span></li>@endif
                    @if(!empty($firstBranch['email']))<li><span>E-Posta: <a href="mailto:{{ $firstBranch['email'] }}" style="color:inherit; text-decoration:none;">{{ $firstBranch['email'] }}</a></span></li>@endif
                </ul>
            @elseif (is_string($siteSettings?->contact_info) && !empty($siteSettings->contact_info))
                <div class="footer-links">
                    <span>{!! nl2br(e($siteSettings->contact_info)) !!}</span>
                </div>
            @else
                <ul class="footer-links">
                    <li><strong>Merkez Ofis</strong></li>
                    <li><span>İstanbul, Türkiye</span></li>
                    <li><span>Tel: <a href="tel:+905550000000" style="color:inherit; text-decoration:none;">+90 555 000 00 00</a></span></li>
                    <li><span>E-Posta: <a href="mailto:info@simgevip.com" style="color:inherit; text-decoration:none;">info@simgevip.com</a></span></li>
                </ul>
            @endif
        </div>
    </div>

    <div class="container footer-bottom" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.85rem; opacity: 0.7;">
        <p>&copy; {{ date('Y') }} {{ $siteName }}. Tüm hakları saklıdır.</p>
        <div style="display: flex; align-items: center; gap: 0.5rem; opacity: 0.8; font-size: 0.875rem;">
            <x-store-icon name="shield" size="18" />
            <span>Güvenli Alışveriş</span>
        </div>
    </div>
</footer>
