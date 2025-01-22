<?php

namespace App\Services\SeoAnalyzer;


use DOMXPath;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class TechnicalSeoAnalyzerService
{
    private $httpClient;
    private $baseUrl;

    public function __construct(HttpClientService $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function analyze(string $url): array
    {
        $this->baseUrl = $url;
        $response = $this->httpClient->fetchUrl($url);
        $html = $response['html'];

        $robotsTxtStatus = $this->checkRobotsTxt();
        $sitemapStatus = $this->checkSitemap();
        $canonicalStatus = $this->checkCanonical($html);
        $schemaStatus = $this->checkSchema($html);

        $score = $this->calculateScore([
            'robots_txt' => $robotsTxtStatus['exists'],
            'sitemap' => $sitemapStatus['exists'],
            'canonical' => $canonicalStatus['exists'],
            'schema' => $schemaStatus['exists']
        ]);

        return [
            'robots_txt' => $robotsTxtStatus,
            'sitemap' => $sitemapStatus,
            'canonical' => $canonicalStatus,
            'schema' => $schemaStatus,
            'score' => $score,
            'recommendations' => $this->generateRecommendations($robotsTxtStatus, $sitemapStatus, $canonicalStatus, $schemaStatus)
        ];
    }

    private function normalizeBaseUrl(string $url): string
    {
        // URL'yi parse et
        $parsedUrl = parse_url($url);

        // Protokol kontrolü
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'https';

        // Host kontrolü
        if (!isset($parsedUrl['host'])) {
            if (isset($parsedUrl['path'])) {
                $path = $parsedUrl['path'];
                // İlk slash'i kaldır
                $path = ltrim($path, '/');
                // İlk bölümü al (ilk slash'e kadar)
                $host = explode('/', $path)[0];
            } else {
                throw new \Exception('Geçersiz URL formatı');
            }
        } else {
            $host = $parsedUrl['host'];
        }

        // www. kontrolü
        if (!str_starts_with($host, 'www.')) {
            // Önce www'siz dene, sonra www'li
            $baseUrls = [
                $scheme . '://' . $host,
                $scheme . '://www.' . $host
            ];
        } else {
            // Önce www'li dene, sonra www'siz
            $baseUrls = [
                $scheme . '://' . $host,
                $scheme . '://' . str_replace('www.', '', $host)
            ];
        }

        // Her iki protokolü de dene
        $allUrls = [];
        foreach ($baseUrls as $baseUrl) {
            $allUrls[] = $baseUrl;
            $allUrls[] = str_replace('https://', 'http://', $baseUrl);
        }

        // URL'leri test et
        foreach ($allUrls as $testUrl) {
            try {
                $response = $this->httpClient->fetchUrl($testUrl);
                if ($response['status'] === 200) {
                    return $testUrl;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Hiçbiri çalışmazsa ilk URL'yi döndür
        return $allUrls[0];
    }

    private function checkRobotsTxt(): array
    {
        try {
            $baseUrl = $this->normalizeBaseUrl($this->baseUrl);
            $possiblePaths = [
                $baseUrl . '/robots.txt',
                rtrim($baseUrl, '/') . '/robots.txt',
                str_replace('www.', '', $baseUrl) . '/robots.txt',
                str_replace('https://', 'http://', $baseUrl) . '/robots.txt',
                str_replace('http://', 'https://', $baseUrl) . '/robots.txt'
            ];

            foreach ($possiblePaths as $robotsUrl) {
                try {
                    $response = $this->httpClient->fetchUrl($robotsUrl);

                    // Status kontrolü ekle
                    if (!isset($response['status'])) {
                        Log::warning("Missing status in response for URL: $robotsUrl");
                        continue;
                    }

                    // Response içeriğini kontrol et
                    if ($response['status'] === 200) {
                        $content = $response['html'] ?? '';

                        // Boş içerik kontrolü
                        if (empty(trim($content))) {
                            Log::debug("Empty content for URL: $robotsUrl");
                            continue;
                        }

                        // robots.txt formatı kontrolü
                        if (!preg_match('/(User-agent|Disallow|Allow|Sitemap):/i', $content)) {
                            continue;
                        }

                        $issues = [];

                        // Temel robots.txt kontrolleri
                        if (!preg_match('/User-agent:/i', $content)) {
                            $issues[] = 'User-agent direktifi eksik';
                        }

                        // Sitemap kontrolü (opsiyonel)
                        if (!preg_match('/Sitemap:/i', $content)) {
                            $issues[] = 'Sitemap direktifi eksik (önerilen)';
                        }

                        // Tüm site engellemesi kontrolü
                        if (preg_match('/Disallow:\s*\/\s*$/im', $content)) {
                            $issues[] = 'Tüm site indekslemeden engellenmiş';
                        }

                        return [
                            'exists' => true,
                            'status' => empty($issues) ? 'success' : 'warning',
                            'issues' => $issues,
                            'content' => $content,
                            'url' => $robotsUrl
                        ];
                    }
                } catch (\Exception $e) {
                    Log::debug("robots.txt check failed for URL: $robotsUrl - " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("robots.txt check failed: " . $e->getMessage());
        }

        return [
            'exists' => false,
            'status' => 'error',
            'issues' => ['robots.txt dosyası bulunamadı'],
            'content' => null,
            'url' => null
        ];
    }

    private function checkSitemap(): array
    {
        try {
            // Önce robots.txt'den sitemap URL'sini bulmaya çalış
            $sitemapUrl = $this->findSitemapUrlFromRobots();

            if (!$sitemapUrl) {
                // Olası sitemap lokasyonlarını kontrol et
                $baseUrl = $this->normalizeBaseUrl($this->baseUrl);
                $possiblePaths = [
                    $baseUrl . '/sitemap.xml',
                    $baseUrl . '/sitemap_index.xml',
                    $baseUrl . '/sitemap/',
                    $baseUrl . '/sitemaps.xml',
                    $baseUrl . '/sitemap/sitemap.xml',
                    $baseUrl . '/sitemap/index.xml',
                    str_replace('www.', '', $baseUrl) . '/sitemap.xml',
                    str_replace('https://', 'http://', $baseUrl) . '/sitemap.xml',
                    str_replace('http://', 'https://', $baseUrl) . '/sitemap.xml'
                ];

                foreach ($possiblePaths as $path) {
                    try {
                        $response = $this->httpClient->fetchUrl($path);
                        if ($response['status'] === 200) {
                            $content = $response['html'];

                            // Boş içerik kontrolü
                            if (empty(trim($content))) {
                                continue;
                            }

                            // XML veya sitemap içeriği kontrolü
                            if (preg_match('/<\?xml|<urlset|<sitemapindex/i', $content)) {
                                $sitemapUrl = $path;
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::debug("Sitemap check failed for URL: $path - " . $e->getMessage());
                        continue;
                    }
                }
            }

            if ($sitemapUrl) {
                try {
                    $response = $this->httpClient->fetchUrl($sitemapUrl);
                    if ($response['status'] === 200) {
                        $content = $response['html'];
                        $issues = [];

                        // XML yapısını kontrol et
                        if (!preg_match('/<\?xml/i', $content)) {
                            $issues[] = 'Geçerli XML formatında değil';
                        }

                        // Temel sitemap etiketlerini kontrol et
                        if (!preg_match('/<urlset|<sitemapindex/i', $content)) {
                            $issues[] = 'urlset veya sitemapindex etiketi eksik';
                        }
                        if (!preg_match('/<url>|<sitemap>/i', $content)) {
                            $issues[] = 'URL veya sitemap girişleri eksik';
                        }

                        return [
                            'exists' => true,
                            'status' => empty($issues) ? 'success' : 'warning',
                            'issues' => $issues,
                            'url' => $sitemapUrl
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Sitemap content check failed: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Sitemap check failed: " . $e->getMessage());
        }

        return [
            'exists' => false,
            'status' => 'error',
            'issues' => ['Sitemap dosyası bulunamadı'],
            'url' => null
        ];
    }

    private function checkCanonical(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        $canonicalTags = $xpath->query("//link[@rel='canonical']");
        $issues = [];

        if ($canonicalTags->length === 0) {
            return [
                'exists' => false,
                'status' => 'error',
                'issues' => ['Canonical URL tanımlanmamış'],
                'url' => null
            ];
        }

        if ($canonicalTags->length > 1) {
            $issues[] = 'Birden fazla canonical URL tanımlanmış';
        }

        $canonicalUrl = $canonicalTags->item(0)->getAttribute('href');
        if (empty($canonicalUrl)) {
            $issues[] = 'Canonical URL boş';
        }

        return [
            'exists' => true,
            'status' => empty($issues) ? 'success' : 'warning',
            'issues' => $issues,
            'url' => $canonicalUrl
        ];
    }

    private function checkSchema(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        // application/ld+json tipindeki script etiketlerini ara
        $schemas = $xpath->query("//script[@type='application/ld+json']");
        $issues = [];
        $foundSchemas = [];

        if ($schemas->length === 0) {
            return [
                'exists' => false,
                'status' => 'error',
                'issues' => ['Schema markup bulunamadı'],
                'types' => []
            ];
        }

        foreach ($schemas as $schema) {
            $content = $schema->nodeValue;
            try {
                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $issues[] = 'Geçersiz JSON formatı';
                    continue;
                }

                if (isset($data['@type'])) {
                    $foundSchemas[] = $data['@type'];
                }
            } catch (\Exception $e) {
                $issues[] = 'Schema parse edilemedi';
            }
        }

        return [
            'exists' => true,
            'status' => empty($issues) ? 'success' : 'warning',
            'issues' => $issues,
            'types' => $foundSchemas
        ];
    }

    private function findSitemapUrlFromRobots(): ?string
    {
        try {
            $baseUrl = $this->normalizeBaseUrl($this->baseUrl);
            $robotsUrl = $baseUrl . '/robots.txt';
            $response = $this->httpClient->fetchUrl($robotsUrl);

            if ($response['status'] === 200) {
                $content = $response['html'];

                // Tüm Sitemap: satırlarını bul
                if (preg_match_all('/Sitemap:\s*(.+)$/im', $content, $matches)) {
                    foreach ($matches[1] as $sitemapUrl) {
                        $sitemapUrl = trim($sitemapUrl);
                        try {
                            $response = $this->httpClient->fetchUrl($sitemapUrl);
                            if ($response['status'] === 200 && !empty(trim($response['html']))) {
                                return $sitemapUrl;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug("Finding sitemap from robots.txt failed: " . $e->getMessage());
        }

        return null;
    }

    private function calculateScore(array $checks): int
    {
        $score = 100;
        $weight = 25; // Her kontrol 25 puan değerinde

        foreach ($checks as $exists) {
            if (!$exists) {
                $score -= $weight;
            }
        }

        return max(0, $score);
    }

    private function generateRecommendations(
        array $robotsTxtStatus,
        array $sitemapStatus,
        array $canonicalStatus,
        array $schemaStatus
    ): array {
        $recommendations = [];

        // robots.txt önerileri
        if (!$robotsTxtStatus['exists']) {
            $recommendations[] = 'robots.txt dosyası oluşturun';
        } elseif (!empty($robotsTxtStatus['issues'])) {
            foreach ($robotsTxtStatus['issues'] as $issue) {
                $recommendations[] = "robots.txt: $issue";
            }
        }

        // sitemap önerileri
        if (!$sitemapStatus['exists']) {
            $recommendations[] = 'XML sitemap oluşturun';
        } elseif (!empty($sitemapStatus['issues'])) {
            foreach ($sitemapStatus['issues'] as $issue) {
                $recommendations[] = "Sitemap: $issue";
            }
        }

        // canonical önerileri
        if (!$canonicalStatus['exists']) {
            $recommendations[] = 'Canonical URL tanımlayın';
        } elseif (!empty($canonicalStatus['issues'])) {
            foreach ($canonicalStatus['issues'] as $issue) {
                $recommendations[] = "Canonical: $issue";
            }
        }

        // schema önerileri
        if (!$schemaStatus['exists']) {
            $recommendations[] = 'Schema markup ekleyin';
        } elseif (!empty($schemaStatus['issues'])) {
            foreach ($schemaStatus['issues'] as $issue) {
                $recommendations[] = "Schema: $issue";
            }
        }

        return $recommendations;
    }
}
