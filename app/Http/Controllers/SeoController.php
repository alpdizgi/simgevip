<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $base = rtrim((string) parse_url(url('/'), PHP_URL_PATH), '/');
        $lines = ['User-agent: *'];

        foreach (['/admin', '/hesabim', '/giris', '/uye-ol', '/sepet', '/arama'] as $path) {
            $lines[] = 'Disallow: ' . $base . $path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = [];
        $urls[] = $this->entry(route('home'), null, 'daily', '1.0');
        $urls[] = $this->entry(route('shop.index'), null, 'daily', '0.9');
        $urls[] = $this->entry(route('about'), null, 'monthly', '0.4');
        $urls[] = $this->entry(route('contact.show'), null, 'monthly', '0.4');
        $urls[] = $this->entry(route('faq.index'), null, 'monthly', '0.4');

        Category::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('slug', '!=', 'en-yeniler')
            ->orderBy('id')
            ->select(['slug', 'updated_at'])
            ->cursor()
            ->each(function (Category $category) use (&$urls) {
                $urls[] = $this->entry($category->url, $category->updated_at, 'weekly', '0.7');
            });

        Product::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->select(['slug', 'updated_at'])
            ->cursor()
            ->each(function (Product $product) use (&$urls) {
                $urls[] = $this->entry(route('product.show', $product->slug), $product->updated_at, 'weekly', '0.8');
            });

        if (class_exists(Page::class)) {
            Page::query()
                ->where('is_active', true)
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderBy('id')
                ->select(['slug', 'updated_at'])
                ->cursor()
                ->each(function (Page $page) use (&$urls) {
                    $urls[] = $this->entry(route('page.show', $page->slug), $page->updated_at, 'monthly', '0.5');
                });
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . implode('', $urls)
            . '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function entry(string $loc, $lastmod, string $changefreq, string $priority): string
    {
        $xml = '<url><loc>' . e($loc) . '</loc>';

        if ($lastmod) {
            $xml .= '<lastmod>' . e($lastmod->toAtomString()) . '</lastmod>';
        }

        return $xml
            . '<changefreq>' . $changefreq . '</changefreq>'
            . '<priority>' . $priority . '</priority></url>';
    }
}
