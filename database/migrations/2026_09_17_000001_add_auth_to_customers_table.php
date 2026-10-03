<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('login_email')->nullable()->unique();
            $table->string('login_phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->rememberToken();
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['login_email']);
            $table->dropUnique(['login_phone']);
            $table->dropColumn(['login_email', 'login_phone', 'password', 'remember_token']);
        });
    }
}
