@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">PDKS Devam Kayıtları</h4><div class="pdks-muted">Giriş/çıkış, çalışma süresi, mola ve doğrulama durumu</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')

    <div class="pdks-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Personel</label><select name="employee_id" class="form-select form-select-sm"><option value="">Tümü</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Başlangıç</label><input type="date" name="date_from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Bitiş</label><input type="date" name="date_to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Durum</label><select name="status" class="form-select form-select-sm"><option value="">Tümü</option><option value="open">Açık</option><option value="closed">Kapalı</option></select></div>
            <div class="col-md-1"><button class="btn btn-sm pdks-btn-main w-100">Filtrele</button></div>
        </form>
    </div>

    <div class="pdks-card">
        <div class="table-responsive">
            <table class="table pdks-table align-middle mb-0">
                <thead><tr><th>Tarih</th><th>Personel</th><th>Vardiya</th><th>Giriş</th><th>Çıkış</th><th>Çalışma</th><th>Mola</th><th>Geç</th><th>Doğrulama</th></tr></thead>
                <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->work_date->format('d.m.Y') }}</td>
                        <td><strong>{{ $record->employee->name }}</strong><div class="small text-muted">{{ $record->employee->department->name ?? '—' }}</div></td>
                        <td>{{ $record->shiftAssignment?->shiftType?->name ?? '—' }}</td>
                        <td>{{ $record->check_in_at?->format('H:i') ?? '—' }}</td>
                        <td>{{ $record->check_out_at?->format('H:i') ?? '—' }}</td>
                        <td>{{ $record->worked_time_label }}</td>
                        <td>{{ $record->break_minutes }} dk.</td>
                        <td>{{ $record->late_minutes }} dk.</td>
                        <td><span class="pdks-chip {{ $record->verification_status === 'verified' ? 'pdks-chip-ok' : 'pdks-chip-bad' }}">{{ $record->verification_status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="p-3 border-top">{{ $records->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection
