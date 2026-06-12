@extends('layouts.default')
@section('title', 'Sipariş Tüketim Raporu')

@include('modules.management_reports.partials.styles')

@section('content')
<div class="container-fluid mr-page">
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text mr-title">
                <h4><i class="fas fa-utensils me-2 text-danger"></i>Sipariş Tüketim Raporu</h4>
                <span>{{ $selectedOutletName }} | {{ $dateFrom->format('d.m.Y') }} - {{ $dateTo->format('d.m.Y') }}</span>
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
                    <option value="">Tüm restoran ve barlar</option>
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
        $peak = $peakHours->first();
        $top = $topProducts->first();
        $kpis = [
            ['label'=>'Toplam Tüketim', 'value'=>number_format($summary['total_qty']), 'sub'=>'Ürün adedi', 'icon'=>'fa-bowl-food', 'color'=>'#175cd3', 'bg'=>'#eff8ff'],
            ['label'=>'Sipariş', 'value'=>number_format($summary['total_orders']), 'sub'=>'Açılan sipariş', 'icon'=>'fa-receipt', 'color'=>'#6941c6', 'bg'=>'#f4f3ff'],
            ['label'=>'Masa / Seans', 'value'=>number_format($summary['total_sessions']), 'sub'=>'Tüketim oluşan', 'icon'=>'fa-chair', 'color'=>'#067647', 'bg'=>'#ecfdf3'],
            ['label'=>'Seans Başı', 'value'=>number_format($summary['avg_qty_per_session'], 1), 'sub'=>'Ortalama ürün', 'icon'=>'fa-chart-simple', 'color'=>'#b54708', 'bg'=>'#fffaeb'],
            ['label'=>'En Yoğun Saat', 'value'=>$peak['hour'] ?? '-', 'sub'=>($peak['total_qty'] ?? 0).' ürün', 'icon'=>'fa-clock', 'color'=>'#c01048', 'bg'=>'#fff1f3'],
            ['label'=>'En Çok Ürün', 'value'=>$top['qty'] ?? 0, 'sub'=>$top['name'] ?? '-', 'icon'=>'fa-ranking-star', 'color'=>'#0e7490', 'bg'=>'#ecfeff'],
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
                <div class="mr-section-title"><i class="fas fa-align-left"></i> Net Özet</div>
                <ul class="mr-note-list">
                    @foreach($insights as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="row g-3">
                @foreach($beverageSummary as $row)
                <div class="col-md-4 col-xl-12">
                    <div class="mr-card mr-kpi" style="--mr-color:{{ $row['key'] === 'soft_drink' ? '#175cd3' : ($row['key'] === 'alcohol' ? '#b54708' : '#c01048') }};--mr-bg:#f8fafc">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="mr-kpi-label">{{ $row['label'] }}</div>
                                <div class="mr-kpi-value">{{ number_format($row['qty']) }}</div>
                                <div class="mr-kpi-sub">Yoğun saat: {{ $row['peak_hour'] }} | {{ $row['top_product'] }}</div>
                            </div>
                            <div class="mr-kpi-icon"><i class="fas {{ $row['key'] === 'soft_drink' ? 'fa-bottle-water' : ($row['key'] === 'alcohol' ? 'fa-wine-glass' : 'fa-martini-glass') }}"></i></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-column"></i> Saatlik Toplam Tüketim</div>
                <div class="mr-chart"><canvas id="hourlyTotalChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="mr-card">
                <div class="mr-section-title"><i class="fas fa-chart-line"></i> Meşrubat / Alkol / Distile Saatleri</div>
                <div class="mr-chart"><canvas id="beverageHourlyChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-clock"></i> En Yoğun Saatler</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Saat</th>
                                <th class="text-center">Ürün</th>
                                <th class="text-center">Sipariş</th>
                                <th>Öne Çıkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peakHours as $row)
                            <tr>
                                <td class="fw-bold">{{ $row['hour'] }}</td>
                                <td class="text-center">{{ number_format($row['total_qty']) }}</td>
                                <td class="text-center">{{ number_format($row['orders']) }}</td>
                                <td>{{ $row['top_product'] }} <small class="text-muted">({{ $row['top_product_qty'] }})</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Saatlik tüketim verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="mr-card h-100">
                <div class="mr-section-title"><i class="fas fa-ranking-star"></i> En Çok Tüketilen Ürünler</div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Kategori</th>
                                <th class="text-center">Yoğun Saat</th>
                                <th class="text-center">Sipariş</th>
                                <th class="text-center">Pay</th>
                                <th class="text-end">Adet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['name'] }}</div>
                                    @if($row['source_category'])
                                        <small class="text-muted">{{ $row['source_category'] }}</small>
                                    @endif
                                </td>
                                <td><span class="mr-pill gray">{{ $row['category'] }}</span></td>
                                <td class="text-center">{{ $row['peak_hour'] }} <small class="text-muted">({{ $row['peak_qty'] }})</small></td>
                                <td class="text-center">{{ number_format($row['orders']) }}</td>
                                <td class="text-center">%{{ $row['share'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($row['qty']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Ürün verisi yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach($beverageSummary as $row)
        <div class="col-xl-4">
            <div class="mr-card h-100">
                <div class="mr-section-title">
                    <i class="fas {{ $row['key'] === 'soft_drink' ? 'fa-bottle-water' : ($row['key'] === 'alcohol' ? 'fa-wine-glass' : 'fa-martini-glass') }}"></i>
                    {{ $row['label'] }} Ürünleri
                </div>
                <div class="table-responsive">
                    <table class="table mr-table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th class="text-end">Adet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($row['top_products'] as $product)
                            <tr>
                                <td>{{ $product['name'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($product['qty']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Kayıt yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 border-top small text-muted">
                    En yoğun saat: <strong>{{ $row['peak_hour'] }}</strong>, {{ number_format($row['peak_qty']) }} adet
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var totalEl = document.getElementById('hourlyTotalChart');
    if (totalEl) {
        new Chart(totalEl, {
            type: 'bar',
            data: {
                labels: @json($hourlyLabels),
                datasets: [
                    {
                        label: 'Ürün adedi',
                        data: @json($hourlyTotalQty),
                        backgroundColor: '#175cd3',
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Sipariş',
                        data: @json($hourlyOrderCount),
                        type: 'line',
                        borderColor: '#c01048',
                        backgroundColor: 'rgba(192,16,72,.1)',
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
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } }
                }
            }
        });
    }

    var beverageEl = document.getElementById('beverageHourlyChart');
    if (beverageEl) {
        new Chart(beverageEl, {
            type: 'line',
            data: {
                labels: @json($hourlyLabels),
                datasets: [
                    {
                        label: 'Meşrubat',
                        data: @json($hourlySoftDrinkQty),
                        borderColor: '#175cd3',
                        backgroundColor: 'rgba(23,92,211,.08)',
                        fill: true,
                        tension: .35
                    },
                    {
                        label: 'Alkol',
                        data: @json($hourlyAlcoholQty),
                        borderColor: '#b54708',
                        backgroundColor: 'rgba(181,71,8,.08)',
                        fill: true,
                        tension: .35
                    },
                    {
                        label: 'Distile Alkol',
                        data: @json($hourlyDistilledAlcoholQty),
                        borderColor: '#c01048',
                        backgroundColor: 'rgba(192,16,72,.08)',
                        fill: true,
                        tension: .35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } } }
            }
        });
    }
})();
</script>
@endpush
