@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yetki Yönetimi</h4>
                <span>Roller ve modül izinleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Yetki Yönetimi</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row align-items-start">

        {{-- Roller Listesi --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Tanımlı Roller</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rol</th>
                                    <th>Sistem Adı</th>
                                    <th>Renk</th>
                                    <th class="text-center">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($roles as $role)
                                @php
                                    $hex = str_starts_with($role->color, '#') ? $role->color : null;
                                    $cls = $hex ? '' : 'bg-' . $role->color;
                                    $sty = $hex ? 'background:' . $hex . ';color:#fff' : '';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge {{ $cls }}" @if($sty) style="{{ $sty }}" @endif>
                                            {{ $role->display_name }}
                                        </span>
                                    </td>
                                    <td><code>{{ $role->name }}</code></td>
                                    <td>
                                        @if($hex)
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:{{ $hex }};border:1px solid #ccc;"></span>
                                                <small class="text-muted">{{ $hex }}</small>
                                            </span>
                                        @else
                                            <code>{{ $role->color }}</code>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                                onclick="openEditModal({{ $role->id }}, '{{ addslashes($role->display_name) }}', '{{ $hex ?? '#6c757d' }}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="{{ route('roles.permissions', $role) }}"
                                           class="btn btn-sm btn-primary me-1">
                                            <i class="fa fa-shield"></i> İzinler
                                        </a>
                                        @if(!$role->is_system)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ addslashes($role->display_name) }} rolü silinsin mi?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Henüz rol yok.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yeni Rol Formu --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Yeni Rol Ekle</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sistem Adı <small class="text-muted">(küçük_harf_alt_çizgi)</small></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="ornek_role"
                                   pattern="[a-z_]+" title="Sadece küçük harf ve alt çizgi">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Görünen Ad</label>
                            <input type="text" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                                   value="{{ old('display_name') }}" placeholder="Şube Müdürü">
                            @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Renk</label>
                            <div class="input-group">
                                <input type="color" name="color" id="newRoleColor"
                                       class="form-control form-control-color @error('color') is-invalid @enderror"
                                       value="{{ old('color', '#6c757d') }}" title="Renk seç" style="max-width:50px">
                                <input type="text" id="newRoleColorText" class="form-control"
                                       value="{{ old('color', '#6c757d') }}" placeholder="#6c757d" readonly>
                            </div>
                            @error('color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-plus me-1"></i> Rol Oluştur
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bilgi Kutusu --}}
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-warning mb-2"><i class="fa fa-info-circle"></i> Bilgi</h6>
                    <ul class="mb-0 small text-muted">
                        <li><strong>super_admin</strong> her zaman tüm izinlere sahiptir.</li>
                        <li>Kullanıcı yönetiminden kullanıcıya rol atayın.</li>
                        <li><i class="fa fa-edit text-secondary"></i> kalem ikonu ile ad/renk düzenleyin.</li>
                        <li><em>İzinler</em> butonu ile modül bazlı CRUD izinleri verin.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Rol Düzenleme Modalı --}}
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editRoleForm" action="">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Rol Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Görünen Ad</label>
                        <input type="text" name="display_name" id="editDisplayName" class="form-control"
                               required maxlength="100" placeholder="Örn: Şube Müdürü">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Renk</label>
                        <div class="input-group">
                            <input type="color" name="color" id="editRoleColor"
                                   class="form-control form-control-color" style="max-width:50px" title="Renk seç">
                            <input type="text" id="editRoleColorText" class="form-control" placeholder="#6c757d" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Yeni rol renk senkron
document.getElementById('newRoleColor').addEventListener('input', function() {
    document.getElementById('newRoleColorText').value = this.value;
});

// Edit modal aç
function openEditModal(id, displayName, color) {
    const form = document.getElementById('editRoleForm');
    form.action = '/roller/' + id;
    document.getElementById('editDisplayName').value = displayName;
    document.getElementById('editRoleColor').value = color;
    document.getElementById('editRoleColorText').value = color;
    const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
    modal.show();
}

// Edit modal renk senkron
document.getElementById('editRoleColor').addEventListener('input', function() {
    document.getElementById('editRoleColorText').value = this.value;
});
</script>
@endpush
@endsection
