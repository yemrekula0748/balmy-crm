<?php
/**
 * Balmy Foresta (Branch 2) — WAO ASYA ALACARTE
 * Yemek Kütüphanesine ürünleri ekler.
 *
 * Kategori eşleşmesi (branch 2):
 *  45 → Sushi
 *  29 → Çorbalar
 *  34 → Ara Sıcaklar
 *  36 → Salatalar
 *  46 → Pilav
 *  49 → Ana Yemekler
 *  50 → Tatlılar
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$branchId = 2;

$products = [

    /* ── SUSHİ (cat 45) ─────────────────────────────────────── */
    [
        'food_category_id' => 45,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Somon maki',
            'en' => 'Salmon maki',
            'de' => 'Lachs-Maki',
            'ru' => 'Лосось маки',
        ],
        'description' => [
            'tr' => '',
            'en' => '',
            'de' => '',
            'ru' => '',
        ],
        'ingredients' => [
            'tr' => 'Sushi pilavı, nori yosun, salatalık, mayonez, krem peynir, taze Norveç somonu',
            'en' => 'Sushi rice, nori seaweed, cucumber, mayonnaise, cream cheese, fresh Norwegian salmon',
            'de' => 'Sushireis, Nori-Algen, Gurke, Mayonnaise, Frischkäse, frischer norwegischer Lachs',
            'ru' => 'Рис для суши, листья нори, огурец, майонез, сливочный сыр, свежий норвежский лосось',
        ],
        'allergens' => ['gluten', 'balik', 'soya', 'sut', 'yumurta'],
    ],
    [
        'food_category_id' => 45,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Avokado maki',
            'en' => 'Avocado maki',
            'de' => 'Avocado-Maki',
            'ru' => 'Авокадо маки',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Sushi pilavı, nori yosun, salatalık, mayonez, krem peynir, avokado',
            'en' => 'Sushi rice, nori seaweed, cucumber, mayonnaise, cream cheese, avocado',
            'de' => 'Sushireis, Nori-Algen, Gurke, Mayonnaise, Frischkäse, Avocado',
            'ru' => 'Рис для суши, листья нори, огурец, майонез, сливочный сыр, авокадо',
        ],
        'allergens' => ['gluten', 'soya', 'sut', 'yumurta'],
    ],
    [
        'food_category_id' => 45,
        'sort_order'       => 3,
        'title' => [
            'tr' => 'California roll',
            'en' => 'California roll',
            'de' => 'California Roll',
            'ru' => 'Калифорния ролл',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Sushi pilavı, nori yosun, kırmızı tobiko, salatalık, krem peynir, mayonez',
            'en' => 'Sushi rice, nori seaweed, red tobiko, cucumber, cream cheese, mayonnaise',
            'de' => 'Sushireis, Nori-Algen, roter Tobiko, Gurke, Frischkäse, Mayonnaise',
            'ru' => 'Рис для суши, листья нори, красный тобико, огурец, сливочный сыр, майонез',
        ],
        'allergens' => ['gluten', 'balik', 'soya', 'sut', 'yumurta'],
    ],
    [
        'food_category_id' => 45,
        'sort_order'       => 4,
        'title' => [
            'tr' => 'Philadelphia roll',
            'en' => 'Philadelphia roll',
            'de' => 'Philadelphia Roll',
            'ru' => 'Филадельфия ролл',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Sushi pilavı, nori yosun, somon dilim, salatalık, avokado, krem peynir, kavrulmuş susam',
            'en' => 'Sushi rice, nori seaweed, salmon slice, cucumber, avocado, cream cheese, toasted sesame',
            'de' => 'Sushireis, Nori-Algen, Lachsscheibe, Gurke, Avocado, Frischkäse, gerösteter Sesam',
            'ru' => 'Рис для суши, листья нори, ломтик лосося, огурец, авокадо, сливочный сыр, жареный кунжут',
        ],
        'allergens' => ['gluten', 'balik', 'susam', 'soya', 'sut', 'yumurta'],
    ],
    [
        'food_category_id' => 45,
        'sort_order'       => 5,
        'title' => [
            'tr' => 'Karides nigiri',
            'en' => 'Prawn nigiri',
            'de' => 'Garnelen-Nigiri',
            'ru' => 'Нигири с креветками',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Sushi pilavı, nori yosun, mayonez, haşlanmış karides',
            'en' => 'Sushi rice, nori seaweed, mayonnaise, boiled prawn',
            'de' => 'Sushireis, Nori-Algen, Mayonnaise, gekochte Garnele',
            'ru' => 'Рис для суши, листья нори, майонез, отварная креветка',
        ],
        'allergens' => ['gluten', 'kabuklu', 'soya', 'yumurta'],
    ],
    [
        'food_category_id' => 45,
        'sort_order'       => 6,
        'title' => [
            'tr' => 'Somon nigiri',
            'en' => 'Salmon nigiri',
            'de' => 'Lachs-Nigiri',
            'ru' => 'Нигири с лососем',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Sushi pilavı, soya sos, mayonez, nori yosun',
            'en' => 'Sushi rice, soy sauce, mayonnaise, nori seaweed',
            'de' => 'Sushireis, Sojasoße, Mayonnaise, Nori-Algen',
            'ru' => 'Рис для суши, соевый соус, майонез, листья нори',
        ],
        'allergens' => ['gluten', 'balik', 'soya', 'yumurta'],
    ],

    /* ── ÇORBALAR (cat 29) ──────────────────────────────────── */
    [
        'food_category_id' => 29,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Tom yum çorba',
            'en' => 'Tom yum soup',
            'de' => 'Tom-Yum-Suppe',
            'ru' => 'Суп том ям',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Karides, kalamar, balık çeşitleri, istiridye mantarı, acı biber, havuç, maydanoz',
            'en' => 'Prawn, squid, assorted fish, oyster mushroom, chilli, carrot, parsley',
            'de' => 'Garnelen, Tintenfisch, verschiedene Fische, Austernpilze, Chili, Karotte, Petersilie',
            'ru' => 'Креветки, кальмар, ассорти из рыбы, вешенки, перец чили, морковь, петрушка',
        ],
        'allergens' => ['balik', 'kabuklu', 'yumusakcalar'],
    ],
    [
        'food_category_id' => 29,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Tai çorba',
            'en' => 'Thai soup',
            'de' => 'Thaisuppe',
            'ru' => 'Тайский суп',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Kızarmış dana eti, kuru soğan, sarımsak, zencefil, biber, havuç, kabak, yumurta, susam yağı',
            'en' => 'Fried beef, dried onion, garlic, ginger, pepper, carrot, zucchini, egg, sesame oil',
            'de' => 'Gebratenes Rindfleisch, getrocknete Zwiebel, Knoblauch, Ingwer, Pfeffer, Karotte, Zucchini, Ei, Sesamöl',
            'ru' => 'Жареная говядина, сухой лук, чеснок, имбирь, перец, морковь, кабачок, яйцо, кунжутное масло',
        ],
        'allergens' => ['yumurta', 'susam', 'soya'],
    ],

    /* ── ARA SICAKLAR (cat 34) ──────────────────────────────── */
    [
        'food_category_id' => 34,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Çin böreği',
            'en' => 'Chinese spring roll',
            'de' => 'Chinesische Frühlingsrolle',
            'ru' => 'Китайские блинчики с начинкой',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Beyaz lahana, soya filizi, havuç, yeşil soğan, kabak, tatlı ekşi sos',
            'en' => 'White cabbage, bean sprouts, carrot, spring onion, zucchini, sweet and sour sauce',
            'de' => 'Weißkohl, Sojasprossen, Karotte, Frühlingszwiebeln, Zucchini, Süß-Sauer-Sauce',
            'ru' => 'Белокочанная капуста, ростки сои, морковь, зелёный лук, кабачок, кисло-сладкий соус',
        ],
        'allergens' => ['gluten', 'soya'],
    ],
    [
        'food_category_id' => 34,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Kızarmış Çin mantısı',
            'en' => 'Fried Chinese dumplings',
            'de' => 'Gebratene chinesische Teigtaschen',
            'ru' => 'Жареные китайские пельмени',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'İnce çıtır hamur içinde, dana kıyması, taze soğan, sarımsak, soya sos',
            'en' => 'Crispy thin dough filled with ground beef, spring onion, garlic, soy sauce',
            'de' => 'Knuspriger dünner Teig gefüllt mit Rinderhackfleisch, Frühlingszwiebeln, Knoblauch, Sojasoße',
            'ru' => 'Хрустящее тонкое тесто с говяжьим фаршем, зелёным луком, чесноком, соевым соусом',
        ],
        'allergens' => ['gluten', 'soya'],
    ],
    [
        'food_category_id' => 34,
        'sort_order'       => 3,
        'title' => [
            'tr' => 'Sebzeli noodle',
            'en' => 'Vegetable noodles',
            'de' => 'Gemüsenudeln',
            'ru' => 'Лапша с овощами',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Jülyen doğranmış soğan, havuç, kabak, mantar, susam yağı, soya sos',
            'en' => 'Julienned onion, carrot, zucchini, mushroom, sesame oil, soy sauce',
            'de' => 'Julienne-Zwiebeln, Karotte, Zucchini, Pilze, Sesamöl, Sojasoße',
            'ru' => 'Лук соломкой, морковь, кабачок, грибы, кунжутное масло, соевый соус',
        ],
        'allergens' => ['gluten', 'soya', 'susam'],
    ],
    [
        'food_category_id' => 34,
        'sort_order'       => 4,
        'title' => [
            'tr' => 'Karidesli noodle',
            'en' => 'Prawn noodles',
            'de' => 'Garnelennudeln',
            'ru' => 'Лапша с креветками',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Karides, soğan, havuç, kabak, mantar, susam yağı, soya sos',
            'en' => 'Prawn, onion, carrot, zucchini, mushroom, sesame oil, soy sauce',
            'de' => 'Garnelen, Zwiebel, Karotte, Zucchini, Pilze, Sesamöl, Sojasoße',
            'ru' => 'Креветки, лук, морковь, кабачок, грибы, кунжутное масло, соевый соус',
        ],
        'allergens' => ['gluten', 'kabuklu', 'soya', 'susam'],
    ],
    [
        'food_category_id' => 34,
        'sort_order'       => 5,
        'title' => [
            'tr' => 'Etli noodle',
            'en' => 'Beef noodles',
            'de' => 'Rindernudeln',
            'ru' => 'Лапша с говядиной',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Jülyen dana eti, soğan, havuç, kabak, mantar, susam yağı, soya sos',
            'en' => 'Julienned beef, onion, carrot, zucchini, mushroom, sesame oil, soy sauce',
            'de' => 'Julienne-Rindfleisch, Zwiebel, Karotte, Zucchini, Pilze, Sesamöl, Sojasoße',
            'ru' => 'Говядина соломкой, лук, морковь, кабачок, грибы, кунжутное масло, соевый соус',
        ],
        'allergens' => ['gluten', 'soya', 'susam'],
    ],

    /* ── SALATALAR (cat 36) ─────────────────────────────────── */
    [
        'food_category_id' => 36,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Soya soslu yeşil salata',
            'en' => 'Green salad with soy sauce',
            'de' => 'Grüner Salat mit Sojasoße',
            'ru' => 'Зелёный салат с соевым соусом',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Meskülen salata, yeşil elma, ananas, mor soğan, soya sos',
            'en' => 'Mesclun salad, green apple, pineapple, red onion, soy sauce',
            'de' => 'Mischsalat, grüner Apfel, Ananas, rote Zwiebel, Sojasoße',
            'ru' => 'Микс-салат, зелёное яблоко, ананас, красный лук, соевый соус',
        ],
        'allergens' => ['soya'],
    ],
    [
        'food_category_id' => 36,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Wakame salata',
            'en' => 'Wakame salad',
            'de' => 'Wakame-Salat',
            'ru' => 'Салат вакамэ',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Jülyen doğranmış havuç, salatalık, beyaz lahana, susam, zeytin yağlı soya sos',
            'en' => 'Julienned carrot, cucumber, white cabbage, sesame, soy sauce with olive oil',
            'de' => 'Julienne-Karotte, Gurke, Weißkohl, Sesam, Sojasoße mit Olivenöl',
            'ru' => 'Морковь соломкой, огурец, белокочанная капуста, кунжут, соевый соус с оливковым маслом',
        ],
        'allergens' => ['soya', 'susam'],
    ],

    /* ── PİLAV (cat 46) ─────────────────────────────────────── */
    [
        'food_category_id' => 46,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Haşlanmış pilav',
            'en' => 'Boiled rice',
            'de' => 'Gekochter Reis',
            'ru' => 'Отварной рис',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Kanton usulü pilav, basmati pirinç, yeşil soğan, havuç, kabak, haşlanmış yumurta',
            'en' => 'Canton style rice, basmati rice, spring onion, carrot, zucchini, boiled egg',
            'de' => 'Kantonesischer Reis, Basmati-Reis, Frühlingszwiebeln, Karotte, Zucchini, gekochtes Ei',
            'ru' => 'Рис по-кантонски, рис басмати, зелёный лук, морковь, кабачок, варёное яйцо',
        ],
        'allergens' => ['yumurta'],
    ],

    /* ── ANA YEMEKLER (cat 49) ──────────────────────────────── */
    [
        'food_category_id' => 49,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'İstiridye soslu dana brokoli',
            'en' => 'Beef broccoli with oyster sauce',
            'de' => 'Rindfleisch-Brokkoli mit Austernsoße',
            'ru' => 'Говядина с брокколи в устричном соусе',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Dana eti, brokoli, havuç, istiridye sosu, soğan, şitaki mantarı',
            'en' => 'Beef, broccoli, carrot, oyster sauce, onion, shiitake mushroom',
            'de' => 'Rindfleisch, Brokkoli, Karotte, Austernsoße, Zwiebel, Shiitake-Pilze',
            'ru' => 'Говядина, брокколи, морковь, устричный соус, лук, грибы шиитаке',
        ],
        'allergens' => ['soya', 'yumusakcalar'],
    ],
    [
        'food_category_id' => 49,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Sizzling dana eti',
            'en' => 'Sizzling beef',
            'de' => 'Sizzling Rindfleisch',
            'ru' => 'Шипящая говядина',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Biber, yeşil soğan, acı sos',
            'en' => 'Pepper, spring onion, spicy sauce',
            'de' => 'Paprika, Frühlingszwiebeln, scharfe Soße',
            'ru' => 'Перец, зелёный лук, острый соус',
        ],
        'allergens' => ['soya'],
    ],
    [
        'food_category_id' => 49,
        'sort_order'       => 3,
        'title' => [
            'tr' => 'Pekin ördeği',
            'en' => 'Peking duck',
            'de' => 'Pekingente',
            'ru' => 'Утка по-пекински',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Kızarmış çıtır Pekin ördeği, taze jülyen doğranmış salatalık, pırasa, yufka ekmeği, hoisin sos',
            'en' => 'Crispy roasted Peking duck, fresh julienned cucumber, leek, thin pancake, hoisin sauce',
            'de' => 'Knusprig gebratene Pekingente, frische Julienne-Gurke, Lauch, dünnes Pfannkuchenblatt, Hoisin-Soße',
            'ru' => 'Хрустящая жареная утка по-пекински, свежий огурец соломкой, лук-порей, тонкий блинчик, соус хойсин',
        ],
        'allergens' => ['gluten', 'soya'],
    ],
    [
        'food_category_id' => 49,
        'sort_order'       => 4,
        'title' => [
            'tr' => 'Tatlı ekşi soslu tavuk',
            'en' => 'Sweet and sour chicken',
            'de' => 'Süß-Sauer-Huhn',
            'ru' => 'Курица в кисло-сладком соусе',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Havuç, salatalık, kuru soğan, ananas',
            'en' => 'Carrot, cucumber, dried onion, pineapple',
            'de' => 'Karotte, Gurke, getrocknete Zwiebel, Ananas',
            'ru' => 'Морковь, огурец, сушёный лук, ананас',
        ],
        'allergens' => ['gluten', 'soya', 'yumurta'],
    ],
    [
        'food_category_id' => 49,
        'sort_order'       => 5,
        'title' => [
            'tr' => 'Chilli Jumbo karides',
            'en' => 'Chilli Jumbo prawn',
            'de' => 'Chilli-Jumbo-Garnele',
            'ru' => 'Креветки чили джамбо',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Acı soslu karides',
            'en' => 'Prawn in spicy sauce',
            'de' => 'Garnelen in scharfer Soße',
            'ru' => 'Креветки в остром соусе',
        ],
        'allergens' => ['kabuklu', 'soya'],
    ],

    /* ── TATLILAR (cat 50) ──────────────────────────────────── */
    [
        'food_category_id' => 50,
        'sort_order'       => 1,
        'title' => [
            'tr' => 'Kızartılmış ballı muz vanilyalı dondurma ile',
            'en' => 'Fried honey banana with vanilla ice cream',
            'de' => 'Gebratene Honigbanane mit Vanilleeis',
            'ru' => 'Жареный банан в меду с ванильным мороженым',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Muz, bal, vanilyalı dondurma',
            'en' => 'Banana, honey, vanilla ice cream',
            'de' => 'Banane, Honig, Vanilleeis',
            'ru' => 'Банан, мёд, ванильное мороженое',
        ],
        'allergens' => ['sut', 'yumurta'],
    ],
    [
        'food_category_id' => 50,
        'sort_order'       => 2,
        'title' => [
            'tr' => 'Kızartılmış ananas vanilyalı dondurma ile',
            'en' => 'Fried pineapple with vanilla ice cream',
            'de' => 'Gebratene Ananas mit Vanilleeis',
            'ru' => 'Жареный ананас с ванильным мороженым',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Ananas, vanilyalı dondurma',
            'en' => 'Pineapple, vanilla ice cream',
            'de' => 'Ananas, Vanilleeis',
            'ru' => 'Ананас, ванильное мороженое',
        ],
        'allergens' => ['sut', 'yumurta'],
    ],
    [
        'food_category_id' => 50,
        'sort_order'       => 3,
        'title' => [
            'tr' => 'Kızarmış dondurma',
            'en' => 'Deep-fried ice cream',
            'de' => 'Frittiertes Eis',
            'ru' => 'Жареное мороженое',
        ],
        'description' => ['tr' => '', 'en' => '', 'de' => '', 'ru' => ''],
        'ingredients' => [
            'tr' => 'Hindistan cevizi, mısır gevreği, esmer şeker ile kaplanmış dondurma',
            'en' => 'Ice cream coated with coconut, cornflakes and brown sugar',
            'de' => 'Mit Kokos, Cornflakes und braunem Zucker paniertes Eis',
            'ru' => 'Мороженое в панировке из кокоса, кукурузных хлопьев и коричневого сахара',
        ],
        'allergens' => ['sut', 'yumurta', 'gluten', 'kuruyemis'],
    ],

];

/* ── Kayıt ── */
$created = 0;
foreach ($products as $data) {
    $fp = FoodProduct::create([
        'branch_id'        => $branchId,
        'food_category_id' => $data['food_category_id'],
        'title'            => $data['title'],
        'description'      => array_filter($data['description'], fn($v) => $v !== ''),
        'ingredients'      => $data['ingredients'],
        'allergens'        => $data['allergens'] ?? null,
        'calories'         => $data['calories']  ?? null,
        'protein'          => $data['protein']   ?? null,
        'carbs'            => $data['carbs']      ?? null,
        'fat'              => $data['fat']        ?? null,
        'price'            => null,
        'is_active'        => true,
        'sort_order'       => $data['sort_order'],
    ]);

    echo sprintf(
        "[%d] %-50s → cat %d\n",
        $fp->id,
        $data['title']['tr'],
        $data['food_category_id']
    );
    $created++;
}

echo PHP_EOL . "Tamamlandı: {$created} ürün eklendi (Branch 2 — Balmy Foresta / WAO ASYA ALACARTE)." . PHP_EOL;
