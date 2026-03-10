<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DoorLog;
use App\Models\User;
use Illuminate\Http\Request;

class DoorLogController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'door_logs',
            ['index'],
            [],
            ['create', 'store', 'quick'],
            [],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        // Oturum açan kullanıcı ve görünür şubeler
        $authUser  = auth()->user();
        $branchIds = $authUser->visibleBranchIds();

        // Varsayılan şube: kullanıcının kendi şubesi (super admin için tüm şubeler)
        $selectedBranchId = $request->filled('branch_id')
            ? $request->branch_id
            : ($authUser->isSuperAdmin() ? null : $authUser->branch_id);

        // Varsayılan: bugün
        $dateFrom = $request->input('date_from', today()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', today()->format('Y-m-d'));

        $query = DoorLog::with(['user.branch', 'user.department', 'branch'])
            ->whereBetween('logged_at', [
                $dateFrom . ' 00:00:00',
                $dateTo   . ' 23:59:59',
            ])
            ->orderBy('logged_at', 'asc');

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Kullanıcı + gün bazında gruplama
        $allLogs = $query->get();

        $groupedLogs = $allLogs
            ->groupBy(fn ($log) => $log->user_id . '|' . \Carbon\Carbon::parse($log->logged_at)->format('Y-m-d'))
            ->map(function ($dayLogs) {
                $first = $dayLogs->first();
                return (object) [
                    'user'    => $first->user,
                    'branch'  => $first->branch,
                    'date'    => \Carbon\Carbon::parse($first->logged_at)->format('Y-m-d'),
                    'entries' => $dayLogs->where('type', 'giris')->sortBy('logged_at')->values(),
                    'exits'   => $dayLogs->where('type', 'cikis')->sortBy('logged_at')->values(),
                    'all'     => $dayLogs->sortBy('logged_at')->values(),
                ];
            })
            ->sortByDesc('date')
            ->values();

        // Manuel sayfalama
        $perPage     = 25;
        $currentPage = (int) $request->input('page', 1);
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedLogs->forPage($currentPage, $perPage),
            $groupedLogs->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // İstatistikler artık allLogs'tan
        $totalGiris     = $allLogs->where('type', 'giris')->count();
        $totalCikis     = $allLogs->where('type', 'cikis')->count();
        $uniquePersonel = $allLogs->unique('user_id')->count();

        // İçeride / Dışarıda: her kullanıcının son kaydına göre (yalnızca görünür şubeler)
        $latestLogIds = DoorLog::selectRaw('MAX(id) as max_id')
            ->when(!$authUser->isSuperAdmin(), fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->groupBy('user_id')
            ->pluck('max_id');

        $latestLogs   = DoorLog::whereIn('id', $latestLogIds)
            ->with(['user.branch', 'user.department'])
            ->get();

        $insideUsers  = $latestLogs->where('type', 'giris')
            ->sortBy(fn ($l) => optional($l->user)->name)
            ->values();

        $outsideUsers = $latestLogs->where('type', 'cikis')
            ->sortBy(fn ($l) => optional($l->user)->name)
            ->values();

        // Filtrelerde kullanılacak veriler
        $branches  = $authUser->isSuperAdmin()
            ? Branch::orderBy('name')->get()
            : Branch::whereIn('id', $branchIds)->orderBy('name')->get();

        // Hızlı kayıt dropdown: yalnızca aynı şube(ler)deki personel
        $managersQuery = User::with('department', 'branch')
            ->whereIn('role', ['dept_manager', 'branch_manager', 'super_admin'])
            ->where('is_active', true)
            ->orderBy('name');
        if (!$authUser->isSuperAdmin()) {
            $managersQuery->whereIn('branch_id', $branchIds);
        }
        $managers = $managersQuery->get();

        $page_title = 'Kapı Giriş/Çıkış';

        return view('modules.door_logs.index', compact(
            'logs', 'branches', 'managers',
            'dateFrom', 'dateTo',
            'totalGiris', 'totalCikis', 'uniquePersonel',
            'insideUsers', 'outsideUsers',
            'selectedBranchId',
            'page_title'
        ));
    }

    public function create()
    {
        $authUser  = auth()->user();
        $branchIds = $authUser->visibleBranchIds();
        $branches  = $authUser->isSuperAdmin()
            ? Branch::orderBy('name')->get()
            : Branch::whereIn('id', $branchIds)->orderBy('name')->get();

        $managersQuery = User::with('department', 'branch')
            ->whereIn('role', ['dept_manager', 'branch_manager', 'super_admin'])
            ->where('is_active', true)
            ->orderBy('name');
        if (!$authUser->isSuperAdmin()) {
            $managersQuery->whereIn('branch_id', $branchIds);
        }
        $managers = $managersQuery->get();

        $page_title = 'Manuel Giriş/Çıkış Kaydı';

        return view('modules.door_logs.create', compact('branches', 'managers', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'type'      => 'required|in:giris,cikis',
            'logged_at' => 'required|date',
            'notes'     => 'nullable|string|max:255',
        ]);

        $user    = User::findOrFail($request->user_id);

        if (!$this->canManageUser($user)) {
            return back()->withInput()->withErrors([
                'user_id' => "{$user->name} farklı bir şubeye ait — kayıt yetkisi yok.",
            ]);
        }

        $lastLog = $this->lastLog($user->id);

        if ($request->type === 'giris' && $lastLog?->type === 'giris') {
            return back()->withInput()->withErrors([
                'user_id' => "{$user->name} zaten içeride — çıkış yapmadan tekrar giriş kaydedilemez.",
            ]);
        }

        if ($request->type === 'cikis' && ($lastLog === null || $lastLog->type === 'cikis')) {
            return back()->withInput()->withErrors([
                'user_id' => "{$user->name} şu an içeride değil — önce giriş kaydı gereklidir.",
            ]);
        }

        DoorLog::create([
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id,
            'type'      => $request->type,
            'logged_at' => $request->logged_at,
            'notes'     => $request->notes,
        ]);

        return redirect()->route('door-logs.index')->with('success', 'Kayıt başarıyla eklendi.');
    }

    /**
     * Hızlı kayıt: Anlık giriş veya çıkış
     */
    public function quick(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type'    => 'required|in:giris,cikis',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$this->canManageUser($user)) {
            return redirect()->back()->with('error',
                "{$user->name} farklı bir şubeye ait — kayıt yetkisi yok."
            );
        }

        $lastLog = $this->lastLog($user->id);

        if ($request->type === 'giris' && $lastLog?->type === 'giris') {
            return redirect()->back()->with('error',
                "{$user->name} zaten içeride — çıkış yapmadan tekrar giriş kaydedilemez."
            );
        }

        if ($request->type === 'cikis' && ($lastLog === null || $lastLog->type === 'cikis')) {
            return redirect()->back()->with('error',
                "{$user->name} şu an içeride değil — önce giriş kaydı gereklidir."
            );
        }

        DoorLog::create([
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id,
            'type'      => $request->type,
            'logged_at' => now(),
            'notes'     => null,
        ]);

        $label = $request->type === 'giris' ? 'Giriş' : 'Çıkış';

        return redirect()->back()->with('success', "{$user->name} için {$label} kaydedildi.");
    }

    /**
     * Giriş yapan kullanıcının hedef personeli yönetme yetkisi var mı?
     * super_admin herkesi yönetebilir; diğerleri yalnızca kendi şubesini.
     */
    private function canManageUser(User $target): bool
    {
        $authUser = auth()->user();
        if ($authUser->isSuperAdmin()) return true;
        return in_array($target->branch_id, $authUser->visibleBranchIds(), true);
    }

    /**
     * Kullanıcının en son kapı kaydını döndürür.
     */
    private function lastLog(int $userId): ?DoorLog
    {
        return DoorLog::where('user_id', $userId)
            ->orderBy('logged_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function destroy(DoorLog $doorLog)
    {
        $doorLog->delete();

        return redirect()->route('door-logs.index')->with('success', 'Kayıt silindi.');
    }
}
