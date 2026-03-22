@extends('layouts.default')

@section('title', 'Rezervasyonlar')

@push('styles')
<style>
    .modern-dt { border-collapse: separate !important; border-spacing: 0 5px !important; }
    .modern-dt thead th { border: none !important; font-size: 11.5px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; white-space: nowrap; background: transparent; padding: 6px 12px 10px; }
    .modern-dt tbody tr { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.06); transition: box-shadow .15s, transform .1s; }
    .modern-dt tbody tr:hover { background: #fff !important; box-shadow: 0 3px 12px rgba(0,0,0,.12) !important; transform: translateY(-1px); }
    .modern-dt tbody tr td { border: none !important; vertical-align: middle; padding: 10px 12px; }
    .modern-dt tbody tr td:first-child { border-radius: 10px 0 0 10px; }
    .modern-dt tbody tr td:last-child  { border-radius: 0 10px 10px 0; }
    /* Column filters row */
    .col-filters th { padding: 4px 6px 8px !important; font-size: inherit !important; color: inherit !important; text-transform: none !important; letter-spacing: 0 !important; font-weight: normal !important; }
    .col-filters input { font-size: 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #f8fafc; transition: border-color .15s, box-shadow .15s; }
    .col-filters input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.15); background: #fff; }
    .col-filters input::placeholder { color: #cbd5e1; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Rezervasyonlar</h4>
                <span>Önbüro — Rezervasyon Takip</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Önbüro</span></li>
                <li class="breadcrumb-item active">Rezervasyonlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stat Cards --}}
    @php
        use App\Models\Reservation;
        $today = now()->toDateString();
        $pending    = $reservations->where('status', 'pending')->count();
        $confirmed  = $reservations->where('status', 'confirmed')->count();
        $checkedIn  = $reservations->where('status', 'checked_in')->count();
        $cancelled  = $reservations->where('status', 'cancelled')->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['label'=>'Beklemede','count'=>$pending,'color'=>'warning','icon'=>'fas fa-hourglass-half'],
            ['label'=>'Onaylandı','count'=>$confirmed,'color'=>'primary','icon'=>'fas fa-check'],
            ['label'=>'Giriş Yapıldı','count'=>$checkedIn,'color'=>'success','icon'=>'fas fa-sign-in-alt'],
            ['label'=>'İptal','count'=>$cancelled,'color'=>'danger','icon'=>'fas fa-times'],
        ] as $stat)
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:var(--bs-{{ $stat['color'] }}-bg, rgba(0,0,0,.05))">
                        <i class="{{ $stat['icon'] }} text-{{ $stat['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-{{ $stat['color'] }}">{{ $stat['count'] }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $stat['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-calendar-check me-2"></i>
            Rezervasyon Listesi
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $reservations->count() }}</span>
        </h5>
        @if(auth()->user()->hasPermission('reservations', 'create'))
        <a href="{{ route('frontdesk.reservations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Yeni Rezervasyon
        </a>
        @endif
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="px-3 py-2 border-bottom bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:.8rem">Göster</span>
                <select id="reservationsTable-len" class="form-select form-select-sm" style="width:72px">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted" style="font-size:.8rem">kayıt</span>
            </div>
            <input type="text" id="reservationsTable-search" class="form-control form-control-sm" placeholder="Ara..." style="max-width:220px">
        </div>
        <div class="table-responsive px-2 pt-1">
            <table id="reservationsTable" class="table modern-dt align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th>Rez. No</th>
                        <th>Acente</th>
                        <th>Oda</th>
                        <th>Giriş</th>
                        <th>Çıkış</th>
                        <th>Gece</th>
                        <th>Misafirler</th>
                        <th>Durum</th>
                        <th>Voucher</th>
                        <th>İşlem</th>
                    </tr>
                    <tr class="col-filters">
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="Ara..."></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="Ara..."></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="Ara..."></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="gg.aa.yy"></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="gg.aa.yy"></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="#"></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="Durum..."></th>
                        <th><input type="text" class="form-control form-control-sm w-100" placeholder="Ara..."></th>
                        <th></th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($reservations as $r)
                        @php $color = \App\Models\Reservation::STATUS_COLORS[$r->status] ?? 'secondary'; @endphp
                        <tr>
                            <td><span class="badge bg-dark fw-semibold">{{ $r->reservation_no }}</span></td>
                            <td>
                                <div class="fw-semibold small">{{ $r->agency->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:.73rem">{{ $r->agency->agency_code ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->room->room_number ?? '—' }}</div>
                                <div class="text-muted small">{{ $r->roomType->name ?? '' }}</div>
                            </td>
                            <td><span class="small">{{ $r->check_in_date->format('d.m.Y') }}</span></td>
                            <td><span class="small">{{ $r->check_out_date->format('d.m.Y') }}</span></td>
                            <td class="text-center fw-bold">{{ $r->nightCount() }}</td>
                            <td>
                                <span class="badge bg-primary me-1">{{ $r->adults }}Y</span>
                                @if($r->children > 0)<span class="badge bg-warning text-dark me-1">{{ $r->children }}Ç</span>@endif
                                @if($r->babies > 0)<span class="badge bg-danger">{{ $r->babies }}B</span>@endif
                                <div class="text-muted" style="font-size:.72rem">{{ $r->guests->count() }} misafir</div>
                            </td>
                            <td><span class="badge bg-{{ $color }}">{{ \App\Models\Reservation::STATUSES[$r->status] ?? $r->status }}</span></td>
                            <td><small class="text-muted">{{ $r->voucher_no ?? '—' }}</small></td>
                            <td>
                                @if(auth()->user()->hasPermission('reservations', 'show'))
                                <a href="{{ route('frontdesk.reservations.show', $r) }}" class="btn btn-sm btn-outline-info" title="Detay">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('reservations', 'delete'))
                                <form action="{{ route('frontdesk.reservations.destroy', $r) }}" method="POST" class="d-inline del-res-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-25"></i>
                            Henüz rezervasyon eklenmemiş.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
        <div class="px-3 py-2 border-top bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <small class="text-muted" id="reservationsTable-info"></small>
            <nav><ul class="pagination pagination-sm mb-0" id="reservationsTable-pagin"></ul></nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="{{ asset('js/modern-table.js') }}"></script>
<script>
$(function () {
    modernTable('reservationsTable', { pageLength: 25 });

    $(document).on('submit', '.del-res-form', function (e) {
        e.preventDefault(); const form = this;
        Swal.fire({
            title: 'Rezervasyon silinsin mi?', text: 'Bu işlem geri alınamaz!',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sil', cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
