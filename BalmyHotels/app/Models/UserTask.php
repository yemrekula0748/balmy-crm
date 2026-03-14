<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description',
        'due_date', 'status', 'priority', 'reminder_sent',
    ];

    protected $casts = [
        'due_date'      => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high'   => 'Yüksek',
            'medium' => 'Orta',
            'low'    => 'Düşük',
            default  => $this->priority,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Bekliyor',
            'in_progress' => 'Devam Ediyor',
            'completed'   => 'Tamamlandı',
            default       => $this->status,
        };
    }
}
