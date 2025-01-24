<?php

namespace App\Services;

use App\Services\SeoAnalyzer\HttpClientService;
use App\Services\SeoAnalyzer\MetaAnalyzerService;
use App\Services\SeoAnalyzer\HeadingAnalyzerService;
use App\Services\SeoAnalyzer\ImageAnalyzerService;
use App\Services\SeoAnalyzer\PerformanceAnalyzerService;
use App\Services\SeoAnalyzer\UrlAnalyzerService;
use App\Services\SeoAnalyzer\TechnicalSeoAnalyzerService;
use App\Services\SeoAnalyzer\LinkAnalyzerService;

class SeoAnalyzerService
{
    private $httpClient;
    private $metaAnalyzer;
    private $headingAnalyzer;
    private $imageAnalyzer;
    private $performanceAnalyzer;
    private $urlAnalyzer;
    private $technicalAnalyzer;
    private $linkAnalyzer;

    public function __construct(
        HttpClientService $httpClient,
        MetaAnalyzerService $metaAnalyzer,
        HeadingAnalyzerService $headingAnalyzer,
        ImageAnalyzerService $imageAnalyzer,
        PerformanceAnalyzerService $performanceAnalyzer,
        UrlAnalyzerService $urlAnalyzer,
        TechnicalSeoAnalyzerService $technicalAnalyzer,
        LinkAnalyzerService $linkAnalyzer
    ) {
        $this->httpClient = $httpClient;
        $this->metaAnalyzer = $metaAnalyzer;
        $this->headingAnalyzer = $headingAnalyzer;
        $this->imageAnalyzer = $imageAnalyzer;
        $this->performanceAnalyzer = $performanceAnalyzer;
        $this->urlAnalyzer = $urlAnalyzer;
        $this->technicalAnalyzer = $technicalAnalyzer;
        $this->linkAnalyzer = $linkAnalyzer;
    }

    public function analyze(string $url): array
    {
        try {
            $response = $this->httpClient->fetchUrl($url);
            $html = $response['html'];

            $metaAnalysis = $this->metaAnalyzer->analyze($html);
            $headingAnalysis = $this->headingAnalyzer->analyze($html);
            $imageAnalysis = $this->imageAnalyzer->analyze($html);
            $performanceAnalysis = $this->performanceAnalyzer->analyze($html, $response['stats']);
            $urlAnalysis = $this->urlAnalyzer->analyze($url);
            $technicalAnalysis = $this->technicalAnalyzer->analyze($url);
            $linkAnalysis = $this->linkAnalyzer->analyze($url, $html);

            return [
                'url' => $url,
                'meta_analysis' => $metaAnalysis,
                'heading_analysis' => $headingAnalysis,
                'image_analysis' => $imageAnalysis,
                'performance_analysis' => $performanceAnalysis,
                'url_analysis' => $urlAnalysis,
                'technical_analysis' => $technicalAnalysis,
                'link_analysis' => $linkAnalysis,
                'overall_score' => $this->calculateOverallScore([
                    $metaAnalysis['score'],
                    $headingAnalysis['score'],
                    $imageAnalysis['score'],
                    $performanceAnalysis['optimization_score'],
                    $urlAnalysis['score'],
                    $technicalAnalysis['score'],
                    $linkAnalysis['score']
                ])
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function calculateOverallScore(array $scores): int
    {
        return (int) array_sum($scores) / count($scores);
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

