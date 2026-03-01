<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teşekkürler – {{ $survey->getTitle($lang) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .ty-card {
            max-width: 480px;
            width: 100%;
            margin: 24px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 52px 40px;
            text-align: center;
        }

        .ty-icon {
            font-size: 72px;
            margin-bottom: 16px;
            animation: bounceIn .6s ease;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .ty-card h2 {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 12px;
        }

        .ty-card p {
            color: rgba(255,255,255,.6);
            font-size: 15px;
            line-height: 1.6;
        }

        img.logo {
            height: 36px;
            filter: brightness(0) invert(1);
            opacity: .6;
            margin-top: 32px;
        }
    </style>
</head>
<body>
<div class="ty-card">
    <div class="ty-icon">🎉</div>
    <h2>Teşekkürler!</h2>
    <p>
        Cevaplarınız başarıyla kaydedildi.<br>
        Değerli geri bildiriminiz için teşekkür ederiz.
    </p>
    <br>
    <p style="font-size:13px;">
        <strong style="color:rgba(255,255,255,.8)">{{ $survey->getTitle($lang) }}</strong>
    </p>
    <img src="{{ asset('images/logo.svg') }}" alt="" class="logo">
</div>
</body>
</html>
