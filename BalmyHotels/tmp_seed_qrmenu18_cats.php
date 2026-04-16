<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QrMenuCategory;

$qrMenuId = 18;

// Mevcut son sort_order
$maxSort = QrMenuCategory::where('qr_menu_id', $qrMenuId)->max('sort_order') ?? 0;

$categories = [
    [
        'tr' => 'Alkollü Sıcak İçecekler',
        'en' => 'Hot Alcoholic Beverages',
        'de' => 'Heiße alkoholische Getränke',
        'ru' => 'Горячие алкогольные напитки',
    ],
    [
        'tr' => 'Alkollü Kokteyller',
        'en' => 'Alcoholic Cocktails',
        'de' => 'Alkoholische Cocktails',
        'ru' => 'Алкогольные коктейли',
    ],
    [
        'tr' => 'Alkolsüz Kokteyller',
        'en' => 'Non-Alcoholic Cocktails',
        'de' => 'Alkoholfreie Cocktails',
        'ru' => 'Безалкогольные коктейли',
    ],
    [
        'tr' => 'Klasikler',
        'en' => 'Classics',
        'de' => 'Klassiker',
        'ru' => 'Классика',
    ],
    [
        'tr' => 'Yeni Nesil',
        'en' => 'New Generation',
        'de' => 'Neue Generation',
        'ru' => 'Новое поколение',
    ],
    [
        'tr' => 'Votkalar',
        'en' => 'Vodkas',
        'de' => 'Wodkas',
        'ru' => 'Водки',
    ],
    [
        'tr' => 'Viskiler',
        'en' => 'Whiskies',
        'de' => 'Whiskys',
        'ru' => 'Виски',
    ],
    [
        'tr' => 'Konyak & Brendi',
        'en' => 'Cognac & Brandy',
        'de' => 'Cognac & Brandy',
        'ru' => 'Коньяк & Бренди',
    ],
    [
        'tr' => 'Cin',
        'en' => 'Gin',
        'de' => 'Gin',
        'ru' => 'Джин',
    ],
    [
        'tr' => 'Likör',
        'en' => 'Liqueur',
        'de' => 'Likör',
        'ru' => 'Ликёр',
    ],
    [
        'tr' => 'Rom & Cachaça',
        'en' => 'Rum & Cachaça',
        'de' => 'Rum & Cachaça',
        'ru' => 'Ром & Кашаса',
    ],
    [
        'tr' => 'Tekilalar',
        'en' => 'Tequilas',
        'de' => 'Tequilas',
        'ru' => 'Текилы',
    ],
    [
        'tr' => 'Vermut',
        'en' => 'Vermouth',
        'de' => 'Wermut',
        'ru' => 'Вермут',
    ],
    [
        'tr' => 'Rakı',
        'en' => 'Raki',
        'de' => 'Raki',
        'ru' => 'Раки',
    ],
    [
        'tr' => 'Biralar',
        'en' => 'Beers',
        'de' => 'Biere',
        'ru' => 'Пиво',
    ],
    [
        'tr' => 'Aperatif',
        'en' => 'Aperitif',
        'de' => 'Aperitif',
        'ru' => 'Аперитив',
    ],
    [
        'tr' => 'Digestiv',
        'en' => 'Digestif',
        'de' => 'Digestif',
        'ru' => 'Дижестив',
    ],
];

$added   = 0;
$skipped = 0;

foreach ($categories as $i => $title) {
    // Duplicate kontrolü (Türkçe ada göre)
    $exists = QrMenuCategory::where('qr_menu_id', $qrMenuId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$title['tr']])
        ->exists();

    if ($exists) {
        echo "ATLA: '{$title['tr']}' zaten mevcut.\n";
        $skipped++;
        continue;
    }

    QrMenuCategory::create([
        'qr_menu_id' => $qrMenuId,
        'title'      => $title,
        'sort_order' => $maxSort + $i + 1,
        'is_active'  => true,
    ]);

    echo "EKLENDI: {$title['tr']}\n";
    $added++;
}

echo "\n✓ Tamamlandı: {$added} kategori eklendi, {$skipped} atlandı.\n";
