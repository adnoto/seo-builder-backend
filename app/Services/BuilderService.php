<?php
?/*Tradeoffs Flagged: BuilderService keyword-to-layout mapping is basic; expand later with AI-driven clustering (SEMrush/Ahrefs integration).
JSON validation ensures semantic HTML but may reject valid structures; refine rules as needed. Scalability ensured via indexes and FKs.*/
namespace App\Services;

class BuilderService
{
    public function validatePageStructure(array $structure): bool
    {
        $h1Count = 0;
        foreach ($structure['components'] ?? [] as $component) {
            if ($component['type'] === 'Hero' && isset($component['props']['headline'])) {
                $h1Count++;
            }
        }
        return $h1Count === 1; // Enforce one H1 per page
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
                'type' => 'ServicesGrid',
                'props' => ['items' => [['title' => 'M&A Valuations'], ['title' => 'Gift & Estate']]],
            ];
            $components[] = ['type' => 'CTA', 'props' => ['text' => 'Schedule a Call']];
        }
        return ['components' => $components, 'version' => '1'];
    }
}