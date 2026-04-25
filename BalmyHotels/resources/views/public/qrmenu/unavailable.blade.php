<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $menu->getTitle() }}</title>
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

        .bg-cover {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: center;
            @if($menu->cover_image)
            background-image: url('{{ asset('uploads/'.$menu->cover_image) }}');
            @endif
            filter: brightness(.25) saturate(.6);
        }

        .card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 380px;
            padding: 3rem 2rem;
            text-align: center;
        }

        .logo-wrap { margin-bottom: 2rem; }
        .logo-img {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
        }
        .logo-letter {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: .75rem;
        }

        .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .7;
        }

        p {
            color: var(--muted);
            font-size: .95rem;
            line-height: 1.6;
        }

        .divider {
            width: 40px;
            height: 2px;
            background: var(--accent);
            margin: 1.5rem auto;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<div class="bg-cover"></div>

<div class="card">
    <div class="logo-wrap">
        @if($menu->logo)
            <img src="{{ asset('uploads/'.$menu->logo) }}" class="logo-img" alt="{{ $menu->getTitle() }}">
        @else
            <span class="logo-letter">{{ mb_strtoupper(mb_substr($menu->name, 0, 1)) }}</span>
        @endif
    </div>

    <h1>{{ $menu->getTitle() }}</h1>
    <div class="divider"></div>

    <div class="icon">🔒</div>
    <p>Bu menü şu anda erişime kapalı.<br>Lütfen daha sonra tekrar deneyin.</p>
</div>
</body>
</html>
