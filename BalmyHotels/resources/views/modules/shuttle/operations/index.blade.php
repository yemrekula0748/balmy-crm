@extends('layouts.default')
@section('title', 'Servis Operasyonu')

@section('content')
@php
    $activeBranchId = $currentBranchId ?? ($branches->count() === 1 ? ($branches->first()->id ?? null) : null);
    $oldBranchMovements = collect(old('branch_movements', []));
    $createBranchId = old('branch_id', $activeBranchId);
    $oldInvolvedBranchIds = collect(old('involved_branch_ids', $createBranchId ? [$createBranchId] : []))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
    $movementPeriods = \App\Models\ShuttleTripBranchMovement::PERIODS;
    $serviceShifts = $serviceShifts ?? collect(\App\Models\ShuttleTrip::SHIFTS)
        ->reject(fn ($shift) => strcasecmp(trim((string) $shift), 'Lojman') === 0)
        ->values()
        ->all();
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
                        'movement_time' => data_get($oldRow, 'movement_time')
                            ?: data_get($oldRow, 'arrival_time')
                            ?: data_get($oldRow, 'departure_time'),
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
                <span>{{ $date->format('d.m.Y') }} - Plaka bazli hareket saati / inen / binen takibi</span>
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
                                Ayni plaka altinda Beach ve Foresta kendi hareket saatini ve kisi sayisini tutar
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
                                       data-auto-date-picker="1"
                                       style="width:135px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                       onchange="this.form.submit()">
                                <button type="button" onclick="changeDate(1)" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;width:32px">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </form>
                        @if(auth()->user()->hasPermission('shuttle_operations', 'create'))
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm fw-semibold px-3"
                                        style="background:#fff;color:#8f6d4f;border:none;border-radius:7px"
                                        data-bs-toggle="modal" data-bs-target="#addTripModal">
                                    <i class="fas fa-plus me-1"></i> Hareket Ekle
                                </button>
                                <button type="button" class="btn btn-sm fw-semibold px-3"
                                        style="background:#eef6f2;color:#2e7d52;border:1px solid rgba(255,255,255,0.35);border-radius:7px"
                                        data-bs-toggle="modal" data-bs-target="#addLodgingTripModal">
                                    <i class="fas fa-home me-1"></i> Lojman Hareketi
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c19b77;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Normal Inen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalIncoming }}</div>
                    <div class="small text-muted mt-1">Lojman haric gunluk inen personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #2e7d52;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Normal Binen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalOutgoing }}</div>
                    <div class="small text-muted mt-1">Lojman haric gunluk binen personel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #7a5c3d;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Normal Servis</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#8f6d4f">{{ $totalTrips }}</div>
                    <div class="small text-muted mt-1">Lojman haric gunluk plaka hareketi</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #6a9f7d;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Lojman Gelen</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#2e7d52">{{ $totalLodgingIncoming }}</div>
                    <div class="small text-muted mt-1">Gunluk lojman gelis personeli</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3d7a5e;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Lojman Giden</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#2e7d52">{{ $totalLodgingOutgoing }}</div>
                    <div class="small text-muted mt-1">Gunluk lojman gidis personeli</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #24583f;border-radius:10px">
                <div class="card-body py-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Lojman Seferi</div>
                    <div class="fw-bold" style="font-size:1.8rem;color:#2e7d52">{{ $totalLodgingTrips }}</div>
                    <div class="small text-muted mt-1">Her gidis/gelis ayri sayilir</div>
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
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Gorevli Guzergah</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Otel Hareketleri</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Toplam</th>
                                <th class="py-3 border-0" style="background:#c19b77;color:#fff">Durum / Not</th>
                                <th class="pe-4 py-3 border-0 text-end" style="background:#c19b77;color:#fff">Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastMovementGroup = null; @endphp
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
                                                    'movement_time' => optional($arrivalMovement)->movement_time
                                                        ? substr($arrivalMovement->movement_time, 0, 5)
                                                        : (optional($departureMovement)->movement_time ? substr($departureMovement->movement_time, 0, 5) : null),
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
                                                'movement_time' => null,
                                                'arrival_time' => null,
                                                'departure_time' => null,
                                            ]),
                                        ])->all()
                                        : [];
                                    $isSuperAdmin = auth()->user()->isSuperAdmin();
                                    $isLodgingTrip = (bool) $trip->is_lodging_trip;
                                    $editContextBranchId = $activeBranchId ?: (int) $trip->branch_id;
                                    $canOwnerEdit = auth()->user()->hasPermission('shuttle_operations', 'edit')
                                        && ($isSuperAdmin || ($activeBranchId && (int) $trip->branch_id === (int) $activeBranchId));
                                    $canBranchProcess = ! $isLodgingTrip && $activeBranchId && auth()->user()->hasPermission('shuttle_operations', 'index');
                                    $branchProcessBranchName = optional($allBranches->firstWhere('id', $activeBranchId))->name ?? '';
                                    $movementRowHasDataForView = function (array $row): bool {
                                        return (int) ($row['arrival'] ?? 0) > 0
                                            || (int) ($row['departure'] ?? 0) > 0
                                            || ! empty($row['movement_time'])
                                            || ! empty($row['arrival_time'])
                                            || ! empty($row['departure_time']);
                                    };
                                    $activeBranchMovementComplete = $activeBranchId
                                        && (
                                            $isLodgingTrip
                                                ? collect($activeBranchPeriodMatrix)->contains(fn ($row) => $movementRowHasDataForView((array) $row))
                                                : collect($movementPeriods)->every(function ($periodLabel, $periodKey) use ($activeBranchPeriodMatrix, $movementRowHasDataForView) {
                                                    return $movementRowHasDataForView((array) ($activeBranchPeriodMatrix[$periodKey] ?? []));
                                                })
                                        );
                                    $movementGroupStatus = $activeBranchId
                                        ? ($isLodgingTrip ? 'Lojman Hareketleri' : ($activeBranchMovementComplete ? 'Tamamlanan Hareketler' : 'Bekleyen Hareketler'))
                                        : 'Servis Hareketleri';
                                    $movementGroupKey = ($isLodgingTrip ? 'lodging' : ($activeBranchMovementComplete ? 'done' : 'pending')) . '|' . $trip->shift;
                                    $rowBackground = $isLodgingTrip ? '#f1fbf5' : ($activeBranchId ? ($activeBranchMovementComplete ? '#f1fbf5' : '#fff6f3') : '#fff');
                                    $rowHoverBackground = $isLodgingTrip ? '#e8f7ee' : ($activeBranchId ? ($activeBranchMovementComplete ? '#e8f7ee' : '#ffeeea') : '#fff');
                                    $rowBorder = $isLodgingTrip ? '#2e7d52' : ($activeBranchId ? ($activeBranchMovementComplete ? '#2e7d52' : '#d36b55') : '#eadcc9');
                                    $statusBadgeStyle = $activeBranchMovementComplete
                                        ? 'background:#eef8f1;color:#2e7d52;border:1px solid #cfe3d8'
                                        : 'background:#fff1ed;color:#b44d3c;border:1px solid #f0c8bd';
                                    if ($isLodgingTrip) {
                                        $statusBadgeStyle = 'background:#eef8f1;color:#2e7d52;border:1px solid #cfe3d8';
                                    }
                                    $statusBadgeText = $isLodgingTrip
                                        ? 'Tekli lojman hareketi'
                                        : ($activeBranchMovementComplete ? 'Bu otel tamamlandi' : 'Bu otelde eksik hareket var');
                                    $firstMovementTime = $trip->arrival_time ?: ($isLodgingTrip ? $trip->departure_time : null);
                                    $lastMovementTime = $trip->departure_time ?: ($isLodgingTrip ? $trip->arrival_time : null);
                                @endphp

                                @if($lastMovementGroup !== $movementGroupKey)
                                    <tr>
                                        <td colspan="7" class="py-2 ps-4" style="background:#fbf6ef;border-top:2px solid #eadcc9">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-inline-flex align-items-center gap-2 fw-bold text-uppercase"
                                                     style="color:#7a5c3d;font-size:1.08rem">
                                                    <span style="width:34px;height:34px;border-radius:50%;background:#eadcc9;color:#8f6d4f;display:inline-flex;align-items:center;justify-content:center">
                                                        <i class="fas fa-clock" style="font-size:.92rem"></i>
                                                    </span>
                                                    {{ $trip->shift }}
                                                </div>
                                                @if($activeBranchId)
                                                    <span class="badge" style="{{ $statusBadgeStyle }};font-size:.72rem;border-radius:999px;padding:5px 10px">
                                                        {{ $movementGroupStatus }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @php $lastMovementGroup = $movementGroupKey; @endphp
                                @endif

                                <tr style="--bs-table-bg:{{ $rowBackground }};--bs-table-hover-bg:{{ $rowHoverBackground }};background:{{ $rowBackground }};border-left:4px solid {{ $rowBorder }}">
                                    <td class="ps-4">
                                        <span style="display:inline-flex;align-items:center;min-width:112px;justify-content:center;background:#fbf6ef;color:#7a5c3d;font-size:1.08rem;font-weight:900;padding:9px 14px;border-radius:999px;border:1px solid #eadcc9;box-shadow:0 2px 6px rgba(122,92,61,.08)">
                                            {{ $trip->shift }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size:1.04rem;line-height:1.2">{{ $trip->vehicle->name ?? '-' }}</div>
                                        @if($trip->vehicle?->plate)
                                            <span style="display:inline-flex;align-items:center;margin-top:6px;background:#8f6d4f;color:#fff;font-family:'Courier New',monospace;font-size:1.34rem;font-weight:900;padding:7px 16px;border-radius:7px;letter-spacing:1.4px;box-shadow:0 2px 8px rgba(143,109,79,.22)">
                                                {{ $trip->vehicle->plate }}
                                            </span>
                                        @endif
                                        <div class="small text-muted mt-1">Kaydi acan: {{ $trip->branch->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $isLodgingTrip ? 'Lojman Hareketi' : ($trip->route->name ?? (collect($trip->vehicle?->routes ?? [])->pluck('name')->implode(', ') ?: 'Guzergah atanmadi')) }}
                                        </div>
                                        <div class="mt-2">
                                            <span style="display:inline-flex;align-items:center;gap:6px;background:#fffaf4;color:#7a5c3d;border:1px solid #eadcc9;border-radius:8px;padding:6px 10px;font-size:1rem;font-weight:800">
                                                <i class="fas fa-calendar-day" style="color:#8f6d4f"></i>
                                                {{ $trip->trip_date->format('d.m.Y') }}
                                            </span>
                                        </div>
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
                                                                $movementTime = $counts['movement_time'] ?? ($counts['arrival_time'] ?? ($counts['departure_time'] ?? null));
                                                            @endphp
                                                            <div class="rounded-3 p-2"
                                                                 style="background:{{ $isActiveRow ? '#eef6f2' : '#fff' }};border:1px solid {{ $isActiveRow ? '#cfe3d8' : '#eadcc9' }}">
                                                                <div class="small fw-semibold text-dark mb-1">{{ $movementBranch->name ?? '-' }}</div>
                                                                <div class="small text-muted d-flex gap-3 flex-wrap">
                                                                    <span><strong>Saat:</strong> {{ $movementTime ?: '-' }}</span>
                                                                    <span><strong>Inen:</strong> {{ $counts['arrival'] }}</span>
                                                                    <span><strong>Binen:</strong> {{ $counts['departure'] }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">Ilk Hareket</div>
                                        <div class="fw-semibold text-dark">{{ $firstMovementTime ? substr($firstMovementTime, 0, 5) : '-' }}</div>
                                        <div class="small text-muted mt-2">Toplam Inen / Binen</div>
                                        <div class="fw-semibold text-dark">{{ $trip->arrival_count }} / {{ $trip->departure_count }}</div>
                                        <div class="small text-muted mt-2">Son Hareket</div>
                                        <div class="fw-semibold text-dark">{{ $lastMovementTime ? substr($lastMovementTime, 0, 5) : '-' }}</div>
                                    </td>
                                    <td style="max-width:250px">
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if($activeBranchId)
                                                <span class="badge" style="{{ $statusBadgeStyle }}">{{ $statusBadgeText }}</span>
                                            @endif
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
                                            @if($isLodgingTrip)
                                                <span class="badge" style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8">Lojman seferi</span>
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
                                        <option value="{{ $vehicle->id }}" @selected(old('shuttle_vehicle_id') == $vehicle->id)>
                                            {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - Kap: {{ $vehicle->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Vardiya <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($serviceShifts as $shift)
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
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
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
                                        'description' => 'Ilk/Ikinci Ugrama ayni servis kaydi icindeki otel hareketidir; ayri sefer sayilmaz. Yeni hareket eklerken sadece secili otelin hareket saati, inen ve binen sayisi girilir.',
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

@if(auth()->user()->hasPermission('shuttle_operations', 'create'))
    <div class="modal fade" id="addLodgingTripModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#2e7d52,#3d7a5e)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-home me-2"></i>Yeni Lojman Hareketi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shuttle.operations.lodging-store') }}" method="POST" id="createLodgingTripForm">
                    @csrf
                    <input type="hidden" name="_lodging_trip_form" value="1">
                    <div class="modal-body px-4 py-3">
                        <div class="rounded-3 p-3 mb-3" style="background:#eef6f2;border:1px solid #cfe3d8">
                            <div class="fw-semibold small mb-1" style="color:#2e7d52">
                                <i class="fas fa-info-circle me-1"></i>Lojman seferi tekli sayilir
                            </div>
                            <div class="small text-muted">
                                Bu formda sadece 1 adet lojman gidis ya da 1 adet lojman gelis hareketi kaydedilir. Gidis ve gelis ayri ayri sefer olarak raporlanir.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kaydi Acan Otel <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((old('_lodging_trip_form') ? old('branch_id', $activeBranchId) : $activeBranchId) == $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Arac / Plaka <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected(old('_lodging_trip_form') && old('shuttle_vehicle_id') == $vehicle->id)>
                                            {{ $vehicle->name }}@if($vehicle->plate) ({{ $vehicle->plate }})@endif - Kap: {{ $vehicle->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Hareket Tipi <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="radio" class="btn-check" name="movement_type" id="lodgingMovementDeparture" value="departure"
                                           @checked(old('_lodging_trip_form') ? old('movement_type', 'departure') === 'departure' : true)>
                                    <label class="btn btn-outline-success flex-fill" for="lodgingMovementDeparture">
                                        <i class="fas fa-arrow-up me-1"></i>Gidis
                                    </label>

                                    <input type="radio" class="btn-check" name="movement_type" id="lodgingMovementArrival" value="arrival"
                                           @checked(old('_lodging_trip_form') && old('movement_type') === 'arrival')>
                                    <label class="btn btn-outline-success flex-fill" for="lodgingMovementArrival">
                                        <i class="fas fa-arrow-down me-1"></i>Gelis
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Tarih <span class="text-danger">*</span></label>
                                <input type="date" name="trip_date" value="{{ old('_lodging_trip_form') ? old('trip_date', $date->toDateString()) : $date->toDateString() }}" class="form-control" data-auto-date-picker="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Hareket Saati <span class="text-danger">*</span></label>
                                <input type="time" name="movement_time" value="{{ old('_lodging_trip_form') ? old('movement_time') : '' }}" class="form-control" data-auto-time-picker="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kisi Sayisi <span class="text-danger">*</span></label>
                                <input type="number" name="headcount" value="{{ old('_lodging_trip_form') ? old('headcount', 1) : 1 }}" class="form-control" min="1" max="500" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Not</label>
                                <textarea name="notes" rows="2" class="form-control" maxlength="500">{{ old('_lodging_trip_form') ? old('notes') : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Iptal</button>
                        <button type="submit" class="btn fw-semibold px-4" style="background:#2e7d52;color:#fff;border-radius:8px">
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
                            <div class="small text-muted mt-1">Ilk/Ikinci Ugrama ayni servis kaydi icindeki otel hareketidir; ayri sefer sayilmaz. Secilen ugrama icin tek hareket saati, inen ve binen sayisi kaydedilir.</div>
                        </div>
                        <div class="rounded-3 p-3 mb-3" style="background:#fffaf4;border:1px solid #eadcc9;border-left:4px solid #c19b77">
                            <div class="fw-bold mb-1" style="color:#7a5c3d">
                                <i class="fas fa-info-circle me-1"></i>Ugrama ne demek?
                            </div>
                            <div class="small" style="color:#6f5b45">
                                Ilk/Ikinci Ugrama, ayni plakanin ayni vardiyada otelinize 1. veya 2. temasidir; ayri sefer sayilmaz.
                                Servis personel getirdiyse <strong>Inen</strong>, personel goturduyse <strong>Binen</strong> sayisini girin.
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Uğrama <span class="text-danger">*</span></label>
                                <select name="movement_period" id="branchProcessMovementPeriod" class="form-select" required>
                                    @foreach($movementPeriods as $periodKey => $periodLabel)
                                        <option value="{{ $periodKey }}">{{ $periodLabel }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1">Getirme/goturme ayrimini saat degil, Inen ve Binen sayilari belirler.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Hareket Saati</label>
                                <input type="time" name="movement_time" id="branchProcessMovementTime" class="form-control" data-auto-time-picker="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Inen <span class="text-danger">*</span></label>
                                <input type="number" name="arrival_count" id="branchProcessArrivalCount" class="form-control" min="0" max="500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Binen <span class="text-danger">*</span></label>
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
    const form = document.getElementById('filterForm');

    if (!input || !form) {
        return;
    }

    const date = parseDateInput(input.value);
    date.setDate(date.getDate() + Number(delta || 0));
    input.value = formatDateInput(date);
    form.submit();
}

function parseDateInput(value) {
    const parts = String(value || '').split('-').map((part) => Number(part));

    if (parts.length !== 3 || parts.some((part) => !Number.isFinite(part))) {
        return new Date();
    }

    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function formatDateInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
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

function bindAutoDatePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-date-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(vehicleSelector, flagsSelector) {
    const vehicleSelect = document.querySelector(vehicleSelector);
    const flagsBox = document.querySelector(flagsSelector);

    if (!vehicleSelect) {
        return;
    }

    const syncVehicleState = () => {
        const selectedVehicle = vehicleSelect.selectedOptions[0];
        const vehicleId = selectedVehicle?.value || '';

        if (flagsBox) {
            flagsBox.classList.toggle('d-none', !vehicleId);
        }
    };

    vehicleSelect.addEventListener('change', syncVehicleState);
    syncVehicleState();
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
        const movementTime = values.movement_time
            || values.arrival_time
            || values.departure_time
            || payload.movementTime
            || payload.arrivalTime
            || payload.departureTime
            || '';

        document.getElementById('branchProcessMovementTime').value = movementTime;
        document.getElementById('branchProcessArrivalCount').value = Number(values.arrival || payload.arrivalCount || 0);
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
    bindAutoDatePickers(document);
    wireTripForm(
        '#createTripVehicleId',
        '#createTripFlagsBox'
    );
    wireBranchMovementIncludes(document);
    wireCreateBranchMovementScope();

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'create') && !old('_branch_process_trip_id') && !old('_lodging_trip_form'))
        const addTripModal = document.getElementById('addTripModal');
        if (addTripModal) {
            new bootstrap.Modal(addTripModal).show();
        }
    @endif

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'create') && old('_lodging_trip_form'))
        const addLodgingTripModal = document.getElementById('addLodgingTripModal');
        if (addLodgingTripModal) {
            new bootstrap.Modal(addLodgingTripModal).show();
        }
    @endif

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'index') && old('_branch_process_trip_id'))
        openBranchProcessModal({
            tripId: @json(old('_branch_process_trip_id')),
            contextBranchId: @json((int) old('context_branch_id', $activeBranchId)),
            branchName: @json(optional($branches->firstWhere('id', $activeBranchId))->name ?? ''),
            movementPeriod: @json(old('movement_period', 'day')),
            movementTime: @json(old('movement_time') ?: old('arrival_time') ?: old('departure_time')),
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
