<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('product_attribute_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('attribute_values')->nullable()->after('season');
        });

        $now = now();
        $defaults = [
            ['key' => 'pattern', 'label' => 'Ürün Kalıbı', 'sort_order' => 10],
            ['key' => 'thickness', 'label' => 'Ürün Kalınlığı', 'sort_order' => 20],
            ['key' => 'length', 'label' => 'Ürün Boyu', 'sort_order' => 30],
            ['key' => 'fabric_content', 'label' => 'Kumaş İçeriği', 'sort_order' => 40],
            ['key' => 'fabric_type', 'label' => 'Kumaş Tipi', 'sort_order' => 50],
            ['key' => 'lining', 'label' => 'Astar Durumu', 'sort_order' => 60],
            ['key' => 'collar_type', 'label' => 'Yaka Tipi', 'sort_order' => 70],
            ['key' => 'sleeve_type', 'label' => 'Kol Tipi', 'sort_order' => 80],
            ['key' => 'sleeve_length', 'label' => 'Kol Boyu', 'sort_order' => 90],
            ['key' => 'closure_type', 'label' => 'Kapama Şekli', 'sort_order' => 100],
            ['key' => 'pocket', 'label' => 'Cep', 'sort_order' => 110],
            ['key' => 'season', 'label' => 'Ürün Sezonu', 'sort_order' => 120],
        ];

        foreach ($defaults as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        DB::table('product_attribute_fields')->insert($defaults);

        $keys = array_column($defaults, 'key');

        DB::table('products')->orderBy('id')->chunkById(100, function ($products) use ($keys) {
            foreach ($products as $product) {
                $values = [];

                foreach ($keys as $key) {
                    $value = $product->{$key} ?? null;

                    if ($value !== null && $value !== '') {
                        $values[$key] = $value;
                    }
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['attribute_values' => $values === [] ? null : json_encode($values)]);
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('attribute_values');
        });

        Schema::dropIfExists('product_attribute_fields');
    }
}
