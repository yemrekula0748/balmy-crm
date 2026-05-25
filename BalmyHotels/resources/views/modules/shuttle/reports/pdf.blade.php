<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Servis Raporu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'dejavu sans', sans-serif; font-size: 10px; color: #2f241c; background: #fff; }
        .header { background: #c19b77; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; letter-spacing: 1px; margin-bottom: 2px; }
        .header p { font-size: 10px; opacity: 0.8; }
        .cards { width: calc(100% - 20px); margin: 0 10px 10px; border-collapse: separate; border-spacing: 8px 0; }
        .cards td { color: #fff; padding: 10px; border-radius: 4px; text-align: center; }
        .cards .value { font-size: 18px; font-weight: bold; }
        .cards .label { font-size: 9px; opacity: 0.8; margin-top: 2px; }
        h2 {
            font-size: 12px;
            color: #8f6d4f;
            border-bottom: 2px solid #c19b77;
            padding-bottom: 4px;
            margin: 14px 10px 6px;
        }
        table { width: calc(100% - 20px); margin: 0 10px 10px; border-collapse: collapse; }
        thead th { background: #c19b77; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td, tfoot td { padding: 5px 8px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-secondary { background: #7a5c3d; color: #fff; }
        .badge-alert { background: #fff1f1; color: #b03a3a; }
        .badge-transfer { background: #fff7e7; color: #9b6a11; }
        .tfoot-row td { background: #e9ecef; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding: 6px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>SERVIS RAPORU</h1>
    <p>
        Donem: {{ $from->format('d.m.Y') }} - {{ $to->format('d.m.Y') }}
        &nbsp;|&nbsp;
        {{ $branchFilter ? 'Sube: ' . $branchFilter->name : 'Tum Subeler' }}
        &nbsp;|&nbsp;
        {{ $vehicleFilter ? 'Arac: ' . $vehicleFilter->name : 'Tum Araclar' }}
        &nbsp;|&nbsp;
        Olusturan: {{ $user->name }}
        &nbsp;|&nbsp;
        Tarih: {{ now()->format('d.m.Y H:i') }}
    </p>
</div>

<table class="cards">
    <tr>
        <td style="background:#c19b77">
            <div class="value">{{ number_format($stats['total_arrival']) }}</div>
            <div class="label">Toplam Gelen</div>
        </td>
        <td style="background:#8f6d4f">
            <div class="value">{{ number_format($stats['total_departure']) }}</div>
            <div class="label">Toplam Donen</div>
        </td>
        <td style="background:#7a5c3d">
            <div class="value">{{ number_format($stats['total_trips']) }}</div>
            <div class="label">Toplam Sefer</div>
        </td>
        <td style="background:#d1913c">
            <div class="value">{{ $stats['total_transfer_trips'] }}</div>
            <div class="label">Aktarimli Sefer (%{{ $stats['transfer_rate'] }})</div>
        </td>
        <td style="background:#c45c5c">
            <div class="value">{{ $stats['total_different_vehicle_trips'] }}</div>
            <div class="label">Farkli Arac (%{{ $stats['different_vehicle_rate'] }})</div>
        </td>
        <td style="background:#7a5c3d">
            <div class="value">{{ $stats['avg_occupancy_arr'] }}%</div>
            <div class="label">Ort. Gelis Doluluk</div>
        </td>
    </tr>
</table>

<h2>Vardiya Bazli Ozet</h2>
<table>
    <thead>
        <tr>
            <th>Vardiya</th>
            <th class="text-center">Sefer</th>
            <th class="text-center">Gelen</th>
            <th class="text-center">Donen</th>
            <th class="text-center">Aktarim</th>
            <th class="text-center">Farkli Arac</th>
        </tr>
    </thead>
    <tbody>
        @foreach($byShift as $shiftName => $data)
            @if($data['count'] > 0)
                <tr>
                    <td><span class="badge badge-secondary">{{ $shiftName }}</span></td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-center"><strong>{{ $data['arrival'] }}</strong></td>
                    <td class="text-center"><strong>{{ $data['departure'] }}</strong></td>
                    <td class="text-center">{{ $data['transfer'] }} (%{{ $data['transfer_rate'] }})</td>
                    <td class="text-center">{{ $data['different_vehicle'] }} (%{{ $data['different_vehicle_rate'] }})</td>
                </tr>
            @endif
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tfoot-row">
            <td>TOPLAM</td>
            <td class="text-center">{{ $stats['total_trips'] }}</td>
            <td class="text-center">{{ $stats['total_arrival'] }}</td>
            <td class="text-center">{{ $stats['total_departure'] }}</td>
            <td class="text-center">{{ $stats['total_transfer_trips'] }}</td>
            <td class="text-center">{{ $stats['total_different_vehicle_trips'] }}</td>
        </tr>
    </tfoot>
</table>

<h2>Arac Bazli Ozet</h2>
<table>
    <thead>
        <tr>
            <th>Arac</th>
            <th>Plaka</th>
            <th class="text-center">Sefer</th>
            <th class="text-center">Gelen</th>
            <th class="text-center">Donen</th>
            <th class="text-center">Aktarim</th>
            <th class="text-center">Farkli Arac</th>
        </tr>
    </thead>
    <tbody>
        @foreach($byVehicle as $data)
            @if($data['trips'] > 0)
                <tr>
                    <td>{{ $data['vehicle']->name }}</td>
                    <td>{{ $data['vehicle']->plate ?: '-' }}</td>
                    <td class="text-center">{{ $data['trips'] }}</td>
                    <td class="text-center">{{ $data['arrival'] }} (%{{ $data['occupancy_arr'] }})</td>
                    <td class="text-center">{{ $data['departure'] }} (%{{ $data['occupancy_dep'] }})</td>
                    <td class="text-center">{{ $data['transfer'] }}</td>
                    <td class="text-center">{{ $data['different_vehicle'] }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

<h2>Sube Hareket Ozetleri</h2>
<table>
    <thead>
        <tr>
            <th>Sube</th>
            <th class="text-center">Gelen</th>
            <th class="text-center">Giden</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branchMovementSummary as $summary)
            <tr>
                <td>{{ $summary['branch']->name }}</td>
                <td class="text-center">{{ $summary['arrival'] }}</td>
                <td class="text-center">{{ $summary['departure'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if($trips->count() <= 200)
    <h2>Sefer Detaylari</h2>
    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Vardiya</th>
                <th>Arac</th>
                <th>Guzergah</th>
                <th class="text-center">Gelis</th>
                <th class="text-center">Donus</th>
                <th>Durum</th>
                <th>Sube Hareketi</th>
                <th>Not</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trips as $trip)
                @php
                    $filteredArrivalCount = (int) $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true) && $movement->movement_type === 'arrival')
                        ->sum('headcount');
                    $filteredDepartureCount = (int) $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true) && $movement->movement_type === 'departure')
                        ->sum('headcount');
                    $filteredArrivalTime = $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true) && $movement->movement_type === 'arrival' && ! empty($movement->movement_time))
                        ->pluck('movement_time')
                        ->map(fn ($time) => substr($time, 0, 5))
                        ->sort()
                        ->first();
                    $filteredDepartureTime = $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true) && $movement->movement_type === 'departure' && ! empty($movement->movement_time))
                        ->pluck('movement_time')
                        ->map(fn ($time) => substr($time, 0, 5))
                        ->sort()
                        ->last();

                    if ($trip->branchMovements->isEmpty() && in_array((int) $trip->branch_id, $reportBranchIds, true)) {
                        $filteredArrivalCount = (int) $trip->arrival_count;
                        $filteredDepartureCount = (int) $trip->departure_count;
                        $filteredArrivalTime = $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : null;
                        $filteredDepartureTime = $trip->departure_time ? substr($trip->departure_time, 0, 5) : null;
                    }

                    $arrivalSummary = $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true)
                            && ((int) $movement->headcount > 0 || ! empty($movement->movement_time)))
                        ->where('movement_type', 'arrival')
                        ->map(fn ($movement) => '[' . $movement->period_label . '] ' . ($movement->branch->name ?? '-') . ' ' . ($movement->movement_time ? substr($movement->movement_time, 0, 5) . ' / ' : '') . $movement->headcount)
                        ->implode(', ');
                    $departureSummary = $trip->branchMovements
                        ->filter(fn ($movement) => in_array((int) $movement->branch_id, $reportBranchIds, true)
                            && ((int) $movement->headcount > 0 || ! empty($movement->movement_time)))
                        ->where('movement_type', 'departure')
                        ->map(fn ($movement) => '[' . $movement->period_label . '] ' . ($movement->branch->name ?? '-') . ' ' . ($movement->movement_time ? substr($movement->movement_time, 0, 5) . ' / ' : '') . $movement->headcount)
                        ->implode(', ');
                    if ($arrivalSummary === '' && $filteredArrivalCount > 0) {
                        $arrivalSummary = ($trip->branch->name ?? '-') . ' ' . ($filteredArrivalTime ? $filteredArrivalTime . ' / ' : '') . $filteredArrivalCount;
                    }
                    if ($departureSummary === '' && $filteredDepartureCount > 0) {
                        $departureSummary = ($trip->branch->name ?? '-') . ' ' . ($filteredDepartureTime ? $filteredDepartureTime . ' / ' : '') . $filteredDepartureCount;
                    }
                @endphp
                <tr>
                    <td>{{ $trip->trip_date->format('d.m.Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $trip->shift }}</span></td>
                    <td>{{ $trip->vehicle->name }}</td>
                    <td>{{ $trip->route->name ?? '-' }}</td>
                    <td class="text-center">
                        {{ $filteredArrivalTime ?: '-' }}
                        / <strong>{{ $filteredArrivalCount }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $filteredDepartureTime ?: '-' }}
                        / <strong>{{ $filteredDepartureCount }}</strong>
                    </td>
                    <td>
                        @if($trip->arrived_with_different_vehicle)
                            <span class="badge badge-alert">Farkli arac</span>
                        @endif
                        @if($trip->is_transfer)
                            <span class="badge badge-transfer">Aktarim</span>
                        @endif
                        @if(! $trip->arrived_with_different_vehicle && ! $trip->is_transfer)
                            -
                        @endif
                    </td>
                    <td style="font-size:9px">
                        @if($arrivalSummary)
                            <div><strong>Gelen:</strong> {{ $arrivalSummary }}</div>
                        @endif
                        @if($departureSummary)
                            <div><strong>Giden:</strong> {{ $departureSummary }}</div>
                        @endif
                        @if(! $arrivalSummary && ! $departureSummary)
                            -
                        @endif
                    </td>
                    <td style="font-size:9px">{{ $trip->notes ? \Illuminate\Support\Str::limit($trip->notes, 50) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="4" class="text-right">TOPLAM</td>
                <td class="text-center">{{ $stats['total_arrival'] }}</td>
                <td class="text-center">{{ $stats['total_departure'] }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
@else
    <div style="margin:10px;padding:10px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;font-size:10px">
        <strong>Not:</strong> {{ $trips->count() }} sefer kaydi bulundu. Detay tablosu sayfa siniri nedeniyle gosterilmedi.
    </div>
@endif

<div class="footer">
    BalmyCRM - Servis Takip Modulu | Olusturma: {{ now()->format('d.m.Y H:i') }}
</div>

</body>
</html>
