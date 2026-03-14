<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\GoogleSnapshot;
use App\Services\GooglePlacesService;

class GoogleReportController extends Controller
{
    public function index()
    {
        // Canlı veri (cache'li)
        $liveStats = [];
        try {
            $liveStats = (new GooglePlacesService())->getAllStats();
        } catch (\Throwable $e) {
            // Sessiz hata
        }

        // Her otel için son 60 günlük grafik verisi
        $chartData = [];
        foreach (GooglePlacesService::HOTELS as $name => $hotel) {
            $snapshots = GoogleSnapshot::where('place_id', $hotel['place_id'])
                ->orderBy('snapshot_date')
                ->where('snapshot_date', '>=', now()->subDays(60)->toDateString())
                ->get();

            $labels   = [];
            $ratings  = [];
            $dailyNew = [];
            $prevCount = null;

            foreach ($snapshots as $snap) {
                $labels[]  = $snap->snapshot_date->format('d M');
                $ratings[] = $snap->rating;

                if ($prevCount !== null) {
                    $dailyNew[] = max(0, $snap->user_ratings_total - $prevCount);
                } else {
                    $dailyNew[] = 0;
                }
                $prevCount = $snap->user_ratings_total;
            }

            $chartData[$hotel['place_id']] = compact('labels', 'ratings', 'dailyNew', 'name');
        }

        // DB özet istatistikleri
        $summaries = [];
        foreach (GooglePlacesService::HOTELS as $name => $hotel) {
            $placeId = $hotel['place_id'];

            $latest = GoogleSnapshot::where('place_id', $placeId)->orderByDesc('snapshot_date')->first();
            $oldest = GoogleSnapshot::where('place_id', $placeId)->orderBy('snapshot_date')->first();

            $totalNew = 0;
            if ($latest && $oldest && $latest->id !== $oldest->id) {
                $totalNew = max(0, $latest->user_ratings_total - $oldest->user_ratings_total);
            }

            $summaries[$placeId] = [
                'name'           => $name,
                'latest'         => $latest,
                'total_new'      => $totalNew,
                'snapshot_count' => GoogleSnapshot::where('place_id', $placeId)->count(),
            ];
        }

        $page_title = 'Google Puanları';

        return view('modules.google.index', compact(
            'liveStats', 'chartData', 'summaries', 'page_title'
        ));
    }

    /**
     * Manuel snapshot al.
     */
    public function snapshot()
    {
        \Artisan::call('google:snapshot');
        return back()->with('success', 'Google verileri güncellendi.');
    }
}
