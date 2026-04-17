<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// description alanında veri olan ürünleri bul
$items = DB::table('qr_menu_items')
    ->whereBetween('id', [555, 694])
    ->whereNotNull('description')
    ->where('description', '!=', 'null')
    ->where('description', '!=', '[]')
    ->where('description', '!=', '{}')
    ->get(['id', 'title', 'description']);

echo "Description dolu olan ürün sayısı: " . count($items) . "\n\n";

foreach ($items as $it) {
    $title = json_decode($it->title, true);
    $titleTr = is_array($title) ? ($title['tr'] ?? json_encode($title)) : $title;

    $desc = json_decode($it->description, true);

    echo "id={$it->id} title={$titleTr}\n";
    echo "  RAW desc: " . $it->description . "\n";

    // Çift encode mi?
    if (is_string($desc)) {
        echo "  >> ÇIFT ENCODE! Inner: {$desc}\n";
    } else {
        echo "  >> Temiz (array)\n";
    }
    echo "\n";
}
