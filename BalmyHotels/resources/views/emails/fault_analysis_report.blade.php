<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arıza Analiz Raporu</title>
    <style>
        body { margin:0; padding:0; background:#eef2f7; font-family:'Segoe UI',Tahoma,Arial,sans-serif; }
        table { border-collapse:collapse; }
        .outer  { background:#eef2f7; padding:32px 0; }
        .inner  { width:600px; margin:0 auto; background:#ffffff; border-radius:10px; overflow:hidden;
                  box-shadow:0 2px 16px rgba(0,0,0,.10); }
        .hdr         { background:linear-gradient(135deg,#0f172a 0%,#1e40af 100%); padding:0; }
        .hdr-inner   { padding:30px 36px 22px; }
        .hdr-logo    { font-size:11px; color:rgba(255,255,255,.55); letter-spacing:.8px;
                       text-transform:uppercase; margin-bottom:10px; }
        .hdr-title   { font-size:22px; font-weight:700; color:#ffffff; margin:0 0 5px; }
        .hdr-sub     { font-size:12px; color:rgba(255,255,255,.65); margin:0; }
        .meta-bar    { background:rgba(255,255,255,.08); border-top:1px solid rgba(255,255,255,.1);
                       padding:10px 36px; font-size:11px; color:rgba(255,255,255,.6); }
        .meta-bar strong { color:rgba(255,255,255,.85); }
        .body-pad    { padding:28px 36px 20px; }
        .sec-hdr     { font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
                       color:#94a3b8; border-bottom:2px solid #f1f5f9; padding-bottom:6px; margin:0 0 16px; }
        /* Summary chips */
        .chips-row   { display:table; width:100%; border:1px solid #e2e8f0;
                       border-radius:8px; overflow:hidden; margin-bottom:24px; }
        .chip        { display:table-cell; text-align:center; padding:14px 8px;
                       border-right:1px solid #e2e8f0; vertical-align:middle; }
        .chip:last-child { border-right:none; }
        .chip-num    { font-size:20px; font-weight:800; display:block; line-height:1; }
        .chip-lbl    { font-size:9px; font-weight:700; letter-spacing:.07em; text-transform:uppercase;
                       color:#64748b; display:block; margin-top:3px; }
        /* Insight card */
        .insight     { border:1px solid #e2e8f0; border-radius:8px; margin-bottom:12px;
                       overflow:hidden; }
        .ins-head    { padding:12px 14px 8px; }
        .ins-pill    { display:inline-block; padding:2px 9px; border-radius:4px;
                       font-size:8px; font-weight:800; letter-spacing:.08em; text-transform:uppercase;
                       margin-bottom:5px; }
        .ins-title   { font-size:13px; font-weight:700; color:#111827; line-height:1.3; margin:0; }
        .ins-body    { padding:0 14px 10px; font-size:12px; color:#374151; line-height:1.75; }
        .ins-body strong { color:#111827; }
        .ins-footer  { background:#f8f9fb; border-top:1px solid #f1f5f9;
                       padding:6px 14px; font-size:10px; color:#64748b; }
        .tag         { display:inline-block; background:#e2e8f0; color:#475569;
                       padding:1px 7px; border-radius:4px; font-size:9px; font-weight:600;
                       margin-right:4px; }

        .pill-critical { background:#fef2f2; color:#dc2626; }
        .pill-warning  { background:#fffbeb; color:#b45309; }
        .pill-info     { background:#eff6ff; color:#2563eb; }
        .pill-positive { background:#f0fdf4; color:#059669; }
        .border-critical { border-left:4px solid #ef4444; }
        .border-warning  { border-left:4px solid #f59e0b; }
        .border-info     { border-left:4px solid #3b82f6; }
        .border-positive { border-left:4px solid #10b981; }

        .attach-note { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px;
                       padding:14px 18px; margin-bottom:22px; font-size:12px; color:#1d4ed8; }
        .footer      { background:#f8f9fb; border-top:1px solid #e2e8f0; padding:16px 36px;
                       font-size:10px; color:#94a3b8; text-align:center; }
    </style>
</head>
<body>
<div class="outer">
<div class="inner">

    {{-- HEADER --}}
    <div class="hdr">
        <div class="hdr-inner">
            <div class="hdr-logo">Balmy Hotels &nbsp;&middot;&nbsp; Teknik Arıza Takip</div>
            <div class="hdr-title">🧠 Operasyonel Arıza Analiz Raporu</div>
            <div class="hdr-sub">Yapay Zeka Destekli &nbsp;&middot;&nbsp; Son 3 Günlük Desen Analizi</div>
        </div>
        <div class="meta-bar">
            <strong>Dönem:</strong> {{ $windowStart }} – {{ $reportDate }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Analiz Tarihi:</strong> {{ $reportDate }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Toplam Kayıt:</strong> {{ $totalFaults }} arıza
        </div>
    </div>

    <div class="body-pad">

        {{-- Ek notu --}}
        <div class="attach-note">
            📎 Raporun detaylı PDF versiyonu bu e-postaya ek olarak eklenmiştir.
            PDF üzerinde arıza bazlı bağlantılar, metrikler ve tüm analiz bulguları yer almaktadır.
        </div>

        {{-- Summary Chips --}}
        <div class="sec-hdr">Özet</div>
        <div class="chips-row">
            <div class="chip">
                <span class="chip-num" style="color:#ef4444">{{ $criticalCount }}</span>
                <span class="chip-lbl">Kritik</span>
            </div>
            <div class="chip">
                <span class="chip-num" style="color:#d97706">{{ $warningCount }}</span>
                <span class="chip-lbl">Uyarı</span>
            </div>
            <div class="chip">
                <span class="chip-num" style="color:#2563eb">{{ $infoCount }}</span>
                <span class="chip-lbl">Bilgi</span>
            </div>
            <div class="chip">
                <span class="chip-num" style="color:#059669">{{ $positiveCount }}</span>
                <span class="chip-lbl">Olumlu</span>
            </div>
            <div class="chip">
                <span class="chip-num" style="color:#6366f1">{{ $totalFaults }}</span>
                <span class="chip-lbl">Arıza</span>
            </div>
        </div>

        {{-- Insight Cards (ilk 8) --}}
        @php
        $levelMeta = [
            'critical' => ['pill'=>'pill-critical','border'=>'border-critical','label'=>'KRİTİK'],
            'warning'  => ['pill'=>'pill-warning', 'border'=>'border-warning', 'label'=>'UYARI'],
            'info'     => ['pill'=>'pill-info',    'border'=>'border-info',    'label'=>'BİLGİ'],
            'positive' => ['pill'=>'pill-positive','border'=>'border-positive','label'=>'OLUMLU'],
        ];
        $displayed = 0;
        @endphp
        <div class="sec-hdr" style="margin-top:8px">Analiz Bulguları</div>
        @foreach($insights as $insight)
        @if($displayed < 8)
        @php $meta = $levelMeta[$insight['level']] ?? $levelMeta['info']; $displayed++; @endphp
        <div class="insight {{ $meta['border'] }}">
            <div class="ins-head">
                <div class="ins-pill {{ $meta['pill'] }}">{{ $meta['label'] }}</div>
                <p class="ins-title">{{ $insight['title'] }}</p>
            </div>
            <div class="ins-body">{!! strip_tags($insight['body'], '<strong><em><b><i>') !!}</div>
            <div class="ins-footer">
                @foreach($insight['tags'] as $tag)
                <span class="tag">{{ $tag }}</span>
                @endforeach
                @if(!empty($insight['metric']))
                &nbsp;&nbsp;<strong>{{ $insight['metric']['value'] }}</strong> {{ $insight['metric']['label'] }}
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @if(count($insights) > 8)
        <p style="font-size:11px;color:#64748b;text-align:center;margin-top:8px">
            + {{ count($insights) - 8 }} daha fazla bulgu — tüm analiz için ekteki PDF'i inceleyiniz.
        </p>
        @endif

    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Balmy Hotels &nbsp;&middot;&nbsp; Teknik Arıza Takip Sistemi<br>
        Bu e-posta otomatik veri analizi sonucu üretilmiştir. &nbsp;|&nbsp; Gizlilik: Dahili Kullanım
    </div>

</div>
</div>
</body>
</html>
