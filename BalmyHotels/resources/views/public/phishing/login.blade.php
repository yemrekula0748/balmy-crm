<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hediye Kampanyası</title>
    <style>
        *{box-sizing:border-box}html,body{min-height:100%;margin:0}
        body{font-family:"Segoe UI",Arial,sans-serif;color:#1f2937;background:linear-gradient(135deg,#edf2f7 0%,#f8fafc 48%,#e2e8f0 100%)}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 16px}
        .panel{width:100%;max-width:440px;background:#fff;border:1px solid #dfe5ec;border-radius:8px;box-shadow:0 18px 50px rgba(15,23,42,.14);padding:42px 44px 34px}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:34px;color:#16385f;font-size:18px;font-weight:650}
        .brand-mark{position:relative;width:42px;height:42px;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:3px;transform:rotate(-3deg)}
        .brand-tile{display:block;border-radius:6px}.brand-tile:nth-child(1){background:#e85d4a}.brand-tile:nth-child(2){background:#e3aa2d}.brand-tile:nth-child(3){background:#168f88}.brand-tile:nth-child(4){background:#4a67b0}
        .brand-mark:before,.brand-mark:after{content:"";position:absolute;z-index:2;background:#fff;border-radius:2px}.brand-mark:before{width:4px;height:46px;left:19px;top:-2px}.brand-mark:after{width:46px;height:4px;left:-2px;top:19px}
        h1{font-size:25px;line-height:1.25;margin:0 0 12px;font-weight:650;color:#111827}
        .lead{font-size:14px;line-height:1.55;color:#5b6574;margin:0 0 28px}
        label{display:block;font-size:13px;font-weight:600;margin-bottom:7px;color:#374151}
        input{width:100%;height:46px;border:1px solid #9ca3af;border-radius:4px;padding:0 12px;font-size:15px;outline:none;background:#fff;color:#111827}
        input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.13)}
        .actions{display:flex;justify-content:flex-end;margin-top:26px}
        button{height:42px;min-width:112px;border:0;border-radius:4px;padding:0 20px;background:#1d4f91;color:#fff;font-size:14px;font-weight:600;cursor:pointer}
        button:hover{background:#173f74}button:focus{outline:3px solid rgba(29,79,145,.25);outline-offset:2px}
        .account-line{display:flex;align-items:center;gap:8px;margin:-4px 0 24px;color:#4b5563;font-size:14px}
        .back{border:0;background:transparent;color:#374151;min-width:auto;height:auto;padding:4px;font-size:18px}
        .back:hover{background:transparent;color:#111827}
        .help{margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.55;color:#7a8491}
        .error{display:none;color:#b91c1c;font-size:12px;margin-top:7px}
        .step[hidden]{display:none}
        @media(max-width:520px){.page{align-items:flex-start;padding:0}.panel{min-height:100vh;max-width:none;border:0;border-radius:0;box-shadow:none;padding:34px 24px}}
    </style>
</head>
<body>
<main class="page">
    <section class="panel" aria-labelledby="pageTitle">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <span class="brand-tile"></span><span class="brand-tile"></span>
                <span class="brand-tile"></span><span class="brand-tile"></span>
            </span>
            Mail Giriş
        </div>

        <div class="step" id="emailStep">
            <h1 id="pageTitle">Mail Şifrenizin Süresi Doldu</h1>
            <p class="lead">Hesap güvenliği bildirimini görüntülemek için kurumsal e-posta adresinizle devam edin.</p>
            <label for="emailInput">E-posta adresi</label>
            {{-- Bilerek name niteliği yoktur: değer hiçbir isteğe eklenmez. --}}
            <input id="emailInput" type="email" inputmode="email" autocomplete="off" spellcheck="false" required>
            <div class="error" id="emailError">Geçerli bir e-posta adresi yazın.</div>
            <div class="actions"><button type="button" id="nextButton">İleri</button></div>
        </div>

        <div class="step" id="passwordStep" hidden>
            <div class="account-line">
                <button class="back" type="button" id="backButton" aria-label="Geri">←</button>
                <span id="emailPreview"></span>
            </div>
            <h1>Parolanızı girin</h1>
            <p class="lead">Güvenlik doğrulamasını tamamlamak için parolanızla devam edin.</p>
            <form method="POST" action="{{ route('phishing-simulation.attempt', $target->token) }}" id="attemptForm">
                @csrf
                <label for="passwordInput">Parola</label>
                {{-- Bilerek name niteliği yoktur ve gönderimden önce temizlenir. --}}
                <input id="passwordInput" type="password" autocomplete="new-password" required>
                <div class="error" id="passwordError">Devam etmek için bir değer yazın.</div>
                <div class="actions"><button type="submit">Oturum aç</button></div>
            </form>
        </div>

        <div class="help">Bu bağlantı kurumsal güvenlik kontrolleri kapsamında oluşturulmuştur.</div>
    </section>
</main>

<script>
(function () {
    'use strict';
    const emailStep = document.getElementById('emailStep');
    const passwordStep = document.getElementById('passwordStep');
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // Sayfanın gerçek bir tarayıcıda çalıştığını doğrulayan, içeriksiz ölçüm çağrısı.
    fetch(@json(route('phishing-simulation.opened', $target->token)), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
    }).catch(function () {});

    document.getElementById('nextButton').addEventListener('click', function () {
        if (!emailInput.validity.valid || !emailInput.value.trim()) {
            emailError.style.display = 'block';
            emailInput.focus();
            return;
        }
        emailError.style.display = 'none';
        document.getElementById('emailPreview').textContent = emailInput.value.trim();
        emailStep.hidden = true;
        passwordStep.hidden = false;
        passwordInput.focus();
    });

    document.getElementById('backButton').addEventListener('click', function () {
        passwordInput.value = '';
        passwordStep.hidden = true;
        emailStep.hidden = false;
        emailInput.focus();
    });

    document.getElementById('attemptForm').addEventListener('submit', function (event) {
        if (!passwordInput.value) {
            event.preventDefault();
            passwordError.style.display = 'block';
            passwordInput.focus();
            return;
        }
        passwordError.style.display = 'none';
        // Savunma katmanı: alanlarda name yoktur; ayrıca değerler gönderimden önce silinir.
        emailInput.value = '';
        passwordInput.value = '';
    });
})();
</script>
</body>
</html>
