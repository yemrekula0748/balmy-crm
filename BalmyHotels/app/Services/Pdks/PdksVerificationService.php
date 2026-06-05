<?php

namespace App\Services\Pdks;

use App\Models\PdksDevicePolicy;
use Illuminate\Http\Request;

class PdksVerificationService
{
    public function verify(Request $request, ?int $branchId): array
    {
        $policy = PdksDevicePolicy::where('is_active', true)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->orderByRaw('branch_id IS NULL')
            ->first();

        $issues = [];
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $accuracy = $request->input('accuracy');
        $ssid = trim((string) $request->input('wifi_ssid'));
        $bssid = strtolower(trim((string) $request->input('wifi_bssid')));
        $mockDetected = $request->boolean('is_mock_location');

        $requireGps = $policy?->require_gps ?? true;
        $blockMock = $policy?->block_mock_location ?? true;

        if ($requireGps && (!$lat || !$lng)) {
            $issues[] = 'GPS konumu zorunlu ancak konum bilgisi alınamadı.';
        }

        if ($lat && $lng && $policy?->allowed_latitude && $policy?->allowed_longitude) {
            $distance = $this->distanceMeters(
                (float) $lat,
                (float) $lng,
                (float) $policy->allowed_latitude,
                (float) $policy->allowed_longitude
            );

            if ($distance > (int) $policy->allowed_radius_meters) {
                $issues[] = 'Konum izin verilen otel çevresi dışında görünüyor (' . round($distance) . ' m).';
            }
        }

        if ($accuracy && $policy?->max_accuracy_meters && (int) $accuracy > (int) $policy->max_accuracy_meters) {
            $issues[] = 'GPS hassasiyeti yetersiz (' . (int) $accuracy . ' m).';
        }

        if ($policy?->require_wifi) {
            $allowedSsids = array_filter((array) $policy->allowed_wifi_ssids);
            $allowedBssids = array_map('strtolower', array_filter((array) $policy->allowed_wifi_bssids));

            if (!$ssid && !$bssid) {
                $issues[] = 'WiFi doğrulaması zorunlu ancak SSID/BSSID gelmedi.';
            } elseif ($allowedSsids && $ssid && !in_array($ssid, $allowedSsids, true)) {
                $issues[] = 'WiFi SSID izinli ağ listesinde değil.';
            } elseif ($allowedBssids && $bssid && !in_array($bssid, $allowedBssids, true)) {
                $issues[] = 'WiFi BSSID izinli erişim noktası listesinde değil.';
            }
        }

        if ($mockDetected && $blockMock) {
            $issues[] = 'Sahte konum/mock GPS tespit edildi.';
        }

        return [
            'policy_id' => $policy?->id,
            'status' => empty($issues) ? 'verified' : 'rejected',
            'issues' => $issues,
        ];
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
