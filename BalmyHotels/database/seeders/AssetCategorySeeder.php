<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name'  => 'Bilgisayar & Teknoloji',
                'color' => '#2563eb',
                'description' => 'Bilgisayar, çevre birimleri ve teknoloji ekipmanları',
                'children' => [
                    'Dizüstü Bilgisayar (Notebook)',
                    'Masaüstü Bilgisayar (Kasa)',
                    'Monitör',
                    'Klavye',
                    'Mouse',
                    'Hoparlör / Ses Sistemi',
                    'Yazıcı',
                    'Projektör',
                    'UPS / Güç Kaynağı',
                    'Tablet / iPad',
                    'Web Kamerası',
                    'Harici Disk / USB',
                    'Switch / Router / Modem',
                ],
            ],
            [
                'name'  => 'Televizyon & Görüntü',
                'color' => '#7c3aed',
                'description' => 'TV, projeksiyon ve görüntü sistemleri',
                'children' => [
                    'Televizyon (Akıllı TV)',
                    'Televizyon (Standart)',
                    'Projeksiyon Cihazı',
                    'Projeksiyon Perdesi',
                    'DVD / Blu-ray Player',
                    'Dijital Tabela Ekranı',
                ],
            ],
            [
                'name'  => 'Mobilya',
                'color' => '#92400e',
                'description' => 'Oda, lobi, restoran ve ofis mobilyaları',
                'children' => [
                    'Koltuk / Berjer',
                    'Sandalye',
                    'Masa (Yemek Masası)',
                    'Masa (Çalışma / Ofis)',
                    'Sehpa / Orta Sehpa',
                    'Komidin',
                    'Dolap / Gardırop',
                    'Vitrin / Konsol',
                    'Karyola / Yatak Çerçevesi (Tek)',
                    'Karyola / Yatak Çerçevesi (Çift)',
                    'Bar Taburesi',
                    'Şezlong',
                    'Ranza',
                    'Raf / Kitaplık',
                ],
            ],
            [
                'name'  => 'Beyaz Eşya & Mutfak Aletleri',
                'color' => '#0891b2',
                'description' => 'Buzdolabı, çamaşır makinesi ve küçük ev aletleri',
                'children' => [
                    'Buzdolabı (Büyük)',
                    'Minibar',
                    'Derin Dondurucu',
                    'Çamaşır Makinesi (Ev Tipi)',
                    'Kurutucu (Ev Tipi)',
                    'Bulaşık Makinesi',
                    'Fırın (Elektrik/Gaz)',
                    'Mikrodalga Fırın',
                    'Su Isıtıcı (Kettle)',
                    'Kahve Makinesi (Oda)',
                    'Tost Makinesi',
                ],
            ],
            [
                'name'  => 'Isıtma & Soğutma',
                'color' => '#dc2626',
                'description' => 'Klima, ısıtıcı, havalandırma sistemleri',
                'children' => [
                    'Klima (Split)',
                    'Klima (Kaset / Kanal)',
                    'Oda Isıtıcı',
                    'Vantilatör / Ayaklı Fan',
                    'Tavan Fanı',
                    'Havalandırma Sistemi (Fan Coil)',
                    'Banyo Kurutma Radyatörü',
                ],
            ],
            [
                'name'  => 'Güvenlik & Erişim Kontrol',
                'color' => '#b45309',
                'description' => 'Kamera, alarm, kapı kilidi ve erişim sistemleri',
                'children' => [
                    'Güvenlik Kamerası (Sabit)',
                    'Güvenlik Kamerası (PTZ / Döner)',
                    'NVR / DVR Kayıt Cihazı',
                    'Kapı Kilidi (Akıllı / Kartlı)',
                    'Kart Okuyucu',
                    'Alarm Paneli',
                    'Hareket Sensörü',
                    'Metal Dedektör',
                    'Turnike / Geçiş Sistemi',
                ],
            ],
            [
                'name'  => 'Mutfak & Restoran Ekipmanları',
                'color' => '#16a34a',
                'description' => 'Endüstriyel mutfak ve servis ekipmanları',
                'children' => [
                    'Endüstriyel Ocak (Gaz / Elektrik)',
                    'Davlumbaz / Aspiratör',
                    'Endüstriyel Fırın (Konveksiyonlu)',
                    'Endüstriyel Buzdolabı',
                    'Endüstriyel Derin Dondurucu',
                    'Salamander (Üst Isıtıcı)',
                    'Izgara / Mangal',
                    'Fritöz',
                    'Bain-Marie (Yemek Isıtıcı)',
                    'Kahve / Espresso Makinesi',
                    'Blender / Karıştırıcı',
                    'Dilimleme Makinesi',
                    'Çay Makinesi',
                    'Bulaşık Makinesi (Endüstriyel)',
                    'Buzdolabı (Bar / Tezgah Altı)',
                ],
            ],
            [
                'name'  => 'Çamaşırhane & Ütü',
                'color' => '#0284c7',
                'description' => 'Çamaşırhane, ütü ve tekstil bakım ekipmanları',
                'children' => [
                    'Çamaşır Makinesi (Ticari)',
                    'Kurutucu (Ticari)',
                    'Ütü (El Tipi)',
                    'Ütü Masası',
                    'Ütü Presi (Buharlı)',
                    'Yıkama Kurutma Kombine (Ticari)',
                ],
            ],
            [
                'name'  => 'Ofis & İletişim',
                'color' => '#4338ca',
                'description' => 'Ofis cihazları, telefon ve ödeme sistemleri',
                'children' => [
                    'Fotokopi / Çok İşlevli Yazıcı',
                    'Faks Makinesi',
                    'IP Telefon / Masaüstü Telefon',
                    'Kablosuz Telefon',
                    'Para Kasası',
                    'POS / Yazar Kasa',
                    'Barkod / QR Okuyucu',
                    'Etiket Yazıcı',
                    'Projeksiyon / Sunum Sistemi',
                ],
            ],
            [
                'name'  => 'Ses & Sahne Sistemleri',
                'color' => '#be185d',
                'description' => 'Ses, müzik, sahne ve etkinlik ekipmanları',
                'children' => [
                    'Hoparlör (Sahne / PA)',
                    'Hoparlör (Arka Fon / Ambiyans)',
                    'Amplifikatör',
                    'Mikrofon (Kablolu)',
                    'Mikrofon (Kablosuz / Telsiz)',
                    'Mixer / Ses Masası',
                    'Müzik Çalar / DJ Ekipmanı',
                    'Tele Konferans Sistemi',
                ],
            ],
            [
                'name'  => 'Aydınlatma',
                'color' => '#ca8a04',
                'description' => 'İç ve dış mekan aydınlatma armatürleri',
                'children' => [
                    'Avize',
                    'Tavan Lambası / LED Panel',
                    'Spot / Ray Aydınlatma',
                    'Abajur / Masa Lambası',
                    'Aplik (Duvar Lambası)',
                    'Dış Mekan Aydınlatma',
                    'Dekoratif Aydınlatma',
                    'Acil Çıkış / Güvenlik Lambası',
                ],
            ],
            [
                'name'  => 'Spor & Fitness',
                'color' => '#15803d',
                'description' => 'Spor salonu, yüzme havuzu ve rekreasyon ekipmanları',
                'children' => [
                    'Koşu Bandı',
                    'Kondisyon Bisikleti',
                    'Kürek Makinesi (Rowing)',
                    'Eliptik / Stepper',
                    'Halter Seti / Dambıl',
                    'Ağırlık Aleti (Multi Gym)',
                    'Bench Press / Ağırlık Tezgahı',
                    'Yoga Matı',
                    'Tenis Raket / Badminton Seti',
                ],
            ],
            [
                'name'  => 'Havuz & Bahçe',
                'color' => '#0e7490',
                'description' => 'Yüzme havuzu, spa ve bahçe ekipmanları',
                'children' => [
                    'Havuz Pompası',
                    'Havuz Filtresi',
                    'Jakuzi / Banyotech',
                    'Sauna Fırını',
                    'Şezlong (Havuz / Bahçe)',
                    'Bahçe Masası & Sandalyesi',
                    'Güneş Şemsiyesi',
                    'Sulama Sistemi',
                    'Çim Biçme Makinesi',
                ],
            ],
            [
                'name'  => 'Banyo & Sıhhi Tesisat',
                'color' => '#6d28d9',
                'description' => 'Banyo ekipmanları ve sıhhi tesisat armatürleri',
                'children' => [
                    'Duş Kabini / Kabin',
                    'Küvet',
                    'Jakuzi (Banyo)',
                    'Saç Kurutma Makinesi',
                    'Ayna (Aydınlatmalı)',
                    'Havlu Isıtıcı',
                    'Su Arıtma Cihazı',
                ],
            ],
            [
                'name'  => 'Taşıt & Araçlar',
                'color' => '#374151',
                'description' => 'Otel servis araçları, transfer ve lojistik taşıtları',
                'children' => [
                    'Servis Minibüsü / Otobüsü',
                    'Binek Araç (VIP Transfer)',
                    'Elektrikli Araç',
                    'Golf Arabası',
                    'Bagaj Arabası (Kap Boy)',
                    'Oda Servis Arabası',
                    'Temizlik Arabası',
                ],
            ],
            [
                'name'  => 'Temizlik & Bakım',
                'color' => '#065f46',
                'description' => 'Temizlik makineleri, ev bakım ekipmanları',
                'children' => [
                    'Endüstriyel Süpürge',
                    'Zemin Yıkama Makinesi',
                    'Halı Yıkama / Temizleme',
                    'Buharlı Temizleme Makinesi',
                    'Çöp Arabası / Konteyneri',
                    'Havalandırma / Koku Makinesi',
                ],
            ],
            [
                'name'  => 'Enerji & Altyapı',
                'color' => '#9a3412',
                'description' => 'Jeneratör, UPS, elektrik ve altyapı sistemleri',
                'children' => [
                    'Jeneratör',
                    'UPS (Kesintisiz Güç)',
                    'Güneş Paneli',
                    'Trafo / Elektrik Panosu',
                    'Su Deposu / Pompa Sistemi',
                    'Isı Pompası / Kazan',
                ],
            ],
            [
                'name'  => 'Genel & Diğer',
                'color' => '#6b7280',
                'description' => 'Diğer ekipman ve demirbaşlar',
                'children' => [],
            ],
        ];

        foreach ($tree as $parentData) {
            $children = $parentData['children'];
            unset($parentData['children']);

            // Skip if parent already exists
            $parent = AssetCategory::firstOrCreate(
                ['name' => $parentData['name'], 'parent_id' => null],
                array_merge($parentData, ['parent_id' => null])
            );

            foreach ($children as $childName) {
                AssetCategory::firstOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id],
                    [
                        'name'      => $childName,
                        'color'     => $parent->color,
                        'parent_id' => $parent->id,
                    ]
                );
            }
        }
    }
}
