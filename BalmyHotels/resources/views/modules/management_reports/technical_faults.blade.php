@extends('layouts.default')
@section('title', 'Teknik Arıza Raporu')

@include('modules.management_reports.partials.styles')

@section('content')
<div class="container-fluid mr-page">
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text mr-title">
                <h4><i class="fas fa-tools me-2 text-warning"></i>Teknik Arıza Raporu</h4>
                <span>{{ $dateFrom->format('d.m.Y') }} - {{ $dateTo->format('d.m.Y') }} dönemi teknik arıza özeti</span>
            </div>
        </div>
        <div class="col-sm-5 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Üst Yönetim Rapor</li>
            </ol>
        </div>
    </div>

    @include('modules.management_reports.partials.nav')
    @include('modules.management_reports.partials.filter', ['routeName' => 'management-reports.technical-faults'])

    @php
        $kpis = [
            ['label'=>'Toplam Arıza', 'value'=>number_format($summary['total']), 'sub'=>'Açılan kayıt', 'icon'=>'fa-layer-group', 'color'=>'#175cd3', 'bg'=>'#eff8ff'],
            ['label'=>'Aktif Takip', 'value'=>number_format($summary['active']), 'sub'=>'Açık veya işlemde', 'icon'=>'fa-circle-exclamation', 'color'=>'#b42318', 'bg'=>'#fef3f2'],
            ['label'=>'Kapanan', 'value'=>number_format($summary['closed']), 'sub'=>'%'.$summary['closed_pct'].' kapanma', 'icon'=>'fa-circle-check', 'color'=>'#067647', 'bg'=>'#ecfdf3'],
            ['label'=>'Kritik Aktif', 'value'=>number_format($summary['critical_active']), 'sub'=>'Yüksek/kritik öncelik', 'icon'=>'fa-triangle-exclamation', 'color'=>'#c01048', 'bg'=>'#fff1f3'],
            ['label'=>'Ort. Çözüm', 'value'=>\App\Models\Fault::formatHours($summary['avg_hours']), 'sub'=>'Kapanan kayıtlarda', 'icon'=>'fa-hourglass-half', 'color'=>'#0e7490', 'bg'=>'#ecfeff'],
            ['label'=>'SLA Uyum', 'value'=>$summary['sla_pct'] !== null ? '%'.$summary['sla_pct'] : '—', 'sub'=>'Hedef sürede kapanma', 'icon'=>'fa-shield-halved', 'color'=>'#6941c6', 'bg'=>'#f4f3ff'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="mr-card mr-kpi" style="--mr-color:{{ $kpi['color'] }};--mr-bg:{{ $kpi['bg'] }}">
                <div class="mr-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                <div class="mr-kpi-value" style="font-size:{{ mb_strlen((string)$kpi['value']) > 7 ? '1.2rem' : '1.55rem' }}">{{ $kpi['value'] }}</div>
                <div class="mr-kpi-label">{{ $kpi['label'] }}</div>
                <div class="mr-kpi-sub">{{ $kpi['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-align-left"></i> Yönetim Özeti</div>
                <ul class="mr-note-list">
                    @foreach($insights as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-bell"></i> Açık Kritik / Yüksek Öncelikli Kayıtlar</div>
                @forelse($criticalActiveFaults as $fault)
                    <div class="mr-compact-stat">
                        <div>
                            <strong>#{{ $fault->id }} {{ $fault->faultType?->name ?? $fault->title }}</strong>
                            <div class="text-muted small">
                                {{ $fault->department?->name ?? '-' }} · {{ $fault->faultLocation?->name ?? '-' }}
                                @if($fault->faultArea) · {{ $fault->faultArea->name }} @endif
                            </div>
                        </div>
                        <span @class(['mr-pill', 'red' => $fault->priority === 'critical', 'amber' => $fault->priority !== 'critical'])>
                            {{ \App\Models\Fault::PRIORITIES[$fault->priority] ?? $fault->priority }}
                        </span>
                    </div>
                @empty
                    <div class="mr-empty">Açık yüksek veya kritik öncelikli arıza yok.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-line"></i> Günlük Açılan ve Kapanan Arızalar</div>
                <div class="mr-chart"><canvas id="faultDailyChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-chart-pie"></i> Durum Dağılımı</div>
                <div class="mr-chart"><canvas id="faultStatusChart"></canvas></div>
                @if($priorityDistribution->count())
                <div class="px-3 pb-3 d-flex gap-2 flex-wrap">
                    @foreach($priorityDistribution as $row)
                        <span @class([
                            'mr-pill',
                            'green' => $row['priority'] === 'low',
                            'amber' => $row['priority'] === 'medium',
                            'red' => in_array($row['priority'], ['high', 'critical']),
                        ])>{{ $row['label'] }}: {{ $row['total'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-sitemap"></i> Departmanlara Göre İş Yükü</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Departman</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Aktif</th>
                                <th class="text-center">SLA</th>
                                <th class="text-end">Ort. Çözüm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byDepartment as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                <td class="text-center"><span @class(['mr-pill', 'red' => $row['active'] > 0, 'green' => $row['active'] === 0])>{{ $row['active'] }}</span></td>
                                <td class="text-center">{{ $row['sla_pct'] !== null ? '%'.$row['sla_pct'] : '—' }}</td>
                                <td class="text-end">{{ \App\Models\Fault::formatHours($row['avg_hours']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Departman verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-tags"></i> Arıza Tipleri</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Arıza Tipi</th>
                                <th class="text-center">Hedef</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Aktif</th>
                                <th>SLA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byType as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['target_hours'] }}s</td>
                                <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                <td class="text-center"><span @class(['mr-pill', 'red' => $row['active'] > 0, 'green' => $row['active'] === 0])>{{ $row['active'] }}</span></td>
                                <td style="min-width:120px">
                                    @php $sla = $row['sla_pct'] ?? 0; @endphp
                                    <div class="mr-progress" style="--mr-color:{{ $sla >= 80 ? '#067647' : ($sla >= 50 ? '#b54708' : '#b42318') }}">
                                        <span style="width:{{ min(100, $sla) }}%"></span>
                                    </div>
                                    <small class="text-muted">{{ $row['sla_pct'] !== null ? '%'.$row['sla_pct'] : 'Ölçüm yok' }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Arıza tipi verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-location-dot"></i> Konumlara Göre Arızalar</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Konum</th>
                                <th>Öne Çıkan Tip</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Aktif</th>
                                <th class="text-center">Kritik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byLocation as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['top_type'] }}</td>
                                <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                <td class="text-center"><span @class(['mr-pill', 'red' => $row['active'] > 0, 'green' => $row['active'] === 0])>{{ $row['active'] }}</span></td>
                                <td class="text-center">{{ $row['critical_active'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Konum verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-map"></i> Alanlara Göre İlk 15</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Alan</th>
                                <th>Konum</th>
                                <th>Öne Çıkan Tip</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Aktif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byArea as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['location'] }}</td>
                                <td>{{ $row['top_type'] }}</td>
                                <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                <td class="text-center"><span @class(['mr-pill', 'red' => $row['active'] > 0, 'green' => $row['active'] === 0])>{{ $row['active'] }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Alan verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var dailyEl = document.getElementById('faultDailyChart');
    if (dailyEl) {
        new Chart(dailyEl, {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [
                    {
                        label: 'Açılan',
                        data: @json($dailyOpened),
                        borderColor: '#b42318',
                        backgroundColor: 'rgba(180,35,24,.1)',
                        fill: true,
                        tension: .35
                    },
                    {
                        label: 'Kapanan',
                        data: @json($dailyClosed),
                        borderColor: '#067647',
                        backgroundColor: 'rgba(6,118,71,.1)',
                        fill: true,
                        tension: .35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' } }, x: { grid: { display: false } } }
            }
        });
    }

    var statusEl = document.getElementById('faultStatusChart');
    if (statusEl) {
        var statusRows = @json($statusDistribution);
        var colors = {
            open: '#b42318',
            in_progress: '#b54708',
            winter_plan: '#175cd3',
            waiting_material: '#6941c6',
            resolved: '#067647',
            closed: '#667085'
        };
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: statusRows.map(function (row) { return row.label; }),
                datasets: [{
                    data: statusRows.map(function (row) { return row.total; }),
                    backgroundColor: statusRows.map(function (row) { return colors[row.status] || '#98a2b3'; }),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
            }
        });
    }
})();
</script>
@endpush
