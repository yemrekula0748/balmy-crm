@extends('layouts.default')

@section('title', 'Misafir Kontrol Kayitlari')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/jquery.dataTables.min.css') }}">
<style>
    .guest-control-history-card { border: 0; border-radius: 18px; box-shadow: 0 16px 35px rgba(15, 23, 42, .08); }
    .guest-control-stat-card {
        border-radius: 16px;
        padding: 1.15rem 1.2rem;
        background: linear-gradient(180deg, #fff, #f8fafc);
        border: 1px solid rgba(148, 163, 184, .14);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .04);
    }
    .guest-control-table-wrap .dataTables_wrapper .dataTables_filter input,
    .guest-control-table-wrap .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        min-height: 38px;
        padding: .45rem .75rem;
        background: #fff;
    }
    .guest-control-table-wrap .dataTables_wrapper .dataTables_filter,
    .guest-control-table-wrap .dataTables_wrapper .dataTables_length {
        margin-bottom: 1rem;
    }
    .guest-control-table-wrap table.dataTable thead th {
        border-bottom: 0 !important;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: .9rem .8rem;
        background: #f8fafc;
    }
    .guest-control-table-wrap table.dataTable tbody td {
        padding: .95rem .8rem;
        border-top: 1px solid #eef2f7;
        vertical-align: middle;
    }
    .guest-control-table-wrap .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 10px !important;
        border: 1px solid transparent !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Misafir Kontrol Kayitlari</h4>
                <span>Balmy Foresta icin kaydedilen toplu giris/cikis islemleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Onburo</span></li>
                <li class="breadcrumb-item active">Misafir Kontrol Kayitlari</li>
            </ol>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="guest-control-stat-card h-100">
                <div class="text-muted small mb-1">Toplam Kayit</div>
                <div class="display-6 fw-bold text-dark">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="guest-control-stat-card h-100">
                <div class="text-muted small mb-1">Giris Yapti</div>
                <div class="display-6 fw-bold text-success">{{ $stats['check_in'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="guest-control-stat-card h-100">
                <div class="text-muted small mb-1">Cikis Yapti</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['check_out'] }}</div>
            </div>
        </div>
    </div>

    <div class="card guest-control-history-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Filtreler</h5>
                    <small class="text-muted">Server filtresi + tablo icinde ek DataTable aramasi kullanabilirsin.</small>
                </div>
                @if(auth()->user()->hasPermission('guest_control', 'index'))
                    <a href="{{ route('frontdesk.guest-control.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Sorgu Ekranina Don
                    </a>
                @endif
            </div>

            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Baslangic</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Bitis</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Oda</label>
                    <input type="text" name="room_no" class="form-control" value="{{ $roomNo }}" placeholder="Orn: 682">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Islem</label>
                    <select name="action_type" class="form-select">
                        <option value="">Tum Islemler</option>
                        <option value="check_in" @selected($actionType === 'check_in')>Giris Yapti</option>
                        <option value="check_out" @selected($actionType === 'check_out')>Cikis Yapti</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ara</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Misafir, telefon, e-posta">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary">Filtrele</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('frontdesk.guest-control.history') }}" class="btn btn-link ps-0">Filtreleri temizle</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card guest-control-history-card">
        <div class="card-body p-4 guest-control-table-wrap">
            <div class="table-responsive">
                <table id="guestControlLogsTable" class="table w-100 align-middle">
                    <thead>
                        <tr>
                            <th>Kayit Zamani</th>
                            <th>Islem</th>
                            <th>Oda</th>
                            <th>Misafir</th>
                            <th>API Check-in</th>
                            <th>API Check-out</th>
                            <th>Iletisim</th>
                            <th>Kaydeden</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td data-order="{{ optional($log->action_at)->format('Y-m-d H:i:s') }}">
                                    <div class="fw-semibold text-dark">{{ optional($log->action_at)->format('d.m.Y') }}</div>
                                    <small class="text-muted">{{ optional($log->action_at)->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\GuestControlLog::ACTION_COLORS[$log->action_type] ?? 'secondary' }}-subtle text-{{ \App\Models\GuestControlLog::ACTION_COLORS[$log->action_type] ?? 'secondary' }}">
                                        {{ $log->actionLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ $log->room_no }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->full_name }}</div>
                                    <small class="text-muted">{{ $log->hotel_name }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ optional($log->hotel_checkin_date)->format('d.m.Y') ?? '-' }}</div>
                                    @if($log->arrival_time)
                                        <small class="text-muted">{{ $log->arrival_time }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ optional($log->hotel_checkout_date)->format('d.m.Y') ?? '-' }}</div>
                                    @if($log->departure_time)
                                        <small class="text-muted">{{ $log->departure_time }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $log->phone ?? '-' }}</div>
                                    <small class="text-muted">{{ $log->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->creator?->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $log->branch?->name ?? 'Balmy Foresta' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script>
    (function ($) {
        $('#guestControlLogsTable').DataTable({
            pageLength: 25,
            lengthChange: false,
            order: [[0, 'desc']],
            language: {
                search: 'Tablo icinde ara:',
                zeroRecords: 'Kayit bulunamadi',
                info: '_TOTAL_ kayittan _START_ - _END_ arasi',
                infoEmpty: 'Kayit yok',
                paginate: {
                    previous: 'Geri',
                    next: 'Ileri',
                },
            },
        });
    })(jQuery);
</script>
@endpush
