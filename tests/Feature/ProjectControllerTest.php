<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_owner_can_create_project(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/projects', [
                'name' => 'Test Project',
                'target_keywords' => ['business valuation'],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $user->id,
        ]);
    }

    public function test_viewer_cannot_create_project(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/projects', [
                'name' => 'Test Project',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_list_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/projects');

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_can_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/projects/{$project->id}", [
                'name' => 'Updated Project',
                'target_keywords' => ['new keyword'],
            ], ['If-Match' => $project->updated_at->toString()]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
        ]);
    }

    public function test_version_mismatch_returns_409(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/projects/{$project->id}", [
                'name' => 'Updated Project',
            ], ['If-Match' => 'wrong-version']);

        $response->assertStatus(409);
    }
}
