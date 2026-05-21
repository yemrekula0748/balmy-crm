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

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <video
                        id="educationVideo"
                        controls
                        controlsList="nodownload noplaybackrate"
                        disablePictureInPicture
                        preload="metadata"
                        style="width:100%;max-height:520px;border-radius:8px;background:#111"
                    >
                        <source src="{{ route('education.learning.video', $assignment) }}">
                    </video>
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
    const progressUrl = @json(route('education.learning.progress', $assignment));
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let maxWatched = {{ (int) $assignment->max_watched_seconds }};
    let lastPingAt = 0;
    let seekingBack = false;

    function isWindowActive() {
        return document.visibilityState === 'visible' && document.hasFocus();
    }

    function pauseIfInactive() {
        if (!isWindowActive() && !video.paused) {
            video.pause();
        }
    }

    function clampSeeking() {
        if (seekingBack) {
            return;
        }

        const allowed = maxWatched + 5;
        if (video.currentTime > allowed) {
            seekingBack = true;
            video.currentTime = allowed;
            seekingBack = false;
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
            }),
        })
            .then((response) => response.ok ? response.json() : null)
            .then(updateUi)
            .catch(() => {});
    }

    video.addEventListener('timeupdate', function () {
        clampSeeking();
        sendProgress(false);
    });
    video.addEventListener('play', pauseIfInactive);
    video.addEventListener('pause', () => sendProgress(true));
    video.addEventListener('ended', () => sendProgress(true));
    video.addEventListener('seeking', clampSeeking);
    document.addEventListener('visibilitychange', pauseIfInactive);
    window.addEventListener('blur', pauseIfInactive);
    window.addEventListener('beforeunload', () => sendProgress(true));
});
</script>
@endpush
