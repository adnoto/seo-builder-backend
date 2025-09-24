<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_exports', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'ready', 'failed'])->change();
        });
    }

    public function down()
    {
        Schema::table('project_exports', function (Blueprint $table) {
            $table->enum('status', ['pending', 'ready', 'failed'])->change();
        });
    }
};