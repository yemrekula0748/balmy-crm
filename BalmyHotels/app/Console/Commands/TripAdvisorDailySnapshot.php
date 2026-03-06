<?php

namespace App\Console\Commands;

use App\Models\TripAdvisorSnapshot;
use App\Services\TripAdvisorService;
use Illuminate\Console\Command;

class TripAdvisorDailySnapshot extends Command
{
    protected $signature   = 'tripadvisor:snapshot';
    protected $description = 'TripAdvisor otel puanlarını veritabanına günlük olarak kaydeder.';

    public function handle(): int
    {
        $service  = new TripAdvisorService();
        $today    = now()->toDateString();
        $stats    = $service->getAllStats();

        if (empty($stats)) {
            $this->error('TripAdvisor API boş yanıt döndürdü.');
            return self::FAILURE;
        }

        foreach ($stats as $hotel) {
            TripAdvisorSnapshot::updateOrCreate(
                [
                    'location_id'   => $hotel['location_id'],
                    'snapshot_date' => $today,
                ],
                [
                    'hotel_name'     => $hotel['name'],
                    'rating'         => $hotel['rating'],
                    'num_reviews'    => $hotel['num_reviews'],
                    'ranking_string' => $hotel['ranking'],
                ]
            );

            $this->info("✓ {$hotel['name']} — Puan: {$hotel['rating']} | Yorum: {$hotel['num_reviews']}");
        }

        // Kaydedilen verinin cache'ini sıfırla ki dashboard taze veri göstersin
        $service->clearCache();

        $this->info('Snapshot tamamlandı.');
        return self::SUCCESS;
    }
}
