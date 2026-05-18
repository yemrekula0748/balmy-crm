<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class HotelAdvisorHotspotService
{
    public function hotel(string $hotelKey): array
    {
        $hotel = config("services.hoteladvisor.hotels.{$hotelKey}");

        if (!is_array($hotel)) {
            throw new RuntimeException('HotelAdvisor otel ayari bulunamadi.');
        }

        $hotel['key'] = $hotelKey;
        $hotel['base_url'] = $this->normalizeBaseUrl(
            (string) config('services.hoteladvisor.base_url', '')
        );

        return $hotel;
    }

    public function isConfigured(string $hotelKey): bool
    {
        $hotel = config("services.hoteladvisor.hotels.{$hotelKey}");

        return is_array($hotel)
            && !empty($hotel['api_key'])
            && !empty($hotel['hotel_id'])
            && !empty(config('services.hoteladvisor.base_url'));
    }

    public function getRoomGuests(string $hotelKey, string $roomNo): array
    {
        $hotel = $this->hotel($hotelKey);

        if (empty($hotel['api_key'])) {
            throw new RuntimeException('HotelAdvisor API anahtari tanimli degil.');
        }

        if (empty($hotel['hotel_id'])) {
            throw new RuntimeException('HotelAdvisor HOTELID tanimli degil.');
        }

        if (empty($hotel['base_url'])) {
            throw new RuntimeException('HotelAdvisor base URL tanimli degil.');
        }

        $response = Http::acceptJson()
            ->withToken((string) $hotel['api_key'])
            ->timeout(20)
            ->get($hotel['base_url'] . '/apisequence/GetHotspotList', [
                'HOTELID' => $hotel['hotel_id'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('HotelAdvisor servisine ulasilamadi.');
        }

        $payload = $response->json();

        if (!is_array($payload) || empty($payload[0]) || !is_array($payload[0])) {
            throw new RuntimeException('HotelAdvisor beklenmeyen bir cevap dondu.');
        }

        $meta = $payload[0];

        if (!(bool) ($meta['STATUS'] ?? false)) {
            throw new RuntimeException((string) ($meta['MESSAGE'] ?? 'HotelAdvisor hata dondurdu.'));
        }

        $roomNo = $this->normalizeRoomNo($roomNo);
        $rows = $meta['DATA'] ?? [];

        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(function ($row) use ($roomNo) {
                return $this->normalizeRoomNo((string) ($row['ROOMNO'] ?? '')) === $roomNo;
            })
            ->values()
            ->map(function ($row) use ($hotel) {
                return $this->mapGuest($hotel, is_array($row) ? $row : []);
            })
            ->all();
    }

    private function mapGuest(array $hotel, array $row): array
    {
        $selectionKey = $this->selectionKey($row);
        $roomNo = trim((string) ($row['ROOMNO'] ?? ''));
        $checkIn = $this->normalizeDate($row['CHECKIN'] ?? null);
        $checkOut = $this->normalizeDate($row['CHECKOUT'] ?? null);

        return [
            'selection_key' => $selectionKey,
            'stay_signature' => sha1(implode('|', [
                $hotel['hotel_id'] ?? '',
                $roomNo,
                $selectionKey,
                $checkIn ?? '',
                $checkOut ?? '',
            ])),
            'hotel_key' => (string) ($hotel['key'] ?? ''),
            'hotel_name' => (string) ($hotel['name'] ?? ''),
            'hotel_id' => (string) ($row['HOTELID'] ?? $hotel['hotel_id'] ?? ''),
            'branch_id' => $hotel['branch_id'] ?? null,
            'room_no' => $roomNo,
            'name' => trim((string) ($row['NAME'] ?? '')),
            'lname' => trim((string) ($row['LNAME'] ?? '')),
            'full_name' => trim(
                trim((string) ($row['NAME'] ?? '')) . ' ' . trim((string) ($row['LNAME'] ?? ''))
            ),
            'guest_id' => $this->nullableString($row['GUESTID'] ?? null),
            'reservation_id' => $this->nullableString($row['RESID'] ?? null),
            'reservation_name_id' => $this->nullableString($row['RESNAMEID'] ?? null),
            'phone' => $this->nullableString($row['PHONE'] ?? null),
            'email' => $this->nullableString($row['EMAIL'] ?? null),
            'nationality' => $this->nullableString($row['NATIONALITY'] ?? null),
            'national_id_no' => $this->nullableString($row['NATIONALIDNO'] ?? null),
            'passport_no' => $this->nullableString($row['PASSPORTNO'] ?? null),
            'checkin' => $checkIn,
            'checkout' => $checkOut,
            'arrival_time' => $this->nullableString($row['ARRIVALTIME'] ?? null),
            'departure_time' => $this->nullableString($row['DEPARTURETIME'] ?? null),
            'raw' => $row,
        ];
    }

    private function selectionKey(array $row): string
    {
        foreach (['RESNAMEID', 'GUESTID'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return sha1(implode('|', [
            trim((string) ($row['ROOMNO'] ?? '')),
            trim((string) ($row['NAME'] ?? '')),
            trim((string) ($row['LNAME'] ?? '')),
            trim((string) ($row['CHECKIN'] ?? '')),
            trim((string) ($row['CHECKOUT'] ?? '')),
        ]));
    }

    private function normalizeBaseUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }

    private function normalizeRoomNo(string $roomNo): string
    {
        return strtoupper(trim($roomNo));
    }

    private function normalizeDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
