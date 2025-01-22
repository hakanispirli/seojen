<?php

namespace App\Services\SeoAnalyzer;

class PerformanceAnalyzerService
{
    private const IDEAL_LOAD_TIME = 2000; // 2 saniye
    private const IDEAL_PAGE_SIZE = 2097152; // 2 MB

    public function analyze(string $html, array $stats): array
    {
        $loadTime = $stats['load_time'] ?? 0;
        $pageSize = $stats['size'] ?? 0;
        $isSSL = $stats['is_ssl'] ?? false;

        $loadTimeScore = $this->calculateLoadTimeScore($loadTime);
        $pageSizeScore = $this->calculatePageSizeScore($pageSize);
        $sslScore = $isSSL ? 100 : 0;

        $issues = [];
        $recommendations = [];

        // Yükleme süresi analizi
        if ($loadTime > self::IDEAL_LOAD_TIME) {
            $issues[] = 'Sayfa yükleme süresi çok yüksek';
            $recommendations[] = 'Sayfa yükleme süresini ' . (self::IDEAL_LOAD_TIME / 1000) . ' saniyenin altına düşürün';
        }

        // Sayfa boyutu analizi
        if ($pageSize > self::IDEAL_PAGE_SIZE) {
            $issues[] = 'Sayfa boyutu çok büyük';
            $recommendations[] = 'Sayfa boyutunu ' . $this->formatBytes(self::IDEAL_PAGE_SIZE) . ' altına düşürün';
        }

        // SSL analizi
        if (!$isSSL) {
            $issues[] = 'SSL sertifikası kullanılmıyor';
            $recommendations[] = 'HTTPS protokolüne geçiş yapın';
        }

        return [
            'load_time' => [
                'value' => $loadTime,
                'formatted' => number_format($loadTime, 2) . ' ms',
                'score' => $loadTimeScore
            ],
            'page_size' => [
                'value' => $pageSize,
                'formatted' => $this->formatBytes($pageSize),
                'score' => $pageSizeScore
            ],
            'ssl' => [
                'enabled' => $isSSL,
                'score' => $sslScore
            ],
            'issues' => $issues,
            'recommendations' => $recommendations,
            'optimization_score' => (int)(($loadTimeScore + $pageSizeScore + $sslScore) / 3)
        ];
    }

    private function calculateLoadTimeScore(float $loadTime): int
    {
        if ($loadTime <= self::IDEAL_LOAD_TIME) {
            return 100;
        }

        $score = 100 - (($loadTime - self::IDEAL_LOAD_TIME) / 100);
        return max(0, min(100, (int)$score));
    }

    private function calculatePageSizeScore(int $pageSize): int
    {
        if ($pageSize <= self::IDEAL_PAGE_SIZE) {
            return 100;
        }

        $score = 100 - (($pageSize - self::IDEAL_PAGE_SIZE) / (1024 * 1024) * 10);
        return max(0, min(100, (int)$score));
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
