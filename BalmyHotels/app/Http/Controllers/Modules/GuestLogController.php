<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\GuestLog;
use App\Models\User;
use Illuminate\Http\Request;

class GuestLogController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'guest_logs',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'checkOut'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $dateFrom = $request->input('date_from', today()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   today()->format('Y-m-d'));

        $query = GuestLog::with(['branch', 'department', 'host', 'createdBy'])
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('check_in_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('check_in_at', 'desc');

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('purpose'))       $query->where('purpose', $request->purpose);
        if ($request->filled('status')) {
            if ($request->status === 'inside')  $query->whereNull('check_out_at');
            if ($request->status === 'left')    $query->whereNotNull('check_out_at');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('visitor_name', 'like', "%$s%")
                ->orWhere('visitor_phone', 'like', "%$s%")
                ->orWhere('visitor_company', 'like', "%$s%")
                ->orWhere('visitor_id_no', 'like', "%$s%")
            );
        }

        $logs = $query->paginate(25)->withQueryString();

        // Özet istatistikler
        $base        = GuestLog::whereIn('branch_id', $branchIds)
                               ->whereBetween('check_in_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        $totalToday  = (clone $base)->count();
        $stillInside = (clone $base)->whereNull('check_out_at')->count();
        $leftCount   = (clone $base)->whereNotNull('check_out_at')->count();

        // İçeridekiler: şu an check_out_at = null olanlar (tüm tarihler)
        $insideNow = GuestLog::with(['host.department', 'department'])
            ->whereIn('branch_id', $branchIds)
            ->whereNull('check_out_at')
            ->orderBy('check_in_at')
            ->get();

        // Dışarıdakiler: şubenin dept_manager'larından şu an içeride olmayanlar
        $deptManagers  = User::with('department')
            ->where('role', 'dept_manager')
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->orderBy('name')->get();
        $insideHostIds = $insideNow->pluck('host_user_id')->filter()->unique();

        // Kapı loglarından her müdürün binada olup olmadığını kontrol et
        $mgrIds           = $deptManagers->pluck('id');
        $latestDoorLogIds = \App\Models\DoorLog::whereIn('user_id', $mgrIds)
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('user_id')
            ->pluck('max_id');
        $latestDoorLogs   = \App\Models\DoorLog::whereIn('id', $latestDoorLogIds)
            ->get()
            ->keyBy('user_id');

        // Müzüzäıt: ziyaretçisi yok + binada (giris logu var veya hiç log yok ama is_active)
        // Binada değil: son logu 'cikis'
        // Aktif ziyaretçisi var: insideHostIds içinde
        $freeManagers    = $deptManagers->filter(function ($mgr) use ($insideHostIds, $latestDoorLogs) {
            if ($insideHostIds->contains($mgr->id)) return false; // ziyaretçisi var
            $lastLog = $latestDoorLogs->get($mgr->id);
            return !$lastLog || $lastLog->type === 'giris'; // binada veya hiç log yok
        })->values();

        $absentManagers  = $deptManagers->filter(function ($mgr) use ($insideHostIds, $latestDoorLogs) {
            if ($insideHostIds->contains($mgr->id)) return false; // ziyaretçisi var (zaten içeridekiler'de)
            $lastLog = $latestDoorLogs->get($mgr->id);
            return $lastLog && $lastLog->type === 'cikis'; // binada değil
        })->values();

        // Eski değişken adını koruyarak view'e gönder
        $outsideManagers = $freeManagers;

        $branches    = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $page_title  = 'Ziyaretçi Kayıtları';

        return view('modules.guest_logs.index', compact(
            'logs', 'branches', 'departments',
            'dateFrom', 'dateTo',
            'totalToday', 'stillInside', 'leftCount',
            'insideNow', 'outsideManagers', 'absentManagers',
            'page_title'
        ));
    }

    public function create()
    {
        $user        = auth()->user();
        $branchIds   = $user->visibleBranchIds();
        $branches    = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        // Sadece şubenin Departman Müdürleri görüntülenir
        $hosts = User::where('role', 'dept_manager')
                     ->where('is_active', true)
                     ->whereIn('branch_id', $branchIds)
                     ->orderBy('name')->get();

        $autoBranchId = count($branchIds) === 1 ? $branchIds[0] : null;
        $page_title   = 'Ziyaretçi Kaydı Ekle';

        return view('modules.guest_logs.create', compact(
            'branches', 'departments', 'hosts', 'autoBranchId', 'page_title'
        ));
    }

    public function store(Request $request)
    {
        // Aynı müdürün yanında zaten içeride bir ziyaretçi varsa engelle
        if ($request->filled('host_user_id')) {
            $alreadyInside = GuestLog::where('host_user_id', $request->host_user_id)
                ->whereNull('check_out_at')
                ->exists();
            if ($alreadyInside) {
                return back()->withInput()->withErrors([
                    'host_user_id' => 'Bu müdürün yanında zaten içeride bir ziyaretçi var. Önce çıkış kaydedilmeli.'
                ]);
            }
        }

        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'department_id'  => 'nullable|exists:departments,id',
            'host_user_id'   => 'nullable|exists:users,id',
            'visitor_name'   => 'required|string|max:255',
            'visitor_phone'  => 'nullable|string|max:30',
            'visitor_id_no'  => 'nullable|string|max:50',
            'visitor_company'=> 'nullable|string|max:255',
            'purpose'        => 'required|in:meeting,delivery,interview,official,other',
            'purpose_note'   => 'nullable|string|max:500',
            'check_in_at'    => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        GuestLog::create([
            'branch_id'       => $request->branch_id,
            'department_id'   => $request->department_id,
            'host_user_id'    => $request->host_user_id,
            'created_by'      => auth()->id(),
            'visitor_name'    => $request->visitor_name,
            'visitor_phone'   => $request->visitor_phone,
            'visitor_id_no'   => $request->visitor_id_no,
            'visitor_company' => $request->visitor_company,
            'purpose'         => $request->purpose,
            'purpose_note'    => $request->purpose_note,
            'check_in_at'     => $request->check_in_at,
            'notes'           => $request->notes,
        ]);

        return redirect()->route('guest-logs.index')
            ->with('success', $request->visitor_name . ' için ziyaretçi kaydı oluşturuldu.');
    }

    public function show(GuestLog $guestLog)
    {
        $guestLog->load(['branch', 'department', 'host', 'createdBy']);
        $page_title = 'Ziyaretçi Detayı';
        return view('modules.guest_logs.show', compact('guestLog', 'page_title'));
    }

    public function edit(GuestLog $guestLog)
    {
        $user        = auth()->user();
        $branchIds   = $user->visibleBranchIds();
        $branches    = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $hosts       = User::where('role', 'dept_manager')
                           ->where('is_active', true)
                           ->whereIn('branch_id', $branchIds)
                           ->orderBy('name')->get();
        $page_title  = 'Ziyaretçi Kaydı Düzenle';

        return view('modules.guest_logs.edit', compact(
            'guestLog', 'branches', 'departments', 'hosts', 'page_title'
        ));
    }

    public function update(Request $request, GuestLog $guestLog)
    {
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'department_id'  => 'nullable|exists:departments,id',
            'host_user_id'   => 'nullable|exists:users,id',
            'visitor_name'   => 'required|string|max:255',
            'visitor_phone'  => 'nullable|string|max:30',
            'visitor_id_no'  => 'nullable|string|max:50',
            'visitor_company'=> 'nullable|string|max:255',
            'purpose'        => 'required|in:meeting,delivery,interview,official,other',
            'purpose_note'   => 'nullable|string|max:500',
            'check_in_at'    => 'required|date',
            'check_out_at'   => 'nullable|date|after_or_equal:check_in_at',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $guestLog->update([
            'branch_id'       => $request->branch_id,
            'department_id'   => $request->department_id,
            'host_user_id'    => $request->host_user_id,
            'visitor_name'    => $request->visitor_name,
            'visitor_phone'   => $request->visitor_phone,
            'visitor_id_no'   => $request->visitor_id_no,
            'visitor_company' => $request->visitor_company,
            'purpose'         => $request->purpose,
            'purpose_note'    => $request->purpose_note,
            'check_in_at'     => $request->check_in_at,
            'check_out_at'    => $request->check_out_at ?: null,
            'notes'           => $request->notes,
        ]);

        return redirect()->route('guest-logs.show', $guestLog)
            ->with('success', 'Kayıt güncellendi.');
    }

    /** Çıkış saatini kaydet (hızlı buton) */
    public function checkOut(GuestLog $guestLog)
    {
        $guestLog->update(['check_out_at' => now()]);
        return back()->with('success', $guestLog->visitor_name . ' çıkış yaptı.');
    }

    public function destroy(GuestLog $guestLog)
    {
        $guestLog->delete();
        return redirect()->route('guest-logs.index')->with('success', 'Kayıt silindi.');
    }
}
