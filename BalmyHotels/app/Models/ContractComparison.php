<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractComparison extends Model
{
    protected $fillable = [
        'user_id', 'title',
        'file_a_name', 'file_b_name',
        'file_a_type', 'file_b_type',
        'lines_added', 'lines_removed', 'lines_equal',
        'similarity', 'diff_json',
    ];

    protected $casts = [
        'diff_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Toplam satır sayısı */
    public function totalLines(): int
    {
        return $this->lines_added + $this->lines_removed + $this->lines_equal;
    }
}
