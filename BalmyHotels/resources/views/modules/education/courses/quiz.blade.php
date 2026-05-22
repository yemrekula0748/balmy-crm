@extends('layouts.default')
@section('title', 'Quiz Sorulari')

@section('content')
@php
    $questionRows = old('questions');
    if ($questionRows === null) {
        $questionRows = $course->quizQuestions->map(function ($question) {
            $options = $question->options->values();
            $correctIndex = $options->search(fn ($option) => (bool) $option->is_correct);

            return [
                'question' => $question->question,
                'options' => $options->pluck('option_text')->all(),
                'correct_option' => $correctIndex === false ? 0 : $correctIndex,
            ];
        })->values()->all();
    }

    $questionRows = count($questionRows) > 0 ? $questionRows : [[
        'question' => '',
        'options' => ['', '', '', ''],
        'correct_option' => 0,
    ]];
@endphp

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Quiz Sorulari</h4>
                <span>{{ $course->title }}</span>
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

    <div class="card border-0 shadow-sm" style="border-radius:10px">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Egitim Sonu Quiz</h5>
                <small class="text-muted">Ogrenen video tamamlandiktan sonra bu sorulari cevaplar.</small>
            </div>
            <a href="{{ route('education.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">Egitime Don</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('education.courses.quiz.update', $course) }}" id="quizForm">
                @csrf
                @method('PUT')

                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Minimum Dogru Sayisi</label>
                        <input
                            type="number"
                            name="quiz_min_correct"
                            class="form-control"
                            min="0"
                            value="{{ old('quiz_min_correct', $course->quiz_min_correct ?: $course->quizQuestions->count()) }}"
                        >
                        <small class="text-muted">Quiz varsa en az 1 olmali ve soru sayisini gecmemeli.</small>
                    </div>
                    <div class="col-md-8 text-md-end">
                        <button type="button" class="btn btn-outline-primary" id="addQuestionBtn">
                            <i class="fas fa-plus me-1"></i>Soru Ekle
                        </button>
                    </div>
                </div>

                <div id="questionsWrap">
                    @foreach($questionRows as $index => $row)
                        @php
                            $options = array_values($row['options'] ?? ['', '', '', '']);
                            $options = array_pad(array_slice($options, 0, 4), 4, '');
                            $correctOption = (int) ($row['correct_option'] ?? 0);
                        @endphp
                        <div class="quiz-question border rounded p-3 mb-3" style="border-color:#e6edf5!important">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <h6 class="mb-0">Soru <span class="question-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Soru Metni</label>
                                <textarea name="questions[{{ $index }}][question]" rows="2" class="form-control" maxlength="1000">{{ $row['question'] ?? '' }}</textarea>
                            </div>
                            <div class="row g-2">
                                @for($optionIndex = 0; $optionIndex < 4; $optionIndex++)
                                    <div class="col-md-6">
                                        <label class="form-label">Secenek {{ $optionIndex + 1 }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <input
                                                    type="radio"
                                                    name="questions[{{ $index }}][correct_option]"
                                                    value="{{ $optionIndex }}"
                                                    @checked($correctOption === $optionIndex)
                                                    aria-label="Dogru secenek"
                                                >
                                            </span>
                                            <input
                                                type="text"
                                                name="questions[{{ $index }}][options][{{ $optionIndex }}]"
                                                class="form-control"
                                                value="{{ $options[$optionIndex] }}"
                                                maxlength="500"
                                                placeholder="Secenek metni"
                                            >
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <small class="text-muted d-block mt-2">Dogru secenegi soldaki radio ile isaretle.</small>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i>Quiz Kaydet</button>
                    <a href="{{ route('education.courses.show', $course) }}" class="btn btn-outline-secondary">Vazgec</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('questionsWrap');
    const addBtn = document.getElementById('addQuestionBtn');
    let nextIndex = wrap.querySelectorAll('.quiz-question').length;

    function questionTemplate(index) {
        const number = index + 1;
        let options = '';
        for (let i = 0; i < 4; i++) {
            options += `
                <div class="col-md-6">
                    <label class="form-label">Secenek ${i + 1}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <input type="radio" name="questions[${index}][correct_option]" value="${i}" ${i === 0 ? 'checked' : ''} aria-label="Dogru secenek">
                        </span>
                        <input type="text" name="questions[${index}][options][${i}]" class="form-control" maxlength="500" placeholder="Secenek metni">
                    </div>
                </div>`;
        }

        return `
            <div class="quiz-question border rounded p-3 mb-3" style="border-color:#e6edf5!important">
                <div class="d-flex justify-content-between gap-2 mb-2">
                    <h6 class="mb-0">Soru <span class="question-number">${number}</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Soru Metni</label>
                    <textarea name="questions[${index}][question]" rows="2" class="form-control" maxlength="1000"></textarea>
                </div>
                <div class="row g-2">${options}</div>
                <small class="text-muted d-block mt-2">Dogru secenegi soldaki radio ile isaretle.</small>
            </div>`;
    }

    function refreshNumbers() {
        wrap.querySelectorAll('.quiz-question').forEach((question, index) => {
            question.querySelector('.question-number').textContent = index + 1;
        });
    }

    addBtn.addEventListener('click', function () {
        wrap.insertAdjacentHTML('beforeend', questionTemplate(nextIndex));
        nextIndex++;
        refreshNumbers();
    });

    wrap.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-question');
        if (!button) {
            return;
        }

        button.closest('.quiz-question').remove();
        refreshNumbers();
    });
});
</script>
@endpush
