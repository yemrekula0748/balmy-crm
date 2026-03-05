<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripAdvisorSnapshot extends Model
{
    protected $table = 'tripadvisor_snapshots';

    protected $fillable = [
        'hotel_name',
        'location_id',
        'rating',
        'num_reviews',
        'ranking_string',
        'snapshot_date',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'rating'        => 'float',
        'num_reviews'   => 'integer',
    ];

    /**
     * Belirli bir otel için son 30 günlük snapshot'ı döner.
     */
    public static function last30Days(int $locationId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('location_id', $locationId)
            ->orderBy('snapshot_date')
            ->where('snapshot_date', '>=', now()->subDays(30)->toDateString())
            ->get();
    }

    /**
     * Günlük yeni yorum sayısını hesaplar (bir önceki güne göre fark).
     */
    public function dailyNewReviews(): int
    {
        $prev = self::where('location_id', $this->location_id)
            ->where('snapshot_date', '<', $this->snapshot_date)
            ->orderByDesc('snapshot_date')
            ->first();

        if (!$prev) return 0;
        return max(0, $this->num_reviews - $prev->num_reviews);
    }
}
