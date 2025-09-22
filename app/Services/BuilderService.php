<?php

namespace App\Services;

class BuilderService
{
    public function validatePageStructure(array $structure): bool
    {
        $h1Count = 0;
        $hasMain = false;
        $headingLevels = [];
        
        foreach ($structure['components'] ?? [] as $component) {
            if ($component['type'] === 'Hero' && isset($component['props']['headline'])) {
                $h1Count++;
                $headingLevels[] = 1;
            }
            if ($component['type'] === 'Section' && isset($component['props']['heading'])) {
                // Get actual heading level from component props
                $level = $component['props']['heading_level'] ?? 2;
                $headingLevels[] = $level;
            }
            if ($component['type'] === 'Main') {
                $hasMain = true;
            }
        }
        
        // Check: exactly one H1, proper heading hierarchy, and main landmark
        return $h1Count === 1 && 
            $this->validateHeadingHierarchy($headingLevels) && 
            $hasMain;
    }

    private function validateHeadingHierarchy(array $levels): bool
    {
        if (empty($levels)) return true;
        
        // First heading should be H1
        if ($levels[0] !== 1) return false;
        
        // Check that heading levels don't skip (h1→h3 without h2 is invalid)
        for ($i = 1; $i < count($levels); $i++) {
            if ($levels[$i] > $levels[$i-1] + 1) {
                return false;
            }
        }
        
        return true;
    }

    public function suggestLayoutFromKeywords(array $keywords): array
    {
        $components = [];
        if (in_array('business valuation', $keywords)) {
            $components[] = [
                'type' => 'Hero',
                'props' => ['headline' => 'Expert Business Valuations', 'sub' => 'USPAP-compliant reports', 'cta' => 'Get a Quote'],
            ];
            $components[] = [
                'type' => 'Main',
                'props' => ['content' => 'Main content section'],
            ];
            $components[] = [
                'type' => 'ServicesGrid',
                'props' => ['items' => [['title' => 'M&A Valuations', 'heading' => 'Valuation Services'], ['title' => 'Gift & Estate']]],
            ];
            $components[] = ['type' => 'CTA', 'props' => ['text' => 'Schedule a Call']];
        }
        return ['components' => $components, 'version' => '1'];
    }
}