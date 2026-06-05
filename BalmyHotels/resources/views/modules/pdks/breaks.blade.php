@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">Mola Tipleri</h4><div class="pdks-muted">Yemek, sigara, dinlenme gibi mola tipleri ve limitleri</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="row g-3">
        @if(auth()->user()->hasPermission('pdks_breaks','create'))
        <div class="col-lg-4">
            <div class="pdks-card p-3">
                <h6 class="fw-bold">Mola Tipi Ekle/Güncelle</h6>
                <form method="POST" action="{{ route('pdks.breaks.store') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control form-control-sm" placeholder="Ad" required></div>
                    <div class="col-6"><input name="code" class="form-control form-control-sm" placeholder="Kod" required></div>
                    <div class="col-6"><input type="number" name="max_minutes" class="form-control form-control-sm" placeholder="Limit dk."></div>
                    <div class="col-6"><input name="color" class="form-control form-control-sm" value="#c19b77"></div>
                    <div class="col-6"><input type="number" name="sort_order" class="form-control form-control-sm" value="0"></div>
                    <div class="col-12"><label class="small"><input type="checkbox" name="is_paid" value="1"> Ücretli mola</label></div>
                    <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Kaydet</button></div>
                </form>
            </div>
        </div>
        @endif
        <div class="{{ auth()->user()->hasPermission('pdks_breaks','create') ? 'col-lg-8' : 'col-12' }}">
            <div class="pdks-card">
                <table class="table pdks-table align-middle mb-0"><thead><tr><th>Ad</th><th>Kod</th><th>Limit</th><th>Ücretli</th><th>Durum</th></tr></thead><tbody>
                @foreach($breakTypes as $type)
                    <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $type->color }}"></span> {{ $type->name }}</td><td>{{ $type->code }}</td><td>{{ $type->max_minutes ?? 'Limitsiz' }}</td><td>{{ $type->is_paid ? 'Evet' : 'Hayır' }}</td><td>{{ $type->is_active ? 'Aktif' : 'Pasif' }}</td></tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
