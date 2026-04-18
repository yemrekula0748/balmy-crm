<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikroTikUsageLog extends Model
{
    protected $table = 'mikrotik_usage_logs';

    protected $fillable = [
        'username',
        'mac_address',
        'ip_address',
        'bytes_in',
        'bytes_out',
        'session_started_at',
        'last_seen_at',
    ];

    protected $casts = [
        'bytes_in'          => 'integer',
        'bytes_out'         => 'integer',
        'session_started_at' => 'datetime',
        'last_seen_at'      => 'datetime',
    ];
}
