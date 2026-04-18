<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Menu 18 kaynak: tüm fp_id'leri sort_order sırasıyla topla
$sourceCats = App\Models\QrMenuCategory::where('qr_menu_id', 18)
    ->orderBy('sort_order')->orderBy('id')->get();

$sourceFpIds = [];
foreach ($sourceCats as $cat) {
    $items = $cat->items()->orderBy('sort_order')->pluck('food_product_id')->toArray();
    foreach ($items as $fpId) {
        $sourceFpIds[] = $fpId;
    }
}

echo "Kaynak (menu 18) fp_id listesi: " . count($sourceFpIds) . " ürün\n\n";

// Hedef kategoriler
$targetCats = [8 => 109, 10 => 108, 11 => 110, 12 => 111, 13 => 112, 14 => 113, 15 => 114];

$allMatch = true;
foreach ($targetCats as $mid => $catId) {
    $targetFpIds = App\Models\QrMenuItem::where('category_id', $catId)
        ->orderBy('sort_order')->pluck('food_product_id')->toArray();

    $missing  = array_diff($sourceFpIds, $targetFpIds);   // kaynak'ta var hedef'te yok
    $extra    = array_diff($targetFpIds, $sourceFpIds);   // hedef'te var kaynak'ta yok
    $countOk  = count($sourceFpIds) === count($targetFpIds);
    $orderOk  = $sourceFpIds === $targetFpIds;

    if ($orderOk) {
        echo "Menu {$mid} (cat {$catId}): ✓ Birebir eşleşiyor (" . count($targetFpIds) . " ürün)\n";
    } else {
        $allMatch = false;
        echo "Menu {$mid} (cat {$catId}): ✗ FARK VAR\n";
        echo "  Kaynak: " . count($sourceFpIds) . " | Hedef: " . count($targetFpIds) . "\n";
        if ($missing) echo "  Kaynak'ta olup hedef'te YOK: " . implode(', ', $missing) . "\n";
        if ($extra)   echo "  Hedef'te olup kaynak'ta YOK: " . implode(', ', $extra) . "\n";
        if ($countOk && !$orderOk) {
            // Sıra farklı - hangi pozisyonlar?
            $diffs = [];
            foreach ($sourceFpIds as $i => $fpId) {
                if ($targetFpIds[$i] !== $fpId) {
                    $diffs[] = "pos {$i}: kaynak={$fpId} hedef={$targetFpIds[$i]}";
                }
            }
            if ($diffs) echo "  Sıra farkı: " . implode(', ', array_slice($diffs, 0, 10)) . "\n";
        }
    }
}

echo $allMatch ? "\n✓ Tüm hedef kategoriler kaynak ile birebir eşleşiyor.\n" : "\n✗ Bazı kategorilerde fark var!\n";
