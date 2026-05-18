<?php

namespace App\Http\Controllers\Modules;

use App\Models\GuestControlLog;
use App\Services\HotelAdvisorHotspotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class GuestControlController extends BaseModuleController
{
    public function __construct(
        private readonly HotelAdvisorHotspotService $hotspotService
    ) {
        $this->middleware('perm:guest_control,index')->only(['index', 'lookup']);
        $this->middleware('perm:guest_control,create')->only(['store']);
        $this->middleware('perm:guest_control_history,index')->only(['history']);
    }

    public function index(): View
    {
        $hotel = $this->hotspotService->hotel('foresta');
        $integrationReady = $this->hotspotService->isConfigured('foresta');

        return view('modules.onburo.guest_control.index', compact('hotel', 'integrationReady'));
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_no' => 'required|string|max:20',
        ]);

        try {
            $guests = $this->hotspotService->getRoomGuests('foresta', $validated['room_no']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        if (empty($guests)) {
            return response()->json([
                'message' => 'Bu oda icin misafir bulunamadi.',
            ], 404);
        }

        return response()->json([
            'message' => 'Misafirler bulundu.',
            'room_no' => strtoupper(trim($validated['room_no'])),
            'hotel' => [
                'key' => 'foresta',
                'name' => 'Balmy Foresta',
            ],
            'guests' => $guests,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_no' => 'required|string|max:20',
            'action_type' => 'required|in:' . GuestControlLog::ACTION_CHECK_IN . ',' . GuestControlLog::ACTION_CHECK_OUT,
            'selection_keys' => 'required|array|min:1',
            'selection_keys.*' => 'required|string|max:191',
        ]);

        try {
            $hotel = $this->hotspotService->hotel('foresta');
            $guests = collect(
                $this->hotspotService->getRoomGuests('foresta', $validated['room_no'])
            )->keyBy('selection_key');
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $selectedGuests = collect($validated['selection_keys'])
            ->map(fn ($selectionKey) => $guests->get($selectionKey))
            ->filter()
            ->values();

        if ($selectedGuests->isEmpty()) {
            return response()->json([
                'message' => 'Kaydedilecek secili misafir bulunamadi.',
            ], 422);
        }

        $createdCount = 0;
        $existingCount = 0;

        DB::transaction(function () use ($selectedGuests, $validated, $hotel, &$createdCount, &$existingCount) {
            foreach ($selectedGuests as $guest) {
                $log = GuestControlLog::firstOrCreate(
                    [
                        'hotel_id' => (int) ($hotel['hotel_id'] ?? $guest['hotel_id'] ?? 0),
                        'action_type' => $validated['action_type'],
                        'stay_signature' => $guest['stay_signature'],
                    ],
                    [
                        'hotel_key' => 'foresta',
                        'hotel_name' => (string) ($hotel['name'] ?? 'Balmy Foresta'),
                        'branch_id' => $hotel['branch_id'] ?? null,
                        'room_no' => $guest['room_no'],
                        'selection_key' => $guest['selection_key'],
                        'api_guest_id' => $guest['guest_id'],
                        'api_reservation_id' => $guest['reservation_id'],
                        'api_reservation_name_id' => $guest['reservation_name_id'],
                        'first_name' => $guest['name'],
                        'last_name' => $guest['lname'],
                        'full_name' => $guest['full_name'],
                        'phone' => $guest['phone'],
                        'email' => $guest['email'],
                        'nationality' => $guest['nationality'],
                        'national_id_no' => $guest['national_id_no'],
                        'passport_no' => $guest['passport_no'],
                        'hotel_checkin_date' => $guest['checkin'],
                        'hotel_checkout_date' => $guest['checkout'],
                        'arrival_time' => $guest['arrival_time'],
                        'departure_time' => $guest['departure_time'],
                        'payload' => $guest['raw'],
                        'created_by' => auth()->id(),
                        'action_at' => now(),
                    ]
                );

                if ($log->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $existingCount++;
                }
            }
        });

        $actionLabel = $validated['action_type'] === GuestControlLog::ACTION_CHECK_IN
            ? 'giris'
            : 'cikis';

        $message = "{$createdCount} misafir icin {$actionLabel} kaydi olusturuldu.";
        if ($existingCount > 0) {
            $message .= " {$existingCount} kayit zaten mevcuttu.";
        }

        return response()->json([
            'message' => $message,
            'created_count' => $createdCount,
            'existing_count' => $existingCount,
        ]);
    }

    public function history(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $roomNo = trim((string) $request->input('room_no', ''));
        $actionType = trim((string) $request->input('action_type', ''));
        $search = trim((string) $request->input('search', ''));

        $query = GuestControlLog::with(['creator', 'branch'])
            ->where('hotel_key', 'foresta')
            ->whereDate('action_at', '>=', $dateFrom)
            ->whereDate('action_at', '<=', $dateTo);

        if ($roomNo !== '') {
            $query->where('room_no', 'like', '%' . $roomNo . '%');
        }

        if (in_array($actionType, [GuestControlLog::ACTION_CHECK_IN, GuestControlLog::ACTION_CHECK_OUT], true)) {
            $query->where('action_type', $actionType);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('room_no', 'like', '%' . $search . '%');
            });
        }

        $logs = $query
            ->orderByDesc('action_at')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total' => $logs->count(),
            'check_in' => $logs->where('action_type', GuestControlLog::ACTION_CHECK_IN)->count(),
            'check_out' => $logs->where('action_type', GuestControlLog::ACTION_CHECK_OUT)->count(),
        ];

        return view('modules.onburo.guest_control.history', compact(
            'logs',
            'stats',
            'dateFrom',
            'dateTo',
            'roomNo',
            'actionType',
            'search'
        ));
    }
}
