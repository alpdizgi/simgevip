<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreHoldController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        if (! $customer) {
            abort(401, 'Mağazada ayırmak için giriş yapmalısınız.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer'],
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $name = trim((string) $customer->name);
        $phone = trim((string) ($customer->phone ?: $customer->login_phone));
        if ($name === '' || $phone === '') {
            throw ValidationException::withMessages([
                'profile' => 'Mağazada ayırma talebi için hesap bilgilerinizde ad soyad ve telefon gerekli. Lütfen profilinizi tamamlayın.',
            ]);
        }
        $data['customer_name'] = $name;
        $data['phone'] = $phone;
        $data['email'] = $customer->email ?: $customer->login_email;

        $product = Product::query()->findOrFail($data['product_id']);

        $details = collect([
            'Mağazada ayırma talebi',
            'Ürün: ' . $product->name,
            $product->sku ? 'SKU: ' . $product->sku : null,
            ! empty($data['color']) ? 'Renk: ' . $data['color'] : null,
            ! empty($data['size']) ? 'Beden: ' . $data['size'] : null,
            'Link: ' . route('product.show', $product->slug),
            ! empty($data['note']) ? 'Not: ' . $data['note'] : null,
        ])->filter()->implode("\n");

        Reservation::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'color' => $data['color'] ?? null,
            'size' => $data['size'] ?? null,
            'customer_name' => $data['customer_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'reservation_date' => now(),
            'guests_count' => 1,
            'status' => 'pending',
            'is_admin_hold' => false,
            'notes' => $data['note'] ?? null,
        ]);

        return response()->json([
            'message' => 'Talebiniz alındı. Mağaza sizinle iletişime geçecek.',
        ]);
    }
}
