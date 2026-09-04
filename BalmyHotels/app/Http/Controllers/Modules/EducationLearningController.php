<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EducationLearningController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'education_learning',
            ['index'],
            ['show', 'video'],
            [],
            ['progress'],
            []
        );
    }

    public function index(Request $request)
    {
        $allowedStatuses = [
            EducationAssignment::STATUS_NOT_STARTED,
            EducationAssignment::STATUS_IN_PROGRESS,
            EducationAssignment::STATUS_COMPLETED,
        ];
        $status = in_array($request->get('status'), $allowedStatuses, true)
            ? $request->get('status')
            : null;
        $search = mb_substr(trim((string) $request->get('search', '')), 0, 100);
        $currentWeekStart = Carbon::now()->startOfWeek()->toDateString();

        $baseAssignmentsQuery = EducationAssignment::query()
            ->where('user_id', Auth::id())
            ->whereHas('course', fn ($query) => $query->where('is_active', true));

        $summaryAssignments = (clone $baseAssignmentsQuery)
            ->get(['status', 'progress_percent']);

        $assignmentSummary = [
            'total' => $summaryAssignments->count(),
            'not_started' => $summaryAssignments->where('status', EducationAssignment::STATUS_NOT_STARTED)->count(),
            'in_progress' => $summaryAssignments->where('status', EducationAssignment::STATUS_IN_PROGRESS)->count(),
            'completed' => $summaryAssignments->where('status', EducationAssignment::STATUS_COMPLETED)->count(),
            'average_progress' => round((float) $summaryAssignments->avg('progress_percent'), 1),
        ];

        $assignments = (clone $baseAssignmentsQuery)
            ->with([
                'course.trainer',
                'course.quizQuestions',
                'assigner',
                'latestQuizAttempt',
                'passedQuizAttempt',
            ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $searchTerm = '%' . addcslashes($search, '%_\\') . '%';

                $query->whereHas('course', fn ($courseQuery) => $courseQuery->where('title', 'like', $searchTerm));
            })
            ->orderByRaw("CASE WHEN assigned_week_start = ? THEN 0 ELSE 1 END", [$currentWeekStart])
            ->orderByDesc('assigned_week_start')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $weeklyNotificationCount = (clone $baseAssignmentsQuery)
            ->where('assigned_week_start', $currentWeekStart)
            ->where(function ($assignmentQuery) {
                $assignmentQuery
                    ->where('status', '!=', EducationAssignment::STATUS_COMPLETED)
                    ->orWhere(function ($quizQuery) {
                        $quizQuery
                            ->where('status', EducationAssignment::STATUS_COMPLETED)
                            ->whereHas('course.quizQuestions')
                            ->whereDoesntHave('passedQuizAttempts');
                    });
            })
            ->count();

        return view('modules.education.learning.index', [
            'assignments' => $assignments,
            'status' => $status,
            'search' => $search,
            'weeklyNotificationCount' => $weeklyNotificationCount,
            'assignmentSummary' => $assignmentSummary,
        ]);
    }

    public function show(EducationAssignment $assignment)
    {
        $this->ensureOwnAssignment($assignment);
        $assignment->load([
            'course.trainer',
            'course.quizQuestions',
            'assigner',
            'latestQuizAttempt',
            'passedQuizAttempt',
        ]);

        return view('modules.education.learning.show', compact('assignment'));
    }

    public function video(EducationAssignment $assignment)
    {
        $this->ensureOwnAssignment($assignment);
        $assignment->load('course');

        $path = $assignment->course?->video_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'video/mp4';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function progress(Request $request, EducationAssignment $assignment)
    {
        $this->ensureOwnAssignment($assignment);

        $data = $request->validate([
            'current_time' => 'required|numeric|min:0|max:86400',
            'duration' => 'required|numeric|min:1|max:86400',
            'is_visible' => 'required|boolean',
            'is_playing' => 'required|boolean',
            'is_ended' => 'nullable|boolean',
        ]);

        $assignment->loadMissing('course');
        $verifiedCourseDuration = (int) ($assignment->course?->duration_seconds ?? 0);
        $reportedDuration = max((int) round((float) $data['duration']), 1);
        $duration = $verifiedCourseDuration > 0
            ? $verifiedCourseDuration
            : max((int) $assignment->duration_seconds, $reportedDuration, 1);
        $currentTime = (int) floor($data['current_time']);
        $currentMax = (int) $assignment->max_watched_seconds;
        $isVisible = $request->boolean('is_visible');
        $isPlaying = $request->boolean('is_playing');
        $isEnded = $request->boolean('is_ended');
        $minimumTrackedForCompletion = max(
            (int) floor($duration * 0.80),
            $duration - 12,
            1
        );
        $canAcceptEnded = $isEnded
            && $currentTime >= ($duration - 2)
            && $currentMax >= $minimumTrackedForCompletion;

        if (($isVisible && $isPlaying) || $canAcceptEnded) {
            $allowedJumpSeconds = 20;

            $newMax = $canAcceptEnded
                ? $duration
                : ($currentTime <= ($currentMax + $allowedJumpSeconds)
                    ? max($currentMax, $currentTime)
                    : $currentMax);
            $newMax = min($newMax, $duration);

            $progress = min(100, round($newMax / $duration * 100, 2));
            $status = $progress >= 95
                ? EducationAssignment::STATUS_COMPLETED
                : EducationAssignment::STATUS_IN_PROGRESS;

            $assignment->fill([
                'duration_seconds' => $duration,
                'watched_seconds' => $newMax,
                'max_watched_seconds' => $newMax,
                'progress_percent' => $progress,
                'status' => $status,
                'started_at' => $assignment->started_at ?: now(),
                'completed_at' => $status === EducationAssignment::STATUS_COMPLETED
                    ? ($assignment->completed_at ?: now())
                    : $assignment->completed_at,
                'last_watched_at' => now(),
            ])->save();
        }

        return response()->json([
            'ok' => true,
            'progress_percent' => (float) $assignment->fresh()->progress_percent,
            'max_watched_seconds' => (int) $assignment->fresh()->max_watched_seconds,
            'status' => $assignment->fresh()->status,
        ]);
    }

    private function ensureOwnAssignment(EducationAssignment $assignment): void
    {
        abort_unless(
            (int) $assignment->user_id === (int) Auth::id() || Auth::user()->isSuperAdmin(),
            403
        );
    }
}
