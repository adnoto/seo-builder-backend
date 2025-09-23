<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'target_keywords' => 'array',
        'settings' => 'array',
    ];

    protected $fillable = ['name', 'user_id', 'target_keywords', 'settings'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function exports()
    {
        return $this->hasMany(ProjectExport::class);
    }
}