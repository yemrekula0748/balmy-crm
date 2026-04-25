<?php
/**
 * Balmy Foresta (branch_id=2) — Eksik ürünler
 *   1. Sebze Mücver           → cat:35 (Sıcak Başlangıçlar)
 *   2. Antep Usulü İçli Köfte → cat:35
 */

require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$IMAGE      = 'food_library/mHm067xbGzkBP6XID9QhhaTfKctMgLUdCcvRotcm.png';
$BRANCH     = 2;
$CAT_HOT    = 35; // Sıcak Başlangıçlar

function exists_check(int $branchId, string $titleTr): bool
{
    return FoodProduct::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [trim($titleTr)])
        ->exists();
}

$products = [

    // ══════════════════════════════════════════════════════════════
    // 1. Sebze Mücver
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_HOT,
        'title' => [
            'tr' => 'Sebze Mücver',
            'en' => 'Vegetable Fritters',
            'de' => 'Gemüsefritter',
            'ru' => 'Овощные оладьи',
        ],
        'description' => [
            'tr' => 'Taze sebzelerle hazırlanmış çıtır mücver, körpe roka yaprakları, ekşi krema ve yeşil yağ ile servis edilir.',
            'en' => 'Crispy vegetable fritters served with fresh baby arugula, sour cream and herb oil.',
            'de' => 'Knusprige Gemüsefritter mit frischen Rucola-Blättern, saurer Sahne und Kräuteröl.',
            'ru' => 'Хрустящие овощные оладьи подаются со свежей руколой, сметаной и зелёным маслом.',
        ],
        'ingredients' => [
            'tr' => 'Kabak, havuç, soğan, yumurta, un, körpe roka, ekşi krema, yeşil yağ',
            'en' => 'Zucchini, carrot, onion, egg, flour, baby arugula, sour cream, herb oil',
            'de' => 'Zucchini, Karotte, Zwiebel, Ei, Mehl, Rucola, saure Sahne, Kräuteröl',
            'ru' => 'Кабачок, морковь, лук, яйцо, мука, молодая рукола, сметана, масло с травами',
        ],
        // Alerjenler: yumurta, gluten (un), sut (ekşi krema)
        'allergens' => ['yumurta', 'gluten', 'sut'],
        'calories'  => 280.0,
        'protein'   =>   9.0,
        'carbs'     =>  28.0,
        'fat'       =>  15.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 10,
        'image'     => $IMAGE,
    ],

    // ══════════════════════════════════════════════════════════════
    // 2. Antep Usulü İçli Köfte
    // ══════════════════════════════════════════════════════════════
    [
        'branch_id'        => $BRANCH,
        'food_category_id' => $CAT_HOT,
        'title' => [
            'tr' => 'Antep Usulü İçli Köfte',
            'en' => 'Antep Style Stuffed Meatballs',
            'de' => 'Gefüllte Fleischbällchen nach Antep-Art',
            'ru' => 'Фаршированные тефтели по-антепски',
        ],
        'description' => [
            'tr' => 'Gaziantep yöresine özgü baharatlı kıyma ve ceviz dolgulu çıtır içli köfte, taze baharatlı yoğurt sos ve limon ile sunulur.',
            'en' => 'Crispy stuffed meatballs with spiced ground meat and walnut filling from Gaziantep, served with fresh herbed yogurt sauce and lemon.',
            'de' => 'Knusprige gefüllte Fleischbällchen mit gewürztem Hackfleisch und Walnussfüllung aus Gaziantep, serviert mit frischer Kräuteryogurtsauce und Zitrone.',
            'ru' => 'Хрустящие фаршированные тефтели с пряным фаршем и грецкими орехами по-газиантепски, подаются с йогуртовым соусом с травами и лимоном.',
        ],
        'ingredients' => [
            'tr' => 'Bulgur, kıyma, ceviz, soğan, baharatlar, yoğurt sos, limon',
            'en' => 'Bulgur wheat, ground meat, walnut, onion, spices, yogurt sauce, lemon',
            'de' => 'Bulgur, Hackfleisch, Walnuss, Zwiebel, Gewürze, Joghurtsauce, Zitrone',
            'ru' => 'Булгур, фарш, грецкий орех, лук, специи, йогуртовый соус, лимон',
        ],
        // Alerjenler: gluten (bulgur), kuruyemis (ceviz), sut (yoğurt)
        'allergens' => ['gluten', 'kuruyemis', 'sut'],
        'calories'  => 350.0,
        'protein'   =>  18.0,
        'carbs'     =>  32.0,
        'fat'       =>  16.0,
        'price'     => 0,
        'is_active' => true,
        'sort_order'=> 20,
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
