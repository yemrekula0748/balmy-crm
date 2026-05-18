@extends('layouts.default')
@section('title', 'Sefer Duzenle')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Sefer Duzenle</h4>
                <span>{{ $operation->branch->name ?? '-' }} <i class="fas fa-arrow-right mx-1"></i> {{ $operation->destinationBranch->name ?? '-' }}</span>
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

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0" style="border-radius:14px;overflow:hidden">
                <div class="card-header border-0 text-white" style="background:linear-gradient(135deg,#1e2d3d,#2c3e50)">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ $operation->trip_date->format('d.m.Y') }} - {{ $operation->shift }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('shuttle.operations.update', $operation) }}" method="POST" id="editTripForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="context_branch_id" value="{{ $contextBranchId }}">

                        @if($contextRole === 'origin')
                            <div class="alert alert-info border-0" style="background:#eef3f9;color:#2a5298">
                                Bu ekran kaynak otel tarafini duzenler. Hedef otel, kendi inen ve binen bilgisini ayri isler.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kaynak Otel <span class="text-danger">*</span></label>
                                    <select name="branch_id" id="editTripBranchId" class="form-select" required>
                                        @foreach($branches as $branchOption)
                                            <option value="{{ $branchOption->id }}" @selected(old('branch_id', $operation->branch_id) == $branchOption->id)>
                                                {{ $branchOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hedef Otel <span class="text-danger">*</span></label>
                                    <select name="destination_branch_id" id="editTripDestinationBranchId" class="form-select" required>
                                        @foreach($destinationBranches as $branchOption)
                                            <option value="{{ $branchOption->id }}" @selected(old('destination_branch_id', $operation->destination_branch_id) == $branchOption->id)>
                                                {{ $branchOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Arac <span class="text-danger">*</span></label>
                                    <select name="shuttle_vehicle_id" id="editTripVehicleId" class="form-select" required>
                                        @foreach($vehicles as $vehicle)
                                            <option
                                                value="{{ $vehicle->id }}"
                                                data-branch-id="{{ $vehicle->branch_id }}"
                                                data-route-ids="{{ $vehicle->routes->pluck('id')->implode(',') }}"
                                                @selected(old('shuttle_vehicle_id', $operation->shuttle_vehicle_id) == $vehicle->id)
                                            >
                                                {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - Kap: {{ $vehicle->capacity }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Guzergah</label>
                                    <select name="route_id" id="editTripRouteId" class="form-select">
                                        <option value="">- Seciniz -</option>
                                        @foreach($routes as $route)
                                            <option value="{{ $route->id }}" @selected(old('route_id', $operation->route_id) == $route->id)>
                                                {{ $route->name }} - {{ $route->branch->name ?? 'Sube yok' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="small text-muted mt-1" id="editTripRouteHelp">Secilen aracin gorevli oldugu guzergahlar listelenir.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vardiya <span class="text-danger">*</span></label>
                                    <select name="shift" class="form-select" required>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift }}" @selected(old('shift', $operation->shift) === $shift)>{{ $shift }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                                    <input type="date" name="trip_date" value="{{ old('trip_date', $operation->trip_date->format('Y-m-d')) }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kaynaktan Cikis Saati</label>
                                    <input type="time" name="origin_departure_time"
                                           value="{{ old('origin_departure_time', $operation->origin_departure_time ? substr($operation->origin_departure_time, 0, 5) : '') }}"
                                           class="form-control" data-auto-time-picker="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kaynaktan Cikan Kisi <span class="text-danger">*</span></label>
                                    <input type="number" name="origin_departure_count"
                                           value="{{ old('origin_departure_count', $operation->origin_departure_count) }}"
                                           min="0" max="500" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <div class="rounded-3 p-3" style="background:#eef6f2;border:1px solid #cfe3d8">
                                        <div class="small text-uppercase fw-semibold text-muted mb-2">Hedef Otelde Gorunen Referans</div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <div class="small text-muted">Indi</div>
                                                <div class="fw-semibold text-dark">{{ $operation->arrival_count }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="small text-muted">Indi Saati</div>
                                                <div class="fw-semibold text-dark">{{ $operation->arrival_time ? substr($operation->arrival_time, 0, 5) : '-' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="small text-muted">Bindi</div>
                                                <div class="fw-semibold text-dark">{{ $operation->departure_count }}</div>
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

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Not</label>
                                    <textarea name="notes" rows="3" class="form-control" maxlength="500">{{ old('notes', $operation->notes) }}</textarea>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-success border-0" style="background:#eef6f2;color:#2e7d52">
                                Bu ekran hedef otel tarafini duzenler. Kaynak otelin girdigi arac, sayi ve cikis bilgisi yalnizca referans olarak gorunur.
                            </div>

                            <div class="rounded-3 p-3 mb-4" style="background:#f6f8fb;border:1px solid #e1e7f0">
                                <div class="small text-uppercase fw-semibold text-muted mb-2">Kaynak Bilgisi</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="small text-muted">Kaynak Otel</div>
                                        <div class="fw-semibold text-dark">{{ $operation->branch->name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Cikan Kisi</div>
                                        <div class="fw-semibold text-dark">{{ $operation->origin_departure_count }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Cikis Saati</div>
                                        <div class="fw-semibold text-dark">{{ $operation->origin_departure_time ? substr($operation->origin_departure_time, 0, 5) : '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kac Kisi Indi <span class="text-danger">*</span></label>
                                    <input type="number" name="arrival_count"
                                           value="{{ old('arrival_count', $operation->arrival_count) }}"
                                           min="0" max="500" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Varis Saati</label>
                                    <input type="time" name="arrival_time"
                                           value="{{ old('arrival_time', $operation->arrival_time ? substr($operation->arrival_time, 0, 5) : '') }}"
                                           class="form-control" data-auto-time-picker="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kac Kisi Bindi <span class="text-danger">*</span></label>
                                    <input type="number" name="departure_count"
                                           value="{{ old('departure_count', $operation->departure_count) }}"
                                           min="0" max="500" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cikis Saati</label>
                                    <input type="time" name="departure_time"
                                           value="{{ old('departure_time', $operation->departure_time ? substr($operation->departure_time, 0, 5) : '') }}"
                                           class="form-control" data-auto-time-picker="1">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Kaynak Tarafin Notu</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $operation->notes }}</textarea>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Kaydet
                            </button>
                            <a href="{{ route('shuttle.operations.index', ['branch_id' => $contextBranchId, 'date' => $operation->trip_date->format('Y-m-d')]) }}"
                               class="btn btn-outline-secondary">
                                Vazgec
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
        // Tarayici desteklemiyorsa sessizce gec.
    }
}

function bindAutoTimePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-time-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(branchSelector, destinationSelector, vehicleSelector, routeSelector, helpSelector) {
    const branchSelect = document.querySelector(branchSelector);
    const destinationSelect = document.querySelector(destinationSelector);
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
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
            const matches = !option.value || !branchId || option.dataset.branchId === branchId;
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

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireTripForm(
        '#editTripBranchId',
        '#editTripDestinationBranchId',
        '#editTripVehicleId',
        '#editTripRouteId',
        '#editTripRouteHelp'
    );
});
</script>
@endpush
