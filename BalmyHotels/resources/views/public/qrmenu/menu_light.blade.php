<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#fdfaf6">
    <title>{{ $menu->getTitle($lang) }}</title>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7HHCB1JYV7"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-7HHCB1JYV7');
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:   #C19B77;
            --accent-d: #C19B7722;
            --accent-m: #C19B7766;
            --bg:       #fdfaf6;
            --bg2:      #f5f0e8;
            --surface:  #ffffff;
            --border:   #e8dfd0;
            --border2:  #d9ccba;
            --text:     #2e2318;
            --text-sub: #7a6552;
            --muted:    #b3a08a;
            --cormorant:'Cormorant Garamond', Georgia, serif;
            --jost:     'Jost', system-ui, sans-serif;
        }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--jost);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100dvh;
            padding-bottom: calc(env(safe-area-inset-bottom,0px) + 4rem);
        }

        /* ── HEADER ── */
        .menu-header {
            padding: calc(env(safe-area-inset-top,0px) + 2.6rem) 1.5rem 2rem;
            text-align: center;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }
        .hdr-logo {
            width: 64px; height: 64px; border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border2);
            margin: 0 auto .9rem; display: block;
        }
        .hdr-logo-placeholder {
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--bg2);
            border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--cormorant); font-size: 1.6rem; font-weight: 600;
            color: var(--accent); margin: 0 auto .9rem;
        }
        .hdr-title {
            font-family: var(--cormorant);
            font-size: clamp(1.4rem, 6vw, 2rem);
            font-weight: 300; letter-spacing: .28em;
            text-transform: uppercase; color: var(--text);
            line-height: 1.1;
        }
        .hdr-rule {
            display: flex; align-items: center; gap: .6rem;
            justify-content: center; margin: .85rem auto 0; max-width: 180px;
        }
        .hdr-rule-line { flex: 1; height: 1px; background: var(--accent-m); }
        .hdr-rule-dot {
            width: 4px; height: 4px; border-radius: 50%;
            background: var(--accent); flex-shrink: 0;
        }

        /* ── LANG BAR ── */
        .lang-bar {
            position: fixed; top: calc(env(safe-area-inset-top,0px) + .65rem); right: .75rem;
            z-index: 300; display: flex; gap: .3rem;
        }
        .lang-bar a {
            display: inline-flex; align-items: center; gap: .2rem;
            padding: .26rem .6rem; border-radius: 50px;
            font-family: var(--jost); font-size: .62rem; font-weight: 500;
            letter-spacing: .06em; text-decoration: none;
            background: rgba(253,250,246,.9);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border: 1px solid var(--border2); color: var(--text-sub);
            transition: .2s;
        }
        .lang-bar a.active, .lang-bar a:hover {
            background: var(--accent); border-color: var(--accent); color: #fff;
        }

        /* ── STICKY NAV ── */
        .toc-bar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(253,250,246,.96);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            overflow-x: auto; overflow-y: hidden; scrollbar-width: none;
        }
        .toc-bar::-webkit-scrollbar { display: none; }
        .toc-tabs { display: flex; padding: 0 .75rem; min-width: max-content; }
        .toc-tab {
            flex-shrink: 0; padding: .72rem .95rem;
            font-family: var(--jost); font-size: .64rem; font-weight: 400;
            letter-spacing: .16em; text-transform: uppercase;
            white-space: nowrap; color: var(--muted);
            border-bottom: 1.5px solid transparent;
            text-decoration: none; cursor: pointer;
            transition: color .18s, border-color .18s;
        }
        .toc-tab:hover { color: var(--text-sub); }
        .toc-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* ── PAGE BODY ── */
        .page-body { padding-bottom: 1rem; }

        /* ── CATEGORY BLOCK ── */
        .cat-block { margin-bottom: .5rem; }

        /* Category image */
        .cat-image-wrap {
            position: relative; width: 100%;
            padding-top: 52%; overflow: hidden;
            background: var(--bg2);
        }
        .cat-image-wrap img {
            position: absolute; inset: 0;
            width: 100%; height: 100%; object-fit: cover;
            display: block;
        }
        .cat-image-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(46,35,24,.45) 0%, transparent 55%);
        }

        /* Category header */
        .cat-header {
            padding: 1.6rem 1.4rem .5rem;
            text-align: center;
            background: var(--surface);
        }
        .cat-header-inner {
            display: inline-flex; flex-direction: column; align-items: center; gap: .45rem;
        }
        .cat-label {
            font-family: var(--jost);
            font-size: .6rem; font-weight: 400;
            letter-spacing: .22em; text-transform: uppercase;
            color: var(--muted);
        }
        .cat-title {
            font-family: var(--cormorant);
            font-size: clamp(1.3rem, 5vw, 1.7rem);
            font-weight: 300; color: var(--text);
            letter-spacing: .06em; line-height: 1.1;
        }
        .cat-rule {
            display: flex; align-items: center; gap: .5rem; width: 100%; max-width: 120px;
        }
        .cat-rule-line { flex: 1; height: 1px; background: var(--accent-m); }
        .cat-rule-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }
        .cat-desc {
            font-family: var(--cormorant); font-style: italic;
            font-size: .95rem; color: var(--text-sub);
            text-align: center; padding: 0 1.4rem .6rem;
            line-height: 1.55; background: var(--surface);
        }

        /* ── FEATURED SECTION ── */
        .featured-header {
            padding: 1.6rem 1.4rem .5rem;
            text-align: center; background: var(--surface);
        }
        .feat-label {
            font-family: var(--jost); font-size: .6rem; font-weight: 400;
            letter-spacing: .22em; text-transform: uppercase; color: var(--muted);
            display: block; margin-bottom: .4rem;
        }
        .feat-title {
            font-family: var(--cormorant);
            font-size: clamp(1.3rem, 5vw, 1.7rem);
            font-weight: 300; color: var(--text); letter-spacing: .06em;
        }

        /* ── ITEMS LIST ── */
        .items-list {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 0 1.3rem;
        }

        .item-row {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: .75rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            cursor: pointer; transition: background .12s;
        }
        .items-list .item-row:last-child { border-bottom: none; }
        .item-row:hover, .item-row:active {
            background: var(--bg);
            margin: 0 -1.3rem; padding-left: 1.3rem; padding-right: 1.3rem;
        }

        .item-left { flex: 1; min-width: 0; }
        .item-right { flex-shrink: 0; text-align: right; min-width: 60px; }

        .ir-name {
            font-family: var(--cormorant);
            font-size: 1.05rem; font-weight: 400;
            color: var(--text); line-height: 1.2;
        }
        .ir-desc {
            font-family: var(--cormorant); font-style: italic;
            font-size: .88rem; color: var(--text-sub);
            line-height: 1.5; margin-top: .18rem;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .ir-badges { display: flex; gap: .2rem; flex-wrap: wrap; margin-top: .3rem; }
        .badge-pill {
            padding: 1px 7px; border-radius: 50px;
            font-family: var(--jost); font-size: .55rem;
            font-weight: 500; border: 1px solid; white-space: nowrap;
        }
        .ir-allergens { display: flex; gap: .14rem; flex-wrap: wrap; margin-top: .26rem; }
        .allergen-chip {
            display: inline-flex; align-items: center; gap: .1rem;
            padding: 1px 5px; border-radius: 50px;
            background: rgba(193,155,119,.1); border: 1px solid rgba(193,155,119,.3);
            font-size: .58rem; color: #9a7555; white-space: nowrap;
        }
        .allergen-chip .a-emo { font-size: .72rem; }
        .ir-info-tag {
            font-family: var(--jost); font-size: .55rem;
            color: var(--muted); border: 1px solid var(--border2);
            border-radius: 4px; padding: 1px 6px;
            display: inline-block; margin-top: .28rem; letter-spacing: .05em;
        }

        .ir-price {
            font-family: var(--cormorant);
            font-size: 1.05rem; font-weight: 400;
            color: var(--accent); white-space: nowrap; letter-spacing: .02em;
        }
        .ir-glass-bottle {
            display: flex; flex-direction: column; gap: .12rem;
            margin-top: .22rem; align-items: flex-end;
        }
        .ir-gb-tag {
            font-family: var(--jost); font-size: .61rem;
            color: var(--text-sub); background: var(--bg2);
            border: 1px solid var(--border); border-radius: 4px;
            padding: 1px 5px; white-space: nowrap;
        }

        /* ── SUB-HEADING DIVIDER ── */
        .sub-heading-divider {
            display: flex; align-items: center; gap: .55rem;
            padding: .9rem 0 .3rem;
        }
        .sh-line { flex: 1; height: 1px; background: var(--border2); }
        .sh-text {
            font-family: var(--jost); font-size: .58rem;
            letter-spacing: .18em; text-transform: uppercase;
            color: var(--muted); white-space: nowrap;
        }

        /* ── DIVIDER BETWEEN CATEGORIES ── */
        .cat-divider {
            height: 8px; background: var(--bg2);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        /* ── FOOTER ── */
        .menu-footer {
            text-align: center; padding: 3rem 1rem 2rem;
            background: var(--surface); border-top: 1px solid var(--border);
            display: flex; flex-direction: column; align-items: center; gap: .55rem;
        }
        .footer-rule {
            display: flex; align-items: center; gap: .55rem; width: 120px;
        }
        .footer-rule-line { flex: 1; height: 1px; background: var(--border2); }
        .footer-rule-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--accent); }
        .footer-text {
            font-family: var(--jost); font-size: .55rem;
            letter-spacing: .22em; text-transform: uppercase; color: var(--muted);
        }

        /* ── BOTTOM SHEET ── */
        .sheet-backdrop {
            display: none; position: fixed; inset: 0; z-index: 400;
            background: rgba(46,35,24,.45);
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
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
            border-bottom: 1px solid var(--border);
        }
        .sheet-handle::after {
            content: ''; display: block;
            width: 34px; height: 4px; border-radius: 2px; background: var(--border2);
        }
        .sheet-close {
            position: absolute; top: .65rem; right: .8rem; z-index: 3;
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--bg2); border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-sub); font-size: .8rem; cursor: pointer; transition: background .15s;
        }
        .sheet-close:hover { background: var(--bg); }

        .sheet-no-img {
            width: 100%; padding: 1.8rem 0 .9rem;
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
            border-bottom: 1px solid var(--border);
        }
        .sheet-no-img-rule {
            display: flex; align-items: center; gap: .45rem; width: 80px;
        }
        .sheet-no-img-rule-line { flex: 1; height: 1px; background: var(--border2); }
        .sheet-no-img-rule-dot { width: 4px; height: 4px; border-radius: 50%; background: var(--accent); }

        .sheet-body { padding: 1.2rem 1.3rem 2.5rem; }
        .sheet-badges { display: flex; gap: .3rem; flex-wrap: wrap; margin-bottom: .6rem; }
        .sheet-title {
            font-family: var(--cormorant);
            font-size: clamp(1.35rem, 5vw, 1.75rem);
            font-weight: 400; color: var(--text); line-height: 1.15; margin-bottom: .55rem;
        }
        .sheet-desc {
            font-family: var(--cormorant); font-style: italic;
            font-size: .92rem; color: var(--text-sub); line-height: 1.65; margin-bottom: 1rem;
        }
        .sheet-price-row {
            display: flex; align-items: baseline; gap: .55rem; margin-bottom: .65rem;
            padding: .55rem .85rem; background: var(--bg2);
            border-radius: 8px; border: 1px solid var(--border2);
        }
        .sheet-price { font-family: var(--cormorant); font-size: 1.45rem; font-weight: 400; color: var(--accent); letter-spacing: .04em; }
        .sheet-price-note { font-family: var(--jost); font-size: .67rem; color: var(--muted); }

        .glass-btl-sheet { display: flex; flex-direction: column; gap: .35rem; margin-bottom: .9rem; }
        .glass-btl-sheet-tag {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--bg2); border: 1px solid var(--border2);
            border-radius: 7px; padding: .4rem .7rem;
        }
        .gbs-lbl { font-family: var(--jost); font-size: .73rem; color: var(--text-sub); }
        .gbs-price { font-family: var(--cormorant); font-size: 1rem; font-weight: 400; color: var(--accent); }

        .sheet-tabs {
            display: flex; border-bottom: 1px solid var(--border); margin-bottom: .8rem;
        }
        .sheet-tab-btn {
            flex: 1; padding: .5rem .4rem; font-family: var(--jost); font-size: .65rem; font-weight: 400;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--muted); background: none; border: none;
            border-bottom: 1.5px solid transparent; cursor: pointer;
            transition: color .18s, border-color .18s;
        }
        .sheet-tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .sheet-tab-panel { display: none; }
        .sheet-tab-panel.active { display: block; }

        .sheet-options-title {
            font-family: var(--jost); font-size: .62rem; font-weight: 500;
            letter-spacing: .12em; text-transform: uppercase; color: var(--muted);
            margin-bottom: .5rem; border-bottom: 1px solid var(--border); padding-bottom: .4rem;
        }
        .sheet-option-row {
            display: flex; align-items: flex-start; gap: .5rem;
            padding: .5rem 0; border-bottom: 1px solid var(--border); font-size: .79rem;
        }
        .sheet-option-row:last-child { border-bottom: none; }
        .sop-label { font-family: var(--jost); color: var(--text-sub); min-width: 90px; flex-shrink: 0; font-size: .72rem; }
        .sop-value { color: var(--text); font-weight: 500; flex: 1; }
        .sop-tags { display: flex; gap: .25rem; flex-wrap: wrap; flex: 1; }
        .sop-tag { font-family: var(--jost); padding: 2px 8px; border-radius: 50px; background: var(--bg2); border: 1px solid var(--border2); font-size: .67rem; color: var(--text); }

        .allergen-chip-lg {
            display: inline-flex; align-items: center; gap: .35rem;
            margin: .2rem .22rem; padding: .3rem .68rem;
            border-radius: 40px; background: rgba(193,155,119,.1);
            border: 1px solid rgba(193,155,119,.3);
            font-family: var(--jost); font-size: .77rem; color: #9a7555;
        }
        .nut-cell {
            background: var(--bg2); border-radius: 8px;
            padding: .45rem .3rem; border: 1px solid var(--border2); text-align: center;
        }
        .nut-val { font-family: var(--cormorant); font-size: 1.05rem; font-weight: 400; color: var(--accent); }
        .nut-unit { font-family: var(--jost); font-size: .57rem; color: var(--muted); }
        .nut-lbl { font-family: var(--jost); font-size: .59rem; color: var(--text-sub); margin-top: .1rem; }

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
    @if($menu->logo)
        <img src="{{ asset('uploads/'.$menu->logo) }}" alt="" class="hdr-logo">
    @else
        <div class="hdr-logo-placeholder">{{ strtoupper(substr($menu->name,0,1)) }}</div>
    @endif
    <div class="hdr-title">{{ $_t['digital_menu'] }}</div>
    <div class="hdr-rule">
        <div class="hdr-rule-line"></div>
        <div class="hdr-rule-dot"></div>
        <div class="hdr-rule-line"></div>
    </div>
</header>

<nav class="toc-bar" id="toc-bar">
    <div class="toc-tabs">
        @if($featured->count() > 0)
            <a class="toc-tab" href="#section-featured">{{ $_t['featured'] }}</a>
        @endif
        @foreach($categories as $cat)
            <a class="toc-tab" href="#section-cat-{{ $cat->id }}">{{ $cat->getTitle($lang) }}</a>
        @endforeach
    </div>
</nav>

<main class="page-body">

    @if($featured->count() > 0)
    <div class="cat-block" id="section-featured">
        <div class="featured-header">
            <span class="feat-label">— {{ $_t['recommended'] }} —</span>
            <div class="feat-title">{{ $_t['featured'] }}</div>
        </div>
        <ul class="items-list">
            @foreach($featured as $fitem)
            <li class="item-row" onclick="openSheet({{ $fitem->id }})">
                <div class="item-left">
                    <div class="ir-name">{{ $fitem->getTitle($lang) }}</div>
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
    <div class="cat-divider"></div>
    @endif

    @foreach($menu->categories as $loop_index => $category)
    @if($loop_index > 0)<div class="cat-divider"></div>@endif
    <div class="cat-block" id="section-cat-{{ $category->id }}">

        {{-- Category image if available --}}
        @if($category->image)
        <div class="cat-image-wrap">
            <img src="{{ asset('uploads/'.$category->image) }}" alt="{{ $category->getTitle($lang) }}">
            <div class="cat-image-overlay"></div>
        </div>
        @endif

        <div class="cat-header">
            <div class="cat-header-inner">
                @if($category->icon)<span class="cat-label">{{ $category->icon }}</span>@endif
                <div class="cat-title">{{ $category->getTitle($lang) }}</div>
                <div class="cat-rule">
                    <div class="cat-rule-line"></div>
                    <div class="cat-rule-dot"></div>
                    <div class="cat-rule-line"></div>
                </div>
            </div>
        </div>

        @if($category->getDescription($lang))
            <div class="cat-desc">{{ $category->getDescription($lang) }}</div>
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
                    <div class="sh-text">{{ $sh }}</div>
                    <div class="sh-line"></div>
                </div>
            </li>
            @endif
            <li class="item-row" onclick="openSheet({{ $item->id }})">
                <div class="item-left">
                    <div class="ir-name">{{ $item->getTitle($lang) }}</div>
                    @if($item->getDescription($lang))
                        <div class="ir-desc">{{ $item->getDescription($lang) }}</div>
                    @endif
                    @if($item->badges)
                    <div class="ir-badges">
                        @foreach(array_slice($item->badges, 0, 3) as $badge)
                        @php $bc = \App\Models\QrMenuItem::BADGE_COLORS[$badge] ?? '#C19B77'; @endphp
                        <span class="badge-pill" style="color:{{ $bc }};border-color:{{ $bc }}40;background:{{ $bc }}12">{{ $badge }}</span>
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
        <div class="footer-rule-dot"></div>
        <div class="footer-rule-line"></div>
    </div>
    <div class="footer-text">{{ $menu->getTitle($lang) }}</div>
    <div class="footer-rule">
        <div class="footer-rule-line"></div>
        <div class="footer-rule-dot"></div>
        <div class="footer-rule-line"></div>
    </div>
</footer>

{{-- DETAY BOTTOM SHEET --}}
<div class="sheet-backdrop" id="sheetBackdrop" onclick="closeSheet(event)">
    <div class="sheet" id="detailSheet">
        <div class="sheet-handle"></div>
        <button class="sheet-close" onclick="closeSheet(null,true)">&#10005;</button>
        <div id="sheetNoImg" class="sheet-no-img">
            <div class="sheet-no-img-rule">
                <div class="sheet-no-img-rule-line"></div>
                <div class="sheet-no-img-rule-dot"></div>
                <div class="sheet-no-img-rule-line"></div>
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
                <div id="sheetIngredientsTxt" style="font-family:'Cormorant Garamond',Georgia,serif;font-style:italic;font-size:.92rem;color:var(--text-sub);line-height:1.65;padding:.4rem 0"></div>
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
                'calories' => $_fp->calories, 'protein' => $_fp->protein,
                'carbs'    => $_fp->carbs,    'fat'     => $_fp->fat,
            ] : null,
        ];
    }
}
foreach ($featured as $_fit) {
    $exists = array_filter($_menuItemsJson, fn($i) => $i['id'] === $_fit->id);
    if (!$exists) {
        $_fp = $_fit->foodProduct; $_allergens = [];
        foreach ($_fit->foodProduct?->allergens ?? [] as $_ak) {
            $aInfo = \App\Models\FoodProduct::ALLERGENS[$_ak] ?? null;
            if ($aInfo) $_allergens[] = ['key' => $_ak, 'label' => $aInfo[$lang] ?? $aInfo['tr'], 'emoji' => $aInfo['emoji']];
        }
        $_menuItemsJson[] = [
            'id' => $_fit->id, 'title' => $_fit->getTitle($lang), 'description' => $_fit->getDescription($lang),
            'price' => $_fit->effectivePrice() ? $_fit->formattedPrice($menu->currency_symbol) : null,
            'price_glass' => $_fit->price_glass ?? $_fp?->price_glass, 'price_bottle' => $_fit->price_bottle ?? $_fp?->price_bottle,
            'cl_glass' => $_fit->cl_glass ?? $_fp?->cl_glass, 'cl_bottle' => $_fit->cl_bottle ?? $_fp?->cl_bottle,
            'badges' => $_fit->badges ?? [], 'allergens' => $_allergens,
            'ingredients' => $_fp?->ingredients[$lang] ?? $_fp?->ingredients['tr'] ?? null,
            'options' => $_fp?->options ?? [],
            'nutrition' => ($_fp && ($_fp->calories || $_fp->protein || $_fp->carbs || $_fp->fat)) ? [
                'calories' => $_fp->calories, 'protein' => $_fp->protein, 'carbs' => $_fp->carbs, 'fat' => $_fp->fat,
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
    var tocLinks = document.querySelectorAll('.toc-tab[href^="#"]');
    var sections = [];
    tocLinks.forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            var target = document.querySelector(a.getAttribute('href'));
            if(!target) return;
            var offset = (document.getElementById('toc-bar').offsetHeight || 44) + 8;
            var top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({top: top, behavior: 'smooth'});
        });
        var id = a.getAttribute('href').slice(1);
        var el = document.getElementById(id);
        if(el) sections.push({link: a, el: el});
    });
    window.addEventListener('scroll', function(){
        var offset = (document.getElementById('toc-bar').offsetHeight || 44) + 20;
        var current = null;
        sections.forEach(function(s){ if(s.el.getBoundingClientRect().top <= offset) current = s; });
        tocLinks.forEach(function(l){ l.classList.remove('active'); });
        if(current) current.link.classList.add('active');
    }, {passive: true});
    if(sections.length > 0) sections[0].link.classList.add('active');

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
        var col = BADGE_COLORS[b] || '#C19B77';
        var span = document.createElement('span'); span.className = 'badge-pill';
        span.style.cssText = 'color:'+col+';border-color:'+col+'40;background:'+col+'12;font-size:.7rem;padding:3px 10px';
        span.textContent = b; badgesEl.appendChild(span);
    });
    var descEl = document.getElementById('sheetDesc');
    if(item.description){ descEl.textContent = item.description; descEl.style.display = ''; } else { descEl.style.display = 'none'; }
    var priceRow = document.getElementById('sheetPriceRow');
    var glassBtlEl = document.getElementById('sheetGlassBottle');
    if(item.price){ document.getElementById('sheetPrice').textContent = item.price; priceRow.style.display = ''; } else { priceRow.style.display = 'none'; }
    glassBtlEl.innerHTML = '';
    if(item.price_glass || item.price_bottle){
        var sym = '{{ $menu->currency_symbol }}';
        if(item.price_glass){ var t1 = document.createElement('div'); t1.className = 'glass-btl-sheet-tag'; t1.innerHTML = '<span class="gbs-lbl">🥃 '+I18N.glass+(item.cl_glass?' '+item.cl_glass+'cl':'')+'</span><span class="gbs-price">'+sym+' '+item.price_glass.toFixed(2)+'</span>'; glassBtlEl.appendChild(t1); }
        if(item.price_bottle){ var t2 = document.createElement('div'); t2.className = 'glass-btl-sheet-tag'; t2.innerHTML = '<span class="gbs-lbl">🍾 '+I18N.bottle+(item.cl_bottle?' '+item.cl_bottle+'cl':'')+'</span><span class="gbs-price">'+sym+' '+item.price_bottle.toFixed(2)+'</span>'; glassBtlEl.appendChild(t2); }
        glassBtlEl.style.display = '';
    } else { glassBtlEl.style.display = 'none'; }
    var optsWrap = document.getElementById('sheetOptions'); var optsList = document.getElementById('sheetOptionsList'); optsList.innerHTML = '';
    var opts = item.options || [];
    if(opts.length > 0){
        opts.forEach(function(opt){ var row = document.createElement('div'); row.className = 'sheet-option-row'; var lbl = document.createElement('div'); lbl.className = 'sop-label'; if(opt.label && typeof opt.label === 'object'){ lbl.textContent = opt.label[CURRENT_LANG] || opt.label['tr'] || Object.values(opt.label)[0] || ''; } else { lbl.textContent = opt.label || ''; } row.appendChild(lbl); if(opt.type === 'tags'){ var tw = document.createElement('div'); tw.className = 'sop-tags'; (opt.value||'').split(',').forEach(function(tag){ tag=tag.trim(); if(!tag) return; var t=document.createElement('span'); t.className='sop-tag'; t.textContent=tag; tw.appendChild(t); }); row.appendChild(tw); } else { var val = document.createElement('div'); val.className = 'sop-value'; val.textContent = opt.value; row.appendChild(val); } optsList.appendChild(row); });
        optsWrap.style.display = '';
    } else { optsWrap.style.display = 'none'; }
    var allergenList = document.getElementById('sheetAllergensList'); allergenList.innerHTML = '';
    var allergens = item.allergens || [];
    if(allergens.length > 0){ allergens.forEach(function(a){ var chip = document.createElement('div'); chip.className = 'allergen-chip-lg'; chip.innerHTML = '<span style="font-size:1.15rem">'+a.emoji+'</span><span>'+a.label+'</span>'; allergenList.appendChild(chip); }); } else { allergenList.innerHTML = '<p style="font-size:.78rem;color:var(--muted);margin:.4rem 0">—</p>'; }
    var ingEl = document.getElementById('sheetIngredientsTxt'); ingEl.textContent = item.ingredients || '—';
    var nutWrap = document.getElementById('sheetNutrition');
    if(item.nutrition){ var n=item.nutrition; function nutCell(id,val,unit,lbl){ var el=document.getElementById(id); el.innerHTML='<div class="nut-val">'+(val||'—')+'</div><div class="nut-unit">'+unit+'</div><div class="nut-lbl">'+lbl+'</div>'; } nutCell('nutCalories',n.calories?Math.round(n.calories):null,'kcal',I18N.calories); nutCell('nutProtein',n.protein,'g',I18N.protein); nutCell('nutCarbs',n.carbs,'g',I18N.carbs); nutCell('nutFat',n.fat,'g',I18N.fat); nutWrap.style.display=''; } else { nutWrap.style.display='none'; }
    var sheetTabs=document.getElementById('sheetTabs'); var tabInfo=document.getElementById('tabInfo'); var tabIngredients=document.getElementById('tabIngredients'); var tabAllergens=document.getElementById('tabAllergens');
    var hasContent = opts.length > 0 || allergens.length > 0 || item.ingredients || item.nutrition;
    if(hasContent){ sheetTabs.style.display=''; sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(btn){ btn.onclick=function(){ sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(b){ b.classList.remove('active'); }); btn.classList.add('active'); tabInfo.classList.remove('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active'); if(btn.dataset.tab==='allergens') tabAllergens.classList.add('active'); else if(btn.dataset.tab==='ingredients') tabIngredients.classList.add('active'); else tabInfo.classList.add('active'); }; }); tabInfo.classList.add('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active'); sheetTabs.querySelectorAll('.sheet-tab-btn').forEach(function(b,i){ b.classList.toggle('active',i===0); }); } else { sheetTabs.style.display='none'; tabInfo.classList.add('active'); tabIngredients.classList.remove('active'); tabAllergens.classList.remove('active'); }
    var backdrop=document.getElementById('sheetBackdrop'); backdrop.style.display='flex'; document.body.style.overflow='hidden';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){ backdrop.classList.add('open'); }); });
}
function closeSheet(e,force){
    if(e && e.target!==document.getElementById('sheetBackdrop') && !force) return;
    var backdrop=document.getElementById('sheetBackdrop'); backdrop.classList.remove('open'); document.body.style.overflow='';
    setTimeout(function(){ backdrop.style.display='none'; },310);
}
document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeSheet(null,true); });
</script>
</body>
</html>
