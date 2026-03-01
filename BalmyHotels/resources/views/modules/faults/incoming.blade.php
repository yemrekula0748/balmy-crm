@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-inbox me-2 text-danger"></i>Gelen Arızalar</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Gelen Arızalar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label-sm fw-semibold">Durum:</label>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:150px">
                        <option value="">Tümü</option>
                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('status'))
                    <div class="col-auto">
                        <a href="{{ route('faults.incoming') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="row g-3">
        @forelse($faults as $fault)
        <div class="col-12">
            <div class="card border-start border-4 border-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                <div class="card-body">
                    <div class="row align-items-start g-3">
                        {{-- Arıza Bilgileri --}}
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                                    {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                </span>
                                <span class="text-muted small">#{{ $fault->id }}</span>
                                <span class="text-muted small">{{ $fault->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <h5 class="mb-1">
                                <a href="{{ route('faults.show', $fault) }}" class="text-dark text-decoration-none">
                                    {{ $fault->title }}
                                </a>
                            </h5>

                            <div class="d-flex flex-wrap gap-2 text-muted small mb-2">
                                @if($fault->faultLocation)
                                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $fault->faultLocation->name }}
                                        @if($fault->faultArea) / {{ $fault->faultArea->name }}@endif
                                    </span>
                                @endif
                                <span><i class="fas fa-building me-1"></i>{{ $fault->branch->name ?? '—' }}</span>
                                <span><i class="fas fa-user me-1"></i>{{ $fault->reporter->name ?? '—' }}</span>
                            </div>

                            <p class="text-muted small mb-2">{{ Str::limit($fault->description, 120) }}</p>

                            @if($fault->image_path)
                                <a href="{{ Storage::url($fault->image_path) }}" target="_blank" class="small">
                                    <i class="fas fa-image me-1"></i> Fotoğraf
                                </a>
                            @endif
                        </div>

                        {{-- Durum Güncelleme --}}
                        <div class="col-md-5">
                            @if($canUpdate && !in_array($fault->status, ['closed']))
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-semibold mb-2">Durum Güncelle</h6>
                                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <select name="status" class="form-select form-select-sm" required>
                                            @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                                @if($val !== $fault->status)
                                                <option value="{{ $val }}">{{ $lbl }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="note" class="form-control form-control-sm" rows="2"
                                                  placeholder="Açıklama ekleyin..." required></textarea>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100">Güncelle</button>
                                </form>
                            </div>
                            @else
                            <div class="text-center text-muted py-3">
                                @if($fault->status === 'closed')
                                    <i class="fas fa-check-circle fa-2x text-secondary mb-1 d-block"></i>
                                    <small>Kapalı</small>
                                @else
                                    <small>Durum güncelleme yetkiniz yok.</small>
                                @endif
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
                    <i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i>
                    <h5>Harika! Departmanınıza atanmış açık arıza bulunmuyor.</h5>
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
