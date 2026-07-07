<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServicePlannerGeocodingService
{
    private const OSM_URL = 'https://nominatim.openstreetmap.org/search';
    private const GOOGLE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function geocode(string $address): ?array
    {
        $normalizedAddress = $this->normalizeAddress($address);

        if ($normalizedAddress === '') {
            return null;
        }

        return Cache::remember(
            'service_planner_geocode_' . md5($normalizedAddress),
            now()->addDays(30),
            function () use ($normalizedAddress) {
                return $this->geocodeWithGoogle($normalizedAddress)
                    ?? $this->geocodeWithOpenStreetMap($normalizedAddress);
            }
        );
    }

    private function geocodeWithGoogle(string $address): ?array
    {
        $apiKey = (string) config('services.google_places.key', '');

        if ($apiKey === '') {
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->get(self::GOOGLE_URL, [
                    'address' => $address,
                    'key' => $apiKey,
                    'language' => 'tr',
                    'region' => 'tr',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->json();
            if (($body['status'] ?? null) !== 'OK') {
                return null;
            }

            $result = $body['results'][0] ?? null;
            if (! $result) {
                return null;
            }

            return [
                'latitude' => (float) data_get($result, 'geometry.location.lat'),
                'longitude' => (float) data_get($result, 'geometry.location.lng'),
                'provider' => 'google',
                'formatted_address' => (string) ($result['formatted_address'] ?? $address),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Service planner Google geocode failed: ' . $exception->getMessage());

            return null;
        }
    }

    private function geocodeWithOpenStreetMap(string $address): ?array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'BalmyHotels Service Planner/1.0',
                    'Accept-Language' => 'tr',
                ])
                ->get(self::OSM_URL, [
                    'q' => $address,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'countrycodes' => 'tr',
                    'addressdetails' => 0,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json()[0] ?? null;
            if (! $result) {
                return null;
            }

            return [
                'latitude' => (float) ($result['lat'] ?? 0),
                'longitude' => (float) ($result['lon'] ?? 0),
                'provider' => 'osm',
                'formatted_address' => (string) ($result['display_name'] ?? $address),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Service planner OSM geocode failed: ' . $exception->getMessage());

            return null;
        }
    }

    private function normalizeAddress(string $address): string
    {
        return trim(preg_replace('/\s+/', ' ', $address));
    }
}
