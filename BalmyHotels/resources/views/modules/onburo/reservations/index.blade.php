@extends('layouts.default')

@section('title', 'Rezervasyonlar')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    table.dataTable thead th { white-space:nowrap; }
    .rez-row td { vertical-align:middle; }
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="reservationsTable" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $('#reservationsTable').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json' },
        order: [[3, 'desc']],
        pageLength: 25,
        columnDefs: [{ targets: [-1], orderable: false }]
    });

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
