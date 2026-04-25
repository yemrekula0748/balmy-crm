@extends('layouts.default')

@section('title', 'Kontratlar')

@push('styles')
<style>
    /* ── Layout ── */
    .modern-dt { border-collapse: collapse !important; }
    .modern-dt thead tr.col-header { background: #f1f5f9; }
    .modern-dt thead th { border: none !important; font-size: 11px; color: #64748b; font-weight: 700;
        text-transform: uppercase; letter-spacing: .6px; white-space: nowrap;
        padding: 11px 14px; border-bottom: 2px solid #e2e8f0 !important; }
    .modern-dt tbody tr { background: #fff; transition: background .1s; }
    .modern-dt tbody tr:nth-child(even) { background: #fafbfc; }
    .modern-dt tbody tr:hover { background: #f0f6ff !important; }
    .modern-dt tbody tr td { border: none !important; border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle; padding: 10px 14px; }

    /* ── Status stripe (left border via outline trick) ── */
    .modern-dt tbody tr.contract-active  td:first-child { border-left: 3px solid #16a34a !important; }
    .modern-dt tbody tr.contract-expired td:first-child { border-left: 3px solid #dc2626 !important; }
    .modern-dt tbody tr.contract-future  td:first-child { border-left: 3px solid #d97706 !important; }

    /* ── Contract code chip ── */
    .code-chip { display:inline-flex; align-items:center; gap:6px;
        background:#1e293b; color:#f8fafc; border-radius:6px;
        padding:4px 10px; font-size:12px; font-weight:700; letter-spacing:.3px; }

    /* ── Agency cell ── */
    .agency-label { font-size:13px; font-weight:600; color:#1e293b; }
    .agency-sub   { font-size:11px; color:#94a3b8; margin-top:1px; }

    /* ── Date range ── */
    .date-from { color:#16a34a; font-weight:600; font-size:12.5px; }
    .date-to   { color:#dc2626; font-weight:600; font-size:12.5px; }
    .date-arrow{ color:#cbd5e1; margin:0 4px; font-size:11px; }

    /* ── Status badges ── */
    .status-active  { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;
        font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-block; }
    .status-expired { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;
        font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-block; }
    .status-future  { background:#fef9c3; color:#92400e; border:1px solid #fde68a;
        font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-block; }

    /* ── Room type chip ── */
    .room-chip { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;
        font-size:11.5px; font-weight:600; padding:3px 9px; border-radius:5px; display:inline-block; }

    /* ── Price grid ── */
    .price-grid { display:flex; flex-wrap:wrap; gap:3px; }
    .price-pill { display:inline-flex; align-items:center; gap:4px; border-radius:4px;
        padding:3px 7px; font-size:11px; white-space:nowrap; border:1px solid transparent; }
    .price-pill-adult   { background:#f0fdf4; border-color:#bbf7d0; }
    .price-pill-adult .pp-label   { color:#15803d; font-weight:700; }
    .price-pill-adult .pp-val     { color:#166534; font-weight:700; }
    .price-pill-child   { background:#fefce8; border-color:#fde68a; }
    .price-pill-child .pp-label   { color:#92400e; font-weight:700; }
    .price-pill-child .pp-val     { color:#78350f; font-weight:700; }
    .price-pill-baby    { background:#fff1f2; border-color:#fecdd3; }
    .price-pill-baby .pp-label    { color:#be123c; font-weight:700; }
    .price-pill-baby .pp-val      { color:#9f1239; font-weight:700; }

    /* ── Table toolbar ── */
    .table-toolbar { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 16px; }

    /* ── Stat cards ── */
    .stat-card { border-radius:10px; overflow:hidden; }
    .stat-card .stat-icon { width:46px;height:46px;border-radius:8px;
        display:flex;align-items:center;justify-content:center;font-size:18px; }
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
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-check-circle" style="color:#16a34a"></i></div>
                    <div>
                        <div class="fs-3 fw-bold lh-1 mb-1" style="color:#16a34a">{{ $active }}</div>
                        <div class="text-uppercase fw-semibold" style="font-size:10.5px;color:#64748b;letter-spacing:.5px">Aktif Kontrat</div>
                    </div>
                    <div class="ms-auto" style="font-size:38px;font-weight:900;color:#dcfce7;line-height:1">A</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#fef9c3"><i class="fas fa-clock" style="color:#d97706"></i></div>
                    <div>
                        <div class="fs-3 fw-bold lh-1 mb-1" style="color:#d97706">{{ $future }}</div>
                        <div class="text-uppercase fw-semibold" style="font-size:10.5px;color:#64748b;letter-spacing:.5px">Gelecek Kontrat</div>
                    </div>
                    <div class="ms-auto" style="font-size:38px;font-weight:900;color:#fef9c3;line-height:1">G</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#fee2e2"><i class="fas fa-times-circle" style="color:#dc2626"></i></div>
                    <div>
                        <div class="fs-3 fw-bold lh-1 mb-1" style="color:#dc2626">{{ $expired }}</div>
                        <div class="text-uppercase fw-semibold" style="font-size:10.5px;color:#64748b;letter-spacing:.5px">Süresi Dolmuş</div>
                    </div>
                    <div class="ms-auto" style="font-size:38px;font-weight:900;color:#fee2e2;line-height:1">S</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-toolbar d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:.8rem">Göster</span>
                <select id="contractsTable-len" class="form-select form-select-sm" style="width:72px">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted" style="font-size:.8rem">kayıt</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2" style="font-size:11px;color:#64748b">
                    <span class="d-inline-block" style="width:10px;height:10px;border-radius:2px;background:#16a34a"></span>Aktif
                    <span class="d-inline-block ms-1" style="width:10px;height:10px;border-radius:2px;background:#d97706"></span>Gelecek
                    <span class="d-inline-block ms-1" style="width:10px;height:10px;border-radius:2px;background:#dc2626"></span>Süresi Dolmuş
                </div>
                <input type="text" id="contractsTable-search" class="form-control form-control-sm" placeholder="Ara..." style="max-width:220px">
            </div>
        </div>
        <div class="table-responsive">
            <table id="contractsTable" class="table modern-dt align-middle mb-0 w-100">
                <thead class="col-header">
                    <tr>
                        <th>Kontrat Kodu</th>
                        <th>Acente</th>
                        <th>Oda Tipi</th>
                        <th>Tarih Aralığı</th>
                        <th>Durum</th>
                        <th>Fiyatlar</th>
                        <th style="width:60px"></th>
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
                            <td>
                                <span class="code-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    {{ $contract->contract_code }}
                                </span>
                            </td>
                            <td>
                                <div class="agency-label">{{ $contract->agency->name ?? '—' }}</div>
                                <div class="agency-sub">{{ $contract->agency->agency_code ?? '' }}</div>
                            </td>
                            <td><span class="room-chip">{{ $contract->roomType->name ?? '—' }}</span></td>
                            <td>
                                <span class="date-from">{{ $contract->start_date->format('d.m.Y') }}</span>
                                <span class="date-arrow">→</span>
                                <span class="date-to">{{ $contract->end_date->format('d.m.Y') }}</span>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px">
                                    {{ $contract->start_date->diffInDays($contract->end_date) }} gün
                                </div>
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="status-active">✓ Aktif</span>
                                @elseif($isFuture)
                                    <span class="status-future">● Gelecek</span>
                                @else
                                    <span class="status-expired">× Süresi Doldu</span>
                                @endif
                            </td>
                            <td>
                                <div class="price-grid">
                                    @if($contract->price_single)
                                    <span class="price-pill price-pill-adult"><span class="pp-label">1Y</span><span class="pp-val">{{ number_format($contract->price_single,2) }}</span></span>
                                    @endif
                                    @if($contract->price_double)
                                    <span class="price-pill price-pill-adult"><span class="pp-label">2Y</span><span class="pp-val">{{ number_format($contract->price_double,2) }}</span></span>
                                    @endif
                                    @if($contract->price_triple)
                                    <span class="price-pill price-pill-adult"><span class="pp-label">3Y</span><span class="pp-val">{{ number_format($contract->price_triple,2) }}</span></span>
                                    @endif
                                    @if($contract->price_quad)
                                    <span class="price-pill price-pill-adult"><span class="pp-label">4Y</span><span class="pp-val">{{ number_format($contract->price_quad,2) }}</span></span>
                                    @endif
                                    @if($contract->price_child1)
                                    <span class="price-pill price-pill-child"><span class="pp-label">Ç1</span><span class="pp-val">{{ number_format($contract->price_child1,2) }}</span></span>
                                    @endif
                                    @if($contract->price_child2)
                                    <span class="price-pill price-pill-child"><span class="pp-label">Ç2</span><span class="pp-val">{{ number_format($contract->price_child2,2) }}</span></span>
                                    @endif
                                    @if($contract->price_baby1)
                                    <span class="price-pill price-pill-baby"><span class="pp-label">B1</span><span class="pp-val">{{ number_format($contract->price_baby1,2) }}</span></span>
                                    @endif
                                    @if($contract->price_baby2)
                                    <span class="price-pill price-pill-baby"><span class="pp-label">B2</span><span class="pp-val">{{ number_format($contract->price_baby2,2) }}</span></span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
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
        <div class="px-3 py-2 border-top bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <small class="text-muted" id="contractsTable-info"></small>
            <nav><ul class="pagination pagination-sm mb-0" id="contractsTable-pagin"></ul></nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="{{ asset('js/modern-table.js') }}"></script>
<script>
$(function () {
    modernTable('contractsTable', { pageLength: 25 });

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
