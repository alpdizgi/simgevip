<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sliders = Slider::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $rootCategories = Category::query()
            ->roots()
            ->inNav()
            ->with(['children' => fn ($q) => $q->inNav()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $categoryRows = [];
        foreach ($rootCategories as $cat) {
            $q = Product::query()
                ->with(['category.parent', 'campaigns'])
                ->withCount('variants');

            if ($cat->slug === 'indirimli-urunler') {
                $q->where(function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('has_campaign', true)->whereNotNull('campaign_discount_percentage');
                    })->orWhereHas('campaigns', fn ($c) => $c->active());
                });
            } elseif (in_array($cat->slug, ['en-yeniler', 'yeni-gelenler'], true)) {
                $q->latest();
            } elseif ($cat->slug === 'yeni-sezon') {
                $q->where('is_new_season', true);
            } else {
                $childIds = $cat->children->pluck('id');
                $ids = collect([$cat->id])->merge($childIds)->all();
                $q->whereIn('category_id', $ids);
            }

            $products = $q->latest()->take(8)->get();

            if ($products->isNotEmpty()) {
                $categoryRows[] = [
                    'category' => $cat,
                    'products' => $products,
                ];
            }
        }

        $featured = Product::query()
            ->with(['category.parent', 'campaigns'])
            ->withCount('variants')
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        if ($featured->isEmpty()) {
            $featured = Product::query()->with(['category.parent', 'campaigns'])->withCount('variants')->latest()->take(8)->get();
        }

        $campaign = Campaign::query()
            ->active()
            ->whereHas('products')
            ->with(['products' => fn ($q) => $q->with(['category.parent', 'campaigns'])->withCount('variants')->latest()->take(8)])
            ->latest()
            ->first();

        $latest = Product::query()->with(['category.parent', 'campaigns'])->withCount('variants')->latest()->take(4)->get();

        $productDeals = Product::query()
            ->with(['category.parent', 'campaigns'])
            ->withCount('variants')
            ->where('has_campaign', true)
            ->whereNotNull('campaign_discount_percentage')
            ->latest()
            ->take(4)
            ->get();

        return view('store.home', compact(
            'sliders',
            'categoryRows',
            'featured',
            'campaign',
            'latest',
            'productDeals'
        ));
    }
}
