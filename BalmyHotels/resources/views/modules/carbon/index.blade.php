@extends('layouts.default')

@push('styles')
<style>
.carbon-stat-card { border-radius: 14px; overflow: hidden; }
.carbon-stat-card .stat-icon { width: 52px; height: 52px; border-radius: 10px; display:flex; align-items:center; justify-content:center; }
.scope-badge-1 { background:#ffeaea; color:#c0392b; border:1px solid #e74c3c; }
.scope-badge-2 { background:#fff8e1; color:#e67e22; border:1px solid #f39c12; }
.scope-badge-3 { background:#e8f5e9; color:#1a6b3c; border:1px solid #27ae60; }
.hcmi-ring { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem; border:3px solid; }
.rating-Ap  { background:#e8f5e9; color:#1a6b3c; border-color:#1a6b3c; }
.rating-A   { background:#d4edda; color:#27ae60; border-color:#27ae60; }
.rating-B   { background:#fff3cd; color:#e67e22; border-color:#f39c12; }
.rating-C   { background:#ffe5b4; color:#d35400; border-color:#e67e22; }
.rating-D   { background:#fde8e8; color:#c0392b; border-color:#e74c3c; }
.rating-E   { background:#f3e5f5; color:#7d3c98; border-color:#8e44ad; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>🌿 Karbon Ayak İzi</h4>
                <span>ISO 14064 · HCMI · GHG Protocol · CSRD AB Uyumlu Raporlar</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Karbon Ayak İzi</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Özet İstatistik Kartları --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card carbon-stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small">Toplam Rapor</div>
                        <div class="fw-bold fs-4">{{ number_format($totalReports) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card carbon-stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9z"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small">Toplam CO₂e (Final Raporlar)</div>
                        <div class="fw-bold fs-4">{{ number_format($totalCo2/1000, 1) }} <span class="fs-6 fw-normal">tCO₂e</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card carbon-stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small">Ort. HCMI Skoru</div>
                        <div class="fw-bold fs-4">{{ $avgScore ? number_format($avgScore, 1) : '—' }} <span class="fs-6 fw-normal">/100</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card carbon-stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2980b9" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small">Son Final Rapor</div>
                        <div class="fw-bold" style="font-size:.95rem">{{ $latestReport ? $latestReport->period_start->format('M Y') : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre & Yeni Rapor --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('carbon.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 small">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">— Tüm Şubeler —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small">Yıl</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">— Tüm Yıllar —</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        <option value="draft"    @selected(request('status')=='draft')>Taslak</option>
                        <option value="final"    @selected(request('status')=='final')>Final</option>
                        <option value="verified" @selected(request('status')=='verified')>Doğrulandı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small">Rapor Tipi</label>
                    <select name="report_type" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        <option value="monthly"   @selected(request('report_type')=='monthly')>Aylık</option>
                        <option value="quarterly" @selected(request('report_type')=='quarterly')>Çeyreklik</option>
                        <option value="annual"    @selected(request('report_type')=='annual')>Yıllık</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('carbon.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                    @can('create', App\Models\CarbonFootprintReport::class)
                    @endcan
                    @if(auth()->user()->hasPermission('carbon_footprint', 'create'))
                    <a href="{{ route('carbon.create') }}" class="btn btn-success btn-sm ms-auto">
                        <i class="fas fa-plus me-1"></i> Yeni Rapor
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between py-3">
            <h6 class="mb-0 fw-bold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 8v4l3 3"/></svg>
                Karbon Ayak İzi Raporları
            </h6>
            <span class="badge bg-secondary">{{ $reports->total() }} rapor</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Rapor Adı</th>
                            <th>Şube</th>
                            <th>Dönem</th>
                            <th>Tip</th>
                            <th class="text-center">Toplam CO₂e</th>
                            <th class="text-center">kgCO₂e/Oda-Gece</th>
                            <th class="text-center">HCMI</th>
                            <th class="text-center">Durum</th>
                            <th class="text-end pe-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $r->id }}</td>
                            <td>
                                <a href="{{ route('carbon.show', $r) }}" class="fw-semibold text-decoration-none">{{ $r->title }}</a>
                                @if($r->standards_applied && count($r->standards_applied))
                                    <br>
                                    @foreach(array_slice($r->standards_applied, 0, 3) as $s)
                                        <span class="badge bg-light text-dark border small" style="font-size:0.68rem">{{ $s }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-muted small">{{ $r->branch?->name ?? '—' }}</td>
                            <td class="small">
                                {{ $r->period_start->format('d.m.Y') }}<br>
                                <span class="text-muted">{{ $r->period_end->format('d.m.Y') }}</span>
                            </td>
                            <td>
                                @switch($r->report_type)
                                    @case('monthly')   <span class="badge bg-light text-dark border">Aylık</span>   @break
                                    @case('quarterly') <span class="badge bg-light text-dark border">Çeyreklik</span> @break
                                    @case('annual')    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Yıllık</span> @break
                                @endswitch
                            </td>
                            <td class="text-center fw-semibold">
                                <span class="text-danger">{{ number_format($r->total_co2_total / 1000, 2) }}</span>
                                <small class="text-muted d-block">tCO₂e</small>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold">{{ number_format($r->co2_per_room_night, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($r->hcmi_rating)
                                    <div class="hcmi-ring mx-auto rating-{{ str_replace('+','p',$r->hcmi_rating) }}">
                                        {{ $r->hcmi_rating }}
                                    </div>
                                    <div class="small text-muted mt-1">{{ number_format($r->hcmi_score, 0) }}p</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">{!! $r->status_badge !!}</td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('carbon.show', $r) }}" class="btn btn-outline-secondary btn-sm" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('carbon_footprint', 'edit'))
                                    <a href="{{ route('carbon.edit', $r) }}" class="btn btn-outline-primary btn-sm" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('carbon.pdf', $r) }}" class="btn btn-outline-danger btn-sm" title="PDF İndir" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('carbon_footprint', 'delete'))
                                    <form method="POST" action="{{ route('carbon.destroy', $r) }}" class="d-inline"
                                          onsubmit="return confirm('Bu raporu silmek istediğinizden emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" class="mb-3 d-block mx-auto"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M8 12h8M12 8v8"/></svg>
                                Henüz karbon ayak izi raporu oluşturulmamış.
                                @if(auth()->user()->hasPermission('carbon_footprint', 'create'))
                                    <br><a href="{{ route('carbon.create') }}" class="btn btn-success btn-sm mt-2">
                                        <i class="fas fa-plus me-1"></i> İlk Raporu Oluştur
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reports->hasPages())
        <div class="card-footer d-flex justify-content-end">
            {{ $reports->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
