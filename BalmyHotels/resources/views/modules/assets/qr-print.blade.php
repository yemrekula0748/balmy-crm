<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR — {{ $asset->asset_code }}</title>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #fff;
    display: flex;
    justify-content: center;
    padding: 16px;
}
.label-wrap {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
/* Single 80mm label */
.label {
    width: 80mm;
    padding: 6mm 5mm;
    border: 1px dashed #ccc;
    border-radius: 4mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3mm;
    page-break-inside: avoid;
    background: #fff;
}
.label-logo {
    font-size: 10pt;
    font-weight: 700;
    color: #c19b77;
    letter-spacing: .03em;
    text-align: center;
}
.label-code {
    font-family: 'Courier New', monospace;
    font-size: 11pt;
    font-weight: 700;
    color: #1f2937;
    letter-spacing: .06em;
}
.label-name {
    font-size: 9pt;
    color: #374151;
    text-align: center;
    max-width: 68mm;
    line-height: 1.3;
}
.label-qr {
    margin: 1mm 0;
}
.label-qr canvas { display: block; }
.label-meta {
    font-size: 7pt;
    color: #6b7280;
    text-align: center;
    line-height: 1.5;
}
.label-meta strong { color: #374151; }
.label-url {
    font-size: 6pt;
    color: #9ca3af;
    text-align: center;
    word-break: break-all;
    max-width: 70mm;
}
.divider {
    width: 100%;
    border: none;
    border-top: 0.5px dashed #d1d5db;
}

/* Print overrides */
@media print {
    body { padding: 0; background: #fff; }
    .no-print { display: none !important; }
    .label { border-color: transparent; }
}
</style>
</head>
<body>

<div class="label-wrap">

    <div class="no-print" style="width:80mm;display:flex;gap:8px;justify-content:flex-end;margin-bottom:8px">
        <button onclick="window.print()" style="padding:6px 14px;background:#c19b77;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px">
            🖨 Yazdır
        </button>
        <button onclick="window.close()" style="padding:6px 14px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;font-size:12px">
            ✕ Kapat
        </button>
    </div>

    <div class="label">
        <div class="label-logo">BALMY HOTELS</div>

        <hr class="divider">

        <div class="label-code">{{ $asset->asset_code }}</div>
        <div class="label-name">{{ $asset->name }}</div>

        <div class="label-qr">
            <canvas id="qrCanvas"></canvas>
        </div>

        <div class="label-meta">
            @if($asset->category)
                <strong>{{ $asset->category->name }}</strong><br>
            @endif
            @if($asset->location)
                {{ $asset->location }}<br>
            @endif
            @if($asset->branch)
                {{ $asset->branch->name }}
            @endif
        </div>

        <hr class="divider">

        <div class="label-url">{{ $asset->publicQrUrl() }}</div>
    </div>

</div>

<script>
QRCode.toCanvas(document.getElementById('qrCanvas'), '{{ $asset->publicQrUrl() }}', {
    width: 160,
    margin: 1,
    color: { dark: '#1f2937', light: '#ffffff' },
    errorCorrectionLevel: 'M'
}, function(err) { if (err) console.error(err); });
</script>
</body>
</html>
