<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->filled('kategori')) {
            $redirectCategory = Category::query()
                ->where('slug', (string) $request->input('kategori'))
                ->first();

            if ($redirectCategory) {
                $targetSlug = $redirectCategory->slug === 'en-yeniler'
                    ? 'yeni-gelenler'
                    : $redirectCategory->slug;
                $query = collect($request->except('kategori'))
                    ->reject(function ($value, $key) {
                        if ($value === null || $value === '') {
                            return true;
                        }
                        if ($key === 'siralama' && $value === 'yeni') {
                            return true;
                        }
                        if ($key === 'page' && (int) $value <= 1) {
                            return true;
                        }

                        return false;
                    })
                    ->all();
                $target = route('shop.category', $targetSlug);
                if ($query !== []) {
                    $target .= '?' . http_build_query($query);
                }

                return redirect()->to($target, 301);
            }
        }

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $query = Product::query()->with(['category.parent', 'variants', 'campaigns']);

        if ($request->filled('kategori')) {
            $category = Category::query()->where('slug', (string) $request->input('kategori'))->first();
            if ($category) {
                $this->applyCategoryFilter($query, $category);
            }
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->input('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if ($request->filled('kampanya')) {
            $campaignId = $request->input('kampanya');
            if ($campaignId === 'all') {
                $query->where(function ($q) {
                    $q->where('has_campaign', true)
                      ->orWhereHas('campaigns');
                });
            } else {
                $query->whereHas('campaigns', function ($q) use ($campaignId) {
                    $q->where('campaigns.id', $campaignId);
                });
            }
        }

        $sort = $request->get('siralama', 'yeni');
        $this->applySort($query, $sort);

        if ($request->filled('favoriler') && auth('customer')->check()) {
            $query->whereIn('id', function ($q) {
                $q->select('product_id')
                  ->from('customer_favorites')
                  ->where('customer_id', auth('customer')->id());
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->paginate(12);
        $products->withQueryString();

        return view('store.shop', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $request->get('kategori'),
            'search' => $request->get('q'),
            'sort' => $sort,
        ]);
    }

    public function category(Request $request, string $slug): View|RedirectResponse
    {
        if ($slug === 'en-yeniler') {
            return redirect()->route('shop.category', 'yeni-gelenler', 301);
        }

        $category = Category::query()
            ->with(['children' => fn ($q) => $q->inNav()->orderBy('sort_order')])
            ->where('slug', $slug)
            ->firstOrFail();

        $query = Product::query()->with(['category.parent', 'variants', 'campaigns']);
        $this->applyCategoryFilter($query, $category);

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->input('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        $sort = $request->get('siralama', 'yeni');
        $this->applySort($query, $sort);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->paginate(12);
        $products->withQueryString();

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('store.shop', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $category->slug,
            'category' => $category,
            'search' => $request->get('q'),
            'sort' => $sort,
        ]);
    }

    protected function applyCategoryFilter(Builder $query, Category $category): void
    {
        // Özel sanal kategoriler
        if ($category->slug === 'indirimli-urunler') {
            $query->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->where('has_campaign', true)
                        ->whereNotNull('campaign_discount_percentage');
                })->orWhereHas('campaigns', function ($c) {
                    $c->active();
                });
            });

            return;
        }

        if (in_array($category->slug, ['en-yeniler', 'yeni-gelenler'], true)) {
            $query->latest();

            return;
        }

        if ($category->slug === 'yeni-sezon') {
            $query->where('is_new_season', true);

            return;
        }

        $ids = collect([$category->id])
            ->merge($category->children()->pluck('id'))
            ->all();

        $query->whereIn('category_id', $ids);
    }

    public function liveSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'results' => [],
                'total' => 0,
            ]);
        }

        $term = '%' . $q . '%';

        $limit = (int) $request->input('limit', 6);
        $limit = min(12, max(1, $limit));

        $products = Product::query()
            ->with(['category.parent', 'campaigns'])
            ->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('description', 'like', $term);
            })
            ->latest()
            ->take($limit)
            ->get();

        $results = $products->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Koleksiyon',
                'sku' => $product->sku,
                'image' => $product->image_url,
                'price' => $product->formatted_price,
                'discounted_price' => $product->formatted_discounted_price,
                'badge' => $product->campaign_badge,
                'url' => route('product.show', $product->slug),
            ];
        });

        $totalCount = Product::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('description', 'like', $term);
            })
            ->count();

        return response()->json([
            'query' => $q,
            'results' => $results,
            'total' => $totalCount,
            'view_all_url' => route('shop.index', ['q' => $q]),
        ]);
    }

    protected function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'fiyat_asc' => $query->orderBy('price'),
            'fiyat_desc' => $query->orderByDesc('price'),
            'ad' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }
}
