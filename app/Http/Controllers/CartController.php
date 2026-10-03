<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(CartService $cart): JsonResponse
    {
        return response()->json($cart->summary());
    }

    public function store(Request $request, CartService $cart): JsonResponse
    {
        if (! Auth::guard('customer')->check()) {
            abort(401, 'Sepete eklemek için giriş yapmalısınız.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'size' => ['nullable', 'string', 'max:40'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::query()->with('variants')->findOrFail($data['product_id']);

        if ($product->variants->isNotEmpty()) {
            if (empty($data['variant_id'])) {
                return response()->json(['message' => 'Lütfen renk seçin.'], 422);
            }
            if (empty($data['size'])) {
                return response()->json(['message' => 'Lütfen beden seçin.'], 422);
            }
        }

        try {
            $line = $cart->add(
                $product,
                isset($data['variant_id']) ? (int) $data['variant_id'] : null,
                $data['size'] ?? null,
                (int) ($data['qty'] ?? 1)
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Ürün sepete eklendi.',
            'line' => $line,
            'cart' => $cart->summary(),
        ]);
    }

    public function update(Request $request, CartService $cart): JsonResponse
    {
        if (! Auth::guard('customer')->check()) {
            abort(401, 'Sepeti güncellemek için giriş yapmalısınız.');
        }

        $data = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'qty' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $cart->updateQty($data['key'], (int) $data['qty']);

        return response()->json([
            'message' => 'Sepet güncellendi.',
            'cart' => $cart->summary(),
        ]);
    }

    public function destroy(Request $request, CartService $cart): JsonResponse
    {
        if (! Auth::guard('customer')->check()) {
            abort(401, 'Sepetten çıkarmak için giriş yapmalısınız.');
        }

        $data = $request->validate([
            'key' => ['required', 'string', 'max:120'],
        ]);

        $cart->remove($data['key']);

        return response()->json([
            'message' => 'Ürün sepetten çıkarıldı.',
            'cart' => $cart->summary(),
        ]);
    }
}
