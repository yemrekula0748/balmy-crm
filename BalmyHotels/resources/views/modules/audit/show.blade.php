@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Denetim Detayı</h4>
                <span>#{{ $audit->id }} — {{ $audit->auditType?->name }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">İç Denetim</a></li>
                <li class="breadcrumb-item active">Detay</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        {{-- Denetim Bilgileri --}}
        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Denetim Bilgileri</h5>
                    @if($audit->status === 'open')
                        <span class="badge badge-warning light">Açık</span>
                    @else
                        <span class="badge badge-secondary light">Kapalı</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted small">Denetim Tipi</dt>
                        <dd class="col-sm-7 fw-semibold">{{ $audit->auditType?->name ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Şube</dt>
                        <dd class="col-sm-7">{{ $audit->branch?->name ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Departman</dt>
                        <dd class="col-sm-7">
                            @if($audit->department)
                                <span class="badge" style="background:{{ $audit->department->color }}">{{ $audit->department->name }}</span>
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted small">Denetçi</dt>
                        <dd class="col-sm-7">{{ $audit->auditor?->name ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Oluşturuldu</dt>
                        <dd class="col-sm-7">
                            <small>{{ $audit->created_at->format('d.m.Y H:i') }}</small><br>
                            <small class="text-muted">{{ $audit->created_at->diffForHumans() }}</small>
                        </dd>

                        @if($audit->notes)
                        <dt class="col-sm-5 text-muted small mt-2">Not</dt>
                        <dd class="col-sm-7 mt-2"><small class="text-muted">{{ $audit->notes }}</small></dd>
                        @endif
                    </dl>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2">
                        <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Listeye Dön
                        </a>
                        @if(auth()->user()->hasPermission('audits', 'delete'))
                        <button class="btn btn-danger btn-sm ms-auto btn-sil-audit" data-id="{{ $audit->id }}">
                            <i class="fas fa-trash me-1"></i> Sil
                        </button>
                        <form id="form-sil-audit" action="{{ route('audit.destroy', $audit) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Uygunsuzluklar --}}
        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Uygunsuzluklar
                        <span class="badge bg-secondary ms-1">{{ $audit->nonconformities->count() }}</span>
                    </h5>
                    @php
                        $openCount = $audit->nonconformities->where('status','open')->count();
                        $resolvedCount = $audit->nonconformities->where('status','resolved')->count();
                    @endphp
                    @if($openCount > 0)
                        <span class="badge badge-danger light">{{ $openCount }} açık</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($audit->nonconformities->isEmpty())
                        <div class="text-center text-muted py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#dee2e6" stroke-width="1.5" viewBox="0 0 24 24" class="mb-2 d-block mx-auto">
                                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Bu denetimde uygunsuzluk kaydedilmemiş.
                        </div>
                    @else
                        @foreach($audit->nonconformities as $nc)
                        <div class="border rounded p-3 mb-3 @if($nc->status === 'resolved') bg-light @endif">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                                    @if($nc->status === 'open')
                                        <span class="badge badge-danger light">Açık</span>
                                    @else
                                        <span class="badge badge-success light">Çözüldü</span>
                                    @endif
                                </div>
                                @if($nc->status === 'open' && auth()->user()->hasPermission('audit_nonconformities', 'edit'))
                                <form action="{{ route('audit.nonconformities.resolve', $nc) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-xs">
                                        <i class="fas fa-check me-1"></i> Çözüldü İşaretle
                                    </button>
                                </form>
                                @endif
                            </div>

                            <p class="mb-2">{{ $nc->description }}</p>

                            @if($nc->photo_path)
                            <div class="mb-2">
                                <a href="{{ Storage::url($nc->photo_path) }}" target="_blank">
                                    <img src="{{ Storage::url($nc->photo_path) }}" alt="Uygunsuzluk Fotoğrafı"
                                         style="max-height:200px;max-width:100%;border-radius:8px;border:1px solid #dee2e6;cursor:zoom-in;">
                                </a>
                            </div>
                            @endif

                            @if($nc->status === 'resolved' && $nc->resolved_at)
                            <small class="text-muted">
                                Çözüldü: {{ $nc->resolved_at->format('d.m.Y H:i') }}
                                @if($nc->resolver) — {{ $nc->resolver->name }}@endif
                            </small>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelector('.btn-sil-audit')?.addEventListener('click', function () {
    Swal.fire({
        title: 'Emin misiniz?',
        text: 'Bu denetim ve tüm uygunsuzlukları silinecek!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal'
    }).then(r => {
        if (r.isConfirmed) document.getElementById('form-sil-audit').submit();
    });
});
</script>
@endpush
