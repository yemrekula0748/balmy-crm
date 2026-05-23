<?php

namespace App\Http\Controllers\Modules;

use App\Models\AnimationEvent;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimationEventController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'animation_events',
            ['index'],
            ['show'],
            ['create', 'store'],
            [],
            ['destroy']
        );
    }

    public function index()
    {
        $branchIds = auth()->user()->visibleBranchIds();

        $events = AnimationEvent::with(['branch', 'dates', 'participants'])
            ->whereIn('branch_id', $branchIds)
            ->latest()
            ->paginate(20);

        $page_title = 'Animasyon Etkinlikleri';

        return view('modules.animation.events.index', compact('events', 'page_title'));
    }

    public function create()
    {
        $branchIds = auth()->user()->visibleBranchIds();
        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $autoBranchId = count($branchIds) === 1 ? $branchIds[0] : null;
        $page_title = 'Etkinlik Oluştur';

        return view('modules.animation.events.create', compact('branches', 'autoBranchId', 'page_title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'nullable|string|max:255',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
        ]);

        abort_if(!in_array((int) $data['branch_id'], auth()->user()->visibleBranchIds(), true), 403);

        $participants = collect($data['participants'])
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->unique(fn($name) => mb_strtolower($name))
            ->values();

        $dates = collect($data['dates'])
            ->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values();

        if ($participants->isEmpty() || $dates->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['participants' => 'En az bir katılımcı ve bir etkinlik tarihi eklemelisiniz.']);
        }

        $event = DB::transaction(function () use ($data, $participants, $dates) {
            $event = AnimationEvent::create([
                'branch_id' => $data['branch_id'],
                'created_by' => auth()->id(),
                'name' => $data['name'],
                'is_active' => true,
            ]);

            foreach ($participants as $participant) {
                $event->participants()->create(['name' => $participant]);
            }

            foreach ($dates as $date) {
                $event->dates()->create(['event_date' => $date]);
            }

            return $event;
        });

        return redirect()
            ->route('animation.events.show', $event)
            ->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }

    public function show(AnimationEvent $event)
    {
        $this->authorizeEventBranch($event);

        $event->load(['branch', 'creator', 'dates.attendances', 'participants']);
        $page_title = $event->name;

        return view('modules.animation.events.show', compact('event', 'page_title'));
    }

    public function destroy(AnimationEvent $event)
    {
        $this->authorizeEventBranch($event);
        $event->delete();

        return redirect()
            ->route('animation.events.index')
            ->with('success', 'Etkinlik silindi.');
    }

    private function authorizeEventBranch(AnimationEvent $event): void
    {
        abort_if(!in_array($event->branch_id, auth()->user()->visibleBranchIds(), true), 403);
    }
}
