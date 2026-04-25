<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'email',
        'user_id',
        'ip_address',
        'user_agent',
        'status',
        'failure_reason',
        'country',
        'city',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function getBrowserAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg'))    return 'Chrome';
        if (str_contains($ua, 'Firefox'))   return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Edg'))       return 'Edge';
        if (str_contains($ua, 'OPR') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) return 'IE';
        return 'Bilinmiyor';
    }

    public function getOsAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (str_contains($ua, 'Windows'))   return 'Windows';
        if (str_contains($ua, 'Macintosh')) return 'macOS';
        if (str_contains($ua, 'Linux'))     return 'Linux';
        if (str_contains($ua, 'Android'))   return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Bilinmiyor';
    }
}
