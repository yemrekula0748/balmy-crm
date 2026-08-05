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
        $status = $request->get('status');
        $currentWeekStart = Carbon::now()->startOfWeek()->toDateString();

        $assignments = EducationAssignment::with([
                'course.trainer',
                'course.quizQuestions',
                'assigner',
                'latestQuizAttempt',
                'passedQuizAttempt',
            ])
            ->where('user_id', Auth::id())
            ->whereHas('course', fn ($query) => $query->where('is_active', true))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN assigned_week_start = ? THEN 0 ELSE 1 END", [$currentWeekStart])
            ->orderByDesc('assigned_week_start')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $weeklyNotificationCount = EducationAssignment::where('user_id', Auth::id())
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
            'weeklyNotificationCount' => $weeklyNotificationCount,
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

        $duration = max((int) floor($data['duration']), (int) $assignment->duration_seconds, 1);
        $currentTime = (int) floor($data['current_time']);
        $currentMax = (int) $assignment->max_watched_seconds;
        $isVisible = $request->boolean('is_visible');
        $isPlaying = $request->boolean('is_playing');
        $isEnded = $request->boolean('is_ended');

        if ($isVisible && ($isPlaying || $isEnded)) {
            $allowedJumpSeconds = 20;
            $canAcceptEnded = $isEnded
                && $currentTime >= ($duration - 2)
                && $currentMax >= (int) floor($duration * 0.95);

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
