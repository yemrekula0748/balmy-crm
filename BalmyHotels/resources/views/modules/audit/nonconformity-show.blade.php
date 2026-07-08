@extends('layouts.default')

@section('content')
<div class="container-fluid">
    @php
        $canResolveNonconformity = auth()->user()->hasPermission('audit_nonconformities', 'edit')
            || auth()->user()->hasPermission('audits', 'show');
    @endphp
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Uygunsuzluk Detayi</h4>
                <span>#{{ $nonconformity->id }} - Denetim #{{ $nonconformity->audit_id }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.nonconformities.index') }}">Uygunsuzluklarim</a></li>
                <li class="breadcrumb-item active">Detay</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Kayit Bilgileri</h5>
                    @if($nonconformity->status === 'open')
                        <span class="badge badge-danger light">Acik</span>
                    @else
                        <span class="badge badge-success light">Cozuldu</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted small">Denetim No</dt>
                        <dd class="col-sm-7 fw-semibold">#{{ $nonconformity->audit_id }}</dd>

                        <dt class="col-sm-5 text-muted small">Denetim Tipi</dt>
                        <dd class="col-sm-7">{{ $nonconformity->audit?->auditType?->name ?? '-' }}</dd>

                        <dt class="col-sm-5 text-muted small">Sube</dt>
                        <dd class="col-sm-7">{{ $nonconformity->branch?->name ?? '-' }}</dd>

                        <dt class="col-sm-5 text-muted small">Departman</dt>
                        <dd class="col-sm-7">
                            @if($nonconformity->department)
                                <span class="badge" style="background:{{ $nonconformity->department->color }}">{{ $nonconformity->department->name }}</span>
                            @else
                                -
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted small">Denetci</dt>
                        <dd class="col-sm-7">{{ $nonconformity->audit?->auditor?->name ?? '-' }}</dd>

                        <dt class="col-sm-5 text-muted small">Kayit Tarihi</dt>
                        <dd class="col-sm-7">
                            <small>{{ $nonconformity->created_at->format('d.m.Y H:i') }}</small><br>
                            <small class="text-muted">{{ $nonconformity->created_at->diffForHumans() }}</small>
                        </dd>

                        @if($nonconformity->status === 'resolved' && $nonconformity->resolved_at)
                            <dt class="col-sm-5 text-muted small">Cozum Tarihi</dt>
                            <dd class="col-sm-7">
                                <small>{{ $nonconformity->resolved_at->format('d.m.Y H:i') }}</small>
                                @if($nonconformity->resolver)
                                    <br><small class="text-muted">{{ $nonconformity->resolver->name }}</small>
                                @endif
                            </dd>
                        @endif
                    </dl>
                </div>
                <div class="card-footer bg-transparent d-flex gap-2 flex-wrap">
                    <a href="{{ route('audit.nonconformities.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Listeye Don
                    </a>

                    @if(auth()->user()->hasPermission('audits', 'show'))
                        <a href="{{ route('audit.show', $nonconformity->audit_id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-clipboard-list me-1"></i> Denetimi Ac
                        </a>
                    @endif

                    @if($nonconformity->status === 'open' && $canResolveNonconformity)
                        <form action="{{ route('audit.nonconformities.resolve', $nonconformity) }}" method="POST" class="ms-lg-auto">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i> Cozuldu Isaretle
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aciklama</h5>
                </div>
                <div class="card-body">
                    <div style="white-space:pre-line;word-break:break-word;line-height:1.7;color:#495057;">
                        {{ $nonconformity->description ?: '-' }}
                    </div>

                    @if($nonconformity->photo_path)
                        <hr>
                        <h6 class="mb-3">Fotograf</h6>
                        <a href="{{ Storage::disk('public')->url($nonconformity->photo_path) }}" target="_blank">
                            <img
                                src="{{ Storage::disk('public')->url($nonconformity->photo_path) }}"
                                alt="Uygunsuzluk Fotografi"
                                style="max-height:420px;max-width:100%;border-radius:10px;border:1px solid #dee2e6;cursor:zoom-in;"
                            >
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
