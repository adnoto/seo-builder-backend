<?php

namespace Tests\Feature;

use App\Models\ProjectExport;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $viewer;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles with sanctum guard
        Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'viewer', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);

        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole('owner');

        $this->viewer = User::factory()->create();
        $this->viewer->assignRole('viewer');

        // Create project with pages
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Project'
        ]);

        Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Home Page',
            'slug' => 'home',
            'page_structure' => [
                'components' => [
                    [
                        'type' => 'Hero',
                        'props' => [
                            'headline' => 'Welcome',
                            'sub' => 'Test subtitle',
                            'cta' => 'Get Started'
                        ]
                    ]
                ]
            ]
        ]);

        // Use fake storage
        Storage::fake('local');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_exports_for_project_owner()
    {
        Sanctum::actingAs($this->user);

        // Create a test export
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'export_type' => 'wordpress_theme',
            'status' => 'ready',
            'file_path' => 'exports/test-export.zip',
            'original_filename' => 'test-theme.zip'
        ]);

        $response = $this->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'export_type',
                        'status',
                        'file_size_formatted',
                        'download_count',
                        'last_downloaded_at',
                        'is_ready',
                        'has_project_changed',
                        'created_at'
                    ]
                ])
                ->assertJsonFragment([
                    'id' => $export->id,
                    'export_type' => 'wordpress_theme'
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_export_listing_to_viewer()
    {
        Sanctum::actingAs($this->viewer);

        $response = $this->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_wordpress_theme_export()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports", [
            'export_type' => 'wordpress_theme'
        ]);

        // Debug the 500 error if it occurs
        if ($response->status() === 500) {
            dump($response->getContent());
        }

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'export_type',
                    'status',
                    'file_size_formatted',
                    'created_at',
                    'download_url'
                ])
                ->assertJsonFragment([
                    'export_type' => 'wordpress_theme',
                    'status' => 'ready'
                ]);

        // Verify export was created in database
        $this->assertDatabaseHas('project_exports', [
            'project_id' => $this->project->id,
            'export_type' => 'wordpress_theme',
            'status' => 'ready'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_defaults_to_wordpress_theme_export_type()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'export_type' => 'wordpress_theme'
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_export_creation_to_viewer()
    {
        Sanctum::actingAs($this->viewer);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_export_creation_failure()
    {
        Sanctum::actingAs($this->user);

        // Create a project with no pages to trigger failure
        $emptyProject = Project::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/projects/{$emptyProject->id}/exports");

        $response->assertStatus(500)
                ->assertJsonStructure([
                    'error',
                    'message'
                ]);

        // Verify failed export is marked as failed
        $this->assertDatabaseHas('project_exports', [
            'project_id' => $emptyProject->id,
            'status' => 'failed'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_shows_export_details()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'export_type' => 'wordpress_theme',
            'status' => 'ready',
            'download_count' => 5,
            'file_path' => 'exports/test-theme.zip',
            'original_filename' => 'test-theme.zip'
        ]);

        $response = $this->getJson("/api/exports/{$export->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'project_id',
                    'export_type',
                    'status',
                    'file_size_formatted',
                    'download_count',
                    'last_downloaded_at',
                    'is_ready',
                    'has_project_changed',
                    'created_at',
                    'updated_at',
                    'download_url'
                ])
                ->assertJsonFragment([
                    'id' => $export->id,
                    'download_count' => 5
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_downloads_ready_export()
    {
        Sanctum::actingAs($this->user);

        // Mock a file in storage
        Storage::put('exports/test-export.zip', 'fake zip content');

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'ready',
            'file_path' => 'exports/test-export.zip',
            'original_filename' => 'test-theme.zip'
        ]);

        $response = $this->get("/api/exports/{$export->id}/download");

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/zip')
                ->assertHeader('content-disposition', 'attachment; filename="test-theme.zip"');

        // Verify download was recorded
        $export->refresh();
        $this->assertEquals(1, $export->download_count);
        $this->assertNotNull($export->last_downloaded_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_download_of_pending_export()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending'
        ]);

        $response = $this->getJson("/api/exports/{$export->id}/download");

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'error' => 'Export is not ready for download'
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_missing_export_file()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'ready',
            'file_path' => 'exports/nonexistent.zip',
            'original_filename' => 'nonexistent.zip'
        ]);

        $response = $this->getJson("/api/exports/{$export->id}/download");

        $response->assertStatus(404)
                ->assertJsonFragment([
                    'error' => 'Export file not found'
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_export_and_file()
    {
        Sanctum::actingAs($this->user);

        // Create a file in storage
        Storage::put('exports/test-export.zip', 'fake content');

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'file_path' => 'exports/test-export.zip',
            'original_filename' => 'test-export.zip'
        ]);

        $response = $this->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Export deleted successfully'
                ]);

        // Verify export was deleted from database
        $this->assertDatabaseMissing('project_exports', [
            'id' => $export->id
        ]);

        // Verify file was deleted from storage
        Storage::assertMissing('exports/test-export.zip');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_export_deletion_to_viewer()
    {
        Sanctum::actingAs($this->viewer);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'file_path' => 'exports/test-export.zip',
            'original_filename' => 'test-export.zip'
        ]);

        $response = $this->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'file_path' => 'exports/test-export.zip',
            'original_filename' => 'test-export.zip'
        ]);

        // Test all endpoints without authentication
        $this->getJson("/api/projects/{$this->project->id}/exports")->assertStatus(401);
        $this->postJson("/api/projects/{$this->project->id}/exports")->assertStatus(401);
        $this->getJson("/api/exports/{$export->id}")->assertStatus(401);
        $this->getJson("/api/exports/{$export->id}/download")->assertStatus(401);
        $this->deleteJson("/api/exports/{$export->id}")->assertStatus(401);
    }
}