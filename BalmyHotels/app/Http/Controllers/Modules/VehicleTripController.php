<?php

namespace App\Http\Controllers\Modules;

use App\Models\Vehicle;
use App\Models\VehicleTrip;
use App\Models\VehicleTripLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleTripController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'vehicle_trips',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['complete', 'update'],
            ['destroy']
        );

        // Kontrol sayfası için ayrı yetki
        $this->middleware('perm:vehicle_trip_control,index')->only(['control']);
    }

    /** Tüm görevler listesi */
    public function index(Request $request)
    {
        $trips = VehicleTrip::with(['vehicle', 'user'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->orderByDesc('started_at')
            ->paginate(20)
            ->withQueryString();

        $vehicles = Vehicle::where('is_active', true)->orderBy('plate')->get();

        return view('modules.vehicles.trips.index', compact('trips', 'vehicles'));
    }

    /** Görev başlat formu */
    public function create()
    {
        // Sadece görevde olmayan aktif araçları listele
        $vehicles = Vehicle::where('is_active', true)
            ->whereDoesntHave('activeTrip')
            ->orderBy('plate')
            ->get();

        return view('modules.vehicles.trips.create', compact('vehicles'));
    }

    /** Görevi kaydet (başlat) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'start_km'      => 'required|integer|min:0',
            'start_km_photo'=> 'required|image|max:10240',
            'destination'   => 'required|string|max:200',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        // Araç halihazırda görevde mi?
        if ($vehicle->activeTrip) {
            return back()->withErrors(['vehicle_id' => 'Bu araç şu anda görevde, başka bir görev başlatılamaz.'])->withInput();
        }

        // Km kontrolü
        if ($data['start_km'] < $vehicle->current_km) {
            return back()->withErrors(['start_km' => 'Başlangıç km değeri aracın mevcut km değerinden (' . $vehicle->current_km . ') küçük olamaz.'])->withInput();
        }

        // Fotoğraf kaydet (kamera çekimi — galeri engeli view'da)
        $photo = $request->file('start_km_photo');
        $path  = $photo->store('vehicle_trips', 'public');

        VehicleTrip::create([
            'vehicle_id'      => $vehicle->id,
            'user_id'         => Auth::id(),
            'start_km'        => $data['start_km'],
            'start_km_photo'  => $path,
            'destination'     => $data['destination'],
            'status'          => 'active',
            'started_at'      => now(),
        ]);

        return redirect()->route('vehicle-trips.my')
            ->with('success', 'Görev başlatıldı! İyi yolculuklar.');
    }

    /** Aktif görev detay (GPS takip sayfası) */
    public function show(VehicleTrip $vehicleTrip)
    {
        // Sadece görevin sahibi veya yönetici erişebilir
        if ($vehicleTrip->user_id !== Auth::id()) {
            abort_unless(Auth::user()->hasPermission('vehicle_trip_control', 'index'), 403);
        }

        $vehicleTrip->load(['vehicle', 'user', 'locations']);
        return view('modules.vehicles.trips.show', compact('vehicleTrip'));
    }

    /** Görev yazdır (standalone print view) */
    public function printTrip(VehicleTrip $vehicleTrip)
    {
        if ($vehicleTrip->user_id !== Auth::id()) {
            abort_unless(Auth::user()->hasPermission('vehicle_trip_control', 'index'), 403);
        }

        $vehicleTrip->load(['vehicle', 'user', 'locations']);
        return view('modules.vehicles.trips.print', compact('vehicleTrip'));
    }

    /** Kullanıcının kendi aktif görevi */
    public function myTrip()
    {
        $activeTrip = VehicleTrip::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with(['vehicle', 'locations'])
            ->first();

        return view('modules.vehicles.trips.my', compact('activeTrip'));
    }

    /** Kullanıcının görevi bitirme yetkisi var mı? */
    private function canComplete(VehicleTrip $vehicleTrip): bool
    {
        return $vehicleTrip->isActive() &&
               ($vehicleTrip->user_id === Auth::id() || Auth::user()->hasPermission('vehicle_trip_control', 'index'));
    }

    /** Görevi bitir formu (GET) */
    public function complete(VehicleTrip $vehicleTrip)
    {
        abort_unless($this->canComplete($vehicleTrip), 403);
        $vehicleTrip->load('vehicle');

        // GPS km varsa başlangıç km'ye ekleyerek bitiş km önerisi hesapla
        $stats        = $this->calcTripStats($vehicleTrip);
        $suggestedEndKm = $stats['gps_km']
            ? (int) round($vehicleTrip->start_km + $stats['gps_km'])
            : $vehicleTrip->start_km;

        return view('modules.vehicles.trips.complete', compact('vehicleTrip', 'suggestedEndKm', 'stats'));
    }

    /** Görevi bitir (POST) */
    public function update(Request $request, VehicleTrip $vehicleTrip)
    {
        abort_unless($this->canComplete($vehicleTrip), 403);

        $data = $request->validate([
            'end_km'       => 'required|integer|min:' . $vehicleTrip->start_km,
            'end_km_photo' => 'required|image|max:10240',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $photo = $request->file('end_km_photo');
        $path  = $photo->store('vehicle_trips', 'public');

        // GPS istatistiklerini hesapla
        $stats = $this->calcTripStats($vehicleTrip);

        $vehicleTrip->update([
            'end_km'       => $data['end_km'],
            'end_km_photo' => $path,
            'notes'        => $data['notes'] ?? null,
            'status'       => 'completed',
            'completed_at' => now(),
            'gps_km'       => $stats['gps_km'],
            'avg_speed'    => $stats['avg_speed'],
            'min_speed'    => $stats['min_speed'],
            'max_speed'    => $stats['max_speed'],
        ]);

        // Aracın km'ini güncelle
        $vehicleTrip->vehicle->update(['current_km' => $data['end_km']]);

        return redirect()->route('vehicle-trips.index')
            ->with('success', 'Görev tamamlandı! Araç km değeri güncellendi.');
    }

    /** GPS konum kayıtlarından hız ve mesafe istatistiklerini hesapla */
    private function calcTripStats(VehicleTrip $vehicleTrip): array
    {
        $locations = $vehicleTrip->locations()->select('lat', 'lng', 'speed', 'recorded_at')->get();

        if ($locations->isEmpty()) {
            return ['gps_km' => null, 'avg_speed' => null, 'min_speed' => null, 'max_speed' => null];
        }

        // Haversine ile toplam mesafe
        $totalKm = 0.0;
        for ($i = 1; $i < $locations->count(); $i++) {
            $totalKm += $this->haversineKm(
                (float) $locations[$i - 1]->lat, (float) $locations[$i - 1]->lng,
                (float) $locations[$i]->lat,     (float) $locations[$i]->lng
            );
        }

        // Hız istatistikleri (null olmayan kayıtlardan)
        $speeds = $locations->whereNotNull('speed')->pluck('speed')->filter(fn($s) => $s >= 0);

        return [
            'gps_km'    => $totalKm > 0 ? round($totalKm, 2) : null,
            'avg_speed' => $speeds->isNotEmpty() ? round($speeds->avg(), 2) : null,
            'min_speed' => $speeds->isNotEmpty() ? round($speeds->min(), 2) : null,
            'max_speed' => $speeds->isNotEmpty() ? round($speeds->max(), 2) : null,
        ];
    }

    /** Haversine formülü — iki koordinat arası km */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Görev sil */
    public function destroy(VehicleTrip $vehicleTrip)
    {
        // Fotoğrafları da sil
        if ($vehicleTrip->start_km_photo) {
            $startPath = public_path('uploads/' . $vehicleTrip->start_km_photo);
            if (file_exists($startPath)) @unlink($startPath);
        }
        if ($vehicleTrip->end_km_photo) {
            $endPath = public_path('uploads/' . $vehicleTrip->end_km_photo);
            if (file_exists($endPath)) @unlink($endPath);
        }

        $vehicleTrip->delete();
        return redirect()->route('vehicle-trips.index')
            ->with('success', 'Görev kaydı silindi.');
    }

    /** Kontrol / Harita sayfası */
    public function control(Request $request)
    {
        $activeTrips = VehicleTrip::where('status', 'active')
            ->with(['vehicle', 'user', 'locations'])
            ->orderByDesc('started_at')
            ->get();

        $selectedTrip = null;
        if ($request->trip_id) {
            $selectedTrip = VehicleTrip::with(['vehicle', 'user', 'locations'])
                ->findOrFail($request->trip_id);
        }

        // Son 7 günün tamamlanmış görevleri de listele
        $recentTrips = VehicleTrip::with(['vehicle', 'user'])
            ->where('started_at', '>=', now()->subDays(7))
            ->orderByDesc('started_at')
            ->get();

        return view('modules.vehicles.trips.control', compact('activeTrips', 'selectedTrip', 'recentTrips'));
    }

    /** API: Konum kaydet (AJAX POST) */
    public function storeLocation(Request $request, VehicleTrip $vehicleTrip)
    {
        // Sadece görevin sahibi konum kaydedebilir
        abort_unless($vehicleTrip->user_id === Auth::id() && $vehicleTrip->isActive(), 403);

        $data = $request->validate([
            'lat'   => 'required|numeric|between:-90,90',
            'lng'   => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0|max:300', // km/h gönderilir
        ]);

        VehicleTripLocation::create([
            'vehicle_trip_id' => $vehicleTrip->id,
            'lat'             => $data['lat'],
            'lng'             => $data['lng'],
            'speed'           => isset($data['speed']) ? round($data['speed'], 2) : null,
            'recorded_at'     => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /** API: Kontrol sayfası için konum güncelleme (polling) */
    public function controlLocations(VehicleTrip $vehicleTrip)
    {
        abort_unless(Auth::user()->hasPermission('vehicle_trip_control', 'index'), 403);

        $locations = $vehicleTrip->locations()
            ->select('lat', 'lng', 'recorded_at')
            ->get()
            ->map(fn($l) => ['lat' => (float)$l->lat, 'lng' => (float)$l->lng, 'time' => $l->recorded_at->format('H:i:s')]);

        return response()->json([
            'status'    => $vehicleTrip->status,
            'locations' => $locations,
        ]);
    }
}
