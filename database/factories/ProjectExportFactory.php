<?php

namespace Database\Factories;

use App\Models\ProjectExport;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectExportFactory extends Factory
{
    protected $model = ProjectExport::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'export_type' => 'wordpress_theme',
            'file_path' => 'exports/test-export-' . $this->faker->uuid() . '.zip',
            'original_filename' => 'test-theme-export.zip',
            'file_size' => $this->faker->numberBetween(100000, 5000000), // 100KB to 5MB
            'signed_url' => null,
            'expires_at' => null,
            'download_count' => 0,
            'last_downloaded_at' => null,
            'snapshot_sha' => $this->faker->sha256(),
            'export_metadata' => null,
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'file_path' => null,
            'original_filename' => null,
            'file_size' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'file_path' => null,
            'original_filename' => null,
            'file_size' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}