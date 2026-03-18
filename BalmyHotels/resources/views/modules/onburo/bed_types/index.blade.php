@extends('layouts.default')

@section('title', 'Yatak Tipleri')

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

    <div class="row g-4">

        {{-- Tablo --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-bed me-2 text-muted"></i>Yatak Tipi Listesi
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $bedTypes->count() }}</span>
                    </h6>
                    @if(auth()->user()->hasPermission('bed_types', 'create'))
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBedTypeModal">
                        <i class="fas fa-plus me-1"></i> Yeni Ekle
                    </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Yatak Tipi Adı</th>
                                <th>Kısaltma</th>
                                <th style="width:120px">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bedTypes as $i => $bt)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $bt->name }}</td>
                                <td><span class="badge bg-secondary fs-6">{{ $bt->abbreviation }}</span></td>
                                <td>
                                    @if(auth()->user()->hasPermission('bed_types', 'edit'))
                                    <button class="btn btn-sm btn-outline-primary edit-bt-btn"
                                        data-id="{{ $bt->id }}"
                                        data-name="{{ $bt->name }}"
                                        data-abbreviation="{{ $bt->abbreviation }}">
                                        <i class="fas fa-edit"></i>
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
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Yatak tipi eklenmemiş</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Bilgi --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #c19b77 !important">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-muted"></i>Yatak Tipleri Hakkında</h6>
                    <p class="text-muted small mb-2">Yatak tipleri, odalara atanarak misafirlerin oda tercihlerinin belirlenmesinde kullanılır.</p>
                    <p class="text-muted small mb-0">Örnekler:</p>
                    <ul class="small text-muted mt-1">
                        <li>Single Bed (SNG)</li>
                        <li>Double Bed (DBL)</li>
                        <li>Twin Bed (TWN)</li>
                        <li>King Size (KING)</li>
                        <li>Queen Size (QN)</li>
                    </ul>
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
    // Edit
    $(document).on('click', '.edit-bt-btn', function () {
        const id = $(this).data('id');
        $('#edit_bt_name').val($(this).data('name'));
        $('#edit_bt_abbr').val($(this).data('abbreviation'));
        $('#editBedTypeForm').attr('action', '/onburo/yatak-tipleri/' + id);
        new bootstrap.Modal(document.getElementById('editBedTypeModal')).show();
    });

    // Delete
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
