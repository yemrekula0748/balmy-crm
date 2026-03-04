@extends('layouts.default')

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-chart-bar me-2" style="color:#4361ee"></i>Arıza İstatistikleri & Skor Tablosu</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">İstatistikler</li>
            </ol>
        </div>
    </div>

    {{-- Dönem Filtresi --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                <label class="fw-semibold mb-0 small">Dönem:</label>
                @foreach(['7'=>'Son 7 Gün','30'=>'Son 30 Gün','90'=>'Son 90 Gün','365'=>'Son 1 Yıl','all'=>'Tüm Zamanlar'] as $val=>$lbl)
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="period" id="p_{{ $val }}" value="{{ $val }}"
                               @checked($period === $val) onchange="this.form.submit()">
                        <label class="form-check-label small" for="p_{{ $val }}">{{ $lbl }}</label>
                    </div>
                @endforeach
            </form>
        </div>
    </div>

    {{-- Özet Kartlar --}}
    <div class="row g-3 mb-4">
        @php
        $summaryCards = [
            ['label'=>'Toplam Arıza',    'value'=>$summary['total'],       'color'=>'#4361ee','bg'=>'#eef0ff','icon'=>'fa-clipboard-list'],
            ['label'=>'Açık',            'value'=>$summary['open'],        'color'=>'#dc3545','bg'=>'#fdecea','icon'=>'fa-exclamation-circle'],
            ['label'=>'İşlemde',         'value'=>$summary['in_progress'], 'color'=>'#f97316','bg'=>'#fff3e0','icon'=>'fa-tools'],
            ['label'=>'Kapalı',          'value'=>$summary['closed'],      'color'=>'#10b981','bg'=>'#e8f5e9','icon'=>'fa-check-double'],
            ['label'=>'Ort. Çözüm (sa)', 'value'=>$summary['avg_hours'] ?? '—','color'=>'#8b5cf6','bg'=>'#f5f0ff','icon'=>'fa-clock'],
            ['label'=>'SLA Uyumu',       'value'=>($summary['sla_pct'] !== null ? '%'.$summary['sla_pct'] : '—'),'color'=>'#0ea5e9','bg'=>'#e0f2fe','icon'=>'fa-shield-alt'],
        ];
        @endphp
        @foreach($summaryCards as $card)
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="box-shadow:0 2px 12px rgba(0,0,0,.07);border-bottom:3px solid {{ $card['color'] }} !important">
                <div class="card-body d-flex align-items-center gap-3 py-3 px-3">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:{{ $card['bg'] }}">
                        <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }};font-size:18px"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;color:{{ $card['color'] }};line-height:1.2">{{ $card['value'] }}</div>
                        <div class="text-muted" style="font-size:0.72rem">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4 align-items-start">

        {{-- DEPARTMAN SKOR TABLOSU --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#4361ee,#3a4fe0);border:0">
                    <i class="fas fa-trophy text-warning"></i>
                    <h5 class="card-title mb-0 text-white">Departman Skor Tablosu</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px">#</th>
                                    <th>Departman</th>
                                    <th class="text-center">Toplam</th>
                                    <th class="text-center text-danger">Açık</th>
                                    <th class="text-center text-warning">İşlemde</th>
                                    <th class="text-center text-success">Kapalı</th>
                                    <th class="text-center">Ort. Süre (sa)</th>
                                    <th style="min-width:140px">SLA Uyumu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptScoreboard as $i => $row)
                                @php
                                    $sla = $row['sla_pct'];
                                    $slaColor = $sla === null ? 'secondary' : ($sla >= 80 ? 'success' : ($sla >= 50 ? 'warning' : 'danger'));
                                    $medalIcons = ['🥇','🥈','🥉'];
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold" style="font-size:1.1rem">
                                        {{ $medalIcons[$i] ?? ($i+1) }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row['dept']?->name ?? '—' }}</div>
                                        @if($row['dept']?->branch)
                                            <div class="text-muted small">{{ $row['dept']->branch->name }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                    <td class="text-center">
                                        @if($row['open'] > 0)
                                            <span class="badge bg-danger">{{ $row['open'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($row['in_progress'] > 0)
                                            <span class="badge bg-warning text-dark">{{ $row['in_progress'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $row['closed'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($row['avg_hours'] !== null)
                                            <span class="fw-semibold">{{ $row['avg_hours'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sla !== null)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-fill">
                                                    <div class="progress" style="height:8px;border-radius:4px">
                                                        <div class="progress-bar bg-{{ $slaColor }}" style="width:{{ $sla }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="text-{{ $slaColor }} fw-semibold small">%{{ $sla }}</span>
                                            </div>
                                            <div class="text-muted" style="font-size:10px">{{ $row['sla_count'] }} ölçüm</div>
                                        @else
                                            <span class="text-muted small">Veri yok</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4 align-items-start">

        {{-- ARIZA TÜRÜ PERFORMANSI --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#f97316,#ea6c0a);border:0">
                    <i class="fas fa-tags text-white"></i>
                    <h5 class="card-title mb-0 text-white">Arıza Türü Performansı</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tür</th>
                                    <th class="text-center">Toplam</th>
                                    <th class="text-center text-danger">Açık</th>
                                    <th class="text-center">Hedef (sa)</th>
                                    <th class="text-center">Ort. (sa)</th>
                                    <th style="min-width:120px">SLA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($typeStats as $row)
                                @php
                                    $sla = $row['sla_pct'];
                                    $slaColor = $sla === null ? 'secondary' : ($sla >= 80 ? 'success' : ($sla >= 50 ? 'warning' : 'danger'));
                                    $avgOk = $row['avg_hours'] && $row['avg_hours'] <= $row['target_hours'];
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $row['type_name'] }}</td>
                                    <td class="text-center">{{ $row['total'] }}</td>
                                    <td class="text-center">
                                        @if($row['open'] > 0)<span class="badge bg-danger">{{ $row['open'] }}</span>
                                        @else<span class="text-muted">—</span>@endif
                                    </td>
                                    <td class="text-center text-muted">{{ $row['target_hours'] }}</td>
                                    <td class="text-center">
                                        @if($row['avg_hours'] !== null)
                                            <span class="fw-semibold text-{{ $avgOk ? 'success' : 'danger' }}">{{ $row['avg_hours'] }}</span>
                                        @else<span class="text-muted">—</span>@endif
                                    </td>
                                    <td>
                                        @if($sla !== null)
                                            <div class="progress" style="height:6px;margin-bottom:2px">
                                                <div class="progress-bar bg-{{ $slaColor }}" style="width:{{ $sla }}%"></div>
                                            </div>
                                            <small class="text-{{ $slaColor }}">%{{ $sla }}</small>
                                        @else<span class="text-muted small">—</span>@endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Veri yok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KONUM BAZLI + ALAN TOP 10 --}}
        <div class="col-lg-5">
            {{-- Konum --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#10b981,#059669);border:0">
                    <i class="fas fa-map-marker-alt text-white"></i>
                    <h5 class="card-title mb-0 text-white">Konum Bazlı (Top 10)</h5>
                </div>
                <div class="card-body p-3">
                    @php $maxLoc = $locationStats->max('total') ?: 1; @endphp
                    @forelse($locationStats as $row)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">{{ $row['name'] }}</span>
                            <div class="d-flex gap-1 align-items-center">
                                @if($row['open'] > 0)<span class="badge bg-danger" style="font-size:10px">{{ $row['open'] }} açık</span>@endif
                                <span class="badge bg-secondary" style="font-size:10px">{{ $row['total'] }}</span>
                            </div>
                        </div>
                        <div class="progress" style="height:6px;border-radius:3px">
                            <div class="progress-bar bg-success" style="width:{{ round($row['total']/$maxLoc*100) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small mb-0">Veri yok.</p>
                    @endforelse
                </div>
            </div>

            {{-- Alan Top 10 --}}
            @if($areaStats->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);border:0">
                    <i class="fas fa-layer-group text-white"></i>
                    <h5 class="card-title mb-0 text-white">Alan Top 10</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Alan</th><th>Konum</th><th class="text-center">Toplam</th><th class="text-center text-danger">Açık</th></tr></thead>
                        <tbody>
                        @foreach($areaStats as $row)
                        <tr>
                            <td class="small fw-semibold">{{ $row['area_name'] }}</td>
                            <td class="small text-muted">{{ $row['loc_name'] }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $row['total'] }}</span></td>
                            <td class="text-center">
                                @if($row['open'] > 0)<span class="badge bg-danger">{{ $row['open'] }}</span>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- AYLIK TREND + ŞUBE --}}
    <div class="row g-4 mb-4 align-items-start">
        <div class="col-{{ $branchStats ? 'lg-8' : '12' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Aylık Arıza Trendi</h5></div>
                <div class="card-body">
                    <div style="position:relative;height:{{ $branchStats ? '240px' : '280px' }}">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        @if($branchStats)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);border:0">
                    <i class="fas fa-building text-white"></i>
                    <h5 class="card-title mb-0 text-white">Şube Karşılaştırması</h5>
                </div>
                <div class="card-body p-3">
                    @php $maxBranch = $branchStats->max('total') ?: 1; @endphp
                    @foreach($branchStats as $row)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold">{{ $row['name'] }}</span>
                            <span class="small text-muted">{{ $row['total'] }} toplam</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:5px">
                            <div class="progress-bar" style="width:{{ round($row['total']/$maxBranch*100) }}%;background:#0ea5e9"></div>
                        </div>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-danger" style="font-size:10px">{{ $row['open'] }} açık</span>
                            <span class="badge bg-success" style="font-size:10px">{{ $row['closed'] }} kapalı</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const trendData = @json($monthlyTrend);
    if (!trendData.length) return;

    const labels = trendData.map(r => {
        const [y, m] = r.month.split('-');
        const months = ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'];
        return months[parseInt(m)-1] + ' ' + y;
    });

    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Yeni Arıza',
                    data: trendData.map(r => r.total),
                    backgroundColor: 'rgba(67,97,238,0.6)',
                    borderColor: '#4361ee',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Kapalı',
                    data: trendData.map(r => r.closed),
                    backgroundColor: 'rgba(16,185,129,0.5)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4,
                    type: 'line',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
@endpush
