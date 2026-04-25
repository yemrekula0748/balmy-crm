<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/place/details/json';

    /**
     * Otellerin Google Place ID'leri ve Maps URL'leri
     */
    public const HOTELS = [
        'Balmy Foresta' => [
            'place_id' => 'ChIJj_o1awC_wxQRJtG4gli3Ucg',
            'maps_url' => 'https://www.google.com/maps/place/Balmy+Foresta/@36.7094471,30.5643169,17z',
        ],
        'Balmy Beach Resort Kemer' => [
            'place_id' => 'ChIJ8fevahm3wxQR9b45G8zlIdc',
            'maps_url' => 'https://www.google.com/maps/place/Balmy+Beach+Resort+Kemer/@36.7358957,30.560801,17z',
        ],
    ];

    public function __construct()
    {
        $this->apiKey = config('services.google_places.key', '');
    }

    /**
     * Tüm otellerin canlı verilerini döndürür (cache'li, 6 saat).
     */
    public function getAllStats(): array
    {
        return Cache::remember('google_places_stats', now()->addHours(6), function () {
            $results = [];

            foreach (self::HOTELS as $name => $hotel) {
                $data = $this->getPlaceDetails($hotel['place_id']);

                if ($data) {
                    $results[] = [
                        'name'               => $name,
                        'place_id'           => $hotel['place_id'],
                        'maps_url'           => $hotel['maps_url'],
                        'rating'             => $data['rating'] ?? null,
                        'user_ratings_total' => $data['user_ratings_total'] ?? null,
                        'url'                => $data['url'] ?? $hotel['maps_url'],
                    ];
                }
            }

            return $results;
        });
    }

    /**
     * Google Places API'den yer detaylarını çeker.
     */
    protected function getPlaceDetails(string $placeId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get($this->baseUrl, [
                    'place_id' => $placeId,
                    'fields'   => 'name,rating,user_ratings_total,url',
                    'key'      => $this->apiKey,
                    'language' => 'tr',
                ]);

            if ($response->successful()) {
                $body = $response->json();
                if (($body['status'] ?? '') === 'OK') {
                    return $body['result'] ?? null;
                }
                Log::warning("Google Places API hatası [{$placeId}]: " . ($body['status'] ?? 'unknown'));
            }
        } catch (\Exception $e) {
            Log::error("Google Places bağlantı hatası [{$placeId}]: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Cache'i temizle.
     */
    public function clearCache(): void
    {
        Cache::forget('google_places_stats');
    }
}
