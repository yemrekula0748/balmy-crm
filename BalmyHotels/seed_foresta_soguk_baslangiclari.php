<?php
/**
 * Balmy Foresta (branch_id=2) — Eksik ürün ekleyici
 * Eklenecek ürünler:
 *   1. BURRATA           → cat:31 (Soğuk Başlangıçlar)
 *   2. Trüflü Mayonezli Dana Carpaccio → cat:31
 *   3. MEYVE             → cat:50 (Tatlılar)
 *
 * Görsel: product 339 ile aynı
 *   food_library/mHm067xbGzkBP6XID9QhhaTfKctMgLUdCcvRotcm.png
 */

require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$IMAGE    = 'food_library/mHm067xbGzkBP6XID9QhhaTfKctMgLUdCcvRotcm.png';
$BRANCH   = 2;
$CAT_COLD = 31;   // Soğuk Başlangıçlar
$CAT_DES  = 50;   // Tatlılar

// ──────────────────────────────────────────────────────────────────────────────
// Yardımcı: Aynı şube + Türkçe ad var mı kontrol et
// ──────────────────────────────────────────────────────────────────────────────
function exists_check(int $branchId, string $titleTr): bool
{
    return FoodProduct::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [trim($titleTr)])
        ->exists();
}

// ──────────────────────────────────────────────────────────────────────────────
// Ürün tanımları
// ──────────────────────────────────────────────────────────────────────────────
$products = [

    // ══════════════════════════════════════════════════════════════
    // 1. BURRATA — Soğuk Başlangıçlar
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_COLD,
        'title' => [
            'tr' => 'Burrata',
            'en' => 'Burrata',
            'de' => 'Burrata',
            'ru' => 'Бурата',
        ],
        'description' => [
            'tr' => 'Kremsi burrata peyniri, tatlı domates pestili, taze fesleğen sosu ve aromatik kekik ile sunulan nefis bir İtalyan klasiği.',
            'en' => 'Creamy burrata cheese served with sweet tomato paste, fresh basil sauce and aromatic thyme — a classic Italian appetizer.',
            'de' => 'Cremiger Burrata-Käse mit süßer Tomatenpaste, frischer Basilikumsauce und aromatischem Thymian – ein italienischer Klassiker.',
            'ru' => 'Сливочный сыр бурата с томатным вареньем, свежим соусом из базилика и ароматным тимьяном — итальянская классика.',
        ],
        'ingredients' => [
            'tr' => 'Burrata peyniri, domates pestili, fesleğen sos, taze kekik',
            'en' => 'Burrata cheese, tomato paste, basil sauce, fresh thyme',
            'de' => 'Burrata-Käse, Tomatenpaste, Basilikumsauce, frischer Thymian',
            'ru' => 'Сыр бурата, томатная паста, соус из базилика, свежий тимьян',
        ],
        'allergens' => ['sut'],   // Süt / Laktoz
        'calories'  => 380.0,
        'protein'   => 18.0,
        'carbs'     =>  8.0,
        'fat'       => 30.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 10,
        'image'     => $IMAGE,
    ],

    // ══════════════════════════════════════════════════════════════
    // 2. Trüflü Mayonezli Dana Carpaccio — Soğuk Başlangıçlar
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_COLD,
        'title' => [
            'tr' => 'Trüflü Mayonezli Dana Carpaccio',
            'en' => 'Beef Carpaccio with Truffle Mayonnaise',
            'de' => 'Rindfleisch-Carpaccio mit Trüffelmayonnaise',
            'ru' => 'Карпаччо из говядины с трюфельным майонезом',
        ],
        'description' => [
            'tr' => 'İnce dilimlenmiş dana bonfile, trüflü mayonez, taze limon, rokola yaprakları, parmesan rendeleri ve balzamik sos ile zarifçe hazırlanmış bir başlangıç.',
            'en' => 'Thinly sliced beef tenderloin elegantly prepared with truffle mayonnaise, fresh lemon, arugula, shaved parmesan and balsamic dressing.',
            'de' => 'Dünn aufgeschnittenes Rinderfilet, elegant zubereitet mit Trüffelmayonnaise, frischer Zitrone, Rucola, Parmesanspänen und Balsamico.',
            'ru' => 'Тонко нарезанная говяжья вырезка, изысканно приготовленная с трюфельным майонезом, свежим лимоном, руколой, пармезаном и бальзамическим соусом.',
        ],
        'ingredients' => [
            'tr' => 'Dana bonfile, trüflü mayonez, limon, rokola, parmesan, balzamik',
            'en' => 'Beef tenderloin, truffle mayonnaise, lemon, arugula, parmesan, balsamic',
            'de' => 'Rinderfilet, Trüffelmayonnaise, Zitrone, Rucola, Parmesan, Balsamico',
            'ru' => 'Говяжья вырезка, трюфельный майонез, лимон, рукола, пармезан, бальзамик',
        ],
        // Alerjenler: yumurta (mayonez), sut (parmesan), sulfit (balzamik sirke)
        'allergens' => ['yumurta', 'sut', 'sulfit'],
        'calories'  => 320.0,
        'protein'   => 24.0,
        'carbs'     =>  5.0,
        'fat'       => 22.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 20,
        'image'     => $IMAGE,
    ],

    // ══════════════════════════════════════════════════════════════
    // 3. MEYVE — Tatlılar
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_DES,
        'title' => [
            'tr' => 'Meyve',
            'en' => 'Seasonal Fruit',
            'de' => 'Saisonobst',
            'ru' => 'Сезонные фрукты',
        ],
        'description' => [
            'tr' => 'Günün en taze mevsim meyvelerinden özenle hazırlanmış, soyulmuş meyve tabağı.',
            'en' => 'A carefully prepared plate of the freshest peeled seasonal fruits of the day.',
            'de' => 'Ein sorgfältig zubereiteter Teller mit den frischesten geschälten Saisonfrüchten des Tages.',
            'ru' => 'Тщательно очищенная тарелка со свежайшими сезонными фруктами дня.',
        ],
        'ingredients' => [
            'tr' => 'Mevsim meyveleri',
            'en' => 'Seasonal fruits',
            'de' => 'Saisonfrüchte',
            'ru' => 'Сезонные фрукты',
        ],
        'allergens' => [],   // Alerjen yok
        'calories'  => 120.0,
        'protein'   =>   1.0,
        'carbs'     =>  28.0,
        'fat'       =>   0.5,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 500,
        'image'     => $IMAGE,
    ],
];

// ──────────────────────────────────────────────────────────────────────────────
// Ekleme döngüsü
// ──────────────────────────────────────────────────────────────────────────────
$added   = 0;
$skipped = 0;

foreach ($products as $data) {
    $titleTr = $data['title']['tr'];

    if (exists_check($data['branch_id'], $titleTr)) {
        echo "ATLA   : \"{$titleTr}\" zaten mevcut — atlandı.\n";
        $skipped++;
        continue;
    }

    FoodProduct::create($data);
    echo "EKLENDI: \"{$titleTr}\"\n";
    $added++;
}

echo "\n=== SONUÇ: {$added} ürün eklendi, {$skipped} ürün zaten mevcuttu. ===\n";
