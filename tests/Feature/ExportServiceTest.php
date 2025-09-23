<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Page;
use App\Services\ExportService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $exportService;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->exportService = app(ExportService::class);
        $user = User::factory()->create()->assignRole('owner');
        $this->project = Project::factory()->create(['user_id' => $user->id]);
        Storage::fake('private');
    }

    public function testItGeneratesWordpressThemeForProjectWithPages()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'page_type' => 'home',
            'slug' => 'home',
            'page_structure' => [
                'components' => [
                    ['id' => 'hero-1', 'type' => 'Hero', 'props' => ['headline' => 'Welcome', 'aria_label' => 'Main hero']],
                ],
            ],
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);
        $zip->addFromString('page-test.php', '<?php // dummy test page ?>');
        $zip->close();

        $this->assertFileExists($zipPath);
        $this->exportService->cleanup($zipPath);
        $this->assertFileDoesNotExist($zipPath);
    }

    public function testItCreatesValidWordpressThemeStructure()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'page_type' => 'home',
            'slug' => 'home',
            'page_structure' => [
                'components' => [
                    ['id' => 'hero-1', 'type' => 'Hero', 'props' => ['headline' => 'Welcome', 'aria_label' => 'Main hero']],
                ],
            ],
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $this->assertFileExists($tempDir . '/style.css');
        $this->assertFileExists($tempDir . '/header.php');
        $this->assertFileExists($tempDir . '/footer.php');
        $this->assertFileExists($tempDir . '/index.php');
        $this->assertFileExists($tempDir . '/page-home.php');

        $this->deleteDirectory($tempDir);
        $this->exportService->cleanup($zipPath);
    }

    public function testItGeneratesUniqueThemeNames()
    {
        $zipPath1 = $this->exportService->generateWordPressTheme($this->project);
        sleep(1); // Ensure different timestamp
        $zipPath2 = $this->exportService->generateWordPressTheme($this->project);

        $this->assertNotEquals($zipPath1, $zipPath2);
        $this->assertFileExists($zipPath1);
        $this->assertFileExists($zipPath2);

        $this->exportService->cleanup($zipPath1);
        $this->exportService->cleanup($zipPath2);
    }

    public function testItHandlesProjectsWithNoPages()
    {
        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $this->assertFileExists($tempDir . '/page-empty.php');
        $this->deleteDirectory($tempDir);
        $this->exportService->cleanup($zipPath);
    }

    public function testItHandlesPagesWithEmptyComponents()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'page_type' => 'empty',
            'slug' => 'empty',
            'page_structure' => ['components' => []],
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-empty.php');
        $this->assertStringContainsString('No content defined for this page', $pageContent);

        $this->deleteDirectory($tempDir);
        $this->exportService->cleanup($zipPath);
    }

    public function testItProperlyEscapesComponentContent()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'page_type' => 'xss-test',
            'slug' => 'xss-test',
            'page_structure' => [
                'components' => [
                    ['id' => 'hero-1', 'type' => 'Hero', 'props' => ['headline' => '<script>alert("xss")</script>', 'aria_label' => 'Main hero']],
                ],
            ],
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-xss-test.php');
        $this->assertStringContainsString('&lt;script&gt;', $pageContent);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $pageContent);

        $this->deleteDirectory($tempDir);
        $this->exportService->cleanup($zipPath);
    }

    public function testItHandlesUnknownComponentTypes()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'page_type' => 'unknown',
            'slug' => 'unknown',
            'page_structure' => [
                'components' => [
                    ['id' => 'unknown-1', 'type' => 'UnknownComponent', 'props' => ['aria_label' => 'Unknown component']],
                ],
            ],
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-unknown.php');
        $this->assertStringContainsString('Unknown component type: UnknownComponent', $pageContent);

        $this->deleteDirectory($tempDir);
        $this->exportService->cleanup($zipPath);
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
?>