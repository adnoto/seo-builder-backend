<?php

namespace App\Jobs;

use App\Models\ProjectExport;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWordPressExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $projectExport;

    public function __construct(ProjectExport $projectExport)
    {
        $this->projectExport = $projectExport;
    }

    public function handle(ExportService $exportService)
    {
        try {
            Log::info('Starting export generation for project: ' . $this->projectExport->project_id);
            
            // Update status to processing
            $this->projectExport->update(['status' => 'processing']);

            // Generate the theme (moved from controller)
            $zipPath = $exportService->generateWordPressTheme($this->projectExport->project);

            // Get relative path for storage
            $relativePath = str_replace(storage_path('app/'), '', $zipPath);
            $filename = "project-{$this->projectExport->project->name}-theme.zip";

            // Mark export as ready (using your existing method)
            $this->projectExport->markAsReady($relativePath, $filename);

            Log::info('Export completed successfully for project: ' . $this->projectExport->project_id);

        } catch (\Exception $e) {
            Log::error('Export failed for project: ' . $this->projectExport->project_id . ' - ' . $e->getMessage());
            
            // Mark export as failed (using your existing method)
            $this->projectExport->markAsFailed();
            
            // Re-throw the exception so the job fails properly
            throw $e;
        }
    }
}