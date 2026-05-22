@extends('layouts.default')
@section('title', 'Quiz')

@section('content')
@php
    $course = $assignment->course;
    $latestAttempt = $assignment->latestQuizAttempt;
    $passedAttempt = $assignment->passedQuizAttempt;
    $effectiveMinCorrect = $course->quiz_min_correct ?: $course->quizQuestions->count();
@endphp

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Quiz</h4>
                <span>{{ $course->title ?? 'Egitim' }}</span>
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

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:10px">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-1">Ogrenme Kontrolu</h5>
                    <small class="text-muted">Egitimi onaylamak icin minimum {{ $effectiveMinCorrect }} dogru cevap gerekli.</small>
                </div>
                <div class="card-body">
                    @if($passedAttempt)
                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <div class="fw-semibold mb-1">Quiz basarili.</div>
                            <div>{{ $passedAttempt->correct_answers }} / {{ $passedAttempt->total_questions }} dogru cevap verdin. Egitim onayin tamamlandi.</div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('education.learning.quiz.submit', $assignment) }}">
                            @csrf
                            @foreach($course->quizQuestions as $questionIndex => $question)
                                <div class="border rounded p-3 mb-3" style="border-color:#e6edf5!important">
                                    <div class="fw-semibold mb-3">{{ $questionIndex + 1 }}. {{ $question->question }}</div>
                                    <div class="row g-2">
                                        @foreach($question->options as $option)
                                            <div class="col-md-6">
                                                <label class="d-flex gap-2 align-items-start border rounded p-2 h-100" style="cursor:pointer;border-color:#e8eef6!important">
                                                    <input
                                                        type="radio"
                                                        name="answers[{{ $question->id }}]"
                                                        value="{{ $option->id }}"
                                                        class="mt-1"
                                                        @checked((int) old('answers.'.$question->id) === (int) $option->id)
                                                        required
                                                    >
                                                    <span>{{ $option->option_text }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-check me-1"></i>Quiz'i Tamamla
                            </button>
                            <a href="{{ route('education.learning.show', $assignment) }}" class="btn btn-outline-secondary">Egitime Don</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:10px">
                <div class="card-body">
                    <div class="small text-muted">Video Ilerlemesi</div>
                    <div class="fs-3 fw-bold mb-2">%{{ number_format($assignment->progress_percent, 1) }}</div>
                    <div class="progress mb-3" style="height:10px">
                        <div class="progress-bar bg-success" style="width:{{ $assignment->progress_percent }}%"></div>
                    </div>
                    <div class="small text-muted">Soru Sayisi</div>
                    <div class="fw-semibold mb-3">{{ $course->quizQuestions->count() }}</div>
                    <div class="small text-muted">Minimum Dogru</div>
                    <div class="fw-semibold mb-3">{{ $effectiveMinCorrect }}</div>
                    <div class="small text-muted">Son Deneme</div>
                    @if($latestAttempt)
                        <div class="fw-semibold">{{ $latestAttempt->correct_answers }} / {{ $latestAttempt->total_questions }}</div>
                        <span class="badge {{ $latestAttempt->passed ? 'bg-success' : 'bg-danger' }}">{{ $latestAttempt->status_label }}</span>
                        <div class="small text-muted mt-2">{{ $latestAttempt->submitted_at?->format('d.m.Y H:i') }}</div>
                    @else
                        <div class="fw-semibold">Henuz yok</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
