# SimgeVIP Konfeksiyon Mağazası Web Uygulaması

Bu proje, SimgeVIP mağazası için ürün tanıtımı ve site yönetim paneli içeren Laravel 8 tabanlı modern bir web uygulamasıdır.

## Kurulum
1. Proje dosyalarını XAMPP `htdocs` dizinine taşıyın.
2. `php artisan migrate` komutunu çalıştırarak veritabanı tablolarını oluşturun.

## Yönetici Paneli (Filament PHP)
Yönetim paneline erişmek için `http://localhost/simgevip/public/admin` adresine gidin.
Sisteme e-posta adresiyle değil, yalnızca **Kullanıcı Adı** ile giriş yapılmaktadır. Şifremi unuttum özelliği güvenlik sebebiyle kapalıdır.

**Giriş Bilgileri:**
- **Kullanıcı Adı:** `admin`
- **Şifre:** `admin`

### CSS ve JS Yapısı
Projedeki tüm özel tasarım gerektiren sayfalar için satıriçi (inline) CSS veya JavaScript kullanılmamıştır. Tüm dosyalar ayrıştırılarak `public` klasörü içerisine yerleştirilmiştir.

**Admin Giriş Sayfası Dosyaları:**
- Backend Controller Sınıfı: `app/Filament/Pages/Auth/Login.php`
- Temel HTML İskeleti: `resources/views/filament/layouts/login-layout.blade.php`
- Görünüm (View): `resources/views/filament/pages/auth/login.blade.php`
- Stil (CSS): `public/css/admin-login.css`
- İşlevsellik (JS): `public/js/admin-login.js`

Aynı şekilde kullanıcı arayüzü (Frontend) tarafında da Vanilla CSS/SCSS tercih edilecek olup, ilgili tüm stil ve script dosyaları `public/css/` ve `public/js/` dizinlerinde modüler olarak tutulacaktır.
