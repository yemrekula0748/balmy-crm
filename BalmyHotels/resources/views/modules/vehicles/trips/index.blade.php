@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Araç Görevleri</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Araç Görevleri</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Üst butonlar --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        @if(Auth::user()->hasPermission('vehicle_trips', 'create'))
        <a href="{{ route('vehicle-trips.create') }}" class="btn btn-primary">
            <i class="fa fa-play me-1"></i> Yeni Görev Başlat
        </a>
        @endif
        <a href="{{ route('vehicle-trips.my') }}" class="btn btn-outline-success">
            <i class="fa fa-map-marker me-1"></i> Aktif Görevim
        </a>
        @if(Auth::user()->hasPermission('vehicle_trip_control', 'index'))
        <a href="{{ route('vehicle-trips.control') }}" class="btn btn-outline-info">
            <i class="fa fa-map me-1"></i> Görev Kontrol Haritası
        </a>
        @endif
    </div>

    {{-- Filtre --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durumlar</option>
                        <option value="active" @selected(request('status') === 'active')>Devam Ediyor</option>
                        <option value="completed" @selected(request('status') === 'completed')>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <select name="vehicle_id" class="form-select form-select-sm">
                        <option value="">Tüm Araçlar</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" @selected(request('vehicle_id') == $v->id)>{{ $v->plate }} — {{ $v->brand }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fa fa-search me-1"></i>Filtrele</button>
                    <a href="{{ route('vehicle-trips.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Araç</th>
                            <th>Sürücü</th>
                            <th>Gidilen Yer</th>
                            <th>Başlangıç KM</th>
                            <th>Bitiş KM</th>
                            <th>Toplam</th>
                            <th>Başlangıç</th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                        <tr>
                            <td class="text-muted small">#{{ $trip->id }}</td>
                            <td>
                                <strong>{{ $trip->vehicle->plate ?? '-' }}</strong>
                                <div class="text-muted small">{{ $trip->vehicle->brand ?? '' }} {{ $trip->vehicle->model ?? '' }}</div>
                            </td>
                            <td>{{ $trip->user->name ?? '-' }}</td>
                            <td>{{ $trip->destination }}</td>
                            <td>{{ number_format($trip->start_km) }} km</td>
                            <td>{{ $trip->end_km ? number_format($trip->end_km).' km' : '—' }}</td>
                            <td>
                                @if($trip->totalKm() !== null)
                                    <span class="badge bg-light text-dark border">{{ number_format($trip->totalKm()) }} km</span>
                                @else —
                                @endif
                            </td>
                            <td class="small">{{ $trip->started_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($trip->status === 'active')
                                    <span class="badge bg-success"><i class="fa fa-circle fa-xs me-1"></i>Devam Ediyor</span>
                                @else
                                    <span class="badge bg-secondary">Tamamlandı</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('vehicle-trips.show', $trip) }}" class="btn btn-xs btn-outline-primary" title="Detay">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->hasPermission('vehicle_trips', 'delete'))
                                    <form action="{{ route('vehicle-trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Bu görevi silmek istediğinizden emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger" title="Sil"><i class="fa fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Görev kaydı bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($trips->hasPages())
            <div class="p-3">{{ $trips->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
