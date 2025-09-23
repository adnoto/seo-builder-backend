<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Storage::fake('local');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_exports_for_project_owner()
    {
        Sanctum::actingAs($this->user);

        $exports = ProjectExport::factory()->count(2)->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_export_listing_to_viewer()
    {
        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_wordpress_theme_export()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports", [
            'export_type' => 'wordpress_theme',
        ]);

        if ($response->status() === 500) {
            dump($response->getContent());
        }

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'export_type',
                     'status',
                     'project_id',
                     'snapshot_sha',
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
        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $response = $this->postJson("/api/projects/{$this->project->id}/exports");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_export_creation_failure()
    {
        Sanctum::actingAs($this->user);

        $emptyProject = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson("/api/projects/{$emptyProject->id}/exports", [
            'export_type' => 'wordpress_theme',
        ]);

        $response->assertStatus(500)
                 ->assertJsonStructure([
                     'error',
                     'message'
                 ]);

        $this->assertDatabaseHas('project_exports', [
            'project_id' => $emptyProject->id,
            'status'     => 'failed',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_shows_export_details()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status'     => 'ready',
            'file_path'  => 'exports/test-theme.zip',
        ]);

        $response = $this->getJson("/api/exports/{$export->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'project_id',
                     'export_type',
                     'status',
                     'snapshot_sha',
                 ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_downloads_ready_export()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status'     => 'ready',
            'file_path'  => 'exports/test-theme.zip',
        ]);

        Storage::disk('local')->put('exports/test-theme.zip', 'dummy zip content');

        $response = $this->get("/api/exports/{$export->id}/download");

        $response->assertStatus(200)
         ->assertHeader('content-type', 'application/zip')
         ->assertHeader('content-disposition', 'attachment; filename="test-theme.zip"');

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_download_of_pending_export()
    {
        Sanctum::actingAs($this->user);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status'     => 'pending',
            'file_path'  => null,
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
            'status'     => 'ready',
            'file_path'  => 'exports/missing.zip',
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

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status'     => 'ready',
            'file_path'  => 'exports/test-delete.zip',
        ]);

        Storage::disk('local')->put('exports/test-delete.zip', 'delete me');

        $response = $this->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message' => 'Export deleted successfully'
                 ]);

        $this->assertDatabaseMissing('project_exports', [
            'id' => $export->id
        ]);
        Storage::disk('local')->assertMissing('exports/test-delete.zip');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_export_deletion_to_viewer()
    {
        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/exports/{$export->id}");

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->getJson("/api/projects/{$this->project->id}/exports")->assertStatus(401);
        $this->postJson("/api/projects/{$this->project->id}/exports")->assertStatus(401);
        $this->getJson("/api/exports/{$export->id}")->assertStatus(401);
        $this->get("/api/exports/{$export->id}/download")->assertStatus(401);
        $this->deleteJson("/api/exports/{$export->id}")->assertStatus(401);
    }
}
