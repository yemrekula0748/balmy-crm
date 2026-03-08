@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Görev Kontrol Merkezi</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle-trips.index') }}">Araç Görevleri</a></li>
                <li class="breadcrumb-item active">Kontrol Merkezi</li>
            </ol>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sol panel: aktif görevler --}}
        <div class="col-lg-4">
            {{-- Aktif Görevler --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <span class="badge bg-success me-2">{{ $activeTrips->count() }}</span>
                        Aktif Görevler
                    </h6>
                    <span class="text-muted small" id="lastUpdate">—</span>
                </div>
                <div class="list-group list-group-flush" id="activeList">
                    @forelse($activeTrips as $trip)
                    <a href="{{ route('vehicle-trips.control', ['trip_id' => $trip->id]) }}"
                       class="list-group-item list-group-item-action @if(isset($selectedTrip) && $selectedTrip?->id === $trip->id) active @endif"
                       data-trip="{{ $trip->id }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $trip->vehicle->plate }}</div>
                                <div class="small text-{{ isset($selectedTrip) && $selectedTrip?->id === $trip->id ? 'light' : 'muted' }}">
                                    {{ $trip->user->name ?? '-' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small fw-semibold">{{ $trip->destination }}</div>
                                <div class="small text-{{ isset($selectedTrip) && $selectedTrip?->id === $trip->id ? 'light' : 'muted' }}">
                                    {{ $trip->locations->count() }} konum
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="px-2 pb-1">
                        <a href="{{ route('vehicle-trips.complete', $trip) }}" class="btn btn-danger btn-sm w-100">
                            <i class="fa fa-stop-circle me-1"></i>Görevi Bitir
                        </a>
                    </div>
                    @empty
                    <div class="list-group-item text-muted text-center py-3">
                        <i class="fa fa-check-circle me-1"></i> Aktif görev yok
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Son 7 günün görevleri --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Son 7 Günün Görevleri</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height:320px;overflow-y:auto;">
                    @forelse($recentTrips as $trip)
                    <a href="{{ route('vehicle-trips.control', ['trip_id' => $trip->id]) }}"
                       class="list-group-item list-group-item-action py-2 @if(isset($selectedTrip) && $selectedTrip?->id === $trip->id) bg-light @endif">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="small fw-semibold">{{ $trip->vehicle->plate ?? '-' }}</span>
                                <span class="text-muted small ms-1">{{ $trip->user->name ?? '' }}</span>
                            </div>
                            @if($trip->status === 'active')
                                <span class="badge bg-success very-small">Aktif</span>
                            @else
                                <span class="badge bg-light text-dark border very-small">Bitti</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:11px;">{{ $trip->destination }} · {{ $trip->started_at->format('d.m H:i') }}</div>
                    </a>
                    @empty
                    <div class="list-group-item text-muted text-center py-3 small">Kayıt yok</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sağ panel: harita --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        @if($selectedTrip)
                            <span class="text-primary">{{ $selectedTrip->vehicle->plate }}</span>
                            <span class="text-muted small ms-2">{{ $selectedTrip->user->name ?? '' }} — {{ $selectedTrip->destination }}</span>
                        @else
                            Harita
                        @endif
                    </h5>
                    @if($selectedTrip && $selectedTrip->status === 'active')
                        <span class="badge bg-success">
                            <i class="fa fa-circle fa-xs me-1"></i>Canlı
                        </span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if(!$selectedTrip)
                        <div class="text-center text-muted py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#ccc" class="d-block mx-auto mb-3" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            <p>Soldaki listeden bir görev seçin.</p>
                        </div>
                    @elseif($selectedTrip->locations->isEmpty())
                        <div class="text-center text-muted py-5">
                            Henüz konum kaydı bulunmuyor.
                        </div>
                    @else
                        <div id="map" style="height:600px;border-radius:0 0 0.375rem 0.375rem;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($selectedTrip && $selectedTrip->locations->isNotEmpty())
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let currentLocations = @json($selectedTrip->locations->map(fn($l) => ['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'time'=>$l->recorded_at->format('H:i:s')]));

const map = L.map('map');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 18
}).addTo(map);

// Rota
let latlngs = currentLocations.map(l => [l.lat, l.lng]);
let polyline = L.polyline(latlngs, { color: '#e91e63', weight: 5, opacity: 0.85 }).addTo(map);
map.fitBounds(polyline.getBounds(), { padding: [30, 30] });

// Marker ikonları
const startIcon = L.divIcon({
    className: '',
    html: `<div style="
        background:#28a745;width:16px;height:16px;border-radius:50%;
        border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>`,
    iconAnchor: [8, 8]
});
const liveIcon = L.divIcon({
    className: '',
    html: `<div style="
        background:#e91e63;width:20px;height:20px;border-radius:50%;
        border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.5);
        animation:pulse 1.5s ease-in-out infinite"></div>
        <style>@keyframes pulse{0%,100%{transform:scale(1)opacity:1}50%{transform:scale(1.3);opacity:.7}}</style>`,
    iconAnchor: [10, 10]
});
const endIcon = L.divIcon({
    className: '',
    html: `<div style="
        background:#6c757d;width:16px;height:16px;border-radius:50%;
        border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>`,
    iconAnchor: [8, 8]
});

let startMarker = L.marker(latlngs[0], { icon: startIcon })
    .addTo(map)
    .bindPopup('<strong>Başlangıç</strong><br>' + currentLocations[0].time);

let lastMarker = L.marker(latlngs[latlngs.length - 1], {
    icon: {{ $selectedTrip->status === 'active' ? 'liveIcon' : 'endIcon' }}
}).addTo(map).bindPopup(
    '{{ $selectedTrip->status === "active" ? "<strong>Son Konum</strong>" : "<strong>Bitiş</strong>" }}<br>' +
    currentLocations[currentLocations.length - 1].time
);

// Canlı polling
@if($selectedTrip->status === 'active')
const POLL_URL = '{{ route("vehicle-trips.control-locations", $selectedTrip) }}';

setInterval(() => {
    fetch(POLL_URL)
        .then(r => r.json())
        .then(data => {
            document.getElementById('lastUpdate').textContent = 'Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');

            if (data.locations && data.locations.length > currentLocations.length) {
                currentLocations = data.locations;
                const newLatlngs = data.locations.map(l => [l.lat, l.lng]);

                polyline.setLatLngs(newLatlngs);
                lastMarker.setLatLng(newLatlngs[newLatlngs.length - 1]);
                lastMarker.setPopupContent('<strong>Son Konum</strong><br>' + data.locations[data.locations.length - 1].time);

                // Haritayı son konuma kaydır (animate)
                map.panTo(newLatlngs[newLatlngs.length - 1], { animate: true });
            }

            if (data.status === 'completed') {
                location.reload();
            }
        }).catch(() => {});
}, 10000); // 10 saniye
@endif
</script>
@endpush
@endif

<style>
.very-small { font-size: 10px; }
</style>
