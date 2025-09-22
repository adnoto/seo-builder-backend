<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_can_assign_role_to_user(): void
    {
        $user = User::factory()->create();
        $role = Role::findByName('editor');

        $user->assignRole('editor');

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->can('edit project'));
    }

    public function test_role_middleware_protects_route(): void
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $viewerUser = User::factory()->create();
        $viewerUser->assignRole('viewer');

        // Test with your existing page creation endpoint
        $response = $this->actingAs($adminUser, 'sanctum')
            ->postJson('/api/projects/1/pages', [
                'page_type' => 'standard',
                'slug' => 'test-page',
                'title' => 'Test Page'
            ]);
        $response->assertStatus(201);

        $response = $this->actingAs($viewerUser, 'sanctum')
            ->postJson('/api/projects/1/pages', [
                'page_type' => 'standard',
                'slug' => 'test-page',
                'title' => 'Test Page'
            ]);
        $response->assertStatus(403);
    }

    public function test_policy_denies_unauthorized_access(): void
    {
        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('owner');

        $viewerUser = User::factory()->create();
        $viewerUser->assignRole('viewer');

        $project = Project::factory()->create(['user_id' => $ownerUser->id]);

        $response = $this->actingAs($ownerUser, 'sanctum')
            ->getJson("/api/projects/{$project->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($viewerUser, 'sanctum')
            ->getJson("/api/projects/{$project->id}");
        $response->assertStatus(403);
    }
}
