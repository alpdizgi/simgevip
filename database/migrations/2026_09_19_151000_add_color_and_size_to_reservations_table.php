<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColorAndSizeToReservationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'color')) {
                    $table->string('color', 80)->nullable()->after('product_id');
                }
                if (! Schema::hasColumn('reservations', 'size')) {
                    $table->string('size', 40)->nullable()->after('color');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'size')) {
                    $table->dropColumn('size');
                }
                if (Schema::hasColumn('reservations', 'color')) {
                    $table->dropColumn('color');
                }
            });
        }
    }
}
