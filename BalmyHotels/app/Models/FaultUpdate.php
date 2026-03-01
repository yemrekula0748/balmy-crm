<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultUpdate extends Model
{
    protected $fillable = ['fault_id', 'user_id', 'note', 'status_from', 'status_to'];

    public function fault() { return $this->belongsTo(Fault::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
