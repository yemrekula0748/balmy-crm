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
            'energy_gas'       => ['label' => 'Doğal Gaz',                       'unit' => 'm³',  'ef' => 2.204,  'ef_source' => 'IPCC AR6 2023',
                'help' => 'Doğal gaz ana sayacından dönemlik okuma (m³). Kazan, mutfak ocağı, çamaşırhane kazanı dahil tesisteki tüm tüketimi girin.'],
            'energy_lng'       => ['label' => 'LNG (Sıvılaştırılmış Doğal Gaz)', 'unit' => 'ton', 'ef' => 2748.0, 'ef_source' => 'IPCC AR6 2023 / DEFRA 2023',
                'help' => 'LNG tankından kullanılan ton cinsinden miktar. Dolum/teslimat irsaliyesi veya fatura toplamından hesaplanır. 1 m³ LNG ≈ 0.441 ton.'],
            'energy_fuel_oil'  => ['label' => 'Fuel Oil',                         'unit' => 'L',   'ef' => 2.967,  'ef_source' => 'IPCC AR6 2023',
                'help' => 'Kazan veya jeneratör fuel oil tüketimi (L). Yakıt faturası veya depo dolum/azalma kayıtlarından hesaplanır.'],
            'energy_lpg'       => ['label' => 'LPG',                              'unit' => 'L',   'ef' => 1.513,  'ef_source' => 'IPCC AR6 2023',
                'help' => 'LPG tüpleri veya depo sayaçlarından ölçülen tüketim (L). Mutfak ve ısıtma kullanımını kapsar. 1 kg LPG ≈ 1.96 L.'],
            'energy_coal'      => ['label' => 'Kömür',                            'unit' => 'kg',  'ef' => 2.421,  'ef_source' => 'IPCC AR6 2023',
                'help' => 'Kazan veya ısıtma sistemine dönemde yüklenen kömür miktarı (kg). Satın alma faturasından veya tartım kayıtlarından.'],
            'transport_diesel' => ['label' => 'Kurumsal Araç — Motorin',          'unit' => 'L',   'ef' => 2.526,  'ef_source' => 'DEFRA 2023',
                'help' => 'Otele ait araçların (servis, minibüs, kamyonet) motorin tüketimi (L). Yakıt kartı ekstresi veya akaryakıt faturası dökümünden.'],
            'transport_petrol' => ['label' => 'Kurumsal Araç — Benzin',           'unit' => 'L',   'ef' => 2.310,  'ef_source' => 'DEFRA 2023',
                'help' => 'Otele ait benzinli araçların tüketimi (L). Yakıt kartı ekstresi veya akaryakıt faturası dökümünden.'],
            'refrigerant_r410a'=> ['label' => 'Soğutucu Gaz R410A',              'unit' => 'kg',  'ef' => 2088.0, 'ef_source' => 'IPCC AR6 GWP100',
                'help' => 'Yıllık klima/VRF/chiller bakımında doldurulan R410A miktarı (kg). Servis formu veya faturasından. GWP=2088 — küçük miktarlar büyük etki yaratır!'],
            'refrigerant_r32'  => ['label' => 'Soğutucu Gaz R32',                'unit' => 'kg',  'ef' => 675.0,  'ef_source' => 'IPCC AR6 GWP100',
                'help' => 'Klima servislerinde doldurulan R32 miktarı (kg). Teknik servis faturasından. GWP=675.'],
            'refrigerant_r134a'=> ['label' => 'Soğutucu Gaz R134a',              'unit' => 'kg',  'ef' => 1430.0, 'ef_source' => 'IPCC AR6 GWP100',
                'help' => 'Soğutma dolabı, minibar veya merkezi soğutma servisinde kullanılan R134a miktarı (kg). Servis kayıtlarından. GWP=1430.'],
        ],
        // Scope 2 — Dolaylı enerji emisyonları
        'scope2' => [
            // Location-Based Yaklaşım
            'energy_electricity'      => ['label' => 'Elektrik — Şebeke (Location-Based)',                       'unit' => 'kWh', 'ef' => 0.649, 'ef_source' => 'IEA Turkey 2023',
                'help' => 'Elektrik dağıtım şebekesinden çekilen TOPLAM kWh. Tüm elektrik faturalarını toplayın: odalar, mutfak, lobi, ortak alanlar, çamaşırhane, asansör, pompa sistemi dahil.'],
            'energy_electricity_re'   => ['label' => 'Elektrik — Yenilenebilir (Location-Based LCA)',             'unit' => 'kWh', 'ef' => 0.017, 'ef_source' => 'IEA Solar/Wind 2023',
                'help' => 'Şebeke bağlantılı ancak LCA analizinde düşük emisyon faktörlü yenilenebilir kaynaklı elektrik. I-REC/YEK-G sertifikanız yoksa bu satırı kullanmayın; yukarıdaki şebeke satırına ekleyin.'],
            // Market-Based Yaklaşım (ISO 14064-1 §8.3)
            'energy_electricity_irec' => ['label' => 'Elektrik — I-REC / YEK-G / GoO Sertifikalı (Market-Based)', 'unit' => 'kWh', 'ef' => 0.0,   'ef_source' => 'ISO 14064-1 Market-Based / RE100',
                'help' => 'I-REC, YEK-G veya GoO belgesiyle ispatlanan yenilenebilir elektrik miktarı (kWh). EF=0 uygulanır. Sertifika belgesi denetçiye ibraz edilmelidir (ISO 14064-1 §8.3.3).'],
            'energy_onsite_solar'     => ['label' => 'Tesis İçi Üretim — Çatı GES / Rüzgar (Market-Based)',      'unit' => 'kWh', 'ef' => 0.0,   'ef_source' => 'ISO 14064-1 Market-Based / GHG Protocol',
                'help' => 'Kendi çatı GES veya tesis içi rüzgar türbininden üretilerek tüketilen kWh. Üretim sayacından okunur. EF=0 uygulanır; mahsuplaşma ile Scope 2 emisyonunuzu azaltır.'],
            'district_cooling'        => ['label' => 'Merkezi Soğutma',                                           'unit' => 'kWh', 'ef' => 0.250, 'ef_source' => 'DEFRA 2023',
                'help' => 'Bölgesel soğutma ağından veya merkezi chiller istasyonundan alınan soğutma enerjisi (kWh). Enerji sayacı veya sağlayıcı faturasından.'],
            'district_heating'        => ['label' => 'Merkezi Isıtma',                                            'unit' => 'kWh', 'ef' => 0.180, 'ef_source' => 'DEFRA 2023',
                'help' => 'Bölgesel ısı ağından veya buhar istasyonundan alınan ısı enerjisi (kWh). Doğalgaz dağıtım şirketi ısı satışı veya buhar sayacından.'],
        ],
        // Scope 3 — Diğer dolaylı emisyonlar
        'scope3' => [
            'water_municipal'         => ['label' => 'Şebeke Suyu',                  'unit' => 'm³',  'ef' => 0.344,  'ef_source' => 'DEFRA 2023',
                'help' => 'Su idaresi faturasındaki toplam tüketim (m³). Yüzme havuzu, sulama, mutfak, misafir odaları, çamaşırhane dahil tesisteki tüm su kullanımını girin.'],
            'water_wastewater'        => ['label' => 'Atık Su İşleme',               'unit' => 'm³',  'ef' => 0.708,  'ef_source' => 'DEFRA 2023',
                'help' => 'Atıksu arıtma tesisine gönderilen su miktarı (m³). Atıksu faturanız yoksa şebeke suyu × 0.80 katsayısı ile tahmin edin.'],
            'waste_general'           => ['label' => 'Karışık Atık (Çöp)',           'unit' => 'kg',  'ef' => 0.490,  'ef_source' => 'IPCC 2019',
                'help' => 'Ayrıştırılmamış karışık çöp miktarı (kg). Taşıyıcı firma kantar fişlerinden veya konteyner hacim × doluluk oranı tahmini ile hesaplanır.'],
            'waste_food'              => ['label' => 'Gıda Atığı',                   'unit' => 'kg',  'ef' => 2.530,  'ef_source' => 'IPCC 2019',
                'help' => 'Mutfaktan çıkan hazırlanmamış+tabak artığı gıda miktarı (kg). Çöplükte metan (CH₄) ürettiği için yüksek emisyon faktörüne sahiptir. Günlük tartım tutanaklarından.'],
            'waste_recycled'          => ['label' => 'Geri Dönüştürülen Atık',       'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'DEFRA 2023',
                'help' => 'Kağıt, cam, plastik, metal olarak ayrıştırılan ve geri dönüşüme gönderilen toplam atık (kg). Geri dönüşüm firması kantar fişlerinden.'],
            'waste_compost'           => ['label' => 'Kompost Atığı',                'unit' => 'kg',  'ef' => 0.010,  'ef_source' => 'DEFRA 2023',
                'help' => 'Organik atıktan kompost yapılan miktar (kg). Bu kaleme taşıdığınız miktarı Gıda Atığı kaleminden düşürün; çift sayımı önler.'],
            'food_beef'               => ['label' => 'Sığır Eti Tüketimi',           'unit' => 'kg',  'ef' => 27.00,  'ef_source' => 'IPCC/FAO 2023',
                'help' => 'Dönemde satın alınan sığır/dana eti (kg). En yüksek emisyon faktörüne sahip gıda kalemi (27 kgCO₂e/kg). Mutfak tedarik faturalarından.'],
            'food_chicken'            => ['label' => 'Tavuk Tüketimi',               'unit' => 'kg',  'ef' => 5.70,   'ef_source' => 'IPCC/FAO 2023',
                'help' => 'Dönemde satın alınan tavuk, hindi gibi kümes hayvanı eti toplamı (kg). Tedarikçi faturası dökümünden.'],
            'food_fish'               => ['label' => 'Balık & Deniz Ürünleri',       'unit' => 'kg',  'ef' => 3.20,   'ef_source' => 'FAO 2023',
                'help' => 'Tüm deniz ürünleri dahil balık alımı (kg). Balık çeşitliliğine göre EF değişse de ortalama 3.20 kullanılır. Tedarikçi faturalarından.'],
            'food_dairy'              => ['label' => 'Süt & Peynir & Tereyağı',      'unit' => 'kg',  'ef' => 3.20,   'ef_source' => 'FAO 2023',
                'help' => 'Süt, peynir, yoğurt, tereyağı, krema gibi tüm süt ürünleri toplamı (kg). Tedarikçi faturalarından kg cinsinden hesaplayın.'],
            'food_plant'              => ['label' => 'Bitkisel Gıda',                'unit' => 'kg',  'ef' => 0.78,   'ef_source' => 'IPCC/FAO 2023',
                'help' => 'Sebze, meyve, tahıl, baklagil gibi bitkisel gıda alımı (kg). En düşük karbon ayak izine sahip gıda grubudur. Yerel tedarikçiden alım nakliye Scope 3\'ünü düşürür.'],
            'transport_staff'         => ['label' => 'Personel Ulaşımı',             'unit' => 'km',  'ef' => 0.192,  'ef_source' => 'DEFRA 2023',
                'help' => 'Personel servis araçlarının dönemde kat ettiği toplam km. Hesap: güzergah km × günlük sefer × iş günü sayısı.'],
            'transport_guest_shuttle' => ['label' => 'Misafir Shuttle (Transfer)',   'unit' => 'km',  'ef' => 0.089,  'ef_source' => 'DEFRA 2023',
                'help' => 'Havalimanı/şehir transfer aracınızın dönemdeki toplam km. Rezervasyon kayıtlarından; gidiş+dönüş dahil toplam mesafe.'],
            'procurement_cleaning'    => ['label' => 'Temizlik Kimyasalları',        'unit' => 'kg',  'ef' => 3.100,  'ef_source' => 'Ecoinvent 3.9',
                'help' => 'Satın alınan tüm temizlik kimyasalları toplamı (kg): deterjan, dezenfektan, yüzey temizleyici, halı şampuanı vb. Tedarikçi faturalarından.'],
            'procurement_linen'       => ['label' => 'Tekstil Alımı (Yeni Çarşaf/Havlu)', 'unit' => 'kg', 'ef' => 15.00, 'ef_source' => 'Ecoinvent 3.9',
                'help' => 'Dönemde SATIN ALINAN yeni çarşaf, havlu, bornoz, masa örtüsü (kg). Yıkama miktarı değil, yalnızca yeni alım miktarıdır.'],
            'laundry_onsite'          => ['label' => 'Çamaşırhane — Tesis İçi',     'unit' => 'kg',  'ef' => 0.0,    'ef_source' => 'GHG Protocol — kapsam çakışması',
                'help' => 'Tesis içi çamaşırhanenin yıkadığı çamaşır miktarı (kg). Doğal gaz ve elektrik tüketimleri zaten Scope 1/2\'de kayıtlıysa burayı 0 bırakın — çift sayımı önler.'],
            'laundry_external'        => ['label' => 'Çamaşırhane — Dış Servis',    'unit' => 'kg',  'ef' => 0.540,  'ef_source' => 'DEFRA 2023 / Ecoinvent 3.9',
                'help' => 'Otel dışı firmaya gönderilen çamaşır miktarı (kg). Teslimat/teslim alım irsaliyelerinden veya dış firma faturasındaki kg bilgisinden.'],
            'procurement_amenities'   => ['label' => 'Misafir Tüketim Malzemeleri', 'unit' => 'kg',  'ef' => 4.200,  'ef_source' => 'Ecoinvent 3.9',
                'help' => 'Odalara konan şampuan, sabun, duş jeli, diş fırçası gibi tek/kısa kullanımlık ürünler toplamı (kg). Tedarikçi faturasından.'],
            'business_travel_air'     => ['label' => 'İş Seyahati (Uçak)',          'unit' => 'km',  'ef' => 0.285,  'ef_source' => 'ICAO 2023',
                'help' => 'Personelin iş amaçlı uçuş mesafesi toplamı (km). Bilet rezervasyonlarından; gidiş+dönüş dahil toplam km. ICAO hesaplayıcı kullanılabilir.'],
        ],
    ];

    const STANDARDS = [
        'ISO 14064-1'      => 'ISO 14064-1:2018 - Sera Gazı Hesaplama & Raporlama',
        'GHG_Protocol'     => 'GHG Protocol Corporate Standard',
        'HCMI'             => 'HCMI - Hotel Carbon Measurement Initiative',
        'ISO 14001'        => 'ISO 14001:2015 - Çevre Yönetim Sistemi',
        'ISO 50001'        => 'ISO 50001:2018 - Enerji Yönetim Sistemi',
        'CSRD'             => 'CSRD - AB Kurumsal Sürdürülebilirlik Raporlama Direktifi',
        'EU_Taxonomy'      => 'EU Taxonomy for Sustainable Finance',
        'GRI_305'          => 'GRI 305 - Emisyonlar',
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
