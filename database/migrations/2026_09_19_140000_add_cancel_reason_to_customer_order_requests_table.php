<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelReasonToCustomerOrderRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('customer_order_requests') && ! Schema::hasColumn('customer_order_requests', 'cancel_reason')) {
            Schema::table('customer_order_requests', function (Blueprint $table) {
                $table->text('cancel_reason')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('customer_order_requests') && Schema::hasColumn('customer_order_requests', 'cancel_reason')) {
            Schema::table('customer_order_requests', function (Blueprint $table) {
                $table->dropColumn('cancel_reason');
            });
        }
    }
}
