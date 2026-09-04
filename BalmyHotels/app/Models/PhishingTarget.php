<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhishingTarget extends Model
{
    protected $fillable = [
        'campaign_id',
        'user_id',
        'target_name',
        'target_email',
        'token',
        'click_count',
        'first_clicked_at',
        'last_clicked_at',
        'credential_attempt_count',
        'first_credential_attempted_at',
        'last_credential_attempted_at',
    ];

    protected $casts = [
        'first_clicked_at' => 'datetime',
        'last_clicked_at' => 'datetime',
        'first_credential_attempted_at' => 'datetime',
        'last_credential_attempted_at' => 'datetime',
        'click_count' => 'integer',
        'credential_attempt_count' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(PhishingCampaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        if ($this->first_credential_attempted_at) {
            return 'Bilgi gönderme denemesi';
        }

        if ($this->first_clicked_at) {
            return 'Bağlantıyı açtı';
        }

        return 'Açmadı';
    }
}
