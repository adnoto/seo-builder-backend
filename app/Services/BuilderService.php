<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BuilderService
{
    /**
     * Create a page from template data
     *
     * @param Project $project
     * @param array $pageData
     * @return array
     * @throws ValidationException
     */
    public function createPage(Project $project, array $pageData): array
    {
        try {
            // Set default SEO robots if not provided
            if (!isset($pageData['seo_data']['robots'])) {
                $pageData['seo_data']['robots'] = 'index,follow';
            }

            // Validate page structure using existing method
            if (!$this->validatePageStructure($pageData['page_structure'])) {
                throw ValidationException::withMessages([
                    'page_structure' => 'Invalid page structure: must have exactly one H1 and proper heading hierarchy'
                ]);
            }

            // Validate and enforce SEO constraints
            $this->validateSeoConstraints($pageData);

            // Create the page
            $page = Page::create([
                'project_id' => $project->id,
                'page_type' => $pageData['page_type'],
                'slug' => $pageData['slug'],
                'title' => $pageData['title'],
                'meta_description' => $pageData['meta_description'],
                'seo_data' => $pageData['seo_data'],
                'page_structure' => $pageData['page_structure'],
            ]);

            Log::info('Page created from template', [
                'project_id' => $project->id,
                'page_type' => $pageData['page_type'],
                'page_id' => $page->id
            ]);

            return [
                'id' => $page->id,
                'page_type' => $page->page_type,
                'slug' => $page->slug,
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'seo_data' => $page->seo_data,
                'page_structure' => $page->page_structure,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create page from template', [
                'project_id' => $project->id,
                'page_type' => $pageData['page_type'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate SEO constraints for the page
     *
     * @param array $pageData
     * @throws ValidationException
     */
    private function validateSeoConstraints(array $pageData): void
    {
        // Ensure required SEO fields exist
        if (!isset($pageData['seo_data']['schema'])) {
            throw ValidationException::withMessages(['seo_data.schema' => 'Schema is required']);
        }

        if (!isset($pageData['seo_data']['keywords'])) {
            throw ValidationException::withMessages(['seo_data.keywords' => 'Keywords are required']);
        }

        // Validate Hero component constraints
        $components = $pageData['page_structure']['components'] ?? [];
        $heroComponents = array_filter($components, fn($c) => $c['type'] === 'Hero');
        $heroCount = count($heroComponents);

        if ($pageData['page_type'] === 'home') {
            if ($heroCount !== 1) {
                throw ValidationException::withMessages([
                    'page_structure.components' => 'Home page must have exactly one Hero component for semantic HTML'
                ]);
            }
        } else {
            if ($heroCount > 1) {
                throw ValidationException::withMessages([
                    'page_structure.components' => 'Non-home pages can have at most one Hero component'
                ]);
            }
        }
    }

    public function validatePageStructure(array $structure): bool
    {
        $h1Count = 0;
        $headingLevels = [];
        
        foreach ($structure['components'] ?? [] as $component) {
            if ($component['type'] === 'Hero' && isset($component['props']['headline'])) {
                $h1Count++;
                $headingLevels[] = 1;
            }
            if ($component['type'] === 'Section' && isset($component['props']['heading'])) {
                // Get actual heading level from component props
                $level = $component['props']['heading_level'] ?? 2;
                $headingLevels[] = $level;
            }
        }
        
        // Restore proper validation: exactly one H1 and proper heading hierarchy
        return $h1Count === 1 && $this->validateHeadingHierarchy($headingLevels);
    }

    private function validateHeadingHierarchy(array $levels): bool
    {
        if (empty($levels)) return true;
        
        // First heading should be H1
        if ($levels[0] !== 1) return false;
        
        // Check that heading levels don't skip (h1→h3 without h2 is invalid)
        for ($i = 1; $i < count($levels); $i++) {
            if ($levels[$i] > $levels[$i-1] + 1) {
                return false;
            }
        }
        
        return true;
    }

    public function suggestLayoutFromKeywords(array $keywords): array
    {
        $components = [];
        if (in_array('business valuation', $keywords)) {
            $components[] = [
                'type' => 'Hero',
                'props' => ['headline' => 'Expert Business Valuations', 'sub' => 'USPAP-compliant reports', 'cta' => 'Get a Quote'],
            ];
            $components[] = [
                'type' => 'Main',
                'props' => ['content' => 'Main content section'],
            ];
            $components[] = [
                'type' => 'ServicesGrid',
                'props' => ['items' => [['title' => 'M&A Valuations', 'heading' => 'Valuation Services'], ['title' => 'Gift & Estate']]],
            ];
            $components[] = ['type' => 'CTA', 'props' => ['text' => 'Schedule a Call']];
        }
        return ['components' => $components, 'version' => '1'];
    }
}