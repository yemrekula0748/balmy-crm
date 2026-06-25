@extends('layouts.default')

@push('styles')
<style>
    .nonconformity-table {
        min-width: 1080px;
    }

    .nonconformity-description-cell {
        min-width: 320px;
        max-width: 420px;
    }

    .nonconformity-description-preview,
    .nonconformity-description-full {
        white-space: pre-line;
        word-break: break-word;
        color: #495057;
        line-height: 1.55;
    }

    .nonconformity-description-preview {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
        overflow: hidden;
    }

    .nonconformity-description-details summary {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .5rem;
        color: #0d6efd;
        cursor: pointer;
        font-size: .78rem;
        font-weight: 600;
        list-style: none;
    }

    .nonconformity-description-details summary::-webkit-details-marker {
        display: none;
    }

    .nonconformity-description-details summary::before {
        content: '+';
        font-size: .9rem;
        line-height: 1;
    }

    .nonconformity-description-details[open] summary::before {
        content: '-';
    }

    .nonconformity-description-full {
        margin-top: .65rem;
        padding: .75rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
    }

    @media (max-width: 767.98px) {
        .nonconformity-description-cell {
            min-width: 280px;
            max-width: 280px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Uygunsuzluklarım</h4>
                <span>Departmanıma ait tüm uygunsuzluk kayıtları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">İç Denetim</a></li>
                <li class="breadcrumb-item active">Uygunsuzluklarım</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Uygunsuzluk Listesi</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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
                        @foreach(\App\Models\AuditNonconformity::STATUSES as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filtrele</button>
                    <a href="{{ route('audit.nonconformities.index') }}" class="btn btn-secondary btn-sm">Sıfırla</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle nonconformity-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Denetim</th>
                            <th>Departman</th>
                            <th>Şube</th>
                            <th>Açıklama</th>
                            <th>Fotoğraf</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nonconformities as $nc)
                            @php
                                $description = trim((string) $nc->description);
                                $descriptionPreview = \Illuminate\Support\Str::limit(
                                    preg_replace('/\s+/', ' ', $description),
                                    180
                                );
                                $hasLongDescription = \Illuminate\Support\Str::length($description) > 180;
                            @endphp
                            <tr class="{{ $nc->status === 'resolved' ? 'table-light' : '' }}">
                                <td class="text-muted small">{{ $nc->id }}</td>
                                <td>
                                    <a href="{{ route('audit.show', $nc->audit_id) }}" class="text-primary small">
                                        #{{ $nc->audit_id }} - {{ $nc->audit?->auditType?->name ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    @if($nc->department)
                                        <span class="badge" style="background:{{ $nc->department->color }}">{{ $nc->department->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $nc->branch?->name ?? '-' }}</small></td>
                                <td class="nonconformity-description-cell">
                                    @if($description !== '')
                                        <div class="nonconformity-description-preview small">{{ $descriptionPreview }}</div>

                                        @if($hasLongDescription)
                                            <details class="nonconformity-description-details">
                                                <summary>Tam açıklamayı göster</summary>
                                                <div class="nonconformity-description-full small">{{ $description }}</div>
                                            </details>
                                        @endif
                                    @else
                                        <span class="text-muted small">Açıklama yok</span>
                                    @endif
                                </td>
                                <td>
                                    @if($nc->photo_path)
                                        <a href="{{ Storage::disk('public')->url($nc->photo_path) }}" target="_blank">
                                            <img
                                                src="{{ Storage::disk('public')->url($nc->photo_path) }}"
                                                style="height:48px;width:64px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;"
                                                alt="Fotoğraf"
                                            >
                                        </a>
                                    @else
                                        <span class="text-muted small">Yok</span>
                                    @endif
                                </td>
                                <td>
                                    @if($nc->status === 'open')
                                        <span class="badge badge-danger light">Açık</span>
                                    @else
                                        <span class="badge badge-success light">Çözüldü</span>
                                        @if($nc->resolved_at)
                                            <br><small class="text-muted">{{ $nc->resolved_at->format('d.m.Y') }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $nc->created_at->format('d.m.Y') }}</small><br>
                                    <small class="text-muted">{{ $nc->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-end">
                                    @if($nc->status === 'open' && auth()->user()->hasPermission('audit_nonconformities', 'edit'))
                                        <form action="{{ route('audit.nonconformities.resolve', $nc) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-xs">
                                                <i class="fas fa-check me-1"></i> Çözüldü
                                            </button>
                                        </form>
                                    @else
                                        @if($nc->resolver)
                                            <small class="text-muted">{{ $nc->resolver->name }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">Uygunsuzluk kaydı bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $nonconformities->links() }}
        </div>
    </div>
</div>
@endsection
