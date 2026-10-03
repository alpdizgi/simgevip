<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportTables extends Migration
{
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('subject', 180);
            $table->string('category', 40)->default('general');
            $table->string('status', 30)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamp('customer_read_at')->nullable();
            $table->timestamp('admin_read_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_reply_at']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->string('author_type', 20);
            $table->unsignedBigInteger('author_id');
            $table->text('body');
            $table->timestamps();
            $table->index(['support_ticket_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
}
