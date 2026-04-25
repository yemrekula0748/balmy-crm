@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Denetim Analiz & İstatistik</h4>
                <span>Şube ve departman bazlı denetim raporları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">İç Denetim</a></li>
                <li class="breadcrumb-item active">Analiz</li>
            </ol>
        </div>
    </div>

    {{-- FİLTRELER --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Filtrele</h5></div>
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Departman</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">Tüm Departmanlar</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Denetim Tipi</label>
                    <select name="audit_type_id" class="form-select form-select-sm">
                        <option value="">Tüm Tipler</option>
                        @foreach($auditTypes as $t)
                            <option value="{{ $t->id }}" @selected(request('audit_type_id') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Uygula</button>
                    <a href="{{ route('audit.analytics.index') }}" class="btn btn-secondary btn-sm">Sıfırla</a>
                </div>
            </form>
        </div>
    </div>

    {{-- PDF EXPORT --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('audit.analytics.pdf', request()->query()) }}"
           class="btn btn-danger"
           target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                 stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            PDF Raporu İndir
        </a>
    </div>

    {{-- ÖZET KARTLAR --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Toplam Denetim</p>
                        <h2 class="fw-bold mb-0" style="color:#c19b77;">{{ $totalAudits }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdf5ee;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#c19b77" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Toplam Uygunsuzluk</p>
                        <h2 class="fw-bold mb-0 text-warning">{{ $totalNonconformities }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fff8e1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#ffc107" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Açık Uygunsuzluk</p>
                        <h2 class="fw-bold mb-0 text-danger">{{ $openNonconformities }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdecea;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#dc3545" stroke-width="2" viewBox="0 0 24 24">
                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Çözülen Uygunsuzluk</p>
                        <h2 class="fw-bold mb-0 text-success">{{ $resolvedNonconformities }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#e8f5e9;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#28a745" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRAFİKLER --}}
    <div class="row mb-4">
        <div class="col-xl-5 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Denetim Tipine Göre Dağılım</h5></div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="typeChart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-7 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Aylık Denetim & Uygunsuzluk Trendi</h5></div>
                <div class="card-body">
                    <canvas id="trendChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Şube Bazlı --}}
        <div class="col-xl-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Şube Bazlı İstatistik</h5></div>
                <div class="card-body">
                    @forelse($byBranch as $row)
                    @php $pct = $totalAudits > 0 ? $row['audit_count'] / $totalAudits * 100 : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold small">{{ $row['branch_name'] }}</span>
                            <span class="small text-muted">{{ $row['audit_count'] }} denetim | {{ $row['nc_count'] }} uygunsuzluk</span>
                        </div>
                        <div class="progress" style="height:12px;border-radius:6px;">
                            <div class="progress-bar" style="width:{{ $pct }}%;background:#c19b77;"></div>
                        </div>
                        @if($row['open_nc'] > 0)
                        <small class="text-danger">{{ $row['open_nc'] }} açık uygunsuzluk</small>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Kayıt yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Departman Bazlı --}}
        <div class="col-xl-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Departman Bazlı İstatistik</h5></div>
                <div class="card-body">
                    @forelse($byDepartment as $row)
                    @php $maxNc = $byDepartment->max('nc_count'); $pct = $maxNc > 0 ? $row['nc_count'] / $maxNc * 100 : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold small">
                                <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{{ $row['dept_color'] }}"></span>
                                {{ $row['dept_name'] }}
                                <small class="text-muted ms-1">/ {{ $row['branch_name'] }}</small>
                            </span>
                            <span class="small text-muted">{{ $row['audit_count'] }} denetim | {{ $row['nc_count'] }} uy.</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:6px;">
                            <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $row['dept_color'] }};"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Kayıt yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Denetim Tipi Tablosu --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Denetim Tipi Performans Tablosu</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Denetim Tipi</th>
                            <th class="text-center">Denetim Sayısı</th>
                            <th class="text-center">Uygunsuzluk</th>
                            <th class="text-center">Açık</th>
                            <th class="text-center">Ort. Uygunsuzluk/Denetim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byType as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['type_name'] }}</td>
                            <td class="text-center">{{ $row['audit_count'] }}</td>
                            <td class="text-center">
                                <span class="{{ $row['nc_count'] > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                                    {{ $row['nc_count'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($row['open_nc'] > 0)
                                    <span class="badge badge-danger light">{{ $row['open_nc'] }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $row['audit_count'] > 0 ? round($row['nc_count'] / $row['audit_count'], 1) : 0 }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Kayıt yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// Denetim tipi dağılım donut
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byType->pluck('type_name')) !!},
        datasets: [{
            data: {!! json_encode($byType->pluck('audit_count')) !!},
            backgroundColor: ['#c19b77','#5b8ff9','#5ad8a6','#f6bd16','#e86452','#6dc8ec','#945fb9','#ff9845'],
            borderWidth: 2,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'right' } } }
});

// Aylık trend
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
        datasets: [
            {
                label: 'Denetim',
                data: {!! json_encode($monthlyTrend->pluck('audits')) !!},
                backgroundColor: 'rgba(193,155,119,0.7)',
                borderColor: '#c19b77',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Uygunsuzluk',
                data: {!! json_encode($monthlyTrend->pluck('nc_total')) !!},
                backgroundColor: 'rgba(220,53,69,0.55)',
                borderColor: '#dc3545',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endpush
