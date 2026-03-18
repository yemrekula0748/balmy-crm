@extends('layouts.default')

@section('title', 'Kontratlar')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .contract-active  { border-left: 3px solid #22c55e; }
    .contract-expired { border-left: 3px solid #ef4444; }
    .contract-future  { border-left: 3px solid #f59e0b; }
    .price-pill { display:inline-flex; align-items:center; gap:4px; background:#f8f9fa;
                  border:1px solid #dee2e6; border-radius:20px; padding:2px 9px;
                  font-size:.72rem; white-space:nowrap; margin:2px; }
    .price-pill .pp-label { color:#6c757d; font-weight:600; }
    .price-pill .pp-val   { font-weight:700; color:#212529; }
    .price-section { margin-bottom:3px; }
    #contractsTable thead th { white-space:nowrap; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4b5563; }
    #contractsTable tbody tr:hover { background:#fdf8f4 !important; }
    #contractsTable td { vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Kontratlar</h4>
                <span>Acente Yönetimi — Kontrat Listesi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agencies.index') }}">Acenteler</a></li>
                <li class="breadcrumb-item active">Kontratlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <h5 class="mb-0 fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Kontrat Listesi
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $contracts->count() }}</span>
        </h5>
        @if(auth()->user()->hasPermission('agency_contracts', 'create'))
        <a href="{{ route('agencies.contracts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Yeni Kontrat Ekle
        </a>
        @endif
    </div>

    {{-- Stat Cards --}}
    @php
        $now = now()->toDateString();
        $active  = $contracts->filter(fn($c) => $c->start_date->toDateString() <= $now && $c->end_date->toDateString() >= $now)->count();
        $expired = $contracts->filter(fn($c) => $c->end_date->toDateString() < $now)->count();
        $future  = $contracts->filter(fn($c) => $c->start_date->toDateString() > $now)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #22c55e !important">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(34,197,94,.15)">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ $active }}</div>
                        <div class="text-muted small">Aktif Kontrat</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b !important">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(245,158,11,.15)">
                        <i class="fas fa-clock text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ $future }}</div>
                        <div class="text-muted small">Gelecek Kontrat</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ef4444 !important">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(239,68,68,.15)">
                        <i class="fas fa-times-circle text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">{{ $expired }}</div>
                        <div class="text-muted small">Süresi Dolmuş</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="contractsTable" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Kontrat Kodu</th>
                            <th>Acente</th>
                            <th>Oda Tipi</th>
                            <th>Tarih Aralığı</th>
                            <th>Durum</th>
                            <th>Fiyatlar (YT / Ç0 / B0)</th>
                            <th style="width:70px">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        @php
                            $isActive  = $contract->start_date->toDateString() <= $now && $contract->end_date->toDateString() >= $now;
                            $isExpired = $contract->end_date->toDateString() < $now;
                            $isFuture  = $contract->start_date->toDateString() > $now;
                            $rowClass  = $isActive ? 'contract-active' : ($isExpired ? 'contract-expired' : 'contract-future');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td><span class="badge bg-dark fw-semibold">{{ $contract->contract_code }}</span></td>
                            <td>
                                <div class="fw-semibold small">{{ $contract->agency->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:.73rem">{{ $contract->agency->agency_code ?? '' }}</div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $contract->roomType->name ?? '—' }}</span></td>
                            <td>
                                <span class="small text-success fw-semibold">{{ $contract->start_date->format('d.m.Y') }}</span>
                                <span class="text-muted mx-1">→</span>
                                <span class="small text-danger fw-semibold">{{ $contract->end_date->format('d.m.Y') }}</span>
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($isFuture)
                                    <span class="badge bg-warning text-dark">Gelecek</span>
                                @else
                                    <span class="badge bg-danger">Süresi Doldu</span>
                                @endif
                            </td>
                            <td>
                                <div class="price-section">
                                    @if($contract->price_single)
                                    <span class="price-pill"><span class="pp-label">1Y</span><span class="pp-val">{{ number_format($contract->price_single,2) }}</span></span>
                                    @endif
                                    @if($contract->price_double)
                                    <span class="price-pill"><span class="pp-label">2Y</span><span class="pp-val">{{ number_format($contract->price_double,2) }}</span></span>
                                    @endif
                                    @if($contract->price_triple)
                                    <span class="price-pill"><span class="pp-label">3Y</span><span class="pp-val">{{ number_format($contract->price_triple,2) }}</span></span>
                                    @endif
                                    @if($contract->price_quad)
                                    <span class="price-pill"><span class="pp-label">4Y</span><span class="pp-val">{{ number_format($contract->price_quad,2) }}</span></span>
                                    @endif
                                </div>
                                <div class="price-section">
                                    @if($contract->price_child1)
                                    <span class="price-pill" style="background:#fff8e1;border-color:#f9c74f"><span class="pp-label">Ç1</span><span class="pp-val">{{ number_format($contract->price_child1,2) }}</span></span>
                                    @endif
                                    @if($contract->price_child2)
                                    <span class="price-pill" style="background:#fff8e1;border-color:#f9c74f"><span class="pp-label">Ç2</span><span class="pp-val">{{ number_format($contract->price_child2,2) }}</span></span>
                                    @endif
                                    @if($contract->price_baby1)
                                    <span class="price-pill" style="background:#ffeded;border-color:#f87171"><span class="pp-label">B1</span><span class="pp-val">{{ number_format($contract->price_baby1,2) }}</span></span>
                                    @endif
                                    @if($contract->price_baby2)
                                    <span class="price-pill" style="background:#ffeded;border-color:#f87171"><span class="pp-label">B2</span><span class="pp-val">{{ number_format($contract->price_baby2,2) }}</span></span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('agency_contracts', 'delete'))
                                <form action="{{ route('agencies.contracts.destroy', $contract) }}" method="POST" class="d-inline delete-contract-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-file-contract fa-2x mb-2 d-block opacity-25"></i>
                            Henüz kontrat eklenmemiş.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $('#contractsTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json' },
        order: [[3, 'desc']],
        pageLength: 25,
        columnDefs: [{ targets: [-1], orderable: false, searchable: false }]
    });

    $(document).on('submit', '.delete-contract-form', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Kontrat silinsin mi?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
