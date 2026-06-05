@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">Fazla Mesai</h4><div class="pdks-muted">Fazla mesai talepleri, onay ve aylık takip</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="pdks-card">
        <div class="table-responsive">
            <table class="table pdks-table align-middle mb-0">
                <thead><tr><th>Personel</th><th>Tarih</th><th>Saat</th><th>Süre</th><th>Durum</th><th>Gerekçe</th><th>İşlem</th></tr></thead>
                <tbody>
                @forelse($requests as $ot)
                    <tr>
                        <td><strong>{{ $ot->employee->name }}</strong><div class="small text-muted">{{ $ot->employee->department->name ?? '—' }}</div></td>
                        <td>{{ $ot->overtime_date->format('d.m.Y') }}</td>
                        <td>{{ substr($ot->start_time,0,5) }} - {{ substr($ot->end_time,0,5) }}</td>
                        <td>{{ intdiv($ot->duration_minutes,60) }} sa. {{ $ot->duration_minutes % 60 }} dk.</td>
                        <td><span class="pdks-chip {{ $ot->status === 'approved' ? 'pdks-chip-ok' : ($ot->status === 'rejected' ? 'pdks-chip-bad' : 'pdks-chip-warn') }}">{{ $ot->status }}</span></td>
                        <td>{{ $ot->reason }}</td>
                        <td>@if($ot->status === 'pending' && auth()->user()->hasPermission('pdks_overtime','edit'))<form method="POST" action="{{ route('pdks.overtime.status',$ot) }}" class="d-inline">@csrf<input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Onay</button></form> <form method="POST" action="{{ route('pdks.overtime.status',$ot) }}" class="d-inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-danger">Red</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Fazla mesai talebi yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())<div class="p-3 border-top">{{ $requests->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection
