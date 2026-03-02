@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Yönetimi</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Demirbaş</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    {{-- İSTATİSTİK KARTLARI --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-primary h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Toplam</div>
                    <div class="fw-bold fs-3">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-success h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Mevcut</div>
                    <div class="fw-bold fs-3 text-success">{{ $stats['available'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-warning h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Kullanımda</div>
                    <div class="fw-bold fs-3 text-warning">{{ $stats['in_use'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-secondary h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Bakım / H.Dışı</div>
                    <div class="fw-bold fs-3 text-secondary">{{ $stats['maintenance'] + $stats['retired'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FİLTRE --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('assets.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Ad, Kod, Seri No..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach(\App\Models\Asset::STATUSES as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filtrele</button>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
                    <a href="{{ route('assets.create') }}" class="btn btn-sm ms-auto" style="background:#c19b77;color:#fff">+ Ekle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- LİSTE --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kod</th>
                            <th>Ad</th>
                            <th>Kategori</th>
                            <th>Şube</th>
                            <th>Konum</th>
                            <th>Durum</th>
                            <th>Garanti</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td><code class="text-dark">{{ $asset->asset_code }}</code></td>
                                <td>
                                    <a href="{{ route('assets.show', $asset) }}" class="text-decoration-none fw-semibold">
                                        {{ $asset->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($asset->category)
                                        <span class="badge" style="background:{{ $asset->category->color }}">{{ $asset->category->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $asset->branch->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $asset->location ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\Asset::STATUS_COLORS[$asset->status] }}">
                                        {{ \App\Models\Asset::STATUS_ICONS[$asset->status] }} {{ \App\Models\Asset::STATUSES[$asset->status] }}
                                    </span>
                                </td>
                                <td class="small">
                                    @if($asset->warranty_until)
                                        <span class="{{ $asset->isWarrantyExpired() ? 'text-danger' : 'text-success' }}">
                                            {{ $asset->warranty_until->format('d.m.Y') }}
                                            @if($asset->isWarrantyExpired()) <small>(Doldu)</small> @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('assets.show', $asset) }}" class="btn btn-xs btn-outline-info me-1">Detay</a>
                                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-xs btn-outline-primary me-1">Düzenle</a>
                                    <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="d-inline" data-delete>
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Demirbaş bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assets->hasPages())
            <div class="card-footer">{{ $assets->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('[data-delete]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Demirbaşı sil?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Evet, sil', cancelButtonText: 'İptal'
        }).then(r => { if(r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
