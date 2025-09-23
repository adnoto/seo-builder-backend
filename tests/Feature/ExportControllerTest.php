<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectExport;
use App\Services\ExportService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create()->assignRole('owner');
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
        Storage::fake('private');
    }

    public function testItListsExportsForProjectOwner()
    {
        ProjectExport::factory()->count(3)->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user)->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function testItDeniesExportListingToViewer()
    {
        $viewer = User::factory()->create()->assignRole('viewer');
        ProjectExport::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($viewer)->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    public function testItCreatesWordpressThemeExport()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andReturn(storage_path('app/private/exports/test.zip'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/exports", [
                'export_type' => 'wordpress_theme',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'export_type',
                'status',
                'download_url',
            ]);
    }

    public function testItDefaultsToWordpressThemeExportType()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andReturn(storage_path('app/private/exports/test.zip'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(201)
            ->assertJsonFragment(['export_type' => 'wordpress_theme']);
    }

    public function testItDeniesExportCreationToViewer()
    {
        $viewer = User::factory()->create()->assignRole('viewer');

        $response = $this->actingAs($viewer)
            ->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    public function testItHandlesExportCreationFailure()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andThrow(new \RuntimeException('Export generation failed'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(500)
            ->assertJsonFragment(['error' => 'Export generation failed']);
    }

    public function testItHandlesEmptyProjectExport()
    {
        $emptyProject = Project::factory()->create(['user_id' => $this->user->id]);
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andReturn(storage_path('app/private/exports/test-empty.zip'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$emptyProject->id}/exports");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'export_type',
                'status',
                'download_url',
            ]);

        $this->assertDatabaseHas('project_exports', [
            'project_id' => $emptyProject->id,
            'export_type' => 'wordpress_theme',
            'status' => 'ready',
        ]);
    }

    public function testItShowsExportDetails()
    {
        $export = ProjectExport::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/exports/{$export->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $export->id]);
    }

    public function testItDownloadsReadyExport()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'ready',
            'file_path' => storage_path('app/private/exports/test.zip'),
        ]);

        Storage::disk('private')->put('exports/test.zip', 'dummy content');

        $response = $this->actingAs($this->user)
            ->get("/api/exports/{$export->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/zip');
    }

    public function testItPreventsDownloadOfPendingExport()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/api/exports/{$export->id}/download");

        $response->assertStatus(400)
            ->assertJsonFragment(['error' => 'Export is not ready']);
    }

    public function testItHandlesMissingExportFile()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'ready',
            'file_path' => storage_path('app/private/exports/missing.zip'),
        ]);

        $response = $this->actingAs($this->user)
            ->get("/api/exports/{$export->id}/download");

        $response->assertStatus(404)
            ->assertJsonFragment(['error' => 'Export file not found']);
    }

    public function testItDeletesExportAndFile()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'ready',
            'file_path' => storage_path('app/private/exports/test.zip'),
        ]);

        Storage::disk('private')->put('exports/test.zip', 'dummy content');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('project_exports', ['id' => $export->id]);
        $this->assertFileDoesNotExist($export->file_path);
    }

    public function testItDeniesExportDeletionToViewer()
    {
        $viewer = User::factory()->create()->assignRole('viewer');
        $export = ProjectExport::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($viewer)
            ->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(403);
    }

    public function testItRequiresAuthenticationForAllEndpoints()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/exports");
        $response->assertStatus(401);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports");
        $response->assertStatus(401);

        $export = ProjectExport::factory()->create(['project_id' => $this->project->id]);
        $response = $this->getJson("/api/exports/{$export->id}/download");
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/exports/{$export->id}");
        $response->assertStatus(401);
    }
}
?>