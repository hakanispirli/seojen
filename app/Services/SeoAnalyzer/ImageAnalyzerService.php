<?php

namespace App\Services\SeoAnalyzer;

use DOMDocument;

class ImageAnalyzerService
{
    private $dom;

    public function analyze(string $html): array
    {
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($html, LIBXML_NOERROR);

        $images = $this->getImages();

        return [
            'images' => $images,
            'statistics' => $this->getStatistics($images),
            'score' => $this->calculateScore($images)
        ];
    }

    private function getImages(): array
    {
        $images = [];
        $imgs = $this->dom->getElementsByTagName('img');

        foreach ($imgs as $img) {
            $images[] = [
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'has_alt' => $img->hasAttribute('alt'),
                'status' => $this->analyzeImageStatus($img)
            ];
        }

        return $images;
    }

    private function analyzeImageStatus($img): string
    {
        if (!$img->hasAttribute('alt')) return 'error';
        if (strlen($img->getAttribute('alt')) === 0) return 'warning';
        return 'success';
    }

    private function getStatistics(array $images): array
    {
        $total = count($images);
        $withAlt = 0;
        $withEmptyAlt = 0;
        $withoutAlt = 0;

        foreach ($images as $image) {
            if (!$image['has_alt']) {
                $withoutAlt++;
            } elseif (strlen($image['alt']) === 0) {
                $withEmptyAlt++;
            } else {
                $withAlt++;
            }
        }

        return [
            'total' => $total,
            'with_alt' => $withAlt,
            'with_empty_alt' => $withEmptyAlt,
            'without_alt' => $withoutAlt
        ];
    }

    private function calculateScore(array $images): int
    {
        if (empty($images)) return 100;

        $score = 100;
        $total = count($images);
        $withoutAlt = 0;
        $withEmptyAlt = 0;

        foreach ($images as $image) {
            if (!$image['has_alt']) {
                $withoutAlt++;
            } elseif (strlen($image['alt']) === 0) {
                $withEmptyAlt++;
            }
        }

        $score -= ($withoutAlt / $total) * 50;
        $score -= ($withEmptyAlt / $total) * 25;

        return max(0, (int)$score);
    }
}
