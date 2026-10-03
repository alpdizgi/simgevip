<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductSizesTable extends Migration
{
    public function up()
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = collect(['38', '40', '42', '44', '46', '48', '50', '52'])
            ->values()
            ->map(fn (string $name, int $index) => [
                'name' => $name,
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('product_sizes')->insert($rows);
    }

    public function down()
    {
        Schema::dropIfExists('product_sizes');
    }
}
