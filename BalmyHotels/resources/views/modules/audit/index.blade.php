@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>İç Denetimler</h4>
                <span>Şube içi denetim kayıtları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">İç Denetim</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- İSTATİSTİK KARTLARI --}}
    <div class="row mb-3">
        <div class="col-xl-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Toplam Denetim</p>
                        <h2 class="fw-bold mb-0" style="color:#c19b77;">{{ $totalAudits }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdf5ee;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#c19b77" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Toplam Uygunsuzluk</p>
                        <h2 class="fw-bold mb-0 text-warning">{{ $totalNonconformities }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fff8e1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#ffc107" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Açık Uygunsuzluk</p>
                        <h2 class="fw-bold mb-0 text-danger">{{ $openNonconformities }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;background:#fdecea;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                             stroke="#dc3545" stroke-width="2" viewBox="0 0 24 24">
                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DENETİM LİSTESİ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Denetim Kayıtları</h4>
            @if(auth()->user()->hasPermission('audits', 'create'))
            <a href="{{ route('audit.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Yeni Denetim Oluştur
            </a>
            @endif
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
                <div class="col-md-3">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">Tüm Departmanlar</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="audit_type_id" class="form-select form-select-sm">
                        <option value="">Tüm Denetim Tipleri</option>
                        @foreach($auditTypes as $t)
                            <option value="{{ $t->id }}" @selected(request('audit_type_id') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach(\App\Models\Audit::STATUSES as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filtrele</button>
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">Sıfırla</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Denetim Tipi</th>
                            <th>Şube</th>
                            <th>Departman</th>
                            <th>Denetçi</th>
                            <th>Uygunsuzluk</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                        <tr>
                            <td class="text-muted small">{{ $audit->id }}</td>
                            <td class="fw-semibold">{{ $audit->auditType?->name ?? '-' }}</td>
                            <td><small>{{ $audit->branch?->name ?? '-' }}</small></td>
                            <td>
                                @if($audit->department)
                                    <span class="badge" style="background:{{ $audit->department->color }}">
                                        {{ $audit->department->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><small>{{ $audit->auditor?->name ?? '-' }}</small></td>
                            <td>
                                @php $ncCount = $audit->nonconformities->count(); $openCount = $audit->nonconformities->where('status','open')->count(); @endphp
                                @if($ncCount > 0)
                                    <span class="badge badge-warning light me-1">{{ $ncCount }} adet</span>
                                    @if($openCount > 0)
                                        <span class="badge badge-danger light">{{ $openCount }} açık</span>
                                    @else
                                        <span class="badge badge-success light">tümü kapalı</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Yok</span>
                                @endif
                            </td>
                            <td>
                                @if($audit->status === 'open')
                                    <span class="badge badge-warning light">Açık</span>
                                @else
                                    <span class="badge badge-secondary light">Kapalı</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $audit->created_at->format('d.m.Y') }}</small><br>
                                <small class="text-muted">{{ $audit->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <a href="{{ route('audit.show', $audit) }}" class="btn btn-primary btn-xs me-1">Detay</a>
                                @if(auth()->user()->hasPermission('audits', 'delete'))
                                <button class="btn btn-danger btn-xs btn-sil"
                                        data-id="{{ $audit->id }}" data-name="#{{ $audit->id }}">Sil</button>
                                <form id="form-sil-{{ $audit->id }}"
                                      action="{{ route('audit.destroy', $audit) }}"
                                      method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">Denetim kaydı bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Emin misiniz?',
            text: `Denetim kaydı ${this.dataset.name} ve tüm uygunsuzlukları silinecek!`,
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
