<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Fault;
use App\Models\FaultArea;
use App\Models\FaultLocation;
use App\Models\FaultType;
use App\Models\FaultUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FaultController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'faults',
            ['index', 'incoming', 'myReports', 'myDepartment', 'ajaxDepartments', 'ajaxLocations', 'ajaxAreas', 'ajaxFaultTypes'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'updateStatus', 'addComment', 'assign'],
            ['destroy']
        );
    }


    /* ---------------------------------------------------------------
     | İSTATİSTİK + LİSTE
     --------------------------------------------------------------- */
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $query = Fault::with(['reporter', 'department', 'branch', 'faultType', 'faultLocation', 'faultArea'])
            ->whereIn('branch_id', $branchIds)
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'resolved' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('department_id')) $query->where('assigned_department_id', $request->department_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%$s%")
                                      ->orWhere('description', 'like', "%$s%"));
        }

        $faults = $query->paginate(20)->withQueryString();

        $base = Fault::whereIn('branch_id', $branchIds);
        if ($request->filled('branch_id')) (clone $base)->where('branch_id', $request->branch_id);

        $statsByStatus = (clone $base)->select('status', DB::raw('count(*) as total'))
                                      ->groupBy('status')->pluck('total', 'status');
        $statsByDept   = (clone $base)->select('assigned_department_id', DB::raw('count(*) as total'))
                                      ->groupBy('assigned_department_id')->get();

        $branches    = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::where('fault_assignable', true)->orderBy('name')->get();
        $avgResolution = Fault::whereIn('branch_id', $branchIds)
                              ->whereNotNull('resolved_at')
                              ->get()
                              ->avg(fn($f) => $f->created_at->diffInHours($f->resolved_at));

        $monthlyTrend = Fault::whereIn('branch_id', $branchIds)
                             ->where('created_at', '>=', now()->subMonths(6))
                             ->get()
                             ->groupBy(fn($f) => $f->created_at->format('Y-m'))
                             ->map(fn($g, $month) => (object)['month' => $month, 'total' => $g->count()])
                             ->sortKeys()
                             ->values();

        $page_title  = 'Teknik Arıza Takip';

        return view('modules.faults.index', compact(
            'faults', 'branches', 'departments',
            'statsByStatus', 'statsByDept', 'avgResolution', 'monthlyTrend', 'page_title'
        ));
    }

    /* ---------------------------------------------------------------
     | ARIZA BİLDİR — FORM & KAYIT
     --------------------------------------------------------------- */
    public function create()
    {
        $user       = auth()->user();
        $branchIds  = $user->visibleBranchIds();
        $branches   = Branch::whereIn('id', $branchIds)->orderBy('name')->get();

        $faultLocations = FaultLocation::whereIn('branch_id', $branchIds)
            ->where('is_active', true)->with('areas')->orderBy('name')->get();

        $faultTypes = FaultType::where('is_active', true)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('name')->get();

        $departments = Department::where('fault_assignable', true)
            ->whereIn('branch_id', $branchIds)->orderBy('name')->get();

        $autoBranchId = count($branchIds) === 1 ? $branchIds[0] : null;
        $page_title   = 'Arıza Bildir';

        return view('modules.faults.create', compact(
            'branches', 'faultLocations', 'faultTypes', 'departments',
            'autoBranchId', 'page_title'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'              => 'required|exists:branches,id',
            'assigned_department_id' => 'required|exists:departments,id',
            'fault_type_id'          => 'required|exists:fault_types,id',
            'fault_location_id'      => 'required|exists:fault_locations,id',
            'fault_area_id'          => 'nullable|exists:fault_areas,id',
            'description'            => 'required|string',
            'image'                  => 'nullable|image|max:4096',
        ]);

        $user = auth()->user();
        abort_if(!in_array($request->branch_id, $user->visibleBranchIds()), 403);

        $faultType = FaultType::with('departments')->findOrFail($request->fault_type_id);

        // Arıza türünün seçilen departman tarafından kullanılabilir olduğunu doğrula
        abort_if(
            !$faultType->allowedForDepartment((int) $request->assigned_department_id),
            403,
            'Bu arıza türü seçilen departman için kullanılamaz.'
        );
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('faults', 'public');
        }

        $fault = Fault::create([
            'branch_id'              => $request->branch_id,
            'reported_by'            => auth()->id(),
            'assigned_department_id' => $request->assigned_department_id,
            'fault_type_id'          => $request->fault_type_id,
            'fault_location_id'      => $request->fault_location_id ?: null,
            'fault_area_id'          => $request->fault_area_id ?: null,
            'title'                  => $faultType->name,
            'description'            => $request->description,
            'image_path'             => $imagePath,
            'status'                 => 'open',
        ]);

        FaultUpdate::create([
            'fault_id'    => $fault->id,
            'user_id'     => auth()->id(),
            'note'        => 'Arıza kaydı oluşturuldu.',
            'status_from' => null,
            'status_to'   => 'open',
        ]);

        return redirect()->route('faults.show', $fault)
            ->with('success', 'Arıza bildirimi başarıyla kaydedildi.');
    }

    /* ---------------------------------------------------------------
     | DETAY
     --------------------------------------------------------------- */
    public function show(Fault $fault)
    {
        $user = auth()->user();
        abort_if(!in_array($fault->branch_id, $user->visibleBranchIds()), 403);

        $fault->load(['reporter', 'department', 'branch', 'faultType', 'faultLocation', 'faultArea', 'updates.user']);
        $page_title = $fault->title;

        $canUpdate = $user->isSuperAdmin()
            || $user->isBranchManager()
            || ($user->department_id && $user->department_id === $fault->assigned_department_id)
            || $fault->reported_by === $user->id;

        $users = User::whereIn('branch_id', $user->visibleBranchIds())
                     ->orderBy('name')
                     ->get();

        return view('modules.faults.show', compact('fault', 'canUpdate', 'users', 'page_title'));
    }

    /* ---------------------------------------------------------------
     | GELEN ARIZALAR
     --------------------------------------------------------------- */
    public function incoming(Request $request)
    {
        $user   = auth()->user();
        $deptId = $user->department_id;
        abort_if(!$deptId, 403, 'Departmanınız tanımlı değil.');

        $query = Fault::with(['reporter', 'branch', 'faultType', 'faultLocation', 'faultArea'])
            ->where('assigned_department_id', $deptId)
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'resolved' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) $query->where('status', $request->status);

        $faults    = $query->paginate(20)->withQueryString();
        $canUpdate = $user->isSuperAdmin() || $user->isBranchManager() || $user->isDeptManager();
        $page_title = 'Gelen Arızalar';

        return view('modules.faults.incoming', compact('faults', 'canUpdate', 'page_title'));
    }

    /* ---------------------------------------------------------------
     | BİLDİRDİKLERİM
     --------------------------------------------------------------- */
    public function myReports(Request $request)
    {
        $user = auth()->user();

        $query = Fault::with(['department', 'branch', 'faultType', 'faultLocation', 'faultArea']);

        if ($user->department_id) {
            // Aynı departmandaki tüm kullanıcıların bildirdiği arızalar
            $deptUserIds = User::where('department_id', $user->department_id)->pluck('id');
            $query->whereIn('reported_by', $deptUserIds);
        } else {
            // Departmansız kullanıcı → sadece kendi bildirdikleri
            $query->where('reported_by', $user->id);
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        $query->orderBy('created_at', 'desc');

        $faults     = $query->paginate(20)->withQueryString();
        $page_title = 'Bildirdiklerim';

        return view('modules.faults.my_reports', compact('faults', 'page_title'));
    }

    /* ---------------------------------------------------------------
     | DEPARTMANIM — istatistik
     --------------------------------------------------------------- */
    public function myDepartment()
    {
        $user   = auth()->user();
        $deptId = $user->department_id;
        abort_if(!$deptId, 403, 'Departmanınız tanımlı değil.');

        $dept = Department::findOrFail($deptId);
        $base = Fault::where('assigned_department_id', $deptId);

        $totals = [
            'open'        => (clone $base)->where('status', 'open')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'resolved'    => (clone $base)->where('status', 'resolved')->count(),
            'closed'      => (clone $base)->where('status', 'closed')->count(),
        ];

        $byType = (clone $base)
            ->select('fault_type_id', DB::raw('count(*) as total'))
            ->groupBy('fault_type_id')->get()
            ->map(fn($r) => [
                'type_name' => FaultType::find($r->fault_type_id)?->name ?? '—',
                'count'     => $r->total,
            ]);

        $typePerformance = Fault::where('assigned_department_id', $deptId)
            ->whereNotNull('resolved_at')->with('faultType')->get()
            ->groupBy('fault_type_id')
            ->map(function ($group) {
                $ft = $group->first()->faultType;
                $target = $ft?->completion_hours ?? 24;
                $avgH   = round($group->avg(fn($f) => $f->created_at->diffInHours($f->resolved_at)), 1);
                $onTime = $group->filter(fn($f) => $f->created_at->diffInHours($f->resolved_at) <= $target)->count();
                return [
                    'type_name'   => $ft?->name ?? '—',
                    'target_hours'=> $target,
                    'avg_hours'   => $avgH,
                    'total'       => $group->count(),
                    'on_time'     => $onTime,
                    'on_time_pct' => $group->count() > 0 ? round($onTime / $group->count() * 100) : 0,
                ];
            })->values();

        $monthlyTrend = Fault::where('assigned_department_id', $deptId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn($f) => $f->created_at->format('Y-m'))
            ->map(fn($g, $month) => (object)['month' => $month, 'total' => $g->count()])
            ->sortKeys()
            ->values();

        $avgResolutionHours = Fault::where('assigned_department_id', $deptId)
            ->whereNotNull('resolved_at')->get()
            ->avg(fn($f) => $f->created_at->diffInHours($f->resolved_at));

        $page_title = 'Departmanım — ' . $dept->name;
        return view('modules.faults.my_department', compact(
            'dept', 'totals', 'byType', 'typePerformance',
            'monthlyTrend', 'avgResolutionHours', 'page_title'
        ));
    }

    /* ---------------------------------------------------------------
     | DURUM GÜNCELLE
     --------------------------------------------------------------- */
    public function updateStatus(Request $request, Fault $fault)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'note'   => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $canUpdate = $user->isSuperAdmin()
            || $user->isBranchManager()
            || ($user->department_id && $user->department_id === $fault->assigned_department_id)
            || $fault->reported_by === $user->id;
        abort_if(!$canUpdate, 403);

        $old = $fault->status;
        $fault->update([
            'status'      => $request->status,
            'resolved_at' => in_array($request->status, ['resolved', 'closed']) && !$fault->resolved_at ? now() : $fault->resolved_at,
            'closed_at'   => $request->status === 'closed' && !$fault->closed_at ? now() : $fault->closed_at,
        ]);

        FaultUpdate::create([
            'fault_id'    => $fault->id,
            'user_id'     => auth()->id(),
            'note'        => $request->note,
            'status_from' => $old,
            'status_to'   => $request->status,
        ]);

        return back()->with('success', 'Durum güncellendi.');
    }

    /* ---------------------------------------------------------------
     | YORUM EKLE
     --------------------------------------------------------------- */
    public function addComment(Request $request, Fault $fault)
    {
        $request->validate(['note' => 'required|string|max:1000']);

        FaultUpdate::create([
            'fault_id'    => $fault->id,
            'user_id'     => auth()->id(),
            'note'        => $request->note,
            'status_from' => $fault->status,
            'status_to'   => $fault->status,
        ]);

        return back()->with('success', 'Yorum eklendi.');
    }

    /* ---------------------------------------------------------------
     | SİL
     --------------------------------------------------------------- */
    public function destroy(Fault $fault)
    {
        abort_if(!auth()->user()->isSuperAdmin(), 403);
        if ($fault->image_path) Storage::disk('public')->delete($fault->image_path);
        $fault->delete();
        return redirect()->route('faults.index')->with('success', 'Arıza kaydı silindi.');
    }

    /* ---------------------------------------------------------------
     | AJAX — Şubeye göre departman / konum / alan
     --------------------------------------------------------------- */
    public function ajaxDepartments(Request $request)
    {
        $departments = Department::where('fault_assignable', true)
            ->where('branch_id', $request->branch_id)
            ->orderBy('name')->get(['id', 'name']);
        return response()->json($departments);
    }

    public function ajaxLocations(Request $request)
    {
        $locations = FaultLocation::where('branch_id', $request->branch_id)
            ->where('is_active', true)
            ->with(['areas' => fn($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')->get();
        return response()->json($locations);
    }

    public function ajaxAreas(Request $request)
    {
        $areas = FaultArea::where('fault_location_id', $request->location_id)
            ->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return response()->json($areas);
    }

    public function ajaxFaultTypes(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $deptId    = (int) $request->department_id;

        $types = FaultType::where('is_active', true)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->where(fn($q) => $q
                ->whereDoesntHave('departments')
                ->orWhereHas('departments', fn($dq) => $dq->where('departments.id', $deptId))
            )
            ->orderBy('name')
            ->get(['id', 'name', 'completion_hours']);

        return response()->json($types);
    }
}
