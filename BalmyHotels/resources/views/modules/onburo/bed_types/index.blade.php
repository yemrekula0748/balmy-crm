@extends('layouts.default')

@section('title', 'Yatak Tipleri')

@push('styles')
<style>
    .bt-card {
        border: 1px solid #e9ecef;
        border-radius: .75rem;
        transition: box-shadow .2s, transform .15s;
        height: 100%;
    }
    .bt-card:hover {
        box-shadow: 0 4px 18px rgba(0,0,0,.1);
        transform: translateY(-2px);
    }
    .bt-icon-wrap {
        width: 52px; height: 52px;
        border-radius: .6rem;
        background: linear-gradient(135deg,#c19b77,#a07855);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .bt-abbr { font-size: 1.05rem; font-weight: 800; letter-spacing: .04em; color: #212529; }
    .bt-actions { opacity: 0; transition: opacity .15s; }
    .bt-card:hover .bt-actions { opacity: 1; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yatak Tipleri</h4>
                <span>Önbüro — Yatak Tipi Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Önbüro</span></li>
                <li class="breadcrumb-item active">Yatak Tipleri</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fas fa-bed me-2 text-muted"></i>Yatak Tipleri
                <span class="badge bg-primary bg-opacity-10 text-primary ms-2 fs-6">{{ $bedTypes->count() }}</span>
            </h5>
            <small class="text-muted">Odalara atanan yatak konfigürasyonları</small>
        </div>
        @if(auth()->user()->hasPermission('bed_types', 'create'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBedTypeModal">
            <i class="fas fa-plus me-1"></i> Yeni Yatak Tipi Ekle
        </button>
        @endif
    </div>

    <div class="row g-3">

        {{-- Bed type cards --}}
        <div class="col-lg-8">
            <div class="row g-3">
                @forelse($bedTypes as $bt)
                <div class="col-sm-6 col-md-4">
                    <div class="bt-card p-3 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bt-icon-wrap">
                                <i class="fas fa-bed text-white fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $bt->name }}</div>
                                <span class="bt-abbr">{{ $bt->abbreviation }}</span>
                            </div>
                        </div>
                        <div class="bt-actions d-flex gap-2 justify-content-end">
                            @if(auth()->user()->hasPermission('bed_types', 'edit'))
                            <button class="btn btn-sm btn-outline-primary edit-bt-btn"
                                data-id="{{ $bt->id }}"
                                data-name="{{ $bt->name }}"
                                data-abbreviation="{{ $bt->abbreviation }}">
                                <i class="fas fa-edit me-1"></i>Düzenle
                            </button>
                            @endif
                            @if(auth()->user()->hasPermission('bed_types', 'delete'))
                            <form action="{{ route('frontdesk.bed-types.destroy', $bt) }}" method="POST" class="d-inline delete-bt-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <i class="fas fa-bed fa-3x text-muted opacity-25 mb-3"></i>
                        <p class="text-muted mb-3">Henüz yatak tipi eklenmemiş.</p>
                        @if(auth()->user()->hasPermission('bed_types', 'create'))
                        <button class="btn btn-primary mx-auto" style="width:fit-content" data-bs-toggle="modal" data-bs-target="#addBedTypeModal">
                            <i class="fas fa-plus me-1"></i> İlk Yatak Tipini Ekle
                        </button>
                        @endif
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #c19b77 !important">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle me-2" style="color:#c19b77"></i>Yatak Tipleri Hakkında
                    </h6>
                    <p class="text-muted small mb-3">
                        Yatak tipleri, odalara atanarak misafirlerin oda tercihlerinin belirlenmesinde kullanılır.
                    </p>
                    <div class="mb-1" style="font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#8d9297">Yaygın Örnekler</div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach([['SNG','Single'],['DBL','Double'],['TWN','Twin'],['KING','King'],['QN','Queen'],['3BED','Triple'],['BUNK','Ranza']] as [$abbr,$name])
                        <span class="badge rounded-pill text-bg-light border" style="font-size:.78rem">
                            <span class="fw-bold">{{ $abbr }}</span> · <span class="text-muted">{{ $name }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Add Modal --}}
@if(auth()->user()->hasPermission('bed_types', 'create'))
<div class="modal fade" id="addBedTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Yeni Yatak Tipi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('frontdesk.bed-types.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Yatak Tipi Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="ör: Double Bed" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Kısaltma <span class="text-danger">*</span></label>
                        <input type="text" name="abbreviation" class="form-control" placeholder="ör: DBL" style="text-transform:uppercase" required>
                        <small class="text-muted">Büyük harf, kısa (maks. 6 karakter)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn fw-semibold" style="background:#c19b77;color:#fff">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Edit Modal --}}
@if(auth()->user()->hasPermission('bed_types', 'edit'))
<div class="modal fade" id="editBedTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Yatak Tipi Düzenle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBedTypeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Yatak Tipi Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_bt_name" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Kısaltma <span class="text-danger">*</span></label>
                        <input type="text" name="abbreviation" id="edit_bt_abbr" class="form-control" style="text-transform:uppercase" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn fw-semibold" style="background:#c19b77;color:#fff">
                        <i class="fas fa-save me-1"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $(document).on('click', '.edit-bt-btn', function () {
        const id = $(this).data('id');
        $('#edit_bt_name').val($(this).data('name'));
        $('#edit_bt_abbr').val($(this).data('abbreviation'));
        $('#editBedTypeForm').attr('action', '/onburo/yatak-tipleri/' + id);
        new bootstrap.Modal(document.getElementById('editBedTypeModal')).show();
    });

    $(document).on('submit', '.delete-bt-form', function (e) {
        e.preventDefault(); const form = this;
        Swal.fire({
            title: 'Yatak tipi silinsin mi?',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sil', cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
