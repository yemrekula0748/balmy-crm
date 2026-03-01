<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoorLog extends Model
{
    protected $fillable = ['user_id', 'branch_id', 'type', 'logged_at', 'notes'];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isGiris(): bool
    {
        return $this->type === 'giris';
    }
}
