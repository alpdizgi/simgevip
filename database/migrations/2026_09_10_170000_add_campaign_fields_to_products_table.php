<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_campaign')->default(false)->after('is_featured');
            $table->decimal('campaign_discount_percentage', 5, 2)->nullable()->after('has_campaign');
            $table->unsignedInteger('campaign_nth_item')->nullable()->after('campaign_discount_percentage');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_campaign', 'campaign_discount_percentage', 'campaign_nth_item']);
        });
    }
};
