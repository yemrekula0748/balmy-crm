@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Aktif Görevim</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle-trips.index') }}">Araç Görevleri</a></li>
                <li class="breadcrumb-item active">Aktif Görevim</li>
            </ol>
        </div>
    </div>

    @if(!$activeTrip)
        <div class="row justify-content-center">
            <div class="col-md-6 text-center py-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#ccc" class="mb-3" viewBox="0 0 16 16">
                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                </svg>
                <h5 class="text-muted">Aktif göreviniz bulunmuyor.</h5>
                @if(Auth::user()->hasPermission('vehicle_trips', 'create'))
                <a href="{{ route('vehicle-trips.create') }}" class="btn btn-primary mt-2">
                    <i class="fa fa-play me-2"></i>Yeni Görev Başlat
                </a>
                @endif
            </div>
        </div>
    @else
        {{-- Aktif görev bilgisi --}}
        <div class="row justify-content-center">
            <div class="col-lg-8">

                {{-- Bilgi kartı --}}
                <div class="card border-success mb-3">
                    <div class="card-header bg-success bg-opacity-10 border-success d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-success">
                            <span class="badge bg-success me-2 pulse-badge">● CANLI</span>
                            Görev #{{ $activeTrip->id }}
                        </h5>
                        <span class="text-muted small">{{ $activeTrip->started_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Araç</div>
                                <div class="fw-semibold">{{ $activeTrip->vehicle->plate }} — {{ $activeTrip->vehicle->brand }} {{ $activeTrip->vehicle->model }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Gidilen Yer</div>
                                <div class="fw-semibold">{{ $activeTrip->destination }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Başlangıç KM</div>
                                <div class="fw-semibold">{{ number_format($activeTrip->start_km) }} km</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Konum Kaydı</div>
                                <div class="fw-semibold" id="locationCount">{{ $activeTrip->locations->count() }} nokta</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GPS Durum --}}
                <div class="card mb-3" id="gpsCard">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div id="gpsIcon" class="text-warning fs-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <div id="gpsStatus" class="fw-semibold">Konum alınıyor...</div>
                            <div id="gpsCoords" class="text-muted small">GPS izni bekleniyor</div>
                        </div>
                        <div id="gpsBadge" class="badge bg-warning">Bekleniyor</div>
                    </div>
                </div>

                {{-- Görevi Bitir butonu --}}
                <div class="d-grid">
                    <a href="{{ route('vehicle-trips.complete', $activeTrip) }}" class="btn btn-danger btn-lg">
                        <i class="fa fa-stop-circle me-2"></i>Görevi Bitir
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@if($activeTrip)
@push('scripts')
<script>
const TRIP_ID = {{ $activeTrip->id }};
const LOCATION_URL = '{{ route("vehicle-trips.location", $activeTrip) }}';
const CSRF = '{{ csrf_token() }}';

let locationCount = {{ $activeTrip->locations->count() }};
let watchId = null;
let lastSent = 0;
const SEND_INTERVAL = 5000; // 5 saniyede bir gönder

function updateGpsUI(status, coordText, badgeClass, badgeText) {
    document.getElementById('gpsStatus').textContent = status;
    document.getElementById('gpsCoords').textContent = coordText;
    const badge = document.getElementById('gpsBadge');
    badge.className = 'badge ' + badgeClass;
    badge.textContent = badgeText;
}

function sendLocation(lat, lng) {
    const now = Date.now();
    if (now - lastSent < SEND_INTERVAL) return;
    lastSent = now;

    fetch(LOCATION_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ lat, lng })
    }).then(r => r.json()).then(() => {
        locationCount++;
        document.getElementById('locationCount').textContent = locationCount + ' nokta';
    }).catch(() => {});
}

function startTracking() {
    if (!navigator.geolocation) {
        updateGpsUI('GPS desteklenmiyor', 'Bu tarayıcı konumu desteklemiyor.', 'bg-danger', 'Hata');
        return;
    }

    const opts = { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 };

    watchId = navigator.geolocation.watchPosition(
        pos => {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            updateGpsUI(
                'Konum aktif — her 30 saniyede kaydediliyor',
                `${lat}, ${lng} (±${Math.round(pos.coords.accuracy)}m)`,
                'bg-success',
                'Aktif'
            );
            sendLocation(pos.coords.latitude, pos.coords.longitude);
        },
        err => {
            let msg = 'Konum alınamıyor';
            if (err.code === 1) msg = 'Konum izni reddedildi. Lütfen tarayıcı ayarlarından izin verin.';
            if (err.code === 2) msg = 'Konum servisi kullanılamıyor.';
            if (err.code === 3) msg = 'Konum isteği zaman aşımına uğradı.';
            updateGpsUI(msg, '', 'bg-danger', 'Hata');
        },
        opts
    );
}

// Sayfa yüklenince izlemeyi başlat
startTracking();

// Sayfa kapatılırken izlemeyi durdur
window.addEventListener('beforeunload', () => {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
});
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.pulse-badge { animation: pulse 1.5s ease-in-out infinite; }
</style>
@endpush
@endif

@endsection
