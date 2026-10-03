<x-filament::page class="filament-resources-view-record-page filament-resources-customers">
    @php
        $member = filled($record->password);
        $phone = $record->phone ?: $record->login_phone;
        $email = $record->email ?: $record->login_email;
        $cart = collect($record->cart_items ?? []);
        $relationManagers = $this->getRelationManagers();
    @endphp

    <section class="sv-customer-profile" aria-labelledby="customer-name">
        <div class="sv-customer-identity">
            <span class="sv-customer-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($record->name, 0, 1)) }}</span>
            <div>
                <p class="sv-customer-eyebrow">Müşteri #{{ $record->getKey() }}</p>
                <h2 id="customer-name">{{ $record->name }}</h2>
                <span @class(['sv-customer-membership', 'is-member' => $member])>{{ $member ? 'Kayıtlı Üye' : 'Misafir Müşteri' }}</span>
            </div>
        </div>
        <div class="sv-customer-contact">
            @if (filled($phone))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"><x-heroicon-o-phone /><span>{{ $phone }}</span></a>
            @else
                <span>Telefon belirtilmemiş</span>
            @endif
            @if (filled($email))
                <a href="mailto:{{ $email }}"><x-heroicon-o-mail /><span>{{ $email }}</span></a>
            @else
                <span>E-posta belirtilmemiş</span>
            @endif
        </div>
    </section>

    <dl class="sv-customer-metrics">
        <div><dt>Sipariş Talebi</dt><dd>{{ $record->order_requests_count }}</dd></div>
        <div><dt>Toplam Talep Tutarı</dt><dd>{{ number_format((float) $record->orderRequests()->sum('total'), 2, ',', '.') }} <small>TL</small></dd></div>
        <div><dt>Mağaza Talebi / Randevu</dt><dd>{{ $record->reservations_count }}</dd></div>
        <div><dt>Sepet</dt><dd>{{ $cart->isEmpty() ? 'Boş' : $cart->count() . ' ürün çeşidi' }}</dd></div>
    </dl>

    <div class="sv-customer-information">
        <section class="sv-customer-card" aria-labelledby="customer-account-heading">
            <h3 id="customer-account-heading">Hesap Bilgileri</h3>
            <dl class="sv-customer-fields">
                <div><dt>Kayıt Tarihi</dt><dd>{{ $record->created_at?->format('d.m.Y H:i') ?? 'Belirtilmemiş' }}</dd></div>
                <div><dt>Üyelik Durumu</dt><dd>{{ $member ? 'Şifre ile giriş yapabilir' : 'Üyelik şifresi bulunmuyor' }}</dd></div>
                <div><dt>Giriş E-postası</dt><dd>{{ $record->login_email ?: 'Belirtilmemiş' }}</dd></div>
                <div><dt>Giriş Telefonu</dt><dd>{{ $record->login_phone ?: 'Belirtilmemiş' }}</dd></div>
            </dl>
        </section>
        <section class="sv-customer-card" aria-labelledby="customer-notes-heading">
            <h3 id="customer-notes-heading">Adres ve Notlar</h3>
            <dl class="sv-customer-fields sv-customer-fields-stacked">
                <div><dt>Adres</dt><dd class="sv-customer-multiline">{{ $record->address ?: 'Henüz adres eklenmemiş.' }}</dd></div>
                <div><dt>Yönetici Notları</dt><dd class="sv-customer-multiline">{{ $record->notes ?: 'Henüz yönetici notu eklenmemiş.' }}</dd></div>
            </dl>
        </section>
    </div>

    @if (count($relationManagers))
        <section class="sv-customer-history" aria-labelledby="customer-history-heading">
            <h2 id="customer-history-heading">Müşteri Geçmişi</h2>
            <x-filament::resources.relation-managers
                :active-manager="$activeRelationManager"
                :managers="$relationManagers"
                :owner-record="$record"
                :page-class="static::class"
            />
        </section>
    @endif
</x-filament::page>
