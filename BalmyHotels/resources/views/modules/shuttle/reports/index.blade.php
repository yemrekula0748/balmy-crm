@extends('layouts.default')
@section('title', 'Servis Raporlari')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Raporlari</h4>
                <span>{{ $from->format('d.m.Y') }} - {{ $to->format('d.m.Y') }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="#">Servis Takip</a></li>
                <li class="breadcrumb-item active">Raporlar</li>
            </ol>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:14px">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('shuttle.reports.index') }}" id="reportForm"
                          class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1 small">Sube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">- Tum Subeler -</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 small">Arac</label>
                            <select name="vehicle_id" class="form-select form-select-sm">
                                <option value="">- Tum Araclar -</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" @selected($vehicleId == $v->id)>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 small">Donem</label>
                            <select name="period" class="form-select form-select-sm" id="periodSelect">
                                <option value="daily" @selected($period === 'daily')>Bugun</option>
                                <option value="weekly" @selected($period === 'weekly')>Bu Hafta</option>
                                <option value="monthly" @selected($period === 'monthly')>Bu Ay</option>
                                <option value="custom" @selected($period === 'custom')>Ozel Aralik</option>
                            </select>
                        </div>
                        <div id="customDateRange" class="{{ $period === 'custom' ? '' : 'd-none' }} col-md-3">
                            <label class="form-label mb-1 small">Tarih Araligi</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
                                <span class="input-group-text">-</span>
                                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                <i class="fas fa-filter me-1"></i> Filtrele
                            </button>
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <a href="{{ route('shuttle.reports.excel', array_filter([
                                'branch_id' => $branchId,
                                'vehicle_id' => $vehicleId,
                                'period' => $period,
                                'from' => $from->toDateString(),
                                'to' => $to->toDateString(),
                            ])) }}"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                            <a href="{{ route('shuttle.reports.pdf', array_filter([
                                'branch_id' => $branchId,
                                'vehicle_id' => $vehicleId,
                                'from' => $from->toDateString(),
                                'to' => $to->toDateString(),
                            ])) }}"
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $cards = [
            ['label' => 'Toplam Gelen', 'value' => number_format($stats['total_arrival']), 'sub' => 'Donem boyunca gelen personel', 'color' => '#c19b77,#a97d57'],
            ['label' => 'Toplam Donen', 'value' => number_format($stats['total_departure']), 'sub' => 'Donem boyunca donen personel', 'color' => '#8f6d4f,#c19b77'],
            ['label' => 'Toplam Sefer', 'value' => number_format($stats['total_trips']), 'sub' => 'Kayitli servis seferi', 'color' => '#7a5c3d,#a97d57'],
            ['label' => 'Aktarimli Sefer', 'value' => $stats['total_transfer_trips'], 'sub' => '%' . $stats['transfer_rate'] . ' oran', 'color' => '#8e5a2b,#d1913c'],
            ['label' => 'Farkli Aracla Gelen', 'value' => $stats['total_different_vehicle_trips'], 'sub' => '%' . $stats['different_vehicle_rate'] . ' oran', 'color' => '#b03a3a,#d98b73'],
            ['label' => 'Lojman Guzergahi', 'value' => $stats['total_lodging_route_trips'], 'sub' => '%' . $stats['lodging_route_rate'] . ' oran', 'color' => '#3d7a5e,#7fb08c'],
            ['label' => 'Ort. Gelis Doluluk', 'value' => $stats['avg_occupancy_arr'] . '%', 'sub' => 'Donus ort.: ' . $stats['avg_occupancy_dep'] . '%', 'color' => '#aa6f39,#c19b77'],
        ];
    @endphp

    <div class="row g-3 mb-3">
        @foreach($cards as $card)
            <div class="col-sm-6 col-lg-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,{{ $card['color'] }});border-radius:14px">
                    <div class="card-body text-white py-3">
                        <div class="fs-3 fw-bold">{{ $card['value'] }}</div>
                        <div class="small fw-semibold mt-2">{{ $card['label'] }}</div>
                        <div class="small opacity-75 mt-1">{{ $card['sub'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2" style="color:#c19b77"></i>Gunluk Sefer Grafigi
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0"><i class="fas fa-clock me-2" style="color:#c19b77"></i>Vardiya Bazli Ozet</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Vardiya</th>
                                <th class="text-center">Sefer</th>
                                <th class="text-center" style="color:#c19b77">Gelis</th>
                                <th class="text-center text-success">Donus</th>
                                <th class="text-center text-warning">Aktarim</th>
                                <th class="text-center" style="color:#2e7d52">Lojman</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byShift as $shiftName => $data)
                                @if($data['count'] > 0)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $shiftName }}</span></td>
                                        <td class="text-center">{{ $data['count'] }}</td>
                                        <td class="text-center fw-bold" style="color:#c19b77">{{ $data['arrival'] }}</td>
                                        <td class="text-center fw-bold text-success">{{ $data['departure'] }}</td>
                                        <td class="text-center">
                                            <div class="fw-bold text-warning">{{ $data['transfer'] }}</div>
                                            <small class="text-muted">%{{ $data['transfer_rate'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold" style="color:#2e7d52">{{ $data['lodging_route'] }}</div>
                                            <small class="text-muted">%{{ $data['lodging_route_rate'] }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">TOPLAM</td>
                                <td class="text-center fw-bold">{{ $stats['total_trips'] }}</td>
                                <td class="text-center fw-bold" style="color:#c19b77">{{ $stats['total_arrival'] }}</td>
                                <td class="text-center fw-bold text-success">{{ $stats['total_departure'] }}</td>
                                <td class="text-center fw-bold text-warning">{{ $stats['total_transfer_trips'] }}</td>
                                <td class="text-center fw-bold" style="color:#2e7d52">{{ $stats['total_lodging_route_trips'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0"><i class="fas fa-bus me-2" style="color:#c19b77"></i>Arac Bazli Ozet</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Arac</th>
                                    <th class="text-center">Sefer</th>
                                    <th class="text-center" style="color:#c19b77">Gelis</th>
                                    <th class="text-center text-success">Donus</th>
                                    <th class="text-center">Aktarim</th>
                                    <th class="text-center">Farkli Arac</th>
                                    <th class="text-center">Lojman</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byVehicle as $data)
                                    @if($data['trips'] > 0)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $data['vehicle']->name }}</div>
                                                @if($data['vehicle']->plate)
                                                    <small class="text-muted">{{ $data['vehicle']->plate }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $data['trips'] }}</td>
                                            <td class="text-center fw-bold" style="color:#c19b77">
                                                {{ $data['arrival'] }}
                                                <small class="d-block text-muted">%{{ $data['occupancy_arr'] }}</small>
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                {{ $data['departure'] }}
                                                <small class="d-block text-muted">%{{ $data['occupancy_dep'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold text-warning">{{ $data['transfer'] }}</div>
                                                <small class="text-muted">%{{ $data['transfer_rate'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold text-danger">{{ $data['different_vehicle'] }}</div>
                                                <small class="text-muted">%{{ $data['different_vehicle_rate'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold" style="color:#2e7d52">{{ $data['lodging_route'] }}</div>
                                                <small class="text-muted">%{{ $data['lodging_route_rate'] }}</small>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(empty(array_filter($byVehicle, fn($data) => $data['trips'] > 0)))
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">Veri bulunamadi.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2" style="color:#c19b77"></i>Sube Hareket Ozetleri</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sube</th>
                                <th class="text-center">Gelen</th>
                                <th class="text-center">Giden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchMovementSummary as $summary)
                                <tr>
                                    <td class="fw-semibold">{{ $summary['branch']->name }}</td>
                                    <td class="text-center fw-bold" style="color:#c19b77">{{ $summary['arrival'] }}</td>
                                    <td class="text-center fw-bold text-success">{{ $summary['departure'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2" style="color:#c19b77"></i>Operasyon Sapma Ozeti</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="rounded-3 p-3 h-100" style="background:#fff7e7;border:1px solid #f1ddb2">
                                <div class="small text-uppercase fw-semibold text-muted mb-1">Aktarim Yapilan</div>
                                <div class="fs-3 fw-bold" style="color:#9b6a11">{{ $stats['total_transfer_trips'] }}</div>
                                <div class="small text-muted mt-2">Toplam seferin %{{ $stats['transfer_rate'] }} kadari</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="rounded-3 p-3 h-100" style="background:#fff1f1;border:1px solid #f0d0d0">
                                <div class="small text-uppercase fw-semibold text-muted mb-1">Farkli Aracla Gelen</div>
                                <div class="fs-3 fw-bold" style="color:#b03a3a">{{ $stats['total_different_vehicle_trips'] }}</div>
                                <div class="small text-muted mt-2">Toplam seferin %{{ $stats['different_vehicle_rate'] }} kadari</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="rounded-3 p-3 h-100" style="background:#eef6f2;border:1px solid #cfe3d8">
                                <div class="small text-uppercase fw-semibold text-muted mb-1">Lojman Guzergahi</div>
                                <div class="fs-3 fw-bold" style="color:#2e7d52">{{ $stats['total_lodging_route_trips'] }}</div>
                                <div class="small text-muted mt-2">Toplam seferin %{{ $stats['lodging_route_rate'] }} kadari</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="rounded-3 p-3 h-100" style="background:#f9f4ed;border:1px solid #eadcc9">
                                <div class="small text-uppercase fw-semibold text-muted mb-1">Toplam Istisna</div>
                                <div class="fs-3 fw-bold" style="color:#7a5c3d">{{ $stats['exception_trips'] }}</div>
                                <div class="small text-muted mt-2">Toplam seferin %{{ $stats['exception_rate'] }} kadari</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Baslik</th>
                                    <th class="text-center">Sayi</th>
                                    <th class="text-center">Oran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aktarim yapilan sefer</td>
                                    <td class="text-center fw-bold">{{ $stats['total_transfer_trips'] }}</td>
                                    <td class="text-center">%{{ $stats['transfer_rate'] }}</td>
                                </tr>
                                <tr>
                                    <td>Farkli aracla gelen sefer</td>
                                    <td class="text-center fw-bold">{{ $stats['total_different_vehicle_trips'] }}</td>
                                    <td class="text-center">%{{ $stats['different_vehicle_rate'] }}</td>
                                </tr>
                                <tr>
                                    <td>Lojman guzergahi yapilan sefer</td>
                                    <td class="text-center fw-bold">{{ $stats['total_lodging_route_trips'] }}</td>
                                    <td class="text-center">%{{ $stats['lodging_route_rate'] }}</td>
                                </tr>
                                <tr>
                                    <td>Herhangi bir istisna olan sefer</td>
                                    <td class="text-center fw-bold">{{ $stats['exception_trips'] }}</td>
                                    <td class="text-center">%{{ $stats['exception_rate'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:14px">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0"><i class="fas fa-list me-2" style="color:#c19b77"></i>Servis Bazli Hareket Detayi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarih</th>
                                    <th>Vardiya</th>
                                    <th>Arac / Plaka</th>
                                    <th>Guzergah</th>
                                    <th class="text-center">Ilk Gelis</th>
                                    <th class="text-center">Son Cikis</th>
                                    <th class="text-center">Toplam Indi</th>
                                    <th class="text-center">Toplam Bindi</th>
                                    <th>Otel Hareketleri</th>
                                    <th>Durum</th>
                                    <th>Not</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips as $trip)
                                    @php
                                        $movementRows = $trip->branchMovements
                                            ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true))
                                            ->groupBy(fn ($movement) => $movement->movement_period ?? \App\Models\ShuttleTripBranchMovement::DEFAULT_PERIOD)
                                            ->map(function ($periodItems, $periodKey) {
                                                return [
                                                    'period_label' => \App\Models\ShuttleTripBranchMovement::PERIODS[$periodKey] ?? $periodKey,
                                                    'branches' => $periodItems
                                                        ->groupBy('branch_id')
                                                        ->map(function ($items) {
                                                            $arrivalMovement = $items->firstWhere('movement_type', 'arrival');
                                                            $departureMovement = $items->firstWhere('movement_type', 'departure');

                                                            return [
                                                                'branch_name' => optional(optional($arrivalMovement)->branch ?? optional($departureMovement)->branch)->name ?? '-',
                                                                'arrival_time' => optional($arrivalMovement)->movement_time ? substr($arrivalMovement->movement_time, 0, 5) : null,
                                                                'arrival_count' => (int) optional($arrivalMovement)->headcount,
                                                                'departure_time' => optional($departureMovement)->movement_time ? substr($departureMovement->movement_time, 0, 5) : null,
                                                                'departure_count' => (int) optional($departureMovement)->headcount,
                                                            ];
                                                        })
                                                        ->filter(fn ($row) => $row['arrival_count'] > 0
                                                            || $row['departure_count'] > 0
                                                            || ! empty($row['arrival_time'])
                                                            || ! empty($row['departure_time']))
                                                        ->values(),
                                                ];
                                            })
                                            ->filter(fn ($periodRow) => $periodRow['branches']->isNotEmpty())
                                            ->values();

                                        $arrivalTimes = $movementRows->flatMap(fn ($periodRow) => $periodRow['branches']->pluck('arrival_time'))->filter()->sort()->values();
                                        $departureTimes = $movementRows->flatMap(fn ($periodRow) => $periodRow['branches']->pluck('departure_time'))->filter()->sort()->values();
                                        $tripArrivalCount = (int) $movementRows->sum(fn ($periodRow) => $periodRow['branches']->sum('arrival_count'));
                                        $tripDepartureCount = (int) $movementRows->sum(fn ($periodRow) => $periodRow['branches']->sum('departure_count'));
                                        $tripArrivalTime = $arrivalTimes->first() ?: ($trip->arrival_time ? substr($trip->arrival_time, 0, 5) : null);
                                        $tripDepartureTime = $departureTimes->last() ?: ($trip->departure_time ? substr($trip->departure_time, 0, 5) : null);
                                    @endphp
                                    <tr>
                                        <td>{{ $trip->trip_date->format('d.m.Y') }}</td>
                                        <td><span class="badge bg-secondary">{{ $trip->shift }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $trip->vehicle->name ?? '-' }}</div>
                                            @if($trip->vehicle?->plate)
                                                <small class="text-muted">{{ $trip->vehicle->plate }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $trip->route->name ?? (collect($trip->vehicle?->routes ?? [])->pluck('name')->implode(', ') ?: '-') }}</td>
                                        <td class="text-center fw-semibold">{{ $tripArrivalTime ?: '-' }}</td>
                                        <td class="text-center fw-semibold">{{ $tripDepartureTime ?: '-' }}</td>
                                        <td class="text-center fw-bold" style="color:#c19b77">{{ $tripArrivalCount }}</td>
                                        <td class="text-center fw-bold text-success">{{ $tripDepartureCount }}</td>
                                        <td style="min-width:290px">
                                            @if($movementRows->isEmpty())
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($movementRows as $periodRow)
                                                        <div class="rounded-3 p-2" style="background:#fbf6ef;border:1px solid #eadcc9">
                                                            <div class="fw-semibold small mb-2" style="color:#8f6d4f">{{ $periodRow['period_label'] }}</div>
                                                            <div class="d-flex flex-column gap-2">
                                                                @foreach($periodRow['branches'] as $movementRow)
                                                                    <div class="rounded-3 p-2" style="background:#fff;border:1px solid #eadcc9">
                                                                        <div class="fw-semibold text-dark small mb-1">{{ $movementRow['branch_name'] }}</div>
                                                                        <div class="small text-muted d-flex gap-3 flex-wrap">
                                                                            <span><strong>Geldi:</strong> {{ $movementRow['arrival_time'] ?: '-' }}</span>
                                                                            <span><strong>Indi:</strong> {{ $movementRow['arrival_count'] }}</span>
                                                                        </div>
                                                                        <div class="small text-muted d-flex gap-3 flex-wrap mt-1">
                                                                            <span><strong>Cikti:</strong> {{ $movementRow['departure_time'] ?: '-' }}</span>
                                                                            <span><strong>Bindi:</strong> {{ $movementRow['departure_count'] }}</span>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($trip->arrived_with_different_vehicle)
                                                    <span class="badge" style="background:#fff1f1;color:#b03a3a;border:1px solid #f0d0d0">Farkli arac</span>
                                                @endif
                                                @if($trip->is_transfer)
                                                    <span class="badge" style="background:#fff7e7;color:#9b6a11;border:1px solid #f1ddb2">Aktarim</span>
                                                @endif
                                                @if($trip->is_lodging_route)
                                                    <span class="badge" style="background:#eef6f2;color:#2e7d52;border:1px solid #cfe3d8">Lojman guzergahi</span>
                                                @endif
                                                @if(! $trip->arrived_with_different_vehicle && ! $trip->is_transfer && ! $trip->is_lodging_route)
                                                    <span class="text-muted">Normal</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $trip->notes ? \Illuminate\Support\Str::limit($trip->notes, 60) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">Detay servis kaydi bulunamadi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    document.getElementById('customDateRange').classList.toggle('d-none', this.value !== 'custom');
});

const ctx = document.getElementById('dailyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: 'Gelen Personel',
                data: @json($chartData['arrival']),
                borderColor: '#c19b77',
                backgroundColor: 'rgba(193,155,119,0.15)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            },
            {
                label: 'Donen Personel',
                data: @json($chartData['departure']),
                borderColor: '#8f6d4f',
                backgroundColor: 'rgba(143,109,79,0.15)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>
@endpush
