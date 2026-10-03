@extends('store.layout')
@section('title', 'Üye Ol')
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-auth')
@section('content')
<section class="customer-auth__section" aria-labelledby="customer-auth-title">
    <div class="customer-auth__shell container">
        <aside class="customer-auth__intro">
            <span class="customer-auth__eyebrow">SİMGEVIP HESABIM</span>
            <h2>Size özel bir alan.</h2>
            <p>Hesabınızı oluşturun; e-posta adresiniz veya telefon numaranızla giriş yapın.</p>
            <div class="customer-auth__intro-note">Zaten hesabınız var mı? <a href="{{ route('customer.login', array_filter(['next' => request('next')])) }}">Giriş yapın <span aria-hidden="true">↗</span></a></div>
        </aside>
        <div class="customer-auth__panel">
            <div class="customer-auth__heading">
                <span class="customer-auth__step">02 / ÜYELİK</span>
                <h1 id="customer-auth-title">Üye Ol</h1>
                <p>E-posta veya telefon bilgilerinizden en az birini girin.</p>
            </div>
            <form class="customer-auth__form" method="POST" action="{{ route('customer.register.submit') }}">
                @csrf
                <div class="customer-auth__field"><label for="name">Ad Soyad</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required placeholder="Adınız ve soyadınız">@error('name') <small class="customer-auth__error">{{ $message }}</small> @enderror</div>
                <div class="customer-auth__field"><label for="email">E-posta <span>(isteğe bağlı)</span></label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="ornek@mail.com">@error('email') <small class="customer-auth__error">{{ $message }}</small> @enderror</div>
                <div class="customer-auth__field"><label for="phone">Telefon <span>(isteğe bağlı)</span></label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="05xx xxx xx xx">@error('phone') <small class="customer-auth__error">{{ $message }}</small> @enderror</div>
                <div class="customer-auth__field"><label for="password">Şifre <span>(en az 8 karakter)</span></label><input id="password" type="password" name="password" autocomplete="new-password" required minlength="8" placeholder="Şifrenizi oluşturun">@error('password') <small class="customer-auth__error">{{ $message }}</small> @enderror</div>
                <div class="customer-auth__field"><label for="password_confirmation">Şifre Tekrarı</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8" placeholder="Şifrenizi tekrar girin"></div>
                <div class="customer-auth__field" style="display: flex; gap: 0.75rem; align-items: flex-start; margin-top: 1rem; margin-bottom: 1.5rem;">
                    <input type="checkbox" id="terms" name="terms" required style="margin-top: 0.25rem; width: 1.25rem; height: 1.25rem; cursor: pointer; flex-shrink: 0;">
                    <label for="terms" style="font-size: 0.85rem; line-height: 1.5; color: #444; text-transform: none; font-weight: normal; margin: 0; cursor: pointer;">
                        <a href="{{ route('page.show', 'kullanim-kosullari') }}" target="_blank" style="text-decoration: underline; color: #000; font-weight: 500;">Kullanım Koşulları</a>'nı, 
                        <a href="{{ route('page.show', 'gizlilik-politikasi') }}" target="_blank" style="text-decoration: underline; color: #000; font-weight: 500;">Gizlilik Politikası</a>'nı ve 
                        <a href="{{ route('page.show', 'cerez-politikasi') }}" target="_blank" style="text-decoration: underline; color: #000; font-weight: 500;">Çerez Politikası</a>'nı okudum, anladım ve kabul ediyorum.
                    </label>
                </div>
                <button class="customer-auth__submit" type="submit">Üye Ol <span aria-hidden="true">→</span></button>
            </form>
            <p class="customer-auth__switch">Zaten hesabınız var mı? <a href="{{ route('customer.login', array_filter(['next' => request('next')])) }}">Giriş yapın</a></p>
        </div>
    </div>
</section>
@endsection
