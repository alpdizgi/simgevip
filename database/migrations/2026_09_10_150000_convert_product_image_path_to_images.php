<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image_path');
        });

        DB::table('products')->whereNotNull('image_path')->get()->each(function ($product) {
            DB::table('products')->where('id', $product->id)->update([
                'images' => json_encode([$product->image_path]),
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
        });

        DB::table('products')->whereNotNull('images')->get()->each(function ($product) {
            $images = json_decode($product->images, true) ?? [];
            DB::table('products')->where('id', $product->id)->update([
                'image_path' => $images[0] ?? null,
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
