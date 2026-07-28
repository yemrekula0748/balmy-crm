@extends('layouts.default')
@section('title', $course->exists ? 'Egitim Duzenle' : 'Yeni Egitim')

@push('styles')
<style>
    .education-upload-status {
        min-height: 58px;
        padding: 12px;
        border: 1px solid #dfe4ea;
        border-radius: 6px;
        background: #f7f9fb;
    }

    .education-upload-status .progress {
        height: 8px;
        border-radius: 4px;
        background: #e3e8ed;
    }

    .education-upload-status .progress-bar {
        transition: width .2s ease;
    }

    .education-upload-message {
        min-height: 18px;
        color: #53606d;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $course->exists ? 'Egitim Duzenle' : 'Yeni Egitim' }}</h4>
                <span>Video, dil ve konu basligi</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-body">
            <form method="POST"
                  action="{{ $course->exists ? route('education.courses.update', $course) : route('education.courses.store') }}"
                  enctype="multipart/form-data"
                  id="educationCourseForm">
                @csrf
                @if($course->exists)
                    @method('PUT')
                @endif
                <input type="hidden" name="video_upload_token" id="educationVideoUploadToken"
                       value="{{ old('video_upload_token') }}">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Konu Basligi <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dil <span class="text-danger">*</span></label>
                        <select name="language" class="form-select" required>
                            @foreach($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('language', $course->language ?: 'tr') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Aciklama</label>
                        <textarea name="description" rows="4" class="form-control" maxlength="5000">{{ old('description', $course->description) }}</textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Video {{ $course->exists ? '' : '*' }}</label>
                        <input type="file" id="educationVideoInput" class="form-control"
                               accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg"
                               {{ $course->exists || old('video_upload_token') ? '' : 'required' }}>
                        <input type="hidden" name="duration_seconds" id="educationDurationSeconds" value="{{ old('duration_seconds', $course->duration_seconds ?? 0) }}">
                        <small class="text-muted">MP4/WebM/MOV/AVI/MPEG</small>

                        <div class="education-upload-status mt-2 {{ old('video_upload_token') ? '' : 'd-none' }}"
                             id="educationUploadStatus">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <div class="education-upload-message" id="educationUploadMessage">
                                    {{ old('video_upload_token') ? 'Video yuklendi ve kaydedilmeye hazir.' : '' }}
                                </div>
                                <strong class="small" id="educationUploadPercent">
                                    {{ old('video_upload_token') ? '100%' : '0%' }}
                                </strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Video yukleme ilerlemesi"
                                 aria-valuemin="0" aria-valuemax="100"
                                 aria-valuenow="{{ old('video_upload_token') ? '100' : '0' }}">
                                <div class="progress-bar bg-success" id="educationUploadProgress"
                                     style="width:{{ old('video_upload_token') ? '100' : '0' }}%"></div>
                            </div>
                        </div>

                        @error('video')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('video_upload_token')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $course->exists ? $course->is_active : true))>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                    @if($course->exists && $course->video_path)
                        <div class="col-12">
                            <video controls preload="metadata" style="width:100%;max-height:360px;border-radius:8px;background:#111">
                                <source src="{{ route('education.courses.video', $course) }}">
                            </video>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit" id="educationSaveButton">
                        <i class="fas fa-save me-1"></i>
                        <span id="educationSaveButtonText">Kaydet</span>
                    </button>
                    <a href="{{ route('education.courses.index') }}" class="btn btn-outline-secondary">Vazgec</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('educationCourseForm');
    var fileInput = document.getElementById('educationVideoInput');
    var durationInput = document.getElementById('educationDurationSeconds');
    var uploadTokenInput = document.getElementById('educationVideoUploadToken');
    var uploadStatus = document.getElementById('educationUploadStatus');
    var uploadMessage = document.getElementById('educationUploadMessage');
    var uploadPercent = document.getElementById('educationUploadPercent');
    var uploadProgress = document.getElementById('educationUploadProgress');
    var saveButton = document.getElementById('educationSaveButton');
    var saveButtonText = document.getElementById('educationSaveButtonText');
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var startUploadUrl = @json(route('education.courses.video-upload.start'));
    var chunkUploadUrl = @json(route('education.courses.video-upload.chunk'));
    var cancelUploadUrl = @json(route('education.courses.video-upload.cancel'));
    var courseExists = @json((bool) $course->exists);
    var uploadInProgress = false;
    var initialChunkSize = 4 * 1024 * 1024;
    var minimumChunkSize = 128 * 1024;

    if (!form || !fileInput || !durationInput || !uploadTokenInput) {
        return;
    }

    function setUploadProgress(percent, message, isError) {
        var safePercent = Math.max(0, Math.min(100, Math.round(percent)));

        uploadStatus.classList.remove('d-none');
        uploadMessage.textContent = message;
        uploadMessage.classList.toggle('text-danger', Boolean(isError));
        uploadMessage.classList.toggle('text-success', !isError && safePercent === 100);
        uploadPercent.textContent = safePercent + '%';
        uploadProgress.style.width = safePercent + '%';
        uploadProgress.classList.toggle('bg-danger', Boolean(isError));
        uploadProgress.classList.toggle('bg-success', !isError && safePercent === 100);
        uploadProgress.parentElement.setAttribute('aria-valuenow', String(safePercent));
    }

    function setSavingState(isSaving) {
        saveButton.disabled = isSaving;
        fileInput.disabled = isSaving;
        saveButtonText.textContent = isSaving ? 'Video Yukleniyor' : 'Kaydet';
    }

    async function responseErrorMessage(response) {
        try {
            var payload = await response.json();

            if (payload.errors) {
                var firstErrorGroup = Object.values(payload.errors)[0];
                if (Array.isArray(firstErrorGroup) && firstErrorGroup[0]) {
                    return firstErrorGroup[0];
                }
            }

            if (payload.message) {
                return payload.message;
            }
        } catch (error) {
            // HTML proxy error pages are handled by the status text below.
        }

        return 'Video yuklenemedi. Sunucu yaniti: HTTP ' + response.status + '.';
    }

    async function cancelUpload(uploadToken) {
        if (!uploadToken) {
            return;
        }

        try {
            await fetch(cancelUploadUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ upload_token: uploadToken })
            });
        } catch (error) {
            // Stale uploads are also removed automatically by the server.
        }
    }

    async function startUpload(file) {
        var response = await fetch(startUploadUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: file.name,
                size: file.size,
                mime: file.type || ''
            })
        });

        if (!response.ok) {
            throw new Error(await responseErrorMessage(response));
        }

        return response.json();
    }

    async function uploadFileInChunks(file) {
        var previousToken = uploadTokenInput.value;
        uploadTokenInput.value = '';
        await cancelUpload(previousToken);

        var upload = await startUpload(file);
        var uploadToken = upload.upload_token;
        var offset = Number(upload.uploaded_bytes || 0);
        var chunkSize = initialChunkSize;

        try {
            while (offset < file.size) {
                var end = Math.min(offset + chunkSize, file.size);
                var formData = new FormData();

                formData.append('upload_token', uploadToken);
                formData.append('offset', String(offset));
                formData.append('chunk', file.slice(offset, end), file.name + '.part');

                var response;

                try {
                    response = await fetch(chunkUploadUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                } catch (networkError) {
                    if (chunkSize <= minimumChunkSize) {
                        throw new Error('Sunucuya baglanilamadi. Internet baglantisini kontrol edip tekrar deneyin.');
                    }

                    chunkSize = Math.max(minimumChunkSize, Math.floor(chunkSize / 2));
                    setUploadProgress(
                        file.size ? (offset / file.size) * 100 : 0,
                        'Baglanti yenileniyor, daha kucuk parca ile devam ediliyor.',
                        false
                    );
                    continue;
                }

                if (response.status === 413) {
                    if (chunkSize <= minimumChunkSize) {
                        throw new Error('Sunucu en kucuk video parcasini da reddetti. Web sunucusu istek limiti kontrol edilmeli.');
                    }

                    chunkSize = Math.max(minimumChunkSize, Math.floor(chunkSize / 2));
                    setUploadProgress(
                        file.size ? (offset / file.size) * 100 : 0,
                        'Sunucu limiti algilandi, parca boyutu kucultulerek devam ediliyor.',
                        false
                    );
                    continue;
                }

                if (response.status === 409) {
                    var conflictPayload = await response.json();
                    offset = Number(conflictPayload.uploaded_bytes || 0);
                    continue;
                }

                if (!response.ok) {
                    throw new Error(await responseErrorMessage(response));
                }

                var payload = await response.json();
                offset = Number(payload.uploaded_bytes);

                if (!Number.isFinite(offset) || offset < 0 || offset > file.size) {
                    throw new Error('Sunucu gecersiz yukleme ilerlemesi dondurdu.');
                }

                setUploadProgress(
                    file.size ? (offset / file.size) * 100 : 100,
                    'Video yukleniyor...',
                    false
                );
            }
        } catch (error) {
            await cancelUpload(uploadToken);
            throw error;
        }

        uploadTokenInput.value = uploadToken;
        setUploadProgress(100, 'Video yuklendi ve kaydedilmeye hazir.', false);
    }

    fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        var previousToken = uploadTokenInput.value;

        if (!file) {
            return;
        }

        uploadTokenInput.value = '';
        cancelUpload(previousToken);
        setUploadProgress(0, file.name, false);

        var objectUrl = URL.createObjectURL(file);
        var video = document.createElement('video');

        video.preload = 'metadata';
        video.onloadedmetadata = function () {
            URL.revokeObjectURL(objectUrl);

            if (Number.isFinite(video.duration) && video.duration > 0) {
                durationInput.value = Math.min(Math.round(video.duration), 86400);
            }
        };
        video.onerror = function () {
            URL.revokeObjectURL(objectUrl);
        };
        video.src = objectUrl;
    });

    form.addEventListener('submit', async function (event) {
        if (uploadInProgress) {
            event.preventDefault();
            return;
        }

        var file = fileInput.files && fileInput.files[0];
        if (!file) {
            if (uploadTokenInput.value || courseExists) {
                return;
            }

            event.preventDefault();
            setUploadProgress(0, 'Lutfen bir video secin.', true);
            fileInput.focus();
            return;
        }

        event.preventDefault();
        uploadInProgress = true;
        setSavingState(true);
        setUploadProgress(0, 'Video yuklemesi baslatiliyor...', false);

        try {
            await uploadFileInChunks(file);
            fileInput.disabled = true;
            saveButtonText.textContent = 'Kaydediliyor';
            form.submit();
        } catch (error) {
            uploadInProgress = false;
            setSavingState(false);
            setUploadProgress(0, error.message || 'Video yuklenemedi.', true);
        }
    });
});
</script>
@endpush
