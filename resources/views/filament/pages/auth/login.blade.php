<div class="login-wrapper">
    <div class="login-box" id="loginBox">
        <div class="login-brand">
            <h1>SimgeVIP</h1>
            <p>Kurumsal Yönetim Paneli Girişi</p>
        </div>

        <form wire:submit.prevent="authenticate" method="POST" class="login-form">
            @csrf
            <div class="form-group">
                <input type="text" wire:model.defer="username" class="custom-input" placeholder="Kullanıcı adı veya e-posta" required autofocus autocomplete="username">
                @error('username') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <input type="password" wire:model.defer="password" class="custom-input" placeholder="Şifre" required autocomplete="current-password">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="login-submit-btn" wire:loading.attr="disabled">
                <span wire:loading.remove>Giriş Yap</span>
                <span wire:loading>Giriş yapılıyor...</span>
            </button>
        </form>
    </div>
</div>
