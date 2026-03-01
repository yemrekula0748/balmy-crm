<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->getTitle($lang) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.97); border-radius: 24px;
            padding: 56px 48px; max-width: 440px; width: 100%;
            text-align: center; box-shadow: 0 32px 80px rgba(0,0,0,0.4);
        }
        .icon-circle {
            width: 100px; height: 100px;
            background: linear-gradient(135deg,#10b981,#059669);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 28px;
            box-shadow: 0 8px 32px rgba(16,185,129,0.35);
            animation: pop 0.5s cubic-bezier(0.175,0.885,0.32,1.275) both;
        }
        @keyframes pop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .icon-circle i { color: #fff; font-size: 44px; }
        h1 { font-size: 1.8rem; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; }
        p  { color: #6b7280; line-height: 1.6; font-size: 0.97rem; }
        .survey-name {
            display: inline-block; margin-top: 20px; padding: 10px 20px;
            background: #f0f3ff; border-radius: 20px; color: #4361ee;
            font-weight: 600; font-size: 0.9rem;
        }
        .confetti { font-size: 2rem; animation: fall 2s ease-in-out infinite alternate; }
        @keyframes fall { from { transform: translateY(-8px); } to { transform: translateY(4px); } }
    </style>
</head>
<body>
    <div class="card">
        <div style="margin-bottom: 12px" class="confetti">🎉</div>
        <div class="icon-circle"><i class="fas fa-check"></i></div>

        @php
        $messages = [
            'tr' => ['title' => 'Teşekkür Ederiz!', 'body' => 'Anketimizi doldurduğunuz için çok teşekkür ederiz. Görüşleriniz bizim için çok değerlidir.'],
            'en' => ['title' => 'Thank You!',        'body' => 'Thank you for completing our survey. Your feedback is very valuable to us.'],
            'de' => ['title' => 'Vielen Dank!',      'body' => 'Vielen Dank, dass Sie unsere Umfrage ausgefüllt haben. Ihre Meinung ist uns sehr wichtig.'],
            'ru' => ['title' => 'Спасибо!',          'body' => 'Спасибо, что прошли наш опрос. Ваши отзывы очень ценны для нас.'],
            'ar' => ['title' => 'شكراً لك!',         'body' => 'شكراً لك على إكمال استطلاعنا. ملاحظاتك قيّمة جداً بالنسبة لنا.'],
            'fr' => ['title' => 'Merci !',           'body' => 'Merci d\'avoir répondu à notre enquête. Vos retours sont très précieux pour nous.'],
        ];
        $msg = $messages[$lang] ?? $messages['tr'];
        @endphp

        <h1>{{ $msg['title'] }}</h1>
        <p>{{ $msg['body'] }}</p>
        <div class="survey-name">
            <i class="fas fa-poll me-1"></i>{{ $survey->getTitle($lang) ?: $survey->getTitle() }}
        </div>
    </div>
</body>
</html>
