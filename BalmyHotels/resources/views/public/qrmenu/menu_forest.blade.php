<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b1508">
    <title>{{ $menu->getTitle($lang) }}</title>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7HHCB1JYV7"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-7HHCB1JYV7');
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400;1,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:     {{ $menu->theme_color ?? '#7cad6a' }};
            --accent-dim: {{ $menu->theme_color ?? '#7cad6a' }}22;
            --accent-mid: {{ $menu->theme_color ?? '#7cad6a' }}55;
            --bg:         #0b1508;
            --surface:    #111e0e;
            --surface2:   #162213;
            --surface3:   #1c2d18;
            --border:     rgba(255,255,255,.055);
            --border2:    rgba(255,255,255,.10);
            --text:       #e8e0d4;
            --text-sub:   #8a9e80;
            --muted:      #496040;
            --serif:      'Playfair Display', Georgia, serif;
            --sans:       'Inter', system-ui, sans-serif;
            --radius:     12px;
        }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100dvh;
            padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 3rem);
        }

        /* ── HEADER ── */
        .menu-header {
            position: relative;
            padding: calc(env(safe-area-inset-top,0px) + 2.4rem) 1.4rem 2rem;
            text-align: center;
            overflow: hidden;
        }
        .hdr-bg {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 50% 0%, var(--accent-dim) 0%, transparent 70%),
                        linear-gradient(180deg, #0b1508 0%, #0f1c0c 100%);
        }
        /* Decorative botanical lines */
        .hdr-bg::before, .hdr-bg::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            border: 1px solid var(--accent-dim);
            left: 50%; top: -80px;
            transform: translateX(-50%);
        }
        .hdr-bg::after {
            width: 180px; height: 180px;
            top: -40px;
            border-color: var(--accent-mid);
            opacity: .3;
        }
        .hdr-content { position: relative; z-index: 2; }
        .hdr-logo {
            width: 64px; height: 64px; border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--accent-mid);
            box-shadow: 0 0 0 5px var(--accent-dim), 0 8px 32px rgba(0,0,0,.45);
            margin: 0 auto .9rem;
            display: block;
        }
        .hdr-logo-placeholder {
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--surface2);
            border: 1px solid var(--accent-mid);
            box-shadow: 0 0 0 5px var(--accent-dim);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--serif); font-size: 1.6rem; font-weight: 700;
            color: var(--accent); margin: 0 auto .9rem;
        }
        .hdr-ornament {
            display: flex; align-items: center; gap: .6rem;
            justify-content: center; margin-bottom: .55rem;
        }
        .hdr-ornament-line {
            flex: 1; max-width: 60px; height: 1px;
            background: linear-gradient(to right, transparent, var(--accent-mid));
        }
        .hdr-ornament-line.r { background: linear-gradient(to left, transparent, var(--accent-mid)); }
        .hdr-ornament-icon { font-size: .8rem; color: var(--accent); opacity: .7; }
        .hdr-title {
            font-family: var(--serif);
            font-size: clamp(1.6rem, 6vw, 2.1rem);
            font-weight: 700; font-style: italic;
            color: var(--text); line-height: 1.1;
            letter-spacing: -.01em;
        }
        .hdr-sub {
            font-size: .62rem; color: var(--muted);
            letter-spacing: .22em; text-transform: uppercase;
            margin-top: .45rem;
        }

        /* ── LANG BAR ── */
        .lang-bar {
            position: fixed; top: calc(env(safe-area-inset-top,0px) + .65rem); right: .8rem;
            z-index: 300; display: flex; gap: .3rem;
        }
        .lang-bar a {
            display: inline-flex; align-items: center; gap: .2rem;
            padding: .26rem .6rem; border-radius: 50px;
            font-size: .63rem; font-weight: 500; letter-spacing: .05em;
            text-decoration: none;
            background: rgba(11,21,8,.75);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border2);
            color: var(--text-sub); transition: .2s;
        }
        .lang-bar a.active, .lang-bar a:hover {
            background: var(--accent); border-color: var(--accent); color: #fff;
        }

        /* ── CATEGORY BAR ── */
        .cat-bar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(11,21,8,.92);
            backdrop-filter: blur(24px) saturate(1.8); -webkit-backdrop-filter: blur(24px) saturate(1.8);
            border-bottom: 1px solid var(--border2);
            overflow-x: auto; overflow-y: hidden; scrollbar-width: none;
        }
        .cat-bar::-webkit-scrollbar { display: none; }
        .cat-tabs { display: flex; padding: 0 .75rem; min-width: max-content; }
        .cat-tab {
            flex-shrink: 0; padding: .8rem 1rem;
            font-size: .68rem; font-weight: 500;
            letter-spacing: .1em; text-transform: uppercase;
            white-space: nowrap; color: var(--muted);
            border-bottom: 1.5px solid transparent;
            text-decoration: none; cursor: pointer;
            transition: color .18s, border-color .18s;
        }
        .cat-tab:hover { color: var(--text-sub); }
        .cat-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* ── SECTIONS ── */
        .cat-section { display: none; }
        .cat-section.active { display: block; }

        .section { padding: 2rem 1rem 0; }
        .section-header { margin-bottom: 1.4rem; }
        .section-header-deco {
            display: flex; align-items: center; gap: .7rem; margin-bottom: .55rem;
        }
        .section-deco-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, var(--accent-mid), transparent);
        }
        .section-deco-leaf { font-size: .85rem; color: var(--accent); opacity: .6; }
        .section-title {
            font-family: var(--serif);
            font-size: clamp(1.3rem, 5vw, 1.6rem);
            font-weight: 700; font-style: italic;
            color: var(--text); letter-spacing: .01em;
        }
        .section-title-en {
            font-size: .62rem; color: var(--muted);
            letter-spacing: .15em; text-transform: uppercase;
            margin-top: .2rem;
        }
        .section-desc {
            font-size: .73rem; color: var(--text-sub);
            line-height: 1.6; margin-top: .4rem;
        }

        /* ── FEATURED STRIP ── */
        .featured-list { display: flex; flex-direction: column; gap: 0; }
        .feat-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: .9rem 1rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer; transition: background .15s;
            position: relative;
        }
        .feat-row:first-child { border-top: 1px solid var(--border); }
        .feat-row:hover, .feat-row:active { background: var(--surface); }
        .feat-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent); flex-shrink: 0; margin-right: .75rem;
        }
        .feat-name {
            font-size: .86rem; font-weight: 500; color: var(--text);
            flex: 1; padding-right: .5rem;
        }
        .feat-price {
            font-family: var(--serif); font-size: .95rem;
            font-weight: 700; color: var(--accent); white-space: nowrap;
        }

        /* ── ITEM LIST ── */
        .items-list { display: flex; flex-direction: column; gap: 0; padding-bottom: 2rem; }

        .item-row {
            display: flex; align-items: flex-start;
            padding: 1rem 1rem 1rem 1.2rem;
            border-bottom: 1px dashed var(--border);
            cursor: pointer; transition: background .15s;
            position: relative;
        }
        .item-row:first-child { border-top: 1px dashed var(--border); }
        .item-row:hover, .item-row:active { background: var(--surface); }

        .item-row-left { flex: 1; padding-right: 1rem; }
        .item-row-right { flex-shrink: 0; text-align: right; min-width: 70px; }

        .ir-name {
            font-family: var(--serif);
            font-size: .96rem; font-weight: 500;
            color: var(--text); line-height: 1.25;
            letter-spacing: .01em;
        }
        .ir-desc {
            font-size: .7rem; color: var(--text-sub);
            line-height: 1.5; margin-top: .25rem;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .ir-badges { display: flex; gap: .2rem; flex-wrap: wrap; margin-top: .35rem; }
        .badge-pill {
            padding: 1px 7px; border-radius: 50px;
            font-size: .56rem; font-weight: 500;
            border: 1px solid; white-space: nowrap;
        }
        .ir-allergens { display: flex; gap: .18rem; flex-wrap: wrap; margin-top: .3rem; }
        .allergen-chip {
            display: inline-flex; align-items: center; gap: .14rem;
            padding: 1px 5px; border-radius: 50px;
            background: rgba(232,160,32,.12);
            border: 1px solid rgba(232,160,32,.28);
            font-size: .58rem; color: #e8a020; white-space: nowrap;
        }
        .allergen-chip .a-emo { font-size: .75rem; }

        .ir-price {
            font-family: var(--serif);
            font-size: 1rem; font-weight: 700;
            color: var(--accent); white-space: nowrap;
        }
        .ir-glass-bottle {
            display: flex; flex-direction: column; gap: .15rem; margin-top: .25rem;
        }
        .ir-gb-tag {
            font-size: .63rem; color: var(--text-sub);
            background: var(--surface2); border: 1px solid var(--border2);
            border-radius: 4px; padding: 1px 5px; white-space: nowrap; text-align: right;
        }
        .ir-info-dot {
            display: inline-flex; align-items: center; justify-content: center;
            width: 16px; height: 16px; border-radius: 50%;
            background: var(--accent-dim); border: 1px solid var(--accent-mid);
            font-size: .6rem; font-weight: 700; color: var(--accent);
            margin-top: .35rem; float: right; clear: both;
        }

        /* ── SUB-HEADING DIVIDER ── */
        .sub-heading-divider {
            display: flex; align-items: center; gap: .55rem;
            padding: 1.1rem 1rem .5rem;
        }
        .sh-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, var(--accent-mid), transparent);
        }
        .sh-line.r { background: linear-gradient(to left, var(--accent-mid), transparent); }
        .sh-text {
            font-family: var(--serif); font-size: .82rem;
            font-style: italic; color: var(--accent);
            white-space: nowrap; letter-spacing: .05em;
        }
        .sh-leaf { font-size: .75rem; color: var(--accent); opacity: .55; }

        /* ── FOOTER ── */
        .menu-footer {
            text-align: center; padding: 3rem 1rem 1.5rem;
            font-size: .58rem; letter-spacing: .18em;
            text-transform: uppercase; color: rgba(255,255,255,.06);
        }

        /* ── BOTTOM SHEET ── */
        .sheet-backdrop {
            display: none; position: fixed; inset: 0; z-index: 400;
            background: rgba(0,0,0,.7);
            backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
            align-items: flex-end; justify-content: center;
            opacity: 0; transition: opacity .28s ease;
        }
        .sheet-backdrop.open { display: flex; opacity: 1; }

        .sheet {
            position: relative; background: var(--surface);
            border-radius: 20px 20px 0 0;
            width: 100%; max-width: 520px;
            max-height: 86dvh; overflow-y: auto; overscroll-behavior: contain;
            transform: translateY(60px); transition: transform .3s cubic-bezier(.22,.9,.36,1);
            scrollbar-width: none;
            border-top: 1px solid var(--border2);
        }
        .sheet::-webkit-scrollbar { display: none; }
        .sheet-backdrop.open .sheet { transform: translateY(0); }

        .sheet-handle {
            position: sticky; top: 0; z-index: 2;
            width: 100%; display: flex; justify-content: center;
            padding: .75rem 0 .2rem;
            background: var(--surface);
        }
        .sheet-handle::after {
            content: ''; display: block;
            width: 36px; height: 4px; border-radius: 2px;
            background: rgba(255,255,255,.15);
        }
        .sheet-close {
            position: absolute; top: .65rem; right: .8rem; z-index: 3;
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--surface2); border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-sub); font-size: .8rem; cursor: pointer;
            transition: background .15s;
        }
        .sheet-close:hover { background: var(--surface3); }

        .sheet-no-img {
            width: 100%; padding: 1.6rem 0 .8rem;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border-bottom: 1px solid var(--border);
        }
        .sheet-no-img-icon { font-size: 2.2rem; margin-bottom: .3rem; }
        .sheet-no-img-line {
            width: 40px; height: 1px; background: var(--accent-mid); margin-top: .4rem;
        }

        .sheet-body { padding: 1.1rem 1.3rem 2.5rem; }
        .sheet-badges { display: flex; gap: .3rem; flex-wrap: wrap; margin-bottom: .65rem; }
        .sheet-title {
            font-family: var(--serif);
            font-size: clamp(1.3rem, 5vw, 1.7rem);
            font-weight: 700; font-style: italic;
            color: var(--text); line-height: 1.15; margin-bottom: .55rem;
        }
        .sheet-desc { font-size: .8rem; color: var(--text-sub); line-height: 1.65; margin-bottom: 1rem; }

        .sheet-price-row {
            display: flex; align-items: baseline; gap: .55rem; margin-bottom: .65rem;
            padding: .6rem .85rem; background: var(--surface2);
            border-radius: 8px; border: 1px solid var(--border2);
        }
        .sheet-price { font-family: var(--serif); font-size: 1.45rem; font-weight: 700; color: var(--accent); }
        .sheet-price-note { font-size: .68rem; color: var(--muted); }

        .glass-btl-sheet { display: flex; flex-direction: column; gap: .35rem; margin-bottom: .9rem; }
        .glass-btl-sheet-tag {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--surface2); border: 1px solid var(--border2);
            border-radius: 7px; padding: .4rem .7rem;
        }
        .gbs-lbl { font-size: .74rem; color: var(--text-sub); }
        .gbs-price { font-family: var(--serif); font-size: .98rem; font-weight: 700; color: var(--accent); }

        /* Sheet tabs */
        .sheet-tabs {
            display: flex; border-bottom: 1px solid var(--border2);
            margin-bottom: .8rem;
        }
        .sheet-tab-btn {
            flex: 1; padding: .52rem .4rem; font-size: .7rem; font-weight: 500;
            color: var(--muted); background: none; border: none;
            border-bottom: 1.5px solid transparent;
            cursor: pointer; transition: color .18s, border-color .18s; letter-spacing: .04em;
        }
        .sheet-tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .sheet-tab-panel { display: none; }
        .sheet-tab-panel.active { display: block; }

        .sheet-options-title {
            font-size: .65rem; font-weight: 600; letter-spacing: .1em;
            text-transform: uppercase; color: var(--muted);
            margin-bottom: .55rem; border-bottom: 1px solid var(--border);
            padding-bottom: .4rem;
        }
        .sheet-option-row {
            display: flex; align-items: flex-start; gap: .5rem;
            padding: .5rem 0; border-bottom: 1px solid var(--border);
            font-size: .78rem;
        }
        .sheet-option-row:last-child { border-bottom: none; }
        .sop-label { color: var(--text-sub); min-width: 90px; flex-shrink: 0; font-size: .72rem; }
        .sop-value { color: var(--text); font-weight: 500; flex: 1; }
        .sop-tags { display: flex; gap: .25rem; flex-wrap: wrap; flex: 1; }
        .sop-tag {
            padding: 2px 8px; border-radius: 50px;
            background: var(--surface3); border: 1px solid var(--border2);
            font-size: .68rem; color: var(--text);
        }

        .allergen-chip-lg {
            display: inline-flex; align-items: center; gap: .35rem;
            margin: .2rem .22rem; padding: .32rem .7rem;
            border-radius: 40px; background: rgba(232,160,32,.1);
            border: 1px solid rgba(232,160,32,.28);
            font-size: .78rem; color: #e8a020;
        }

        .nut-cell {
            background: var(--surface2); border-radius: 8px;
            padding: .45rem .3rem; border: 1px solid var(--border2);
            text-align: center;
        }
        .nut-val { font-family: var(--serif); font-size: 1.05rem; font-weight: 700; color: var(--accent); }
        .nut-unit { font-size: .58rem; color: var(--muted); }
        .nut-lbl { font-size: .6rem; color: var(--text-sub); margin-top: .12rem; }

        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body>

@php
$_i18n = [
    'tr' => ['product_info'=>'Ürün Bilgileri','tax_note'=>'KDV dahil','featured'=>'Öne Çıkanlar','recommended'=>'Önerilen','digital_menu'=>'Dijital Menü','allergens_tab'=>'Alerjenler','info_tab'=>'Bilgiler','ingredients_tab'=>'İçindekiler','calories'=>'Kalori','protein'=>'Protein','carbs'=>'Karbonhidrat','fat'=>'Yağ','glass'=>'Bardak','bottle'=>'Şişe'],
    'en' => ['product_info'=>'Product Info','tax_note'=>'Tax included','featured'=>'Featured','recommended'=>'Recommended','digital_menu'=>'Digital Menu','allergens_tab'=>'Allergens','info_tab'=>'Details','ingredients_tab'=>'Ingredients','calories'=>'Calories','protein'=>'Protein','carbs'=>'Carbohydrates','fat'=>'Fat','glass'=>'Glass','bottle'=>'Bottle'],
    'de' => ['product_info'=>'Produktinfo','tax_note'=>'Inkl. MwSt.','featured'=>'Highlights','recommended'=>'Empfohlen','digital_menu'=>'Digitale Karte','allergens_tab'=>'Allergene','info_tab'=>'Details','ingredients_tab'=>'Zutaten','calories'=>'Kalorien','protein'=>'Protein','carbs'=>'Kohlenhydrate','fat'=>'Fett','glass'=>'Glas','bottle'=>'Flasche'],
    'ru' => ['product_info'=>'О продукте','tax_note'=>'Включая НДС','featured'=>'Рекомендуем','recommended'=>'Рекомендовано','digital_menu'=>'Цифровое меню','allergens_tab'=>'Аллергены','info_tab'=>'Детали','ingredients_tab'=>'Состав','calories'=>'Калории','protein'=>'Белок','carbs'=>'Углеводы','fat'=>'Жиры','glass'=>'Бокал','bottle'=>'Бутылка'],
    'ar' => ['product_info'=>'معلومات المنتج','tax_note'=>'شامل الضريبة','featured'=>'المميزة','recommended'=>'موصى به','digital_menu'=>'قائمة رقمية','allergens_tab'=>'مسببات الحساسية','info_tab'=>'تفاصيل','ingredients_tab'=>'المكونات','calories'=>'سعرات','protein'=>'بروتين','carbs'=>'كربوهيدرات','fat'=>'دهون','glass'=>'كأس','bottle'=>'زجاجة'],
    'fr' => ['product_info'=>'Info produit','tax_note'=>'TVA incluse','featured'=>'En vedette','recommended'=>'Recommandé','digital_menu'=>'Menu numérique','allergens_tab'=>'Allergènes','info_tab'=>'Détails','ingredients_tab'=>'Ingrédients','calories'=>'Calories','protein'=>'Protéines','carbs'=>'Glucides','fat'=>'Lipides','glass'=>'Verre','bottle'=>'Bouteille'],
    'es' => ['product_info'=>'Info producto','tax_note'=>'IVA incluido','featured'=>'Destacados','recommended'=>'Recomendado','digital_menu'=>'Menú digital','allergens_tab'=>'Alérgenos','info_tab'=>'Detalles','ingredients_tab'=>'Ingredientes','calories'=>'Calorías','protein'=>'Proteínas','carbs'=>'Carbohidratos','fat'=>'Grasas','glass'=>'Copa','bottle'=>'Botella'],
    'it' => ['product_info'=>'Info prodotto','tax_note'=>'IVA inclusa','featured'=>'In evidenza','recommended'=>'Consigliato','digital_menu'=>'Menu digitale','allergens_tab'=>'Allergeni','info_tab'=>'Dettagli','ingredients_tab'=>'Ingredienti','calories'=>'Calorie','protein'=>'Proteine','carbs'=>'Carboidrati','fat'=>'Grassi','glass'=>'Calice','bottle'=>'Bottiglia'],
    'nl' => ['product_info'=>'Productinfo','tax_note'=>'BTW inbegrepen','featured'=>'Uitgelicht','recommended'=>'Aanbevolen','digital_menu'=>'Digitale kaart','allergens_tab'=>'Allergenen','info_tab'=>'Details','ingredients_tab'=>'Ingrediënten','calories'=>'Calorieën','protein'=>'Eiwit','carbs'=>'Koolhydraten','fat'=>'Vet','glass'=>'Glas','bottle'=>'Fles'],
    'zh' => ['product_info'=>'产品信息','tax_note'=>'含税','featured'=>'推荐','recommended'=>'推荐','digital_menu'=>'数字菜单','allergens_tab'=>'过敏原','info_tab'=>'详情','ingredients_tab'=>'配料','calories'=>'卡路里','protein'=>'蛋白质','carbs'=>'碳水化合物','fat'=>'脂肪','glass'=>'杯','bottle'=>'瓶'],
];
$_t = $_i18n[$lang] ?? $_i18n['tr'];
@endphp

@if($menu->languages->count() > 1)
<div class="lang-bar">
    @foreach($menu->languages as $l)
        <a href="{{ route('qrmenu.view', [$menu->name, $l->code]) }}"
           class="{{ $l->code === $lang ? 'active' : '' }}">
            {{ $l->flag }} {{ strtoupper($l->code) }}
        </a>
    @endforeach
</div>
@endif

<header class="menu-header">
    <div class="hdr-bg"></div>
    <div class="hdr-content">
        @if($menu->logo)
            <img src="{{ asset('uploads/'.$menu->logo) }}" alt="" class="hdr-logo">
        @else
            <div class="hdr-logo-placeholder">{{ strtoupper(substr($menu->name,0,1)) }}</div>
        @endif
        <div class="hdr-ornament">
            <div class="hdr-ornament-line"></div>
            <span class="hdr-ornament-icon">✦</span>
            <div class="hdr-ornament-line r"></div>
        </div>
        <div class="hdr-title">{{ $menu->getTitle($lang) }}</div>
        <div class="hdr-sub">{{ $_t['digital_menu'] }}</div>
    </div>
</header>

<div class="cat-bar" id="cat-bar">
    <div class="cat-tabs">
        @if($featured->count() > 0)
            <a class="cat-tab" data-id="featured">✦ {{ $_t['featured'] }}</a>
        @endif
        @foreach($categories as $cat)
            <a class="cat-tab" data-id="cat-{{ $cat->id }}">
                @if($cat->icon) {{ $cat->icon }} @endif
                {{ $cat->getTitle($lang) }}
            </a>
        @endforeach
    </div>
</div>

@if($featured->count() > 0)
<div class="cat-section" id="featured">
    <div class="section">
        <div class="section-header">
            <div class="section-header-deco">
                <div class="section-deco-line"></div>
                <span class="section-deco-leaf">✦</span>
            </div>
            <div class="section-title">{{ $_t['featured'] }}</div>
        </div>
        <div class="featured-list">
            @foreach($featured as $fitem)
            <div class="feat-row" onclick="openSheet({{ $fitem->id }})">
                <div class="feat-dot"></div>
                <div class="feat-name">{{ $fitem->getTitle($lang) }}</div>
                @if($fitem->effectivePrice())
                    <div class="feat-price">{{ $fitem->formattedPrice($menu->currency_symbol) }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@foreach($menu->categories as $category)
<div class="cat-section" id="cat-{{ $category->id }}">
    <div class="section">
        <div class="section-header">
            <div class="section-header-deco">
                <div class="section-deco-line"></div>
                <span class="section-deco-leaf">✦</span>
            </div>
            <div class="section-title">
                {{ $category->getTitle($lang) }}
            </div>
            @if($category->getDescription($lang))
                <div class="section-desc">{{ $category->getDescription($lang) }}</div>
            @endif
        </div>

        <div class="items-list">
            @php $renderedSubHeadings = []; @endphp
            @foreach($category->items->sortBy('sort_order') as $item)
            @php
                $sh = $item->getSubHeading($lang);
                $needsDivider = $sh && !in_array($sh, $renderedSubHeadings);
                if ($needsDivider) $renderedSubHeadings[] = $sh;
                $hasOpts = !empty($item->foodProduct?->options);
                $_pg = $item->price_glass  ?? $item->foodProduct?->price_glass;
                $_pb = $item->price_bottle ?? $item->foodProduct?->price_bottle;
                $_cg = $item->cl_glass     ?? $item->foodProduct?->cl_glass;
                $_cb = $item->cl_bottle    ?? $item->foodProduct?->cl_bottle;
                $itemAllergens = $item->foodProduct?->allergens ?? [];
            @endphp
            @if($needsDivider)
            <div class="sub-heading-divider">
                <div class="sh-line"></div>
                <span class="sh-leaf">✦</span>
                <div class="sh-text">{{ $sh }}</div>
                <span class="sh-leaf">✦</span>
                <div class="sh-line r"></div>
            </div>
            @endif
            <div class="item-row" onclick="openSheet({{ $item->id }})">
                <div class="item-row-left">
                    <div class="ir-name">{{ $item->getTitle($lang) }}</div>
                    @if($item->getDescription($lang))
                        <div class="ir-desc">{{ $item->getDescription($lang) }}</div>
                    @endif
                    @if($item->badges)
                    <div class="ir-badges">
                        @foreach(array_slice($item->badges, 0, 3) as $badge)
                        @php $bc = \App\Models\QrMenuItem::BADGE_COLORS[$badge] ?? '#7cad6a'; @endphp
                        <span class="badge-pill" style="color:{{ $bc }};border-color:{{ $bc }}35;background:{{ $bc }}12">{{ $badge }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if(count($itemAllergens) > 0)
                    <div class="ir-allergens">
                        @foreach(array_slice($itemAllergens, 0, 5) as $ak)
                        @php $aInfo = \App\Models\FoodProduct::ALLERGENS[$ak] ?? null; @endphp
                        @if($aInfo)
                        <span class="allergen-chip" title="{{ $aInfo[$lang] ?? $aInfo['tr'] }}">
                            <span class="a-emo">{{ $aInfo['emoji'] }}</span>
                        </span>
                        @endif
                        @endforeach
                        @if(count($itemAllergens) > 5)
                        <span class="allergen-chip">+{{ count($itemAllergens) - 5 }}</span>
                        @endif
                    </div>
                    @endif
                    @if($hasOpts)
                        <span class="ir-info-dot">i</span>
                    @endif
                </div>
                <div class="item-row-right">
                    @if($item->effectivePrice())
                        <div class="ir-price">{{ $item->formattedPrice($menu->currency_symbol) }}</div>
                    @endif
                    @if($_pg || $_pb)
                    <div class="ir-glass-bottle">
                        @if($_pg)
                        <div class="ir-gb-tag">🥃{{ $_cg ? ' '.$_cg.'cl' : '' }} {{ $menu->currency_symbol }}{{ number_format($_pg, 2) }}</div>
                        @endif
                        @if($_pb)
                        <div class="ir-gb-tag">🍾{{ $_cb ? ' '.$_cb.'cl' : '' }} {{ $menu->currency_symbol }}{{ number_format($_pb, 2) }}</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

<div class="menu-footer">{{ $menu->getTitle($lang) }} &nbsp;✦&nbsp; {{ $_t['digital_menu'] }}</div>

{{-- DETAY BOTTOM SHEET --}}
<div class="sheet-backdrop" id="sheetBackdrop" onclick="closeSheet(event)">
    <div class="sheet" id="detailSheet">
        <div class="sheet-handle"></div>
        <button class="sheet-close" onclick="closeSheet(null,true)">&#10005;</button>
        <div id="sheetNoImg" class="sheet-no-img">
            <div class="sheet-no-img-icon">🌿</div>
            <div class="sheet-no-img-line"></div>
        </div>
        <div class="sheet-body">
            <div id="sheetBadges" class="sheet-badges"></div>
            <div id="sheetTitle" class="sheet-title"></div>
            <div id="sheetDesc" class="sheet-desc" style="display:none"></div>
            <div id="sheetPriceRow" class="sheet-price-row" style="display:none">
                <div id="sheetPrice" class="sheet-price"></div>
                <div class="sheet-price-note">{{ $_t['tax_note'] }}</div>
            </div>
            <div id="sheetGlassBottle" class="glass-btl-sheet" style="display:none"></div>
            <div id="sheetTabs" class="sheet-tabs" style="display:none">
                <button class="sheet-tab-btn active" data-tab="info">{{ $_t['info_tab'] }}</button>
                <button class="sheet-tab-btn" data-tab="ingredients">{{ $_t['ingredients_tab'] }}</button>
                <button class="sheet-tab-btn" data-tab="allergens">{{ $_t['allergens_tab'] }}</button>
            </div>
            <div id="tabInfo" class="sheet-tab-panel active">
                <div id="sheetNutrition" style="display:none;margin-bottom:.8rem">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;text-align:center">
                        <div id="nutCalories" class="nut-cell"></div>
                        <div id="nutProtein"  class="nut-cell"></div>
                        <div id="nutCarbs"    class="nut-cell"></div>
                        <div id="nutFat"      class="nut-cell"></div>
                    </div>
                </div>
                <div id="sheetOptions" class="sheet-options" style="display:none">
                    <div class="sheet-options-title">{{ $_t['product_info'] }}</div>
                    <div id="sheetOptionsList"></div>
                </div>
            </div>
            <div id="tabIngredients" class="sheet-tab-panel">
                <div id="sheetIngredientsTxt" style="font-size:.8rem;color:var(--text-sub);line-height:1.65;padding:.4rem 0"></div>
            </div>
            <div id="tabAllergens" class="sheet-tab-panel">
                <div id="sheetAllergensList" style="padding:.3rem 0"></div>
            </div>
        </div>
    </div>
</div>

@php
$_menuItemsJson = [];
foreach ($categories as $_cat) {
    foreach ($_cat->items->sortBy('sort_order') as $_it) {
        $_allergens = [];
        foreach ($_it->foodProduct?->allergens ?? [] as $_ak) {
            $aInfo = \App\Models\FoodProduct::ALLERGENS[$_ak] ?? null;
            if ($aInfo) $_allergens[] = ['key' => $_ak, 'label' => $aInfo[$lang] ?? $aInfo['tr'], 'emoji' => $aInfo['emoji']];
        }
        $_fp = $_it->foodProduct;
        $_menuItemsJson[] = [
            'id'           => $_it->id,
            'title'        => $_it->getTitle($lang),
            'description'  => $_it->getDescription($lang),
            'price'        => $_it->effectivePrice() ? $_it->formattedPrice($menu->currency_symbol) : null,
            'price_glass'  => $_it->price_glass  ?? $_fp?->price_glass,
            'price_bottle' => $_it->price_bottle ?? $_fp?->price_bottle,
            'cl_glass'     => $_it->cl_glass     ?? $_fp?->cl_glass,
            'cl_bottle'    => $_it->cl_bottle    ?? $_fp?->cl_bottle,
            'badges'       => $_it->badges ?? [],
            'allergens'    => $_allergens,
            'ingredients'  => $_fp?->ingredients[$lang] ?? $_fp?->ingredients['tr'] ?? null,
            'options'      => $_fp?->options ?? [],
            'nutrition'    => ($_fp && ($_fp->calories || $_fp->protein || $_fp->carbs || $_fp->fat)) ? [
                'calories' => $_fp->calories,
                'protein'  => $_fp->protein,
                'carbs'    => $_fp->carbs,
                'fat'      => $_fp->fat,
            ] : null,
        ];
    }
}
@endphp
<script>
var CURRENT_LANG = '{{ $lang }}';
var MENU_ITEMS = @json($_menuItemsJson);
var BADGE_COLORS = @json(\App\Models\QrMenuItem::BADGE_COLORS);
var I18N = {
    allergens_tab:   '{{ $_t['allergens_tab'] }}',
    info_tab:        '{{ $_t['info_tab'] }}',
    ingredients_tab: '{{ $_t['ingredients_tab'] }}',
    product_info:    '{{ $_t['product_info'] }}',
    tax_note:        '{{ $_t['tax_note'] }}',
    calories:        '{{ $_t['calories'] }}',
    protein:         '{{ $_t['protein'] }}',
    carbs:           '{{ $_t['carbs'] }}',
    fat:             '{{ $_t['fat'] }}',
    glass:           '{{ $_t['glass'] }}',
    bottle:          '{{ $_t['bottle'] }}',
};
</script>

<script>
(function(){
    var tabs = document.querySelectorAll('.cat-tab[data-id]');

    function switchTab(id) {
        document.querySelectorAll('.cat-section').forEach(function(s){ s.classList.remove('active'); });
        tabs.forEach(function(t){ t.classList.remove('active'); });
        var sec = document.getElementById(id);
        if(sec) sec.classList.add('active');
        var tab = document.querySelector('.cat-tab[data-id="'+id+'"]');
        if(tab) {
            tab.classList.add('active');
            tab.scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
        }
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    tabs.forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            switchTab(a.dataset.id);
        });
    });

    if(tabs.length > 0) switchTab(tabs[0].dataset.id);

    // Swipe-to-close sheet
    var sheet = document.getElementById('detailSheet');
    var startY=0, curY=0, isDrag=false;
    sheet.addEventListener('touchstart',function(e){ if(sheet.scrollTop>0) return; startY=e.touches[0].clientY; isDrag=true; sheet.style.transition='none'; },{passive:true});
    sheet.addEventListener('touchmove',function(e){ if(!isDrag) return; curY=e.touches[0].clientY-startY; if(curY>0) sheet.style.transform='translateY('+curY+'px)'; },{passive:true});
    sheet.addEventListener('touchend',function(){ sheet.style.transition=''; if(curY>90) closeSheet(null,true); else sheet.style.transform=''; isDrag=false; curY=0; });
})();

function openSheet(itemId){
    var item = MENU_ITEMS.find(function(i){ return i.id===itemId; });
    if(!item) return;

    document.getElementById('sheetTitle').textContent = item.title;

    var badgesEl = document.getElementById('sheetBadges');
    badgesEl.innerHTML = '';
    (item.badges||[]).forEach(function(b){
        var col = BADGE_COLORS[b] || '#7cad6a';
        var span = document.createElement('span'); span.className = 'badge-pill';
        span.style.cssText = 'color:'+col+';border-color:'+col+'35;background:'+col+'12;font-size:.72rem;padding:3px 10px';
        span.textContent = b; badgesEl.appendChild(span);
    });

    var descEl = document.getElementById('sheetDesc');
    if(item.description){ descEl.textContent = item.description; descEl.style.display = ''; }
    else { descEl.style.display = 'none'; }

    var priceRow = document.getElementById('sheetPriceRow');
    var glassBtlEl = document.getElementById('sheetGlassBottle');
    if(item.price){ document.getElementById('sheetPrice').textContent = item.price; priceRow.style.display = ''; }
    else { priceRow.style.display = 'none'; }

    glassBtlEl.innerHTML = '';
    if(item.price_glass || item.price_bottle){
        var sym = '{{ $menu->currency_symbol }}';
        if(item.price_glass){
            var t1 = document.createElement('div'); t1.className = 'glass-btl-sheet-tag';
            t1.innerHTML = '<span class="gbs-lbl">🥃 '+I18N.glass+(item.cl_glass?' '+item.cl_glass+'cl':'')+'</span><span class="gbs-price">'+sym+' '+item.price_glass.toFixed(2)+'</span>';
            glassBtlEl.appendChild(t1);
        }
        if(item.price_bottle){
            var t2 = document.createElement('div'); t2.className = 'glass-btl-sheet-tag';
            t2.innerHTML = '<span class="gbs-lbl">🍾 '+I18N.bottle+(item.cl_bottle?' '+item.cl_bottle+'cl':'')+'</span><span class="gbs-price">'+sym+' '+item.price_bottle.toFixed(2)+'</span>';
            glassBtlEl.appendChild(t2);
        }
        glassBtlEl.style.display = '';
    } else { glassBtlEl.style.display = 'none'; }

    var optsWrap = document.getElementById('sheetOptions');
    var optsList = document.getElementById('sheetOptionsList');
    optsList.innerHTML = '';
    var opts = item.options || [];
    if(opts.length > 0){
        opts.forEach(function(opt){
            var row = document.createElement('div'); row.className = 'sheet-option-row';
            var lbl = document.createElement('div'); lbl.className = 'sop-label';
            if(opt.label && typeof opt.label === 'object'){
                lbl.textContent = opt.label[CURRENT_LANG] || opt.label['tr'] || Object.values(opt.label)[0] || '';
            } else { lbl.textContent = opt.label || ''; }
            row.appendChild(lbl);
            if(opt.type === 'tags'){
                var tw = document.createElement('div'); tw.className = 'sop-tags';
                (opt.value||'').split(',').forEach(function(tag){ tag=tag.trim(); if(!tag) return; var t=document.createElement('span'); t.className='sop-tag'; t.textContent=tag; tw.appendChild(t); });
                row.appendChild(tw);
            } else {
                var val = document.createElement('div'); val.className = 'sop-value'; val.textContent = opt.value; row.appendChild(val);
            }
            optsList.appendChild(row);
        });
        optsWrap.style.display = '';
    } else { optsWrap.style.display = 'none'; }

    var allergenList = document.getElementById('sheetAllergensList');
    allergenList.innerHTML = '';
    var allergens = item.allergens || [];
    if(allergens.length > 0){
        allergens.forEach(function(a){
            var chip = document.createElement('div'); chip.className = 'allergen-chip-lg';
            chip.innerHTML = '<span style="font-size:1.2rem">'+a.emoji+'</span><span>'+a.label+'</span>';
            allergenList.appendChild(chip);
        });
    } else {
        allergenList.innerHTML = '<p style="font-size:.78rem;color:var(--muted);margin:.4rem 0">—</p>';
    }

    var ingEl = document.getElementById('sheetIngredientsTxt');
    ingEl.textContent = item.ingredients || '—';

    var nutWrap = document.getElementById('sheetNutrition');
    if(item.nutrition){
        var n = item.nutrition;
        function nutCell(id, val, unit, lbl){
            var el = document.getElementById(id);
            el.innerHTML = '<div class="nut-val">'+(val||'—')+'</div><div class="nut-unit">'+unit+'</div><div class="nut-lbl">'+lbl+'</div>';
        }
        nutCell('nutCalories', n.calories ? Math.round(n.calories) : null, 'kcal', I18N.calories);
        nutCell('nutProtein',  n.protein, 'g', I18N.protein);
        nutCell('nutCarbs',    n.carbs,   'g', I18N.carbs);
        nutCell('nutFat',      n.fat,     'g', I18N.fat);
        nutWrap.style.display = '';
    } else { nutWrap.style.display = 'none'; }

    var sheetTabs = document.getElementById('sheetTabs');
    var tabInfo = document.getElementById('tabInfo');
    var tabIngredients = document.getElementById('tabIngredients');
    var tabAllergens = document.getElementById('tabAllergens');
    var hasContent = opts.length > 0 || allergens.length > 0 || item.ingredients || item.nutrition;
    if(hasContent){
        sheetTabs.style.display = '';
        sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(btn){
            btn.onclick = function(){
                sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(b){ b.classList.remove('active'); });
                btn.classList.add('active');
                tabInfo.classList.remove('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active');
                if(btn.dataset.tab === 'allergens') tabAllergens.classList.add('active');
                else if(btn.dataset.tab === 'ingredients') tabIngredients.classList.add('active');
                else tabInfo.classList.add('active');
            };
        });
        tabInfo.classList.add('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active');
        sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(b,i){ b.classList.toggle('active', i===0); });
    } else {
        sheetTabs.style.display = 'none';
        tabInfo.classList.add('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active');
    }

    var backdrop = document.getElementById('sheetBackdrop');
    backdrop.style.display = 'flex'; document.body.style.overflow = 'hidden';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){ backdrop.classList.add('open'); }); });
}

function closeSheet(e, force){
    if(e && e.target !== document.getElementById('sheetBackdrop') && !force) return;
    var backdrop = document.getElementById('sheetBackdrop');
    backdrop.classList.remove('open'); document.body.style.overflow = '';
    setTimeout(function(){ backdrop.style.display = 'none'; }, 310);
}

document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeSheet(null, true); });
</script>
</body>
</html>
