@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Görev Detayı #{{ $vehicleTrip->id }}</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle-trips.index') }}">Araç Görevleri</a></li>
                <li class="breadcrumb-item active">#{{ $vehicleTrip->id }}</li>
            </ol>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-5">
            {{-- Bilgi kartı --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Görev Bilgileri</h5>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('vehicle-trips.print', $vehicleTrip) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary"
                           style="border-radius:8px;font-size:12px">
                            <i class="fas fa-print me-1"></i>Görevi Yazdır
                        </a>
                        @if($vehicleTrip->status === 'active')
                            <span class="badge bg-success pulse-badge">● Devam Ediyor</span>
                        @else
                            <span class="badge bg-secondary">Tamamlandı</span>
                        @endif
                    </div>
                </div>                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="text-muted ps-3" style="width:40%">Araç</td><td><strong>{{ $vehicleTrip->vehicle->plate }}</strong> — {{ $vehicleTrip->vehicle->brand }} {{ $vehicleTrip->vehicle->model }}</td></tr>
                            <tr><td class="text-muted ps-3">Sürücü</td><td>{{ $vehicleTrip->user->name ?? '-' }}</td></tr>
                            <tr><td class="text-muted ps-3">Gidilen Yer</td><td>{{ $vehicleTrip->destination }}</td></tr>
                            <tr><td class="text-muted ps-3">Başlangıç KM</td><td>{{ number_format($vehicleTrip->start_km) }} km</td></tr>
                            <tr><td class="text-muted ps-3">Dönüş KM</td><td>{{ $vehicleTrip->end_km ? number_format($vehicleTrip->end_km).' km' : '—' }}</td></tr>
                            @if($vehicleTrip->totalKm() !== null)
                            <tr><td class="text-muted ps-3">Toplam KM</td><td><strong class="text-primary">{{ number_format($vehicleTrip->totalKm()) }} km</strong></td></tr>
                            @endif
                            @if($vehicleTrip->gps_km !== null)
                            <tr><td class="text-muted ps-3">GPS Mesafe</td><td>{{ $vehicleTrip->gps_km }} km</td></tr>
                            @endif
                            @if($vehicleTrip->avg_speed !== null)
                            <tr><td class="text-muted ps-3">Ort. Hız</td><td>{{ $vehicleTrip->avg_speed }} km/h</td></tr>
                            @endif
                            @if($vehicleTrip->min_speed !== null)
                            <tr>
                                <td class="text-muted ps-3">Min. Hız</td>
                                <td><span class="badge" style="background:rgba(16,185,129,.12);color:#059669;font-size:12px;font-weight:600">{{ $vehicleTrip->min_speed }} km/h</span></td>
                            </tr>
                            @endif
                            @if($vehicleTrip->max_speed !== null)
                            <tr>
                                <td class="text-muted ps-3">Maks. Hız</td>
                                <td><span class="badge" style="background:rgba(239,68,68,.12);color:#dc2626;font-size:12px;font-weight:600">{{ $vehicleTrip->max_speed }} km/h</span></td>
                            </tr>
                            @endif
                            <tr><td class="text-muted ps-3">Başlangıç</td><td>{{ $vehicleTrip->started_at->format('d.m.Y H:i') }}</td></tr>
                            @if($vehicleTrip->completed_at)
                            <tr><td class="text-muted ps-3">Bitiş</td><td>{{ $vehicleTrip->completed_at->format('d.m.Y H:i') }}</td></tr>
                            @endif
                            <tr><td class="text-muted ps-3">Konum Noktası</td><td>{{ $vehicleTrip->locations->count() }} adet</td></tr>
                            @if($vehicleTrip->notes)
                            <tr><td class="text-muted ps-3">Not</td><td>{{ $vehicleTrip->notes }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Görevi Bitir butonu (görev sahibi veya yetkili yönetici) --}}
            @if($vehicleTrip->status === 'active' && ($vehicleTrip->user_id === Auth::id() || Auth::user()->hasPermission('vehicle_trip_control', 'index')))
            <div class="d-grid mt-3">
                <a href="{{ route('vehicle-trips.complete', $vehicleTrip) }}" class="btn btn-danger btn-lg">
                    <i class="fa fa-stop-circle me-2"></i>Görevi Bitir
                </a>
            </div>
            @endif

            {{-- Fotoğraflar --}}
            <div class="card">
                <div class="card-header"><h6 class="mb-0">KM Fotoğrafları</h6></div>                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Başlangıç</p>
                            <a href="{{ asset('uploads/'.$vehicleTrip->start_km_photo) }}" target="_blank">
                                <img src="{{ asset('uploads/'.$vehicleTrip->start_km_photo) }}"
                                     class="img-fluid rounded border" style="width:100%;height:120px;object-fit:cover;">
                            </a>
                        </div>
                        @if($vehicleTrip->end_km_photo)
                        <div class="col-6">
                            <p class="text-muted small mb-1">Dönüş</p>
                            <a href="{{ asset('uploads/'.$vehicleTrip->end_km_photo) }}" target="_blank">
                                <img src="{{ asset('uploads/'.$vehicleTrip->end_km_photo) }}"
                                     class="img-fluid rounded border" style="width:100%;height:120px;object-fit:cover;">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Harita --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Güzergah Haritası</h5>
                    <span class="badge bg-light text-dark border" id="pointBadge">{{ $vehicleTrip->locations->count() }} nokta</span>
                </div>
                <div class="card-body p-0">
                    @if($vehicleTrip->locations->isEmpty())
                        <div class="text-center text-muted py-5">Henüz konum kaydı yok.</div>
                    @else
                        <div id="map" style="height:500px;border-radius:0 0 0.375rem 0.375rem;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($vehicleTrip->locations->isNotEmpty())
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const locations = @json($vehicleTrip->locations->map(fn($l) => ['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'time'=>$l->recorded_at->format('H:i:s')]));

const map = L.map('map');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 18
}).addTo(map);

// Rota çizgisi
const latlngs = locations.map(l => [l.lat, l.lng]);
const polyline = L.polyline(latlngs, { color: '#e91e63', weight: 4, opacity: 0.8 }).addTo(map);
map.fitBounds(polyline.getBounds(), { padding: [30, 30] });

// Başlangıç ve bitiş marker
if (locations.length > 0) {
    const startIcon = L.divIcon({ className: '', html: '<div style="background:#28a745;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>', iconAnchor:[7,7] });
    const endIcon   = L.divIcon({ className: '', html: '<div style="background:#e91e63;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>', iconAnchor:[7,7] });

    L.marker(latlngs[0], { icon: startIcon }).addTo(map).bindPopup('Başlangıç — ' + locations[0].time);
    if (latlngs.length > 1) {
        L.marker(latlngs[latlngs.length - 1], { icon: endIcon }).addTo(map)
          .bindPopup('{{ $vehicleTrip->status === "active" ? "Son Konum" : "Bitiş" }} — ' + locations[locations.length - 1].time);
    }
}

// Canlı görevde polling
@if($vehicleTrip->status === 'active')
const POLL_URL = '{{ route("vehicle-trips.control-locations", $vehicleTrip) }}';
setInterval(() => {
    fetch(POLL_URL).then(r => r.json()).then(data => {
        if (data.locations && data.locations.length > latlngs.length) {
            const newPoints = data.locations.map(l => [l.lat, l.lng]);
            polyline.setLatLngs(newPoints);
            // son nokta marker güncelle
            document.getElementById('pointBadge').textContent = data.locations.length + ' nokta';
        }
        if (data.status === 'completed') {
            location.reload();
        }
    }).catch(() => {});
}, 15000); // 15 saniye
@endif
</script>
@endpush
@endif

<style>
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
.pulse-badge { animation: pulse 1.5s ease-in-out infinite; }
</style>
