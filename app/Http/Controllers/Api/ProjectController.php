<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\TemplateRegistryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function __construct(private TemplateRegistryService $templateRegistryService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner|admin', ['only' => ['store', 'update', 'destroy', 'applyArchetype']]);
        $this->middleware('role:owner|admin|editor|viewer', ['only' => ['index', 'show']]);
    }

    /**
     * Get all projects for the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $projects = Project::where('user_id', auth()->id())
            ->select('id', 'name', 'target_keywords', 'settings', 'updated_at')
            ->get();
        return response()->json($projects);
    }

    /**
     * Create a new project.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_keywords' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $project = Project::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return response()->json($project, 201);
    }

    /**
     * Get a specific project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        return response()->json($project);
    }

    /**
     * Update a project.
     *
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => 'string|max:255',
            'target_keywords' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        if ($request->header('If-Match') !== $project->updated_at->toString()) {
            return response()->json(['error' => 'Version mismatch'], 409);
        }

        $project->update($validated);
        return response()->json($project);
    }

    /**
     * Delete a project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }

    /**
     * Apply an archetype to a project.
     *
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function applyArchetype(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'archetype' => 'required|string|in:services,products,professional,portfolio,default',
            'idempotency_key' => 'required|string',
        ]);

        try {
            $pages = $this->templateRegistryService->applyToProject(
                $project,
                $request->input('archetype'),
                $request->input('idempotency_key')
            );
            return response()->json(['pages' => $pages], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
?>