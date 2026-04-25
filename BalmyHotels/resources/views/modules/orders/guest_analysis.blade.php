@extends('layouts.default')
@section('content')
<style>
.ai-section-header{background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;border-radius:10px 10px 0 0;padding:14px 20px;display:flex;align-items:center;gap:10px}
.ai-section-num{min-width:32px;height:32px;border-radius:50%;background:rgba(20,184,166,.25);border:1.5px solid rgba(20,184,166,.7);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#5eead4}
.ai-section-title{font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.ai-section-subtitle{font-size:11px;color:#94a3b8;margin-left:auto}
.ai-card{background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:22px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.ai-card-body{padding:18px 20px}
.kpi-card{border-radius:10px;padding:16px 18px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,.05)}
.kpi-value{font-size:22px;font-weight:700;line-height:1;margin-top:4px}
.kpi-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:2px}
.kpi-delta{font-size:11px;margin-top:6px}
.insight-box{background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #14b8a6;border-radius:0 8px 8px 0;padding:12px 16px;font-size:12px;line-height:1.6}
.insight-box.warn{border-left-color:#f59e0b}
.insight-box.danger{border-left-color:#ef4444}
.insight-box.success{border-left-color:#10b981}
.stat-callout{background:linear-gradient(135deg,#f0fdfa,#f8fafc);border:1px solid #99f6e4;border-radius:8px;padding:10px 14px;text-align:center}
.stat-callout .val{font-size:20px;font-weight:700;color:#0d9488}
.stat-callout .lbl{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.hr-divider{border:none;border-top:1px solid #f1f5f9;margin:14px 0}
.table-num-col{font-weight:600;color:#0d9488;width:36px;text-align:center}
.bucket-bar{border-radius:3px;transition:height .4s}
.rec-card{border-radius:8px;padding:14px 16px;margin-bottom:10px;border-left:4px solid}
.rec-priority-high{background:#fff5f5;border-left-color:#ef4444}
.rec-priority-med{background:#fffbeb;border-left-color:#f59e0b}
.rec-priority-ok{background:#f0fdf4;border-left-color:#10b981}
.chapter-badge{display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;color:#475569}
.waiter-rank-1{background:linear-gradient(to right,#f0fdfa,#fff)}
.waiter-rank-2{background:linear-gradient(to right,#f0f9ff,#fff)}
.waiter-rank-3{background:linear-gradient(to right,#f0fdf4,#fff)}
</style>
<div class="container-fluid">

{{-- Başlık --}}
<div class="row page-titles mx-0">
    <div class="col-sm-6 p-md-0">
        <div class="welcome-text">
            <h4 class="d-flex align-items-center gap-2">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#14b8a6,#0d9488);color:#fff;font-size:16px;font-weight:800;flex-shrink:0;">👥</span>
                Misafir Tüketim Analizi
            </h4>
        </div>
    </div>
    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
            <li class="breadcrumb-item active">Misafir Analizi</li>
        </ol>
    </div>
</div>

{{-- Filtre --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.guest-analysis') }}" class="row g-3 align-items-end">
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
                <button type="submit" class="btn btn-sm w-100" style="background:#0d9488;color:#fff">
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

@if($totalSessions == 0 && $totalOrders == 0)
<div style="text-align:center;padding:40px 20px;color:#94a3b8"><h5>Seçilen aralıkta veri bulunamadı.</h5><p class="mb-0">Lütfen filtrelerinizi genişletin.</p></div>
@else

{{-- ═══════════════════════════════════════════════════════════
     SECTION 01 — Misafir Hacmi Özeti
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">01</div>
        <span class="ai-section-title">Misafir Hacmi Özeti</span>
        <span class="ai-section-subtitle">{{ $dateFrom }} → {{ $dateTo }} &bull; {{ $dayCount }} gün</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #14b8a6">
                    <div class="kpi-label">Toplam Oturum</div>
                    <div class="kpi-value" style="color:#0d9488">{{ number_format($totalSessions) }}</div>
                    <div class="kpi-delta text-muted">{{ $activeDays }} aktif gün</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #6366f1">
                    <div class="kpi-label">Toplam Sipariş</div>
                    <div class="kpi-value" style="color:#4f46e5">{{ number_format($totalOrders) }}</div>
                    <div class="kpi-delta text-muted">Ort. {{ $avgOrdersPerDay }} / gün</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #f59e0b">
                    <div class="kpi-label">Toplam Tüketim (adet)</div>
                    <div class="kpi-value" style="color:#d97706">{{ number_format($totalQty) }}</div>
                    <div class="kpi-delta text-muted">{{ $avgQtyPerSession }} adet / oturum</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" style="border-top:3px solid #8b5cf6">
                    <div class="kpi-label">Sipariş Yoğunluğu</div>
                    <div class="kpi-value" style="color:#7c3aed">{{ $sessionIntensity }}×</div>
                    <div class="kpi-delta text-muted">sipariş / oturum</div>
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
                    <div class="val" style="color:#0d9488">{{ $peakDayQty }}</div>
                    <div class="lbl">Zirve Gün Tüketimi</div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">{{ $peakDayDate }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-callout" style="background:linear-gradient(135deg,{{ $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#f0fdf4,#fff' : '#fff5f5,#fff' }})">
                    <div class="val" style="color:{{ $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#16a34a' : '#dc2626' }}">
                        @if($weeklyGrowth !== null) {{ $weeklyGrowth >= 0 ? '+' : '' }}%{{ $weeklyGrowth }} @else — @endif
                    </div>
                    <div class="lbl">Haftalık Tüketim Büyümesi</div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Son 7 vs önceki 7 gün</div>
                </div>
            </div>
        </div>
        @if($zeroQtyDays > 0)
        <div class="insight-box warn mt-3">
            <strong>Dikkat:</strong> {{ $dayCount }} günlük dönemde <strong>{{ $zeroQtyDays }} gün</strong> tüketim verisi sıfır. Operasyonel kapalılık veya veri eksikliği olabileceği değerlendirilmelidir.
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 02 — Tüketim Trendi
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">02</div>
        <span class="ai-section-title">Günlük Tüketim Trendi</span>
        <span class="ai-section-subtitle">Kalem & sipariş akışı</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="insight-box success">
                    <strong>Zirve Gün:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#15803d">{{ number_format($peakDayQty) }} kalem</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $peakDayDate }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box danger">
                    <strong>En Düşük Gün:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#dc2626">{{ number_format($lowestDayQty) }} kalem</span><br>
                    <span style="font-size:11px;color:#64748b">{{ $lowestDayDate }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box">
                    <strong>Önceki Hafta:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:#4f46e5">{{ number_format($week1Qty) }} kalem</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="insight-box {{ isset($weeklyGrowth) && $weeklyGrowth !== null && $weeklyGrowth >= 0 ? 'success' : 'danger' }}">
                    <strong>Son Hafta:</strong><br>
                    <span style="font-size:15px;font-weight:700;color:{{ isset($weeklyGrowth) && $weeklyGrowth !== null && $weeklyGrowth >= 0 ? '#15803d' : '#dc2626' }}">{{ number_format($week2Qty) }} kalem</span>
                </div>
            </div>
        </div>
        <canvas id="dailyQtyChart" height="90"></canvas>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 03 — Restoran Dağılımı
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">03</div>
        <span class="ai-section-title">Restoran Bazında Dağılım</span>
        <span class="ai-section-subtitle">Hacim metrikleri</span>
    </div>
    <div class="ai-card-body">
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#64748b">
                        <th>#</th><th>Restoran</th>
                        <th class="text-end">Tüketim (adet)</th>
                        <th class="text-end">Pay %</th>
                        <th class="text-end">Oturum</th>
                        <th class="text-end">Kalem/Oturum</th>
                        <th class="text-end">Sipariş/Gün</th>
                        <th class="text-end">Yoğunluk</th>
                        <th class="text-end">Ürün Çeşidi</th>
                        <th style="min-width:120px">Pay Çubuğu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($restoStats as $i => $r)
                    @php $share = $totalQty > 0 ? round($r->qty / $totalQty * 100, 1) : 0; @endphp
                    <tr>
                        <td class="table-num-col">{{ $i+1 }}</td>
                        <td class="fw-semibold">{{ $r->restaurant_name }}</td>
                        <td class="text-end fw-semibold">{{ number_format($r->qty) }}</td>
                        <td class="text-end">%{{ $share }}</td>
                        <td class="text-end">{{ number_format($r->sessions) }}</td>
                        <td class="text-end" style="color:#0d9488;font-weight:600">{{ $r->qty_per_session }}</td>
                        <td class="text-end" style="color:#6366f1;font-weight:600">{{ $r->orders_per_day }}</td>
                        <td class="text-end">{{ $r->intensity }}×</td>
                        <td class="text-end text-muted">{{ $r->distinct_items }}</td>
                        <td>
                            <div style="background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden">
                                <div style="height:8px;border-radius:4px;width:{{ $share }}%;background:#14b8a6"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($topResto)
        <div class="insight-box success mt-3">
            <strong>🏆 En Yüksek Tüketim:</strong> {{ $topResto->restaurant_name }} — {{ number_format($topResto->qty) }} adet, {{ $topResto->qty_per_session }} kalem/oturum
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 04 — Tüketim Profili
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">04</div>
        <span class="ai-section-title">Tüketim Profili — En Çok Talep Gören Ürünler</span>
        <span class="ai-section-subtitle">{{ $distinctItems }} farklı ürün sınıfı</span>
    </div>
    <div class="ai-card-body">
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px">
                        <th>#</th><th>Ürün</th>
                        <th class="text-end">Toplam Adet</th>
                        <th class="text-end">Velocity/gün</th>
                        <th class="text-end">Oturum Sayısı</th>
                        <th class="text-end">Satış Günü</th>
                        <th style="min-width:140px">Talep Çubuğu</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxQty = $topProducts->max('qty') ?: 1; @endphp
                    @foreach($topProducts as $i => $p)
                    <tr @if($i < 3) style="background:linear-gradient(to right,{{ ['#f0fdfa','#f0f9ff','#f0fdf4'][$i] }},#fff)" @endif>
                        <td class="table-num-col">{{ $i+1 }}</td>
                        <td class="fw-semibold">{{ Str::limit($p->item_name, 36) }}</td>
                        <td class="text-end fw-semibold" style="color:#1e293b">{{ number_format($p->qty) }}</td>
                        <td class="text-end" style="color:#0d9488;font-weight:600">{{ $p->velocity }}</td>
                        <td class="text-end text-muted">{{ number_format($p->session_count) }}</td>
                        <td class="text-end text-muted">{{ $p->active_days_sold }}</td>
                        <td>
                            <div style="background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden">
                                <div style="height:8px;border-radius:4px;width:{{ round($p->qty/$maxQty*100) }}%;background:#14b8a6;"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 05 — Zaman Örüntüleri
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">05</div>
        <span class="ai-section-title">Zaman Örüntüleri</span>
        <span class="ai-section-subtitle">Saatlik & günlük trafik</span>
    </div>
    <div class="ai-card-body">
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
                <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Saatlik Sipariş & Tüketim</div>
                <canvas id="hourlyChart" height="80"></canvas>
            </div>
            <div class="col-md-4">
                <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Haftanın Günleri (Sipariş)</div>
                <canvas id="weekdayChart" height="160"></canvas>
                <div class="insight-box mt-2">
                    <strong>En Yoğun:</strong> {{ $busiestDay }} &bull; <strong>En Sakin:</strong> {{ $quietestDay }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 06 — Masa Verimliliği
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">06</div>
        <span class="ai-section-title">Masa Kalış Süresi & Verimlilik</span>
        <span class="ai-section-subtitle">σ = {{ $overallStdDur }} dk &bull; CV = %{{ $durCoeffVar }}</span>
    </div>
    <div class="ai-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="stat-callout">
                    <div class="val" style="color:#0d9488">{{ $overallAvgDur }} dk</div>
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
        @if($durBucketTotal > 0)
        <div class="row g-2 mb-3">
            @foreach($durBuckets as $label => $cnt)
            @php $bPct = round($cnt / $durBucketTotal * 100, 1); @endphp
            <div class="col text-center" style="min-width:70px">
                <div style="height:80px;display:flex;align-items:flex-end;justify-content:center">
                    <div class="bucket-bar" style="width:36px;height:{{ max(round($bPct * 0.8), 2) }}px;background:{{ $bPct > 35 ? '#14b8a6' : '#99f6e4' }}"></div>
                </div>
                <div style="font-size:13px;font-weight:700;color:#1e293b;margin-top:4px">{{ $cnt }}</div>
                <div style="font-size:10px;color:#64748b">{{ $label }}</div>
                <div style="font-size:10px;color:#94a3b8">%{{ $bPct }}</div>
            </div>
            @endforeach
        </div>
        @endif
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
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 07 — Personel Etkinliği
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">07</div>
        <span class="ai-section-title">Personel Etkinlik Matrisi</span>
        <span class="ai-section-subtitle">{{ $waiterCount }} aktif garson &bull; Ort. {{ $avgOrdersPerWaiter }} sipariş/garson</span>
    </div>
    <div class="ai-card-body">
        @if($topWaiter)
        <div class="insight-box success mb-3">
            <strong>🏅 En Etkin Garson:</strong> {{ $topWaiter->waiter_name }} —
            {{ number_format($topWaiter->order_count) }} sipariş,
            {{ $topWaiter->orders_per_day }} sipariş/gün,
            {{ $topWaiter->qty_per_session }} kalem/oturum,
            {{ $topWaiter->items_per_order }} kalem/sipariş
        </div>
        @endif
        <div style="overflow-x:auto">
            <table class="table table-sm mb-0" style="font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px">
                        <th>#</th><th>Garson</th>
                        <th class="text-end">Sipariş</th>
                        <th class="text-end">Oturum</th>
                        <th class="text-end">Tüketim (adet)</th>
                        <th class="text-end">Sipariş/Gün</th>
                        <th class="text-end">Kalem/Oturum</th>
                        <th class="text-end">Kalem/Sipariş</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waiterStats as $i => $w)
                    <tr class="{{ $i === 0 ? 'waiter-rank-1' : ($i === 1 ? 'waiter-rank-2' : ($i === 2 ? 'waiter-rank-3' : '')) }}">
                        <td style="font-weight:600;color:#0d9488;width:36px;text-align:center">{{ $i+1 }}</td>
                        <td class="fw-semibold">{{ $w->waiter_name }}</td>
                        <td class="text-end">{{ number_format($w->order_count) }}</td>
                        <td class="text-end">{{ number_format($w->session_count) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($w->item_qty) }}</td>
                        <td class="text-end" style="color:#6366f1;font-weight:600">{{ $w->orders_per_day }}</td>
                        <td class="text-end" style="color:#0d9488;font-weight:600">{{ $w->qty_per_session }}</td>
                        <td class="text-end" style="color:#f59e0b;font-weight:600">{{ $w->items_per_order }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 08 — Operasyonel Öneriler
═══════════════════════════════════════════════════════════ --}}
<div class="ai-card">
    <div class="ai-section-header">
        <div class="ai-section-num">08</div>
        <span class="ai-section-title">Operasyonel Öneriler</span>
        <span class="ai-section-subtitle">Öncelik sırasına göre</span>
    </div>
    <div class="ai-card-body">
        @php
            $recs = [];
            if($coeffVar > 65) $recs[] = ['high','Tüketim Dalgalanması','CV %'.$coeffVar.' ile günlük tüketimde yüksek volatilite gözlemlenmektedir. Kapasite planlaması ve stok yönetiminin esnekleştirilmesi önceliklidir.'];
            if($sessionIntensity < 1.5) $recs[] = ['med','Sipariş Yoğunluğu Düşük','Sipariş yoğunluğu '.$sessionIntensity.'× — misafir başına düşen sipariş sayısı artırılabilir. Masa ziyareti sıklığı ve ikram önerileri gözden geçirilmelidir.'];
            if($weeklyGrowth !== null && $weeklyGrowth < -10) $recs[] = ['high','Tüketim Düşüşü','Son hafta bir önceki haftaya göre %'.abs($weeklyGrowth).' düşüş. Memnuniyet değerlendirmesi ve operasyonel inceleme önerilir.'];
            if($weeklyGrowth !== null && $weeklyGrowth > 15) $recs[] = ['ok','Pozitif Tüketim Trendi','%'.$weeklyGrowth.' büyüme kaydedildi. Bu momentumu koruyacak operasyonel iyileştirmeler belgelenmelidir.'];
            if($zeroQtyDays > 3) $recs[] = ['med','Veri Eksikliği / Kapalı Günler',$zeroQtyDays.' gün tüketim verisi sıfır. Bu günlerin planlandı izin mi yoksa veri kaybı mı olduğu netleştirilmelidir.'];
            if($avgQtyPerSession < 3) $recs[] = ['med','Düşük Tüketim/Oturum','Oturum başına ortalama '.$avgQtyPerSession.' kalem oldukça düşük. Misafir rehberliği ve ikram sunumu gözden geçirilmelidir.'];
            if(count($recs) === 0) $recs[] = ['ok','Operasyonel Denge','Seçilen dönemde tüm hacim göstergeleri kabul edilebilir aralıklarda. İzlemeye devam önerilmektedir.'];
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
            <strong>Not:</strong> Bu öneriler {{ $dateFrom }} – {{ $dateTo }} dönemindeki {{ $dayCount }} günlük veriden otomatik olarak türetilmiştir. Bu raporda fiyat ve hasılat bilgisi yer almamaktadır.
        </div>
    </div>
</div>

@endif {{-- end data check --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const dQtyLabels = @json($dailyQtyRaw->keys());
    const dQtyVals   = @json($dailyQtyRaw->values());
    const dOrdVals   = @json($dailyOrderCountRaw->values());
    new Chart(document.getElementById('dailyQtyChart'), {
        type:'line',
        data:{labels:dQtyLabels,datasets:[
            {label:'Tüketim (adet)',data:dQtyVals,borderColor:'#14b8a6',backgroundColor:'rgba(20,184,166,.1)',fill:true,tension:.35,pointRadius:2,borderWidth:2,yAxisID:'y'},
            {label:'Sipariş Sayısı',data:dOrdVals,borderColor:'#6366f1',backgroundColor:'transparent',fill:false,tension:.35,pointRadius:2,borderWidth:1.5,borderDash:[4,3],yAxisID:'y1'}
        ]},
        options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{font:{size:11}}}},scales:{
            x:{ticks:{font:{size:10},maxTicksLimit:20}},
            y:{position:'left',ticks:{font:{size:10}}},
            y1:{position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:10}}}
        }}
    });

    const hLabels = Array.from({length:24},(_,i)=>i+':00');
    const hOrders = @json(array_values($hourlyData->toArray()));
    const hQty    = @json(array_values($hourlyQtyData->toArray()));
    new Chart(document.getElementById('hourlyChart'), {
        type:'bar',
        data:{labels:hLabels,datasets:[
            {label:'Sipariş',data:hOrders,backgroundColor:'rgba(99,102,241,.7)',borderRadius:3,yAxisID:'y'},
            {label:'Tüketim (adet)',data:hQty,type:'line',borderColor:'#14b8a6',backgroundColor:'transparent',fill:false,tension:.4,pointRadius:2,borderWidth:2,yAxisID:'y1'}
        ]},
        options:{responsive:true,plugins:{legend:{labels:{font:{size:10}}}},scales:{
            x:{ticks:{font:{size:9}}},
            y:{ticks:{font:{size:9}}},
            y1:{position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:9}}}
        }}
    });

    const wdLabels = @json($weekdayData->keys()->toArray());
    const wdData   = @json($weekdayData->values()->toArray());
    new Chart(document.getElementById('weekdayChart'), {
        type:'bar',
        data:{labels:wdLabels,datasets:[{label:'Sipariş',data:wdData,backgroundColor:'rgba(20,184,166,.75)',borderRadius:4}]},
        options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{
            x:{ticks:{font:{size:9}}},
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
