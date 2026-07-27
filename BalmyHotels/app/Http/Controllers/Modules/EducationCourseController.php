<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $this->guardVideoUploadFailure($request, $course);

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
            ],
        ], [
            'video.uploaded' => 'Video yuklenemedi. Sunucu dosyayi kabul etmedi; buyuk ihtimalle dosya boyutu limiti asildi.',
            'video.mimes' => 'Video formati desteklenmiyor. Lutfen MP4, MOV, AVI, MPEG veya WebM yukleyin.',
        ], [
            'video' => 'video dosyasi',
        ]);

        $data['duration_seconds'] = (int) ($data['duration_seconds'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function iniSizeToKilobytes(string|false $value): int
    {
        if (! is_string($value) || trim($value) === '') {
            return 0;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024),
            'm' => (int) round($number * 1024),
            'k' => (int) round($number),
            default => (int) round($number / 1024),
        };
    }

    private function guardVideoUploadFailure(Request $request, ?EducationCourse $course = null): void
    {
        $videoFile = $request->file('video');
        if ($videoFile) {
            return;
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMaxKb = $this->iniSizeToKilobytes(ini_get('post_max_size'));
        if ($contentLength > 0 && $postMaxKb > 0 && $contentLength > ($postMaxKb * 1024)) {
            Log::warning('Education course video upload exceeded post_max_size before files were parsed.', [
                'content_length' => $contentLength,
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'memory_limit' => ini_get('memory_limit'),
            ]);

            throw ValidationException::withMessages([
                'video' => $this->getVideoUploadErrorMessage(UPLOAD_ERR_INI_SIZE),
            ]);
        }

        $fileInfo = $_FILES['video'] ?? null;
        if (! is_array($fileInfo)) {
            return;
        }

        $errorCode = (int) ($fileInfo['error'] ?? UPLOAD_ERR_OK);

        if ($errorCode === UPLOAD_ERR_OK) {
            return;
        }

        if ($errorCode === UPLOAD_ERR_NO_FILE && $course) {
            return;
        }

        Log::warning('Education course video upload failed before validation.', [
            'error_code' => $errorCode,
            'error_label' => $this->getUploadErrorLabel($errorCode),
            'content_length' => $request->server('CONTENT_LENGTH'),
            'content_type' => $request->server('CONTENT_TYPE'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
            'tmp_dir_writable' => is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()),
            'files_video' => $fileInfo,
        ]);

        throw ValidationException::withMessages([
            'video' => $this->getVideoUploadErrorMessage($errorCode),
        ]);
    }

    private function getVideoUploadErrorMessage(int $errorCode): string
    {
        $limits = sprintf(
            'Canli PHP limitleri: upload_max_filesize=%s, post_max_size=%s.',
            ini_get('upload_max_filesize') ?: '?',
            ini_get('post_max_size') ?: '?'
        );

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Video yuklenemedi: dosya sunucu upload limitini asti. ' . $limits . ' Kod: ' . $errorCode . '.',
            UPLOAD_ERR_PARTIAL => 'Video yuklenemedi: dosya sunucuya eksik ulasti. Baglanti veya proxy kesintisi olabilir. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_NO_TMP_DIR => 'Video yuklenemedi: sunucuda gecici upload klasoru bulunamadi. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_CANT_WRITE => 'Video yuklenemedi: sunucu dosyayi diske yazamadi. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_EXTENSION => 'Video yuklenemedi: bir PHP eklentisi yuklemeyi durdurdu. Kod: ' . $errorCode . '.',
            default => 'Video yuklenemedi. ' . $limits . ' Kod: ' . $errorCode . '.',
        };
    }

    private function getUploadErrorLabel(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_OK => 'UPLOAD_ERR_OK',
            UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE',
            UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
            UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL',
            UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
            UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
            UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION',
            default => 'UPLOAD_ERR_UNKNOWN',
        };
    }
}
