<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$image = 'food_library/h4bEIzvF1AGLfDJmlfJujj6KZ6T4TD33hk6toadT.png';

// Çorbalar = 29 (branch_id=2, Balmy Foresta)
$data = [
    'branch_id'        => 2,
    'food_category_id' => 29,
    'image'            => $image,
    'title'            => [
        'tr' => 'Şefin Mezeleri',
        'en' => "Chef's Mezze Selection",
        'de' => 'Mezze-Auswahl des Küchenchefs',
        'ru' => 'Мезе от шефа',
    ],
    'description'      => [
        'tr' => 'Adana usulü taretür, ahtapot söğüş, deniz börülcesi, midye dolma, Cunda ezme, fava patlıcan salatası, Ezine peyniri, kavun',
        'en' => 'Adana-style tarator, octopus söğüş, sea purslane, stuffed mussels, Cunda ezme, fava eggplant salad, Ezine cheese, melon',
        'de' => 'Tarator nach Adana-Art, Oktopus-Söğüş, Meerfenchel, gefüllte Muscheln, Cunda-Ezme, Fava-Auberginensalat, Ezine-Käse, Melone',
        'ru' => 'Тараторный соус по-адански, осьминог сёгюш, морской укроп, фаршированные мидии, эзме из Джунды, салат из баклажана с фавой, сыр Эзине, дыня',
    ],
    'ingredients'      => [
        'tr' => 'Adana usulü taretür, ahtapot, deniz börülcesi, midye dolma, ezme, fava, patlıcan, Ezine peyniri, kavun',
        'en' => 'Adana tarator, octopus, sea purslane, stuffed mussels, ezme, fava beans, eggplant, Ezine cheese, melon',
        'de' => 'Adana-Tarator, Oktopus, Meerfenchel, gefüllte Muscheln, Ezme, Favabohnen, Aubergine, Ezine-Käse, Melone',
        'ru' => 'Тараторный соус по-адански, осьминог, морской укроп, фаршированные мидии, эзме, бобы фава, баклажан, сыр Эзине, дыня',
    ],
    'allergens'        => ['kabuklu', 'yumusakcalar', 'sut', 'susam', 'gluten'],
    'price'            => 0,
    'calories'         => 320,
    'protein'          => 18,
    'carbs'            => 22,
    'fat'              => 16,
    'sort_order'       => 0,
    'is_active'        => true,
];

$exists = FoodProduct::where('branch_id', $data['branch_id'])
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$data['title']['tr']])
    ->exists();

if ($exists) {
    echo "ATLA: '{$data['title']['tr']}' zaten mevcut.\n";
} else {
    FoodProduct::create($data);
    echo "EKLENDI: [{$data['food_category_id']}] {$data['title']['tr']}\n";
}
