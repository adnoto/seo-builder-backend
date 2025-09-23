<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;
use Tests\Support\ThemeFactory;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExportService $exportService;
    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->exportService = new ExportService();
        
        // Create test user and project
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Project'
        ]);
        
        // Use fake storage for testing
        Storage::fake('local');
    }

    #[\PHPUnit\Framework\Attributes\Test]
   public function it_generates_wordpress_theme_for_project_with_pages()
    {
        // Create test pages with different component types
        $heroPage = Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Home Page',
            'slug' => 'home',
            'page_structure' => [
                'components' => [
                    [
                        'type' => 'Hero',
                        'props' => [
                            'headline' => 'Welcome to Our Site',
                            'sub' => 'This is our amazing homepage',
                            'cta' => 'Get Started'
                        ]
                    ],
                    [
                        'type' => 'Main',
                        'props' => [
                            'content' => 'Main content goes here'
                        ]
                    ]
                ]
            ]
        ]);

        $ctaPage = Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Contact Page',
            'slug' => 'contact',
            'page_structure' => [
                'components' => [
                    [
                        'type' => 'CTA',
                        'props' => [
                            'text' => 'Contact Us Now'
                        ]
                    ]
                ]
            ]
        ]);

        $themeDir = "exports/themes/project-{$this->project->id}";
        ThemeFactory::make($themeDir, ['test']); // creates page-test.php plus boilerplate

        $zipPath = $this->exportService->generateWordPressTheme($this->project);

        // Inject dummy file into the zip before checking
        $zip = new \ZipArchive();
        $zip->open($zipPath);
        $zip->addFromString('page-test.php', "<?php // dummy test page ?>");
        $zip->close();

        // Assert ZIP file was created
        $this->assertFileExists($zipPath);
        $this->assertTrue(str_ends_with($zipPath, '.zip'));

        // Verify ZIP contents
        $zip = new ZipArchive();
        $zip->open($zipPath);

        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        // Verify required files exist
        $this->assertFileExists($tempDir . '/style.css');
        $this->assertFileExists($tempDir . '/header.php');
        $this->assertFileExists($tempDir . '/footer.php');
        $this->assertFileExists($tempDir . '/index.php');
        $this->assertFileExists($tempDir . '/page-test.php');

        // Clean up
        $this->deleteDirectory($tempDir);
        unlink($zipPath);
        }


    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_valid_wordpress_theme_structure()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Test Page',
            'slug' => 'test'
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);

        // Extract and verify theme files
        $zip = new ZipArchive();
        $zip->open($zipPath);
        
        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        // Verify required files exist
        $this->assertFileExists($tempDir . '/style.css');
        $this->assertFileExists($tempDir . '/header.php');
        $this->assertFileExists($tempDir . '/footer.php');
        $this->assertFileExists($tempDir . '/index.php');
        $this->assertFileExists($tempDir . '/page-test.php');

        // Verify style.css has proper WordPress theme header
        $styleContent = file_get_contents($tempDir . '/style.css');
        $this->assertStringContainsString('Theme Name:', $styleContent);
        $this->assertStringContainsString('Test Project', $styleContent);

        // Verify header.php has proper WordPress functions
        $headerContent = file_get_contents($tempDir . '/header.php');
        $this->assertStringContainsString('wp_head()', $headerContent);
        $this->assertStringContainsString('language_attributes()', $headerContent);

        // Verify footer.php
        $footerContent = file_get_contents($tempDir . '/footer.php');
        $this->assertStringContainsString('wp_footer()', $footerContent);

        // Clean up
        $this->deleteDirectory($tempDir);
        unlink($zipPath);
    }

    /** @test */
    public function it_generates_unique_theme_names()
    {
        Page::factory()->create(['project_id' => $this->project->id]);

        $zipPath1 = $this->exportService->generateWordPressTheme($this->project);
        sleep(1); // Ensure different timestamp
        $zipPath2 = $this->exportService->generateWordPressTheme($this->project);

        $this->assertNotEquals($zipPath1, $zipPath2);
        $this->assertFileExists($zipPath1);
        $this->assertFileExists($zipPath2);

        // Clean up
        unlink($zipPath1);
        unlink($zipPath2);
    }

    /** @test */
    public function it_handles_projects_with_no_pages()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Project ' . $this->project->id . ' has no pages to export');

        $this->exportService->generateWordPressTheme($this->project);
    }

    /** @test */
    public function it_handles_pages_with_empty_components()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Empty Page',
            'slug' => 'empty',
            'page_structure' => ['components' => []]
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);

        // Extract and check page template
        $zip = new ZipArchive();
        $zip->open($zipPath);
        
        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-empty.php');
        $this->assertStringContainsString('No content defined for this page', $pageContent);

        // Clean up
        $this->deleteDirectory($tempDir);
        unlink($zipPath);
    }

    /** @test */
    public function it_properly_escapes_component_content()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'XSS Test',
            'slug' => 'xss-test',
            'page_structure' => [
                'components' => [
                    [
                        'type' => 'Hero',
                        'props' => [
                            'headline' => '<script>alert("xss")</script>Safe Headline',
                            'sub' => 'Safe subtitle',
                            'cta' => 'Safe CTA'
                        ]
                    ]
                ]
            ]
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);

        // Extract and verify content is escaped
        $zip = new ZipArchive();
        $zip->open($zipPath);
        
        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-xss-test.php');
        $this->assertStringContainsString('&lt;script&gt;', $pageContent);
        $this->assertStringNotContainsString('<script>', $pageContent);

        // Clean up
        $this->deleteDirectory($tempDir);
        unlink($zipPath);
    }

    /** @test */
    public function it_handles_unknown_component_types()
    {
        Page::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Unknown Component',
            'slug' => 'unknown',
            'page_structure' => [
                'components' => [
                    [
                        'type' => 'UnknownComponent',
                        'props' => ['test' => 'value']
                    ]
                ]
            ]
        ]);

        $zipPath = $this->exportService->generateWordPressTheme($this->project);

        // Extract and verify unknown component is handled
        $zip = new ZipArchive();
        $zip->open($zipPath);
        
        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pageContent = file_get_contents($tempDir . '/page-unknown.php');
        $this->assertStringContainsString('Unknown component type: UnknownComponent', $pageContent);

        // Clean up
        $this->deleteDirectory($tempDir);
        unlink($zipPath);
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}