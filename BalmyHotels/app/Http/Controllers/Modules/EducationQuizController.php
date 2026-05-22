<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationAssignment;
use App\Models\EducationCourse;
use App\Models\EducationQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EducationQuizController extends BaseModuleController
{
    public function __construct()
    {
        $this->middleware('perm:education_courses,edit')->only(['edit', 'update']);
        $this->middleware('perm:education_learning,show')->only(['take']);
        $this->middleware('perm:education_learning,edit')->only(['submit']);
    }

    public function edit(EducationCourse $course)
    {
        $course->load(['quizQuestions.options']);

        return view('modules.education.courses.quiz', compact('course'));
    }

    public function update(Request $request, EducationCourse $course)
    {
        $preparedQuestions = $this->prepareQuestions($request);
        $minCorrect = (int) $request->input('quiz_min_correct', 0);

        if (count($preparedQuestions) === 0) {
            $minCorrect = 0;
        } elseif ($minCorrect < 0) {
            return back()->withInput()->withErrors(['quiz_min_correct' => 'Minimum dogru sayisi negatif olamaz.']);
        } elseif ($minCorrect < 1) {
            return back()->withInput()->withErrors(['quiz_min_correct' => 'Quiz varsa minimum dogru sayisi en az 1 olmalidir.']);
        } elseif ($minCorrect > count($preparedQuestions)) {
            return back()->withInput()->withErrors(['quiz_min_correct' => 'Minimum dogru sayisi soru sayisindan fazla olamaz.']);
        }

        DB::transaction(function () use ($course, $preparedQuestions, $minCorrect) {
            $course->update(['quiz_min_correct' => count($preparedQuestions) > 0 ? $minCorrect : 0]);
            $course->quizAttempts()->delete();
            $course->quizQuestions()->delete();

            foreach ($preparedQuestions as $questionIndex => $questionData) {
                $question = $course->quizQuestions()->create([
                    'question' => $questionData['question'],
                    'sort_order' => $questionIndex + 1,
                ]);

                foreach ($questionData['options'] as $optionIndex => $optionText) {
                    $question->options()->create([
                        'option_text' => $optionText,
                        'is_correct' => $optionIndex === $questionData['correct_option'],
                        'sort_order' => $optionIndex + 1,
                    ]);
                }
            }
        });

        return redirect()
            ->route('education.courses.show', $course)
            ->with('success', 'Quiz sorulari kaydedildi.');
    }

    public function take(EducationAssignment $assignment)
    {
        $this->ensureOwnAssignment($assignment);
        $assignment->load(['course.quizQuestions.options', 'latestQuizAttempt', 'passedQuizAttempt']);

        abort_unless($assignment->course && $assignment->course->has_quiz, 404);
        if (! $assignment->video_completed) {
            return redirect()
                ->route('education.learning.show', $assignment)
                ->withErrors(['quiz' => 'Quiz sadece video tamamlandiktan sonra acilir.']);
        }

        return view('modules.education.learning.quiz', compact('assignment'));
    }

    public function submit(Request $request, EducationAssignment $assignment)
    {
        $this->ensureOwnAssignment($assignment);
        $assignment->load(['course.quizQuestions.options', 'passedQuizAttempt']);

        abort_unless($assignment->course && $assignment->course->has_quiz, 404);
        if (! $assignment->video_completed) {
            return redirect()
                ->route('education.learning.show', $assignment)
                ->withErrors(['quiz' => 'Quiz sadece video tamamlandiktan sonra acilir.']);
        }

        if ($assignment->quiz_passed) {
            return redirect()
                ->route('education.learning.show', $assignment)
                ->with('success', 'Bu egitimin quiz onayi zaten tamamlanmis.');
        }

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|integer|exists:education_quiz_options,id',
        ], [
            'answers.required' => 'Tum sorular icin bir cevap secmelisin.',
            'answers.*.required' => 'Tum sorular icin bir cevap secmelisin.',
            'answers.*.exists' => 'Secilen cevaplardan biri gecersiz.',
        ]);

        $questions = $assignment->course->quizQuestions;
        $answers = collect($data['answers'] ?? [])->mapWithKeys(fn ($optionId, $questionId) => [
            (int) $questionId => (int) $optionId,
        ]);

        if ($answers->count() !== $questions->count()) {
            return back()->withInput()->withErrors(['answers' => 'Tum sorular icin bir cevap secmelisin.']);
        }

        $correctCount = 0;
        $answerRows = [];
        foreach ($questions as $question) {
            $selectedOptionId = (int) $answers->get((int) $question->id);
            $selectedOption = $question->options->firstWhere('id', $selectedOptionId);

            if (! $selectedOption) {
                return back()->withInput()->withErrors(['answers' => 'Secilen cevaplardan biri bu soruya ait degil.']);
            }

            $isCorrect = (bool) $selectedOption->is_correct;
            if ($isCorrect) {
                $correctCount++;
            }

            $answerRows[] = [
                'education_quiz_question_id' => $question->id,
                'education_quiz_option_id' => $selectedOption->id,
                'is_correct' => $isCorrect,
            ];
        }

        $totalQuestions = $questions->count();
        $minCorrect = (int) $assignment->course->quiz_min_correct;
        $minCorrect = $minCorrect > 0 ? min($minCorrect, $totalQuestions) : $totalQuestions;
        $passed = $correctCount >= $minCorrect;

        DB::transaction(function () use ($assignment, $totalQuestions, $correctCount, $minCorrect, $passed, $answerRows) {
            $attempt = EducationQuizAttempt::create([
                'education_assignment_id' => $assignment->id,
                'education_course_id' => $assignment->education_course_id,
                'user_id' => Auth::id(),
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'min_correct' => $minCorrect,
                'passed' => $passed,
                'submitted_at' => now(),
            ]);

            foreach ($answerRows as $row) {
                $attempt->answers()->create($row);
            }
        });

        return redirect()
            ->route('education.learning.show', $assignment)
            ->with($passed ? 'success' : 'error', $passed
                ? 'Quiz basarili. Egitim onayin tamamlandi.'
                : 'Quiz basarisiz. Tekrar deneyebilirsin.');
    }

    private function prepareQuestions(Request $request): array
    {
        $rows = $request->input('questions', []);
        if (! is_array($rows)) {
            return [];
        }

        $prepared = [];
        foreach ($rows as $index => $row) {
            $questionText = trim((string) ($row['question'] ?? ''));
            $options = array_values(array_map(
                fn ($option) => trim((string) $option),
                is_array($row['options'] ?? null) ? $row['options'] : []
            ));
            $hasAnyContent = $questionText !== '' || collect($options)->filter()->isNotEmpty();

            if (! $hasAnyContent) {
                continue;
            }

            if ($questionText === '') {
                throw ValidationException::withMessages([
                    'questions' => ($index + 1) . '. sorunun metni bos olamaz.',
                ]);
            }

            if (count($options) !== 4 || collect($options)->contains(fn ($option) => $option === '')) {
                throw ValidationException::withMessages([
                    'questions' => ($index + 1) . '. soru icin 4 secenegin tamamini doldurmalisin.',
                ]);
            }

            $correctOption = isset($row['correct_option']) ? (int) $row['correct_option'] : -1;
            if ($correctOption < 0 || $correctOption > 3) {
                throw ValidationException::withMessages([
                    'questions' => ($index + 1) . '. soru icin dogru secenegi secmelisin.',
                ]);
            }

            $prepared[] = [
                'question' => $questionText,
                'options' => $options,
                'correct_option' => $correctOption,
            ];
        }

        return $prepared;
    }

    private function ensureOwnAssignment(EducationAssignment $assignment): void
    {
        abort_unless(
            (int) $assignment->user_id === (int) Auth::id() || Auth::user()->isSuperAdmin(),
            403
        );
    }
}
