<?php
/**
 * odalar.xlsx → fault_areas import
 * Balmy Foresta (branch_id = 2) için oda numaralarını konuma eşleştirir.
 */

require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

$app    = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1) Balmy Foresta konumlarını çek (branch_id = 2)
$locationMap = DB::table('fault_locations')
    ->where('branch_id', 2)
    ->get(['id', 'name'])
    ->keyBy('name');

if ($locationMap->isEmpty()) {
    echo "HATA: Balmy Foresta (branch_id=2) için hiç konum bulunamadı.\n";
    exit(1);
}

echo "Mevcut Foresta konumları:\n";
foreach ($locationMap as $name => $loc) {
    echo "  [{$loc->id}] {$name}\n";
}
echo "\n";

// 2) xlsx oku
$spreadsheet = IOFactory::load('odalar.xlsx');
$sheet       = $spreadsheet->getActiveSheet();

$inserted  = 0;
$skipped   = 0;
$unknown   = [];
$now       = now()->toDateTimeString();
$toInsert  = [];

foreach ($sheet->getRowIterator(2) as $row) {
    $cells = $row->getCellIterator();
    $cells->setIterateOnlyExistingCells(false);
    $data = [];
    foreach ($cells as $cell) {
        $data[] = trim((string) $cell->getValue());
    }

    $odaNo = $data[0] ?? '';
    $konum = $data[1] ?? '';

    if ($odaNo === '') continue;

    if (!isset($locationMap[$konum])) {
        $unknown[] = "{$odaNo} → \"{$konum}\"";
        $skipped++;
        continue;
    }

    $locationId = $locationMap[$konum]->id;

    // Duplicate kontrolü
    $exists = DB::table('fault_areas')
        ->where('fault_location_id', $locationId)
        ->where('name', $odaNo)
        ->exists();

    if ($exists) {
        echo "  Zaten var, atladı: {$odaNo} ({$konum})\n";
        $skipped++;
        continue;
    }

    $toInsert[] = [
        'fault_location_id' => $locationId,
        'name'              => $odaNo,
        'is_active'         => 1,
        'created_at'        => $now,
        'updated_at'        => $now,
    ];
    $inserted++;
}

// 3) Toplu ekle
if (!empty($toInsert)) {
    DB::table('fault_areas')->insert($toInsert);
    echo "✓ {$inserted} oda başarıyla eklendi.\n";
} else {
    echo "Eklenecek yeni kayıt bulunamadı.\n";
}

if ($skipped > 0) {
    echo "  {$skipped} kayıt atlandı.\n";
}

if (!empty($unknown)) {
    echo "\nEşleşmeyen konumlar (xlsx'te var ama DB'de yok):\n";
    foreach ($unknown as $u) {
        echo "  - {$u}\n";
    }
}
