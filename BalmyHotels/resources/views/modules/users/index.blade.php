@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Kullanıcı Yönetimi</h4>
                <span>Çalışanlar ve roller</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Kullanıcılar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtre --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Şube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">— Tüm Şubeler —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Rol</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">— Tüm Roller —</option>
                                @foreach($roles as $role_item)
                                    <option value="{{ $role_item->name }}" @selected(request('role') == $role_item->name)>{{ $role_item->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Arama</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                value="{{ request('search') }}" placeholder="İsim veya e-posta...">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i> Filtrele
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm ms-auto">
                                <i class="fas fa-plus me-1"></i> Yeni Kullanıcı
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Kullanıcılar
                        <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Unvan</th>
                                    <th>Rol</th>
                                    <th>Şube</th>
                                    <th>Departman</th>
                                    <th>Telefon</th>
                                    <th>Durum</th>
                                    <th class="text-center">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $u)
                                    <tr>
                                        <td class="fw-semibold">{{ $u->name }}</td>
                                        <td><small>{{ $u->email }}</small></td>
                                        <td>{{ $u->title ?? '—' }}</td>
                                        <td>
                                            @foreach($u->userRoles as $ur)
                                                @php
                                                    $roleObj = $roles->firstWhere('name', $ur->role_name);
                                                @endphp
                                                <span class="badge me-1"
                                                    style="background:{{ $roleObj->color ?? '#6c757d' }}">
                                                    {{ $roleObj->display_name ?? $ur->role_name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>{{ optional($u->branch)->name ?? '—' }}</td>
                                        <td>{{ optional($u->department)->name ?? '—' }}</td>
                                        <td>{{ $u->phone ?? '—' }}</td>
                                        <td>
                                            @if($u->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('users.edit', $u) }}"
                                               class="btn btn-xs btn-warning" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($u->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $u) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirmDelete(event)">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x d-block mb-2 opacity-25"></i>
                                            Kullanıcı bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
function confirmDelete(e) {
    e.preventDefault();
    const form = e.target;
    Swal.fire({
        title: 'Kullanıcıyı sil?',
        text: 'Bu işlem geri alınamaz!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal'
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
    return false;
}
</script>
@endpush
