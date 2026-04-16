<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$p = FoodProduct::where('branch_id', 2)
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", ['Şefin Mezeleri'])
    ->first();

if (!$p) { echo "Bulunamadı.\n"; exit; }

echo json_encode([
    'id'          => $p->id,
    'category_id' => $p->food_category_id,
    'description' => $p->description,
    'ingredients' => $p->ingredients,
    'calories'    => $p->calories,
    'protein'     => $p->protein,
    'carbs'       => $p->carbs,
    'fat'         => $p->fat,
    'allergens'   => $p->allergens,
    'image'       => $p->image,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
