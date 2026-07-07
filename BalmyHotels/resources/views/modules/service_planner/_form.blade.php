@php
    $vehicleNames = old('vehicle_names');
    $vehicleCapacities = old('vehicle_capacities');
    $vehicleColors = old('vehicle_colors');

    if ($vehicleNames === null || $vehicleCapacities === null) {
        $existingVehicles = isset($plan) && $plan->relationLoaded('vehicles')
            ? $plan->vehicles
            : collect();

        if ($existingVehicles->isNotEmpty()) {
            $vehicleNames = $existingVehicles->pluck('name')->all();
            $vehicleCapacities = $existingVehicles->pluck('seat_capacity')->all();
            $vehicleColors = $existingVehicles->pluck('color')->all();
        } else {
            $vehicleNames = ['Servis 1', 'Servis 2'];
            $vehicleCapacities = [16, 16];
            $vehicleColors = ['#C19B77', '#5D7FA3'];
        }
    }
@endphp

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <h5 class="mb-3">Plan Bilgileri</h5>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Plan Adi</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $plan->name ?? '') }}" placeholder="Ornek: Foresta Sabah Personel Servisi">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plan Tarihi</label>
                        <input type="date" name="plan_date" class="form-control @error('plan_date') is-invalid @enderror"
                               value="{{ old('plan_date', isset($plan->plan_date) && $plan->plan_date ? $plan->plan_date->format('Y-m-d') : '') }}">
                        @error('plan_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sube</label>
                        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                            <option value="">Genel / Ortak</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $plan->branch_id ?? '') === (string) $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kalkis Noktasi Adi</label>
                        <input type="text" name="start_location_name" class="form-control @error('start_location_name') is-invalid @enderror"
                               value="{{ old('start_location_name', $plan->start_location_name ?? '') }}" placeholder="Ornek: Balmy Foresta Personel Girisi">
                        @error('start_location_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Kalkis Adresi</label>
                        <textarea name="start_address" rows="3" class="form-control @error('start_address') is-invalid @enderror"
                                  placeholder="Adres ne kadar acik olursa otomatik rota o kadar dogru hesaplanir.">{{ old('start_address', $plan->start_address ?? '') }}</textarea>
                        @error('start_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Plan Notu</label>
                        <textarea name="planning_notes" rows="4" class="form-control @error('planning_notes') is-invalid @enderror"
                                  placeholder="Vardiya bilgisi, ozel mahalle dagilimi, surucu notu gibi ek bilgileri yazabilirsiniz.">{{ old('planning_notes', $plan->planning_notes ?? '') }}</textarea>
                        @error('planning_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="mb-1">Servis ve Koltuk Bilgisi</h5>
                        <div class="text-muted small">Her servis icin isim ve koltuk kapasitesi girin.</div>
                    </div>
                    <button type="button" class="btn btn-sm" id="addVehicleRow"
                            style="background:#c19b77;border-color:#c19b77;color:#fff;">
                        <i class="fas fa-plus me-1"></i> Servis Ekle
                    </button>
                </div>

                @error('vehicle_capacities')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                <div id="vehicleRows" class="d-flex flex-column gap-3">
                    @foreach($vehicleCapacities as $index => $capacity)
                        <div class="border rounded-3 p-3 vehicle-row" style="background:#fbf8f3;border-color:#eadcc9 !important;">
                            <div class="row g-2 align-items-end">
                                <div class="col-5">
                                    <label class="form-label small">Servis Adi</label>
                                    <input type="text" name="vehicle_names[]" class="form-control"
                                           value="{{ $vehicleNames[$index] ?? ('Servis ' . ($index + 1)) }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Koltuk</label>
                                    <input type="number" name="vehicle_capacities[]" min="1" max="100" class="form-control"
                                           value="{{ $capacity }}">
                                </div>
                                <div class="col-2">
                                    <label class="form-label small">Renk</label>
                                    <input type="color" name="vehicle_colors[]" class="form-control form-control-color w-100"
                                           value="{{ $vehicleColors[$index] ?? '#C19B77' }}">
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-link text-danger p-0 remove-vehicle" title="Sil">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 rounded-3 p-3" style="background:#f6fbf9;border:1px solid #d6eee5;">
                    <div class="fw-semibold mb-1">Akis</div>
                    <div class="small text-muted">
                        1. Plani kaydedin.
                        2. Excel ile personel ve adres listesini yukleyin.
                        3. Sistem kapasiteye gore servisleri otomatik dagitsin.
                        4. Sonucu PDF olarak indirin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="vehicleRowTemplate">
    <div class="border rounded-3 p-3 vehicle-row" style="background:#fbf8f3;border-color:#eadcc9 !important;">
        <div class="row g-2 align-items-end">
            <div class="col-5">
                <label class="form-label small">Servis Adi</label>
                <input type="text" name="vehicle_names[]" class="form-control" value="">
            </div>
            <div class="col-4">
                <label class="form-label small">Koltuk</label>
                <input type="number" name="vehicle_capacities[]" min="1" max="100" class="form-control" value="16">
            </div>
            <div class="col-2">
                <label class="form-label small">Renk</label>
                <input type="color" name="vehicle_colors[]" class="form-control form-control-color w-100" value="#C19B77">
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-link text-danger p-0 remove-vehicle" title="Sil">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('vehicleRows');
        const addButton = document.getElementById('addVehicleRow');
        const template = document.getElementById('vehicleRowTemplate');
        const colors = ['#C19B77', '#5D7FA3', '#4E8D7C', '#B76E79', '#8A6FB5', '#D4904F'];

        function bindRemoveButtons() {
            container.querySelectorAll('.remove-vehicle').forEach(function (button) {
                button.onclick = function () {
                    if (container.querySelectorAll('.vehicle-row').length === 1) {
                        return;
                    }

                    button.closest('.vehicle-row').remove();
                    refreshNames();
                };
            });
        }

        function refreshNames() {
            container.querySelectorAll('.vehicle-row').forEach(function (row, index) {
                const nameInput = row.querySelector('input[name="vehicle_names[]"]');
                const colorInput = row.querySelector('input[name="vehicle_colors[]"]');

                if (!nameInput.value.trim()) {
                    nameInput.value = 'Servis ' + (index + 1);
                }

                if (colorInput && !colorInput.dataset.manual) {
                    colorInput.value = colors[index % colors.length];
                }
            });
        }

        container.querySelectorAll('input[name="vehicle_colors[]"]').forEach(function (input) {
            input.addEventListener('input', function () {
                input.dataset.manual = '1';
            });
        });

        addButton.addEventListener('click', function () {
            const node = template.content.cloneNode(true);
            const nextIndex = container.querySelectorAll('.vehicle-row').length;
            const nameInput = node.querySelector('input[name="vehicle_names[]"]');
            const colorInput = node.querySelector('input[name="vehicle_colors[]"]');

            nameInput.value = 'Servis ' + (nextIndex + 1);
            colorInput.value = colors[nextIndex % colors.length];

            container.appendChild(node);
            bindRemoveButtons();
            refreshNames();
        });

        bindRemoveButtons();
        refreshNames();
    });
</script>
@endpush
