<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class DomainAgeService
{
    /**
     * WHOIS sunucuları listesi
     */
    private $whoisServers = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'info' => 'whois.afilias.net',
        'biz' => 'whois.biz',
        'io' => 'whois.nic.io',
        'tr' => 'whois.nic.tr',
        'com.tr' => 'whois.nic.tr',
    ];

    /**
     * Domain yaşını ve bilgilerini kontrol eder
     */
    public function checkDomain(string $domain): array
    {
        try {
            $domainParts = explode('.', $domain);
            $tld = implode('.', array_slice($domainParts, 1));

            if (!isset($this->whoisServers[$tld])) {
                throw new Exception('Bu domain uzantısı desteklenmiyor.');
            }

            $whoisServer = $this->whoisServers[$tld];
            $whoisData = $this->queryWhoisServer($whoisServer, $domain);

            // WHOIS verilerinden tarihleri çıkart
            $creationDate = $this->extractDate($whoisData, [
                'Creation Date:',
                'Created on:',
                'Created Date:',
                'Kayıt Tarihi',
                'Domain Name Commencement Date:',
                'Created:'
            ]);

            $expiryDate = $this->extractDate($whoisData, [
                'Registry Expiry Date:',
                'Expiration Date:',
                'Expires on:',
                'Bitiş Tarihi',
                'Expires:'
            ]);

            $updateDate = $this->extractDate($whoisData, [
                'Updated Date:',
                'Last Modified:',
                'Last Updated:',
                'Son Güncelleme',
                'Modified:'
            ]);

            if (!$creationDate) {
                throw new Exception('Domain kayıt tarihi bulunamadı.');
            }

            $creationCarbon = Carbon::parse($creationDate);
            $now = Carbon::now();

            // Tarihleri karşılaştır ve eskiyse hesapla
            if ($creationCarbon->isPast()) {
                // Toplam gün farkını hesapla
                $totalDays = $creationCarbon->diffInDays($now);

                // Yıl, ay ve gün hesaplama
                $years = floor($totalDays / 365);
                $remainingDays = $totalDays % 365;
                $months = floor($remainingDays / 30);
                $days = $remainingDays % 30;
            } else {
                // Gelecek tarihse sıfırla
                $totalDays = $years = $months = $days = 0;
            }

            return [
                'success' => true,
                'data' => [
                    'domain' => $domain,
                    'age' => [
                        'years' => $years,
                        'months' => $months,
                        'days' => $days,
                        'total_days' => $totalDays,
                        'formatted' => $this->formatAge($years, $months, $days)
                    ],
                    'dates' => [
                        'created' => $creationCarbon->format('d.m.Y'),
                        'updated' => $updateDate ? Carbon::parse($updateDate)->format('d.m.Y') : null,
                        'expires' => $expiryDate ? Carbon::parse($expiryDate)->format('d.m.Y') : null,
                    ]
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * WHOIS sunucusuna sorgu gönderir
     */
    private function queryWhoisServer(string $whoisServer, string $domain): string
    {
        $socket = fsockopen($whoisServer, 43, $errno, $errstr, 10);
        if (!$socket) {
            throw new Exception("Bağlantı hatası: $errstr");
        }

        fputs($socket, $domain . "\r\n");
        $response = '';

        while (!feof($socket)) {
            $response .= fgets($socket, 128);
        }

        fclose($socket);
        return $response;
    }

    /**
     * WHOIS verisinden tarih bilgisini çıkartır
     */
    private function extractDate(string $whoisData, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match('/' . preg_quote($pattern) . '\s*([^\n\r]+)/', $whoisData, $matches)) {
                $date = trim($matches[1]);
                // Farklı tarih formatlarını temizle
                $date = preg_replace('/\s+\([^)]+\)/', '', $date);
                $date = str_replace(['UTC', 'GMT', 'T', 'Z'], ' ', $date);
                $date = trim($date);

                // Tarih dönüşümünü dene
                if ($timestamp = strtotime($date)) {
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }
        }
        return null;
    }

    /**
     * Yaş bilgisini formatlar
     */
    private function formatAge(int $years, int $months, int $days): string
    {
        if ($years == 0 && $months == 0 && $days == 0) {
            return "Yeni kayıt";
        }

        $parts = [];

        if ($years > 0) {
            $parts[] = "{$years} yıl";
        }
        if ($months > 0) {
            $parts[] = "{$months} ay";
        }
        if ($days > 0) {
            $parts[] = "{$days} gün";
        }

        return implode(' ', $parts);
    }
}
