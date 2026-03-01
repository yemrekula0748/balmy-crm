@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Teknik Arıza Takip</h4>
                <span>Otel arıza bildirimleri ve takibi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Teknik Arıza</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         İSTATİSTİK KARTI SATIRI
    ═══════════════════════════════════════════════════════════ --}}
    <div class="row mb-3">
        {{-- Açık --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Açık Arızalar</p>
                        <h2 class="fw-bold mb-0 text-danger">{{ $statsByStatus['open'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdecea;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#dc3545" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- İşlemde --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">İşlemdeki</p>
                        <h2 class="fw-bold mb-0 text-warning">{{ $statsByStatus['in_progress'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fff8e1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#ffc107" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- Çözüldü --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Çözülen</p>
                        <h2 class="fw-bold mb-0 text-success">{{ $statsByStatus['resolved'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#e8f5e9;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#28a745" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- Ort. Çözüm Süresi --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Ort. Çözüm Süresi</p>
                        <h2 class="fw-bold mb-0" style="color:#c19b77;">
                            {{ ($avgResolution ?? null) ? round($avgResolution) . ' sa.' : '-' }}
                        </h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdf5ee;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#c19b77" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         GRAFİK SATIRI
    ═══════════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        {{-- Öncelik Dağılımı --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Öncelik Dağılımı</h5></div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="priorityChart" height="220"></canvas>
                </div>
            </div>
        </div>
        {{-- Durum Dağılımı --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Durum Dağılımı</h5></div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>
        </div>
        {{-- Aylık Trend --}}
        <div class="col-xl-4 col-lg-12 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Aylık Trend (6 Ay)</h5></div>
                <div class="card-body">
                    <canvas id="trendChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Departman Bazlı --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Departmana Göre Arızalar</h5></div>
                <div class="card-body">
                    @foreach($statsByDept->sortByDesc('total') as $row)
                        @php $pct = $row->total / max($statsByDept->sum('total'), 1) * 100; @endphp
                        <div class="d-flex align-items-center mb-2 gap-2">
                            <div style="width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="small fw-semibold">
                                {{ $row->department?->name ?? 'Atanmamış' }}
                            </div>
                            <div class="flex-fill">
                                <div class="progress" style="height:14px;border-radius:8px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:{{ $pct }}%;background-color:{{ $row->department?->color ?? '#c19b77' }};">
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-secondary ms-1">{{ $row->total }}</span>
                        </div>
                    @endforeach
                    @if($statsByDept->isEmpty())
                        <p class="text-muted text-center mb-0">Henüz kayıt yok.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         ARIZA LİSTESİ
    ═══════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Arıza Kayıtları</h4>
            <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm">
                + Arıza Bildir
            </a>
        </div>
        <div class="card-body">

            {{-- FİLTRELER --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">Tüm Departmanlar</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach(\App\Models\Fault::STATUSES as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Tüm Öncelikler</option>
                        @foreach(\App\Models\Fault::PRIORITIES as $val => $label)
                            <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Başlık, konum, açıklama..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filtrele</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Başlık</th>
                            <th>Öncelik</th>
                            <th>Durum</th>
                            <th>Departman</th>
                            <th>Konum</th>
                            <th>Bildiren</th>
                            <th>Şube</th>
                            <th>Tarih</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faults as $fault)
                            <tr>
                                <td class="text-muted small">{{ $fault->id }}</td>
                                <td>
                                    <a href="{{ route('faults.show', $fault) }}" class="text-dark fw-semibold text-decoration-none">
                                        {{ Str::limit($fault->title, 40) }}
                                    </a>
                                    @if($fault->updates->count() > 0)
                                        <span class="badge bg-light text-muted border ms-1">
                                            {{ $fault->updates->count() }} güncelleme
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php $pc = \App\Models\Fault::PRIORITY_COLORS[$fault->priority]; @endphp
                                    <span class="badge badge-{{ $pc }} light">
                                        {{ \App\Models\Fault::PRIORITIES[$fault->priority] }}
                                    </span>
                                </td>
                                <td>
                                    @php $sc = \App\Models\Fault::STATUS_COLORS[$fault->status]; @endphp
                                    <span class="badge badge-{{ $sc }} light">
                                        {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                    </span>
                                </td>
                                <td>
                                    @if($fault->department)
                                        <span class="badge" style="background:{{ $fault->department->color }};">
                                            {{ $fault->department->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $fault->location ?? '-' }}</small></td>
                                <td><small>{{ $fault->reporter?->name ?? '-' }}</small></td>
                                <td><small>{{ $fault->branch?->name ?? '-' }}</small></td>
                                <td>
                                    <small>{{ $fault->created_at->format('d.m.Y') }}</small><br>
                                    <small class="text-muted">{{ $fault->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="btn btn-primary btn-xs me-1">Detay</a>
                                    <button class="btn btn-danger btn-xs btn-sil"
                                            data-id="{{ $fault->id }}"
                                            data-name="{{ $fault->title }}">Sil</button>
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
                                    Arıza kaydı bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $faults->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// ── Öncelik Donut
new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: {
        labels: ['Düşük', 'Orta', 'Yüksek', 'Kritik'],
        datasets: [{
            data: [
                {{ $statsByPriority['low'] ?? 0 }},
                {{ $statsByPriority['medium'] ?? 0 }},
                {{ $statsByPriority['high'] ?? 0 }},
                {{ $statsByPriority['critical'] ?? 0 }},
            ],
            backgroundColor: ['#28a745','#ffc107','#dc3545','#212529'],
            borderWidth: 2,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// ── Durum Donut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Açık', 'İşlemde', 'Çözüldü', 'Kapalı'],
        datasets: [{
            data: [
                {{ $statsByStatus['open'] ?? 0 }},
                {{ $statsByStatus['in_progress'] ?? 0 }},
                {{ $statsByStatus['resolved'] ?? 0 }},
                {{ $statsByStatus['closed'] ?? 0 }},
            ],
            backgroundColor: ['#dc3545','#ffc107','#28a745','#6c757d'],
            borderWidth: 2,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// ── Aylık Trend Bar
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
        datasets: [{
            label: 'Bildirilen Arıza',
            data: {!! json_encode($monthlyTrend->pluck('total')) !!},
            backgroundColor: 'rgba(193,155,119,0.7)',
            borderColor: '#c19b77',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ── SweetAlert Sil
document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Emin misiniz?',
            text: `"${this.dataset.name}" kaydı silinecek!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(r => {
            if (r.isConfirmed) document.getElementById('form-sil-' + this.dataset.id).submit();
        });
    });
});
</script>
@endpush
