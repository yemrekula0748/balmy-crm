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
        .header p  { font-size: 10px; opacity: 0.8; }
        .meta-row { display: flex; gap: 20px; margin-bottom: 14px; padding: 0 10px; }
        .meta-item { background: #f1f3f5; border-radius: 4px; padding: 8px 14px; flex: 1; text-align: center; }
        .meta-item .val  { font-size: 18px; font-weight: bold; color: #1e2d3d; }
        .meta-item .lbl  { font-size: 9px; color: #6c757d; margin-top: 2px; }
        h2 { font-size: 12px; color: #1e2d3d; border-bottom: 2px solid #1e2d3d; padding-bottom: 4px;
             margin: 14px 10px 6px; }
        table { width: calc(100% - 20px); margin: 0 10px; border-collapse: collapse; margin-bottom: 10px; }
        thead th { background: #1e2d3d; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-primary { background: #2a5298; color: #fff; }
        .badge-success { background: #28a745; color: #fff; }
        .badge-secondary { background: #6c757d; color: #fff; }
        .tfoot-row td { background: #e9ecef; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center;
                  font-size: 8px; color: #999; padding: 6px; border-top: 1px solid #dee2e6; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

<div class="header">
    <h1>SERVİS RAPORU</h1>
    <p>
        Dönem: {{ $from->format('d.m.Y') }} — {{ $to->format('d.m.Y') }}
        &nbsp;|&nbsp;
        {{ $branchFilter ? 'Şube: ' . $branchFilter->name : 'Tüm Şubeler' }}
        &nbsp;|&nbsp;
        Oluşturan: {{ $user->name }}
        &nbsp;|&nbsp;
        Tarih: {{ now()->format('d.m.Y H:i') }}
    </p>
</div>

{{-- Özet Satırı --}}
<table>
    <tr>
        <td style="width:16%;text-align:center;background:#2a5298;color:#fff;padding:10px;border-radius:4px">
            <div style="font-size:20px;font-weight:bold">{{ number_format($stats['total_arrival']) }}</div>
            <div style="font-size:9px;opacity:.8">Toplam Gelen</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:16%;text-align:center;background:#28a745;color:#fff;padding:10px;border-radius:4px">
            <div style="font-size:20px;font-weight:bold">{{ number_format($stats['total_departure']) }}</div>
            <div style="font-size:9px;opacity:.8">Toplam Dönen</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:16%;text-align:center;background:#495057;color:#fff;padding:10px;border-radius:4px">
            <div style="font-size:20px;font-weight:bold">{{ number_format($stats['total_trips']) }}</div>
            <div style="font-size:9px;opacity:.8">Toplam Sefer</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:16%;text-align:center;background:#6f42c1;color:#fff;padding:10px;border-radius:4px">
            <div style="font-size:20px;font-weight:bold">{{ $stats['avg_daily_arrival'] }}</div>
            <div style="font-size:9px;opacity:.8">Günlük Ort. Geliş</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:16%;text-align:center;background:#fd7e14;color:#fff;padding:10px;border-radius:4px">
            <div style="font-size:20px;font-weight:bold">{{ $stats['avg_daily_departure'] }}</div>
            <div style="font-size:9px;opacity:.8">Günlük Ort. Dönüş</div>
        </td>
    </tr>
</table>

{{-- Vardiya Özeti --}}
<h2>Vardiya Bazlı Özet</h2>
<table>
    <thead>
        <tr>
            <th>Vardiya</th>
            <th class="text-center">Sefer Sayısı</th>
            <th class="text-center">Gelen Personel</th>
            <th class="text-center">Dönen Personel</th>
            <th class="text-center">Ortalama Geliş</th>
            <th class="text-center">Ortalama Dönüş</th>
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
            <td class="text-center">{{ $data['count'] > 0 ? round($data['arrival'] / $data['count'], 1) : 0 }}</td>
            <td class="text-center">{{ $data['count'] > 0 ? round($data['departure'] / $data['count'], 1) : 0 }}</td>
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
            <td class="text-center">{{ $stats['avg_daily_arrival'] }}</td>
            <td class="text-center">{{ $stats['avg_daily_departure'] }}</td>
        </tr>
    </tfoot>
</table>

{{-- Araç Bazlı Özet --}}
<h2>Araç Bazlı Özet</h2>
<table>
    <thead>
        <tr>
            <th>Araç</th>
            <th>Plaka</th>
            <th class="text-center">Sefer</th>
            <th class="text-center">Gelen</th>
            <th class="text-center">Dönen</th>
            <th class="text-center">Doluluk % (Geliş)</th>
            <th class="text-center">Doluluk % (Dönüş)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($byVehicle as $data)
        @if($data['trips'] > 0)
        <tr>
            <td>{{ $data['vehicle']->name }}</td>
            <td>{{ $data['vehicle']->plate ?: '—' }}</td>
            <td class="text-center">{{ $data['trips'] }}</td>
            <td class="text-center">{{ $data['arrival'] }}</td>
            <td class="text-center">{{ $data['departure'] }}</td>
            <td class="text-center">{{ $data['occupancy_arr'] }}%</td>
            <td class="text-center">{{ $data['occupancy_dep'] }}%</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>

{{-- Tüm Seferler --}}
@if($trips->count() <= 200)
<h2>Sefer Detayları</h2>
<table>
    <thead>
        <tr>
            <th>Tarih</th>
            <th>Vardiya</th>
            <th>Araç</th>
            <th>Güzergah</th>
            <th class="text-center">Geliş Saat</th>
            <th class="text-center">Gelen</th>
            <th class="text-center">Dönüş Saat</th>
            <th class="text-center">Dönen</th>
            <th>Not</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trips as $t)
        <tr>
            <td>{{ $t->trip_date->format('d.m.Y') }}</td>
            <td><span class="badge badge-secondary">{{ $t->shift }}</span></td>
            <td>{{ $t->vehicle->name }}</td>
            <td>{{ $t->route->name ?? '—' }}</td>
            <td class="text-center">{{ $t->arrival_time ? substr($t->arrival_time, 0, 5) : '—' }}</td>
            <td class="text-center"><strong>{{ $t->arrival_count }}</strong></td>
            <td class="text-center">{{ $t->departure_time ? substr($t->departure_time, 0, 5) : '—' }}</td>
            <td class="text-center"><strong>{{ $t->departure_count }}</strong></td>
            <td style="font-size:9px">{{ $t->notes ? \Str::limit($t->notes, 50) : '' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tfoot-row">
            <td colspan="5" class="text-right">TOPLAM</td>
            <td class="text-center">{{ $trips->sum('arrival_count') }}</td>
            <td></td>
            <td class="text-center">{{ $trips->sum('departure_count') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@else
<div style="margin:10px;padding:10px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;font-size:10px">
    <strong>Not:</strong> {{ $trips->count() }} sefer kaydı bulunmaktadır. Detay tablosu sayfa sınırı nedeniyle gösterilmemiştir.
</div>
@endif

<div class="footer">
    BalmyCRM — Servis Takip Modülü &nbsp;|&nbsp; Oluşturma: {{ now()->format('d.m.Y H:i') }}
</div>

</body>
</html>
