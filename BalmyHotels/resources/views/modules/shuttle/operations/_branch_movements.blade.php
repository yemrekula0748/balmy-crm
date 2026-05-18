@php
    $movementValues = collect($movementValues ?? []);
    $showArrival = $showArrival ?? true;
    $showDeparture = $showDeparture ?? true;
    $title = $title ?? 'Sube Bazli Personel Dagilimi';
    $description = $description ?? 'Foresta ve Beach personelini ayri ayri gir; toplamlar otomatik hesaplanir ve raporlar karismaz.';
    $theme = $theme ?? 'neutral';
    $compact = $compact ?? false;

    $themeStyles = [
        'neutral' => ['bg' => '#faf6f1', 'border' => '#eadcc9', 'text' => '#7a5c3d'],
        'arrival' => ['bg' => '#f0f4ff', 'border' => '#d0ddf5', 'text' => '#2a5298'],
        'departure' => ['bg' => '#eef6f2', 'border' => '#cfe3d8', 'text' => '#2e7d52'],
    ][$theme] ?? ['bg' => '#faf6f1', 'border' => '#eadcc9', 'text' => '#7a5c3d'];

    $inputClass = $compact ? 'form-control form-control-sm text-center' : 'form-control text-center';
@endphp

<div class="p-3 rounded" style="background:{{ $themeStyles['bg'] }};border:1px solid {{ $themeStyles['border'] }}">
    <div class="fw-semibold small mb-2" style="color:{{ $themeStyles['text'] }}">
        <i class="fas fa-exchange-alt me-1"></i>{{ $title }}
    </div>
    <div class="small text-muted mb-3">
        {{ $description }}
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th class="border-0 ps-0 small text-muted text-uppercase">Sube</th>
                    @if($showArrival)
                        <th class="border-0 small text-muted text-uppercase text-center">Gelen</th>
                    @endif
                    @if($showDeparture)
                        <th class="border-0 pe-0 small text-muted text-uppercase text-center">Giden</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $movementBranch)
                    @php
                        $branchValue = $movementValues->get($movementBranch->id, []);
                        $arrivalValue = data_get($branchValue, 'arrival', 0);
                        $departureValue = data_get($branchValue, 'departure', 0);
                    @endphp
                    <tr>
                        <td class="ps-0 fw-semibold text-dark">{{ $movementBranch->name }}</td>
                        @if($showArrival)
                            <td class="text-center">
                                <input
                                    type="number"
                                    min="0"
                                    max="500"
                                    name="branch_movements[{{ $movementBranch->id }}][arrival]"
                                    value="{{ $arrivalValue }}"
                                    class="{{ $inputClass }}"
                                    style="max-width:96px;margin:0 auto"
                                    data-movement-kind="arrival"
                                >
                            </td>
                        @endif
                        @if($showDeparture)
                            <td class="pe-0 text-center">
                                <input
                                    type="number"
                                    min="0"
                                    max="500"
                                    name="branch_movements[{{ $movementBranch->id }}][departure]"
                                    value="{{ $departureValue }}"
                                    class="{{ $inputClass }}"
                                    style="max-width:96px;margin:0 auto"
                                    data-movement-kind="departure"
                                >
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('branch_movements')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.arrival')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.departure')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
