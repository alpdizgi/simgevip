<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WomenBoutiqueProductsSeeder extends Seeder
{
    public function run()
    {
        // 1. Dizinleri hazırla
        $productsDir = storage_path('app/public/products');
        $variantsDir = storage_path('app/public/products/variants');

        if (!File::isDirectory($productsDir)) {
            File::makeDirectory($productsDir, 0755, true);
        }
        if (!File::isDirectory($variantsDir)) {
            File::makeDirectory($variantsDir, 0755, true);
        }

        // 2. Mevcut ürünleri ve varyantları temizle
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductVariant::truncate();
        if (DB::getSchemaBuilder()->hasTable('campaign_product')) {
            DB::table('campaign_product')->truncate();
        }
        Product::truncate();

        // Erkek giyim ve gereksiz ana kategorileri kaldır
        Category::where('slug', 'like', '%erkek%')
            ->orWhere('name', 'like', '%erkek%')
            ->delete();
        Category::where('slug', 'kadin-giyim')->whereNull('parent_id')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Kategorileri doğrula ve güncelle/oluştur
        $categoriesMap = $this->setupCategories();

        // 4. Kaliteli görsel kütüphanesi hazırla (Görseller yerel olarak oluşturulur)
        $this->ensureImagesExist($productsDir, $variantsDir);

        // 5. Her kategoriye 8 adet kaliteli kadın giyim ürünü ekle
        $this->seedProducts($categoriesMap);
    }

    protected function setupCategories(): array
    {
        // Ana ve alt kategori tanımları
        $structure = [
            'elbise' => [
                'name' => 'Elbise',
                'parent' => null,
                'sort' => 1,
            ],
            'ikili-takim' => [
                'name' => 'İkili Takım',
                'parent' => null,
                'sort' => 2,
                'children' => [
                    'ikili-takim-etekli-takim'     => ['name' => 'Etekli Takım', 'sort' => 1],
                    'ikili-takim-pantolonlu-takim' => ['name' => 'Pantolonlu Takım', 'sort' => 2],
                ]
            ],
            'uclu-takim' => [
                'name' => 'Üçlü Takım',
                'parent' => null,
                'sort' => 3,
                'children' => [
                    'uclu-takim-pantolonlu-takim' => ['name' => 'Pantolonlu Takım', 'sort' => 1],
                    'uclu-takim-etekli-takim'     => ['name' => 'Etekli Takım', 'sort' => 2],
                ]
            ],
            'ust-giyim' => [
                'name' => 'Üst Giyim',
                'parent' => null,
                'sort' => 4,
                'children' => [
                    'ust-giyim-gomlek' => ['name' => 'Gömlek & Bluz', 'sort' => 1],
                    'ust-giyim-tunik'  => ['name' => 'Tunik', 'sort' => 2],
                    'ust-giyim-kazak'  => ['name' => 'Triko & Kazak', 'sort' => 3],
                    'ust-giyim-hirka'  => ['name' => 'Hırka', 'sort' => 4],
                    'ust-giyim-sweat'  => ['name' => 'Sweatshirt', 'sort' => 5],
                    'ust-giyim-iclik'  => ['name' => 'İçlik', 'sort' => 6],
                ]
            ],
            'alt-giyim' => [
                'name' => 'Alt Giyim',
                'parent' => null,
                'sort' => 5,
                'children' => [
                    'alt-giyim-pantolon' => ['name' => 'Pantolon', 'sort' => 1],
                    'alt-giyim-etek'     => ['name' => 'Etek', 'sort' => 2],
                    'alt-giyim-jean'     => ['name' => 'Jean', 'sort' => 3],
                ]
            ],
            'dis-giyim' => [
                'name' => 'Dış Giyim',
                'parent' => null,
                'sort' => 6,
                'children' => [
                    'dis-giyim-trenckot' => ['name' => 'Trençkot', 'sort' => 1],
                    'dis-giyim-ceket'    => ['name' => 'Ceket & Blazer', 'sort' => 2],
                    'dis-giyim-kaban'    => ['name' => 'Kaban & Palto', 'sort' => 3],
                    'dis-giyim-yelek'    => ['name' => 'Yelek', 'sort' => 4],
                    'dis-giyim-mont'     => ['name' => 'Mont', 'sort' => 5],
                    'dis-giyim-kap'      => ['name' => 'Kap', 'sort' => 6],
                ]
            ],
            'sal' => [
                'name' => 'Şal & Eşarp',
                'parent' => null,
                'sort' => 7,
            ],
            'aksesuar' => [
                'name' => 'Aksesuar',
                'parent' => null,
                'sort' => 8,
            ],
        ];

        $map = [];

        foreach ($structure as $slug => $data) {
            $cat = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'parent_id' => null,
                    'sort_order' => $data['sort'],
                    'show_in_nav' => true,
                ]
            );
            $map[$slug] = $cat;

            if (!empty($data['children'])) {
                foreach ($data['children'] as $cSlug => $cData) {
                    $childCat = Category::updateOrCreate(
                        ['slug' => $cSlug],
                        [
                            'name' => $cData['name'],
                            'parent_id' => $cat->id,
                            'sort_order' => $cData['sort'],
                            'show_in_nav' => true,
                        ]
                    );
                    $map[$cSlug] = $childCat;
                }
            }
        }

        return $map;
    }

    protected function ensureImagesExist(string $productsDir, string $variantsDir): void
    {
        // Kategori temalı şık görsel paletleri
        $palettes = [
            'elbise' => ['#e8d5c4', '#c9a88d', '#8b5a3e', '#2c3e50', '#7d4f50'],
            'takim' => ['#d5dbdb', '#5d6d7e', '#34495e', '#1c2833', '#85929e'],
            'ust' => ['#fce4ec', '#f8bbd0', '#e1bee7', '#d1c4e9', '#c5cae9'],
            'alt' => ['#cfd8dc', '#90a4ae', '#607d8b', '#455a64', '#37474f'],
            'dis' => ['#d7ccc8', '#bcaaa4', '#8d6e63', '#6d4c41', '#4e342e'],
            'sal' => ['#f3e5f5', '#e1bee7', '#ce93d8', '#ba68c8', '#ab47bc'],
            'aksesuar' => ['#fff3e0', '#ffe0b2', '#ffcc80', '#ffb74d', '#ffa726'],
        ];

        // 40 adet ürün ana görseli oluştur/kontrol et
        for ($i = 1; $i <= 40; $i++) {
            $pFilename = "product-img-{$i}.svg";
            $pPath = $productsDir . DIRECTORY_SEPARATOR . $pFilename;
            if (!File::exists($pPath)) {
                $type = ['elbise', 'takim', 'ust', 'alt', 'dis', 'sal', 'aksesuar'][$i % 7];
                $color = $palettes[$type][$i % 5];
                $this->createSvgImage($pPath, "SimgeVIP Kadın Butik", "Model #{$i}", $color);
            }
        }

        // 30 adet varyant görseli oluştur/kontrol et
        for ($i = 1; $i <= 30; $i++) {
            $vFilename = "variant-img-{$i}.svg";
            $vPath = $variantsDir . DIRECTORY_SEPARATOR . $vFilename;
            if (!File::exists($vPath)) {
                $colors = ['#111827', '#f8fafc', '#f5ebe0', '#4a5568', '#78350f', '#14532d', '#7f1d1d', '#1e3a8a'];
                $c = $colors[$i % count($colors)];
                $this->createSvgImage($vPath, "SimgeVIP Koleksiyonu", "Renk Seçeneği", $c);
            }
        }
    }

    protected function createSvgImage(string $path, string $brand, string $title, string $bgColor): void
    {
        $textColor = (hexdec(substr($bgColor, 1, 2)) * 0.299 + hexdec(substr($bgColor, 3, 2)) * 0.587 + hexdec(substr($bgColor, 5, 2)) * 0.114) > 186 ? '#1e293b' : '#ffffff';
        $subColor = $textColor === '#ffffff' ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.6)';

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$bgColor};stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1e293b;stop-opacity:0.25" />
        </linearGradient>
    </defs>
    <rect width="800" height="1000" fill="url(#grad)" />
    <rect x="40" y="40" width="720" height="920" fill="none" stroke="{$subColor}" stroke-width="1.5" stroke-dasharray="6,6" opacity="0.4"/>
    
    <g transform="translate(400, 470)" text-anchor="middle">
        <text y="-50" font-family="'Inter', 'Helvetica Neue', sans-serif" font-size="20" font-weight="500" letter-spacing="6" fill="{$subColor}">{$brand}</text>
        <text y="10" font-family="'Inter', 'Helvetica Neue', sans-serif" font-size="34" font-weight="700" letter-spacing="1" fill="{$textColor}">{$title}</text>
        <line x1="-60" y1="40" x2="60" y2="40" stroke="{$subColor}" stroke-width="2" opacity="0.8"/>
        <text y="70" font-family="'Inter', 'Helvetica Neue', sans-serif" font-size="16" font-weight="400" letter-spacing="2" fill="{$subColor}">KADIN GİYİM &amp; BUTİK</text>
    </g>
</svg>
SVG;
        File::put($path, $svg);
    }

    protected function seedProducts(array $categoriesMap): void
    {
        // 16 Kategori için 10'ar adet özel kadın giyim ürünü tanımları
        $catalog = [
            'elbise' => [
                'code' => 'ELB',
                'items' => [
                    ['Kuşaklı Keten Gömlek Elbise', 1250, 'Gömlek yaka, boydan sedef düğmeli, beli ayarlanabilir kuşaklı doğal keten elbise.'],
                    ['Pileli Şifon Abiye Elbise', 1890, 'Özel davetler için tasarlanmış, tam astarlı ve dökümlü pilise şifon kumaş.'],
                    ['Volanlı Poplin Midi Elbise', 1150, 'Etek ucu fırfırlı, pamuklu nefes alan poplin kumaşıyla gün boyu ferahlık.'],
                    ['Beli Büzgülü Saten Elbise', 1650, 'İpeksi parlak saten doku, zarif manşetler ve dökümlü etek kesimi.'],
                    ['Düğme Detaylı Triko Elbise', 1350, 'Mevsimlik esnek ribana triko kumaş, metal gold düğme garnili.'],
                    ['Kruvaze Yaka Keten Elbise', 1450, 'Şık kruvaze kapama, geniş cepli ve doğal taş rengi keten dokuma.'],
                    ['Çiçek Desenli Şifon Elbise', 1290, 'Pastel tonlarda soft floral desen, tül astar ve balon kol detaylı.'],
                    ['Fermuarlı Spor Kesim Elbise', 1090, 'Günlük şıklık sunan iki iplik penye kumaş, dik yaka ve metal fermuarlı.'],
                    ['Kemerli Kaşkorse Midi Elbise', 1190, 'Vücudu toparlayan elastan ribana doku, gold tokalı deri kemer hediyeli.'],
                    ['Kat Kat Robalı Bohem Elbise', 1390, 'Geniş roba kesimli, salaş ve rahat pamuk vual kumaştan bohem tasarım.'],
                ],
                'pattern' => 'Rahat Kalıp',
                'length' => 'Maxi (135 cm)',
                'fabric_type' => 'Doğal Keten & Pamuk',
                'lining' => 'Gövde Astarlı',
                'collar' => 'Gömlek Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'ikili-takim' => [
                'code' => 'IKT',
                'items' => [
                    ['Double Krep Pantolonlu Takım', 2150, 'Dökümlü dökümlü double krep kumaş, tunik boy ceket ve boru paça pantolon.'],
                    ['Beli Kuşaklı Etekli Takım', 1950, 'Kuşaklı tunik ve kloş etekten oluşan zarif ve modern iki parça takım.'],
                    ['Oversize Gömlek & Palazzo Takım', 1850, 'Yazlık viskon kumaş, salaş gömlek ve beli lastikli geniş paça pantolon.'],
                    ['Yelekli İkili Keten Takım', 1750, 'Kruvaze kesim uzun yelek ve havuç kesim keten pantolon kombini.'],
                    ['Cepli Poplin İkili Takım', 1650, 'Önü çift kapak cepli salaş gömlek ve rahat kesim pantolon.'],
                    ['Büzgü Detaylı Triko Takım', 1990, 'İnce dokulu likralı triko, boğazlı kazak ve dökümlü triko pantolon.'],
                    ['Düğmeli Etekli Takım', 1890, 'Boydan düğmeli üst ve pilili etek, pamuk karışımlı dokuma kumaş.'],
                    ['Fermuarlı Spor İkili Takım', 1550, 'Haftasonu konforu için ribanalı eşofman üstü ve jogger pantolon.'],
                    ['Asimetrik Tunik & Pantolon Takım', 2050, 'Asimetrik kesim modern tunik ve cigarette pantolon uyumu.'],
                    ['Piliseli İkili Abiye Takım', 2350, 'Şık akşamlara özel sim detaylı piliseli dökümlü kumaş.'],
                ],
                'pattern' => 'Dökümlü Rahat Kesim',
                'length' => 'Tunik 90 cm / Pantolon 102 cm',
                'fabric_type' => 'İthal Double Krep',
                'lining' => 'Astarsız (İç Göstermez)',
                'collar' => 'Hakim Yaka',
                'sleeve' => 'Standart Uzun Kol',
            ],

            'uclu-takim' => [
                'code' => 'UCT',
                'items' => [
                    ['Krep Kimonolu Üçlü Takım', 2650, 'Uzun kimono, iç askılı bluz ve havuç kesim pantolondan oluşan lüks 3\'lü set.'],
                    ['Yelekli Klasik Üçlü Takım', 2450, 'Blazer ceket, kruvaze yelek ve boru paça kumaş pantolon takımı.'],
                    ['Keten Hırkalı Üçlü Takım', 2250, 'Doğal keten uzun hırka, pamuklu iç atlet ve beli lastikli pantolon.'],
                    ['Şifon Pelerinli Abiye 3\'lü Takım', 2850, 'Dökümlü şifon pelerin, taş işlemeli iç bluz ve yüksek bel pantolon.'],
                    ['Triko Ceketli Üçlü Takım', 2350, 'Düğmeli triko ceket, bisiklet yaka iç kazak ve triko etek.'],
                    ['Trençkotlu Üçlü Kombin Takım', 2750, 'Mevsimlik trençkot, basic iç gömlek ve palazzo pantolon.'],
                    ['Saten İçlikli Şık Üçlü Takım', 2550, 'Kruvaze ceket, degaje yaka saten içlik ve dökümlü pantolon.'],
                    ['Kapüşonlu Spor Üçlü Takım', 1950, 'Fermuarlı kapüşonlu hırka, bisiklet yaka tişört ve rahat jogger.'],
                    ['Kuşaklı Ceketli Üçlü Takım', 2490, 'Geniş kemerli ceket, V yaka iç bluz ve cigarette pantolon.'],
                    ['Baskılı Şal Detaylı Üçlü Takım', 2390, 'Uyumlu desenli şal, dökümlü tunik ve boru paça pantolon seti.'],
                ],
                'pattern' => 'Özel Tasarım Kalıp',
                'length' => 'Dış Parça 110 cm / Pantolon 102 cm',
                'fabric_type' => 'Premium Krep & Keten',
                'lining' => 'Kısmi Astarlı',
                'collar' => 'Şal Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'ust-giyim-gomlek' => [
                'code' => 'GMK',
                'items' => [
                    ['Gizli Patlı Poplin Gömlek', 850, '%100 pamuklu poplin, gizli düğme patı ve manşet detaylı modern gömlek.'],
                    ['Drapeli İpek Saten Bluz', 980, 'Dökümlü döküm saten kumaş, zarif boyun drapeli şık davet bluzu.'],
                    ['Oversize Çizgili Keten Gömlek', 920, 'Casual tarzda ince çizgili keten, düşük omuz salaş kesim.'],
                    ['Fırfır Yakalı Romantik Bluz', 890, 'Yaka ve kol ağızlarında romantik fırfır garnili pamuklu kumaş.'],
                    ['Boydan Düğmeli Viskon Tunik Gömlek', 950, 'Yumuşak dökümlü viskon kumaş, uzun ve arkası hafif oval kesim.'],
                    ['Kravat Yaka Şifon Bluz', 990, 'Zarif boyun fiyonk bağı, hafif şeffaf kollu ve astarlı gövde.'],
                    ['Cepli Safari Keten Gömlek', 940, 'Kapaklı çift göğüs cepli, katlanabilir apoletli spor gömlek.'],
                    ['İncili Pamuklu Beyaz Gömlek', 890, 'Yaka uçlarında mini inci zımbalı zamansız klasik beyaz gömlek.'],
                    ['Kolları Büzgülü Desenli Bluz', 910, 'Lastikli manşetler, geometrik modern desenli dökümlü kumaş.'],
                    ['Kruvaze Kapama Şık Bluz', 960, 'Yandan bağlamalı kruvaze form, ofis ve günlük kullanıma uygun.'],
                ],
                'pattern' => 'Standart & Rahat',
                'length' => 'Standart (75 cm)',
                'fabric_type' => '%100 Pamuk Poplin & Saten',
                'lining' => 'Astarsız',
                'collar' => 'Gömlek Yaka',
                'sleeve' => 'Manşetli Uzun Kol',
            ],

            'ust-giyim-tunik' => [
                'code' => 'TNK',
                'items' => [
                    ['Yırtmaçlı Basic Pamuk Tunik', 890, 'Yanları derin yırtmaçlı, rahat adımlama sağlayan penye tunik.'],
                    ['Garnili İki Renk Keten Tunik', 1050, 'Zıt renk bloklu tasarım, doğal keten dokusuyla ferah kullanım.'],
                    ['Beli Büzgülü Cepli Tunik', 980, 'İçten ayarlanabilir büzgü ipi, çift fonksiyonel cep detaylı.'],
                    ['Fermuarlı Spor Viskon Tunik', 920, 'Yakadan göğse kadar metal fermuar kapama, modern dik yaka.'],
                    ['Kolu Lastikli Şifon Garnili Tunik', 1120, 'Etek ucu ve kol ağzı şifon katmanlı şık tasarım tunik.'],
                    ['Piliseli Asimetrik Tunik', 1080, 'Önü kısa arkası uzun asimetrik etek ucu, piliseli kumaş dokusu.'],
                    ['Kapüşonlu Kanguru Cepli Tunik', 850, 'Haftasonu konforu sunan pamuklu iki iplik spor tunik.'],
                    ['Metal Tokalı Kuşaklı Tunik', 1150, 'Özel kemer tokası, dökümlü medine ipeği kumaştan şık silüet.'],
                    ['Düğme Detaylı Triko Tunik', 990, 'Yan yırtmaçlarında dekoratif sedef düğmeli triko tunik.'],
                    ['Boydan Düğmeli Salaş Tunik', 940, 'Gömlek boyu aşan uzun kesim, dökümlü pamuk kumaş.'],
                ],
                'pattern' => 'Oversize Tunik Kalıp',
                'length' => 'Uzun Tunik (95 cm)',
                'fabric_type' => 'Viskon & Keten Karışımlı',
                'lining' => 'Astarsız',
                'collar' => 'Dik Yaka / Hakim Yaka',
                'sleeve' => 'Düşük Omuz Uzun Kol',
            ],

            'ust-giyim-kazak' => [
                'code' => 'KZK',
                'items' => [
                    ['Balıkçı Yaka Kaşmir Dokulu Kazak', 1150, 'Yumuşacık kaşmir hissi veren özel iplik, tam balıkçı yaka.'],
                    ['Örgü Desenli Salaş Triko Kazak', 990, 'Saç örgüsü kabartmalı tasarım, düşük omuz ve salaş form.'],
                    ['V Yaka İtalyan Yünlü Kazak', 1250, 'Hafif ve sıcak tutan merinos yün karışımlı V yaka kazak.'],
                    ['Çizgili Marin Triko Kazak', 920, 'Zamansız lacivert-ekru çizgili marin stil, kayık yaka kesim.'],
                    ['Yarasa Kol Yumuşak Dokulu Kazak', 1080, 'Dökümlü yarasa kol tasarımı, daralan manşetlerle çok şık.'],
                    ['Kolları Fırfırlı Fitilli Kazak', 960, 'Elastan ribana fitil, omuzlarda zarif fırfır detaylı.'],
                    ['Fermuarlı Polo Yaka Triko', 1020, 'Yarım fermuarlı polo yaka, modern şehir stili kazak.'],
                    ['Sim İplikli Şık Triko Kazak', 1180, 'Hafif sim parıltılı lüks iplik, akşam davetlerine uygun triko.'],
                    ['Karpuz Kol Triko Kazak', 950, 'Omuzda pileli hacimli kol formu, yuvarlak yaka günlük kazak.'],
                    ['Oversize Boğazlı Triko Kazak', 1120, 'Geniş boğaz kesimi, dökümlü etek ucu yırtmaçlı triko.'],
                ],
                'pattern' => 'Yumuşak Triko Kalıp',
                'length' => 'Standart (68 cm)',
                'fabric_type' => '%60 Yün & Akrilik Karışımlı',
                'lining' => 'Astarsız',
                'collar' => 'Balıkçı / Bisiklet Yaka',
                'sleeve' => 'Standart Kol',
            ],

            'ust-giyim-hirka' => [
                'code' => 'HRK',
                'items' => [
                    ['Gold Düğmeli Buklet Hırka', 1450, 'Chanel tarzı buklet doku, özel kabartmalı gold düğmeler.'],
                    ['Kuşaklı Uzun Triko Hırka', 1350, 'Diz boyu uzunluk, örgü kuşağı ve geniş cepli triko hırka.'],
                    ['Oversize Saç Örgülü Hırka', 1190, 'Kalın sıcak tutan iplik, el örgüsü görünümlü nostaljik hırka.'],
                    ['V Yaka Salaş Basic Hırka', 980, 'Geniş düğmeli, dökümlü salaş form mevsimlik triko hırka.'],
                    ['İnci Düğmeli Kısa Hırka', 1120, 'Yüksek bel pantolonlarla harika duran inci düğmeli crop hırka.'],
                    ['Ekose Desenli Jakarlı Hırka', 1290, 'Pastel ekose desen dokuması, kontrast şerit biyeli tasarım.'],
                    ['Deri Biyeli Triko Hırka', 1390, 'Yaka ve ceplerde deri görünümlü biye detaylı şık hırka.'],
                    ['Kapüşonlu Fermuarlı Triko Hırka', 1080, 'Çift yönlü fermuarlı, günlük kullanım için kapüşonlu spor hırka.'],
                    ['Yumuşak Moher Dokulu Hırka', 1420, 'Tüylü yumuşacık moher dokusu, dökümlü şal yaka kesim.'],
                    ['Dantel Garnili İnce Yazlık Hırka', 950, 'Serin yaz akşamları için hafif pamuklu tül garnili hırka.'],
                ],
                'pattern' => 'Dökümlü Hırka Kalıp',
                'length' => 'Midi Boy (90 cm)',
                'fabric_type' => 'Buklet & Triko Dokuma',
                'lining' => 'Astarsız',
                'collar' => 'V Yaka / Şal Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'ust-giyim-sweat' => [
                'code' => 'SWT',
                'items' => [
                    ['Kanguru Cepli Kapüşonlu Sweatshirt', 890, '3 iplik şardonlu pamuklu kumaş, geniş kanguru cepli.'],
                    ['Fermuar Yakalı Oversize Sweatshirt', 940, 'Yarım metal fermuarlı dik yaka, rahat düşük omuz formu.'],
                    ['Minimal Nakışlı Basic Sweatshirt', 820, 'Göğüste ton-sür-ton minimal SimgeVIP nakış logosu.'],
                    ['Beli Lastikli Kısa Sweatshirt', 860, 'Geniş ribanalı bel ve kol manşeti, pamuk penye kumaş.'],
                    ['Yandan Yırtmaçlı Uzun Sweatshirt', 920, 'Tayt üstüne giyilebilecek uzunlukta, yanları fermuar yırtmaçlı.'],
                    ['Polo Yaka Vintage Sweatshirt', 880, 'Nostaljik kolej yaka kesim, yumuşak pamuklu iki iplik.'],
                    ['Baskılı Salaş Boyfriend Sweatshirt', 910, 'Önü sanatsal tipografi baskılı, ekstra dökümlü boyfriend kalıp.'],
                    ['Reglan Kollu Dikiş Detaylı Sweat', 870, 'Dışa dönük dikiş efektli modern tasarım, nefes alan pamuk.'],
                    ['Fırfırlı Omuz Detaylı Sweatshirt', 950, 'Spor şıklığı buluşturan omuz volanlı feminen sweatshirt.'],
                    ['Kapüşonlu İnce Yazlık Sweatshirt', 790, 'Mevsim geçişleri için hafif ince pamuklu penye kumaş.'],
                ],
                'pattern' => 'Oversize & Salaş',
                'length' => 'Standart (72 cm)',
                'fabric_type' => '%100 Pamuk 3 İplik',
                'lining' => 'Şardonlu Sıcak İç Yüzey',
                'collar' => 'Kapüşonlu / Dik Yaka',
                'sleeve' => 'Ribana Manşetli Kol',
            ],

            'alt-giyim-pantolon' => [
                'code' => 'PNT',
                'items' => [
                    ['Yüksek Bel Palazzo Kumaş Pantolon', 1150, 'Dökümlü döküm double krep, ekstra geniş paça şık pantolon.'],
                    ['Beli Lastikli Havuç Pantolon', 980, 'Arkası rahat lastikli, önü pensli cigarette havuç kesim.'],
                    ['Pilili Geniş Paça Keten Pantolon', 1220, 'Doğal taş rengi keten dokuma, önden çift pilili modern kesim.'],
                    ['Dikiş Detaylı İspanyol Paça Pantolon', 1090, 'Bacak boyunu uzun gösteren önden dikişli ispanyol paça.'],
                    ['Boru Paça Klasik Ofis Pantolonu', 1050, 'Ütü izli, kırışmayan kumaş teknolojisi, kemer köprülü.'],
                    ['Kargo Cepli Dökümlü Pantolon', 1190, 'Yanda zarif kapaklı cepler, paçası büzülebilen tencel kumaş.'],
                    ['Deri Görünümlü Yüksek Bel Pantolon', 1350, 'İçi yumuşak süet dokulu, esnek ve toparlayıcı suni deri.'],
                    ['Beli Kuşaklı Kaşkorse Pantolon', 920, 'Haftasonu ve ev şıklığı için esnek ribana doku, kuşaklı.'],
                    ['Bilek Boy Duble Paça Pantolon', 1080, 'Duble kıvrımlı paça, pamuk saten esnek dokuma kumaş.'],
                    ['Pensli Dökümlü Palazzo Pantolon', 1180, 'Belden dökülen geniş pileler, ipeksi tuşeli lüks krep.'],
                ],
                'pattern' => 'Yüksek Bel Palazzo & Havuç',
                'length' => '102 cm',
                'fabric_type' => 'Double Krep & Pamuk Saten',
                'lining' => 'Astarsız',
                'collar' => 'Yok',
                'sleeve' => 'Yok',
            ],

            'alt-giyim-etek' => [
                'code' => 'ETK',
                'items' => [
                    ['Beli Lastikli Piliseli Saten Etek', 1050, 'Kalıcı ince pilise baskılı, ışıl ışıl saten dökümlü maxi etek.'],
                    ['A Kesim Düğmeli Keten Etek', 1120, 'Boydan ahşap düğmeli, yanları cepli A kesim doğal keten etek.'],
                    ['Kloş Kesim Krep Midi Etek', 990, 'Uçuşan geniş kloş kesim, yüksek bel gizli fermuarlı krep etek.'],
                    ['Yırtmaçlı Kalem Etek', 950, 'Arkadan yırtmaçlı, toparlayıcı likralı krep kumaş klasik kalem etek.'],
                    ['Kat Kat Fırfırlı Bohem Etek', 1180, 'Geniş fırfır katmanları, hafif pamuk vual kumaştan yazlık etek.'],
                    ['Kemerli Kaşe Kışlık Etek', 1280, 'Sıcak tutan yünlü kaşe doku, deri tokalı kemer detaylı midi etek.'],
                    ['Asimetrik Kesim Şifon Etek', 1080, 'İçi astarlı, dışı dökümlü asimetrik şifon katmanlı tasarım.'],
                    ['Deri Görünümlü Kloş Etek', 1350, 'Dökümlü mat suni deri, yüksek bel fermuarlı midi boy etek.'],
                    ['Beli Büzgülü Desenli Viskon Etek', 920, 'Soft pastel çiçek desenli, geniş etek ucuyla rahat viskon.'],
                    ['Triko Ribana Düz Etek', 980, 'Esnek fitilli triko, rahat adım yırtmacı ve beli lastikli form.'],
                ],
                'pattern' => 'Kloş & Piliseli',
                'length' => 'Maxi (95 cm)',
                'fabric_type' => 'Saten & Piliseli Şifon',
                'lining' => 'Gövde Astarlı',
                'collar' => 'Yok',
                'sleeve' => 'Yok',
            ],

            'alt-giyim-jean' => [
                'code' => 'JEA',
                'items' => [
                    ['Yüksek Bel Wide Leg Jean', 1190, '%100 pamuklu kaliteli denim, ekstra geniş paça yüksek bel.'],
                    ['Mom Fit Vintage Yıkamalı Jean', 1090, 'Rahat basen ve daralan paça, nostaljik açık mavi yıkama.'],
                    ['Düz Boru Paça Klasik Jean', 1050, 'Zamansız düz kesim, toparlayıcı esnek likralı denim kumaş.'],
                    ['Flare İspanyol Paça Jean', 1220, 'Dizden genişleyen paça, bacakları uzun gösteren koyu mavi yıkama.'],
                    ['Beli Lastikli Rahat Jean Pantolon', 990, 'Beli büzgülü ve bağcıklı, yazlık hafif tencel denim dokusu.'],
                    ['Paçası Püsküllü Straight Jean', 1120, 'Doğal püskül detaylı paçalar, yüksek bel rahat kalıp.'],
                    ['Beyaz Yüksek Bel Wide Leg Jean', 1180, 'Yaz aylarının vazgeçilmezi tok beyaz denim, iç göstermez.'],
                    ['Ekru Vintage Düz Kesim Jean', 1150, 'Ham pamuk rengi ekru denim, kontrast dikiş efektli.'],
                    ['Antrasit Yıkamalı Slouchy Jean', 1140, 'Modern havuç form, koyu gri eskitme yıkamalı denim.'],
                    ['Kargo Cepli Rahat Kesim Jean', 1250, 'Fonksiyonel kapak cepler, bol dökümlü sokak modası denimi.'],
                ],
                'pattern' => 'Yüksek Bel Wide Leg & Mom Fit',
                'length' => '104 cm',
                'fabric_type' => '%100 Pamuk Denim',
                'lining' => 'Astarsız',
                'collar' => 'Yok',
                'sleeve' => 'Yok',
            ],

            'dis-giyim-trenckot' => [
                'code' => 'TRN',
                'items' => [
                    ['Kruvaze İtalyan Model Trençkot', 2450, 'Su itici gabardin kumaş, omuz apoletli ve geniş kemerli klasik.'],
                    ['Dökümlü Soft Dokulu Trençkot', 2190, 'İpeksi dökümlü tencel kumaş, astarsız hafif mevsimlik trençkot.'],
                    ['Kolları Büzgülü Modern Trençkot', 2280, 'Kol ağızları ayarlanabilir büzgülü, arkası rüzgarlıklı şık tasarım.'],
                    ['Deri Garnili Uzun Trençkot', 2650, 'Yaka ve ceplerde taba deri garniler, boydan kemerli lüks trençkot.'],
                    ['Kapüşonlu Spor Trençkot', 1990, 'Çıkarılabilir kapüşonlu, fermuarlı ve cepli su geçirmez kumaş.'],
                    ['Ekose Astarlı Klasik Trençkot', 2550, 'İçi özel tasarım ekose astarlı, çift sıra boynuz düğmeli.'],
                    ['Geniş Klapalı Salaş Trençkot', 2350, 'Oversize dökümlü kesim, kemerle bağlanarak şekil alan model.'],
                    ['Fermuar Detaylı Trençkot', 2150, 'Gümüş metal fermuarlar, dik yaka ve sportif şık görünüm.'],
                    ['Yandan Yırtmaçlı Maxi Trençkot', 2490, 'Topuklara kadar inen maxi boy, yanları çıtçıt yırtmaçlı.'],
                    ['Haki Safari Model Trençkot', 2250, 'Beli içten büzgülü, safari cep detaylı doğal pamuk gabardin.'],
                ],
                'pattern' => 'Oversize Klasik Trençkot',
                'length' => 'Maxi (125 cm)',
                'fabric_type' => 'Su İtici Pamuk Gabardin',
                'lining' => 'Tam Astarlı',
                'collar' => 'Kruvaze / Geniş Klapa',
                'sleeve' => 'Kemerli Uzun Kol',
            ],

            'dis-giyim-ceket' => [
                'code' => 'CKT',
                'items' => [
                    ['Kruvaze Blazer Ceket', 1890, 'Geniş omuz vatkası, çift sıra gold düğmeli kaliteli blazer.'],
                    ['Tüvit Chanel Tarzı Ceket', 2150, 'Özel dokuma tüvit kumaş, saçaklı kenar ve inci düğmeli tasarım.'],
                    ['Keten Tek Düğme Blazer Ceket', 1650, 'Yazlık doğal keten dokuma, hafif ve serin tutan astarsız blazer.'],
                    ['Deri Görünümlü Oversize Ceket', 2250, 'Yumuşacık mat suni deri, biker form geniş cepli ceket.'],
                    ['Kuşaklı Şal Yaka Ceket', 1790, 'Düğmesiz kuşakla bağlanan kimono form, dökümlü krep kumaş.'],
                    ['Ekose Desenli Yün Ceket', 1950, 'İngiliz ekose deseni, kadife yaka garnili kışlık şık ceket.'],
                    ['Crop Model Düğmeli Ceket', 1550, 'Yüksek bel pantolonlarla kusursuz kombinlenen kısa ceket.'],
                    ['Beli Büzgülü Safari Ceket', 1690, 'Pamuk gabardin kumaş, çıtçıtlı fonksiyonel kapak cepli.'],
                    ['Dantel Garnili Abiye Ceket', 2350, 'Özel geceler için jakar desen ve kol uçlarında dantel garnili.'],
                    ['Kolej Yaka Bomber Ceket', 1490, 'Ribana yakalı, parlak saten kumaştan lüks bomber ceket.'],
                ],
                'pattern' => 'Blazer & Oversize Kalıp',
                'length' => 'Standart (75 cm)',
                'fabric_type' => 'Krep & Tüvit & Keten',
                'lining' => 'Saten Astarlı',
                'collar' => 'Kruvaze / Erkek Yaka',
                'sleeve' => 'Vatkalı Uzun Kol',
            ],

            'dis-giyim-kaban' => [
                'code' => 'KBN',
                'items' => [
                    ['Kuşaklı Kaşe Kaban', 3150, '%70 yün içerikli sıcak kaşe, geniş şal yaka ve dökümlü kuşak.'],
                    ['Kruvaze Oversize Palto', 3450, 'Erkek yaka, boydan kruvaze düğmeli lüks İtalyan kaşe palto.'],
                    ['Kapitone Dikişli Uzun Mont', 2350, 'Hafif ve sıcak tutan kaz tüyü dolgu efektli su geçirmez mont.'],
                    ['Kürklü Yaka Kaşe Kaban', 3290, 'Çıkarılabilir yumuşak kürk yaka garnili, kemerli kadın kaban.'],
                    ['Beli Büzgülü Şişme Mont', 2190, 'Beli içten lastikli, rüzgar geçirmez kapüşonlu kışlık mont.'],
                    ['Teddy Peluş Sıcak Kaban', 2450, 'Son dönemin trendi yumuşacık teddy peluş kumaş, çıtçıtlı.'],
                    ['Ekose Desenli Yün Palto', 3190, 'Büyük ekose desenli yün dokuma, geniş cepli kışlık palto.'],
                    ['Dikişsiz Termal Uzun Kaban', 2790, 'Modern minimalist dikişsiz silüet, gizli cepli fonksiyonel.'],
                    ['Şal Yakalı Dökümlü Kaban', 2950, 'Düğmesiz şal form, astarlı birinci sınıf alpaka yün karışımı.'],
                    ['Çift Taraflı Giyilebilir Mont', 2650, 'Bir tarafı parlak kumaş diğer tarafı yumuşak peluş 2\'si 1 arada mont.'],
                ],
                'pattern' => 'Oversize Kışlık Kalıp',
                'length' => 'Uzun Palto (120 cm)',
                'fabric_type' => '%70 Yün Kaşe & Su İtici Kumaş',
                'lining' => 'Tam Kapitone & Saten Astarlı',
                'collar' => 'Şal Yaka / Kürklü Yaka',
                'sleeve' => 'Geniş Manşetli Kol',
            ],

            'sal' => [
                'code' => 'SAL',
                'items' => [
                    ['Medine İpeği Şal', 390, 'Kayma yapmayan tok doku, nefes alan Medine ipeği kumaştan.'],
                    ['Jakarlı Monogram İpek Şal', 550, 'Lüks monogram jakar desen dokumalı, parlak ve zarif şal.'],
                    ['Bambu Pamuklu Doğal Şal', 340, 'Yaz için serin tutan doğal bambu lifleri, ütü istemeyen kumaş.'],
                    ['Taş İşlemeli Abiye Şal', 650, 'Uçlarında el işçiliği kristal taş dizimli özel davet şalı.'],
                    ['Krinkıl Pamuklu Dökümlü Şal', 320, 'Kendinden kırışık krinkıl doku, gün boyu bozulmayan form.'],
                    ['Kaşmir Hissiyatlı Kışlık Şal', 480, 'Soğuk havalar için yumuşacık sıcak tutan dokuma kaşmir şal.'],
                    ['Pilise Dokulu Parlak Şal', 420, 'İnce piliseli modern doku, hafif simli zarif parlaklık.'],
                    ['İtalyan Vual Eşarp / Şal', 450, 'Hafif ve dökümlü vual kumaş, canlı renk pigmentli.'],
                    ['Çift Taraflı İki Renk Şal', 380, 'İki farklı renk kombinasyonuyla çift yönlü kullanılabilir şal.'],
                    ['Desenli Soft Twill İpek Şal', 520, 'Modern soyut desenler, tok duruşlu twill ipek tuşesi.'],
                ],
                'pattern' => 'Standart Şal Ebatı (80x200 cm)',
                'length' => '200 cm',
                'fabric_type' => '%100 Medine İpeği & Bambu',
                'lining' => 'Yok',
                'collar' => 'Yok',
                'sleeve' => 'Yok',
            ],

            'aksesuar' => [
                'code' => 'AKS',
                'items' => [
                    ['Zincir Askılı Kapitone Deri Çanta', 950, 'Gold metal zincir askı, birinci sınıf kapitone suni deri omuz çantası.'],
                    ['Kare Tokalı Deri Kemer', 350, 'Gold kare tokalı, elbiseler ve kabanlar için toparlayıcı kemer.'],
                    ['Baguette Kol Çantası', 790, '90\'lar tarzı minimalist baget form, mıknatıslı kapaklı şık çanta.'],
                    ['İncili Zirkon Taşlı Broş', 280, 'Ceket ve şal üzerinde ışıltı katan el yapımı inci broş.'],
                    ['Büyük Boy Süet Alışveriş Çantası', 890, 'Geniş iç hacimli, yumuşak süet dokulu mıknatıslı tote çanta.'],
                    ['Burgu Zincir Kolye & Bileklik Seti', 420, 'Kararmaz çelik üzeri 14K altın kaplama burgu zincir seti.'],
                    ['Manyetik Güçlü Şal Mıknatısı Seti', 180, 'Şal ve eşarplara zarar vermeyen güçlü gold/gümüş mıknatıs seti.'],
                    ['Hasırlı Yazlık Kol Çantası', 750, 'Doğal rafya hasır dokuma, ahşap kulplu plaj ve şehir çantası.'],
                    ['İpek Dokulu Saç Fuları', 220, 'Çanta sapına veya saça bağlanabilen çok amaçlı saten fular.'],
                    ['Deri Telefon Askılı Mini Çanta', 590, 'Kartlıklı ve ayarlanabilir uzun askılı kompakt telefon çantası.'],
                ],
                'pattern' => 'Standart Ebat',
                'length' => 'Standart',
                'fabric_type' => 'Birinci Sınıf Deri & Çelik',
                'lining' => 'Kumaş Astarlı',
                'collar' => 'Yok',
                'sleeve' => 'Yok',
            ],

            'ust-giyim-iclik' => [
                'code' => 'ICL',
                'items' => [
                    ['Dikişsiz Termal Kadın İçlik', 350, 'Vücudu saran esnek mikrofiber doku, kış boyu sıcak tutan dikişsiz termal içlik.'],
                    ['Modal Kumaş Sıfır Kol İçlik', 290, 'İpeksi yumuşaklıkta nefes alan modal kumaş, kıyafetlerin altından belli olmayan kesim.'],
                    ['Boğazlı Penye Kadın İçlik', 320, 'Ceket ve hırkaların içine giyilebilen yumuşak pamuklu boğazlı içlik.'],
                    ['Pamuklu Askılı Atlet İçlik', 240, '%100 organik taranmış pamuk, terletmeyen doğal lif yapısı.'],
                    ['Kaşkorse Uzun Kollu İçlik', 380, 'Toparlayıcı elastan ribana doku, gömlek ve triko altına tam uyumlu.'],
                    ['V Yaka İpeksi Dokulu İçlik', 310, 'Derin V yaka dekolteli bluzlar için özel tasarlanmış görünmez içlik.'],
                    ['Toparlayıcı Likralı Korse İçlik', 450, 'Karın ve basen bölgesini 1 beden incelten dikişsiz toparlayıcı içlik.'],
                    ['Kışlık Polar Astarlı İçlik', 420, 'İçi yumuşak şardonlu polar, sıfır derecenin altındaki havalara özel.'],
                    ['Bambu Dikişsiz Body İçlik', 360, 'Doğal antibakteriyel bambu lifi, alttan çıtçıtlı body formu.'],
                    ['Dantel Detaylı Zarif İçlik', 340, 'Yaka ucunda zarif Fransız güpür dantel garnili şık içlik.'],
                ],
                'pattern' => 'Vücudu Saran Tam Kalıp',
                'length' => 'Standart',
                'fabric_type' => 'Modal & Termal Likra',
                'lining' => 'Astarsız',
                'collar' => 'V Yaka / Boğazlı',
                'sleeve' => 'Uzun Kol / Sıfır Kol',
            ],

            'dis-giyim-yelek' => [
                'code' => 'YLK',
                'items' => [
                    ['Kruvaze Keten Uzun Yelek', 1350, 'Doğal keten dokuma, şık kruvaze kapama ve geniş cepli modern uzun yelek.'],
                    ['Şişme Kapitone Spor Yelek', 1250, 'Kaz tüyü dolgu efektli, hafif ve rüzgar geçirmez dik yaka spor yelek.'],
                    ['Kaşe Kumaş Kuşaklı Yelek', 1550, 'Yün kaşe kumaş, dökümlü şal yaka ve belden kuşaklı kadın kışlık yelek.'],
                    ['Deri Görünümlü Biker Yelek', 1650, 'Asimetrik metal fermuarlı mat suni deri, kemer detaylı stil yelek.'],
                    ['Düğmeli Triko Uzun Yelek', 1190, 'Yumuşak dokulu kalın triko, boydan sedef düğmeli mevsimlik yelek.'],
                    ['Cepli Klasik Takım Yeleği', 1100, 'Kumaş pantolonlarla kombinlenen V yaka astarlı klasik kadın yelek.'],
                    ['Teddy Peluş Sıcak Yelek', 1390, 'Yumuşacık peluş kumaş, kapüşonlu ve fermuarlı kışlık rahat yelek.'],
                    ['Düğmeli Denim Jean Yelek', 1050, 'Vintage açık mavi yıkama, göğüs cepli salaş kesim denim yelek.'],
                    ['Püskül Detaylı Süet Yelek', 1420, 'Bohem tarzda lazer kesim püsküller, yumuşak dokulu taba süet.'],
                    ['Kapüşonlu Su Geçirmez Yelek', 1290, 'Yağmura dayanıklı kumaş, çift yönlü fermuar ve polar cepli yelek.'],
                ],
                'pattern' => 'Rahat & Dökümlü',
                'length' => 'Uzun (90 cm)',
                'fabric_type' => 'Keten & Kaşe & Kapitone',
                'lining' => 'Astarlı',
                'collar' => 'V Yaka / Şal Yaka',
                'sleeve' => 'Kolsuz',
            ],

            'dis-giyim-mont' => [
                'code' => 'MNT',
                'items' => [
                    ['Kapüşonlu Kısa Şişme Mont', 1950, 'Hafif ve sıcak tutan dolgulu kapitone, su itici kumaş dik yaka mont.'],
                    ['Kürklü Yaka Parlak Kışlık Mont', 2350, 'Çıkarılabilir lüks kürk yaka garnisi, metalik parlak dokulu kumaş.'],
                    ['Dikişsiz Hafif Kaz Tüyü Mont', 2150, 'Ultra hafif sıkıştırılabilir teknoloji, fermuarlı yan cepli.'],
                    ['Beli Kemerli Uzun Şişme Mont', 2550, 'Diz boyu uzunluk, belde toparlayıcı kemer ve rüzgar korumalı manşet.'],
                    ['Kapitone Desenli Bomber Mont', 1850, 'Ribana yakalı kolej stili, hafif elyaf dolgulu mevsimlik bomber.'],
                    ['Oversize Parka Tipi Kışlık Mont', 2450, 'İçi sıcak tutan polar astarlı, beli büzgülü kışlık dayanıklı parka.'],
                    ['Peluş Astarlı Su İtici Mont', 2250, 'Dışı su kaydırıcı membran, içi tamamen yumuşacık teddy peluş.'],
                    ['Büzgülü Yaka Termal Mont', 2050, 'Boynu saran yüksek büzgülü yaka, soğuk hava geçirmeyen özel dikiş.'],
                    ['Çift Yönlü Fermuarlı Spor Mont', 1890, 'Aşağıdan ve yukarıdan açılan fermuar, hareket özgürlüğü sağlayan kesim.'],
                    ['Deri Garnili Kapitone Mont', 2290, 'Yaka ve ceplerde suni deri garniler, zarif baklava kapitone dikiş.'],
                ],
                'pattern' => 'Oversize & Regular',
                'length' => 'Kalça Boyu (75 cm)',
                'fabric_type' => 'Su İtici Membran & Elyaf Dolgu',
                'lining' => 'Polar & Saten Astarlı',
                'collar' => 'Kapüşonlu & Dik Yaka',
                'sleeve' => 'Ribanalı Uzun Kol',
            ],

            'dis-giyim-kap' => [
                'code' => 'KAP',
                'items' => [
                    ['Düğmeli Pamuk Gabardin Kap', 1650, 'Boydan sedef düğmeli, nefes alan pamuk gabardin kumaş mevsimlik kap.'],
                    ['Gizli Patlı Kuşaklı Uzun Kap', 1790, 'Gizli düğme patı, belde ayarlanabilir dökümlü kuşak ve dik yaka.'],
                    ['Fermuarlı Spor Çift Cepli Kap', 1550, 'Gümüş metal fermuarlı, kolları katlanabilir apoletli günlük kap.'],
                    ['Dökümlü Krep Mevsimlik Kap', 1850, 'Kırışmayan dökümlü ithal krep kumaş, diz altı zarif boy.'],
                    ['Kapüşonlu Büzgülü Trenç Kap', 1720, 'Beli ve kapüşonu büzgülü, sportif şıklık sunan su itici kap.'],
                    ['Garnili Biyeli Modern Kap', 1890, 'Yaka ve kol ağızlarında kontrast biye detaylı şık kadın kap.'],
                    ['Kolları Katlamalı Keten Kap', 1680, 'Doğal keten kumaş, sıcak ilkbahar ve yaz ayları için ideal ferah kap.'],
                    ['Boydan Fermuarlı Kot Kap', 1590, 'Tencel denim dokuma, dökümlü ve yumuşak kot kap modeli.'],
                    ['Asimetrik Kesim Şık Kap', 1820, 'Önü hafif asimetrik kapanan kruvaze form, ofis ve günlük uyumlu.'],
                    ['Beli Lastikli Yağmurluk Kap', 1490, 'Hafif rüzgarlık kumaş, katlanıp çantaya sığabilen pratik kap.'],
                ],
                'pattern' => 'Dökümlü Uzun Kap',
                'length' => 'Midi / Maxi (115 cm)',
                'fabric_type' => 'Pamuk Gabardin & Double Krep',
                'lining' => 'Astarsız (İç Göstermez)',
                'collar' => 'Hakim Yaka & Gömlek Yaka',
                'sleeve' => 'Apoletli Uzun Kol',
            ],

            'ikili-takim-etekli-takim' => [
                'code' => 'ITE',
                'items' => [
                    ['Kruvaze Ceketli Etekli Takım', 2250, 'Kruvaze ceket ve kloş etekten oluşan lüks davet takımı.'],
                    ['Piliseli Etek & Tunik Takımı', 2050, 'Dökümlü piliseli etek ve kuşaklı tunik, dökümlü krep kumaş.'],
                    ['Tüvit Ceket & Kalem Etek Takım', 2450, 'İnci düğmeli tüvit ceket ve yüksek bel kalem etek seti.'],
                    ['Keten Gömlek & Kloş Etek Takım', 1890, 'Yazlık doğal keten gömlek ve beli lastikli geniş kloş etek.'],
                    ['Triko Kazak & Ribana Etek Takımı', 1980, 'Yumuşak triko kazak ve esnek fitilli triko etek kombini.'],
                    ['Düğmeli Yelek & Midi Etek Takım', 1850, 'Önü düğmeli kruvaze yelek ve A kesim midi etek.'],
                    ['Şifon Bluz & Kat Kat Etek Takım', 2150, 'Özel günler için tül şifon fırfırlı etek ve dökümlü bluz.'],
                    ['Beli Kuşaklı Tunik & Etek Seti', 1920, 'Yanları yırtmaçlı tunik ve düz kesim astarlı etek takımı.'],
                    ['Balon Kol Üst & Çan Etek Takım', 2080, 'Hacimli kol kesimi ve yüksek bel çan etek harmonisi.'],
                    ['Asimetrik Tunik & Pilili Etek Takım', 2190, 'Modern asimetrik dökümlü tunik ve piliseli parlak etek.'],
                ],
                'pattern' => 'Etekli Takım Kalıbı',
                'length' => 'Üst 80 cm / Etek 95 cm',
                'fabric_type' => 'İthal Krep & Tüvit & Keten',
                'lining' => 'Etek Astarlı',
                'collar' => 'Gömlek Yaka / V Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'ikili-takim-pantolonlu-takim' => [
                'code' => 'ITP',
                'items' => [
                    ['Double Krep Pantolonlu Takım', 2250, 'Boru paça krep pantolon ve boydan düğmeli şık tunik seti.'],
                    ['Oversize Tunik & Palazzo Takım', 1990, 'Ekstra dökümlü salaş tunik ve yüksek bel geniş paça pantolon.'],
                    ['Yelekli Keten Pantolon Takımı', 1890, 'Doğal taş rengi keten yelek ve havuç kesim pantolon.'],
                    ['Kuşaklı Kimono & Pantolon Takım', 2100, 'Geniş kol kimono ceket ve beli lastikli dökümlü pantolon.'],
                    ['Cepli Safari Tunik & Pantolon', 1850, 'Kapaklı cepli spor tunik ve paçası duble kumaş pantolon.'],
                    ['Fermuarlı Spor Pantolonlu Takım', 1650, 'İki iplik penye eşofman üstü ve toparlayıcı jogger pantolon.'],
                    ['İpek Saten Bluz & Pantolon Takım', 2390, 'Dökümlü ipek saten üst ve palazzo kumaş pantolon şıklığı.'],
                    ['Asimetrik Kesim Pantolonlu Takım', 2050, 'Önü kısa arkası uzun tunik ve cigarette pantolon uyumu.'],
                    ['Dökümlü Şifon Tunik & Pantolon', 2190, 'Gövdesi astarlı dökümlü şifon tunik ve yüksek bel pantolon.'],
                    ['Kaşkorse Triko Pantolonlu Takım', 1950, 'Esnek ribana triko kazak ve dökümlü triko pantolon takımı.'],
                ],
                'pattern' => 'Pantolonlu Takım Kalıbı',
                'length' => 'Tunik 90 cm / Pantolon 102 cm',
                'fabric_type' => 'Double Krep & Saten',
                'lining' => 'Astarsız (İç Göstermez)',
                'collar' => 'Hakim Yaka & Şal Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'uclu-takim-pantolonlu-takim' => [
                'code' => 'UTP',
                'items' => [
                    ['Kimonolu 3\'lü Pantolon Takımı', 2650, 'Uzun dökümlü kimono, iç askılı bluz ve havuç pantolon.'],
                    ['Klasik Blazer Yelek Pantolon Takım', 2850, 'Blazer ceket, kruvaze yelek ve boru paça kumaş pantolon.'],
                    ['Keten Hırka Atlet Pantolon Seti', 2350, 'Doğal keten uzun hırka, pamuklu iç atlet ve beli lastikli pantolon.'],
                    ['Şifon Pelerinli 3\'lü Abiye Takım', 2950, 'Dökümlü şifon pelerin, taş işlemeli iç bluz ve yüksek bel pantolon.'],
                    ['Dökümlü Trençkotlu 3\'lü Kombin', 2750, 'Mevsimlik trençkot, basic iç gömlek ve palazzo pantolon.'],
                    ['Kuşaklı Ceket Bluz Pantolon Takım', 2550, 'Geniş kemerli ceket, V yaka iç bluz ve cigarette pantolon.'],
                    ['Spor Kapüşonlu 3\'lü Eşofman Seti', 1990, 'Fermuarlı hırka, bisiklet yaka tişört ve rahat jogger.'],
                    ['Saten İçlikli Kruvaze 3\'lü Takım', 2690, 'Kruvaze ceket, degaje yaka saten içlik ve dökümlü pantolon.'],
                    ['Jakarlı Hırka Tunik Pantolon Takım', 2450, 'Özel jakar dokuma hırka, basic tunik ve kumaş pantolon.'],
                    ['Deri Detaylı Ceket Bluz Pantolon', 2790, 'Suni deri garnili ceket, dökümlü bluz ve yüksek bel pantolon.'],
                ],
                'pattern' => '3 Parça Pantolon Takımı',
                'length' => 'Dış Parça 105 cm / Pantolon 102 cm',
                'fabric_type' => 'Double Krep & Keten',
                'lining' => 'Ceket Astarlı',
                'collar' => 'Kruvaze & Şal Yaka',
                'sleeve' => 'Uzun Kol',
            ],

            'uclu-takim-etekli-takim' => [
                'code' => 'UTE',
                'items' => [
                    ['Kimonolu Etekli 3\'lü Takım', 2650, 'Dökümlü uzun kimono, iç askılı atlet ve kloş etek kombini.'],
                    ['Tüvit Ceket Yelek Etek Takımı', 2950, 'Lüks tüvit kumaş ceket, uyumlu yelek ve midi boy kalem etek.'],
                    ['Piliseli Şifon 3\'lü Etek Takımı', 2790, 'Dökümlü şifon tunik, iç büstiyer ve piliseli maxi etek.'],
                    ['Triko Hırka Büstiyer Etek Seti', 2250, 'Düğmeli triko hırka, triko büstiyer ve dökümlü triko etek.'],
                    ['Keten Şal Etekli 3\'lü Kombin', 2450, 'Doğal keten tunik gömlek, keten etek ve uyumlu keten şal.'],
                    ['Kruvaze Ceket Bluz Etek Takımı', 2850, 'Gold düğmeli ceket, ipeksi saten bluz ve A kesim krep etek.'],
                    ['Dökümlü Kaşe Yelek Kazak Etek', 2650, 'Kışlık kaşe yelek, yumuşak triko kazak ve kaşe midi etek.'],
                    ['Saten Garnili 3\'lü Etekli Abiye Seti', 3150, 'Özel davetlere özel saten garnili ceket, büstiyer ve kloş etek.'],
                    ['Asimetrik Pelerinli 3\'lü Etek Takım', 2750, 'Dökümlü asimetrik pelerin, iç tunik ve pilili maxi etek.'],
                    ['Dantelli Bluz Hırka Etek Takımı', 2890, 'Fransız dantel işlemeli bluz, dökümlü hırka ve yüksek bel etek.'],
                ],
                'pattern' => '3 Parça Etekli Takım',
                'length' => 'Dış Parça 100 cm / Etek 95 cm',
                'fabric_type' => 'Tüvit & Şifon Krep',
                'lining' => 'Etek ve Ceket Astarlı',
                'collar' => 'Şal Yaka / V Yaka',
                'sleeve' => 'Uzun Kol',
            ],
        ];

        // Renk paleti havuzu
        $colorPool = [
            ['name' => 'Siyah', 'hex' => '#111827'],
            ['name' => 'Ekru', 'hex' => '#f8fafc'],
            ['name' => 'Bej', 'hex' => '#f5ebe0'],
            ['name' => 'Haki', 'hex' => '#3f4e3c'],
            ['name' => 'Vizon', 'hex' => '#a89f91'],
            ['name' => 'Bordo', 'hex' => '#6b1d2f'],
            ['name' => 'İndigo', 'hex' => '#1e293b'],
            ['name' => 'Zümrüt Yeşili', 'hex' => '#14532d'],
            ['name' => 'Gül Kurusu', 'hex' => '#b76e79'],
            ['name' => 'Camel', 'hex' => '#c19a6b'],
        ];

        $globalProductCounter = 1;

        foreach ($catalog as $catSlug => $catData) {
            $cat = $categoriesMap[$catSlug] ?? null;
            if (!$cat) {
                continue;
            }

            foreach (array_slice($catData['items'], 0, 8) as $itemIndex => $item) {
                $name = $item[0];
                $price = $item[1];
                $description = $item[2];
                $sku = 'URN-' . $catData['code'] . '-' . str_pad($itemIndex + 1, 3, '0', STR_PAD_LEFT);

                // Ürün için 2 adet görsel yolu ata
                $img1 = 'products/product-img-' . (($globalProductCounter % 40) + 1) . '.svg';
                $img2 = 'products/product-img-' . ((($globalProductCounter + 5) % 40) + 1) . '.svg';

                // Kampanya ve öne çıkarma ayarları
                $isFeatured = ($itemIndex % 3 === 0);
                $hasCampaign = ($itemIndex % 4 === 0);
                $discountPercent = $hasCampaign ? [15, 20, 25, 30][$itemIndex % 4] : null;

                $product = Product::create([
                    'category_id' => $cat->id,
                    'sku' => $sku,
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . strtolower($catData['code']) . '-' . ($itemIndex + 1),
                    'description' => $description,
                    'price' => $price,
                    'images' => [$img1, $img2],
                    'is_featured' => $isFeatured,
                    'is_new_season' => ($itemIndex % 3 === 0),
                    'has_campaign' => $hasCampaign,
                    'campaign_discount_percentage' => $discountPercent,
                    'pattern' => $catData['pattern'],
                    'thickness' => 'Mevsimlik & Dökümlü',
                    'length' => $catData['length'],
                    'fabric_content' => '%80 Viskon %20 Keten',
                    'fabric_type' => $catData['fabric_type'],
                    'lining' => $catData['lining'],
                    'collar_type' => $catData['collar'],
                    'sleeve_type' => 'Standart',
                    'sleeve_length' => $catData['sleeve'],
                    'closure_type' => 'Düğmeli / Gizli Fermuarlı',
                    'pocket' => 'Çift Cepli',
                    'season' => '2026 İlkbahar / Yaz Koleksiyonu',
                ]);

                // Her ürüne 2 veya 3 adet renk varyantı ekle
                $colorCount = ($itemIndex % 2 === 0) ? 3 : 2;
                $assignedColors = array_slice($colorPool, ($itemIndex * 2) % 7, $colorCount);

                foreach ($assignedColors as $cIdx => $c) {
                    $vImg1 = 'products/variants/variant-img-' . ((($globalProductCounter + $cIdx) % 30) + 1) . '.svg';
                    $vImg2 = 'products/product-img-' . (($globalProductCounter % 40) + 1) . '.svg';

                    // Her bedene gerçek pozitif stok ata
                    $sizesStock = [
                        '38' => 12 + $cIdx * 2,
                        '40' => 18 + $cIdx * 3,
                        '42' => 15 + $cIdx * 2,
                        '44' => 10 + $cIdx,
                        '46' => 8 + $cIdx,
                        '48' => 6,
                        '50' => 4,
                        '52' => 3,
                    ];

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color' => $c['name'],
                        'images' => [$vImg1, $vImg2],
                        'sizes' => $sizesStock,
                    ]);
                }

                $globalProductCounter++;
            }
        }
    }
}
