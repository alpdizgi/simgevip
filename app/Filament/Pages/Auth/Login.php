<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Http\Livewire\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public $username = '';
    public $password = '';
    public $remember = false;

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'username' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $login = trim((string) $this->username);
        $credentialsField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $authenticated = Filament::auth()->attempt([
            $credentialsField => $login,
            'password' => $this->password,
        ], $this->remember ?? false);

        if (! $authenticated && $credentialsField === 'username') {
            $authenticated = Filament::auth()->attempt([
                'email' => $login,
                'password' => $this->password,
            ], $this->remember ?? false);
        }

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'username' => __('Giriş başarısız, kullanıcı adı veya şifre hatalı.'),
            ]);
        }

        session()->regenerate();

        Notification::make()
            ->title('Giriş başarılı')
            ->body('Yönetim paneline hoş geldiniz.')
            ->success()
            ->send();

        return app(LoginResponse::class);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('username')
                ->label('Kullanıcı Adı')
                ->required()
                ->autocomplete('username')
                ->extraInputAttributes(['class' => 'custom-input']),
            TextInput::make('password')
                ->label('Şifre')
                ->password()
                ->required()
                ->extraInputAttributes(['class' => 'custom-input']),
        ];
    }

    public function render(): View
    {
        return view('filament.pages.auth.login')
            ->layout('filament.layouts.login-layout', [
                'title' => 'Admin Panel Giriş',
            ]);
    }
}
