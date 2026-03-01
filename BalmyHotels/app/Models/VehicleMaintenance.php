<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id','user_id','type','maintenance_at','km',
        'next_km','next_date','service_name','cost','notes'
    ];

    protected $casts = [
        'maintenance_at' => 'date',
        'next_date'      => 'date',
    ];

    const TYPES = [
        'servis'  => 'Periyodik Servis',
        'lastik'  => 'Lastik',
        'yag'     => 'Yağ Değişimi',
        'egzoz'   => 'Egzoz',
        'fren'    => 'Fren',
        'diger'   => 'Diğer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
