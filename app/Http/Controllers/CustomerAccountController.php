<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureCustomerSessionFresh;
use App\Models\CustomerOrderRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAccountController extends Controller
{
    public function index(CartService $cart)
    {
        $customer = Auth::guard('customer')->user();

        return view('store.auth.account', [
            'customer' => $customer,
            'cart' => $cart->summary(),
            'favorites' => $customer->favorites()->latest('customer_favorites.created_at')->get(),
            'reservations' => $customer->reservations()->with('product')->latest()->get(),
            'orderRequests' => $customer->orderRequests()->latest()->get(),
            'supportTickets' => $customer->supportTickets()->orderByDesc('last_reply_at')->get(),
        ]);
    }

    public function addFavorite(Product $product)
    {
        Auth::guard('customer')->user()->favorites()->syncWithoutDetaching([$product->id]);
        return back()->with('success', 'Ürün favorilerinize eklendi.');
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        $favorites = Auth::guard('customer')->user()->favorites();
        $isFavorite = $favorites->whereKey($product->id)->exists();
        if ($isFavorite) {
            $favorites->detach($product->id);
        } else {
            $favorites->syncWithoutDetaching([$product->id]);
        }

        $message = $isFavorite ? 'Ürün favorilerinizden kaldırıldı.' : 'Ürün favorilerinize eklendi.';
        if ($request->expectsJson()) {
            return response()->json(['is_favorite' => ! $isFavorite, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function removeFavorite(Product $product)
    {
        Auth::guard('customer')->user()->favorites()->detach($product->id);
        return back()->with('success', 'Ürün favorilerinizden kaldırıldı.');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);
        $customer = Auth::guard('customer')->user();
        if (filled($data['phone'] ?? null)) {
            $digits = preg_replace('/\D+/', '', $data['phone']);
            if (strlen($digits) === 11 && Str::startsWith($digits, '0')) $digits = '90' . substr($digits, 1);
            elseif (strlen($digits) === 10 && Str::startsWith($digits, '5')) $digits = '90' . $digits;
            if (! preg_match('/^90[0-9]{10}$/', $digits)) {
                throw ValidationException::withMessages(['phone' => 'Geçerli bir telefon numarası girin.']);
            }
            $data['phone'] = $digits;
            if (\App\Models\Customer::where('login_phone', $digits)->where('id', '!=', $customer->id)->exists()) {
                throw ValidationException::withMessages(['phone' => 'Bu telefon numarası başka bir hesapta kayıtlı.']);
            }
            if (blank($customer->login_phone)) {
                $data['login_phone'] = $digits;
            }
        }
        $customer->forceFill([
            'name' => trim($data['name']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'login_phone' => $data['login_phone'] ?? $customer->login_phone,
        ])->save();
        return redirect(route('customer.account') . '#profile')->with('success', 'Hesap bilgileriniz güncellendi.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $customer = Auth::guard('customer')->user();
        $passwordKey = 'customer-password:' . $customer->id;
        if (RateLimiter::tooManyAttempts($passwordKey, 5)) {
            throw ValidationException::withMessages([
                'current_password' => 'Çok fazla deneme yapıldı. Lütfen biraz sonra tekrar deneyin.',
            ]);
        }
        if (! Hash::check($data['current_password'], $customer->password)) {
            RateLimiter::hit($passwordKey, 600);
            throw ValidationException::withMessages(['current_password' => 'Mevcut şifre hatalı.']);
        }
        RateLimiter::clear($passwordKey);
        $customer->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();
        $request->session()->put(EnsureCustomerSessionFresh::HASH_KEY, $customer->getAuthPassword());
        $request->session()->regenerate();
        return redirect(route('customer.account') . '#profile')->with('success', 'Şifreniz güncellendi. Diğer cihazlardaki oturumlar kapatıldı.');
    }

    public function updateCart(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'qty' => ['required', 'integer', 'min:0', 'max:20'],
        ]);
        $cart->updateQty($data['key'], (int) $data['qty']);
        return redirect(route('customer.account') . '#cart')->with('success', 'Sepet güncellendi.');
    }

    public function cancelReservation(Reservation $reservation)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless((int) $reservation->customer_id === (int) $customer->id, 404);
        abort_unless($reservation->status === 'pending', 422);
        $reservation->update(['status' => 'cancelled']);
        return redirect(route('customer.account') . '#reservations')->with('success', 'Ayırma talebi iptal edildi.');
    }

    public function cancelOrderRequest(Request $request, CustomerOrderRequest $orderRequest)
    {
        abort_unless((int) $orderRequest->customer_id === (int) Auth::guard('customer')->id(), 404);
        abort_unless($orderRequest->status === 'pending', 422);

        $data = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Lütfen bir iptal nedeni belirtiniz.',
        ]);

        $orderRequest->update([
            'status' => 'cancel_requested',
            'cancel_reason' => $data['cancel_reason'],
        ]);

        return redirect(route('customer.account') . '#orders')->with('success', 'İptal isteğiniz ve gerekçeniz alındı. Mağaza onayından sonra sonuçlanır.');
    }

    public function createOrderRequest(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'source' => ['required', 'in:cart,product'],
            'product_id' => ['required_if:source,product', 'nullable', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer'],
            'size' => ['nullable', 'string', 'max:40'],
        ]);

        if ($data['source'] === 'cart') {
            $items = array_values($cart->items());
            if (! $items) {
                return response()->json(['message' => 'Sepetiniz boş.'], 422);
            }
            $total = $cart->subtotal();
            $whatsappUrl = $cart->whatsappCheckoutUrl();
        } else {
            $product = Product::with('variants')->findOrFail($data['product_id']);
            $variant = null;
            if ($product->variants->isNotEmpty()) {
                $variant = $product->variants->firstWhere('id', (int) ($data['variant_id'] ?? 0));
                if (! $variant || empty($data['size']) || (int) (($variant->sizes ?? [])[$data['size']] ?? 0) < 1) {
                    return response()->json(['message' => 'Geçerli renk ve beden seçin.'], 422);
                }
            }
            $total = (float) ($product->discounted_price ?? $product->price);
            $items = [[
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $variant ? $variant->cover_image_url : $product->image_url,
                'color' => $variant->color ?? null,
                'size' => $data['size'] ?? null,
                'qty' => 1,
                'price' => $total,
            ]];
            $whatsappUrl = $cart->whatsappProductUrl($product, $variant->color ?? null, $data['size'] ?? null);
        }

        $order = CustomerOrderRequest::create([
            'customer_id' => Auth::guard('customer')->id(),
            'source' => $data['source'],
            'status' => 'pending',
            'items' => $items,
            'total' => $total,
        ]);

        if (! is_string($whatsappUrl) || ! str_starts_with($whatsappUrl, 'https://wa.me/')) {
            $whatsappUrl = null;
        }

        if (! $request->expectsJson()) {
            if ($whatsappUrl === null) {
                return redirect(route('customer.account') . '#orders')->with('success', 'Sipariş talebiniz alındı.');
            }

            return redirect()->away($whatsappUrl);
        }

        return response()->json(['id' => $order->id, 'whatsapp_url' => $whatsappUrl]);
    }
}
