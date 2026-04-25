<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a0f0a">
    <title>{{ $menu->getTitle($lang) }}</title>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7HHCB1JYV7"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-7HHCB1JYV7');
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;700&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:    {{ $menu->theme_color ?? '#c4923a' }};
            --accent-d:  {{ $menu->theme_color ?? '#c4923a' }}20;
            --accent-m:  {{ $menu->theme_color ?? '#c4923a' }}50;
            --bg:        #1a0f0a;
            --bg2:       #221209;
            --surface:   #2a1710;
            --surface2:  #321c13;
            --border:    rgba(196,146,58,.13);
            --border2:   rgba(196,146,58,.22);
            --text:      #f0e6d8;
            --text-sub:  #9e8a6e;
            --muted:     #5a4535;
            --cinzel:    'Cinzel', serif;
            --crimson:   'Crimson Pro', Georgia, serif;
            --sans:      'DM Sans', system-ui, sans-serif;
        }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--crimson);
            font-size: 16px;
            line-height: 1.6;
            min-height: 100dvh;
            padding-bottom: calc(env(safe-area-inset-bottom,0px) + 4rem);
            /* subtle texture */
            background-image: radial-gradient(ellipse at 30% 0%, rgba(196,146,58,.06) 0%, transparent 55%),
                              radial-gradient(ellipse at 70% 100%, rgba(196,146,58,.04) 0%, transparent 50%);
        }

        /* ── HEADER ── */
        .menu-header {
            padding: calc(env(safe-area-inset-top,0px) + 2.5rem) 1.5rem 2.2rem;
            text-align: center;
            border-bottom: 1px solid var(--border2);
            position: relative;
        }
        .hdr-logo {
            width: 68px; height: 68px; border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--accent-m);
            box-shadow: 0 0 0 5px var(--accent-d), 0 8px 32px rgba(0,0,0,.55);
            margin: 0 auto 1rem; display: block;
        }
        .hdr-logo-placeholder {
            width: 68px; height: 68px; border-radius: 50%;
            background: var(--surface);
            border: 1px solid var(--accent-m);
            box-shadow: 0 0 0 5px var(--accent-d);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--cinzel); font-size: 1.5rem; font-weight: 700;
            color: var(--accent); margin: 0 auto 1rem;
        }
        /* decorative top line pair */
        .hdr-rule {
            display: flex; align-items: center; gap: .5rem;
            justify-content: center; margin-bottom: 1rem;
        }
        .hdr-rule-line {
            flex: 1; max-width: 80px; height: 1px;
            background: linear-gradient(to right, transparent, var(--accent-m));
        }
        .hdr-rule-line.r { background: linear-gradient(to left, transparent, var(--accent-m)); }
        .hdr-diamond { width: 6px; height: 6px; background: var(--accent); transform: rotate(45deg); }
        .hdr-title {
            font-family: var(--cinzel);
            font-size: clamp(1.25rem, 5.5vw, 1.8rem);
            font-weight: 400; letter-spacing: .12em;
            text-transform: uppercase; color: var(--text);
            line-height: 1.2;
        }
        .hdr-sub {
            font-family: var(--crimson);
            font-size: .82rem; font-style: italic;
            color: var(--text-sub); margin-top: .5rem;
            letter-spacing: .06em;
        }



        /* ── STICKY NAV (mini, optional) ── */
        .toc-bar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(26,15,10,.93);
            backdrop-filter: blur(20px) saturate(1.5); -webkit-backdrop-filter: blur(20px) saturate(1.5);
            border-bottom: 1px solid var(--border2);
            overflow-x: auto; overflow-y: hidden; scrollbar-width: none;
        }
        .toc-bar::-webkit-scrollbar { display: none; }
        .toc-tabs { display: flex; padding: 0 .75rem; min-width: max-content; }
        .toc-tab {
            flex-shrink: 0; padding: .72rem .95rem;
            font-family: var(--cinzel); font-size: .6rem; font-weight: 400;
            letter-spacing: .14em; text-transform: uppercase;
            white-space: nowrap; color: var(--muted);
            border-bottom: 1.5px solid transparent;
            text-decoration: none; cursor: pointer;
            transition: color .18s, border-color .18s;
        }
        .toc-tab:hover { color: var(--text-sub); }
        .toc-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* ── CATEGORY SECTIONS (all visible, single page) ── */
        .page-body { padding: 0 0 1rem; }

        .cat-block { padding: 0; }

        /* Category separator */
        .cat-separator {
            display: flex; align-items: center; gap: .85rem;
            padding: 2.2rem 1.4rem 1.4rem;
        }
        .cs-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, var(--border2), var(--border));
        }
        .cs-ornament {
            display: flex; align-items: center; gap: .45rem; flex-shrink: 0;
            flex-direction: column;
        }
        .cs-diamond-row { display: flex; align-items: center; gap: .35rem; }
        .cs-diamond {
            width: 5px; height: 5px; background: var(--accent);
            transform: rotate(45deg); flex-shrink: 0;
        }
        .cs-diamond.sm { width: 3px; height: 3px; background: var(--accent-m); }
        .cs-title {
            font-family: var(--cinzel);
            font-size: clamp(.9rem, 3.5vw, 1.1rem);
            font-weight: 400; letter-spacing: .18em;
            text-transform: uppercase; color: var(--accent);
            white-space: nowrap; line-height: 1;
        }
        .cs-desc {
            font-family: var(--crimson);
            font-size: .9rem; font-style: italic;
            color: var(--text-sub); text-align: center;
            padding: 0 1.4rem .9rem;
            line-height: 1.55;
        }

        /* ── FEATURED BLOCK ── */
        .featured-block {
            margin: 0 1rem 1.2rem;
            border: 1px solid var(--border2);
            border-radius: 10px; overflow: hidden;
            background: var(--surface);
        }
        .fb-header {
            display: flex; align-items: center; gap: .5rem;
            padding: .65rem 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
        }
        .fb-header-diamond { width: 5px; height: 5px; background: var(--accent); transform: rotate(45deg); }
        .fb-header-title {
            font-family: var(--cinzel); font-size: .65rem;
            letter-spacing: .18em; text-transform: uppercase;
            color: var(--accent); font-weight: 400;
        }

        /* ── ITEM LIST ── */
        .items-list { list-style: none; padding: 0 1rem; }

        .item-row {
            display: flex; align-items: baseline; justify-content: space-between;
            padding: .85rem 0;
            border-bottom: 1px solid var(--border);
            cursor: pointer; transition: background .12s;
            gap: .75rem;
        }
        .items-list .item-row:last-child { border-bottom: none; }
        .item-row:hover, .item-row:active { background: rgba(196,146,58,.04); margin: 0 -1rem; padding-left: 1rem; padding-right: 1rem; }

        .item-left { flex: 1; min-width: 0; }
        .item-right { flex-shrink: 0; text-align: right; }

        .ir-name {
            font-family: var(--crimson);
            font-size: 1.05rem; font-weight: 400;
            color: var(--text); line-height: 1.25;
        }
        .ir-name-text { display: block; }
        .ir-desc {
            font-family: var(--crimson);
            font-size: .83rem; font-style: italic;
            color: var(--text-sub); line-height: 1.5;
            margin-top: .15rem;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .ir-badges { display: flex; gap: .2rem; flex-wrap: wrap; margin-top: .3rem; }
        .badge-pill {
            padding: 1px 7px; border-radius: 50px;
            font-family: var(--sans); font-size: .55rem;
            font-weight: 500; border: 1px solid; white-space: nowrap;
        }
        .ir-allergens { display: flex; gap: .15rem; flex-wrap: wrap; margin-top: .28rem; }
        .allergen-chip {
            display: inline-flex; align-items: center; gap: .12rem;
            padding: 1px 5px; border-radius: 50px;
            background: rgba(232,160,32,.1); border: 1px solid rgba(232,160,32,.25);
            font-size: .58rem; color: #e8a020; white-space: nowrap;
        }
        .allergen-chip .a-emo { font-size: .72rem; }
        .ir-info-tag {
            display: inline-block; margin-top: .28rem;
            font-family: var(--sans); font-size: .56rem;
            color: var(--muted); border: 1px solid var(--border2);
            border-radius: 4px; padding: 1px 6px; letter-spacing: .05em;
        }

        .ir-price {
            font-family: var(--cinzel);
            font-size: .98rem; font-weight: 400;
            color: var(--accent); white-space: nowrap;
            letter-spacing: .04em;
        }
        .ir-glass-bottle {
            display: flex; flex-direction: column; gap: .12rem;
            margin-top: .22rem; align-items: flex-end;
        }
        .ir-gb-tag {
            font-family: var(--sans); font-size: .62rem;
            color: var(--text-sub); background: var(--surface2);
            border: 1px solid var(--border); border-radius: 4px;
            padding: 1px 5px; white-space: nowrap;
        }

        /* ── SUB-HEADING DIVIDER ── */
        .sub-heading-divider {
            display: flex; align-items: center; gap: .55rem;
            padding: 1.1rem 0 .5rem;
        }
        .sh-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, var(--accent-m), var(--border));
        }
        .sh-line.r { background: linear-gradient(to left, var(--accent-m), var(--border)); }
        .sh-diamond { width: 4px; height: 4px; background: var(--accent); transform: rotate(45deg); flex-shrink: 0; }
        .sh-text {
            font-family: var(--cinzel); font-size: .58rem;
            letter-spacing: .16em; text-transform: uppercase;
            color: var(--text-sub); white-space: nowrap;
        }

        /* ── FOOTER ── */
        .menu-footer {
            text-align: center; padding: 3rem 1rem 2rem;
            display: flex; flex-direction: column; align-items: center; gap: .6rem;
        }
        .footer-rule {
            display: flex; align-items: center; gap: .6rem; width: 100%; max-width: 220px;
        }
        .footer-rule-line { flex: 1; height: 1px; background: var(--border2); }
        .footer-rule-d { width: 4px; height: 4px; background: var(--accent-m); transform: rotate(45deg); }
        .footer-text {
            font-family: var(--cinzel); font-size: .55rem;
            letter-spacing: .2em; text-transform: uppercase;
            color: var(--muted);
        }

        /* ── BOTTOM SHEET ── */
        .sheet-backdrop {
            display: none; position: fixed; inset: 0; z-index: 400;
            background: rgba(0,0,0,.72);
            backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
            align-items: flex-end; justify-content: center;
            opacity: 0; transition: opacity .28s ease;
        }
        .sheet-backdrop.open { display: flex; opacity: 1; }
        .sheet {
            position: relative; background: var(--surface);
            border-radius: 20px 20px 0 0;
            border-top: 1px solid var(--border2);
            width: 100%; max-width: 520px;
            max-height: 86dvh; overflow-y: auto; overscroll-behavior: contain;
            transform: translateY(60px); transition: transform .3s cubic-bezier(.22,.9,.36,1);
            scrollbar-width: none;
        }
        .sheet::-webkit-scrollbar { display: none; }
        .sheet-backdrop.open .sheet { transform: translateY(0); }

        .sheet-handle {
            position: sticky; top: 0; z-index: 2; background: var(--surface);
            width: 100%; display: flex; justify-content: center; padding: .75rem 0 .2rem;
        }
        .sheet-handle::after {
            content: ''; display: block;
            width: 34px; height: 4px; border-radius: 2px;
            background: rgba(255,255,255,.14);
        }
        .sheet-close {
            position: absolute; top: .65rem; right: .8rem; z-index: 3;
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--surface2); border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-sub); font-size: .8rem; cursor: pointer; transition: background .15s;
        }
        .sheet-close:hover { background: var(--surface); }

        .sheet-no-img {
            width: 100%; padding: 1.8rem 0 .9rem;
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
            border-bottom: 1px solid var(--border);
        }
        .sheet-no-img-ornament {
            display: flex; align-items: center; gap: .4rem;
        }
        .sheet-no-img-line { width: 32px; height: 1px; background: var(--accent-m); }
        .sheet-no-img-d { width: 5px; height: 5px; background: var(--accent); transform: rotate(45deg); }

        .sheet-body { padding: 1.1rem 1.3rem 2.5rem; }
        .sheet-badges { display: flex; gap: .3rem; flex-wrap: wrap; margin-bottom: .65rem; }
        .sheet-title {
            font-family: var(--crimson);
            font-size: clamp(1.35rem, 5vw, 1.7rem);
            font-weight: 600; color: var(--text);
            line-height: 1.15; margin-bottom: .55rem;
        }
        .sheet-desc { font-family: var(--crimson); font-size: .88rem; font-style: italic; color: var(--text-sub); line-height: 1.65; margin-bottom: 1rem; }

        .sheet-price-row {
            display: flex; align-items: baseline; gap: .55rem; margin-bottom: .65rem;
            padding: .55rem .85rem; background: var(--surface2);
            border-radius: 8px; border: 1px solid var(--border2);
        }
        .sheet-price { font-family: var(--cinzel); font-size: 1.4rem; font-weight: 400; color: var(--accent); letter-spacing: .06em; }
        .sheet-price-note { font-family: var(--sans); font-size: .67rem; color: var(--muted); }

        .glass-btl-sheet { display: flex; flex-direction: column; gap: .35rem; margin-bottom: .9rem; }
        .glass-btl-sheet-tag {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--surface2); border: 1px solid var(--border2); border-radius: 7px; padding: .4rem .7rem;
        }
        .gbs-lbl { font-family: var(--sans); font-size: .73rem; color: var(--text-sub); }
        .gbs-price { font-family: var(--cinzel); font-size: .95rem; font-weight: 400; color: var(--accent); }

        .sheet-tabs {
            display: flex; border-bottom: 1px solid var(--border2); margin-bottom: .8rem;
        }
        .sheet-tab-btn {
            flex: 1; padding: .5rem .4rem; font-family: var(--sans); font-size: .68rem; font-weight: 500;
            letter-spacing: .07em; text-transform: uppercase;
            color: var(--muted); background: none; border: none;
            border-bottom: 1.5px solid transparent; cursor: pointer;
            transition: color .18s, border-color .18s;
        }
        .sheet-tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .sheet-tab-panel { display: none; }
        .sheet-tab-panel.active { display: block; }

        .sheet-options-title {
            font-family: var(--sans); font-size: .63rem; font-weight: 600;
            letter-spacing: .1em; text-transform: uppercase; color: var(--muted);
            margin-bottom: .55rem; border-bottom: 1px solid var(--border); padding-bottom: .4rem;
        }
        .sheet-option-row {
            display: flex; align-items: flex-start; gap: .5rem;
            padding: .5rem 0; border-bottom: 1px solid var(--border); font-size: .79rem;
        }
        .sheet-option-row:last-child { border-bottom: none; }
        .sop-label { font-family: var(--sans); color: var(--text-sub); min-width: 90px; flex-shrink: 0; font-size: .72rem; }
        .sop-value { color: var(--text); font-weight: 500; flex: 1; }
        .sop-tags { display: flex; gap: .25rem; flex-wrap: wrap; flex: 1; }
        .sop-tag { font-family: var(--sans); padding: 2px 8px; border-radius: 50px; background: var(--surface2); border: 1px solid var(--border2); font-size: .67rem; color: var(--text); }

        .allergen-chip-lg {
            display: inline-flex; align-items: center; gap: .35rem;
            margin: .2rem .22rem; padding: .3rem .68rem;
            border-radius: 40px; background: rgba(232,160,32,.1);
            border: 1px solid rgba(232,160,32,.26);
            font-family: var(--sans); font-size: .77rem; color: #e8a020;
        }
        .nut-cell {
            background: var(--surface2); border-radius: 8px;
            padding: .45rem .3rem; border: 1px solid var(--border2); text-align: center;
        }
        .nut-val { font-family: var(--cinzel); font-size: .98rem; font-weight: 400; color: var(--accent); }
        .nut-unit { font-family: var(--sans); font-size: .57rem; color: var(--muted); }
        .nut-lbl { font-family: var(--sans); font-size: .59rem; color: var(--text-sub); margin-top: .1rem; }

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



<header class="menu-header">
    @if($menu->logo)
        <img src="{{ asset('uploads/'.$menu->logo) }}" alt="" class="hdr-logo">
    @else
        <div class="hdr-logo-placeholder">{{ strtoupper(substr($menu->name,0,1)) }}</div>
    @endif
    <div class="hdr-rule">
        <div class="hdr-rule-line"></div>
        <div class="hdr-diamond"></div>
        <div class="hdr-rule-line r"></div>
    </div>
    <div class="hdr-title">{{ $menu->getTitle($lang) }}</div>
    <div class="hdr-sub">{{ $_t['digital_menu'] }}</div>
</header>

{{-- Sticky Table of Contents --}}
<nav class="toc-bar" id="toc-bar">
    <div class="toc-tabs">
        @if($featured->count() > 0)
            <a class="toc-tab" href="#section-featured">{{ $_t['featured'] }}</a>
        @endif
        @foreach($categories as $cat)
            <a class="toc-tab" href="#section-cat-{{ $cat->id }}">
                {{ $cat->getTitle($lang) }}
            </a>
        @endforeach
    </div>
</nav>

<main class="page-body">

    {{-- Featured --}}
    @if($featured->count() > 0)
    <div class="cat-block" id="section-featured">
        <div class="cat-separator">
            <div class="cs-line"></div>
            <div class="cs-ornament">
                <div class="cs-diamond-row">
                    <div class="cs-diamond sm"></div>
                    <div class="cs-diamond"></div>
                    <div class="cs-diamond sm"></div>
                </div>
                <div class="cs-title">{{ $_t['featured'] }}</div>
            </div>
            <div class="cs-line" style="background:linear-gradient(to left, var(--border2), var(--border))"></div>
        </div>
        <ul class="items-list">
            @foreach($featured as $fitem)
            <li class="item-row" onclick="openSheet({{ $fitem->id }})">
                <div class="item-left">
                    <div class="ir-name">
                        <span class="ir-name-text">{{ $fitem->getTitle($lang) }}</span>
                    </div>
                    @if($fitem->getDescription($lang))
                        <div class="ir-desc">{{ $fitem->getDescription($lang) }}</div>
                    @endif
                </div>
                <div class="item-right">
                    @if($fitem->effectivePrice())
                        <div class="ir-price">{{ $fitem->formattedPrice($menu->currency_symbol) }}</div>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- All categories, single page --}}
    @foreach($menu->categories as $category)
    <div class="cat-block" id="section-cat-{{ $category->id }}">
        <div class="cat-separator">
            <div class="cs-line"></div>
            <div class="cs-ornament">
                <div class="cs-diamond-row">
                    <div class="cs-diamond sm"></div>
                    <div class="cs-diamond"></div>
                    <div class="cs-diamond sm"></div>
                </div>
                <div class="cs-title">
                    @if($category->icon) {{ $category->icon }} &nbsp;@endif
                    {{ $category->getTitle($lang) }}
                </div>
            </div>
            <div class="cs-line" style="background:linear-gradient(to left, var(--border2), var(--border))"></div>
        </div>

        @if($category->getDescription($lang))
            <div class="cs-desc">{{ $category->getDescription($lang) }}</div>
        @endif

        <ul class="items-list">
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
            <li>
                <div class="sub-heading-divider">
                    <div class="sh-line"></div>
                    <div class="sh-diamond"></div>
                    <div class="sh-text">{{ $sh }}</div>
                    <div class="sh-diamond"></div>
                    <div class="sh-line r"></div>
                </div>
            </li>
            @endif
            <li class="item-row" onclick="openSheet({{ $item->id }})">
                <div class="item-left">
                    <div class="ir-name">
                        <span class="ir-name-text">{{ $item->getTitle($lang) }}</span>
                    </div>
                    @if($item->getDescription($lang))
                        <div class="ir-desc">{{ $item->getDescription($lang) }}</div>
                    @endif
                    @if($item->badges)
                    <div class="ir-badges">
                        @foreach(array_slice($item->badges, 0, 3) as $badge)
                        @php $bc = \App\Models\QrMenuItem::BADGE_COLORS[$badge] ?? '#c4923a'; @endphp
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
                        @if(count($itemAllergens) > 5)<span class="allergen-chip">+{{ count($itemAllergens) - 5 }}</span>@endif
                    </div>
                    @endif
                    @if($hasOpts)<span class="ir-info-tag">+ detay</span>@endif
                </div>
                <div class="item-right">
                    @if($item->effectivePrice())
                        <div class="ir-price">{{ $item->formattedPrice($menu->currency_symbol) }}</div>
                    @endif
                    @if($_pg || $_pb)
                    <div class="ir-glass-bottle">
                        @if($_pg)<div class="ir-gb-tag">🥃{{ $_cg ? ' '.$_cg.'cl' : '' }} {{ $menu->currency_symbol }}{{ number_format($_pg, 2) }}</div>@endif
                        @if($_pb)<div class="ir-gb-tag">🍾{{ $_cb ? ' '.$_cb.'cl' : '' }} {{ $menu->currency_symbol }}{{ number_format($_pb, 2) }}</div>@endif
                    </div>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endforeach

</main>

<footer class="menu-footer">
    <div class="footer-rule">
        <div class="footer-rule-line"></div>
        <div class="footer-rule-d"></div>
        <div class="footer-rule-line"></div>
    </div>
    <div class="footer-text">{{ $menu->getTitle($lang) }}</div>
    <div class="footer-rule">
        <div class="footer-rule-line"></div>
        <div class="footer-rule-d"></div>
        <div class="footer-rule-line"></div>
    </div>
</footer>

{{-- DETAY BOTTOM SHEET --}}
<div class="sheet-backdrop" id="sheetBackdrop" onclick="closeSheet(event)">
    <div class="sheet" id="detailSheet">
        <div class="sheet-handle"></div>
        <button class="sheet-close" onclick="closeSheet(null,true)">&#10005;</button>
        <div id="sheetNoImg" class="sheet-no-img">
            <div class="sheet-no-img-ornament">
                <div class="sheet-no-img-line"></div>
                <div class="sheet-no-img-d"></div>
                <div class="sheet-no-img-line"></div>
            </div>
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
                <div id="sheetIngredientsTxt" style="font-size:.87rem;font-style:italic;color:var(--text-sub);line-height:1.65;padding:.4rem 0"></div>
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
// also add featured items that may not be in categories
foreach ($featured as $_fit) {
    $exists = array_filter($_menuItemsJson, fn($i) => $i['id'] === $_fit->id);
    if (!$exists) {
        $_fp = $_fit->foodProduct;
        $_allergens = [];
        foreach ($_fit->foodProduct?->allergens ?? [] as $_ak) {
            $aInfo = \App\Models\FoodProduct::ALLERGENS[$_ak] ?? null;
            if ($aInfo) $_allergens[] = ['key' => $_ak, 'label' => $aInfo[$lang] ?? $aInfo['tr'], 'emoji' => $aInfo['emoji']];
        }
        $_menuItemsJson[] = [
            'id'           => $_fit->id,
            'title'        => $_fit->getTitle($lang),
            'description'  => $_fit->getDescription($lang),
            'price'        => $_fit->effectivePrice() ? $_fit->formattedPrice($menu->currency_symbol) : null,
            'price_glass'  => $_fit->price_glass  ?? $_fp?->price_glass,
            'price_bottle' => $_fit->price_bottle ?? $_fp?->price_bottle,
            'cl_glass'     => $_fit->cl_glass     ?? $_fp?->cl_glass,
            'cl_bottle'    => $_fit->cl_bottle    ?? $_fp?->cl_bottle,
            'badges'       => $_fit->badges ?? [],
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
    // Smooth-scroll anchor links & active highlight in toc
    var tocLinks = document.querySelectorAll('.toc-tab[href^="#"]');
    var sections = [];
    tocLinks.forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            var target = document.querySelector(a.getAttribute('href'));
            if(!target) return;
            var offset = document.getElementById('toc-bar').offsetHeight;
            var top = target.getBoundingClientRect().top + window.scrollY - offset - 12;
            window.scrollTo({top: top, behavior: 'smooth'});
        });
        var id = a.getAttribute('href').slice(1);
        var el = document.getElementById(id);
        if(el) sections.push({link: a, el: el});
    });

    // Highlight current section on scroll
    window.addEventListener('scroll', function(){
        var offset = (document.getElementById('toc-bar').offsetHeight || 48) + 20;
        var current = null;
        sections.forEach(function(s){
            if(s.el.getBoundingClientRect().top <= offset) current = s;
        });
        tocLinks.forEach(function(l){ l.classList.remove('active'); });
        if(current) current.link.classList.add('active');
    }, {passive: true});

    if(sections.length > 0) sections[0].link.classList.add('active');

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
        var col = BADGE_COLORS[b] || '#c4923a';
        var span = document.createElement('span'); span.className = 'badge-pill';
        span.style.cssText = 'color:'+col+';border-color:'+col+'35;background:'+col+'12;font-size:.7rem;padding:3px 10px';
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
            chip.innerHTML = '<span style="font-size:1.15rem">'+a.emoji+'</span><span>'+a.label+'</span>';
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
