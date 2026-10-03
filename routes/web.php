<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerSupportController;
use App\Http\Controllers\GithubDeployController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/link-storage', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Resim bağlantıları (symlink) başarıyla oluşturuldu! Ana sayfaya dönebilirsiniz.';
});

Route::post('/admin/github-gonder', [GithubDeployController::class, 'store'])
    ->middleware(\Filament\Http\Middleware\Authenticate::class)
    ->name('admin.github.deploy');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest:customer')->group(function () {
    Route::get('/giris', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/giris', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
    Route::get('/uye-ol', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/uye-ol', [CustomerAuthController::class, 'register'])->name('customer.register.submit');
});
Route::middleware('auth:customer')->group(function () {
    Route::get('/hesabim', [CustomerAccountController::class, 'index'])->name('customer.account');
    Route::post('/hesabim/destek', [CustomerSupportController::class, 'store'])->name('customer.support.store');
    Route::get('/hesabim/destek/{ticket}', [CustomerSupportController::class, 'show'])->name('customer.support.show');
    Route::post('/hesabim/destek/{ticket}/yanit', [CustomerSupportController::class, 'reply'])->name('customer.support.reply');
    Route::post('/cikis', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::post('/hesabim/favoriler/{product}', [CustomerAccountController::class, 'addFavorite'])->name('customer.favorites.add');
    Route::post('/hesabim/favoriler/{product}/toggle', [CustomerAccountController::class, 'toggleFavorite'])->name('customer.favorites.toggle');
    Route::delete('/hesabim/favoriler/{product}', [CustomerAccountController::class, 'removeFavorite'])->name('customer.favorites.remove');
    Route::patch('/hesabim/sepet', [CustomerAccountController::class, 'updateCart'])->name('customer.cart.update');
    Route::patch('/hesabim/ayirma/{reservation}/iptal', [CustomerAccountController::class, 'cancelReservation'])->name('customer.reservations.cancel');
    Route::post('/hesabim/siparis-talepleri', [CustomerAccountController::class, 'createOrderRequest'])->name('customer.orders.create');
    Route::patch('/hesabim/siparis-talepleri/{orderRequest}/iptal', [CustomerAccountController::class, 'cancelOrderRequest'])->name('customer.orders.cancel');
    Route::patch('/hesabim/profil', [CustomerAccountController::class, 'updateProfile'])->name('customer.profile.update');
    Route::patch('/hesabim/sifre', [CustomerAccountController::class, 'updatePassword'])->name('customer.password.update');
});

Route::get('/koleksiyon', [ShopController::class, 'index'])->name('shop.index');
Route::get('/arama/canli', [ShopController::class, 'liveSearch'])->name('search.live');
Route::get('/kategori/{slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/urun/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/sepet', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::middleware('auth:customer')->group(function () {
    Route::post('/sepet', [\App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::post('/sepet/guncelle', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::post('/sepet/kaldir', [\App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/magazada-ayir', [\App\Http\Controllers\StoreHoldController::class, 'store'])->name('store.hold');
});

Route::get('/hakkimizda', [PageController::class, 'about'])->name('about');
Route::get('/sikca-sorulan-sorular', [\App\Http\Controllers\Store\FaqController::class, 'index'])->name('faq.index');
Route::get('/sayfa/{slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/iletisim', [ContactController::class, 'show'])->name('contact.show');
Route::post('/iletisim', [ContactController::class, 'store'])->name('contact.store');
