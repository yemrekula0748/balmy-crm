<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarbonFootprintReport extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'title', 'report_type',
        'period_start', 'period_end',
        'total_guests', 'occupied_rooms', 'total_rooms', 'staff_count', 'total_area_sqm',
        'total_co2_scope1', 'total_co2_scope2', 'total_co2_scope3', 'total_co2_total',
        'co2_per_guest', 'co2_per_room_night', 'co2_per_sqm', 'co2_per_staff',
        'hcmi_score', 'hcmi_rating', 'renewable_energy_pct', 'waste_recycling_rate', 'water_intensity',
        'standards_applied', 'methodology_notes', 'improvement_notes',
        'status', 'pdf_path', 'finalized_at',
    ];

    protected $casts = [
        'period_start'        => 'date',
        'period_end'          => 'date',
        'finalized_at'        => 'datetime',
        'standards_applied'   => 'array',
        'total_co2_scope1'    => 'float',
        'total_co2_scope2'    => 'float',
        'total_co2_scope3'    => 'float',
        'total_co2_total'     => 'float',
        'co2_per_guest'       => 'float',
        'co2_per_room_night'  => 'float',
        'co2_per_sqm'         => 'float',
        'co2_per_staff'       => 'float',
        'hcmi_score'          => 'float',
        'renewable_energy_pct'  => 'float',
        'waste_recycling_rate'  => 'float',
        'water_intensity'       => 'float',
    ];

    // ------------------------------------------------------------------
    // Kategori tanımları (form için)
    // ------------------------------------------------------------------
    const CATEGORIES = [
        // Scope 1 — Doğrudan emisyonlar
        'scope1' => [
            'energy_gas'       => ['label' => 'Doğal Gaz', 'unit' => 'm³',   'ef' => 2.204,  'ef_source' => 'IPCC AR6 2023'],
            'energy_fuel_oil'  => ['label' => 'Fuel Oil',  'unit' => 'L',    'ef' => 2.967,  'ef_source' => 'IPCC AR6 2023'],
            'energy_lpg'       => ['label' => 'LPG',       'unit' => 'L',    'ef' => 1.513,  'ef_source' => 'IPCC AR6 2023'],
            'energy_coal'      => ['label' => 'Kömür',     'unit' => 'kg',   'ef' => 2.421,  'ef_source' => 'IPCC AR6 2023'],
            'transport_diesel' => ['label' => 'Kurumsal Araç - Motorin', 'unit' => 'L', 'ef' => 2.526, 'ef_source' => 'DEFRA 2023'],
            'transport_petrol' => ['label' => 'Kurumsal Araç - Benzin',  'unit' => 'L', 'ef' => 2.310, 'ef_source' => 'DEFRA 2023'],
            'refrigerant_r410a'=> ['label' => 'Soğutucu Gaz R410A', 'unit' => 'kg', 'ef' => 2088.0, 'ef_source' => 'IPCC AR6 GWP100'],
            'refrigerant_r32'  => ['label' => 'Soğutucu Gaz R32',   'unit' => 'kg', 'ef' => 675.0,  'ef_source' => 'IPCC AR6 GWP100'],
            'refrigerant_r134a'=> ['label' => 'Soğutucu Gaz R134a', 'unit' => 'kg', 'ef' => 1430.0, 'ef_source' => 'IPCC AR6 GWP100'],
        ],
        // Scope 2 — Dolaylı enerji emisyonları
        'scope2' => [
            'energy_electricity'    => ['label' => 'Elektrik (Şebeke)', 'unit' => 'kWh', 'ef' => 0.649, 'ef_source' => 'IEA Turkey 2023'],
            'energy_electricity_re' => ['label' => 'Elektrik (Yenilenebilir)', 'unit' => 'kWh', 'ef' => 0.017, 'ef_source' => 'IEA Solar/Wind 2023'],
            'district_cooling'      => ['label' => 'Merkezi Soğutma', 'unit' => 'kWh', 'ef' => 0.250, 'ef_source' => 'DEFRA 2023'],
            'district_heating'      => ['label' => 'Merkezi Isıtma',  'unit' => 'kWh', 'ef' => 0.180, 'ef_source' => 'DEFRA 2023'],
        ],
        // Scope 3 — Diğer dolaylı emisyonlar
        'scope3' => [
            'water_municipal'    => ['label' => 'Şebeke Suyu',        'unit' => 'm³',  'ef' => 0.344,  'ef_source' => 'DEFRA 2023'],
            'water_wastewater'   => ['label' => 'Atık Su İşleme',     'unit' => 'm³',  'ef' => 0.708,  'ef_source' => 'DEFRA 2023'],
            'waste_general'      => ['label' => 'Karışık Atık (Çöp)', 'unit' => 'kg',  'ef' => 0.490,  'ef_source' => 'IPCC 2019'],
            'waste_food'         => ['label' => 'Gıda Atığı',         'unit' => 'kg',  'ef' => 2.530,  'ef_source' => 'IPCC 2019'],
            'waste_recycled'     => ['label' => 'Geri Dönüştürülen Atık', 'unit' => 'kg', 'ef' => 0.021, 'ef_source' => 'DEFRA 2023'],
            'waste_compost'      => ['label' => 'Kompost Atığı',      'unit' => 'kg',  'ef' => 0.010,  'ef_source' => 'DEFRA 2023'],
            'food_beef'          => ['label' => 'Sığır Eti Tüketimi', 'unit' => 'kg',  'ef' => 27.00,  'ef_source' => 'IPCC/FAO 2023'],
            'food_chicken'       => ['label' => 'Tavuk Tüketimi',     'unit' => 'kg',  'ef' => 5.70,   'ef_source' => 'IPCC/FAO 2023'],
            'food_fish'          => ['label' => 'Balık Tüketimi',     'unit' => 'kg',  'ef' => 3.20,   'ef_source' => 'FAO 2023'],
            'food_dairy'         => ['label' => 'Süt & Peynir',       'unit' => 'kg',  'ef' => 3.20,   'ef_source' => 'FAO 2023'],
            'food_plant'         => ['label' => 'Bitkisel Gıda',      'unit' => 'kg',  'ef' => 0.78,   'ef_source' => 'IPCC/FAO 2023'],
            'transport_staff'    => ['label' => 'Personel Ulaşımı (km)', 'unit' => 'km', 'ef' => 0.192, 'ef_source' => 'DEFRA 2023'],
            'transport_guest_shuttle' => ['label' => 'Misafir Shuttle',  'unit' => 'km', 'ef' => 0.089, 'ef_source' => 'DEFRA 2023'],
            'procurement_cleaning'    => ['label' => 'Temizlik Ürünleri', 'unit' => 'kg', 'ef' => 3.100, 'ef_source' => 'Ecoinvent 3.9'],
            'procurement_linen'       => ['label' => 'Çamaşır/Keten',     'unit' => 'kg', 'ef' => 15.00, 'ef_source' => 'Ecoinvent 3.9'],
            'procurement_amenities'   => ['label' => 'Misafir Malzemeleri','unit' => 'kg', 'ef' => 4.200, 'ef_source' => 'Ecoinvent 3.9'],
            'business_travel_air'     => ['label' => 'İş Seyahati (Uçak)', 'unit' => 'km', 'ef' => 0.285, 'ef_source' => 'ICAO 2023'],
        ],
    ];

    const STANDARDS = [
        'ISO 14064-1'  => 'ISO 14064-1:2018 - Sera Gazı Hesaplama & Raporlama',
        'ISO 14001'    => 'ISO 14001:2015 - Çevre Yönetim Sistemi',
        'ISO 50001'    => 'ISO 50001:2018 - Enerji Yönetim Sistemi',
        'HCMI'         => 'HCMI - Hotel Carbon Measurement Initiative',
        'GHG_Protocol' => 'GHG Protocol Corporate Standard',
        'CSRD'         => 'CSRD - AB Kurumsal Sürdürülebilirlik Raporlama Direktifi',
        'CDP'          => 'CDP - Carbon Disclosure Project',
        'EU_Taxonomy'  => 'EU Taxonomy for Sustainable Finance',
        'TCFD'         => 'TCFD - Task Force on Climate-related Financial Disclosures',
        'SDG_13'       => 'BM SDG 13 - İklim Eylemi',
        'GRI_305'      => 'GRI 305 - Emisyonlar',
        'SASB_Hospitality' => 'SASB Hotels & Lodging Sektör Standardı',
    ];

    const HCMI_RATINGS = [
        'A+' => ['min' => 90, 'color' => '#1a6b3c', 'label' => 'Mükemmel'],
        'A'  => ['min' => 75, 'color' => '#27ae60', 'label' => 'Çok İyi'],
        'B'  => ['min' => 60, 'color' => '#f39c12', 'label' => 'İyi'],
        'C'  => ['min' => 45, 'color' => '#e67e22', 'label' => 'Ortalama'],
        'D'  => ['min' => 25, 'color' => '#e74c3c', 'label' => 'Zayıf'],
        'E'  => ['min' => 0,  'color' => '#8e44ad', 'label' => 'Yetersiz'],
    ];

    // ------------------------------------------------------------------
    // İlişkiler
    // ------------------------------------------------------------------
    public function entries(): HasMany
    {
        return $this->hasMany(CarbonFootprintEntry::class, 'report_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ------------------------------------------------------------------
    // Accessor & Yardımcılar
    // ------------------------------------------------------------------
    public function scopeEntriesByScope(int $scope)
    {
        return $this->entries()->where('scope', $scope)->get();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'    => '<span class="badge bg-secondary">Taslak</span>',
            'final'    => '<span class="badge bg-success">Final</span>',
            'verified' => '<span class="badge bg-primary">Doğrulandı</span>',
            default    => '',
        };
    }

    public function getRatingColorAttribute(): string
    {
        foreach (self::HCMI_RATINGS as $key => $r) {
            if ($this->hcmi_rating === $key) {
                return $r['color'];
            }
        }
        return '#666';
    }

    /** Toplam CO2 → tonne formatı */
    public function getTotalCo2TonneAttribute(): float
    {
        return round($this->total_co2_total / 1000, 3);
    }

    public static function computeHcmiRating(float $score): string
    {
        foreach (self::HCMI_RATINGS as $key => $r) {
            if ($score >= $r['min']) return $key;
        }
        return 'E';
    }
}
