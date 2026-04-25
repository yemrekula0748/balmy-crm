@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Denetim Tipleri</h4>
                <span>İç denetim tipi tanımları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">İç Denetim</a></li>
                <li class="breadcrumb-item active">Denetim Tipleri</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        {{-- YENİ TİP EKLE --}}
        @if(auth()->user()->hasPermission('audit_types', 'create'))
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Yeni Denetim Tipi</h5></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-sm"><ul class="mb-0 small">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul></div>
                    @endif
                    <form action="{{ route('audit.types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tip Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Örn: Temizlik Kontrolü" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Açıklama</label>
                            <textarea name="description" rows="2"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Opsiyonel açıklama...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Sıra</label>
                                <input type="number" name="sort_order" class="form-control"
                                       value="{{ old('sort_order', 0) }}" min="0">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check form-switch ms-2 mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="is_active_new"
                                           @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active_new">Aktif</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i> Tip Ekle
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- TİP LİSTESİ --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Mevcut Denetim Tipleri</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tip Adı</th>
                                    <th>Açıklama</th>
                                    <th>Sıra</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                <tr>
                                    <td class="text-muted small">{{ $type->id }}</td>
                                    <td class="fw-semibold">{{ $type->name }}</td>
                                    <td><small class="text-muted">{{ $type->description ?? '-' }}</small></td>
                                    <td><span class="badge bg-light text-dark border">{{ $type->sort_order }}</span></td>
                                    <td>
                                        @if($type->is_active)
                                            <span class="badge badge-success light">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary light">Pasif</span>
                                        @endif
                                    </td>
                                    <td class="text-end" style="white-space:nowrap;">
                                        @if(auth()->user()->hasPermission('audit_types', 'edit'))
                                        <button class="btn btn-warning btn-xs me-1"
                                                onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ addslashes($type->description ?? '') }}', {{ $type->sort_order }}, {{ $type->is_active ? 'true' : 'false' }})">
                                            Düzenle
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('audit_types', 'delete'))
                                        <button class="btn btn-danger btn-xs btn-sil"
                                                data-id="{{ $type->id }}" data-name="{{ $type->name }}">
                                            Sil
                                        </button>
                                        <form id="form-sil-{{ $type->id }}"
                                              action="{{ route('audit.types.destroy', $type) }}"
                                              method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">Henüz denetim tipi eklenmemiş.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DÜZENLE MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Denetim Tipini Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tip Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Açıklama</label>
                        <textarea name="description" id="edit_description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sıra</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch ms-2 mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                <label class="form-check-label" for="edit_is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
function openEditModal(id, name, description, sortOrder, isActive) {
    document.getElementById('editForm').action = '/ic-denetim/tipler/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_sort_order').value = sortOrder;
    document.getElementById('edit_is_active').checked = isActive;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Emin misiniz?',
            text: `"${this.dataset.name}" denetim tipi silinecek!`,
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
