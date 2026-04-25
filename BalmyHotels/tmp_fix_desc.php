<?php
/**
 * 555-694 arası ürünlerde description alanını çift encode'dan düzeltir.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$items = DB::table('qr_menu_items')
    ->whereBetween('id', [555, 694])
    ->whereNotNull('description')
    ->where('description', '!=', 'null')
    ->get(['id', 'title', 'description']);

echo count($items) . " ürün kontrol ediliyor\n\n";

$fixed = 0;
$clean = 0;

foreach ($items as $it) {
    $decoded = json_decode($it->description, true);

    // Çift encode: ilk decode string geliyor
    if (is_string($decoded)) {
        $inner = json_decode($decoded, true);
        if (is_array($inner)) {
            DB::table('qr_menu_items')->where('id', $it->id)->update([
                'description' => json_encode($inner, JSON_UNESCAPED_UNICODE),
            ]);
            $title = json_decode($it->title, true);
            $titleTr = is_array($title) ? ($title['tr'] ?? '?') : $title;
            echo "FIX id={$it->id} [{$titleTr}]\n";
            $fixed++;
        }
    } else {
        $clean++;
    }
}

echo "\n=== TAMAMLANDI: {$fixed} düzeltildi, {$clean} zaten temizdi ===\n";
