@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3">
        <div class="pdks-hero-bar"></div>
        <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1"><i class="fas fa-fingerprint me-2" style="color:#c19b77"></i>Personel PDKS</h4>
                <div class="pdks-muted">GPS zorunlu giriş/çıkış, mola, izin, vardiya ve fazla mesai takibi</div>
            </div>
            <div class="text-end">
                <div class="fw-bold">{{ $employee->name }}</div>
                <div class="small text-muted">{{ $employee->branch->name ?? 'Şube yok' }} · {{ $employee->department->name ?? 'Departman yok' }}</div>
            </div>
        </div>
    </div>

    @include('modules.pdks._nav')
    @include('modules.pdks._flash')

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="pdks-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="small text-muted">Bugünkü Durum</div>
                        <h5 class="mb-0">{{ now()->format('d.m.Y') }}</h5>
                    </div>
                    @if($todayRecord?->check_out_at)
                        <span class="pdks-chip pdks-chip-ok">Gün kapandı</span>
                    @elseif($todayRecord?->check_in_at)
                        <span class="pdks-chip pdks-chip-warn">İçeride</span>
                    @else
                        <span class="pdks-chip pdks-chip-bad">Giriş yok</span>
                    @endif
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6"><div class="border rounded p-2"><small class="text-muted">Giriş</small><div class="fw-bold">{{ $todayRecord?->check_in_at?->format('H:i') ?? '—' }}</div></div></div>
                    <div class="col-6"><div class="border rounded p-2"><small class="text-muted">Çıkış</small><div class="fw-bold">{{ $todayRecord?->check_out_at?->format('H:i') ?? '—' }}</div></div></div>
                    <div class="col-6"><div class="border rounded p-2"><small class="text-muted">Çalışma</small><div class="fw-bold">{{ $todayRecord?->worked_time_label ?? '0 sa. 0 dk.' }}</div></div></div>
                    <div class="col-6"><div class="border rounded p-2"><small class="text-muted">Mola</small><div class="fw-bold">{{ (int)($todayRecord?->break_minutes ?? 0) }} dk.</div></div></div>
                </div>

                @if($todayShift)
                    <div class="alert alert-light border small">
                        <strong>Vardiya:</strong> {{ $todayShift->shiftType->name ?? '—' }}
                        <span class="text-muted">({{ substr($todayShift->shiftType->start_time ?? '',0,5) }} - {{ substr($todayShift->shiftType->end_time ?? '',0,5) }})</span>
                    </div>
                @endif

                @if($activeBreak)
                    <form method="POST" action="{{ route('pdks.break-end') }}" class="pdks-location-form">
                        @csrf
                        @include('modules.pdks._location_fields')
                        <button class="btn btn-warning w-100 fw-bold"><i class="fas fa-mug-hot me-1"></i>Aktif Molayı Bitir: {{ $activeBreak->breakType->name }}</button>
                    </form>
                @elseif(!$todayRecord?->check_in_at)
                    <form method="POST" action="{{ route('pdks.check-in') }}" class="pdks-location-form">
                        @csrf
                        @include('modules.pdks._location_fields')
                        <button class="btn pdks-btn-main w-100 fw-bold"><i class="fas fa-sign-in-alt me-1"></i>Giriş Yap</button>
                    </form>
                @elseif(!$todayRecord?->check_out_at)
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('pdks.check-out') }}" class="pdks-location-form">
                            @csrf
                            @include('modules.pdks._location_fields')
                            <button class="btn btn-danger w-100 fw-bold"><i class="fas fa-sign-out-alt me-1"></i>Çıkış Yap</button>
                        </form>
                        <form method="POST" action="{{ route('pdks.break-start') }}" class="pdks-location-form">
                            @csrf
                            @include('modules.pdks._location_fields')
                            <div class="input-group">
                                <select name="break_type_id" class="form-select" required>
                                    @foreach($breakTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} @if($type->max_minutes)({{ $type->max_minutes }} dk.)@endif</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary fw-bold">Mola Başlat</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="alert alert-success mb-0">Bugünkü giriş/çıkış tamamlandı.</div>
                @endif

                <div class="small text-muted mt-3" id="pdks-location-status">Konum bilgisi gönderim sırasında alınacak.</div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-calendar-check"></i><div><small class="text-muted">Gün</small><div class="fw-bold">{{ $monthlySummary['days'] }}</div></div></div></div>
                <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-clock"></i><div><small class="text-muted">Çalışma</small><div class="fw-bold">{{ intdiv($monthlySummary['worked_minutes'],60) }} sa.</div></div></div></div>
                <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-umbrella-beach"></i><div><small class="text-muted">İzin Bakiyesi</small><div class="fw-bold">{{ $monthlySummary['annual_leave_balance'] }} gün</div></div></div></div>
                <div class="col-md-3"><div class="pdks-card pdks-stat"><i class="fas fa-business-time"></i><div><small class="text-muted">F. Mesai</small><div class="fw-bold">{{ intdiv($monthlySummary['approved_overtime_minutes'],60) }} sa.</div></div></div></div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="pdks-card p-3">
                        <h6 class="fw-bold mb-3">İzin Talebi</h6>
                        <form method="POST" action="{{ route('pdks.leaves.store') }}" class="row g-2">
                            @csrf
                            <div class="col-12"><select name="leave_type_id" class="form-select form-select-sm" required>@foreach($leaveTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></div>
                            <div class="col-6"><input type="date" name="start_date" class="form-control form-control-sm" required></div>
                            <div class="col-6"><input type="date" name="end_date" class="form-control form-control-sm" required></div>
                            <div class="col-12"><textarea name="reason" rows="2" class="form-control form-control-sm" placeholder="Açıklama"></textarea></div>
                            <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">İzin Talep Et</button></div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pdks-card p-3">
                        <h6 class="fw-bold mb-3">Fazla Mesai Talebi</h6>
                        <form method="POST" action="{{ route('pdks.overtime.store') }}" class="row g-2">
                            @csrf
                            <div class="col-12"><input type="date" name="overtime_date" class="form-control form-control-sm" required></div>
                            <div class="col-6"><input type="time" name="start_time" class="form-control form-control-sm" required></div>
                            <div class="col-6"><input type="time" name="end_time" class="form-control form-control-sm" required></div>
                            <div class="col-12"><textarea name="reason" rows="2" class="form-control form-control-sm" placeholder="Gerekçe" required></textarea></div>
                            <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Fazla Mesai Talep Et</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="pdks-card p-3 mt-3">
                <h6 class="fw-bold mb-3">Vardiya Değişim Talebi</h6>
                <form method="POST" action="{{ route('pdks.shift-change.store') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="shift_assignment_id" value="{{ $todayShift?->id }}">
                    <div class="col-md-4">
                        <select name="requested_shift_type_id" class="form-select form-select-sm">
                            <option value="">Vardiya seçmeden talep</option>
                            @foreach($shiftTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><input type="date" name="requested_shift_date" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><input name="reason" class="form-control form-control-sm" placeholder="Talep gerekçesi" required></div>
                    <div class="col-md-2"><button class="btn btn-sm pdks-btn-main w-100">Talep Et</button></div>
                </form>
            </div>

            <div class="pdks-card p-3 mt-3">
                <h6 class="fw-bold mb-3">Aylık Günlük Kayıtlar</h6>
                <div class="table-responsive">
                    <table class="table pdks-table align-middle mb-0">
                        <thead><tr><th>Tarih</th><th>Giriş</th><th>Çıkış</th><th>Çalışma</th><th>Mola</th><th>Durum</th></tr></thead>
                        <tbody>
                        @forelse($monthlyRecords as $record)
                            <tr>
                                <td>{{ $record->work_date->format('d.m.Y') }}</td>
                                <td>{{ $record->check_in_at?->format('H:i') ?? '—' }}</td>
                                <td>{{ $record->check_out_at?->format('H:i') ?? '—' }}</td>
                                <td>{{ $record->worked_time_label }}</td>
                                <td>{{ $record->break_minutes }} dk.</td>
                                <td><span class="pdks-chip {{ $record->status === 'closed' ? 'pdks-chip-ok' : 'pdks-chip-warn' }}">{{ $record->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Bu ay kayıt yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.pdks-location-form').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        if (!navigator.geolocation || form.dataset.locationReady === '1') return;
        event.preventDefault();
        var status = document.getElementById('pdks-location-status');
        if (status) status.textContent = 'GPS konumu alınıyor...';
        navigator.geolocation.getCurrentPosition(function(position) {
            form.querySelector('.pdks-latitude').value = position.coords.latitude;
            form.querySelector('.pdks-longitude').value = position.coords.longitude;
            form.querySelector('.pdks-accuracy').value = Math.round(position.coords.accuracy || 0);
            form.dataset.locationReady = '1';
            form.submit();
        }, function() {
            if (status) status.textContent = 'GPS alınamadı. Konum iznini açıp tekrar deneyin.';
            alert('PDKS için GPS konumu zorunludur. Lütfen konum izni verin.');
        }, {enableHighAccuracy:true, timeout:12000, maximumAge:0});
    });
});
</script>
@endpush
@endsection
