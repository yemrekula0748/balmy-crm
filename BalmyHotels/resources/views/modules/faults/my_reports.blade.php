@extends('layouts.default')

@section('title', 'Bildirdiklerim')

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Bildirdiklerim</h4>
                <span class="text-muted" style="font-size:.82rem">Departmanınızın arıza bildirimleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center gap-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Bildirdiklerim</li>
            </ol>
            <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm fw-semibold">
                <i class="fas fa-plus me-1"></i> Yeni Arıza Bildir
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm my-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Durum Filtresi --}}
    <div class="card border-0 shadow-sm mb-3 mt-2" style="border-radius:12px">
        <div class="card-body py-2 px-4">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted fw-semibold" style="font-size:.8rem">Durum:</span>
                <a href="{{ route('faults.my-reports') }}"
                   class="btn btn-sm rounded-pill {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}"
                   style="font-size:.75rem;padding:3px 14px">
                    Tümü ({{ $faults->total() }})
                </a>
                @php
                    $fMap = ['open'=>'btn-danger','in_progress'=>'btn-warning','resolved'=>'btn-success','closed'=>'btn-secondary'];
                    $foMap= ['open'=>'btn-outline-danger','in_progress'=>'btn-outline-warning','resolved'=>'btn-outline-success','closed'=>'btn-outline-secondary'];
                @endphp
                @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                <a href="{{ route('faults.my-reports', ['status'=>$val]) }}"
                   class="btn btn-sm rounded-pill {{ request('status') === $val ? ($fMap[$val] ?? 'btn-dark') : ($foMap[$val] ?? 'btn-outline-secondary') }}"
                   style="font-size:.75rem;padding:3px 14px">
                    {{ $lbl }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Şekerleme sayaçlar --}}
    <div class="row g-2 mb-3">
        @php
            $counts = $faults->getCollection()->groupBy('status');
            $countsAll = \App\Models\Fault::STATUSES;
        @endphp
        @foreach($countsAll as $val => $lbl)
        @php
            $cMap = ['open'=>['#ef4444','#fef2f2','fa-circle-exclamation'],'in_progress'=>['#f59e0b','#fffbeb','fa-rotate'],'resolved'=>['#10b981','#f0fdf4','fa-circle-check'],'closed'=>['#6b7280','#f1f5f9','fa-circle-xmark']];
            [$fg,$bg,$ico] = $cMap[$val] ?? ['#6b7280','#f1f5f9','fa-circle'];
        @endphp
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0" style="border-radius:10px;background:{{ $bg }};border-left:4px solid {{ $fg }}!important">
                <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                    <i class="fas {{ $ico }}" style="color:{{ $fg }};font-size:1.1rem"></i>
                    <div>
                        <div class="fw-bold lh-1" style="font-size:1.1rem;color:{{ $fg }}">{{ $counts->get($val, collect())->count() }}</div>
                        <div style="font-size:.68rem;color:{{ $fg }};opacity:.8;font-weight:600">{{ $lbl }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Arıza Kartları --}}
    <div class="d-flex flex-column gap-2">
        @forelse($faults as $fault)
        @php
            $isMine = $fault->reported_by === auth()->id();
            $scMap  = ['open'=>['#ef4444','#fef2f2'],'in_progress'=>['#f59e0b','#fffbeb'],'resolved'=>['#10b981','#f0fdf4'],'closed'=>['#6b7280','#f1f5f9']];
            [$sFg,$sBg] = $scMap[$fault->status] ?? ['#6b7280','#f1f5f9'];
            $deptColor  = $fault->department?->color ?? '#6366f1';
        @endphp
        <div class="card border-0 shadow-sm" style="border-radius:10px;border-left:4px solid {{ $sFg }}!important;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 4px 18px rgba(0,0,0,.1)'"
             onmouseleave="this.style.boxShadow='0 1px 6px rgba(0,0,0,.06)'">
            <div class="card-body py-2 px-3">
                <div class="row align-items-center g-0">

                    {{-- Başlık + Meta --}}
                    <div class="col-xl-4 col-lg-5 col-md-6 pe-3">
                        <div class="d-flex align-items-start gap-2">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                 style="width:34px;height:34px;background:{{ $sBg }}">
                                <i class="fas fa-wrench" style="font-size:.75rem;color:{{ $sFg }}"></i>
                            </div>
                            <div style="min-width:0">
                                <a href="{{ route('faults.show', $fault) }}"
                                   class="fw-semibold text-dark text-decoration-none d-block text-truncate"
                                   style="font-size:.83rem" title="{{ $fault->title }}">{{ $fault->title }}</a>
                                <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                    <span class="badge" style="font-size:.65rem;background:{{ $sBg }};color:{{ $sFg }};border-radius:5px">
                                        {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                    </span>
                                    @if($isMine)
                                    <span class="badge" style="font-size:.65rem;background:#eef2ff;color:#4f46e5;border-radius:5px">
                                        <i class="fas fa-user me-1" style="font-size:.55rem"></i>Ben bildirdim
                                    </span>
                                    @elseif($fault->reporter)
                                    <span class="badge" style="font-size:.65rem;background:#f0fdf4;color:#15803d;border-radius:5px">
                                        <i class="fas fa-user me-1" style="font-size:.55rem"></i>{{ $fault->reporter->name }}
                                    </span>
                                    @endif
                                    @if($fault->department)
                                    <span class="badge" style="font-size:.65rem;background:{{ $deptColor }};color:#fff;border-radius:5px">
                                        {{ $fault->department->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Konum + Tür --}}
                    <div class="col-xl-3 col-lg-3 col-md-6 d-none d-md-block border-start ps-3 pe-3" style="border-color:#f1f5f9!important">
                        @if($fault->faultLocation)
                        <div class="text-truncate mb-1" style="font-size:.72rem;color:#374151;font-weight:600">
                            <i class="fas fa-map-marker-alt me-1" style="color:#94a3b8"></i>
                            {{ $fault->faultLocation->name }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}
                        </div>
                        @endif
                        @if($fault->faultType)
                        <div style="font-size:.68rem;color:#64748b">
                            <i class="fas fa-tag me-1" style="color:#94a3b8"></i>{{ $fault->faultType->name }}
                        </div>
                        @endif
                        <div style="font-size:.65rem;color:#94a3b8" class="mt-1">
                            {{ $fault->created_at->format('d.m.Y H:i') }}
                            &middot; {{ $fault->created_at->diffForHumans() }}
                        </div>
                    </div>

                    {{-- Açıklama --}}
                    <div class="col-xl-3 col-lg-2 d-none d-lg-block border-start ps-3 pe-3" style="border-color:#f1f5f9!important">
                        <p class="mb-0 text-muted" style="font-size:.73rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                            {{ $fault->description }}
                        </p>
                    </div>

                    {{-- Güncelle / Kapalı / Eylem --}}
                    <div class="col-xl-2 col-lg-2 col-md-12 d-flex justify-content-end align-items-center gap-1 mt-2 mt-lg-0">
                        @if($isMine && $fault->status !== 'closed')
                        <button class="btn btn-sm btn-outline-warning fw-semibold" style="border-radius:7px;font-size:.72rem;padding:3px 10px"
                                data-bs-toggle="modal" data-bs-target="#updateModal{{ $fault->id }}">
                            <i class="fas fa-pen me-1"></i>Güncelle
                        </button>
                        @elseif($fault->status === 'closed')
                        <span class="text-muted" style="font-size:.7rem">
                            <i class="fas fa-lock me-1"></i>{{ $fault->closed_at?->format('d.m.Y') }}
                        </span>
                        @endif
                        <a href="{{ route('faults.show', $fault) }}"
                           class="btn btn-sm btn-outline-primary" style="border-radius:7px;padding:3px 8px">
                            <i class="fas fa-eye" style="font-size:.75rem"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Güncelle Modal --}}
        @if($isMine && $fault->status !== 'closed')
        <div class="modal fade" id="updateModal{{ $fault->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
                <div class="modal-content border-0 shadow" style="border-radius:14px">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">Durumu Güncelle</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                        @csrf
                        <div class="modal-body pt-2">
                            <p class="text-muted small mb-3">{{ $fault->title }}</p>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Yeni Durum</label>
                                <select name="status" class="form-select form-select-sm" style="border-radius:8px" required>
                                    @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                    @if($val !== $fault->status)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-1">
                                <label class="form-label small fw-semibold">Not</label>
                                <textarea name="note" class="form-control form-control-sm" rows="3"
                                          style="border-radius:8px;resize:none" required
                                          placeholder="Güncelleme notu yazın..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold px-4" style="border-radius:8px">Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @empty
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px">
            <div class="card-body">
                <i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-25"></i>
                <h5 class="text-muted mb-3">Henüz bildirim yapmadınız.</h5>
                <a href="{{ route('faults.create') }}" class="btn btn-danger fw-semibold px-4">
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
