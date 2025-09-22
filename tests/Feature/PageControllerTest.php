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

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_can_create_page_with_valid_structure(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner'); // Call assignRole on the user instance
        
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'home',
                'slug' => 'home',
                'title' => 'Home Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Hero', 'props' => ['headline' => 'Welcome', 'sub' => 'Subtitle', 'cta' => 'Click']],
                        ['type' => 'Main', 'props' => ['content' => 'Main content']],
                    ],
                ],
            ]);

        if ($response->status() !== 201) {
            dump($response->json());
        }    

        $response->assertStatus(201);
        $this->assertDatabaseHas('pages', [
            'project_id' => $project->id,
            'slug' => 'home',
        ]);
    }

    public function test_invalid_structure_fails(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
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

    public function test_viewer_cannot_create_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

           // Debug output
        dump('User roles:', $user->getRoleNames()->toArray());
        dump('User has viewer role:', $user->hasRole('viewer'));
        dump('User has owner role:', $user->hasRole('owner')); 



        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'home',
                'slug' => 'home',
                'title' => 'Home Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Hero', 'props' => ['headline' => 'Welcome']],
                        ['type' => 'Main', 'props' => ['content' => 'Main content']],
                    ],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_update_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);
        $page = Page::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/projects/{$project->id}/pages/{$page->id}", [
                'title' => 'Updated Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Hero', 'props' => ['headline' => 'Updated', 'sub' => 'Subtitle', 'cta' => 'Click']],
                        ['type' => 'Main', 'props' => ['content' => 'Main content']],
                    ],
                ],
            ], ['If-Match' => $page->updated_at->toString()]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Page']);
    }

    public function test_invalid_heading_order_fails(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$project->id}/pages", [
                'page_type' => 'home',
                'slug' => 'home', 
                'title' => 'Home Page',
                'page_structure' => [
                    'components' => [
                        ['type' => 'Section', 'props' => ['heading' => 'H2 First']], // Missing H1
                        ['type' => 'Main', 'props' => ['content' => 'Main content']],
                    ],
                ],
            ]);

        $response->assertStatus(422);
    }
}