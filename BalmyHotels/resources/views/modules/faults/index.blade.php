@extends('layouts.default')

@section('title', 'Teknik Arıza Takip')

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Teknik Arıza Takip</h4>
                <span class="text-muted" style="font-size:.82rem">Tüm arıza bildirimleri ve durum takibi</span>
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
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 mt-2">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Özet Stat Kartları --}}
    <div class="row g-2 mb-3 mt-1">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#ef4444,#f87171);box-shadow:0 2px 12px rgba(239,68,68,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-exclamation text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $statsByStatus['open'] ?? 0 }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">AÇIK ARIZA</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#f59e0b,#fbbf24);box-shadow:0 2px 12px rgba(245,158,11,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-rotate text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $statsByStatus['in_progress'] ?? 0 }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">İŞLEMDE</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#10b981,#34d399);box-shadow:0 2px 12px rgba(16,185,129,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-check text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $statsByStatus['resolved'] ?? 0 }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">ÇÖZÜLDÜ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#6b7280,#9ca3af);box-shadow:0 2px 12px rgba(107,114,128,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-xmark text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $statsByStatus['closed'] ?? 0 }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">KAPALI</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#6366f1,#818cf8);box-shadow:0 2px 12px rgba(99,102,241,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-clock text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">
                            {{ $avgResolution ? round($avgResolution).'sa' : '—' }}
                        </div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">ORT. ÇÖZÜM</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik + Departman --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-chart-donut" style="color:#6366f1"></i>
                        <span class="fw-semibold" style="font-size:.88rem">Durum Dağılımı</span>
                    </div>
                    <canvas id="statusChart" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-chart-bar" style="color:#f59e0b"></i>
                        <span class="fw-semibold" style="font-size:.88rem">Aylık Trend (6 Ay)</span>
                    </div>
                    <canvas id="trendChart" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-sitemap" style="color:#10b981"></i>
                        <span class="fw-semibold" style="font-size:.88rem">Departman Bazında</span>
                    </div>
                    @php $deptTotal = $statsByDept->sum('total'); @endphp
                    @forelse($statsByDept->sortByDesc('total')->take(6) as $row)
                    @php
                        $dept = $departments->firstWhere('id', $row->assigned_department_id);
                        $pct  = $deptTotal > 0 ? round($row->total / $deptTotal * 100) : 0;
                        $col  = $dept?->color ?? '#6366f1';
                    @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-truncate" style="font-size:.72rem;font-weight:600;color:#374151;max-width:120px">
                                {{ $dept?->name ?? 'Atanmamış' }}
                            </span>
                            <span style="font-size:.68rem;color:#94a3b8">{{ $row->total }}</span>
                        </div>
                        <div class="rounded-pill" style="height:5px;background:#f1f5f9">
                            <div class="rounded-pill h-100" style="width:{{ $pct }}%;background:{{ $col }}"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small text-center mb-0">Kayıt yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre + Tablo Kartı --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4"
             style="border-bottom:1px solid #f1f5f9;border-radius:12px 12px 0 0">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list" style="color:#6366f1"></i>
                <span class="fw-bold" style="font-size:.9rem">Arıza Kayıtları</span>
                <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.7rem">
                    {{ number_format($faults->total()) }} kayıt
                </span>
            </div>
            <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm fw-semibold">
                <i class="fas fa-plus me-1"></i> Arıza Bildir
            </a>
        </div>

        {{-- Filtreler --}}
        <div class="px-4 py-3" style="background:#fafbff;border-bottom:1px solid #f1f5f9">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <select name="branch_id" class="form-select form-select-sm" style="border-radius:8px">
                            <option value="">Tüm Şubeler</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <select name="department_id" class="form-select form-select-sm" style="border-radius:8px">
                            <option value="">Tüm Departmanlar</option>
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <select name="status" class="form-select form-select-sm" style="border-radius:8px">
                            <option value="">Tüm Durumlar</option>
                            @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search" style="font-size:.75rem"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" style="border-radius:0 8px 8px 0"
                                   placeholder="Başlık veya açıklama..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:8px">
                            <i class="fas fa-search me-1"></i>Filtrele
                        </button>
                        @if(request()->hasAny(['branch_id','department_id','status','search']))
                        <a href="{{ route('faults.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:8px">
                            <i class="fas fa-xmark me-1"></i>Temizle
                        </a>
                        @endif
                        <a href="{{ route('faults.stats') }}" class="btn btn-outline-secondary btn-sm ms-auto" style="border-radius:8px">
                            <i class="fas fa-chart-bar me-1"></i>İstatistikler
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Arıza Satırları --}}
        <div class="p-3 d-flex flex-column gap-2">
            @forelse($faults as $fault)
            @php
                $scMap  = ['open'=>['#ef4444','#fef2f2'],'in_progress'=>['#f59e0b','#fffbeb'],'resolved'=>['#10b981','#f0fdf4'],'closed'=>['#6b7280','#f1f5f9']];
                [$sFg,$sBg] = $scMap[$fault->status] ?? ['#6b7280','#f1f5f9'];
                $deptColor  = $fault->department?->color ?? '#6366f1';
            @endphp
            <div class="card border-0" style="border-radius:10px;border-left:4px solid {{ $sFg }}!important;box-shadow:0 1px 8px rgba(0,0,0,.06);transition:box-shadow .15s"
                 onmouseenter="this.style.boxShadow='0 3px 16px rgba(0,0,0,.1)'"
                 onmouseleave="this.style.boxShadow='0 1px 8px rgba(0,0,0,.06)'">
                <div class="card-body py-2 px-3">
                    <div class="row align-items-center g-0">

                        {{-- ID + Başlık + Departman --}}
                        <div class="col-xl-4 col-lg-4 col-md-6 pe-3">
                            <div class="d-flex align-items-start gap-2">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                     style="width:30px;height:30px;background:{{ $sBg }}">
                                    <i class="fas fa-wrench" style="font-size:.7rem;color:{{ $sFg }}"></i>
                                </div>
                                <div style="min-width:0">
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="fw-semibold text-dark text-decoration-none d-block text-truncate"
                                       style="font-size:.83rem" title="{{ $fault->title }}">{{ $fault->title }}</a>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        @if($fault->department)
                                        <span class="badge" style="font-size:.65rem;background:{{ $deptColor }};color:#fff;border-radius:5px">
                                            {{ $fault->department->name }}
                                        </span>
                                        @endif
                                        <span class="text-muted" style="font-size:.65rem">#{{ $fault->id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Durum + Konum --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 d-none d-md-block border-start ps-3 pe-3" style="border-color:#f1f5f9!important">
                            <span class="badge rounded-pill mb-1" style="font-size:.72rem;background:{{ $sBg }};color:{{ $sFg }}">
                                <span class="rounded-circle d-inline-block me-1" style="width:6px;height:6px;background:{{ $sFg }}"></span>
                                {{ \App\Models\Fault::STATUSES[$fault->status] }}
                            </span>
                            @if($fault->faultLocation)
                            <div class="text-truncate" style="font-size:.68rem;color:#64748b">
                                <i class="fas fa-map-marker-alt me-1" style="color:#94a3b8"></i>
                                {{ $fault->faultLocation->name }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}
                            </div>
                            @endif
                            @if($fault->faultType)
                            <div class="text-truncate" style="font-size:.65rem;color:#94a3b8">
                                <i class="fas fa-tag me-1"></i>{{ $fault->faultType->name }}
                            </div>
                            @endif
                        </div>

                        {{-- Şube + Bildiren --}}
                        <div class="col-xl-3 col-lg-3 d-none d-lg-block border-start ps-3 pe-3" style="border-color:#f1f5f9!important">
                            <div style="font-size:.72rem;color:#374151;font-weight:600" class="mb-1">
                                <i class="fas fa-building me-1" style="color:#94a3b8"></i>{{ $fault->branch?->name ?? '—' }}
                            </div>
                            <div style="font-size:.68rem;color:#64748b">
                                <i class="fas fa-user me-1" style="color:#94a3b8"></i>{{ $fault->reporter?->name ?? '—' }}
                            </div>
                            <div style="font-size:.65rem;color:#94a3b8" class="mt-1">
                                {{ $fault->created_at->format('d.m.Y H:i') }}
                                &middot; {{ $fault->created_at->diffForHumans() }}
                            </div>
                        </div>

                        {{-- Eylemler --}}
                        <div class="col-xl-2 col-lg-2 col-md-12 d-flex justify-content-end align-items-center gap-1 mt-2 mt-lg-0">
                            <a href="{{ route('faults.show', $fault) }}"
                               class="btn btn-sm btn-outline-primary" style="border-radius:7px;padding:3px 8px">
                                <i class="fas fa-eye" style="font-size:.75rem"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-sil"
                                    style="border-radius:7px;padding:3px 8px"
                                    data-id="{{ $fault->id }}" data-name="{{ $fault->title }}">
                                <i class="fas fa-trash-alt" style="font-size:.75rem"></i>
                            </button>
                            <form id="form-sil-{{ $fault->id }}" action="{{ route('faults.destroy', $fault) }}"
                                  method="POST" class="d-none">@csrf @method('DELETE')</form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                <span>Arıza kaydı bulunamadı.</span>
            </div>
            @endforelse
        </div>

        @if($faults->hasPages())
        <div class="px-4 py-3 border-top">{{ $faults->links() }}</div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
var statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Açık','İşlemde','Çözüldü','Kapalı'],
        datasets: [{
            data: [{{ $statsByStatus['open'] ?? 0 }},{{ $statsByStatus['in_progress'] ?? 0 }},{{ $statsByStatus['resolved'] ?? 0 }},{{ $statsByStatus['closed'] ?? 0 }}],
            backgroundColor: ['#ef4444','#f59e0b','#10b981','#9ca3af'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:10, font:{ size:11 } } } } }
});

var trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
        datasets: [{
            label: 'Arıza',
            data: {!! json_encode($monthlyTrend->pluck('total')) !!},
            backgroundColor: 'rgba(99,102,241,.7)',
            borderRadius: 5, borderWidth: 0
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } }, x:{ grid:{ display:false } } }
    }
});

document.querySelectorAll('.btn-sil').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var name = this.dataset.name;
        var id   = this.dataset.id;
        Swal.fire({
            title: 'Emin misiniz?',
            text: '"' + name + '" silinecek!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sil',
            cancelButtonText: 'İptal'
        }).then(function(r) { if (r.isConfirmed) document.getElementById('form-sil-' + id).submit(); });
    });
});
</script>
@endpush
