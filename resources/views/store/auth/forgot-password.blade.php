@extends('store.layout')
@section('title', 'Şifremi Unuttum')
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-auth')
@section('content')
<section class="customer-auth__section" aria-labelledby="customer-auth-title">
    <div class="customer-auth__shell container">
        <aside class="customer-auth__intro">
            <span class="customer-auth__eyebrow">SİMGEVIP HESABIM</span>
            <h2>Şifrenizi mi unuttunuz?</h2>
            <p>E-posta adresinizi girin, size şifrenizi sıfırlamanız için bir bağlantı gönderelim.</p>
        </aside>
        <div class="customer-auth__panel">
            <div class="customer-auth__heading">
                <span class="customer-auth__step">ŞİFRE SIFIRLAMA</span>
                <h1 id="customer-auth-title">Şifremi Unuttum</h1>
                <p>Kayıtlı e-posta adresinizi girin.</p>
            </div>
            <form class="customer-auth__form" method="POST" action="{{ route('customer.password.email') }}">
                @csrf
                <div class="customer-auth__field">
                    <label for="email">E-posta adresi</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="E-posta adresiniz">
                    @error('email') <small class="customer-auth__error">{{ $message }}</small> @enderror
                </div>
                <button class="customer-auth__submit" type="submit">Sıfırlama Bağlantısı Gönder <span aria-hidden="true">→</span></button>
            </form>
            <p class="customer-auth__switch">Vazgeçtiniz mi? <a href="{{ route('customer.login') }}">Giriş ekranına dönün</a></p>
        </div>
    </div>
</section>
@endsection
