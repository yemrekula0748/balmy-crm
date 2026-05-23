<?php

namespace App\Http\Controllers\Modules;

use App\Models\AnimationEventAttendance;
use App\Models\AnimationEventDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventTrackingController extends BaseModuleController
{
    public function __construct()
    {
        $this->middleware('perm:event_tracking,index')->only(['index', 'show']);
        $this->middleware('perm:event_tracking,create')->only(['store']);
    }

    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->date ?: now()->toDateString();
        $branchIds = auth()->user()->visibleBranchIds();

        $eventDates = AnimationEventDate::with(['event.branch', 'event.participants', 'attendances.participant'])
            ->whereDate('event_date', $date)
            ->whereHas('event', fn($q) => $q->whereIn('branch_id', $branchIds)->where('is_active', true))
            ->orderBy('event_date')
            ->get();

        $page_title = 'Etkinlik Takip';

        return view('modules.door_logs.event_tracking.index', compact('eventDates', 'date', 'page_title'));
    }

    public function show(AnimationEventDate $eventDate)
    {
        $this->authorizeEventDate($eventDate);

        $eventDate->load(['event.branch', 'event.participants', 'attendances.participant', 'attendances.checker']);
        $presentIds = $eventDate->attendances->pluck('animation_event_participant_id')->all();
        $page_title = 'Etkinlik Giriş İşlemleri';

        return view('modules.door_logs.event_tracking.show', compact('eventDate', 'presentIds', 'page_title'));
    }

    public function store(Request $request, AnimationEventDate $eventDate)
    {
        $this->authorizeEventDate($eventDate);

        $data = $request->validate([
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer',
        ]);

        $eventDate->load('event.participants');
        $allowedIds = $eventDate->event->participants->pluck('id')->all();
        $selectedIds = collect($data['participant_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->intersect($allowedIds)
            ->unique()
            ->values();

        DB::transaction(function () use ($eventDate, $selectedIds) {
            AnimationEventAttendance::where('animation_event_date_id', $eventDate->id)->delete();

            foreach ($selectedIds as $participantId) {
                AnimationEventAttendance::create([
                    'animation_event_date_id' => $eventDate->id,
                    'animation_event_participant_id' => $participantId,
                    'checked_by' => auth()->id(),
                    'checked_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('door-logs.event-tracking.show', $eventDate)
            ->with('success', 'Etkinlik girişleri kaydedildi.');
    }

    private function authorizeEventDate(AnimationEventDate $eventDate): void
    {
        $eventDate->loadMissing('event');
        abort_if(!in_array($eventDate->event->branch_id, auth()->user()->visibleBranchIds(), true), 403);
    }
}
