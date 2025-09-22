<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subscription_tier', ['starter','pro','agency'])
                  ->default('starter')
                  ->after('remember_token');

            $table->enum('subscription_status', ['active','suspended','inactive'])
                  ->default('active')
                  ->after('subscription_tier');

            $table->unsignedInteger('credits')
                  ->default(0)
                  ->after('subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_tier','subscription_status','credits']);
        });
    }
};

