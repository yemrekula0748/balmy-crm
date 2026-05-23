<!DOCTYPE html>
<html lang="tr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Arıza Analiz Raporu</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9.5pt;
    color: #1e293b;
    background: #fff;
    line-height: 1.55;
}

/* ═══ COVER HEADER ═══════════════════════════════════════════ */
.cover {
    background: #0f172a;
    padding: 0;
    margin-bottom: 0;
}
.cover-inner {
    padding: 32px 36px 24px;
}
.cover-badge {
    display: inline-block;
    background: rgba(99,102,241,.25);
    border: 1px solid rgba(165,180,252,.35);
    border-radius: 20px;
    padding: 3px 14px;
    font-size: 7pt;
    font-weight: bold;
    letter-spacing: .12em;
    color: #a5b4fc;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.cover-title {
    font-size: 20pt;
    font-weight: bold;
    color: #ffffff;
    letter-spacing: -.02em;
    margin-bottom: 6px;
    line-height: 1.15;
}
.cover-sub {
    font-size: 9pt;
    color: rgba(255,255,255,.55);
    margin-bottom: 18px;
    line-height: 1.6;
}
.cover-meta-row {
    border-top: 1px solid rgba(255,255,255,.1);
    padding-top: 14px;
    display: table;
    width: 100%;
}
.cover-meta-cell {
    display: table-cell;
    vertical-align: middle;
}
.cover-meta-item {
    display: inline-block;
    margin-right: 24px;
    font-size: 7.5pt;
    color: rgba(255,255,255,.55);
}
.cover-meta-item strong { color: rgba(255,255,255,.85); }

/* ═══ SUMMARY BAR ════════════════════════════════════════════ */
.summary-bar {
    background: #f8f9fb;
    border-bottom: 2px solid #e2e8f0;
    padding: 14px 36px;
    display: table;
    width: 100%;
}
.summary-chip {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    padding: 0 10px;
    border-right: 1px solid #e2e8f0;
}
.summary-chip:last-child { border-right: none; }
.sc-num { font-size: 18pt; font-weight: bold; line-height: 1; }
.sc-lbl { font-size: 6.5pt; font-weight: bold; letter-spacing: .07em; text-transform: uppercase; color: #64748b; margin-top: 2px; }

/* ═══ PAGE BODY ══════════════════════════════════════════════ */
.body-wrap { padding: 24px 36px; }

.narrative-box {
    border: 1px solid #e2e8f0;
    border-left: 4px solid #6366f1;
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 18px;
    page-break-inside: avoid;
}
.narrative-head {
    display: table;
    width: 100%;
    margin-bottom: 7px;
}
.narrative-title {
    display: table-cell;
    font-size: 9pt;
    font-weight: bold;
    color: #0f172a;
    vertical-align: middle;
}
.narrative-score {
    display: table-cell;
    width: 70px;
    text-align: right;
    font-size: 16pt;
    font-weight: bold;
    vertical-align: middle;
}
.narrative-text {
    font-size: 8.5pt;
    color: #334155;
    line-height: 1.6;
    margin-bottom: 8px;
}
.narrative-actions {
    margin: 6px 0 0 14px;
    padding: 0;
    font-size: 8pt;
    color: #475569;
    line-height: 1.5;
}

/* ═══ SECTION LABEL ══════════════════════════════════════════ */
.section-divider {
    margin: 22px 0 14px;
    display: table;
    width: 100%;
}
.section-label {
    display: inline-block;
    padding: 4px 14px 4px 10px;
    border-radius: 6px;
    font-size: 7pt;
    font-weight: bold;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.section-line {
    display: table-cell;
    vertical-align: middle;
    width: 100%;
}
.section-line-inner {
    height: 1px;
    background: #e2e8f0;
    margin-left: 10px;
}
.lbl-critical { background: #fef2f2; color: #dc2626; border-left: 3px solid #ef4444; }
.lbl-warning  { background: #fffbeb; color: #b45309; border-left: 3px solid #f59e0b; }
.lbl-info     { background: #eff6ff; color: #2563eb; border-left: 3px solid #3b82f6; }
.lbl-positive { background: #f0fdf4; color: #059669; border-left: 3px solid #10b981; }

/* ═══ INSIGHT CARD ═══════════════════════════════════════════ */
.card {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
    overflow: hidden;
    page-break-inside: avoid;
}
.card-header {
    padding: 10px 14px 8px;
    display: table;
    width: 100%;
}
.card-icon-col { display: table-cell; vertical-align: top; width: 32px; }
.card-icon {
    width: 28px; height: 28px; border-radius: 7px;
    text-align: center; line-height: 28px;
    font-size: 11pt; font-weight: bold;
    display: inline-block;
}
.card-text-col { display: table-cell; vertical-align: top; padding-left: 10px; }
.card-level-pill {
    display: inline-block;
    padding: 1px 8px;
    border-radius: 4px;
    font-size: 6pt;
    font-weight: bold;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.pill-critical { background: #fef2f2; color: #dc2626; }
.pill-warning  { background: #fffbeb; color: #b45309; }
.pill-info     { background: #eff6ff; color: #1d4ed8; }
.pill-positive { background: #f0fdf4; color: #059669; }

.card-title {
    font-size: 9.5pt;
    font-weight: bold;
    color: #111827;
    margin-bottom: 6px;
    line-height: 1.3;
}
.card-body {
    padding: 0 14px 10px 52px;
    font-size: 8.5pt;
    color: #374151;
    line-height: 1.7;
}
.card-body strong { color: #111827; font-weight: bold; }
.card-body em { font-style: italic; color: #4b5563; }

.card-footer {
    background: #f8f9fb;
    border-top: 1px solid #f1f5f9;
    padding: 6px 14px 6px 52px;
    display: table;
    width: 100%;
}
.card-tags {
    display: table-cell;
    vertical-align: middle;
}
.tag {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    padding: 1px 7px;
    border-radius: 4px;
    font-size: 6.5pt;
    font-weight: bold;
    margin-right: 4px;
}
.card-metric {
    display: table-cell;
    text-align: right;
    vertical-align: middle;
    white-space: nowrap;
}
.metric-val { font-size: 13pt; font-weight: bold; color: #1e293b; }
.metric-lbl { font-size: 6pt; color: #94a3b8; display: block; margin-top: -2px; text-align: right; }

/* Stripe by level */
.stripe-critical { border-left: 4px solid #ef4444; }
.stripe-warning  { border-left: 4px solid #f59e0b; }
.stripe-info     { border-left: 4px solid #3b82f6; }
.stripe-positive { border-left: 4px solid #10b981; }

/* icon bg by level */
.icon-critical { background: #fef2f2; color: #ef4444; }
.icon-warning  { background: #fffbeb; color: #d97706; }
.icon-info     { background: #eff6ff; color: #3b82f6; }
.icon-positive { background: #f0fdf4; color: #10b981; }

/* ═══ FAULT LINKS ════════════════════════════════════════════ */
.fault-chips { padding: 4px 14px 8px 52px; }
.fault-chip {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 2px 8px;
    font-size: 7pt;
    color: #374151;
    margin: 2px 3px 2px 0;
}
.fault-chip-id {
    display: inline-block;
    background: #e2e8f0;
    color: #6b7280;
    padding: 0 4px;
    border-radius: 3px;
    font-weight: bold;
    font-size: 6.5pt;
    margin-right: 3px;
}

/* ═══ FOOTER ═════════════════════════════════════════════════ */
.pdf-footer {
    margin-top: 30px;
    padding: 14px 36px;
    border-top: 1px solid #e2e8f0;
    background: #f8f9fb;
    font-size: 7.5pt;
    color: #94a3b8;
    display: table;
    width: 100%;
}
.footer-left  { display: table-cell; vertical-align: middle; }
.footer-right { display: table-cell; text-align: right; vertical-align: middle; }

/* Page break */
.page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ═══ KAPAK BAŞLIĞI ══════════════════════════════════════════════════ --}}
<div class="cover">
    <div class="cover-inner">
        <div class="cover-badge">&#9679; Balmy Hotels &nbsp;&middot;&nbsp; Arıza Zekası &nbsp;&middot;&nbsp; Yapay Zeka Analizi</div>
        <div class="cover-title">Operasyonel Arıza Analiz Raporu</div>
        <div class="cover-sub">
            Son 3 günlük arıza veritabanı üzerinde çok boyutlu desen analizi, SLA uyum kontrolü ve
            departman yük tespiti. Bu rapor otomatik olarak üretilmiş olup veri güdümlü bulgular içermektedir.
        </div>
        <div class="cover-meta-row">
            <div class="cover-meta-cell">
                <span class="cover-meta-item">
                    <strong>Dönem:</strong> {{ $windowStart }} – {{ $reportDate }}
                </span>
                <span class="cover-meta-item">
                    <strong>Analiz Tarihi:</strong> {{ $reportDate }}
                </span>
                <span class="cover-meta-item">
                    <strong>İncelenen Kayıt:</strong> {{ $totalFaults }} arıza
                </span>
                <span class="cover-meta-item">
                    <strong>Toplam Bulgu:</strong> {{ $findingCount }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ═══ ÖZET BAR ═══════════════════════════════════════════════════════ --}}
<div class="summary-bar">
    <div class="summary-chip">
        <div class="sc-num" style="color:#ef4444">{{ $criticalCount }}</div>
        <div class="sc-lbl">Kritik Bulgu</div>
    </div>
    <div class="summary-chip">
        <div class="sc-num" style="color:#d97706">{{ $warningCount }}</div>
        <div class="sc-lbl">Uyarı</div>
    </div>
    <div class="summary-chip">
        <div class="sc-num" style="color:#2563eb">{{ $infoCount }}</div>
        <div class="sc-lbl">Bilgi</div>
    </div>
    <div class="summary-chip">
        <div class="sc-num" style="color:#059669">{{ $positiveCount }}</div>
        <div class="sc-lbl">Olumlu</div>
    </div>
    <div class="summary-chip">
        <div class="sc-num" style="color:#6366f1">{{ $totalFaults }}</div>
        <div class="sc-lbl">Toplam Arıza</div>
    </div>
    <div class="summary-chip">
        <div class="sc-num" style="color:#0891b2">{{ $todayCount }}</div>
        <div class="sc-lbl">Bugün</div>
    </div>
</div>

<div class="body-wrap">

@if(!empty($narrative ?? null))
<div class="narrative-box" style="border-left-color: {{ $narrative['risk_color'] ?? '#6366f1' }}">
    <div class="narrative-head">
        <div class="narrative-title">
            Yönetici Özeti · {{ $narrative['risk_label'] ?? 'Analiz' }}
        </div>
        <div class="narrative-score" style="color: {{ $narrative['risk_color'] ?? '#6366f1' }}">
            {{ $narrative['risk_score'] ?? 0 }}
        </div>
    </div>
    <div class="narrative-text">{{ $narrative['summary'] ?? '' }}</div>
    @if(!empty($narrative['action_plan']))
    <ol class="narrative-actions">
        @foreach(array_slice($narrative['action_plan'], 0, 4) as $step)
        <li>{{ $step }}</li>
        @endforeach
    </ol>
    @endif
</div>
@endif

@php
$levelMeta = [
    'critical' => ['label'=>'KRİTİK',  'cls'=>'lbl-critical',  'pill'=>'pill-critical',  'stripe'=>'stripe-critical', 'icon_cls'=>'icon-critical'],
    'warning'  => ['label'=>'UYARI',   'cls'=>'lbl-warning',   'pill'=>'pill-warning',   'stripe'=>'stripe-warning',  'icon_cls'=>'icon-warning'],
    'info'     => ['label'=>'BİLGİ',   'cls'=>'lbl-info',      'pill'=>'pill-info',      'stripe'=>'stripe-info',     'icon_cls'=>'icon-info'],
    'positive' => ['label'=>'OLUMLU',  'cls'=>'lbl-positive',  'pill'=>'pill-positive',  'stripe'=>'stripe-positive', 'icon_cls'=>'icon-positive'],
];
@endphp

@foreach(['critical','warning','info','positive'] as $lvl)
@php $sectionInsights = collect($insights)->where('level', $lvl)->values(); @endphp
@if($sectionInsights->count() > 0)

{{-- Section Divider --}}
<div style="margin: 22px 0 14px;">
    <span class="section-label {{ $levelMeta[$lvl]['cls'] }}">
        {{ $levelMeta[$lvl]['label'] }} BULGULAR &nbsp;({{ $sectionInsights->count() }})
    </span>
</div>

@foreach($sectionInsights as $insight)
<div class="card {{ $levelMeta[$lvl]['stripe'] }}">
    <div class="card-header">
        <div class="card-icon-col">
            <div class="card-icon {{ $levelMeta[$lvl]['icon_cls'] }}">&#9670;</div>
        </div>
        <div class="card-text-col">
            <div class="card-level-pill {{ $levelMeta[$lvl]['pill'] }}">
                {{ $levelMeta[$lvl]['label'] }}
            </div>
            <div class="card-title">{{ $insight['title'] }}</div>
        </div>
    </div>
    <div class="card-body">
        {!! strip_tags($insight['body'], '<strong><em><b><i>') !!}
    </div>

    @if(!empty($insight['faults']) && $insight['faults']->count() > 0)
    <div class="fault-chips">
        @foreach($insight['faults']->take(8) as $f)
        <span class="fault-chip">
            <span class="fault-chip-id">#{{ $f->id }}</span>
            {{ Str::limit($f->faultType?->name ?? $f->title, 28) }}
            @if($f->faultArea) — {{ $f->faultArea->name }}@endif
            ·
            {{ \App\Models\Fault::STATUSES[$f->status] ?? $f->status }}
        </span>
        @endforeach
        @if($insight['faults']->count() > 8)
        <span class="fault-chip">+{{ $insight['faults']->count() - 8 }} daha...</span>
        @endif
    </div>
    @endif

    <div class="card-footer">
        <div class="card-tags">
            @foreach($insight['tags'] as $tag)
            <span class="tag">{{ $tag }}</span>
            @endforeach
        </div>
        @if(!empty($insight['metric']))
        <div class="card-metric">
            <span class="metric-val">{{ $insight['metric']['value'] }}</span>
            <span class="metric-lbl">{{ $insight['metric']['label'] }}</span>
        </div>
        @endif
    </div>
</div>
@endforeach

@endif
@endforeach

</div>

{{-- ═══ FOOTER ═════════════════════════════════════════════════════════ --}}
<div class="pdf-footer">
    <div class="footer-left">
        <strong>Balmy Hotels · Teknik Arıza Takip Sistemi</strong><br>
        Bu rapor otomatik veri analizi ile üretilmiştir. Tüm bulgular gerçek zamanlı veritabanı sorgusuna dayanmaktadır.
    </div>
    <div class="footer-right">
        Rapor Tarihi: {{ $reportDate }}<br>
        Gizlilik: Dahili Kullanım
    </div>
</div>

</body>
</html>
