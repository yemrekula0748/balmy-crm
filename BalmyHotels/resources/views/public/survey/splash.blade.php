<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->getTitle('tr') ?: $survey->getTitle() }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .splash-card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4);
        }
        .hotel-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg,#4361ee,#3a0ca3);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(67,97,238,0.35);
        }
        .hotel-icon i { color: #fff; font-size: 36px; }
        h1 { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .subtitle { color: #6b7280; font-size: 0.95rem; margin-bottom: 36px; line-height: 1.5; }
        .lang-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .lang-btn {
            display: flex; align-items: center; gap: 12px; padding: 16px 20px;
            border: 2px solid #e5e7eb; border-radius: 14px; background: #fff;
            cursor: pointer; text-decoration: none; color: #1a1a2e;
            transition: all 0.2s; font-size: 1rem; font-weight: 500;
        }
        .lang-btn:hover { border-color: #4361ee; background: #f0f3ff; color: #4361ee; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.15); }
        .lang-btn .flag { font-size: 1.8rem; }
        .lang-btn .name { text-align: left; }
        .lang-btn .name .sub { font-size: 0.75rem; color: #9ca3af; font-weight: 400; }
        .footer-note { font-size: 0.8rem; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="splash-card">
        <div class="hotel-icon"><i class="fas fa-poll"></i></div>
        <h1>{{ $survey->getTitle('tr') ?: $survey->getTitle() }}</h1>
        @php $desc = $survey->getDescription('tr') ?: $survey->getDescription(); @endphp
        @if($desc)
        <p class="subtitle">{{ $desc }}</p>
        @else
        <p class="subtitle">Lütfen anketi doldurmak istediğiniz dili seçin.<br>Please select your preferred language.</p>
        @endif

        <div class="lang-grid" @if(count($survey->languages) === 1) style="grid-template-columns:1fr" @endif>
            @foreach($survey->languages as $lang)
                @php $info = \App\Models\Survey::AVAILABLE_LANGUAGES[$lang] ?? ['name'=>strtoupper($lang),'flag'=>'🌐','dir'=>'ltr']; @endphp
                <a href="{{ route('surveys.public.form', [$survey->slug, $lang]) }}" class="lang-btn">
                    <span class="flag">{{ $info['flag'] }}</span>
                    <span class="name">
                        {{ $info['name'] }}
                        @if($survey->getTitle($lang) && $survey->getTitle($lang) !== $survey->getTitle('tr'))
                            <div class="sub">{{ Str::limit($survey->getTitle($lang), 30) }}</div>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>

        <p class="footer-note"><i class="fas fa-shield-alt me-1"></i>Yanıtlarınız gizli tutulur.</p>
    </div>
</body>
</html>
