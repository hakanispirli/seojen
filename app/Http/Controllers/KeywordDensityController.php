<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\HttpClientKeywordService;

class KeywordDensityController extends Controller
{
    private $stopWords;
    private $httpClient;

    public function __construct(HttpClientKeywordService $httpClient)
    {
        $this->httpClient = $httpClient;
        // Türkçe stop words listesi
        $this->stopWords = [
            'acaba', 'ama', 'aslında', 'az', 'bazı', 'belki', 'biri', 'birkaç', 'birşey', 'biz',
            'bu', 'çok', 'çünkü', 'da', 'daha', 'de', 'defa', 'diye', 'eğer', 'en', 'gibi', 'hem',
            'hep', 'hepsi', 'her', 'hiç', 'için', 'ile', 'ise', 'kez', 'ki', 'kim', 'mı', 'mu',
            'mü', 'nasıl', 'ne', 'neden', 'nerde', 'nerede', 'nereye', 'niçin', 'niye', 'o', 'sanki',
            'şey', 'siz', 'şu', 'tüm', 've', 'veya', 'ya', 'yani', 'bir', 'var', 'yok', 'olan',
            'olur', 'sonra', 'ben', 'sen', 'onlar', 'bana', 'sana', 'ona', 'biz', 'size', 'bize',
            'onlara', 'onu', 'bunu', 'şunu', 'böyle', 'şöyle', 'öyle', 'dolayı', 'tarafından'
        ];
    }

    public function index()
    {
        return view('tools.keyword-density.index');
    }

    public function analyze(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_type' => 'required|in:text,url',
            'content' => 'required_if:content_type,text',
            'url' => 'required_if:content_type,url|url',
            'exclude_stop_words' => 'boolean',
            'use_stemming' => 'boolean',
            'min_word_length' => 'integer|min:1|max:10'
        ], [
            'content.required_if' => 'Lütfen analiz edilecek metni girin.',
            'url.required_if' => 'Lütfen geçerli bir URL girin.',
            'url.url' => 'Lütfen geçerli bir URL formatı girin.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // İçeriği al
            if ($request->content_type === 'url') {
                $content = $this->getContentFromUrl($request->url);
            } else {
                $content = $request->content;
            }

            // Boş içerik kontrolü
            if (empty(trim($content))) {
                throw new \Exception('İçerik boş olamaz.');
            }

            $analysis = $this->analyzeContent(
                $content,
                $request->boolean('exclude_stop_words'),
                $request->boolean('use_stemming'),
                $request->input('min_word_length', 3)
            );

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Analiz sırasında bir hata oluştu: ' . $e->getMessage()]
            ], 500);
        }
    }

    private function getContentFromUrl(string $url): string
    {
        try {
            return $this->httpClient->getContent($url);
        } catch (\Exception $e) {
            throw new \Exception('URL içeriği alınamadı: ' . $e->getMessage());
        }
    }

    private function analyzeContent(string $content, bool $excludeStopWords, bool $useStemming, int $minWordLength): array
    {
        // Metni küçük harfe çevir ve gereksiz boşlukları temizle
        $content = mb_strtolower(trim($content));

        // Noktalama işaretlerini kaldır
        $content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content);

        // Kelimeleri diziye ayır
        $words = preg_split('/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Stop words'leri kaldır
        if ($excludeStopWords) {
            $words = array_filter($words, function($word) {
                return !in_array($word, $this->stopWords);
            });
        }

        // Stemming uygula
        if ($useStemming) {
            $words = array_map(function($word) {
                return Str::singular($word); // Basit stemming için
            }, $words);
        }

        // Minimum kelime uzunluğunu kontrol et
        $words = array_filter($words, function($word) use ($minWordLength) {
            return mb_strlen($word) >= $minWordLength;
        });

        // Kelime sayılarını hesapla
        $wordCounts = array_count_values($words);
        $totalWords = count($words);

        // Sonuçları yüzdelik olarak hesapla ve sırala
        $results = [];
        foreach ($wordCounts as $word => $count) {
            $results[] = [
                'word' => $word,
                'count' => $count,
                'density' => round(($count / $totalWords) * 100, 2)
            ];
        }

        // Yoğunluğa göre sırala
        usort($results, function($a, $b) {
            return $b['density'] <=> $a['density'];
        });

        return [
            'total_words' => $totalWords,
            'unique_words' => count($wordCounts),
            'keywords' => array_slice($results, 0, 50), // İlk 50 kelimeyi döndür
            'stats' => [
                'avg_density' => round(array_sum(array_column($results, 'density')) / count($results), 2),
                'max_density' => $results[0]['density'] ?? 0,
                'min_density' => end($results)['density'] ?? 0
            ]
        ];
    }
}
