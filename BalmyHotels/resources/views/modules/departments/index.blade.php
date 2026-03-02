@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Departman Yönetimi</h4>
                <span>Şube departmanları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Departmanlar</li>
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Departmanlar</h4>
                    <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                        + Yeni Departman
                    </a>
                </div>
                <div class="card-body">

                    {{-- FİLTRELER --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Departman adı ara..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">Filtrele</button>
                            <a href="{{ route('departments.index') }}" class="btn btn-secondary btn-sm flex-fill">Temizle</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Departman Adı</th>
                                    <th>Şube</th>
                                    <th>Renk</th>
                                    <th>Çalışan Sayısı</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $dept->color }}; font-size:.85rem;">
                                                {{ $dept->name }}
                                            </span>
                                        </td>
                                        <td>{{ $dept->branch->name ?? '-' }}</td>
                                        <td>
                                            <span class="d-inline-block rounded" style="width:24px;height:24px;background:{{ $dept->color }};border:1px solid #ddd;"></span>
                                            <small class="text-muted ms-1">{{ $dept->color }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $dept->users()->count() }} kişi</span>
                                        </td>
                                        <td>
                                            @if($dept->is_active)
                                                <span class="badge badge-success light">Aktif</span>
                                            @else
                                                <span class="badge badge-danger light">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('departments.edit', $dept) }}"
                                               class="btn btn-warning btn-xs me-1">Düzenle</a>
                                            <button type="button"
                                                    class="btn btn-danger btn-xs btn-sil"
                                                    data-id="{{ $dept->id }}"
                                                    data-name="{{ $dept->name }}">
                                                Sil
                                            </button>
                                            <form id="form-sil-{{ $dept->id }}"
                                                  action="{{ route('departments.destroy', $dept) }}"
                                                  method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Henüz departman eklenmemiş.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        Swal.fire({
            title: 'Emin misiniz?',
            text: `"${name}" departmanı silinecek!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-sil-' + id).submit();
            }
        });
    });
});
</script>
@endpush
