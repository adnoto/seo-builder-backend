<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TemplateRegistryService
{
    public function __construct(private BuilderService $builderService)
    {
    }

    /**
     * Get archetype skeleton by business type.
     *
     * @param string $businessType
     * @return array
     */
    public function getArchetype(string $businessType): array
    {
        return Cache::remember("archetype:{$businessType}", 3600, function () use ($businessType) {
            $archetypes = config('templates.archetypes', []);
            return $archetypes[$businessType] ?? $archetypes['default'];
        });
    }

    /**
     * Apply archetype to project, creating pages with validation.
     *
     * @param Project $project
     * @param string $archetype
     * @param string $idempotencyKey
     * @return array
     * @throws ValidationException
     */
    public function applyToProject(Project $project, string $archetype, string $idempotencyKey): array
    {
        $cacheKey = "idempotency:{$project->id}:{$idempotencyKey}";
        
        // Check if we've already processed this request
        if (Cache::has($cacheKey)) {
            Log::info('Idempotent request skipped', ['key' => $idempotencyKey]);
            // Return the cached result instead of querying the database
            return Cache::get($cacheKey);
        }

        $structure = $this->getArchetype($archetype);
        $this->validateArchetype($structure);

        $pages = [];
        foreach ($structure['pages'] as $pageData) {
            $pages[] = $this->builderService->createPage($project, $pageData);
        }

        // Cache the result for idempotency
        Cache::put($cacheKey, $pages, 86400);
        
        return $pages;
    }

    /**
     * Validate archetype structure.
     *
     * @param array $structure
     * @throws ValidationException
     */
    private function validateArchetype(array $structure): void
    {
        $validator = Validator::make($structure, [
            'name' => 'required|string',
            'description' => 'required|string',
            'pages' => 'required|array|min:1',
            'pages.*.page_type' => 'required|string',
            'pages.*.slug' => 'required|string',
            'pages.*.title' => 'required|string',
            'pages.*.meta_description' => 'required|string|max:160',
            'pages.*.seo_data' => 'required|array',
            'pages.*.seo_data.schema' => 'required|array',
            'pages.*.seo_data.keywords' => 'required|array',
            'pages.*.page_structure' => 'required|array',
            'pages.*.page_structure.version' => 'required|string',
            'pages.*.page_structure.components' => 'required|array|min:1',
            'pages.*.page_structure.components.*.id' => 'required|string',
            'pages.*.page_structure.components.*.type' => 'required|string',
            'pages.*.page_structure.components.*.props' => 'required|array',
            'pages.*.page_structure.components.*.props.aria_label' => 'required|string',
            'pages.*.page_structure.components.*.prompt_metadata' => 'present|array',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}