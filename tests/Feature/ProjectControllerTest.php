<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_page(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'home',
                'slug' => 'home',
                'title' => 'Home Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Hero', 'props' => ['headline' => 'Welcome']],
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pages', [
            'project_id' => $project->id,
            'slug' => 'home',
        ]);
    }

    public function test_invalid_structure_fails(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'home',
                'slug' => 'home',
                'title' => 'Home Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Hero', 'props' => ['headline' => 'First']],
                        ['type' => 'Hero', 'props' => ['headline' => 'Second']],
                    ],
                ],
            ]);

        $response->assertStatus(422);
    }
}