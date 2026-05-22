<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EducationCourseController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'education_courses',
            ['index'],
            ['show', 'video'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $language = $request->get('language');

        $courses = EducationCourse::with(['trainer'])
            ->withCount(['assignments', 'quizQuestions'])
            ->when($language, fn ($query) => $query->where('language', $language))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('modules.education.courses.index', [
            'courses' => $courses,
            'languages' => EducationCourse::LANGUAGES,
            'language' => $language,
        ]);
    }

    public function create()
    {
        return view('modules.education.courses.form', [
            'course' => new EducationCourse(),
            'languages' => EducationCourse::LANGUAGES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCourse($request);
        $data['trainer_id'] = Auth::id();
        $data['video_path'] = $request->file('video')->store('education/videos', 'public');
        $data['video_original_name'] = $request->file('video')->getClientOriginalName();
        unset($data['video']);

        EducationCourse::create($data);

        return redirect()->route('education.courses.index')->with('success', 'Egitim icerigi olusturuldu.');
    }

    public function show(EducationCourse $course)
    {
        $course->load([
            'trainer',
            'quizQuestions.options',
            'assignments.learner.branch',
            'assignments.latestQuizAttempt',
            'assignments.passedQuizAttempt',
        ]);

        return view('modules.education.courses.show', compact('course'));
    }

    public function video(EducationCourse $course)
    {
        abort_unless($course->video_path && Storage::disk('public')->exists($course->video_path), 404);

        $absolutePath = Storage::disk('public')->path($course->video_path);
        $mimeType = Storage::disk('public')->mimeType($course->video_path) ?: 'video/mp4';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . basename($course->video_path) . '"',
        ]);
    }

    public function edit(EducationCourse $course)
    {
        return view('modules.education.courses.form', [
            'course' => $course,
            'languages' => EducationCourse::LANGUAGES,
        ]);
    }

    public function update(Request $request, EducationCourse $course)
    {
        $data = $this->validateCourse($request, $course);

        if ($request->hasFile('video')) {
            if ($course->video_path) {
                Storage::disk('public')->delete($course->video_path);
            }

            $data['video_path'] = $request->file('video')->store('education/videos', 'public');
            $data['video_original_name'] = $request->file('video')->getClientOriginalName();
        }
        unset($data['video']);

        $course->update($data);

        return redirect()->route('education.courses.index')->with('success', 'Egitim icerigi guncellendi.');
    }

    public function destroy(EducationCourse $course)
    {
        if ($course->video_path) {
            Storage::disk('public')->delete($course->video_path);
        }

        $course->delete();

        return redirect()->route('education.courses.index')->with('success', 'Egitim icerigi silindi.');
    }

    private function validateCourse(Request $request, ?EducationCourse $course = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'language' => ['required', Rule::in(array_keys(EducationCourse::LANGUAGES))],
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
            'is_active' => 'nullable|boolean',
            'video' => [
                $course ? 'nullable' : 'required',
                'file',
                'mimes:mp4,mov,avi,mpeg,webm',
                'max:512000',
            ],
        ]);

        $data['duration_seconds'] = (int) ($data['duration_seconds'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
