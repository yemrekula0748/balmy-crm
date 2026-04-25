<?php

namespace App\Console\Commands;

use App\Models\GoogleSnapshot;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class GoogleDailySnapshot extends Command
{
    protected $signature   = 'google:snapshot';
    protected $description = 'Google Places otel puanlarını veritabanına günlük olarak kaydeder.';

    public function handle(): int
    {
        $service = new GooglePlacesService();
        $today   = now()->toDateString();
        $stats   = $service->getAllStats();

        if (empty($stats)) {
            $this->error('Google Places API boş yanıt döndürdü.');
            return self::FAILURE;
        }

        foreach ($stats as $hotel) {
            GoogleSnapshot::updateOrCreate(
                [
                    'place_id'      => $hotel['place_id'],
                    'snapshot_date' => $today,
                ],
                [
                    'hotel_name'         => $hotel['name'],
                    'rating'             => $hotel['rating'],
                    'user_ratings_total' => $hotel['user_ratings_total'],
                ]
            );

            $this->info("✓ {$hotel['name']} — Puan: {$hotel['rating']} | Yorum: {$hotel['user_ratings_total']}");
        }

        $service->clearCache();

        $this->info('Google snapshot tamamlandı.');
        return self::SUCCESS;
    }
}
