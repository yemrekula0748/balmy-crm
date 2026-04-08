<?php
/**
 * Balmy Beach â€” Yemek KÃ¼tÃ¼phanesi Kategorileri OluÅŸtur
 * ve mevcut FoodProduct'lara (ID 415-432) food_category_id + ingredients ekle
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodCategory;
use App\Models\FoodProduct;

$branchId = 1;

/* â”€â”€ 1. Kategorileri oluÅŸtur â”€â”€ */
$catDefs = [
    [
        'sort_order' => 1, 'icon' => 'ğŸ¥ª',
        'title' => ['tr'=>'BaÅŸlangÄ±Ã§lar','en'=>'Starters','de'=>'Vorspeisen','ru'=>'Ğ—Ğ°ĞºÑƒÑĞºĞ¸','ar'=>'Ø§Ù„Ù…Ù‚Ø¨Ù„Ø§Øª'],
    ],
    [
        'sort_order' => 2, 'icon' => 'ğŸ',
        'title' => ['tr'=>'Ara SÄ±caklar','en'=>'Hot Appetizers','de'=>'Warme Vorspeisen','ru'=>'Ğ“Ğ¾Ñ€ÑÑ‡Ğ¸Ğµ Ğ·Ğ°ĞºÑƒÑĞºĞ¸','ar'=>'Ø§Ù„Ù…Ù‚Ø¨Ù„Ø§Øª Ø§Ù„Ø³Ø§Ø®Ù†Ø©'],
    ],
    [
        'sort_order' => 3, 'icon' => 'ğŸ¥—',
        'title' => ['tr'=>'Salatalar','en'=>'Salads','de'=>'Salate','ru'=>'Ğ¡Ğ°Ğ»Ğ°Ñ‚Ñ‹','ar'=>'Ø§Ù„Ø³Ù„Ø·Ø§Øª'],
    ],
    [
        'sort_order' => 4, 'icon' => 'ğŸ½ï¸',
        'title' => ['tr'=>'Ana Yemekler','en'=>'Main Courses','de'=>'Hauptgerichte','ru'=>'ĞÑĞ½Ğ¾Ğ²Ğ½Ñ‹Ğµ Ğ±Ğ»ÑĞ´Ğ°','ar'=>'Ø§Ù„Ø£Ø·Ø¨Ø§Ù‚ Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠØ©'],
    ],
    [
        'sort_order' => 5, 'icon' => 'ğŸ®',
        'title' => ['tr'=>'TatlÄ±lar','en'=>'Desserts','de'=>'Desserts','ru'=>'Ğ”ĞµÑĞµÑ€Ñ‚Ñ‹','ar'=>'Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª'],
    ],
];

$cats = [];
foreach ($catDefs as $def) {
    $cat = FoodCategory::create([
        'branch_id'  => $branchId,
        'title'      => $def['title'],
        'icon'       => $def['icon'],
        'sort_order' => $def['sort_order'],
        'is_active'  => true,
    ]);
    $cats[$def['title']['tr']] = $cat->id;
    echo "Kategori oluÅŸturuldu: [{$cat->id}] " . $def['title']['tr'] . PHP_EOL;
}

/* â”€â”€ 2. ÃœrÃ¼n gÃ¼ncellemeleri â”€â”€ */
$updates = [
    // [food_product_id, category_title_tr, ingredients]
    [
        'id'         => 415,
        'cat'        => 'BaÅŸlangÄ±Ã§lar',
        'ingredients'=> [
            'tr' => 'Somon FÃ¼me, Roka, KÄ±rmÄ±zÄ± SoÄŸan, Patates Tava, Limon',
            'en' => 'Smoked Salmon, Arugula, Red Onion, Pan-Fried Potatoes, Lemon',
            'de' => 'GerÃ¤ucherter Lachs, Rucola, Rote Zwiebel, Bratkartoffeln, Zitrone',
            'ru' => 'ĞšĞ¾Ğ¿Ñ‡Ñ‘Ğ½Ñ‹Ğ¹ Ğ»Ğ¾ÑĞ¾ÑÑŒ, Ğ ÑƒĞºĞºĞ¾Ğ»Ğ°, ĞšÑ€Ğ°ÑĞ½Ñ‹Ğ¹ Ğ»ÑƒĞº, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ, Ğ›Ğ¸Ğ¼Ğ¾Ğ½',
            'ar' => 'Ø³Ù„Ù…ÙˆÙ† Ù…Ø¯Ø®Ù†ØŒ Ø¬Ø±Ø¬ÙŠØ±ØŒ Ø¨ØµÙ„ Ø£Ø­Ù…Ø±ØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©ØŒ Ù„ÙŠÙ…ÙˆÙ†',
        ],
    ],
    [
        'id'         => 416,
        'cat'        => 'BaÅŸlangÄ±Ã§lar',
        'ingredients'=> [
            'tr' => 'Baget EkmeÄŸi, Roast Beef, KorniÅŸon TurÅŸu, Hardal, Roka, Patates Tava',
            'en' => 'Baguette, Roast Beef, Gherkin Pickles, Mustard, Arugula, Pan-Fried Potatoes',
            'de' => 'Baguette, Roast Beef, Essiggurken, Senf, Rucola, Bratkartoffeln',
            'ru' => 'Ğ‘Ğ°Ğ³ĞµÑ‚, Ğ Ğ¾ÑÑ‚Ğ±Ğ¸Ñ„, ĞœĞ°Ñ€Ğ¸Ğ½Ğ¾Ğ²Ğ°Ğ½Ğ½Ñ‹Ğµ Ğ¾Ğ³ÑƒÑ€Ñ†Ñ‹, Ğ“Ğ¾Ñ€Ñ‡Ğ¸Ñ†Ğ°, Ğ ÑƒĞºĞºĞ¾Ğ»Ğ°, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ',
            'ar' => 'Ø®Ø¨Ø² Ø¨Ø§ØºÙŠØªØŒ Ø±ÙˆØ³ØªØ¨ÙŠÙØŒ Ù…Ø®Ù„Ù„ ÙƒÙˆØ±Ù†ÙŠØ´ÙˆÙ†ØŒ Ø®Ø±Ø¯Ù„ØŒ Ø¬Ø±Ø¬ÙŠØ±ØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©',
        ],
    ],
    [
        'id'         => 417,
        'cat'        => 'BaÅŸlangÄ±Ã§lar',
        'ingredients'=> [
            'tr' => 'KÄ±zarmÄ±ÅŸ Ekmek, Tavuk FÃ¼me GÃ¶ÄŸsÃ¼, Marul, Domates, Cheddar Peyniri, Mayonez, Patates Tava, KorniÅŸon TurÅŸu',
            'en' => 'Toasted Bread, Smoked Chicken Breast, Lettuce, Tomato, Cheddar Cheese, Mayonnaise, Pan-Fried Potatoes, Gherkin Pickles',
            'de' => 'GerÃ¶stetes Brot, GerÃ¤ucherte HÃ¤hnchenbrust, Salat, Tomate, Cheddar-KÃ¤se, Mayonnaise, Bratkartoffeln, Essiggurken',
            'ru' => 'Ğ¢Ğ¾ÑÑ‚, ĞšĞ¾Ğ¿Ñ‡Ñ‘Ğ½Ğ°Ñ ĞºÑƒÑ€Ğ¸Ğ½Ğ°Ñ Ğ³Ñ€ÑƒĞ´ĞºĞ°, Ğ¡Ğ°Ğ»Ğ°Ñ‚, ĞŸĞ¾Ğ¼Ğ¸Ğ´Ğ¾Ñ€, Ğ¡Ñ‹Ñ€ Ğ§ĞµĞ´Ğ´ĞµÑ€, ĞœĞ°Ğ¹Ğ¾Ğ½ĞµĞ·, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ, ĞœĞ°Ñ€Ğ¸Ğ½Ğ¾Ğ²Ğ°Ğ½Ğ½Ñ‹Ğµ Ğ¾Ğ³ÑƒÑ€Ñ†Ñ‹',
            'ar' => 'Ø®Ø¨Ø² Ù…Ø­Ù…ØµØŒ ØµØ¯Ø± Ø¯Ø¬Ø§Ø¬ Ù…Ø¯Ø®Ù†ØŒ Ø®Ø³ØŒ Ø·Ù…Ø§Ø·Ù…ØŒ Ø¬Ø¨Ù†Ø© Ø´ÙŠØ¯Ø±ØŒ Ù…Ø§ÙŠÙˆÙ†ÙŠØ²ØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©ØŒ Ù…Ø®Ù„Ù„ ÙƒÙˆØ±Ù†ÙŠØ´ÙˆÙ†',
        ],
    ],
    [
        'id'         => 418,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Penne Makarna, Taze Mantar, Parmesan Peyniri, Krema, SarÄ±msak, ZeytinyaÄŸÄ±, Tuz, Karabiber',
            'en' => 'Penne Pasta, Fresh Mushrooms, Parmesan Cheese, Cream, Garlic, Olive Oil, Salt, Black Pepper',
            'de' => 'Penne-Nudeln, Frische Champignons, ParmesankÃ¤se, Sahne, Knoblauch, OlivenÃ¶l, Salz, Schwarzer Pfeffer',
            'ru' => 'ĞŸĞ°ÑÑ‚Ğ° Ğ¿ĞµĞ½Ğ½Ğµ, Ğ¡Ğ²ĞµĞ¶Ğ¸Ğµ Ğ³Ñ€Ğ¸Ğ±Ñ‹, Ğ¡Ñ‹Ñ€ ĞŸĞ°Ñ€Ğ¼ĞµĞ·Ğ°Ğ½, Ğ¡Ğ»Ğ¸Ğ²ĞºĞ¸, Ğ§ĞµÑĞ½Ğ¾Ğº, ĞĞ»Ğ¸Ğ²ĞºĞ¾Ğ²Ğ¾Ğµ Ğ¼Ğ°ÑĞ»Ğ¾, Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'Ø¨Ø§Ø³ØªØ§ Ø¨ÙŠÙ†ÙŠØŒ ÙØ·Ø± Ø·Ø§Ø²Ø¬ØŒ Ø¬Ø¨Ù†Ø© Ø¨Ø§Ø±Ù…ÙŠØ²Ø§Ù†ØŒ ÙƒØ±ÙŠÙ…Ø©ØŒ Ø«ÙˆÙ…ØŒ Ø²ÙŠØª Ø²ÙŠØªÙˆÙ†ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 419,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Spagetti, Domates Sos, Taze FesleÄŸen, SarÄ±msak, ZeytinyaÄŸÄ±, Tuz, Karabiber',
            'en' => 'Spaghetti, Tomato Sauce, Fresh Basil, Garlic, Olive Oil, Salt, Black Pepper',
            'de' => 'Spaghetti, Tomatensauce, Frisches Basilikum, Knoblauch, OlivenÃ¶l, Salz, Schwarzer Pfeffer',
            'ru' => 'Ğ¡Ğ¿Ğ°Ğ³ĞµÑ‚Ñ‚Ğ¸, Ğ¢Ğ¾Ğ¼Ğ°Ñ‚Ğ½Ñ‹Ğ¹ ÑĞ¾ÑƒÑ, Ğ¡Ğ²ĞµĞ¶Ğ¸Ğ¹ Ğ±Ğ°Ğ·Ğ¸Ğ»Ğ¸Ğº, Ğ§ĞµÑĞ½Ğ¾Ğº, ĞĞ»Ğ¸Ğ²ĞºĞ¾Ğ²Ğ¾Ğµ Ğ¼Ğ°ÑĞ»Ğ¾, Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'Ø³Ø¨Ø§ØºÙŠØªÙŠØŒ ØµÙ„ØµØ© Ø·Ù…Ø§Ø·Ù…ØŒ Ø±ÙŠØ­Ø§Ù† Ø·Ø§Ø²Ø¬ØŒ Ø«ÙˆÙ…ØŒ Ø²ÙŠØª Ø²ÙŠØªÙˆÙ†ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 420,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Tortelini (Mozzarella, Ricotta, Parmesan, Gorgonzola dolgulu), Krema, TereyaÄŸÄ±, SarÄ±msak, Tuz, Karabiber',
            'en' => 'Tortellini (filled with Mozzarella, Ricotta, Parmesan, Gorgonzola), Cream, Butter, Garlic, Salt, Black Pepper',
            'de' => 'Tortellini (gefÃ¼llt mit Mozzarella, Ricotta, Parmesan, Gorgonzola), Sahne, Butter, Knoblauch, Salz, Schwarzer Pfeffer',
            'ru' => 'Ğ¢Ğ¾Ñ€Ñ‚ĞµĞ»Ğ»Ğ¸Ğ½Ğ¸ (Ñ Ğ½Ğ°Ñ‡Ğ¸Ğ½ĞºĞ¾Ğ¹ ĞœĞ¾Ñ†Ğ°Ñ€ĞµĞ»Ğ»Ğ°, Ğ Ğ¸ĞºĞ¾Ñ‚Ñ‚Ğ°, ĞŸĞ°Ñ€Ğ¼ĞµĞ·Ğ°Ğ½, Ğ“Ğ¾Ñ€Ğ³Ğ¾Ğ½Ğ·Ğ¾Ğ»Ğ°), Ğ¡Ğ»Ğ¸Ğ²ĞºĞ¸, ĞœĞ°ÑĞ»Ğ¾, Ğ§ĞµÑĞ½Ğ¾Ğº, Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'ØªÙˆØ±ØªÙŠÙ„ÙŠÙ†ÙŠ (Ù…Ø­Ø´Ùˆ Ø¨Ù…ÙˆØ²Ø§Ø±ÙŠÙ„Ø§ ÙˆØ±ÙŠÙƒÙˆØªØ§ ÙˆØ¨Ø§Ø±Ù…ÙŠØ²Ø§Ù† ÙˆØ¬ÙˆØ±Ø¬ÙˆÙ†Ø²ÙˆÙ„Ø§)ØŒ ÙƒØ±ÙŠÙ…Ø©ØŒ Ø²Ø¨Ø¯Ø©ØŒ Ø«ÙˆÙ…ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 421,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'BuÄŸday Unu Tortilla, Izgara Tavuk, Cheddar Peyniri, TatlÄ±-AcÄ± Biber Sosu, Soya Sosu, SoÄŸan, DolmalÄ±k Biber',
            'en' => 'Wheat Flour Tortilla, Grilled Chicken, Cheddar Cheese, Sweet-Chili Sauce, Soy Sauce, Onion, Bell Pepper',
            'de' => 'Weizentortilla, Gegrilltes HÃ¤hnchen, Cheddar-KÃ¤se, SÃ¼ÃŸ-scharfe Sauce, SojasoÃŸe, Zwiebel, Paprika',
            'ru' => 'ĞŸÑˆĞµĞ½Ğ¸Ñ‡Ğ½Ğ°Ñ Ñ‚Ğ¾Ñ€Ñ‚Ğ¸Ğ»ÑŒÑ, Ğ–Ğ°Ñ€ĞµĞ½Ğ°Ñ ĞºÑƒÑ€Ğ¸Ñ†Ğ°, Ğ¡Ñ‹Ñ€ Ğ§ĞµĞ´Ğ´ĞµÑ€, ĞšĞ¸ÑĞ»Ğ¾-ÑĞ»Ğ°Ğ´ĞºĞ¸Ğ¹ ÑĞ¾ÑƒÑ, Ğ¡Ğ¾ĞµĞ²Ñ‹Ğ¹ ÑĞ¾ÑƒÑ, Ğ›ÑƒĞº, Ğ‘Ğ¾Ğ»Ğ³Ğ°Ñ€ÑĞºĞ¸Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'Ø®Ø¨Ø² ØªÙˆØ±ØªÙŠÙ„Ø§ Ù‚Ù…Ø­ØŒ Ø¯Ø¬Ø§Ø¬ Ù…Ø´ÙˆÙŠØŒ Ø¬Ø¨Ù†Ø© Ø´ÙŠØ¯Ø±ØŒ ØµÙ„ØµØ© Ø­Ù„ÙˆØ© Ø­Ø§Ø±Ø©ØŒ ØµÙ„ØµØ© ØµÙˆÙŠØ§ØŒ Ø¨ØµÙ„ØŒ ÙÙ„ÙÙ„ Ø±ÙˆÙ…ÙŠ',
        ],
    ],
    [
        'id'         => 422,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Dana KÄ±yma KÃ¶fte, Susam Baget Ekmek (TereyaÄŸlÄ±), Marul, Domates, SoÄŸan, TurÅŸu, KetÃ§ap, Hardal, Patates Tava',
            'en' => 'Ground Beef Patty, Sesame Burger Bun (Buttered), Lettuce, Tomato, Onion, Pickles, Ketchup, Mustard, Pan-Fried Potatoes',
            'de' => 'Hackfleisch-Patty, SesamburgerbrÃ¶tchen (gebuttert), Salat, Tomate, Zwiebel, Essiggurken, Ketchup, Senf, Bratkartoffeln',
            'ru' => 'ĞšĞ¾Ñ‚Ğ»ĞµÑ‚Ğ° Ğ¸Ğ· Ğ³Ğ¾Ğ²ÑĞ¶ÑŒĞµĞ³Ğ¾ Ñ„Ğ°Ñ€ÑˆĞ°, Ğ‘ÑƒĞ»Ğ¾Ñ‡ĞºĞ° Ñ ĞºÑƒĞ½Ğ¶ÑƒÑ‚Ğ¾Ğ¼ (Ñ Ğ¼Ğ°ÑĞ»Ğ¾Ğ¼), Ğ¡Ğ°Ğ»Ğ°Ñ‚, ĞŸĞ¾Ğ¼Ğ¸Ğ´Ğ¾Ñ€, Ğ›ÑƒĞº, ĞœĞ°Ñ€Ğ¸Ğ½Ğ¾Ğ²Ğ°Ğ½Ğ½Ñ‹Ğµ Ğ¾Ğ³ÑƒÑ€Ñ†Ñ‹, ĞšĞµÑ‚Ñ‡ÑƒĞ¿, Ğ“Ğ¾Ñ€Ñ‡Ğ¸Ñ†Ğ°, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ',
            'ar' => 'ÙƒÙˆÙØªØ© Ù„Ø­Ù… Ø¨Ù‚Ø±ÙŠ Ù…ÙØ±ÙˆÙ…ØŒ Ø®Ø¨Ø² Ø¨Ø±ØºØ± Ø¨Ø§Ù„Ø³Ù…Ø³Ù… (Ø¨Ø§Ù„Ø²Ø¨Ø¯Ø©)ØŒ Ø®Ø³ØŒ Ø·Ù…Ø§Ø·Ù…ØŒ Ø¨ØµÙ„ØŒ Ù…Ø®Ù„Ù„ØŒ ÙƒØ§ØªØ´Ø¨ØŒ Ø®Ø±Ø¯Ù„ØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©',
        ],
    ],
    [
        'id'         => 423,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Tost EkmeÄŸi, Domates SalÃ§asÄ±, KaÅŸar Peyniri, Dana Sucuk, Patates Tava',
            'en' => 'Toast Bread, Tomato Paste, Kashar Cheese, Beef Sausage, Pan-Fried Potatoes',
            'de' => 'Toastbrot, Tomatenmark, KaÅŸar-KÃ¤se, Rindswurst, Bratkartoffeln',
            'ru' => 'Ğ¢Ğ¾ÑÑ‚Ğ¾Ğ²Ñ‹Ğ¹ Ñ…Ğ»ĞµĞ±, Ğ¢Ğ¾Ğ¼Ğ°Ñ‚Ğ½Ğ°Ñ Ğ¿Ğ°ÑÑ‚Ğ°, Ğ¡Ñ‹Ñ€ ĞšĞ°ÑˆĞ°Ñ€, Ğ“Ğ¾Ğ²ÑĞ¶ÑŒÑ ĞºĞ¾Ğ»Ğ±Ğ°ÑĞ°, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ',
            'ar' => 'Ø®Ø¨Ø² ØªÙˆØ³ØªØŒ Ù…Ø¹Ø¬ÙˆÙ† Ø·Ù…Ø§Ø·Ù…ØŒ Ø¬Ø¨Ù†Ø© ÙƒØ§Ø´Ø§Ø±ØŒ Ø³Ø¬Ù‚ Ø¨Ù‚Ø±ÙŠØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©',
        ],
    ],
    [
        'id'         => 424,
        'cat'        => 'Ara SÄ±caklar',
        'ingredients'=> [
            'tr' => 'Tost EkmeÄŸi, Domates SalÃ§asÄ±, KaÅŸar Peyniri, Patates Tava',
            'en' => 'Toast Bread, Tomato Paste, Kashar Cheese, Pan-Fried Potatoes',
            'de' => 'Toastbrot, Tomatenmark, KaÅŸar-KÃ¤se, Bratkartoffeln',
            'ru' => 'Ğ¢Ğ¾ÑÑ‚Ğ¾Ğ²Ñ‹Ğ¹ Ñ…Ğ»ĞµĞ±, Ğ¢Ğ¾Ğ¼Ğ°Ñ‚Ğ½Ğ°Ñ Ğ¿Ğ°ÑÑ‚Ğ°, Ğ¡Ñ‹Ñ€ ĞšĞ°ÑˆĞ°Ñ€, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ ĞºĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ',
            'ar' => 'Ø®Ø¨Ø² ØªÙˆØ³ØªØŒ Ù…Ø¹Ø¬ÙˆÙ† Ø·Ù…Ø§Ø·Ù…ØŒ Ø¬Ø¨Ù†Ø© ÙƒØ§Ø´Ø§Ø±ØŒ Ø¨Ø·Ø§Ø·Ø³ Ù…Ù‚Ù„ÙŠØ©',
        ],
    ],
    [
        'id'         => 425,
        'cat'        => 'Salatalar',
        'ingredients'=> [
            'tr' => 'Taze Roka, Avokado, KavrulmuÅŸ Badem, Narenciye (Portakal + Limon) Sosu, ZeytinyaÄŸÄ±, Tuz, Karabiber',
            'en' => 'Fresh Arugula, Avocado, Toasted Almonds, Citrus (Orange + Lemon) Dressing, Olive Oil, Salt, Black Pepper',
            'de' => 'Frischer Rucola, Avocado, GerÃ¶stete Mandeln, Zitrus (Orange + Zitrone) Dressing, OlivenÃ¶l, Salz, Schwarzer Pfeffer',
            'ru' => 'Ğ¡Ğ²ĞµĞ¶Ğ°Ñ Ñ€ÑƒĞºĞºĞ¾Ğ»Ğ°, ĞĞ²Ğ¾ĞºĞ°Ğ´Ğ¾, Ğ–Ğ°Ñ€ĞµĞ½Ñ‹Ğ¹ Ğ¼Ğ¸Ğ½Ğ´Ğ°Ğ»ÑŒ, Ğ¦Ğ¸Ñ‚Ñ€ÑƒÑĞ¾Ğ²Ğ°Ñ (Ğ°Ğ¿ĞµĞ»ÑŒÑĞ¸Ğ½ + Ğ»Ğ¸Ğ¼Ğ¾Ğ½) Ğ·Ğ°Ğ¿Ñ€Ğ°Ğ²ĞºĞ°, ĞĞ»Ğ¸Ğ²ĞºĞ¾Ğ²Ğ¾Ğµ Ğ¼Ğ°ÑĞ»Ğ¾, Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'Ø¬Ø±Ø¬ÙŠØ± Ø·Ø§Ø²Ø¬ØŒ Ø£ÙÙˆÙƒØ§Ø¯ÙˆØŒ Ù„ÙˆØ² Ù…Ø­Ù…ØµØŒ ØµÙ„ØµØ© Ø§Ù„Ø­Ù…Ø¶ÙŠØ§Øª (Ø¨Ø±ØªÙ‚Ø§Ù„ + Ù„ÙŠÙ…ÙˆÙ†)ØŒ Ø²ÙŠØª Ø²ÙŠØªÙˆÙ†ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 426,
        'cat'        => 'Salatalar',
        'ingredients'=> [
            'tr' => 'SalatalÄ±k, Domates, Siyah Zeytin, KÄ±rmÄ±zÄ± SoÄŸan, Limon Suyu, ZeytinyaÄŸÄ±, Tuz, Kuru Nane',
            'en' => 'Cucumber, Tomato, Black Olives, Red Onion, Lemon Juice, Olive Oil, Salt, Dried Mint',
            'de' => 'Gurke, Tomate, Schwarze Oliven, Rote Zwiebel, Zitronensaft, OlivenÃ¶l, Salz, Getrocknete Minze',
            'ru' => 'ĞĞ³ÑƒÑ€ĞµÑ†, ĞŸĞ¾Ğ¼Ğ¸Ğ´Ğ¾Ñ€, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğµ Ğ¾Ğ»Ğ¸Ğ²ĞºĞ¸, ĞšÑ€Ğ°ÑĞ½Ñ‹Ğ¹ Ğ»ÑƒĞº, Ğ›Ğ¸Ğ¼Ğ¾Ğ½Ğ½Ñ‹Ğ¹ ÑĞ¾Ğº, ĞĞ»Ğ¸Ğ²ĞºĞ¾Ğ²Ğ¾Ğµ Ğ¼Ğ°ÑĞ»Ğ¾, Ğ¡Ğ¾Ğ»ÑŒ, Ğ¡ÑƒÑ…Ğ°Ñ Ğ¼ÑÑ‚Ğ°',
            'ar' => 'Ø®ÙŠØ§Ø±ØŒ Ø·Ù…Ø§Ø·Ù…ØŒ Ø²ÙŠØªÙˆÙ† Ø£Ø³ÙˆØ¯ØŒ Ø¨ØµÙ„ Ø£Ø­Ù…Ø±ØŒ Ø¹ØµÙŠØ± Ù„ÙŠÙ…ÙˆÙ†ØŒ Ø²ÙŠØª Ø²ÙŠØªÙˆÙ†ØŒ Ù…Ù„Ø­ØŒ Ù†Ø¹Ù†Ø§Ø¹ Ù…Ø¬ÙÙ',
        ],
    ],
    [
        'id'         => 427,
        'cat'        => 'Ana Yemekler',
        'ingredients'=> [
            'tr' => 'Tavuk GÃ¶ÄŸsÃ¼, YoÄŸurt, Baharatlar (Kimyon, Pul Biber, Kekik), Kabak, HavuÃ§, SoÄŸan (Sote), Tahin, Limon Suyu',
            'en' => 'Chicken Breast, Yogurt, Spices (Cumin, Red Pepper Flakes, Thyme), Zucchini, Carrot, Onion (SautÃ©ed), Tahini, Lemon Juice',
            'de' => 'HÃ¤hnchenbrust, Joghurt, GewÃ¼rze (KreuzkÃ¼mmel, Chiliflocken, Thymian), Zucchini, Karotte, Zwiebel (Sautiert), Tahini, Zitronensaft',
            'ru' => 'ĞšÑƒÑ€Ğ¸Ğ½Ğ°Ñ Ğ³Ñ€ÑƒĞ´ĞºĞ°, Ğ™Ğ¾Ğ³ÑƒÑ€Ñ‚, Ğ¡Ğ¿ĞµÑ†Ğ¸Ğ¸ (Ğ—Ğ¸Ñ€Ğ°, ĞŸĞ°Ğ¿Ñ€Ğ¸ĞºĞ°, Ğ¢Ğ¸Ğ¼ÑŒÑĞ½), ĞšĞ°Ğ±Ğ°Ñ‡Ğ¾Ğº, ĞœĞ¾Ñ€ĞºĞ¾Ğ²ÑŒ, Ğ›ÑƒĞº (Ñ‚ÑƒÑˆÑ‘Ğ½Ñ‹Ğ¹), Ğ¢Ğ°Ñ…Ğ¸Ğ½Ğ¸, Ğ›Ğ¸Ğ¼Ğ¾Ğ½Ğ½Ñ‹Ğ¹ ÑĞ¾Ğº',
            'ar' => 'ØµØ¯Ø± Ø¯Ø¬Ø§Ø¬ØŒ Ø²Ø¨Ø§Ø¯ÙŠØŒ Ø¨Ù‡Ø§Ø±Ø§Øª (ÙƒÙ…ÙˆÙ†ØŒ ÙÙ„ÙÙ„ Ø£Ø­Ù…Ø± Ù…Ø¬ÙÙØŒ Ø²Ø¹ØªØ±)ØŒ ÙƒÙˆØ³Ø§ØŒ Ø¬Ø²Ø±ØŒ Ø¨ØµÙ„ (Ù…Ù‚Ù„ÙŠ)ØŒ Ø·Ø­ÙŠÙ†Ø©ØŒ Ø¹ØµÙŠØ± Ù„ÙŠÙ…ÙˆÙ†',
        ],
    ],
    [
        'id'         => 428,
        'cat'        => 'Ana Yemekler',
        'ingredients'=> [
            'tr' => 'Levrek Fileto, TereyaÄŸ, Limon Suyu, SarÄ±msak, Taze Kekik, Mevsim Sebzeleri (Kabak, HavuÃ§, Brokoli), Tuz, Karabiber',
            'en' => 'Sea Bass Fillet, Butter, Lemon Juice, Garlic, Fresh Thyme, Seasonal Vegetables (Zucchini, Carrot, Broccoli), Salt, Black Pepper',
            'de' => 'Wolfsbarschfilet, Butter, Zitronensaft, Knoblauch, Frischer Thymian, SaisongemÃ¼se (Zucchini, Karotte, Brokkoli), Salz, Schwarzer Pfeffer',
            'ru' => 'Ğ¤Ğ¸Ğ»Ğµ Ğ¼Ğ¾Ñ€ÑĞºĞ¾Ğ³Ğ¾ Ğ¾ĞºÑƒĞ½Ñ, ĞœĞ°ÑĞ»Ğ¾, Ğ›Ğ¸Ğ¼Ğ¾Ğ½Ğ½Ñ‹Ğ¹ ÑĞ¾Ğº, Ğ§ĞµÑĞ½Ğ¾Ğº, Ğ¡Ğ²ĞµĞ¶Ğ¸Ğ¹ Ñ‚Ğ¸Ğ¼ÑŒÑĞ½, Ğ¡ĞµĞ·Ğ¾Ğ½Ğ½Ñ‹Ğµ Ğ¾Ğ²Ğ¾Ñ‰Ğ¸ (ĞšĞ°Ğ±Ğ°Ñ‡Ğ¾Ğº, ĞœĞ¾Ñ€ĞºĞ¾Ğ²ÑŒ, Ğ‘Ñ€Ğ¾ĞºĞºĞ¾Ğ»Ğ¸), Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'ÙÙŠÙ„ÙŠÙ‡ Ø³Ù…Ùƒ Ù‚Ø§Ø±ÙˆØµØŒ Ø²Ø¨Ø¯Ø©ØŒ Ø¹ØµÙŠØ± Ù„ÙŠÙ…ÙˆÙ†ØŒ Ø«ÙˆÙ…ØŒ Ø²Ø¹ØªØ± Ø·Ø§Ø²Ø¬ØŒ Ø®Ø¶Ø±ÙˆØ§Øª Ù…ÙˆØ³Ù…ÙŠØ© (ÙƒÙˆØ³Ø§ØŒ Ø¬Ø²Ø±ØŒ Ø¨Ø±ÙˆÙƒÙ„ÙŠ)ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 429,
        'cat'        => 'Ana Yemekler',
        'ingredients'=> [
            'tr' => 'Dana Bonfile, ZeytinyaÄŸÄ±, SarÄ±msak, Taze Biberiye, Taze Kekik, Patates, Mevsim Sebzeleri (DolmalÄ±k Biber, Kabak, SoÄŸan), Tuz, Karabiber',
            'en' => 'Beef Tenderloin, Olive Oil, Garlic, Fresh Rosemary, Fresh Thyme, Potatoes, Seasonal Vegetables (Bell Pepper, Zucchini, Onion), Salt, Black Pepper',
            'de' => 'Rinderfilet, OlivenÃ¶l, Knoblauch, Frischer Rosmarin, Frischer Thymian, Kartoffeln, SaisongemÃ¼se (Paprika, Zucchini, Zwiebel), Salz, Schwarzer Pfeffer',
            'ru' => 'Ğ“Ğ¾Ğ²ÑĞ¶ÑŒÑ Ğ²Ñ‹Ñ€ĞµĞ·ĞºĞ°, ĞĞ»Ğ¸Ğ²ĞºĞ¾Ğ²Ğ¾Ğµ Ğ¼Ğ°ÑĞ»Ğ¾, Ğ§ĞµÑĞ½Ğ¾Ğº, Ğ¡Ğ²ĞµĞ¶Ğ¸Ğ¹ Ñ€Ğ¾Ğ·Ğ¼Ğ°Ñ€Ğ¸Ğ½, Ğ¡Ğ²ĞµĞ¶Ğ¸Ğ¹ Ñ‚Ğ¸Ğ¼ÑŒÑĞ½, ĞšĞ°Ñ€Ñ‚Ğ¾Ñ„ĞµĞ»ÑŒ, Ğ¡ĞµĞ·Ğ¾Ğ½Ğ½Ñ‹Ğµ Ğ¾Ğ²Ğ¾Ñ‰Ğ¸ (Ğ‘Ğ¾Ğ»Ğ³Ğ°Ñ€ÑĞºĞ¸Ğ¹ Ğ¿ĞµÑ€ĞµÑ†, ĞšĞ°Ğ±Ğ°Ñ‡Ğ¾Ğº, Ğ›ÑƒĞº), Ğ¡Ğ¾Ğ»ÑŒ, Ğ§Ñ‘Ñ€Ğ½Ñ‹Ğ¹ Ğ¿ĞµÑ€ĞµÑ†',
            'ar' => 'ÙÙŠÙ„ÙŠÙ‡ Ø¨Ù‚Ø±ÙŠØŒ Ø²ÙŠØª Ø²ÙŠØªÙˆÙ†ØŒ Ø«ÙˆÙ…ØŒ Ø¥ÙƒÙ„ÙŠÙ„ Ø§Ù„Ø¬Ø¨Ù„ Ø§Ù„Ø·Ø§Ø²Ø¬ØŒ Ø²Ø¹ØªØ± Ø·Ø§Ø²Ø¬ØŒ Ø¨Ø·Ø§Ø·Ø³ØŒ Ø®Ø¶Ø±ÙˆØ§Øª Ù…ÙˆØ³Ù…ÙŠØ© (ÙÙ„ÙÙ„ Ø±ÙˆÙ…ÙŠØŒ ÙƒÙˆØ³Ø§ØŒ Ø¨ØµÙ„)ØŒ Ù…Ù„Ø­ØŒ ÙÙ„ÙÙ„ Ø£Ø³ÙˆØ¯',
        ],
    ],
    [
        'id'         => 430,
        'cat'        => 'TatlÄ±lar',
        'ingredients'=> [
            'tr' => 'Tam YaÄŸlÄ± SÃ¼t, PirinÃ§, Åeker, Yumurta SarÄ±sÄ±, Vanilya, VanilyalÄ± Dondurma',
            'en' => 'Whole Milk, Rice, Sugar, Egg Yolk, Vanilla, Vanilla Ice Cream',
            'de' => 'Vollmilch, Reis, Zucker, Eigelb, Vanille, Vanilleeis',
            'ru' => 'Ğ¦ĞµĞ»ÑŒĞ½Ğ¾Ğµ Ğ¼Ğ¾Ğ»Ğ¾ĞºĞ¾, Ğ Ğ¸Ñ, Ğ¡Ğ°Ñ…Ğ°Ñ€, Ğ¯Ğ¸Ñ‡Ğ½Ñ‹Ğ¹ Ğ¶ĞµĞ»Ñ‚Ğ¾Ğº, Ğ’Ğ°Ğ½Ğ¸Ğ»ÑŒ, Ğ’Ğ°Ğ½Ğ¸Ğ»ÑŒĞ½Ğ¾Ğµ Ğ¼Ğ¾Ñ€Ğ¾Ğ¶ĞµĞ½Ğ¾Ğµ',
            'ar' => 'Ø­Ù„ÙŠØ¨ ÙƒØ§Ù…Ù„ Ø§Ù„Ø¯Ø³Ù…ØŒ Ø£Ø±Ø²ØŒ Ø³ÙƒØ±ØŒ ØµÙØ§Ø± Ø¨ÙŠØ¶ØŒ ÙØ§Ù†ÙŠÙ„ÙŠØ§ØŒ Ø¢ÙŠØ³ ÙƒØ±ÙŠÙ… ÙØ§Ù†ÙŠÙ„ÙŠØ§',
        ],
    ],
    [
        'id'         => 431,
        'cat'        => 'TatlÄ±lar',
        'ingredients'=> [
            'tr' => 'Mevsiminde taze meyveler (Kavun, Karpuz, Ã‡ilek, Ananas, Kivi, Portakal)',
            'en' => 'Seasonal fresh fruits (Melon, Watermelon, Strawberry, Pineapple, Kiwi, Orange)',
            'de' => 'Saisonale frische FrÃ¼chte (Melone, Wassermelone, Erdbeere, Ananas, Kiwi, Orange)',
            'ru' => 'Ğ¡ĞµĞ·Ğ¾Ğ½Ğ½Ñ‹Ğµ ÑĞ²ĞµĞ¶Ğ¸Ğµ Ñ„Ñ€ÑƒĞºÑ‚Ñ‹ (Ğ”Ñ‹Ğ½Ñ, ĞÑ€Ğ±ÑƒĞ·, ĞšĞ»ÑƒĞ±Ğ½Ğ¸ĞºĞ°, ĞĞ½Ğ°Ğ½Ğ°Ñ, ĞšĞ¸Ğ²Ğ¸, ĞĞ¿ĞµĞ»ÑŒÑĞ¸Ğ½)',
            'ar' => 'ÙÙˆØ§ÙƒÙ‡ Ø·Ø§Ø²Ø¬Ø© Ù…ÙˆØ³Ù…ÙŠØ© (Ø´Ù…Ø§Ù…ØŒ Ø¨Ø·ÙŠØ®ØŒ ÙØ±Ø§ÙˆÙ„Ø©ØŒ Ø£Ù†Ø§Ù†Ø§Ø³ØŒ ÙƒÙŠÙˆÙŠØŒ Ø¨Ø±ØªÙ‚Ø§Ù„)',
        ],
    ],
    [
        'id'         => 432,
        'cat'        => 'TatlÄ±lar',
        'ingredients'=> [
            'tr' => 'TereyaÄŸÄ±, Bitter Ã‡ikolata, Åeker, Yumurta, Un, Ceviz, Kabartma Tozu, VanilyalÄ± Dondurma',
            'en' => 'Butter, Dark Chocolate, Sugar, Eggs, Flour, Walnuts, Baking Powder, Vanilla Ice Cream',
            'de' => 'Butter, Dunkle Schokolade, Zucker, Eier, Mehl, WalnÃ¼sse, Backpulver, Vanilleeis',
            'ru' => 'ĞœĞ°ÑĞ»Ğ¾, Ğ¢Ñ‘Ğ¼Ğ½Ñ‹Ğ¹ ÑˆĞ¾ĞºĞ¾Ğ»Ğ°Ğ´, Ğ¡Ğ°Ñ…Ğ°Ñ€, Ğ¯Ğ¹Ñ†Ğ°, ĞœÑƒĞºĞ°, Ğ“Ñ€ĞµÑ†ĞºĞ¸Ğµ Ğ¾Ñ€ĞµÑ…Ğ¸, Ğ Ğ°Ğ·Ñ€Ñ‹Ñ…Ğ»Ğ¸Ñ‚ĞµĞ»ÑŒ, Ğ’Ğ°Ğ½Ğ¸Ğ»ÑŒĞ½Ğ¾Ğµ Ğ¼Ğ¾Ñ€Ğ¾Ğ¶ĞµĞ½Ğ¾Ğµ',
            'ar' => 'Ø²Ø¨Ø¯Ø©ØŒ Ø´ÙˆÙƒÙˆÙ„Ø§ØªØ© Ø¯Ø§ÙƒÙ†Ø©ØŒ Ø³ÙƒØ±ØŒ Ø¨ÙŠØ¶ØŒ Ø¯Ù‚ÙŠÙ‚ØŒ Ø¬ÙˆØ²ØŒ Ø¨ÙˆØ¯Ø±Ø© Ø¨ÙŠÙƒÙ†Ø¬ØŒ Ø¢ÙŠØ³ ÙƒØ±ÙŠÙ… ÙØ§Ù†ÙŠÙ„ÙŠØ§',
        ],
    ],
];

echo PHP_EOL . "ÃœrÃ¼nler gÃ¼ncelleniyor..." . PHP_EOL;
foreach ($updates as $u) {
    $fp = FoodProduct::find($u['id']);
    if (!$fp) {
        echo "  âœ— FoodProduct #{$u['id']} bulunamadÄ±!" . PHP_EOL;
        continue;
    }
    $fp->food_category_id = $cats[$u['cat']];
    $fp->ingredients      = $u['ingredients'];
    $fp->save();
    echo "  âœ“ [{$u['id']}] " . $fp->title['tr'] . " â†’ Kategori: {$u['cat']} (#{$cats[$u['cat']]})" . PHP_EOL;
}

echo PHP_EOL . "TamamlandÄ±!" . PHP_EOL;


