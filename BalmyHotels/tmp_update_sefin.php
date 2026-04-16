<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$product = FoodProduct::find(287);

if (!$product) {
    echo "Ürün bulunamadı (ID: 287)\n";
    exit;
}

$product->update([
    'food_category_id' => 29, // Çorbalar
    'image'            => 'food_library/h4bEIzvF1AGLfDJmlfJujj6KZ6T4TD33hk6toadT.png',
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
        'tr' => 'Adana usulü taretür, ahtapot, deniz börülcesi, midye dolma, Cunda ezme, fava, patlıcan, Ezine peyniri, kavun',
        'en' => 'Adana tarator, octopus, sea purslane, stuffed mussels, Cunda ezme, fava beans, eggplant, Ezine cheese, melon',
        'de' => 'Adana-Tarator, Oktopus, Meerfenchel, gefüllte Muscheln, Cunda Ezme, Favabohnen, Aubergine, Ezine-Käse, Melone',
        'ru' => 'Тараторный соус по-адански, осьминог, морской укроп, фаршированные мидии, эзме из Джунды, бобы фава, баклажан, сыр Эзине, дыня',
    ],
    'allergens'        => ['kabuklu', 'yumusakcalar', 'sut', 'susam', 'gluten'],
    'calories'         => 420,
    'protein'          => 24,
    'carbs'            => 28,
    'fat'              => 26,
]);

echo "GUNCELLENDI: #{$product->id} — {$product->getTitle('tr')} → kategori 29 (Çorbalar)\n";
