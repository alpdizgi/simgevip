<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakım Aşamasında | {{ $settings->site_name ?? 'Simge Giyim' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f6f2;
            color: #221f1d;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .maintenance-card {
            width: 100%;
            max-width: 580px;
            background: #ffffff;
            border: 1px solid #e8e2d9;
            border-radius: 12px;
            box-shadow: 0 16px 48px rgba(35, 28, 20, 0.05);
            padding: clamp(2.25rem, 5vw, 3.5rem);
            text-align: center;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .brand-logo img {
            max-height: 46px;
            width: auto;
            object-fit: contain;
        }

        .brand-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #1e1b19;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            background: #f4ede4;
            color: #6d5b4b;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-radius: 999px;
            margin-bottom: 1.75rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background-color: #bfa181;
            border-radius: 50%;
        }

        .title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(1.85rem, 4vw, 2.35rem);
            font-weight: 600;
            color: #1e1b19;
            line-height: 1.2;
            letter-spacing: -0.015em;
            margin-bottom: 1rem;
        }

        .description {
            font-size: 0.95rem;
            color: #635b53;
            line-height: 1.7;
            max-width: 480px;
            margin: 0 auto 2rem;
        }

        .divider {
            width: 48px;
            height: 1px;
            background: #ded7ce;
            margin: 0 auto 2rem;
        }

        .contact-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            background: #1e1b19;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.8rem 1.6rem;
            border-radius: 6px;
            transition: background 0.15s ease, transform 0.1s ease;
            width: 100%;
            max-width: 320px;
        }

        .btn-whatsapp:hover {
            background: #3e3732;
        }

        .btn-whatsapp svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
            flex-shrink: 0;
        }

        .contact-meta {
            font-size: 0.84rem;
            color: #7a7269;
        }

        .contact-meta a {
            color: #1e1b19;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .social-section {
            border-top: 1px solid #ebe5dc;
            padding-top: 1.75rem;
            margin-top: 1.75rem;
        }

        .social-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8c8277;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #e2dbd1;
            border-radius: 50%;
            color: #49423c;
            background: #ffffff;
            text-decoration: none;
            transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
        }

        .social-btn:hover {
            border-color: #1e1b19;
            color: #1e1b19;
            background: #f7f3ee;
        }

        .social-btn svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        .footer-note {
            margin-top: 2rem;
            font-size: 0.76rem;
            color: #9d9489;
        }
    </style>
</head>
<body>
    @php
        $siteName = $settings->site_name ?? 'Simge Giyim';
        $contact = (isset($settings->contact_info) && is_array($settings->contact_info) && !empty($settings->contact_info[0])) 
            ? $settings->contact_info[0] 
            : [];
        $phone = $contact['phone'] ?? null;
        $email = $contact['email'] ?? null;
        $whatsappUrl = isset($settings) ? $settings->whatsapp_url : null;
        
        $rawSocial = (isset($settings->social_media) && is_array($settings->social_media)) ? $settings->social_media : [];
        $validSocial = [];
        foreach ($rawSocial as $s) {
            $url = trim($s['url'] ?? '');
            if (!empty($url) && $url !== '#' && $url !== '/' && !str_ends_with($url, 'simgegiyim.com.tr/')) {
                $validSocial[] = [
                    'platform' => strtolower(trim($s['platform'] ?? '')),
                    'url' => $url,
                ];
            }
        }
    @endphp

    <main class="maintenance-card">
        <header>
            <div class="brand-logo">
                @if(!empty($settings->logo_path) && file_exists(public_path('storage/' . $settings->logo_path)))
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $siteName }}">
                @else
                    <span class="brand-text">{{ $siteName }}</span>
                @endif
            </div>

            <div>
                <span class="status-badge">
                    <span class="status-dot"></span>
                    Bakım Modu
                </span>
            </div>
        </header>

        <h1 class="title">Çok Yakında Buradayız</h1>

        <p class="description">
            {{ !empty($message) ? $message : 'Yeni sezon koleksiyonlarımız ve alışveriş deneyiminizi kusursuzlaştırmak için sitemizi güncelliyoruz. Kısa süre içinde tekrar sizlerle birlikteyiz.' }}
        </p>

        <div class="divider"></div>

        @if($whatsappUrl || $phone || $email)
            <div class="contact-actions">
                @if($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
                        <svg viewBox="0 0 24 24"><path d="M12.04 4.2A7.7 7.7 0 0 0 5.4 16.1L4.4 19.6l3.6-1a7.7 7.7 0 0 0 11.5-6.6 7.7 7.7 0 0 0-7.46-7.8zm4.48 10.9c-.2.55-1.14 1.05-1.58 1.1-.4.05-.9.08-1.46-.09-.33-.1-.76-.25-1.31-.49-2.3-1-3.8-3.32-3.92-3.48-.11-.15-.93-1.24-.93-2.37 0-1.12.58-1.67.79-1.9.2-.22.44-.28.59-.28h.42c.14 0 .32-.05.5.38.2.46.64 1.58.7 1.7.06.11.1.25.02.4-.08.15-.12.24-.24.37-.12.13-.25.29-.36.39-.12.11-.24.23-.1.45.14.22.62 1.02 1.33 1.65.91.81 1.68 1.07 1.92 1.19.24.12.38.1.52-.06.14-.16.6-.7.76-.94.16-.24.32-.2.54-.12.22.08 1.4.66 1.64.78.24.12.4.18.46.28.06.1.06.58-.14 1.13z"/></svg>
                        <span>WhatsApp ile İletişim</span>
                    </a>
                @endif

                <div class="contact-meta">
                    @if($phone)
                        <span>Tel: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></span>
                    @endif
                    @if($phone && $email)
                        <span style="margin: 0 0.35rem; opacity: 0.5;">·</span>
                    @endif
                    @if($email)
                        <span>E-posta: <a href="mailto:{{ $email }}">{{ $email }}</a></span>
                    @endif
                </div>
            </div>
        @endif

        @if(!empty($validSocial))
            <section class="social-section" aria-label="Sosyal Medya">
                <div class="social-title">Bizi Takip Edin</div>
                <div class="social-links">
                    @foreach($validSocial as $s)
                        <a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer" class="social-btn" title="{{ ucfirst($s['platform']) }}" aria-label="{{ ucfirst($s['platform']) }}">
                            @if($s['platform'] === 'instagram')
                                <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            @elseif($s['platform'] === 'facebook')
                                <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            @elseif($s['platform'] === 'pinterest')
                                <svg viewBox="0 0 24 24"><path d="M12 0a12 12 0 0 0-4.37 23.18c-.07-.98-.13-2.5.03-3.57.14-.98.93-6.52.93-6.52s-.24-.48-.24-1.18c0-1.1.64-1.93 1.44-1.93.68 0 1 .51 1 1.12 0 .68-.43 1.7-.66 2.65-.19.79.4 1.44 1.18 1.44 1.41 0 2.5-1.49 2.5-3.64 0-1.9-1.37-3.23-3.32-3.23-2.26 0-3.59 1.7-3.59 3.45 0 .68.26 1.42.59 1.82.07.08.08.15.06.23-.06.26-.2.83-.23.95-.04.16-.13.19-.3.12-1.12-.52-1.82-2.16-1.82-3.48 0-2.83 2.06-5.43 5.93-5.43 3.12 0 5.54 2.22 5.54 5.19 0 3.1-1.95 5.59-4.66 5.59-.91 0-1.77-.47-2.06-1.03l-.56 2.14c-.2.79-.75 1.77-1.12 2.37A11.98 11.98 0 0 0 12 24c6.63 0 12-5.37 12-12S18.63 0 12 0z"/></svg>
                            @elseif($s['platform'] === 'youtube')
                                <svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            @elseif($s['platform'] === 'twitter' || $s['platform'] === 'x')
                                <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            @elseif($s['platform'] === 'tiktok')
                                <svg viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ $siteName }}. Tüm hakları saklıdır.
        </div>
    </main>
</body>
</html>
