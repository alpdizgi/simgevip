<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureCustomerSessionFresh;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $this->rememberCustomerReturn($request->query('next'));

        return view('store.auth.login');
    }

    public function showRegister(Request $request)
    {
        $this->rememberCustomerReturn($request->query('next'));

        return view('store.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:180'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $registerKey = 'customer-register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($registerKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Çok fazla kayıt denemesi yapıldı. Lütfen biraz sonra tekrar deneyin.',
            ]);
        }
        RateLimiter::hit($registerKey, 600);

        $email = filled($data['email'] ?? null) ? Str::lower(trim($data['email'])) : null;
        $phone = filled($data['phone'] ?? null) ? $this->normalizePhone($data['phone']) : null;
        if (filled($data['phone'] ?? null) && ! $phone) {
            throw ValidationException::withMessages(['phone' => 'Geçerli bir telefon numarası girin.']);
        }
        if ($email && Customer::where('login_email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Bu e-posta adresiyle zaten bir hesap var.']);
        }
        if ($phone && Customer::where('login_phone', $phone)->exists()) {
            throw ValidationException::withMessages(['phone' => 'Bu telefon numarasıyla zaten bir hesap var.']);
        }

        $customer = Customer::create([
            'name' => trim($data['name']),
            'email' => $email,
            'phone' => $phone,
            'login_email' => $email,
            'login_phone' => $phone,
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->put(EnsureCustomerSessionFresh::HASH_KEY, $customer->getAuthPassword());
        app(CartService::class)->mergeSessionForCustomer();

        return redirect()->to($this->safeCustomerRedirect($request->session()->pull('url.intended')));
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:180'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($data['login']);
        $normalized = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? Str::lower($identifier) : ($this->normalizePhone($identifier) ?: $identifier);
        $key = 'customer-login:' . $request->ip() . ':' . $normalized;
        $ipKey = 'customer-login-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 30)) {
            throw ValidationException::withMessages(['login' => 'Çok fazla deneme yapıldı. Lütfen biraz sonra tekrar deneyin.']);
        }

        $credentials = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? ['login_email' => Str::lower($identifier), 'password' => $data['password']]
            : ['login_phone' => $this->normalizePhone($identifier), 'password' => $data['password']];

        if (! $credentials[array_key_first($credentials)] || ! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 60);
            throw ValidationException::withMessages(['login' => 'Telefon/e-posta veya şifre hatalı.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put(
            EnsureCustomerSessionFresh::HASH_KEY,
            (string) Auth::guard('customer')->user()->getAuthPassword()
        );
        app(CartService::class)->mergeSessionForCustomer();

        return redirect()->to($this->safeCustomerRedirect($request->session()->pull('url.intended')));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->forget(CartService::SESSION_KEY);
        $request->session()->forget(EnsureCustomerSessionFresh::HASH_KEY);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function rememberCustomerReturn(mixed $next): void
    {
        if (! is_string($next) || $next === '') {
            return;
        }

        $safe = $this->safeCustomerRedirect($next);
        $blocked = [route('customer.login'), route('customer.register'), route('customer.account')];
        if (! in_array($safe, $blocked, true)) {
            session(['url.intended' => $safe]);
        }
    }

    private function safeCustomerRedirect(?string $url): string
    {
        $fallback = route('customer.account');
        if (! is_string($url) || $url === '') {
            return $fallback;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        $appUrl = rtrim(url('/'), '/');
        if ($url === $appUrl || str_starts_with($url, $appUrl . '/')) {
            return $url;
        }

        return $fallback;
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = '90' . substr($digits, 1);
        } elseif (strlen($digits) === 10 && $digits[0] === '5') {
            $digits = '90' . $digits;
        }

        return preg_match('/^90[0-9]{10}$/', $digits) ? $digits : null;
    }
}
