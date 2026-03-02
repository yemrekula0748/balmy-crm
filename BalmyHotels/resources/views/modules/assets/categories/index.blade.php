@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Demirbaş Kategorileri</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Kategoriler</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Kategoriler</h4>
            <a href="{{ route('asset-categories.create') }}" class="btn btn-sm" style="background:#c19b77;color:#fff">+ Kategori Ekle</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori Adı</th>
                            <th>Açıklama</th>
                            <th>Dinamik Alanlar</th>
                            <th>Demirbaş Sayısı</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td>
                                    <span class="badge" style="background:{{ $cat->color }};font-size:13px;">{{ $cat->name }}</span>
                                </td>
                                <td class="text-muted small">{{ $cat->description ?? '-' }}</td>
                                <td>
                                    @if($cat->field_definitions && count($cat->field_definitions) > 0)
                                        @foreach($cat->field_definitions as $field)
                                            <span class="badge bg-light text-dark border me-1">{{ $field['label'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('assets.index', ['category_id' => $cat->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-secondary">{{ $cat->assets_count }}</span>
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('asset-categories.edit', $cat) }}" class="btn btn-xs btn-outline-primary me-1">Düzenle</a>
                                    <form action="{{ route('asset-categories.destroy', $cat) }}" method="POST" class="d-inline" data-delete>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Henüz kategori yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('[data-delete]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Emin misiniz?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Evet, sil', cancelButtonText: 'İptal'
        }).then(r => { if(r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
