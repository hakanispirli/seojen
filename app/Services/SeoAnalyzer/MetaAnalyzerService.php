<?php

namespace App\Services\SeoAnalyzer;

use DOMDocument;

class MetaAnalyzerService
{
    private $dom;

    public function analyze(string $html): array
    {
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($html, LIBXML_NOERROR);

        return [
            'title' => $this->getTitle(),
            'meta_tags' => $this->getMetaTags(),
            'score' => $this->calculateScore()
        ];
    }

    private function getTitle(): array
    {
        $title = '';
        $titleTags = $this->dom->getElementsByTagName('title');

        if ($titleTags->length > 0) {
            $title = $titleTags->item(0)->nodeValue;
        }

        return [
            'content' => $title,
            'length' => strlen($title),
            'status' => $this->analyzeTitleStatus($title)
        ];
    }

    private function getMetaTags(): array
    {
        $metaTags = [];
        $metas = $this->dom->getElementsByTagName('meta');

        foreach ($metas as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $metaTags['description'] = [
                    'content' => $meta->getAttribute('content'),
                    'length' => strlen($meta->getAttribute('content')),
                    'status' => $this->analyzeDescriptionStatus($meta->getAttribute('content'))
                ];
            }
        }

        return $metaTags;
    }

    private function analyzeTitleStatus(string $title): string
    {
        $length = strlen($title);
        if ($length === 0) return 'error';
        if ($length < 30) return 'warning';
        if ($length > 60) return 'warning';
        return 'success';
    }

    private function analyzeDescriptionStatus(string $description): string
    {
        $length = strlen($description);
        if ($length === 0) return 'error';
        if ($length < 120) return 'warning';
        if ($length > 160) return 'warning';
        return 'success';
    }

    private function calculateScore(): int
    {
        $score = 100;
        $title = $this->getTitle();

        if ($title['status'] === 'error') $score -= 30;
        if ($title['status'] === 'warning') $score -= 15;

        return max(0, $score);
    }
}
