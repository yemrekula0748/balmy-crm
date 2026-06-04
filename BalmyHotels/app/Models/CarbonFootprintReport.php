<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarbonFootprintReport extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'title', 'report_type',
        'hotel_name', 'location',
        'period_start', 'period_end',
        'total_guests', 'occupied_rooms', 'total_rooms', 'total_beds', 'staff_count',
        'female_staff_count', 'male_staff_count', 'total_area_sqm', 'open_area_sqm',
        'occupancy_rate', 'average_stay_days',
        'total_co2_scope1', 'total_co2_scope2', 'total_co2_scope3', 'total_co2_total',
        'co2_per_guest', 'co2_per_room_night', 'co2_per_sqm', 'co2_per_staff',
        'hcmi_score', 'hcmi_rating', 'renewable_energy_pct', 'waste_recycling_rate', 'water_intensity',
        'standards_applied', 'factor_dataset_version', 'methodology_notes',
        'verification_notes', 'iso_14001_notes', 'improvement_notes',
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
        'total_beds'          => 'integer',
        'female_staff_count'  => 'integer',
        'male_staff_count'    => 'integer',
        'open_area_sqm'       => 'float',
        'occupancy_rate'      => 'float',
        'average_stay_days'   => 'float',
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
            'energy_lng'       => ['label' => 'LNG (Sıvılaştırılmış Doğal Gaz)', 'unit' => 'ton', 'ef' => 2748.0, 'ef_source' => 'IPCC AR6 / UK Gov 2025',
                'help' => 'LNG tankından kullanılan ton cinsinden miktar. Dolum/teslimat irsaliyesi veya fatura toplamından hesaplanır. 1 m³ LNG ≈ 0.441 ton.'],
            'energy_fuel_oil'  => ['label' => 'Fuel Oil',                         'unit' => 'L',   'ef' => 2.967,  'ef_source' => 'IPCC AR6 2023',
                'help' => 'Kazan veya jeneratör fuel oil tüketimi (L). Yakıt faturası veya depo dolum/azalma kayıtlarından hesaplanır.'],
            'energy_lpg'       => ['label' => 'LPG Tüketimi',                     'unit' => 'L',   'ef' => 1.557,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'LPG tüpleri veya depo sayaçlarından ölçülen tüketim (L). Mutfak ve ısıtma kullanımını kapsar. 1 kg LPG ≈ 1.96 L.'],
            'energy_coal'      => ['label' => 'Kömür Tüketimi',                   'unit' => 'kg',  'ef' => 2.421,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Kazan veya ısıtma sistemine dönemde yüklenen kömür miktarı (kg). Satın alma faturasından veya tartım kayıtlarından.'],
            'transport_diesel' => ['label' => 'Şirket Araçları — Motorin',        'unit' => 'L',   'ef' => 2.661,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Otele ait araçların (servis, minibüs, kamyonet) motorin tüketimi (L). Yakıt kartı ekstresi veya akaryakıt faturası dökümünden.'],
            'generator_diesel' => ['label' => 'Jeneratör Yakıtı',                 'unit' => 'L',   'ef' => 2.661,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Jeneratör için kullanılan motorin miktarı (L). Yakıt faturası, depo sayacı veya dolum fişinden alınır.'],
            'transport_petrol' => ['label' => 'Şirket Araçları — Benzin',         'unit' => 'L',   'ef' => 2.314,  'ef_source' => 'UK Gov GHG Factors 2025',
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
            'energy_electricity'      => ['label' => 'Toplam Elektrik Tüketimi — Şebeke',                         'unit' => 'kWh', 'ef' => 0.469, 'ef_source' => 'ETKB EVÇED 2023 Dağıtım',
                'help' => 'Elektrik dağıtım şebekesinden çekilen toplam kWh. ETKB/EVÇED 2023 tüketim noktası dağıtım hattı faktörü: 0,469 tCO2e/MWh = 0,469 kgCO2e/kWh.'],
            'energy_electricity_re'   => ['label' => 'Yenilenebilir Enerji — Sertifikasız LCA',                   'unit' => 'kWh', 'ef' => 0.017, 'ef_source' => 'IEA/DEFRA LCA varsayımı',
                'help' => 'Şebeke bağlantılı ancak sertifikasız yenilenebilir kaynaklı elektrik için yalnızca açıklama/varsayım amaçlı kalem. I-REC/YEK-G yoksa şebeke kaleminden düşmeyin.'],
            // Market-Based Yaklaşım (ISO 14064-1 §8.3)
            'energy_electricity_irec' => ['label' => 'Elektrik — I-REC / YEK-G / GoO Sertifikalı (Market-Based)', 'unit' => 'kWh', 'ef' => 0.0,   'ef_source' => 'ISO 14064-1 Market-Based / RE100',
                'help' => 'I-REC, YEK-G veya GoO belgesiyle ispatlanan yenilenebilir elektrik miktarı (kWh). EF=0 uygulanır. Sertifika belgesi denetçiye ibraz edilmelidir (ISO 14064-1 §8.3.3).'],
            'energy_onsite_solar'     => ['label' => 'Tesis İçi Üretim — Çatı GES / Rüzgar (Market-Based)',      'unit' => 'kWh', 'ef' => 0.0,   'ef_source' => 'ISO 14064-1 Market-Based / GHG Protocol',
                'help' => 'Kendi çatı GES veya tesis içi rüzgar türbininden üretilerek tüketilen kWh. Üretim sayacından okunur. EF=0 uygulanır; mahsuplaşma ile Scope 2 emisyonunuzu azaltır.'],
            'district_cooling'        => ['label' => 'Merkezi Soğutma',                                           'unit' => 'kWh', 'ef' => 0.250, 'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Bölgesel soğutma ağından veya merkezi chiller istasyonundan alınan soğutma enerjisi (kWh). Enerji sayacı veya sağlayıcı faturasından.'],
            'district_heating'        => ['label' => 'Merkezi Isıtma',                                            'unit' => 'kWh', 'ef' => 0.180, 'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Bölgesel ısı ağından veya buhar istasyonundan alınan ısı enerjisi (kWh). Doğalgaz dağıtım şirketi ısı satışı veya buhar sayacından.'],
        ],
        // Scope 3 — Diğer dolaylı emisyonlar
        'scope3' => [
            'water_municipal'         => ['label' => 'Şebeke Suyu',                  'unit' => 'm³',  'ef' => 0.344,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Su idaresi faturasındaki toplam tüketim (m³). Yüzme havuzu, sulama, mutfak, misafir odaları, çamaşırhane dahil tesisteki tüm su kullanımını girin.'],
            'water_well'              => ['label' => 'Kuyu Suyu',                    'unit' => 'm³',  'ef' => 0.000,  'ef_source' => 'ISO 14001 izleme',
                'help' => 'Kuyu suyu çekimi (m³). Pompa elektriği Scope 2 içinde hesaplanıyorsa EF=0 bırakılır; su çekim izni ve sayaç kaydı denetim kanıtıdır.'],
            'water_wastewater'        => ['label' => 'Atık Su İşleme',               'unit' => 'm³',  'ef' => 0.708,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Atıksu arıtma tesisine gönderilen su miktarı (m³). Atıksu faturanız yoksa şebeke suyu × 0.80 katsayısı ile tahmin edin.'],
            'waste_general'           => ['label' => 'Karışık Atık (Çöp)',           'unit' => 'kg',  'ef' => 0.490,  'ef_source' => 'IPCC 2019',
                'help' => 'Ayrıştırılmamış karışık çöp miktarı (kg). Taşıyıcı firma kantar fişlerinden veya konteyner hacim × doluluk oranı tahmini ile hesaplanır.'],
            'waste_plastic'           => ['label' => 'Plastik Atık',                 'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Geri dönüşüme gönderilen plastik atık kg. Kantar fişi veya geri dönüşüm firma formu ile desteklenmelidir.'],
            'waste_glass'             => ['label' => 'Cam Atık',                     'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Geri dönüşüme gönderilen cam atık kg.'],
            'waste_paper'             => ['label' => 'Kağıt Atık',                   'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Geri dönüşüme gönderilen kağıt/karton atık kg.'],
            'waste_metal'             => ['label' => 'Metal Atık',                   'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Geri dönüşüme gönderilen metal atık kg.'],
            'waste_food'              => ['label' => 'Gıda Atığı',                   'unit' => 'kg',  'ef' => 2.530,  'ef_source' => 'IPCC 2019',
                'help' => 'Mutfaktan çıkan hazırlanmamış+tabak artığı gıda miktarı (kg). Çöplükte metan (CH₄) ürettiği için yüksek emisyon faktörüne sahiptir. Günlük tartım tutanaklarından.'],
            'waste_organic'           => ['label' => 'Organik Atık',                 'unit' => 'kg',  'ef' => 0.490,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Gıda dışı organik atık veya ayrıştırılmış organik atık kg. Kompost yapılan miktarı kompost kalemine taşıyın.'],
            'waste_recycled'          => ['label' => 'Geri Dönüştürülen Atık',       'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Kağıt, cam, plastik, metal olarak ayrıştırılan ve geri dönüşüme gönderilen toplam atık (kg). Geri dönüşüm firması kantar fişlerinden.'],
            'waste_compost'           => ['label' => 'Kompost Atığı',                'unit' => 'kg',  'ef' => 0.010,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Organik atıktan kompost yapılan miktar (kg). Bu kaleme taşıdığınız miktarı Gıda Atığı kaleminden düşürün; çift sayımı önler.'],
            'waste_hazardous'         => ['label' => 'Kimyasal / Tehlikeli Atık',    'unit' => 'kg',  'ef' => 0.500,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Tehlikeli atık beyanı, MOTAT/Ulusal Atık Taşıma Formu veya lisanslı firma teslim formu ile desteklenmelidir.'],
            'waste_oil'               => ['label' => 'Atık Yağ',                     'unit' => 'L',   'ef' => 0.000,  'ef_source' => 'ISO 14001 izleme',
                'help' => 'Lisanslı firmaya verilen atık yağ litre. Karbon hesabından çok ISO 14001 yasal uygunluk/atık yönetimi takibidir.'],
            'waste_electronic'        => ['label' => 'Elektronik Atık',              'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'E-atık teslim miktarı kg. Lisanslı firma teslim belgesi açıklama alanına girilmelidir.'],
            'waste_battery'           => ['label' => 'Pil Atığı',                    'unit' => 'kg',  'ef' => 0.021,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Atık pil kg. TAP/teslim formu veya iç toplama tutanağı kanıt olarak saklanmalıdır.'],
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
            'food_meat_total'         => ['label' => 'Et Ürünleri',                  'unit' => 'kg',  'ef' => 12.00,  'ef_source' => 'FAO/IPCC ortalama',
                'help' => 'Et ürünleri toplam kg. Eğer dana/tavuk/balık ayrı giriliyorsa bu satırı 0 bırakın; çift sayımı önler.'],
            'food_produce'            => ['label' => 'Sebze Meyve',                  'unit' => 'kg',  'ef' => 0.78,   'ef_source' => 'FAO/IPCC ortalama',
                'help' => 'Sebze, meyve ve bitkisel ürün tedariki kg.'],
            'beverage_procurement'    => ['label' => 'İçecek Tedariki',              'unit' => 'L',   'ef' => 0.50,   'ef_source' => 'Scope 3 tedarik varsayımı',
                'help' => 'İçecek satın alma litre. Ürün bazlı tedarikçi faktörü yoksa düşük güven seviyeli genel Scope 3 varsayımıdır; açıklama alanına tedarikçi/fatura notu girin.'],
            'transport_staff'         => ['label' => 'Personel Ulaşımı',             'unit' => 'km',  'ef' => 0.192,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Personel servis araçlarının dönemde kat ettiği toplam km. Hesap: güzergah km × günlük sefer × iş günü sayısı.'],
            'transport_guest_shuttle' => ['label' => 'Misafir Shuttle (Transfer)',   'unit' => 'km',  'ef' => 0.089,  'ef_source' => 'UK Gov GHG Factors 2025',
                'help' => 'Havalimanı/şehir transfer aracınızın dönemdeki toplam km. Rezervasyon kayıtlarından; gidiş+dönüş dahil toplam mesafe.'],
            'procurement_cleaning'    => ['label' => 'Temizlik Kimyasalları',        'unit' => 'kg',  'ef' => 3.100,  'ef_source' => 'Ecoinvent 3.9',
                'help' => 'Satın alınan tüm temizlik kimyasalları toplamı (kg): deterjan, dezenfektan, yüzey temizleyici, halı şampuanı vb. Tedarikçi faturalarından.'],
            'procurement_cleaning_litre' => ['label' => 'Kimyasal Kullanımı',        'unit' => 'L',   'ef' => 3.100,  'ef_source' => 'Ecoinvent 3.9 varsayım',
                'help' => 'Temizlik kimyasalları litre. Yoğunluk bilinmiyorsa kg eşdeğeri varsayımı kullanılır; mümkünse MSDS/tedarikçi kg bilgisi ekleyin.'],
            'procurement_linen'       => ['label' => 'Tekstil Alımı (Yeni Çarşaf/Havlu)', 'unit' => 'kg', 'ef' => 15.00, 'ef_source' => 'Ecoinvent 3.9',
                'help' => 'Dönemde SATIN ALINAN yeni çarşaf, havlu, bornoz, masa örtüsü (kg). Yıkama miktarı değil, yalnızca yeni alım miktarıdır.'],
            'laundry_onsite'          => ['label' => 'Çamaşırhane — Tesis İçi',     'unit' => 'kg',  'ef' => 0.0,    'ef_source' => 'GHG Protocol — kapsam çakışması',
                'help' => 'Tesis içi çamaşırhanenin yıkadığı çamaşır miktarı (kg). Doğal gaz ve elektrik tüketimleri zaten Scope 1/2\'de kayıtlıysa burayı 0 bırakın — çift sayımı önler.'],
            'laundry_external'        => ['label' => 'Çamaşırhane — Dış Servis',    'unit' => 'kg',  'ef' => 0.540,  'ef_source' => 'UK Gov 2025 / Ecoinvent 3.9',
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
        'ISO 14001'        => 'ISO 14001:2026 - Çevre Yönetim Sistemi',
        'ISO 50001'        => 'ISO 50001:2018 - Enerji Yönetim Sistemi',
        'CSRD'             => 'CSRD - AB Kurumsal Sürdürülebilirlik Raporlama Direktifi',
        'EU_Taxonomy'      => 'EU Taxonomy for Sustainable Finance',
        'GRI_305'          => 'GRI 305 - Emisyonlar',
        'SASB_Hospitality' => 'SASB Hotels & Lodging Sektör Standardı',
        'ESRS_E1'          => 'ESRS E1 - İklim Değişikliği',
    ];

    const DEFAULT_FACTOR_DATASET_VERSION = 'ETKB EVÇED 2023 elektrik faktörleri; UK Government GHG Conversion Factors 2025; HCMI v2.0; GHG Protocol';

    const SOURCE_REFERENCES = [
        [
            'code' => 'ETKB_EVÇED_ELECTRICITY_2023',
            'title' => 'T.C. Enerji ve Tabii Kaynaklar Bakanlığı - Türkiye Elektrik Üretimi ve Elektrik Tüketim Noktası Emisyon Faktörleri',
            'url' => 'https://enerji.gov.tr/evced-cevre-ve-iklim-elektrik-uretim-tuketim-emisyon-faktorleri',
            'summary' => 'Türkiye elektrik tüketimi kaynaklı Kapsam 2 hesaplarında tüketim noktası emisyon faktörü referansı. Dağıtım hattından bağlı tüketim için 0,469 tCO2e/MWh bilgisi kullanılır.',
        ],
        [
            'code' => 'UK_GHG_FACTORS_2025',
            'title' => 'UK Government GHG Conversion Factors 2025',
            'url' => 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
            'summary' => 'Yakıt, su, atık, ulaşım ve satın alma kalemlerinde uluslararası kurum raporlaması için kullanılan resmi dönüşüm faktörü seti.',
        ],
        [
            'code' => 'GHG_PROTOCOL',
            'title' => 'GHG Protocol Corporate Accounting and Reporting Standard',
            'url' => 'https://ghgprotocol.org/corporate-standard',
            'summary' => 'Scope 1, Scope 2 ve Scope 3 sınıflandırması ile kurumsal sera gazı envanteri yaklaşımı.',
        ],
        [
            'code' => 'ISO_14064_1',
            'title' => 'ISO 14064-1:2018',
            'url' => 'https://www.iso.org/standard/66453.html',
            'summary' => 'Kuruluş düzeyinde sera gazı emisyonlarının nicelendirilmesi, raporlanması ve doğrulanması için prensip ve gereklilikler.',
        ],
        [
            'code' => 'HCMI',
            'title' => 'Hotel Carbon Measurement Initiative (HCMI)',
            'url' => 'https://sustainablehospitalityalliance.org/resource/hotel-carbon-measurement-initiative/',
            'summary' => 'Oteller için konaklama ve toplantı kaynaklı karbon ayak izinin tutarlı hesaplanmasına yönelik sektör metodolojisi.',
        ],
        [
            'code' => 'ISO_14001',
            'title' => 'ISO 14001 Çevre Yönetim Sistemi',
            'url' => 'https://www.iso.org/standard/92300.html',
            'summary' => 'Çevresel performans, yasal uygunluk, risk ve sürekli iyileştirme takibi için yönetim sistemi çerçevesi.',
        ],
        [
            'code' => 'ISO_50001',
            'title' => 'ISO 50001:2018 Enerji Yönetim Sistemi',
            'url' => 'https://www.iso.org/standard/69426.html',
            'summary' => 'Enerji performans göstergeleri, enerji baz çizgisi ve sürekli iyileştirme takibi için çerçeve.',
        ],
        [
            'code' => 'GRI_305',
            'title' => 'GRI 305: Emissions 2016',
            'url' => 'https://www.globalreporting.org/publications/documents/english/gri-305-emissions-2016/',
            'summary' => 'Scope 1, Scope 2, Scope 3 emisyonları ve emisyon yoğunluğu açıklamaları için GRI konu standardı.',
        ],
        [
            'code' => 'CSRD_ESRS',
            'title' => 'CSRD / ESRS',
            'url' => 'https://finance.ec.europa.eu/financial-markets/company-reporting-and-auditing/company-reporting/corporate-sustainability-reporting_en',
            'summary' => 'AB sürdürülebilirlik raporlaması; ESRS kapsamında iklim, enerji, emisyon ve metrik açıklamalarını destekler.',
        ],
        [
            'code' => 'SASB_HOTELS',
            'title' => 'SASB Hotels & Lodging',
            'url' => 'https://www.ifrs.org/issued-standards/sasb-standards/',
            'summary' => 'Otelcilik sektöründe yatırımcı odaklı, sektöre özgü sürdürülebilirlik açıklamaları için kullanılır.',
        ],
    ];

    const INPUT_SCHEMA = [
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Otel Bilgisi', 'name' => 'Otel Adı', 'unit' => 'Metin', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Otel Bilgisi', 'name' => 'Lokasyon', 'unit' => 'Metin', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Oda Sayısı', 'unit' => 'Adet', 'standard' => 'HCMI', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Yatak Sayısı', 'unit' => 'Adet', 'standard' => 'HCMI', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Kapalı Alan', 'unit' => 'm²', 'standard' => 'ISO 50001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Açık Alan', 'unit' => 'm²', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Enerji', 'sub_category' => 'Elektrik', 'name' => 'Toplam Elektrik Tüketimi', 'unit' => 'kWh', 'standard' => 'ISO 50001 / GHG', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Elektrik', 'name' => 'Yenilenebilir Enerji', 'unit' => 'kWh', 'standard' => 'ESG / ISO 50001', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Yakıt', 'name' => 'LPG, Motorin, Kömür, Jeneratör Yakıtı', 'unit' => 'L / kg', 'standard' => 'GHG Protocol', 'frequency' => 'aylık'],
        ['category' => 'Su', 'sub_category' => 'Tüketim', 'name' => 'Şebeke / Kuyu / Toplam Su', 'unit' => 'm³', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Tüm Atıklar', 'name' => 'Evsel, geri dönüşüm, organik, tehlikeli atıklar', 'unit' => 'kg / L', 'standard' => 'ISO 14001 / GRI', 'frequency' => 'aylık'],
        ['category' => 'Satın Alma', 'sub_category' => 'Gıda / Kimyasal / Tekstil', 'name' => 'Et, sebze-meyve, içecek, kimyasal, linen', 'unit' => 'kg / L', 'standard' => 'ESG / Scope 3', 'frequency' => 'aylık'],
        ['category' => 'KPI', 'sub_category' => 'Karbon', 'name' => 'kgCO2e / Occupied Room, kgCO2e / m²', 'unit' => 'Oran', 'standard' => 'HCMI / GRI', 'frequency' => 'hesaplanan'],
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

    public static function categoryDefinition(?string $category): ?array
    {
        foreach (self::CATEGORIES as $scopeCategories) {
            if (isset($scopeCategories[$category])) {
                return $scopeCategories[$category];
            }
        }

        return null;
    }

    public static function calculationMethodFor(?string $category): string
    {
        $definition = self::categoryDefinition($category);

        return $definition['calculation'] ?? 'CO2e (kg) = Faaliyet verisi x emisyon faktörü';
    }

    public static function standardForCategory(?string $category, ?int $scope = null): string
    {
        $category = (string) $category;

        if (str_starts_with($category, 'water_') || str_starts_with($category, 'waste_')) {
            return 'ISO 14001 / GRI 305';
        }

        if (str_starts_with($category, 'energy_') || str_starts_with($category, 'district_')) {
            return $scope === 2 ? 'ISO 50001 / GHG Scope 2' : 'ISO 50001 / GHG Scope 1';
        }

        if (str_starts_with($category, 'food_') || str_starts_with($category, 'procurement_') || str_starts_with($category, 'beverage_')) {
            return 'GHG Scope 3 / ESG';
        }

        if (str_starts_with($category, 'transport_') || str_starts_with($category, 'laundry_')) {
            return 'HCMI / GHG Scope 3';
        }

        if (str_starts_with($category, 'refrigerant_')) {
            return 'GHG Scope 1 / ISO 14064-1';
        }

        return match ($scope) {
            1 => 'GHG Scope 1 / ISO 14064-1',
            2 => 'GHG Scope 2 / ISO 14064-1',
            3 => 'GHG Scope 3 / GRI 305',
            default => 'GHG Protocol',
        };
    }

    public function auditChecks(): array
    {
        $days = $this->period_start && $this->period_end
            ? max(1, $this->period_start->diffInDays($this->period_end) + 1)
            : 1;
        $maxRoomNights = (int) $this->total_rooms * $days;
        $calculatedOccupancy = $maxRoomNights > 0
            ? round(((int) $this->occupied_rooms / $maxRoomNights) * 100, 2)
            : null;
        $entries = $this->relationLoaded('entries') ? $this->entries : $this->entries()->get();
        $positiveEntries = $entries->filter(fn ($entry) => (float) $entry->quantity > 0 || (float) $entry->co2_kg > 0);
        $entriesWithoutSource = $positiveEntries->filter(fn ($entry) => empty($entry->ef_source));
        $entriesWithoutExplanation = $positiveEntries->filter(fn ($entry) => empty($entry->notes));
        $sumTotal = round((float) $entries->sum('co2_kg'), 3);

        return [
            [
                'label' => 'Dönem kontrolü',
                'ok' => $this->period_start && $this->period_end && $this->period_end->greaterThanOrEqualTo($this->period_start),
                'detail' => $this->period_start && $this->period_end
                    ? $this->period_start->format('d.m.Y') . ' - ' . $this->period_end->format('d.m.Y')
                    : 'Dönem girilmemiş.',
            ],
            [
                'label' => 'Oda-gece kapasite kontrolü',
                'ok' => $maxRoomNights === 0 || (int) $this->occupied_rooms <= $maxRoomNights,
                'detail' => $maxRoomNights > 0
                    ? "Girilen oda-gece: {$this->occupied_rooms}; maksimum teorik: {$maxRoomNights}; hesaplanan doluluk: %{$calculatedOccupancy}"
                    : 'Toplam oda sayısı girilmediği için kapasite kontrolü sınırlı.',
            ],
            [
                'label' => 'Emisyon toplamı kontrolü',
                'ok' => abs($sumTotal - round((float) $this->total_co2_total, 3)) <= 0.01,
                'detail' => 'Kalem toplamı: ' . number_format($sumTotal, 3) . ' kgCO2e; rapor toplamı: ' . number_format((float) $this->total_co2_total, 3) . ' kgCO2e.',
            ],
            [
                'label' => 'Faktör kaynak kontrolü',
                'ok' => $entriesWithoutSource->isEmpty(),
                'detail' => $entriesWithoutSource->isEmpty()
                    ? 'Pozitif tüm kalemlerde emisyon faktörü kaynağı mevcut.'
                    : $entriesWithoutSource->count() . ' pozitif kalemde faktör kaynağı eksik.',
            ],
            [
                'label' => 'Açıklama / kanıt izi kontrolü',
                'ok' => $entriesWithoutExplanation->isEmpty(),
                'detail' => $entriesWithoutExplanation->isEmpty()
                    ? 'Pozitif tüm kalemlerde açıklama girilmiş.'
                    : $entriesWithoutExplanation->count() . ' pozitif kalemde açıklama alanı boş. Denetim için fatura/sayaç/varsayım notu önerilir.',
            ],
        ];
    }
}
