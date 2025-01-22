<?php

namespace App\Http\Controllers;

use App\Services\SeoAnalyzerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoAnalyzerController extends Controller
{
    private $seoAnalyzer;

    public function __construct(SeoAnalyzerService $seoAnalyzer)
    {
        $this->seoAnalyzer = $seoAnalyzer;
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|string'
        ]);

        try {
            $url = $this->normalizeUrl($request->url);

            if (!$this->isValidUrl($url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz URL formatı'
                ], 422);
            }

            $results = $this->seoAnalyzer->analyze($url);

            if (!empty($results['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $results['message']
                ], 422);
            }

            // Sonuçları session'a kaydet
            session(['analysis_results' => $results]);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Analiz sırasında bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    private function normalizeUrl(string $url): string
    {
        // URL'yi temizle
        $url = trim($url);

        // Protokol ekle
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://' . $url;
        }

        // Sondaki slash'i kaldır
        $url = rtrim($url, '/');

        // URL'deki boşlukları encode et
        $url = str_replace(' ', '%20', $url);

        // Türkçe karakterleri encode et
        $turkishChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
        $englishChars = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
        $url = str_replace($turkishChars, $englishChars, $url);

        return $url;
    }

    private function isValidUrl(string $url): bool
    {
        // Parse URL
        $parsedUrl = parse_url($url);

        // Temel kontroller
        if (empty($parsedUrl['scheme']) || empty($parsedUrl['host'])) {
            return false;
        }

        // Geçerli protokol kontrolü
        if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return false;
        }

        // Host formatı kontrolü
        if (!preg_match('/^([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/', $parsedUrl['host'])) {
            return false;
        }

        // DNS kontrolü
        if (!checkdnsrr($parsedUrl['host'], 'A')) {
            return false;
        }

        return true;
    }
}
