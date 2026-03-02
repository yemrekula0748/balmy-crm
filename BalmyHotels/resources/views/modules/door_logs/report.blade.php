@extends('layouts.default')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Kapı Giriş/Çıkış Raporu</h4>
                <span>Personel çalışma süresi ve giriş/çıkış istatistikleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.index') }}">Kapı Giriş</a></li>
                <li class="breadcrumb-item active">Rapor</li>
            </ol>
        </div>
    </div>

    {{-- FİLTRE FORMU --}}
    <div class="card mb-4">
        <div class="card-header py-3">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filtrele
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('door-reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $filters['dateFrom'] }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $filters['dateTo'] }}">
                </div>
                @if(count($branches) > 0)
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected($filters['branchId'] == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">Departman</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">Tüm Departmanlar</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected($filters['deptId'] == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('door-reports.index') }}" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-redo me-1"></i> Sıfırla
                    </a>
                </div>
                <div class="col-md-2 col-sm-6">
                    <a href="{{ route('door-reports.pdf') }}?{{ http_build_query(request()->query()) }}"
                       target="_blank"
                       class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-file-pdf me-1"></i> PDF İndir
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ÖZET KARTLAR --}}
    <div class="row mb-4">
        @php
            $summaryCards = [
                ['label' => 'Aktif Personel',    'value' => $data['summary']['total_users'],   'icon' => 'fa-users',       'grad' => 'linear-gradient(135deg,#3a0ca3,#4361ee)'],
                ['label' => 'Toplam Giriş',       'value' => $data['summary']['total_entries'], 'icon' => 'fa-sign-in-alt', 'grad' => 'linear-gradient(135deg,#1e7e34,#28a745)'],
                ['label' => 'Toplam Çıkış',       'value' => $data['summary']['total_exits'],  'icon' => 'fa-sign-out-alt','grad' => 'linear-gradient(135deg,#b02a37,#dc3545)'],
                ['label' => 'Toplam Çalışma (sa.)','value' => $data['summary']['total_hours'], 'icon' => 'fa-clock',       'grad' => 'linear-gradient(135deg,#b45309,#f59e0b)'],
            ];
        @endphp
        @foreach($summaryCards as $card)
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 text-white h-100" style="background:{{ $card['grad'] }}">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="mb-1 opacity-75" style="font-size:13px;">{{ $card['label'] }}</p>
                        <h2 class="mb-0 fw-bold">{{ $card['value'] }}</h2>
                    </div>
                    <i class="fas {{ $card['icon'] }} fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- GRAFİKLER --}}
    <div class="row mb-4">
        {{-- Günlük Aktivite --}}
        <div class="col-xl-8 col-12 mb-3 mb-xl-0">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Günlük Toplam Çalışma Saati
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Departman Dağılımı --}}
        <div class="col-xl-4 col-12">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-success"></i>
                        Departman Bazlı Giriş Dağılımı
                    </h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="deptChart" style="max-height:260px;max-width:100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- PERSONEİL TABLOSU --}}
    <div class="card">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">
                <i class="fas fa-table me-2 text-primary"></i>
                Personel Bazlı Detay Raporu
                <span class="badge bg-secondary ms-1">{{ count($data['userStats']) }} kişi</span>
            </h6>
            <a href="{{ route('door-reports.pdf') }}?{{ http_build_query(request()->query()) }}"
               target="_blank" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
        </div>
        <div class="card-body p-0">
            @if(empty($data['userStats']))
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Seçilen filtrelere ait kayıt bulunamadı.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Personel</th>
                            <th>Şube</th>
                            <th>Departman</th>
                            <th class="text-center">Çalışılan Gün</th>
                            <th class="text-center">Toplam Saat</th>
                            <th class="text-center">Giriş</th>
                            <th class="text-center">Çıkış</th>
                            <th class="text-center">Ort. Saat/Gün</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['userStats'] as $i => $stat)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold"
                                          style="width:34px;height:34px;font-size:13px;background:#4361ee;flex-shrink:0;">
                                        {{ strtoupper(substr(optional($stat['user'])->name ?? '?', 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ optional($stat['user'])->name ?? '-' }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ optional($stat['user'])->title ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $stat['branch'] }}</td>
                            <td>{{ $stat['department'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                    {{ $stat['worked_days'] }} gün
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $h = floor($stat['total_minutes'] / 60);
                                    $m = $stat['total_minutes'] % 60;
                                @endphp
                                <span class="fw-bold text-success">{{ $h }}s {{ $m }}dk</span>
                                <div class="text-muted" style="font-size:11px;">{{ $stat['total_hours'] }} sa.</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success light">{{ $stat['entry_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger light">{{ $stat['exit_count'] }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $avg = $stat['worked_days'] > 0
                                        ? round($stat['total_hours'] / $stat['worked_days'], 1)
                                        : 0;
                                @endphp
                                <span class="fw-semibold text-warning">{{ $avg }} sa.</span>
                                {{-- Progress bar --}}
                                @php $pct = min(100, round($avg / 9 * 100)); @endphp
                                <div class="progress mt-1" style="height:3px;">
                                    <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="ps-3" colspan="4">TOPLAM</td>
                            <td class="text-center">—</td>
                            <td class="text-center text-success">{{ $data['summary']['total_hours'] }} sa.</td>
                            <td class="text-center">{{ $data['summary']['total_entries'] }}</td>
                            <td class="text-center">{{ $data['summary']['total_exits'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chart-js/Chart.bundle.min.js') }}"></script>
<script>
(function () {
    // ── Günlük aktivite grafiği
    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($data['dailyLabels']) !!},
                datasets: [{
                    label: 'Çalışma Saati',
                    data:  {!! json_encode($data['dailyMinutes']) !!},
                    backgroundColor: 'rgba(67,97,238,0.7)',
                    borderColor: '#4361ee',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v + ' sa.',
                            font: { size: 11 }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }

    // ── Departman dağılım grafiği
    const deptCtx = document.getElementById('deptChart');
    if (deptCtx) {
        const labels = {!! json_encode($data['deptDist']->keys()->values()) !!};
        const values = {!! json_encode($data['deptDist']->values()->values()) !!};
        const palette = ['#4361ee','#28a745','#dc3545','#f59e0b','#06b6d4','#8b5cf6','#ec4899','#f97316','#6b7280'];
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: palette.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } }
                }
            }
        });
    }
})();
</script>
@endpush
