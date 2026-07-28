<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EducationCourseController extends BaseModuleController
{
    private const VIDEO_UPLOAD_DIRECTORY = 'education/video-uploads';
    private const VIDEO_UPLOAD_TTL_SECONDS = 86400;
    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'mpeg', 'mpg', 'webm'];

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
            'serverUploadLimitText' => $this->getServerUploadLimitText(),
        ]);
    }

    public function startVideoUpload(Request $request): JsonResponse
    {
        $this->authorizeVideoUpload();
        $this->purgeExpiredVideoUploads();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1'],
            'mime' => ['nullable', 'string', 'max:255'],
        ]);

        $originalName = basename(str_replace('\\', '/', $data['name']));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (! in_array($extension, self::VIDEO_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'video' => 'Video formati desteklenmiyor. Lutfen MP4, MOV, AVI, MPEG veya WebM yukleyin.',
            ]);
        }

        $token = bin2hex(random_bytes(20));
        $metadata = [
            'token' => $token,
            'user_id' => (int) Auth::id(),
            'original_name' => $originalName,
            'expected_size' => (int) $data['size'],
            'client_mime' => (string) ($data['mime'] ?? ''),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $disk = Storage::disk('local');
        $metadataStored = $disk->put(
            $this->videoUploadMetadataPath($token),
            json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        $partFileStored = $disk->put($this->videoUploadPartPath($token), '');

        if (! $metadataStored || ! $partFileStored) {
            $this->deleteVideoUploadFiles($token);
            abort(500, 'Video icin gecici yukleme alani olusturulamadi.');
        }

        return response()->json([
            'upload_token' => $token,
            'uploaded_bytes' => 0,
        ], 201);
    }

    public function uploadVideoChunk(Request $request): JsonResponse
    {
        $this->authorizeVideoUpload();

        $data = $request->validate([
            'upload_token' => ['required', 'string', 'regex:/^[a-f0-9]{40}$/'],
            'offset' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file'],
        ]);

        $token = $data['upload_token'];
        $metadata = $this->getVideoUploadMetadata($token);
        $this->authorizeVideoUploadOwner($metadata);

        $chunk = $request->file('chunk');
        if (! $chunk || ! $chunk->isValid()) {
            throw ValidationException::withMessages([
                'video' => 'Video parcasi sunucuya eksik ulasti. Yukleme otomatik olarak yeniden denenebilir.',
            ]);
        }

        $chunkSize = (int) $chunk->getSize();
        if ($chunkSize < 1) {
            throw ValidationException::withMessages([
                'video' => 'Bos video parcasi kabul edilmedi.',
            ]);
        }

        $partPath = Storage::disk('local')->path($this->videoUploadPartPath($token));
        $output = fopen($partPath, 'c+b');
        $input = fopen($chunk->getRealPath(), 'rb');

        if ($output === false || $input === false) {
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_resource($input)) {
                fclose($input);
            }
            abort(500, 'Video parcasi gecici dosyaya yazilamadi.');
        }

        try {
            if (! flock($output, LOCK_EX)) {
                abort(500, 'Video yukleme dosyasi kilitlenemedi.');
            }

            fseek($output, 0, SEEK_END);
            $currentSize = (int) ftell($output);
            $requestedOffset = (int) $data['offset'];

            if ($currentSize !== $requestedOffset) {
                return response()->json([
                    'message' => 'Yukleme sirasi yenilendi.',
                    'uploaded_bytes' => $currentSize,
                    'complete' => $currentSize === (int) $metadata['expected_size'],
                ], 409);
            }

            $expectedSize = (int) $metadata['expected_size'];
            if (($currentSize + $chunkSize) > $expectedSize) {
                throw ValidationException::withMessages([
                    'video' => 'Video parcasi beklenen dosya boyutunu asiyor.',
                ]);
            }

            $writtenBytes = stream_copy_to_stream($input, $output);
            fflush($output);

            if ($writtenBytes === false || (int) $writtenBytes !== $chunkSize) {
                ftruncate($output, $currentSize);
                fflush($output);
                abort(500, 'Video parcasi diske tam olarak yazilamadi.');
            }

            $uploadedBytes = $currentSize + (int) $writtenBytes;
        } finally {
            flock($output, LOCK_UN);
            fclose($input);
            fclose($output);
        }

        $metadata['updated_at'] = now()->toIso8601String();
        Storage::disk('local')->put(
            $this->videoUploadMetadataPath($token),
            json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return response()->json([
            'uploaded_bytes' => $uploadedBytes,
            'complete' => $uploadedBytes === (int) $metadata['expected_size'],
        ]);
    }

    public function cancelVideoUpload(Request $request)
    {
        $this->authorizeVideoUpload();

        $data = $request->validate([
            'upload_token' => ['required', 'string', 'regex:/^[a-f0-9]{40}$/'],
        ]);

        $metadata = $this->findVideoUploadMetadata($data['upload_token']);
        if ($metadata) {
            $this->authorizeVideoUploadOwner($metadata);
            $this->deleteVideoUploadFiles($data['upload_token']);
        }

        return response()->noContent();
    }

    public function store(Request $request)
    {
        $data = $this->validateCourse($request);
        $data['trainer_id'] = Auth::id();
        $video = $this->storeIncomingVideo($request);
        $data['video_path'] = $video['path'];
        $data['video_original_name'] = $video['original_name'];
        unset($data['video'], $data['video_upload_token']);

        try {
            EducationCourse::create($data);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($video['path']);
            throw $exception;
        }

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
            'serverUploadLimitText' => $this->getServerUploadLimitText(),
        ]);
    }

    public function update(Request $request, EducationCourse $course)
    {
        $data = $this->validateCourse($request, $course);
        $oldVideoPath = $course->video_path;
        $replacementVideo = null;

        if ($request->filled('video_upload_token') || $request->hasFile('video')) {
            $replacementVideo = $this->storeIncomingVideo($request);
            $data['video_path'] = $replacementVideo['path'];
            $data['video_original_name'] = $replacementVideo['original_name'];
        }
        unset($data['video'], $data['video_upload_token']);

        try {
            $course->update($data);
        } catch (\Throwable $exception) {
            if ($replacementVideo) {
                Storage::disk('public')->delete($replacementVideo['path']);
            }
            throw $exception;
        }

        if ($replacementVideo && $oldVideoPath && $oldVideoPath !== $replacementVideo['path']) {
            Storage::disk('public')->delete($oldVideoPath);
        }

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
            'video_upload_token' => [
                $course ? 'nullable' : 'required_without:video',
                'nullable',
                'string',
                'regex:/^[a-f0-9]{40}$/',
            ],
            'video' => [
                $course ? 'nullable' : 'required_without:video_upload_token',
                'nullable',
                'file',
                'mimes:mp4,mov,avi,mpeg,mpg,webm',
            ],
        ], [
            'video.uploaded' => $this->getVideoUploadErrorMessage(UPLOAD_ERR_INI_SIZE),
            'video.mimes' => 'Video formati desteklenmiyor. Lutfen MP4, MOV, AVI, MPEG veya WebM yukleyin.',
            'video.required_without' => 'Lutfen bir video secin.',
            'video_upload_token.required_without' => 'Video yuklemesi tamamlanmadan egitim kaydedilemez.',
        ], [
            'video' => 'video dosyasi',
        ]);

        $data['duration_seconds'] = (int) ($data['duration_seconds'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function storeIncomingVideo(Request $request): array
    {
        if ($request->filled('video_upload_token')) {
            return $this->storeChunkedVideo($request->string('video_upload_token')->toString());
        }

        $video = $request->file('video');
        if (! $video) {
            throw ValidationException::withMessages([
                'video' => 'Lutfen bir video secin.',
            ]);
        }

        $storedPath = $video->store('education/videos', 'public');
        if (! $storedPath) {
            abort(500, 'Video kalici depolama alanina yazilamadi.');
        }

        return [
            'path' => $storedPath,
            'original_name' => $video->getClientOriginalName(),
        ];
    }

    private function storeChunkedVideo(string $token): array
    {
        $metadata = $this->getVideoUploadMetadata($token);
        $this->authorizeVideoUploadOwner($metadata);

        $disk = Storage::disk('local');
        $partRelativePath = $this->videoUploadPartPath($token);
        if (! $disk->exists($partRelativePath)) {
            throw ValidationException::withMessages([
                'video' => 'Parcali video yuklemesi bulunamadi. Lutfen videoyu yeniden secin.',
            ]);
        }

        $actualSize = (int) $disk->size($partRelativePath);
        $expectedSize = (int) ($metadata['expected_size'] ?? 0);
        if ($actualSize < 1 || $actualSize !== $expectedSize) {
            throw ValidationException::withMessages([
                'video' => sprintf(
                    'Video yuklemesi tamamlanmadi. Yuklenen: %s / %s.',
                    $this->formatBytes($actualSize),
                    $this->formatBytes($expectedSize)
                ),
            ]);
        }

        $absolutePath = $disk->path($partRelativePath);
        $originalName = (string) ($metadata['original_name'] ?? 'video.mp4');
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $absolutePath,
            $originalName,
            (string) ($metadata['client_mime'] ?? '') ?: null,
            UPLOAD_ERR_OK,
            true
        );

        $validator = Validator::make(
            ['video' => $uploadedFile],
            ['video' => ['required', 'file', 'mimes:mp4,mov,avi,mpeg,mpg,webm']],
            ['video.mimes' => 'Video formati desteklenmiyor. Lutfen MP4, MOV, AVI, MPEG veya WebM yukleyin.']
        );

        if ($validator->fails()) {
            $this->deleteVideoUploadFiles($token);
            throw new ValidationException($validator);
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $storedPath = 'education/videos/' . Str::uuid() . '.' . $extension;
        $publicDisk = Storage::disk('public');
        $publicDisk->makeDirectory('education/videos');
        $targetPath = $publicDisk->path($storedPath);

        if (! @rename($absolutePath, $targetPath)) {
            $sourceStream = fopen($absolutePath, 'rb');
            if ($sourceStream === false) {
                abort(500, 'Video gecici depolama alanindan okunamadi.');
            }

            try {
                $stored = $publicDisk->writeStream($storedPath, $sourceStream);
            } finally {
                fclose($sourceStream);
            }

            if (! $stored) {
                abort(500, 'Video kalici depolama alanina yazilamadi.');
            }
        }

        $this->deleteVideoUploadFiles($token);

        return [
            'path' => $storedPath,
            'original_name' => $originalName,
        ];
    }

    private function authorizeVideoUpload(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && (
                $user->hasPermission('education_courses', 'create')
                || $user->hasPermission('education_courses', 'edit')
            ),
            403
        );
    }

    private function authorizeVideoUploadOwner(array $metadata): void
    {
        abort_unless((int) ($metadata['user_id'] ?? 0) === (int) Auth::id(), 403);
    }

    private function getVideoUploadMetadata(string $token): array
    {
        $metadata = $this->findVideoUploadMetadata($token);

        if (
            ! $metadata
            || ! isset($metadata['user_id'], $metadata['expected_size'], $metadata['original_name'])
            || (int) $metadata['expected_size'] < 1
        ) {
            throw ValidationException::withMessages([
                'video' => 'Video yukleme kaydi bulunamadi veya suresi doldu. Lutfen videoyu yeniden secin.',
            ]);
        }

        return $metadata;
    }

    private function findVideoUploadMetadata(string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }

        $disk = Storage::disk('local');
        $path = $this->videoUploadMetadataPath($token);
        if (! $disk->exists($path)) {
            return null;
        }

        $metadata = json_decode($disk->get($path), true);

        return is_array($metadata) ? $metadata : null;
    }

    private function videoUploadMetadataPath(string $token): string
    {
        return self::VIDEO_UPLOAD_DIRECTORY . '/' . $token . '.json';
    }

    private function videoUploadPartPath(string $token): string
    {
        return self::VIDEO_UPLOAD_DIRECTORY . '/' . $token . '.part';
    }

    private function deleteVideoUploadFiles(string $token): void
    {
        Storage::disk('local')->delete([
            $this->videoUploadMetadataPath($token),
            $this->videoUploadPartPath($token),
        ]);
    }

    private function purgeExpiredVideoUploads(): void
    {
        $disk = Storage::disk('local');
        $expiryTimestamp = now()->subSeconds(self::VIDEO_UPLOAD_TTL_SECONDS)->getTimestamp();

        foreach ($disk->files(self::VIDEO_UPLOAD_DIRECTORY) as $path) {
            if (! str_ends_with($path, '.json')) {
                continue;
            }

            $metadata = json_decode($disk->get($path), true);
            $updatedAt = is_array($metadata) ? strtotime((string) ($metadata['updated_at'] ?? '')) : false;

            if ($updatedAt !== false && $updatedAt >= $expiryTimestamp) {
                continue;
            }

            $token = pathinfo($path, PATHINFO_FILENAME);
            if (preg_match('/^[a-f0-9]{40}$/', $token)) {
                $this->deleteVideoUploadFiles($token);
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
        }

        return number_format($bytes / (1024 * 1024 * 1024), 2, ',', '.') . ' GB';
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
            if (method_exists($videoFile, 'isValid') && ! $videoFile->isValid()) {
                $errorCode = method_exists($videoFile, 'getError')
                    ? (int) $videoFile->getError()
                    : UPLOAD_ERR_INI_SIZE;

                Log::warning('Education course video upload arrived as invalid UploadedFile.', [
                    'error_code' => $errorCode,
                    'error_label' => $this->getUploadErrorLabel($errorCode),
                    'client_original_name' => method_exists($videoFile, 'getClientOriginalName') ? $videoFile->getClientOriginalName() : null,
                    'client_mime_type' => method_exists($videoFile, 'getClientMimeType') ? $videoFile->getClientMimeType() : null,
                    'content_length' => $request->server('CONTENT_LENGTH'),
                    'content_type' => $request->server('CONTENT_TYPE'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'memory_limit' => ini_get('memory_limit'),
                    'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
                    'tmp_dir_writable' => is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()),
                ]);

                throw ValidationException::withMessages([
                    'video' => $this->getVideoUploadErrorMessage($errorCode),
                ]);
            }

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
        $limits = $this->getServerUploadLimitText();

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Video yuklenemedi: dosya sunucu upload limitini asti. ' . $limits . ' Kod: ' . $errorCode . '.',
            UPLOAD_ERR_PARTIAL => 'Video yuklenemedi: dosya sunucuya eksik ulasti. Baglanti veya proxy kesintisi olabilir. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_NO_TMP_DIR => 'Video yuklenemedi: sunucuda gecici upload klasoru bulunamadi. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_CANT_WRITE => 'Video yuklenemedi: sunucu dosyayi diske yazamadi. Kod: ' . $errorCode . '.',
            UPLOAD_ERR_EXTENSION => 'Video yuklenemedi: bir PHP eklentisi yuklemeyi durdurdu. Kod: ' . $errorCode . '.',
            default => 'Video yuklenemedi. ' . $limits . ' Kod: ' . $errorCode . '.',
        };
    }

    private function getServerUploadLimitText(): string
    {
        return sprintf(
            'Canli PHP limitleri: upload_max_filesize=%s, post_max_size=%s.',
            ini_get('upload_max_filesize') ?: '?',
            ini_get('post_max_size') ?: '?'
        );
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
