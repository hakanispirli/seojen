<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpClientKeywordService
{
    /**
     * URL'den içerik alır ve temizler
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function getContent(string $url): string
    {
        try {
            // URL'yi doğrula ve düzenle
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "https://" . $url;
            }

            // Gerçekçi tarayıcı başlıkları
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Connection' => 'keep-alive'
            ];

            // HTTP isteği gönder
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(30)
                ->withOptions([
                    'curl' => [
                        CURLOPT_ENCODING => '', // Otomatik encoding algılama
                        CURLOPT_FOLLOWLOCATION => true, // Yönlendirmeleri takip et
                        CURLOPT_MAXREDIRS => 5, // Maksimum yönlendirme sayısı
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception('HTTP ' . $response->status());
            }

            $html = $response->body();

            // Meta refresh yönlendirmelerini kontrol et
            if (preg_match('/<meta[^>]*?url=(.*?)["\']/', $html, $matches)) {
                return $this->getContent(html_entity_decode($matches[1]));
            }

            // HTML temizleme
            $content = preg_replace([
                '/<script\b[^>]*>(.*?)<\/script>/is',  // JavaScript
                '/<style\b[^>]*>(.*?)<\/style>/is',    // CSS
                '/<header\b[^>]*>(.*?)<\/header>/is',  // Header
                '/<footer\b[^>]*>(.*?)<\/footer>/is',  // Footer
                '/<nav\b[^>]*>(.*?)<\/nav>/is',        // Navigation
                '/<aside\b[^>]*>(.*?)<\/aside>/is',    // Sidebar
                '/<!--(.*?)-->/s',                     // Yorumlar
            ], '', $html);

            // HTML etiketlerini temizle
            $content = strip_tags($content);

            // HTML entities'leri decode et
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Birden fazla boşlukları temizle
            $content = preg_replace('/\s+/', ' ', $content);

            // Başındaki ve sonundaki boşlukları temizle
            $content = trim($content);

            // Boş içerik kontrolü
            if (empty($content)) {
                throw new \Exception('İçerik bulunamadı');
            }

            return $content;

        } catch (\Exception $e) {
            throw new \Exception('URL içeriği alınamadı: ' . $e->getMessage());
        }
    }
}
