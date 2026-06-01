@extends('layouts.default')
@section('title', 'Servis Operasyonu')

@section('content')
@php
    $activeBranchId = $currentBranchId ?? ($branches->first()->id ?? null);
    $oldBranchMovements = collect(old('branch_movements', []));
    $createBranchId = old('branch_id', $activeBranchId);
    $oldInvolvedBranchIds = collect(old('involved_branch_ids', $createBranchId ? [$createBranchId] : []))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
    $movementPeriods = \App\Models\ShuttleTripBranchMovement::PERIODS;
    $createMovementValues = collect($movementPeriods)->mapWithKeys(function ($periodLabel, $periodKey) use ($branches, $oldBranchMovements, $oldInvolvedBranchIds) {
        return [
            $periodKey => $branches->mapWithKeys(function ($branch) use ($periodKey, $oldBranchMovements, $oldInvolvedBranchIds) {
                $branchId = (int) $branch->id;
                $oldRow = (array) data_get($oldBranchMovements->all(), "$periodKey.$branchId", []);

                return [
                    $branchId => [
                        'included' => in_array($branchId, $oldInvolvedBranchIds, true),
                        'arrival' => (int) data_get($oldRow, 'arrival', 0),
                        'departure' => (int) data_get($oldRow, 'departure', 0),
                        'arrival_time' => data_get($oldRow, 'arrival_time'),
                        'departure_time' => data_get($oldRow, 'departure_time'),
                    ],
                ];
            })->all(),
        ];
    })->all();
@endphp

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Operasyonu</h4>
                <span>{{ $date->format('d.m.Y') }} - Plaka bazli geldi / indi / cikti / bindi takibi</span>
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
            <div class="card border-0" style="background:linear-gradient(135deg,#c19b77 0%,#a97d57 100%);border-radius:12px">
                <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-shuttle-van text-white"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold fs-5">Ortak Servis Hareketi</div>
                            <div style="color:rgba(255,255,255,0.55);font-size:.82rem">
                                Ayni plaka altinda Beach ve Foresta ayri saat ve kisi kaydi tutar
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="GET" action="{{ route('shuttle.operations.index') }}" class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                            <select name="branch_id" class="form-select form-select-sm"
                                    style="min-width:170px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                    onchange="this.form.submit()">
                                <option value="" style="color:#333;background:#fff">- Benim Otellerim -</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($currentBranchId == $branch->id) style="color:#333;background:#fff">
                                        {{ $branch->name }}
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
                                    style="background:#fff;color:#8f6d4f;border:none;border-radius:7px"
                                    data-bs-toggle="modal" data-bs-target="#addTripModal">
                                <i class="fas fa-plus me-1"></i> Hareket Ekle
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c19b77;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Bu Otelde Inen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalIncoming }}</div>
                    <div class="small text-muted mt-1">Secili otel icin gunluk toplam inen personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #2e7d52;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Bu Otelden Binen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalOutgoing }}</div>
                    <div class="small text-muted mt-1">Secili otel icin gunluk toplam binen personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #7a5c3d;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Gorunen Servis</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalTrips }}</div>
                    <div class="small text-muted mt-1">Bu otelin dahil oldugu gunluk plaka hareketi</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
        <div class="card-header border-0 d-flex align-items-center justify-content-between px-4 py-3"
             style="background:linear-gradient(135deg,#c19b77 0%,#a97d57 100%)">
            <span class="text-white fw-semibold">{{ $date->format('d.m.Y') }} Hareket Listesi</span>
            @if($activeBranchId)
                <span style="background:rgba(255,255,255,0.12);color:#fff;font-size:.75rem;padding:3px 10px;border-radius:20px">
                    {{ optional($branches->firstWhere('id', $activeBranchId))->name ?? 'Secili Otel' }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($trips->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-route fa-2x mb-3 d-block"></i>
                    Bu tarih icin servis hareketi bulunmuyor.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 border-0" style="background:#c19b77;color:#fff">Vardiya</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Arac / Plaka</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Guzergah</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Otel Hareketleri</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Toplam</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Durum / Not</th>
                                <th class="pe-4 py-3 border-0 text-end" style="background:#c19b77;color:#fff">Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastShift = null; @endphp
                            @foreach($trips as $trip)
                                @php
                                    $periodMatrix = $trip->branchMovements
                                        ->groupBy(fn ($movement) => $movement->movement_period ?? \App\Models\ShuttleTripBranchMovement::DEFAULT_PERIOD)
                                        ->map(function ($periodItems) {
                                            return $periodItems->groupBy('branch_id')->map(function ($items) {
                                                $arrivalMovement = $items->firstWhere('movement_type', 'arrival');
                                                $departureMovement = $items->firstWhere('movement_type', 'departure');

                                                return [
                                                    'arrival' => (int) optional($arrivalMovement)->headcount,
                                                    'departure' => (int) optional($departureMovement)->headcount,
                                                    'arrival_time' => optional($arrivalMovement)->movement_time ? substr($arrivalMovement->movement_time, 0, 5) : null,
                                                    'departure_time' => optional($departureMovement)->movement_time ? substr($departureMovement->movement_time, 0, 5) : null,
                                                ];
                                            });
                                        });
                                    $activeBranchPeriodMatrix = $activeBranchId
                                        ? collect($movementPeriods)->mapWithKeys(fn ($label, $periodKey) => [
                                            $periodKey => (array) data_get($periodMatrix, "$periodKey.$activeBranchId", [
                                                'arrival' => 0,
                                                'departure' => 0,
                                                'arrival_time' => null,
                                                'departure_time' => null,
                                            ]),
                                        ])->all()
                                        : [];
                                    $isSuperAdmin = auth()->user()->isSuperAdmin();
                                    $editContextBranchId = $activeBranchId ?: (int) $trip->branch_id;
                                    $canOwnerEdit = auth()->user()->hasPermission('shuttle_operations', 'edit')
                                        && ($isSuperAdmin || ($activeBranchId && (int) $trip->branch_id === (int) $activeBranchId));
                                    $canBranchProcess = $activeBranchId && auth()->user()->hasPermission('shuttle_operations', 'index');
                                    $branchProcessBranchName = optional($allBranches->firstWhere('id', $activeBranchId))->name ?? '';
                                @endphp

                                @if($lastShift !== $trip->shift)
                                    <tr>
                                        <td colspan="7" class="py-1 ps-4" style="background:#fbf6ef;border-top:2px solid #eadcc9">
                                            <small class="fw-bold text-uppercase" style="color:#8f6d4f;letter-spacing:.5px;font-size:.7rem">
                                                <i class="fas fa-clock me-1 opacity-60"></i>{{ $trip->shift }}
                                            </small>
                                        </td>
                                    </tr>
                                    @php $lastShift = $trip->shift; @endphp
                                @endif

                                <tr>
                                    <td class="ps-4">
                                        <span style="background:#fbf6ef;color:#8f6d4f;font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px;">
                                            {{ $trip->shift }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $trip->vehicle->name ?? '-' }}</div>
                                        @if($trip->vehicle?->plate)
                                            <span style="background:#8f6d4f;color:#fff;font-family:'Courier New',monospace;font-size:.7rem;font-weight:700;padding:1px 7px;border-radius:4px;">
                                                {{ $trip->vehicle->plate }}
                                            </span>
                                        @endif
                                        <div class="small text-muted mt-1">Kaydi acan: {{ $trip->branch->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $trip->route->name ?? 'Guzergah belirtilmedi' }}</div>
                                        <div class="small text-muted mt-1">Tarih: {{ $trip->trip_date->format('d.m.Y') }}</div>
                                    </td>
                                    <td style="min-width:320px">
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($movementPeriods as $periodKey => $periodLabel)
                                                @php $periodRows = collect($periodMatrix->get($periodKey, [])); @endphp
                                                @continue($periodRows->isEmpty())
                                                <div class="rounded-3 p-2" style="background:#fbf6ef;border:1px solid #eadcc9">
                                                    <div class="small fw-bold mb-2" style="color:#8f6d4f">{{ $periodLabel }}</div>
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($periodRows as $branchId => $counts)
                                                            @php
                                                                $movementBranch = $allBranches->firstWhere('id', (int) $branchId);
                                                                $isActiveRow = $activeBranchId && (int) $branchId === (int) $activeBranchId;
                                                            @endphp
                                                            <div class="rounded-3 p-2"
                                                                 style="background:{{ $isActiveRow ? '#eef6f2' : '#fff' }};border:1px solid {{ $isActiveRow ? '#cfe3d8' : '#eadcc9' }}">
                                                                <div class="small fw-semibold text-dark mb-1">{{ $movementBranch->name ?? '-' }}</div>
                                                                <div class="small text-muted d-flex gap-3 flex-wrap">
                                                                    <span><strong>Geldi:</strong> {{ $counts['arrival_time'] ?: '-' }}</span>
                                                                    <span><strong>Indi:</strong> {{ $counts['arrival'] }}</span>
                                                                </div>
                                                                <div class="small text-muted d-flex gap-3 flex-wrap mt-1">
                                                                    <span><strong>Cikti:</strong> {{ $counts['departure_time'] ?: '-' }}</span>
                                                                    <span><strong>Bindi:</strong> {{ $counts['departure'] }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">Ilk Gelis</div>
                                        <div class="fw-semibold text-dark">{{ $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : '-' }}</div>
                                        <div class="small text-muted mt-2">Toplam Indi / Bindi</div>
                                        <div class="fw-semibold text-dark">{{ $trip->arrival_count }} / {{ $trip->departure_count }}</div>
                                        <div class="small text-muted mt-2">Son Cikis</div>
                                        <div class="fw-semibold text-dark">{{ $trip->departure_time ? substr($trip->departure_time, 0, 5) : '-' }}</div>
                                    </td>
                                    <td style="max-width:250px">
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if($canOwnerEdit)
                                                <span class="badge" style="background:#fbf6ef;color:#8f6d4f;border:1px solid #eadcc9">Kaydi duzenleyebilirsin</span>
                                            @elseif($canBranchProcess)
                                                <span class="badge" style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8">Kendi otelini isleyebilirsin</span>
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
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                                            @if($canOwnerEdit)
                                                <a href="{{ route('shuttle.operations.edit', ['operation' => $trip, 'branch_id' => $editContextBranchId]) }}"
                                                   class="btn btn-sm"
                                                   style="background:#fbf6ef;color:#8f6d4f;border:1px solid #eadcc9;font-size:.78rem">
                                                    <i class="fas fa-edit"></i> Duzenle
                                                </a>
                                            @endif
                                            @if($canBranchProcess)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm"
                                                    style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8;font-size:.78rem"
                                                    data-trip-id="{{ (int) $trip->id }}"
                                                    data-context-branch-id="{{ (int) ($activeBranchId ?? 0) }}"
                                                    data-branch-name="{{ $branchProcessBranchName }}"
                                                    data-period-movements='@json($activeBranchPeriodMatrix)'
                                                    onclick="openBranchProcessModalFromButton(this)"
                                                >
                                                    <i class="fas fa-people-arrows me-1"></i> Kendi Otelini Isle
                                                </button>
                                            @endif
                                            @if($canOwnerEdit && auth()->user()->hasPermission('shuttle_operations', 'delete'))
                                                <form action="{{ route('shuttle.operations.destroy', $trip) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Bu servis hareketini silmek istiyor musunuz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm"
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#c19b77,#a97d57)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-plus me-2"></i>Yeni Servis Hareketi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shuttle.operations.store') }}" method="POST" id="createTripForm">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kaydi Acan Otel <span class="text-danger">*</span></label>
                                <select name="branch_id" id="createTripBranchId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected(old('branch_id', $activeBranchId) == $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="involved_branch_ids[]" id="createInvolvedBranchId" value="{{ old('branch_id', $activeBranchId) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Arac / Plaka <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id" id="createTripVehicleId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($vehicles as $vehicle)
                                        <option
                                            value="{{ $vehicle->id }}"
                                            data-route-ids="{{ $vehicle->routes->pluck('id')->implode(',') }}"
                                            @selected(old('shuttle_vehicle_id') == $vehicle->id)
                                        >
                                            {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - {{ $vehicle->branch->name ?? 'Sube yok' }} - Kap: {{ $vehicle->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Guzergah</label>
                                <select name="route_id" id="createTripRouteId" class="form-select">
                                    <option value="">- Arac secildikten sonra listelenir -</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}" @selected(old('route_id') == $route->id)>
                                            {{ $route->name }} - {{ $route->branch->name ?? 'Sube yok' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1" id="createTripRouteHelp">Yalnizca secilen aracin gorevli oldugu guzergahlar listelenir.</div>
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
                                <div id="createBranchMovementScope">
                                    @include('modules.shuttle.operations._branch_movements', [
                                        'branches' => $branches,
                                        'movementValues' => $createMovementValues,
                                        'editableBranchIds' => auth()->user()->visibleShuttleBranchIds(),
                                        'selectableBranchIds' => [],
                                        'showInclude' => false,
                                        'title' => 'Kendi Otel Hareketin',
                                        'description' => 'Yeni hareket eklerken sadece secili otelin gunduz ve aksam geldi / indi / cikti / bindi satirlari gorunur. Diger otel kendi ekranindan isler.',
                                        'theme' => 'info',
                                    ])
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
                        <button type="submit" class="btn fw-semibold px-4" style="background:#c19b77;color:#fff;border-radius:8px">
                            <i class="fas fa-save me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if(auth()->user()->hasPermission('shuttle_operations', 'index'))
    <div class="modal fade" id="branchProcessModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#2e7d52,#3d7a5e)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-people-arrows me-2"></i>Kendi Otel Hareketini Isle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="branchProcessForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="context_branch_id" id="branchProcessContextBranchId" value="{{ old('context_branch_id', $activeBranchId) }}">
                    <input type="hidden" name="_branch_process_trip_id" id="branchProcessTripId" value="{{ old('_branch_process_trip_id') }}">
                    <div class="modal-body px-4 py-3">
                        <div class="rounded-3 p-3 mb-3" style="background:#fbf8f5;border:1px solid #eadcc9">
                            <div class="small text-uppercase fw-semibold text-muted mb-1">Islem Yapilan Otel</div>
                            <div class="fw-semibold text-dark" id="branchProcessBranchName">-</div>
                            <div class="small text-muted mt-1">Yalnizca secilen donem icin bu otelin geldi / indi / cikti / bindi bilgisi kaydedilir.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Hareket Donemi <span class="text-danger">*</span></label>
                                <select name="movement_period" id="branchProcessMovementPeriod" class="form-select" required>
                                    @foreach($movementPeriods as $periodKey => $periodLabel)
                                        <option value="{{ $periodKey }}">{{ $periodLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Geldi Saati</label>
                                <input type="time" name="arrival_time" id="branchProcessArrivalTime" class="form-control" data-auto-time-picker="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Indi <span class="text-danger">*</span></label>
                                <input type="number" name="arrival_count" id="branchProcessArrivalCount" class="form-control" min="0" max="500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Cikti Saati</label>
                                <input type="time" name="departure_time" id="branchProcessDepartureTime" class="form-control" data-auto-time-picker="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Bindi <span class="text-danger">*</span></label>
                                <input type="number" name="departure_count" id="branchProcessDepartureCount" class="form-control" min="0" max="500" required>
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

function wireTripForm(vehicleSelector, routeSelector, flagsSelector, helpSelector) {
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
    const flagsBox = document.querySelector(flagsSelector);
    const helpBox = document.querySelector(helpSelector);

    if (!vehicleSelect || !routeSelect) {
        return;
    }

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
                helpBox.textContent = 'Yalnizca secilen aracin gorevli oldugu guzergahlar listelenir.';
            } else if (allowedRouteIds.size === 0) {
                helpBox.textContent = 'Bu araca henuz guzergah atamasi yapilmamis.';
            } else {
                helpBox.textContent = 'Yalnizca secilen aracin gorevli oldugu guzergahlar gorunuyor.';
            }
        }
    };

    vehicleSelect.addEventListener('change', syncRoutes);
    syncRoutes();
}

function wireBranchMovementIncludes(scope = document) {
    scope.querySelectorAll('[data-branch-include]').forEach((checkbox) => {
        const syncRow = () => {
            const branchId = checkbox.dataset.branchInclude;
            const rows = scope.querySelectorAll(`[data-branch-row="${branchId}"]`);
            if (!rows.length) {
                return;
            }

            rows.forEach((row) => {
                row.querySelectorAll('input[data-branch-id]').forEach((input) => {
                    if (input.hasAttribute('readonly')) {
                        return;
                    }

                    input.disabled = !checkbox.checked;
                    if (!checkbox.checked) {
                        input.value = '';
                    }
                });
            });
        };

        checkbox.addEventListener('change', syncRow);
        syncRow();
    });
}

function wireCreateBranchMovementScope() {
    const branchSelect = document.getElementById('createTripBranchId');
    const hiddenBranchInput = document.getElementById('createInvolvedBranchId');
    const scope = document.getElementById('createBranchMovementScope');

    if (!branchSelect || !hiddenBranchInput || !scope) {
        return;
    }

    const syncRows = () => {
        const selectedBranchId = branchSelect.value || '';
        hiddenBranchInput.value = selectedBranchId;

        scope.querySelectorAll('[data-branch-row]').forEach((row) => {
            const isSelectedBranch = row.dataset.branchRow === selectedBranchId;
            row.classList.toggle('d-none', !isSelectedBranch);

            row.querySelectorAll('input[data-branch-id]').forEach((input) => {
                input.disabled = !isSelectedBranch;

                if (!isSelectedBranch) {
                    input.value = '';
                }
            });
        });
    };

    branchSelect.addEventListener('change', syncRows);
    syncRows();
}

function openBranchProcessModal(payload) {
    const modalElement = document.getElementById('branchProcessModal');
    if (!modalElement) {
        return;
    }

    const baseUrl = '{{ url("servis-takip/operasyon") }}';
    document.getElementById('branchProcessForm').action = baseUrl + '/' + payload.tripId + '/donus';
    document.getElementById('branchProcessTripId').value = payload.tripId || '';
    document.getElementById('branchProcessContextBranchId').value = payload.contextBranchId || '';
    document.getElementById('branchProcessBranchName').textContent = payload.branchName || '-';

    const periodSelect = document.getElementById('branchProcessMovementPeriod');
    const periodMovements = payload.periodMovements || {};
    const fillPeriodFields = () => {
        const selectedPeriod = periodSelect?.value || 'day';
        const values = periodMovements[selectedPeriod] || {};

        document.getElementById('branchProcessArrivalTime').value = values.arrival_time || payload.arrivalTime || '';
        document.getElementById('branchProcessArrivalCount').value = Number(values.arrival || payload.arrivalCount || 0);
        document.getElementById('branchProcessDepartureTime').value = values.departure_time || payload.departureTime || '';
        document.getElementById('branchProcessDepartureCount').value = Number(values.departure || payload.departureCount || 0);
    };

    if (periodSelect) {
        periodSelect.value = payload.movementPeriod || 'day';
        periodSelect.onchange = fillPeriodFields;
    }
    fillPeriodFields();

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function openBranchProcessModalFromButton(button) {
    if (!button) {
        return;
    }

    openBranchProcessModal({
        tripId: Number(button.dataset.tripId || 0),
        contextBranchId: Number(button.dataset.contextBranchId || 0),
        branchName: button.dataset.branchName || '',
        periodMovements: (() => {
            try {
                return JSON.parse(button.dataset.periodMovements || '{}');
            } catch (error) {
                return {};
            }
        })(),
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireTripForm(
        '#createTripVehicleId',
        '#createTripRouteId',
        '#createTripFlagsBox',
        '#createTripRouteHelp'
    );
    wireBranchMovementIncludes(document);
    wireCreateBranchMovementScope();

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'create') && !old('_branch_process_trip_id'))
        const addTripModal = document.getElementById('addTripModal');
        if (addTripModal) {
            new bootstrap.Modal(addTripModal).show();
        }
    @endif

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'index') && old('_branch_process_trip_id'))
        openBranchProcessModal({
            tripId: @json(old('_branch_process_trip_id')),
            contextBranchId: @json((int) old('context_branch_id', $activeBranchId)),
            branchName: @json(optional($branches->firstWhere('id', $activeBranchId))->name ?? ''),
            movementPeriod: @json(old('movement_period', 'day')),
            arrivalTime: @json(old('arrival_time')),
            arrivalCount: @json((int) old('arrival_count', 0)),
            departureTime: @json(old('departure_time')),
            departureCount: @json((int) old('departure_count', 0)),
        });
    @endif
});
</script>
@endpush
@endsection
