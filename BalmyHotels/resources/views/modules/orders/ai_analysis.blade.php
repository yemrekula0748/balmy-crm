@extends('layouts.default')
@section('content')
<style>
.ai-section-header{background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;border-radius:10px 10px 0 0;padding:14px 20px;display:flex;align-items:center;gap:10px}
.ai-section-num{min-width:32px;height:32px;border-radius:50%;background:rgba(99,102,241,.35);border:1.5px solid rgba(99,102,241,.7);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#a5b4fc}
.ai-section-title{font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.ai-section-subtitle{font-size:11px;color:#94a3b8;margin-left:auto}
.ai-card{background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:22px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.ai-card-body{padding:18px 20px}
.kpi-card{border-radius:10px;padding:16px 18px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,.05)}
.kpi-value{font-size:22px;font-weight:700;line-height:1;margin-top:4px}
.kpi-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:2px}
.kpi-delta{font-size:11px;margin-top:6px}
.insight-box{background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #6366f1;border-radius:0 8px 8px 0;padding:12px 16px;font-size:12px;line-height:1.6}
.insight-box.warn{border-left-color:#f59e0b}
.insight-box.danger{border-left-color:#ef4444}
.insight-box.success{border-left-color:#10b981}
.chapter-badge{display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;color:#475569}
.health-bar-bg{background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden;flex:1}
.health-bar-fill{height:8px;border-radius:4px;transition:width .4s}
.stat-callout{background:linear-gradient(135deg,#eef2ff,#f8fafc);border:1px solid #c7d2fe;border-radius:8px;padding:10px 14px;text-align:center}
.stat-callout .val{font-size:20px;font-weight:700;color:#4f46e5}
.stat-callout .lbl{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.hhi-meter-bg{height:14px;background:linear-gradient(to right,#10b981 0%,#10b981 25%,#f59e0b 25%,#f59e0b 50%,#ef4444 50%,#ef4444 100%);border-radius:7px;position:relative;overflow:visible}
.hhi-needle{position:absolute;top:-4px;width:6px;height:22px;background:#1e293b;border-radius:3px;transform:translateX(-50%)}
.hr-divider{border:none;border-top:1px solid #f1f5f9;margin:14px 0}
.pareto-box{background:linear-gradient(135deg,#fef3c7,#fffbeb);border:1px solid #fcd34d;border-radius:8px;padding:14px 18px}
.bucket-bar{background:#6366f1;border-radius:3px;transition:height .4s}
.waiter-rank-1{background:linear-gradient(to right,#fef9c3,#fff)}
.waiter-rank-2{background:linear-gradient(to right,#f0f9ff,#fff)}
.waiter-rank-3{background:linear-gradient(to right,#f0fdf4,#fff)}
.rec-card{border-radius:8px;padding:14px 16px;margin-bottom:10px;border-left:4px solid}
.rec-priority-high{background:#fff5f5;border-left-color:#ef4444}
.rec-priority-med{background:#fffbeb;border-left-color:#f59e0b}
.rec-priority-ok{background:#f0fdf4;border-left-color:#10b981}
.table-num-col{font-weight:600;color:#4f46e5;width:36px;text-align:center}
.empty-state{text-align:center;padding:40px 20px;color:#94a3b8}
</style>
<div class="container-fluid">

{{-- Başlık --}}
<div class="row page-titles mx-0">
    <div class="col-sm-6 p-md-0">
        <div class="welcome-text">
            <h4 class="d-flex align-items-center gap-2">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:13px;font-weight:800;flex-shrink:0;">AI</span>
                Stratejik Sipariş Analizi
            </h4>
        </div>
    </div>
    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
            <li class="breadcrumb-item active">Stratejik Analiz</li>
        </ol>
    </div>
</div>

{{-- Filtre --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.ai-analysis') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Restoran</label>
                <select name="restaurant_id" class="form-select form-select-sm">
                    <option value="">— Tüm Restoranlar —</option>
                    @foreach($restaurants as $r)
                        <option value="{{ $r->id }}" @selected($restaurantId == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Başlangıç</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Bitiş</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>
                    Analiz Et
                </button>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(7)">Son 7 Gün</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(30)">Son 30</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(90)">Son 90</button>
            </div>
        </form>
    </div>
</div>

@if($paidRevenue == 0 && $totalSessions == 0)
<div class="empty-state"><h5>Seçilen aralıkta veri bulunamadı.</h5><p class="mb-0">Lütfen filtrelerinizi genişletin.</p></div>
@else

{{-- ═══════════════════════════════════════════════════════════
     SECTION 01 — Executive Summary
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">01</div>
        <span class="ai-section-title">Yönetici Özeti</span>
        <span class="ai-section-subtitle">{{ $dateFrom }} → {{ $dateTo }} &bull; {{ $dayCount }} gün</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #6366f1">
                    <div class="kpi-label">Toplam Hasılat</div>
                    <div class="kpi-value" style="color:#4f46e5">₺{{ number_format($paidRevenue,2,'.',',') }}</div>
                    <div class="kpi-delta text-muted">{{ $activeDays }} aktif gün</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #0ea5e9">
                    <div class="kpi-label">Günlük Ort. Hasılat</div>
                    <div class="kpi-value" style="color:#0284c7">₺{{ number_format($avgDailyRev,2,'.',',') }}</div>
                    <div class="kpi-delta text-muted">σ ₺{{ number_format($revStdDev,2,'.',',') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #10b981">
                    <div class="kpi-label">Toplam Oturum</div>
                    <div class="kpi-value" style="color:#059669">{{ number_format($totalSessions) }}</div>
                    <div class="kpi-delta text-muted">Ort. ₺{{ number_format($avgSessionRev,2,'.',',') }} / oturum</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #f59e0b">
                    <div class="kpi-label">Toplam Sipariş</div>
                    <div class="kpi-value" style="color:#d97706">{{ number_format($totalOrders) }}</div>
                    <div class="kpi-delta text-muted">Ort. {{ $avgOrdersPerDay }} / gün</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #8b5cf6">
                    <div class="kpi-label">Toplam Kalem (adet)</div>
                    <div class="kpi-value" style="color:#7c3aed">{{ number_format($totalQty) }}</div>
                    <div class="kpi-delta text-muted">{{ $avgQtyPerSession }} kalem / oturum</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #ec4899">
                    <div class="kpi-label">Sipariş Yoğunluğu</div>
                    <div class="kpi-value" style="color:#db2777">{{ $sessionIntensity }}×</div>
                    <div class="kpi-delta text-muted">sipariş / oturum</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #14b8a6">
                    <div class="kpi-label">Ücretli Kalem %</div>
                    <div class="kpi-value" style="color:#0d9488">%{{ $paidQtyPct }}</div>
                    <div class="kpi-delta text-muted">{{ number_format($paidQtyTotal) }} adet ücretli</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #64748b">
                    <div class="kpi-label">Farklı Ürün</div>
                    <div class="kpi-value" style="color:#475569">{{ $distinctItems }}</div>
                    <div class="kpi-delta text-muted">{{ $distinctPaidItems }} ücretli + {{ $distinctFreeItems }} ücretsiz</div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-callout">
                    <div class="val" style="color:{{ $consistencyColor }}">%{{ $coeffVar }}</div>
                    <div class="lbl">Değişim Katsayısı (CV)</div>
                    <div style="font-size:11px;font-weight:600;color:{{ $consistencyColor }};margin-top:4px">{{ $consistencyTR }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-callout">
                    <div class="val" style="color:#6366f1">{{ $peakToAvgRatio }}×</div>
                    <div class="lbl">Zirve / Ortalama Oranı</div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Peak ₺{{ number_format($peakDayRev,0,'.',',') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-callout" style="background:linear-gradient(135deg,{{ $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#f0fdf4,#fff' : '#fff5f5,#fff' }});">
                    <div class="val" style="color:{{ $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#16a34a' : '#dc2626' }}">
                        @if($weeklyGrowth !== null) {{ $weeklyGrowth >= 0 ? '+' : '' }}%{{ $weeklyGrowth }} @else — @endif
                    </div>
                    <div class="lbl">Haftalık Büyüme</div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Son 7 vs önceki 7 gün</div>
                </div>
            </div>
        </div>
        @if($zeroRevDays > 0)
        <div class="insight-box warn mt-3">
            <strong>Dikkat:</strong> {{ $dayCount }} günlük dönemde <strong>{{ $zeroRevDays }} gün</strong> hasılat sıfır. Bu günler operasyonel kapalılık mı, yoksa veri eksikliği mi? Kontrol edilmesi önerilir.
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 02 — Finansal Trend
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">02</div>
        <span class="ai-section-title">Finansal Trend Analizi</span>
        <span class="ai-section-subtitle">Günlük hasılat & sipariş akışı</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="insight-box success">
                    <strong>Zirve Gün:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#15803d">₺{{ number_format($peakDayRev,2,'.',',') }}</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $peakDayDate }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box danger">
                    <strong>En Düşük Gün:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#dc2626">₺{{ number_format($lowestDayRev,2,'.',',') }}</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $lowestDayDate }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>Önceki Hafta:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#4f46e5">₺{{ number_format($week1Rev,2,'.',',') }}</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $w1Start ?? '' }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box {{ isset($weeklyGrowth) && $weeklyGrowth !== null && $weeklyGrowth >= 0 ? 'success' : 'danger' }}">
                    <strong>Son Hafta:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:{{ isset($weeklyGrowth) && $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#15803d' : '#dc2626' }}">₺{{ number_format($week2Rev,2,'.',',') }}</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $w2Start ?? '' }} – {{ $dateTo }}</span>
                </div>
            </div>
        </div>
        <canvas id="dailyRevenueChart" height="90"></canvas>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 03 — Restoran Portföyü
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">03</div>
        <span class="ai-section-title">Restoran Portföy Analizi</span>
        <span class="ai-section-subtitle">HHI: {{ number_format($hhiIndex) }} — {{ $hhiLabelTR }}</span>
    </div>
    <div class="ai-card-body">
        {{-- HHI Meter --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1" style="font-size:11px;color:#64748b">
                <span>Dengeli</span><span>Orta yoğunlaşma</span><span>Yüksek yoğunlaşma</span>
            </div>
            <div class="hhi-meter-bg" style="max-width:600px">
                @php $hhiPct = min(round($hhiIndex/10000*100), 100); @endphp
                <div class="hhi-needle" style="left:{{ $hhiPct }}%"></div>
            </div>
            <div class="mt-2" style="font-size:12px;color:#475569">
                HHI Endeksi: <strong>{{ number_format($hhiIndex) }}</strong> / 10.000 &mdash; {{ $hhiLabelTR }}
                <span class="ms-2 text-muted">(2.500'ün altı = rekabetçi, 5.000'in üstü = tek nokta riski)</span>
            </div>
        </div>
        <div class="hr-divider"></div>
        {{-- Health Score Table --}}
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#64748b">
                        <th>Restoran</th>
                        <th class="text-end">Hasılat</th>
                        <th class="text-end">Pay %</th>
                        <th class="text-end">₺/Oturum</th>
                        <th class="text-end">₺/Gün</th>
                        <th class="text-end">Yoğunluk</th>
                        <th class="text-end">Ücretsiz%</th>
                        <th style="min-width:160px">Sağlık Skoru</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($healthScores as $r)
                    @php $share = $paidRevenue > 0 ? round($r->revenue / $paidRevenue * 100, 1) : 0; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $r->restaurant_name }}</td>
                        <td class="text-end">₺{{ number_format($r->revenue,0,'.',',') }}</td>
                        <td class="text-end">%{{ $share }}</td>
                        <td class="text-end">₺{{ number_format($r->per_session,0,'.',',') }}</td>
                        <td class="text-end">₺{{ number_format($r->per_day,0,'.',',') }}</td>
                        <td class="text-end">{{ $r->intensity }}×</td>
                        <td class="text-end">%{{ $r->free_pct }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="health-bar-bg">
                                    <div class="health-bar-fill" style="width:{{ $r->health_score }}%;background:{{ $r->health_color }}"></div>
                                </div>
                                <span style="font-size:11px;font-weight:600;color:{{ $r->health_color }};white-space:nowrap">{{ $r->health_score }}/100 {{ $r->health_labelTR }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($topResto && $bottomResto)
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="insight-box success"><strong>🏆 En İyi Performans:</strong> {{ $topResto->restaurant_name }} — ₺{{ number_format($topResto->revenue,0,'.',',') }} hasılat, ₺{{ number_format($topResto->per_session,0,'.',',') }} / oturum</div>
            </div>
            <div class="col-md-6">
                <div class="insight-box danger"><strong>⚠ Düşük Performans:</strong> {{ $bottomResto->restaurant_name }} — ₺{{ number_format($bottomResto->revenue,0,'.',',') }} hasılat, optimizasyon öncelikli</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 04 — Ürün Zekası
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">04</div>
        <span class="ai-section-title">Ürün Portföyü & Pareto Analizi</span>
        <span class="ai-section-subtitle">{{ $distinctPaidItems }} ücretli ürün sınıfı</span>
    </div>
    <div class="ai-card-body">
        {{-- Pareto callout --}}
        <div class="pareto-box mb-4 d-flex align-items-start gap-3">
            <div style="font-size:28px;line-height:1">📊</div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#92400e">Pareto 80/20 Tespiti</div>
                <div style="font-size:13px;color:#78350f;margin-top:4px">
                    Toplam hasılatın <strong>%80'i</strong>, ücretli ürünlerin yalnızca
                    <strong>%{{ $paretoRatio }}</strong>'si tarafından üretiyor
                    (<strong>{{ $paretoProductCount }}</strong> / {{ $distinctPaidItems }} ürün sınıfı).
                </div>
                @if($paretoRatio <= 25)
                <div style="font-size:11px;color:#92400e;margin-top:4px">✅ Çok verimli ürün portföyü — odaklanmış strateji etkili çalışıyor.</div>
                @elseif($paretoRatio <= 40)
                <div style="font-size:11px;color:#92400e;margin-top:4px">⚠ Kabul edilebilir yoğunlaşma — düşük performanslı ürünler gözden geçirilebilir.</div>
                @else
                <div style="font-size:11px;color:#dc2626;margin-top:4px">🔴 Hasılat çok dağınık — menü optimizasyonu ve odaklanma stratejisi önerilir.</div>
                @endif
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-7">
                <div class="fw-semibold mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569">En Yüksek Hasılatlı 20 Ürün</div>
                <div style="overflow-x:auto">
                    <table class="table table-sm mb-0" style="font-size:12px">
                        <thead>
                            <tr style="background:#f8fafc;font-size:11px;color:#64748b">
                                <th>#</th><th>Ürün</th><th class="text-end">Hasılat</th><th class="text-end">Adet</th><th class="text-end">Ort. Fiyat</th><th class="text-end">Velocity/gün</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPaidProducts as $i => $p)
                            <tr @if($i<3) style="background:linear-gradient(to right,{{ ['#fef9c3','#f0f9ff','#f0fdf4'][$i] }},#fff)" @endif>
                                <td class="table-num-col">{{ $i+1 }}</td>
                                <td>{{ Str::limit($p->item_name,32) }}</td>
                                <td class="text-end fw-semibold">₺{{ number_format($p->revenue,0,'.',',') }}</td>
                                <td class="text-end">{{ number_format($p->qty) }}</td>
                                <td class="text-end">₺{{ number_format($p->avg_price,2,'.',',') }}</td>
                                <td class="text-end" style="color:#6366f1;font-weight:600">{{ $p->velocity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <div class="fw-semibold mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569">Premium Ürünler (Fiyat Sırası)</div>
                <table class="table table-sm mb-0" style="font-size:12px">
                    <thead>
                        <tr style="background:#f8fafc;font-size:11px;color:#64748b">
                            <th>#</th><th>Ürün</th><th class="text-end">Ort. Fiyat</th><th class="text-end">Adet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($premiumProducts as $i => $p)
                        <tr>
                            <td class="table-num-col">{{ $i+1 }}</td>
                            <td>{{ Str::limit($p->item_name,28) }}</td>
                            <td class="text-end fw-semibold" style="color:#7c3aed">₺{{ number_format($p->avg_price,2,'.',',') }}</td>
                            <td class="text-end text-muted">{{ number_format($p->qty) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="hr-divider"></div>
                <div class="fw-semibold mb-2 mt-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569">Top Ücretsiz Ürünler</div>
                <table class="table table-sm mb-0" style="font-size:12px">
                    <thead>
                        <tr style="background:#f8fafc;font-size:11px;color:#64748b">
                            <th>#</th><th>Ürün</th><th class="text-end">Adet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topFreeProducts->take(8) as $i => $p)
                        <tr>
                            <td class="table-num-col">{{ $i+1 }}</td>
                            <td>{{ Str::limit($p->item_name,30) }}</td>
                            <td class="text-end">{{ number_format($p->qty) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 05 — Zamansal Zeka
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">05</div>
        <span class="ai-section-title">Zamansal Yoğunluk Analizi</span>
        <span class="ai-section-subtitle">Saatlik & günlük örüntüler</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>Zirve Saat (Sipariş):</strong> <span style="font-size:15px;font-weight:700;color:#6366f1">{{ sprintf('%02d:00', $peakHour) }}</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $peakHourCnt }} sipariş</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>Zirve Saat (Hasılat):</strong> <span style="font-size:15px;font-weight:700;color:#10b981">{{ sprintf('%02d:00', $peakRevHour) }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>En Yoğun Gün:</strong> <span style="font-size:14px;font-weight:700;color:#f59e0b">{{ $busiestDay }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>En Sakin Gün:</strong> <span style="font-size:14px;font-weight:700;color:#94a3b8">{{ $quietestDay }}</span>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:18px;font-weight:700;color:#6366f1">%{{ $morningPct }}</div>
                    <div style="font-size:11px;color:#64748b">Sabah (06–11)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:18px;font-weight:700;color:#f59e0b">%{{ $lunchPct }}</div>
                    <div style="font-size:11px;color:#64748b">Öğle (12–14)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:18px;font-weight:700;color:#10b981">%{{ $afternoonPct }}</div>
                    <div style="font-size:11px;color:#64748b">İkindi (15–17)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:18px;font-weight:700;color:#8b5cf6">%{{ $eveningPct }}</div>
                    <div style="font-size:11px;color:#64748b">Akşam (18–23)</div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-8">
                <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Saatlik Sipariş & Hasılat Dağılımı</div>
                <canvas id="hourlyChart" height="80"></canvas>
            </div>
            <div class="col-md-4">
                <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Haftanın Günleri (Hasılat)</div>
                <canvas id="weekdayChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 06 — Masa Kalış Süresi Kinetiği
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">06</div>
        <span class="ai-section-title">Masa Kalış Süresi Kinetiği</span>
        <span class="ai-section-subtitle">σ = {{ $overallStdDur }} dk &bull; CV = %{{ $durCoeffVar }}</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="stat-callout">
                    <div class="val" style="color:#6366f1">{{ $overallAvgDur }} dk</div>
                    <div class="lbl">Ortalama Kalış</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-callout">
                    <div class="val" style="color:#f59e0b">{{ $overallStdDur }} dk</div>
                    <div class="lbl">Std. Sapma (σ)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-callout">
                    <div class="val" style="color:{{ $durCoeffVar <= 30 ? '#10b981' : ($durCoeffVar <= 55 ? '#f59e0b' : '#ef4444') }}">%{{ $durCoeffVar }}</div>
                    <div class="lbl">CV (tutarlılık)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-callout">
                    <div class="val" style="color:#8b5cf6">{{ $durBucketTotal }}</div>
                    <div class="lbl">Analiz Edilen Oturum</div>
                </div>
            </div>
        </div>
        {{-- Duration buckets bar chart --}}
        @if($durBucketTotal > 0)
        <div class="row g-2 mb-3">
            @foreach($durBuckets as $label => $cnt)
            @php $bPct = round($cnt / $durBucketTotal * 100, 1); @endphp
            <div class="col text-center" style="min-width:70px">
                <div style="height:80px;display:flex;align-items:flex-end;justify-content:center">
                    <div class="bucket-bar" style="width:36px;height:{{ max(round($bPct * 0.8), 2) }}px;background:{{ $bPct > 35 ? '#6366f1' : '#a5b4fc' }}"></div>
                </div>
                <div style="font-size:13px;font-weight:700;color:#1e293b;margin-top:4px">{{ $cnt }}</div>
                <div style="font-size:10px;color:#64748b">{{ $label }}</div>
                <div style="font-size:10px;color:#94a3b8">%{{ $bPct }}</div>
            </div>
            @endforeach
        </div>
        @endif
        <div class="hr-divider"></div>
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px">
                        <th>Restoran</th>
                        <th class="text-end">Ort. Kalış</th>
                        <th class="text-end">Min</th>
                        <th class="text-end">Max</th>
                        <th class="text-end">σ</th>
                        <th class="text-end">Oturum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessionDurRaw as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->restaurant_name }}</td>
                        <td class="text-end">{{ $r->avg_min }} dk</td>
                        <td class="text-end text-muted">{{ $r->min_min }} dk</td>
                        <td class="text-end text-muted">{{ $r->max_min }} dk</td>
                        <td class="text-end">{{ $r->std_min }} dk</td>
                        <td class="text-end">{{ number_format($r->session_count) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($longestStayResto || $shortestStayResto)
        <div class="row g-3 mt-2">
            @if($longestStayResto)
            <div class="col-md-6">
                <div class="insight-box">
                    <strong>⏳ En Uzun Kalış:</strong> {{ $longestStayResto->restaurant_name }} — ortalama {{ $longestStayResto->avg_min }} dk. Yüksek bağlılık veya yavaş servis sinyali olabilir.
                </div>
            </div>
            @endif
            @if($shortestStayResto)
            <div class="col-md-6">
                <div class="insight-box warn">
                    <strong>⚡ En Kısa Kalış:</strong> {{ $shortestStayResto->restaurant_name }} — ortalama {{ $shortestStayResto->avg_min }} dk. Hızlı rotasyon avantajı veya erken ayrılma riski.
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 07 — Personel Performans Matrisi
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">07</div>
        <span class="ai-section-title">Personel Performans Matrisi</span>
        <span class="ai-section-subtitle">{{ $waiterCount }} aktif garson &bull; Ort. ₺{{ number_format($avgRevPerWaiter,0,'.',',') }} / garson</span>
    </div>
    <div class="ai-card-body">
        @if($topWaiter)
        <div class="insight-box success mb-3">
            <strong>🏅 En İyi Performans:</strong> {{ $topWaiter->waiter_name }} —
            ₺{{ number_format($topWaiter->revenue,0,'.',',') }} hasılat,
            {{ $topWaiter->orders_per_day }} sipariş/gün,
            ₺{{ number_format($topWaiter->rev_per_session,0,'.',',') }} / oturum,
            {{ $topWaiter->items_per_order }} kalem/sipariş
        </div>
        @endif
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px">
                        <th>#</th><th>Garson</th>
                        <th class="text-end">Hasılat</th>
                        <th class="text-end">Sipariş</th>
                        <th class="text-end">Oturum</th>
                        <th class="text-end">Sipariş/Gün</th>
                        <th class="text-end">₺/Oturum</th>
                        <th class="text-end">Kalem/Sipariş</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waiterStats as $i => $w)
                    <tr class="{{ $i === 0 ? 'waiter-rank-1' : ($i === 1 ? 'waiter-rank-2' : ($i === 2 ? 'waiter-rank-3' : '')) }}">
                        <td class="table-num-col">{{ $i+1 }}</td>
                        <td class="fw-semibold">{{ $w->waiter_name }}</td>
                        <td class="text-end">₺{{ number_format($w->revenue,0,'.',',') }}</td>
                        <td class="text-end">{{ number_format($w->order_count) }}</td>
                        <td class="text-end">{{ number_format($w->session_count) }}</td>
                        <td class="text-end" style="color:#6366f1;font-weight:600">{{ $w->orders_per_day }}</td>
                        <td class="text-end" style="color:#10b981;font-weight:600">₺{{ number_format($w->rev_per_session,0,'.',',') }}</td>
                        <td class="text-end" style="color:#f59e0b;font-weight:600">{{ $w->items_per_order }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 08 — Stratejik Öneriler
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">08</div>
        <span class="ai-section-title">Stratejik Öneriler & Eylem Planı</span>
        <span class="ai-section-subtitle">Öncelik sırasına göre</span>
    </div>
    <div class="ai-card-body">
        @php
            $recs = [];
            if($coeffVar > 65) $recs[] = ['high','Gelir Volatilitesi','CV %'.$coeffVar.' ile çok yüksek günlük dalgalanma gözlemlenmiştir. Rezarvasyon garantisi, minimum konsümüsyon veya sabit menü paketi uygulamaları ile gelir tabanı stabilize edilmelidir.'];
            if($hhiIndex > 5000) $recs[] = ['high','Portföy Yoğunlaşma Riski','HHI '.number_format($hhiIndex).' — gelirin aşırı tek noktada yoğunlaştığı görülmektedir. İkincil restoranları geliştiren promosyon stratejileri ile portföy dengeli yapıya kavuşturulmalıdır.'];
            if($paretoRatio > 40) $recs[] = ['med','Menü Optimizasyonu','Ürün portföyünün geniş olmasına rağmen hasılatın dağınık olduğu tespiti yapılmıştır. Düşük velocity\'li ürünlerin gözden geçirilmesi ve yıldız ürünlerin öne çıkarılması önerilmektedir.'];
            if($peakToAvgRatio > 3) $recs[] = ['med','Zirve Kapasitesi Yönetimi','Zirve-ortalama oranı '.$peakToAvgRatio.'× ile yüksek. Zirve saatlerde personel takviyesi ve rezervasyon optimizasyonu ile hizmet kalitesi korunabilir.'];
            if($freeQtyPct > 40) $recs[] = ['med','Ücretsiz Ürün Oranı','Toplam kalemlerin %'.$freeQtyPct.'si ücretsiz kategorisinde. Misafir memnuniyetini koruyarak seçici ücretsiz ürün politikası gözden geçirilmelidir.'];
            if($weeklyGrowth !== null && $weeklyGrowth < -10) $recs[] = ['high','Negatif Büyüme Alarmı','Son hafta bir önceki haftaya göre %'.abs($weeklyGrowth).' düşüş göstermiştir. Acil müdahale planı ve sebep analizi başlatılmalıdır.'];
            if($weeklyGrowth !== null && $weeklyGrowth > 15) $recs[] = ['ok','Pozitif Büyüme Momenti','Son hafta %'.$weeklyGrowth.' büyüme kaydedilmiştir. Bu momentumu korumak için başarılı stratejiler belgelenmeli ve ölçeklendirilmelidir.'];
            if($zeroRevDays > 3) $recs[] = ['med','Kapalı/Veri Eksik Günler','Dönemde '.$zeroRevDays.' sıfır hasılatlı gün mevcuttur. Bu günlerin operasyonel kapalılık mı yoksa veri eksikliği mi olduğu netleştirilmelidir.'];
            if(count($recs) === 0) $recs[] = ['ok','Operasyonel Denge','Seçilen dönemde tüm göstergeler kabul edilebilir aralıklarda bulunmaktadır. Mevcut stratejinin sürdürülmesi ve düzenli izleme yapılması önerilmektedir.'];
        @endphp
        @foreach($recs as $rec)
        <div class="rec-card rec-priority-{{ $rec[0] }}">
            <div class="d-flex align-items-start gap-2">
                <span style="font-size:16px;line-height:1.2">{{ $rec[0]==='high' ? '🔴' : ($rec[0]==='med' ? '🟡' : '🟢') }}</span>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#1e293b">{{ $rec[1] }}</div>
                    <div style="font-size:12px;color:#475569;margin-top:3px">{{ $rec[2] }}</div>
                </div>
                <div class="ms-auto">
                    <span class="chapter-badge">{{ strtoupper($rec[0]==='high' ? 'Yüksek' : ($rec[0]==='med' ? 'Orta' : 'İyi')) }} Öncelik</span>
                </div>
            </div>
        </div>
        @endforeach
        <div class="insight-box mt-3" style="font-size:11px;color:#64748b">
            <strong>Not:</strong> Bu öneriler {{ $dateFrom }} – {{ $dateTo }} dönemindeki {{ $dayCount }} günlük veriden otomatik olarak türetilmiştir.
        </div>
    </div>
</div>

@endif {{-- end data check --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const dRevLabels = @json($dailyRevRaw->keys());
    const dRevVals   = @json($dailyRevRaw->values());
    const dOrdVals   = @json($dailyOrderCountRaw->values());

    new Chart(document.getElementById('dailyRevenueChart'), {
        type:'line',
        data:{
            labels:dRevLabels,
            datasets:[
                {label:'Hasılat (₺)',data:dRevVals,borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,.1)',fill:true,tension:.35,pointRadius:2,borderWidth:2,yAxisID:'y'},
                {label:'Sipariş Sayısı',data:dOrdVals,borderColor:'#f59e0b',backgroundColor:'transparent',fill:false,tension:.35,pointRadius:2,borderWidth:1.5,borderDash:[4,3],yAxisID:'y1'}
            ]
        },
        options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{font:{size:11}}}},scales:{
            x:{ticks:{font:{size:10},maxTicksLimit:20}},
            y:{position:'left',ticks:{font:{size:10},callback:v=>'₺'+v.toLocaleString('tr-TR')}},
            y1:{position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:10}}}
        }}
    });

    const hLabels = Array.from({length:24},(_,i)=>i+':00');
    const hOrders = @json(array_values($hourlyData->toArray()));
    const hRev    = @json(array_values($hourlyRevData->toArray()));
    new Chart(document.getElementById('hourlyChart'), {
        type:'bar',
        data:{labels:hLabels,datasets:[
            {label:'Sipariş',data:hOrders,backgroundColor:'rgba(99,102,241,.7)',borderRadius:3,yAxisID:'y'},
            {label:'Hasılat (₺)',data:hRev,type:'line',borderColor:'#10b981',backgroundColor:'transparent',fill:false,tension:.4,pointRadius:2,borderWidth:2,yAxisID:'y1'}
        ]},
        options:{responsive:true,plugins:{legend:{labels:{font:{size:10}}}},scales:{
            x:{ticks:{font:{size:9}}},
            y:{ticks:{font:{size:9}}},
            y1:{position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:9},callback:v=>'₺'+v.toLocaleString()}}
        }}
    });

    const wdLabels = @json($weekdayRevData->keys()->toArray());
    const wdRev    = @json($weekdayRevData->values()->toArray());
    new Chart(document.getElementById('weekdayChart'), {
        type:'bar',
        data:{labels:wdLabels,datasets:[{label:'Hasılat (₺)',data:wdRev,backgroundColor:'rgba(139,92,246,.75)',borderRadius:4}]},
        options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{
            x:{ticks:{font:{size:9},callback:v=>'₺'+v.toLocaleString()}},
            y:{ticks:{font:{size:10}}}
        }}
    });
})();

function setRange(days){
    const to=new Date();const from=new Date();from.setDate(from.getDate()-days+1);
    const fmt=d=>d.toISOString().slice(0,10);
    document.querySelector('[name=date_from]').value=fmt(from);
    document.querySelector('[name=date_to]').value=fmt(to);
}
</script>
@endpush
@endsection
