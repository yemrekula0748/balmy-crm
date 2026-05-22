@extends('layouts.default')
@section('title', 'Egitimlerim')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Egitimlerim</h4>
                <span>Atanan video egitimler</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if($weeklyNotificationCount > 0)
        <div class="alert alert-info border-0 shadow-sm">
            Bu hafta tamamlaman gereken {{ $weeklyNotificationCount }} egitim var.
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0">Atanan Egitimler</h5>
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tum Durumlar</option>
                    <option value="not_started" @selected($status === 'not_started')>Baslamadi</option>
                    <option value="in_progress" @selected($status === 'in_progress')>Devam ediyor</option>
                    <option value="completed" @selected($status === 'completed')>Tamamlandi</option>
                </select>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($assignments as $assignment)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded p-3 h-100" style="border-color:#e5ebf3!important">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <span class="badge bg-secondary">{{ strtoupper($assignment->language) }}</span>
                                <span class="small text-muted">{{ $assignment->assigned_week_start->format('d.m.Y') }}</span>
                            </div>
                            <h6 class="fw-semibold mb-2">{{ $assignment->course->title ?? '-' }}</h6>
                            <p class="small text-muted" style="min-height:38px">{{ \Illuminate\Support\Str::limit($assignment->course->description ?? '', 95) }}</p>
                            <div class="progress mb-2" style="height:8px">
                                <div class="progress-bar {{ $assignment->status === 'completed' ? 'bg-success' : '' }}" style="width:{{ $assignment->progress_percent }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">%{{ number_format($assignment->progress_percent, 1) }}</small>
                                <small class="fw-semibold">{{ $assignment->status_label }}</small>
                            </div>
                            @if($assignment->course && $assignment->course->has_quiz)
                                <div class="mb-3">
                                    @if($assignment->quiz_passed)
                                        <span class="badge bg-success">Quiz onaylandi</span>
                                    @elseif($assignment->video_completed)
                                        <span class="badge bg-warning text-dark">Quiz bekliyor</span>
                                    @else
                                        <span class="badge bg-light text-dark">Quiz video sonrasi</span>
                                    @endif
                                </div>
                            @endif
                            <a href="{{ route('education.learning.show', $assignment) }}" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-play me-1"></i>Izle
                            </a>
                            @if($assignment->course && $assignment->course->has_quiz && $assignment->video_completed && ! $assignment->quiz_passed)
                                <a href="{{ route('education.learning.quiz', $assignment) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                    <i class="fas fa-question-circle me-1"></i>Quiz'e Gir
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">Atanmis egitim bulunmuyor.</div>
                    </div>
                @endforelse
            </div>
        </div>
        @if($assignments->hasPages())
            <div class="card-footer bg-white">{{ $assignments->links() }}</div>
        @endif
    </div>
</div>
@endsection
