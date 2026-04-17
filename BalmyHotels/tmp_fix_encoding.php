<?php
/**
 * 555-694 arası yeni eklenen ürünlerin çift encode edilmiş
 * title ve sub_heading alanlarını düzeltir.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QrMenuItem;
use Illuminate\Support\Facades\DB;

$items = QrMenuItem::whereBetween('id', [555, 694])->get();

echo count($items) . " ürün düzeltilecek\n\n";

DB::beginTransaction();
try {
    foreach ($items as $item) {
        $updates = [];

        // --- sub_heading düzelt ---
        $rawSH = $item->getRawOriginal('sub_heading');
        if ($rawSH !== null) {
            $decoded = json_decode($rawSH, true);
            // Çift encode: ilk decode string geliyor, ikinci decode array
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                $updates['sub_heading'] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        // --- title düzelt ---
        $rawTitle = $item->getRawOriginal('title');
        if ($rawTitle !== null) {
            $decoded = json_decode($rawTitle, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                $updates['title'] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        if (!empty($updates)) {
            DB::table('qr_menu_items')->where('id', $item->id)->update($updates);
            $trTitle = is_array(json_decode($updates['title'] ?? '{}', true))
                ? (json_decode($updates['title'], true)['tr'] ?? '?')
                : '?';
            echo "FIX id={$item->id} title={$trTitle} sh_tr=" . (json_decode($updates['sub_heading'] ?? '{}', true)['tr'] ?? '?') . "\n";
        }
    }

    DB::commit();
    echo "\n=== TAMAMLANDI ===\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "HATA: " . $e->getMessage() . "\n";
    exit(1);
}
