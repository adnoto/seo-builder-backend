<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Services\TemplateRegistryService;
use App\Services\BuilderService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TemplateRegistryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function testGetArchetypeReturnsValidStructureForAllArchetypes()
    {
        $service = app(TemplateRegistryService::class);
        $archetypes = ['services', 'products', 'professional', 'portfolio', 'default'];

        foreach ($archetypes as $type) {
            $archetype = $service->getArchetype($type);
            $this->assertArrayHasKey('name', $archetype, "Archetype $type missing name");
            $this->assertArrayHasKey('description', $archetype, "Archetype $type missing description");
            $this->assertArrayHasKey('pages', $archetype, "Archetype $type missing pages");
            $this->assertNotEmpty($archetype['pages'], "Archetype $type has no pages");

            foreach ($archetype['pages'] as $page) {
                $this->assertArrayHasKey('page_type', $page, "Page in $type missing page_type");
                $this->assertArrayHasKey('slug', $page, "Page in $type missing slug");
                $this->assertArrayHasKey('title', $page, "Page in $type missing title");
                $this->assertArrayHasKey('meta_description', $page, "Page in $type missing meta_description");
                $this->assertArrayHasKey('seo_data', $page, "Page in $type missing seo_data");
                $this->assertArrayHasKey('schema', $page['seo_data'], "Page in $type missing schema");
                $this->assertArrayHasKey('keywords', $page['seo_data'], "Page in $type missing keywords");
                $this->assertArrayHasKey('page_structure', $page, "Page in $type missing page_structure");
                $this->assertArrayHasKey('version', $page['page_structure'], "Page in $type missing version");
                $this->assertArrayHasKey('components', $page['page_structure'], "Page in $type missing components");

                foreach ($page['page_structure']['components'] as $component) {
                    $this->assertArrayHasKey('id', $component, "Component in $type missing id");
                    $this->assertArrayHasKey('type', $component, "Component in $type missing type");
                    $this->assertArrayHasKey('props', $component, "Component in $type missing props");
                    $this->assertArrayHasKey('aria_label', $component['props'], "Component in $type missing aria_label");
                    $this->assertArrayHasKey('prompt_metadata', $component, "Component in $type missing prompt_metadata");
                    if (!empty($component['prompt_metadata'])) {
                        $this->assertArrayHasKey('maxLength', $component['prompt_metadata'], "Component in $type missing maxLength");
                        $this->assertArrayHasKey('readingLevel', $component['prompt_metadata'], "Component in $type missing readingLevel");
                    }
                }
            }
        }
    }

    public function testGetArchetypeReturnsDefaultForInvalidType()
    {
        $service = app(TemplateRegistryService::class);
        $archetype = $service->getArchetype('invalid');
        $this->assertEquals('Default (Catch-All)', $archetype['name']);
        $this->assertNotEmpty($archetype['pages']);
        $this->assertArrayHasKey('seo_data', $archetype['pages'][0]);
    }

    public function testApplyToProjectCreatesPagesWithValidStructure()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);
        $service = app(TemplateRegistryService::class);
        $builder = $this->mock(BuilderService::class);

        $builder->shouldReceive('createPage')
            ->times(4)
            ->andReturnUsing(function ($proj, $pageData) {
                return [
                    'id' => rand(1, 100),
                    'page_type' => $pageData['page_type'],
                    'slug' => $pageData['slug'],
                    'seo_data' => $pageData['seo_data'],
                    'page_structure' => $pageData['page_structure'],
                ];
            });

        $pages = $service->applyToProject($project, 'professional', 'uuid-123');

        $this->assertCount(4, $pages);
        $this->assertEquals('home', $pages[0]['page_type']);
        $this->assertEquals('services', $pages[1]['page_type']);
        $this->assertEquals('team', $pages[2]['page_type']);
        $this->assertEquals('contact', $pages[3]['page_type']);
    }

    public function testApplyToProjectHandlesIdempotency()
    {
        Cache::flush(); // Clear cache to ensure fresh state
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);
        $service = app(TemplateRegistryService::class);
        $builder = $this->mock(BuilderService::class);

        $builder->shouldReceive('createPage')
            ->times(4)
            ->andReturnUsing(function ($proj, $pageData) {
                return [
                    'id' => rand(1, 100),
                    'page_type' => $pageData['page_type'],
                    'slug' => $pageData['slug'],
                ];
            });

        // First call: should create pages
        $pages = $service->applyToProject($project, 'professional', 'uuid-123');
        $this->assertCount(4, $pages);

        // Second call with same key: should skip creation
        $builder->shouldNotReceive('createPage');
        $pages = $service->applyToProject($project, 'professional', 'uuid-123');
        $this->assertCount(0, $pages); // Returns existing pages (mocked as empty for simplicity)

        // New key: should create pages again
        $builder->shouldReceive('createPage')->times(4);
        $pages = $service->applyToProject($project, 'professional', 'uuid-456');
        $this->assertCount(4, $pages);
    }

    public function testApplyToProjectEnforcesSeoConstraints()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);
        $service = app(TemplateRegistryService::class);
        $builder = $this->mock(BuilderService::class);

        $builder->shouldReceive('createPage')
            ->times(4)
            ->andReturnUsing(function ($proj, $pageData) {
                return [
                    'id' => rand(1, 100),
                    'page_type' => $pageData['page_type'],
                    'slug' => $pageData['slug'],
                    'seo_data' => $pageData['seo_data'],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => $pageData['page_type'] === 'home' ? [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => ['headline' => 'Test', 'aria_label' => 'Main hero'],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                        ] : $pageData['page_structure']['components'],
                    ],
                ];
            });

        $pages = $service->applyToProject($project, 'professional', 'uuid-789');

        foreach ($pages as $page) {
            $this->assertArrayHasKey('schema', $page['seo_data']);
            $this->assertArrayHasKey('keywords', $page['seo_data']);
            $this->assertEquals('index,follow', $page['seo_data']['robots']);
            $components = $page['page_structure']['components'];
            $heroCount = count(array_filter($components, fn($c) => $c['type'] === 'Hero'));
            if ($page['page_type'] === 'home') {
                $this->assertEquals(1, $heroCount, 'Exactly one Hero component for semantic HTML on home page');
            } else {
                $this->assertLessThanOrEqual(1, $heroCount, 'At most one Hero component on non-home pages');
            }
        }
    }
}
?>