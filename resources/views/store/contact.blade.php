@extends('store.layout')

@section('title', 'İletişim')
@section('meta_description', $siteName . ' ile iletişime geçin. Mağaza adresi, telefon ve e-posta.')
@section('canonical', route('contact.show'))

@push('head')
@php
    $contactBranch = collect($siteSettings->contact_info ?? [])->first(function ($branch) {
        return is_array($branch) && (filled($branch['address'] ?? null) || filled($branch['phone'] ?? null) || filled($branch['email'] ?? null));
    });
    $storeSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ClothingStore',
        'name' => $siteName,
        'url' => route('home'),
    ];
    if (is_array($contactBranch)) {
        if (filled($contactBranch['phone'] ?? null)) {
            $storeSchema['telephone'] = $contactBranch['phone'];
        }
        if (filled($contactBranch['email'] ?? null)) {
            $storeSchema['email'] = $contactBranch['email'];
        }
        if (filled($contactBranch['address'] ?? null)) {
            $storeSchema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => \App\Support\SeoText::plain($contactBranch['address'], 300),
            ];
        }
    }
    if (filled(optional($siteSettings)->logo_path)) {
        $storeSchema['image'] = asset('storage/' . ltrim($siteSettings->logo_path, '/'));
    }

    $formatPhone = function ($phone) {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (preg_match('/^0(\d{3})(\d{3})(\d{2})(\d{2})$/', $digits, $m)) {
            return '0'.$m[1].' '.$m[2].' '.$m[3].' '.$m[4];
        }

        return $phone;
    };

    $socialIcons = [
        'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r="0.8" fill="currentColor" stroke="none"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.2 20v-6.2h2.1l.3-2.4h-2.4V9.8c0-.7.2-1.2 1.2-1.2H16.7V6.4c-.3 0-1.1-.1-2.1-.1-2.1 0-3.5 1.3-3.5 3.6v2h-2.3v2.4h2.3V20h3.1z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.2 4c.4 2.3 1.7 3.9 4 4.2v2.3c-1.4 0-2.7-.4-4-1.2v6.4c0 3.3-2.5 5.6-5.7 5.6S3 18.9 3 15.7c0-3.2 2.6-5.5 5.8-5.5.4 0 .8 0 1.2.1v2.5c-.4-.2-.8-.2-1.2-.2-1.7 0-3 1.3-3 3.1s1.4 3.1 3.1 3.1 2.9-1.3 2.9-3.2V4h2.4z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 4.2A7.7 7.7 0 0 0 5.4 16.1L4.4 19.6l3.6-1a7.7 7.7 0 0 0 11.5-6.6 7.7 7.7 0 0 0-7.46-7.8zm4.48 10.9c-.2.55-1.14 1.05-1.58 1.1-.4.05-.9.08-1.46-.09-.33-.1-.76-.25-1.31-.49-2.3-1-3.8-3.32-3.92-3.48-.11-.15-.93-1.24-.93-2.37 0-1.12.58-1.67.79-1.9.2-.22.44-.28.59-.28h.42c.14 0 .32-.05.5.38.2.46.64 1.58.7 1.7.06.11.1.25.02.4-.08.15-.12.24-.24.37-.12.13-.25.29-.36.39-.12.11-.24.23-.1.45.14.22.62 1.02 1.33 1.65.91.81 1.68 1.07 1.92 1.19.24.12.38.1.52-.06.14-.16.6-.7.76-.94.16-.24.32-.2.54-.12.22.08 1.4.66 1.64.78.24.12.4.18.46.28.06.1.06.58-.14 1.13z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.2 8.2a2.7 2.7 0 0 0-1.9-1.9C17.6 6 12 6 12 6s-5.6 0-7.3.3a2.7 2.7 0 0 0-1.9 1.9C2.5 9.9 2.5 12 2.5 12s0 2.1.3 3.8a2.7 2.7 0 0 0 1.9 1.9C6.4 18 12 18 12 18s5.6 0 7.3-.3a2.7 2.7 0 0 0 1.9-1.9c.3-1.7.3-3.8.3-3.8s0-2.1-.3-3.8zM10.2 14.8V9.2L15.4 12l-5.2 2.8z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.7 10.4 21.4 3h-1.6l-5.8 6.4L9.2 3H3.4l7 10-7 7.7h1.6l6.1-6.8 4.9 6.8h5.8l-7.1-10.3zm-2.2 2.4-.7-1-5.6-7.7h2.4l4.5 6.2.7 1 5.9 8.1h-2.4l-4.8-6.6z"/></svg>',
        'pinterest' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3.2A8.8 8.8 0 0 0 8.1 19.9c-.05-.7-.1-1.78.02-2.55l1.3-5.5s-.32-.65-.32-1.6c0-1.5.87-2.62 1.95-2.62.92 0 1.37.69 1.37 1.52 0 .93-.59 2.32-.9 3.61-.25 1.07.54 1.95 1.6 1.95 1.92 0 3.21-2.47 3.21-5.39 0-2.22-1.5-3.88-4.22-3.88-3.08 0-5 2.3-5 4.86 0 .88.26 1.5.67 1.98.18.22.21.3.14.55l-.25.98c-.08.3-.26.41-.6.3-1.34-.55-1.96-2.02-1.96-3.67 0-2.73 2.31-6.01 6.89-6.01 3.67 0 6.08 2.66 6.08 5.51 0 3.77-2.1 6.58-5.2 6.58-1.04 0-2.02-.56-2.36-1.2l-.64 2.45c-.23.9-.86 2.02-1.28 2.7A8.8 8.8 0 1 0 12 3.2z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.7 9.3H4.1V19h2.6V9.3zM5.4 4.6A1.55 1.55 0 1 0 5.4 7.7 1.55 1.55 0 0 0 5.4 4.6zM19.9 19h-2.6v-5.1c0-1.36-.5-2.1-1.55-2.1-.8 0-1.24.54-1.45 1.06-.08.18-.1.44-.1.7V19h-2.6s.04-8.55 0-9.7h2.6v1.52c.35-.54 1.12-1.32 2.78-1.32 2.03 0 3.52 1.33 3.52 4.18V19z"/></svg>',
    ];
@endphp
<script type="application/ld+json">{!! json_encode($storeSchema, \App\Support\SeoText::jsonFlags()) !!}</script>
@endpush

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <p class="eyebrow">İletişim</p>
        <h1>Size nasıl yardımcı olabiliriz?</h1>
        <p>Sipariş, ürün bilgisi veya iş birliği için yazın. Mesajınız ekibimize ulaşır, kısa sürede dönüş yaparız.</p>
    </div>
</section>

<section class="section contact-section">
    <div class="container">
        <div class="contact-layout">
            <aside class="contact-panel">
                <div>
                    <p class="eyebrow">Mağaza</p>
                    <h2>Bize ulaşın</h2>
                    <p class="contact-panel__lead">Telefon, e-posta veya aşağıdaki form üzerinden doğrudan iletişime geçebilirsiniz.</p>
                </div>

                @if (is_array($siteSettings?->contact_info) && count($siteSettings->contact_info) > 0)
                    <div class="contact-branches">
                        @foreach ($siteSettings->contact_info as $branch)
                            <div class="contact-branch">
                                @if(!empty($branch['branch_name']))
                                    <p class="contact-branch__name">{{ $branch['branch_name'] }}</p>
                                @endif

                                @if(!empty($branch['address']))
                                    <div class="contact-channel">
                                        <span class="contact-channel__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
                                        </span>
                                        <span>
                                            <span class="contact-channel__label">Adres</span>
                                            <span class="contact-channel__value">{{ $branch['address'] }}</span>
                                        </span>
                                    </div>
                                @endif

                                @if(!empty($branch['phone']))
                                    <a class="contact-channel" href="tel:{{ preg_replace('/\s+/', '', $branch['phone']) }}">
                                        <span class="contact-channel__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7.2 3.8h2.2l1.2 3-1.5 1a12.4 12.4 0 0 0 5.9 5.9l1-1.5 3 1.2v2.2c0 .7-.6 1.3-1.3 1.3A14.7 14.7 0 0 1 5.9 5.1c0-.7.6-1.3 1.3-1.3z"/></svg>
                                        </span>
                                        <span>
                                            <span class="contact-channel__label">Telefon</span>
                                            <span class="contact-channel__value">{{ $formatPhone($branch['phone']) }}</span>
                                        </span>
                                    </a>
                                @endif

                                @if(!empty($branch['email']))
                                    <a class="contact-channel" href="mailto:{{ $branch['email'] }}">
                                        <span class="contact-channel__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="5.5" width="17" height="13" rx="1.5"/><path d="m4.5 7 7.5 6 7.5-6"/></svg>
                                        </span>
                                        <span>
                                            <span class="contact-channel__label">E-posta</span>
                                            <span class="contact-channel__value">{{ $branch['email'] }}</span>
                                        </span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif (is_string($siteSettings?->contact_info) && !empty($siteSettings->contact_info))
                    <p class="contact-fallback">{!! nl2br(e($siteSettings->contact_info)) !!}</p>
                @else
                    <p class="contact-fallback">{!! nl2br(e("İstanbul, Türkiye\ninfo@simgevip.com\n+90 212 000 00 00")) !!}</p>
                @endif

                @if ($siteSettings && !empty($siteSettings->social_media))
                    <div class="contact-social">
                        <p class="footer-title">Sosyal medya</p>
                        @if (is_array($siteSettings->social_media))
                            <div class="contact-social__list">
                                @foreach ($siteSettings->social_media as $sm)
                                    @if(!empty($sm['url']))
                                        @php
                                            $platform = strtolower($sm['platform'] ?? 'link');
                                        @endphp
                                        <a href="{{ $sm['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($platform) }}">
                                            {!! $socialIcons[$platform] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1"/><path d="M14 10a5 5 0 0 0-7.1-.1l-2 2a5 5 0 0 0 7.1 7.1l1.1-1"/></svg>' !!}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="contact-fallback">{!! nl2br(e($siteSettings->social_media)) !!}</p>
                        @endif
                    </div>
                @endif
            </aside>

            <form method="post" action="{{ route('contact.store') }}" class="contact-form">
                @csrf
                <div class="contact-form__head">
                    <h2>Mesaj bırakın</h2>
                    <p>Formu doldurun. Sipariş numaranız varsa konuya eklemeniz yanıtı hızlandırır.</p>
                </div>

                <div class="contact-form__grid">
                    <label class="field">
                        <span>Ad Soyad</span>
                        <input type="text" name="sender_name" value="{{ old('sender_name') }}" autocomplete="name" required>
                        @error('sender_name') <small class="error">{{ $message }}</small> @enderror
                    </label>

                    <label class="field">
                        <span>E-posta</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email') <small class="error">{{ $message }}</small> @enderror
                    </label>
                </div>

                <label class="field">
                    <span>Konu</span>
                    <input type="text" name="subject" value="{{ old('subject', request('urun') ? 'Ürün: '.request('urun') : '') }}" placeholder="Sipariş, ürün veya iş birliği">
                    @error('subject') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Mesaj</span>
                    <textarea name="message" rows="6" required placeholder="Size nasıl yardımcı olabileceğimizi yazın">{{ old('message') }}</textarea>
                    @error('message') <small class="error">{{ $message }}</small> @enderror
                </label>

                <button type="submit" class="btn btn--dark">Mesajı gönder</button>
                <p class="contact-form__note">Mesajınız yalnızca müşteri hizmetleri ekibine iletilir. Genellikle aynı gün içinde yanıtlarız.</p>
            </form>
        </div>

        @php
            $contactMaps = collect(is_array($siteSettings?->contact_info) ? $siteSettings->contact_info : [])
                ->map(function ($branch) {
                    if (! is_array($branch)) {
                        return null;
                    }
                    $url = \App\Support\SafeMapEmbed::url($branch['map_embed'] ?? null);
                    if (! $url) {
                        return null;
                    }
                    return [
                        'name' => $branch['branch_name'] ?? 'Mağaza',
                        'url' => $url,
                    ];
                })
                ->filter()
                ->values();
        @endphp

        @if ($contactMaps->isNotEmpty())
            <div class="contact-maps">
                @foreach ($contactMaps as $map)
                    <figure class="contact-map">
                        <figcaption>{{ $map['name'] }}</figcaption>
                        <iframe
                            src="{{ $map['url'] }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="{{ $map['name'] }} haritası"
                        ></iframe>
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="contact-topics">
            <a href="{{ route('faq.index') }}">
                <strong>Sorular</strong>
                <span>Sipariş, beden ve iade yanıtları</span>
            </a>
            <a href="{{ route('shop.index') }}">
                <strong>Koleksiyon</strong>
                <span>Ürünleri inceleyin, sorunuzu netleştirin</span>
            </a>
            <a href="{{ route('about') }}">
                <strong>Marka</strong>
                <span>Simge Vip Giyim hakkında</span>
            </a>
        </div>
    </div>
</section>
@endsection
