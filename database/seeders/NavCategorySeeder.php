<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NavCategorySeeder extends Seeder
{
    public function run()
    {
        $this->renameLegacySlugs();

        $tree = [
            ['name' => 'İndirimli Ürünler', 'slug' => 'indirimli-urunler', 'children' => []],
            ['name' => 'Yeni Gelenler', 'slug' => 'yeni-gelenler', 'children' => []],
            ['name' => 'Yeni Sezon', 'slug' => 'yeni-sezon', 'children' => []],
            ['name' => 'Elbise', 'slug' => 'elbise', 'children' => []],
            ['name' => 'Şal & Eşarp', 'slug' => 'sal', 'children' => []],
            ['name' => 'Aksesuar', 'slug' => 'aksesuar', 'children' => []],
            ['name' => 'Dış Giyim', 'slug' => 'dis-giyim', 'children' => ['Yelek', 'Mont', 'Kap', 'Ceket', 'Trençkot', 'Kaban']],
            ['name' => 'Üst Giyim', 'slug' => 'ust-giyim', 'children' => ['Sweat', 'Gömlek', 'Kazak', 'Hırka', 'Tunik', 'İçlik']],
            ['name' => 'Alt Giyim', 'slug' => 'alt-giyim', 'children' => ['Etek', 'Pantolon', 'Jean']],
            ['name' => 'İkili Takım', 'slug' => 'ikili-takim', 'children' => ['Etekli Takım', 'Pantolonlu Takım']],
            ['name' => 'Üçlü Takım', 'slug' => 'uclu-takim', 'children' => ['Pantolonlu Takım', 'Etekli Takım']],
        ];

        $order = 0;

        foreach ($tree as $item) {
            $order++;
            $parent = Category::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'parent_id' => null,
                    'sort_order' => $order,
                    'show_in_nav' => true,
                ]
            );

            $childOrder = 0;
            foreach ($item['children'] as $childName) {
                $childOrder++;
                $childSlug = Str::slug($item['name'] . '-' . $childName);

                Category::query()->updateOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'sort_order' => $childOrder,
                        'show_in_nav' => true,
                    ]
                );
            }
        }
    }

    protected function renameLegacySlugs(): void
    {
        $legacy = Category::query()->where('slug', 'en-yeniler')->first();

        if ($legacy && ! Category::query()->where('slug', 'yeni-gelenler')->exists()) {
            $legacy->update([
                'name' => 'Yeni Gelenler',
                'slug' => 'yeni-gelenler',
            ]);
        }
    }
}
