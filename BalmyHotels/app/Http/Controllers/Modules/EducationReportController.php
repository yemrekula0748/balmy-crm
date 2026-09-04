<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Department;
use App\Models\EducationAssignment;
use App\Models\EducationCourse;
use App\Models\EducationEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EducationReportController extends BaseModuleController
{
    private const DETAIL_STATUSES = [
        'approved',
        'completed',
        'quiz_passed',
        'in_progress',
        'not_started',
        'overdue',
    ];

    public function __construct()
    {
        $this->requirePermission('education_reports', ['index', 'export'], [], [], [], []);
    }

    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $branches = Branch::orderBy('name')->get();
        $departments = Department::with('branch')
            ->when($filters['branch_id'], fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->orderBy('name')
            ->get();

        $courses = EducationCourse::with('trainer')
            ->withCount('quizQuestions')
            ->when($filters['language'], fn ($query) => $query->where('language', $filters['language']))
            ->orderByDesc('is_active')
            ->orderBy('title')
            ->get();

        $globalAssignments = $this->globalAssignmentQuery($filters)
            ->with([
                'course.quizQuestions:id,education_course_id',
                'learner:id,name,branch_id,department_id',
                'passedQuizAttempt:education_quiz_attempts.id,education_quiz_attempts.education_assignment_id,education_quiz_attempts.passed',
            ])
            ->get();

        $contextAssignments = $filters['course_id']
            ? $globalAssignments->where('education_course_id', $filters['course_id'])->values()
            : $globalAssignments;

        $courseAssignments = $globalAssignments->groupBy('education_course_id');
        $courseStats = $courses
            ->map(function (EducationCourse $course) use ($courseAssignments) {
                $aggregate = $this->aggregateAssignments(
                    $courseAssignments->get($course->id, collect())
                );

                return array_merge($aggregate, ['course' => $course]);
            })
            ->sortBy([
                fn (array $left, array $right) => $right['assigned'] <=> $left['assigned'],
                fn (array $left, array $right) => strnatcasecmp($left['course']->title, $right['course']->title),
            ])
            ->values();

        $departmentStats = $this->buildDepartmentStats($contextAssignments, $departments);
        $stats = $this->aggregateAssignments($contextAssignments);

        $assignments = $this->detailAssignmentQuery($filters)
            ->with([
                'course.quizQuestions:id,education_course_id',
                'learner.branch',
                'learner.department',
                'latestQuizAttempt',
                'passedQuizAttempt:education_quiz_attempts.id,education_quiz_attempts.education_assignment_id,education_quiz_attempts.passed',
            ])
            ->withCount('quizAttempts')
            ->orderByDesc('assigned_week_start')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $events = $this->eventReportQuery($filters)
            ->orderByDesc('starts_at')
            ->get();

        $selectedCourse = $filters['course_id']
            ? EducationCourse::find($filters['course_id'])
            : null;
        $selectedDepartment = ctype_digit((string) $filters['department_id'])
            ? Department::with('branch')->find((int) $filters['department_id'])
            : null;

        return view('modules.education.reports.index', [
            'languages' => EducationCourse::LANGUAGES,
            'filters' => $filters,
            'branches' => $branches,
            'departments' => $departments,
            'courses' => $courses,
            'stats' => $stats,
            'courseStats' => $courseStats,
            'departmentStats' => $departmentStats,
            'assignments' => $assignments,
            'events' => $events,
            'selectedCourse' => $selectedCourse,
            'selectedDepartment' => $selectedDepartment,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $query = $this->detailAssignmentQuery($filters)
            ->with([
                'course.quizQuestions:id,education_course_id',
                'learner.branch',
                'learner.department',
                'latestQuizAttempt',
                'passedQuizAttempt:education_quiz_attempts.id,education_quiz_attempts.education_assignment_id,education_quiz_attempts.passed',
            ])
            ->withCount('quizAttempts')
            ->orderBy('id');

        $filename = 'egitim-raporu-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Personel',
                'Departman',
                'Şube',
                'Eğitim Modülü',
                'Dil',
                'Atama Tarihi',
                'Son Tarih',
                'İlerleme (%)',
                'Video Durumu',
                'Eğitim Onayı',
                'Quiz Durumu',
                'Son Quiz Puanı',
                'Quiz Denemesi',
                'Başlama Tarihi',
                'Tamamlama Tarihi',
                'Son İşlem',
            ], ';');

            $query->chunkById(250, function ($assignments) use ($output) {
                foreach ($assignments as $assignment) {
                    $latestAttempt = $assignment->latestQuizAttempt;
                    $quizScore = $latestAttempt && $latestAttempt->total_questions > 0
                        ? round($latestAttempt->correct_answers / $latestAttempt->total_questions * 100, 1)
                        : null;

                    fputcsv($output, [
                        $assignment->learner?->name ?? '-',
                        $assignment->learner?->department?->name ?? 'Departman belirtilmemiş',
                        $assignment->learner?->branch?->name ?? '-',
                        $assignment->course?->title ?? '-',
                        strtoupper((string) $assignment->language),
                        $assignment->assigned_week_start?->format('d.m.Y'),
                        $assignment->due_at?->format('d.m.Y H:i'),
                        number_format((float) $assignment->progress_percent, 1, ',', ''),
                        $assignment->video_completed ? 'Tamamlandı' : $this->statusLabel($assignment->status),
                        $assignment->training_approved ? 'Onaylandı' : 'Bekliyor',
                        $this->quizStatusLabel($assignment),
                        $quizScore !== null ? '%' . number_format($quizScore, 1, ',', '') : '',
                        (int) $assignment->quiz_attempts_count,
                        $assignment->started_at?->format('d.m.Y H:i'),
                        $assignment->completed_at?->format('d.m.Y H:i'),
                        $assignment->last_watched_at?->format('d.m.Y H:i'),
                    ], ';');
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $language = (string) $request->get('language', '');
        if (! array_key_exists($language, EducationCourse::LANGUAGES)) {
            $language = '';
        }

        $from = $this->parseReportDate($request->get('from'), Carbon::now()->startOfYear())->startOfDay();
        $to = $this->parseReportDate($request->get('to'), Carbon::now())->endOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $branchId = ctype_digit((string) $request->get('branch_id'))
            ? (int) $request->get('branch_id')
            : null;
        if ($branchId && ! Branch::whereKey($branchId)->exists()) {
            $branchId = null;
        }

        $departmentId = (string) $request->get('department_id', '');
        if ($departmentId !== '__none__' && ! ctype_digit($departmentId)) {
            $departmentId = '';
        }
        if (ctype_digit($departmentId)) {
            $departmentExists = Department::query()
                ->whereKey((int) $departmentId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->exists();
            if (! $departmentExists) {
                $departmentId = '';
            }
        }

        $courseId = ctype_digit((string) $request->get('course_id'))
            ? (int) $request->get('course_id')
            : null;
        if ($courseId) {
            $courseExists = EducationCourse::query()
                ->whereKey($courseId)
                ->when($language, fn ($query) => $query->where('language', $language))
                ->exists();
            if (! $courseExists) {
                $courseId = null;
            }
        }

        $status = (string) $request->get('status', '');
        if (! in_array($status, self::DETAIL_STATUSES, true)) {
            $status = '';
        }

        return [
            'language' => $language,
            'from' => $from,
            'to' => $to,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'course_id' => $courseId,
            'status' => $status,
            'search' => mb_substr(trim((string) $request->get('search', '')), 0, 100),
        ];
    }

    private function parseReportDate(mixed $value, Carbon $fallback): Carbon
    {
        if (! $value) {
            return $fallback->copy();
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    private function globalAssignmentQuery(array $filters): Builder
    {
        return EducationAssignment::query()
            ->whereBetween('assigned_week_start', [
                $filters['from']->toDateString(),
                $filters['to']->toDateString(),
            ])
            ->when($filters['language'], fn ($query) => $query->where('language', $filters['language']))
            ->when($filters['branch_id'], function ($query) use ($filters) {
                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery
                    ->where('branch_id', $filters['branch_id']));
            })
            ->when($filters['department_id'] === '__none__', function ($query) {
                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery->whereNull('department_id'));
            })
            ->when(ctype_digit((string) $filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery
                    ->where('department_id', (int) $filters['department_id']));
            });
    }

    private function detailAssignmentQuery(array $filters): Builder
    {
        $query = $this->globalAssignmentQuery($filters)
            ->when($filters['course_id'], fn ($assignmentQuery) => $assignmentQuery
                ->where('education_course_id', $filters['course_id']))
            ->when($filters['search'] !== '', function ($assignmentQuery) use ($filters) {
                $searchTerm = '%' . addcslashes($filters['search'], '%_\\') . '%';

                $assignmentQuery->where(function ($searchQuery) use ($searchTerm) {
                    $searchQuery
                        ->whereHas('learner', fn ($learnerQuery) => $learnerQuery->where('name', 'like', $searchTerm))
                        ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('title', 'like', $searchTerm));
                });
            });

        return $this->applyStatusFilter($query, $filters['status']);
    }

    private function applyStatusFilter(Builder $query, string $status): Builder
    {
        if ($status === 'approved') {
            return $query->where(function ($approvedQuery) {
                $approvedQuery
                    ->where(function ($withoutQuizQuery) {
                        $withoutQuizQuery
                            ->whereHas('course', fn ($courseQuery) => $courseQuery->whereDoesntHave('quizQuestions'))
                            ->where(fn ($videoQuery) => $videoQuery
                                ->where('status', EducationAssignment::STATUS_COMPLETED)
                                ->orWhere('progress_percent', '>=', 95));
                    })
                    ->orWhere(function ($withQuizQuery) {
                        $withQuizQuery
                            ->whereHas('passedQuizAttempt')
                            ->where(fn ($videoQuery) => $videoQuery
                                ->where('status', EducationAssignment::STATUS_COMPLETED)
                                ->orWhere('progress_percent', '>=', 95));
                    });
            });
        }

        if ($status === 'completed') {
            return $query->where(fn ($videoQuery) => $videoQuery
                ->where('status', EducationAssignment::STATUS_COMPLETED)
                ->orWhere('progress_percent', '>=', 95));
        }

        if ($status === 'quiz_passed') {
            return $query->whereHas('passedQuizAttempt');
        }

        if ($status === 'overdue') {
            return $query
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->where(function ($notApprovedQuery) {
                    $notApprovedQuery
                        ->where(function ($videoIncompleteQuery) {
                            $videoIncompleteQuery
                                ->where('status', '!=', EducationAssignment::STATUS_COMPLETED)
                                ->where('progress_percent', '<', 95);
                        })
                        ->orWhere(function ($quizIncompleteQuery) {
                            $quizIncompleteQuery
                                ->whereHas('course.quizQuestions')
                                ->whereDoesntHave('passedQuizAttempt');
                        });
                });
        }

        if (in_array($status, [
            EducationAssignment::STATUS_IN_PROGRESS,
            EducationAssignment::STATUS_NOT_STARTED,
        ], true)) {
            return $query->where('status', $status);
        }

        return $query;
    }

    private function aggregateAssignments(Collection $assignments): array
    {
        $assigned = $assignments->count();
        $videoCompleted = $assignments->filter(fn (EducationAssignment $assignment) => $assignment->video_completed)->count();
        $approved = $assignments->filter(fn (EducationAssignment $assignment) => $assignment->training_approved)->count();
        $overdue = $assignments->filter(fn (EducationAssignment $assignment) => $assignment->due_at
            && $assignment->due_at->isPast()
            && ! $assignment->training_approved)->count();

        return [
            'assigned' => $assigned,
            'learners' => $assignments->pluck('user_id')->unique()->count(),
            'video_completed' => $videoCompleted,
            'approved' => $approved,
            'quiz_passed' => $assignments->filter(fn (EducationAssignment $assignment) => $assignment->quiz_passed)->count(),
            'in_progress' => $assignments->where('status', EducationAssignment::STATUS_IN_PROGRESS)->count(),
            'not_started' => $assignments->where('status', EducationAssignment::STATUS_NOT_STARTED)->count(),
            'overdue' => $overdue,
            'avg_progress' => $assigned > 0 ? round((float) $assignments->avg('progress_percent'), 1) : 0,
            'completion_rate' => $assigned > 0 ? round($approved / $assigned * 100, 1) : 0,
        ];
    }

    private function buildDepartmentStats(Collection $assignments, Collection $departments): Collection
    {
        $departmentMap = $departments->keyBy('id');

        return $assignments
            ->groupBy(fn (EducationAssignment $assignment) => $assignment->learner?->department_id ?: '__none__')
            ->map(function (Collection $departmentAssignments, int|string $departmentId) use ($departmentMap) {
                $department = $departmentId === '__none__'
                    ? null
                    : $departmentMap->get((int) $departmentId);

                return array_merge($this->aggregateAssignments($departmentAssignments), [
                    'department_id' => $departmentId,
                    'department' => $department,
                    'name' => $department?->name ?? 'Departman belirtilmemiş',
                    'branch_name' => $department?->branch?->name,
                ]);
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function eventReportQuery(array $filters): Builder
    {
        $responseFilter = function ($query) use ($filters) {
            $query
                ->when($filters['branch_id'], function ($responseQuery) use ($filters) {
                    $responseQuery->whereHas('learner', fn ($learnerQuery) => $learnerQuery
                        ->where('branch_id', $filters['branch_id']));
                })
                ->when($filters['department_id'] === '__none__', function ($responseQuery) {
                    $responseQuery->whereHas('learner', fn ($learnerQuery) => $learnerQuery->whereNull('department_id'));
                })
                ->when(ctype_digit((string) $filters['department_id']), function ($responseQuery) use ($filters) {
                    $responseQuery->whereHas('learner', fn ($learnerQuery) => $learnerQuery
                        ->where('department_id', (int) $filters['department_id']));
                });
        };

        return EducationEvent::query()
            ->with([
                'trainer',
                'responses' => $responseFilter,
                'responses.learner.department',
            ])
            ->whereBetween('starts_at', [$filters['from'], $filters['to']])
            ->when($filters['language'], fn ($query) => $query->where('language', $filters['language']));
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            EducationAssignment::STATUS_COMPLETED => 'Tamamlandı',
            EducationAssignment::STATUS_IN_PROGRESS => 'Devam ediyor',
            default => 'Başlamadı',
        };
    }

    private function quizStatusLabel(EducationAssignment $assignment): string
    {
        if (! $assignment->course || ! $assignment->course->has_quiz) {
            return 'Quiz yok';
        }

        if ($assignment->quiz_passed) {
            return 'Başarılı';
        }

        if ($assignment->latestQuizAttempt) {
            return 'Tekrar gerekli';
        }

        return $assignment->video_completed ? 'Quiz bekliyor' : 'Video bekleniyor';
    }
}
