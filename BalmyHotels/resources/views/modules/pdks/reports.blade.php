@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">PDKS Raporları</h4><div class="pdks-muted">Attendance, leave ve overtime özetleri</div></div></div>
    @include('modules.pdks._nav')
    <div class="pdks-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="small">Başlangıç</label><input type="date" name="date_from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-md-3"><label class="small">Bitiş</label><input type="date" name="date_to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><button class="btn btn-sm pdks-btn-main w-100">Raporla</button></div>
        </form>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-users"></i><div><small class="text-muted">Personel</small><div class="fw-bold">{{ $employeeSummary->count() }}</div></div></div></div>
        <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-calendar"></i><div><small class="text-muted">Kayıt</small><div class="fw-bold">{{ $attendance->count() }}</div></div></div></div>
        <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-clock"></i><div><small class="text-muted">Çalışma</small><div class="fw-bold">{{ intdiv($attendance->sum('worked_minutes'),60) }} sa.</div></div></div></div>
        <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-business-time"></i><div><small class="text-muted">Onaylı FM</small><div class="fw-bold">{{ intdiv(($overtimeSummary['approved'] ?? collect())->sum('duration_minutes'),60) }} sa.</div></div></div></div>
    </div>
    <div class="pdks-card">
        <div class="table-responsive">
            <table class="table pdks-table align-middle mb-0">
                <thead><tr><th>Personel</th><th>Gün</th><th>Çalışma</th><th>Mola</th><th>Geç Kalma</th><th>Erken Çıkış</th></tr></thead>
                <tbody>
                @forelse($employeeSummary as $row)
                    <tr><td><strong>{{ $row['employee']->name ?? '—' }}</strong><div class="small text-muted">{{ $row['employee']->department->name ?? '—' }}</div></td><td>{{ $row['days'] }}</td><td>{{ intdiv($row['worked_minutes'],60) }} sa. {{ $row['worked_minutes'] % 60 }} dk.</td><td>{{ $row['break_minutes'] }} dk.</td><td>{{ $row['late_minutes'] }} dk.</td><td>{{ $row['early_leave_minutes'] }} dk.</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Rapor verisi yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
