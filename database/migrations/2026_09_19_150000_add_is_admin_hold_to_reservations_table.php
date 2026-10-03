<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAdminHoldToReservationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reservations') && ! Schema::hasColumn('reservations', 'is_admin_hold')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->boolean('is_admin_hold')->default(false)->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'is_admin_hold')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('is_admin_hold');
            });
        }
    }
}
