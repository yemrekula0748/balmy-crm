@extends('layouts.default')

@section('content')
<div class="container-fluid">
    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Araç Yönetimi</h4>
                <span>Tüm şube araçları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Araçlar</li>
            </ol>
        </div>
    </div>

    {{-- Flash Mesajlar --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Yaklaşan Süresi Dolacak Sigortalar --}}
    @if($warnings->count())
        <div class="alert alert-warning alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Uyarı!</strong>
            {{ $warnings->count() }} araçta 30 gün içinde dolacak sigorta/kasko bulunmaktadır.
            <ul class="mb-0 mt-1">
                @foreach($warnings as $w)
                    <li>
                        <strong>{{ $w->vehicle->plate }}</strong> —
                        {{ $w->type === 'trafik' ? 'Trafik Sigortası' : 'Kasko' }}
                        <span class="text-danger">{{ \Carbon\Carbon::parse($w->end_date)->format('d.m.Y') }}</span>
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtre + Yeni Araç Butonu --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('vehicles.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Şube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">— Tüm Şubeler —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1">Arama (Plaka / Marka / Model)</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                value="{{ request('search') }}" placeholder="34 ABC 123...">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i> Filtrele
                            </button>
                            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Temizle
                            </a>
                            <a href="{{ route('vehicles.create') }}" class="btn btn-success btn-sm ms-auto">
                                <i class="fas fa-plus me-1"></i> Yeni Araç
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Araç Tablosu --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Araçlar
                        <span class="badge bg-primary ms-2">{{ $vehicles->total() }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Plaka</th>
                                    <th>Marka / Model</th>
                                    <th>Yıl</th>
                                    <th>Tip</th>
                                    <th>Şube</th>
                                    <th>Güncel KM</th>
                                    <th>Sigorta</th>
                                    <th>Kasko</th>
                                    <th>Durum</th>
                                    <th class="text-center">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $v)
                                    @php
                                        $ins   = $v->activeInsurance;
                                        $casc  = $v->activeCasco;
                                        $insExpiring = $ins && \Carbon\Carbon::parse($ins->end_date)->diffInDays(now(), false) > -30;
                                        $cascExpiring = $casc && \Carbon\Carbon::parse($casc->end_date)->diffInDays(now(), false) > -30;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('vehicles.show', $v) }}" class="fw-bold text-primary">
                                                {{ $v->plate }}
                                            </a>
                                        </td>
                                        <td>{{ $v->brand }} {{ $v->model }}</td>
                                        <td>{{ $v->year }}</td>
                                        <td>
                                            @php
                                                $types = ['binek'=>'Binek','minibus'=>'Minibüs','kamyonet'=>'Kamyonet','kamyon'=>'Kamyon','diger'=>'Diğer'];
                                            @endphp
                                            {{ $types[$v->type] ?? $v->type }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                {{ $v->branch->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($v->current_km) }} km</td>
                                        <td>
                                            @if($ins)
                                                <span class="badge {{ $insExpiring ? 'bg-warning text-dark' : 'bg-success' }}">
                                                    {{ \Carbon\Carbon::parse($ins->end_date)->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">Yok</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($casc)
                                                <span class="badge {{ $cascExpiring ? 'bg-warning text-dark' : 'bg-success' }}">
                                                    {{ \Carbon\Carbon::parse($casc->end_date)->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">Yok</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($v->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('vehicles.show', $v) }}"
                                               class="btn btn-xs btn-info" title="Detay">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('vehicles.edit', $v) }}"
                                               class="btn btn-xs btn-warning" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vehicles.destroy', $v) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirmDelete(event)">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-car fa-2x d-block mb-2 opacity-25"></i>
                                            Araç bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Sayfalama --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $vehicles->links() }}
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
        title: 'Aracı sil?',
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

@if(session('success'))
    toastr.success("{{ session('success') }}");
@endif
</script>
@endpush
