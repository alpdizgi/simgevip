<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttributesToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('pattern')->nullable()->after('is_featured');
            $table->string('thickness')->nullable()->after('pattern');
            $table->string('length')->nullable()->after('thickness');
            $table->string('fabric_content')->nullable()->after('length');
            $table->string('fabric_type')->nullable()->after('fabric_content');
            $table->string('lining')->nullable()->after('fabric_type');
            $table->string('collar_type')->nullable()->after('lining');
            $table->string('sleeve_type')->nullable()->after('collar_type');
            $table->string('sleeve_length')->nullable()->after('sleeve_type');
            $table->string('closure_type')->nullable()->after('sleeve_length');
            $table->string('pocket')->nullable()->after('closure_type');
            $table->string('season')->nullable()->after('pocket');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'pattern',
                'thickness',
                'length',
                'fabric_content',
                'fabric_type',
                'lining',
                'collar_type',
                'sleeve_type',
                'sleeve_length',
                'closure_type',
                'pocket',
                'season',
            ]);
        });
    }
}
