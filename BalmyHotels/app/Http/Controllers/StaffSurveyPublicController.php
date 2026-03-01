<?php

namespace App\Http\Controllers;

use App\Models\StaffSurvey;
use App\Models\StaffSurveyAnswer;
use App\Models\StaffSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffSurveyPublicController extends Controller
{
    /** Anket formu */
    public function form(string $slug)
    {
        $survey = StaffSurvey::where('slug', $slug)
            ->where('is_active', true)
            ->with(['questions' => fn($q) => $q->orderBy('sort_order')->with('conditionQuestion')])
            ->firstOrFail();

        // Çok dil varsa + dil seçimi açıksa dil seçimi
        $langs = $survey->languages;
        if (count($langs) > 1 && $survey->show_language_select) {
            $lang = request()->get('lang');
            if (!$lang || !in_array($lang, $langs)) {
                // Dil seçim ekranı göster
                return view('public.staff_survey.splash', compact('survey', 'langs'));
            }
        } else {
            $lang = $langs[0] ?? 'tr';
        }

        // allow_multiple=false ise cookie kontrolü
        if (!$survey->allow_multiple) {
            $token = request()->cookie('ssv_' . $slug);
            if ($token) {
                $done = StaffSurveyResponse::where('survey_id', $survey->id)
                    ->where('respondent_token', $token)
                    ->whereNotNull('submitted_at')
                    ->exists();
                if ($done) {
                    return view('public.staff_survey.thankyou', compact('survey', 'lang'));
                }
            }
        }

        return view('public.staff_survey.form', compact('survey', 'lang'));
    }

    /** Anketi kaydet */
    public function submit(Request $request, string $slug)
    {
        $survey = StaffSurvey::where('slug', $slug)
            ->where('is_active', true)
            ->with('questions')
            ->firstOrFail();

        $lang = $request->input('lang', $survey->languages[0] ?? 'tr');

        // Tekrar doldurma engeli
        if (!$survey->allow_multiple) {
            $token = $request->cookie('ssv_' . $slug);
            if ($token) {
                $done = StaffSurveyResponse::where('survey_id', $survey->id)
                    ->where('respondent_token', $token)
                    ->whereNotNull('submitted_at')
                    ->exists();
                if ($done) {
                    return view('public.staff_survey.thankyou', compact('survey', 'lang'));
                }
            }
        }

        $newToken = $request->cookie('ssv_' . $slug) ?: Str::random(40);

        $response = StaffSurveyResponse::create([
            'survey_id'             => $survey->id,
            'respondent_name'       => !$survey->is_anonymous ? $request->respondent_name : null,
            'respondent_dept'       => $survey->show_dept_field ? $request->respondent_dept : null,
            'respondent_employee_id'=> $survey->show_employee_id_field ? $request->respondent_employee_id : null,
            'lang'                  => $lang,
            'respondent_token'      => $newToken,
            'ip_address'            => $request->ip(),
            'submitted_at'          => now(),
        ]);

        // Cevapları kaydet (koşullu soruların atlanmasını JS zaten sağlıyor,
        // burada gelen her q_X anahtarını kaydediyoruz)
        foreach ($survey->questions as $question) {
            $key = 'q_' . $question->id;
            if ($request->has($key)) {
                $value = is_array($request->input($key))
                    ? json_encode($request->input($key))
                    : (string)$request->input($key);

                StaffSurveyAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                    'answer'      => $value,
                ]);
            }
        }

        $cookie = cookie('ssv_' . $slug, $newToken, 60 * 24 * 30); // 30 gün

        return redirect()->route('staff-surveys.public.thankyou', $slug)
            ->withCookie($survey->allow_multiple ? null : $cookie);
    }

    /** Teşekkür sayfası */
    public function thankyou(string $slug)
    {
        $survey = StaffSurvey::where('slug', $slug)->firstOrFail();
        $lang   = request()->get('lang', $survey->languages[0] ?? 'tr');
        return view('public.staff_survey.thankyou', compact('survey', 'lang'));
    }
}
