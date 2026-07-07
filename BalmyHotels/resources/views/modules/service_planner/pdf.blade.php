<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>{{ $plan->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #2f2f2f; font-size: 12px; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #c19b77; padding-bottom: 14px; }
        .title { font-size: 22px; font-weight: bold; color: #7a5c3d; margin-bottom: 6px; }
        .muted { color: #6b7280; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .meta-table td { padding: 6px 8px; border: 1px solid #eadcc9; }
        .meta-label { width: 22%; background: #fbf6ef; font-weight: bold; }
        .route-card { margin-top: 20px; border: 1px solid #eadcc9; border-radius: 10px; }
        .route-head { background: #fbf6ef; padding: 10px 12px; border-bottom: 1px solid #eadcc9; }
        .route-title { font-weight: bold; font-size: 15px; }
        .stats { margin-top: 4px; font-size: 11px; color: #6b7280; }
        table.route-table { width: 100%; border-collapse: collapse; }
        table.route-table th, table.route-table td { border: 1px solid #eeeeee; padding: 7px 8px; vertical-align: top; }
        table.route-table th { background: #f8fafc; font-weight: bold; }
        .small { font-size: 10px; }
        a { color: #3b82f6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Servis Planlayici Ciktisi</div>
        <div class="muted">{{ $plan->name }}</div>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Sube</td>
                <td>{{ $plan->branch?->name ?? 'Genel / Ortak' }}</td>
                <td class="meta-label">Tarih</td>
                <td>{{ $plan->plan_date?->format('d.m.Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Kalkis Noktasi</td>
                <td>{{ $plan->start_location_name }}</td>
                <td class="meta-label">Toplam Servis</td>
                <td>{{ $plan->vehicles->count() }}</td>
            </tr>
            <tr>
                <td class="meta-label">Kalkis Adresi</td>
                <td>{{ $plan->start_address }}</td>
                <td class="meta-label">Toplam Kisi</td>
                <td>{{ $plan->stops->count() }}</td>
            </tr>
        </table>
    </div>

    @foreach($routes as $route)
        <div class="route-card">
            <div class="route-head">
                <div class="route-title">{{ $route['vehicle']->name }}</div>
                <div class="stats">
                    {{ $route['assignments']->count() }}/{{ $route['vehicle']->seat_capacity }} dolu
                    | {{ number_format($route['total_distance_km'], 1, ',', '.') }} km
                    | {{ $route['estimated_minutes'] }} dk
                    @if($route['maps_url'])
                        | Harita: {{ $route['maps_url'] }}
                    @endif
                </div>
            </div>

            <table class="route-table">
                <thead>
                    <tr>
                        <th style="width:7%;">Sira</th>
                        <th style="width:18%;">Personel</th>
                        <th>Adres</th>
                        <th style="width:12%;">Ilce</th>
                        <th style="width:10%;">Mesafe</th>
                        <th style="width:10%;">Sure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($route['assignments'] as $assignment)
                        <tr>
                            <td>{{ $assignment->stop_order }}</td>
                            <td>{{ $assignment->stop?->passenger_name }}</td>
                            <td>
                                {{ $assignment->stop?->address }}
                                @if($assignment->stop?->notes)
                                    <div class="small muted">{{ $assignment->stop->notes }}</div>
                                @endif
                            </td>
                            <td>{{ $assignment->stop?->district ?: '-' }}</td>
                            <td>{{ number_format((float) $assignment->leg_distance_km, 1, ',', '.') }} km</td>
                            <td>{{ $assignment->travel_minutes }} dk</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Bu servis icin atama bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
