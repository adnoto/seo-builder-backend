<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Exception;
use ZipArchive;

class ExportService
{
    public function generateWordPressTheme(Project $project): string
    {
        $pages = Page::where('project_id', $project->id)->get();
        
        if ($pages->isEmpty()) {
            throw new Exception("Project {$project->id} has no pages to export");
        }
        
        // Generate unique theme name to prevent overwrites
        $themeName = "seobuilder-project-{$project->id}-" . date('Ymd-His');
        $themeDir = "exports/{$themeName}";
        
        Storage::makeDirectory($themeDir);
        
        // Generate style.css (must be first for WordPress theme recognition)
        $style = $this->generateStyleCss($project, $themeName);
        Storage::put("{$themeDir}/style.css", $style);
        
        // Generate header.php
        $header = $this->generateHeader($project);
        Storage::put("{$themeDir}/header.php", $header);
        
        // Generate footer.php
        $footer = $this->generateFooter();
        Storage::put("{$themeDir}/footer.php", $footer);
        
        // Generate index.php (WordPress fallback requirement)
        $index = $this->generateIndexTemplate();
        Storage::put("{$themeDir}/index.php", $index);
        
        // Generate page templates
        foreach ($pages as $page) {
            $content = $this->generatePageTemplate($page);
            Storage::put("{$themeDir}/page-{$page->slug}.php", $content);
        }
        
        // Create ZIP file - use storage disk for consistency
        $zipFilename = "seobuilder-project-{$project->id}-" . date('Ymd-His') . ".zip";
        $zipPath = "exports/{$zipFilename}";
        $fullZipPath = Storage::path($zipPath);

        // Ensure directory exists
        $zipDir = dirname($fullZipPath);
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($fullZipPath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create ZIP file: {$fullZipPath}");
        }
        
        // Add all theme files to ZIP
        $files = Storage::files($themeDir);
        foreach ($files as $file) {
            $zip->addFile(Storage::path($file), basename($file));
        }
        
        if (!$zip->close()) {
            throw new Exception("Failed to close ZIP file: {$fullZipPath}");
        }
        
        // Clean up temporary directory
        Storage::deleteDirectory($themeDir);
        
        return $fullZipPath;
    }
    
    protected function generateStyleCss(Project $project, string $themeName): string
    {
        return <<<CSS
/*
Theme Name: SEO Builder Project {$project->name}
Description: Generated theme for project {$project->name}
Author: SEO Builder
Version: 1.0
Text Domain: {$themeName}
*/

body {
    font-family: system-ui, -apple-system, sans-serif;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

header {
    background: #f8f9fa;
    padding: 2rem 1rem;
    text-align: center;
}

main {
    padding: 2rem 1rem;
    max-width: 800px;
    margin: 0 auto;
}

.cta-section {
    background: #007cba;
    color: white;
    padding: 2rem 1rem;
    text-align: center;
}

.cta-section a {
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 4px;
}
CSS;
    }
    
    protected function generateHeader(Project $project): string
    {
        return <<<PHP
<?php
/**
 * Header template for SEO Builder theme
 * Project: {$project->name}
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
PHP;
    }
    
    protected function generateFooter(): string
    {
        return <<<PHP
<?php wp_footer(); ?>
</body>
</html>
PHP;
    }
    
    protected function generateIndexTemplate(): string
    {
        return <<<PHP
<?php
/**
 * Main template file
 * Fallback template for SEO Builder theme
 */
get_header();
?>

<main>
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article>
                <h1><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>No content found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
PHP;
    }
    
    protected function generatePageTemplate(Page $page): string
    {
        $content = "<?php\n/**\n * Template Name: {$page->title}\n * Generated from SEO Builder\n */\nget_header();\n?>\n\n";
        
        $components = $page->page_structure['components'] ?? [];
        
        if (empty($components)) {
            $content .= "<main><h1>" . e($page->title) . "</h1><p>No content defined for this page.</p></main>\n";
        } else {
            foreach ($components as $component) {
                $content .= $this->renderComponent($component);
            }
        }
        
        $content .= "\n<?php get_footer(); ?>";
        
        return $content;
    }
    
    protected function renderComponent(array $component): string
    {
        switch ($component['type']) {
            case 'Hero':
                return "<header>\n" .
                       "    <h1>" . e($component['props']['headline'] ?? '') . "</h1>\n" .
                       "    <p>" . e($component['props']['sub'] ?? '') . "</p>\n" .
                       "    <a href='#'>" . e($component['props']['cta'] ?? 'Learn More') . "</a>\n" .
                       "</header>\n\n";
                       
            case 'Main':
                return "<main>\n" .
                       "    " . e($component['props']['content'] ?? '') . "\n" .
                       "</main>\n\n";
                       
            case 'CTA':
                return "<section class='cta-section'>\n" .
                       "    <a href='#'>" . e($component['props']['text'] ?? 'Click Here') . "</a>\n" .
                       "</section>\n\n";
                       
            default:
                return "<!-- Unknown component type: {$component['type']} -->\n";
        }
    }
}