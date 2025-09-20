<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'page_structure' => 'array',
        'seo_data' => 'array',
        'ai_generated_content' => 'array',
    ];

    protected $fillable = [
        'project_id',
        'page_type',
        'slug',
        'title',
        'meta_description',
        'page_structure',
        'seo_data',
        'ai_generated_content',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}