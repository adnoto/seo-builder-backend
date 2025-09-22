<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');  
        $this->middleware('role:owner|admin', ['only' => ['store', 'update', 'destroy']]);
        $this->middleware('role:owner|admin|editor|viewer', ['only' => ['index', 'show']]);
    }

    public function index(): JsonResponse
    {
        $projects = Project::where('user_id', auth()->id())
            ->select('id', 'name', 'target_keywords', 'settings', 'updated_at')
            ->get();
        return response()->json($projects);
    }

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

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        return response()->json($project);
    }

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

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }
}