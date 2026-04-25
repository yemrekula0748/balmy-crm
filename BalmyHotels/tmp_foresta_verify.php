<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$check = FoodProduct::where('branch_id', 2)
    ->whereIn('food_category_id', [31, 50])
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) IN ('Burrata','Trüflü Mayonezli Dana Carpaccio','Meyve')")
    ->get(['id','food_category_id','title','description','ingredients','allergens','calories','protein','carbs','fat','price','image']);

foreach ($check as $p) {
    echo "ID: " . $p->id . "\n";
    echo "  Title  : " . json_encode($p->title, JSON_UNESCAPED_UNICODE) . "\n";
    echo "  Desc   : " . json_encode($p->description, JSON_UNESCAPED_UNICODE) . "\n";
    echo "  Ingr   : " . json_encode($p->ingredients, JSON_UNESCAPED_UNICODE) . "\n";
    echo "  Allerg : " . json_encode($p->allergens, JSON_UNESCAPED_UNICODE) . "\n";
    echo "  Kal/Pro/Karb/Yağ: " . $p->calories . "/" . $p->protein . "/" . $p->carbs . "/" . $p->fat . "\n";
    echo "  Fiyat  : " . $p->price . "\n";
    echo "  Image  : " . $p->image . "\n";
    echo "  Cat    : " . $p->food_category_id . "\n";
    echo "\n";
}
echo "Toplam: " . $check->count() . " ürün\n";
