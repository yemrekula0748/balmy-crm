@extends('layouts.default')
@section('title', $course->title)

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $course->title }}</h4>
                <span>{{ $course->language_label }} egitim icerigi</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @php
        $canGenerateAttendanceForm = auth()->user()->isSuperAdmin()
            || auth()->user()->isBranchManager()
            || (int) $course->trainer_id === (int) auth()->id();
    @endphp

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <video controls preload="metadata" style="width:100%;max-height:430px;border-radius:8px;background:#111">
                        <source src="{{ route('education.courses.video', $course) }}">
                    </video>
                    <p class="mt-3 mb-0 text-muted">{{ $course->description ?: 'Aciklama girilmemis.' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div class="small text-muted">Egitmen</div>
                    <div class="fw-semibold mb-3">{{ $course->trainer->name ?? '-' }}</div>
                    <div class="small text-muted">Dil</div>
                    <div class="fw-semibold mb-3">{{ $course->language_label }}</div>
                    <div class="small text-muted">Atama Sayisi</div>
                    <div class="fw-semibold mb-3">{{ $course->assignments->count() }}</div>
                    @php($effectiveMinCorrect = $course->quiz_min_correct ?: $course->quizQuestions->count())
                    <div class="small text-muted">Quiz</div>
                    <div class="fw-semibold mb-3">
                        {{ $course->quizQuestions->count() }} soru
                        @if($course->quizQuestions->count() > 0)
                            <small class="text-muted d-block">Min. {{ $effectiveMinCorrect }} dogru</small>
                        @endif
                    </div>
                    <div class="small text-muted">Durum</div>
                    <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-light text-dark' }}">{{ $course->is_active ? 'Aktif' : 'Pasif' }}</span>
                    @if(auth()->user()->hasPermission('education_courses','edit'))
                        <a href="{{ route('education.courses.quiz.edit', $course) }}" class="btn btn-outline-primary w-100 mt-3">
                            <i class="fas fa-question-circle me-1"></i>Quiz Sorulari
                        </a>
                    @endif
                    @if($course->canBeDeletedBy(auth()->user()))
                        <form method="POST" action="{{ route('education.courses.destroy', $course) }}" class="mt-2" onsubmit="return confirm('Bu egitimi, atamalarini ve quiz kayitlarini kalici olarak silmek istiyor musunuz?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger w-100" type="submit">
                                <i class="fas fa-trash me-1"></i>Egitimi Sil
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3" style="border-radius:8px">
        <div class="card-header bg-white border-0"><h5 class="mb-0">Atama ve Quiz Durumu</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ogrenen</th>
                            <th class="text-center">Video</th>
                            <th class="text-center">Quiz</th>
                            <th class="text-center">Son Deneme</th>
                            <th class="text-center">Onay</th>
                            <th class="text-center">Katılım Formu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->assignments as $assignment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $assignment->learner->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $assignment->learner->branch->name ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height:7px">
                                        <div class="progress-bar" style="width:{{ $assignment->progress_percent }}%"></div>
                                    </div>
                                    <small class="text-muted">%{{ number_format($assignment->progress_percent, 1) }}</small>
                                </td>
                                <td class="text-center">
                                    @if(! $course->has_quiz)
                                        <span class="badge bg-light text-dark">Quiz yok</span>
                                    @elseif($assignment->quiz_passed)
                                        <span class="badge bg-success">Basarili</span>
                                    @elseif($assignment->video_completed)
                                        <span class="badge bg-warning text-dark">Bekliyor</span>
                                    @else
                                        <span class="badge bg-light text-dark">Video bekleniyor</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($assignment->latestQuizAttempt)
                                        {{ $assignment->latestQuizAttempt->correct_answers }} / {{ $assignment->latestQuizAttempt->total_questions }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $assignment->training_approved ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $assignment->training_approved ? 'Onayli' : 'Bekliyor' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($canGenerateAttendanceForm && $assignment->training_approved)
                                        <a href="{{ route('education.courses.attendance-form', [$course, $assignment]) }}"
                                           class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                            <i class="fas fa-file-signature me-1"></i>Formu Çıkar
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Bu egitim henuz kimseye atanmamis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
