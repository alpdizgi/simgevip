<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerPanelTables extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->json('cart_items')->nullable();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
        });

        Schema::create('customer_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });

        Schema::create('customer_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('source')->default('cart');
            $table->string('status')->default('pending');
            $table->json('items');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_order_requests');
        Schema::dropIfExists('customer_favorites');
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('product_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('cart_items');
        });
    }
}
