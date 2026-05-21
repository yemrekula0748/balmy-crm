<?php

namespace App\Http\Controllers\Modules;

use App\Models\EducationCourse;
use App\Models\EducationEvent;
use App\Models\EducationEventResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EducationEventController extends BaseModuleController
{
    public function __construct()
    {
        $this->middleware('perm:education_events,index')->only(['index']);
        $this->middleware('perm:education_events,show')->only(['show', 'respond']);
        $this->middleware('perm:education_events,create')->only(['create', 'store']);
        $this->middleware('perm:education_events,edit')->only(['edit', 'update']);
        $this->middleware('perm:education_events,delete')->only(['destroy']);
    }

    public function index()
    {
        $query = EducationEvent::with(['trainer', 'responses']);
        if (! $this->canManageEvents()) {
            $query->whereHas('responses', fn ($responseQuery) => $responseQuery->where('user_id', Auth::id()));
        }

        $events = $query->orderByDesc('starts_at')->paginate(15);

        return view('modules.education.events.index', compact('events'));
    }

    public function create()
    {
        return view('modules.education.events.form', [
            'event' => new EducationEvent(),
            'languages' => EducationCourse::LANGUAGES,
            'learners' => $this->learnerQuery()->with(['branch', 'department'])->orderBy('name')->get(),
            'selectedLearnerIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);
        $learnerIds = $this->validLearnerIds($data['user_ids']);
        if (! $this->allSelectedUsersAreLearners($data['user_ids'], $learnerIds)) {
            return back()->withInput()->withErrors([
                'user_ids' => 'Secilen kisilerden bazilarinin Ogrenen rolu bulunmuyor. Lutfen ogreneni kontrol edip tekrar kaydedin.',
            ]);
        }

        $data['trainer_id'] = Auth::id();

        $event = EducationEvent::create(collect($data)->except('user_ids')->all());
        $this->syncResponses($event, $learnerIds);

        return redirect()->route('education.events.show', $event)->with('success', 'Yuz yuze egitim duyurusu olusturuldu.');
    }

    public function show(EducationEvent $event)
    {
        abort_unless($this->canViewEvent($event), 403);
        $event->load(['trainer', 'responses.learner.branch', 'responses.learner.department']);
        $myResponse = $event->responses->firstWhere('user_id', Auth::id());

        return view('modules.education.events.show', compact('event', 'myResponse'));
    }

    public function edit(EducationEvent $event)
    {
        $event->load('responses');

        return view('modules.education.events.form', [
            'event' => $event,
            'languages' => EducationCourse::LANGUAGES,
            'learners' => $this->learnerQuery()->with(['branch', 'department'])->orderBy('name')->get(),
            'selectedLearnerIds' => $event->responses->pluck('user_id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function update(Request $request, EducationEvent $event)
    {
        $data = $this->validateEvent($request);
        $learnerIds = $this->validLearnerIds($data['user_ids']);
        if (! $this->allSelectedUsersAreLearners($data['user_ids'], $learnerIds)) {
            return back()->withInput()->withErrors([
                'user_ids' => 'Secilen kisilerden bazilarinin Ogrenen rolu bulunmuyor. Lutfen ogreneni kontrol edip tekrar kaydedin.',
            ]);
        }

        $event->update(collect($data)->except('user_ids')->all());
        $this->syncResponses($event, $learnerIds);

        return redirect()->route('education.events.show', $event)->with('success', 'Yuz yuze egitim duyurusu guncellendi.');
    }

    public function respond(Request $request, EducationEvent $event)
    {
        $response = $event->responses()->where('user_id', Auth::id())->first();
        abort_unless($response, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                EducationEventResponse::STATUS_ATTENDING,
                EducationEventResponse::STATUS_DECLINED,
            ])],
            'note' => 'nullable|string|max:1000',
        ]);

        if ($data['status'] === EducationEventResponse::STATUS_DECLINED && blank($data['note'] ?? null)) {
            return back()->withInput()->withErrors(['note' => 'Katilamayacak kisiler icin aciklama gereklidir.']);
        }

        $response->fill([
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'responded_at' => now(),
        ])->save();

        return back()->with('success', 'Katilim cevabin kaydedildi.');
    }

    public function destroy(EducationEvent $event)
    {
        $event->delete();

        return redirect()->route('education.events.index')->with('success', 'Yuz yuze egitim duyurusu silindi.');
    }

    private function validateEvent(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'language' => ['required', Rule::in(array_keys(EducationCourse::LANGUAGES))],
            'location' => 'nullable|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'response_deadline_at' => 'nullable|date|before_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
        ], [
            'title.required' => 'Egitim basligi zorunludur.',
            'title.max' => 'Egitim basligi en fazla 255 karakter olabilir.',
            'description.max' => 'Aciklama en fazla 5000 karakter olabilir.',
            'language.required' => 'Dil secimi zorunludur.',
            'language.in' => 'Gecerli bir dil secimi yapmalisiniz.',
            'location.max' => 'Konum en fazla 255 karakter olabilir.',
            'starts_at.required' => 'Baslangic tarihi zorunludur.',
            'starts_at.date' => 'Baslangic tarihi gecersiz.',
            'ends_at.date' => 'Bitis tarihi gecersiz.',
            'ends_at.after_or_equal' => 'Bitis tarihi baslangic tarihinden once olamaz.',
            'response_deadline_at.date' => 'Cevap son tarihi gecersiz.',
            'response_deadline_at.before_or_equal' => 'Cevap son tarihi baslangic tarihinden sonra olamaz.',
            'user_ids.required' => 'En az bir ogrenen secmelisiniz.',
            'user_ids.array' => 'Ogrenen secimi gecersiz.',
            'user_ids.min' => 'En az bir ogrenen secmelisiniz.',
            'user_ids.*.exists' => 'Secilen ogrenenlerden biri sistemde bulunamadi.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function syncResponses(EducationEvent $event, array $learnerIds): void
    {
        foreach ($learnerIds as $learnerId) {
            $event->responses()->firstOrCreate([
                'user_id' => $learnerId,
            ], [
                'status' => EducationEventResponse::STATUS_PENDING,
            ]);
        }

        $event->responses()
            ->whereNotIn('user_id', $learnerIds)
            ->where('status', EducationEventResponse::STATUS_PENDING)
            ->delete();
    }

    private function validLearnerIds(array $userIds): array
    {
        $userIds = $this->normalizeUserIds($userIds);

        return $this->learnerQuery()
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function allSelectedUsersAreLearners(array $userIds, array $learnerIds): bool
    {
        return count($this->normalizeUserIds($userIds)) === count(array_unique(array_map('intval', $learnerIds)));
    }

    private function normalizeUserIds(array $userIds): array
    {
        return array_values(array_unique(array_map('intval', $userIds)));
    }

    private function learnerQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->learners();
    }

    private function canManageEvents(): bool
    {
        return Auth::user()->isSuperAdmin()
            || Auth::user()->hasPermission('education_events', 'create')
            || Auth::user()->hasPermission('education_events', 'edit');
    }

    private function canViewEvent(EducationEvent $event): bool
    {
        return $this->canManageEvents()
            || $event->responses()->where('user_id', Auth::id())->exists();
    }
}
