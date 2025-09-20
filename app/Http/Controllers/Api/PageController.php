<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\BuilderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    protected $builderService;

    public function __construct(BuilderService $builderService)
    {
        $this->builderService = $builderService;
    }

    public function index(Request $request, $projectId): JsonResponse
    {
        $pages = Page::where('project_id', $projectId)
            ->select('id', 'page_type', 'slug', 'title', 'updated_at')
            ->get();
        return response()->json($pages);
    }

    public function store(Request $request, $projectId): JsonResponse
    {
        $validated = $request->validate([
            'page_type' => 'required|string',
            'slug' => 'required|string|regex:/^[a-z0-9-]+$/',
            'title' => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'page_structure' => 'nullable|array',
        ]);

        if (!$this->builderService->validatePageStructure($validated['page_structure'] ?? [])) {
            return response()->json(['error' => 'Invalid page structure'], 422);
        }

        $page = Page::create([
            ...$validated,
            'project_id' => $projectId,
        ]);

        return response()->json($page, 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page->project);
        return response()->json($page);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page->project);
        $validated = $request->validate([
            'page_type' => 'string',
            'slug' => 'string|regex:/^[a-z0-9-]+$/',
            'title' => 'string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'page_structure' => 'nullable|array',
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
}