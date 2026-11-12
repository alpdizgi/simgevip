<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnnouncementsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'announcements')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('announcements')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'announcements')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('announcements');
            });
        }
    }
}
