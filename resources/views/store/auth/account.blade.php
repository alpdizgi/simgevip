@extends('store.layout')
@section('title', 'Hesabım')
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-dashboard')
@push('head')<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ filemtime(public_path('css/customer-dashboard.css')) }}">@endpush
@section('content')
<div class="customer-dashboard__page container">
    {{-- Üst Hoş Geldiniz Bannerı --}}
    <div class="dashboard-hero">
        <div class="dashboard-hero__profile">
            <div class="dashboard-hero__avatar">
                {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
            </div>
            <div class="dashboard-hero__details">
                <div class="dashboard-hero__badge">SimgeVIP Müşteri Hesabı</div>
                <h1 class="dashboard-hero__title">Merhaba, {{ $customer->name }}</h1>
                <div class="dashboard-hero__meta">
                    @if($customer->email || $customer->login_email)
                        <span class="dashboard-hero__meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            {{ $customer->email ?: $customer->login_email }}
                        </span>
                    @endif
                    @if($customer->phone || $customer->login_phone)
                        <span class="dashboard-hero__meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            {{ $customer->phone ?: $customer->login_phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('customer.logout') }}">
            @csrf
            <button class="dashboard-hero__logout" type="submit" title="Hesaptan Çıkış Yap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Çıkış Yap
            </button>
        </form>
    </div>

    {{-- 4 Hızlı Sayaç Kartı --}}
    <div class="dashboard-stat-grid">
        <a href="#cart" class="dashboard-stat-card" data-tab="cart">
            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
            <div class="dashboard-stat-card__body">
                <strong>{{ $cart['count'] }}</strong>
                <span>Sepetteki Ürün</span>
            </div>
            <span class="dashboard-stat-card__arrow">→</span>
        </a>

        <a href="#orders" class="dashboard-stat-card" data-tab="orders">
            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--orders">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <div class="dashboard-stat-card__body">
                <strong>{{ $orderRequests->count() }}</strong>
                <span>Sipariş Talebi</span>
            </div>
            <span class="dashboard-stat-card__arrow">→</span>
        </a>

        <a href="#reservations" class="dashboard-stat-card" data-tab="reservations">
            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--reservations">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="dashboard-stat-card__body">
                <strong>{{ $reservations->count() }}</strong>
                <span>Mağazada Ayırma</span>
            </div>
            <span class="dashboard-stat-card__arrow">→</span>
        </a>

        <a href="#favorites" class="dashboard-stat-card" data-tab="favorites">
            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--favorites">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <div class="dashboard-stat-card__body">
                <strong>{{ $favorites->count() }}</strong>
                <span>Favori Ürün</span>
            </div>
            <span class="dashboard-stat-card__arrow">→</span>
        </a>
    </div>

    {{-- Ana Düzen: Sol Menü & Sağ İçerik --}}
    <div class="customer-dashboard__layout">
        {{-- Sol Menü / Sekmeler --}}
        <nav class="customer-dashboard__nav" aria-label="Hesap bölümleri">
            <a href="#cart" class="dashboard-nav-item active" data-tab="cart">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    Sepetim
                </span>
                <span class="dashboard-nav-item__badge">{{ $cart['count'] }}</span>
            </a>

            <a href="#orders" class="dashboard-nav-item" data-tab="orders">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Sipariş Taleplerim
                </span>
                <span class="dashboard-nav-item__badge">{{ $orderRequests->count() }}</span>
            </a>

            <a href="#reservations" class="dashboard-nav-item" data-tab="reservations">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Mağazada Ayırdıklarım
                </span>
                <span class="dashboard-nav-item__badge">{{ $reservations->count() }}</span>
            </a>

            <a href="#favorites" class="dashboard-nav-item" data-tab="favorites">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    Favorilerim
                </span>
                <span class="dashboard-nav-item__badge">{{ $favorites->count() }}</span>
            </a>

            <a href="#support" class="dashboard-nav-item" data-tab="support">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Destek Taleplerim
                </span>
                <span class="dashboard-nav-item__badge">{{ $supportTickets->count() }}</span>
            </a>

            <a href="#profile" class="dashboard-nav-item" data-tab="profile">
                <span class="dashboard-nav-item__left">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Hesap Bilgilerim
                </span>
            </a>
        </nav>

        {{-- Sağ İçerik Alanı --}}
        <div class="customer-dashboard__content">

            {{-- 1. SEPETİM --}}
            <section id="cart" class="customer-dashboard__section dashboard-tab-panel active">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">01 / SEPET</span>
                        <h2>Sepetim</h2>
                    </div>
                    <a href="{{ route('shop.index') }}" class="dashboard-head-link">Alışverişe Devam Et →</a>
                </div>

                @if ($cart['count'] > 0)
                    <div class="dashboard-cart-list">
                        @foreach ($cart['items'] as $item)
                            <article class="dashboard-cart-row">
                                <a href="{{ $item['url'] ?? route('shop.index') }}" class="dashboard-cart-row__image">
                                    <img src="{{ $item['image'] ?? asset('images/store/placeholder.svg') }}" alt="{{ $item['name'] }}" loading="lazy">
                                </a>
                                <div class="dashboard-cart-row__info">
                                    <a href="{{ $item['url'] ?? route('shop.index') }}" class="dashboard-cart-row__title">{{ $item['name'] }}</a>
                                    <div class="dashboard-cart-row__badges">
                                        @if(!empty($item['color']))
                                            <span class="dashboard-chip">Renk: {{ $item['color'] }}</span>
                                        @endif
                                        @if(!empty($item['size']))
                                            <span class="dashboard-chip">Beden: {{ $item['size'] }}</span>
                                        @endif
                                    </div>
                                    <div class="dashboard-cart-row__price">{{ $item['line_total_formatted'] }}</div>
                                    @if (!empty($item['nth_discount_note']))
                                        <div class="dashboard-cart-row__note">{{ $item['nth_discount_note'] }}</div>
                                    @elseif (!empty($item['nth_pending_note']))
                                        <div class="dashboard-cart-row__note">{{ $item['nth_pending_note'] }}</div>
                                    @endif
                                </div>
                                <div class="dashboard-cart-row__actions">
                                    <form method="POST" action="{{ route('customer.cart.update') }}" class="dashboard-qty-form">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                        <div class="dashboard-qty-control">
                                            <button type="button" class="qty-btn minus" onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                            <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="20" onchange="this.form.submit()">
                                            <button type="button" class="qty-btn plus" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('customer.cart.update') }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                        <input type="hidden" name="qty" value="0">
                                        <button class="dashboard-remove-btn" type="submit" title="Sepetten Kaldır">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Kaldır
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="dashboard-cart-summary">
                        <div class="dashboard-cart-summary__row">
                            <span>Toplam Ürün Sayısı:</span>
                            <strong>{{ $cart['count'] }} Adet</strong>
                        </div>
                        <div class="dashboard-cart-summary__row dashboard-cart-summary__row--grand">
                            <span>Toplam Tutar:</span>
                            <strong>{{ $cart['grand_total_formatted'] }}</strong>
                        </div>
                        <div class="dashboard-whatsapp-banner">
                            <div class="dashboard-whatsapp-banner__text">
                                <strong>WhatsApp ile Kolay Sipariş</strong>
                                <p>Sepetinizdeki ürünler doğrudan butik WhatsApp hattımıza iletilir, temsilcimiz anında onaylar.</p>
                            </div>
                            <form method="POST" action="{{ route('customer.orders.create') }}">
                                @csrf
                                <input type="hidden" name="source" value="cart">
                                <button class="dashboard-whatsapp-btn" type="submit">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
                                    WhatsApp ile Sipariş Talebi Gönder →
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-state__icon">🛒</div>
                        <h3>Sepetiniz Henüz Boş</h3>
                        <p>Beğendiğiniz ürünleri sepete ekleyerek buradan kolayca sipariş oluşturabilirsiniz.</p>
                        <a href="{{ route('shop.index') }}" class="dashboard-empty-state__btn">Koleksiyonu Keşfet →</a>
                    </div>
                @endif
            </section>

            {{-- 2. SİPARİŞ TALEPLERİM --}}
            <section id="orders" class="customer-dashboard__section dashboard-tab-panel">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">02 / TALEPLER</span>
                        <h2>Sipariş Taleplerim</h2>
                    </div>
                </div>
                <p class="customer-dashboard__note">Sipariş talepleriniz mağaza danışmanımız tarafından onaylandıktan sonra hazırlanıp kargoya verilir.</p>

                @forelse ($orderRequests as $order)
                    <div class="dashboard-order-card">
                        <div class="dashboard-order-card__header">
                            <div>
                                <div class="dashboard-order-card__number">Talep #{{ $order->id }}</div>
                                <div class="dashboard-order-card__date">
                                    {{ $order->created_at->format('d.m.Y — H:i') }} · 
                                    <span class="dashboard-chip dashboard-chip--sm">{{ $order->source === 'cart' ? 'Sepet Siparişi' : 'Hızlı Sipariş' }}</span>
                                </div>
                            </div>
                            <div class="dashboard-order-card__status-wrap">
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'Beklemede', 'class' => 'warning'],
                                        'confirmed' => ['label' => 'Onaylandı', 'class' => 'success'],
                                        'shipped' => ['label' => 'Kargoya Verildi', 'class' => 'info'],
                                        'cancel_requested' => ['label' => 'İptal İsteği Alındı', 'class' => 'danger'],
                                        'cancelled' => ['label' => 'İptal Edildi', 'class' => 'muted'],
                                    ];
                                    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'muted'];
                                @endphp
                                <span class="dashboard-badge dashboard-badge--{{ $st['class'] }}">{{ $st['label'] }}</span>
                            </div>
                        </div>

                        {{-- Ürünler Listesi --}}
                        <div class="dashboard-order-card__items">
                            @foreach ($order->items ?? [] as $item)
                                <div class="dashboard-order-item">
                                    <div class="dashboard-order-item__thumb">
                                        <img src="{{ $item['image'] ?? asset('images/store/placeholder.svg') }}" alt="{{ $item['name'] ?? 'Ürün' }}">
                                    </div>
                                    <div class="dashboard-order-item__info">
                                        <div class="dashboard-order-item__name">{{ $item['name'] ?? 'Ürün' }}</div>
                                        <div class="dashboard-order-item__variant">
                                            @if(!empty($item['color'])) <span>Renk: {{ $item['color'] }}</span> @endif
                                            @if(!empty($item['size'])) <span>Beden: {{ $item['size'] }}</span> @endif
                                            <span>Adet: {{ $item['qty'] ?? 1 }}</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-order-item__price">
                                        {{ isset($item['price']) ? number_format((float)$item['price'], 2, ',', '.') . ' TL' : '' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="dashboard-order-card__footer">
                            <div class="dashboard-order-card__total">
                                <span>Toplam Tutar:</span>
                                <strong>{{ number_format((float) $order->total, 2, ',', '.') }} TL</strong>
                            </div>

                            @if($order->status === 'pending')
                                <details class="dashboard-cancel-details">
                                    <summary class="dashboard-cancel-summary">
                                        <span>⚠️ Siparişi İptal Et</span>
                                    </summary>
                                    <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="dashboard-cancel-form">
                                        @csrf @method('PATCH')
                                        <label for="cancel_reason_{{ $order->id }}">İptal Gerekçeniz:</label>
                                        <textarea id="cancel_reason_{{ $order->id }}" name="cancel_reason" rows="2" required placeholder="İptal talebinizin nedenini kısaca açıklayınız..."></textarea>
                                        <button type="submit" class="dashboard-btn-danger">İptal Talebini Onayla</button>
                                    </form>
                                </details>
                            @elseif($order->status === 'cancel_requested' && $order->cancel_reason)
                                <div class="dashboard-alert dashboard-alert--warning">
                                    <strong>İptal Talebiniz İnceleniyor:</strong> {{ $order->cancel_reason }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-state__icon">📦</div>
                        <h3>Henüz Sipariş Talebiniz Yok</h3>
                        <p>Verdiğiniz sipariş talepleri ve durumları burada listelenecektir.</p>
                    </div>
                @endforelse
            </section>

            {{-- 3. MAĞAZADA AYIRDIKLARIM --}}
            <section id="reservations" class="customer-dashboard__section dashboard-tab-panel">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">03 / MAĞAZA</span>
                        <h2>Mağazada Ayırdıklarım</h2>
                    </div>
                </div>
                <p class="customer-dashboard__note">Mağazamızda adınıza rezerve edilen ürünleri ve randevularınızı buradan takip edebilirsiniz.</p>

                @forelse ($reservations as $reservation)
                    <div class="dashboard-reservation-card">
                        <div class="dashboard-reservation-card__thumb">
                            @if($reservation->product)
                                <img src="{{ $reservation->product->image_url }}" alt="{{ $reservation->product->name }}">
                            @else
                                <div class="dashboard-placeholder-thumb">🏷️</div>
                            @endif
                        </div>
                        <div class="dashboard-reservation-card__details">
                            <div class="dashboard-reservation-card__top">
                                <div>
                                    <h3 class="dashboard-reservation-card__title">
                                        {{ $reservation->product->name ?? 'Mağazada Ürün Ayırma Talebi' }}
                                    </h3>
                                    <div class="dashboard-reservation-card__meta">
                                        <span>Talep Tarihi: {{ $reservation->created_at->format('d.m.Y H:i') }}</span>
                                        @if($reservation->reservation_date)
                                            <span class="dashboard-chip dashboard-chip--date">
                                                📅 Randevu: {{ $reservation->reservation_date->format('d.m.Y — H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="dashboard-reservation-card__badge-wrap">
                                    @php
                                        $resStatusMap = [
                                            'pending' => ['label' => 'Beklemede', 'class' => 'warning'],
                                            'confirmed' => ['label' => 'Mağazada Ayrıldı', 'class' => 'success'],
                                            'cancelled' => ['label' => 'İptal Edildi', 'class' => 'muted'],
                                        ];
                                        $rst = $resStatusMap[$reservation->status] ?? ['label' => $reservation->status, 'class' => 'muted'];
                                    @endphp
                                    <span class="dashboard-badge dashboard-badge--{{ $rst['class'] }}">{{ $rst['label'] }}</span>
                                </div>
                            </div>

                            <div class="dashboard-reservation-card__tags">
                                @if($reservation->selected_color)
                                    <span class="dashboard-chip">Renk: {{ $reservation->selected_color }}</span>
                                @endif
                                @if($reservation->selected_size)
                                    <span class="dashboard-chip">Beden: {{ $reservation->selected_size }}</span>
                                @endif
                                @if($reservation->is_admin_hold)
                                    <span class="dashboard-chip dashboard-chip--vip">👑 Mağaza Özel Ayrımı</span>
                                @endif
                            </div>

                            @if($reservation->clean_notes)
                                <div class="dashboard-reservation-card__note">
                                    <strong>Not:</strong> {{ $reservation->clean_notes }}
                                </div>
                            @endif

                            @if($reservation->status === 'pending')
                                <div class="dashboard-reservation-card__actions">
                                    <form method="POST" action="{{ route('customer.reservations.cancel', $reservation) }}">
                                        @csrf @method('PATCH')
                                        <button class="dashboard-btn-outline-danger" type="submit">Ayırma Talebini İptal Et</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-state__icon">🏷️</div>
                        <h3>Henüz Ayırma Talebiniz Yok</h3>
                        <p>Beğendiğiniz ürünleri mağazamızda denemek üzere ücretsiz olarak adınıza rezerve edebilirsiniz.</p>
                    </div>
                @endforelse
            </section>

            {{-- 4. FAVORİLERİM --}}
            <section id="favorites" class="customer-dashboard__section dashboard-tab-panel">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">04 / SEÇTİKLERİM</span>
                        <h2>Favorilerim</h2>
                    </div>
                    <a href="{{ route('shop.index', ['favoriler' => 1]) }}" class="dashboard-head-link">Tümünü Keşfet →</a>
                </div>

                @if ($favorites->isNotEmpty())
                    <div class="dashboard-favorites-grid">
                        @foreach ($favorites as $product)
                            <article class="dashboard-fav-card">
                                <a href="{{ route('product.show', $product->slug) }}" class="dashboard-fav-card__image-link">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                                </a>
                                <div class="dashboard-fav-card__body">
                                    <a href="{{ route('product.show', $product->slug) }}" class="dashboard-fav-card__title">{{ $product->name }}</a>
                                    <div class="dashboard-fav-card__price">
                                        {{ $product->formatted_discounted_price ?: $product->formatted_price }}
                                    </div>
                                    <div class="dashboard-fav-card__actions">
                                        <a href="{{ route('product.show', $product->slug) }}" class="dashboard-fav-card__view-btn">Ürünü İncele</a>
                                        <form method="POST" action="{{ route('customer.favorites.remove', $product) }}">
                                            @csrf @method('DELETE')
                                            <button class="dashboard-fav-card__remove-btn" type="submit" title="Favorilerden Kaldır">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-state__icon">❤️</div>
                        <h3>Henüz Favoriniz Yok</h3>
                        <p>Koleksiyonumuzdaki beğendiğiniz parçaları kalp ikonuna tıklayarak favorilerinize kaydedebilirsiniz.</p>
                        <a href="{{ route('shop.index') }}" class="dashboard-empty-state__btn">Ürünleri İncele →</a>
                    </div>
                @endif
            </section>

            {{-- 5. DESTEK TALEPLERİM --}}
            <section id="support" class="customer-dashboard__section dashboard-tab-panel">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">05 / DESTEK</span>
                        <h2>Destek Taleplerim</h2>
                    </div>
                </div>
                <p class="customer-dashboard__note">Sipariş, ürün, mağazada ayırma veya hesabınızla ilgili sorularınızı ekibimize iletin.</p>

                @if ($supportTickets->isNotEmpty())
                    <div class="dashboard-support-list">
                        @foreach ($supportTickets as $ticket)
                            <a class="dashboard-support-row" href="{{ route('customer.support.show', $ticket) }}">
                                <div class="dashboard-support-row__icon">💬</div>
                                <div class="dashboard-support-row__body">
                                    <div class="dashboard-support-row__title">#{{ $ticket->id }} — {{ $ticket->subject }}</div>
                                    <div class="dashboard-support-row__meta">
                                        {{ $ticket->updated_at->format('d.m.Y H:i') }}
                                        @if ($ticket->customer_read_at === null && $ticket->messages()->where('author_type', 'admin')->exists())
                                            <span class="dashboard-chip dashboard-chip--new">● Yeni Yanıt</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="dashboard-support-row__status">
                                    @php
                                        $ticketMap = [
                                            'open' => ['label' => 'Açık', 'class' => 'warning'],
                                            'waiting_customer' => ['label' => 'Yanıtınız Bekleniyor', 'class' => 'info'],
                                            'resolved' => ['label' => 'Çözüldü', 'class' => 'success'],
                                            'closed' => ['label' => 'Kapalı', 'class' => 'muted'],
                                        ];
                                        $tst = $ticketMap[$ticket->status] ?? ['label' => $ticket->status, 'class' => 'muted'];
                                    @endphp
                                    <span class="dashboard-badge dashboard-badge--{{ $tst['class'] }}">{{ $tst['label'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="dashboard-support-form-card">
                    <h3>Yeni Destek Talebi Oluştur</h3>
                    <form class="dashboard-form" method="POST" action="{{ route('customer.support.store') }}">
                        @csrf
                        <div class="dashboard-form__row">
                            <label>Konu Kategorisi
                                <select name="category" required>
                                    @foreach (['general' => 'Genel Soru / Bilgi', 'order' => 'Sipariş Talebi', 'product' => 'Ürün Hakkında', 'reservation' => 'Mağazada Ayırma / Randevu', 'account' => 'Hesap İşlemleri'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        @error('category')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                        <div class="dashboard-form__row">
                            <label>Başlık / Konu
                                <input name="subject" value="{{ old('subject') }}" minlength="5" maxlength="180" required placeholder="Kısaca iletmek istediğiniz konuyu yazın...">
                            </label>
                        </div>
                        @error('subject')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                        <div class="dashboard-form__row">
                            <label>Mesajınız
                                <textarea name="body" rows="4" minlength="10" maxlength="10000" required placeholder="Size yardımcı olabilmemiz için sorunuzun detaylarını belirtin...">{{ old('body') }}</textarea>
                            </label>
                        </div>
                        @error('body')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                        <button class="customer-dashboard__primary" type="submit">Talebi Gönder</button>
                    </form>
                </div>
            </section>

            {{-- 6. HESAP BİLGİLERİM --}}
            <section id="profile" class="customer-dashboard__section dashboard-tab-panel">
                <div class="customer-dashboard__section-head">
                    <div>
                        <span class="dashboard-section-tag">06 / HESAP</span>
                        <h2>Hesap Bilgilerim</h2>
                    </div>
                </div>

                <div class="dashboard-profile-grid">
                    {{-- Profil Güncelleme --}}
                    <div class="dashboard-profile-box">
                        <h3>Kişisel Bilgiler</h3>
                        <form class="dashboard-form" method="POST" action="{{ route('customer.profile.update') }}">
                            @csrf @method('PATCH')
                            <div class="dashboard-form__row">
                                <label>Ad Soyad
                                    <input name="name" value="{{ old('name', $customer->name) }}" required maxlength="120">
                                </label>
                            </div>
                            @error('name')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                            <div class="dashboard-form__row">
                                <label>Telefon Numarası
                                    <input type="tel" name="phone" value="{{ old('phone', $customer->phone ?: $customer->login_phone) }}" maxlength="40" autocomplete="tel" placeholder="05xx xxx xx xx">
                                </label>
                            </div>
                            @error('phone')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                            <div class="dashboard-form__row">
                                <label>Kayıtlı E-posta
                                    <input type="email" value="{{ $customer->email ?: $customer->login_email ?: '—' }}" disabled style="background:#f9fafb; cursor:not-allowed; opacity:0.8;">
                                </label>
                            </div>

                            <div class="dashboard-form__row">
                                <label>Teslimat / İletişim Adresi
                                    <textarea name="address" rows="3" maxlength="2000" placeholder="Açık adresinizi yazınız...">{{ old('address', $customer->address) }}</textarea>
                                </label>
                            </div>
                            @error('address')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                            <button class="customer-dashboard__primary" type="submit">Bilgileri Güncelle</button>
                        </form>
                    </div>

                    {{-- Şifre Güncelleme --}}
                    <div class="dashboard-profile-box">
                        <h3>Şifre Değiştir</h3>
                        <form class="dashboard-form" method="POST" action="{{ route('customer.password.update') }}">
                            @csrf @method('PATCH')
                            <div class="dashboard-form__row">
                                <label>Mevcut Şifreniz
                                    <input type="password" name="current_password" autocomplete="current-password" required placeholder="••••••••">
                                </label>
                            </div>
                            @error('current_password')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                            <div class="dashboard-form__row">
                                <label>Yeni Şifre
                                    <input type="password" name="password" autocomplete="new-password" minlength="8" required placeholder="En az 8 karakter">
                                </label>
                            </div>
                            @error('password')<small class="customer-dashboard__error">{{ $message }}</small>@enderror

                            <div class="dashboard-form__row">
                                <label>Yeni Şifre Tekrarı
                                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required placeholder="••••••••">
                                </label>
                            </div>

                            <button class="customer-dashboard__primary" type="submit">Şifremi Değiştir</button>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

{{-- Sekme Değiştirici JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.dashboard-nav-item, .dashboard-stat-card');
    const panels = document.querySelectorAll('.dashboard-tab-panel');

    function activateTab(tabId) {
        if (!tabId) tabId = 'cart';
        tabId = tabId.replace('#', '');

        const targetPanel = document.getElementById(tabId);
        if (!targetPanel) return;

        // Sekme butonlarını güncelle
        document.querySelectorAll('.dashboard-nav-item').forEach(item => {
            if (item.dataset.tab === tabId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Panelleri güncelle
        panels.forEach(panel => {
            if (panel.id === tabId) {
                panel.classList.add('active');
            } else {
                panel.classList.remove('active');
            }
        });
    }

    // Tıklama dinleyicileri
    navItems.forEach(item => {
        item.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const tabId = href.substring(1);
                history.pushState(null, null, '#' + tabId);
                activateTab(tabId);
                window.scrollTo({
                    top: document.querySelector('.customer-dashboard__layout').offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Hash varsa o sekmeyi aç
    if (window.location.hash) {
        activateTab(window.location.hash);
    }
});
</script>
@endsection
