@php
    $movementValues = collect($movementValues ?? []);
    $editableBranchIds = collect($editableBranchIds ?? [])->map(fn ($id) => (int) $id)->all();
    $selectableBranchIds = collect($selectableBranchIds ?? [])->map(fn ($id) => (int) $id)->all();
    $showInclude = $showInclude ?? true;
    $title = $title ?? 'Otel Bazli Indi / Bindi';
    $description = $description ?? 'Tum otelleri gorebilirsin; sadece kendi otel satirini duzenleyebilirsin.';
    $theme = $theme ?? 'neutral';
    $compact = $compact ?? false;

    $themeStyles = [
        'neutral' => ['bg' => '#faf6f1', 'border' => '#eadcc9', 'text' => '#7a5c3d'],
        'info' => ['bg' => '#f0f4ff', 'border' => '#d0ddf5', 'text' => '#2a5298'],
        'success' => ['bg' => '#eef6f2', 'border' => '#cfe3d8', 'text' => '#2e7d52'],
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
                    @if($showInclude)
                        <th class="border-0 ps-0 small text-muted text-uppercase text-center" style="width:90px">Dahil</th>
                    @endif
                    <th class="border-0 small text-muted text-uppercase">Otel</th>
                    <th class="border-0 small text-muted text-uppercase text-center">Indi</th>
                    <th class="border-0 pe-0 small text-muted text-uppercase text-center">Bindi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $movementBranch)
                    @php
                        $branchId = (int) $movementBranch->id;
                        $branchValue = $movementValues->get($branchId, []);
                        $included = (bool) data_get($branchValue, 'included', false);
                        $arrivalValue = (int) data_get($branchValue, 'arrival', 0);
                        $departureValue = (int) data_get($branchValue, 'departure', 0);
                        $canEditRow = in_array($branchId, $editableBranchIds, true);
                        $canToggleInclude = in_array($branchId, $selectableBranchIds, true);
                        $rowLocked = ! $canEditRow;
                    @endphp
                    <tr data-branch-row="{{ $branchId }}">
                        @if($showInclude)
                            <td class="ps-0 text-center">
                                <div class="form-check d-inline-flex justify-content-center">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="{{ $branchId }}"
                                        name="involved_branch_ids[]"
                                        id="branchIncluded{{ $branchId }}"
                                        @checked($included)
                                        @disabled(! $canToggleInclude)
                                        data-branch-include="{{ $branchId }}"
                                    >
                                    @if($included && ! $canToggleInclude)
                                        <input type="hidden" name="involved_branch_ids[]" value="{{ $branchId }}">
                                    @endif
                                </div>
                            </td>
                        @endif
                        <td>
                            <div class="fw-semibold text-dark">{{ $movementBranch->name }}</div>
                            @if($rowLocked)
                                <div class="small text-muted">Gorunur, degistirilemez</div>
                            @elseif($canToggleInclude)
                                <div class="small text-muted">Kendi satirin</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <input
                                type="number"
                                min="0"
                                max="500"
                                name="branch_movements[{{ $branchId }}][arrival]"
                                value="{{ $arrivalValue }}"
                                class="{{ $inputClass }}"
                                style="max-width:96px;margin:0 auto"
                                data-movement-kind="arrival"
                                data-branch-id="{{ $branchId }}"
                                @readonly($rowLocked)
                            >
                        </td>
                        <td class="pe-0 text-center">
                            <input
                                type="number"
                                min="0"
                                max="500"
                                name="branch_movements[{{ $branchId }}][departure]"
                                value="{{ $departureValue }}"
                                class="{{ $inputClass }}"
                                style="max-width:96px;margin:0 auto"
                                data-movement-kind="departure"
                                data-branch-id="{{ $branchId }}"
                                @readonly($rowLocked)
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('involved_branch_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.arrival')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.departure')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
