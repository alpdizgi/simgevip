<div class="cart-drawer" data-cart-drawer aria-hidden="true">
    <div class="cart-drawer__backdrop" data-cart-close></div>
    <aside class="cart-drawer__panel" role="dialog" aria-label="Alışveriş sepeti">
        <div class="cart-drawer__head">
            <div class="cart-drawer__title-wrap">
                <x-store-icon name="bag" :size="20" />
                <h2>Sepetiniz</h2>
                <span class="cart-drawer__badge" data-cart-header-count>0</span>
            </div>
            <button type="button" class="cart-drawer__close" data-cart-close aria-label="Kapat" title="Kapat">
                <x-store-icon name="x" :size="20" />
            </button>
        </div>

        <div class="cart-drawer__body" data-cart-items>
            <div class="cart-drawer__empty">
                <div class="cart-drawer__empty-icon">
                    <x-store-icon name="bag" :size="36" />
                </div>
                <h3>Sepetiniz Henüz Boş</h3>
                <p>Koleksiyonumuzdaki zarif parçaları inceleyip beğendiklerinizi sepete ekleyebilirsiniz.</p>
                <a href="{{ route('shop.index') }}" class="btn btn--dark btn--sm cart-drawer__empty-btn">Koleksiyonu Keşfet</a>
            </div>
        </div>

        <div class="cart-drawer__foot" data-cart-foot hidden>
            <div class="cart-savings-banner" data-cart-savings-banner hidden>
                <x-store-icon name="spark" :size="14" />
                <span>Bu siparişte <strong data-cart-savings-amount>0,00 TL</strong> kazanç sağladınız!</span>
            </div>

            <div class="cart-breakdown">
                <div class="cart-breakdown__row" data-cart-row-original>
                    <span class="cart-breakdown__label">Toplam Tutar</span>
                    <span class="cart-breakdown__value" data-cart-original-total>0,00 TL</span>
                </div>
                <div class="cart-breakdown__row cart-breakdown__row--discount" data-cart-row-discount hidden>
                    <span class="cart-breakdown__label">
                        <x-store-icon name="tag" :size="13" />
                        <span>İndirim Tutarı</span>
                    </span>
                    <span class="cart-breakdown__value" data-cart-discount-total>-0,00 TL</span>
                </div>
                <div class="cart-breakdown__divider"></div>
                <div class="cart-breakdown__row cart-breakdown__row--grand">
                    <span class="cart-breakdown__label">Genel Toplam</span>
                    <strong class="cart-breakdown__value" data-cart-grand-total data-cart-subtotal>0,00 TL</strong>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: flex-start; margin-top: 1rem; margin-bottom: 1rem;">
                <input type="checkbox" id="cart-terms" name="cart_terms" style="margin-top: 0.2rem; cursor: pointer; flex-shrink: 0;">
                <label for="cart-terms" style="font-size: 0.75rem; line-height: 1.4; color: #555; font-weight: normal; margin: 0; cursor: pointer;">
                    <a href="{{ route('page.show', 'mesafeli-satis-sozlesmesi') }}" target="_blank" style="text-decoration: underline; color: #000;">Mesafeli Satış Sözleşmesi</a>'ni ve <a href="{{ route('page.show', 'gizlilik-politikasi') }}" target="_blank" style="text-decoration: underline; color: #000;">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                </label>
            </div>

            <a href="#" class="btn btn--whatsapp cart-drawer__checkout" data-cart-whatsapp target="_blank" rel="noopener noreferrer">
                <x-store-icon name="whatsapp" :size="17" />
                <span>WhatsApp ile Sipariş Ver</span>
            </a>

            <div class="cart-drawer__note-wrap">
                <p class="cart-drawer__guarantee">
                    <x-store-icon name="shield" :size="13" />
                    <span>Güvenli & Hızlı Sipariş Onayı</span>
                </p>
                <p class="cart-drawer__note">Ödeme ve teslimat WhatsApp üzerinden netleştirilir.</p>
            </div>
        </div>
    </aside>
</div>

<div class="hold-modal" data-hold-modal data-customer-signed-in="{{ Auth::guard('customer')->check() ? '1' : '0' }}" data-customer-ready="{{ Auth::guard('customer')->check() && filled(Auth::guard('customer')->user()->name) && filled(Auth::guard('customer')->user()->phone ?: Auth::guard('customer')->user()->login_phone) ? '1' : '0' }}" aria-hidden="true">
    <div class="hold-modal__backdrop" data-hold-close></div>
    <div class="hold-modal__panel" role="dialog" aria-label="Mağazada ayır">
        <div class="hold-modal__head">
            <h2>Mağazada Ayır</h2>
            <button type="button" class="hold-modal__close" data-hold-close aria-label="Kapat">
                <x-store-icon name="x" :size="18" />
            </button>
        </div>
        <p class="hold-modal__intro" data-hold-product-label>Ürünü mağazada sizin için ayıralım.</p>
        @if (Auth::guard('customer')->check() && (blank(Auth::guard('customer')->user()->name) || blank(Auth::guard('customer')->user()->phone ?: Auth::guard('customer')->user()->login_phone)))
            <div class="hold-modal__profile-notice" data-hold-profile-notice>
                <p>Mağazada ayırmak için hesap bilgilerinizde ad soyad ve telefon bulunmalı.</p>
                <a class="btn btn--dark" href="{{ route('customer.account') }}#profile">Bilgilerimi Tamamla</a>
            </div>
        @endif
        @guest('customer')
            <p class="hold-modal__account-info">Mağazada ayırmak için hesabınıza giriş yapın.</p>
            <a class="btn btn--dark" href="{{ route('customer.login') }}">Giriş Yap</a>
        @endguest
        <form class="hold-modal__form" data-hold-form @guest('customer') hidden @endguest @if (Auth::guard('customer')->check() && (blank(Auth::guard('customer')->user()->name) || blank(Auth::guard('customer')->user()->phone ?: Auth::guard('customer')->user()->login_phone))) hidden @endif>
            <input type="hidden" name="product_id" data-hold-product-id>
            <input type="hidden" name="variant_id" data-hold-variant-id>
            <input type="hidden" name="color" data-hold-color>
            <input type="hidden" name="size" data-hold-size>
            @auth('customer')
                <p class="hold-modal__account-info">Talep, hesabınızdaki ad ve telefon bilgileriyle gönderilir.</p>
            @endauth
            <label>
                <span>Not (opsiyonel)</span>
                <textarea name="note" rows="3" maxlength="1000" placeholder="Teslim almak istediğiniz gün / saat vb."></textarea>
            </label>
            <p class="hold-modal__error" data-hold-error hidden></p>
            <button type="submit" class="btn btn--dark">Talebi Gönder</button>
        </form>
    </div>
</div>
