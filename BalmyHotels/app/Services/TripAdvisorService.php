<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripAdvisorService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.content.tripadvisor.com/api/v1';

    // Otellerin TripAdvisor location ID'leri
    public const HOTELS = [
        'Balmy Foresta'            => 33069117,
        'Balmy Beach Resort Kemer' => 25423773,
    ];

    public function __construct()
    {
        $this->apiKey = config('services.tripadvisor.key', '');
    }

    /**
     * Tüm otellerin istatistiklerini döndürür (cache'li).
     */
    public function getAllStats(): array
    {
        return Cache::remember('tripadvisor_stats', now()->addHours(6), function () {
            $results = [];

            foreach (self::HOTELS as $name => $locationId) {
                $data = $this->getLocationDetails($locationId);

                if ($data) {
                    $results[] = [
                        'name'        => $name,
                        'location_id' => $locationId,
                        'rating'      => $data['rating']      ?? null,
                        'num_reviews' => $data['num_reviews']  ?? null,
                        'ranking'     => $data['ranking_data']['ranking_string'] ?? null,
                        'url'         => $data['web_url']      ?? '#',
                        'photo'       => $data['photo']['images']['large']['url'] ?? null,
                        'rating_image_url' => $data['rating_image_url'] ?? null,
                    ];
                }
            }

            return $results;
        });
    }

    /**
     * TripAdvisor Content API'sinden lokasyon detaylarını çeker.
     */
    protected function getLocationDetails(int $locationId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withoutVerifying()   // Windows geliştirme ortamında SSL CA bundle eksikliği için
                ->get("{$this->baseUrl}/location/{$locationId}/details", [
                'key'      => $this->apiKey,
                'language' => 'tr',
                'currency' => 'TRY',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("TripAdvisor API hatası [{$locationId}]: " . $response->status());
        } catch (\Exception $e) {
            Log::error("TripAdvisor bağlantı hatası [{$locationId}]: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Cache'i temizle (manuel yenileme için).
     */
    public function clearCache(): void
    {
        Cache::forget('tripadvisor_stats');
    }
}
