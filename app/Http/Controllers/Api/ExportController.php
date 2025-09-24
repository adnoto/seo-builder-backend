<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateWordPressExport;
use App\Models\Project;
use App\Models\ProjectExport;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
        // Remove role middleware - let policies handle authorization
    }

    /**
     * Get all exports for a project
     */
    public function index(Project $project): JsonResponse
    {
        // Check if user can view the project (this should be handled by ProjectPolicy)
        $this->authorize('view', $project);
        
        $exports = $project->exports()
            ->latest()
            ->get()
            ->map(function ($export) {
                return [
                    'id' => $export->id,
                    'export_type' => $export->export_type,
                    'status' => $export->status,
                    'file_size_formatted' => $export->file_size_formatted,
                    'download_count' => $export->download_count,
                    'last_downloaded_at' => $export->last_downloaded_at,
                    'is_ready' => $export->is_ready,
                    'has_project_changed' => $export->hasProjectChanged(),
                    'created_at' => $export->created_at,
                ];
            });

        return response()->json($exports);
    }

    /**
     * Create a new export
     */
    /**
     * Create a new export
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        // Check if user can update the project
        $this->authorize('update', $project);

        // Validate request
        $validated = $request->validate([
            'export_type' => 'sometimes|string|in:wordpress_theme',
        ]);

        $exportType = $validated['export_type'] ?? 'wordpress_theme';

        try {
            // Create export record first (pending status)
            $export = ProjectExport::create([
                'project_id' => $project->id,
                'export_type' => $exportType,
                'status' => 'pending',
                'snapshot_sha' => (new ProjectExport())->setRelation('project', $project)->generateSnapshotSha(),
            ]);

            // Dispatch the job instead of processing immediately
            GenerateWordPressExport::dispatch($export);

            return response()->json([
                'id' => $export->id,
                'export_type' => $export->export_type,
                'status' => $export->status,
                'message' => 'Export started - processing in background',
                'created_at' => $export->created_at,
            ], 202); // 202 = Accepted (processing)

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to start export',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download an export file
     */
    public function download(ProjectExport $export): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $export);

        // Check if export is ready and file exists
        if (!$export->is_ready) {
            return response()->json([
                'error' => 'Export is not ready'
            ], 400);
        }

        if (!$export->file_exists) {
            return response()->json([
                'error' => 'Export file not found'
            ], 404);
        }

        // Record the download
        $export->recordDownload();

        // Stream the file download using the private disk
        return Storage::disk('private')->download(
            $export->file_path,
            $export->generateDownloadFilename(),
            [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $export->generateDownloadFilename() . '"'
            ]
        );
    }

    /**
     * Delete an export
     */
    public function destroy(ProjectExport $export): JsonResponse
    {
        $this->authorize('delete', $export);

        try {
            // This will trigger the model's deleting event which cleans up the file
            $export->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete export',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific export
     */
    public function show(ProjectExport $export): JsonResponse
    {
        $this->authorize('view', $export);

        return response()->json([
            'id' => $export->id,
            'project_id' => $export->project_id,
            'export_type' => $export->export_type,
            'status' => $export->status,
            'file_size_formatted' => $export->file_size_formatted,
            'download_count' => $export->download_count,
            'last_downloaded_at' => $export->last_downloaded_at,
            'is_ready' => $export->is_ready,
            'has_project_changed' => $export->hasProjectChanged(),
            'created_at' => $export->created_at,
            'updated_at' => $export->updated_at,
            'download_url' => $export->is_ready ? route('exports.download', $export) : null,
        ]);
    }
}