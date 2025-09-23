<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectExport extends Model
{
    use HasFactory;

    protected $table = 'project_exports';

    protected $fillable = [
        'project_id',
        'export_type',
        'file_path',
        'original_filename',
        'file_size',
        'signed_url',
        'expires_at',
        'download_count',
        'last_downloaded_at',
        'snapshot_sha',
        'export_metadata',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'export_metadata' => 'array',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scopes
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeWordPressThemes($query)
    {
        return $query->where('export_type', 'wordpress_theme');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsReadyAttribute(): bool
    {
        return $this->status === 'ready' && !$this->is_expired;
    }

    public function getFileExistsAttribute(): bool
    {
        return $this->file_path && Storage::exists($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Business Logic Methods
     */
   public function markAsReady(string $filePath, string $filename): void
    {
        $this->update([
            'status' => 'ready',
            'file_path' => $filePath,
            'original_filename' => $filename,
            'file_size' => Storage::exists($filePath) ? Storage::size($filePath) : null,
        ]);
    }
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function recordDownload(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function generateDownloadFilename(): string
    {
        return $this->original_filename ?? 
               "project-{$this->project_id}-export.zip";
    }

    public function cleanup(): bool
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    /**
     * Model Events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($export) {
            // Clean up file when export is deleted
            $export->cleanup();
        });
        
        // Optional: Clean up files older than 24 hours
        static::created(function ($export) {
            // Delete exports older than 24 hours
            static::where('created_at', '<', now()->subDay())
                ->chunk(100, function ($oldExports) {
                    foreach ($oldExports as $oldExport) {
                        $oldExport->delete();
                    }
                });
        });
    }

    /**
     * Generate snapshot SHA for content tracking
     */
    public function generateSnapshotSha(): string
    {
        $pages = $this->project->pages()
            ->select('id', 'title', 'slug', 'page_structure', 'updated_at')
            ->orderBy('id')
            ->get();
            
        $content = $pages->map(function ($page) {
            return [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'structure' => $page->page_structure,
                'updated_at' => $page->updated_at->toISOString(),
            ];
        })->toJson();

        return hash('sha256', $content);
    }

    /**
     * Check if project content has changed since export
     */
    public function hasProjectChanged(): bool
    {
        if (!$this->snapshot_sha) {
            return true;
        }

        return $this->snapshot_sha !== $this->generateSnapshotSha();
    }
}