<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationAssignment;
use App\Models\EducationCourse;
use App\Models\EducationEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EducationReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('education_reports', ['index'], [], [], [], []);
    }

    public function index(Request $request)
    {
        $language = $request->get('language');
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        $assignmentsQuery = EducationAssignment::with(['course.trainer', 'learner.branch'])
            ->whereBetween('assigned_week_start', [$from->toDateString(), $to->toDateString()])
            ->when($language, fn ($query) => $query->where('language', $language));

        $allAssignments = (clone $assignmentsQuery)->get();
        $assignments = (clone $assignmentsQuery)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $totalAssignments = $allAssignments->count();
        $completedAssignments = $allAssignments
            ->where('status', EducationAssignment::STATUS_COMPLETED)
            ->count();

        $stats = [
            'total_assignments' => $totalAssignments,
            'completed_assignments' => $completedAssignments,
            'in_progress_assignments' => $allAssignments->where('status', EducationAssignment::STATUS_IN_PROGRESS)->count(),
            'not_started_assignments' => $allAssignments->where('status', EducationAssignment::STATUS_NOT_STARTED)->count(),
            'avg_progress' => $totalAssignments > 0 ? round($allAssignments->avg('progress_percent'), 1) : 0,
            'completion_rate' => $totalAssignments > 0 ? round($completedAssignments / $totalAssignments * 100, 1) : 0,
        ];

        $courseStats = EducationCourse::with(['trainer', 'assignments.learner'])
            ->when($language, fn ($query) => $query->where('language', $language))
            ->orderBy('title')
            ->get()
            ->map(function (EducationCourse $course) use ($from, $to) {
                $assignments = $course->assignments
                    ->filter(fn (EducationAssignment $assignment) => $assignment->assigned_week_start->between($from, $to));
                $count = $assignments->count();

                return [
                    'course' => $course,
                    'assigned' => $count,
                    'completed' => $assignments->where('status', EducationAssignment::STATUS_COMPLETED)->count(),
                    'avg_progress' => $count > 0 ? round($assignments->avg('progress_percent'), 1) : 0,
                ];
            });

        $events = EducationEvent::with(['trainer', 'responses.learner'])
            ->whereBetween('starts_at', [$from, $to])
            ->when($language, fn ($query) => $query->where('language', $language))
            ->orderByDesc('starts_at')
            ->get();

        return view('modules.education.reports.index', [
            'languages' => EducationCourse::LANGUAGES,
            'language' => $language,
            'from' => $from,
            'to' => $to,
            'stats' => $stats,
            'courseStats' => $courseStats,
            'assignments' => $assignments,
            'events' => $events,
        ]);
    }
}
