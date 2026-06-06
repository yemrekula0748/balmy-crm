@php
    $movementValues = collect($movementValues ?? []);
    $editableBranchIds = collect($editableBranchIds ?? [])->map(fn ($id) => (int) $id)->all();
    $selectableBranchIds = collect($selectableBranchIds ?? [])->map(fn ($id) => (int) $id)->all();
    $showInclude = $showInclude ?? true;
    $title = $title ?? 'Otel Bazli Hareket';
    $description = $description ?? 'Ilk/Ikinci Ugrama ayni servis kaydi icindeki otel hareketidir; ayri sefer sayilmaz. Sadece kendi otel satirini duzenleyebilirsin.';
    $theme = $theme ?? 'neutral';
    $compact = $compact ?? false;
    $periods = \App\Models\ShuttleTripBranchMovement::PERIODS;

    $themeStyles = [
        'neutral' => ['bg' => '#faf6f1', 'border' => '#eadcc9', 'text' => '#7a5c3d'],
        'info' => ['bg' => '#fbf6ef', 'border' => '#eadcc9', 'text' => '#8f6d4f'],
        'success' => ['bg' => '#eef6f2', 'border' => '#cfe3d8', 'text' => '#2e7d52'],
    ][$theme] ?? ['bg' => '#faf6f1', 'border' => '#eadcc9', 'text' => '#7a5c3d'];

    $countClass = $compact ? 'form-control form-control-sm text-center' : 'form-control text-center';
    $timeClass = $compact ? 'form-control form-control-sm text-center' : 'form-control text-center';
    $periodHints = [
        'day' => 'Bu vardiyada servisin otelinize ilk temasidir. Personel getiriyorsa Inen, personel goturuyorsa Binen sayisini bu satira yazin.',
        'evening' => 'Ayni plakanin ayni vardiyada otelinize ikinci temasidir. Servis tekrar ugradiginda inen veya binen kisileri bu satira yazin.',
    ];
@endphp

<div class="p-3 rounded" style="background:{{ $themeStyles['bg'] }};border:1px solid {{ $themeStyles['border'] }}">
    <div class="fw-semibold small mb-2" style="color:{{ $themeStyles['text'] }}">
        <i class="fas fa-exchange-alt me-1"></i>{{ $title }}
    </div>
    <div class="small text-muted mb-3">
        {{ $description }}
    </div>

    <div class="rounded-3 p-3 mb-3" style="background:#fffaf4;border:1px solid #eadcc9;border-left:4px solid #c19b77">
        <div class="fw-bold mb-1" style="color:#7a5c3d">
            <i class="fas fa-info-circle me-1"></i>Ilk/Ikinci Ugrama nasil okunur?
        </div>
        <div class="small" style="color:#6f5b45">
            <strong>Ilk/Ikinci Ugrama</strong> = ayni plakanin ayni vardiyada otelinize 1. veya 2. temas kaydidir; ayri sefer sayilmaz.
            Servis personel getiriyorsa <strong>Inen</strong>, personel goturuyorsa <strong>Binen</strong> alanina yazin.
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @foreach($periods as $periodKey => $periodLabel)
            @php
                $periodMovementValues = collect($movementValues->get($periodKey, []));
                $showIncludeColumn = $showInclude && $loop->first;
                $periodHint = $periodHints[$periodKey] ?? 'Bu satir, ayni plakanin ayni vardiyadaki otel temasini ifade eder. Inen veya binen sayisini hareket anina gore girin.';
            @endphp
            <div class="rounded-3 bg-white p-3" style="border:1px solid {{ $themeStyles['border'] }}">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div class="fw-semibold small" style="color:{{ $themeStyles['text'] }}">
                        {{ $periodLabel }}
                    </div>
                    <span class="badge" style="background:{{ $themeStyles['bg'] }};color:{{ $themeStyles['text'] }};border:1px solid {{ $themeStyles['border'] }}">
                        Ayni servis kaydi icinde
                    </span>
                </div>
                <div class="small mb-3 px-3 py-2 rounded" style="background:#fbf8f5;color:#6f5b45;border:1px dashed #eadcc9">
                    {{ $periodHint }}
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                @if($showIncludeColumn)
                                    <th class="border-0 ps-0 small text-muted text-uppercase text-center" style="width:82px">Dahil</th>
                                @endif
                                <th class="border-0 small text-muted text-uppercase">Otel</th>
                                <th class="border-0 small text-muted text-uppercase text-center">Hareket Saati</th>
                                <th class="border-0 small text-muted text-uppercase text-center">Inen</th>
                                <th class="border-0 pe-0 small text-muted text-uppercase text-center">Binen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branches as $movementBranch)
                                @php
                                    $branchId = (int) $movementBranch->id;
                                    $branchValue = $periodMovementValues->get($branchId, []);
                                    $included = (bool) data_get($branchValue, 'included', false);
                                    $arrivalValue = (int) data_get($branchValue, 'arrival', 0);
                                    $departureValue = (int) data_get($branchValue, 'departure', 0);
                                    $movementTimeValue = data_get($branchValue, 'movement_time')
                                        ?: data_get($branchValue, 'arrival_time')
                                        ?: data_get($branchValue, 'departure_time');
                                    $canEditRow = in_array($branchId, $editableBranchIds, true);
                                    $canToggleInclude = in_array($branchId, $selectableBranchIds, true);
                                    $rowLocked = ! $canEditRow;
                                @endphp
                                <tr data-branch-row="{{ $branchId }}" data-period-row="{{ $periodKey }}">
                                    @if($showIncludeColumn)
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
                                        @elseif($canToggleInclude && $showIncludeColumn)
                                            <div class="small text-muted">Kendi otel satirin</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input
                                            type="time"
                                            name="branch_movements[{{ $periodKey }}][{{ $branchId }}][movement_time]"
                                            value="{{ $movementTimeValue ? substr($movementTimeValue, 0, 5) : '' }}"
                                            class="{{ $timeClass }}"
                                            style="max-width:130px;margin:0 auto"
                                            data-branch-id="{{ $branchId }}"
                                            data-period-id="{{ $periodKey }}"
                                            data-auto-time-picker="1"
                                            @readonly($rowLocked)
                                        >
                                    </td>
                                    <td class="text-center">
                                        <input
                                            type="number"
                                            min="0"
                                            max="500"
                                            name="branch_movements[{{ $periodKey }}][{{ $branchId }}][arrival]"
                                            value="{{ $arrivalValue }}"
                                            class="{{ $countClass }}"
                                            style="max-width:96px;margin:0 auto"
                                            data-branch-id="{{ $branchId }}"
                                            data-period-id="{{ $periodKey }}"
                                            @readonly($rowLocked)
                                        >
                                    </td>
                                    <td class="pe-0 text-center">
                                        <input
                                            type="number"
                                            min="0"
                                            max="500"
                                            name="branch_movements[{{ $periodKey }}][{{ $branchId }}][departure]"
                                            value="{{ $departureValue }}"
                                            class="{{ $countClass }}"
                                            style="max-width:96px;margin:0 auto"
                                            data-branch-id="{{ $branchId }}"
                                            data-period-id="{{ $periodKey }}"
                                            @readonly($rowLocked)
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @error('involved_branch_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.*.arrival')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.*.departure')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    @error('branch_movements.*.*.movement_time')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
