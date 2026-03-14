<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Görev #{{ $vehicleTrip->id }} — Yazdır</title>
    @if($vehicleTrip->locations->isNotEmpty())
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            font-size: 14px;
        }

        /* ── Print toolbar ─────────────────────────────── */
        .toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #1e293b;
            padding: 10px 24px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,.25);
        }
        .toolbar-left { font-size: 13px; color: rgba(255,255,255,.7); }
        .toolbar-left strong { color: #fff; font-size: 15px; margin-right: 8px; }
        .toolbar-btn {
            background: linear-gradient(135deg, #4361ee, #6b7de8);
            color: #fff; border: none; border-radius: 8px;
            padding: 9px 22px; font-size: 13px; font-weight: 600;
            cursor: pointer; letter-spacing: .3px;
            transition: opacity .2s;
        }
        .toolbar-btn:hover { opacity: .88; }

        /* ── Page ──────────────────────────────────────── */
        .print-wrap {
            max-width: 880px;
            margin: 72px auto 48px;
            padding: 0 16px;
        }

        /* ── Header card ───────────────────────────────── */
        .page-header {
            background: linear-gradient(135deg, #1e2a5e 0%, #4361ee 60%, #6b7de8 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 20px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .page-header-left h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .page-header-left p  { font-size: 12px; opacity: .65; }
        .page-header-badge {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            padding: 12px 20px;
            text-align: center;
            position: relative; z-index: 1;
        }
        .page-header-badge .num  { font-size: 28px; font-weight: 800; line-height: 1; }
        .page-header-badge .lbl  { font-size: 11px; opacity: .65; margin-top: 3px; }
        .status-pill {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        /* ── Info grid ─────────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .info-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 13px 16px;
        }
        .info-card.full { grid-column: span 2; }
        .info-label  { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 4px; }
        .info-value  { font-size: 15px; font-weight: 600; color: #1e293b; }

        /* ── Speed stats ───────────────────────────────── */
        .speed-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .speed-card {
            border-radius: 10px;
            padding: 14px 12px;
            text-align: center;
            border: 1px solid;
        }
        .speed-card .sc-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
        .speed-card .sc-val { font-size: 22px; font-weight: 800; line-height: 1; }

        /* ── Map section ───────────────────────────────── */
        .section-title {
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .8px;
            margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }
        #print-map {
            height: 360px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        /* ── Divider ───────────────────────────────────── */
        .divider { border: none; border-top: 1.5px solid #e2e8f0; margin: 28px 0; }

        /* ── Signature area ────────────────────────────── */
        .sig-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            margin-top: 8px;
        }
        .sig-box { text-align: center; }
        .sig-space { height: 64px; }
        .sig-line  { border-top: 2px solid #334155; padding-top: 10px; }
        .sig-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .8px; }
        .sig-name  { font-size: 14px; font-weight: 600; color: #1e293b; margin-top: 4px; }

        /* ── Footer ────────────────────────────────────── */
        .print-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* ── @media print ──────────────────────────────── */
        @media print {
            body      { background: #fff; }
            .toolbar  { display: none; }
            .print-wrap { margin-top: 0; max-width: 100%; padding: 0; }
            .page-header { border-radius: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .info-card, .speed-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            #print-map { height: 300px; }
            .toolbar-btn { display: none; }
        }
    </style>
</head>
<body>

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<div class="toolbar">
    <div class="toolbar-left">
        <strong>Araç Görev Formu #{{ $vehicleTrip->id }}</strong>
        {{ $vehicleTrip->vehicle->plate }} — {{ $vehicleTrip->destination }}
    </div>
    <button class="toolbar-btn" onclick="window.print()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>Yazdır / PDF Kaydet
    </button>
</div>

<div class="print-wrap">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-header-left" style="position:relative;z-index:1">
            <h1>Araç Görev Formu</h1>
            <p>{{ config('app.name') }} &nbsp;·&nbsp; {{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu</p>
            @if($vehicleTrip->status === 'active')
                <span class="status-pill" style="background:rgba(16,185,129,.25);color:#6ee7b7">● Devam Ediyor</span>
            @else
                <span class="status-pill" style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.8)">✓ Tamamlandı</span>
            @endif
        </div>
        <div class="page-header-badge">
            <div class="num">#{{ $vehicleTrip->id }}</div>
            <div class="lbl">Görev No</div>
        </div>
    </div>

    {{-- ── Info grid ────────────────────────────────────────────────────────── --}}
    <div class="section-title">Görev Bilgileri</div>
    <div class="info-grid">
        <div class="info-card">
            <div class="info-label">Araç</div>
            <div class="info-value">{{ $vehicleTrip->vehicle->plate }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">{{ $vehicleTrip->vehicle->brand }} {{ $vehicleTrip->vehicle->model }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Sürücü</div>
            <div class="info-value">{{ $vehicleTrip->user->name ?? '-' }}</div>
        </div>
        <div class="info-card full">
            <div class="info-label">Gidilen Yer / Güzergah</div>
            <div class="info-value">{{ $vehicleTrip->destination }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Başlangıç KM</div>
            <div class="info-value">{{ number_format($vehicleTrip->start_km) }} km</div>
        </div>
        <div class="info-card">
            <div class="info-label">Dönüş KM</div>
            <div class="info-value">{{ $vehicleTrip->end_km ? number_format($vehicleTrip->end_km).' km' : '—' }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Başlangıç Zamanı</div>
            <div class="info-value">{{ $vehicleTrip->started_at->format('d.m.Y H:i') }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Bitiş Zamanı</div>
            <div class="info-value">{{ $vehicleTrip->completed_at?->format('d.m.Y H:i') ?? '—' }}</div>
        </div>
        @if($vehicleTrip->totalKm() !== null)
        <div class="info-card">
            <div class="info-label">Toplam KM (sayaç)</div>
            <div class="info-value" style="color:#4361ee">{{ number_format($vehicleTrip->totalKm()) }} km</div>
        </div>
        @endif
        <div class="info-card">
            <div class="info-label">Konum Noktası</div>
            <div class="info-value">{{ $vehicleTrip->locations->count() }} adet</div>
        </div>
        @if($vehicleTrip->notes)
        <div class="info-card full">
            <div class="info-label">Notlar</div>
            <div class="info-value" style="font-weight:500">{{ $vehicleTrip->notes }}</div>
        </div>
        @endif
    </div>

    {{-- ── Speed stats ──────────────────────────────────────────────────────── --}}
    @if($vehicleTrip->gps_km || $vehicleTrip->avg_speed || $vehicleTrip->min_speed !== null || $vehicleTrip->max_speed !== null)
    <div class="section-title" style="margin-top:20px">Hız & Mesafe İstatistikleri</div>
    <div class="speed-row">
        @if($vehicleTrip->gps_km)
        <div class="speed-card" style="background:#f0f9ff;border-color:#93c5fd;color:#1e40af">
            <div class="sc-lbl">GPS Mesafe</div>
            <div class="sc-val">{{ $vehicleTrip->gps_km }}<span style="font-size:13px;font-weight:500"> km</span></div>
        </div>
        @endif
        @if($vehicleTrip->avg_speed)
        <div class="speed-card" style="background:#f0fdf4;border-color:#86efac;color:#166534">
            <div class="sc-lbl">Ort. Hız</div>
            <div class="sc-val">{{ $vehicleTrip->avg_speed }}<span style="font-size:13px;font-weight:500"> km/h</span></div>
        </div>
        @endif
        @if($vehicleTrip->min_speed !== null)
        <div class="speed-card" style="background:#fefce8;border-color:#fde68a;color:#92400e">
            <div class="sc-lbl">Min. Hız</div>
            <div class="sc-val">{{ $vehicleTrip->min_speed }}<span style="font-size:13px;font-weight:500"> km/h</span></div>
        </div>
        @endif
        @if($vehicleTrip->max_speed !== null)
        <div class="speed-card" style="background:#fff1f2;border-color:#fca5a5;color:#991b1b">
            <div class="sc-lbl">Maks. Hız</div>
            <div class="sc-val">{{ $vehicleTrip->max_speed }}<span style="font-size:13px;font-weight:500"> km/h</span></div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Map ─────────────────────────────────────────────────────────────── --}}
    @if($vehicleTrip->locations->isNotEmpty())
    <div class="section-title" style="margin-top:20px">Güzergah Haritası</div>
    <div id="print-map"></div>
    @endif

    <hr class="divider">

    {{-- ── Signatures ──────────────────────────────────────────────────────── --}}
    <div class="section-title">İmza</div>
    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-space"></div>
            <div class="sig-line">
                <div class="sig-title">Şoför İmza</div>
                <div class="sig-name">{{ $vehicleTrip->user->name ?? '' }}</div>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-space"></div>
            <div class="sig-line">
                <div class="sig-title">Kontrol Eden Müdür İmza</div>
                <div class="sig-name">&nbsp;</div>
            </div>
        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
    <div class="print-footer">
        Bu form <strong>{{ config('app.name') }}</strong> sistemi tarafından otomatik oluşturulmuştur.<br>
        Görev #{{ $vehicleTrip->id }} &nbsp;·&nbsp; {{ now()->format('d.m.Y H:i') }}
    </div>

</div>{{-- .print-wrap --}}

@if($vehicleTrip->locations->isNotEmpty())
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const locations = @json($vehicleTrip->locations->map(fn($l) => [
        'lat'  => (float) $l->lat,
        'lng'  => (float) $l->lng,
        'time' => $l->recorded_at->format('H:i:s'),
        'spd'  => $l->speed,
    ]));

    const map = L.map('print-map');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 18
    }).addTo(map);

    const latlngs  = locations.map(l => [l.lat, l.lng]);
    const polyline = L.polyline(latlngs, { color: '#4361ee', weight: 5, opacity: 0.9 }).addTo(map);
    map.fitBounds(polyline.getBounds(), { padding: [32, 32] });

    if (locations.length > 0) {
        const startIcon = L.divIcon({
            className: '',
            html: '<div style="background:#10b981;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',
            iconAnchor: [7, 7]
        });
        const endIcon = L.divIcon({
            className: '',
            html: '<div style="background:#ef4444;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',
            iconAnchor: [7, 7]
        });

        L.marker(latlngs[0], { icon: startIcon }).addTo(map)
            .bindPopup('🟢 Başlangıç &mdash; ' + locations[0].time);

        if (latlngs.length > 1) {
            L.marker(latlngs[latlngs.length - 1], { icon: endIcon }).addTo(map)
                .bindPopup('🔴 {{ $vehicleTrip->status === "active" ? "Son Konum" : "Bitiş" }} &mdash; ' + locations[locations.length - 1].time);
        }
    }
})();
</script>
@endif

</body>
</html>
