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
        // Varsayılan: bugün
        $dateFrom = $request->input('date_from', today()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', today()->format('Y-m-d'));

        $query = DoorLog::with(['user.branch', 'user.department'])
            ->whereBetween('logged_at', [
                $dateFrom . ' 00:00:00',
                $dateTo   . ' 23:59:59',
            ])
            ->orderBy('logged_at', 'desc');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(30)->withQueryString();

        // İstatistik (aynı filtreli)
        $statsQuery = DoorLog::whereBetween('logged_at', [
            $dateFrom . ' 00:00:00',
            $dateTo   . ' 23:59:59',
        ]);
        if ($request->filled('branch_id')) {
            $statsQuery->where('branch_id', $request->branch_id);
        }

        $totalGiris = (clone $statsQuery)->where('type', 'giris')->count();
        $totalCikis = (clone $statsQuery)->where('type', 'cikis')->count();
        $uniquePersonel = (clone $statsQuery)->distinct('user_id')->count('user_id');

        // Filtrelerde kullanılacak veriler
        $branches = Branch::orderBy('name')->get();
        $managers = User::whereIn('role', ['dept_manager', 'branch_manager', 'super_admin'])
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();

        $page_title = 'Kapı Giriş/Çıkış';

        return view('modules.door_logs.index', compact(
            'logs', 'branches', 'managers',
            'dateFrom', 'dateTo',
            'totalGiris', 'totalCikis', 'uniquePersonel',
            'page_title'
        ));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $managers = User::whereIn('role', ['dept_manager', 'branch_manager', 'super_admin'])
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();

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

        $user = User::findOrFail($request->user_id);

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
     * Hızlı kayıt: Anlık giriş veya çıkış (AJAX veya form POST)
     */
    public function quick(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type'    => 'required|in:giris,cikis',
        ]);

        $user = User::findOrFail($request->user_id);

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

    public function destroy(DoorLog $doorLog)
    {
        $doorLog->delete();

        return redirect()->route('door-logs.index')->with('success', 'Kayıt silindi.');
    }
}
