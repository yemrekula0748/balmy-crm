<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $showcase->title }}</title>

    <meta name="theme-color" content="{{ $showcase->accent_color }}">
    <meta property="og:title" content="{{ $showcase->title }}">
    @if($showcase->subtitle)
    <meta property="og:description" content="{{ $showcase->subtitle }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:   {{ $showcase->accent_color }};
            --accent20: {{ $showcase->accent_color }}33;
            --bg:       #0b0b0b;
            --surface:  #141414;
            --card:     #1a1a1a;
            --border:   rgba(255,255,255,.07);
            --text:     #f0ece6;
            --muted:    #888;
        }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100dvh;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Hero ────────────────────────────────────────────── */
        .hero {
            position: relative;
            min-height: 52dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem 3rem;
            text-align: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 50% 0%, var(--accent20), transparent 70%),
                var(--bg);
        }

        .hero-grain {
            position: absolute;
            inset: 0;
            z-index: 1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.06'/%3E%3C/svg%3E");
            opacity: .5;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: var(--accent20);
            border: 1px solid var(--accent);
            border-radius: 99px;
            padding: .3rem 1rem;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 1.4rem;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 6vw, 3.8rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -.01em;
            margin-bottom: .9rem;
        }

        .hero-sub {
            color: var(--muted);
            font-size: clamp(.85rem, 2vw, 1.05rem);
            font-weight: 300;
            max-width: 560px;
            line-height: 1.7;
        }

        /* ── Grid ────────────────────────────────────────────── */
        .grid-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 5rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }

        /* ── Card ────────────────────────────────────────────── */
        .menu-card {
            position: relative;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform .25s cubic-bezier(.22,1,.36,1),
                        box-shadow .25s,
                        border-color .25s;
        }
        .menu-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 50px rgba(0,0,0,.5), 0 0 0 1px var(--accent);
            border-color: var(--accent);
        }

        /* Cover image */
        .card-cover {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
        .card-cover-placeholder {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            background: linear-gradient(135deg, #1e1e1e 0%, #282828 100%);
        }

        /* Overlay band on image */
        .card-img-wrap {
            position: relative;
        }
        .card-img-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 40%, rgba(0,0,0,.65) 100%);
        }

        /* Card body */
        .card-body {
            padding: 1.1rem 1.25rem 1.25rem;
        }

        .card-logo {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: .75rem;
            border: 2px solid var(--border);
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: .35rem;
        }

        .card-sub {
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.5;
        }

        .card-arrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .9rem;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--accent);
            transition: gap .2s;
        }
        .menu-card:hover .card-arrow { gap: .7rem; }

        /* ── Footer ──────────────────────────────────────────── */
        .page-footer {
            text-align: center;
            padding: 2rem 1rem 3rem;
            color: var(--muted);
            font-size: .75rem;
            border-top: 1px solid var(--border);
        }

        /* ── Scroll reveal animation ──────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .menu-card {
            animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both;
        }
        @for($i = 0; $i < 20; $i++)
        .menu-card:nth-child({{ $i + 1 }}) { animation-delay: {{ $i * 0.06 }}s; }
        @endfor
    </style>
</head>
<body>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-grain"></div>
        <div class="hero-content">
            <div class="hero-eyebrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Dijital Menüler
            </div>
            <h1 class="hero-title">{{ $showcase->title }}</h1>
            @if($showcase->subtitle)
            <p class="hero-sub">{{ $showcase->subtitle }}</p>
            @endif
        </div>
    </section>

    <!-- Menu Cards -->
    <div class="grid-wrapper">
        @if($showcase->items->isEmpty())
        <div style="text-align:center;color:var(--muted);padding:4rem 0">
            Bu vitrine henüz menü eklenmemiş.
        </div>
        @else
        <div class="menu-grid">
            @foreach($showcase->items as $item)
            @php
                $menu  = $item->menu;
                $label = $item->label ?: ($menu->getTitle('tr') ?? $menu->name);
                $url   = route('qrmenu.show', $menu->name);
            @endphp
            <a href="{{ $url }}" class="menu-card">
                {{-- Cover görsel --}}
                <div class="card-img-wrap">
                    @if($menu->cover_image)
                    <img src="{{ asset('uploads/'.$menu->cover_image) }}" alt="{{ $label }}" class="card-cover">
                    @else
                    <div class="card-cover-placeholder">🍽</div>
                    @endif
                </div>

                <div class="card-body">
                    {{-- Logo --}}
                    @if($menu->logo)
                    <img src="{{ asset('uploads/'.$menu->logo) }}" alt="" class="card-logo">
                    @endif

                    <div class="card-title">{{ $label }}</div>

                    @php
                        $catCount  = $menu->categories->count();
                        $langCodes = $menu->languages->pluck('flag')->implode(' ');
                    @endphp
                    <div class="card-sub">
                        @if($catCount) {{ $catCount }} kategori &nbsp;·&nbsp; @endif
                        {{ $langCodes }}
                    </div>

                    <div class="card-arrow">
                        Menüyü Gör
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <footer class="page-footer">
        Dijital menü sistemi &mdash; {{ now()->year }}
    </footer>

</body>
</html>
