@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sipariş Analizi</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.take') }}">Sipariş</a></li>
                <li class="breadcrumb-item active">Analiz</li>
            </ol>
        </div>
    </div>

    {{-- Filtre --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('orders.analytics') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Restoran</label>
                    <select name="restaurant_id" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" @selected($restaurantId == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Başlangıç</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Bitiş</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm text-white px-4" style="background:#c19b77">Filtrele</button>
                    <a href="{{ route('orders.analytics') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── KPI Kartlar ─────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #c19b77 !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Toplam Hasılat</div>
                    <div class="fw-bold fs-5" style="color:#c19b77">
                        ₺{{ number_format($summary['paid_revenue'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #5c9e6e !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Toplam Seans</div>
                    <div class="fw-bold fs-5 text-success">{{ number_format($summary['total_sessions']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #4e8fcb !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Toplam Sipariş</div>
                    <div class="fw-bold fs-5 text-primary">{{ number_format($summary['total_orders']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #e68a3c !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Seans Başına Hasılat</div>
                    <div class="fw-bold fs-5" style="color:#e68a3c">
                        ₺{{ number_format($summary['avg_session_revenue'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #9c5fcb !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Toplam Kalem (Adet)</div>
                    <div class="fw-bold fs-5" style="color:#9c5fcb">{{ number_format($summary['total_qty']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm h-100 border-0" style="border-left:4px solid #c19b77 !important">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Tarih Aralığı</div>
                    <div class="fw-bold" style="font-size:.85rem;color:#555">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($dateTo)->format('d.m.Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Satır 1: Günlük hasılat trendi + Saatlik yoğunluk ─────────── --}}
    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Günlük Hasılat Trendi</h6></div>
                <div class="card-body">
                    <canvas id="chartDailyRevenue" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Saatlik Sipariş Yoğunluğu</h6></div>
                <div class="card-body">
                    <canvas id="chartHourly" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Satır 2: En çok kazandıran ürünler + Restoran payı ─────────── --}}
    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-7">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">En Çok Kazandıran Ürünler (Ücretli, İlk 10)</h6></div>
                <div class="card-body">
                    <canvas id="chartTopEarning" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Restoran Bazında Hasılat</h6></div>
                <div class="card-body d-flex flex-column align-items-center">
                    <canvas id="chartRestaurants" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Satır 3: Haftalık dağılım + Ortalama masa süresi ───────────── --}}
    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Haftanın Günlerine Göre Sipariş</h6></div>
                <div class="card-body">
                    <canvas id="chartWeekday" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Restoran Bazında Ort. Masa Süresi (dk)</h6></div>
                <div class="card-body">
                    @if($avgDuration->isNotEmpty())
                    <canvas id="chartDuration" height="140"></canvas>
                    @else
                    <p class="text-muted text-center mt-3">Henüz kapatılmış masa yok.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Satır 4: Ürün tabloları ──────────────────────────────────────── --}}
    <div class="row g-3 mb-4 align-items-start">

        {{-- En çok kazandıran ücretli ürünler --}}
        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">En Çok Kazandıran Ücretli Ürünler</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ürün</th>
                                    <th class="text-end">Hasılat</th>
                                    <th class="text-end">Adet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topEarningProducts as $i => $p)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $p->item_name }}</td>
                                    <td class="text-end fw-semibold" style="color:#c19b77">
                                        ₺{{ number_format($p->revenue, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end text-muted">{{ number_format($p->qty) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Veri yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- En çok adet tüketilen ücretli ürünler --}}
        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">En Çok Tüketilen Ücretli Ürünler</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ürün</th>
                                    <th class="text-end">Adet</th>
                                    <th class="text-end">Ort. Fiyat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPaidProductsByQty as $i => $p)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $p->item_name }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($p->qty) }}</td>
                                    <td class="text-end text-muted">₺{{ number_format($p->avg_price, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Veri yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- En çok tüketilen ücretsiz ürünler --}}
        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h6 class="mb-0">En Çok Tüketilen Ücretsiz Ürünler</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ürün</th>
                                    <th class="text-end">Adet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topFreeProducts as $i => $p)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $p->item_name }}</td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($p->qty) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Veri yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Satır 5: Restoran hasılat tablosu + En aktif garsonlar ──────── --}}
    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Restoran Hasılat Detayı</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Restoran</th>
                                    <th class="text-end">Hasılat</th>
                                    <th class="text-end">Seans</th>
                                    <th class="text-end">Kalem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalR = $topRestaurants->sum('revenue'); @endphp
                                @forelse($topRestaurants as $r)
                                <tr>
                                    <td>{{ $r->restaurant_name }}</td>
                                    <td class="text-end fw-semibold" style="color:#c19b77">
                                        ₺{{ number_format($r->revenue, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end text-muted">{{ number_format($r->sessions) }}</td>
                                    <td class="text-end text-muted">{{ number_format($r->qty) }}</td>
                                </tr>
                                @if($totalR > 0)
                                <tr class="bg-light">
                                    <td colspan="4" class="py-0 px-2">
                                        <div class="progress" style="height:4px">
                                            <div class="progress-bar" style="width:{{ round($r->revenue/$totalR*100) }}%; background:#c19b77"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Veri yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">En Aktif Garsonlar</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Garson</th>
                                    <th class="text-end">Sipariş</th>
                                    <th class="text-end">Kalem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topWaiters as $i => $w)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $w->name }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($w->order_count) }}</td>
                                    <td class="text-end text-muted">{{ number_format($w->item_qty) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Veri yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
const brandColor  = '#c19b77';
const brandAlpha  = 'rgba(193,155,119,0.25)';
const colors10    = [
    '#c19b77','#e68a3c','#4e8fcb','#5c9e6e','#9c5fcb',
    '#d96060','#4ec9c9','#e8b84b','#6e8cbf','#a67c52'
];

// 1. Günlük hasılat
new Chart(document.getElementById('chartDailyRevenue'), {
    type: 'line',
    data: {
        labels: @json($dailyLabels),
        datasets: [{
            label: 'Hasılat (₺)',
            data: @json($dailyValues),
            borderColor: brandColor,
            backgroundColor: brandAlpha,
            borderWidth: 2,
            pointRadius: 3,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => '₺' + v.toLocaleString('tr-TR') } }
        }
    }
});

// 2. Saatlik yoğunluk
new Chart(document.getElementById('chartHourly'), {
    type: 'bar',
    data: {
        labels: @json(range(0, 23)).map(h => h.toString().padStart(2,'0') + ':00'),
        datasets: [{
            data: @json($hourlyData->values()),
            backgroundColor: brandColor,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// 3. En çok kazandıran ürünler (yatay bar)
new Chart(document.getElementById('chartTopEarning'), {
    type: 'bar',
    data: {
        labels: @json($topEarningProducts->pluck('item_name')),
        datasets: [{
            label: 'Hasılat (₺)',
            data: @json($topEarningProducts->pluck('revenue')),
            backgroundColor: colors10,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { callback: v => '₺' + v.toLocaleString('tr-TR') } }
        }
    }
});

// 4. Restoran payı (doughnut)
@if($topRestaurants->isNotEmpty())
new Chart(document.getElementById('chartRestaurants'), {
    type: 'doughnut',
    data: {
        labels: @json($topRestaurants->pluck('restaurant_name')),
        datasets: [{
            data: @json($topRestaurants->pluck('revenue')),
            backgroundColor: colors10,
            borderWidth: 1
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₺' + Number(ctx.raw).toLocaleString('tr-TR', {minimumFractionDigits:2})
                }
            }
        }
    }
});
@endif

// 5. Haftalık dağılım
new Chart(document.getElementById('chartWeekday'), {
    type: 'bar',
    data: {
        labels: @json($dowLabels),
        datasets: [{
            label: 'Sipariş Sayısı',
            data: @json($weekdayData->values()),
            backgroundColor: colors10.slice(0, 7),
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

// 6. Ortalama masa süresi
@if($avgDuration->isNotEmpty())
new Chart(document.getElementById('chartDuration'), {
    type: 'bar',
    data: {
        labels: @json($avgDuration->pluck('restaurant_name')),
        datasets: [{
            label: 'Ort. Süre (dk)',
            data: @json($avgDuration->map(fn($r) => round($r->avg_min, 1))),
            backgroundColor: '#4e8fcb',
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => v + ' dk' }
            }
        }
    }
});
@endif
</script>
@endpush
