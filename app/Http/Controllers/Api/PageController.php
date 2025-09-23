<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Project;  // ← ADD THIS IMPORT
use App\Services\BuilderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    protected $builderService;

    public function __construct(BuilderService $builderService)
    {
        $this->middleware('auth:sanctum');
        $this->builderService = $builderService;
        $this->middleware('role:owner|admin', ['only' => ['store', 'update']]);
        $this->middleware('role:owner|admin|editor|viewer', ['only' => ['index', 'show']]);
    }

    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $pages = $project->pages()
            ->select('id', 'page_type', 'slug', 'title', 'page_structure', 'meta_description', 'seo_data', 'updated_at')
            ->get();
        return response()->json($pages);
    }

    public function store(Request $request, Project $project): JsonResponse  // ← FIX: Use Project model binding
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'page_type' => 'required|string',
            'slug' => 'required|string|regex:/^[a-z0-9-]+$/',
            'title' => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'page_structure' => 'nullable|array',
            'seo_data' => 'nullable|array',
            'ai_generated_content' => 'nullable|array',
            'is_master_template' => 'boolean',
            'parent_template_id' => 'nullable|exists:pages,id',
        ]);

        Log::info('PageController@store', ['request' => $validated, 'project_id' => $project->id]);

        if (!$this->builderService->validatePageStructure($validated['page_structure'] ?? [])) {
            return response()->json(['error' => 'Invalid page structure'], 422);
        }

        $page = Page::create([
            ...$validated,
            'project_id' => $project->id,  // ← FIX: Use $project->id instead of $projectId
        ]);

        return response()->json($page, 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page->project);
        return response()->json($page);
    }

    public function update(Request $request, Project $project, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'page_type' => 'string',
            'slug' => 'string|regex:/^[a-z0-9-]+$/',
            'title' => 'string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'page_structure' => 'nullable|array',
            'seo_data' => 'nullable|array',
            'ai_generated_content' => 'nullable|array',
            'is_master_template' => 'boolean',
            'parent_template_id' => 'nullable|exists:pages,id',
        ]);

        if ($request->header('If-Match') !== $page->updated_at->toString()) {
            return response()->json(['error' => 'Version mismatch'], 409);
        }

        if (isset($validated['page_structure']) && !$this->builderService->validatePageStructure($validated['page_structure'])) {
            return response()->json(['error' => 'Invalid page structure'], 422);
        }

        $page->update($validated);
        return response()->json($page);
    }
    
    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page->project);
        $page->delete();
        return response()->json(null, 204);
    }
}