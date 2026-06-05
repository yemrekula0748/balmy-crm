@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">İzin Yönetimi</h4><div class="pdks-muted">İzin talebi, geçmiş ve onay süreci</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="row g-3">
        @if(auth()->user()->hasPermission('pdks_leaves','create'))
        <div class="col-lg-4">
            <div class="pdks-card p-3">
                <h6 class="fw-bold">İzin Tipi Ekle/Güncelle</h6>
                <form method="POST" action="{{ route('pdks.leaves.types.store') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control form-control-sm" placeholder="Yıllık İzin" required></div>
                    <div class="col-6"><input name="code" class="form-control form-control-sm" placeholder="annual" required></div>
                    <div class="col-6"><input type="number" step="0.5" name="default_days" class="form-control form-control-sm" placeholder="Hak gün"></div>
                    <div class="col-6"><input name="color" class="form-control form-control-sm" value="#c19b77"></div>
                    <div class="col-6"><label class="small mt-2"><input type="checkbox" name="is_paid" value="1" checked> Ücretli</label></div>
                    <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Kaydet</button></div>
                </form>
            </div>
            <div class="pdks-card p-3 mt-3">
                <h6 class="fw-bold">Tanımlı İzin Tipleri</h6>
                @foreach($leaveTypes as $type)
                    <div class="d-flex justify-content-between border-bottom py-2"><span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:{{ $type->color }}"></span> {{ $type->name }}</span><small>{{ $type->default_days }} gün</small></div>
                @endforeach
            </div>
        </div>
        @endif
        <div class="{{ auth()->user()->hasPermission('pdks_leaves','create') ? 'col-lg-8' : 'col-12' }}">
            <div class="pdks-card">
                <div class="table-responsive">
                    <table class="table pdks-table align-middle mb-0">
                        <thead><tr><th>Personel</th><th>Tip</th><th>Tarih</th><th>Gün</th><th>Durum</th><th>Açıklama</th><th>İşlem</th></tr></thead>
                        <tbody>
                        @forelse($requests as $leave)
                            <tr>
                                <td><strong>{{ $leave->employee->name }}</strong><div class="small text-muted">{{ $leave->employee->department->name ?? '—' }}</div></td>
                                <td>{{ $leave->leaveType->name ?? '—' }}</td>
                                <td>{{ $leave->start_date->format('d.m.Y') }} - {{ $leave->end_date->format('d.m.Y') }}</td>
                                <td>{{ $leave->total_days }}</td>
                                <td><span class="pdks-chip {{ $leave->status === 'approved' ? 'pdks-chip-ok' : ($leave->status === 'rejected' ? 'pdks-chip-bad' : 'pdks-chip-warn') }}">{{ $leave->status }}</span></td>
                                <td>{{ $leave->reason }}</td>
                                <td>
                                    @if($leave->status === 'pending' && auth()->user()->hasPermission('pdks_leaves','edit'))
                                        <form method="POST" action="{{ route('pdks.leaves.status',$leave) }}" class="d-inline">@csrf<input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Onay</button></form>
                                        <form method="POST" action="{{ route('pdks.leaves.status',$leave) }}" class="d-inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-danger">Red</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">İzin talebi yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($requests->hasPages())<div class="p-3 border-top">{{ $requests->links('pagination::bootstrap-5') }}</div>@endif
            </div>
        </div>
    </div>
</div>
@endsection
