<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('page_type')->default('standard');
            $table->string('meta_description', 160)->nullable();
            $table->json('page_structure')->nullable();
            $table->json('seo_data')->nullable();
            $table->json('ai_generated_content')->nullable();
            $table->boolean('is_master_template')->default(false);
            $table->unsignedBigInteger('parent_template_id')->nullable();
            
            $table->foreign('parent_template_id')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            //
        });
    }
};
