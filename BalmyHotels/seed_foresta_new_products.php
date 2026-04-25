<?php
/**
 * Balmy Foresta (branch_id=2) — Yeni Ürün Ekleme
 * Çalıştır: php seed_foresta_new_products.php
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

// Product 345'ten alınan resim
$sharedImage = 'food_library/h4bEIzvF1AGLfDJmlfJujj6KZ6T4TD33hk6toadT.png';

// Kategori ID'leri (branch_id=2 — Balmy Foresta)
// 29=Çorbalar, 31=Soğuk Başlangıçlar, 33=Meze Çeşitleri
// 36=Salatalar, 44=Makarnalar, 45=Sushi, 49=Ana Yemekler, 52=Meyve

$products = [

    /* ────────── ÇORBALAR (29) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 29,
        'title'            => [
            'tr' => 'Lebeniye Çorba',
            'en' => 'Lebeniye Soup',
            'de' => 'Lebeniye Suppe',
            'ru' => 'Суп Лебение',
        ],
        'description'      => [
            'tr' => 'Minik köfte parçaları, nohut, özlü terbiyalı sos, yanık tereyağlı nane',
            'en' => 'Mini meatball pieces, chickpeas, rich egg-lemon sauce, browned butter with mint',
            'de' => 'Kleine Hackbällchen, Kichererbsen, kräftige Avgolemono-Sauce, braune Butter mit Minze',
            'ru' => 'Мини-фрикадельки, нут, насыщенный яично-лимонный соус, топлёное масло с мятой',
        ],
        'ingredients'      => [
            'tr' => 'Köfte, nohut, terbiye sosu, tereyağ, nane',
            'en' => 'Meatballs, chickpeas, egg-lemon sauce, butter, mint',
            'de' => 'Hackbällchen, Kichererbsen, Avgolemono-Sauce, Butter, Minze',
            'ru' => 'Фрикадельки, нут, соус авголемоно, масло, мята',
        ],
        'allergens'        => ['gluten', 'sut', 'yumurta'],
        'calories'         => 185,
        'protein'          => 10,
        'carbs'            => 16,
        'fat'              => 8,
    ],

    /* ────────── SALATALAR (36) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 36,
        'title'            => [
            'tr' => 'Pirinç Salatası',
            'en' => 'Rice Salad',
            'de' => 'Reissalat',
            'ru' => 'Рисовый салат',
        ],
        'description'      => [
            'tr' => 'Haşlanmış pirinç, jülyen doğranmış havuç, salatalık, beyaz lahana, susam, zeytinyağlı soya sos',
            'en' => 'Boiled rice, julienned carrots, cucumber, white cabbage, sesame, olive oil soy dressing',
            'de' => 'Gekochter Reis, Julienne-Karotten, Gurke, Weißkohl, Sesam, Olivenöl-Soja-Dressing',
            'ru' => 'Варёный рис, морковь жюльен, огурец, белокочанная капуста, кунжут, заправка из соевого соуса',
        ],
        'ingredients'      => [
            'tr' => 'Pirinç, havuç, salatalık, beyaz lahana, susam, zeytinyağı, soya sosu',
            'en' => 'Rice, carrot, cucumber, white cabbage, sesame seeds, olive oil, soy sauce',
            'de' => 'Reis, Karotte, Gurke, Weißkohl, Sesam, Olivenöl, Sojasoße',
            'ru' => 'Рис, морковь, огурец, белокочанная капуста, кунжут, оливковое масло, соевый соус',
        ],
        'allergens'        => ['susam', 'soya', 'gluten'],
        'calories'         => 220,
        'protein'          => 5,
        'carbs'            => 32,
        'fat'              => 8,
    ],

    /* ────────── MEZE ÇEŞİTLERİ (33) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 33,
        'title'            => [
            'tr' => 'Humus',
            'en' => 'Hummus',
            'de' => 'Hummus',
            'ru' => 'Хумус',
        ],
        'description'      => [
            'tr' => 'Geleneksel tahin, nohut, limon, zeytinyağı ve sarımsakla hazırlanan humus',
            'en' => 'Traditional hummus with tahini, chickpeas, lemon, olive oil and garlic',
            'de' => 'Traditioneller Hummus mit Tahini, Kichererbsen, Zitrone, Olivenöl und Knoblauch',
            'ru' => 'Традиционный хумус с тахини, нутом, лимоном, оливковым маслом и чесноком',
        ],
        'ingredients'      => [
            'tr' => 'Nohut, tahin, limon suyu, zeytinyağı, sarımsak',
            'en' => 'Chickpeas, tahini, lemon juice, olive oil, garlic',
            'de' => 'Kichererbsen, Tahini, Zitronensaft, Olivenöl, Knoblauch',
            'ru' => 'Нут, тахини, лимонный сок, оливковое масло, чеснок',
        ],
        'allergens'        => ['susam'],
        'calories'         => 160,
        'protein'          => 7,
        'carbs'            => 14,
        'fat'              => 9,
    ],

    /* ────────── SOĞUK BAŞLANGIÇLAR (31) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 31,
        'title'            => [
            'tr' => 'Parçalanmış Peynir Kulesi',
            'en' => 'Crumbled Cheese Tower',
            'de' => 'Gezupfter Käseturm',
            'ru' => 'Башня из раскрошенного сыра',
        ],
        'description'      => [
            'tr' => 'Fındık taratorlu semiz otu, taze köy peyniri, domates konfi ve pesto sos ile',
            'en' => 'Purslane in hazelnut tarator, fresh village cheese, tomato confit and pesto sauce',
            'de' => 'Portulak in Haselnuss-Tarator, frischer Dorfkäse, Tomatenkonfit und Pesto-Sauce',
            'ru' => 'Портулак с ореховым тараторным соусом, свежий деревенский сыр, конфи из томатов и соус песто',
        ],
        'ingredients'      => [
            'tr' => 'Köy peyniri, semiz otu, fındık, domates konfi, pesto sosu, tarator',
            'en' => 'Village cheese, purslane, hazelnut, tomato confit, pesto sauce, tarator',
            'de' => 'Dorfkäse, Portulak, Haselnuss, Tomatenkonfit, Pesto-Sauce, Tarator',
            'ru' => 'Деревенский сыр, портулак, фундук, конфи из томатов, соус песто, тараторный соус',
        ],
        'allergens'        => ['sut', 'kuruyemis'],
        'calories'         => 280,
        'protein'          => 14,
        'carbs'            => 10,
        'fat'              => 22,
    ],

    /* ────────── MAKARNALAR (44) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 44,
        'title'            => [
            'tr' => 'Farfalla Arrabiata',
            'en' => 'Farfalle Arrabiata',
            'de' => 'Farfalle Arrabiata',
            'ru' => 'Фарфалле Аррабиата',
        ],
        'description'      => [
            'tr' => 'Güneşte kurutulmuş domates sos, acı biber, fesleğen yaprakları, sızma zeytinyağı, parmesan yaprakları',
            'en' => 'Sun-dried tomato sauce, chili pepper, basil leaves, extra virgin olive oil, parmesan shavings',
            'de' => 'Sonnengetrocknete Tomatensauce, Chili, Basilikumblätter, natives Olivenöl extra, Parmesanspäne',
            'ru' => 'Соус из вяленых томатов, чили, листья базилика, оливковое масло extra virgin, стружка пармезана',
        ],
        'ingredients'      => [
            'tr' => 'Farfalle makarna, güneşte kurutulmuş domates, acı biber, fesleğen, zeytinyağı, parmesan',
            'en' => 'Farfalle pasta, sun-dried tomatoes, chili pepper, basil, olive oil, parmesan',
            'de' => 'Farfalle-Pasta, getrocknete Tomaten, Chilischote, Basilikum, Olivenöl, Parmesan',
            'ru' => 'Паста фарфалле, вяленые томаты, чили, базилик, оливковое масло, пармезан',
        ],
        'allergens'        => ['gluten', 'sut'],
        'calories'         => 380,
        'protein'          => 12,
        'carbs'            => 56,
        'fat'              => 12,
    ],

    /* ────────── MEYVE (52) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 52,
        'title'            => [
            'tr' => 'Mevsim Meyve',
            'en' => 'Seasonal Fruit',
            'de' => 'Saisonales Obst',
            'ru' => 'Сезонные фрукты',
        ],
        'description'      => [
            'tr' => 'Soyulmuş mevsim meyve küpleri',
            'en' => 'Peeled seasonal fruit cubes',
            'de' => 'Geschälte saisonale Fruchtwürfel',
            'ru' => 'Очищенные кубики сезонных фруктов',
        ],
        'ingredients'      => [
            'tr' => 'Mevsim meyveleri',
            'en' => 'Seasonal fruits',
            'de' => 'Saisonale Früchte',
            'ru' => 'Сезонные фрукты',
        ],
        'allergens'        => [],
        'calories'         => 70,
        'protein'          => 1,
        'carbs'            => 17,
        'fat'              => 0,
    ],

    /* ────────── SOĞUK BAŞLANGIÇLAR (31) – Levrek Ceviche ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 31,
        'title'            => [
            'tr' => 'Levrek Ceviche',
            'en' => 'Sea Bass Ceviche',
            'de' => 'Wolfsbarsch-Ceviche',
            'ru' => 'Севиче из сибаса',
        ],
        'description'      => [
            'tr' => 'Limon asidi ile pişirilmiş levrek parçacıkları, tereyağlı çıtır ekmek, kırmızı soğan, dereotu',
            'en' => 'Sea bass pieces cured with lemon acid, buttered crispy bread, red onion, dill',
            'de' => 'In Zitronensäure gegarter Wolfsbarsch, knuspriges Butterbrot, rote Zwiebel, Dill',
            'ru' => 'Кусочки сибаса, маринованные в лимонном соке, хрустящий хлеб с маслом, красный лук, укроп',
        ],
        'ingredients'      => [
            'tr' => 'Levrek, limon suyu, kırmızı soğan, dereotu, tereyağ, çıtır ekmek',
            'en' => 'Sea bass, lemon juice, red onion, dill, butter, crispy bread',
            'de' => 'Wolfsbarsch, Zitronensaft, rote Zwiebel, Dill, Butter, Knusperbrot',
            'ru' => 'Сибас, лимонный сок, красный лук, укроп, масло, хрустящий хлеб',
        ],
        'allergens'        => ['balik', 'gluten', 'sut'],
        'calories'         => 190,
        'protein'          => 20,
        'carbs'            => 10,
        'fat'              => 8,
    ],

    /* ────────── ANA YEMEKLER (49) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 49,
        'title'            => [
            'tr' => 'Izgara Somon Fileto',
            'en' => 'Grilled Salmon Fillet',
            'de' => 'Gegrilltes Lachsfilet',
            'ru' => 'Жареное филе лосося',
        ],
        'description'      => [
            'tr' => 'Taze baharatlarla mühürlenmiş somon ızgara, ılık humus bezelye taneleri, yeşillik filizleri, tereyağlı safran',
            'en' => 'Seared salmon with fresh spices, warm hummus pea gems, green sprouts, saffron butter',
            'de' => 'Gebratener Lachs mit frischen Gewürzen, warme Hummus-Erbsen, grüne Triebe, Safran-Butter',
            'ru' => 'Лосось, обжаренный со свежими специями, тёплые горошины с хумусом, зелёные ростки, сафрановое масло',
        ],
        'ingredients'      => [
            'tr' => 'Somon fileto, bezelye, humus, safran, tereyağ, taze baharatlar',
            'en' => 'Salmon fillet, peas, hummus, saffron, butter, fresh herbs',
            'de' => 'Lachsfilet, Erbsen, Hummus, Safran, Butter, frische Kräuter',
            'ru' => 'Филе лосося, горошек, хумус, шафран, масло, свежие специи',
        ],
        'allergens'        => ['balik', 'sut', 'susam'],
        'calories'         => 350,
        'protein'          => 30,
        'carbs'            => 14,
        'fat'              => 18,
    ],
    [
        'branch_id'        => 2,
        'food_category_id' => 49,
        'title'            => [
            'tr' => 'Panga Pane',
            'en' => 'Breaded Panga Fish',
            'de' => 'Panierter Pangasius',
            'ru' => 'Пангасиус в панировке',
        ],
        'description'      => [
            'tr' => 'Limon rendesi ve fesleğen ile lezzetlendirilmiş panga balığı, mevsim yeşillikleri, tarator sos',
            'en' => 'Panga fish flavored with lemon zest and basil, seasonal greens, tarator sauce',
            'de' => 'Pangasius mit Zitronenschale und Basilikum, saisonales Gemüse, Tarator-Sauce',
            'ru' => 'Пангасиус с цедрой лимона и базиликом, сезонная зелень, соус тараторный',
        ],
        'ingredients'      => [
            'tr' => 'Panga balığı, limon rendesi, fesleğen, galeta unu, mevsim yeşillikleri, tarator sos',
            'en' => 'Panga fish, lemon zest, basil, breadcrumbs, seasonal greens, tarator sauce',
            'de' => 'Pangasius, Zitronenschale, Basilikum, Semmelbrösel, saisonales Gemüse, Taratorsauce',
            'ru' => 'Пангасиус, цедра лимона, базилик, панировочные сухари, зезонная зелень, тараторный соус',
        ],
        'allergens'        => ['balik', 'gluten', 'yumurta', 'susam'],
        'calories'         => 290,
        'protein'          => 22,
        'carbs'            => 20,
        'fat'              => 12,
    ],

    /* ────────── SALATALAR (36) – Caprese ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 36,
        'title'            => [
            'tr' => 'Caprese Salata',
            'en' => 'Caprese Salad',
            'de' => 'Caprese Salat',
            'ru' => 'Салат Капрезе',
        ],
        'description'      => [
            'tr' => 'Misket mozarella, kiraz domates, fesleğen yaprakları, parmesan peyniri, balzamik glaze',
            'en' => 'Mini mozzarella balls, cherry tomatoes, basil leaves, parmesan cheese, balsamic glaze',
            'de' => 'Mozzarella-Kugeln, Kirschtomaten, Basilikumblätter, Parmesankäse, Balsamico-Glasur',
            'ru' => 'Шарики моцареллы, томаты черри, листья базилика, сыр пармезан, бальзамическая глазурь',
        ],
        'ingredients'      => [
            'tr' => 'Mozarella, kiraz domates, fesleğen, parmesan, balzamik sirke',
            'en' => 'Mozzarella, cherry tomatoes, basil, parmesan, balsamic vinegar',
            'de' => 'Mozzarella, Kirschtomaten, Basilikum, Parmesan, Balsamicoessig',
            'ru' => 'Моцарелла, томаты черри, базилик, пармезан, бальзамический уксус',
        ],
        'allergens'        => ['sut', 'sulfit'],
        'calories'         => 240,
        'protein'          => 14,
        'carbs'            => 8,
        'fat'              => 17,
    ],

    /* ────────── ANA YEMEKLER (49) – Parmigiana ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 49,
        'title'            => [
            'tr' => 'Parmigiana',
            'en' => 'Parmigiana',
            'de' => 'Parmigiana',
            'ru' => 'Пармиджана',
        ],
        'description'      => [
            'tr' => 'İnce kızarmış patlıcan katları arasında bolonez sos, beşamel, mozarella peyniri, domates sos ile',
            'en' => 'Layers of thin fried eggplant with bolognese sauce, béchamel, mozzarella and tomato sauce',
            'de' => 'Dünne gebratene Auberginenschichten mit Bolognese, Béchamel, Mozzarella und Tomatensoße',
            'ru' => 'Тонкие слои жареных баклажанов с соусом болоньезе, бешамель, моцареллой и томатным соусом',
        ],
        'ingredients'      => [
            'tr' => 'Patlıcan, bolonez sos, beşamel sos, mozarella, domates sosu',
            'en' => 'Eggplant, bolognese sauce, béchamel sauce, mozzarella, tomato sauce',
            'de' => 'Aubergine, Bolognese-Sauce, Béchamel-Sauce, Mozzarella, Tomatensauce',
            'ru' => 'Баклажан, соус болоньезе, соус бешамель, моцарелла, томатный соус',
        ],
        'allergens'        => ['gluten', 'sut', 'yumurta'],
        'calories'         => 380,
        'protein'          => 18,
        'carbs'            => 25,
        'fat'              => 22,
    ],
    [
        'branch_id'        => 2,
        'food_category_id' => 49,
        'title'            => [
            'tr' => 'Kuşkonmazlı Trüflü Risotto',
            'en' => 'Truffle & Asparagus Risotto',
            'de' => 'Trüffel-Spargel-Risotto',
            'ru' => 'Ризотто с трюфелем и спаржей',
        ],
        'description'      => [
            'tr' => 'Trüf yağı, kuşkonmaz, krema, parmesan, tereyağ',
            'en' => 'Truffle oil, asparagus, cream, parmesan, butter',
            'de' => 'Trüffelöl, Spargel, Sahne, Parmesan, Butter',
            'ru' => 'Трюфельное масло, спаржа, сливки, пармезан, сливочное масло',
        ],
        'ingredients'      => [
            'tr' => 'Arborio pirinci, kuşkonmaz, trüf yağı, parmesan, krema, tereyağ',
            'en' => 'Arborio rice, asparagus, truffle oil, parmesan, cream, butter',
            'de' => 'Arborio-Reis, Spargel, Trüffelöl, Parmesan, Sahne, Butter',
            'ru' => 'Рис арборио, спаржа, трюфельное масло, пармезан, сливки, сливочное масло',
        ],
        'allergens'        => ['sut'],
        'calories'         => 420,
        'protein'          => 10,
        'carbs'            => 52,
        'fat'              => 18,
    ],

    /* ────────── MAKARNALAR (44) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 44,
        'title'            => [
            'tr' => 'Peynir Soslu Tortellini',
            'en' => 'Tortellini in Cheese Sauce',
            'de' => 'Tortellini in Käsesauce',
            'ru' => 'Тортеллини в сырном соусе',
        ],
        'description'      => [
            'tr' => 'Ada çayı ile lezzetlendirilmiş krema sosu',
            'en' => 'Cream sauce delicately flavored with sage',
            'de' => 'Sahnesauce, verfeinert mit Salbei',
            'ru' => 'Сливочный соус, ароматизированный шалфеем',
        ],
        'ingredients'      => [
            'tr' => 'Tortellini, krema, ada çayı, peynir',
            'en' => 'Tortellini, cream, sage, cheese',
            'de' => 'Tortellini, Sahne, Salbei, Käse',
            'ru' => 'Тортеллини, сливки, шалфей, сыр',
        ],
        'allergens'        => ['gluten', 'sut', 'yumurta'],
        'calories'         => 380,
        'protein'          => 16,
        'carbs'            => 46,
        'fat'              => 16,
    ],
    [
        'branch_id'        => 2,
        'food_category_id' => 44,
        'title'            => [
            'tr' => 'Kuşkonmazlı Trüflü Spagetti',
            'en' => 'Truffle & Asparagus Spaghetti',
            'de' => 'Trüffel-Spargel-Spaghetti',
            'ru' => 'Спагетти с трюфелем и спаржей',
        ],
        'description'      => [
            'tr' => 'Kuşkonmaz, trüf yağı, krema, parmesan peynir, tereyağı',
            'en' => 'Asparagus, truffle oil, cream, parmesan cheese, butter',
            'de' => 'Spargel, Trüffelöl, Sahne, Parmesankäse, Butter',
            'ru' => 'Спаржа, трюфельное масло, сливки, сыр пармезан, сливочное масло',
        ],
        'ingredients'      => [
            'tr' => 'Spagetti, kuşkonmaz, trüf yağı, parmesan, krema, tereyağ',
            'en' => 'Spaghetti, asparagus, truffle oil, parmesan, cream, butter',
            'de' => 'Spaghetti, Spargel, Trüffelöl, Parmesan, Sahne, Butter',
            'ru' => 'Спагетти, спаржа, трюфельное масло, пармезан, сливки, сливочное масло',
        ],
        'allergens'        => ['gluten', 'sut', 'yumurta'],
        'calories'         => 420,
        'protein'          => 14,
        'carbs'            => 55,
        'fat'              => 16,
    ],

    /* ────────── SUSHİ (45) ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 45,
        'title'            => [
            'tr' => 'Somon Maki',
            'en' => 'Salmon Maki',
            'de' => 'Lachs-Maki',
            'ru' => 'Маки с лососем',
        ],
        'description'      => [
            'tr' => 'Sushi pilavı, nori yosun, salatalık, mayonez, krem peynir, somon',
            'en' => 'Sushi rice, nori seaweed, cucumber, mayonnaise, cream cheese, salmon',
            'de' => 'Sushi-Reis, Nori-Algen, Gurke, Mayonnaise, Frischkäse, Lachs',
            'ru' => 'Рис для суши, водоросли нори, огурец, майонез, сливочный сыр, лосось',
        ],
        'ingredients'      => [
            'tr' => 'Sushi pirinci, nori, salatalık, mayonez, krem peynir, somon',
            'en' => 'Sushi rice, nori, cucumber, mayonnaise, cream cheese, salmon',
            'de' => 'Sushi-Reis, Nori, Gurke, Mayonnaise, Frischkäse, Lachs',
            'ru' => 'Рис суши, нори, огурец, майонез, сливочный сыр, лосось',
        ],
        'allergens'        => ['balik', 'yumurta', 'sut', 'gluten'],
        'calories'         => 210,
        'protein'          => 10,
        'carbs'            => 28,
        'fat'              => 7,
    ],

    /* ────────── ANA YEMEKLER (49) – İstiridye Soslu Dana Brokoli ────────── */
    [
        'branch_id'        => 2,
        'food_category_id' => 49,
        'title'            => [
            'tr' => 'İstiridye Soslu Dana Brokoli',
            'en' => 'Beef & Broccoli in Oyster Sauce',
            'de' => 'Rindfleisch & Brokkoli in Austernsoße',
            'ru' => 'Говядина с брокколи в устричном соусе',
        ],
        'description'      => [
            'tr' => 'Dana eti, brokoli, havuç, istiridye sosu, soğan, mantar',
            'en' => 'Beef, broccoli, carrot, oyster sauce, onion, mushroom',
            'de' => 'Rindfleisch, Brokkoli, Karotte, Austernsoße, Zwiebel, Pilze',
            'ru' => 'Говядина, брокколи, морковь, устричный соус, лук, грибы',
        ],
        'ingredients'      => [
            'tr' => 'Dana eti, brokoli, havuç, istiridye sosu, soğan, mantar',
            'en' => 'Beef, broccoli, carrot, oyster sauce, onion, mushroom',
            'de' => 'Rindfleisch, Brokkoli, Karotte, Austernsoße, Zwiebel, Pilze',
            'ru' => 'Говядина, брокколи, морковь, устричный соус, лук, грибы',
        ],
        'allergens'        => ['kabuklu', 'soya', 'gluten'],
        'calories'         => 280,
        'protein'          => 22,
        'carbs'            => 18,
        'fat'              => 12,
    ],
];

$added   = 0;
$skipped = 0;

foreach ($products as $data) {
    $titleTr = trim($data['title']['tr']);
    $branchId = $data['branch_id'];

    // Duplicate kontrolü
    $exists = FoodProduct::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$titleTr])
        ->exists();

    if ($exists) {
        echo "ATLA: '{$titleTr}' zaten mevcut.\n";
        $skipped++;
        continue;
    }

    FoodProduct::create([
        'branch_id'        => $data['branch_id'],
        'food_category_id' => $data['food_category_id'],
        'printer_id'       => null,
        'image'            => $sharedImage,
        'title'            => $data['title'],
        'description'      => $data['description'] ?: null,
        'ingredients'      => $data['ingredients'] ?: null,
        'price'            => 0,
        'badges'           => null,
        'allergens'        => !empty($data['allergens']) ? $data['allergens'] : null,
        'options'          => null,
        'calories'         => $data['calories'] ?? null,
        'protein'          => $data['protein'] ?? null,
        'carbs'            => $data['carbs'] ?? null,
        'fat'              => $data['fat'] ?? null,
        'is_active'        => true,
        'sort_order'       => 0,
    ]);

    echo "EKLENDI: [{$data['food_category_id']}] {$titleTr}\n";
    $added++;
}

echo "\n✓ Tamamlandı: {$added} ürün eklendi, {$skipped} ürün atlandı.\n";
