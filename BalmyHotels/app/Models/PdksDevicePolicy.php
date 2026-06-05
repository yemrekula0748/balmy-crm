<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksDevicePolicy extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'require_gps', 'require_wifi', 'allowed_latitude',
        'allowed_longitude', 'allowed_radius_meters', 'max_accuracy_meters',
        'allowed_wifi_ssids', 'allowed_wifi_bssids', 'block_mock_location', 'is_active',
    ];

    protected $casts = [
        'require_gps' => 'boolean',
        'require_wifi' => 'boolean',
        'allowed_wifi_ssids' => 'array',
        'allowed_wifi_bssids' => 'array',
        'block_mock_location' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
}
