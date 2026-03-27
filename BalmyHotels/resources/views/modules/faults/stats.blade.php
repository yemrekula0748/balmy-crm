@extends('layouts.default')

@section('title', 'Arıza İstatistikleri')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
.tw-stat-card{background:#fff;border-radius:.875rem;border:1px solid #e8ecf0;box-shadow:0 1px 6px rgba(15,23,42,.06)}
.tw-table-head th{font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.6rem 1rem;border-bottom:2px solid #f1f5f9}
.tw-table-body td{padding:.55rem 1rem;font-size:.8rem;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.kpi-pill{display:inline-flex;align-items:center;gap:4px;font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:99px}
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line text-primary" style="font-size:1rem"></i>
                    Arıza İstatistikleri
                </h4>
                <span class="text-muted" style="font-size:.8rem">Performans · KPI · SLA Analizi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">İstatistikler</li>
            </ol>
        </div>
    </div>

    {{-- Dönem Filtresi --}}
    <div class="tw-stat-card mb-3 mt-2 px-4 py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted fw-semibold" style="font-size:.78rem"><i class="fas fa-calendar-alt me-1"></i>Dönem:</span>
            @foreach(['7'=>'Son 7 Gün','30'=>'Son 30 Gün','90'=>'Son 90 Gün','365'=>'Son 1 Yıl','all'=>'Tüm Zamanlar'] as $val=>$lbl)
            <a href="{{ route('faults.stats', ['period'=>$val]) }}"
               class="btn btn-sm rounded-pill {{ $period === $val ? 'btn-dark' : 'btn-outline-secondary' }}"
               style="font-size:.72rem;padding:3px 12px">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 1: KPI KART SATIRI
    ════════════════════════════════════════════════════ --}}
    @php
    $kpiCards = [
        ['label'=>'TOPLAM ARIZA',  'value'=>$summary['total'],
         'sub'=>'Seçili dönemde',   'color'=>'#6366f1','bg'=>'rgba(99,102,241,.08)','icon'=>'fa-layer-group'],
        ['label'=>'AÇIK / ACİL',   'value'=>$summary['open'],
         'sub'=>($summary['total'] > 0 ? round($summary['open']/$summary['total']*100) : 0).'% oran',
         'color'=>'#ef4444','bg'=>'rgba(239,68,68,.08)','icon'=>'fa-circle-exclamation'],
        ['label'=>'İŞLEMDE',       'value'=>$summary['in_progress'],
         'sub'=>'Aktif müdahale',    'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.08)','icon'=>'fa-rotate'],
        ['label'=>'KAPALI',        'value'=>$summary['closed'],
         'sub'=>($summary['total'] > 0 ? round($summary['closed']/$summary['total']*100) : 0).'% kapanma',
         'color'=>'#10b981','bg'=>'rgba(16,185,129,.08)','icon'=>'fa-circle-check'],
        ['label'=>'ORT. ÇÖZÜM',    'value'=>$summary['avg_hours'] !== null ? $summary['avg_hours'].' s' : '—',
         'sub'=>'Yanıt süresi',      'color'=>'#0891b2','bg'=>'rgba(8,145,178,.08)','icon'=>'fa-hourglass-half'],
        ['label'=>'SLA UYUM',      'value'=>$summary['sla_pct'] !== null ? '%'.$summary['sla_pct'] : '—',
         'sub'=>($summary['sla_pct'] >= 80 ? 'Hedef karşılandı' : ($summary['sla_pct'] >= 50 ? 'İyileştirme gerekli' : 'Kritik seviye')),
         'color'=>$summary['sla_pct'] >= 80 ? '#10b981' : ($summary['sla_pct'] >= 50 ? '#f59e0b' : '#ef4444'),
         'bg'=>$summary['sla_pct'] >= 80 ? 'rgba(16,185,129,.08)' : ($summary['sla_pct'] >= 50 ? 'rgba(245,158,11,.08)' : 'rgba(239,68,68,.08)'),
         'icon'=>'fa-shield-halved'],
    ];
    @endphp
    <div class="row g-2 mb-3">
        @foreach($kpiCards as $card)
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="tw-stat-card p-3" style="border-left:3px solid {{ $card['color'] }}">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div style="width:36px;height:36px;border-radius:9px;background:{{ $card['bg'] }};display:flex;align-items:center;justify-content:center">
                        <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }};font-size:.9rem"></i>
                    </div>
                </div>
                <div class="fw-bold lh-1 mb-1" style="font-size:1.5rem;color:#1e293b">{{ $card['value'] }}</div>
                <div style="font-size:.62rem;font-weight:700;letter-spacing:.06em;color:{{ $card['color'] }};text-transform:uppercase">{{ $card['label'] }}</div>
                <div class="text-muted mt-1" style="font-size:.68rem">{{ $card['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 2: AYLИК TREND + ÖNCELİK DAĞILIMI
    ════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">
        @if($monthlyTrend->count())
        <div class="col-xl-8">
            <div class="tw-stat-card">
                <div class="d-flex align-items-center gap-2 px-4 py-3" style="border-bottom:1px solid #f1f5f9">
                    <i class="fas fa-chart-bar" style="color:#6366f1"></i>
                    <span class="fw-semibold" style="font-size:.85rem;color:#1e293b">Aylık Arıza Trendi</span>
                    <span class="ms-auto text-muted" style="font-size:.72rem">Son {{ $monthlyTrend->count() }} ay</span>
                </div>
                <div class="p-4" style="position:relative;height:220px">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        @endif
        @if($priorityStats->count())
        <div class="col-xl-4">
            <div class="tw-stat-card h-100">
                <div class="d-flex align-items-center gap-2 px-4 py-3" style="border-bottom:1px solid #f1f5f9">
                    <i class="fas fa-flag" style="color:#ef4444"></i>
                    <span class="fw-semibold" style="font-size:.85rem;color:#1e293b">Öncelik Dağılımı</span>
                </div>
                <div class="p-4 position-relative" style="height:220px">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 3: DEPARTMAN SCOREBOARD + PERFORMANS ANALİZİ
    ════════════════════════════════════════════════════ --}}
    @if($deptScoreboard->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#1e293b">
            <i class="fas fa-trophy" style="color:#f59e0b"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Departman Performans Analizi — Scoreboard</span>
            <span class="ms-auto text-white opacity-50" style="font-size:.72rem">SLA hedef: ≥80% = iyi · ≥50% = orta · &lt;50% = kritik</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr class="tw-table-head">
                    <th>#</th>
                    <th>Departman</th>
                    <th class="text-center">Toplam</th>
                    <th class="text-center">Açık</th>
                    <th class="text-center">İşlemde</th>
                    <th class="text-center">Kapalı</th>
                    <th class="text-end">Ort. Çözüm</th>
                    <th class="text-end">SLA %</th>
                    <th class="text-end">SLA Skor</th>
                </tr></thead>
                <tbody>
                    @foreach($deptScoreboard as $i => $row)
                    @php
                        $slaBg = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                        $slaFg = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                        $slaIcon = $row['sla_pct'] >= 80 ? 'fa-circle-check' : ($row['sla_pct'] >= 50 ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
                        $deptColor = $row['dept']?->color ?? '#6366f1';
                    @endphp
                    <tr style="{{ $i===0 ? 'background:#fffbeb' : '' }}">
                        <td class="tw-table-body" style="color:#94a3b8;font-weight:700">
                            @if($i===0)<i class="fas fa-crown" style="color:#f59e0b"></i>
                            @else{{ $i+1 }}@endif
                        </td>
                        <td class="tw-table-body">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle flex-shrink-0" style="width:9px;height:9px;background:{{ $deptColor }};display:inline-block"></span>
                                <span class="fw-semibold" style="color:#1e293b">{{ $row['dept']?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="tw-table-body text-center fw-bold" style="color:#1e293b">{{ $row['total'] }}</td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#fffbeb;color:#d97706">{{ $row['in_progress'] }}</span>
                        </td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                        <td class="tw-table-body text-end" style="color:#64748b">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].' s' : '—' }}
                        </td>
                        <td class="tw-table-body text-end">
                            @if($row['sla_pct'] !== null)
                            <span class="kpi-pill" style="background:{{ $slaBg }};color:{{ $slaFg }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else<span class="text-muted">—</span>@endif
                        </td>
                        <td class="tw-table-body text-end">
                            @if($row['sla_pct'] !== null)
                            <i class="fas {{ $slaIcon }}" style="color:{{ $slaFg }};font-size:.8rem"></i>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 4: DEPARTMAN AYLIK ÇÖZÜM SÜRESİ
    ════════════════════════════════════════════════════ --}}
    @if($deptMonthlyResolution->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#1e40af">
            <i class="fas fa-clock" style="color:#bfdbfe"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Departman Aylık Çözüm Süresi (Son 12 Ay)</span>
        </div>
        <div class="p-4" style="position:relative;height:280px">
            <canvas id="deptMonthlyChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 5: ARIZA TÜRÜ KPI + SLA
    ════════════════════════════════════════════════════ --}}
    @if($typeStats->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#065f46">
            <i class="fas fa-tags" style="color:#6ee7b7"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Arıza Türü KPI & SLA Analizi</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr class="tw-table-head">
                    <th>Arıza Türü</th>
                    <th class="text-center">SLA Hedef</th>
                    <th class="text-center">Toplam</th>
                    <th class="text-center">Açık</th>
                    <th class="text-center">Ort. Çözüm</th>
                    <th class="text-center">SLA %</th>
                    <th>SLA Bar</th>
                </tr></thead>
                <tbody>
                    @foreach($typeStats as $row)
                    @php
                        $slaBg2 = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                        $slaFg2 = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                        $slaBar = $row['sla_pct'] >= 80 ? '#10b981' : ($row['sla_pct'] >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td class="tw-table-body fw-semibold" style="color:#1e293b">{{ $row['type_name'] }}</td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#f1f5f9;color:#64748b">
                                <i class="fas fa-clock" style="font-size:.55rem"></i>{{ $row['target_hours'] }}s
                            </span>
                        </td>
                        <td class="tw-table-body text-center fw-bold" style="color:#1e293b">{{ $row['total'] }}</td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="tw-table-body text-center" style="color:#64748b">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].'s' : '—' }}
                            @if($row['avg_hours'] !== null && $row['avg_hours'] > $row['target_hours'])
                            <i class="fas fa-exclamation-triangle text-warning ms-1" style="font-size:.65rem" title="SLA aşımı"></i>
                            @endif
                        </td>
                        <td class="tw-table-body text-center">
                            @if($row['sla_pct'] !== null)
                            <span class="kpi-pill" style="background:{{ $slaBg2 }};color:{{ $slaFg2 }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else<span class="text-muted">—</span>@endif
                        </td>
                        <td class="tw-table-body" style="min-width:100px">
                            @if($row['sla_pct'] !== null)
                            <div style="background:#f1f5f9;border-radius:99px;height:6px;overflow:hidden">
                                <div style="width:{{ $row['sla_pct'] }}%;height:100%;background:{{ $slaBar }};border-radius:99px"></div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 6: DETAYLI KONUM İSTATİSTİKLERİ
    ════════════════════════════════════════════════════ --}}
    @if($locationTypeStats->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#134e4a">
            <i class="fas fa-map-marker-alt" style="color:#5eead4"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Detaylı Konum İstatistikleri</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr class="tw-table-head">
                    <th>Konum</th>
                    <th class="text-center">Toplam Arıza</th>
                    <th class="text-center">Açık Arıza</th>
                    <th class="text-center">Kapalı Arıza</th>
                    <th>En Sık Arıza Türü</th>
                    <th>Dağılım</th>
                </tr></thead>
                <tbody>
                    @php $maxLoc = $locationTypeStats->max('total') ?: 1; @endphp
                    @foreach($locationTypeStats as $row)
                    <tr>
                        <td class="tw-table-body fw-semibold" style="color:#1e293b">{{ $row['location'] }}</td>
                        <td class="tw-table-body text-center fw-bold" style="color:#1e293b">{{ $row['total'] }}</td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                        <td class="tw-table-body" style="color:#64748b;max-width:180px">
                            <span class="text-truncate d-block" title="{{ $row['top_type'] }}">{{ $row['top_type'] }}</span>
                        </td>
                        <td class="tw-table-body" style="min-width:80px">
                            <div style="background:#f1f5f9;border-radius:99px;height:5px;overflow:hidden">
                                <div style="width:{{ round($row['total']/$maxLoc*100) }}%;height:100%;background:#0891b2;border-radius:99px"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 7: DETAYLI ALAN İSTATİSTİKLERİ
    ════════════════════════════════════════════════════ --}}
    @if($areaTypeStats->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#4c1d95">
            <i class="fas fa-door-open" style="color:#c4b5fd"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Detaylı Alan İstatistikleri</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr class="tw-table-head">
                    <th>Alan</th>
                    <th>Konum</th>
                    <th class="text-center">Toplam</th>
                    <th class="text-center">Açık</th>
                    <th class="text-center">Kapalı</th>
                    <th>En Sık Arıza Türü</th>
                </tr></thead>
                <tbody>
                    @foreach($areaTypeStats as $row)
                    <tr>
                        <td class="tw-table-body fw-semibold" style="color:#1e293b">{{ $row['area'] }}</td>
                        <td class="tw-table-body" style="color:#64748b">{{ $row['location'] }}</td>
                        <td class="tw-table-body text-center fw-bold" style="color:#1e293b">{{ $row['total'] }}</td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="tw-table-body text-center">
                            <span class="kpi-pill" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                        <td class="tw-table-body" style="color:#64748b">{{ $row['top_type'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         BÖLÜM 8: ŞUBE KARŞILAŞTIRMA (super admin)
    ════════════════════════════════════════════════════ --}}
    @if($branchStats && $branchStats->count())
    <div class="tw-stat-card mb-3 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#374151">
            <i class="fas fa-hotel" style="color:#d1d5db"></i>
            <span class="fw-bold text-white" style="font-size:.85rem">Şube Karşılaştırma</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr class="tw-table-head">
                    <th>Şube</th><th class="text-center">Toplam</th><th class="text-center">Açık</th><th class="text-center">Kapalı</th>
                </tr></thead>
                <tbody>
                    @foreach($branchStats as $row)
                    <tr>
                        <td class="tw-table-body fw-semibold" style="color:#1e293b">{{ $row['name'] }}</td>
                        <td class="tw-table-body text-center fw-bold" style="color:#1e293b">{{ $row['total'] }}</td>
                        <td class="tw-table-body text-center"><span class="kpi-pill" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span></td>
                        <td class="tw-table-body text-center"><span class="kpi-pill" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    /* ── Monthly trend bar chart ──────────────────────────────── */
    var trendData = @json($monthlyTrend);
    if (trendData.length) {
        var mNames = ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'];
        var tLabels = trendData.map(function(r) {
            var p = r.month.split('-');
            return mNames[parseInt(p[1]) - 1] + ' ' + p[0].slice(2);
        });
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: tLabels,
                datasets: [
                    { label: 'Toplam', data: trendData.map(function(r){return r.total;}), backgroundColor: 'rgba(99,102,241,.75)', borderRadius: 6, borderWidth:0 },
                    { label: 'Kapalı', data: trendData.map(function(r){return r.closed;}), backgroundColor: 'rgba(16,185,129,.75)', borderRadius: 6, borderWidth:0 }
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:10, font:{size:11} } } },
                scales:{ x:{grid:{display:false}}, y:{beginAtZero:true, ticks:{stepSize:1}, grid:{color:'#f1f5f9'} } }
            }
        });
    }

    /* ── Priority donut chart ─────────────────────────────────── */
    var prioData = @json($priorityStats);
    if (prioData.length && document.getElementById('priorityChart')) {
        var prioColors = { low:'#10b981', medium:'#f59e0b', high:'#ef4444', critical:'#1e293b' };
        new Chart(document.getElementById('priorityChart'), {
            type: 'doughnut',
            data: {
                labels: prioData.map(function(r){return r.label;}),
                datasets: [{
                    data: prioData.map(function(r){return r.total;}),
                    backgroundColor: prioData.map(function(r){return prioColors[r.priority]||'#94a3b8';}),
                    borderWidth: 0, hoverOffset: 4
                }]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:8, font:{size:11} } } },
                cutout:'65%'
            }
        });
    }

    /* ── Dept monthly resolution line chart ──────────────────── */
    var deptMonthly = @json($deptMonthlyResolution);
    if (deptMonthly.length && document.getElementById('deptMonthlyChart')) {
        // Collect all unique months
        var allMonths = {};
        deptMonthly.forEach(function(d) { d.monthly.forEach(function(m){ allMonths[m.month]=1; }); });
        var sortedMonths = Object.keys(allMonths).sort();
        var mN = ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'];
        var dmLabels = sortedMonths.map(function(m){ var p=m.split('-'); return mN[parseInt(p[1])-1]+' '+p[0].slice(2); });
        var palette = ['#6366f1','#f59e0b','#10b981','#ef4444','#0891b2','#7c3aed','#db2777','#0369a1','#92400e','#4d7c0f'];
        var datasets = deptMonthly.map(function(d, idx) {
            var monthMap = {};
            d.monthly.forEach(function(m){ monthMap[m.month] = m.avg_hours; });
            return {
                label: d.dept ? d.dept.name : '—',
                data: sortedMonths.map(function(m){ return monthMap[m] || null; }),
                borderColor: palette[idx % palette.length],
                backgroundColor: 'transparent',
                tension: 0.3, borderWidth: 2, pointRadius: 3,
                spanGaps: true
            };
        });
        new Chart(document.getElementById('deptMonthlyChart'), {
            type: 'line',
            data: { labels: dmLabels, datasets: datasets },
            options: {
                responsive:true, maintainAspectRatio:false,
                interaction: { mode:'index', intersect:false },
                plugins:{
                    legend:{ position:'bottom', labels:{ boxWidth:12, padding:10, font:{size:11} } },
                    tooltip:{ callbacks:{ label: function(ctx){ return ctx.dataset.label+': '+(ctx.parsed.y||0)+'s'; } } }
                },
                scales:{
                    x:{ grid:{display:false} },
                    y:{ beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{ callback:function(v){return v+'s';} } }
                }
            }
        });
    }

})();
</script>
@endpush

