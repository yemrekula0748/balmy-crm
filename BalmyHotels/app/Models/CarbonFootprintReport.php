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
        // Kullanıcının verdiği veri seti: eski karbon kalemleri formdan çıkarıldı.
        'scope1' => [
            'energy_lpg' => [
                'label' => 'LPG Tüketimi', 'category_group' => 'Enerji', 'sub_category' => 'Yakıt',
                'unit' => 'Litre', 'ef' => 1.557, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'GHG Protocol / ISO 14064-1', 'frequency' => 'aylık',
                'help' => 'LPG kullanımı. Hesap: litre x 1,557 kgCO2e/litre.',
            ],
            'energy_diesel' => [
                'label' => 'Motorin Tüketimi', 'category_group' => 'Enerji', 'sub_category' => 'Yakıt',
                'unit' => 'Litre', 'ef' => 2.661, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'GHG Protocol / ISO 14064-1', 'frequency' => 'aylık',
                'help' => 'Motorin tüketimi. Servis/araç yakıtı bu satırdan hesaplanır.',
            ],
            'energy_coal' => [
                'label' => 'Kömür Tüketimi', 'category_group' => 'Enerji', 'sub_category' => 'Yakıt',
                'unit' => 'Kg', 'ef' => 2.421, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'GHG Protocol / ISO 14064-1', 'frequency' => 'aylık',
                'help' => 'Kömür tüketimi. Tartım/fatura bilgisiyle girilmelidir.',
            ],
            'generator_diesel' => [
                'label' => 'Jeneratör Yakıtı', 'category_group' => 'Enerji', 'sub_category' => 'Yakıt',
                'unit' => 'Litre', 'ef' => 2.661, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'GHG Protocol / ISO 14064-1', 'frequency' => 'aylık',
                'help' => 'Jeneratöre ait motorin tüketimi. Araç motoriniyle çift sayılmamalıdır.',
            ],
            'carbon_refrigerant_manual' => [
                'label' => 'Soğutucu Gaz Emisyonu', 'category_group' => 'Karbon', 'sub_category' => 'Scope 1',
                'unit' => 'tCO2e', 'ef' => 1000, 'ef_source' => 'GHG Protocol / IPCC GWP',
                'standard' => 'GHG Protocol / ISO 14064-1', 'frequency' => 'hesaplanan',
                'help' => 'Bakım firmasından kg gaz ve GWP ile hesaplanmış tCO2e varsa girin. Sistem kgCO2e için 1000 ile çarpar.',
            ],
        ],
        'scope2' => [
            'energy_electricity' => [
                'label' => 'Toplam Elektrik Tüketimi', 'category_group' => 'Enerji', 'sub_category' => 'Elektrik',
                'unit' => 'kWh', 'ef' => 0.469, 'ef_source' => 'ETKB EVÇED 2023 Dağıtım',
                'standard' => 'ISO 50001 / GHG Scope 2', 'frequency' => 'aylık',
                'help' => 'Aylık toplam elektrik tüketimi. ETKB/EVÇED 2023 dağıtım tüketim noktası faktörü: 0,469 kgCO2e/kWh.',
            ],
            'energy_renewable' => [
                'label' => 'Yenilenebilir Enerji', 'category_group' => 'Enerji', 'sub_category' => 'Elektrik',
                'unit' => 'kWh', 'ef' => 0, 'ef_source' => 'ISO 14064-1 Market-Based / GHG Protocol',
                'standard' => 'ISO 50001 / ESG', 'frequency' => 'aylık',
                'help' => 'GES, YEK-G, I-REC veya benzeri kanıtlı yenilenebilir üretim/tüketim. Karbon toplamına eklenmez; yenilenebilir oranı için kullanılır.',
            ],
        ],
        'scope3' => [
            'water_total' => [
                'label' => 'Toplam Su Tüketimi', 'category_group' => 'Su', 'sub_category' => 'Tüketim',
                'unit' => 'm3', 'ef' => 0, 'ef_source' => 'ISO 14001 izleme',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'Aylık toplam su tüketimi. Karbon toplamına eklenmez; su KPI ve ISO 14001 takibi için kullanılır.',
            ],
            'water_municipal' => [
                'label' => 'Şebeke Suyu', 'category_group' => 'Su', 'sub_category' => 'Tüketim',
                'unit' => 'm3', 'ef' => 0.344, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ISO 14001 / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'Belediye/şebeke suyu. Su temini kaynaklı Scope 3 etkisi için hesaplanır.',
            ],
            'water_well' => [
                'label' => 'Kuyu Suyu', 'category_group' => 'Su', 'sub_category' => 'Tüketim',
                'unit' => 'm3', 'ef' => 0, 'ef_source' => 'ISO 14001 izleme',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'Kuyu suyu çekimi. Pompa elektriği elektrik satırında hesaplandığı için karbon çarpanı uygulanmaz.',
            ],
            'waste_general' => [
                'label' => 'Evsel Atık Miktarı', 'category_group' => 'Atık', 'sub_category' => 'Evsel Atık',
                'unit' => 'Kg', 'ef' => 0.490, 'ef_source' => 'UK Gov GHG Factors 2025 / IPCC',
                'standard' => 'ISO 14001 / GRI 305', 'frequency' => 'aylık',
                'help' => 'Karışık evsel atık miktarı.',
            ],
            'waste_plastic' => [
                'label' => 'Plastik Atık', 'category_group' => 'Atık', 'sub_category' => 'Geri Dönüşüm',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ESG / GRI 305', 'frequency' => 'aylık',
                'help' => 'Geri dönüşüme gönderilen plastik atık.',
            ],
            'waste_glass' => [
                'label' => 'Cam Atık', 'category_group' => 'Atık', 'sub_category' => 'Geri Dönüşüm',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ESG / GRI 305', 'frequency' => 'aylık',
                'help' => 'Geri dönüşüme gönderilen cam atık.',
            ],
            'waste_paper' => [
                'label' => 'Kağıt Atık', 'category_group' => 'Atık', 'sub_category' => 'Geri Dönüşüm',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ESG / GRI 305', 'frequency' => 'aylık',
                'help' => 'Geri dönüşüme gönderilen kağıt/karton atık.',
            ],
            'waste_metal' => [
                'label' => 'Metal Atık', 'category_group' => 'Atık', 'sub_category' => 'Geri Dönüşüm',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ESG / GRI 305', 'frequency' => 'aylık',
                'help' => 'Geri dönüşüme gönderilen metal atık.',
            ],
            'waste_organic' => [
                'label' => 'Organik Atık', 'category_group' => 'Atık', 'sub_category' => 'Organik',
                'unit' => 'Kg', 'ef' => 0.490, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ESG / GRI 305', 'frequency' => 'aylık',
                'help' => 'Organik/gıda atığı miktarı.',
            ],
            'waste_hazardous' => [
                'label' => 'Kimyasal Atık', 'category_group' => 'Atık', 'sub_category' => 'Tehlikeli Atık',
                'unit' => 'Kg', 'ef' => 0.500, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'Lisanslı firma teslim formu ile izlenmesi gereken tehlikeli/kimyasal atık.',
            ],
            'waste_oil' => [
                'label' => 'Atık Yağ', 'category_group' => 'Atık', 'sub_category' => 'Tehlikeli Atık',
                'unit' => 'Litre', 'ef' => 0, 'ef_source' => 'ISO 14001 izleme',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'Lisanslı firmaya verilen atık yağ. Karbon toplamından çok yasal uygunluk takibi içindir.',
            ],
            'waste_electronic' => [
                'label' => 'Elektronik Atık', 'category_group' => 'Atık', 'sub_category' => 'Tehlikeli Atık',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'E-atık teslim miktarı.',
            ],
            'waste_battery' => [
                'label' => 'Pil Atığı', 'category_group' => 'Atık', 'sub_category' => 'Tehlikeli Atık',
                'unit' => 'Kg', 'ef' => 0.021, 'ef_source' => 'UK Gov GHG Factors 2025',
                'standard' => 'ISO 14001', 'frequency' => 'aylık',
                'help' => 'Pil atığı teslim/toplama miktarı.',
            ],
            'food_meat_total' => [
                'label' => 'Et Ürünleri', 'category_group' => 'Satın Alma', 'sub_category' => 'Gıda',
                'unit' => 'Kg', 'ef' => 12.000, 'ef_source' => 'FAO/IPCC ortalama',
                'standard' => 'ESG / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'Et ürünleri toplam alımı. Ürün bazlı detay yoksa ortalama tedarik faktörü kullanılır.',
            ],
            'food_produce' => [
                'label' => 'Sebze Meyve', 'category_group' => 'Satın Alma', 'sub_category' => 'Gıda',
                'unit' => 'Kg', 'ef' => 0.780, 'ef_source' => 'FAO/IPCC ortalama',
                'standard' => 'ESG / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'Sebze, meyve ve bitkisel ürün alımı.',
            ],
            'beverage_procurement' => [
                'label' => 'İçecek', 'category_group' => 'Satın Alma', 'sub_category' => 'Gıda',
                'unit' => 'Litre', 'ef' => 0.500, 'ef_source' => 'Scope 3 tedarik varsayımı',
                'standard' => 'ESG / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'İçecek alımı. Tedarikçi ürün bazlı faktör sağlarsa açıklama alanına not düşülmelidir.',
            ],
            'procurement_cleaning_litre' => [
                'label' => 'Kimyasal Kullanımı', 'category_group' => 'Satın Alma', 'sub_category' => 'Temizlik',
                'unit' => 'Litre', 'ef' => 3.100, 'ef_source' => 'Ecoinvent 3.9 varsayım',
                'standard' => 'ISO 14001 / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'Temizlik kimyasalı kullanımı. MSDS/tedarikçi belgesi varsa açıklama alanına eklenmelidir.',
            ],
            'procurement_linen' => [
                'label' => 'Çamaşır / Linen', 'category_group' => 'Satın Alma', 'sub_category' => 'Tekstil',
                'unit' => 'Kg', 'ef' => 15.000, 'ef_source' => 'Ecoinvent 3.9',
                'standard' => 'ESG / GHG Scope 3', 'frequency' => 'aylık',
                'help' => 'Yeni çarşaf, havlu, bornoz vb. tekstil alımı.',
            ],
            'carbon_procurement_manual' => [
                'label' => 'Satın Alma Emisyonu', 'category_group' => 'Karbon', 'sub_category' => 'Scope 3',
                'unit' => 'tCO2e', 'ef' => 1000, 'ef_source' => 'GHG Protocol Scope 3',
                'standard' => 'GHG Scope 3 / GRI 305', 'frequency' => 'hesaplanan',
                'help' => 'Tedarikçi veya ayrı hesaplama dosyasından gelen satın alma emisyonu varsa tCO2e olarak girin. Yukarıdaki satın alma kalemleriyle çift sayılmamalıdır.',
            ],
            'carbon_staff_commute_manual' => [
                'label' => 'Personel Ulaşımı', 'category_group' => 'Karbon', 'sub_category' => 'Scope 3',
                'unit' => 'tCO2e', 'ef' => 1000, 'ef_source' => 'GHG Protocol Scope 3',
                'standard' => 'GHG Scope 3 / ESRS E1', 'frequency' => 'hesaplanan',
                'help' => 'Personel ulaşımı ayrı hesaplandıysa tCO2e olarak girin.',
            ],
            'carbon_guest_travel_manual' => [
                'label' => 'Misafir Ulaşımı', 'category_group' => 'Karbon', 'sub_category' => 'Scope 3',
                'unit' => 'tCO2e', 'ef' => 1000, 'ef_source' => 'HCMI / GHG Scope 3',
                'standard' => 'HCMI / GHG Scope 3', 'frequency' => 'hesaplanan',
                'help' => 'Misafir ulaşımı HCMI veya ayrı hesap dosyasıyla hesaplandıysa tCO2e olarak girin.',
            ],
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
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Otel Bilgisi', 'name' => 'Otel Adı', 'unit' => 'Metin', 'description' => 'Tesis adı', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Otel Bilgisi', 'name' => 'Lokasyon', 'unit' => 'Metin', 'description' => 'Şehir / Ülke', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Oda Sayısı', 'unit' => 'Adet', 'description' => 'Toplam oda', 'standard' => 'HCMI', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Yatak Sayısı', 'unit' => 'Adet', 'description' => 'Yatak kapasitesi', 'standard' => 'HCMI', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Toplam Kapalı Alan', 'unit' => 'm²', 'description' => 'İç kullanım alanı', 'standard' => 'ISO 50001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Kapasite', 'name' => 'Açık Alan', 'unit' => 'm²', 'description' => 'Açık kullanım alanı', 'standard' => 'ISO 14001', 'frequency' => 'sabit'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Personel', 'name' => 'Toplam Personel Sayısı', 'unit' => 'Kişi', 'description' => 'Ortalama yıllık çalışan', 'standard' => 'ESRS', 'frequency' => 'aylık'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Operasyon', 'name' => 'Doluluk Oranı', 'unit' => '%', 'description' => 'Ortalama occupancy', 'standard' => 'HCMI', 'frequency' => 'aylık'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Operasyon', 'name' => 'Toplam Misafir Sayısı', 'unit' => 'Kişi', 'description' => 'Yıllık guest sayısı', 'standard' => 'HCMI', 'frequency' => 'aylık'],
        ['category' => 'Genel Bilgiler', 'sub_category' => 'Operasyon', 'name' => 'Toplam Geceleme', 'unit' => 'Gece', 'description' => 'Occupied room night', 'standard' => 'HCMI', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Elektrik', 'name' => 'Toplam Elektrik Tüketimi', 'unit' => 'kWh', 'description' => 'Aylık toplam', 'standard' => 'ISO 50001', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Elektrik', 'name' => 'Yenilenebilir Enerji', 'unit' => 'kWh', 'description' => 'GES vb üretim', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Yakıt', 'name' => 'LPG Tüketimi', 'unit' => 'Litre', 'description' => 'LPG kullanımı', 'standard' => 'GHG', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Yakıt', 'name' => 'Motorin Tüketimi', 'unit' => 'Litre', 'description' => 'Diesel usage', 'standard' => 'GHG', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Yakıt', 'name' => 'Kömür Tüketimi', 'unit' => 'Kg', 'description' => 'Coal usage', 'standard' => 'GHG', 'frequency' => 'aylık'],
        ['category' => 'Enerji', 'sub_category' => 'Yakıt', 'name' => 'Jeneratör Yakıtı', 'unit' => 'Litre', 'description' => 'Generator fuel', 'standard' => 'GHG', 'frequency' => 'aylık'],
        ['category' => 'Su', 'sub_category' => 'Tüketim', 'name' => 'Toplam Su Tüketimi', 'unit' => 'm³', 'description' => 'Monthly water', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Su', 'sub_category' => 'Tüketim', 'name' => 'Şebeke Suyu', 'unit' => 'm³', 'description' => 'Municipal water', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Su', 'sub_category' => 'Tüketim', 'name' => 'Kuyu Suyu', 'unit' => 'm³', 'description' => 'Groundwater', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Evsel Atık', 'name' => 'Evsel Atık Miktarı', 'unit' => 'Kg', 'description' => 'Mixed waste', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Geri Dönüşüm', 'name' => 'Plastik Atık', 'unit' => 'Kg', 'description' => 'Plastic waste', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Geri Dönüşüm', 'name' => 'Cam Atık', 'unit' => 'Kg', 'description' => 'Glass waste', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Geri Dönüşüm', 'name' => 'Kağıt Atık', 'unit' => 'Kg', 'description' => 'Paper waste', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Geri Dönüşüm', 'name' => 'Metal Atık', 'unit' => 'Kg', 'description' => 'Metal waste', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Organik', 'name' => 'Organik Atık', 'unit' => 'Kg', 'description' => 'Food waste', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Tehlikeli Atık', 'name' => 'Kimyasal Atık', 'unit' => 'Kg', 'description' => 'Hazardous waste', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Tehlikeli Atık', 'name' => 'Atık Yağ', 'unit' => 'Litre', 'description' => 'Waste oil', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Tehlikeli Atık', 'name' => 'Elektronik Atık', 'unit' => 'Kg', 'description' => 'E-waste', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Atık', 'sub_category' => 'Tehlikeli Atık', 'name' => 'Pil Atığı', 'unit' => 'Kg', 'description' => 'Battery waste', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 1', 'name' => 'Yakıt Kaynaklı Emisyon', 'unit' => 'tCO2e', 'description' => 'Direct emissions', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 1', 'name' => 'Soğutucu Gaz Emisyonu', 'unit' => 'tCO2e', 'description' => 'Refrigerant leakage', 'standard' => 'GHG', 'frequency' => 'hesaplanan / manuel'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 1', 'name' => 'Şirket Araçları Emisyonu', 'unit' => 'tCO2e', 'description' => 'Fleet emissions', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 2', 'name' => 'Elektrik Emisyonu', 'unit' => 'tCO2e', 'description' => 'Purchased electricity', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 3', 'name' => 'Satın Alma Emisyonu', 'unit' => 'tCO2e', 'description' => 'Procurement emissions', 'standard' => 'GHG', 'frequency' => 'hesaplanan / manuel'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 3', 'name' => 'Personel Ulaşımı', 'unit' => 'tCO2e', 'description' => 'Employee commute', 'standard' => 'GHG', 'frequency' => 'hesaplanan / manuel'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 3', 'name' => 'Misafir Ulaşımı', 'unit' => 'tCO2e', 'description' => 'Guest travel', 'standard' => 'HCMI', 'frequency' => 'hesaplanan / manuel'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 3', 'name' => 'Atık Emisyonu', 'unit' => 'tCO2e', 'description' => 'Waste disposal', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'Karbon', 'sub_category' => 'Scope 3', 'name' => 'Su Emisyonu', 'unit' => 'tCO2e', 'description' => 'Water treatment', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'Satın Alma', 'sub_category' => 'Gıda', 'name' => 'Et Ürünleri', 'unit' => 'Kg', 'description' => 'Meat procurement', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Satın Alma', 'sub_category' => 'Gıda', 'name' => 'Sebze Meyve', 'unit' => 'Kg', 'description' => 'Produce procurement', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Satın Alma', 'sub_category' => 'Gıda', 'name' => 'İçecek', 'unit' => 'Litre', 'description' => 'Beverage procurement', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Satın Alma', 'sub_category' => 'Temizlik', 'name' => 'Kimyasal Kullanımı', 'unit' => 'Litre', 'description' => 'Cleaning chemicals', 'standard' => 'ISO 14001', 'frequency' => 'aylık'],
        ['category' => 'Satın Alma', 'sub_category' => 'Tekstil', 'name' => 'Çamaşır / Linen', 'unit' => 'Kg', 'description' => 'Textile procurement', 'standard' => 'ESG', 'frequency' => 'aylık'],
        ['category' => 'Personel', 'sub_category' => 'İnsan Kaynakları', 'name' => 'Kadın Çalışan Sayısı', 'unit' => 'Kişi', 'description' => 'Female employees', 'standard' => 'ESRS', 'frequency' => 'aylık'],
        ['category' => 'Personel', 'sub_category' => 'İnsan Kaynakları', 'name' => 'Erkek Çalışan Sayısı', 'unit' => 'Kişi', 'description' => 'Male employees', 'standard' => 'ESRS', 'frequency' => 'aylık'],
        ['category' => 'Misafir', 'sub_category' => 'Konaklama', 'name' => 'Ortalama Konaklama Süresi', 'unit' => 'Gün', 'description' => 'Average stay', 'standard' => 'HCMI', 'frequency' => 'aylık'],
        ['category' => 'KPI', 'sub_category' => 'Enerji', 'name' => 'kWh / Occupied Room', 'unit' => 'Oran', 'description' => 'Energy KPI', 'standard' => 'HCMI', 'frequency' => 'hesaplanan'],
        ['category' => 'KPI', 'sub_category' => 'Su', 'name' => 'Litre / Guest Night', 'unit' => 'Oran', 'description' => 'Water KPI', 'standard' => 'HCMI', 'frequency' => 'hesaplanan'],
        ['category' => 'KPI', 'sub_category' => 'Atık', 'name' => 'Kg Atık / Guest', 'unit' => 'Oran', 'description' => 'Waste KPI', 'standard' => 'ESG', 'frequency' => 'hesaplanan'],
        ['category' => 'KPI', 'sub_category' => 'Karbon', 'name' => 'KgCO2e / Occupied Room', 'unit' => 'Oran', 'description' => 'Carbon KPI', 'standard' => 'HCMI', 'frequency' => 'hesaplanan'],
        ['category' => 'KPI', 'sub_category' => 'Karbon', 'name' => 'KgCO2e / m²', 'unit' => 'Oran', 'description' => 'Area carbon KPI', 'standard' => 'GHG', 'frequency' => 'hesaplanan'],
        ['category' => 'KPI', 'sub_category' => 'Enerji', 'name' => 'Yenilenebilir Enerji Oranı', 'unit' => '%', 'description' => 'Renewable ratio', 'standard' => 'ESG', 'frequency' => 'hesaplanan'],
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

    private function metricEntries()
    {
        return $this->relationLoaded('entries') ? $this->entries : $this->entries()->get();
    }

    private function metricQuantity(array $categories): float
    {
        return (float) $this->metricEntries()->whereIn('category', $categories)->sum('quantity');
    }

    public function getElectricityKwhAttribute(): float
    {
        return $this->metricQuantity(['energy_electricity']);
    }

    public function getTotalWaterM3Attribute(): float
    {
        $totalWater = $this->metricQuantity(['water_total']);

        return $totalWater > 0
            ? $totalWater
            : $this->metricQuantity(['water_municipal', 'water_well']);
    }

    public function getTotalWasteKgAttribute(): float
    {
        return $this->metricQuantity([
            'waste_general', 'waste_plastic', 'waste_glass', 'waste_paper',
            'waste_metal', 'waste_organic', 'waste_hazardous',
            'waste_electronic', 'waste_battery',
        ]);
    }

    public function getEnergyKwhPerOccupiedRoomAttribute(): float
    {
        return $this->occupied_rooms > 0
            ? round($this->electricity_kwh / $this->occupied_rooms, 4)
            : 0;
    }

    public function getWaterLitrePerGuestNightAttribute(): float
    {
        return $this->occupied_rooms > 0
            ? round(($this->total_water_m3 * 1000) / $this->occupied_rooms, 4)
            : 0;
    }

    public function getWasteKgPerGuestAttribute(): float
    {
        return $this->total_guests > 0
            ? round($this->total_waste_kg / $this->total_guests, 4)
            : 0;
    }
}
