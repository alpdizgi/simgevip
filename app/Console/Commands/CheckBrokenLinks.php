<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Category;
use App\Models\Page;

class CheckBrokenLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:check-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sistemdeki tüm ürün, kategori ve sayfa linklerini tarayarak kırık (404) olanları tespit eder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Kırık link taraması başlatılıyor. Bu işlem biraz sürebilir...');
        $brokenLinks = [];
        $appUrl = config('app.url');

        $this->warn("Kullanılan Ana URL: {$appUrl}");
        $this->warn("Not: Bu taramanın doğru çalışması için projenizin yerel sunucusunun (XAMPP veya artisan serve) çalışıyor olması gerekir.\n");
        
        // 1. Ürün Linkleri
        $this->info('Ürünler taranıyor...');
        $products = Product::whereNotNull('slug')->where('slug', '!=', '')->get();
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();
        
        foreach ($products as $product) {
            $url = route('product.show', $product->slug);
            if (!$this->checkUrl($url)) {
                $brokenLinks[] = ['type' => 'Ürün', 'name' => $product->name, 'url' => $url];
            }
            $bar->advance();
        }
        $bar->finish();

        // 2. Kategori Linkleri
        $this->info("\n\nKategoriler taranıyor...");
        $categories = Category::whereNotNull('slug')->where('slug', '!=', '')->get();
        $bar = $this->output->createProgressBar(count($categories));
        $bar->start();
        
        foreach ($categories as $category) {
            // Category modeli içinde url attribute'u olduğunu varsayıyoruz (Sitemap'te böyle kullanılmış)
            $url = $category->url ?? route('shop.category', $category->slug);
            if (!$this->checkUrl($url)) {
                $brokenLinks[] = ['type' => 'Kategori', 'name' => $category->name, 'url' => $url];
            }
            $bar->advance();
        }
        $bar->finish();

        // 3. Sayfa Linkleri
        if (class_exists(Page::class)) {
            $this->info("\n\nSayfalar taranıyor...");
            $pages = Page::where('is_active', true)->whereNotNull('slug')->where('slug', '!=', '')->get();
            $bar = $this->output->createProgressBar(count($pages));
            $bar->start();
            
            foreach ($pages as $page) {
                $url = route('page.show', $page->slug);
                if (!$this->checkUrl($url)) {
                    $brokenLinks[] = ['type' => 'Sayfa', 'name' => $page->title ?? $page->slug, 'url' => $url];
                }
                $bar->advance();
            }
            $bar->finish();
        }

        $this->info("\n\nTarama Tamamlandı!");

        if (count($brokenLinks) > 0) {
            $this->error('Aşağıdaki linklerde sorun (404 veya sunucu hatası) tespit edildi:');
            $this->table(['Tür', 'İsim', 'Bozuk URL'], $brokenLinks);
        } else {
            $this->info('Tebrikler! Sistemde kırık link bulunamadı. Bütün sayfalar (HTTP 200) sağlıklı çalışıyor.');
        }
    }

    /**
     * URL'ye istek atıp sağlıklı yanıt (200-299) dönüp dönmediğini kontrol eder.
     */
    private function checkUrl($url)
    {
        try {
            // Yerel ortamda SSL hatalarını göz ardı etmek için withoutVerifying kullanıyoruz
            $response = Http::withoutVerifying()->timeout(5)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false; // Sunucuya ulaşılamadı veya zaman aşımı
        }
    }
}
