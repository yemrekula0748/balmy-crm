<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\TripAdvisorSnapshot;
use App\Services\TripAdvisorService;

class TripAdvisorReportController extends Controller
{
    public function index()
    {
        // Canlı veri (cache'li)
        $liveStats = [];
        try {
            $liveStats = (new TripAdvisorService())->getAllStats();
        } catch (\Throwable $e) {
            // Sessiz hata
        }

        // Her otel için son 60 günlük grafik verisi
        $chartData = [];
        foreach (\App\Services\TripAdvisorService::HOTELS as $name => $locationId) {
            $snapshots = TripAdvisorSnapshot::where('location_id', $locationId)
                ->orderBy('snapshot_date')
                ->where('snapshot_date', '>=', now()->subDays(60)->toDateString())
                ->get();

            $labels  = [];
            $ratings = [];
            $reviews = [];
            $dailyNew = [];

            $prevCount = null;
            foreach ($snapshots as $snap) {
                $labels[]  = $snap->snapshot_date->format('d M');
                $ratings[] = $snap->rating;
                $reviews[] = $snap->num_reviews;

                if ($prevCount !== null) {
                    $dailyNew[] = max(0, $snap->num_reviews - $prevCount);
                } else {
                    $dailyNew[] = 0;
                }
                $prevCount = $snap->num_reviews;
            }

            $chartData[$locationId] = compact('labels', 'ratings', 'reviews', 'dailyNew', 'name');
        }

        // Her otel için istatistik özeti (DB'den)
        $summaries = [];
        foreach (\App\Services\TripAdvisorService::HOTELS as $name => $locationId) {
            $latest = TripAdvisorSnapshot::where('location_id', $locationId)
                ->orderByDesc('snapshot_date')
                ->first();

            $oldest = TripAdvisorSnapshot::where('location_id', $locationId)
                ->orderBy('snapshot_date')
                ->first();

            $totalNewReviews = 0;
            if ($latest && $oldest && $latest->id !== $oldest->id) {
                $totalNewReviews = max(0, $latest->num_reviews - $oldest->num_reviews);
            }

            $summaries[$locationId] = [
                'name'            => $name,
                'latest'          => $latest,
                'total_new'       => $totalNewReviews,
                'snapshot_count'  => TripAdvisorSnapshot::where('location_id', $locationId)->count(),
            ];
        }

        $page_title = 'TripAdvisor Puanları';

        return view('modules.tripadvisor.index', compact(
            'liveStats', 'chartData', 'summaries', 'page_title'
        ));
    }

    /**
     * Manuel snapshot al (buton ile tetiklenebilir).
     */
    public function snapshot()
    {
        \Artisan::call('tripadvisor:snapshot');
        return back()->with('success', 'TripAdvisor verileri güncellendi.');
    }
}
