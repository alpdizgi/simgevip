<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoColumnsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('meta_title', 120)->nullable()->after('site_name');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->string('og_image_path')->nullable()->after('logo_path');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'og_image_path']);
        });
    }
}
