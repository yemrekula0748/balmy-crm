<?php
require 'vendor/autoload.php';

$app    = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$locationId = 8; // Balmy Foresta - Mutfak
$now = now()->toDateTimeString();

$alanlar = [
    'ANA MUTFAK',
    'ANA MUTFAK SEBZE HAZIRLIK BÖLÜMÜ',
    'ANA MUTFAK SICAK BÖLÜM',
    'ANA MUTFAK BALIK BÖLÜMÜ',
    'ANA MUTFAK KAHVALTI BÖLÜMÜ',
    'ANA MUTFAK KASAPHANE',
    'ANA MUTFAK KAZAN YIKAMA',
    'ANA MUTFAK KAZAN YIKAMA BÖLÜMÜ',
    'ANA MUTFAK PASTANE',
    'ANA MUTFAK PERSONEL YEMEKHANE',
    'ANA MUTFAK SEBZE HAZIRLIK BÖLÜMÜ',
    'ANA MUTFAK SICAK',
    'ANA MUTFAK SICAK BÖLÜM',
    'ANA MUTFAK SOĞUK BÖLÜMÜ',
    'GARDEN HAUSE MUTFAK',
    'GARDEN HOUSE MUTFAK',
    'GARDEN HOUSE MUTFAK ELEKTRİK ARIZASI',
    'GARDEN MUTFAK',
    'LOBİ A BLOK PERSONEL ASANSÖRÜNÜN MUTFAK TARAFINA AÇILAN KAPISI',
    'LOBİ TUVALETLERİ ÖNÜNDE BULUNAN MUTFAK GİRİŞ KAPISI KAPI KOLU',
    'MUTFAK',
    'MUTFAK 13 NUMARALI DOLAP',
    'MUTFAK 5 NUMARALI DOLAP',
    'MUTFAK 6 NUMARA',
    'MUTFAK 6 NUMARALI DOLAP',
    'MUTFAK 7 NUMARALI DOLAP',
    'MUTFAK BAKLAVA BÖLÜMÜ',
    'MUTFAK BALIKHAN VE KASAPHANE',
    'MUTFAK GARDEN HOUSE',
    'MUTFAK HAZIRLIK SİMİT BÖLÜMÜ',
    'MUTFAK KAHVALTI',
    'MUTFAK KASAPHANE BÖLÜMÜ',
    'MUTFAK KAZAN YIKAMA',
    'MUTFAK MEYVE SEBZE HAZIRLIK BÖLÜMÜ METAL TEZGAH AYAK PABUCU YOKTUR',
    'MUTFAK PASTANE',
    'MUTFAK PASTANE ŞOK DOLABI',
    'MUTFAK PERSONEL MUTFAĞI',
    'MUTFAK ŞAHİT NUMUNE DOLABI YANI',
    'MUTFAK SEASONS',
    'MUTFAK SICAK',
    'MUTFAK SICAK BÖLÜM',
    'MUTFAK SOĞUK',
    'MUTFAK WAO',
    'PERSONEL MUTFAK',
    'SEASONS MUTFAK',
    'SEASONS MUTFAK FB OFİS ÖNÜ',
    'SEAZONS MUTFAK',
    'SICAK MUTFAK',
    'SICAK MUTFAK, KASAP',
    'SUNSET MUTFAK',
];

$inserted = 0;
$skipped  = 0;
$toInsert = [];

foreach ($alanlar as $alan) {
    $exists = DB::table('fault_areas')
        ->where('fault_location_id', $locationId)
        ->where('name', $alan)
        ->exists();

    if ($exists) {
        echo "  Zaten var, atlandı: {$alan}\n";
        $skipped++;
        continue;
    }

    $toInsert[] = [
        'fault_location_id' => $locationId,
        'name'              => $alan,
        'is_active'         => 1,
        'created_at'        => $now,
        'updated_at'        => $now,
    ];
    $inserted++;
}

if (!empty($toInsert)) {
    DB::table('fault_areas')->insert($toInsert);
    echo "✓ {$inserted} alan başarıyla eklendi.\n";
} else {
    echo "Eklenecek yeni kayıt yok.\n";
}

if ($skipped > 0) {
    echo "  {$skipped} kayıt zaten mevcuttu, atlandı.\n";
}
