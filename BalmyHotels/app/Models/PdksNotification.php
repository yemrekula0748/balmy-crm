<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksNotification extends Model
{
    protected $fillable = ['employee_id', 'user_id', 'type', 'title', 'body', 'url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
