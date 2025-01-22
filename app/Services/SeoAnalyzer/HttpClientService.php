<?php

namespace App\Services\SeoAnalyzer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpClientService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false,
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; SEOAnalyzer/1.0; +http://localhost)'
            ]
        ]);
    }

    public function fetchUrl(string $url): array
    {
        try {
            $startTime = microtime(true);
            $response = $this->client->get($url);
            $endTime = microtime(true);

            $body = (string) $response->getBody();

            return [
                'status' => $response->getStatusCode(),
                'html' => $body,
                'headers' => $response->getHeaders(),
                'stats' => [
                    'load_time' => round(($endTime - $startTime) * 1000, 2), // milisaniye cinsinden
                    'size' => strlen($body),
                    'is_ssl' => parse_url($url, PHP_URL_SCHEME) === 'https'
                ]
            ];
        } catch (RequestException $e) {
            // Eğer yanıt varsa (404, 500 gibi hatalar)
            if ($e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
                return [
                    'status' => $e->getResponse()->getStatusCode(),
                    'html' => $body,
                    'headers' => $e->getResponse()->getHeaders(),
                    'stats' => [
                        'load_time' => 0,
                        'size' => strlen($body),
                        'is_ssl' => parse_url($url, PHP_URL_SCHEME) === 'https'
                    ]
                ];
            }

            // Yanıt yoksa (bağlantı hatası gibi)
            return [
                'status' => 0,
                'html' => '',
                'headers' => [],
                'stats' => [
                    'load_time' => 0,
                    'size' => 0,
                    'is_ssl' => parse_url($url, PHP_URL_SCHEME) === 'https'
                ],
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'html' => '',
                'headers' => [],
                'stats' => [
                    'load_time' => 0,
                    'size' => 0,
                    'is_ssl' => parse_url($url, PHP_URL_SCHEME) === 'https'
                ],
                'error' => $e->getMessage()
            ];
        }
    }
}
