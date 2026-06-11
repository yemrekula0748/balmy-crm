@extends('layouts.default')
@section('title', 'Servis Raporu')

@include('modules.management_reports.partials.styles')

@section('content')
<div class="container-fluid mr-page">
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text mr-title">
                <h4><i class="fas fa-bus me-2 text-success"></i>Servis Raporu</h4>
                <span>{{ $dateFrom->format('d.m.Y') }} - {{ $dateTo->format('d.m.Y') }} dönemi servis operasyon özeti</span>
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
    @include('modules.management_reports.partials.filter', ['routeName' => 'management-reports.shuttle-services'])

    @php
        $kpis = [
            ['label'=>'Toplam Sefer', 'value'=>number_format($summary['total_trips']), 'sub'=>'Operasyon kaydı', 'icon'=>'fa-route', 'color'=>'#175cd3', 'bg'=>'#eff8ff'],
            ['label'=>'Toplam Hareket', 'value'=>number_format($summary['total_movement']), 'sub'=>'Geliş + dönüş kişi', 'icon'=>'fa-users', 'color'=>'#6941c6', 'bg'=>'#f4f3ff'],
            ['label'=>'Gelen Personel', 'value'=>number_format($summary['total_arrival']), 'sub'=>'Toplam geliş', 'icon'=>'fa-arrow-right', 'color'=>'#067647', 'bg'=>'#ecfdf3'],
            ['label'=>'Dönen Personel', 'value'=>number_format($summary['total_departure']), 'sub'=>'Toplam dönüş', 'icon'=>'fa-arrow-left', 'color'=>'#b54708', 'bg'=>'#fffaeb'],
            ['label'=>'Aktif Araç', 'value'=>number_format($summary['active_vehicle_count']).' / '.number_format($summary['vehicle_count']), 'sub'=>'Dönemde kullanılan', 'icon'=>'fa-van-shuttle', 'color'=>'#0e7490', 'bg'=>'#ecfeff'],
            ['label'=>'Ort. Doluluk', 'value'=>$summary['avg_occupancy'] !== null ? '%'.$summary['avg_occupancy'] : '—', 'sub'=>'Kapasitesi girilenler', 'icon'=>'fa-gauge-high', 'color'=>'#c01048', 'bg'=>'#fff1f3'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="mr-card mr-kpi" style="--mr-color:{{ $kpi['color'] }};--mr-bg:{{ $kpi['bg'] }}">
                <div class="mr-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                <div class="mr-kpi-value" style="font-size:{{ mb_strlen((string)$kpi['value']) > 7 ? '1.18rem' : '1.55rem' }}">{{ $kpi['value'] }}</div>
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
                <div class="mr-section-title"><i class="fas fa-hotel"></i> Şube Bazlı Hareket</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Şube</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-center">Geliş</th>
                                <th class="text-center">Dönüş</th>
                                <th class="text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchSummary as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['trips'] }}</td>
                                <td class="text-center">{{ $row['arrival'] }}</td>
                                <td class="text-center">{{ $row['departure'] }}</td>
                                <td class="text-end fw-bold">{{ $row['movement'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Şube verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-line"></i> Günlük Servis Hareketi</div>
                <div class="mr-chart"><canvas id="shuttleDailyChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-clock"></i> Vardiya Dağılımı</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Vardiya</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-end">Hareket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byShift as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['trips'] }}</td>
                                <td class="text-end fw-bold">{{ $row['movement'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Vardiya verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-van-shuttle"></i> Araç Bazlı Servis Kullanımı</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Araç</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-center">Geliş</th>
                                <th class="text-center">Dönüş</th>
                                <th>Doluluk</th>
                                <th class="text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byVehicle as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['name'] }}</div>
                                    @if($row['plate'])
                                        <small class="text-muted">{{ $row['plate'] }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $row['trips'] }}</td>
                                <td class="text-center">{{ $row['arrival'] }}</td>
                                <td class="text-center">{{ $row['departure'] }}</td>
                                <td style="min-width:120px">
                                    @if($row['occupancy'] !== null)
                                        <div class="mr-progress" style="--mr-color:{{ $row['occupancy'] >= 85 ? '#b42318' : ($row['occupancy'] >= 60 ? '#b54708' : '#067647') }}">
                                            <span style="width:{{ min(100, $row['occupancy']) }}%"></span>
                                        </div>
                                        <small class="text-muted">%{{ $row['occupancy'] }}</small>
                                    @else
                                        <span class="text-muted">Kapasite yok</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">{{ $row['movement'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Araç verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-route"></i> Güzergah Bazlı Hareket</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Güzergah</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-center">Geliş</th>
                                <th class="text-center">Dönüş</th>
                                <th class="text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byRoute as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['trips'] }}</td>
                                <td class="text-center">{{ $row['arrival'] }}</td>
                                <td class="text-center">{{ $row['departure'] }}</td>
                                <td class="text-end fw-bold">{{ $row['movement'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Güzergah verisi yok.</td></tr>
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
    var el = document.getElementById('shuttleDailyChart');
    if (!el) return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [
                {
                    label: 'Geliş',
                    data: @json($dailyArrival),
                    borderColor: '#175cd3',
                    backgroundColor: 'rgba(23,92,211,.1)',
                    fill: true,
                    tension: .35
                },
                {
                    label: 'Dönüş',
                    data: @json($dailyDeparture),
                    borderColor: '#b54708',
                    backgroundColor: 'rgba(181,71,8,.1)',
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
})();
</script>
@endpush
