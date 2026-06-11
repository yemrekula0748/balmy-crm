@extends('layouts.default')
@section('title', 'Müdür Giriş Çıkışları')

@include('modules.management_reports.partials.styles')

@section('content')
<div class="container-fluid mr-page">
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text mr-title">
                <h4><i class="fas fa-door-open me-2 text-primary"></i>Müdür Giriş Çıkışları</h4>
                <span>{{ $dateFrom->format('d.m.Y') }} - {{ $dateTo->format('d.m.Y') }} dönemi yönetici giriş çıkış özeti</span>
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
    @include('modules.management_reports.partials.filter', ['routeName' => 'management-reports.manager-door-logs'])

    @php
        $kpis = [
            ['label'=>'Toplam Müdür', 'value'=>number_format($summary['total_managers']), 'sub'=>'Seçili şube kapsamı', 'icon'=>'fa-user-tie', 'color'=>'#175cd3', 'bg'=>'#eff8ff'],
            ['label'=>'Kayıt Oluşan', 'value'=>number_format($summary['active_managers']), 'sub'=>'Dönemde hareket var', 'icon'=>'fa-user-check', 'color'=>'#067647', 'bg'=>'#ecfdf3'],
            ['label'=>'Toplam Giriş', 'value'=>number_format($summary['total_entries']), 'sub'=>'Kapı giriş kaydı', 'icon'=>'fa-arrow-right-to-bracket', 'color'=>'#6941c6', 'bg'=>'#f4f3ff'],
            ['label'=>'Toplam Çıkış', 'value'=>number_format($summary['total_exits']), 'sub'=>'Kapı çıkış kaydı', 'icon'=>'fa-arrow-right-from-bracket', 'color'=>'#b54708', 'bg'=>'#fffaeb'],
            ['label'=>'Toplam Süre', 'value'=>number_format($summary['total_hours'], 1), 'sub'=>'Saat', 'icon'=>'fa-clock', 'color'=>'#0e7490', 'bg'=>'#ecfeff'],
            ['label'=>'İçeride Görünen', 'value'=>number_format($summary['inside_now']), 'sub'=>'Son kayda göre', 'icon'=>'fa-location-dot', 'color'=>'#c01048', 'bg'=>'#fff1f3'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="mr-card mr-kpi" style="--mr-color:{{ $kpi['color'] }};--mr-bg:{{ $kpi['bg'] }}">
                <div class="mr-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                <div class="mr-kpi-value">{{ $kpi['value'] }}</div>
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
                <div class="mr-section-title"><i class="fas fa-user-clock"></i> Şu An İçeride Görünen Müdürler</div>
                @forelse($insideManagers as $row)
                    <div class="mr-compact-stat">
                        <div>
                            <strong>{{ $row['manager']->name }}</strong>
                            <div class="text-muted small">{{ $row['department'] }} · {{ $row['branch'] }}</div>
                        </div>
                        <span class="mr-pill green">İçeride</span>
                    </div>
                @empty
                    <div class="mr-empty">Son kayıtlara göre içeride görünen müdür yok.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-line"></i> Günlük Müdür Hareketi</div>
                <div class="mr-chart"><canvas id="managerDailyChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-building"></i> Şube Özeti</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Şube</th>
                                <th class="text-center">Aktif</th>
                                <th class="text-end">Saat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchSummary as $row)
                            <tr>
                                <td>{{ $row['branch'] }}</td>
                                <td class="text-center">{{ $row['active_count'] }} / {{ $row['manager_count'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($row['total_hours'], 1) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Şube verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-sitemap"></i> Departman Bazlı Özet</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Departman</th>
                                <th class="text-center">Müdür</th>
                                <th class="text-center">Eksik Çıkış</th>
                                <th class="text-end">Saat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentSummary as $row)
                            <tr>
                                <td>{{ $row['department'] }}</td>
                                <td class="text-center">{{ $row['active_count'] }} / {{ $row['manager_count'] }}</td>
                                <td class="text-center">
                                    <span @class(['mr-pill', 'red' => $row['missing_exits'] > 0, 'gray' => $row['missing_exits'] === 0])>{{ $row['missing_exits'] }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($row['total_hours'], 1) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Departman verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-list-check"></i> Müdür Bazlı Detay</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Müdür</th>
                                <th>Departman</th>
                                <th class="text-center">Gün</th>
                                <th class="text-center">Giriş/Çıkış</th>
                                <th class="text-center">Ort. Saat</th>
                                <th class="text-center">Ort. Giriş/Çıkış</th>
                                <th>Devam</th>
                                <th class="text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($managerStats as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['manager']->name }}</div>
                                    <small class="text-muted">{{ $row['branch'] }}</small>
                                </td>
                                <td>{{ $row['department'] }}</td>
                                <td class="text-center">{{ $row['worked_days'] }}</td>
                                <td class="text-center">{{ $row['entry_count'] }} / {{ $row['exit_count'] }}</td>
                                <td class="text-center">{{ number_format($row['avg_daily_hours'], 1) }}</td>
                                <td class="text-center">
                                    <div>{{ $row['avg_entry_time'] }}</div>
                                    <small class="text-muted">{{ $row['avg_exit_time'] }}</small>
                                </td>
                                <td style="min-width:120px">
                                    <div class="mr-progress" style="--mr-color:#175cd3">
                                        <span style="width:{{ min(100, $row['attendance_rate']) }}%"></span>
                                    </div>
                                    <small class="text-muted">%{{ $row['attendance_rate'] }}</small>
                                </td>
                                <td class="text-end fw-bold">
                                    {{ number_format($row['total_hours'], 1) }}
                                    @if($row['missing_exits'] > 0)
                                        <div><span class="mr-pill red mt-1">{{ $row['missing_exits'] }} eksik çıkış</span></div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">Müdür kaydı bulunamadı.</td></tr>
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
    var el = document.getElementById('managerDailyChart');
    if (!el) return;

    new Chart(el, {
        data: {
            labels: @json($dailyLabels),
            datasets: [
                {
                    type: 'bar',
                    label: 'Toplam Saat',
                    data: @json($dailyHours),
                    backgroundColor: 'rgba(23, 92, 211, .72)',
                    borderRadius: 5,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Kayıt Oluşan Müdür',
                    data: @json($dailyManagers),
                    borderColor: '#067647',
                    backgroundColor: 'rgba(6, 118, 71, .12)',
                    tension: .35,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Saat' }, grid: { color: '#eef2f6' } },
                y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Müdür' }, grid: { drawOnChartArea: false }, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>
@endpush
