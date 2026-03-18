<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    protected $fillable = [
        'agency_code', 'name', 'currency', 'billing_address',
        'nationalities', 'market', 'payment_type',
        'payment_method', 'accommodation_type',
    ];

    protected $casts = [
        'nationalities' => 'array',
    ];

    const CURRENCIES = [
        'TL'  => 'Türk Lirası (₺)',
        'USD' => 'Dolar ($)',
        'EUR' => 'Euro (€)',
        'GBP' => 'Pound (£)',
    ];

    const MARKETS = [
        'domestic'    => 'İç Pazar',
        'europe'      => 'Avrupa Pazarı',
        'middle_east' => 'Orta Doğu Pazarı',
        'russia'      => 'Rusya Pazarı',
    ];

    const PAYMENT_TYPES = [
        'agency_pay' => 'Acente Ödeyecek',
        'guest_pay'  => 'Misafir Ödeyecek',
    ];

    const PAYMENT_METHODS = [
        'city_ledger'    => 'Krediye Kaldır (City Ledger)',
        'cash'           => 'Nakit',
        'credit_card'    => 'Kredi Kartı',
        'bank_transfer'  => 'Havale',
    ];

    const ACCOMMODATION_TYPES = [
        'sold'      => 'Sold',
        'comp'      => 'Comp',
        'house_use' => 'House Use',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(AgencyContract::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public static function generateCode(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'ACT-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
