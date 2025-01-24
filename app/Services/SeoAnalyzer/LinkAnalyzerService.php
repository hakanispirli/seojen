<?php

namespace App\Services\SeoAnalyzer;

use DOMDocument;
use DOMXPath;
use DOMElement;

class LinkAnalyzerService
{
    private $httpClient;
    private $baseUrl;

    public function __construct(HttpClientService $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function analyze(string $url, string $html): array
    {
        $this->baseUrl = $url;
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        $internalLinks = $this->analyzeInternalLinks($xpath);
        $externalLinks = $this->analyzeExternalLinks($xpath);
        $brokenLinks = $this->checkBrokenLinks(
            array_merge($internalLinks['links'], $externalLinks['links'])
        );
        $anchorAnalysis = $this->analyzeAnchorTexts($xpath);
        $followStatus = $this->analyzeFollowStatus($xpath);

        $score = $this->calculateScore([
            'internal_links' => $internalLinks['score'],
            'external_links' => $externalLinks['score'],
            'broken_links' => $brokenLinks['score'],
            'anchor_texts' => $anchorAnalysis['score'],
            'follow_status' => $followStatus['score']
        ]);

        return [
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
            'broken_links' => $brokenLinks,
            'anchor_texts' => $anchorAnalysis,
            'follow_status' => $followStatus,
            'score' => $score,
            'recommendations' => $this->generateRecommendations(
                $internalLinks,
                $externalLinks,
                $brokenLinks,
                $anchorAnalysis,
                $followStatus
            )
        ];
    }

    private function analyzeInternalLinks(DOMXPath $xpath): array
    {
        $links = [];
        $issues = [];
        $internalLinkCount = 0;

        $nodes = $xpath->query('//a[@href]');
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');
            if ($this->isInternalLink($href)) {
                $internalLinkCount++;
                $links[] = [
                    'url' => $this->makeAbsoluteUrl($href),
                    'text' => $node->textContent,
                    'rel' => $node->getAttribute('rel')
                ];
            }
        }

        // İç bağlantı sayısı kontrolü
        if ($internalLinkCount < 3) {
            $issues[] = 'İç bağlantı sayısı çok düşük. En az 3 iç bağlantı önerilir.';
        } elseif ($internalLinkCount > 100) {
            $issues[] = 'İç bağlantı sayısı çok yüksek. 100\'den az olması önerilir.';
        }

        return [
            'count' => $internalLinkCount,
            'links' => $links,
            'issues' => $issues,
            'score' => $this->calculateInternalLinkScore($internalLinkCount, $issues)
        ];
    }

    private function analyzeExternalLinks(DOMXPath $xpath): array
    {
        $links = [];
        $issues = [];
        $externalLinkCount = 0;

        $nodes = $xpath->query('//a[@href]');
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');
            if ($this->isExternalLink($href)) {
                $externalLinkCount++;
                $links[] = [
                    'url' => $href,
                    'text' => $node->textContent,
                    'rel' => $node->getAttribute('rel')
                ];

                // rel="noopener noreferrer" kontrolü
                if (!str_contains($node->getAttribute('rel'), 'noopener') ||
                    !str_contains($node->getAttribute('rel'), 'noreferrer')) {
                    $issues[] = "Dış bağlantıda güvenlik attributeleri eksik: $href";
                }
            }
        }

        return [
            'count' => $externalLinkCount,
            'links' => $links,
            'issues' => $issues,
            'score' => $this->calculateExternalLinkScore($externalLinkCount, $issues)
        ];
    }

    private function checkBrokenLinks(array $links): array
    {
        $brokenLinks = [];
        $checkedUrls = [];

        foreach ($links as $link) {
            $url = $link['url'];

            // Aynı URL'yi tekrar kontrol etmeyi önle
            if (isset($checkedUrls[$url])) {
                continue;
            }

            try {
                $response = $this->httpClient->fetchUrl($url);
                if ($response['status'] >= 400) {
                    $brokenLinks[] = [
                        'url' => $url,
                        'status' => $response['status']
                    ];
                }
            } catch (\Exception $e) {
                $brokenLinks[] = [
                    'url' => $url,
                    'error' => $e->getMessage()
                ];
            }

            $checkedUrls[$url] = true;
        }

        return [
            'broken_count' => count($brokenLinks),
            'links' => $brokenLinks,
            'score' => $this->calculateBrokenLinkScore(count($brokenLinks))
        ];
    }

    private function analyzeAnchorTexts(DOMXPath $xpath): array
    {
        $anchorTexts = [];
        $issues = [];

        $nodes = $xpath->query('//a[@href]');
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $text = trim($node->textContent);
            $href = $node->getAttribute('href');

            if (empty($text)) {
                $issues[] = "Boş anchor text: $href";
                continue;
            }

            if (in_array(strtolower($text), ['buraya tıklayın', 'tıklayın', 'here', 'click here'])) {
                $issues[] = "Genel anchor text kullanımı: '$text'";
            }

            $anchorTexts[] = [
                'text' => $text,
                'url' => $href,
                'length' => strlen($text)
            ];
        }

        return [
            'texts' => $anchorTexts,
            'issues' => $issues,
            'score' => $this->calculateAnchorTextScore($issues)
        ];
    }

    private function analyzeFollowStatus(DOMXPath $xpath): array
    {
        $followStatus = [
            'follow' => 0,
            'nofollow' => 0
        ];

        $nodes = $xpath->query('//a[@href]');
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $rel = $node->getAttribute('rel');
            if (str_contains($rel, 'nofollow')) {
                $followStatus['nofollow']++;
            } else {
                $followStatus['follow']++;
            }
        }

        return [
            'status' => $followStatus,
            'score' => $this->calculateFollowStatusScore($followStatus)
        ];
    }

    private function isInternalLink(string $href): bool
    {
        if (empty($href) || str_starts_with($href, '#')) {
            return false;
        }

        $baseHost = parse_url($this->baseUrl, PHP_URL_HOST);
        $linkHost = parse_url($href, PHP_URL_HOST);

        return empty($linkHost) || $linkHost === $baseHost;
    }

    private function isExternalLink(string $href): bool
    {
        if (empty($href) || str_starts_with($href, '#')) {
            return false;
        }

        $baseHost = parse_url($this->baseUrl, PHP_URL_HOST);
        $linkHost = parse_url($href, PHP_URL_HOST);

        return !empty($linkHost) && $linkHost !== $baseHost;
    }

    private function makeAbsoluteUrl(string $href): string
    {
        if (empty($href) || str_starts_with($href, '#')) {
            return $href;
        }

        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }

        $baseUrlParts = parse_url($this->baseUrl);
        $base = $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'];

        if (str_starts_with($href, '/')) {
            return $base . $href;
        }

        return $base . '/' . $href;
    }

    private function calculateScore(array $scores): int
    {
        $weights = [
            'internal_links' => 0.25,
            'external_links' => 0.2,
            'broken_links' => 0.25,
            'anchor_texts' => 0.15,
            'follow_status' => 0.15
        ];

        $weightedScore = 0;
        foreach ($scores as $key => $score) {
            $weightedScore += $score * $weights[$key];
        }

        return min(100, max(0, (int)$weightedScore));
    }

    private function calculateInternalLinkScore(int $count, array $issues): int
    {
        $score = 100;

        if ($count < 3) {
            $score -= 40;
        } elseif ($count < 5) {
            $score -= 20;
        } elseif ($count > 100) {
            $score -= 30;
        }

        $score -= count($issues) * 10;

        return max(0, $score);
    }

    private function calculateExternalLinkScore(int $count, array $issues): int
    {
        $score = 100;

        if ($count === 0) {
            $score -= 20;
        } elseif ($count > 50) {
            $score -= 30;
        }

        $score -= count($issues) * 10;

        return max(0, $score);
    }

    private function calculateBrokenLinkScore(int $brokenCount): int
    {
        if ($brokenCount === 0) {
            return 100;
        }

        return max(0, 100 - ($brokenCount * 25));
    }

    private function calculateAnchorTextScore(array $issues): int
    {
        return max(0, 100 - (count($issues) * 15));
    }

    private function calculateFollowStatusScore(array $status): int
    {
        $total = $status['follow'] + $status['nofollow'];
        if ($total === 0) {
            return 0;
        }

        $nofollowRatio = $status['nofollow'] / $total;

        // nofollow oranı %30'dan fazlaysa puan düşür
        if ($nofollowRatio > 0.3) {
            return max(0, 100 - (int)(($nofollowRatio - 0.3) * 200));
        }

        return 100;
    }

    private function generateRecommendations(
        array $internalLinks,
        array $externalLinks,
        array $brokenLinks,
        array $anchorTexts,
        array $followStatus
    ): array {
        $recommendations = [];

        // İç bağlantı önerileri
        if ($internalLinks['count'] < 3) {
            $recommendations[] = 'Sayfaya daha fazla iç bağlantı ekleyin (minimum 3 önerilir).';
        } elseif ($internalLinks['count'] > 100) {
            $recommendations[] = 'İç bağlantı sayısını azaltın (100\'den az olması önerilir).';
        }

        // Dış bağlantı önerileri
        if ($externalLinks['count'] === 0) {
            $recommendations[] = 'Sayfaya güvenilir kaynaklara dış bağlantılar ekleyin.';
        }
        foreach ($externalLinks['issues'] as $issue) {
            $recommendations[] = $issue;
        }

        // Kırık bağlantı önerileri
        if ($brokenLinks['broken_count'] > 0) {
            $recommendations[] = 'Kırık bağlantıları düzeltin veya kaldırın.';
            foreach ($brokenLinks['links'] as $link) {
                $recommendations[] = "Kırık bağlantı tespit edildi: {$link['url']}";
            }
        }

        // Anchor text önerileri
        foreach ($anchorTexts['issues'] as $issue) {
            $recommendations[] = $issue;
        }

        // Follow status önerileri
        $total = $followStatus['status']['follow'] + $followStatus['status']['nofollow'];
        if ($total > 0) {
            $nofollowRatio = $followStatus['status']['nofollow'] / $total;
            if ($nofollowRatio > 0.3) {
                $recommendations[] = 'nofollow bağlantı oranı çok yüksek. Önemli sayfalara dofollow bağlantılar eklemeyi düşünün.';
            }
        }

        return $recommendations;
    }
}
