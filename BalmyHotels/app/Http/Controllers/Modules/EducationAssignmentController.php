<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationAssignment;
use App\Models\EducationCourse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EducationAssignmentController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'education_assignments',
            ['index'],
            [],
            ['store'],
            [],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $language = $request->get('language', 'tr');
        $assignmentSearch = mb_substr(trim((string) $request->get('assignment_search', '')), 0, 100);
        $assignmentDepartment = (string) $request->get('assignment_department', '');
        if ($assignmentDepartment !== ''
            && $assignmentDepartment !== '__none__'
            && ! ctype_digit($assignmentDepartment)) {
            $assignmentDepartment = '';
        }
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $courses = EducationCourse::where('is_active', true)
            ->where('language', $language)
            ->orderBy('title')
            ->get();

        $learners = $this->learnerQuery()
            ->with([
                'branch',
                'department',
                'pdksEmployee:id,user_id,title,employment_type,raw_payload',
            ])
            ->orderBy('name')
            ->get();

        $learners->each(function (User $learner) {
            $learner->setAttribute(
                'education_personnel_origin',
                $learner->pdksEmployee?->personnelOrigin() ?? 'unknown'
            );
            $learner->setAttribute(
                'education_nationality',
                $learner->pdksEmployee?->nationality()
            );
        });

        $learnerOriginCounts = $learners
            ->countBy('education_personnel_origin')
            ->map(fn ($count) => (int) $count)
            ->all();

        $departments = $learners
            ->pluck('department')
            ->filter()
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $assignments = EducationAssignment::with(['course', 'learner.branch', 'learner.department', 'assigner'])
            ->where('assigned_week_start', $weekStart->toDateString())
            ->when($language, fn ($query) => $query->where('language', $language))
            ->when($assignmentSearch !== '', function ($query) use ($assignmentSearch) {
                $searchTerm = '%' . addcslashes($assignmentSearch, '%_\\') . '%';

                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery->where('name', 'like', $searchTerm));
            })
            ->when($assignmentDepartment === '__none__', function ($query) {
                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery->whereNull('department_id'));
            })
            ->when(ctype_digit($assignmentDepartment), function ($query) use ($assignmentDepartment) {
                $query->whereHas('learner', fn ($learnerQuery) => $learnerQuery->where('department_id', (int) $assignmentDepartment));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('modules.education.assignments.index', [
            'languages' => EducationCourse::LANGUAGES,
            'language' => $language,
            'weekStart' => $weekStart,
            'courses' => $courses,
            'learners' => $learners,
            'departments' => $departments,
            'learnerOriginCounts' => $learnerOriginCounts,
            'assignments' => $assignments,
            'assignmentSearch' => $assignmentSearch,
            'assignmentDepartment' => $assignmentDepartment,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'education_course_id' => 'required|exists:education_courses,id',
            'language' => ['required', Rule::in(array_keys(EducationCourse::LANGUAGES))],
            'week_start' => 'required|date',
            'due_at' => 'nullable|date',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        $course = EducationCourse::findOrFail($data['education_course_id']);
        if ($course->language !== $data['language']) {
            return back()->withInput()->withErrors([
                'education_course_id' => 'Seçilen eğitim, seçilen dil ile uyuşmuyor.',
            ]);
        }

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek()->toDateString();
        $requestedLearnerIds = array_values(array_unique(array_map('intval', $data['user_ids'])));
        $learnerIds = $this->learnerQuery()
            ->whereIn('id', $requestedLearnerIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($requestedLearnerIds) !== count(array_unique($learnerIds))) {
            return back()->withInput()->withErrors([
                'user_ids' => 'Seçilen kişilerden bazılarında Öğrenen rolü bulunmuyor. Lütfen seçimi kontrol edip tekrar kaydedin.',
            ]);
        }

        foreach ($learnerIds as $learnerId) {
            $assignment = EducationAssignment::firstOrNew([
                'education_course_id' => $course->id,
                'user_id' => $learnerId,
                'language' => $data['language'],
                'assigned_week_start' => $weekStart,
            ]);

            $assignment->fill([
                'assigned_by' => Auth::id(),
                'due_at' => $data['due_at'] ?? null,
                'duration_seconds' => $assignment->exists
                    ? max((int) $assignment->duration_seconds, (int) $course->duration_seconds)
                    : (int) $course->duration_seconds,
                'status' => $assignment->status ?: EducationAssignment::STATUS_NOT_STARTED,
            ]);
            $assignment->save();
        }

        return redirect()
            ->route('education.assignments.index', [
                'language' => $data['language'],
                'week_start' => $weekStart,
            ])
            ->with('success', count($learnerIds) . ' öğrenen için eğitim ataması hazırlandı.');
    }

    public function destroy(EducationAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Eğitim ataması kaldırıldı.');
    }

    private function learnerQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->learners();
    }
}
