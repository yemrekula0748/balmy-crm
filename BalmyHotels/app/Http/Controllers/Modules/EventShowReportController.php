<?php

namespace App\Http\Controllers\Modules;

use App\Models\AnimationEvent;
use App\Models\AnimationEventDate;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EventShowReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('event_show_reports', ['index', 'pdf'], [], [], [], []);
    }

    public function index(Request $request)
    {
        return view('modules.reports.event_shows.index', $this->buildReportPayload($request));
    }

    public function pdf(Request $request)
    {
        $payload = $this->buildReportPayload($request);
        $payload['generatedAt'] = now();

        $pdf = Pdf::loadView('modules.reports.event_shows.pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $fileName = 'etkinlik-show-raporu-' . $payload['dateFrom'] . '-' . $payload['dateTo'] . '.pdf';

        return $pdf->download($fileName);
    }

    private function buildReportPayload(Request $request): array
    {
        $request->validate([
            'branch_id' => 'nullable|integer',
            'animation_event_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $branchIds = auth()->user()->visibleBranchIds();
        $dateFrom = $request->date_from ?: now()->toDateString();
        $dateTo = $request->date_to ?: $dateFrom;

        if ($request->filled('branch_id')) {
            abort_if(!in_array((int) $request->branch_id, $branchIds, true), 403);
        }

        if ($request->filled('animation_event_id')) {
            abort_if(
                !AnimationEvent::whereKey($request->animation_event_id)->whereIn('branch_id', $branchIds)->exists(),
                403
            );
        }

        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $events = AnimationEvent::whereIn('branch_id', $branchIds)->orderBy('name')->get();

        $eventDates = AnimationEventDate::with(['event.branch', 'event.participants', 'attendances.participant'])
            ->whereBetween('event_date', [$dateFrom, $dateTo])
            ->whereHas('event', function ($q) use ($branchIds, $request) {
                $q->whereIn('branch_id', $branchIds);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('animation_event_id')) {
                    $q->whereKey($request->animation_event_id);
                }
            })
            ->orderBy('event_date')
            ->get();

        $rows = $eventDates->map(function (AnimationEventDate $eventDate) {
            $participants = $eventDate->event->participants;
            $presentIds = $eventDate->attendances->pluck('animation_event_participant_id')->all();
            $present = $participants->whereIn('id', $presentIds)->values();
            $missing = $participants->whereNotIn('id', $presentIds)->values();

            return [
                'event_date' => $eventDate,
                'event' => $eventDate->event,
                'expected_count' => $participants->count(),
                'present_count' => $present->count(),
                'missing_count' => $missing->count(),
                'present_names' => $present->pluck('name')->values(),
                'missing_names' => $missing->pluck('name')->values(),
            ];
        });

        $summary = [
            'show_count' => $rows->count(),
            'expected_count' => $rows->sum('expected_count'),
            'present_count' => $rows->sum('present_count'),
            'missing_count' => $rows->sum('missing_count'),
        ];

        $summary['attendance_rate'] = $summary['expected_count'] > 0
            ? round(($summary['present_count'] / $summary['expected_count']) * 100)
            : 0;

        return [
            'branches' => $branches,
            'events' => $events,
            'rows' => $rows,
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page_title' => 'Etkinlik/Show Raporları',
        ];
    }
}
