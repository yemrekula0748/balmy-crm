@extends('layouts.default')

@push('styles')
<style>
/* â”€â”€ Stat kartlarÄ± â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.fault-stat-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.07);
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.fault-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.11); }
.fault-stat-card .stat-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.fault-stat-card .stat-value { font-size: 1.8rem; font-weight: 700; line-height: 1.1; }
.fault-stat-card .stat-label { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }

/* â”€â”€ Grafik kartlarÄ± â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.chart-card {
    border: none; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.07);
}
.chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f1f3f5;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 1rem 1.25rem 0.75rem;
}

/* â”€â”€ Filtre bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.filter-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
.filter-bar .form-select,
.filter-bar .form-control {
    border-radius: 8px; border-color: #d0d5dd; font-size: 0.85rem;
}

/* â”€â”€ Tablo â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.faults-table { border-collapse: separate; border-spacing: 0; }
.faults-table thead th {
    background: #f8f9fc;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #667085;
    border-bottom: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    white-space: nowrap;
}
.faults-table tbody tr {
    transition: background .12s;
}
.faults-table tbody tr:hover { background: #f8f9fc; }
.faults-table td { padding: 0.7rem 1rem; vertical-align: middle; }
.faults-table tbody tr:last-child td { border-bottom: none; }

.priority-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
}
.status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
}
.status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }

/* Departman badge */
.dept-badge {
    padding: 3px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 600;
    color: #fff;
}

/* BaÅŸlÄ±k + Breadcrumb kutusu */
.page-header-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 14px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.page-header-card .breadcrumb-item a { color: rgba(255,255,255,0.6); }
.page-header-card .breadcrumb-item.active { color: rgba(255,255,255,0.9); }
.page-header-card .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.4); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="page-header-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fas fa-tools me-2" style="color:#f97316"></i>Teknik ArÄ±za Takip</h4>
                <ol class="breadcrumb mb-0" style="background:transparent;padding:0;">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Teknik ArÄ±za</li>
                </ol>
            </div>
            <a href="{{ route('faults.create') }}" class="btn btn-danger fw-semibold px-4">
                <i class="fas fa-plus me-2"></i>ArÄ±za Bildir
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4"
             style="background:#e8f5e9;">
            <i class="fas fa-check-circle text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- â”€â”€ Ä°STATÄ°STÄ°K KARTLARI â”€â”€ --}}
    <div class="row g-3 mb-4">
        @php
        $statCards = [
            ['label'=>'AÃ§Ä±k ArÄ±zalar',    'value'=>$statsByStatus['open'] ?? 0,        'color'=>'#dc3545','bg'=>'#fdecea','icon'=>'fa-exclamation-circle'],
            ['label'=>'Ä°ÅŸlemdeki',        'value'=>$statsByStatus['in_progress'] ?? 0,  'color'=>'#f97316','bg'=>'#fff3e0','icon'=>'fa-sync-alt'],
            ['label'=>'Ã‡Ã¶zÃ¼len',          'value'=>$statsByStatus['resolved'] ?? 0,     'color'=>'#28a745','bg'=>'#e8f5e9','icon'=>'fa-check-circle'],
            ['label'=>'KapalÄ±',           'value'=>$statsByStatus['closed'] ?? 0,       'color'=>'#6c757d','bg'=>'#f0f0f0','icon'=>'fa-times-circle'],
            ['label'=>'Ort. Ã‡Ã¶zÃ¼m (sa)',  'value'=>($avgResolution ?? null) ? round($avgResolution).' sa.' : 'â€”', 'color'=>'#c19b77','bg'=>'#fdf5ee','icon'=>'fa-clock'],
        ];
        @endphp
        @foreach($statCards as $sc)
        <div class="col-xl col-md-4 col-sm-6 col-12">
            <div class="fault-stat-card card h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3 px-3">
                    <div class="stat-icon" style="background:{{ $sc['bg'] }}">
                        <i class="fas {{ $sc['icon'] }}" style="color:{{ $sc['color'] }}"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="color:{{ $sc['color'] }}">{{ $sc['value'] }}</div>
                        <div class="stat-label">{{ $sc['label'] }}</div>
                    </div>
                </div>
                <div style="height:3px;background:{{ $sc['color'] }};opacity:.7"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- â”€â”€ GRAFÄ°KLER â”€â”€ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-chart-pie text-warning"></i> Ã–ncelik DaÄŸÄ±lÄ±mÄ±
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:200px">
                    <canvas id="priorityChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-chart-donut text-info" style="color:#4361ee!important"></i> Durum DaÄŸÄ±lÄ±mÄ±
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:200px">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-12">
            <div class="chart-card card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-chart-bar text-primary"></i> AylÄ±k Trend (6 Ay)
                </div>
                <div class="card-body" style="min-height:200px">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ DEPARTMAN BAZLI â”€â”€ --}}
    <div class="chart-card card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="fas fa-sitemap" style="color:#4361ee"></i> Departmana GÃ¶re ArÄ±zalar
        </div>
        <div class="card-body">
            @forelse($statsByDept->sortByDesc('total') as $row)
                @php $pct = $row->total / max($statsByDept->sum('total'), 1) * 100; @endphp
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="small fw-semibold text-truncate" style="width:160px;flex-shrink:0">
                        {{ $row->department?->name ?? 'AtanmamÄ±ÅŸ' }}
                    </div>
                    <div class="flex-fill">
                        <div class="progress" style="height:10px;border-radius:6px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ $pct }}%;background-color:{{ $row->department?->color ?? '#c19b77' }};border-radius:6px;">
                            </div>
                        </div>
                    </div>
                    <span class="badge rounded-pill" style="background:#f0f0f0;color:#344054;min-width:28px">{{ $row->total }}</span>
                </div>
            @empty
                <p class="text-muted text-center mb-0">HenÃ¼z kayÄ±t yok.</p>
            @endforelse
        </div>
    </div>

    {{-- â”€â”€ ARIZA LÄ°STESÄ° â”€â”€ --}}
    <div class="chart-card card">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold" style="font-size:0.95rem"><i class="fas fa-list me-2 text-muted"></i>ArÄ±za KayÄ±tlarÄ±</span>
            <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm fw-semibold">
                <i class="fas fa-plus me-1"></i> ArÄ±za Bildir
            </a>
        </div>
        <div class="card-body p-0">

            {{-- FÄ°LTRELER --}}
            <div class="filter-bar border-bottom rounded-0" style="border-radius:0!important">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Åube</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">TÃ¼mÃ¼</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Departman</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">TÃ¼mÃ¼</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Durum</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">TÃ¼mÃ¼</option>
                            @foreach(\App\Models\Fault::STATUSES as $val => $label)
                                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Ã–ncelik</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">TÃ¼mÃ¼</option>
                            @foreach(\App\Models\Fault::PRIORITIES as $val => $label)
                                <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Arama</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="BaÅŸlÄ±k, konum, aÃ§Ä±klama..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request()->hasAny(['branch_id','department_id','status','priority','search']))
                            <a href="{{ route('faults.index') }}" class="btn btn-outline-secondary btn-sm" title="Temizle">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table faults-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>BaÅŸlÄ±k</th>
                            <th>Ã–ncelik</th>
                            <th>Durum</th>
                            <th>Departman</th>
                            <th>Konum</th>
                            <th>Bildiren</th>
                            <th>Åube</th>
                            <th>Tarih</th>
                            <th class="text-end">Ä°ÅŸlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faults as $fault)
                        @php
                            $pc = \App\Models\Fault::PRIORITY_COLORS[$fault->priority];
                            $sc = \App\Models\Fault::STATUS_COLORS[$fault->status];
                            $priorityColors = ['danger'=>['#fdecea','#dc3545'],'warning'=>['#fff8e1','#f97316'],'success'=>['#e8f5e9','#28a745'],'dark'=>['#f0f0f0','#212529'],'secondary'=>['#f0f0f0','#6c757d']];
                            $statusColors  = ['danger'=>['#fdecea','#dc3545'],'warning'=>['#fff8e1','#f97316'],'success'=>['#e8f5e9','#28a745'],'secondary'=>['#f0f0f0','#6c757d']];
                            $pBg = $priorityColors[$pc][0] ?? '#f0f0f0';
                            $pFg = $priorityColors[$pc][1] ?? '#333';
                            $sBg = $statusColors[$sc][0]   ?? '#f0f0f0';
                            $sFg = $statusColors[$sc][1]   ?? '#333';
                        @endphp
                            <tr>
                                <td class="text-muted small fw-semibold">#{{ $fault->id }}</td>
                                <td>
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="fw-semibold text-decoration-none"
                                       style="color:#1a1a2e">
                                        {{ Str::limit($fault->title, 38) }}
                                    </a>
                                    @if($fault->updates->count() > 0)
                                        <span class="badge bg-light text-muted border ms-1" style="font-size:0.68rem">
                                            {{ $fault->updates->count() }} gÃ¼ncelleme
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="priority-pill" style="background:{{ $pBg }};color:{{ $pFg }}">
                                        {{ \App\Models\Fault::PRIORITIES[$fault->priority] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill" style="background:{{ $sBg }};color:{{ $sFg }}">
                                        <span class="status-dot" style="background:{{ $sFg }}"></span>
                                        {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                    </span>
                                </td>
                                <td>
                                    @if($fault->department)
                                        <span class="dept-badge"
                                              style="background:{{ $fault->department->color }}">
                                            {{ $fault->department->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">â€”</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $fault->faultLocation?->name ?? 'â€”' }}</td>
                                <td class="small">{{ $fault->reporter?->name ?? 'â€”' }}</td>
                                <td class="small text-muted">{{ $fault->branch?->name ?? 'â€”' }}</td>
                                <td>
                                    <span class="small">{{ $fault->created_at->format('d.m.Y') }}</span>
                                    <br><span class="text-muted" style="font-size:0.72rem">{{ $fault->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="text-end" style="white-space:nowrap">
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="btn btn-sm btn-outline-primary py-1 px-2 me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger py-1 px-2 btn-sil"
                                            data-id="{{ $fault->id }}"
                                            data-name="{{ $fault->title }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="form-sil-{{ $fault->id }}"
                                          action="{{ route('faults.destroy', $fault) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    ArÄ±za kaydÄ± bulunamadÄ±.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($faults->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $faults->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } }
};

new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: {
        labels: ['DÃ¼ÅŸÃ¼k','Orta','YÃ¼ksek','Kritik'],
        datasets: [{ data: [{{ $statsByPriority['low'] ?? 0 }},{{ $statsByPriority['medium'] ?? 0 }},{{ $statsByPriority['high'] ?? 0 }},{{ $statsByPriority['critical'] ?? 0 }}], backgroundColor: ['#28a745','#ffc107','#dc3545','#212529'], borderWidth: 2 }]
    },
    options: chartDefaults
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['AÃ§Ä±k','Ä°ÅŸlemde','Ã‡Ã¶zÃ¼ldÃ¼','KapalÄ±'],
        datasets: [{ data: [{{ $statsByStatus['open'] ?? 0 }},{{ $statsByStatus['in_progress'] ?? 0 }},{{ $statsByStatus['resolved'] ?? 0 }},{{ $statsByStatus['closed'] ?? 0 }}], backgroundColor: ['#dc3545','#f97316','#28a745','#6c757d'], borderWidth: 2 }]
    },
    options: chartDefaults
});

new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
        datasets: [{
            label: 'Bildirilen ArÄ±za',
            data: {!! json_encode($monthlyTrend->pluck('total')) !!},
            backgroundColor: 'rgba(193,155,119,0.7)',
            borderColor: '#c19b77', borderWidth: 1, borderRadius: 5
        }]
    },
    options: { ...chartDefaults, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Emin misiniz?',
            text: `"${this.dataset.name}" silinecek!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sil',
            cancelButtonText: 'Ä°ptal'
        }).then(r => { if (r.isConfirmed) document.getElementById('form-sil-' + this.dataset.id).submit(); });
    });
});
</script>
@endpush
