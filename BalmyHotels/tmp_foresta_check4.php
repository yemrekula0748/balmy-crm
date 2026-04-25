<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$toCheck = [
    'Karpuz Rüyası',
    'Mini Pizza Margarita',
    'Pizza Salami',
    'Spaghetti Bolognese',
    'Spaghetti Napolitana',
    'Parmak Piliç',
    'Mini Hamburger',
    'Puding',
    'Fırın Sütlaç',
    'Dondurma',
    'Meyve',
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
