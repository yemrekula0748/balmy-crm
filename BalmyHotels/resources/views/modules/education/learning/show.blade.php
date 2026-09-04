@extends('layouts.default')
@section('title', $assignment->course->title ?? 'Egitim')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $assignment->course->title ?? 'Egitim' }}</h4>
                <span>{{ strtoupper($assignment->language) }} - {{ $assignment->status_label }}</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div>
                        <video
                            id="educationVideo"
                            controlsList="nodownload noplaybackrate noremoteplayback"
                            disablePictureInPicture
                            disableRemotePlayback
                            playsinline
                            webkit-playsinline="true"
                            tabindex="-1"
                            preload="metadata"
                            style="width:100%;max-height:520px;border-radius:8px;background:#111;cursor:pointer"
                        >
                            <source src="{{ route('education.learning.video', $assignment) }}">
                        </video>
                        <div class="education-player-controls d-flex align-items-center gap-2 mt-2">
                            <button type="button" class="btn btn-primary btn-sm" id="educationPlayPause">
                                <i class="fas fa-play me-1" id="educationPlayPauseIcon"></i>
                                <span id="educationPlayPauseLabel">Oynat</span>
                            </button>
                            <div class="progress flex-grow-1" style="height:8px;pointer-events:none" aria-hidden="true">
                                <div class="progress-bar" id="educationPlaybackBar" style="width:0%"></div>
                            </div>
                            <span class="small text-muted text-nowrap" id="educationPlaybackTime">00:00 / 00:00</span>
                        </div>
                    </div>
                    <div class="alert alert-light border mt-3 mb-0 small">
                        Ilerleme yalnizca bu pencere aktifken ve video oynarken kaydedilir.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div class="small text-muted">Ilerleme</div>
                    <div class="fs-3 fw-bold mb-2" id="progressPercent">%{{ number_format($assignment->progress_percent, 1) }}</div>
                    <div class="progress mb-3" style="height:10px">
                        <div class="progress-bar" id="progressBar" style="width:{{ $assignment->progress_percent }}%"></div>
                    </div>
                    <div class="small text-muted">Atayan</div>
                    <div class="fw-semibold mb-3">{{ $assignment->assigner->name ?? '-' }}</div>
                    <div class="small text-muted">Hafta</div>
                    <div class="fw-semibold mb-3">{{ $assignment->assigned_week_start->format('d.m.Y') }}</div>
                    @if($assignment->due_at)
                        <div class="small text-muted">Son Tarih</div>
                        <div class="fw-semibold mb-3">{{ $assignment->due_at->format('d.m.Y H:i') }}</div>
                    @endif
                    <p class="text-muted mb-0">{{ $assignment->course->description ?: 'Aciklama yok.' }}</p>
                    @if($assignment->course->has_quiz)
                        <hr>
                        <div class="small text-muted">Quiz Onayi</div>
                        @if($assignment->quiz_passed)
                            <div class="alert alert-success py-2 mt-2 mb-0">
                                Quiz basarili. Egitim onayin tamamlandi.
                            </div>
                        @elseif($assignment->video_completed)
                            <a href="{{ route('education.learning.quiz', $assignment) }}" class="btn btn-primary w-100 mt-2">
                                <i class="fas fa-question-circle me-1"></i>Quiz'e Gir
                            </a>
                        @else
                            <a
                                href="{{ route('education.learning.quiz', $assignment) }}"
                                class="btn btn-outline-secondary w-100 mt-2 disabled"
                                id="quizStartLink"
                                aria-disabled="true"
                                style="pointer-events:none"
                            >
                                Quiz video tamamlaninca acilir
                            </a>
                        @endif
                        @if($assignment->latestQuizAttempt)
                            <div class="small text-muted mt-2">
                                Son deneme: {{ $assignment->latestQuizAttempt->correct_answers }} / {{ $assignment->latestQuizAttempt->total_questions }}
                                - {{ $assignment->latestQuizAttempt->status_label }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('educationVideo');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const quizStartLink = document.getElementById('quizStartLink');
    const playPauseButton = document.getElementById('educationPlayPause');
    const playPauseIcon = document.getElementById('educationPlayPauseIcon');
    const playPauseLabel = document.getElementById('educationPlayPauseLabel');
    const playbackBar = document.getElementById('educationPlaybackBar');
    const playbackTime = document.getElementById('educationPlaybackTime');
    const progressUrl = @json(route('education.learning.progress', $assignment));
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let maxWatched = {{ (int) $assignment->max_watched_seconds }};
    let lastPingAt = 0;
    let isRestoringAudio = false;
    let lastAudibleVolume = video.volume > 0 ? video.volume : 1;

    video.disablePictureInPicture = true;
    video.disableRemotePlayback = true;
    video.defaultMuted = false;
    video.muted = false;

    function isWindowActive() {
        return document.visibilityState === 'visible' && document.hasFocus();
    }

    function pauseIfInactive() {
        if (!isWindowActive() && !video.paused) {
            video.pause();
        }
    }

    function limitForwardSeek() {
        const allowed = maxWatched + (video.seeking ? 0.05 : 1.5);
        if (video.currentTime > allowed) {
            video.currentTime = Math.max(0, maxWatched);
            return true;
        }

        return false;
    }

    function formatTime(value) {
        const seconds = Number.isFinite(value) ? Math.max(0, Math.floor(value)) : 0;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = String(seconds % 60).padStart(2, '0');

        return String(minutes).padStart(2, '0') + ':' + remainingSeconds;
    }

    function syncPlayerUi() {
        const isPlaying = !video.paused && !video.ended;
        const duration = Number.isFinite(video.duration) ? video.duration : 0;
        const percent = duration > 0 ? Math.min(100, video.currentTime / duration * 100) : 0;

        playPauseIcon.classList.toggle('fa-play', !isPlaying);
        playPauseIcon.classList.toggle('fa-pause', isPlaying);
        playPauseLabel.textContent = isPlaying ? 'Duraklat' : 'Oynat';
        playbackBar.style.width = percent + '%';
        playbackTime.textContent = formatTime(video.currentTime) + ' / ' + formatTime(duration);
    }

    function togglePlayback() {
        if (video.paused || video.ended) {
            keepAudioEnabled();
            video.play().catch(() => {});
            return;
        }

        video.pause();
    }

    function keepAudioEnabled() {
        if (isRestoringAudio) {
            return;
        }

        if (video.muted || video.volume <= 0) {
            isRestoringAudio = true;
            video.muted = false;
            video.volume = Math.max(lastAudibleVolume, 0.25);
            setTimeout(() => {
                isRestoringAudio = false;
            }, 0);
            return;
        }

        lastAudibleVolume = video.volume;
    }

    function keepNormalPlaybackRate() {
        if (video.playbackRate !== 1) {
            video.playbackRate = 1;
        }
    }

    function closePictureInPicture() {
        if (document.pictureInPictureElement === video && document.exitPictureInPicture) {
            document.exitPictureInPicture().catch(() => {});
        }

        if (
            typeof video.webkitSetPresentationMode === 'function'
            && video.webkitPresentationMode === 'picture-in-picture'
        ) {
            video.webkitSetPresentationMode('inline');
        }
    }

    function updateUi(data) {
        if (!data || typeof data.progress_percent === 'undefined') {
            return;
        }

        const percent = Number(data.progress_percent || 0);
        maxWatched = Math.max(maxWatched, Number(data.max_watched_seconds || 0));
        progressBar.style.width = percent + '%';
        progressPercent.textContent = '%' + percent.toFixed(1);

        if (quizStartLink && percent >= 95) {
            quizStartLink.classList.remove('btn-outline-secondary', 'disabled');
            quizStartLink.classList.add('btn-primary');
            quizStartLink.removeAttribute('aria-disabled');
            quizStartLink.style.pointerEvents = '';
            quizStartLink.innerHTML = "<i class=\"fas fa-question-circle me-1\"></i>Quiz'e Gir";
        }
    }

    function sendProgress(force = false) {
        const now = Date.now();
        if (!force && now - lastPingAt < 9000) {
            return;
        }

        if (!video.duration || video.duration < 1) {
            return;
        }

        lastPingAt = now;

        fetch(progressUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                current_time: video.currentTime,
                duration: video.duration,
                is_visible: isWindowActive(),
                is_playing: !video.paused && !video.ended,
                is_ended: video.ended || video.currentTime >= (video.duration - 1),
            }),
        })
            .then((response) => response.ok ? response.json() : null)
            .then(updateUi)
            .catch(() => {});
    }

    video.addEventListener('timeupdate', function () {
        const wasClamped = limitForwardSeek();

        if (
            !wasClamped
            && !video.seeking
            && video.currentTime <= (maxWatched + 1.5)
            && isWindowActive()
            && !video.paused
            && !video.ended
        ) {
            maxWatched = Math.max(maxWatched, video.currentTime);
        }

        syncPlayerUi();
        sendProgress(false);
    });
    video.addEventListener('loadedmetadata', syncPlayerUi);
    video.addEventListener('play', function () {
        pauseIfInactive();
        syncPlayerUi();
    });
    video.addEventListener('pause', function () {
        syncPlayerUi();
        sendProgress(true);
    });
    video.addEventListener('ended', function () {
        syncPlayerUi();
        sendProgress(true);
    });
    video.addEventListener('seeking', limitForwardSeek);
    video.addEventListener('volumechange', keepAudioEnabled);
    video.addEventListener('ratechange', keepNormalPlaybackRate);
    video.addEventListener('enterpictureinpicture', closePictureInPicture);
    video.addEventListener('webkitpresentationmodechanged', closePictureInPicture);
    video.addEventListener('click', togglePlayback);
    video.addEventListener('dblclick', function (event) {
        event.preventDefault();
        event.stopPropagation();
    });
    video.addEventListener('contextmenu', (event) => event.preventDefault());
    video.addEventListener('keydown', function (event) {
        if (
            ['ArrowRight', 'End', 'PageDown', 'l', 'L'].includes(event.key)
            || /^[1-9]$/.test(event.key)
        ) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
    playPauseButton.addEventListener('click', togglePlayback);
    document.addEventListener('visibilitychange', pauseIfInactive);
    window.addEventListener('blur', pauseIfInactive);
    window.addEventListener('pagehide', pauseIfInactive);
    window.addEventListener('beforeunload', () => sendProgress(true));
});
</script>
@endpush
