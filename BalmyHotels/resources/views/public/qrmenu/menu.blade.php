<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $menu->theme_color ?? '#1a1a2e' }}">
    <title>{{ $menu->getTitle($lang) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:     {{ $menu->theme_color ?? '#c19b77' }};
            --accent-dim: {{ $menu->theme_color ?? '#c19b77' }}22;
            --bg:         #0a0a0a;
            --surface:    #141414;
            --surface2:   #1e1e1e;
            --border:     rgba(255,255,255,.07);
            --text:       #ede8e1;
            --muted:      #636363;
            --serif:      'Cormorant Garamond', Georgia, serif;
            --sans:       'DM Sans', system-ui, sans-serif;
        }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100dvh;
            padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 2rem);
        }

        /* ───── HEADER ───── */
        .menu-header {
            position: relative;
            height: clamp(180px, 40vw, 280px);
            display: flex;
            align-items: flex-end;
            padding: 1.25rem;
            overflow: hidden;
        }
        .hdr-bg {
            position: absolute; inset: 0;
            background-size: cover;
            background-position: center;
            @if($menu->cover_image)
            background-image: url('{{ asset('storage/'.$menu->cover_image) }}');
            @else
            background: linear-gradient(140deg, #111 0%, {{ $menu->theme_color ?? '#c19b77' }}44 100%);
            @endif
            filter: brightness(.35);
        }
        .hdr-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(10,10,10,1) 0%, rgba(10,10,10,.15) 55%, transparent 100%);
        }
        .hdr-content {
            position: relative; z-index: 2;
            display: flex; align-items: center; gap: .85rem;
            width: 100%;
        }
        .hdr-logo {
            width: 52px; height: 52px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--accent);
            box-shadow: 0 0 0 4px var(--accent-dim);
            flex-shrink: 0;
        }
        .hdr-logo-placeholder {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--serif);
            font-size: 1.4rem; font-weight: 600; color: #fff;
            flex-shrink: 0;
            box-shadow: 0 0 0 4px var(--accent-dim);
        }
        .hdr-title {
            font-family: var(--serif);
            font-size: clamp(1.3rem, 5vw, 1.8rem);
            font-weight: 600;
            color: #fff;
            line-height: 1.15;
            letter-spacing: .01em;
        }
        .hdr-sub {
            font-size: .65rem;
            color: var(--muted);
            letter-spacing: .14em;
            text-transform: uppercase;
            margin-top: .18rem;
        }

        /* ───── DİL ÇUBUĞU ───── */
        .lang-bar {
            position: fixed;
            top: calc(env(safe-area-inset-top, 0px) + .75rem);
            right: .85rem;
            z-index: 200;
            display: flex; gap: .35rem;
        }
        .lang-bar a {
            display: inline-flex; align-items: center; gap: .22rem;
            padding: .26rem .6rem;
            border-radius: 50px;
            font-size: .66rem; font-weight: 500;
            letter-spacing: .05em;
            text-decoration: none;
            backdrop-filter: blur(16px) saturate(1.5);
            -webkit-backdrop-filter: blur(16px) saturate(1.5);
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(10,10,10,.65);
            color: rgba(255,255,255,.55);
            transition: .2s;
        }
        .lang-bar a.active,
        .lang-bar a:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        /* ───── KATEGORİ ÇUBUĞU ───── */
        .cat-bar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(10,10,10,.92);
            backdrop-filter: blur(20px) saturate(1.8);
            -webkit-backdrop-filter: blur(20px) saturate(1.8);
            border-bottom: 1px solid var(--border);
            overflow-x: auto; overflow-y: hidden;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .cat-bar::-webkit-scrollbar { display: none; }
        .cat-tabs {
            display: flex;
            padding: 0 .75rem;
            min-width: max-content;
        }
        .cat-tab {
            flex-shrink: 0;
            padding: .75rem .95rem;
            font-size: .77rem; font-weight: 500;
            white-space: nowrap;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            text-decoration: none;
            transition: color .18s, border-color .18s;
            display: flex; align-items: center; gap: .3rem;
        }
        .cat-tab:hover  { color: var(--text); }
        .cat-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* ───── BÖLÜM ───── */
        .section { padding: 1.35rem .9rem 0; }

        .section-header {
            display: flex; align-items: center; gap: .6rem;
            margin-bottom: .75rem;
        }
        .section-title {
            font-family: var(--serif);
            font-size: clamp(1.1rem, 4.5vw, 1.4rem);
            font-weight: 600; font-style: italic;
            color: #fff;
            letter-spacing: .01em;
            white-space: nowrap;
        }
        .section-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, rgba(255,255,255,.12), transparent);
        }
        .section-desc {
            font-size: .74rem; color: var(--muted);
            margin-bottom: .9rem; line-height: 1.5;
        }

        /* ───── ÖNE ÇIKANLAR ───── */
        .featured-scroll {
            display: flex; gap: .7rem;
            overflow-x: auto; padding-bottom: .5rem;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            margin-bottom: .25rem;
        }
        .featured-scroll::-webkit-scrollbar { display: none; }
        .feat-card {
            flex-shrink: 0;
            width: clamp(138px, 37vw, 172px);
            background: var(--surface);
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            position: relative;
            transition: transform .22s, border-color .22s;
        }
        .feat-card:active { transform: scale(.96); border-color: var(--accent); }
        .feat-card .feat-img {
            width: 100%; height: clamp(88px, 22vw, 116px);
            object-fit: cover; display: block;
        }
        .feat-no-img {
            width: 100%; height: clamp(88px, 22vw, 116px);
            background: var(--surface2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
        }
        .feat-badge {
            position: absolute; top: .45rem; left: .45rem;
            background: var(--accent); color: #fff;
            font-size: .56rem; font-weight: 600;
            letter-spacing: .07em; text-transform: uppercase;
            padding: 2px 7px; border-radius: 50px;
        }
        .feat-body { padding: .55rem .65rem .7rem; }
        .feat-name {
            font-size: .8rem; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .feat-desc {
            font-size: .67rem; color: var(--muted); margin-top: .18rem;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .feat-price {
            font-family: var(--serif);
            font-size: .9rem; font-weight: 600; color: var(--accent);
            margin-top: .38rem;
        }

        /* ───── ÜRÜN GRİD ───── */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .65rem;
            padding-bottom: 1.35rem;
        }
        .items-grid > .item-card:only-child {
            grid-column: 1 / -1;
            max-width: 52%;
        }

        .item-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            display: flex; flex-direction: column;
            transition: transform .2s, border-color .2s;
            -webkit-tap-highlight-color: transparent;
        }
        .item-card:active {
            transform: scale(.96);
            border-color: var(--accent);
        }

        /* Görsel — 60% yükseklik oranı (kompakt) */
        .ic-img-wrap {
            position: relative;
            width: 100%;
            padding-top: 60%;
            background: var(--surface2);
            overflow: hidden;
        }
        .ic-img-wrap img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform .35s ease;
        }
        .item-card:hover .ic-img-wrap img { transform: scale(1.05); }
        .ic-no-img {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: var(--muted);
        }

        /* Oval geçiş */
        .ic-wave {
            position: absolute; bottom: -1px; left: 0; right: 0;
            height: 20px;
            background: var(--surface);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .ic-body {
            padding: .38rem .6rem .65rem;
            flex: 1; display: flex; flex-direction: column;
        }
        .ic-name {
            font-size: .81rem; font-weight: 600;
            color: #fff; line-height: 1.25;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .ic-desc {
            font-size: .68rem; color: var(--muted);
            margin-top: .18rem; line-height: 1.4;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            flex: 1;
        }
        .ic-badges {
            display: flex; gap: .2rem; flex-wrap: wrap;
            margin-top: .3rem;
        }
        .badge-pill {
            padding: 1px 6px; border-radius: 50px;
            font-size: .57rem; font-weight: 500;
            border: 1px solid; white-space: nowrap;
        }
        .ic-price {
            font-family: var(--serif);
            font-size: .92rem; font-weight: 600;
            color: var(--accent);
            margin-top: .4rem;
        }

        /* ───── AYRAÇ ───── */
        .section-divider {
            height: 1px;
            background: var(--border);
            margin: 0 .9rem;
        }

        /* ───── FOOTER ───── */
        .menu-footer {
            text-align: center;
            padding: 2rem 1rem 1.5rem;
            font-size: .62rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.1);
        }

        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body>

{{-- DİL ÇUBUĞU --}}
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

{{-- HEADER --}}
<div class="menu-header">
    <div class="hdr-bg"></div>
    <div class="hdr-overlay"></div>
    <div class="hdr-content">
        @if($menu->logo)
            <img src="{{ asset('storage/'.$menu->logo) }}" alt="" class="hdr-logo">
        @else
            <div class="hdr-logo-placeholder">{{ strtoupper(substr($menu->name,0,1)) }}</div>
        @endif
        <div>
            <div class="hdr-title">{{ $menu->getTitle($lang) }}</div>
            <div class="hdr-sub">Dijital Menü</div>
        </div>
    </div>
</div>

{{-- KATEGORİ ÇUBUĞU --}}
<div class="cat-bar" id="cat-bar">
    <div class="cat-tabs">
        @if($featured->count() > 0)
            <a href="#featured" class="cat-tab" data-id="featured">⭐ Öne Çıkanlar</a>
        @endif
        @foreach($categories as $cat)
            <a href="#cat-{{ $cat->id }}" class="cat-tab" data-id="cat-{{ $cat->id }}">
                @if($cat->icon) {{ $cat->icon }} @endif
                {{ $cat->getTitle($lang) }}
            </a>
        @endforeach
    </div>
</div>

{{-- ÖNE ÇIKANLAR --}}
@if($featured->count() > 0)
<div class="section" id="featured">
    <div class="section-header">
        <div class="section-title">⭐ Öne Çıkanlar</div>
        <div class="section-line"></div>
    </div>
    <div class="featured-scroll">
        @foreach($featured as $fitem)
        <div class="feat-card">
            <div class="feat-badge">Önerilen</div>
            @if($fitem->image)
                <img src="{{ asset('storage/'.$fitem->image) }}" alt="{{ $fitem->getTitle($lang) }}" class="feat-img">
            @else
                <div class="feat-no-img">🍽</div>
            @endif
            <div class="feat-body">
                <div class="feat-name">{{ $fitem->getTitle($lang) }}</div>
                @if($fitem->getDescription($lang))
                    <div class="feat-desc">{{ $fitem->getDescription($lang) }}</div>
                @endif
                @if($fitem->price)
                    <div class="feat-price">{{ $fitem->formattedPrice($menu->currency_symbol) }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="section-divider"></div>
@endif

{{-- KATEGORİLER --}}
@foreach($menu->categories as $category)
<div class="section" id="cat-{{ $category->id }}">
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
        <div class="item-card">
            <div class="ic-img-wrap">
                @if($item->image)
                    <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->getTitle($lang) }}">
                @else
                    <div class="ic-no-img">🍽</div>
                @endif
                <div class="ic-wave"></div>
            </div>
            <div class="ic-body">
                <div class="ic-name">{{ $item->getTitle($lang) }}</div>
                @if($item->getDescription($lang))
                    <div class="ic-desc">{{ $item->getDescription($lang) }}</div>
                @endif
                @if($item->badges)
                <div class="ic-badges">
                    @foreach(array_slice($item->badges, 0, 2) as $badge)
                    @php $bc = \App\Models\QrMenuItem::BADGE_COLORS[$badge] ?? '#6c757d'; @endphp
                    <span class="badge-pill"
                          style="color:{{ $bc }};border-color:{{ $bc }}30;background:{{ $bc }}14">
                        {{ $badge }}
                    </span>
                    @endforeach
                </div>
                @endif
                @if($item->price)
                <div class="ic-price">{{ $item->formattedPrice($menu->currency_symbol) }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@if(!$loop->last)
<div class="section-divider"></div>
@endif
@endforeach

{{-- FOOTER --}}
<div class="menu-footer">
    {{ $menu->getTitle($lang) }} &nbsp;·&nbsp; Dijital Menü
</div>

<script>
(function () {
    var catBar   = document.getElementById('cat-bar');
    var tabs     = document.querySelectorAll('.cat-tab[data-id]');
    var sections = document.querySelectorAll('.section[id]');

    // IntersectionObserver ile aktif tab
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var id  = entry.target.id;
            tabs.forEach(function (t) { t.classList.remove('active'); });
            var act = document.querySelector('.cat-tab[data-id="' + id + '"]');
            if (act) {
                act.classList.add('active');
                act.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        });
    }, { rootMargin: '-25% 0px -65% 0px', threshold: 0 });

    sections.forEach(function (s) { io.observe(s); });

    // Smooth scroll — offset için cat-bar yüksekliği
    tabs.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(a.dataset.id);
            if (!target) return;
            var offset = catBar.offsetHeight + 6;
            var top    = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });
        });
    });
})();
</script>
</body>
</html>
