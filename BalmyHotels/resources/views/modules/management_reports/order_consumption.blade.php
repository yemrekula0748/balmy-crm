@extends('layouts.default')
@section('title', 'Sipariş Tüketim Raporu')

@include('modules.management_reports.partials.styles')

@section('content')
<div class="container-fluid mr-page">
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text mr-title">
                <h4><i class="fas fa-utensils me-2 text-danger"></i>Sipariş Tüketim Raporu</h4>
                <span>{{ $dateFrom->format('d.m.Y') }} - {{ $dateTo->format('d.m.Y') }} dönemi restoran ve bar tüketim özeti</span>
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

    <div class="mr-card mr-filter mb-3">
        <form method="GET" action="{{ route('management-reports.order-consumption') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Başlangıç</label>
                <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Bitiş</label>
                <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Şube</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Tüm görünür şubeler</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string)$branchId === (string)$branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Restoran / Bar</label>
                <select name="restaurant_id" class="form-select form-select-sm">
                    <option value="">Tüm alanlar</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @selected((string)$restaurantId === (string)$restaurant->id)>
                            {{ $restaurant->name }}{{ $restaurant->branch ? ' - '.$restaurant->branch->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-filter me-1"></i> Filtrele
                </button>
                <a href="{{ route('management-reports.order-consumption') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    @php
        $kpis = [
            ['label'=>'Ürün Adedi', 'value'=>number_format($summary['total_qty']), 'sub'=>'Toplam tüketim', 'icon'=>'fa-bowl-food', 'color'=>'#175cd3', 'bg'=>'#eff8ff'],
            ['label'=>'Sipariş', 'value'=>number_format($summary['total_orders']), 'sub'=>'Açılan sipariş', 'icon'=>'fa-receipt', 'color'=>'#6941c6', 'bg'=>'#f4f3ff'],
            ['label'=>'Masa / Seans', 'value'=>number_format($summary['total_sessions']), 'sub'=>'Tüketim oluşan', 'icon'=>'fa-chair', 'color'=>'#067647', 'bg'=>'#ecfdf3'],
            ['label'=>'Seans Başı', 'value'=>number_format($summary['avg_qty_per_session'], 1), 'sub'=>'Ortalama ürün', 'icon'=>'fa-chart-simple', 'color'=>'#b54708', 'bg'=>'#fffaeb'],
            ['label'=>'İkram / Sıfır Fiyat', 'value'=>'%'.$summary['inclusive_pct'], 'sub'=>number_format($summary['inclusive_qty']).' ürün', 'icon'=>'fa-gift', 'color'=>'#c01048', 'bg'=>'#fff1f3'],
            ['label'=>'Kayıtlı Tutar', 'value'=>number_format($summary['recorded_value'], 0), 'sub'=>'Ürün bazlı gösterge', 'icon'=>'fa-coins', 'color'=>'#0e7490', 'bg'=>'#ecfeff'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="mr-card mr-kpi" style="--mr-color:{{ $kpi['color'] }};--mr-bg:{{ $kpi['bg'] }}">
                <div class="mr-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                <div class="mr-kpi-value" style="font-size:{{ mb_strlen((string)$kpi['value']) > 7 ? '1.15rem' : '1.55rem' }}">{{ $kpi['value'] }}</div>
                <div class="mr-kpi-label">{{ $kpi['label'] }}</div>
                <div class="mr-kpi-sub">{{ $kpi['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-align-left"></i> Yönetim ve Maliyet Özeti</div>
                <ul class="mr-note-list">
                    @foreach($costRecommendations as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-layer-group"></i> Alan Tipi Özeti</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Tip</th>
                                <th class="text-center">Alan</th>
                                <th class="text-center">Seans</th>
                                <th class="text-end">Ürün</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outletSummary as $row)
                            <tr>
                                <td>{{ $row['type'] }}</td>
                                <td class="text-center">{{ $row['outlets'] }}</td>
                                <td class="text-center">{{ $row['sessions'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($row['qty']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Alan tipi verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-3 pb-3">
                    <div class="mr-chart" style="height:190px;padding:6px 0 0"><canvas id="outletTypeChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-line"></i> Günlük Tüketim ve Seans</div>
                <div class="mr-chart"><canvas id="dailyConsumptionChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-clock"></i> Saatlik Sipariş Yoğunluğu</div>
                <div class="mr-chart"><canvas id="hourlyOrdersChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-store"></i> Restoran / Bar Bazlı Tüketim</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Alan</th>
                                <th>Tip</th>
                                <th class="text-center">Seans</th>
                                <th class="text-center">Sipariş</th>
                                <th class="text-center">Seans Başı</th>
                                <th class="text-end">Ürün</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byRestaurant as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row->restaurant_name }}</div>
                                    <small class="text-muted">{{ $row->branch_name }}</small>
                                </td>
                                <td><span class="mr-pill gray">{{ $row->outlet_type }}</span></td>
                                <td class="text-center">{{ number_format($row->total_sessions) }}</td>
                                <td class="text-center">{{ number_format($row->total_orders) }}</td>
                                <td class="text-center fw-semibold">{{ number_format($row->avg_qty_per_session, 1) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_qty) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Restoran veya bar tüketim verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-hotel"></i> Şube Bazlı Tüketim</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Şube</th>
                                <th class="text-center">Alan</th>
                                <th class="text-center">Seans</th>
                                <th class="text-center">Sipariş</th>
                                <th class="text-end">Ürün</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchSummary as $row)
                            <tr>
                                <td>{{ $row->branch_name }}</td>
                                <td class="text-center">{{ number_format($row->restaurant_count) }}</td>
                                <td class="text-center">{{ number_format($row->session_count) }}</td>
                                <td class="text-center">{{ number_format($row->order_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->item_qty) }}</td>
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

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-ranking-star"></i> En Çok Tüketilen Ürünler</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th class="text-center">Alan</th>
                                <th class="text-end">Adet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $row)
                            <tr>
                                <td>{{ $row->item_name }}</td>
                                <td class="text-center">{{ number_format($row->restaurant_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_qty) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Ürün verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-gift"></i> İkram / Sıfır Fiyat Ürünler</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th class="text-center">Seans</th>
                                <th class="text-end">Adet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topInclusiveProducts as $row)
                            <tr>
                                <td>{{ $row->item_name }}</td>
                                <td class="text-center">{{ number_format($row->session_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_qty) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">İkram/sıfır fiyat ürünü yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-user-check"></i> Sipariş Giren Kullanıcılar</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Kullanıcı</th>
                                <th class="text-center">Seans</th>
                                <th class="text-center">Sipariş</th>
                                <th class="text-end">Ürün</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topWaiters as $row)
                            <tr>
                                <td>{{ $row->waiter_name }}</td>
                                <td class="text-center">{{ number_format($row->session_count) }}</td>
                                <td class="text-center">{{ number_format($row->order_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->item_qty) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Kullanıcı verisi yok.</td></tr>
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
    var dailyEl = document.getElementById('dailyConsumptionChart');
    if (dailyEl) {
        new Chart(dailyEl, {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [
                    {
                        label: 'Ürün adedi',
                        data: @json($dailyQty),
                        borderColor: '#175cd3',
                        backgroundColor: 'rgba(23,92,211,.1)',
                        fill: true,
                        tension: .35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Seans',
                        data: @json($dailySessions),
                        borderColor: '#067647',
                        backgroundColor: 'rgba(6,118,71,.08)',
                        fill: true,
                        tension: .35,
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
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' } },
                    y1: { beginAtZero: true, position: 'right', ticks: { precision: 0 }, grid: { drawOnChartArea: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var hourlyEl = document.getElementById('hourlyOrdersChart');
    if (hourlyEl) {
        new Chart(hourlyEl, {
            type: 'bar',
            data: {
                labels: @json($hourlyLabels),
                datasets: [{
                    label: 'Sipariş',
                    data: @json($hourlyOrders),
                    backgroundColor: '#6941c6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } } }
            }
        });
    }

    var outletEl = document.getElementById('outletTypeChart');
    if (outletEl) {
        new Chart(outletEl, {
            type: 'doughnut',
            data: {
                labels: @json($outletSummary->pluck('type')->values()),
                datasets: [{
                    data: @json($outletSummary->pluck('qty')->values()),
                    backgroundColor: ['#175cd3', '#c01048', '#067647', '#b54708'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
            }
        });
    }
})();
</script>
@endpush
