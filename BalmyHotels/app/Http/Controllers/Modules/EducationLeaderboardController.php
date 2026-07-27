<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationAssignment;
use App\Models\EducationCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EducationLeaderboardController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('education_learning', ['index'], [], [], [], []);
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

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $user = Auth::user();
        $canSeeAll = $user->hasPermission('education_reports', 'index');

        $assignments = EducationAssignment::with([
                'course.quizQuestions',
                'learner.branch',
                'learner.department',
                'quizAttempts',
                'passedQuizAttempt',
            ])
            ->whereBetween('assigned_week_start', [$from->toDateString(), $to->toDateString()])
            ->when($language, fn ($query) => $query->where('language', $language))
            ->when(! $canSeeAll, function ($query) use ($user) {
                $branchIds = $user->visibleBranchIds();

                if (! empty($branchIds)) {
                    $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery->whereIn('branch_id', $branchIds));
                    return;
                }

                $query->where('user_id', $user->id);
            })
            ->get();

        $leaderboardRows = $this->buildLeaderboardRows($assignments);
        $topLeaders = $leaderboardRows->take(3);
        $myRank = $leaderboardRows->firstWhere('learner_id', $user->id);

        return view('modules.education.leaderboard.index', [
            'languages' => EducationCourse::LANGUAGES,
            'language' => $language,
            'from' => $from,
            'to' => $to,
            'scopeLabel' => $canSeeAll ? 'Tum ekip' : ($user->branch?->name ?? 'Subem'),
            'summary' => $this->buildSummary($leaderboardRows, $assignments),
            'leaderboardRows' => $leaderboardRows,
            'topLeaders' => $topLeaders,
            'myRank' => $myRank,
        ]);
    }

    private function buildLeaderboardRows(Collection $assignments): Collection
    {
        $rows = $assignments
            ->filter(fn (EducationAssignment $assignment) => $assignment->learner)
            ->groupBy('user_id')
            ->map(function (Collection $learnerAssignments) {
                $learner = $learnerAssignments->first()->learner;
                $assignmentCount = $learnerAssignments->count();
                $completedCount = $learnerAssignments
                    ->where('status', EducationAssignment::STATUS_COMPLETED)
                    ->count();
                $approvedCount = $learnerAssignments
                    ->filter(fn (EducationAssignment $assignment) => $assignment->training_approved)
                    ->count();
                $quizPassedCount = $learnerAssignments
                    ->filter(fn (EducationAssignment $assignment) => $assignment->quiz_passed)
                    ->count();

                $bestAttempts = $learnerAssignments
                    ->map(fn (EducationAssignment $assignment) => $this->bestAttemptForAssignment($assignment))
                    ->filter()
                    ->values();

                $correctAnswers = (int) $bestAttempts->sum(fn ($attempt) => (int) $attempt->correct_answers);
                $totalQuestions = (int) $bestAttempts->sum(fn ($attempt) => (int) $attempt->total_questions);
                $avgProgress = $assignmentCount > 0
                    ? round((float) $learnerAssignments->avg('progress_percent'), 1)
                    : 0;
                $completionRate = $assignmentCount > 0
                    ? round($completedCount / $assignmentCount * 100, 1)
                    : 0;
                $quizSuccessRate = $totalQuestions > 0
                    ? round($correctAnswers / $totalQuestions * 100, 1)
                    : null;

                $score = ($approvedCount * 120)
                    + ($completedCount * 80)
                    + ($quizPassedCount * 60)
                    + ($correctAnswers * 10)
                    + (int) round($avgProgress);

                return [
                    'rank' => 0,
                    'learner_id' => (int) $learner->id,
                    'learner_name' => $learner->name,
                    'branch_name' => $learner->branch->name ?? '-',
                    'department_name' => $learner->department->name ?? '-',
                    'assignment_count' => $assignmentCount,
                    'completed_count' => $completedCount,
                    'approved_count' => $approvedCount,
                    'quiz_passed_count' => $quizPassedCount,
                    'quiz_attempt_count' => (int) $learnerAssignments->sum(fn (EducationAssignment $assignment) => $assignment->quizAttempts->count()),
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'quiz_success_rate' => $quizSuccessRate,
                    'avg_progress' => $avgProgress,
                    'completion_rate' => $completionRate,
                    'watched_minutes' => (int) round($learnerAssignments->sum('max_watched_seconds') / 60),
                    'score' => $score,
                    'last_activity_at' => $this->lastActivityForAssignments($learnerAssignments),
                ];
            })
            ->values()
            ->sort(function (array $first, array $second) {
                $result = [
                    $second['score'],
                    $second['approved_count'],
                    $second['completed_count'],
                    $second['correct_answers'],
                    $second['avg_progress'],
                ] <=> [
                    $first['score'],
                    $first['approved_count'],
                    $first['completed_count'],
                    $first['correct_answers'],
                    $first['avg_progress'],
                ];

                return $result !== 0
                    ? $result
                    : strcasecmp($first['learner_name'], $second['learner_name']);
            })
            ->values();

        return $rows->map(function (array $row, int $index) {
            $row['rank'] = $index + 1;

            return $row;
        });
    }

    private function bestAttemptForAssignment(EducationAssignment $assignment)
    {
        return $assignment->quizAttempts
            ->sort(function ($first, $second) {
                return [
                    (int) $second->correct_answers,
                    (int) $second->passed,
                    (int) $second->total_questions,
                    optional($second->submitted_at)->timestamp ?? 0,
                ] <=> [
                    (int) $first->correct_answers,
                    (int) $first->passed,
                    (int) $first->total_questions,
                    optional($first->submitted_at)->timestamp ?? 0,
                ];
            })
            ->first();
    }

    private function lastActivityForAssignments(Collection $assignments)
    {
        return $assignments
            ->flatMap(function (EducationAssignment $assignment) {
                $latestAttempt = $assignment->quizAttempts
                    ->sortByDesc(fn ($attempt) => optional($attempt->submitted_at)->timestamp ?? 0)
                    ->first();

                return [
                    $assignment->last_watched_at,
                    $assignment->completed_at,
                    $latestAttempt?->submitted_at,
                ];
            })
            ->filter()
            ->sortByDesc(fn ($date) => $date->timestamp)
            ->first();
    }

    private function buildSummary(Collection $leaderboardRows, Collection $assignments): array
    {
        return [
            'learner_count' => $leaderboardRows->count(),
            'assignment_count' => $assignments->count(),
            'completed_count' => (int) $leaderboardRows->sum('completed_count'),
            'approved_count' => (int) $leaderboardRows->sum('approved_count'),
            'correct_answers' => (int) $leaderboardRows->sum('correct_answers'),
            'avg_score' => $leaderboardRows->count() > 0
                ? round((float) $leaderboardRows->avg('score'))
                : 0,
        ];
    }
}
