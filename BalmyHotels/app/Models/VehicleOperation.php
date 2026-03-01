<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id','user_id','type','km',
        'operation_at','destination','notes'
    ];

    protected $casts = ['operation_at' => 'datetime'];

    const TYPES = [
        'giris'           => 'Araç Girişi',
        'cikis'           => 'Araç Çıkışı',
        'goreve_gidis'    => 'Göreve Gidiş',
        'gorevden_gelis'  => 'Görevden Geliş',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabel()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
