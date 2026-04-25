<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$toCheck = [
    // SOĞUK BAŞLANGIÇLAR
    'Somon Füme',
    'Parçalanmış Mozzarella Kulesi',
    // SALATALAR
    'Tavuk Sezar Salatası',
    'Yunan Salatası',
    // DÜRÜM & BURGERLER
    'Balmy Burger',
    'Balmy Gözleme',
    'Piliç Fajita Dürüm',
    // MAKARNALAR
    'Spaghetti Bolognese',
    'Spaghetti Napolitana',
    'Linguini Aglio',
    'Ravioli',
    // PİZZA
    'Pizza Margarita',
    'Peperoni Pizza',
    'Turco Pizza',
    // PİDELER
    'Kuşbaşı Kaşarlı Pide',
    'Balmy Çıtır Lahmacun',
    // ANA YEMEKLER
    'Et Döner Kebap',
    'Çıtır Tavuk',
    'Çökertme Kebabı',
    'Fish and Chips',
    // TATLILAR
    'Soğuk Baklava',
    'Fırın Sütlaç',
];

echo "=== BRANCH 2 KONTROL ===\n\n";

foreach ($toCheck as $name) {
    $found = FoodProduct::where('branch_id', 2)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [trim($name)])
        ->first(['id','food_category_id','title']);

    if ($found) {
        echo "✓ MEVCUT (ID:{$found->id} cat:{$found->food_category_id}): {$name}\n";
    } else {
        echo "✗ EKSİK : {$name}\n";
    }
}
