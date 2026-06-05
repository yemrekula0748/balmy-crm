<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksSyncLog extends Model
{
    protected $fillable = [
        'branch_id', 'source', 'status', 'parameters', 'total_records',
        'created_records', 'updated_records', 'failed_records', 'error_message',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
}
