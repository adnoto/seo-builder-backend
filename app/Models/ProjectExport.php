<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectExport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id',
        'export_type',
        'signed_url',
        'expires_at',
        'download_count',
        'downloaded_at',
        'snapshot_sha',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'download_count' => 'integer',
        'downloaded_at' => 'datetime',
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