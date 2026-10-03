@extends('store.layout')
@section('title', 'Giriş Yap')
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-auth')
@section('content')
<section class="customer-auth__section" aria-labelledby="customer-auth-title">
    <div class="customer-auth__shell container">
        <aside class="customer-auth__intro">
            <span class="customer-auth__eyebrow">SİMGEVIP HESABIM</span>
            <h2>Yeniden hoş geldiniz.</h2>
            <p>Hesabınıza ulaşmak için e-posta adresinizi veya telefon numaranızı kullanın.</p>
            <div class="customer-auth__intro-note">Henüz hesabınız yok mu? <a href="{{ route('customer.register', array_filter(['next' => request('next')])) }}">Üye olun <span aria-hidden="true">↗</span></a></div>
        </aside>
        <div class="customer-auth__panel">
            <div class="customer-auth__heading">
                <span class="customer-auth__step">01 / GİRİŞ</span>
                <h1 id="customer-auth-title">Giriş Yap</h1>
                <p>Bilgilerinizi girerek devam edin.</p>
            </div>
            <form class="customer-auth__form" method="POST" action="{{ route('customer.login.submit') }}">
                @csrf
                <div class="customer-auth__field">
                    <label for="login">E-posta veya telefon</label>
                    <input id="login" name="login" value="{{ old('login') }}" autocomplete="username" required placeholder="E-posta adresiniz veya 05xx...">
                    @error('login') <small class="customer-auth__error">{{ $message }}</small> @enderror
                </div>
                <div class="customer-auth__field">
                    <label for="password">Şifre</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required placeholder="Şifreniz">
                    @error('password') <small class="customer-auth__error">{{ $message }}</small> @enderror
                </div>
                <label class="customer-auth__remember"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Beni hatırla</label>
                <button class="customer-auth__submit" type="submit">Giriş Yap <span aria-hidden="true">→</span></button>
            </form>
            <p class="customer-auth__switch">Hesabınız yok mu? <a href="{{ route('customer.register', array_filter(['next' => request('next')])) }}">Üye olun</a></p>
        </div>
    </div>
</section>
@endsection
