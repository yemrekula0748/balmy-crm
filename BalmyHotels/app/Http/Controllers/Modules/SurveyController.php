<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'surveys',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'toggle'],
            ['destroy']
        );
    }


    public function index()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $surveys = Survey::with(['branch', 'creator'])
            ->withCount('responses')
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderByDesc('created_at')
            ->get();

        $page_title = 'Misafir Anketleri';
        return view('modules.surveys.index', compact('surveys', 'page_title'));
    }

    public function create()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yeni Anket Oluştur';
        return view('modules.surveys.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'languages'      => 'required|array|min:1',
            'title'          => 'required|array',
            'branch_id'      => 'nullable|exists:branches,id',
            'questions_data' => 'required|string',
        ]);

        $title = array_filter($request->title ?? []);
        if (empty($title)) {
            return back()->withInput()->withErrors(['title' => 'En az bir dilde başlık giriniz.']);
        }

        $baseSlug = Str::slug(reset($title) ?: 'anket');
        $slug     = $baseSlug . '-' . Str::random(6);
        while (Survey::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::random(6);
        }

        $survey = Survey::create([
            'branch_id'            => $request->branch_id,
            'created_by'           => auth()->id(),
            'title'                => $request->title,
            'description'          => $request->description ?? [],
            'slug'                 => $slug,
            'languages'            => $request->languages,
            'is_active'            => $request->boolean('is_active', true),
            'show_language_select' => $request->boolean('show_language_select', true),
        ]);

        $this->saveQuestions($survey, $request->questions_data);

        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Anket oluşturuldu. Paylaşım linki ve QR kodu hazır.');
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'questions.answers.response']);

        $completed = $survey->responses()->whereNotNull('submitted_at');
        $totalResponses = $completed->count();

        // Per-question statistics
        $stats = [];
        foreach ($survey->questions as $q) {
            $answers = SurveyAnswer::where('question_id', $q->id)
                ->whereHas('response', fn($r) => $r->whereNotNull('submitted_at'))
                ->pluck('answer');

            if ($q->type === 'radio') {
                $stats[$q->id] = ['type' => 'radio', 'data' => $answers->countBy()->sortByDesc(fn($v) => $v)->toArray()];
            } elseif ($q->type === 'checkbox') {
                $flat = $answers->flatMap(fn($a) => json_decode($a, true) ?? [])->countBy()->sortByDesc(fn($v) => $v)->toArray();
                $stats[$q->id] = ['type' => 'checkbox', 'data' => $flat];
            } elseif ($q->type === 'rating') {
                $vals = $answers->filter()->map(fn($a) => (int)$a);
                $dist = [];
                for ($i = 1; $i <= 5; $i++) $dist[$i] = $vals->filter(fn($v) => $v == $i)->count();
                $stats[$q->id] = ['type' => 'rating', 'avg' => round($vals->avg(), 1), 'dist' => $dist, 'count' => $vals->count()];
            } elseif ($q->type === 'nps') {
                $vals = $answers->filter()->map(fn($a) => (int)$a);
                $promoters  = $vals->filter(fn($v) => $v >= 9)->count();
                $detractors = $vals->filter(fn($v) => $v <= 6)->count();
                $nps = $vals->count() > 0 ? round((($promoters - $detractors) / $vals->count()) * 100) : null;
                $dist = [];
                for ($i = 0; $i <= 10; $i++) $dist[$i] = $vals->filter(fn($v) => $v == $i)->count();
                $stats[$q->id] = ['type' => 'nps', 'avg' => round($vals->avg(), 1), 'nps' => $nps, 'dist' => $dist, 'count' => $vals->count()];
            } else {
                $stats[$q->id] = ['type' => $q->type, 'data' => $answers->filter()->take(30)->values()->toArray()];
            }
        }

        // Language breakdown
        $langBreakdown = $survey->responses()
            ->whereNotNull('submitted_at')
            ->selectRaw('lang, count(*) as cnt')
            ->groupBy('lang')
            ->pluck('cnt', 'lang')
            ->toArray();

        // Daily responses (last 14 days)
        $dailyLabels = [];
        $dailyCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $dailyLabels[] = $d->format('d.m');
            $dailyCounts[] = $survey->responses()
                ->whereNotNull('submitted_at')
                ->whereDate('submitted_at', $d->format('Y-m-d'))
                ->count();
        }

        $page_title = 'Anket Sonuçları: ' . $survey->getTitle();
        return view('modules.surveys.show', compact(
            'survey', 'totalResponses', 'stats', 'langBreakdown', 'dailyLabels', 'dailyCounts', 'page_title'
        ));
    }

    public function edit(Survey $survey)
    {
        $survey->load('questions');
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Anketi Düzenle';
        return view('modules.surveys.edit', compact('survey', 'branches', 'page_title'));
    }

    public function update(Request $request, Survey $survey)
    {
        $request->validate([
            'languages'      => 'required|array|min:1',
            'title'          => 'required|array',
            'branch_id'      => 'nullable|exists:branches,id',
            'questions_data' => 'required|string',
        ]);

        $survey->update([
            'branch_id'            => $request->branch_id,
            'title'                => $request->title,
            'description'          => $request->description ?? [],
            'languages'            => $request->languages,
            'is_active'            => $request->boolean('is_active', true),
            'show_language_select' => $request->boolean('show_language_select', true),
        ]);

        $survey->questions()->delete();
        $this->saveQuestions($survey, $request->questions_data);

        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Anket güncellendi.');
    }

    public function toggle(Survey $survey)
    {
        $survey->update(['is_active' => !$survey->is_active]);
        return back()->with('success', $survey->is_active ? 'Anket aktif edildi.' : 'Anket devre dışı bırakıldı.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('surveys.index')->with('success', 'Anket silindi.');
    }

    // ---------------------------------------------------------------
    private function saveQuestions(Survey $survey, string $json): void
    {
        $questions = json_decode($json, true) ?? [];
        $created   = [];
        $tempIdMap = []; // temp_id → real DB id

        foreach ($questions as $i => $q) {
            $qModel = SurveyQuestion::create([
                'survey_id'                => $survey->id,
                'sort_order'               => $i + 1,
                'type'                     => $q['type'] ?? 'text',
                'is_required'              => (bool)($q['is_required'] ?? true),
                'translations'             => $q['translations'] ?? [],
                'conditional_question_id'  => null,
                'conditional_answer_value' => null,
            ]);

            $created[]  = ['model' => $qModel, 'raw' => $q];
            $tempIdMap[$q['temp_id'] ?? ('idx_' . $i)] = $qModel->id;
        }

        // Second pass: wire up conditional logic
        foreach ($created as $item) {
            $raw = $item['raw'];
            if (!empty($raw['conditional_temp_id']) && !empty($raw['conditional_answer_value'])) {
                $realId = $tempIdMap[$raw['conditional_temp_id']] ?? null;
                if ($realId) {
                    $item['model']->update([
                        'conditional_question_id'  => $realId,
                        'conditional_answer_value' => $raw['conditional_answer_value'],
                    ]);
                }
            }
        }
    }
}
