@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Kullanıcı</h4>
                <span>Çalışan kaydı oluştur</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Kullanıcılar</a></li>
                <li class="breadcrumb-item active">Yeni Kullanıcı</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7 col-lg-9 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-user-plus me-2 text-success"></i> Kullanıcı Bilgileri
                    </h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="row">

                            {{-- Ad Soyad --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Ahmet Yılmaz" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Unvan --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unvan / Pozisyon</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" placeholder="Resepsiyon Görevlisi">
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- E-posta --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="ahmet@balmy.com" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Telefon --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="text" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="0532 000 00 00">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Şifre --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şifre <span class="text-danger">*</span></label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Min. 6 karakter" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Şifre Tekrar --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation"
                                    class="form-control" placeholder="Aynı şifreyi girin" required>
                            </div>

                            {{-- Rol (çoklu seçim) --}}
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">
                                    Rol(ler) <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal ms-1">— birden fazla seçilebilir</small>
                                </label>
                                @error('roles') <div class="text-danger small mb-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                                @error('roles.*') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                <div class="role-picker d-flex flex-wrap gap-2">
                                    @foreach($roles as $role_item)
                                    @php $checked = in_array($role_item->name, old('roles', [])); @endphp
                                    <div class="role-card @if($checked) selected @endif"
                                         style="--role-color: {{ $role_item->color ?? '#6c757d' }}"
                                         onclick="toggleRole(this)">
                                        <input type="checkbox" name="roles[]"
                                               value="{{ $role_item->name }}"
                                               @checked($checked)
                                               style="display:none">
                                        <span class="role-dot"></span>
                                        <span class="role-name">{{ $role_item->display_name }}</span>
                                        <span class="role-check"><i class="fas fa-check"></i></span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Şube --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Şube</label>
                                <select name="branch_id" id="branch_id"
                                    class="form-select @error('branch_id') is-invalid @enderror">
                                    <option value="">— Seçiniz —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Departman --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Departman</label>
                                <select name="department_id" id="department_id"
                                    class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">— Seçiniz —</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" data-branch="{{ $d->branch_id }}"
                                            @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Kaydet
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Geri
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.role-card {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: 2px solid #dee2e6;
    border-radius: 50px;
    cursor: pointer;
    user-select: none;
    background: #fff;
    transition: all .18s ease;
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    position: relative;
}
.role-card:hover {
    border-color: var(--role-color);
    color: var(--role-color);
    background: color-mix(in srgb, var(--role-color) 6%, white);
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,.08);
}
.role-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--role-color);
    flex-shrink: 0;
    transition: transform .18s ease;
}
.role-check {
    display: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #198754;
    color: #fff;
    font-size: 9px;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.role-card.selected {
    border-color: #198754;
    background: #d1e7dd;
    color: #0f5132;
    font-weight: 600;
    box-shadow: 0 0 0 3px rgba(25,135,84,.2);
}
.role-card.selected .role-check {
    display: inline-flex;
}
.role-card.selected .role-dot {
    transform: scale(1.3);
}
</style>
@endpush

@push('scripts')
<script>
// Departman Şube Filtresi
(function () {
    const allDepts = @json($departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'branch_id' => $d->branch_id]));
    const branchSel = document.getElementById('branch_id');
    const deptSel   = document.getElementById('department_id');
    const oldDept   = '{{ old('department_id') }}';
    if (!branchSel || !deptSel) return;

    function rebuildDepts(branchId, selectedId) {
        const filtered = branchId ? allDepts.filter(d => String(d.branch_id) === String(branchId)) : allDepts;
        deptSel.innerHTML = '<option value="">— Seçiniz —</option>';
        filtered.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            if (String(d.id) === String(selectedId)) opt.selected = true;
            deptSel.appendChild(opt);
        });
        if (!filtered.length) {
            deptSel.innerHTML = '<option value="">Bu Şubeye ait departman yok</option>';
        }
    }

    branchSel.addEventListener('change', function () {
        rebuildDepts(this.value, '');
    });

    // Sayfa yüklenince mevcut Şubeye göre filtrele
    rebuildDepts(branchSel.value, oldDept);
})();

function toggleRole(card) {
    card.classList.toggle('selected');
    const cb = card.querySelector('input[type=checkbox]');
    cb.checked = card.classList.contains('selected');
}
</script>
@endpush