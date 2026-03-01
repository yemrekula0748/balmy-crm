<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StaffSurvey;
use App\Models\StaffSurveyAnswer;
use App\Models\StaffSurveyQuestion;
use App\Models\StaffSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffSurveyController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'staff_surveys',
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

        $surveys = StaffSurvey::with(['branch', 'creator'])
            ->withCount(['responses' => fn($q) => $q->whereNotNull('submitted_at')])
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderByDesc('created_at')
            ->get();

        $page_title = 'Personel Anketleri';
        return view('modules.staff_surveys.index', compact('surveys', 'page_title'));
    }

    public function create()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yeni Personel Anketi';
        return view('modules.staff_surveys.create', compact('branches', 'page_title'));
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

        $baseSlug = Str::slug(reset($title) ?: 'personel-anket');
        $slug     = $baseSlug . '-' . Str::random(6);
        while (StaffSurvey::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::random(6);
        }

        $survey = StaffSurvey::create([
            'branch_id'              => $request->branch_id,
            'created_by'             => auth()->id(),
            'title'                  => $request->title,
            'description'            => $request->description ?? [],
            'slug'                   => $slug,
            'languages'              => $request->languages,
            'is_anonymous'           => $request->boolean('is_anonymous', true),
            'show_dept_field'        => $request->boolean('show_dept_field', true),
            'show_employee_id_field' => $request->boolean('show_employee_id_field', false),
            'show_language_select'   => $request->boolean('show_language_select', false),
            'allow_multiple'         => $request->boolean('allow_multiple', false),
            'is_active'              => $request->boolean('is_active', true),
        ]);

        $this->saveQuestions($survey, $request->questions_data);

        return redirect()->route('staff-surveys.show', $survey)
            ->with('success', 'Personel anketi oluşturuldu. Paylaşım linki ve QR kodu hazır.');
    }

    public function show(StaffSurvey $staffSurvey)
    {
        $staffSurvey->load(['questions', 'questions.answers.response']);

        $totalResponses = $staffSurvey->responses()->whereNotNull('submitted_at')->count();

        // Soru istatistikleri
        $stats = [];
        foreach ($staffSurvey->questions as $q) {
            $answers = StaffSurveyAnswer::where('question_id', $q->id)
                ->whereHas('response', fn($r) => $r->whereNotNull('submitted_at'))
                ->pluck('answer');

            if (in_array($q->type, ['radio', 'yesno'])) {
                $stats[$q->id] = ['type' => $q->type, 'data' => $answers->countBy()->sortByDesc(fn($v) => $v)->toArray()];
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

        // Departman dağılımı
        $deptBreakdown = $staffSurvey->responses()
            ->whereNotNull('submitted_at')
            ->whereNotNull('respondent_dept')
            ->selectRaw('respondent_dept, count(*) as cnt')
            ->groupBy('respondent_dept')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'respondent_dept')
            ->toArray();

        // Son 14 günlük
        $dailyLabels = [];
        $dailyCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $dailyLabels[] = $d->format('d.m');
            $dailyCounts[] = $staffSurvey->responses()
                ->whereNotNull('submitted_at')
                ->whereDate('submitted_at', $d->format('Y-m-d'))
                ->count();
        }

        // Son yanıtlar
        $recentResponses = $staffSurvey->responses()
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->take(20)
            ->get();

        $page_title = 'Anket Sonuçları: ' . $staffSurvey->getTitle();
        return view('modules.staff_surveys.show', compact(
            'staffSurvey', 'totalResponses', 'stats',
            'deptBreakdown', 'dailyLabels', 'dailyCounts', 'recentResponses', 'page_title'
        ));
    }

    public function edit(StaffSurvey $staffSurvey)
    {
        $staffSurvey->load('questions');
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Personel Anketi Düzenle';
        return view('modules.staff_surveys.edit', compact('staffSurvey', 'branches', 'page_title'));
    }

    public function update(Request $request, StaffSurvey $staffSurvey)
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

        $staffSurvey->update([
            'branch_id'              => $request->branch_id,
            'title'                  => $request->title,
            'description'            => $request->description ?? [],
            'languages'              => $request->languages,
            'is_anonymous'           => $request->boolean('is_anonymous', true),
            'show_dept_field'        => $request->boolean('show_dept_field', true),
            'show_employee_id_field' => $request->boolean('show_employee_id_field', false),
            'show_language_select'   => $request->boolean('show_language_select', false),
            'allow_multiple'         => $request->boolean('allow_multiple', false),
            'is_active'              => $request->boolean('is_active', true),
        ]);

        // Eski soruları sil, yeniden kaydet
        $staffSurvey->questions()->delete();
        $this->saveQuestions($staffSurvey, $request->questions_data);

        return redirect()->route('staff-surveys.show', $staffSurvey)
            ->with('success', 'Personel anketi güncellendi.');
    }

    public function toggle(StaffSurvey $staffSurvey)
    {
        $staffSurvey->update(['is_active' => !$staffSurvey->is_active]);
        return back()->with('success', $staffSurvey->is_active ? 'Anket aktif edildi.' : 'Anket pasife alındı.');
    }

    public function destroy(StaffSurvey $staffSurvey)
    {
        $staffSurvey->delete();
        return redirect()->route('staff-surveys.index')->with('success', 'Anket silindi.');
    }

    // -------------------------------------------------------
    // Soru kaydetme yardımcısı
    // -------------------------------------------------------
    private function saveQuestions(StaffSurvey $survey, string $questionsJson): void
    {
        $questionsData = json_decode($questionsJson, true) ?? [];
        if (empty($questionsData)) return;

        // 1. Geçiş: Tüm soruları kaydet, idx→id haritası tut
        $idxToId = [];
        foreach ($questionsData as $idx => $qdata) {
            $q = StaffSurveyQuestion::create([
                'survey_id'  => $survey->id,
                'sort_order' => $idx,
                'type'       => $qdata['type'] ?? 'text',
                'title'      => $qdata['title'] ?? [],
                'options'    => !empty($qdata['options']) ? $qdata['options'] : null,
                'required'   => !empty($qdata['required']),
                // Koşul henüz null, 2. geçiş
                'condition_question_id' => null,
                'condition_answer'      => null,
            ]);
            $idxToId[$idx] = $q->id;
        }

        // 2. Geçiş: Koşulları güncelle
        foreach ($questionsData as $idx => $qdata) {
            $condIdx = $qdata['condition_question_idx'] ?? null;
            $condAns = $qdata['condition_answer'] ?? null;

            if (!is_null($condIdx) && $condAns !== '' && !is_null($condAns) && isset($idxToId[$condIdx])) {
                StaffSurveyQuestion::where('id', $idxToId[$idx])->update([
                    'condition_question_id' => $idxToId[$condIdx],
                    'condition_answer'      => $condAns,
                ]);
            }
        }
    }
}
