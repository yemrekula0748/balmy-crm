@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4 d-flex justify-content-between flex-wrap gap-2"><div><h4 class="mb-1">PDKS Personeller</h4><div class="pdks-muted">Elektra sicil entegrasyonu ve personel kartları</div></div>@if(auth()->user()->hasPermission('pdks_employees','create'))<form method="POST" action="{{ route('pdks.employees.sync-foresta') }}">@csrf<button class="btn pdks-btn-main"><i class="fas fa-sync me-1"></i>Foresta Elektra Sync</button></form>@endif</div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')

    <div class="pdks-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Arama</label><input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Ad, sicil, kart"></div>
            <div class="col-md-3"><label class="form-label small">Şube</label><select name="branch_id" class="form-select form-select-sm"><option value="">Tümü</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label small">Departman</label><select name="department_id" class="form-select form-select-sm"><option value="">Tümü</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Durum</label><select name="active" class="form-select form-select-sm"><option value="">Tümü</option><option value="1" @selected(request('active')==='1')>Aktif</option><option value="0" @selected(request('active')==='0')>Pasif</option></select></div>
            <div class="col-md-1"><button class="btn btn-sm pdks-btn-main w-100">Ara</button></div>
        </form>
    </div>

    <div class="pdks-card">
        <div class="table-responsive">
            <table class="table pdks-table align-middle mb-0">
                <thead><tr><th>#</th><th>Personel</th><th>Şube</th><th>Departman</th><th>Sicil/Kart</th><th>Kaynak</th><th>Login</th><th>Sync</th><th>Durum</th></tr></thead>
                <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td><strong>{{ $employee->name }}</strong><div class="small text-muted">{{ $employee->title ?? '—' }}</div></td>
                        <td>{{ $employee->branch->name ?? '—' }}</td>
                        <td>{{ $employee->department->name ?? '—' }}</td>
                        <td><code>{{ $employee->registry_no ?? '—' }}</code><div class="small text-muted">{{ $employee->pdks_card_no ?? 'Kart yok' }}</div></td>
                        <td>{{ $employee->source }}</td>
                        <td>{{ $employee->user ? $employee->user->email : 'Bağlı değil' }}</td>
                        <td>{{ $employee->last_synced_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td><span class="pdks-chip {{ $employee->is_active ? 'pdks-chip-ok' : 'pdks-chip-bad' }}">{{ $employee->is_active ? 'Aktif' : 'Pasif' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Personel kaydı yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())<div class="p-3 border-top">{{ $employees->links('pagination::bootstrap-5') }}</div>@endif
    </div>

    <div class="pdks-card p-3 mt-3">
        <h6 class="fw-bold">Son Sync Logları</h6>
        <div class="table-responsive">
            <table class="table pdks-table mb-0"><thead><tr><th>Tarih</th><th>Kaynak</th><th>Durum</th><th>Toplam</th><th>Yeni</th><th>Güncel</th><th>Hata</th></tr></thead><tbody>
            @foreach($syncLogs as $log)
                <tr><td>{{ $log->created_at->format('d.m.Y H:i') }}</td><td>{{ $log->source }}</td><td>{{ $log->status }}</td><td>{{ $log->total_records }}</td><td>{{ $log->created_records }}</td><td>{{ $log->updated_records }}</td><td class="text-danger">{{ $log->error_message }}</td></tr>
            @endforeach
            </tbody></table>
        </div>
    </div>
</div>
@endsection
