<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $label->getName($lang) ?: $label->getName() }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #2d6a4f;
            --primary-light: #52b788;
            --accent: #f77f00;
            --bg: #f0f4f0;
            --card: #ffffff;
            --text: #1b2d24;
            --muted: #6b7c72;
            --border: #d8e4dc;
            --radius: 16px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* ── Header ── */
        .top-bar {
            background: var(--primary);
            color: #fff;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.2);
        }
        .top-bar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .top-bar .brand i { font-size: 1.2rem; color: var(--primary-light); }

        /* Dil seçici */
        .lang-switcher {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .lang-switcher a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none;
            color: rgba(255,255,255,.75);
            border: 1.5px solid transparent;
            transition: all .15s;
        }
        .lang-switcher a:hover { color: #fff; border-color: rgba(255,255,255,.4); background: rgba(255,255,255,.1); }
        .lang-switcher a.active { color: #fff; border-color: var(--primary-light); background: rgba(255,255,255,.15); }

        /* ── Hero Card ── */
        .hero {
            max-width: 680px;
            margin: 24px auto 0;
            padding: 0 16px;
        }
        .hero-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .hero-head {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 30px 28px 24px;
            color: #fff;
        }
        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 14px;
        }
        .meal-name {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 10px;
        }
        /* ── Body sections ── */
        .sections { padding: 24px 28px; display: flex; flex-direction: column; gap: 22px; }

        .section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* Besin değerleri */
        .nutrition-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .nutrition-item {
            background: var(--bg);
            border-radius: 12px;
            padding: 14px 8px;
            text-align: center;
        }
        .nutrition-item .val {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            display: block;
        }
        .nutrition-item .unit { font-size: .65rem; color: var(--muted); font-weight: 600; }
        .nutrition-item .lbl { font-size: .72rem; color: var(--text); margin-top: 2px; font-weight: 500; }

        /* Allerjenler */
        .allergen-list { display: flex; flex-wrap: wrap; gap: 10px; }
        .allergen-tag {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 10px;
            background: #fff3e0;
            border: 1.5px solid #ffc97a;
            color: #b25900;
        }
        .allergen-tag .icon { font-size: 1.1rem; flex-shrink: 0; }
        .allergen-langs {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .allergen-langs .a-tr { font-size: .82rem; font-weight: 700; line-height: 1.2; }
        .allergen-langs .a-row2 { display: flex; gap: 5px; align-items: center; }
        .allergen-langs .a-row2 span { font-size: .70rem; font-weight: 600; opacity: .8; }
        .allergen-langs .a-row2 .sep { opacity: .35; font-weight: 400; }
        .allergen-eu {
            font-size: .68rem;
            font-weight: 700;
            opacity: .45;
            align-self: flex-start;
            margin-top: 1px;
        }
        .no-allergen {
            color: var(--muted);
            font-size: .88rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Açıklama / Malzemeler */
        .description-text {
            color: var(--text);
            line-height: 1.7;
            font-size: .92rem;
        }
        .ingredients-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .ingredient-tag {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px 10px;
            font-size: .82rem;
            color: var(--text);
        }

        /* Footer */
        .card-footer {
            background: var(--bg);
            border-top: 1px solid var(--border);
            padding: 14px 28px;
            font-size: .75rem;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 480px) {
            .meal-name { font-size: 1.4rem; }
            .sections { padding: 18px 18px; }
            .nutrition-grid { grid-template-columns: repeat(2, 1fr); }
            .hero-head { padding: 22px 18px 18px; }
            .card-footer { padding: 12px 18px; }
        }

        @media print {
            :root {
                --primary: #1a4731;
                --text: #000;
                --muted: #333;
                --bg: #f8f8f8;
                --border: #bbb;
            }
            body { background: #fff; padding-bottom: 0; }
            .top-bar { display: none; }
            .hero { max-width: 100%; margin: 0; padding: 0; }
            .hero-card { box-shadow: none; border: 1.5px solid #aaa; }

            /* Yemek adı */
            .meal-name { font-size: 1.8rem; font-weight: 900; }

            /* Bölüm başlıkları */
            .section-title {
                font-size: .8rem;
                font-weight: 900;
                letter-spacing: .8px;
                color: #000;
            }

            /* Açıklama / Malzeme metni */
            .description-text {
                font-size: 1rem;
                font-weight: 700;
                color: #000;
                line-height: 1.6;
            }

            /* Malzeme etiketleri */
            .ingredient-tag {
                font-size: .88rem;
                font-weight: 700;
                color: #000;
                border-color: #999;
                background: #f0f0f0;
            }

            /* Allerjen kutusu */
            .allergen-tag {
                background: #fff3e0;
                border-color: #e09000;
            }
            .allergen-langs .a-tr {
                font-size: .88rem;
                font-weight: 900;
                color: #000;
            }
            .allergen-langs .a-row2 span {
                font-size: .76rem;
                font-weight: 700;
                opacity: 1;
                color: #222;
            }
            .allergen-eu {
                opacity: .7;
                font-weight: 800;
            }

            /* Besin değerleri */
            .nutrition-item .val { font-size: 1.2rem; font-weight: 900; color: #000; }
            .nutrition-item .unit { font-weight: 700; color: #333; }
            .nutrition-item .lbl { font-size: .78rem; font-weight: 700; color: #000; }

            /* Kategori rozeti */
            .category-badge { font-weight: 800; }

            /* Footer */
            .card-footer { color: #444; font-weight: 600; }
        }
    </style>
</head>
<body>

{{-- TOP BAR --}}
<div class="top-bar">
    <div class="brand">
        <i class="fas fa-utensils"></i>
        <span>Yemek Bilgisi</span>
    </div>

    @if(count($availableLangs) > 1)
    @php
        $langMeta = [
            'tr' => ['flag' => '🇹🇷', 'name' => 'TR'],
            'en' => ['flag' => '🇬🇧', 'name' => 'EN'],
            'de' => ['flag' => '🇩🇪', 'name' => 'DE'],
            'ru' => ['flag' => '🇷🇺', 'name' => 'RU'],
            'ar' => ['flag' => '🇸🇦', 'name' => 'AR'],
            'fr' => ['flag' => '🇫🇷', 'name' => 'FR'],
        ];
    @endphp
    <div class="lang-switcher">
        @foreach($availableLangs as $l)
        <a href="?lang={{ $l }}" class="{{ $l === $lang ? 'active' : '' }}">
            {{ $langMeta[$l]['flag'] ?? '' }} {{ $langMeta[$l]['name'] ?? strtoupper($l) }}
        </a>
        @endforeach
    </div>
    @endif
</div>

{{-- HERO --}}
<div class="hero">
    <div class="hero-card">

        {{-- Hero Head --}}
        <div class="hero-head">
            @php
                $catLabels = [
                    'soup'      => ['icon' => '🥣', 'label' => ['tr' => 'Çorba',           'en' => 'Soup',       'de' => 'Suppe',       'ru' => 'Суп',          'ar' => 'حساء',   'fr' => 'Soupe']],
                    'salad'     => ['icon' => '🥗', 'label' => ['tr' => 'Salata',           'en' => 'Salad',      'de' => 'Salat',       'ru' => 'Салат',        'ar' => 'سلطة',   'fr' => 'Salade']],
                    'appetizer' => ['icon' => '🫙', 'label' => ['tr' => 'Meze / Başlangıç','en' => 'Appetizer',  'de' => 'Vorspeise',   'ru' => 'Закуска',      'ar' => 'مقبلة',  'fr' => 'Entrée']],
                    'main'      => ['icon' => '🍽', 'label' => ['tr' => 'Ana Yemek',        'en' => 'Main Course','de' => 'Hauptgericht','ru' => 'Основное',     'ar' => 'طبق رئيسي', 'fr' => 'Plat principal']],
                    'side'      => ['icon' => '🥙', 'label' => ['tr' => 'Yan Yemek',        'en' => 'Side Dish',  'de' => 'Beilage',     'ru' => 'Гарнир',       'ar' => 'طبق جانبي', 'fr' => 'Accompagnement']],
                    'dessert'   => ['icon' => '🍮', 'label' => ['tr' => 'Tatlı',            'en' => 'Dessert',    'de' => 'Dessert',     'ru' => 'Десерт',       'ar' => 'حلوى',   'fr' => 'Dessert']],
                    'beverage'  => ['icon' => '🥤', 'label' => ['tr' => 'İçecek',           'en' => 'Beverage',   'de' => 'Getränk',     'ru' => 'Напиток',      'ar' => 'مشروب',  'fr' => 'Boisson']],
                    'breakfast' => ['icon' => '🍳', 'label' => ['tr' => 'Kahvaltı',         'en' => 'Breakfast',  'de' => 'Frühstück',   'ru' => 'Завтрак',      'ar' => 'إفطار',  'fr' => 'Petit-déjeuner']],
                    'other'     => ['icon' => '🍴', 'label' => ['tr' => 'Diğer',            'en' => 'Other',      'de' => 'Sonstiges',   'ru' => 'Прочее',       'ar' => 'أخرى',   'fr' => 'Autre']],
                ];
                $cat = $catLabels[$label->category ?? ''] ?? null;
            @endphp

            @if($cat)
            <div class="category-badge">
                {{ $cat['icon'] }} {{ $cat['label'][$lang] ?? $cat['label']['en'] ?? $label->category }}
            </div>
            @endif

            <div class="meal-name">{{ $label->getName($lang) ?: $label->getName() }}</div>


        </div>

        {{-- Sections --}}
        <div class="sections">

            {{-- Besin Değerleri --}}
            @if($label->calories || $label->fat || $label->protein || $label->carbs)
            @php
                $nutritionLabels = [
                    'tr' => ['calories' => 'Kalori', 'fat' => 'Yağ', 'protein' => 'Protein', 'carbs' => 'Karbonhidrat', 'section' => 'Besin Değerleri'],
                    'en' => ['calories' => 'Calories','fat' => 'Fat','protein' => 'Protein','carbs' => 'Carbs','section' => 'Nutritional Info'],
                    'de' => ['calories' => 'Kalorien','fat' => 'Fett','protein' => 'Protein','carbs' => 'Kohlenhydr.','section' => 'Nährwertangaben'],
                    'ru' => ['calories' => 'Калории','fat' => 'Жиры','protein' => 'Белки','carbs' => 'Углеводы','section' => 'Питательная ценность'],
                    'ar' => ['calories' => 'سعرات','fat' => 'دهون','protein' => 'بروتين','carbs' => 'كربوهيدرات','section' => 'القيم الغذائية'],
                    'fr' => ['calories' => 'Calories','fat' => 'Lipides','protein' => 'Protéines','carbs' => 'Glucides','section' => 'Valeurs nutritionnelles'],
                ];
                $nl = $nutritionLabels[$lang] ?? $nutritionLabels['en'];
            @endphp
            <div>
                <div class="section-title"><i class="fas fa-fire-alt"></i> {{ $nl['section'] }}</div>
                <div class="nutrition-grid">
                    @if($label->calories)
                    <div class="nutrition-item">
                        <span class="val">{{ $label->calories }}</span>
                        <span class="unit">kcal</span>
                        <div class="lbl">{{ $nl['calories'] }}</div>
                    </div>
                    @endif
                    @if($label->fat !== null && $label->fat > 0)
                    <div class="nutrition-item">
                        <span class="val">{{ number_format($label->fat, 1) }}</span>
                        <span class="unit">g</span>
                        <div class="lbl">{{ $nl['fat'] }}</div>
                    </div>
                    @endif
                    @if($label->protein !== null && $label->protein > 0)
                    <div class="nutrition-item">
                        <span class="val">{{ number_format($label->protein, 1) }}</span>
                        <span class="unit">g</span>
                        <div class="lbl">{{ $nl['protein'] }}</div>
                    </div>
                    @endif
                    @if($label->carbs !== null && $label->carbs > 0)
                    <div class="nutrition-item">
                        <span class="val">{{ number_format($label->carbs, 1) }}</span>
                        <span class="unit">g</span>
                        <div class="lbl">{{ $nl['carbs'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Allerjenler --}}
            @php
                $allergenSectionLabels = [
                    'tr' => 'Allerjenler',
                    'en' => 'Allergens',
                    'de' => 'Allergene',
                    'ru' => 'Аллергены',
                    'ar' => 'مسببات الحساسية',
                    'fr' => 'Allergènes',
                ];
                $noAllergenLabels = [
                    'tr' => 'Bildirilmiş allerjen içermez',
                    'en' => 'No declared allergens',
                    'de' => 'Keine deklarierten Allergene',
                    'ru' => 'Нет заявленных аллергенов',
                    'ar' => 'لا تحتوي على مسببات حساسية',
                    'fr' => 'Aucun allergène déclaré',
                ];
                $allergenDetails = \App\Models\FoodLabel::ALLERGENS;
                $presentAllergens = $label->allergens ?? [];
            @endphp
            <div>
                <div class="section-title"><i class="fas fa-exclamation-triangle"></i> {{ $allergenSectionLabels[$lang] ?? 'Allergens' }}</div>
                @if(!empty($presentAllergens))
                <div class="allergen-list">
                    @foreach($presentAllergens as $key)
                        @if(isset($allergenDetails[$key]))
                        @php $a = $allergenDetails[$key]; @endphp
                        <span class="allergen-tag">
                            <span class="icon">{{ $a['icon'] }}</span>
                            <span class="allergen-langs">
                                <span class="a-tr">{{ $a['label'] }}</span>
                                <span class="a-row2">
                                    <span>{{ $a['label_en'] }}</span>
                                    <span class="sep">·</span>
                                    <span>{{ $a['label_de'] }}</span>
                                    <span class="sep">·</span>
                                    <span>{{ $a['label_ru'] }}</span>
                                </span>
                            </span>
                            <span class="allergen-eu">{{ $a['eu'] }}</span>
                        </span>
                        @endif
                    @endforeach
                </div>
                @else
                <p class="no-allergen"><i class="fas fa-check-circle" style="color:#52b788"></i> {{ $noAllergenLabels[$lang] ?? 'No declared allergens' }}</p>
                @endif
            </div>

            {{-- Açıklama --}}
            @php $desc = $label->getDescription($lang); @endphp
            @if($desc)
            @php
                $descSectionLabels = [
                    'tr' => 'Açıklama', 'en' => 'Description', 'de' => 'Beschreibung',
                    'ru' => 'Описание', 'ar' => 'الوصف', 'fr' => 'Description',
                ];
            @endphp
            <div>
                <div class="section-title"><i class="fas fa-align-left"></i> {{ $descSectionLabels[$lang] ?? 'Description' }}</div>
                <p class="description-text">{{ $desc }}</p>
            </div>
            @endif

            {{-- Malzemeler --}}
            @php $ingredients = $label->getIngredients($lang); @endphp
            @if(!empty($ingredients))
            @php
                $ingredientSectionLabels = [
                    'tr' => 'Malzemeler', 'en' => 'Ingredients', 'de' => 'Zutaten',
                    'ru' => 'Ингредиенты', 'ar' => 'المكونات', 'fr' => 'Ingrédients',
                ];
            @endphp
            <div>
                <div class="section-title"><i class="fas fa-list-ul"></i> {{ $ingredientSectionLabels[$lang] ?? 'Ingredients' }}</div>
                <div class="ingredients-list">
                    @foreach($ingredients as $ing)
                    <span class="ingredient-tag">{{ $ing }}</span>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <div class="card-footer">
            <i class="fas fa-shield-alt me-1"></i>
            @php
                $footerTexts = [
                    'tr' => 'Bu bilgiler bilgilendirme amaçlıdır. Alerjiniz varsa personelimize danışın.',
                    'en' => 'This information is for guidance only. If you have allergies, please consult our staff.',
                    'de' => 'Diese Informationen dienen nur zur Orientierung. Bitte wenden Sie sich bei Allergien an unser Personal.',
                    'ru' => 'Эта информация носит ознакомительный характер. При наличии аллергии проконсультируйтесь с персоналом.',
                    'ar' => 'هذه المعلومات هي للتوجيه فقط. إذا كانت لديك حساسية، يرجى استشارة موظفينا.',
                    'fr' => 'Ces informations sont fournies à titre indicatif. En cas d\'allergies, veuillez consulter notre personnel.',
                ];
            @endphp
            {{ $footerTexts[$lang] ?? $footerTexts['en'] }}
        </div>

    </div>
</div>

</body>
</html>
