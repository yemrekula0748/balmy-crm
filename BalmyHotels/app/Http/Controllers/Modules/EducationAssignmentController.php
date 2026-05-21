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
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $courses = EducationCourse::where('is_active', true)
            ->where('language', $language)
            ->orderBy('title')
            ->get();

        $learners = $this->learnerQuery()
            ->with(['branch', 'department'])
            ->orderBy('name')
            ->get();

        $assignments = EducationAssignment::with(['course', 'learner.branch', 'assigner'])
            ->where('assigned_week_start', $weekStart->toDateString())
            ->when($language, fn ($query) => $query->where('language', $language))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('modules.education.assignments.index', [
            'languages' => EducationCourse::LANGUAGES,
            'language' => $language,
            'weekStart' => $weekStart,
            'courses' => $courses,
            'learners' => $learners,
            'assignments' => $assignments,
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
                'education_course_id' => 'Secilen egitim, secilen dil ile uyusmuyor.',
            ]);
        }

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek()->toDateString();
        $learnerIds = $this->learnerQuery()
            ->whereIn('id', $data['user_ids'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

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
            ->with('success', count($learnerIds) . ' ogrenen icin egitim atamasi hazirlandi.');
    }

    public function destroy(EducationAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Egitim atamasi kaldirildi.');
    }

    private function learnerQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('userRoles', fn ($query) => $query->where('role_name', 'ogrenen'));
    }
}
