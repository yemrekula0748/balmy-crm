<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Servis Raporu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'dejavu sans', sans-serif; font-size: 10px; color: #2c3e50; background: #fff; }
        .header { background: #1e2d3d; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; letter-spacing: 1px; margin-bottom: 2px; }
        .header p { font-size: 10px; opacity: 0.8; }
        .cards { width: calc(100% - 20px); margin: 0 10px 10px; border-collapse: separate; border-spacing: 8px 0; }
        .cards td { color: #fff; padding: 10px; border-radius: 4px; text-align: center; }
        .cards .value { font-size: 18px; font-weight: bold; }
        .cards .label { font-size: 9px; opacity: 0.8; margin-top: 2px; }
        h2 {
            font-size: 12px;
            color: #1e2d3d;
            border-bottom: 2px solid #1e2d3d;
            padding-bottom: 4px;
            margin: 14px 10px 6px;
        }
        table { width: calc(100% - 20px); margin: 0 10px 10px; border-collapse: collapse; }
        thead th { background: #1e2d3d; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td, tfoot td { padding: 5px 8px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-secondary { background: #6c757d; color: #fff; }
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
        <td style="background:#2a5298">
            <div class="value">{{ number_format($stats['total_arrival']) }}</div>
            <div class="label">Toplam Gelen</div>
        </td>
        <td style="background:#28a745">
            <div class="value">{{ number_format($stats['total_departure']) }}</div>
            <div class="label">Toplam Donen</div>
        </td>
        <td style="background:#495057">
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
            <th class="text-center">Alinan</th>
            <th class="text-center">Indirilen</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branchMovementSummary as $summary)
            <tr>
                <td>{{ $summary['branch']->name }}</td>
                <td class="text-center">{{ $summary['pickup'] }}</td>
                <td class="text-center">{{ $summary['dropoff'] }}</td>
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
                    $pickupSummary = $trip->branchMovements
                        ->where('movement_type', 'pickup')
                        ->map(fn ($movement) => ($movement->branch->name ?? '-') . ' ' . $movement->headcount)
                        ->implode(', ');
                    $dropoffSummary = $trip->branchMovements
                        ->where('movement_type', 'dropoff')
                        ->map(fn ($movement) => ($movement->branch->name ?? '-') . ' ' . $movement->headcount)
                        ->implode(', ');
                @endphp
                <tr>
                    <td>{{ $trip->trip_date->format('d.m.Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $trip->shift }}</span></td>
                    <td>{{ $trip->vehicle->name }}</td>
                    <td>{{ $trip->route->name ?? '-' }}</td>
                    <td class="text-center">
                        {{ $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : '-' }}
                        / <strong>{{ $trip->arrival_count }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $trip->departure_time ? substr($trip->departure_time, 0, 5) : '-' }}
                        / <strong>{{ $trip->departure_count }}</strong>
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
                        @if($pickupSummary)
                            <div><strong>Alinan:</strong> {{ $pickupSummary }}</div>
                        @endif
                        @if($dropoffSummary)
                            <div><strong>Indirilen:</strong> {{ $dropoffSummary }}</div>
                        @endif
                        @if(! $pickupSummary && ! $dropoffSummary)
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
                <td class="text-center">{{ $trips->sum('arrival_count') }}</td>
                <td class="text-center">{{ $trips->sum('departure_count') }}</td>
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
