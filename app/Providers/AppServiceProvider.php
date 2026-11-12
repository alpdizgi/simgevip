<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')) {
            config([
                'app.debug' => false,
                'session.secure' => true,
            ]);
            URL::forceScheme('https');
        }

        if ($root = config('app.url')) {
            URL::forceRootUrl(rtrim($root, '/'));
        }

        // Livewire geçici yükleme dizinini ve public depolama dizinlerini güvenceye al
        try {
            $dirs = [
                storage_path('app/livewire-tmp'),
                storage_path('app/public/products'),
                storage_path('app/public/products/variants'),
                storage_path('app/public/sliders'),
                storage_path('app/public/campaigns'),
                storage_path('app/public/categories'),
            ];
            foreach ($dirs as $dir) {
                if (! file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('settings')) {
                if (! Schema::hasColumn('settings', 'announcements')) {
                    Schema::table('settings', function ($table) {
                        $table->json('announcements')->nullable();
                    });
                }
                if (! Schema::hasColumn('settings', 'whatsapp_number')) {
                    Schema::table('settings', function ($table) {
                        $table->string('whatsapp_number')->nullable();
                    });
                }
                if (! Schema::hasColumn('settings', 'mail_settings')) {
                    Schema::table('settings', function ($table) {
                        $table->json('mail_settings')->nullable();
                    });
                }

                $settings = Setting::query()->first();
                if ($settings && !empty($settings->mail_settings)) {
                    $mailSettings = $settings->mail_settings;
                    
                    config([
                        'mail.mailers.smtp.host' => $mailSettings['host'] ?? config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $mailSettings['port'] ?? config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.encryption' => $mailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
                        'mail.mailers.smtp.username' => $mailSettings['username'] ?? config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $mailSettings['password'] ?? config('mail.mailers.smtp.password'),
                        'mail.from.address' => $mailSettings['from_address'] ?? config('mail.from.address'),
                        'mail.from.name' => $mailSettings['from_name'] ?? config('mail.from.name'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Veritabanı hazır değilse yoksay (örneğin migration aşamasında)
        }

        \Filament\Forms\Components\FileUpload::macro('convertToWebp', function (int $quality = 82, ?int $maxDimension = 1920) {
            /** @var \Filament\Forms\Components\FileUpload $this */
            $component = $this;

            return $this->saveUploadedFileUsing(function ($file) use ($component, $quality, $maxDimension) {
                return app(\App\Services\WebpConverterService::class)->convertAndStore(
                    $file,
                    $component->getDiskName(),
                    $component->getDirectory(),
                    $quality,
                    $maxDimension
                );
            });
        });

        \Filament\Facades\Filament::serving(function () {
            // Admin paneli Türkçe; site (locale) İngilizce kalır
            app()->setLocale('tr');

            if (class_exists(\Carbon\Carbon::class)) {
                \Carbon\Carbon::setLocale('tr');
            }

            // Kullanıcı avatar açılır menüsüne Profil Ayarları ve Siteyi Görüntüle ekliyoruz
            \Filament\Facades\Filament::registerUserMenuItems([
                'profile' => \Filament\Navigation\UserMenuItem::make()
                    ->label('Profil Ayarları')
                    ->url(route('filament.pages.profil'))
                    ->icon('heroicon-s-cog'),
                'view-site' => \Filament\Navigation\UserMenuItem::make()
                    ->label('Siteyi Görüntüle')
                    ->url(url('/'))
                    ->icon('heroicon-s-globe-alt'),
            ]);

            \Filament\Facades\Filament::registerRenderHook(
                'footer.after',
                fn (): string => \Illuminate\Support\Facades\View::make('admin.partials.footer')->render(),
            );

            \Filament\Facades\Filament::registerRenderHook(
                'styles.end',
                fn (): string => '<link rel="stylesheet" href="' . asset('css/admin-theme.css') . '?v=' . filemtime(public_path('css/admin-theme.css')) . '">'
                    . '<link rel="stylesheet" href="' . asset('css/admin-dashboard.css') . '?v=' . filemtime(public_path('css/admin-dashboard.css')) . '">',
            );

            \Filament\Facades\Filament::registerRenderHook(
                'styles.end',
                fn (): string => '<link rel="stylesheet" href="' . asset('css/admin-product-cards.css') . '?v=' . filemtime(public_path('css/admin-product-cards.css')) . '">'
                    . '<link rel="stylesheet" href="' . asset('css/admin-customers.css') . '?v=' . filemtime(public_path('css/admin-customers.css')) . '">'
                    . '<link rel="stylesheet" href="' . asset('css/admin-order-requests.css') . '?v=' . filemtime(public_path('css/admin-order-requests.css')) . '">'
                    . '<link rel="stylesheet" href="' . asset('css/admin-reservations.css') . '?v=' . filemtime(public_path('css/admin-reservations.css')) . '">',
            );

            \Filament\Facades\Filament::registerRenderHook(
                'styles.end',
                fn (): string => request()->routeIs('filament.resources.settings.*')
                    ? '<link rel="stylesheet" href="' . asset('css/admin-settings.css') . '?v=' . filemtime(public_path('css/admin-settings.css')) . '">'
                    : '',
            );

            \Filament\Facades\Filament::registerNavigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                    ->label('İşlemler'),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('Site Ayarları'),
            ]);
        });

        View::composer('store.*', function ($view) {
            $settings = null;
            $menus = collect();
            $navCategories = collect();

            try {
                if (Schema::hasTable('settings')) {
                    $settings = Setting::query()->first();
                }
                if (Schema::hasTable('menus')) {
                    $menus = Menu::query()
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                }
                if (Schema::hasTable('categories')) {
                    $navCategories = Category::query()
                        ->roots()
                        ->inNav()
                        ->with(['navChildren'])
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get();
                }
            } catch (\Throwable $e) {
                // DB hazır değilse site yine açılsın
            }

            $cartCount = 0;
            try {
                $cartCount = app(CartService::class)->count();
            } catch (\Throwable $e) {
                $cartCount = 0;
            }

            $request = request();
            if (! $request->attributes->has('storeFavoriteProductIds')) {
                $favoriteIds = [];
                try {
                    if (Auth::guard('customer')->check() && Schema::hasTable('customer_favorites')) {
                        $favoriteIds = DB::table('customer_favorites')
                            ->where('customer_id', Auth::guard('customer')->id())
                            ->pluck('product_id')
                            ->map(fn ($id) => (int) $id)
                            ->all();
                    }
                } catch (\Throwable $e) {
                    $favoriteIds = [];
                }
                $request->attributes->set('storeFavoriteProductIds', $favoriteIds);
            }

            $view->with([
                'siteSettings' => $settings,
                'siteMenus' => $menus,
                'navCategories' => $navCategories,
                'siteName' => $settings->site_name ?? config('app.name', 'SimgeVIP'),
                'cartCount' => $cartCount,
                'favoriteProductIds' => $request->attributes->get('storeFavoriteProductIds', []),
                'storeWhatsapp' => isset($settings) ? $settings->whatsapp_digits : preg_replace('/\D+/', '', (string) config('store.whatsapp', '905550000000')),
            ]);
        });
    }
}
