@extends('layouts.default')
@section('title', 'Servis Operasyonu')

@section('content')
@php
    $activeBranchId = $currentBranchId ?? ($branches->first()->id ?? null);
@endphp

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Operasyonu</h4>
                <span>{{ $date->format('d.m.Y') }} - Kaynak ve hedef otel takibi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item">Servis Takip</li>
                <li class="breadcrumb-item active">Operasyon</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%);border-radius:12px">
                <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-shuttle-van text-white"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold fs-5">Cift Tarafli Sefer Takibi</div>
                            <div style="color:rgba(255,255,255,0.55);font-size:.82rem">
                                {{ $date->format('d.m.Y') }} - Her otel yalnizca kendi tarafini isler
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="GET" action="{{ route('shuttle.operations.index') }}"
                              class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                            <select name="branch_id" class="form-select form-select-sm"
                                    style="min-width:170px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                    onchange="this.form.submit()">
                                <option value="" style="color:#333;background:#fff">- Benim Tesislerim -</option>
                                @foreach($branches as $branchOption)
                                    <option value="{{ $branchOption->id }}" @selected($currentBranchId == $branchOption->id) style="color:#333;background:#fff">
                                        {{ $branchOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" onclick="changeDate(-1)" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;width:32px">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <input type="date" name="date" value="{{ $date->toDateString() }}"
                                       class="form-control form-control-sm text-center"
                                       style="width:135px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                       onchange="this.form.submit()">
                                <button type="button" onclick="changeDate(1)" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;width:32px">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </form>
                        @if(auth()->user()->hasPermission('shuttle_operations', 'create'))
                            <button type="button" class="btn btn-sm fw-semibold px-3"
                                    style="background:#fff;color:#1e2d3d;border:none;border-radius:7px"
                                    data-bs-toggle="modal" data-bs-target="#addTripModal">
                                <i class="fas fa-plus me-1"></i> Sefer Baslat
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #2a5298;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Bu Tarafta Inen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#1e2d3d">{{ $totalIncoming }}</div>
                    <div class="small text-muted mt-1">Secili otelin geldiginde karşıladığı personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #2e7d52;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Bu Tarafta Binen/Cikan</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#1e2d3d">{{ $totalOutgoing }}</div>
                    <div class="small text-muted mt-1">Secili otelin servise bindirdigi personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #7a5c3d;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Gorunen Sefer</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#1e2d3d">{{ $totalTrips }}</div>
                    <div class="small text-muted mt-1">Kaynak ya da hedef tarafinda oldugun kayitlar</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
        <div class="card-header border-0 d-flex align-items-center justify-content-between px-4 py-3"
             style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%)">
            <span class="text-white fw-semibold">{{ $date->format('d.m.Y') }} Sefer Listesi</span>
            @if($activeBranchId)
                <span style="background:rgba(255,255,255,0.12);color:#fff;font-size:.75rem;padding:3px 10px;border-radius:20px">
                    {{ optional($branches->firstWhere('id', $activeBranchId))->name ?? 'Secili Tesis' }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($trips->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-route fa-2x mb-3 d-block"></i>
                    Bu tarih ve otel icin sefer kaydi bulunmuyor.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 border-0" style="background:#1e2d3d;color:#fff">Vardiya</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Guzergah</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Arac</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Kaynak Otel</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Hedef Otel</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Durum / Not</th>
                                <th class="py-3 border-0" style="background:#1e2d3d;color:#fff">Ekleyen</th>
                                <th class="pe-4 py-3 border-0 text-end" style="background:#1e2d3d;color:#fff">Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastShift = null; @endphp
                            @foreach($trips as $trip)
                                @php
                                    $contextRole = $activeBranchId
                                        ? ($trip->isOriginForBranch((int) $activeBranchId) ? 'origin' : ($trip->isDestinationForBranch((int) $activeBranchId) ? 'destination' : 'observer'))
                                        : 'observer';
                                    $destinationPayloadJson = e(json_encode([
                                        'tripId' => (int) $trip->id,
                                        'contextBranchId' => (int) ($activeBranchId ?? $trip->destination_branch_id ?? 0),
                                        'sourceBranch' => $trip->branch->name ?? '-',
                                        'destinationBranch' => $trip->destinationBranch->name ?? '-',
                                        'sourceCount' => (int) $trip->origin_departure_count,
                                        'sourceTime' => $trip->origin_departure_time ? substr($trip->origin_departure_time, 0, 5) : '',
                                        'arrivalTime' => $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : '',
                                        'arrivalCount' => (int) $trip->arrival_count,
                                        'departureTime' => $trip->departure_time ? substr($trip->departure_time, 0, 5) : '',
                                        'departureCount' => (int) $trip->departure_count,
                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                                @endphp

                                @if($lastShift !== $trip->shift)
                                    <tr>
                                        <td colspan="8" class="py-1 ps-4" style="background:#f5f7fa;border-top:2px solid #e2e8f0">
                                            <small class="fw-bold text-uppercase" style="color:#1e2d3d;letter-spacing:.5px;font-size:.7rem">
                                                <i class="fas fa-clock me-1 opacity-60"></i>{{ $trip->shift }}
                                            </small>
                                        </td>
                                    </tr>
                                    @php $lastShift = $trip->shift; @endphp
                                @endif

                                <tr>
                                    <td class="ps-4">
                                        <span style="background:#eef2f7;color:#2a4a6b;font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px;">
                                            {{ $trip->shift }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $trip->branch->name ?? '-' }} <i class="fas fa-arrow-right mx-1 text-muted"></i> {{ $trip->destinationBranch->name ?? '-' }}</div>
                                        <div class="small text-muted">{{ $trip->route->name ?? 'Guzergah belirtilmedi' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $trip->vehicle->name ?? '-' }}</div>
                                        @if($trip->vehicle?->plate)
                                            <span style="background:#1e2d3d;color:#fff;font-family:'Courier New',monospace;font-size:.7rem;font-weight:700;padding:1px 7px;border-radius:4px;">
                                                {{ $trip->vehicle->plate }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="rounded-3 p-2" style="background:#f6f0ea;border:1px solid #eadcc9">
                                            <div class="small text-uppercase fw-semibold text-muted mb-1">{{ $trip->branch->name ?? 'Kaynak' }}</div>
                                            <div class="small">
                                                <span class="fw-semibold text-dark">Cikan:</span>
                                                <span class="fw-bold" style="color:#7a5c3d">{{ $trip->origin_departure_count }}</span>
                                            </div>
                                            <div class="small text-muted">
                                                Saat: {{ $trip->origin_departure_time ? substr($trip->origin_departure_time, 0, 5) : '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rounded-3 p-2" style="background:#eef6f2;border:1px solid #cfe3d8">
                                            <div class="small text-uppercase fw-semibold text-muted mb-1">{{ $trip->destinationBranch->name ?? 'Hedef' }}</div>
                                            <div class="small">
                                                <span class="fw-semibold text-dark">Indi:</span>
                                                <span class="fw-bold text-primary">{{ $trip->arrival_count }}</span>
                                            </div>
                                            <div class="small text-muted mb-1">
                                                Saat: {{ $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : '-' }}
                                            </div>
                                            <div class="small">
                                                <span class="fw-semibold text-dark">Bindi:</span>
                                                <span class="fw-bold text-success">{{ $trip->departure_count }}</span>
                                            </div>
                                            <div class="small text-muted">
                                                Saat: {{ $trip->departure_time ? substr($trip->departure_time, 0, 5) : '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width:260px">
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if($trip->isOriginForBranch((int) $activeBranchId))
                                                <span class="badge" style="background:#eef3f9;color:#2a5298;border:1px solid #d0ddf5">Bu kayit burada baslatildi</span>
                                            @elseif($trip->isDestinationForBranch((int) $activeBranchId))
                                                <span class="badge" style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8">Bu kaydi bu taraf isler</span>
                                            @endif
                                            @if($trip->arrived_with_different_vehicle)
                                                <span class="badge" style="background:#fff1f1;color:#b03a3a;border:1px solid #f0d0d0">Farkli arac</span>
                                            @endif
                                            @if($trip->is_transfer)
                                                <span class="badge" style="background:#fff7e7;color:#9b6a11;border:1px solid #f1ddb2">Aktarim</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">{{ $trip->notes ? \Illuminate\Support\Str::limit($trip->notes, 70) : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $trip->creator->name ?? '-' }}</div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if($contextRole === 'origin' && auth()->user()->hasPermission('shuttle_operations', 'edit'))
                                                <a href="{{ route('shuttle.operations.edit', ['operation' => $trip, 'branch_id' => $activeBranchId]) }}"
                                                   class="btn btn-sm"
                                                   style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                                                    <i class="fas fa-edit"></i> Kaynak Duzenle
                                                </a>
                                            @endif
                                            @if($contextRole === 'destination' && auth()->user()->hasPermission('shuttle_operations', 'edit'))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm"
                                                    style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8;font-size:.78rem"
                                                    data-destination-payload="{{ $destinationPayloadJson }}"
                                                    onclick="openDestinationModal(JSON.parse(this.dataset.destinationPayload))"
                                                >
                                                    <i class="fas fa-people-arrows me-1"></i> Indi / Bindi Isle
                                                </button>
                                            @endif
                                            @if(($contextRole === 'origin' || auth()->user()->isSuperAdmin()) && auth()->user()->hasPermission('shuttle_operations', 'delete'))
                                                <form action="{{ route('shuttle.operations.destroy', $trip) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Bu seferi silmek istiyor musunuz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm"
                                                            style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0;font-size:.78rem">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@if(auth()->user()->hasPermission('shuttle_operations', 'create'))
    <div class="modal fade" id="addTripModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#1e2d3d,#2c3e50)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-plus me-2"></i>Yeni Sefer Baslat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shuttle.operations.store') }}" method="POST" id="createTripForm">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kaynak Otel <span class="text-danger">*</span></label>
                                <select name="branch_id" id="createTripBranchId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($branches as $branchOption)
                                        <option value="{{ $branchOption->id }}" @selected(old('branch_id', $activeBranchId) == $branchOption->id)>
                                            {{ $branchOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Hedef Otel <span class="text-danger">*</span></label>
                                <select name="destination_branch_id" id="createTripDestinationBranchId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($destinationBranches as $branchOption)
                                        <option value="{{ $branchOption->id }}" @selected(old('destination_branch_id') == $branchOption->id)>
                                            {{ $branchOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Arac <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id" id="createTripVehicleId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($vehicles as $vehicle)
                                        <option
                                            value="{{ $vehicle->id }}"
                                            data-branch-id="{{ $vehicle->branch_id }}"
                                            data-route-ids="{{ $vehicle->routes->pluck('id')->implode(',') }}"
                                            @selected(old('shuttle_vehicle_id') == $vehicle->id)
                                        >
                                            {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - Kap: {{ $vehicle->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Guzergah</label>
                                <select name="route_id" id="createTripRouteId" class="form-select">
                                    <option value="">- Arac secildikten sonra listelenir -</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}" @selected(old('route_id') == $route->id)>
                                            {{ $route->name }} - {{ $route->branch->name ?? 'Sube yok' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1" id="createTripRouteHelp">Secilen aracin gorevli oldugu guzergahlar listelenir.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Vardiya <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach(\App\Models\ShuttleTrip::SHIFTS as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift') === $shift)>{{ $shift }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Tarih <span class="text-danger">*</span></label>
                                <input type="date" name="trip_date" value="{{ old('trip_date', $date->toDateString()) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kaynaktan Cikis Saati</label>
                                <input type="time" name="origin_departure_time" value="{{ old('origin_departure_time') }}"
                                       class="form-control" data-auto-time-picker="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kaynaktan Cikan Kisi <span class="text-danger">*</span></label>
                                <input type="number" name="origin_departure_count" value="{{ old('origin_departure_count', 0) }}"
                                       min="0" max="500" class="form-control" required>
                                <div class="small text-muted mt-1">Bu sayi hedef otel icin otomatik referans olarak kalir.</div>
                            </div>

                            <div class="col-12">
                                <div id="createTripFlagsBox" class="p-3 rounded {{ old('shuttle_vehicle_id') ? '' : 'd-none' }}"
                                     style="background:#fff4f4;border:1px solid #f0d0d0">
                                    <div class="fw-semibold small mb-2" style="color:#a94442">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Operasyon Istisnalari
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="hidden" name="arrived_with_different_vehicle" value="0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                       name="arrived_with_different_vehicle" id="createDifferentVehicle"
                                                       @checked(old('arrived_with_different_vehicle'))>
                                                <label class="form-check-label fw-semibold" for="createDifferentVehicle">
                                                    Farkli arac ile geldi
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="hidden" name="is_transfer" value="0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                       name="is_transfer" id="createIsTransfer"
                                                       @checked(old('is_transfer'))>
                                                <label class="form-check-label fw-semibold" for="createIsTransfer">
                                                    Aktarim yapildi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Not</label>
                                <textarea name="notes" rows="2" class="form-control" maxlength="500">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Iptal</button>
                        <button type="submit" class="btn fw-semibold px-4" style="background:#1e2d3d;color:#fff;border-radius:8px">
                            <i class="fas fa-save me-1"></i> Seferi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if(auth()->user()->hasPermission('shuttle_operations', 'edit'))
    <div class="modal fade" id="destinationProcessModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#2e7d52,#3d7a5e)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-people-arrows me-2"></i>Hedef Otel Islemi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="destinationProcessForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="context_branch_id" id="destContextBranchId" value="{{ old('context_branch_id', $activeBranchId) }}">
                    <input type="hidden" name="_destination_trip_id" id="destTripId" value="{{ old('_destination_trip_id') }}">
                    <div class="modal-body px-4 py-3">
                        <div class="rounded-3 p-3 mb-3" style="background:#f6f8fb;border:1px solid #e1e7f0">
                            <div class="small text-uppercase fw-semibold text-muted mb-1">Kaynak Bilgisi</div>
                            <div class="fw-semibold text-dark" id="destSourceLabel">-</div>
                            <div class="small text-muted mt-1">
                                Cikan kisi: <span class="fw-semibold text-dark" id="destSourceCount">0</span>
                                <span class="mx-1">|</span>
                                Saat: <span class="fw-semibold text-dark" id="destSourceTime">-</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kac Kisi Indi <span class="text-danger">*</span></label>
                                <input type="number" name="arrival_count" id="destArrivalCount" class="form-control" min="0" max="500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Varis Saati</label>
                                <input type="time" name="arrival_time" id="destArrivalTime" class="form-control" data-auto-time-picker="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kac Kisi Bindi <span class="text-danger">*</span></label>
                                <input type="number" name="departure_count" id="destDepartureCount" class="form-control" min="0" max="500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Cikis Saati</label>
                                <input type="time" name="departure_time" id="destDepartureTime" class="form-control" data-auto-time-picker="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-sm fw-semibold px-4" style="background:#2e7d52;color:#fff;border-radius:8px">
                            <i class="fas fa-save me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
function changeDate(delta) {
    const input = document.querySelector('input[name="date"]');
    const date = new Date(input.value + 'T00:00:00');
    date.setDate(date.getDate() + delta);
    input.value = date.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}

function tryShowPicker(input) {
    if (!input || typeof input.showPicker !== 'function') {
        return;
    }

    try {
        input.showPicker();
    } catch (error) {
        // Desteklenmeyen tarayici sessizce gecsin.
    }
}

function bindAutoTimePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-time-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(branchSelector, destinationSelector, vehicleSelector, routeSelector, flagsSelector, helpSelector) {
    const branchSelect = document.querySelector(branchSelector);
    const destinationSelect = document.querySelector(destinationSelector);
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
    const flagsBox = document.querySelector(flagsSelector);
    const helpBox = document.querySelector(helpSelector);

    if (!branchSelect || !destinationSelect || !vehicleSelect || !routeSelect) {
        return;
    }

    const syncDestinationOptions = () => {
        const sourceBranchId = branchSelect.value;

        Array.from(destinationSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const blocked = sourceBranchId && option.value === sourceBranchId;
            option.hidden = blocked;
            option.disabled = blocked;
        });

        if (destinationSelect.selectedOptions[0] && destinationSelect.selectedOptions[0].disabled) {
            destinationSelect.value = '';
        }
    };

    const syncVehicles = () => {
        const branchId = branchSelect.value;

        Array.from(vehicleSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matches = !branchId || option.dataset.branchId === branchId;
            option.hidden = !matches;
            option.disabled = !matches;
        });

        if (vehicleSelect.selectedOptions[0] && vehicleSelect.selectedOptions[0].disabled) {
            vehicleSelect.value = '';
        }

        syncRoutes();
        syncDestinationOptions();
    };

    const syncRoutes = () => {
        const selectedVehicle = vehicleSelect.selectedOptions[0];
        const vehicleId = selectedVehicle?.value || '';
        const allowedRouteIds = new Set(
            ((selectedVehicle?.dataset.routeIds || '')
                .split(',')
                .map((value) => value.trim())
                .filter(Boolean))
        );

        if (flagsBox) {
            flagsBox.classList.toggle('d-none', !vehicleId);
        }

        Array.from(routeSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const allowed = vehicleId && allowedRouteIds.has(option.value);
            option.hidden = !allowed;
            option.disabled = !allowed;
        });

        if (routeSelect.selectedOptions[0] && routeSelect.selectedOptions[0].disabled) {
            routeSelect.value = '';
        }

        routeSelect.disabled = !vehicleId || allowedRouteIds.size === 0;

        if (helpBox) {
            if (!vehicleId) {
                helpBox.textContent = 'Secilen aracin gorevli oldugu guzergahlar listelenir.';
            } else if (allowedRouteIds.size === 0) {
                helpBox.textContent = 'Bu araca henuz guzergah atamasi yapilmamis.';
            } else {
                helpBox.textContent = 'Yalnizca secilen aracin gorevli oldugu guzergahlar gorunuyor.';
            }
        }
    };

    branchSelect.addEventListener('change', syncVehicles);
    vehicleSelect.addEventListener('change', syncRoutes);

    syncVehicles();
}

function openDestinationModal(payload) {
    const modalElement = document.getElementById('destinationProcessModal');
    if (!modalElement) {
        return;
    }

    const baseUrl = '{{ url("servis-takip/operasyon") }}';
    document.getElementById('destinationProcessForm').action = baseUrl + '/' + payload.tripId + '/donus';
    document.getElementById('destTripId').value = payload.tripId || '';
    document.getElementById('destContextBranchId').value = payload.contextBranchId || '';
    document.getElementById('destSourceLabel').textContent = (payload.sourceBranch || '-') + ' -> ' + (payload.destinationBranch || '-');
    document.getElementById('destSourceCount').textContent = payload.sourceCount || 0;
    document.getElementById('destSourceTime').textContent = payload.sourceTime || '-';
    document.getElementById('destArrivalCount').value = payload.arrivalCount || 0;
    document.getElementById('destArrivalTime').value = payload.arrivalTime || '';
    document.getElementById('destDepartureCount').value = payload.departureCount || 0;
    document.getElementById('destDepartureTime').value = payload.departureTime || '';

    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    modalElement.addEventListener('shown.bs.modal', function handleShown() {
        const input = document.getElementById('destArrivalCount');
        input?.focus();
        modalElement.removeEventListener('shown.bs.modal', handleShown);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireTripForm(
        '#createTripBranchId',
        '#createTripDestinationBranchId',
        '#createTripVehicleId',
        '#createTripRouteId',
        '#createTripFlagsBox',
        '#createTripRouteHelp'
    );

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'create') && !old('_destination_trip_id'))
        const addTripModal = document.getElementById('addTripModal');
        if (addTripModal) {
            new bootstrap.Modal(addTripModal).show();
        }
    @endif

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'edit') && old('_destination_trip_id'))
        openDestinationModal({
            tripId: @json(old('_destination_trip_id')),
            contextBranchId: @json((int) old('context_branch_id', $activeBranchId)),
            sourceBranch: @json('Kayitli Sefer'),
            destinationBranch: @json(optional($branches->firstWhere('id', $activeBranchId))->name ?? ''),
            sourceCount: @json(0),
            sourceTime: @json(''),
            arrivalTime: @json(old('arrival_time', '')),
            arrivalCount: @json((int) old('arrival_count', 0)),
            departureTime: @json(old('departure_time', '')),
            departureCount: @json((int) old('departure_count', 0)),
        });
    @endif
});
</script>
@endpush
@endsection
