<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSnapshot extends Model
{
    protected $table = 'google_snapshots';

    protected $fillable = [
        'hotel_name',
        'place_id',
        'rating',
        'user_ratings_total',
        'snapshot_date',
    ];

    protected $casts = [
        'snapshot_date'      => 'date',
        'rating'             => 'float',
        'user_ratings_total' => 'integer',
    ];

    /**
     * Belirli bir otel için son 60 günlük snapshot'ları döner.
     */
    public static function last60Days(string $placeId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('place_id', $placeId)
            ->orderBy('snapshot_date')
            ->where('snapshot_date', '>=', now()->subDays(60)->toDateString())
            ->get();
    }
}
