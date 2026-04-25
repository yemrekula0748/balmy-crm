<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BRANCH 2 PRODUCTS ===\n";
$products = App\Models\FoodProduct::where('branch_id', 2)->get(['id','food_category_id','title','image']);
foreach ($products as $p) {
    echo $p->id . ' | cat:' . $p->food_category_id . ' | ' . json_encode($p->title, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
echo 'Total: ' . $products->count() . PHP_EOL;

echo "\n=== BRANCH 2 CATEGORIES ===\n";
$cats = App\Models\FoodCategory::where('branch_id', 2)->get(['id','title','sort_order']);
foreach ($cats as $c) {
    echo $c->id . ' | sort:' . $c->sort_order . ' | ' . json_encode($c->title, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo "\n=== PRODUCT 339 ===\n";
$p339 = App\Models\FoodProduct::find(339);
if ($p339) {
    echo 'ID: ' . $p339->id . PHP_EOL;
    echo 'Image: ' . $p339->image . PHP_EOL;
    echo 'Title: ' . json_encode($p339->title, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo 'Description: ' . json_encode($p339->description, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo 'Ingredients: ' . json_encode($p339->ingredients, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo 'Allergens: ' . json_encode($p339->allergens, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo 'Calories: ' . $p339->calories . PHP_EOL;
    echo 'Protein: ' . $p339->protein . PHP_EOL;
    echo 'Carbs: ' . $p339->carbs . PHP_EOL;
    echo 'Fat: ' . $p339->fat . PHP_EOL;
    echo 'Branch: ' . $p339->branch_id . PHP_EOL;
    echo 'Category: ' . $p339->food_category_id . PHP_EOL;
} else {
    echo "Not found!\n";
}
