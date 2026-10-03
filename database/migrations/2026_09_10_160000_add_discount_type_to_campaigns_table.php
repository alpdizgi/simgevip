<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('discount_type')->default('percentage')->after('discount_percentage');
            $table->unsignedInteger('nth_item')->nullable()->after('discount_type');
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'nth_item']);
        });
    }
};
