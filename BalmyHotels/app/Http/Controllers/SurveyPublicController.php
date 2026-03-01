<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyPublicController extends Controller
{
    /** Dil seçim ekranı */
    public function splash(string $slug)
    {
        $survey = Survey::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $langs = $survey->languages;

        // Tek dil varsa veya dil seçimi kapalıysa direkt forma yönlendir
        if (count($langs) === 1 || !$survey->show_language_select) {
            return redirect()->route('surveys.public.form', [$slug, $langs[0]]);
        }

        return view('public.survey.splash', compact('survey'));
    }

    /** Anket formu */
    public function form(string $slug, string $lang)
    {
        $survey = Survey::where('slug', $slug)
            ->where('is_active', true)
            ->with(['questions' => fn($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        // Dil desteklenmiyor mu?
        if (!in_array($lang, $survey->languages)) {
            return redirect()->route('surveys.public.splash', $slug);
        }

        // Daha önce doldurulmuş mu? (cookie kontrolü)
        $token = request()->cookie('sv_' . $slug);
        if ($token) {
            $done = SurveyResponse::where('survey_id', $survey->id)
                ->where('respondent_token', $token)
                ->whereNotNull('submitted_at')
                ->exists();
            if ($done) {
                return view('public.survey.thankyou', compact('survey', 'lang'));
            }
        }

        return view('public.survey.form', compact('survey', 'lang'));
    }

    /** Anketi kaydet */
    public function submit(Request $request, string $slug, string $lang)
    {
        $survey = Survey::where('slug', $slug)
            ->where('is_active', true)
            ->with('questions')
            ->firstOrFail();

        if (!in_array($lang, $survey->languages)) {
            return redirect()->route('surveys.public.splash', $slug);
        }

        // Tekrar doldurma engeli
        $token = $request->cookie('sv_' . $slug);
        if ($token) {
            $done = SurveyResponse::where('survey_id', $survey->id)
                ->where('respondent_token', $token)
                ->whereNotNull('submitted_at')
                ->exists();
            if ($done) {
                return view('public.survey.thankyou', compact('survey', 'lang'));
            }
        }

        $newToken = $token ?: Str::random(40);

        $response = SurveyResponse::create([
            'survey_id'        => $survey->id,
            'lang'             => $lang,
            'respondent_token' => $newToken,
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'submitted_at'     => now(),
        ]);

        // Cevapları kaydet
        foreach ($survey->questions as $question) {
            $key = 'q_' . $question->id;
            if ($request->has($key)) {
                $value = is_array($request->input($key))
                    ? json_encode($request->input($key))
                    : (string)$request->input($key);

                SurveyAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                    'answer'      => $value,
                ]);
            }
        }

        return redirect()->route('surveys.public.thankyou', [$slug, $lang])
            ->cookie('sv_' . $slug, $newToken, 60 * 24 * 30);
    }

    /** Teşekkür sayfası */
    public function thankyou(string $slug, string $lang)
    {
        $survey = Survey::where('slug', $slug)->firstOrFail();
        return view('public.survey.thankyou', compact('survey', 'lang'));
    }
}
