@extends('layouts.default')

@push('styles')
<style>
.my-reports-header {
    background: linear-gradient(135deg, #4361ee 0%, #3a4fe0 100%);
    border-radius: 14px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.my-reports-header .breadcrumb-item a { color: rgba(255,255,255,.6); }
.my-reports-header .breadcrumb-item.active { color: rgba(255,255,255,.9); }
.my-reports-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.4); }

.filter-pill-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    padding: 0.85rem 1.25rem;
    margin-bottom: 1.25rem;
}
.fault-report-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
    transition: transform .15s, box-shadow .15s;
    overflow: hidden;
}
.fault-report-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.11); }
.fault-report-card .status-strip {
    width: 5px; flex-shrink: 0; border-radius: 0;
}
.fault-report-card .meta-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 2px 9px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 600; background: #f3f4f6;
}
.update-form-box {
    background: #f8f9fc;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
}
.update-form-box .form-select,
.update-form-box .form-control {
    border-radius: 8px; font-size: 0.85rem;
}
.empty-state {
    text-align: center; padding: 4rem 1rem; color: #adb5bd;
}
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .4; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="my-reports-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fas fa-list-alt me-2" style="opacity:.85"></i>Bildirdiklerim</h4>
                <ol class="breadcrumb mb-0" style="background:transparent;padding:0;">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                    <li class="breadcrumb-item active">Bildirdiklerim</li>
                </ol>
            </div>
            <a href="{{ route('faults.create') }}" class="btn btn-danger fw-semibold px-4">
                <i class="fas fa-plus me-2"></i>Yeni Arıza Bildir
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-3"
             style="background:#e8f5e9;">
            <i class="fas fa-check-circle text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FİLTRE --}}
    <div class="filter-pill-bar">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <span class="small fw-semibold text-muted">Durum:</span>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('faults.my-reports') }}"
                   class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill py-1 px-3">
                    Tümü
                </a>
                @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                @php
                    $colors = ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'];
                    $c = $colors[$val] ?? 'secondary';
                @endphp
                <a href="{{ route('faults.my-reports', ['status'=>$val]) }}"
                   class="btn btn-sm {{ request('status') === $val ? 'btn-'.$c : 'btn-outline-'.$c }} rounded-pill py-1 px-3">
                    {{ $lbl }}
                </a>
                @endforeach
            </div>
        </form>
    </div>

    {{-- LİSTE --}}
    <div class="d-flex flex-column gap-3">
        @forelse($faults as $fault)
        @php
            $isMine = $fault->reported_by === auth()->id();
            $sc = \App\Models\Fault::STATUS_COLORS[$fault->status];
            $stripColors = ['danger'=>'#dc3545','warning'=>'#f97316','success'=>'#28a745','secondary'=>'#6c757d'];
            $stripColor = $stripColors[$sc] ?? '#dee2e6';
            $statusBgs  = ['danger'=>['#fdecea','#dc3545'],'warning'=>['#fff8e1','#f97316'],'success'=>['#e8f5e9','#28a745'],'secondary'=>['#f0f0f0','#6c757d']];
            $sBg = $statusBgs[$sc][0] ?? '#f0f0f0';
            $sFg = $statusBgs[$sc][1] ?? '#333';
        @endphp
        <div class="fault-report-card card">
            <div class="d-flex" style="min-height:0">
                <div class="status-strip" style="background:{{ $stripColor }}"></div>
                <div class="flex-fill">
                    <div class="card-body">
                        <div class="row align-items-start g-3">
                            {{-- Sol: Bilgi --}}
                            <div class="col-md-8">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill fw-semibold"
                                          style="background:{{ $sBg }};color:{{ $sFg }};font-size:.75rem">
                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $sFg }};display:inline-block"></span>
                                        {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                    </span>
                                    @if($isMine)
                                        <span class="badge" style="background:#eef0ff;color:#4361ee;font-weight:600">
                                            <i class="fas fa-user me-1"></i>Ben bildirdim
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold">Departmanım</span>
                                    @endif
                                    <span class="text-muted" style="font-size:.75rem">#{{ $fault->id }} · {{ $fault->created_at->format('d.m.Y H:i') }}</span>
                                </div>

                                <h5 class="mb-2 fw-bold">
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="text-decoration-none"
                                       style="color:#1a1a2e">
                                        {{ $fault->title }}
                                    </a>
                                </h5>

                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="meta-chip">
                                        <i class="fas fa-building text-muted"></i>{{ $fault->branch->name ?? '—' }}
                                    </span>
                                    <span class="meta-chip">
                                        <i class="fas fa-users text-muted"></i>{{ $fault->department->name ?? '—' }}
                                    </span>
                                    @if($fault->faultLocation)
                                    <span class="meta-chip">
                                        <i class="fas fa-map-marker-alt text-muted"></i>{{ $fault->faultLocation->name }}@if($fault->faultArea) / {{ $fault->faultArea->name }}@endif
                                    </span>
                                    @endif
                                    @if($fault->faultType)
                                    <span class="meta-chip">
                                        <i class="fas fa-tag text-muted"></i>{{ $fault->faultType->name }}
                                    </span>
                                    @endif
                                </div>

                                <p class="text-muted mb-0" style="font-size:.85rem">{{ Str::limit($fault->description, 150) }}</p>
                            </div>

                            {{-- Sağ: Aksiyon --}}
                            <div class="col-md-4">
                                @if($isMine && $fault->status !== 'closed')
                                <div class="update-form-box">
                                    <p class="fw-semibold mb-2 small" style="color:#344054">
                                        <i class="fas fa-pencil-alt me-1 text-primary"></i>Durumu Güncelle
                                    </p>
                                    <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <select name="status" class="form-select form-select-sm" required>
                                                @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                                    @if($val !== $fault->status)
                                                    <option value="{{ $val }}" @if($val === 'closed') style="font-weight:bold;color:#dc3545" @endif>
                                                        {{ $lbl }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="note" class="form-control form-control-sm" rows="2"
                                                      placeholder="Açıklama ekleyin..." required></textarea>
                                        </div>
                                        <button class="btn btn-primary btn-sm w-100 fw-semibold">
                                            <i class="fas fa-save me-1"></i>Güncelle
                                        </button>
                                    </form>
                                </div>
                                @elseif($fault->status === 'closed')
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-check-circle fa-2x mb-1 d-block text-success opacity-75"></i>
                                    <small class="fw-semibold">Kapalı</small>
                                    @if($fault->closed_at)
                                        <div style="font-size:.72rem">{{ $fault->closed_at->format('d.m.Y') }}</div>
                                    @endif
                                </div>
                                @endif

                                <div class="text-end mt-2">
                                    <a href="{{ route('faults.show', $fault) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Detay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm" style="border-radius:14px">
            <div class="card-body empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5 class="fw-semibold mb-2">Henüz bildirim yapmadınız.</h5>
                <p class="text-muted small mb-3">Bir arıza tespit ettiğinizde buradan bildirin.</p>
                <a href="{{ route('faults.create') }}" class="btn btn-danger px-4 fw-semibold">
                    <i class="fas fa-plus me-2"></i>İlk Arızayı Bildir
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if($faults->hasPages())
    <div class="mt-3">{{ $faults->links() }}</div>
    @endif

</div>
@endsection

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto"><label class="col-form-label-sm fw-semibold">Durum:</label></div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:150px">
                        <option value="">Tümü</option>
                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('status'))
                    <div class="col-auto"><a href="{{ route('faults.my-reports') }}" class="btn btn-sm btn-outline-secondary">Temizle</a></div>
                @endif
                <div class="col-auto ms-auto">
                    <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-plus me-1"></i> Yeni Arıza Bildir
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="row g-3">
        @forelse($faults as $fault)
        @php $isMine = $fault->reported_by === auth()->id(); @endphp
        <div class="col-12">
            <div class="card border-start border-4 border-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                <div class="card-body">
                    <div class="row align-items-start g-3">
                        {{-- Bilgiler --}}
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                                    {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                </span>
                                @if($isMine)
                                    <span class="badge bg-primary">Ben bildirdim</span>
                                @else
                                    <span class="badge bg-secondary">Departmanım bildirdi</span>
                                @endif
                                <span class="text-muted small">#{{ $fault->id }} · {{ $fault->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <h5 class="mb-1">
                                <a href="{{ route('faults.show', $fault) }}" class="text-dark text-decoration-none">
                                    {{ $fault->title }}
                                </a>
                            </h5>

                            <div class="d-flex flex-wrap gap-2 text-muted small mb-2">
                                <span><i class="fas fa-building me-1"></i>{{ $fault->branch->name ?? '—' }}</span>
                                <span><i class="fas fa-users me-1"></i>{{ $fault->department->name ?? '—' }}</span>
                                @if($fault->faultLocation)
                                    <span><i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $fault->faultLocation->name }}@if($fault->faultArea) / {{ $fault->faultArea->name }}@endif
                                    </span>
                                @endif
                                @if($fault->faultType)
                                    <span><i class="fas fa-tag me-1"></i>{{ $fault->faultType->name }}</span>
                                @endif
                            </div>

                            <p class="text-muted small mb-0">{{ Str::limit($fault->description, 150) }}</p>
                        </div>

                        {{-- Kapatma (sadece kendi bildirdiğim ve açık olanlar) --}}
                        <div class="col-md-4">
                            @if($isMine && $fault->status !== 'closed')
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-semibold mb-2 small">Durumu Güncelle / Kapat</h6>
                                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <select name="status" class="form-select form-select-sm" required>
                                            @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                                @if($val !== $fault->status)
                                                <option value="{{ $val }}" @if($val === 'closed') style="font-weight:bold;color:#dc3545" @endif>
                                                    {{ $lbl }}
                                                </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="note" class="form-control form-control-sm" rows="2"
                                                  placeholder="Örn: Sorun kendiliğinden çözüldü..." required></textarea>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100">Güncelle</button>
                                </form>
                            </div>
                            @elseif($fault->status === 'closed')
                            <div class="text-center text-muted py-2">
                                <i class="fas fa-check-circle fa-2x text-secondary mb-1 d-block"></i>
                                <small>Kapalı · {{ $fault->closed_at?->format('d.m.Y') }}</small>
                            </div>
                            @endif

                            <div class="mt-2 text-end">
                                <a href="{{ route('faults.show', $fault) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Detay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-clipboard fa-3x mb-3 d-block"></i>
                    <h5>Henüz bildirim yapmadınız.</h5>
                    <a href="{{ route('faults.create') }}" class="btn btn-danger mt-2">
                        <i class="fas fa-plus me-1"></i> İlk Arızayı Bildir
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($faults->hasPages())
    <div class="mt-3">{{ $faults->links() }}</div>
    @endif
</div>
@endsection
