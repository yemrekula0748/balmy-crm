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
            <h4 class="card-title mb-0">Kategoriler <small class="text-muted fs-6">({{ $categories->count() }} ana kategori)</small></h4>
            <a href="{{ route('asset-categories.create') }}" class="btn btn-sm" style="background:#c19b77;color:#fff">+ Kategori / Alt Kategori Ekle</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35%">Kategori Adı</th>
                            <th>Açıklama</th>
                            <th>Özel Alanlar</th>
                            <th class="text-center">Demirbaş</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            {{-- ANA KATEGORİ SATIRI --}}
                            <tr class="table-light fw-semibold" style="border-left: 4px solid {{ $cat->color }}">
                                <td>
                                    @if($cat->children->count() > 0)
                                        <button class="btn btn-link btn-sm p-0 me-1 text-muted toggle-children"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#sub_{{ $cat->id }}"
                                                title="Alt kategorileri göster/gizle">
                                            <span class="toggle-icon">▶</span>
                                        </button>
                                    @else
                                        <span style="display:inline-block;width:22px"></span>
                                    @endif
                                    <span class="badge" style="background:{{ $cat->color }};font-size:13px;">{{ $cat->name }}</span>
                                    @if($cat->children->count() > 0)
                                        <span class="badge bg-secondary ms-1" style="font-size:11px;">{{ $cat->children->count() }} alt kategori</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $cat->description ?? '—' }}</td>
                                <td>
                                    @if($cat->field_definitions && count($cat->field_definitions) > 0)
                                        @foreach($cat->field_definitions as $field)
                                            <span class="badge bg-light text-dark border me-1">{{ $field['label'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('assets.index', ['category_id' => $cat->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-secondary">{{ $cat->assets_count }}</span>
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('asset-categories.create', ['parent_id' => $cat->id]) }}"
                                       class="btn btn-xs btn-outline-secondary me-1" title="Alt Kategori Ekle">+ Alt</a>
                                    <a href="{{ route('asset-categories.edit', $cat) }}" class="btn btn-xs btn-outline-primary me-1">Düzenle</a>
                                    <form action="{{ route('asset-categories.destroy', $cat) }}" method="POST" class="d-inline" data-delete>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger">Sil</button>
                                    </form>
                                </td>
                            </tr>
                            {{-- ALT KATEGORİ SATIRLARI --}}
                            @if($cat->children->count() > 0)
                                <tr class="collapse" id="sub_{{ $cat->id }}">
                                    <td colspan="5" class="p-0">
                                        <table class="table table-sm mb-0 bg-white">
                                            <tbody>
                                                @foreach($cat->children as $sub)
                                                    <tr style="border-left: 4px solid transparent">
                                                        <td style="width:35%;padding-left:2.5rem">
                                                            <span style="color:#aaa;margin-right:6px">└</span>
                                                            <span class="badge" style="background:{{ $sub->color }};font-size:12px;">{{ $sub->name }}</span>
                                                        </td>
                                                        <td class="text-muted small">{{ $sub->description ?? '—' }}</td>
                                                        <td>
                                                            @if($sub->field_definitions && count($sub->field_definitions) > 0)
                                                                @foreach($sub->field_definitions as $field)
                                                                    <span class="badge bg-light text-dark border me-1" style="font-size:10px;">{{ $field['label'] }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted small">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('assets.index', ['category_id' => $sub->id]) }}" class="text-decoration-none">
                                                                <span class="badge bg-secondary">{{ $sub->assets_count }}</span>
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('asset-categories.edit', $sub) }}" class="btn btn-xs btn-outline-primary me-1">Düzenle</a>
                                                            <form action="{{ route('asset-categories.destroy', $sub) }}" method="POST" class="d-inline" data-delete>
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger">Sil</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endif
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

// Toggle icon rotation
document.querySelectorAll('.toggle-children').forEach(btn => {
    const target = document.querySelector(btn.dataset.bsTarget);
    if (target) {
        target.addEventListener('show.bs.collapse', () => btn.querySelector('.toggle-icon').textContent = '▼');
        target.addEventListener('hide.bs.collapse', () => btn.querySelector('.toggle-icon').textContent = '▶');
    }
});
</script>
@endpush
@endsection

