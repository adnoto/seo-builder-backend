<?php

namespace Tests\Feature;

use App\Jobs\GenerateWordPressExport;
use App\Models\Project;
use App\Models\ProjectExport;
use App\Models\User;
use App\Services\ExportService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateWordPressExportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $export;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create()->assignRole('owner');
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
        $this->export = ProjectExport::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending'
        ]);
        Storage::fake('private');
    }

    public function testItProcessesExportSuccessfully()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->with(\Mockery::type(Project::class)) // Accept any Project instance
            ->andReturn(storage_path('app/private/exports/test.zip'));

        $job = new GenerateWordPressExport($this->export);
        $job->handle(app(ExportService::class));

        $this->export->refresh();
        $this->assertEquals('ready', $this->export->status);
        $this->assertNotNull($this->export->file_path);
    }

    public function testItUpdatesStatusToProcessingBeforeStarting()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andReturnUsing(function() {
                // Check status is 'processing' during execution
                $this->export->refresh();
                $this->assertEquals('processing', $this->export->status);
                return storage_path('app/private/exports/test.zip');
            });

        $job = new GenerateWordPressExport($this->export);
        $job->handle(app(ExportService::class));
    }

    public function testItMarksExportAsFailedOnException()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andThrow(new \RuntimeException('Export generation failed'));

        $job = new GenerateWordPressExport($this->export);

        try {
            $job->handle(app(ExportService::class));
        } catch (\RuntimeException $e) {
            // Exception should be re-thrown
            $this->assertEquals('Export generation failed', $e->getMessage());
        }

        $this->export->refresh();
        $this->assertEquals('failed', $this->export->status);
    }

    public function testItSetsCorrectFilePathAndFilename()
    {
        $expectedZipPath = storage_path('app/private/exports/test-project.zip');
        
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andReturn($expectedZipPath);

        $job = new GenerateWordPressExport($this->export);
        $job->handle(app(ExportService::class));

        $this->export->refresh();
        $expectedRelativePath = 'private/exports/test-project.zip';
        $this->assertEquals($expectedRelativePath, $this->export->file_path);
    }

    public function testItHandlesExportServiceException()
    {
        $this->mock(ExportService::class)
            ->shouldReceive('generateWordPressTheme')
            ->once()
            ->andThrow(new \RuntimeException('Service unavailable'));

        $job = new GenerateWordPressExport($this->export);

        $this->expectException(\RuntimeException::class);
        $job->handle(app(ExportService::class));
    }
}