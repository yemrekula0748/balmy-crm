@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-chart-bar me-2 text-primary"></i>Departmanım
                    @if($dept)<small class="text-muted fs-6"> — {{ $dept->name }}</small>@endif
                </h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Departmanım</li>
            </ol>
        </div>
    </div>

    @if(!$dept)
    <div class="alert alert-warning">Herhangi bir departmana atanmamışsınız.</div>
    @else

    {{-- Özet Kartlar --}}
    <div class="row g-3 mb-4">
        @php
        $cards = [
            ['label'=>'Açık',       'key'=>'open',        'color'=>'danger',  'icon'=>'fa-exclamation-circle'],
            ['label'=>'Devam Eden', 'key'=>'in_progress',  'color'=>'warning', 'icon'=>'fa-tools'],
            ['label'=>'Kapalı',     'key'=>'closed',       'color'=>'success', 'icon'=>'fa-check-double'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="col-6 col-md-3">
            <div class="card border-bottom border-4 border-{{ $c['color'] }}">
                <div class="card-body text-center py-3">
                    <i class="fas {{ $c['icon'] }} fa-2x text-{{ $c['color'] }} mb-2 d-block"></i>
                    <h3 class="fw-bold mb-0">{{ $totals[$c['key']] ?? 0 }}</h3>
                    <small class="text-muted">{{ $c['label'] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 align-items-start">
        {{-- Arıza Türü Performans --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Arıza Türü Performansı</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Arıza Türü</th>
                                    <th class="text-center">Toplam</th>
                                    <th class="text-center">Hedef (sa)</th>
                                    <th class="text-center">Ort. Süre (sa)</th>
                                    <th class="text-center">Zamanında</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($typePerformance as $row)
                                @php
                                    $onTime = $row['on_time_pct'] ?? 0;
                                    $barColor = $onTime >= 80 ? 'success' : ($onTime >= 50 ? 'warning' : 'danger');
                                @endphp
                                <tr>
                                    <td>{{ $row['type_name'] }}</td>
                                    <td class="text-center">{{ $row['total'] }}</td>
                                    <td class="text-center">{{ $row['target_hours'] }}</td>
                                    <td class="text-center">
                                        @if($row['avg_hours'])
                                            <span class="fw-semibold text-{{ $row['avg_hours'] <= $row['target_hours'] ? 'success' : 'danger' }}">
                                                {{ number_format($row['avg_hours'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="min-width:100px">
                                        <div class="progress" style="height:6px;margin-bottom:2px">
                                            <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $onTime }}%"></div>
                                        </div>
                                        <small class="text-{{ $barColor }}">%{{ number_format($onTime, 0) }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Henüz kaydedilmiş arıza yok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($avgResolutionHours ?? null)
                <div class="card-footer text-muted small">
                    <i class="fas fa-clock me-1"></i> Kapalı arızalarda ortalama çözüm süresi:
                    <strong>{{ number_format($avgResolutionHours ?? 0, 1) }} saat</strong>
                </div>
                @endif
            </div>
        </div>

        {{-- Aylık Eğilim + Tür Dağılımı --}}
        <div class="col-lg-5">
            {{-- Aylık Eğilim --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Aylık Eğilim (Son 6 Ay)</h5>
                </div>
                <div class="card-body">
                    <div style="position:relative;height:200px">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Türe Göre Dağılım --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-tags me-2 text-primary"></i>Türe Göre Dağılım</h5>
                </div>
                <div class="card-body">
                    @forelse($byType as $typeRow)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">{{ $typeRow['type_name'] }}</span>
                        <span class="badge bg-secondary">{{ $typeRow['count'] }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">Veri yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const monthlyData = @json($monthlyTrend);
    const labels = monthlyData.map(r => r.month);
    const counts = monthlyData.map(r => r.total);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Arıza Sayısı',
                data: counts,
                backgroundColor: 'rgba(99,120,255,0.6)',
                borderColor: '#6378ff',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
})();
</script>
@endpush
