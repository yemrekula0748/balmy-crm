<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $menu->getTitle() }}</title>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7HHCB1JYV7"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-7HHCB1JYV7');
    </script>
    <meta name="theme-color" content="{{ $menu->theme_color ?? '#1a1a2e' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: {{ $menu->theme_color ?? '#c19b77' }};
            --bg: #0d0d0d;
            --surface: #181818;
            --text: #f0ece6;
            --muted: #888;
        }

        body {
            min-height: 100dvh;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Arka plan kapak görseli */
        .bg-cover {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: center;
            @if($menu->cover_image)
            background-image: url('{{ asset('uploads/'.$menu->cover_image) }}');
            @endif
            filter: brightness(.35) saturate(.8);
        }

        .bg-grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.08'/%3E%3C/svg%3E");
            opacity: .4;
            pointer-events: none;
        }

        /* İçerik */
        .splash-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        /* Logo */
        .logo-wrap {
            margin-bottom: 2rem;
        }
        .logo-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
            box-shadow: 0 0 32px color-mix(in srgb, var(--accent) 40%, transparent);
        }
        .logo-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #fff;
            margin: 0 auto;
            box-shadow: 0 0 32px color-mix(in srgb, var(--accent) 40%, transparent);
        }

        /* Başlık */
        .menu-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #fff;
            margin-bottom: .4rem;
        }
        .menu-subtitle {
            font-size: .85rem;
            color: var(--muted);
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 2.5rem;
        }

        /* Ayırıcı çizgi */
        .divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            margin: 0 auto 2.5rem;
        }

        /* Dil seçim butonları */
        .lang-label {
            display: block;
            font-size: .75rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1rem;
        }

        .lang-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all .25s ease;
            text-decoration: none;
            color: var(--text);
            backdrop-filter: blur(8px);
        }
        .lang-btn:hover {
            background: rgba(255,255,255,.1);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.3);
            color: #fff;
        }
        .lang-btn .flag {
            font-size: 1.8rem;
            line-height: 1;
        }
        .lang-btn .lang-name {
            font-weight: 500;
            flex: 1;
            text-align: left;
        }
        .lang-btn .lang-code {
            font-size: .75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .1em;
        }
        .lang-btn .arrow {
            color: var(--accent);
            font-size: 1rem;
            opacity: .6;
        }
        .lang-btn:hover .arrow { opacity: 1; transform: translateX(3px); }

        /* Alt not */
        .footer-note {
            position: fixed;
            bottom: 1.5rem;
            width: 100%;
            text-align: center;
            z-index: 10;
            font-size: .72rem;
            color: rgba(255,255,255,.2);
            letter-spacing: .06em;
        }
    </style>
</head>
<body>
    <div class="bg-cover"></div>
    <div class="bg-grain"></div>

    <div class="splash-card">
        <div class="logo-wrap">
            @if($menu->logo)
                <img src="{{ asset('uploads/'.$menu->logo) }}" alt="logo" class="logo-img">
            @else
                <div class="logo-placeholder">{{ strtoupper(substr($menu->name, 0, 1)) }}</div>
            @endif
        </div>

        <h1 class="menu-title">{{ $menu->getTitle() }}</h1>
        <p class="menu-subtitle">Dijital Menü</p>
        <div class="divider"></div>

        <p class="lang-label">Lütfen dilinizi seçin &mdash; Please select your language</p>

        <div class="lang-list">
            @foreach($menu->languages as $lang)
            <a href="{{ route('qrmenu.view', [$menu->name, $lang->code]) }}" class="lang-btn">
                <span class="flag">{{ $lang->flag }}</span>
                <span class="lang-name">{{ $lang->name }}</span>
                <span class="lang-code">{{ $lang->code }}</span>
                <span class="arrow">›</span>
            </a>
            @endforeach
        </div>
    </div>

    <div class="footer-note">Balmy Hotels · Digital Menu</div>
</body>
</html>
