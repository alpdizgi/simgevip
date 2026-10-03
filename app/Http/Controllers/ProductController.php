<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->with(['category.parent', 'variants', 'campaigns'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::query()
            ->with(['category.parent', 'campaigns'])
            ->withCount('variants')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        if ($related->isEmpty()) {
            $related = Product::query()
                ->with(['category.parent', 'campaigns'])
                ->withCount('variants')
                ->where('id', '!=', $product->id)
                ->latest()
                ->take(4)
                ->get();
        }

        return view('store.product', compact('product', 'related'));
    }
}
