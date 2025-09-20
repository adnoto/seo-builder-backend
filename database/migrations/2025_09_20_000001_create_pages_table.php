<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('page_type');
            $table->string('slug')->index();
            $table->string('title');
            $table->string('meta_description')->nullable();
            $table->json('page_structure')->nullable();
            $table->json('seo_data')->nullable();
            $table->json('ai_generated_content')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'page_type', 'slug'], 'idx_pages_unique');
            $table->index(['project_id', 'page_type'], 'idx_pages_project_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};