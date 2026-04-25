@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sipariş Analizi</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center gap-2">
            <button type="button" id="btnPdfRapor" class="btn btn-sm text-white d-flex align-items-center gap-1" style="background:#1a1a2e;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/><path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/></svg>
                Gün Bazlı PDF Raporu
            </button>
            <ol class="breadcrumb mb-0">
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

{{-- SweetAlert2 --}}
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.css') }}">
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>

<script>
document.getElementById('btnPdfRapor').addEventListener('click', function () {
    const today    = new Date();
    const fmt      = d => d.toISOString().split('T')[0];

    function startOf(unit) {
        const d = new Date(today);
        if (unit === 'week') {
            const day = d.getDay(); // 0=Sun
            const diff = day === 0 ? -6 : 1 - day;
            d.setDate(d.getDate() + diff);
        } else if (unit === 'month') {
            d.setDate(1);
        } else if (unit === 'year') {
            d.setMonth(0, 1);
        }
        return d;
    }

    Swal.fire({
        title: '<strong>Gün Bazlı PDF Raporu</strong>',
        html: `
            <p class="text-muted mb-3" style="font-size:0.92rem;">Rapor oluşturmak istediğiniz dönemi seçin.</p>
            <div class="d-grid gap-2">
                <button class="btn btn-outline-dark btn-sm period-btn" data-period="week">📅 Bu Hafta</button>
                <button class="btn btn-outline-dark btn-sm period-btn" data-period="month">📆 Bu Ay</button>
                <button class="btn btn-outline-dark btn-sm period-btn" data-period="year">🗓️ Bu Yıl</button>
                <button class="btn btn-outline-secondary btn-sm period-btn" data-period="custom">✏️ Özel Tarih Aralığı</button>
            </div>`,
        showConfirmButton: false,
        showCloseButton: true,
        customClass: { popup: 'shadow-lg' },
        didOpen: function () {
            document.querySelectorAll('.period-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const period = this.dataset.period;

                    if (period === 'custom') {
                        Swal.fire({
                            title: 'Tarih Aralığı Seçin',
                            html: `
                                <div class="mb-3 text-start">
                                    <label class="form-label small fw-semibold">Başlangıç Tarihi</label>
                                    <input type="date" id="swal-date-from" class="form-control form-control-sm" value="${fmt(startOf('month'))}">
                                </div>
                                <div class="text-start">
                                    <label class="form-label small fw-semibold">Bitiş Tarihi</label>
                                    <input type="date" id="swal-date-to" class="form-control form-control-sm" value="${fmt(today)}">
                                </div>`,
                            showCancelButton: true,
                            confirmButtonText: '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2z"/></svg> PDF Oluştur',
                            cancelButtonText: 'İptal',
                            confirmButtonColor: '#1a1a2e',
                            preConfirm: function () {
                                const from = document.getElementById('swal-date-from').value;
                                const to   = document.getElementById('swal-date-to').value;
                                if (!from || !to) {
                                    Swal.showValidationMessage('Lütfen her iki tarihi de giriniz.');
                                    return false;
                                }
                                if (from > to) {
                                    Swal.showValidationMessage('Başlangıç tarihi bitiş tarihinden büyük olamaz.');
                                    return false;
                                }
                                return { from, to };
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                openPdf(result.value.from, result.value.to);
                            }
                        });
                    } else {
                        const from = fmt(startOf(period));
                        const to   = fmt(today);
                        Swal.close();
                        openPdf(from, to);
                    }
                });
            });
        }
    });

    function openPdf(from, to) {
        const params = new URLSearchParams({ date_from: from, date_to: to });
        @if(isset($restaurantId) && $restaurantId)
        params.set('restaurant_id', '{{ $restaurantId }}');
        @endif
        window.open('{{ route("orders.analytics.pdf") }}?' + params.toString(), '_blank');
    }
});
</script>
@endpush
