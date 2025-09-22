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
        
        $role = Role::where('name', 'editor')->where('guard_name', 'sanctum')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('editor', 'sanctum'));
        $this->assertTrue($user->can('edit project'));
    }

    public function test_role_middleware_protects_route(): void
    {
        $adminUser = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'sanctum')->first();
        $adminUser->roles()->attach($adminRole);

        $viewerUser = User::factory()->create();
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', 'sanctum')->first();
        $viewerUser->roles()->attach($viewerRole);

        // Create a real project first
        $project = Project::factory()->create();

        $response = $this->actingAs($adminUser, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'standard',
                'slug' => 'test-page',
                'title' => 'Test Page'
            ]);

        // Debug the validation errors
        if ($response->status() === 422) {
            dump($response->json());
}

        $response = $this->actingAs($viewerUser, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'standard',
                'slug' => 'test-page-2',
                'title' => 'Test Page 2'
            ]);
        $response->assertStatus(403);
    }

    public function test_policy_denies_unauthorized_access(): void
    {
        $ownerUser = User::factory()->create();
        $ownerRole = Role::where('name', 'owner')->where('guard_name', 'sanctum')->first();
        $ownerUser->roles()->attach($ownerRole);

        $viewerUser = User::factory()->create();
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', 'sanctum')->first();
        $viewerUser->roles()->attach($viewerRole);

        $project = Project::factory()->create(['user_id' => $ownerUser->id]);

        $response = $this->actingAs($ownerUser, 'sanctum')
            ->getJson("/api/projects/{$project->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($viewerUser, 'sanctum')
            ->getJson("/api/projects/{$project->id}");
        $response->assertStatus(403);
    }
}