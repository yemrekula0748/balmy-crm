@extends('layouts.default')

@section('title', 'Yapay Zeka Analizi — Teknik Arıza')

@push('styles')
<style>
/* ═══════ GENEL ══════════════════════════════════════════════════════════ */
.ai-page { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

/* ═══════ HERO ═══════════════════════════════════════════════════════════ */
.ai-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 45%, #1e40af 100%);
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,.06);
    overflow: hidden;
    position: relative;
    margin-bottom: 26px;
}
.ai-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 60% at 90% 10%, rgba(99,102,241,.25) 0%, transparent 60%),
        radial-gradient(ellipse 40% 40% at 10% 90%, rgba(16,185,129,.12) 0%, transparent 60%);
    pointer-events: none;
}
.ai-hero-inner { position: relative; padding: 32px 36px; }
.ai-hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(99,102,241,.2); border: 1px solid rgba(99,102,241,.4);
    border-radius: 20px; padding: 4px 14px;
    font-size: .72rem; font-weight: 700; letter-spacing: .08em;
    color: #a5b4fc; text-transform: uppercase; margin-bottom: 14px;
}
.ai-hero-title {
    font-size: 1.7rem; font-weight: 800; color: #fff;
    letter-spacing: -.02em; margin: 0 0 10px; line-height: 1.2;
}
.ai-hero-sub {
    font-size: .9rem; color: rgba(255,255,255,.6);
    max-width: 700px; margin: 0 0 22px; line-height: 1.6;
}
.ai-hero-chips { display: flex; flex-wrap: wrap; gap: 10px; }
.ai-hero-chip {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px; padding: 7px 14px;
    font-size: .78rem; color: rgba(255,255,255,.75); font-weight: 500;
}
.ai-hero-chip i { font-size: .7rem; opacity: .8; }

/* ═══════ SUMMARY CHIPS ══════════════════════════════════════════════════ */
.ai-summary { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 28px; }
.ai-summary-chip {
    flex: 1; min-width: 160px;
    background: #fff; border-radius: 14px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
}
.sch-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.sch-num  { font-size: 1.55rem; font-weight: 800; line-height: 1; }
.sch-lbl  { font-size: .74rem; color: #64748b; margin-top: 3px; font-weight: 500; }
.sch-sub  { font-size: .68rem; color: #94a3b8; margin-top: 1px; }

/* AI executive brief */
.ai-brief {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 12px 34px rgba(15,23,42,.07);
    padding: 22px;
    margin: -8px 0 26px;
}
.ai-brief::before {
    content: '';
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, var(--risk-color, #6366f1), rgba(99,102,241,.3));
}
.ai-brief-grid {
    display: grid;
    grid-template-columns: minmax(210px,.86fr) minmax(260px,1.12fr) minmax(260px,1.18fr);
    gap: 18px;
    position: relative;
}
.ai-risk-panel {
    background: #0f172a;
    border-radius: 15px;
    padding: 18px;
    color: #fff;
    min-height: 100%;
}
.ai-risk-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
.ai-risk-score {
    width: 78px;
    height: 78px;
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 12px 28px rgba(15,23,42,.22);
    background: color-mix(in srgb, var(--risk-color, #6366f1) 24%, #111827);
    border: 1px solid color-mix(in srgb, var(--risk-color, #6366f1) 52%, rgba(255,255,255,.18));
    box-shadow: 0 12px 28px color-mix(in srgb, var(--risk-color, #6366f1) 25%, transparent);
}
.ai-risk-score strong { font-size: 1.65rem; line-height: 1; }
.ai-risk-score span { font-size: .64rem; text-transform: uppercase; letter-spacing: .09em; color: rgba(255,255,255,.62); margin-top: 4px; }
.ai-risk-label { font-size: .82rem; font-weight: 800; letter-spacing: .02em; margin-bottom: 4px; }
.ai-risk-sub { font-size: .72rem; color: rgba(255,255,255,.58); line-height: 1.55; }
.ai-brief-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .78rem;
    font-weight: 850;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #334155;
    margin-bottom: 11px;
}
.ai-brief-title i { color: var(--risk-color, #6366f1); }
.ai-brief-text { font-size: .86rem; color: #334155; line-height: 1.75; margin: 0; }
.ai-focus-list,
.ai-action-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
.ai-focus-item {
    display: grid;
    grid-template-columns: 9px 1fr;
    gap: 10px;
    padding: 11px 12px;
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 12px;
}
.ai-focus-dot { width: 9px; height: 9px; border-radius: 999px; margin-top: 6px; background: #64748b; }
.ai-focus-critical .ai-focus-dot { background: #ef4444; }
.ai-focus-warning .ai-focus-dot { background: #f59e0b; }
.ai-focus-info .ai-focus-dot { background: #3b82f6; }
.ai-focus-positive .ai-focus-dot { background: #10b981; }
.ai-focus-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.ai-focus-name { font-size: .8rem; font-weight: 760; color: #0f172a; line-height: 1.35; }
.ai-focus-metric { font-size: .66rem; font-weight: 800; color: #475569; background: #f1f5f9; border-radius: 6px; padding: 2px 7px; white-space: nowrap; }
.ai-focus-why { font-size: .72rem; color: #64748b; line-height: 1.55; margin-top: 4px; }
.ai-action-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: .78rem;
    line-height: 1.55;
    color: #334155;
    background: rgba(248,250,252,.85);
    border: 1px solid #edf2f7;
    border-radius: 11px;
    padding: 10px 12px;
}
.ai-action-index {
    width: 22px;
    height: 22px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    background: var(--risk-color, #6366f1);
    font-size: .68rem;
    font-weight: 850;
}
.ai-confidence {
    margin-top: 14px;
    padding-top: 13px;
    border-top: 1px solid rgba(255,255,255,.12);
    font-size: .72rem;
    color: rgba(255,255,255,.62);
    line-height: 1.55;
}
.ai-confidence strong { color: #fff; }

/* ═══════ SECTION DIVIDER ════════════════════════════════════════════════ */
.ai-section { display: flex; align-items: center; gap: 12px; margin: 30px 0 18px; }
.ai-section-line { flex: 1; height: 1px; background: #e8ecf0; }
.ai-section-label {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: .72rem; font-weight: 800; letter-spacing: .08em;
    text-transform: uppercase; padding: 6px 16px; border-radius: 8px;
    white-space: nowrap;
}
.as-critical { background: rgba(239,68,68,.08);  color: #dc2626; border: 1px solid rgba(239,68,68,.2); }
.as-warning  { background: rgba(245,158,11,.08); color: #b45309; border: 1px solid rgba(245,158,11,.2); }
.as-info     { background: rgba(59,130,246,.08); color: #2563eb; border: 1px solid rgba(59,130,246,.2); }
.as-positive { background: rgba(16,185,129,.08); color: #059669; border: 1px solid rgba(16,185,129,.2); }

/* ═══════ INSIGHT CARD ═══════════════════════════════════════════════════ */
.ai-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #e8ecf0;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    margin-bottom: 18px; overflow: hidden;
    display: flex; transition: box-shadow .2s, transform .15s;
}
.ai-card:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,.1);
    transform: translateY(-1px);
}
.ai-card-stripe { width: 5px; flex-shrink: 0; }
.ai-card.level-critical .ai-card-stripe { background: linear-gradient(180deg, #ef4444, #dc2626); }
.ai-card.level-warning  .ai-card-stripe { background: linear-gradient(180deg, #f59e0b, #d97706); }
.ai-card.level-info     .ai-card-stripe { background: linear-gradient(180deg, #3b82f6, #2563eb); }
.ai-card.level-positive .ai-card-stripe { background: linear-gradient(180deg, #10b981, #059669); }

.ai-card-body { flex: 1; padding: 22px 26px; min-width: 0; }

.ai-card-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px; }
.ai-card-icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.level-critical .ai-card-icon-wrap { background: rgba(239,68,68,.1);  color: #ef4444; }
.level-warning  .ai-card-icon-wrap { background: rgba(245,158,11,.1); color: #d97706; }
.level-info     .ai-card-icon-wrap { background: rgba(59,130,246,.1); color: #3b82f6; }
.level-positive .ai-card-icon-wrap { background: rgba(16,185,129,.1); color: #10b981; }

.ai-level-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 11px; border-radius: 6px;
    font-size: .66rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; margin-bottom: 6px;
}
.pill-critical { background: rgba(239,68,68,.1);  color: #dc2626; }
.pill-warning  { background: rgba(245,158,11,.1); color: #b45309; }
.pill-info     { background: rgba(59,130,246,.1); color: #2563eb; }
.pill-positive { background: rgba(16,185,129,.1); color: #059669; }

.ai-card-title {
    font-size: .97rem; font-weight: 700; color: #111827;
    margin: 0; line-height: 1.35; letter-spacing: -.01em;
}

.ai-card-text {
    font-size: .875rem; color: #374151;
    line-height: 1.75; margin: 0 0 16px;
}

/* Fault links */
.ai-fault-list { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 14px; }
.ai-fault-link {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 8px;
    font-size: .74rem; font-weight: 500;
    background: #f8f9fb; border: 1px solid #e5e7eb;
    color: #374151; text-decoration: none;
    transition: all .15s;
}
.ai-fault-link:hover {
    background: #eff6ff; border-color: #bfdbfe; color: #2563eb;
    text-decoration: none;
}
.ai-fault-link .fl-id {
    font-size: .68rem; font-weight: 700;
    background: #e5e7eb; color: #6b7280;
    padding: 1px 5px; border-radius: 4px;
}
.ai-fault-link:hover .fl-id { background: #bfdbfe; color: #2563eb; }
.ai-fault-more {
    display: inline-flex; align-items: center;
    padding: 4px 11px; border-radius: 8px;
    font-size: .74rem; font-weight: 600;
    background: #f1f5f9; color: #64748b;
    border: 1px dashed #cbd5e1;
}

/* Card footer */
.ai-card-footer {
    display: flex; align-items: center;
    justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}
.ai-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.ai-tag {
    font-size: .68rem; font-weight: 600;
    padding: 3px 9px; border-radius: 5px;
    background: #f1f5f9; color: #64748b;
}
.ai-metric { display: inline-flex; align-items: flex-end; gap: 5px; }
.ai-metric-val { font-size: 1.2rem; font-weight: 800; color: #1e293b; line-height: 1; }
.ai-metric-lbl { font-size: .68rem; color: #94a3b8; font-weight: 600; margin-bottom: 1px; }

/* ═══════ EMPTY STATE ════════════════════════════════════════════════════ */
.ai-empty {
    text-align: center; padding: 60px 20px;
    background: #fff; border-radius: 16px;
    border: 1px solid #e8ecf0;
}
.ai-empty-icon {
    width: 72px; height: 72px; border-radius: 20px;
    background: rgba(99,102,241,.08); color: #6366f1;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; margin: 0 auto 18px;
}

/* ═══════ SCAN ANIMATION ═════════════════════════════════════════════════ */
@keyframes pulse-dot { 0%,100% { opacity:1; } 50% { opacity:.3; } }
.ai-pulse { animation: pulse-dot 1.5s ease-in-out infinite; }
.ai-pulse-2 { animation: pulse-dot 1.5s ease-in-out infinite .5s; }
.ai-pulse-3 { animation: pulse-dot 1.5s ease-in-out infinite 1s; }

@media (max-width: 767px) {
    .ai-hero-inner { padding: 22px 20px; }
    .ai-hero-title { font-size: 1.3rem; }
    .ai-brief { padding: 16px; margin-top: -4px; }
    .ai-brief-grid { grid-template-columns: 1fr; }
    .ai-risk-top { align-items: flex-start; }
    .ai-card-body { padding: 16px 18px; }
    .ai-summary-chip { min-width: 130px; }
}
</style>
@endpush

@section('content')
@php
$grouped   = collect($insights)->groupBy('level');
$critCnt   = $grouped->get('critical', collect())->count();
$warnCnt   = $grouped->get('warning',  collect())->count();
$infoCnt   = $grouped->get('info',     collect())->count();
$posiCnt   = $grouped->get('positive', collect())->count();
$totalCnt  = count($insights);

$levelMeta = [
    'critical' => ['label'=>'KRİTİK',  'pill'=>'pill-critical', 'section'=>'as-critical', 'icon'=>'fa-triangle-exclamation', 'color'=>'#ef4444'],
    'warning'  => ['label'=>'UYARI',   'pill'=>'pill-warning',  'section'=>'as-warning',  'icon'=>'fa-circle-exclamation',   'color'=>'#f59e0b'],
    'info'     => ['label'=>'BİLGİ',   'pill'=>'pill-info',     'section'=>'as-info',     'icon'=>'fa-circle-info',          'color'=>'#3b82f6'],
    'positive' => ['label'=>'OLUMLU',  'pill'=>'pill-positive', 'section'=>'as-positive', 'icon'=>'fa-circle-check',         'color'=>'#10b981'],
];
@endphp

<div class="container-fluid pb-5 ai-page">

    {{-- ── Breadcrumb ──────────────────────────────────────────────────── --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-brain text-primary" style="font-size:1rem"></i>
                    Yapay Zeka Analiz Raporu
                </h4>
                <span class="text-muted" style="font-size:.8rem">
                    Son 3 Günlük Derin Arıza Örüntüsü &amp; Operasyonel Analiz
                </span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Analiz</li>
            </ol>
        </div>
    </div>

    {{-- ── Hero Banner ─────────────────────────────────────────────────── --}}
    <div class="ai-hero mt-2">
        <div class="ai-hero-inner">
            <div class="ai-hero-badge">
                <span class="ai-pulse" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#a5b4fc"></span>
                <span class="ai-pulse-2" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#a5b4fc"></span>
                <span class="ai-pulse-3" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#a5b4fc"></span>
                &nbsp;Balmy Hotels · Arıza Zekası
            </div>
            <h2 class="ai-hero-title">
                <i class="fas fa-microchip me-2" style="color:#818cf8;font-size:1.3rem"></i>
                Operasyonel Arıza Analiz Motoru
            </h2>
            <p class="ai-hero-sub">
                Son <strong style="color:#fff">3 günlük</strong> arıza veritabanı
                (<strong style="color:#e2e8f0">{{ $windowStart->format('d.m.Y') }}</strong>
                –
                <strong style="color:#e2e8f0">{{ $now->format('d.m.Y') }}</strong>)
                üzerinde çok boyutlu desen analizi, SLA uyum kontrolü, departman yük tespiti
                ve tekrarlayan hata örüntüsü taraması gerçekleştirildi.
                Aşağıdaki bulgular, veri üzerinden otomatik olarak türetilmiştir.
            </p>
            <div class="ai-hero-chips">
                <span class="ai-hero-chip">
                    <i class="fas fa-database"></i>
                    {{ $allFaults->count() }} kayıt analiz edildi
                </span>
                <span class="ai-hero-chip">
                    <i class="fas fa-chart-network"></i>
                    {{ $totalCnt }} bulgu üretildi
                </span>
                <span class="ai-hero-chip">
                    <i class="fas fa-calendar-day"></i>
                    Bugün: {{ $todayFaults->count() }} arıza
                </span>
                <span class="ai-hero-chip">
                    <i class="fas fa-clock"></i>
                    Son analiz: {{ $now->format('d.m.Y H:i:s') }}
                </span>
                <a href="{{ route('faults.analysis') }}"
                   style="display:inline-flex;align-items:center;gap:7px;
                          background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);
                          border-radius:8px;padding:7px 14px;font-size:.78rem;color:#fff;
                          text-decoration:none;font-weight:600;transition:background .15s"
                   onmouseover="this.style.background='rgba(255,255,255,.2)'"
                   onmouseout="this.style.background='rgba(255,255,255,.12)'">
                    <i class="fas fa-rotate" style="font-size:.7rem"></i>
                    Yenile
                </a>
                <button id="btn-send-report"
                   style="display:inline-flex;align-items:center;gap:7px;
                          background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);
                          border-radius:8px;padding:7px 14px;font-size:.78rem;color:#fff;
                          font-weight:600;cursor:pointer;transition:background .15s"
                   onmouseover="this.style.background='rgba(255,255,255,.2)'"
                   onmouseout="this.style.background='rgba(255,255,255,.12)'">
                    <i class="fas fa-envelope" style="font-size:.7rem"></i>
                    E-posta Gönder
                </button>
            </div>
        </div>
    </div>

    @if(!empty($narrative))
    <div class="ai-brief" style="--risk-color: {{ $narrative['risk_color'] ?? '#6366f1' }}">
        <div class="ai-brief-grid">
            <div class="ai-risk-panel">
                <div class="ai-risk-top">
                    <div>
                        <div class="ai-risk-label">{{ $narrative['risk_label'] ?? 'Analiz özeti' }}</div>
                        <div class="ai-risk-sub">
                            Karar destek skoru, açık iş oranı, tekrar eden bulgular ve öncelik seviyeleriyle hesaplandı.
                        </div>
                    </div>
                    <div class="ai-risk-score">
                        <strong>{{ $narrative['risk_score'] ?? 0 }}</strong>
                        <span>risk</span>
                    </div>
                </div>
                <p class="ai-risk-sub" style="margin:0">{{ $narrative['summary'] ?? '' }}</p>
                <div class="ai-confidence">
                    <strong>Veri güveni: {{ $narrative['confidence_label'] ?? 'Orta' }}</strong><br>
                    {{ $narrative['confidence_text'] ?? 'Bulgular mevcut veri yoğunluğuna göre yorumlanmıştır.' }}
                </div>
            </div>

            <div>
                <div class="ai-brief-title">
                    <i class="fas fa-crosshairs"></i>
                    Önceliklendirilmiş Odak
                </div>
                <ul class="ai-focus-list">
                    @foreach(($narrative['focus_items'] ?? []) as $item)
                    <li class="ai-focus-item ai-focus-{{ $item['level'] ?? 'info' }}">
                        <span class="ai-focus-dot"></span>
                        <div>
                            <div class="ai-focus-head">
                                <span class="ai-focus-name">{{ $item['title'] ?? 'Operasyonel bulgu' }}</span>
                                @if(!empty($item['metric']))
                                <span class="ai-focus-metric">{{ $item['metric'] }}</span>
                                @endif
                            </div>
                            <div class="ai-focus-why">{{ $item['why'] ?? '' }}</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <div class="ai-brief-title">
                    <i class="fas fa-list-check"></i>
                    Önerilen Aksiyon Planı
                </div>
                <ol class="ai-action-list">
                    @foreach(($narrative['action_plan'] ?? []) as $step)
                    <li class="ai-action-item">
                        <span class="ai-action-index">{{ $loop->iteration }}</span>
                        <span>{{ $step }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Summary Chips ────────────────────────────────────────────────── --}}
    <div class="ai-summary">
        <div class="ai-summary-chip" style="border-left:4px solid #ef4444">
            <div class="sch-icon" style="background:rgba(239,68,68,.1)">
                <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
            </div>
            <div>
                <div class="sch-num" style="color:#ef4444">{{ $critCnt }}</div>
                <div class="sch-lbl">Kritik Bulgu</div>
                <div class="sch-sub">Acil aksiyon gerekli</div>
            </div>
        </div>

        <div class="ai-summary-chip" style="border-left:4px solid #f59e0b">
            <div class="sch-icon" style="background:rgba(245,158,11,.1)">
                <i class="fas fa-circle-exclamation" style="color:#d97706"></i>
            </div>
            <div>
                <div class="sch-num" style="color:#d97706">{{ $warnCnt }}</div>
                <div class="sch-lbl">Uyarı</div>
                <div class="sch-sub">Yakın takip önerilir</div>
            </div>
        </div>

        <div class="ai-summary-chip" style="border-left:4px solid #3b82f6">
            <div class="sch-icon" style="background:rgba(59,130,246,.1)">
                <i class="fas fa-circle-info" style="color:#3b82f6"></i>
            </div>
            <div>
                <div class="sch-num" style="color:#3b82f6">{{ $infoCnt }}</div>
                <div class="sch-lbl">Bilgilendirme</div>
                <div class="sch-sub">Operasyonel veri</div>
            </div>
        </div>

        <div class="ai-summary-chip" style="border-left:4px solid #10b981">
            <div class="sch-icon" style="background:rgba(16,185,129,.1)">
                <i class="fas fa-circle-check" style="color:#10b981"></i>
            </div>
            <div>
                <div class="sch-num" style="color:#10b981">{{ $posiCnt }}</div>
                <div class="sch-lbl">Olumlu Bulgu</div>
                <div class="sch-sub">İyi performans göstergesi</div>
            </div>
        </div>

        <div class="ai-summary-chip" style="border-left:4px solid #6366f1">
            <div class="sch-icon" style="background:rgba(99,102,241,.1)">
                <i class="fas fa-layer-group" style="color:#6366f1"></i>
            </div>
            <div>
                <div class="sch-num" style="color:#6366f1">{{ $allFaults->count() }}</div>
                <div class="sch-lbl">Toplam Kayıt</div>
                <div class="sch-sub">3 günlük pencere</div>
            </div>
        </div>
    </div>

    {{-- ── No Data State ────────────────────────────────────────────────── --}}
    @if(count($insights) === 0)
    <div class="ai-empty">
        <div class="ai-empty-icon">
            <i class="fas fa-magnifying-glass"></i>
        </div>
        <h5 style="font-weight:700;color:#1e293b;margin-bottom:8px">Analiz edilecek veri bulunamadı</h5>
        <p style="color:#64748b;font-size:.88rem">Son 3 günlük dönemde sisteme arıza kaydı girilmemiştir.</p>
    </div>
    @else

    {{-- ══════════════════════════════════════════════════════════════════
         BÖLÜM DÖNGÜSÜ: critical → warning → info → positive
    ═══════════════════════════════════════════════════════════════════ --}}
    @foreach(['critical','warning','info','positive'] as $lvl)
    @php $sectionInsights = collect($insights)->where('level', $lvl)->values(); @endphp
    @if($sectionInsights->count() > 0)

    {{-- Section Divider --}}
    <div class="ai-section">
        <div class="ai-section-line"></div>
        <span class="ai-section-label {{ $levelMeta[$lvl]['section'] }}">
            <i class="fas {{ $levelMeta[$lvl]['icon'] }}"></i>
            {{ $levelMeta[$lvl]['label'] }} BULGULAR
            <span style="background:rgba(0,0,0,.06);padding:1px 7px;border-radius:4px;font-size:.65rem">
                {{ $sectionInsights->count() }}
            </span>
        </span>
        <div class="ai-section-line"></div>
    </div>

    @foreach($sectionInsights as $insight)
    <div class="ai-card level-{{ $lvl }}">
        <div class="ai-card-stripe"></div>
        <div class="ai-card-body">

            {{-- Card Head --}}
            <div class="ai-card-head">
                <div class="ai-card-icon-wrap">
                    <i class="fas {{ $insight['icon'] }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="ai-level-pill {{ $levelMeta[$lvl]['pill'] }}">
                        <i class="fas {{ $levelMeta[$lvl]['icon'] }}" style="font-size:.6rem"></i>
                        {{ $levelMeta[$lvl]['label'] }}
                    </div>
                    <h5 class="ai-card-title">{{ $insight['title'] }}</h5>
                </div>
            </div>

            {{-- Body Text --}}
            <p class="ai-card-text">{!! $insight['body'] !!}</p>

            {{-- Related Faults --}}
            @if(!empty($insight['faults']) && $insight['faults']->count() > 0)
            @php
                $displayFaults = $insight['faults']->take(6);
                $remainingCnt  = $insight['faults']->count() - $displayFaults->count();
            @endphp
            <div class="ai-fault-list">
                <span style="font-size:.73rem;font-weight:700;color:#94a3b8;align-self:center;margin-right:2px">
                    <i class="fas fa-link" style="font-size:.65rem"></i> İlgili arızalar:
                </span>
                @foreach($displayFaults as $f)
                <a href="{{ route('faults.show', $f) }}" class="ai-fault-link" target="_blank">
                    <span class="fl-id">#{{ $f->id }}</span>
                    {{ Str::limit($f->faultType?->name ?? $f->title, 22) }}
                    @if($f->faultArea)
                    <span style="color:#94a3b8;font-size:.68rem">— {{ $f->faultArea->name }}</span>
                    @endif
                    <span style="font-size:.65rem;padding:1px 6px;border-radius:4px;font-weight:700;
                        background:{{ \App\Models\Fault::STATUS_COLORS[$f->status] === 'success' ? 'rgba(16,185,129,.12)' : (in_array(\App\Models\Fault::STATUS_COLORS[$f->status], ['warning','info']) ? 'rgba(245,158,11,.12)' : 'rgba(239,68,68,.12)') }};
                        color:{{ \App\Models\Fault::STATUS_COLORS[$f->status] === 'success' ? '#059669' : (in_array(\App\Models\Fault::STATUS_COLORS[$f->status], ['warning','info']) ? '#b45309' : '#dc2626') }}">
                        {{ \App\Models\Fault::STATUSES[$f->status] ?? $f->status }}
                    </span>
                </a>
                @endforeach
                @if($remainingCnt > 0)
                <span class="ai-fault-more">+{{ $remainingCnt }} daha</span>
                @endif
            </div>
            @endif

            {{-- Card Footer --}}
            <div class="ai-card-footer">
                <div class="ai-tags">
                    @foreach($insight['tags'] as $tag)
                    <span class="ai-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @if(!empty($insight['metric']))
                <div class="ai-metric">
                    <span class="ai-metric-val">{{ $insight['metric']['value'] }}</span>
                    <span class="ai-metric-lbl">{{ $insight['metric']['label'] }}</span>
                </div>
                @endif
            </div>

        </div>
    </div>
    @endforeach

    @endif
    @endforeach

    @endif {{-- end insights check --}}

    {{-- ── Footer Note ──────────────────────────────────────────────────── --}}
    <div style="margin-top:32px;padding:18px 24px;background:#f8f9fb;border-radius:14px;border:1px solid #e8ecf0">
        <div class="d-flex align-items-start gap-3">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-circle-info" style="color:#6366f1;font-size:.85rem"></i>
            </div>
            <div>
                <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:4px">
                    Analiz Metodolojisi &amp; Kapsam Notu
                </div>
                <p style="font-size:.78rem;color:#64748b;margin:0;line-height:1.7">
                    Bu analiz, <strong>{{ $windowStart->format('d.m.Y 00:00') }}</strong> –
                    <strong>{{ $now->format('d.m.Y H:i') }}</strong> tarihleri arasında oluşturulan arıza kayıtlarını kapsamaktadır.
                    Bulgular tamamen veri güdümlü olup gerçek zamanlı veritabanı sorgulamasına dayanmaktadır.
                    Sayfa her yenilendiğinde anında yeni ve güncel analiz üretilir.
                    Raporun güvenilirliği, sisteme doğru ve eksiksiz girilen arıza verilerine bağlıdır.
                    Geçmiş dönem verilerini içeren kapsamlı istatistikler için
                    <a href="{{ route('faults.stats') }}" style="color:#6366f1;font-weight:600">İstatistikler &amp; Skor</a> sayfasını kullanınız.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('btn-send-report').addEventListener('click', function () {
    Swal.fire({
        title: '<strong>Raporu E-posta ile Gönder</strong>',
        html: '<p style="color:#64748b;margin:0 0 4px">Yapay Zeka Analiz Raporu PDF olarak hazırlanacak<br>ve belirttiğiniz adrese gönderilecektir.</p>',
        input: 'email',
        inputLabel: 'E-posta Adresi',
        inputPlaceholder: 'ornek@otel.com',
        inputAttributes: { autocomplete: 'email' },
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane"></i>&nbsp; Gönder',
        cancelButtonText: 'İptal',
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#94a3b8',
        showLoaderOnConfirm: true,
        inputValidator: (value) => {
            if (!value) return 'Lütfen bir e-posta adresi girin.';
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(value)) return 'Geçerli bir e-posta adresi girin.';
        },
        preConfirm: (email) => {
            return fetch('{{ route('faults.analysis.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email }),
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Sunucu hatası oluştu.');
                    });
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(error.message || 'Gönderilemedi. Lütfen tekrar deneyin.');
            });
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
        if (result.isConfirmed && result.value?.success) {
            Swal.fire({
                icon: 'success',
                title: 'Rapor Gönderildi!',
                html: '<p>' + (result.value.message || 'Rapor başarıyla gönderildi.') + '</p>',
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'Tamam',
            });
        }
    });
});
</script>
@endpush
