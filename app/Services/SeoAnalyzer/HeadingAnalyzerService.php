<?php

namespace App\Services\SeoAnalyzer;

use DOMDocument;

class HeadingAnalyzerService
{
    private $dom;

    public function analyze(string $html): array
    {
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($html, LIBXML_NOERROR);

        $headings = $this->getHeadings();

        return [
            'headings' => $headings,
            'structure' => $this->analyzeStructure($headings),
            'score' => $this->calculateScore($headings)
        ];
    }

    private function getHeadings(): array
    {
        $headings = [];

        for ($i = 1; $i <= 6; $i++) {
            $tags = $this->dom->getElementsByTagName('h' . $i);
            $headings['h' . $i] = [
                'count' => $tags->length,
                'content' => []
            ];

            foreach ($tags as $tag) {
                $headings['h' . $i]['content'][] = trim($tag->nodeValue);
            }
        }

        return $headings;
    }

    private function analyzeStructure(array $headings): array
    {
        $issues = [];

        if ($headings['h1']['count'] === 0) {
            $issues[] = 'H1 başlığı bulunamadı.';
        } elseif ($headings['h1']['count'] > 1) {
            $issues[] = 'Birden fazla H1 başlığı kullanılmış.';
        }

        for ($i = 2; $i <= 6; $i++) {
            if ($headings['h' . $i]['count'] > 0 && $headings['h' . ($i-1)]['count'] === 0) {
                $issues[] = "H{$i} başlığı kullanılmış fakat H" . ($i-1) . " başlığı kullanılmamış.";
            }
        }

        return [
            'issues' => $issues,
            'status' => count($issues) === 0 ? 'success' : 'warning'
        ];
    }

    private function calculateScore(array $headings): int
    {
        $score = 100;

        if ($headings['h1']['count'] === 0) $score -= 30;
        if ($headings['h1']['count'] > 1) $score -= 20;

        for ($i = 2; $i <= 6; $i++) {
            if ($headings['h' . $i]['count'] > 0 && $headings['h' . ($i-1)]['count'] === 0) {
                $score -= 10;
            }
        }

        return max(0, $score);
    }
}
