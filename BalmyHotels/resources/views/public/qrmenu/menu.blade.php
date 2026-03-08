<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $menu->theme_color ?? '#c4a35a' }}">
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:      {{ $menu->theme_color ?? '#c4a35a' }};
            --accent-dim:  {{ $menu->theme_color ?? '#c4a35a' }}26;
            --accent-mid:  {{ $menu->theme_color ?? '#c4a35a' }}55;
            --bg:          #0d1117;
            --surface:     #161c26;
            --surface2:    #1c2534;
            --surface3:    #22304a;
            --border:      rgba(255,255,255,.07);
            --border2:     rgba(255,255,255,.12);
            --text:        #e6ddd0;
            --text-sub:    #a09280;
            --muted:       #5a6070;
            --serif:       'Cormorant Garamond', Georgia, serif;
            --sans:        'DM Sans', system-ui, sans-serif;
            --radius:      16px;
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

        /* ---- HEADER ---- */
        .menu-header {
            position: relative;
            height: clamp(200px, 45vw, 300px);
            display: flex; align-items: flex-end;
            padding: 1.4rem; overflow: hidden;
        }
        .hdr-bg {
            position: absolute; inset: 0;
            background-size: cover; background-position: center;
            @if($menu->cover_image)
            background-image: url('{{ asset('uploads/'.$menu->cover_image) }}');
            @else
            background: linear-gradient(145deg, #0d1117 0%, #1c2a40 45%, {{ $menu->theme_color ?? '#c4a35a' }}33 100%);
            @endif
            filter: brightness(.4) saturate(1.2);
        }
        .hdr-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(13,17,23,1) 0%, rgba(13,17,23,.5) 45%, rgba(13,17,23,.05) 100%);
        }
        .hdr-content {
            position: relative; z-index: 2;
            display: flex; align-items: center; gap: .9rem; width: 100%;
        }
        .hdr-logo {
            width: 56px; height: 56px; border-radius: 50%;
            object-fit: cover; border: 1.5px solid var(--accent);
            box-shadow: 0 0 0 4px var(--accent-dim); flex-shrink: 0;
        }
        .hdr-logo-placeholder {
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #e8c97a);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--serif); font-size: 1.5rem; font-weight: 600; color: #fff;
            flex-shrink: 0; box-shadow: 0 0 0 4px var(--accent-dim), 0 4px 24px var(--accent-mid);
        }
        .hdr-title {
            font-family: var(--serif);
            font-size: clamp(1.4rem, 5.5vw, 1.9rem);
            font-weight: 600; color: #fff; line-height: 1.15;
            letter-spacing: .01em; text-shadow: 0 1px 12px rgba(0,0,0,.5);
        }
        .hdr-sub {
            font-size: .64rem; color: var(--text-sub);
            letter-spacing: .14em; text-transform: uppercase; margin-top: .2rem;
        }

        /* ---- D�L �UBU�U ---- */
        .lang-bar {
            position: fixed; top: calc(env(safe-area-inset-top,0px) + .7rem); right: .85rem;
            z-index: 300; display: flex; gap: .35rem;
        }
        .lang-bar a {
            display: inline-flex; align-items: center; gap: .22rem;
            padding: .28rem .65rem; border-radius: 50px;
            font-size: .65rem; font-weight: 500; letter-spacing: .05em;
            text-decoration: none;
            backdrop-filter: blur(20px) saturate(1.5);
            -webkit-backdrop-filter: blur(20px) saturate(1.5);
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(13,17,23,.7); color: rgba(255,255,255,.5);
            transition: .2s;
        }
        .lang-bar a.active, .lang-bar a:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

        /* ---- KATEGOR� �UBU�U ---- */
        .cat-bar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(13,17,23,.9);
            backdrop-filter: blur(24px) saturate(1.8);
            -webkit-backdrop-filter: blur(24px) saturate(1.8);
            border-bottom: 1px solid var(--border2);
            overflow-x: auto; overflow-y: hidden; scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .cat-bar::-webkit-scrollbar { display: none; }
        .cat-tabs { display: flex; padding: 0 .75rem; min-width: max-content; }
        .cat-tab {
            flex-shrink: 0; padding: .78rem 1rem;
            font-size: .76rem; font-weight: 500; white-space: nowrap;
            color: var(--muted); border-bottom: 2px solid transparent;
            text-decoration: none; cursor: pointer;
            transition: color .18s, border-color .18s;
            display: flex; align-items: center; gap: .3rem;
        }
        .cat-tab:hover { color: var(--text); }
        .cat-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* ---- TAB SECTIONS ---- */
        .cat-section { display: none; }
        .cat-section.active { display: block; }

        /* ---- SECTION ---- */
        .section { padding: 1.5rem .85rem 0; }
        .section-header { display: flex; align-items: center; gap: .65rem; margin-bottom: .8rem; }
        .section-title {
            font-family: var(--serif);
            font-size: clamp(1.15rem, 4.5vw, 1.45rem);
            font-weight: 600; font-style: italic;
            color: #f0e8da; letter-spacing: .01em; white-space: nowrap;
        }
        .section-line { flex: 1; height: 1px; background: linear-gradient(to right, var(--border2), transparent); }
        .section-desc { font-size: .74rem; color: var(--text-sub); margin-bottom: .9rem; line-height: 1.55; }

        /* ---- �NE �IKANLAR ---- */
        .featured-scroll {
            display: flex; gap: .75rem; overflow-x: auto; padding-bottom: .6rem;
            scrollbar-width: none; -webkit-overflow-scrolling: touch;
        }
        .featured-scroll::-webkit-scrollbar { display: none; }
        .feat-card {
            flex-shrink: 0; width: clamp(140px, 38vw, 175px);
            background: var(--surface); border-radius: 14px; overflow: hidden;
            border: 1px solid var(--border2); position: relative; cursor: pointer;
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }
        .feat-card:active, .feat-card:hover { transform: scale(.97); border-color: var(--accent-mid); box-shadow: 0 8px 28px rgba(0,0,0,.4); }
        .feat-img { width: 100%; height: clamp(90px, 23vw, 120px); object-fit: cover; display: block; }
        .feat-no-img {
            width: 100%; height: clamp(90px, 23vw, 120px);
            background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
        }
        .feat-badge {
            position: absolute; top: .45rem; left: .45rem;
            background: var(--accent); color: #fff;
            font-size: .57rem; font-weight: 600; letter-spacing: .07em; text-transform: uppercase;
            padding: 2px 8px; border-radius: 50px; box-shadow: 0 2px 8px var(--accent-mid);
        }
        .feat-body { padding: .6rem .7rem .75rem; }
        .feat-name { font-size: .8rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .feat-desc { font-size: .67rem; color: var(--text-sub); margin-top: .18rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .feat-price { font-family: var(--serif); font-size: .92rem; font-weight: 600; color: var(--accent); margin-top: .4rem; }

        /* ---- �R�N GR�D ---- */
        .items-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .7rem; padding-bottom: 1.4rem; }
        .items-grid > .item-card:only-child { grid-column: 1 / -1; max-width: 52%; }

        .item-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden;
            display: flex; flex-direction: column; cursor: pointer;
            transition: transform .2s, box-shadow .2s, border-color .22s;
            -webkit-tap-highlight-color: transparent; position: relative;
        }
        .item-card:hover, .item-card:active { transform: scale(.97); border-color: var(--accent-mid); box-shadow: 0 8px 32px rgba(0,0,0,.45); }

        .ic-img-wrap { position: relative; width: 100%; padding-top: 62%; background: var(--surface2); overflow: hidden; }
        .ic-img-wrap img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .38s ease; }
        .item-card:hover .ic-img-wrap img { transform: scale(1.06); }
        .ic-no-img { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--muted); }
        .ic-wave { position: absolute; bottom: -1px; left: 0; right: 0; height: 22px; background: var(--surface); border-radius: 50% 50% 0 0 / 100% 100% 0 0; }
        .ic-detail-hint {
            position: absolute; bottom: .45rem; right: .5rem;
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--accent); color: #fff;
            font-size: .72rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px var(--accent-mid); z-index: 2; pointer-events: none;
        }

        .ic-body { padding: .42rem .62rem .7rem; flex: 1; display: flex; flex-direction: column; }
        .ic-name { font-size: .82rem; font-weight: 600; color: #f0e8da; line-height: 1.25; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .ic-desc { font-size: .68rem; color: var(--text-sub); margin-top: .18rem; line-height: 1.4; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; flex: 1; }
        .ic-badges { display: flex; gap: .2rem; flex-wrap: wrap; margin-top: .32rem; }
        .badge-pill { padding: 2px 7px; border-radius: 50px; font-size: .57rem; font-weight: 500; border: 1px solid; white-space: nowrap; }
        .ic-price { font-family: var(--serif); font-size: .94rem; font-weight: 600; color: var(--accent); margin-top: .42rem; }

        /* ---- FOOTER ---- */
        .menu-footer { text-align: center; padding: 2.5rem 1rem 1.5rem; font-size: .6rem; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.08); }

        /* ---- DETAY SHEET ---- */
        .sheet-backdrop {
            display: none; position: fixed; inset: 0; z-index: 400;
            background: rgba(0,0,0,.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            align-items: flex-end; justify-content: center;
            opacity: 0; transition: opacity .28s ease;
        }
        .sheet-backdrop.open { display: flex; opacity: 1; }

        .sheet {
            position: relative; background: var(--surface);
            border-radius: 22px 22px 0 0; width: 100%; max-width: 520px;
            max-height: 88dvh; overflow-y: auto; overscroll-behavior: contain;
            transform: translateY(60px); transition: transform .3s cubic-bezier(.22,.9,.36,1);
            scrollbar-width: none;
        }
        .sheet::-webkit-scrollbar { display: none; }
        .sheet-backdrop.open .sheet { transform: translateY(0); }

        .sheet-handle { position: absolute; top: .7rem; left: 50%; transform: translateX(-50%); width: 36px; height: 4px; border-radius: 2px; background: rgba(255,255,255,.15); z-index: 2; }
        .sheet-close {
            position: absolute; top: .7rem; right: .8rem; z-index: 3;
            width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center;
            border: none; color: rgba(255,255,255,.55); font-size: .9rem; cursor: pointer; transition: background .15s;
        }
        .sheet-close:hover { background: rgba(255,255,255,.2); color: #fff; }

        .sheet-img-wrap { position: relative; width: 100%; height: clamp(190px, 50vw, 270px); background: var(--surface2); overflow: hidden; border-radius: 22px 22px 0 0; }
        .sheet-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sheet-img-overlay { position: absolute; inset: 0; background: linear-gradient(to top, var(--surface) 0%, transparent 55%); }
        .sheet-no-img { width: 100%; height: 140px; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 3rem; border-radius: 22px 22px 0 0; }

        .sheet-body { padding: 1.2rem 1.3rem 2rem; }
        .sheet-badges { display: flex; gap: .3rem; flex-wrap: wrap; margin-bottom: .65rem; }
        .sheet-title { font-family: var(--serif); font-size: clamp(1.3rem, 5vw, 1.65rem); font-weight: 600; color: #f5ede0; line-height: 1.2; margin-bottom: .5rem; }
        .sheet-desc { font-size: .82rem; color: var(--text-sub); line-height: 1.6; margin-bottom: 1rem; }

        .sheet-price-row {
            display: flex; align-items: baseline; gap: .55rem; margin-bottom: 1.1rem;
            padding: .7rem .9rem; background: var(--surface2); border-radius: 10px; border: 1px solid var(--border2);
        }
        .sheet-price { font-family: var(--serif); font-size: 1.45rem; font-weight: 600; color: var(--accent); letter-spacing: .01em; }
        .sheet-price-note { font-size: .7rem; color: var(--muted); }

        .sheet-options { margin-top: .6rem; }
        .sheet-options-title { font-size: .67rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: .55rem; border-bottom: 1px solid var(--border); padding-bottom: .4rem; }
        .sheet-option-row { display: flex; align-items: flex-start; gap: .5rem; padding: .5rem 0; border-bottom: 1px solid var(--border); font-size: .79rem; }
        .sheet-option-row:last-child { border-bottom: none; }
        .sop-label { color: var(--text-sub); min-width: 90px; flex-shrink: 0; font-size: .74rem; }
        .sop-value { color: var(--text); font-weight: 500; flex: 1; }
        .sop-tags { display: flex; gap: .25rem; flex-wrap: wrap; flex: 1; }
        .sop-tag { padding: 2px 8px; border-radius: 50px; background: var(--surface3); border: 1px solid var(--border2); font-size: .68rem; color: var(--text); }

        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body>

@php
$_i18n = [
    'tr' => ['product_info'=>'�r�n Bilgileri','tax_note'=>'KDV dahil','featured'=>'�ne ��kanlar','recommended'=>'�nerilen','digital_menu'=>'Dijital Men�'],
    'en' => ['product_info'=>'Product Info','tax_note'=>'Tax included','featured'=>'Featured','recommended'=>'Recommended','digital_menu'=>'Digital Menu'],
    'de' => ['product_info'=>'Produktinfo','tax_note'=>'Inkl. MwSt.','featured'=>'Highlights','recommended'=>'Empfohlen','digital_menu'=>'Digitale Karte'],
    'ru' => ['product_info'=>'? ????????','tax_note'=>'??????? ???','featured'=>'???????????','recommended'=>'?????????????','digital_menu'=>'???????? ????'],
    'ar' => ['product_info'=>'??????? ??????','tax_note'=>'???? ???????','featured'=>'???????','recommended'=>'???? ??','digital_menu'=>'????? ?????'],
    'fr' => ['product_info'=>'Info produit','tax_note'=>'TVA incluse','featured'=>'En vedette','recommended'=>'Recommand�','digital_menu'=>'Menu num�rique'],
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

<div class="menu-header">
    <div class="hdr-bg"></div>
    <div class="hdr-overlay"></div>
    <div class="hdr-content">
        @if($menu->logo)
            <img src="{{ asset('uploads/'.$menu->logo) }}" alt="" class="hdr-logo">
        @else
            <div class="hdr-logo-placeholder">{{ strtoupper(substr($menu->name,0,1)) }}</div>
        @endif
        <div>
            <div class="hdr-title">{{ $menu->getTitle($lang) }}</div>
            <div class="hdr-sub">{{ $_t['digital_menu'] }}</div>
        </div>
    </div>
</div>

<div class="cat-bar" id="cat-bar">
    <div class="cat-tabs">
        @if($featured->count() > 0)
            <a class="cat-tab" data-id="featured">&#11088; {{ $_t['featured'] }}</a>
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
            <div class="section-title">&#11088; {{ $_t['featured'] }}</div>
            <div class="section-line"></div>
        </div>
        <div class="featured-scroll">
            @foreach($featured as $fitem)
            <div class="feat-card" onclick="openSheet({{ $fitem->id }})">
                <div class="feat-badge">{{ $_t['recommended'] }}</div>
                @if($fitem->image)
                    <img src="{{ asset('uploads/'.$fitem->image) }}" alt="{{ $fitem->getTitle($lang) }}" class="feat-img">
                @else
                    <div class="feat-no-img">??</div>
                @endif
                <div class="feat-body">
                    <div class="feat-name">{{ $fitem->getTitle($lang) }}</div>
                    @if($fitem->getDescription($lang))
                        <div class="feat-desc">{{ $fitem->getDescription($lang) }}</div>
                    @endif
                    @if($fitem->effectivePrice())
                        <div class="feat-price">{{ $fitem->formattedPrice($menu->currency_symbol) }}</div>
                    @endif
                </div>
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
            <div class="section-title">
                @if($category->icon) {{ $category->icon }} @endif
                {{ $category->getTitle($lang) }}
            </div>
            <div class="section-line"></div>
        </div>
        @if($category->getDescription($lang))
            <div class="section-desc">{{ $category->getDescription($lang) }}</div>
        @endif

        <div class="items-grid">
            @foreach($category->items->sortBy('sort_order') as $item)
            @php $hasOpts = !empty($item->foodProduct?->options); @endphp
            <div class="item-card" onclick="openSheet({{ $item->id }})">
                <div class="ic-img-wrap">
                    @if($item->image)
                        <img src="{{ asset('uploads/'.$item->image) }}" alt="{{ $item->getTitle($lang) }}">
                    @else
                        <div class="ic-no-img">??</div>
                    @endif
                    <div class="ic-wave"></div>
                    @if($hasOpts)
                    <div class="ic-detail-hint">i</div>
                    @endif
                </div>
                <div class="ic-body">
                    <div class="ic-name">{{ $item->getTitle($lang) }}</div>
                    @if($item->getDescription($lang))
                        <div class="ic-desc">{{ $item->getDescription($lang) }}</div>
                    @endif
                    @if($item->badges)
                    <div class="ic-badges">
                        @foreach(array_slice($item->badges, 0, 2) as $badge)
                        @php $bc = \App\Models\QrMenuItem::BADGE_COLORS[$badge] ?? '#c4a35a'; @endphp
                        <span class="badge-pill" style="color:{{ $bc }};border-color:{{ $bc }}30;background:{{ $bc }}14">{{ $badge }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if($item->effectivePrice())
                    <div class="ic-price">{{ $item->formattedPrice($menu->currency_symbol) }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

<div class="menu-footer">{{ $menu->getTitle($lang) }} &nbsp;�&nbsp; {{ $_t['digital_menu'] }}</div>

{{-- DETAY BOTTOM SHEET --}}
<div class="sheet-backdrop" id="sheetBackdrop" onclick="closeSheet(event)">
    <div class="sheet" id="detailSheet">
        <div class="sheet-handle"></div>
        <button class="sheet-close" onclick="closeSheet(null,true)">&#10005;</button>
        <div id="sheetImgWrap" class="sheet-img-wrap" style="display:none">
            <img id="sheetImg" src="" alt="">
            <div class="sheet-img-overlay"></div>
        </div>
        <div id="sheetNoImg" class="sheet-no-img">??</div>
        <div class="sheet-body">
            <div id="sheetBadges" class="sheet-badges"></div>
            <div id="sheetTitle" class="sheet-title"></div>
            <div id="sheetDesc" class="sheet-desc" style="display:none"></div>
            <div id="sheetPriceRow" class="sheet-price-row" style="display:none">
                <div id="sheetPrice" class="sheet-price"></div>
                <div class="sheet-price-note">{{ $_t['tax_note'] }}</div>
            </div>
            <div id="sheetOptions" class="sheet-options" style="display:none">
                <div class="sheet-options-title">{{ $_t['product_info'] }}</div>
                <div id="sheetOptionsList"></div>
            </div>
        </div>
    </div>
</div>

@php
$_menuItemsJson = [];
foreach ($categories as $_cat) {
    foreach ($_cat->items->sortBy('sort_order') as $_it) {
        $_menuItemsJson[] = [
            'id'          => $_it->id,
            'title'       => $_it->getTitle($lang),
            'description' => $_it->getDescription($lang),
            'price'       => $_it->effectivePrice() ? $_it->formattedPrice($menu->currency_symbol) : null,
            'image'       => $_it->image ? asset('uploads/'.$_it->image) : null,
            'badges'      => $_it->badges ?? [],
            'options'     => $_it->foodProduct?->options ?? [],
        ];
    }
}
@endphp
<script>
var CURRENT_LANG = '{{ $lang }}';
var MENU_ITEMS = @json($_menuItemsJson);
var BADGE_COLORS = @json(\App\Models\QrMenuItem::BADGE_COLORS);
</script>

<script>
(function(){
    var tabs = document.querySelectorAll('.cat-tab[data-id]');
    var catBar = document.getElementById('cat-bar');

    function switchTab(id) {
        // Hide all sections
        document.querySelectorAll('.cat-section').forEach(function(s){ s.classList.remove('active'); });
        // Deactivate all tabs
        tabs.forEach(function(t){ t.classList.remove('active'); });
        // Show selected section
        var sec = document.getElementById(id);
        if(sec) sec.classList.add('active');
        // Activate selected tab
        var tab = document.querySelector('.cat-tab[data-id="'+id+'"]');
        if(tab) {
            tab.classList.add('active');
            tab.scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
        }
        // Scroll page to top of content (below cat-bar)
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    tabs.forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            switchTab(a.dataset.id);
        });
    });

    // Activate first tab on load
    if(tabs.length > 0) {
        switchTab(tabs[0].dataset.id);
    }

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

    var imgWrap=document.getElementById('sheetImgWrap');
    var noImg=document.getElementById('sheetNoImg');
    if(item.image){ document.getElementById('sheetImg').src=item.image; imgWrap.style.display=''; noImg.style.display='none'; }
    else { imgWrap.style.display='none'; noImg.style.display=''; }

    var badgesEl=document.getElementById('sheetBadges');
    badgesEl.innerHTML='';
    (item.badges||[]).forEach(function(b){
        var col=BADGE_COLORS[b]||'#c4a35a';
        var span=document.createElement('span'); span.className='badge-pill';
        span.style.cssText='color:'+col+';border-color:'+col+'30;background:'+col+'14;font-size:.72rem;padding:3px 10px';
        span.textContent=b; badgesEl.appendChild(span);
    });

    document.getElementById('sheetTitle').textContent=item.title;

    var descEl=document.getElementById('sheetDesc');
    if(item.description){ descEl.textContent=item.description; descEl.style.display=''; } else { descEl.style.display='none'; }

    var priceRow=document.getElementById('sheetPriceRow');
    if(item.price){ document.getElementById('sheetPrice').textContent=item.price; priceRow.style.display=''; } else { priceRow.style.display='none'; }

    var optsWrap=document.getElementById('sheetOptions');
    var optsList=document.getElementById('sheetOptionsList');
    optsList.innerHTML='';
    var opts=item.options||[];
    if(opts.length>0){
        opts.forEach(function(opt){
            var row=document.createElement('div'); row.className='sheet-option-row';

            // Label: support both old string format and new {tr,en,...} object format
            var lbl=document.createElement('div'); lbl.className='sop-label';
            if(opt.label && typeof opt.label === 'object') {
                lbl.textContent = opt.label[CURRENT_LANG] || opt.label['tr'] || opt.label['en'] || Object.values(opt.label)[0] || '';
            } else {
                lbl.textContent = opt.label || '';
            }
            row.appendChild(lbl);

            if(opt.type==='tags'){
                var tw=document.createElement('div'); tw.className='sop-tags';
                (opt.value||'').split(',').forEach(function(tag){ tag=tag.trim(); if(!tag) return; var t=document.createElement('span'); t.className='sop-tag'; t.textContent=tag; tw.appendChild(t); });
                row.appendChild(tw);
            } else {
                var val=document.createElement('div'); val.className='sop-value'; val.textContent=opt.value; row.appendChild(val);
            }
            optsList.appendChild(row);
        });
        optsWrap.style.display='';
    } else { optsWrap.style.display='none'; }

    var backdrop=document.getElementById('sheetBackdrop');
    backdrop.style.display='flex'; document.body.style.overflow='hidden';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){ backdrop.classList.add('open'); }); });
}

function closeSheet(e,force){
    if(e && e.target!==document.getElementById('sheetBackdrop') && !force) return;
    var backdrop=document.getElementById('sheetBackdrop');
    backdrop.classList.remove('open'); document.body.style.overflow='';
    setTimeout(function(){ backdrop.style.display='none'; },310);
}

document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeSheet(null,true); });
</script>
</body>
</html>