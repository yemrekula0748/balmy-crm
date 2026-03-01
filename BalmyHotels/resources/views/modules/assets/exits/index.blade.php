@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Eşya Çıkış Formları</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Çıkış Formları</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    {{-- İSTATİSTİKLER --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-warning h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Onay Bekleyen</div>
                    <div class="fw-bold fs-3 text-warning">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-success h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Dışarıda</div>
                    <div class="fw-bold fs-3 text-success">{{ $stats['approved'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-danger h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Gecikmiş İade</div>
                    <div class="fw-bold fs-3 text-danger">{{ $stats['overdue'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-secondary h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">İade Edildi</div>
                    <div class="fw-bold fs-3">{{ $stats['returned'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FİLTRE --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('asset-exits.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Ad, oda, demirbaş kodu..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach(\App\Models\AssetExit::STATUSES as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="taker_type" class="form-select form-select-sm">
                        <option value="">Tüm Tipler</option>
                        @foreach(\App\Models\AssetExit::TAKER_TYPES as $val => $label)
                            <option value="{{ $val }}" @selected(request('taker_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filtrele</button>
                    <a href="{{ route('asset-exits.index') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
                    <a href="{{ route('asset-exits.create') }}" class="btn btn-sm ms-auto" style="background:#c19b77;color:#fff">+ Form Oluştur</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Demirbaş</th>
                            <th>Alan Kişi</th>
                            <th>Sebebi</th>
                            <th>Çıkış Tarihi</th>
                            <th>İade Tarihi</th>
                            <th>Durum</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exits as $exit)
                            <tr @class(['table-warning' => $exit->isOverdue()])>
                                <td class="text-muted small">{{ $exit->id }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $exit->asset->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $exit->asset->asset_code ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ $exit->takerName() }}</div>
                                    <span class="badge bg-light text-dark border" style="font-size:10px">{{ \App\Models\AssetExit::TAKER_TYPES[$exit->taker_type] }}</span>
                                </td>
                                <td class="text-muted small">{{ \Str::limit($exit->purpose, 40) }}</td>
                                <td class="small">{{ $exit->taken_at->format('d.m.Y H:i') }}</td>
                                <td class="small">
                                    @if($exit->returned_at)
                                        <span class="text-success">{{ $exit->returned_at->format('d.m.Y H:i') }}</span>
                                    @elseif($exit->expected_return_at)
                                        <span class="{{ $exit->isOverdue() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                            {{ $exit->expected_return_at->format('d.m.Y H:i') }}
                                            @if($exit->isOverdue()) ⚠ @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\AssetExit::STATUS_COLORS[$exit->status] }}">
                                        {{ \App\Models\AssetExit::STATUSES[$exit->status] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($exit->status === 'pending')
                                        <form action="{{ route('asset-exits.approve', $exit) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-success me-1">Onayla</button>
                                        </form>
                                    @endif
                                    @if($exit->status === 'approved' && !$exit->returned_at)
                                        <form action="{{ route('asset-exits.return', $exit) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-info me-1">İade Al</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('asset-exits.show', $exit) }}" class="btn btn-xs btn-outline-secondary">Detay</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($exits->hasPages())
            <div class="card-footer">{{ $exits->links() }}</div>
        @endif
    </div>
</div>
@endsection
