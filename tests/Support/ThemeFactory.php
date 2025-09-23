<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Storage;

class ThemeFactory
{
    public static function make(string $themeDir, array $pages = []): void
    {
        Storage::disk('local')->put("$themeDir/style.css", "/* test theme */");
        Storage::disk('local')->put("$themeDir/index.php", "<?php // index ?>");
        Storage::disk('local')->put("$themeDir/header.php", "<?php // header ?>");
        Storage::disk('local')->put("$themeDir/footer.php", "<?php // footer ?>");

        foreach ($pages as $slug) {
            Storage::disk('local')->put("$themeDir/page-{$slug}.php", "<?php // page $slug ?>");
        }
    }
}
