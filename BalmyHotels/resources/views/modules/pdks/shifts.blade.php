@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">Vardiya Takvimi</h4><div class="pdks-muted">Toplu vardiya atama, vardiya tipi ve değişim talepleri</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="row g-3">
        @if(auth()->user()->hasPermission('pdks_shifts','create'))
        <div class="col-lg-4">
            <div class="pdks-card p-3 mb-3">
                <h6 class="fw-bold">Vardiya Tipi</h6>
                <form method="POST" action="{{ route('pdks.shifts.types.store') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control form-control-sm" placeholder="A Vardiyası" required></div>
                    <div class="col-4"><input name="code" class="form-control form-control-sm" placeholder="A" required></div>
                    <div class="col-4"><input type="time" name="start_time" class="form-control form-control-sm" required></div>
                    <div class="col-4"><input type="time" name="end_time" class="form-control form-control-sm" required></div>
                    <div class="col-6"><input type="number" name="break_minutes" class="form-control form-control-sm" placeholder="Mola dk."></div>
                    <div class="col-6"><input name="color" class="form-control form-control-sm" value="#c19b77"></div>
                    <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Tip Oluştur</button></div>
                </form>
            </div>
            <div class="pdks-card p-3">
                <h6 class="fw-bold">Toplu Vardiya Ata</h6>
                <form method="POST" action="{{ route('pdks.shifts.assign') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><select name="employee_ids[]" class="form-select form-select-sm" multiple required size="8">@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select></div>
                    <div class="col-12"><select name="shift_type_id" class="form-select form-select-sm" required>@foreach($shiftTypes as $type)<option value="{{ $type->id }}">{{ $type->name }} ({{ substr($type->start_time,0,5) }}-{{ substr($type->end_time,0,5) }})</option>@endforeach</select></div>
                    <div class="col-6"><input type="date" name="date_from" class="form-control form-control-sm" required></div>
                    <div class="col-6"><input type="date" name="date_to" class="form-control form-control-sm" required></div>
                    <div class="col-12"><textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Not"></textarea></div>
                    <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Ata</button></div>
                </form>
            </div>
        </div>
        @endif
        <div class="{{ auth()->user()->hasPermission('pdks_shifts','create') ? 'col-lg-8' : 'col-12' }}">
            <div class="pdks-card p-3 mb-3">
                <form method="GET" class="row g-2 align-items-end"><div class="col-md-4"><label class="small">Başlangıç</label><input type="date" name="date_from" value="{{ $from }}" class="form-control form-control-sm"></div><div class="col-md-4"><label class="small">Bitiş</label><input type="date" name="date_to" value="{{ $to }}" class="form-control form-control-sm"></div><div class="col-md-2"><button class="btn btn-sm pdks-btn-main w-100">Getir</button></div></form>
            </div>
            <div class="pdks-card mb-3">
                <table class="table pdks-table align-middle mb-0"><thead><tr><th>Tarih</th><th>Personel</th><th>Departman</th><th>Vardiya</th><th>Not</th></tr></thead><tbody>
                @forelse($assignments as $assignment)
                    <tr><td>{{ $assignment->shift_date->format('d.m.Y') }}</td><td>{{ $assignment->employee->name }}</td><td>{{ $assignment->employee->department->name ?? '—' }}</td><td>{{ $assignment->shiftType->name ?? '—' }}</td><td>{{ $assignment->notes }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Atama yok.</td></tr>
                @endforelse
                </tbody></table>
                @if($assignments->hasPages())<div class="p-3 border-top">{{ $assignments->links('pagination::bootstrap-5') }}</div>@endif
            </div>
            <div class="pdks-card p-3">
                <h6 class="fw-bold">Vardiya Değişim Talepleri</h6>
                <table class="table pdks-table mb-0"><thead><tr><th>Personel</th><th>Talep</th><th>Durum</th><th>İşlem</th></tr></thead><tbody>
                @foreach($changeRequests as $change)
                    <tr><td>{{ $change->employee->name }}</td><td>{{ $change->requestedShiftType->name ?? 'Tarih değişimi' }}<div class="small text-muted">{{ $change->reason }}</div></td><td>{{ $change->status }}</td><td>@if($change->status==='pending' && auth()->user()->hasPermission('pdks_shifts','edit'))<form method="POST" action="{{ route('pdks.shift-change.status',$change) }}" class="d-inline">@csrf<input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Onay</button></form> <form method="POST" action="{{ route('pdks.shift-change.status',$change) }}" class="d-inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-danger">Red</button></form>@endif</td></tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
