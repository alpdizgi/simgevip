# Kampanya yönetimi ve fiyat akışı incelemesi

Tarih: 30.09.2026. Uygulama davranışı değiştirilmedi. Kontroller yerel kaynak kodu, kurulu Filament/Livewire kodu ve geçici SQLite `:memory:` veritabanındaki senaryolarla yapıldı. Gerçek kampanya, ürün veya müşteri kaydı değiştirilmedi. HTTP isteği giriş sayfasına yönlendi; oturumlu tarayıcıda görsel/etkileşim testi yapılmadı.

## Kapsam ve mevcut akış

1. `CampaignResource` kampanya başlığı, türü, oranı, tarihleri, yayın durumu ve ürün ilişkilerini kaydediyor.
2. Ürün ilişkisi hem ana formdaki çoklu seçim hem `ProductsRelationManager` üzerinden değiştirilebiliyor.
3. `Campaign::active()` sorgusu ve `isCurrentlyActive()` metodu tarih/yayın uygunluğunu belirliyor.
4. `Product::direct_discount_percent` önce ürüne özel indirimi, sonra ilk uygun doğrudan kampanyayı seçiyor. `activeNthCampaign()` bağımsız olarak ilk uygun X. ürün kampanyasını seçiyor.
5. `CartService::items()` güncel doğrudan fiyatı alıyor; kampanyaya bağlı adetleri sepet satırı sırasına göre grupluyor ve her X. adede ek indirim uyguluyor.
6. Ana sayfa yalnızca en yeni aktif, ürün bağlı kampanyayı öne çıkarıyor. Ürün listesi, ürün detayı, arama, favoriler ve sepet Product üzerindeki hesaplanmış fiyat/rozetleri kullanıyor.
7. Sepet üzerinden sipariş talebi fiyatlandırılmış satırları; ürün üzerinden talep ise tek ürünün o anki birim fiyatını kaydediyor. WhatsApp metni sepet servisi tarafından oluşturuluyor.

## P1 — Önce giderilmesi gereken doğrulanmış hatalar

### 1. Geçersiz indirim oranı negatif fiyat üretiyor

- Kaynak: `app/Filament/Resources/CampaignResource.php:73`, `app/Models/Product.php:331`.
- Formda oran için alt/üst sınır yok. Modelde doğrudan fiyat hesaplamasında da koruma yok.
- Livewire oluşturma formu %150 oranını kabul edip kaydetti. 100 TL üründe hesaplanan fiyat **−50 TL** oldu.
- Negatif oran da fiyat artırabilir. X. ürün hesabında oran 0–100 aralığına sıkıştırılırken doğrudan indirim hesabında sıkıştırılmaması ayrıca tutarsızlık yaratıyor.
- Öneri: sunucu tarafında 0'dan büyük, en fazla 100 oran doğrulaması; hesaplama katmanında geçersiz veriyi uygulamama. %100 kampanyaya izin verilip verilmeyeceği açıkça kararlaştırılmalı.

### 2. İki ürün yönetimi kontrolü birbirinin değişikliğini geri alıyor

- Kaynak: `CampaignResource.php:98`, `CampaignResource/RelationManagers/ProductsRelationManager.php:64`; Filament Select ilişkileri kaydederken `sync()` kullanıyor.
- Doğrulama: ana form ürün 1 bağlıyken açıldı; sonrasında ilişkiye ürün 2 eklendi. Ana form kaydedildiğinde bağlı ürünler **[1,2] → [1]** oldu.
- Bu senaryo ayrı ilişki yöneticisinden veya başka bir oturumdan yapılan değişiklikler için önemlidir. Tersi yönde, çıkarılmış ürünün eski form seçimiyle geri bağlanması da aynı mekanizmanın sonucudur.
- Öneri: düzenlemede tek ürün yönetimi noktası; oluşturma sırasında başlangıç seçimi tutulabilir. Alternatif olarak açık yenileme/senkronizasyon ve çakışma denetimi gerekir.

### 3. Detay penceresi açılırken hata oluşuyor

- Kaynak: `CampaignResource.php:272` civarındaki modal düzenleme bağlantısı.
- Geçerli kayıt ve normal başlıkla `mountTableAction('view_details', id)` çağrısı hata verdi:
  `Missing required parameter for [Route: filament.resources.campaigns.edit] ... [Missing parameter: record]`.
- Alt modal aksiyonundaki `fn (Campaign $record)` üst tablo aksiyonunun kaydını otomatik almıyor.
- Öneri: URL'yi kayıt bağlamının bulunduğu üst aksiyonda üretmek veya açık kayıt aktarımı yapmak. Modal açma testi eklenmeli.

### 4. Geçersiz tarih aralığı kaydedilebiliyor

- Kaynak: `CampaignResource.php:116`.
- Başlangıç 10.10.2026 ve bitiş 01.10.2026, gerçek Livewire oluşturma formundan kabul edildi.
- Böyle bir kayıt hiç aktif olmayabilir; planlanan ve süresi biten sorgularına aynı anda girebilir.
- Öneri: bitiş için başlangıca eşit veya sonra olma kuralı; aynı gün kampanyasına izin verilmeli.

### 5. HTML içinde kampanya başlığı kaçışsız kullanılıyor

- Kaynak: `CampaignResource.php:291`: `<img ... alt='{$record->title}' ...>` doğrudan `HtmlString` içinde.
- Tek tırnak içeren başlık HTML niteliğini bozabilir; HTML/nitelik enjeksiyonu riski var. Kampanya görseli varsa bu yol kullanılıyor.
- Kaynak kodundan doğrulandı; tarayıcıda JavaScript çalıştırma denemesi yapılmadı. Mevcut modal hatası, bu ayrı sorunun çözümü sayılmaz.
- Öneri: detay içeriğini Blade şablonuna taşıyıp başlık, URL ve diğer tüm dinamik alanlarda bağlama uygun kaçış kullanmak.

## Kampanya politikasında karar gerektiren davranışlar

### 6. Çoklu kampanyada indirim seçimi ilişki sırasına bağlı

- Kaynak: `Product.php:253–287`.
- 100 TL ürüne %10 ve %50 doğrudan kampanya verilince ilişki sırası [10,50] için fiyat **90 TL**, [50,10] için **50 TL** oldu.
- En yüksek indirim, tarih önceliği veya yönetici önceliği tanımlı değil; ilk uygun kayıt seçiliyor. X. ürün kampanyalarında da ilk kayıt yaklaşımı var.
- Ürüne özel indirim bağlı doğrudan kampanyaları öncelikli olarak geçersiz kılıyor.
- Karar: en avantajlı kampanya mı, yönetici önceliği mi, yoksa aynı ürüne tek kampanya mı? Teknik düzeltme bu karara göre yapılmalı.

### 7. X. ürün indirimi sepet sırasına göre farklı tutar veriyor

- Kaynak: `CartService.php:115–176`.
- Aynı kampanyadaki A=100 TL ve B=1.000 TL; ikinci ürüne %50:
  - Sepet sırası A,B → toplam **600 TL**.
  - Sepet sırası B,A → toplam **1.050 TL**.
- Kodun yorumu açıkça sepet sırasını esas alıyor; bu nedenle tek başına algoritmanın yanlış olduğu söylenemez. Ancak yönetim formu ve müşteri metni bu ticari kuralı açıklamıyor.
- Yalnız kampanyaya dahil ürünler sayılıyor; rozet ise “sepetin X. ürünü” diyor. Kampanya dışı ürünlerle birlikte yanlış beklenti doğabilir.
- Kural her X. adette tekrarlanıyor (2,4,6…); yalnız bir kez uygulanmıyor. Aynı üründen birden fazla adet de sayılıyor.
- Karar: en ucuz ürüne mi, en pahalıya mı, ekleme sırasına mı; her grupta tekrar mı; kampanyalar arasında adet paylaşımı mı?

### 8. Doğrudan indirim ile X. ürün indirimi birleşiyor

- Kaynak: `CartService.php:34`, `CartService.php:88`, `Product.php:253`.
- 100 TL'den iki ürün; doğrudan %50 ve ikinci adede ayrıca %50 → **50 + 25 = 75 TL**.
- Kampanyaları birleştirme ayarı yok. Ürüne özel indirim de X. ürün indirimiyle birleşebilir.
- Öneri: “birleştirilebilir” kuralı veya açık tek-kampanya politikası; hesaplama ve müşteri açıklaması aynı kararı izlemeli.

## P2 — Mağaza sunumu, tutarlılık ve operasyon

### 9. Ana sayfadaki kampanya çağrısı doğru ürünlere gitmiyor

- Kaynak: `resources/views/store/home.blade.php:194–196`, `ShopController::index`, `routes/web.php`.
- “Kampanya ürünleri” bağlantısı filtre olmadan genel mağazayı açıyor. Kampanya slug'ına bağlı mağaza rotası veya kampanya filtresi yok.
- `nth_item` kampanyaları da koşulu belirtilmeden düz “%X indirim” şeklinde tanıtılıyor.
- Öneri: kampanyaya özel liste/filtre, geçerlilik kontrolü ve türüne uygun başlık/açıklama.

### 10. Fiyat ve rozet farklı kampanyalara dayanabiliyor

- Kaynak: `Product.php:270–314`.
- Doğrudan fiyat ve rozet ayrı `first()` seçimleri yapıyor. Doğrulanan örnek: 100 TL üründe rozet “%50 · sepetin 2. ürününe”, görünen birim fiyat **90 TL** (%10 başka kampanyadan).
- Birleşen indirimin tüm koşulları gösterilmiyor. Yönetici listesi/detail oranı `(int)` ile kesiyor; %12,50, yönetimde %12 olarak görünebilir.
- Öneri: tek fiyatlandırma sonucu içinde uygulanan kampanyalar, oranlar ve rozet açıklamaları birlikte üretilmeli.

### 11. Tarihler Türkiye yerine UTC gününe göre çalışıyor

- Kaynak: `config/app.php`, `Campaign.php:scopeActive/isCurrentlyActive`.
- Çalışma anında uygulama saat dilimi **UTC** olarak doğrulandı.
- Türkiye yerel takvim günü hedefleniyorsa kampanya başlangıcı ve bitişi 03:00'a kayar. Bu bir iş beklentisi varsayımıdır; saat dilimi kesinleştirilmelidir.
- Olumlu kontrol: uygulamanın kendi saat diliminde aynı gün başlangıç/bitiş, gelecekte başlangıç, geçmiş bitiş ve tarihsiz kampanya için scope ile model sonucu **4/4 tutarlı**.

### 12. X. ürün alanında tam sayı doğrulaması yok

- Kaynak: `CampaignResource.php:80`, `CartService.php:140`.
- `numeric` ve `min:2` var; integer kuralı yok. SQLite senaryosunda 2,5 değeri formdan kaydedildi; algoritma `(int)` ile 2'ye indiriyor.
- Gerçek MySQL unsigned integer kolonunun sonucu SQL moduna göre farklı olabilir; üretim veritabanında 2,5'in aynen saklandığı iddia edilmiyor. Kesin hata, form doğrulamasının bu girdiyi engellememesi.
- Öneri: integer + min:2; kampanya tipi için açık izinli değer kuralı; tür değişince kullanılmayan alanın temizlenmesi.

### 13. Özet kartlarındaki grafikler gerçek değil

- Kaynak: `CampaignStatsWidget.php`.
- Dört grafikte sabit diziler var; sıfır kayıt varken bile artış eğrisi gösterilebilir.
- “Kampanyalı Ürün Sayısı” tüm pivot ilişkilerini sayıyor; pasif ve bitmiş kampanyalar dahil. Açıklama toplam bağlı ürün diyor; aktif indirimli ürün sayısı olarak yorumlanmamalı.
- Öneri: grafik kaldırılmalı veya gerçek zaman serisi kullanılmalı. Aktif ve toplam bağlı ürün kavramları ayrılmalı.

### 14. Fiyata göre sıralama görünen indirimli fiyatla uyuşmuyor

- Kaynak: `ShopController.php:applySort`.
- Liste `price` kolonuna göre sıralanıyor, kart indirimli fiyatı gösteriyor. Örneğin 1.000→500 TL ürün, 600 TL indirimsiz ürünün arkasında kalabilir.
- Öneri: doğrudan indirim sonrası fiyatı sıralamaya dahil etmek; sepete bağlı X. ürün indirimini tek ürün fiyatına karıştırmamak.

### 15. Sipariş kaydında fiyat dökümü iki giriş yolunda farklı

- Kaynak: `CustomerAccountController.php:148–184`.
- Sepet yolu normalize edilmiş satırları saklıyor. Tek ürün yolu yalnız mevcut birim fiyatı saklıyor; normal fiyat ve uygulanan kampanya kimliği/oranı bulunmuyor.
- Sonradan kampanya silinince/değişince neden o fiyatın verildiğini denetlemek zorlaşıyor. Tek ürün yolunda X. ürün indirimi uygulanmaması, adet 1 olduğu için beklenen davranış.
- Kalemler, toplam ve WhatsApp metni ayrı ayrı fiyatlandırılıyor. Arada kampanya/fiyat değişirse farklı sonuç riski var; yarış durumu runtime olarak üretilmedi.
- Öneri: talep başına tek fiyatlandırma sonucu; değişmez kampanya ve fiyat dökümü kaydı.

### 16. Sepet stok/miktar doğrulamasında kampanyayı etkileyen açıklar var

- Kaynak: `CartService::add`, `updateQty`, `items`; `CustomerAccountController::createOrderRequest`.
- Eklemede stokun en az 1 olması kontrol ediliyor, istenen toplam adedin stokla karşılaştırılması yok. Tekrarlı eklemede satır miktarı artıyor; updateQty stok kontrolü yapmıyor.
- Ürün silinirse `items()` eski satırı düşürmek yerine eski fiyatla tutabiliyor. Talep kaydından önce tekrar uygunluk kontrolü yok.
- Bu durum X. ürün eşiğini gerçekte karşılanamayacak adetlerle tamamlayabilir. Kod bulgusudur; gerçek stok kaydı değiştirilmedi.
- Öneri: talep öncesinde ürün/varyant/beden/adet uygunluğunu tek noktada kontrol etmek. Rezervasyon veya stok düşme kararı ayrıca belirlenmeli.

### 17. Gereksiz tekrar sorguları ve UI yapısı

- `CartService::summary()` bir yanıtta `items()` hesabını altı kez çağırıyor; ürün/kampanya sorguları ve X. ürün hesabı tekrar ediyor.
- Kampanya detay ürün önizlemesi yalnız kategoriyi eager load ediyor; görsel/fiyat/stok ilişkileri ek sorgular üretebilir. Gerçek veri boyutunda performans ölçümü yapılmadı.
- Kampanya detayı sabit açık renkli satır içi CSS ve masaüstü için sabit üç sütun kullanıyor. Mobil ve koyu tema için uygun yapıya taşınmalı.
- Filtre/işlem ikonları etiket ve hizalama bakımından son düzenlenen sayfalardan farklı; genel tema hücre ve araç çubuğu boşlukları burada da üst üste uygulanıyor.
- `campaign_product` şemasında çift için unique constraint yok. Standart arayüz tekrar ilişkiyi engellese bile eşzamanlı/alternatif yazımlar için DB güvencesi bulunmuyor.

## Önerilen uygulama sırası

1. Oran/tür/adet/tarih doğrulamaları ve hesaplamada geçersiz veri koruması; mevcut kayıtların salt okunur taraması.
2. Modal bağlantı hatası ve kaçışsız HTML; ürün ilişkisi için tek yönetim noktası.
3. Kampanya önceliği, birleştirme ve X. ürün dağıtım politikasını kararlaştırma.
4. Tek fiyatlandırma sonucu: ürün fiyatı, rozet, sepet, sipariş kaydı ve WhatsApp aynı sonucu kullanmalı.
5. Kampanya ürünlerine doğru yönlendirme, doğru tanıtım metni, saat dilimi ve görünen fiyata göre sıralama.
6. Gerçek istatistikler, sorgu azaltma, mobil/koyu tema ve son görsel kontrol.

## Doğrulama ve sınırlar

- Geçici deneme kodu yalnız SQLite bellek veritabanına yazdı; inceleme sonunda kaldırıldı.
- Negatif fiyat, oran/tarih doğrulama eksikliği, ilişki kaybı, sıralamaya bağlı fiyat, indirimlerin birleşmesi, modal hatası, ondalık X. ürün ve tarih sınırları uygulama koduyla çalıştırıldı.
- XSS bulgusu kaynak kodu incelemesidir; JavaScript yürütülmedi.
- MySQL veri kalitesi taraması, gerçek tarayıcı mobil/masaüstü/koyu tema ve eşzamanlı istek yük testi yapılmadı.
- Kampanya davranışını değiştirecek bir düzeltme bu incelemeye dahil edilmedi.
