@extends('layouts.default')
@section('title', 'Sefer Duzenle')

@section('content')
@php
    $visibleBranchIds = collect(auth()->user()->visibleShuttleBranchIds())
        ->map(fn ($id) => (int) $id)
        ->all();

    $movementPeriods = \App\Models\ShuttleTripBranchMovement::PERIODS;
    $existingMatrix = $operation->branchMovements
        ->groupBy(fn ($movement) => $movement->movement_period ?? \App\Models\ShuttleTripBranchMovement::DEFAULT_PERIOD)
        ->map(function ($periodItems) {
            return $periodItems
                ->groupBy('branch_id')
                ->map(function ($items) {
                    $arrivalMovement = $items->firstWhere('movement_type', 'arrival');
                    $departureMovement = $items->firstWhere('movement_type', 'departure');

                    return [
                        'arrival' => (int) optional($arrivalMovement)->headcount,
                        'departure' => (int) optional($departureMovement)->headcount,
                        'arrival_time' => optional($arrivalMovement)->movement_time ? substr($arrivalMovement->movement_time, 0, 5) : null,
                        'departure_time' => optional($departureMovement)->movement_time ? substr($departureMovement->movement_time, 0, 5) : null,
                    ];
                })
                ->toArray();
        })
        ->toArray();

    $existingBranchIds = collect($existingMatrix)
        ->flatMap(fn ($periodRows) => array_keys($periodRows))
        ->map(fn ($id) => (int) $id)
        ->all();

    $defaultIncludedBranchIds = collect(array_merge($existingBranchIds, [(int) $operation->branch_id]))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    $oldBranchMovements = collect(old('branch_movements', []));
    $hasOldInvolvedBranchIds = old('involved_branch_ids') !== null;
    $oldInvolvedBranchIds = collect(old('involved_branch_ids', $defaultIncludedBranchIds))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    $movementValues = collect($movementPeriods)->mapWithKeys(function ($periodLabel, $periodKey) use ($allBranches, $existingMatrix, $oldBranchMovements, $oldInvolvedBranchIds, $defaultIncludedBranchIds, $hasOldInvolvedBranchIds) {
        return [
            $periodKey => $allBranches->mapWithKeys(function ($branch) use ($periodKey, $existingMatrix, $oldBranchMovements, $oldInvolvedBranchIds, $defaultIncludedBranchIds, $hasOldInvolvedBranchIds) {
                $branchId = (int) $branch->id;
                $oldRow = (array) data_get($oldBranchMovements->all(), "$periodKey.$branchId", []);
                $currentRow = (array) data_get($existingMatrix, "$periodKey.$branchId", [
                    'arrival' => 0,
                    'departure' => 0,
                    'arrival_time' => null,
                    'departure_time' => null,
                ]);

                return [
                    $branchId => [
                        'included' => $hasOldInvolvedBranchIds
                            ? in_array($branchId, $oldInvolvedBranchIds, true)
                            : in_array($branchId, $defaultIncludedBranchIds, true),
                        'arrival' => (int) data_get($oldRow, 'arrival', $currentRow['arrival']),
                        'departure' => (int) data_get($oldRow, 'departure', $currentRow['departure']),
                        'arrival_time' => data_get($oldRow, 'arrival_time', $currentRow['arrival_time']),
                        'departure_time' => data_get($oldRow, 'departure_time', $currentRow['departure_time']),
                    ],
                ];
            })->all(),
        ];
    })->all();

    $includedBranchCount = count($defaultIncludedBranchIds);
@endphp

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Sefer Duzenle</h4>
                <span>{{ $operation->trip_date->format('d.m.Y') }} - {{ $operation->shift }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shuttle.operations.index', ['branch_id' => $contextBranchId, 'date' => $operation->trip_date->format('Y-m-d')]) }}">Operasyon</a></li>
                <li class="breadcrumb-item active">Duzenle</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden">
                <div class="card-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#c19b77,#a97d57)">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1 text-white fw-semibold">
                                <i class="fas fa-route me-2"></i>Plaka Hareketi
                            </h5>
                            <div style="color:rgba(255,255,255,0.72);font-size:.82rem">
                                {{ $operation->vehicle->name ?? '-' }}@if($operation->vehicle?->plate) / {{ $operation->vehicle->plate }}@endif
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span style="background:rgba(255,255,255,0.12);color:#fff;font-size:.75rem;padding:4px 10px;border-radius:20px">
                                {{ $operation->shift }}
                            </span>
                            <span style="background:rgba(255,255,255,0.12);color:#fff;font-size:.75rem;padding:4px 10px;border-radius:20px">
                                {{ $operation->trip_date->format('d.m.Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($isOwner)
                        <div class="alert border-0 mb-4" style="background:#fbf6ef;color:#8f6d4f">
                            Bu kayit ayni servis plakasi altinda Beach ve Foresta hareketlerini birlikte tutar. Diger taraf sayilari ve saatleri gorebilir; yalnizca kendi satirlarini guncelleyebilir.
                        </div>

                        <form action="{{ route('shuttle.operations.update', $operation) }}" method="POST" id="editTripForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="context_branch_id" value="{{ $contextBranchId }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Kaydi Acan Otel <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select" required>
                                        @foreach($branches as $branchOption)
                                            <option value="{{ $branchOption->id }}" @selected(old('branch_id', $operation->branch_id) == $branchOption->id)>
                                                {{ $branchOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Arac / Plaka <span class="text-danger">*</span></label>
                                    <select name="shuttle_vehicle_id" id="editTripVehicleId" class="form-select" required>
                                        @foreach($vehicles as $vehicle)
                                            <option
                                                value="{{ $vehicle->id }}"
                                                data-route-ids="{{ $vehicle->routes->pluck('id')->implode(',') }}"
                                                @selected(old('shuttle_vehicle_id', $operation->shuttle_vehicle_id) == $vehicle->id)
                                            >
                                                {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - Kap: {{ $vehicle->capacity }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Guzergah</label>
                                    <select name="route_id" id="editTripRouteId" class="form-select">
                                        <option value="">- Seciniz -</option>
                                        @foreach($routes as $route)
                                            <option value="{{ $route->id }}" @selected(old('route_id', $operation->route_id) == $route->id)>
                                                {{ $route->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="small text-muted mt-1" id="editTripRouteHelp">Ortak guzergahlardan, yalnizca secilen aracin gorevli olduklari gorunur.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Vardiya <span class="text-danger">*</span></label>
                                    <select name="shift" class="form-select" required>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift }}" @selected(old('shift', $operation->shift) === $shift)>{{ $shift }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tarih <span class="text-danger">*</span></label>
                                    <input type="date" name="trip_date" value="{{ old('trip_date', $operation->trip_date->format('Y-m-d')) }}" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <div class="rounded-3 p-3" style="background:#fbf8f5;border:1px solid #eadcc9">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="small text-muted text-uppercase fw-semibold mb-1">Dahil Otel</div>
                                                <div class="fw-semibold text-dark">{{ $includedBranchCount }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted text-uppercase fw-semibold mb-1">Toplam Indi</div>
                                                <div class="fw-semibold text-dark">{{ $operation->arrival_count }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted text-uppercase fw-semibold mb-1">Toplam Bindi</div>
                                                <div class="fw-semibold text-dark">{{ $operation->departure_count }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted text-uppercase fw-semibold mb-1">Ekleyen</div>
                                                <div class="fw-semibold text-dark">{{ $operation->creator->name ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="p-3 rounded" style="background:#fff4f4;border:1px solid #f0d0d0">
                                        <div class="fw-semibold small mb-2" style="color:#a94442">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Operasyon Istisnalari
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <input type="hidden" name="arrived_with_different_vehicle" value="0">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                           name="arrived_with_different_vehicle" id="editDifferentVehicle"
                                                           @checked(old('arrived_with_different_vehicle', $operation->arrived_with_different_vehicle))>
                                                    <label class="form-check-label fw-semibold" for="editDifferentVehicle">
                                                        Farkli arac ile geldi
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="hidden" name="is_transfer" value="0">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                           name="is_transfer" id="editIsTransfer"
                                                           @checked(old('is_transfer', $operation->is_transfer))>
                                                    <label class="form-check-label fw-semibold" for="editIsTransfer">
                                                        Aktarim yapildi
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="hidden" name="is_lodging_route" value="0">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                           name="is_lodging_route" id="editIsLodgingRoute"
                                                           @checked(old('is_lodging_route', $operation->is_lodging_route))>
                                                    <label class="form-check-label fw-semibold" for="editIsLodgingRoute">
                                                        Lojman guzergahi yapildi
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    @include('modules.shuttle.operations._branch_movements', [
                                        'branches' => $allBranches,
                                        'movementValues' => $movementValues,
                                        'editableBranchIds' => $visibleBranchIds,
                                        'selectableBranchIds' => $allBranches->pluck('id')->all(),
                                        'title' => 'Otel Bazli Geldi / Indi / Cikti / Bindi',
                                        'description' => 'İlk/İkinci Uğrama aynı servis kaydı içindeki otel giriş-çıkış hareketidir; ayrı sefer sayılmaz. Her otel geldi-çıktı saatini ve kişi sayısını kendi satırında tutar.',
                                        'theme' => 'info',
                                    ])
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Not</label>
                                    <textarea name="notes" rows="3" class="form-control" maxlength="500">{{ old('notes', $operation->notes) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                    <i class="fas fa-save me-1"></i> Kaydet
                                </button>
                                <a href="{{ route('shuttle.operations.index', ['branch_id' => $contextBranchId, 'date' => old('trip_date', $operation->trip_date->format('Y-m-d'))]) }}"
                                   class="btn btn-outline-secondary">
                                    Vazgec
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="alert border-0 mb-4" style="background:#eef6f2;color:#2e7d52">
                            Bu plakada sadece kendi otelinin hareketini isleyebilirsin. Diger otelin saat ve sayi bilgileri read-only gorunur.
                        </div>

                        <form action="{{ route('shuttle.operations.update', $operation) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="context_branch_id" value="{{ $contextBranchId }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="rounded-3 p-3 h-100" style="background:#fbf8f5;border:1px solid #eadcc9">
                                        <div class="small text-muted text-uppercase fw-semibold mb-1">Arac / Plaka</div>
                                        <div class="fw-semibold text-dark">{{ $operation->vehicle->name ?? '-' }}</div>
                                        <div class="small text-muted mt-1">{{ $operation->vehicle->plate ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded-3 p-3 h-100" style="background:#fbf8f5;border:1px solid #eadcc9">
                                        <div class="small text-muted text-uppercase fw-semibold mb-1">Guzergah</div>
                                        <div class="fw-semibold text-dark">{{ $operation->route->name ?? 'Guzergah belirtilmedi' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded-3 p-3 h-100" style="background:#fbf8f5;border:1px solid #eadcc9">
                                        <div class="small text-muted text-uppercase fw-semibold mb-1">Kaydi Acan Otel</div>
                                        <div class="fw-semibold text-dark">{{ $operation->branch->name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    @include('modules.shuttle.operations._branch_movements', [
                                        'branches' => $allBranches,
                                        'movementValues' => $movementValues,
                                        'editableBranchIds' => [$contextBranchId],
                                        'selectableBranchIds' => [],
                                        'showInclude' => false,
                                        'title' => 'İlk / İkinci Uğrama Otel Hareketleri',
                                        'description' => 'İlk/İkinci Uğrama aynı servis kaydı içindeki otel giriş-çıkış hareketidir; ayrı sefer sayılmaz. Bu ekranda yalnızca kendi otelinin satırlarını güncelleyebilirsin.',
                                        'theme' => 'neutral',
                                    ])
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Not</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $operation->notes ?: '-' }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                    <i class="fas fa-save me-1"></i> Kaydet
                                </button>
                                <a href="{{ route('shuttle.operations.index', ['branch_id' => $contextBranchId, 'date' => $operation->trip_date->format('Y-m-d')]) }}"
                                   class="btn btn-outline-secondary">
                                    Vazgec
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tryShowPicker(input) {
    if (!input || typeof input.showPicker !== 'function') {
        return;
    }

    try {
        input.showPicker();
    } catch (error) {
        // Tarayici desteklemiyorsa sessizce gec.
    }
}

function bindAutoTimePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-time-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(vehicleSelector, routeSelector, helpSelector) {
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
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
                helpBox.textContent = 'Ortak guzergahlardan, yalnizca secilen aracin gorevli olduklari gorunur.';
            } else if (allowedRouteIds.size === 0) {
                helpBox.textContent = 'Bu araca henuz guzergah atamasi yapilmamis.';
            } else {
                helpBox.textContent = 'Secilen aracin ortak guzergah gorevleri gorunuyor.';
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

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireBranchMovementIncludes(document);

    @if($isOwner)
        wireTripForm(
            '#editTripVehicleId',
            '#editTripRouteId',
            '#editTripRouteHelp'
        );
    @endif
});
</script>
@endpush
