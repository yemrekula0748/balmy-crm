<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

$toCheck = [
    // ÇORBALAR
    'Beyran Çorbası',
    // MEZE ÇEŞİTLERİ
    'Zeytinyağlı Yaprak Sarma',
    'Acılı Antep Ezme',
    'Kale Biberli Atom',
    'Kıtır Pastırmalı Humus',
    'Narenciyeli Hibeş',
    'Vakfıkebir Tereyağı',
    'Erzincan Tulum Peyniri',
    'Ceviz',
    // SICAK BAŞLANGIÇLAR
    'Sebze Mücver',
    'Antep Usulü İçli Köfte',
    // SALATALAR
    'Gavurdağı Salatası',
    'Cacık',
    // ANA YEMEKLER
    'Hafif Ateşte Pişirilmiş Kuzu Tandır',
    'Adana Kebap',
    'Hafif Acılı Piliç Kanat',
    'Alinazik',
    // TATLILAR
    'Havuç Dilimi Baklava',
    'Kabak Tatlısı',
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

// Kategori listesi de lazım olur
echo "\n=== KATEGORİLER (branch 2) ===\n";
$cats = \App\Models\FoodCategory::where('branch_id', 2)->get(['id','title','sort_order']);
foreach ($cats as $c) {
    echo $c->id . ' | ' . json_encode($c->title, JSON_UNESCAPED_UNICODE) . "\n";
}
