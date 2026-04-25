<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Arıza Bildirimi</title>
    <style>
        body    { margin:0; padding:0; background:#eef2f7; font-family:'Segoe UI',Tahoma,Arial,sans-serif; }
        table   { border-collapse:collapse; }
        img     { border:0; display:block; }
        a       { color:#c0392b; }

        /* Wrapper */
        .outer  { background:#eef2f7; padding:32px 0; }
        .inner  { width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;
                  box-shadow:0 2px 16px rgba(0,0,0,.10); }

        /* Header band */
        .hdr         { background:#b91c1c; padding:0; }
        .hdr-inner   { padding:28px 36px 22px; }
        .hdr-logo    { font-size:13px; color:rgba(255,255,255,.70); letter-spacing:.6px;
                       text-transform:uppercase; margin-bottom:10px; }
        .hdr-title   { font-size:22px; font-weight:700; color:#ffffff; margin:0 0 4px; }
        .hdr-sub     { font-size:13px; color:rgba(255,255,255,.78); margin:0; }

        /* Accent stripe (priority) */
        .stripe      { font-size:12px; color:#ffffff; padding:9px 36px; letter-spacing:.3px; }
        .stripe-crit { background:#7f1d1d; }
        .stripe-high { background:#9a3412; }
        .stripe-med  { background:#92400e; }
        .stripe-low  { background:#14532d; }

        /* Body padding */
        .body-pad    { padding:28px 36px 20px; }

        /* Section header */
        .sec-hdr     { font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
                       color:#94a3b8; border-bottom:2px solid #f1f5f9; padding-bottom:6px;
                       margin:0 0 14px; }

        /* Info table */
        .info-tbl    { width:100%; border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;
                       margin-bottom:22px; font-size:13px; }
        .info-tbl tr:nth-child(odd)  td { background:#f8fafc; }
        .info-tbl tr:nth-child(even) td { background:#ffffff; }
        .info-tbl td { padding:10px 14px; vertical-align:top; border-bottom:1px solid #e2e8f0; }
        .info-tbl tr:last-child td   { border-bottom:0; }
        .info-key    { font-weight:700; color:#475569; width:38%; }
        .info-val    { color:#1e293b; }

        /* Description box */
        .desc-box    { background:#fafafa; border-left:4px solid #dc2626; border-radius:4px;
                       padding:14px 16px; margin-bottom:22px; }
        .desc-label  { font-size:10px; font-weight:700; letter-spacing:.7px; text-transform:uppercase;
                       color:#94a3b8; margin-bottom:6px; }
        .desc-text   { font-size:13.5px; color:#374151; line-height:1.7; margin:0; }

        /* Priority badge */
        .badge-crit  { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-high  { background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-med   { background:#fffbeb; color:#d97706; border:1px solid #fde68a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-low   { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-open  { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

        /* CTA button */
        .cta-wrap    { text-align:center; padding:6px 0 18px; }
        .cta-btn     { background:#b91c1c; color:#ffffff !important; text-decoration:none;
                       padding:13px 36px; border-radius:6px; font-size:14px; font-weight:700;
                       display:inline-block; letter-spacing:.3px; }

        /* Photo */
        .photo-wrap  { text-align:center; margin-bottom:22px; }
        .photo-img   { max-width:100%; max-height:280px; border-radius:6px;
                       border:1px solid #e2e8f0; object-fit:cover; }

        /* Footer */
        .footer      { background:#f8fafc; padding:16px 36px; text-align:center;
                       font-size:11px; color:#94a3b8; border-top:1px solid #e2e8f0; }
    </style>
</head>
<body>
@php
    $pData = [
        'critical' => ['label'=>'Kritik',  'stripe'=>'stripe-crit','badge'=>'badge-crit'],
        'high'     => ['label'=>'Acil',    'stripe'=>'stripe-high','badge'=>'badge-high'],
        'medium'   => ['label'=>'Orta',    'stripe'=>'stripe-med', 'badge'=>'badge-med'],
        'low'      => ['label'=>'Normal',  'stripe'=>'stripe-low', 'badge'=>'badge-low'],
    ];
    $pd = $pData[$fault->priority] ?? $pData['medium'];
@endphp
<div class="outer">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<table class="inner" width="600" cellpadding="0" cellspacing="0">

    {{-- ── HEADER ── --}}
    <tr><td class="hdr">
        <div class="hdr-inner">
            <div class="hdr-logo">🔧 &nbsp;{{ config('app.name') }}</div>
            <div class="hdr-title">Yeni Arıza Bildirimi</div>
            <div class="hdr-sub">Teknik Arıza Takip Sistemi &nbsp;·&nbsp; Bildirim #{{ $fault->id }}</div>
        </div>
    </td></tr>

    {{-- ── PRIORITY STRIPE ── --}}
    <tr><td class="stripe {{ $pd['stripe'] }}">
        <strong>Öncelik:</strong>&nbsp;
        <span class="{{ $pd['badge'] }}">{{ $pd['label'] }}</span>
        &nbsp;&nbsp;|
        &nbsp;<strong>Durum:</strong>&nbsp;<span class="badge-open">Açık</span>
        &nbsp;&nbsp;|
        &nbsp;<strong>Tarih:</strong>&nbsp;{{ $fault->created_at->format('d.m.Y H:i') }}
    </td></tr>

    {{-- ── BODY ── --}}
    <tr><td>
    <div class="body-pad">

        <p style="font-size:14px;color:#374151;margin:0 0 20px">
            Merhaba,<br><br>
            <strong>{{ $fault->department?->name }}</strong> departmanınıza
            <strong>{{ $fault->branch?->name }}</strong> şubesinden yeni bir arıza bildirimi iletildi.
            Lütfen en kısa sürede inceleyip gerekli müdahaleyi sağlayınız.
        </p>

        {{-- Arıza Detayları tablosu --}}
        <div class="sec-hdr">Arıza Detayları</div>
        <table class="info-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="info-key">Bildirim No</td>
                <td class="info-val">#{{ $fault->id }}</td>
            </tr>
            <tr>
                <td class="info-key">Arıza Türü</td>
                <td class="info-val">{{ $fault->faultType?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-key">Bölüm / Şube</td>
                <td class="info-val">{{ $fault->branch?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-key">Sorumlu Departman</td>
                <td class="info-val">{{ $fault->department?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-key">Konum</td>
                <td class="info-val">{{ $fault->faultLocation?->name ?? '—' }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}</td>
            </tr>
            <tr>
                <td class="info-key">Öncelik</td>
                <td class="info-val"><span class="{{ $pd['badge'] }}">{{ $pd['label'] }}</span></td>
            </tr>
            <tr>
                <td class="info-key">Bildiren Kişi</td>
                <td class="info-val">{{ $fault->reporter?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-key">Bildirim Tarihi</td>
                <td class="info-val">{{ $fault->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            @if($fault->faultType?->completion_hours)
            <tr>
                <td class="info-key">Hedef Çözüm Süresi</td>
                <td class="info-val">{{ $fault->faultType->completion_hours }} saat</td>
            </tr>
            @endif
        </table>

        {{-- Açıklama --}}
        @if($fault->description)
        <div class="sec-hdr">Arıza Açıklaması</div>
        <div class="desc-box">
            <p class="desc-text">{{ $fault->description }}</p>
        </div>
        @endif

        {{-- Fotoğraf --}}
        @if($fault->image_path)
        <div class="sec-hdr">Arıza Fotoğrafı</div>
        <div class="photo-wrap">
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($fault->image_path) }}" target="_blank">
                <img class="photo-img"
                     src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($fault->image_path) }}"
                     alt="Arıza fotoğrafı">
            </a>
        </div>
        @endif

        {{-- CTA --}}
        <div class="cta-wrap">
            <a class="cta-btn" href="{{ route('faults.show', $fault) }}">Arızayı Sisteme Git →</a>
        </div>

        <p style="font-size:11px;color:#94a3b8;text-align:center;margin:0">
            Bu e-posta, {{ config('app.name') }} sistemi tarafından otomatik olarak gönderilmiştir.
        </p>

    </div>
    </td></tr>

    {{-- ── FOOTER ── --}}
    <tr><td>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
            &nbsp;·&nbsp; Teknik Arıza Takip Sistemi
            &nbsp;·&nbsp; Tüm hakları saklıdır.
        </div>
    </td></tr>

</table>
</td></tr></table>
</div>
</body>
</html>
