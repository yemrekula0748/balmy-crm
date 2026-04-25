@extends('layouts.default')
@section('title', 'Servis Raporları')

@section('content')
<div class="container-fluid">
    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Raporları</h4>
                <span>{{ $from->format('d.m.Y') }} — {{ $to->format('d.m.Y') }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="#">Servis Takip</a></li>
                <li class="breadcrumb-item active">Raporlar</li>
            </ol>
        </div>
    </div>

    {{-- Filtre --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('shuttle.reports.index') }}" id="reportForm"
                          class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1 small">Şube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">— Tüm Şubeler —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 small">Araç</label>
                            <select name="vehicle_id" class="form-select form-select-sm">
                                <option value="">— Tüm Araçlar —</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" @selected($vehicleId == $v->id)>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 small">Dönem</label>
                            <select name="period" class="form-select form-select-sm" id="periodSelect">
                                <option value="daily"   @selected($period === 'daily')>Bugün</option>
                                <option value="weekly"  @selected($period === 'weekly')>Bu Hafta</option>
                                <option value="monthly" @selected($period === 'monthly')>Bu Ay</option>
                                <option value="custom"  @selected($period === 'custom')>Özel Aralık</option>
                            </select>
                        </div>
                        <div id="customDateRange" class="{{ $period === 'custom' ? '' : 'd-none' }} col-md-3">
                            <label class="form-label mb-1 small">Tarih Aralığı</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
                                <span class="input-group-text">—</span>
                                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Filtrele
                            </button>
                        </div>
                        <div class="col-auto ms-auto">
                            <a href="{{ route('shuttle.reports.pdf', array_filter([
                                'branch_id'  => $branchId,
                                'vehicle_id' => $vehicleId,
                                'from'       => $from->toDateString(),
                                'to'         => $to->toDateString(),
                            ])) }}"
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF İndir
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Özet Kartları --}}
    <div class="row g-3 mb-3">
        @php
            $cards = [
                ['label' => 'Toplam Gelen', 'value' => number_format($stats['total_arrival']), 'icon' => 'fa-arrow-right', 'color' => '#1e3c72,#2a5298'],
                ['label' => 'Toplam Dönen', 'value' => number_format($stats['total_departure']), 'icon' => 'fa-arrow-left', 'color' => '#134e5e,#71b280'],
                ['label' => 'Toplam Sefer', 'value' => number_format($stats['total_trips']), 'icon' => 'fa-bus', 'color' => '#4a4a4a,#6c757d'],
                ['label' => 'Günlük Ort. Geliş', 'value' => $stats['avg_daily_arrival'], 'icon' => 'fa-chart-line', 'color' => '#8e2de2,#4a00e0'],
                ['label' => 'Günlük Ort. Dönüş', 'value' => $stats['avg_daily_departure'], 'icon' => 'fa-chart-bar', 'color' => '#f7971e,#ffd200'],
                ['label' => 'Ort. Doluluk (Geliş)', 'value' => $stats['avg_occupancy_arr'] . '%', 'icon' => 'fa-percentage', 'color' => '#11998e,#38ef7d'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"
                 style="background:linear-gradient(135deg,{{ $card['color'] }})">
                <div class="card-body text-white text-center py-3">
                    <div class="fs-2 fw-bold">{{ $card['value'] }}</div>
                    <div class="small opacity-75 mt-1">
                        <i class="fas {{ $card['icon'] }} me-1"></i> {{ $card['label'] }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2"></i> Günlük Sefer Grafiği
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Alt: Vardiya & Araç Bazlı --}}
    <div class="row g-3">
        {{-- Vardiya Özeti --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Vardiya Bazlı Özet</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Vardiya</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-center text-primary">Geliş</th>
                                <th class="text-center text-success">Dönüş</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byShift as $shiftName => $data)
                            @if($data['count'] > 0)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $shiftName }}</span>
                                </td>
                                <td class="text-center">{{ $data['count'] }}</td>
                                <td class="text-center fw-bold text-primary">{{ $data['arrival'] }}</td>
                                <td class="text-center fw-bold text-success">{{ $data['departure'] }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">TOPLAM</td>
                                <td class="text-center fw-bold">{{ $stats['total_trips'] }}</td>
                                <td class="text-center fw-bold text-primary">{{ $stats['total_arrival'] }}</td>
                                <td class="text-center fw-bold text-success">{{ $stats['total_departure'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Araç Bazlı --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bus me-2"></i> Araç Bazlı Özet</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Araç</th>
                                    <th class="text-center">Sefer</th>
                                    <th class="text-center text-primary">Geliş</th>
                                    <th class="text-center text-success">Dönüş</th>
                                    <th class="text-center">Dol. % ↑</th>
                                    <th class="text-center">Dol. % ↓</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byVehicle as $data)
                                @if($data['trips'] > 0)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $data['vehicle']->name }}</div>
                                        @if($data['vehicle']->plate)
                                            <small class="text-muted">{{ $data['vehicle']->plate }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $data['trips'] }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $data['arrival'] }}</td>
                                    <td class="text-center fw-bold text-success">{{ $data['departure'] }}</td>
                                    <td class="text-center">
                                        @php $pct = $data['occupancy_arr']; @endphp
                                        <div class="progress" style="height:6px;min-width:60px">
                                            <div class="progress-bar bg-primary" style="width:{{ min($pct, 100) }}%"></div>
                                        </div>
                                        <small>{{ $pct }}%</small>
                                    </td>
                                    <td class="text-center">
                                        @php $pct2 = $data['occupancy_dep']; @endphp
                                        <div class="progress" style="height:6px;min-width:60px">
                                            <div class="progress-bar bg-success" style="width:{{ min($pct2, 100) }}%"></div>
                                        </div>
                                        <small>{{ $pct2 }}%</small>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                @if(empty(array_filter($byVehicle, fn($d) => $d['trips'] > 0)))
                                    <tr><td colspan="6" class="text-center text-muted py-3">Veri bulunamadı.</td></tr>
                                @endif
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    document.getElementById('customDateRange').classList.toggle('d-none', this.value !== 'custom');
});

const ctx = document.getElementById('dailyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: 'Gelen Personel',
                data: @json($chartData['arrival']),
                borderColor: '#2a5298',
                backgroundColor: 'rgba(42,82,152,0.15)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            },
            {
                label: 'Dönen Personel',
                data: @json($chartData['departure']),
                borderColor: '#71b280',
                backgroundColor: 'rgba(113,178,128,0.15)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>
@endpush
