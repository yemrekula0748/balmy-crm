@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Çıkış Formu Detayı</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('asset-exits.index') }}">Çıkış Formları</a></li>
                <li class="breadcrumb-item active">#{{ $assetExit->id }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    {{-- BAŞLIK KARTI --}}
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge bg-{{ \App\Models\AssetExit::STATUS_COLORS[$assetExit->status] }} fs-6">
                            {{ \App\Models\AssetExit::STATUSES[$assetExit->status] }}
                        </span>
                        <span class="badge bg-light text-dark border fs-6">
                            {{ \App\Models\AssetExit::TAKER_TYPES[$assetExit->taker_type] }}
                        </span>
                        @if($assetExit->isOverdue())
                            <span class="badge bg-danger fs-6">⚠ Gecikmiş İade</span>
                        @endif
                    </div>
                    <h3 class="mb-1">Form #{{ $assetExit->id }}</h3>
                    <div class="text-muted">
                        Demirbaş:
                        <a href="{{ route('assets.show', $assetExit->asset) }}" class="text-decoration-none fw-semibold">
                            {{ $assetExit->asset->name ?? '—' }} ({{ $assetExit->asset->asset_code ?? '' }})
                        </a>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($assetExit->status === 'pending')
                        <form action="{{ route('asset-exits.approve', $assetExit) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-success">✓ Onayla</button>
                        </form>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">✕ Reddet</button>
                    @endif
                    @if($assetExit->status === 'approved' && !$assetExit->returned_at)
                        <form action="{{ route('asset-exits.return', $assetExit) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-info text-white">↩ İade Al</button>
                        </form>
                    @endif
                    @if($assetExit->status !== 'approved')
                        <form action="{{ route('asset-exits.destroy', $assetExit) }}" method="POST" class="d-inline" id="deleteForm">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-outline-danger" id="deleteBtn">Sil</button>
                        </form>
                    @endif
                    <a href="{{ route('asset-exits.index') }}" class="btn btn-outline-secondary">← Geri</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Alan Kişi Bilgileri</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="text-muted" style="width:45%">Tip</td><td>{{ \App\Models\AssetExit::TAKER_TYPES[$assetExit->taker_type] }}</td></tr>
                            @if($assetExit->taker_type === 'staff')
                                <tr><td class="text-muted">Personel</td><td>{{ $assetExit->staff?->name ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Departman</td><td>{{ $assetExit->staff?->department?->name ?? '—' }}</td></tr>
                            @else
                                <tr><td class="text-muted">Misafir Adı</td><td>{{ $assetExit->guest_name ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Oda No</td><td>{{ $assetExit->guest_room ?? '—' }}</td></tr>
                                <tr><td class="text-muted">TC/Pasaport</td><td>{{ $assetExit->guest_id_no ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Telefon</td><td>{{ $assetExit->guest_phone ?? '—' }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Form Bilgileri</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="text-muted" style="width:45%">Demirbaş</td><td>{{ $assetExit->asset->name ?? '—' }} <code class="small">({{ $assetExit->asset->asset_code ?? '' }})</code></td></tr>
                            <tr><td class="text-muted">Şube</td><td>{{ $assetExit->branch->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Durum</td>
                                <td><span class="badge bg-{{ \App\Models\AssetExit::STATUS_COLORS[$assetExit->status] }}">{{ \App\Models\AssetExit::STATUSES[$assetExit->status] }}</span></td></tr>
                            <tr><td class="text-muted">Çıkış Tarihi</td><td>{{ $assetExit->taken_at->format('d.m.Y H:i') }}</td></tr>
                            <tr><td class="text-muted">Beklenen İade</td>
                                <td class="{{ $assetExit->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                    {{ $assetExit->expected_return_at?->format('d.m.Y H:i') ?? '—' }}
                                    @if($assetExit->isOverdue()) ⚠ @endif
                                </td></tr>
                            <tr><td class="text-muted">İade Tarihi</td>
                                <td class="text-success">{{ $assetExit->returned_at?->format('d.m.Y H:i') ?? '—' }}</td></tr>
                            @if($assetExit->approved_by)
                                <tr><td class="text-muted">Onaylayan</td><td>{{ $assetExit->approver?->name ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Onay Tarihi</td><td>{{ $assetExit->approved_at?->format('d.m.Y H:i') ?? '—' }}</td></tr>
                            @endif
                            @if($assetExit->rejected_reason)
                                <tr><td class="text-muted">Red Sebebi</td><td class="text-danger">{{ $assetExit->rejected_reason }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Çıkış Sebebi</h5></div>
                <div class="card-body"><p class="mb-0">{{ $assetExit->purpose }}</p></div>
            </div>
        </div>

        @if($assetExit->notes)
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Notlar</h5></div>
                    <div class="card-body"><p class="mb-0">{{ $assetExit->notes }}</p></div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Red Modal --}}
@if($assetExit->status === 'pending')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formu Reddet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('asset-exits.reject', $assetExit) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-semibold">Red Sebebi <span class="text-danger">*</span></label>
                    <textarea name="rejected_reason" class="form-control" rows="3" required
                              placeholder="Neden reddedildiğini açıklayın..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('deleteBtn')?.addEventListener('click', function() {
    Swal.fire({ title: 'Formu sil?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Evet, sil', cancelButtonText: 'İptal'
    }).then(r => { if(r.isConfirmed) document.getElementById('deleteForm').submit(); });
});
</script>
@endpush
@endsection
