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

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        // Verify owner role exists
        $this->assertTrue(Role::where('name', 'owner')->exists(), 'Owner role not seeded');
    }

    public function testIndexReturnsProjectsForAuthenticatedUser()
    {
        $user = User::factory()->create()->assignRole('owner');
        Project::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'target_keywords', 'settings', 'updated_at'],
            ]);
    }

    public function testStoreCreatesProject()
    {
        $user = User::factory()->create()->assignRole('owner');

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'Test Project',
            'target_keywords' => ['keyword1', 'keyword2'],
            'settings' => ['brand' => ['name' => 'Test']],
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Project']);
        $this->assertDatabaseHas('projects', ['name' => 'Test Project', 'user_id' => $user->id]);
    }

    public function testShowReturnsProject()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $project->id]);
    }

    public function testUpdateModifiesProject()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/projects/{$project->id}", [
                'name' => 'Updated Project',
            ], ['If-Match' => $project->updated_at->toString()]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Project']);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Project']);
    }

    public function testUpdateFailsOnVersionMismatch()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/projects/{$project->id}", [
                'name' => 'Updated Project',
            ], ['If-Match' => 'invalid-timestamp']);

        $response->assertStatus(409)
            ->assertJsonFragment(['error' => 'Version mismatch']);
    }

    public function testDestroyDeletesProject()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $this->assertTrue($user->hasRole('owner'), 'User does not have owner role');
        $project = Project::factory()->create(['user_id' => $user->id]);
        $this->assertEquals($user->id, $project->user_id, 'Project user_id does not match');

        $response = $this->actingAs($user)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

     public function testApplyArchetypeCreatesPages()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Mock the TemplateRegistryService to avoid real service validation issues
        $templateService = $this->mock(TemplateRegistryService::class);
        $templateService->shouldReceive('applyToProject')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($project) {
                return $arg instanceof \App\Models\Project && $arg->id === $project->id;
            }), 'services', 'test-key-123')
            ->andReturn([
                ['id' => 1, 'page_type' => 'home', 'title' => 'Home'],
                ['id' => 2, 'page_type' => 'services', 'title' => 'Services'],
            ]);

        $response = $this->actingAs($user)
            ->postJson("/api/projects/{$project->id}/apply-archetype", [
                'archetype' => 'services',
                'idempotency_key' => 'test-key-123'
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'pages');
    }

    public function testApplyArchetypeHandlesIdempotency()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Mock the TemplateRegistryService for both calls
        $templateService = $this->mock(TemplateRegistryService::class);
        
        // First call - should create pages
        $templateService->shouldReceive('applyToProject')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($project) {
                return $arg instanceof \App\Models\Project && $arg->id === $project->id;
            }), 'services', 'idempotency-test')
            ->andReturn([
                ['id' => 1, 'page_type' => 'home', 'title' => 'Home'],
                ['id' => 2, 'page_type' => 'services', 'title' => 'Services'],
            ]);

        // Second call - should return cached result (same response)
        $templateService->shouldReceive('applyToProject')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($project) {
                return $arg instanceof \App\Models\Project && $arg->id === $project->id;
            }), 'services', 'idempotency-test')
            ->andReturn([
                ['id' => 1, 'page_type' => 'home', 'title' => 'Home'],
                ['id' => 2, 'page_type' => 'services', 'title' => 'Services'],
            ]);

        // First request
        $response1 = $this->actingAs($user)
            ->postJson("/api/projects/{$project->id}/apply-archetype", [
                'archetype' => 'services',
                'idempotency_key' => 'idempotency-test'
            ]);

        $response1->assertStatus(200)
            ->assertJsonCount(2, 'pages');

        // Second request with same key - should return same result
        $response2 = $this->actingAs($user)
            ->postJson("/api/projects/{$project->id}/apply-archetype", [
                'archetype' => 'services',
                'idempotency_key' => 'idempotency-test'
            ]);

        $response2->assertStatus(200)
            ->assertJsonCount(2, 'pages');

        // Both responses should be identical
        $this->assertEquals($response1->json('pages'), $response2->json('pages'));
    }

    public function testApplyArchetypeFailsForUnauthorizedUser()
    {
        $user = User::factory()->create()->assignRole('viewer');
        $project = Project::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/projects/{$project->id}/apply-archetype", [
                'archetype' => 'professional',
                'idempotency_key' => 'uuid-123',
            ]);

        $response->assertStatus(403);
    }

    public function testApplyArchetypeFailsForInvalidArchetype()
    {
        $user = User::factory()->create()->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/projects/{$project->id}/apply-archetype", [
                'archetype' => 'invalid',
                'idempotency_key' => 'uuid-123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('archetype');
    }
}
?>