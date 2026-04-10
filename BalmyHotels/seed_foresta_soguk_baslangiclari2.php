<?php
/**
 * Balmy Foresta (branch_id=2) — Eksik ürünler
 *   1. Somon Füme              → cat:31 (Soğuk Başlangıçlar)
 *   2. Parçalanmış Mozzarella Kulesi → cat:31
 */

require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$IMAGE    = 'food_library/mHm067xbGzkBP6XID9QhhaTfKctMgLUdCcvRotcm.png';
$BRANCH   = 2;
$CAT_COLD = 31; // Soğuk Başlangıçlar

function exists_check(int $branchId, string $titleTr): bool
{
    return FoodProduct::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [trim($titleTr)])
        ->exists();
}

$products = [

    // ══════════════════════════════════════════════════════════════
    // 1. Somon Füme
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_COLD,
        'title' => [
            'tr' => 'Somon Füme',
            'en' => 'Smoked Salmon',
            'de' => 'Geräucherter Lachs',
            'ru' => 'Копчёный лосось',
        ],
        'description' => [
            'tr' => 'Özel yapılmış çıtır ekmek üzerinde krem peynir, taze yeşil yapraklar, file limon ve sızma zeytinyağı ile sunulan klasik somon füme.',
            'en' => 'Classic smoked salmon served on artisan crispy bread with cream cheese, fresh greens, lemon wedges and extra virgin olive oil.',
            'de' => 'Klassischer geräucherter Lachs auf knusprigem Hausbrot mit Frischkäse, frischem Grün, Zitronenspalten und nativem Olivenöl extra.',
            'ru' => 'Классический копчёный лосось на хрустящем хлебе с сливочным сыром, свежей зеленью, дольками лимона и оливковым маслом первого отжима.',
        ],
        'ingredients' => [
            'tr' => 'Somon füme, çıtır ekmek, krem peynir, taze yeşil yapraklar, limon, sızma zeytinyağı',
            'en' => 'Smoked salmon, crispy bread, cream cheese, fresh green leaves, lemon, extra virgin olive oil',
            'de' => 'Geräucherter Lachs, knuspriges Brot, Frischkäse, frische Grünblätter, Zitrone, natives Olivenöl extra',
            'ru' => 'Копчёный лосось, хрустящий хлеб, сливочный сыр, свежие листья зелени, лимон, оливковое масло первого отжима',
        ],
        // Alerjenler: balık, gluten (ekmek), sut (krem peynir)
        'allergens' => ['balik', 'gluten', 'sut'],
        'calories'  => 310.0,
        'protein'   =>  22.0,
        'carbs'     =>  18.0,
        'fat'       =>  16.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 30,
        'image'     => $IMAGE,
    ],

    // ══════════════════════════════════════════════════════════════
    // 2. Parçalanmış Mozzarella Kulesi
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_COLD,
        'title' => [
            'tr' => 'Parçalanmış Mozzarella Kulesi',
            'en' => 'Torn Mozzarella Tower',
            'de' => 'Mozzarella-Turm',
            'ru' => 'Башня из рваной моцареллы',
        ],
        'description' => [
            'tr' => 'Fındık taratorlu semiz otu, taze parçalanmış mozzarella, domates konfi ve pesto sos ile katman katman hazırlanmış zarif bir başlangıç.',
            'en' => 'An elegant layered starter of purslane with hazelnut tarator, freshly torn mozzarella, tomato confit and pesto sauce.',
            'de' => 'Ein eleganter Schicht-Starter aus Portulak mit Haselnuss-Tarator, frisch gerissenem Mozzarella, Tomatenconfit und Pesto.',
            'ru' => 'Изысканная слоёная закуска из портулака с ореховым тараторо, свежей рваной моцареллой, томатным конфи и соусом песто.',
        ],
        'ingredients' => [
            'tr' => 'Mozzarella, semiz otu, fındık tarator, domates konfi, pesto sos',
            'en' => 'Mozzarella, purslane, hazelnut tarator, tomato confit, pesto sauce',
            'de' => 'Mozzarella, Portulak, Haselnuss-Tarator, Tomatenconfit, Pesto',
            'ru' => 'Моцарелла, портулак, ореховый тараторо, томатный конфи, соус песто',
        ],
        // Alerjenler: sut (mozzarella), kuruyemis (fındık), gluten (pesto bazında olası)
        'allergens' => ['sut', 'kuruyemis'],
        'calories'  => 340.0,
        'protein'   =>  18.0,
        'carbs'     =>  12.0,
        'fat'       =>  24.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 40,
        'image'     => $IMAGE,
    ],
];

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
