<x-filament::page class="filament-dashboard-page">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v=1">
    @endpush
    @php
        $user = \Filament\Facades\Filament::auth()->user();
        
        $totalProducts = \App\Models\Product::count();
        $featuredProducts = \App\Models\Product::where('is_featured', true)->count();
        $newSeasonProducts = \App\Models\Product::where('is_new_season', true)->count();
        
        $totalReservations = \App\Models\Reservation::count();
        $pendingReservations = \App\Models\Reservation::where('status', 'pending')->count();
        
        $totalMessages = \App\Models\Message::count();
        $unreadMessages = \App\Models\Message::where('is_read', false)->count();
        
        $totalCategories = \App\Models\Category::count();
        $rootCategories = \App\Models\Category::whereNull('parent_id')->count();

        $totalCustomers = \App\Models\Customer::count();
        $totalOrders = \App\Models\CustomerOrderRequest::count();
        $pendingOrders = \App\Models\CustomerOrderRequest::where('status', 'pending')->count();
        
        $totalSliders = \App\Models\Slider::count();
        $totalCampaigns = \App\Models\Campaign::count();
        
        $recentProducts = \App\Models\Product::with('category')->latest()->take(5)->get();
        $recentReservations = \App\Models\Reservation::latest()->take(5)->get();
        $recentMessages = \App\Models\Message::latest()->take(5)->get();
        
        $hour = (int) date('H');
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Günaydın';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'İyi Günler';
        } else {
            $greeting = 'İyi Akşamlar';
        }
    @endphp

    <div class="sv-dashboard">
        {{-- 1. HERO HOŞ GELDİNİZ BANNERI --}}
        <div class="sv-dash-hero">
            <div class="sv-dash-hero-content">
                <div class="sv-dash-hero-badge">
                    <span class="sv-dash-pulse-dot"></span>
                    <span class="sv-dash-badge-text">SimgeVIP Yönetim Merkezi</span>
                    <span class="sv-dash-hero-divider">·</span>
                    <span class="sv-dash-hero-date">{{ \Carbon\Carbon::now()->locale('tr')->isoFormat('D MMMM YYYY, dddd') }}</span>
                </div>
                <h1 class="sv-dash-hero-title">{{ $greeting }}, {{ $user->name ?? 'Admin' }}</h1>
                <p class="sv-dash-hero-desc">
                    E-ticaret mağazanızın ürün envanterini, müşteri taleplerini, mağaza rezervasyonlarını ve vitrin ayarlarını tek ekrandan profesyonelce yönetin.
                </p>
            </div>
            <div class="sv-dash-hero-actions">
                <a href="{{ route('filament.resources.products.create') }}" class="sv-dash-btn sv-dash-btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Yeni Ürün Ekle</span>
                </a>
                <a href="{{ url('/') }}" target="_blank" class="sv-dash-btn sv-dash-btn-secondary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    <span>Siteyi Canlı Gör</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
            </div>
        </div>

        {{-- 2. KPI METRİK KARTLARI --}}
        <div class="sv-dash-kpi-grid">
            {{-- Kart 1: Ürünler --}}
            <a href="{{ route('filament.resources.products.index') }}" class="sv-kpi-card sv-kpi-blue">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-blue">Envanter</span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ number_format($totalProducts, 0, ',', '.') }}</div>
                    <div class="sv-kpi-label">Katalogdaki Ürünler</div>
                </div>
                <div class="sv-kpi-footer">
                    <span><strong>{{ $newSeasonProducts }}</strong> Yeni Sezon · <strong>{{ $featuredProducts }}</strong> Öne Çıkan</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>

            {{-- Kart 2: Rezervasyonlar (Mağazada Ayır) --}}
            <a href="{{ route('filament.resources.reservations.index') }}" class="sv-kpi-card sv-kpi-amber">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-amber">
                        @if($pendingReservations > 0)
                            {{ $pendingReservations }} Bekleyen
                        @else
                            Aktif
                        @endif
                    </span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ $totalReservations }}</div>
                    <div class="sv-kpi-label">Mağazada Ayır İstekleri</div>
                </div>
                <div class="sv-kpi-footer">
                    <span>Müşteri mağaza ziyaret talepleri</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>

            {{-- Kart 3: Gelen Mesajlar --}}
            <a href="{{ route('filament.resources.messages.index') }}" class="sv-kpi-card sv-kpi-emerald">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-emerald">
                        @if($unreadMessages > 0)
                            {{ $unreadMessages }} Yeni
                        @else
                            İletişim
                        @endif
                    </span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ $totalMessages }}</div>
                    <div class="sv-kpi-label">İletişim Mesajları</div>
                </div>
                <div class="sv-kpi-footer">
                    <span><strong>{{ $unreadMessages }}</strong> okunmamış mesaj</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>

            {{-- Kart 4: Kategoriler --}}
            <a href="{{ route('filament.resources.categories.index') }}" class="sv-kpi-card sv-kpi-purple">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-purple">Navigasyon</span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ $totalCategories }}</div>
                    <div class="sv-kpi-label">Toplam Kategori</div>
                </div>
                <div class="sv-kpi-footer">
                    <span><strong>{{ $rootCategories }}</strong> Ana Menü başlığı</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\CustomerResource::getUrl('index') }}" class="sv-kpi-card sv-kpi-ink">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-ink">Hesaplar</span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ number_format($totalCustomers, 0, ',', '.') }}</div>
                    <div class="sv-kpi-label">Kayıtlı Müşteriler</div>
                </div>
                <div class="sv-kpi-footer">
                    <span>Müşteri hesapları</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\OrderRequestResource::getUrl('index') }}" class="sv-kpi-card sv-kpi-ink">
                <div class="sv-kpi-header">
                    <div class="sv-kpi-icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </div>
                    <span class="sv-kpi-tag sv-kpi-tag-ink">
                        @if($pendingOrders > 0)
                            {{ $pendingOrders }} Bekleyen
                        @else
                            Sipariş
                        @endif
                    </span>
                </div>
                <div class="sv-kpi-body">
                    <div class="sv-kpi-value">{{ number_format($totalOrders, 0, ',', '.') }}</div>
                    <div class="sv-kpi-label">Sipariş Talepleri</div>
                </div>
                <div class="sv-kpi-footer">
                    <span><strong>{{ $pendingOrders }}</strong> onay bekliyor</span>
                    <svg class="sv-kpi-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>
        </div>

        {{-- 3. ORTA ALAN: SON EKLENEN ÜRÜNLER & MAĞAZADA AYIR TALEPLERİ --}}
        <div class="sv-dash-side">
            {{-- Sol Kolon: Son Eklenen Ürünler --}}
            <div class="sv-dash-panel">
                <div class="sv-dash-panel-head">
                    <div class="sv-dash-panel-title-group">
                        <h2 class="sv-dash-panel-title">Son Eklenen Ürünler</h2>
                        <span class="sv-dash-panel-sub">Kataloğa eklenen son 5 ürün</span>
                    </div>
                    <a href="{{ route('filament.resources.products.index') }}" class="sv-dash-panel-link">
                        <span>Tümünü Gör</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>
                <div class="sv-dash-table-wrap">
                    <table class="sv-dash-table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Durum</th>
                                <th class="text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $p)
                                @php
                                    $img = null;
                                    if (!empty($p->images) && is_array($p->images) && count($p->images) > 0) {
                                        $img = asset('storage/' . ltrim($p->images[0], '/'));
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="sv-table-product-item">
                                            @if($img)
                                                <img src="{{ $img }}" alt="{{ $p->name }}" class="sv-table-product-thumb">
                                            @else
                                                <div class="sv-table-product-thumb sv-table-product-thumb-placeholder">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                                </div>
                                            @endif
                                            <div class="sv-table-product-info">
                                                <span class="sv-table-product-name" title="{{ $p->name }}">{{ \Illuminate\Support\Str::limit($p->name, 42) }}</span>
                                                <span class="sv-table-product-sku">SKU: {{ $p->sku ?: '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sv-dash-cat-badge">{{ $p->category?->name ?: 'Genel' }}</span>
                                    </td>
                                    <td>
                                        <span class="sv-table-price">₺{{ number_format($p->price, 2, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @if($p->is_featured)
                                            <span class="sv-status-pill sv-status-featured">Öne Çıkan</span>
                                        @elseif($p->is_new_season)
                                            <span class="sv-status-pill sv-status-new">Yeni Sezon</span>
                                        @else
                                            <span class="sv-status-pill sv-status-normal">Standart</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('filament.resources.products.edit', ['record' => $p]) }}" class="sv-table-action-btn">
                                            Düzenle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="sv-table-empty">Henüz ürün eklenmemiş.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sağ Kolon: talepler ve mesajlar --}}
            <div class="sv-dash-side">
            <div class="sv-dash-panel">
                <div class="sv-dash-panel-head">
                    <div class="sv-dash-panel-title-group">
                        <h2 class="sv-dash-panel-title">Mağazada Ayır Talepleri</h2>
                        <span class="sv-dash-panel-sub">Müşterilerin mağaza randevu ve ürün ayırma istekleri</span>
                    </div>
                    <a href="{{ route('filament.resources.reservations.index') }}" class="sv-dash-panel-link">
                        <span>Tümünü Gör</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>

                @if($recentReservations->count() > 0)
                    <div class="sv-dash-table-wrap">
                        <table class="sv-dash-table">
                            <thead>
                                <tr>
                                    <th>Müşteri</th>
                                    <th>Telefon</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th class="text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReservations as $res)
                                    <tr>
                                        <td>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $res->customer_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $res->email ?: 'E-posta belirtilmedi' }}</div>
                                        </td>
                                        <td>{{ $res->phone }}</td>
                                        <td>{{ $res->reservation_date ? $res->reservation_date->format('d.m.Y H:i') : $res->created_at->format('d.m.Y') }}</td>
                                        <td>
                                            @if($res->status === 'approved' || $res->status === 'confirmed')
                                                <span class="sv-status-pill sv-status-success">Onaylandı</span>
                                            @elseif($res->status === 'cancelled')
                                                <span class="sv-status-pill sv-status-danger">İptal</span>
                                            @else
                                                <span class="sv-status-pill sv-status-warning">Beklemede</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('filament.resources.reservations.edit', ['record' => $res]) }}" class="sv-table-action-btn">İncele</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="sv-dash-empty-state">
                        <div class="sv-dash-empty-icon">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                        <h3 class="sv-dash-empty-title">Henüz rezervasyon bulunmuyor</h3>
                        <p class="sv-dash-empty-desc">Müşteriler ürün detay sayfasından veya sepetten "Mağazada Ayır" butonuna tıkladığında oluşturulan randevu talepleri anlık olarak burada listelenir.</p>
                        <a href="{{ route('filament.resources.reservations.index') }}" class="sv-dash-btn sv-dash-btn-secondary" style="margin-top: 0.75rem;">
                            <span>Rezervasyon Listesine Git</span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="sv-dash-panel">
                <div class="sv-dash-panel-head">
                    <div class="sv-dash-panel-title-group">
                        <h2 class="sv-dash-panel-title">Son Mesajlar</h2>
                        <span class="sv-dash-panel-sub">İletişim formundan gelen son kayıtlar</span>
                    </div>
                    <a href="{{ route('filament.resources.messages.index') }}" class="sv-dash-panel-link">
                        <span>Tümünü Gör</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>
                @if($recentMessages->count() > 0)
                    <div class="sv-msg-list">
                        @foreach($recentMessages as $msg)
                            <a href="{{ route('filament.resources.messages.edit', ['record' => $msg]) }}" class="sv-msg-row">
                                <span class="sv-msg-dot {{ $msg->is_read ? '' : 'is-unread' }}"></span>
                                <span class="sv-msg-body">
                                    <span class="sv-msg-name">{{ $msg->sender_name ?: 'İsimsiz' }}</span>
                                    <span class="sv-msg-subject">{{ \Illuminate\Support\Str::limit($msg->subject ?: $msg->message, 64) }}</span>
                                </span>
                                <span class="sv-msg-time">{{ $msg->created_at?->format('d.m.Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="sv-dash-empty-state">
                        <h3 class="sv-dash-empty-title">Mesaj yok</h3>
                        <p class="sv-dash-empty-desc">İletişim formundan gelen mesajlar burada görünür.</p>
                    </div>
                @endif
            </div>
            </div>
        </div>

        {{-- 4. HIZLI ERİŞİM KISAYOLLARI VE VİTRİN YÖNETİMİ --}}
        <div class="sv-dash-shortcuts-card">
            <div class="sv-dash-shortcuts-head">
                <div>
                    <h3 class="sv-dash-shortcuts-title">Hızlı Vitrin & Yönetim Kısayolları</h3>
                    <p class="sv-dash-shortcuts-sub">Sık kullanılan yönetim modüllerine hızlı geçiş</p>
                </div>
            </div>
            <div class="sv-dash-shortcuts-grid">
                <a href="{{ route('filament.resources.sliders.index') }}" class="sv-shortcut-item">
                    <div class="sv-shortcut-icon sv-shortcut-purple">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </div>
                    <div class="sv-shortcut-content">
                        <span class="sv-shortcut-title">Slider & Banner</span>
                        <span class="sv-shortcut-meta">{{ $totalSliders }} aktif vitrin slaytı</span>
                    </div>
                </a>

                <a href="{{ route('filament.resources.menus.index') }}" class="sv-shortcut-item">
                    <div class="sv-shortcut-icon sv-shortcut-blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </div>
                    <div class="sv-shortcut-content">
                        <span class="sv-shortcut-title">Menü & Navigasyon</span>
                        <span class="sv-shortcut-meta">Sürükle-bırak menü sıralaması</span>
                    </div>
                </a>

                <a href="{{ route('filament.resources.campaigns.index') }}" class="sv-shortcut-item">
                    <div class="sv-shortcut-icon sv-shortcut-amber">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    </div>
                    <div class="sv-shortcut-content">
                        <span class="sv-shortcut-title">Kampanyalar & İndirim</span>
                        <span class="sv-shortcut-meta">{{ $totalCampaigns }} aktif kampanya</span>
                    </div>
                </a>

                <a href="{{ route('filament.resources.settings.index') }}" class="sv-shortcut-item">
                    <div class="sv-shortcut-icon sv-shortcut-emerald">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    </div>
                    <div class="sv-shortcut-content">
                        <span class="sv-shortcut-title">Genel Ayarlar</span>
                        <span class="sv-shortcut-meta">Logo, WhatsApp, İletişim</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-filament::page>
