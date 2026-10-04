@extends('store.layout')
@section('title', 'Şifre Sıfırlama')
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-auth')
@section('content')
<section class="customer-auth__section" aria-labelledby="customer-auth-title">
    <div class="customer-auth__shell container">
        <aside class="customer-auth__intro">
            <span class="customer-auth__eyebrow">SİMGEVIP HESABIM</span>
            <h2>Yeni şifrenizi belirleyin.</h2>
            <p>Hesabınıza erişmek için yeni bir şifre oluşturun. Şifrenizin en az 8 karakter olması gerekmektedir.</p>
        </aside>
        <div class="customer-auth__panel">
            <div class="customer-auth__heading">
                <span class="customer-auth__step">ŞİFRE SIFIRLAMA</span>
                <h1 id="customer-auth-title">Yeni Şifre</h1>
                <p>Lütfen yeni şifrenizi girin.</p>
            </div>
            <form class="customer-auth__form" method="POST" action="{{ route('customer.password.update_from_reset') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="customer-auth__field">
                    <label for="email">E-posta adresi</label>
                    <input id="email" type="email" name="email" value="{{ request('email', old('email')) }}" required readonly>
                    @error('email') <small class="customer-auth__error">{{ $message }}</small> @enderror
                </div>
                <div class="customer-auth__field">
                    <label for="password">Yeni Şifre</label>
                    <input id="password" type="password" name="password" required placeholder="En az 8 karakter">
                    @error('password') <small class="customer-auth__error">{{ $message }}</small> @enderror
                </div>
                <div class="customer-auth__field">
                    <label for="password_confirmation">Yeni Şifre (Tekrar)</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Şifrenizi tekrar girin">
                </div>
                <button class="customer-auth__submit" type="submit">Şifreyi Güncelle <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </div>
</section>
@endsection
