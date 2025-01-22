<?php

namespace App\Services\SeoAnalyzer;

use DOMDocument;
use DOMElement;

class UrlAnalyzerService
{
    private const MAX_URL_LENGTH = 75;
    private const SPECIAL_CHARS = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', ' ', '!', '*', '\'', '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '#', '[', ']'];
    private const REPLACEMENTS = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c', '-', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

    private $httpClient;

    public function __construct(HttpClientService $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function analyze(string $url): array
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $segments = explode('/', trim($path, '/'));

        $urlLength = strlen($url);
        $hasSpecialChars = $this->checkSpecialCharacters($url);
        $hasKeywords = $this->checkKeywords($segments);
        $isSeoFriendly = $this->checkSeoFriendly($url);

        try {
            $problematicUrls = $this->findProblematicUrls($url);
        } catch (\Exception $e) {
            $problematicUrls = [];
        }

        $score = $this->calculateScore([
            'length' => $urlLength <= self::MAX_URL_LENGTH,
            'special_chars' => !$hasSpecialChars,
            'keywords' => $hasKeywords,
            'seo_friendly' => $isSeoFriendly
        ]);

        $recommendations = $this->generateRecommendations($urlLength, $hasSpecialChars, $hasKeywords, $isSeoFriendly);

        return [
            'url_length' => [
                'length' => $urlLength,
                'status' => $urlLength <= self::MAX_URL_LENGTH ? 'success' : 'error',
                'message' => $urlLength <= self::MAX_URL_LENGTH ?
                    'URL uzunluğu ideal seviyede.' :
                    'URL çok uzun. ' . self::MAX_URL_LENGTH . ' karakterden kısa olması önerilir.'
            ],
            'special_characters' => [
                'has_special' => $hasSpecialChars,
                'status' => !$hasSpecialChars ? 'success' : 'error',
                'message' => !$hasSpecialChars ?
                    'URL\'de özel karakter kullanılmamış.' :
                    'URL\'de özel karakterler kullanılmış. Kaldırılması önerilir.'
            ],
            'keywords' => [
                'has_keywords' => $hasKeywords,
                'status' => $hasKeywords ? 'success' : 'warning',
                'message' => $hasKeywords ?
                    'URL\'de anahtar kelimeler kullanılmış.' :
                    'URL\'de anahtar kelime kullanımı önerilir.'
            ],
            'seo_friendly' => [
                'is_friendly' => $isSeoFriendly,
                'status' => $isSeoFriendly ? 'success' : 'error',
                'message' => $isSeoFriendly ?
                    'URL yapısı SEO dostu.' :
                    'URL yapısı SEO dostu değil.'
            ],
            'problematic_urls' => $problematicUrls,
            'score' => $score,
            'recommendations' => $recommendations
        ];
    }

    private function checkSpecialCharacters(string $url): bool
    {
        $url = urldecode($url);
        foreach (self::SPECIAL_CHARS as $char) {
            if (str_contains($url, $char)) {
                return true;
            }
        }
        return false;
    }

    private function checkKeywords(array $segments): bool
    {
        // En az 3 karakterli segment varsa anahtar kelime olarak kabul et
        foreach ($segments as $segment) {
            if (strlen($segment) >= 3 && !is_numeric($segment)) {
                return true;
            }
        }
        return false;
    }

    private function checkSeoFriendly(string $url): bool
    {
        // SEO dostu URL kriterleri
        $url = strtolower($url);
        return
            !str_contains($url, ' ') && // Boşluk içermiyor
            !preg_match('/[A-Z]/', $url) && // Büyük harf içermiyor
            !str_contains($url, '--') && // Çift tire içermiyor
            preg_match('/^[a-z0-9\-\/\.]+$/', $url); // Sadece küçük harf, rakam, tire ve slash içeriyor
    }

    private function calculateScore(array $checks): int
    {
        $score = 100;

        if (!$checks['length']) $score -= 25;
        if (!$checks['special_chars']) $score -= 25;
        if (!$checks['keywords']) $score -= 25;
        if (!$checks['seo_friendly']) $score -= 25;

        return max(0, $score);
    }

    private function generateRecommendations(
        int $urlLength,
        bool $hasSpecialChars,
        bool $hasKeywords,
        bool $isSeoFriendly
    ): array {
        $recommendations = [];

        if ($urlLength > self::MAX_URL_LENGTH) {
            $recommendations[] = 'URL uzunluğunu ' . self::MAX_URL_LENGTH . ' karakterin altına düşürün';
        }

        if ($hasSpecialChars) {
            $recommendations[] = 'URL\'deki özel karakterleri kaldırın';
            $recommendations[] = 'Türkçe karakterler yerine İngilizce karşılıklarını kullanın';
        }

        if (!$hasKeywords) {
            $recommendations[] = 'URL\'de anahtar kelimeler kullanın';
        }

        if (!$isSeoFriendly) {
            $recommendations[] = 'URL\'de küçük harf kullanın';
            $recommendations[] = 'Kelimeler arasında tire (-) kullanın';
            $recommendations[] = 'URL\'de sadece harf, rakam ve tire kullanın';
        }

        // JavaScript pseudo-protokol için öneri
        $recommendations[] = 'JavaScript pseudo-protokol (javascript:void(0);) kullanımından kaçının';
        $recommendations[] = 'JavaScript olayları için modern event listener yaklaşımını kullanın';
        $recommendations[] = 'Gerçek URL\'ler veya uygun durumlarda "#" kullanın';

        return $recommendations;
    }

    private function findProblematicUrls(string $baseUrl): array
    {
        $problematicUrls = [];

        try {
            $response = $this->httpClient->fetchUrl($baseUrl);
            $html = $response['html'];

            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            @$dom->loadHTML($html, LIBXML_NOERROR);

            $links = $dom->getElementsByTagName('a');
            /** @var DOMElement $link */
            foreach ($links as $link) {
                $href = $link->getAttribute('href');

                // JavaScript pseudo-protokol kontrolü
                if (str_starts_with(strtolower($href), 'javascript:')) {
                    $problematicUrls[] = [
                        'url' => $href,
                        'issues' => ['JavaScript pseudo-protokol kullanımı SEO açısından uygun değil. Bunun yerine gerçek URL veya "#" kullanın ve JavaScript olayları için onclick yerine addEventListener kullanın.']
                    ];
                    continue;
                }

                // Boş veya geçersiz href kontrolü
                if (empty($href) || $href === '#' || str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:')) {
                    continue;
                }

                // URL'yi mutlak URL'ye çevir
                if (!filter_var($href, FILTER_VALIDATE_URL)) {
                    $href = $this->makeAbsoluteUrl($baseUrl, $href);
                }

                // URL'yi parse et
                $parsedUrl = parse_url($href);
                if (!isset($parsedUrl['path'])) {
                    continue;
                }

                // Sadece path kısmını kontrol et
                $path = trim($parsedUrl['path'], '/');
                if (empty($path)) {
                    continue; // Ana sayfa URL'si için kontrol yapma
                }

                $issues = [];

                // URL uzunluğu kontrolü - tüm URL için
                if (strlen($href) > self::MAX_URL_LENGTH) {
                    $issues[] = 'URL çok uzun';
                }

                // Özel karakter ve SEO dostu kontrolü sadece path için
                if ($this->hasSpecialCharactersInPath($path)) {
                    $issues[] = 'Path\'de özel karakter içeriyor';
                }

                if (!$this->isSeoFriendlyPath($path)) {
                    $issues[] = 'Path SEO dostu değil';
                }

                if (!empty($issues)) {
                    $problematicUrls[] = [
                        'url' => $href,
                        'issues' => $issues
                    ];
                }
            }
            libxml_clear_errors();
        } catch (\Exception $e) {
            return [];
        }

        return $problematicUrls;
    }

    private function hasSpecialCharactersInPath(string $path): bool
    {
        // Türkçe karakterler ve bazı özel karakterler için kontrol
        $specialChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', ' ', '!', '*', '\'', '(', ')', ';', ':', '@', '=', '+', '$', ',', '[', ']'];

        $path = urldecode($path);
        foreach ($specialChars as $char) {
            if (str_contains($path, $char)) {
                return true;
            }
        }
        return false;
    }

    private function isSeoFriendlyPath(string $path): bool
    {
        // SEO dostu path kriterleri
        return
            !str_contains($path, ' ') && // Boşluk içermiyor
            !preg_match('/[A-Z]/', $path) && // Büyük harf içermiyor
            !str_contains($path, '--') && // Çift tire içermiyor
            preg_match('/^[a-z0-9\-\/]+$/', $path); // Sadece küçük harf, rakam, tire ve slash içeriyor
    }

    private function makeAbsoluteUrl(string $baseUrl, string $relativeUrl): string
    {
        $parsedBase = parse_url($baseUrl);
        $base = $parsedBase['scheme'] . '://' . $parsedBase['host'];

        if (str_starts_with($relativeUrl, '/')) {
            return $base . $relativeUrl;
        }

        return $base . '/' . $relativeUrl;
    }
}
