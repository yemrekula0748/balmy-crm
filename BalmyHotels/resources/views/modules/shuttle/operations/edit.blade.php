@extends('layouts.default')
@section('title', 'Sefer Duzenle')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sefer Duzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shuttle.operations.index') }}">Operasyon</a></li>
                <li class="breadcrumb-item active">Duzenle</li>
            </ol>
        </div>
    </div>

    @php
        $movementValues = $operation->branchMovements
            ->groupBy('branch_id')
            ->map(function ($items) {
                return [
                    'arrival' => (int) optional($items->firstWhere('movement_type', 'arrival'))->headcount,
                    'departure' => (int) optional($items->firstWhere('movement_type', 'departure'))->headcount,
                ];
            })
            ->toArray();

        if ($movementValues === []) {
            $movementValues = [
                $operation->branch_id => [
                    'arrival' => (int) $operation->arrival_count,
                    'departure' => (int) $operation->departure_count,
                ],
            ];
        }

        $oldMovementValues = old('branch_movements', $movementValues);
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0" style="border-radius:14px;overflow:hidden">
                <div class="card-header border-0 text-white"
                     style="background:linear-gradient(135deg,#1e2d3d,#2c3e50)">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ $operation->trip_date->format('d.m.Y') }} - {{ $operation->shift }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('shuttle.operations.update', $operation) }}" method="POST" id="editTripForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sube <span class="text-danger">*</span></label>
                                <select name="branch_id" id="editTripBranchId" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $operation->branch_id) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Arac <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id" id="editTripVehicleId"
                                        class="form-select @error('shuttle_vehicle_id') is-invalid @enderror" required>
                                    @foreach($vehicles as $v)
                                        <option
                                            value="{{ $v->id }}"
                                            data-branch-id="{{ $v->branch_id }}"
                                            data-route-ids="{{ $v->routes->pluck('id')->implode(',') }}"
                                            @selected(old('shuttle_vehicle_id', $operation->shuttle_vehicle_id) == $v->id)
                                        >
                                            {{ $v->name }}@if($v->plate) ({{ $v->plate }})@endif - Kap: {{ $v->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shuttle_vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Guzergah</label>
                                <select name="route_id" id="editTripRouteId" class="form-select @error('route_id') is-invalid @enderror">
                                    <option value="">- Seciniz -</option>
                                    @foreach($routes as $r)
                                        <option value="{{ $r->id }}" @selected(old('route_id', $operation->route_id) == $r->id)>
                                            {{ $r->name }} - {{ $r->branch->name ?? 'Sube yok' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1" id="editTripRouteHelp">
                                    Secilen aracin gorevli oldugu guzergahlar listelenir.
                                </div>
                                @error('route_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vardiya <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift', $operation->shift) === $shift)>{{ $shift }}</option>
                                    @endforeach
                                </select>
                                @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="trip_date"
                                    value="{{ old('trip_date', $operation->trip_date->format('Y-m-d')) }}"
                                    class="form-control @error('trip_date') is-invalid @enderror"
                                    required
                                >
                                @error('trip_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div id="editTripFlagsBox" class="p-3 rounded" style="background:#fff4f4;border:1px solid #f0d0d0">
                                    <div class="fw-semibold small mb-2" style="color:#a94442">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Operasyon Istisnalari
                                    </div>
                                    <div class="small text-muted mb-3">
                                        Planlanan arac disinda gerceklesen gelis veya transfer durumlarini isaretle.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
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
                                        <div class="col-md-6">
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
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded h-100" style="background:#f0f4ff;border:1px solid #d0ddf5">
                                    <div class="fw-semibold small mb-2" style="color:#2a5298">
                                        <i class="fas fa-arrow-right me-1"></i>Gelis Bilgileri
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label small">Gelis Saati</label>
                                            <input
                                                type="time"
                                                name="arrival_time"
                                                value="{{ old('arrival_time', $operation->arrival_time ? substr($operation->arrival_time, 0, 5) : '') }}"
                                                class="form-control"
                                                data-auto-time-picker="1"
                                            >
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small">Toplam Gelen</label>
                                            <input
                                                type="number"
                                                name="arrival_count"
                                                value="{{ old('arrival_count', $operation->arrival_count) }}"
                                                min="0"
                                                max="500"
                                                class="form-control"
                                                id="editArrivalTotal"
                                                readonly
                                            >
                                            <div class="small text-muted mt-1">
                                                Toplam, asagidaki sube satirlarindan otomatik hesaplanir.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded h-100" style="background:#eef6f2;border:1px solid #cfe3d8">
                                    <div class="fw-semibold small mb-2" style="color:#2e7d52">
                                        <i class="fas fa-arrow-left me-1"></i>Donus Bilgileri
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label small">Donus Saati</label>
                                            <input
                                                type="time"
                                                name="departure_time"
                                                value="{{ old('departure_time', $operation->departure_time ? substr($operation->departure_time, 0, 5) : '') }}"
                                                class="form-control"
                                                data-auto-time-picker="1"
                                            >
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small">Toplam Giden</label>
                                            <input
                                                type="number"
                                                name="departure_count"
                                                value="{{ old('departure_count', $operation->departure_count) }}"
                                                min="0"
                                                max="500"
                                                class="form-control"
                                                id="editDepartureTotal"
                                                readonly
                                            >
                                            <div class="small text-muted mt-1">
                                                Toplam, asagidaki sube satirlarindan otomatik hesaplanir.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                @include('modules.shuttle.operations._branch_movements', [
                                    'branches' => $branches,
                                    'movementValues' => $oldMovementValues,
                                ])
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Not</label>
                                <textarea name="notes" rows="2" class="form-control" maxlength="500">{{ old('notes', $operation->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guncelle
                            </button>
                            <a
                                href="{{ route('shuttle.operations.index', ['date' => $operation->trip_date->format('Y-m-d'), 'branch_id' => $operation->branch_id]) }}"
                                class="btn btn-outline-secondary"
                            >
                                Iptal
                            </a>
                        </div>
                    </form>
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
        // Browser desteklemiyorsa sessizce gec.
    }
}

function bindAutoTimePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-time-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(branchSelector, vehicleSelector, routeSelector, flagsSelector, helpSelector, currentRouteId = '', legacyVehicleId = '') {
    const branchSelect = document.querySelector(branchSelector);
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
    const flagsBox = document.querySelector(flagsSelector);
    const helpBox = document.querySelector(helpSelector);

    if (!branchSelect || !vehicleSelect || !routeSelect) {
        return;
    }

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

            const allowed = vehicleId && (
                allowedRouteIds.has(option.value)
                || (option.value === currentRouteId && vehicleId === legacyVehicleId)
            );
            option.hidden = !allowed;
            option.disabled = !allowed;
        });

        if (routeSelect.selectedOptions[0] && routeSelect.selectedOptions[0].disabled) {
            routeSelect.value = '';
        }

        routeSelect.disabled = !vehicleId || (allowedRouteIds.size === 0 && !(currentRouteId && vehicleId === legacyVehicleId));

        if (helpBox) {
            if (!vehicleId) {
                helpBox.textContent = 'Secilen aracin gorevli oldugu guzergahlar listelenir.';
            } else if (allowedRouteIds.size === 0 && !currentRouteId) {
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

function wireMovementTotals(formSelector, arrivalTotalSelector, departureTotalSelector) {
    const form = document.querySelector(formSelector);
    if (!form) {
        return;
    }

    if (form.dataset.movementTotalsBound === '1') {
        if (typeof form._movementTotalsUpdater === 'function') {
            form._movementTotalsUpdater();
        }
        return;
    }

    const updateTotals = () => {
        const arrivalInputs = form.querySelectorAll('input[data-movement-kind="arrival"]');
        const departureInputs = form.querySelectorAll('input[data-movement-kind="departure"]');

        const arrivalTotal = Array.from(arrivalInputs).reduce((total, input) => total + (parseInt(input.value || '0', 10) || 0), 0);
        const departureTotal = Array.from(departureInputs).reduce((total, input) => total + (parseInt(input.value || '0', 10) || 0), 0);

        const arrivalField = arrivalTotalSelector ? form.querySelector(arrivalTotalSelector) : null;
        const departureField = departureTotalSelector ? form.querySelector(departureTotalSelector) : null;

        if (arrivalField) {
            arrivalField.value = arrivalTotal;
        }

        if (departureField) {
            departureField.value = departureTotal;
        }
    };

    form.querySelectorAll('input[data-movement-kind]').forEach((input) => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    form.dataset.movementTotalsBound = '1';
    form._movementTotalsUpdater = updateTotals;
    updateTotals();
}

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireTripForm(
        '#editTripBranchId',
        '#editTripVehicleId',
        '#editTripRouteId',
        '#editTripFlagsBox',
        '#editTripRouteHelp',
        @json((string) ($operation->route_id ?? '')),
        @json((string) $operation->shuttle_vehicle_id)
    );
    wireMovementTotals('#editTripForm', '#editArrivalTotal', '#editDepartureTotal');
});
</script>
@endpush
