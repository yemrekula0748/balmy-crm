@php
    $movementValues = collect($movementValues ?? []);
@endphp

<div class="p-3 rounded" style="background:#faf6f1;border:1px solid #eadcc9">
    <div class="fw-semibold small mb-2" style="color:#7a5c3d">
        <i class="fas fa-exchange-alt me-1"></i>Sube Hareketi (Opsiyonel)
    </div>
    <div class="small text-muted mb-3">
        Foresta'dan kalkip Beach'ten personel alma gibi ortak seferleri kontrollu takip etmek icin kullan.
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th class="border-0 ps-0 small text-muted text-uppercase">Sube</th>
                    <th class="border-0 small text-muted text-uppercase text-center">Alinan</th>
                    <th class="border-0 pe-0 small text-muted text-uppercase text-center">Indirilen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $movementBranch)
                    @php
                        $branchValue = $movementValues->get($movementBranch->id, []);
                        $pickupValue = data_get($branchValue, 'pickup', 0);
                        $dropoffValue = data_get($branchValue, 'dropoff', 0);
                    @endphp
                    <tr>
                        <td class="ps-0 fw-semibold text-dark">{{ $movementBranch->name }}</td>
                        <td class="text-center">
                            <input
                                type="number"
                                min="0"
                                max="500"
                                name="branch_movements[{{ $movementBranch->id }}][pickup]"
                                value="{{ $pickupValue }}"
                                class="form-control form-control-sm text-center"
                                style="max-width:90px;margin:0 auto"
                            >
                        </td>
                        <td class="pe-0 text-center">
                            <input
                                type="number"
                                min="0"
                                max="500"
                                name="branch_movements[{{ $movementBranch->id }}][dropoff]"
                                value="{{ $dropoffValue }}"
                                class="form-control form-control-sm text-center"
                                style="max-width:90px;margin:0 auto"
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('branch_movements')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.pickup')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.dropoff')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
