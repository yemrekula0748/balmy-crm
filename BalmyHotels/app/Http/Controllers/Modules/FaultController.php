<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Mail\FaultAnalysisReport;
use App\Mail\FaultReported;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Fault;
use App\Models\FaultArea;
use App\Models\FaultLocation;
use App\Models\FaultType;
use App\Models\FaultUpdate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FaultController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'faults',
            ['index', 'incoming', 'myReports', 'myDepartment', 'ajaxDepartments', 'ajaxLocations', 'ajaxAreas', 'ajaxFaultTypes'],
            [],
            ['create', 'store'],
            ['edit', 'update', 'updateStatus', 'addComment', 'assign'],
            ['destroy']
        );
        $this->middleware('fault.detail')->only(['show']);
        $this->middleware('perm:fault_stats,index')->only(['stats', 'analysis', 'sendAnalysisReport']);
        $this->middleware('perm:fault_room_reports,index')->only(['roomReport', 'roomReportExcel']);
        $this->middleware('perm:fault_type_reports,index')->only(['typeReport', 'typeReportExcel']);
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
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'winter_plan' THEN 2 WHEN 'waiting_material' THEN 3 WHEN 'resolved' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
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
                              ->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60);

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
            'priority'               => 'required|in:low,medium,high,critical',
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
            'priority'               => $request->priority ?? 'medium',
            'status'                 => 'open',
        ]);

        FaultUpdate::create([
            'fault_id'    => $fault->id,
            'user_id'     => auth()->id(),
            'note'        => 'Arıza kaydı oluşturuldu.',
            'status_from' => null,
            'status_to'   => 'open',
        ]);

        // Departmandaki kullanıcılara bildirim maili gönder
        $fault->load(['reporter', 'branch', 'department', 'faultType', 'faultLocation', 'faultArea']);
        $recipients = User::where('branch_id', $fault->branch_id)
            ->where('department_id', $fault->assigned_department_id)
            ->where('is_active', true)
            ->where('fault_notify', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new FaultReported($fault));
            } catch (\Throwable $e) {
                // Mail hatası arıza kaydını engellemesin
                \Illuminate\Support\Facades\Log::error('FaultReported mail gönderilemedi: ' . $e->getMessage(), [
                    'fault_id' => $fault->id,
                    'to'       => $recipient->email,
                ]);
            }
        }

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

        $allowedBacks = [
            route('faults.incoming'),
            route('faults.my-reports'),
            route('faults.my-department'),
            route('faults.index'),
        ];
        $prev    = url()->previous();
        $backUrl = in_array(rtrim($prev, '/'), array_map(fn($u) => rtrim($u, '/'), $allowedBacks))
            ? $prev
            : route('faults.index');

        return view('modules.faults.show', compact('fault', 'canUpdate', 'users', 'page_title', 'backUrl'));
    }

    /* ---------------------------------------------------------------
     | GELEN ARIZALAR
     --------------------------------------------------------------- */
    public function incoming(Request $request)
    {
        $user   = auth()->user();
        $deptId = $user->department_id;
        abort_if(!$deptId, 403, 'Departmanınız tanımlı değil.');

        $dept = Department::find($deptId);
        $base = Fault::where('assigned_department_id', $deptId);

        $stats = [
            'total'       => (clone $base)->count(),
            'open'        => (clone $base)->where('status', 'open')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'closed'      => (clone $base)->whereIn('status', ['resolved', 'closed'])->count(),
            'avg_hours'   => round((clone $base)->whereNotNull('resolved_at')->get()
                ->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60) ?? 0, 2),
        ];

        $query = Fault::with(['reporter', 'branch', 'faultType', 'faultLocation', 'faultArea'])
            ->where('assigned_department_id', $deptId)
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'winter_plan' THEN 2 WHEN 'waiting_material' THEN 3 WHEN 'resolved' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->orderBy('created_at', 'desc');

        // Arıza türleri: kullanıcının şubesi ve departmanına uygun
        $faultTypes = FaultType::where('is_active', true)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
            ->get()
            ->filter(fn($ft) => $ft->allowedForDepartment($deptId))
            ->sortBy('name')
            ->values();

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('fault_type'))    $query->where('fault_type_id', $request->fault_type);
        if ($request->filled('location_id'))   $query->where('fault_location_id', $request->location_id);
        if ($request->filled('area_id'))       $query->where('fault_area_id', $request->area_id);
        if ($request->filled('date_from'))     $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%$s%")->orWhere('description', 'like', "%$s%"));
        }

        $faults    = $query->paginate(20)->withQueryString();
        $canUpdate = $user->isSuperAdmin() || $user->isBranchManager() || $user->isDeptManager();
        $branchId  = $user->branch_id;
        $locations = FaultLocation::where('is_active', true)->where('branch_id', $branchId)->orderBy('name')->get();
        $areas     = $request->filled('location_id')
            ? FaultArea::where('fault_location_id', $request->location_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $page_title = 'Gelen Arızalar';

        return view('modules.faults.incoming', compact('faults', 'canUpdate', 'dept', 'stats', 'page_title', 'faultTypes', 'locations', 'areas'));
    }

    /* ---------------------------------------------------------------
     | GELEN ARIZA POLLING (AJAX)
     --------------------------------------------------------------- */
    public function ajaxNewIncoming(Request $request)
    {
        $user   = auth()->user();
        $deptId = $user->department_id;

        if (!$deptId) {
            return response()->json(['faults' => [], 'count' => 0]);
        }

        $lastId = (int) $request->input('last_id', 0);

        $faults = Fault::with(['reporter', 'faultType', 'faultLocation', 'faultArea'])
            ->where('assigned_department_id', $deptId)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($fault) => [
                'id'            => $fault->id,
                'title'         => $fault->title,
                'description'   => $fault->description,
                'status'        => $fault->status,
                'status_label'  => Fault::STATUSES[$fault->status]      ?? $fault->status,
                'status_color'  => Fault::STATUS_COLORS[$fault->status] ?? 'secondary',
                'priority'      => $fault->priority,
                'type_name'     => $fault->faultType?->name     ?? '',
                'location_name' => $fault->faultLocation?->name ?? '',
                'area_name'     => $fault->faultArea?->name     ?? '',
                'reporter_name' => $fault->reporter?->name      ?? '',
                'image_url'     => $fault->image_path ? asset('uploads/'.$fault->image_path) : null,
                'created_at'    => $fault->created_at->format('d.m.Y H:i'),
                'show_url'      => route('faults.show', $fault),
            ]);

        return response()->json(['faults' => $faults, 'count' => $faults->count()]);
    }

    /* ---------------------------------------------------------------
     | BİLDİRDİKLERİM
     --------------------------------------------------------------- */
    public function myReports(Request $request)
    {
        $user = auth()->user();

        $query = Fault::with(['department', 'branch', 'faultType', 'faultLocation', 'faultArea', 'reporter']);

        if ($user->department_id) {
            // Aynı departmandaki tüm kullanıcıların bildirdiği arızalar
            $deptUserIds = User::where('department_id', $user->department_id)->pluck('id');
            $query->whereIn('reported_by', $deptUserIds);
        } else {
            // Departmansız kullanıcı → sadece kendi bildirdikleri
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('department_id')) $query->where('assigned_department_id', $request->department_id);
        if ($request->filled('location_id'))   $query->where('fault_location_id', $request->location_id);
        if ($request->filled('area_id'))       $query->where('fault_area_id', $request->area_id);
        if ($request->filled('date_from'))     $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('created_at', '<=', $request->date_to);

        $query->orderBy('created_at', 'desc');

        $faults      = $query->paginate(20)->withQueryString();
        $branchId    = $user->branch_id;
        $departments = Department::where('fault_assignable', true)
            ->where('branch_id', $branchId)
            ->orderBy('name')->get();
        $locations   = FaultLocation::where('is_active', true)
            ->where('branch_id', $branchId)
            ->orderBy('name')->get();
        $areas       = $request->filled('location_id')
            ? FaultArea::where('fault_location_id', $request->location_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $page_title  = 'Bildirdiklerim';

        return view('modules.faults.my_reports', compact('faults', 'page_title', 'departments', 'locations', 'areas'));
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
                $avgH   = round($group->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60), 2);
                $onTime = $group->filter(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60 <= $target)->count();
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
            ->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60);

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
            'status' => ['required', Rule::in(array_keys(Fault::STATUSES))],
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

    /* ---------------------------------------------------------------
     | İSTATİSTİKLER — Scoreboard
     --------------------------------------------------------------- */
    public function stats(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $period    = $request->input('period', '90');

        $query = Fault::with(['faultType', 'faultLocation', 'faultArea', 'department', 'branch'])
            ->whereIn('branch_id', $branchIds);
        if ($period !== 'all') {
            $query->where('created_at', '>=', now()->subDays((int) $period));
        }
        $allFaults = $query->get();

        // Özet
        $resolvedFaults = $allFaults->filter(fn($f) => $f->resolved_at);
        $slaOnTime = $resolvedFaults->filter(function ($f) {
            return $f->created_at->diffInMinutes($f->resolved_at) / 60 <= ($f->faultType?->completion_hours ?? 24);
        })->count();

        $summary = [
            'total'       => $allFaults->count(),
            'open'        => $allFaults->where('status', 'open')->count(),
            'in_progress' => $allFaults->where('status', 'in_progress')->count(),
            'closed'      => $allFaults->whereIn('status', ['resolved', 'closed'])->count(),
            'avg_hours'   => $resolvedFaults->count() > 0
                ? round($resolvedFaults->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60), 2)
                : null,
            'sla_pct'     => $resolvedFaults->count() > 0
                ? round($slaOnTime / $resolvedFaults->count() * 100)
                : null,
        ];

        // Departman Scoreboard
        $deptScoreboard = $allFaults->groupBy('assigned_department_id')
            ->map(function ($faults) {
                $dept     = $faults->first()->department;
                $resolved = $faults->filter(fn($f) => $f->resolved_at);
                $avgH     = $resolved->count() > 0
                    ? round($resolved->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60), 2)
                    : null;
                $onTime = $resolved->filter(function ($f) {
                    return $f->created_at->diffInMinutes($f->resolved_at) / 60 <= ($f->faultType?->completion_hours ?? 24);
                })->count();
                return [
                    'dept'        => $dept,
                    'total'       => $faults->count(),
                    'open'        => $faults->where('status', 'open')->count(),
                    'in_progress' => $faults->where('status', 'in_progress')->count(),
                    'closed'      => $faults->whereIn('status', ['resolved', 'closed'])->count(),
                    'avg_hours'   => $avgH,
                    'sla_pct'     => $resolved->count() > 0 ? round($onTime / $resolved->count() * 100) : null,
                    'sla_count'   => $resolved->count(),
                ];
            })->sortByDesc('total')->values();

        // Arıza Türü Performansı
        $typeStats = $allFaults->groupBy('fault_type_id')
            ->map(function ($faults) {
                $ft       = $faults->first()->faultType;
                $target   = $ft?->completion_hours ?? 24;
                $resolved = $faults->filter(fn($f) => $f->resolved_at);
                $avgH     = $resolved->count() > 0
                    ? round($resolved->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60), 2)
                    : null;
                $onTime = $resolved->filter(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60 <= $target)->count();
                return [
                    'type_name'    => $ft?->name ?? '—',
                    'target_hours' => $target,
                    'total'        => $faults->count(),
                    'open'         => $faults->where('status', 'open')->count(),
                    'avg_hours'    => $avgH,
                    'sla_pct'      => $resolved->count() > 0 ? round($onTime / $resolved->count() * 100) : null,
                    'sla_count'    => $resolved->count(),
                ];
            })->sortByDesc('total')->values();

        // Konum Bazında
        $locationStats = $allFaults->groupBy('fault_location_id')
            ->map(function ($faults) {
                $loc = $faults->first()->faultLocation;
                return [
                    'name'  => $loc?->name ?? 'Belirtilmemiş',
                    'total' => $faults->count(),
                    'open'  => $faults->where('status', 'open')->count(),
                ];
            })->sortByDesc('total')->take(10)->values();

        // Alan Top 10
        $areaStats = $allFaults->filter(fn($f) => $f->fault_area_id)
            ->groupBy('fault_area_id')
            ->map(function ($faults) {
                return [
                    'area_name' => $faults->first()->faultArea?->name ?? '—',
                    'loc_name'  => $faults->first()->faultLocation?->name ?? '—',
                    'total'     => $faults->count(),
                    'open'      => $faults->where('status', 'open')->count(),
                ];
            })->sortByDesc('total')->take(10)->values();

        // Aylık Trend
        $monthlyTrend = $allFaults
            ->groupBy(fn($f) => $f->created_at->format('Y-m'))
            ->map(fn($g, $m) => [
                'month'  => $m,
                'total'  => $g->count(),
                'closed' => $g->whereIn('status', ['resolved', 'closed'])->count(),
            ])
            ->sortKeys()->values();

        // Şube Karşılaştırma (sadece super_admin)
        $branchStats = $user->isSuperAdmin()
            ? $allFaults->groupBy('branch_id')->map(function ($faults) {
                return [
                    'name'  => $faults->first()->branch?->name ?? '—',
                    'total' => $faults->count(),
                    'open'  => $faults->where('status', 'open')->count(),
                    'closed'=> $faults->whereIn('status', ['resolved', 'closed'])->count(),
                ];
            })->sortByDesc('total')->values()
            : null;

        // Detaylı Konum × Tür tablosu
        $locationTypeStats = $allFaults->groupBy('fault_location_id')
            ->map(function ($locFaults) {
                $locName = $locFaults->first()->faultLocation?->name ?? 'Belirtilmemiş';
                $byType  = $locFaults->groupBy('fault_type_id')
                    ->map(function ($tFaults) {
                        $typeName = $tFaults->first()->faultType?->name ?? '—';
                        return [
                            'type_name' => $typeName,
                            'total'     => $tFaults->count(),
                            'open'      => $tFaults->where('status', 'open')->count(),
                            'closed'    => $tFaults->whereIn('status', ['resolved', 'closed'])->count(),
                        ];
                    })->sortByDesc('total')->values();
                $topType = $byType->first()['type_name'] ?? '—';
                return [
                    'location'   => $locName,
                    'total'      => $locFaults->count(),
                    'open'       => $locFaults->where('status', 'open')->count(),
                    'closed'     => $locFaults->whereIn('status', ['resolved', 'closed'])->count(),
                    'top_type'   => $topType,
                    'by_type'    => $byType,
                ];
            })->sortByDesc('total')->values();

        // Detaylı Alan × Tür tablosu
        $areaTypeStats = $allFaults->filter(fn($f) => $f->fault_area_id)
            ->groupBy('fault_area_id')
            ->map(function ($aFaults) {
                $areaName = $aFaults->first()->faultArea?->name ?? '—';
                $locName  = $aFaults->first()->faultLocation?->name ?? '—';
                $topType  = $aFaults->groupBy('fault_type_id')
                    ->map(fn($g) => ['name' => $g->first()->faultType?->name ?? '—', 'count' => $g->count()])
                    ->sortByDesc('count')->first()['name'] ?? '—';
                return [
                    'area'     => $areaName,
                    'location' => $locName,
                    'total'    => $aFaults->count(),
                    'open'     => $aFaults->where('status', 'open')->count(),
                    'closed'   => $aFaults->whereIn('status', ['resolved', 'closed'])->count(),
                    'top_type' => $topType,
                ];
            })->sortByDesc('total')->values();

        // Departman aylık çözüm süreleri (son 12 ay)
        $deptMonthlyResolution = Fault::with(['department', 'faultType'])
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('resolved_at')
            ->where('created_at', '>=', now()->subMonths(12))
            ->get()
            ->groupBy('assigned_department_id')
            ->map(function ($faults) {
                $dept = $faults->first()->department;
                $monthly = $faults->groupBy(fn($f) => $f->created_at->format('Y-m'))
                    ->map(fn($g, $m) => [
                        'month'     => $m,
                        'avg_hours' => round($g->avg(fn($f) => $f->created_at->diffInHours($f->resolved_at)), 1),
                        'count'     => $g->count(),
                    ])->sortKeys()->values();
                return [
                    'dept'    => $dept,
                    'monthly' => $monthly,
                ];
            })->values();

        // Öncelik bazında dağılım
        $priorityStats = $allFaults->groupBy('priority')
            ->map(fn($g, $k) => [
                'priority' => $k,
                'label'    => \App\Models\Fault::PRIORITIES[$k] ?? $k,
                'total'    => $g->count(),
                'open'     => $g->where('status', 'open')->count(),
                'closed'   => $g->whereIn('status', ['resolved', 'closed'])->count(),
            ])->sortByDesc('total')->values();

        $page_title = 'Arıza İstatistikleri';
        return view('modules.faults.stats', compact(
            'summary', 'deptScoreboard', 'typeStats',
            'locationStats', 'areaStats', 'monthlyTrend', 'branchStats',
            'locationTypeStats', 'areaTypeStats', 'deptMonthlyResolution', 'priorityStats',
            'period', 'page_title'
        ));
    }

    /* ---------------------------------------------------------------
     | ODA BAZLI RAPOR
     --------------------------------------------------------------- */
    public function roomReport(Request $request)
    {
        return view('modules.faults.reports.room', $this->buildRoomReportPayload($request));

        $request->validate([
            'branch_id'         => 'nullable|integer',
            'fault_location_id' => 'nullable|integer',
            'fault_area_id'     => 'nullable|integer',
            'fault_type_id'     => 'nullable|integer',
            'status'            => 'nullable|in:open,in_progress,winter_plan,waiting_material,resolved,closed',
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $locations = FaultLocation::with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $areas = FaultArea::with('location.branch')
            ->where('is_active', true)
            ->whereHas('location', fn($q) => $q->whereIn('branch_id', $branchIds)->where('is_active', true))
            ->get()
            ->sortBy(fn($area) => ($area->location?->branch?->name ?? '') . ' ' . ($area->location?->name ?? '') . ' ' . $area->name)
            ->values();
        $faultTypes = FaultType::where('is_active', true)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('name')
            ->get();

        $selectedArea = null;
        if ($request->filled('branch_id')) {
            abort_if(!in_array((int) $request->branch_id, $branchIds, true), 403);
        }
        if ($request->filled('fault_location_id')) {
            abort_if(!FaultLocation::whereKey($request->fault_location_id)->whereIn('branch_id', $branchIds)->exists(), 403);
        }
        if ($request->filled('fault_area_id')) {
            $selectedArea = FaultArea::with('location.branch')
                ->whereKey($request->fault_area_id)
                ->whereHas('location', fn($q) => $q->whereIn('branch_id', $branchIds))
                ->firstOrFail();
        }

        $hasSearch = $request->filled('branch_id')
            || $request->filled('fault_location_id')
            || $request->filled('fault_area_id')
            || $request->filled('fault_type_id')
            || $request->filled('status')
            || $request->filled('date_from')
            || $request->filled('date_to');

        $baseQuery = Fault::with(['branch', 'department', 'faultType', 'faultLocation', 'faultArea', 'reporter'])
            ->whereIn('branch_id', $branchIds);

        if ($request->filled('branch_id')) {
            $baseQuery->where('branch_id', $request->branch_id);
        }
        if ($request->filled('fault_location_id')) {
            $baseQuery->where('fault_location_id', $request->fault_location_id);
        }
        if ($request->filled('fault_area_id')) {
            $baseQuery->where('fault_area_id', $request->fault_area_id);
        }
        if ($request->filled('fault_type_id')) {
            $baseQuery->where('fault_type_id', $request->fault_type_id);
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $reportFaults = $hasSearch ? (clone $baseQuery)->latest()->get() : collect();
        $faults = $hasSearch
            ? (clone $baseQuery)->latest()->paginate(30)->withQueryString()
            : $this->emptyFaultPaginator($request);

        $typeStats = $reportFaults
            ->groupBy(fn($fault) => $fault->fault_type_id ?: 'unknown')
            ->map(function ($group) {
                $first = $group->first();
                $resolved = $group->whereIn('status', ['resolved', 'closed'])->count();
                $open = $group->whereNotIn('status', ['resolved', 'closed'])->count();
                $avgHours = $group->whereNotNull('resolved_at')->avg(fn($fault) => $fault->created_at->diffInMinutes($fault->resolved_at) / 60);

                return [
                    'type' => $first?->faultType,
                    'name' => $first?->faultType?->name ?? 'Tür seçilmemiş',
                    'total' => $group->count(),
                    'open' => $open,
                    'resolved' => $resolved,
                    'last_fault' => $group->sortByDesc('created_at')->first(),
                    'avg_hours' => $avgHours,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $summary = [
            'total' => $reportFaults->count(),
            'open' => $reportFaults->whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolved' => $reportFaults->whereIn('status', ['resolved', 'closed'])->count(),
            'type_count' => $typeStats->count(),
        ];

        $page_title = 'Oda Bazlı Arıza Raporu';

        return view('modules.faults.reports.room', compact(
            'branches', 'locations', 'areas', 'faultTypes', 'selectedArea',
            'faults', 'reportFaults', 'typeStats', 'summary', 'page_title'
        ));
    }

    /* ---------------------------------------------------------------
     | ARIZA TÜRÜ BAZLI RAPOR
     --------------------------------------------------------------- */
    public function typeReport(Request $request)
    {
        return view('modules.faults.reports.type', $this->buildTypeReportPayload($request));

        $request->validate([
            'branch_id'         => 'nullable|integer',
            'fault_location_id' => 'nullable|integer',
            'fault_type_id'     => 'nullable|integer',
            'status'            => 'nullable|in:open,in_progress,winter_plan,waiting_material,resolved,closed',
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $locations = FaultLocation::with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $faultTypes = FaultType::where('is_active', true)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('name')
            ->get();

        if ($request->filled('branch_id')) {
            abort_if(!in_array((int) $request->branch_id, $branchIds, true), 403);
        }
        if ($request->filled('fault_location_id')) {
            abort_if(!FaultLocation::whereKey($request->fault_location_id)->whereIn('branch_id', $branchIds)->exists(), 403);
        }

        $filterQuery = Fault::with(['branch', 'department', 'faultType', 'faultLocation', 'faultArea', 'reporter'])
            ->whereIn('branch_id', $branchIds);

        if ($request->filled('branch_id')) {
            $filterQuery->where('branch_id', $request->branch_id);
        }
        if ($request->filled('fault_location_id')) {
            $filterQuery->where('fault_location_id', $request->fault_location_id);
        }
        if ($request->filled('status')) {
            $filterQuery->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $filterQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $filterQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $topTypes = (clone $filterQuery)
            ->whereNotNull('fault_type_id')
            ->latest()
            ->get()
            ->groupBy('fault_type_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'type' => $first?->faultType,
                    'name' => $first?->faultType?->name ?? 'Tür seçilmemiş',
                    'total' => $group->count(),
                    'room_count' => $group->whereNotNull('fault_area_id')->pluck('fault_area_id')->unique()->count(),
                    'last_fault' => $group->sortByDesc('created_at')->first(),
                ];
            })
            ->sortByDesc('total')
            ->take(12)
            ->values();

        $selectedType = null;
        $reportFaults = collect();
        $faults = $this->emptyFaultPaginator($request);
        $roomStats = collect();
        $summary = ['total' => 0, 'room_count' => 0, 'open' => 0, 'resolved' => 0, 'top_room' => null];

        if ($request->filled('fault_type_id')) {
            $selectedType = FaultType::where('is_active', true)
                ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
                ->findOrFail($request->fault_type_id);

            $typeQuery = (clone $filterQuery)->where('fault_type_id', $selectedType->id);
            $reportFaults = (clone $typeQuery)->latest()->get();
            $faults = (clone $typeQuery)->latest()->paginate(30)->withQueryString();

            $roomStats = $reportFaults
                ->groupBy(fn($fault) => $fault->fault_area_id ?: 'no_area')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'area' => $first?->faultArea,
                        'location' => $first?->faultLocation,
                        'branch' => $first?->branch,
                        'room_name' => $first?->faultArea?->name ?? 'Alan seçilmemiş',
                        'location_name' => $first?->faultLocation?->name ?? '-',
                        'branch_name' => $first?->branch?->name ?? '-',
                        'total' => $group->count(),
                        'open' => $group->whereNotIn('status', ['resolved', 'closed'])->count(),
                        'resolved' => $group->whereIn('status', ['resolved', 'closed'])->count(),
                        'last_fault' => $group->sortByDesc('created_at')->first(),
                    ];
                })
                ->sortByDesc('total')
                ->values();

            $summary = [
                'total' => $reportFaults->count(),
                'room_count' => $roomStats->count(),
                'open' => $reportFaults->whereNotIn('status', ['resolved', 'closed'])->count(),
                'resolved' => $reportFaults->whereIn('status', ['resolved', 'closed'])->count(),
                'top_room' => $roomStats->first(),
            ];
        }

        $page_title = 'Arıza Bazlı Rapor';

        return view('modules.faults.reports.type', compact(
            'branches', 'locations', 'faultTypes', 'selectedType',
            'topTypes', 'roomStats', 'faults', 'reportFaults', 'summary', 'page_title'
        ));
    }

    /* ---------------------------------------------------------------
     | ANALİZ — Son 3 Gün Yapay Zeka Analiz Raporu
     --------------------------------------------------------------- */
    public function roomReportExcel(Request $request)
    {
        $payload = $this->buildRoomReportPayload($request);

        if (!$payload['hasSearch']) {
            return redirect()
                ->route('faults.room-report', $request->query())
                ->with('error', 'Excel almak için önce en az bir filtre seçip raporu oluşturun.');
        }

        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ozet');

        $filters = $this->buildFaultFilterSummary([
            'Şube' => optional($payload['branches']->firstWhere('id', (int) $request->branch_id))->name,
            'Konum' => optional($payload['locations']->firstWhere('id', (int) $request->fault_location_id))->name,
            'Oda / Alan' => $payload['selectedArea']?->name,
            'Arıza Türü' => optional($payload['faultTypes']->firstWhere('id', (int) $request->fault_type_id))->name,
            'Durum' => Fault::STATUSES[$request->status] ?? null,
            'Başlangıç' => $this->formatFilterDate($request->date_from),
            'Bitiş' => $this->formatFilterDate($request->date_to),
        ]);

        $metrics = [
            ['label' => 'Toplam Arıza', 'value' => $payload['summary']['total']],
            ['label' => 'Açık / Devam Eden', 'value' => $payload['summary']['open']],
            ['label' => 'Çözülen / Kapalı', 'value' => $payload['summary']['resolved']],
            ['label' => 'Kategori Sayısı', 'value' => $payload['summary']['type_count']],
        ];

        $this->buildFaultOverviewSheet(
            $summarySheet,
            'Oda Bazlı Arıza Raporu',
            'Seçili oda / alan filtresine ait genel görünüm ve aktif filtre özeti',
            $filters,
            $metrics,
            '#1E3A5F'
        );

        $this->buildFaultTableSheet(
            $spreadsheet,
            'Kategori Dagilimi',
            'Arıza türlerine göre yoğunluk kırılımı',
            '#1E3A5F',
            ['Arıza Türü', 'Toplam', 'Açık', 'Çözülen', 'Ort. Çözüm (saat)', 'Son Kayıt No', 'Son Kayıt Tarihi'],
            $payload['typeStats']->map(function (array $stat) {
                return [
                    $stat['name'],
                    $stat['total'],
                    $stat['open'],
                    $stat['resolved'],
                    $stat['avg_hours'] !== null ? round($stat['avg_hours'], 1) : '-',
                    $stat['last_fault']?->id ?? '-',
                    $stat['last_fault']?->created_at?->format('d.m.Y H:i') ?? '-',
                ];
            })->all()
        );

        $this->buildFaultTableSheet(
            $spreadsheet,
            'Ariza Listesi',
            'Filtreye giren tüm kayıtların detay tablosu',
            '#1E3A5F',
            ['ID', 'Tarih', 'Saat', 'Şube', 'Konum', 'Oda / Alan', 'Arıza Türü', 'Başlık', 'Departman', 'Bildiren', 'Durum', 'Öncelik', 'Çözüm Tarihi', 'Çözüm Süresi (saat)', 'Açıklama'],
            $payload['reportFaults']->map(function (Fault $fault) {
                $resolutionHours = $fault->resolved_at
                    ? round($fault->created_at->diffInMinutes($fault->resolved_at) / 60, 1)
                    : '-';

                return [
                    $fault->id,
                    $fault->created_at?->format('d.m.Y'),
                    $fault->created_at?->format('H:i'),
                    $fault->branch?->name ?? '-',
                    $fault->faultLocation?->name ?? '-',
                    $fault->faultArea?->name ?? '-',
                    $fault->faultType?->name ?? ($fault->title ?: '-'),
                    $fault->title ?: '-',
                    $fault->department?->name ?? '-',
                    $fault->reporter?->name ?? '-',
                    Fault::STATUSES[$fault->status] ?? $fault->status,
                    Fault::PRIORITIES[$fault->priority] ?? $fault->priority,
                    $fault->resolved_at?->format('d.m.Y H:i') ?? '-',
                    $resolutionHours,
                    trim(strip_tags((string) $fault->description)) ?: '-',
                ];
            })->all()
        );

        return $this->downloadSpreadsheet(
            $spreadsheet,
            'oda-ariza-raporu-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function typeReportExcel(Request $request)
    {
        $payload = $this->buildTypeReportPayload($request);

        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ozet');

        $filters = $this->buildFaultFilterSummary([
            'Arıza Türü' => $payload['selectedType']?->name,
            'Şube' => optional($payload['branches']->firstWhere('id', (int) $request->branch_id))->name,
            'Konum' => optional($payload['locations']->firstWhere('id', (int) $request->fault_location_id))->name,
            'Durum' => Fault::STATUSES[$request->status] ?? null,
            'Başlangıç' => $this->formatFilterDate($request->date_from),
            'Bitiş' => $this->formatFilterDate($request->date_to),
        ]);

        if ($payload['selectedType']) {
            $metrics = [
                ['label' => 'Seçilen Arıza', 'value' => $payload['selectedType']->name],
                ['label' => 'Toplam Kayıt', 'value' => $payload['summary']['total']],
                ['label' => 'Etkilenen Oda', 'value' => $payload['summary']['room_count']],
                ['label' => 'Açık Kayıt', 'value' => $payload['summary']['open']],
            ];

            $subtitle = 'Seçilen arıza türünün odalara göre dağılımı ve detaylı kayıt listesi';
        } else {
            $metrics = [
                ['label' => 'Listelenen Tür', 'value' => $payload['topTypes']->count()],
                ['label' => 'Toplam Kayıt Havuzu', 'value' => $payload['topTypes']->sum('total')],
                ['label' => 'Filtrelenen Konum', 'value' => $request->filled('fault_location_id') ? 1 : 'Tümü'],
                ['label' => 'Filtrelenen Şube', 'value' => $request->filled('branch_id') ? 1 : 'Tümü'],
            ];

            $subtitle = 'Arıza türü seçilmeden önce öne çıkan türlerin yoğunluk özeti';
        }

        $this->buildFaultOverviewSheet(
            $summarySheet,
            'Arıza Bazlı Rapor',
            $subtitle,
            $filters,
            $metrics,
            '#B45309'
        );

        if ($payload['selectedType']) {
            $this->buildFaultTableSheet(
                $spreadsheet,
                'Oda Dagilimi',
                'Seçilen türün en çok görüldüğü oda / alan kırılımı',
                '#B45309',
                ['Oda / Alan', 'Konum', 'Şube', 'Toplam', 'Açık', 'Çözülen', 'Son Kayıt No', 'Son Kayıt Tarihi'],
                $payload['roomStats']->map(function (array $room) {
                    return [
                        $room['room_name'],
                        $room['location_name'],
                        $room['branch_name'],
                        $room['total'],
                        $room['open'],
                        $room['resolved'],
                        $room['last_fault']?->id ?? '-',
                        $room['last_fault']?->created_at?->format('d.m.Y H:i') ?? '-',
                    ];
                })->all()
            );

            $this->buildFaultTableSheet(
                $spreadsheet,
                'Ariza Detaylari',
                'Seçilen arıza türüne ait ayrıntılı kayıt listesi',
                '#B45309',
                ['ID', 'Tarih', 'Saat', 'Şube', 'Konum', 'Oda / Alan', 'Departman', 'Bildiren', 'Durum', 'Öncelik', 'Başlık', 'Çözüm Tarihi', 'Açıklama'],
                $payload['reportFaults']->map(function (Fault $fault) {
                    return [
                        $fault->id,
                        $fault->created_at?->format('d.m.Y'),
                        $fault->created_at?->format('H:i'),
                        $fault->branch?->name ?? '-',
                        $fault->faultLocation?->name ?? '-',
                        $fault->faultArea?->name ?? '-',
                        $fault->department?->name ?? '-',
                        $fault->reporter?->name ?? '-',
                        Fault::STATUSES[$fault->status] ?? $fault->status,
                        Fault::PRIORITIES[$fault->priority] ?? $fault->priority,
                        $fault->title ?: '-',
                        $fault->resolved_at?->format('d.m.Y H:i') ?? '-',
                        trim(strip_tags((string) $fault->description)) ?: '-',
                    ];
                })->all()
            );
        } else {
            $this->buildFaultTableSheet(
                $spreadsheet,
                'On Plana Cikan Turler',
                'Filtrelere göre en sık görülen arıza türleri',
                '#B45309',
                ['Arıza Türü', 'Toplam', 'Etkilenen Oda', 'Son Kayıt No', 'Son Kayıt Tarihi'],
                $payload['topTypes']->map(function (array $item) {
                    return [
                        $item['name'],
                        $item['total'],
                        $item['room_count'],
                        $item['last_fault']?->id ?? '-',
                        $item['last_fault']?->created_at?->format('d.m.Y H:i') ?? '-',
                    ];
                })->all()
            );
        }

        return $this->downloadSpreadsheet(
            $spreadsheet,
            'ariza-turu-raporu-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    private function buildRoomReportPayload(Request $request): array
    {
        $request->validate([
            'branch_id'         => 'nullable|integer',
            'fault_location_id' => 'nullable|integer',
            'fault_area_id'     => 'nullable|integer',
            'fault_type_id'     => 'nullable|integer',
            'status'            => 'nullable|in:open,in_progress,winter_plan,waiting_material,resolved,closed',
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $locations = FaultLocation::with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $areas = FaultArea::with('location.branch')
            ->where('is_active', true)
            ->whereHas('location', fn ($q) => $q->whereIn('branch_id', $branchIds)->where('is_active', true))
            ->get()
            ->sortBy(fn ($area) => ($area->location?->branch?->name ?? '') . ' ' . ($area->location?->name ?? '') . ' ' . $area->name)
            ->values();
        $faultTypes = FaultType::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('name')
            ->get();

        $selectedArea = null;
        if ($request->filled('branch_id')) {
            abort_if(!in_array((int) $request->branch_id, $branchIds, true), 403);
        }
        if ($request->filled('fault_location_id')) {
            abort_if(!FaultLocation::whereKey($request->fault_location_id)->whereIn('branch_id', $branchIds)->exists(), 403);
        }
        if ($request->filled('fault_area_id')) {
            $selectedArea = FaultArea::with('location.branch')
                ->whereKey($request->fault_area_id)
                ->whereHas('location', fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->firstOrFail();
        }

        $hasSearch = $request->filled('branch_id')
            || $request->filled('fault_location_id')
            || $request->filled('fault_area_id')
            || $request->filled('fault_type_id')
            || $request->filled('status')
            || $request->filled('date_from')
            || $request->filled('date_to');

        $baseQuery = $this->makeFaultReportBaseQuery($branchIds);
        $this->applyRoomReportFilters($baseQuery, $request);

        $reportFaults = $hasSearch ? (clone $baseQuery)->latest()->get() : collect();
        $faults = $hasSearch
            ? (clone $baseQuery)->latest()->paginate(30)->withQueryString()
            : $this->emptyFaultPaginator($request);

        $typeStats = $reportFaults
            ->groupBy(fn ($fault) => $fault->fault_type_id ?: 'unknown')
            ->map(function (Collection $group) {
                $first = $group->first();
                $resolved = $group->whereIn('status', ['resolved', 'closed'])->count();
                $open = $group->whereNotIn('status', ['resolved', 'closed'])->count();
                $avgHours = $group->whereNotNull('resolved_at')->avg(
                    fn (Fault $fault) => $fault->created_at->diffInMinutes($fault->resolved_at) / 60
                );

                return [
                    'type' => $first?->faultType,
                    'name' => $first?->faultType?->name ?? 'Tür seçilmemiş',
                    'total' => $group->count(),
                    'open' => $open,
                    'resolved' => $resolved,
                    'last_fault' => $group->sortByDesc('created_at')->first(),
                    'avg_hours' => $avgHours,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'branches' => $branches,
            'locations' => $locations,
            'areas' => $areas,
            'faultTypes' => $faultTypes,
            'selectedArea' => $selectedArea,
            'faults' => $faults,
            'reportFaults' => $reportFaults,
            'typeStats' => $typeStats,
            'summary' => [
                'total' => $reportFaults->count(),
                'open' => $reportFaults->whereNotIn('status', ['resolved', 'closed'])->count(),
                'resolved' => $reportFaults->whereIn('status', ['resolved', 'closed'])->count(),
                'type_count' => $typeStats->count(),
            ],
            'hasSearch' => $hasSearch,
            'page_title' => 'Oda Bazlı Arıza Raporu',
        ];
    }

    private function buildTypeReportPayload(Request $request): array
    {
        $request->validate([
            'branch_id'         => 'nullable|integer',
            'fault_location_id' => 'nullable|integer',
            'fault_type_id'     => 'nullable|integer',
            'status'            => 'nullable|in:open,in_progress,winter_plan,waiting_material,resolved,closed',
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $locations = FaultLocation::with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $faultTypes = FaultType::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('name')
            ->get();

        if ($request->filled('branch_id')) {
            abort_if(!in_array((int) $request->branch_id, $branchIds, true), 403);
        }
        if ($request->filled('fault_location_id')) {
            abort_if(!FaultLocation::whereKey($request->fault_location_id)->whereIn('branch_id', $branchIds)->exists(), 403);
        }

        $filterQuery = $this->makeFaultReportBaseQuery($branchIds);
        $this->applyTypeReportFilters($filterQuery, $request);

        $topTypes = (clone $filterQuery)
            ->whereNotNull('fault_type_id')
            ->latest()
            ->get()
            ->groupBy('fault_type_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'type' => $first?->faultType,
                    'name' => $first?->faultType?->name ?? 'Tür seçilmemiş',
                    'total' => $group->count(),
                    'room_count' => $group->whereNotNull('fault_area_id')->pluck('fault_area_id')->unique()->count(),
                    'last_fault' => $group->sortByDesc('created_at')->first(),
                ];
            })
            ->sortByDesc('total')
            ->take(12)
            ->values();

        $selectedType = null;
        $reportFaults = collect();
        $faults = $this->emptyFaultPaginator($request);
        $roomStats = collect();
        $summary = ['total' => 0, 'room_count' => 0, 'open' => 0, 'resolved' => 0, 'top_room' => null];

        if ($request->filled('fault_type_id')) {
            $selectedType = FaultType::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
                ->findOrFail($request->fault_type_id);

            $typeQuery = (clone $filterQuery)->where('fault_type_id', $selectedType->id);
            $reportFaults = (clone $typeQuery)->latest()->get();
            $faults = (clone $typeQuery)->latest()->paginate(30)->withQueryString();

            $roomStats = $reportFaults
                ->groupBy(fn ($fault) => $fault->fault_area_id ?: 'no_area')
                ->map(function (Collection $group) {
                    $first = $group->first();

                    return [
                        'area' => $first?->faultArea,
                        'location' => $first?->faultLocation,
                        'branch' => $first?->branch,
                        'room_name' => $first?->faultArea?->name ?? 'Alan seçilmemiş',
                        'location_name' => $first?->faultLocation?->name ?? '-',
                        'branch_name' => $first?->branch?->name ?? '-',
                        'total' => $group->count(),
                        'open' => $group->whereNotIn('status', ['resolved', 'closed'])->count(),
                        'resolved' => $group->whereIn('status', ['resolved', 'closed'])->count(),
                        'last_fault' => $group->sortByDesc('created_at')->first(),
                    ];
                })
                ->sortByDesc('total')
                ->values();

            $summary = [
                'total' => $reportFaults->count(),
                'room_count' => $roomStats->count(),
                'open' => $reportFaults->whereNotIn('status', ['resolved', 'closed'])->count(),
                'resolved' => $reportFaults->whereIn('status', ['resolved', 'closed'])->count(),
                'top_room' => $roomStats->first(),
            ];
        }

        return [
            'branches' => $branches,
            'locations' => $locations,
            'faultTypes' => $faultTypes,
            'selectedType' => $selectedType,
            'topTypes' => $topTypes,
            'roomStats' => $roomStats,
            'faults' => $faults,
            'reportFaults' => $reportFaults,
            'summary' => $summary,
            'page_title' => 'Arıza Bazlı Rapor',
        ];
    }

    private function makeFaultReportBaseQuery(array $branchIds)
    {
        return Fault::with(['branch', 'department', 'faultType', 'faultLocation', 'faultArea', 'reporter'])
            ->whereIn('branch_id', $branchIds);
    }

    private function applyRoomReportFilters($query, Request $request): void
    {
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('fault_location_id')) {
            $query->where('fault_location_id', $request->fault_location_id);
        }
        if ($request->filled('fault_area_id')) {
            $query->where('fault_area_id', $request->fault_area_id);
        }
        if ($request->filled('fault_type_id')) {
            $query->where('fault_type_id', $request->fault_type_id);
        }

        $this->applySharedFaultReportFilters($query, $request);
    }

    private function applyTypeReportFilters($query, Request $request): void
    {
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('fault_location_id')) {
            $query->where('fault_location_id', $request->fault_location_id);
        }

        $this->applySharedFaultReportFilters($query, $request);
    }

    private function applySharedFaultReportFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    private function buildFaultFilterSummary(array $map): array
    {
        $items = [];

        foreach ($map as $label => $value) {
            if ($value !== null && $value !== '') {
                $items[] = $label . ': ' . $value;
            }
        }

        if (empty($items)) {
            $items[] = 'Filtre: Varsayılan görünüm';
        }

        $items[] = 'Rapor Tarihi: ' . now()->format('d.m.Y H:i');

        return $items;
    }

    private function formatFilterDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y');
    }

    private function buildFaultOverviewSheet(
        $sheet,
        string $title,
        string $subtitle,
        array $filters,
        array $metrics,
        string $accentColor
    ): void {
        $sheet->freezePane('A9');
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A4', 'Aktif Filtreler');
        $sheet->mergeCells('A4:F4');

        $row = 5;
        foreach ($filters as $filter) {
            $sheet->setCellValue('A' . $row, '- ' . $filter);
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;
        }

        $metricStartRow = max($row + 1, 9);
        $sheet->setCellValue('A' . $metricStartRow, 'Gösterge');
        $sheet->setCellValue('B' . $metricStartRow, 'Değer');

        $metricRow = $metricStartRow + 1;
        foreach ($metrics as $metric) {
            $sheet->setCellValue('A' . $metricRow, $metric['label']);
            $sheet->setCellValue('B' . $metricRow, $metric['value']);
            $metricRow++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setSize(11)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
        $sheet->getStyle('A' . $metricStartRow . ':B' . $metricStartRow)->applyFromArray($this->faultTableHeaderStyle($accentColor));
        $sheet->getStyle('A' . ($metricStartRow + 1) . ':B' . ($metricRow - 1))->applyFromArray($this->faultTableBodyStyle());
        $sheet->getStyle('A1:F' . max($metricRow - 1, 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        foreach (range(1, 6) as $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
    }

    private function buildFaultTableSheet(
        Spreadsheet $spreadsheet,
        string $title,
        string $subtitle,
        string $accentColor,
        array $headers,
        array $rows
    ): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($this->limitWorksheetTitle($title));
        $sheet->freezePane('A4');

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:' . $lastColumn . '2');

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '3', $header);
        }

        $rowIndex = 4;
        foreach ($rows as $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex + 1) . $rowIndex, $value);
            }
            $rowIndex++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('A3:' . $lastColumn . '3')->applyFromArray($this->faultTableHeaderStyle($accentColor));

        if ($rowIndex > 4) {
            $sheet->getStyle('A4:' . $lastColumn . ($rowIndex - 1))->applyFromArray($this->faultTableBodyStyle());
        } else {
            $sheet->setCellValue('A4', 'Kayıt bulunamadı.');
            $sheet->mergeCells('A4:' . $lastColumn . '4');
            $sheet->getStyle('A4')->getFont()->getColor()->setARGB('FF94A3B8');
        }

        $sheet->setAutoFilter('A3:' . $lastColumn . '3');

        for ($column = 1; $column <= count($headers); $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
    }

    private function faultTableHeaderStyle(string $accentColor): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->normalizeSpreadsheetColor($accentColor)],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
    }

    private function faultTableBodyStyle(): array
    {
        return [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
    }

    private function normalizeSpreadsheetColor(string $color): string
    {
        $normalized = strtoupper(ltrim($color, '#'));

        return strlen($normalized) === 6 ? 'FF' . $normalized : $normalized;
    }

    private function limitWorksheetTitle(string $title): string
    {
        return mb_substr($title, 0, 31);
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function analysis()
    {
        return view('modules.faults.analysis', $this->buildAnalysisPayload(request()));

        $user        = auth()->user();
        $branchIds   = $user->visibleBranchIds();
        $now         = now();
        $todayStart  = $now->copy()->startOfDay();
        $yestStart   = $now->copy()->subDay()->startOfDay();
        $windowStart = $now->copy()->subDays(2)->startOfDay(); // 3 günlük pencere

        $allFaults = Fault::with([
                'department', 'branch', 'faultType',
                'faultLocation', 'faultArea', 'reporter', 'updates',
            ])
            ->whereIn('branch_id', $branchIds)
            ->where('created_at', '>=', $windowStart)
            ->get();

        $todayFaults     = $allFaults->filter(fn($f) => $f->created_at->gte($todayStart));
        $yestFaults      = $allFaults->filter(fn($f) =>
            $f->created_at->gte($yestStart) && $f->created_at->lt($todayStart));
        $dayBeforeFaults = $allFaults->filter(fn($f) => $f->created_at->lt($yestStart));

        $insights = [];

        /* ── 1. AYNI ALAN + AYNI TÜR: Ardışık günlerde tekrar ─────────── */
        $areaTypeGroups = $allFaults
            ->filter(fn($f) => $f->fault_area_id && $f->fault_type_id)
            ->groupBy(fn($f) => $f->fault_area_id . '|' . $f->fault_type_id);

        foreach ($areaTypeGroups as $group) {
            $hasToday = $group->filter(fn($f) => $f->created_at->gte($todayStart))->count() > 0;
            $hasYest  = $group->filter(fn($f) =>
                $f->created_at->gte($yestStart) && $f->created_at->lt($todayStart)
            )->count() > 0;

            if ($hasToday && $hasYest) {
                $first    = $group->first();
                $areaName = $first->faultArea?->name   ?? '—';
                $locName  = $first->faultLocation?->name ?? '—';
                $typeName = $first->faultType?->name   ?? '—';
                $deptName = $first->department?->name  ?? '—';
                $has3Day  = $group->filter(fn($f) => $f->created_at->lt($yestStart))->count() > 0;

                $extra = $has3Day
                    ? ' <strong>3 gün üst üste aynı sorun kayıt altına alınmaktadır</strong>; bu durum ekipman arızasından ziyade altyapısal bir probleme işaret ediyor olabilir.'
                    : '';

                $insights[] = [
                    'level'  => 'critical',
                    'icon'   => 'fa-triangle-exclamation',
                    'title'  => '"' . $locName . ' — ' . $areaName . '" Alanında Ardışık Günlerde ' . $typeName . ' Tekrarı',
                    'body'   => sprintf(
                        'Sistem tarafından kritik bir yinelenme örüntüsü tespit edilmiştir: <strong>%s</strong> konumundaki <strong>%s</strong> numaralı alanda, <strong>%s</strong> türü arıza hem dün hem de bugün kayıt altına alınmıştır.%s '
                        . 'Ardışık günlerde aynı türde arızanın vuku bulması, gerçekleştirilen müdahalenin geçici bir çözüm sunduğunu ve altta yatan teknik sorunun kalıcı biçimde giderilmediğini kuvvetle düşündürmektedir. '
                        . '<strong>%s</strong> departmanının bu vakayı acilen köklü onarım perspektifiyle yeniden ele alması gerekmektedir. '
                        . 'Bu alan–tür kombinasyonu için son 3 gün içinde toplam <strong>%d</strong> arıza kaydı oluşturulmuştur.',
                        $locName, $areaName, $typeName, $extra, $deptName, $group->count()
                    ),
                    'faults' => $group,
                    'metric' => ['value' => $group->count() . '×', 'label' => '3 günde tekrar'],
                    'tags'   => [$typeName, $locName, $has3Day ? '3 Gün Üst Üste' : 'Ardışık Gün'],
                ];
            }
        }

        /* ── 2. HACIM: Alan başına 3+ arıza / 2 tekrar (aynı tür) ─────── */
        $areaVolume = $allFaults
            ->filter(fn($f) => $f->fault_area_id)
            ->groupBy('fault_area_id');

        foreach ($areaVolume as $group) {
            $first    = $group->first();
            $areaName = $first->faultArea?->name    ?? '—';
            $locName  = $first->faultLocation?->name ?? '—';

            if ($group->count() >= 3) {
                $openCnt  = $group->whereNotIn('status', ['resolved', 'closed'])->count();
                $typeList = $group->groupBy('fault_type_id')
                    ->map(fn($g) => $g->first()->faultType?->name ?? '—')
                    ->join(', ');

                $insights[] = [
                    'level'  => 'critical',
                    'icon'   => 'fa-fire',
                    'title'  => '"' . $locName . ' — ' . $areaName . '" Son 3 Günün Arıza Odak Noktası (' . $group->count() . ' Vaka)',
                    'body'   => sprintf(
                        '<strong>%s</strong> konumundaki <strong>%s</strong> numaralı alanda son 3 gün içinde <strong>%d</strong> farklı arıza vakası kayıt altına alınmıştır. '
                        . 'Tespit edilen arıza türleri: <em>%s</em>. '
                        . 'Bu denli yüksek arıza sıklığı, söz konusu alanın teknik ekipman envanterinin, elektrik-mekanik tesisatının veya fiziksel altyapısının köklü bir değerlendirmeden geçirilmesini zorunlu kılmaktadır. '
                        . 'Mevcut durumda <strong>%d</strong> arıza hâlâ açık konumdadır; bu vakalar için öncelikli müdahale planlanmalıdır.',
                        $locName, $areaName, $group->count(), $typeList, $openCnt
                    ),
                    'faults' => $group,
                    'metric' => ['value' => $group->count(), 'label' => 'arıza / 3 gün'],
                    'tags'   => [$locName, $areaName, 'Yoğun Alan'],
                ];
            } elseif ($group->count() === 2) {
                $typeIds = $group->pluck('fault_type_id')->unique();
                if ($typeIds->count() === 1) {
                    $typeName = $first->faultType?->name  ?? '—';
                    $deptName = $first->department?->name ?? '—';
                    $sorted   = $group->sortBy('created_at');

                    $insights[] = [
                        'level'  => 'warning',
                        'icon'   => 'fa-rotate',
                        'title'  => '"' . $areaName . '" Alanında ' . $typeName . ' İki Kez Tekrarlandı',
                        'body'   => sprintf(
                            '<strong>%s</strong> konumundaki <strong>%s</strong> alanında, <strong>%s</strong> arızası 3 günlük dönem içinde ikinci kez meydana gelmiştir. '
                            . 'İlk vaka <strong>%s</strong> tarihinde, ikinci vaka ise <strong>%s</strong> tarihinde bildirilmiştir. '
                            . 'İlk müdahalenin kalıcı bir çözüm sunmadığı anlaşılmaktadır; geçici onarımın kısa sürede yetersiz kaldığı görülmektedir. '
                            . '<strong>%s</strong> departmanının ilk arıza kaydını ve yapılan işlemi gözden geçirmesi, yinelemenin önüne geçmek adına kritik önem taşımaktadır.',
                            $locName, $areaName, $typeName,
                            $sorted->first()->created_at->format('d.m.Y H:i'),
                            $sorted->last()->created_at->format('d.m.Y H:i'),
                            $deptName
                        ),
                        'faults' => $group,
                        'metric' => ['value' => '2×', 'label' => 'tekrar'],
                        'tags'   => [$typeName, 'Yinelenen Arıza'],
                    ];
                }
            }
        }

        /* ── 3. YAVAŞ ÇÖZÜM: 6 saatin üzerinde ────────────────────────── */
        $slowResolved = $allFaults->filter(fn($f) =>
            $f->resolved_at &&
            $f->created_at->diffInMinutes($f->resolved_at) / 60 > 6
        );
        if ($slowResolved->count() > 0) {
            $worst      = $slowResolved->sortByDesc(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at)
            )->first();
            $worstHours = round($worst->created_at->diffInMinutes($worst->resolved_at) / 60, 1);
            $worstArea  = $worst->faultArea?->name ?? ($worst->faultLocation?->name ?? 'Belirtilmemiş');
            $avgH       = round($slowResolved->avg(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at) / 60
            ), 1);

            $insights[] = [
                'level'  => 'warning',
                'icon'   => 'fa-clock',
                'title'  => $slowResolved->count() . ' Arıza 6 Saatin Üzerinde Çözüm Süresiyle Kapatıldı',
                'body'   => sprintf(
                    'Son 3 günlük dönemde çözüme kavuşturulan arızalar incelendiğinde, <strong>%d</strong> vakanın 6 saatin üzerinde bir süre gerektirdiği görülmektedir. '
                    . 'Bu vakalar arasındaki ortalama çözüm süresi <strong>%.1f saat</strong> olarak ölçülmüştür. '
                    . 'En yavaş çözülen vaka, <strong>%s</strong> alanındaki <strong>%s</strong> arızasıdır; bu arıza <strong>%.1f saat</strong> içinde kapatılabilmiştir. '
                    . 'Uzun çözüm süreleri; ekip kapasitesi yetersizliği, yedek parça tedarik gecikmeleri veya acil müdahale önceliklendirmesindeki sorunların habercisi olabilir. '
                    . 'İlgili vakalar ve departmanlar incelenmeli, tekrarlayan gecikmelerin sistematik çözümüne odaklanılmalıdır.',
                    $slowResolved->count(), $avgH, $worstArea, $worst->faultType?->name ?? '—', $worstHours
                ),
                'faults' => $slowResolved,
                'metric' => ['value' => Fault::formatHours($avgH), 'label' => 'ort. gecikme'],
                'tags'   => ['Çözüm Süresi', 'SLA Riski'],
            ];
        }

        /* ── 4. ASKIDA ARIZA: 12 saatten uzun açık ─────────────────────── */
        $staleFaults = $allFaults->filter(fn($f) =>
            in_array($f->status, ['open', 'in_progress']) &&
            $f->created_at->diffInHours($now) > 12
        );
        if ($staleFaults->count() > 0) {
            $oldest    = $staleFaults->sortBy('created_at')->first();
            $oldestAge = round($oldest->created_at->diffInHours($now), 1);

            $insights[] = [
                'level'  => 'warning',
                'icon'   => 'fa-hourglass-half',
                'title'  => $staleFaults->count() . ' Arıza 12 Saatin Üzerinde Çözüm Bekliyor',
                'body'   => sprintf(
                    'Son 3 günlük pencerede açılan arızalar arasında <strong>%d</strong> tanesi, üzerinden 12 saatten fazla zaman geçmiş olmasına karşın hâlâ <em>açık</em> ya da <em>işlemde</em> statüsündedir. '
                    . 'Bu kayıtlar için çözüm sürecinin nerede askıda kaldığı araştırılmalıdır. '
                    . 'En eski bekleme kaydı; <strong>%s</strong> tarihinde %s alanında açılan <strong>%s</strong> arızasıdır ve bu arıza üzerinden <strong>%.1f saat</strong> geçmiştir. '
                    . 'Uzun süreli çözümsüz kayıtlar; yük kapasitesi aşımına, personel atama eksikliklerine veya materyal/yedek parça beklentisine işaret edebilir.',
                    $staleFaults->count(),
                    $oldest->created_at->format('d.m.Y H:i'),
                    $oldest->faultArea?->name ?? ($oldest->faultLocation?->name ?? 'Belirtilmemiş'),
                    $oldest->faultType?->name ?? '—',
                    $oldestAge
                ),
                'faults' => $staleFaults,
                'metric' => ['value' => $staleFaults->count(), 'label' => 'bekleyen'],
                'tags'   => ['Uzun Bekleme', 'Aksiyon Gerekli'],
            ];
        }

        /* ── 5. KRİTİK ÖNCELİK: Açık kalan kritik arızalar ────────────── */
        $criticalOpen = $allFaults->filter(fn($f) =>
            $f->priority === 'critical' && !in_array($f->status, ['resolved', 'closed'])
        );
        if ($criticalOpen->count() > 0) {
            $minAge = $criticalOpen->min(fn($f) => $f->created_at->diffInMinutes($now) / 60);
            $maxAge = $criticalOpen->max(fn($f) => $f->created_at->diffInMinutes($now) / 60);

            $insights[] = [
                'level'  => 'critical',
                'icon'   => 'fa-skull-crossbones',
                'title'  => 'KRİTİK: ' . $criticalOpen->count() . ' Acil Öncelikli Arıza Hâlâ Kapatılmadı',
                'body'   => sprintf(
                    '<em>Yüksek önem derecesi:</em> Son 3 günlük dönemde <strong>%d</strong> adet <strong>kritik</strong> öncelikli arıza kaydı oluşturulmuş, ancak bu vakalar henüz çözüme kavuşturulamamıştır. '
                    . 'Kritik öncelikli arızalar; misafir güvenliğini, tesis işletimini veya temel altyapı sistemlerini tehdit edebilecek niteliktedir. '
                    . 'Bu kayıtların üzerinden geçen süre <strong>%s</strong> ile <strong>%s</strong> arasında değişmektedir. '
                    . 'Yönetime ivedilikle bilgi verilmeli, gerekli kaynak tahsisi yapılmalı ve her kritik arıza için bireysel aksiyon planı hazırlanmalıdır.',
                    $criticalOpen->count(),
                    Fault::formatHours($minAge),
                    Fault::formatHours($maxAge)
                ),
                'faults' => $criticalOpen,
                'metric' => ['value' => $criticalOpen->count(), 'label' => 'kritik açık'],
                'tags'   => ['KRİTİK', 'Acil Müdahale'],
            ];
        }

        /* ── 6. SLA İHLALİ: Hedefi aşan çözümler ──────────────────────── */
        $slaBreaches = $allFaults->filter(fn($f) =>
            $f->resolved_at &&
            $f->faultType &&
            ($f->created_at->diffInMinutes($f->resolved_at) / 60) > $f->faultType->completion_hours
        );
        if ($slaBreaches->count() > 0) {
            $avgExcess = round($slaBreaches->avg(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at) / 60 - $f->faultType->completion_hours
            ), 1);
            $worstBreach = $slaBreaches->sortByDesc(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at) / 60 - $f->faultType->completion_hours
            )->first();
            $worstExcess = round(
                $worstBreach->created_at->diffInMinutes($worstBreach->resolved_at) / 60 - $worstBreach->faultType->completion_hours,
                1
            );

            $insights[] = [
                'level'  => 'warning',
                'icon'   => 'fa-shield-halved',
                'title'  => $slaBreaches->count() . ' Arızada SLA Hedefi Aşıldı — Ort. ' . $avgExcess . ' Sa. Fazla',
                'body'   => sprintf(
                    'SLA (Hizmet Seviyesi Anlaşması) uyum analizi: Son 3 günde çözüme kavuşturulan arızalar içinde <strong>%d</strong> vaka, belirlenen tamamlanma hedefini aşmıştır. '
                    . 'Ortalama SLA aşım süresi <strong>%.1f saat</strong> olarak ölçülmüştür. '
                    . 'En büyük ihlal <strong>%s</strong> türünde yaşanmış; bu vaka hedefin <strong>%.1f saat</strong> üzerinde tamamlanmıştır. '
                    . 'Kronik SLA ihlalleri misafir memnuniyetini düşürmekte, operasyonel maliyetleri artırmakta ve teknik ekip verimliliğine dair ciddi soru işaretleri doğurmaktadır. '
                    . 'İhlallerin en sık yaşandığı arıza türleri ve departmanlar öncelikli iyileştirme kapsamına alınmalıdır.',
                    $slaBreaches->count(), $avgExcess,
                    $worstBreach->faultType?->name ?? '—', $worstExcess
                ),
                'faults' => $slaBreaches,
                'metric' => ['value' => $slaBreaches->count(), 'label' => 'SLA ihlali'],
                'tags'   => ['SLA İhlali', 'Servis Kalitesi'],
            ];
        }

        /* ── 7. BUGÜN YANITSIZ KALAN ACİL ARIZA ────────────────────────── */
        $urgentStale = $todayFaults->filter(fn($f) =>
            in_array($f->priority, ['critical', 'high']) &&
            $f->status === 'open' &&
            $f->created_at->diffInHours($now) >= 2
        );
        if ($urgentStale->count() > 0) {
            $insights[] = [
                'level'  => 'critical',
                'icon'   => 'fa-bell-slash',
                'title'  => 'Bugün Açılan ' . $urgentStale->count() . ' Yüksek/Kritik Öncelikli Arıza 2+ Saattir Yanıtsız',
                'body'   => sprintf(
                    'Bugün sisteme iletilen arıza kayıtları arasında <strong>%d</strong> adet yüksek veya kritik öncelikli bildirim, '
                    . 'açılışından itibaren 2 saatten fazla süre geçmesine rağmen hâlâ <em>açık</em> statüsünde beklemeye devam etmektedir. '
                    . 'Yüksek ve kritik öncelikli arızalarda bildirim anından itibaren derhal müdahale başlatılması operasyonel güvenlik açısından zorunludur. '
                    . 'Bu kayıtların ilgili departman yöneticilerine ivedilikle iletilmesi ve statü güncellemesinin yapılması gerekmektedir. '
                    . 'Yanıtsız acil vakalar operasyonel riski artırmakta; misafir deneyimini ve otel itibarını doğrudan tehdit etmektedir.',
                    $urgentStale->count()
                ),
                'faults' => $urgentStale,
                'metric' => ['value' => $urgentStale->count(), 'label' => 'yanıtsız acil'],
                'tags'   => ['Acil', 'Yanıtsız', 'Bugün'],
            ];
        }

        /* ── 8. DEPARTMAN YÜKÜ: En fazla arıza alan birim ──────────────── */
        if ($allFaults->count() >= 3) {
            $deptLoad = $allFaults
                ->groupBy('assigned_department_id')
                ->map(fn($g) => [
                    'dept'       => $g->first()->department,
                    'count'      => $g->count(),
                    'open'       => $g->whereNotIn('status', ['resolved', 'closed'])->count(),
                    'todayCount' => $g->filter(fn($f) => $f->created_at->gte($todayStart))->count(),
                ])
                ->sortByDesc('count');

            $topDept = $deptLoad->first();
            if ($topDept && $deptLoad->count() > 1) {
                $secondDept = $deptLoad->skip(1)->first();
                $loadRatio  = $allFaults->count() > 0
                    ? round($topDept['count'] / $allFaults->count() * 100) : 0;

                $insights[] = [
                    'level'  => 'info',
                    'icon'   => 'fa-sitemap',
                    'title'  => ($topDept['dept']?->name ?? '—') . ' Departmanı En Yüksek Arıza Yükünü Taşıyor (%' . $loadRatio . ')',
                    'body'   => sprintf(
                        'Departman bazlı yük analizi: Son 3 günlük arıza dağılımı değerlendirildiğinde, toplam kaydın <strong>%%%d</strong>\'i (<strong>%d arıza</strong>) <strong>%s</strong> departmanına yönlendirilmiştir. '
                        . 'Bu departmanda halihazırda <strong>%d</strong> arıza açık/işlemde statüsündedir; bugün ise bu birime <strong>%d</strong> yeni arıza iletilmiştir. '
                        . 'İkinci sırada <strong>%d arıza</strong> ile <strong>%s</strong> departmanı bulunmaktadır. '
                        . 'Yük dengesizliği kronik bir hal alıyor ise kaynak ve personel dağılımı yeniden değerlendirilmelidir.',
                        $loadRatio,
                        $topDept['count'], $topDept['dept']?->name ?? '—',
                        $topDept['open'],
                        $topDept['todayCount'],
                        $secondDept['count'], $secondDept['dept']?->name ?? '—'
                    ),
                    'faults' => null,
                    'metric' => ['value' => '%' . $loadRatio, 'label' => 'dept payı'],
                    'tags'   => ['Departman Yükü', $topDept['dept']?->name ?? '—'],
                ];
            }
        }

        /* ── 9. SAATSEL PİK: En yoğun saat dilimi ──────────────────────── */
        if ($allFaults->count() >= 3) {
            $hourDist = $allFaults
                ->groupBy(fn($f) => (int) $f->created_at->format('H'))
                ->map(fn($g) => $g->count())
                ->sortByDesc(fn($v) => $v);

            $peakHour  = $hourDist->keys()->first();
            $peakCount = $hourDist->first();

            if ($peakCount >= 2) {
                $slot = match (true) {
                    $peakHour >= 6  && $peakHour < 12 => 'sabah (06:00–12:00)',
                    $peakHour >= 12 && $peakHour < 14 => 'öğle (12:00–14:00)',
                    $peakHour >= 14 && $peakHour < 18 => 'öğleden sonra (14:00–18:00)',
                    $peakHour >= 18 && $peakHour < 22 => 'akşam (18:00–22:00)',
                    default                            => 'gece (22:00–06:00)',
                };

                $insights[] = [
                    'level'  => 'info',
                    'icon'   => 'fa-clock-rotate-left',
                    'title'  => sprintf('Arıza Bildirimlerinde Pik Saat: %02d:00–%02d:00 Bandında Yoğunlaşma', $peakHour, ($peakHour + 1) % 24),
                    'body'   => sprintf(
                        'Saatlik dağılım analizi, son 3 günlük dönemdeki arıza bildirimlerinin <strong>%s</strong> zaman diliminde belirgin biçimde yoğunlaştığını ortaya koymaktadır. '
                        . 'Tam olarak <strong>%02d:00–%02d:00</strong> saatleri arasında <strong>%d</strong> arıza kaydı oluşturulmuştur. '
                        . 'Bu zaman dilimine teknik personel erişiminin ve müdahale hızının yeterli olup olmadığı değerlendirilmelidir. '
                        . 'Pik saat aralığında ekip hazırlık düzeyinin artırılması, çözüm sürelerini ve misafir memnuniyetini doğrudan iyileştirebilir.',
                        $slot, $peakHour, ($peakHour + 1) % 24, $peakCount
                    ),
                    'faults' => null,
                    'metric' => ['value' => sprintf('%02d:00', $peakHour), 'label' => 'pik saat'],
                    'tags'   => ['Zaman Analizi', sprintf('%02d:00 Pik', $peakHour)],
                ];
            }
        }

        /* ── 10. GECE ARIZA: 22:00–06:00 bildirimleri ──────────────────── */
        $nightFaults = $allFaults->filter(fn($f) =>
            $f->created_at->hour >= 22 || $f->created_at->hour < 6
        );
        if ($nightFaults->count() >= 2) {
            $insights[] = [
                'level'  => 'info',
                'icon'   => 'fa-moon',
                'title'  => 'Gece Saatlerinde (22:00–06:00) ' . $nightFaults->count() . ' Arıza Bildirimi Alındı',
                'body'   => sprintf(
                    'Son 3 günlük dönem içinde <strong>%d</strong> arıza, gece saatleri (22:00–06:00) aralığında sisteme iletilmiştir. '
                    . 'Bu vakalar, gece vardiyasındaki teknik ekibin müdahale kapasitesini ve yanıt hızını sorgulatmaktadır. '
                    . 'Gece saatlerinde meydana gelen arızaların çözüm süreleri gündüz vakalarıyla kıyaslanmalı; '
                    . 'eğer gece yanıt süreleri belirgin biçimde uzunsa gece nöbet çizelgesi ve teknik personel dağılımı yeniden planlanmalıdır.',
                    $nightFaults->count()
                ),
                'faults' => $nightFaults,
                'metric' => ['value' => $nightFaults->count(), 'label' => 'gece arızası'],
                'tags'   => ['Gece Vardiyası', 'Nöbet Analizi'],
            ];
        }

        /* ── 11. SICAK NOKTA: En sorunlu konum ─────────────────────────── */
        $locGroups = $allFaults
            ->groupBy('fault_location_id')
            ->map(fn($g) => [
                'name'     => $g->first()->faultLocation?->name ?? 'Belirtilmemiş',
                'count'    => $g->count(),
                'open'     => $g->whereNotIn('status', ['resolved', 'closed'])->count(),
                'typeList' => $g->groupBy('fault_type_id')
                    ->map(fn($tg) => $tg->first()->faultType?->name ?? '—')
                    ->join(', '),
            ])
            ->sortByDesc('count');

        $hotLoc = $locGroups->first();
        if ($hotLoc && $hotLoc['count'] >= 2 && $locGroups->count() > 1) {
            $insights[] = [
                'level'  => 'warning',
                'icon'   => 'fa-location-dot',
                'title'  => '"' . $hotLoc['name'] . '" Son 3 Günün En Sorunlu Konumu (' . $hotLoc['count'] . ' Arıza)',
                'body'   => sprintf(
                    'Konum bazlı yoğunluk analizi: <strong>%s</strong> bölgesi son 3 gün içinde <strong>%d</strong> arıza kaydıyla öne çıkan sıcak nokta olarak belirlenmiştir. '
                    . 'Bu konumda kayıt altına alınan arıza türleri: <em>%s</em>. '
                    . 'Bölgede hâlâ <strong>%d</strong> açık arıza bulunmakta olup, konumun bu periyottaki arıza yoğunluğu yaş faktörü, kullanım sıklığı veya altyapı kalitesiyle doğrudan ilişkili olabilir. '
                    . 'Konum bazlı önleyici bakım planı gözden geçirilmelidir.',
                    $hotLoc['name'], $hotLoc['count'], $hotLoc['typeList'], $hotLoc['open']
                ),
                'faults' => null,
                'metric' => ['value' => $hotLoc['count'], 'label' => 'konum arızası'],
                'tags'   => [$hotLoc['name'], 'Sıcak Nokta'],
            ];
        }

        /* ── 12. GÜNLÜK KARŞILAŞTIRMA: Bugün vs dün ────────────────────── */
        $todayCnt    = $todayFaults->count();
        $yestCnt     = $yestFaults->count();
        $todayClosed = $todayFaults->filter(fn($f) => in_array($f->status, ['resolved', 'closed']))->count();

        if ($todayCnt > 0 || $yestCnt > 0) {
            $diff  = $todayCnt - $yestCnt;
            $level = match (true) {
                $diff >= 4  => 'warning',
                $diff <= -3 => 'positive',
                default     => 'info',
            };
            $trendMsg = match (true) {
                $diff > 0 => sprintf('Dün ile kıyaslandığında <strong>%d</strong> adet daha fazla arıza bildirilmiştir; bu artış dikkat yönetimini gerektirir.', $diff),
                $diff < 0 => sprintf('Dün ile kıyaslandığında <strong>%d</strong> adet daha az arıza bildirilmiştir; olumlu bir azalma sinyali olarak değerlendirilebilir.', abs($diff)),
                default   => 'Bugünkü arıza sayısı dünkü ile aynı seviyededir; durum sabit seyretmektedir.',
            };

            $insights[] = [
                'level'  => $level,
                'icon'   => 'fa-chart-simple',
                'title'  => 'Günlük Karşılaştırma — Bugün: ' . $todayCnt . ' Arıza | Dün: ' . $yestCnt . ' Arıza',
                'body'   => sprintf(
                    'Günlük karşılaştırmalı analiz: Bugün toplam <strong>%d</strong> arıza kaydı oluşturulmuş, bunların <strong>%d</strong> adedi aynı gün içinde çözüme kavuşturulmuştur. '
                    . '%s '
                    . 'Bugünkü kritik öncelikli arıza sayısı <strong>%d</strong>, yüksek öncelikli arıza sayısı ise <strong>%d</strong> olarak gerçekleşmiştir.',
                    $todayCnt, $todayClosed, $trendMsg,
                    $todayFaults->where('priority', 'critical')->count(),
                    $todayFaults->where('priority', 'high')->count()
                ),
                'faults' => null,
                'metric' => ['value' => ($diff >= 0 ? '+' : '') . $diff, 'label' => 'dünden fark'],
                'tags'   => ['Günlük Özet', 'Trend'],
            ];
        }

        /* ── 13. HIZLI ÇÖZÜM: 2 saat altında kapanan bugünkü arızalar (OLUMLU) ── */
        $fastToday = $todayFaults->filter(fn($f) =>
            $f->resolved_at &&
            $f->created_at->diffInHours($f->resolved_at) <= 2
        );
        if ($fastToday->count() > 0) {
            $fastAvg = round($fastToday->avg(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at)
            ), 0);

            $insights[] = [
                'level'  => 'positive',
                'icon'   => 'fa-bolt',
                'title'  => 'Bugün ' . $fastToday->count() . ' Arıza 2 Saat İçinde Çözüme Kavuşturuldu',
                'body'   => sprintf(
                    'Bugünün performans tablosunda son derece olumlu bir gösterge öne çıkmaktadır: Bugün açılan arızaların <strong>%d</strong> adedi, bildirimi takiben en fazla <strong>2 saat</strong> içinde başarıyla kapatılmıştır. '
                    . 'Bu vakalar için ortalama çözüm süresi <strong>%d dakika</strong> olarak ölçülmüştür. '
                    . 'Bu olağanüstü yanıt hızı, ilgili departmanların yüksek hazırlık düzeyini, etkin koordinasyonu ve güçlü operasyonel disiplini açıkça yansıtmaktadır. '
                    . 'Bu başarı modelinin tüm departmanlara örnek alınması ve sürdürülmesi tavsiye edilir.',
                    $fastToday->count(), $fastAvg
                ),
                'faults' => $fastToday,
                'metric' => ['value' => $fastToday->count(), 'label' => 'hızlı çözüm'],
                'tags'   => ['Başarılı', 'Hızlı Yanıt', 'Olumlu'],
            ];
        }

        /* ── 14. OLUMLU: Bugün hiç arıza yok ───────────────────────────── */
        if ($todayFaults->count() === 0 && $allFaults->count() > 0) {
            $insights[] = [
                'level'  => 'positive',
                'icon'   => 'fa-circle-check',
                'title'  => 'Bugün Henüz Hiç Arıza Bildirimi Alınmadı',
                'body'   => 'Bugünün veri tabanı incelendiğinde, bu tarihe ait herhangi bir arıza kaydına rastlanmamaktadır. '
                    . 'Bu sonuç, teknolojik altyapının ve ekipman bakımının etkili biçimde sürdürüldüğüne dair önemli bir göstergedir. '
                    . 'Tüm teknik ekiplerin ve departmanların hazırlık düzeyi yüksek görünmektedir. '
                    . 'Ancak bu tablonun sürdürülebilir kılınması için planlanmış önleyici bakım programlarının eksiksiz uygulanması kritik önem taşımaktadır.',
                'faults' => null,
                'metric' => ['value' => '0', 'label' => 'bugün arıza'],
                'tags'   => ['Sessiz Gün', 'Olumlu'],
            ];
        }

        /* ── 15. HİÇ VERİ YOK ──────────────────────────────────────────── */
        if ($allFaults->count() === 0) {
            $insights[] = [
                'level'  => 'positive',
                'icon'   => 'fa-circle-check',
                'title'  => 'Son 3 Günde Herhangi Bir Arıza Kaydı Bulunmamaktadır',
                'body'   => 'Analiz edilen 3 günlük dönem (' . $windowStart->format('d.m.Y') . ' – ' . $now->format('d.m.Y') . ') boyunca hiçbir arıza bildirimi sisteme iletilmemiştir. '
                    . 'Bu istisnai bir operasyonel performans göstergesidir. Tüm ekipmanlar, tesisler ve teknik altyapılar normal çalışma koşullarında seyretmekte olup herhangi bir acil müdahale gerekmemektedir.',
                'faults' => null,
                'metric' => ['value' => '0', 'label' => '3 günde arıza'],
                'tags'   => ['Mükemmel', 'Sorunsuz'],
            ];
        }

        /* Sıralama: critical → warning → info → positive */
        $order = ['critical' => 0, 'warning' => 1, 'info' => 2, 'positive' => 3];
        usort($insights, fn($a, $b) => ($order[$a['level']] ?? 9) <=> ($order[$b['level']] ?? 9));

        $narrative = $this->buildAnalysisNarrative($allFaults, $insights, $todayFaults, $yestFaults, $now);

        $page_title = 'Yapay Zeka Analizi';
        return view('modules.faults.analysis', compact(
            'insights', 'allFaults', 'todayFaults', 'yestFaults',
            'dayBeforeFaults', 'windowStart', 'now', 'page_title', 'narrative'
        ));
    }

    /* ---------------------------------------------------------------
     | ANALİZ RAPORU E-POSTA GÖNDER (AJAX POST)
     --------------------------------------------------------------- */
    public function sendAnalysisReport(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $payload = $this->buildAnalysisPayload($request);
        $allFaults = $payload['allFaults'];
        $insights = $payload['insights'];
        $narrative = $payload['narrative'];
        $now = $payload['now'];
        $analysisMeta = $payload['analysisMeta'];
        $grouped = collect($insights)->groupBy('level');

        $viewData = [
            'insights' => $insights,
            'narrative' => $narrative,
            'windowStart' => $analysisMeta['range_start']->format('d.m.Y'),
            'windowEnd' => $analysisMeta['range_end']->format('d.m.Y'),
            'rangeLabel' => $analysisMeta['range_label'],
            'periodLabel' => $analysisMeta['period_label'],
            'reportDate' => $now->format('d.m.Y H:i'),
            'totalFaults' => $allFaults->count(),
            'criticalCount' => $grouped->get('critical', collect())->count(),
            'warningCount' => $grouped->get('warning', collect())->count(),
            'infoCount' => $grouped->get('info', collect())->count(),
            'positiveCount' => $grouped->get('positive', collect())->count(),
            'todayCount' => $payload['todayFaults']->count(),
            'todayLabel' => $analysisMeta['last_day_label'],
            'findingCount' => count($insights),
        ];

        $pdf = Pdf::loadView('pdf.fault_analysis', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'         => 'DejaVu Sans',
                'isHtml5ParserEnabled'=> true,
                'isRemoteEnabled'     => false,
            ]);

        $mailable = new FaultAnalysisReport(
            pdfContent:    $pdf->output(),
            reportDate:    $now->format('d.m.Y H:i'),
            totalFaults:   $allFaults->count(),
            criticalCount: $viewData['criticalCount'],
            findingCount:  count($insights),
        );
        $mailable->with($viewData);

        Mail::to($request->email)->send($mailable);

        return response()->json([
            'success' => true,
            'message' => 'Rapor başarıyla ' . $request->email . ' adresine gönderildi.',
        ]);

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user        = auth()->user();
        $branchIds   = $user->visibleBranchIds();
        $now         = now();
        $todayStart  = $now->copy()->startOfDay();
        $windowStart = $now->copy()->subDays(2)->startOfDay();

        $allFaults = Fault::with([
                'department', 'branch', 'faultType',
                'faultLocation', 'faultArea', 'reporter', 'updates',
            ])
            ->whereIn('branch_id', $branchIds)
            ->where('created_at', '>=', $windowStart)
            ->get();

        $todayFaults  = $allFaults->filter(fn($f) => $f->created_at->gte($todayStart));
        $yestStart    = $now->copy()->subDay()->startOfDay();
        $yestFaults   = $allFaults->filter(fn($f) =>
            $f->created_at->gte($yestStart) && $f->created_at->lt($todayStart)
        );

        // Aynı analiz mantığını kullan
        $insights = $this->buildAnalysisInsights($allFaults, $now, $todayStart, $yestStart, $windowStart);
        $narrative = $this->buildAnalysisNarrative($allFaults, $insights, $todayFaults, $yestFaults, $now);

        $grouped = collect($insights)->groupBy('level');
        $viewData = [
            'insights'      => $insights,
            'narrative'     => $narrative,
            'windowStart'   => $windowStart->format('d.m.Y'),
            'reportDate'    => $now->format('d.m.Y H:i'),
            'totalFaults'   => $allFaults->count(),
            'criticalCount' => $grouped->get('critical', collect())->count(),
            'warningCount'  => $grouped->get('warning',  collect())->count(),
            'infoCount'     => $grouped->get('info',     collect())->count(),
            'positiveCount' => $grouped->get('positive', collect())->count(),
            'todayCount'    => $todayFaults->count(),
            'findingCount'  => count($insights),
        ];

        $pdf = Pdf::loadView('pdf.fault_analysis', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'         => 'DejaVu Sans',
                'isHtml5ParserEnabled'=> true,
                'isRemoteEnabled'     => false,
            ]);

        $mailable = new FaultAnalysisReport(
            pdfContent:    $pdf->output(),
            reportDate:    $now->format('d.m.Y H:i'),
            totalFaults:   $allFaults->count(),
            criticalCount: $viewData['criticalCount'],
            findingCount:  count($insights),
        );
        $mailable->with($viewData);

        Mail::to($request->email)->send($mailable);

        return response()->json([
            'success' => true,
            'message' => 'Rapor başarıyla ' . $request->email . ' adresine gönderildi.',
        ]);
    }

    /* ---------------------------------------------------------------
     | ORTAK: ANALİZ HESAPLAMA
     --------------------------------------------------------------- */
    private function emptyFaultPaginator(Request $request, int $perPage = 30): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function buildAnalysisPayload(Request $request): array
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $now = now();

        if ($request->filled('date_from')) {
            $rangeStart = Carbon::parse($request->date_from)->startOfDay();
        } elseif ($request->filled('date_to')) {
            $rangeStart = Carbon::parse($request->date_to)->subDays(2)->startOfDay();
        } else {
            $rangeStart = $now->copy()->subDays(2)->startOfDay();
        }

        if ($request->filled('date_to')) {
            $rangeEnd = Carbon::parse($request->date_to)->endOfDay();
        } elseif ($request->filled('date_from')) {
            $rangeEnd = Carbon::parse($request->date_from)->endOfDay();
        } else {
            $rangeEnd = $now->copy();
        }

        if ($rangeEnd->gt($now)) {
            $rangeEnd = $now->copy();
        }

        $rangeEndDay = $rangeEnd->copy()->startOfDay();
        $periodDays = max(1, $rangeStart->copy()->startOfDay()->diffInDays($rangeEndDay) + 1);
        $previousDayStart = $rangeEndDay->copy()->subDay();

        $allFaults = Fault::with([
                'department', 'branch', 'faultType',
                'faultLocation', 'faultArea', 'reporter', 'updates',
            ])
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->get();

        $todayFaults = $allFaults->filter(fn ($fault) => $fault->created_at->gte($rangeEndDay));
        $yestFaults = $periodDays >= 2
            ? $allFaults->filter(fn ($fault) => $fault->created_at->gte($previousDayStart) && $fault->created_at->lt($rangeEndDay))
            : collect();

        $analysisMeta = [
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'range_end_day' => $rangeEndDay,
            'range_start_text' => $rangeStart->format('d.m.Y 00:00'),
            'range_end_text' => $rangeEnd->format('d.m.Y H:i'),
            'range_label' => $rangeStart->format('d.m.Y') === $rangeEndDay->format('d.m.Y')
                ? $rangeStart->format('d.m.Y')
                : $rangeStart->format('d.m.Y') . ' – ' . $rangeEndDay->format('d.m.Y'),
            'period_days' => $periodDays,
            'period_label' => $periodDays === 1 ? '1 günlük analiz' : $periodDays . ' günlük analiz',
            'period_phrase' => $periodDays === 1 ? 'seçilen gün' : 'seçilen ' . $periodDays . ' günlük dönem',
            'last_day_label' => $rangeEndDay->format('d.m.Y'),
            'previous_day_label' => $previousDayStart->format('d.m.Y'),
            'comparison_enabled' => $periodDays >= 2,
            'query' => [
                'date_from' => $rangeStart->toDateString(),
                'date_to' => $rangeEndDay->toDateString(),
            ],
        ];

        $insights = $this->buildAdaptiveAnalysisInsights($allFaults, $now, $todayFaults, $yestFaults, $analysisMeta);
        $narrative = $this->buildAdaptiveAnalysisNarrative($allFaults, $insights, $todayFaults, $yestFaults, $analysisMeta);

        $page_title = 'Yapay Zeka Analizi';
        $windowStart = $rangeStart;

        return compact(
            'insights',
            'allFaults',
            'todayFaults',
            'yestFaults',
            'now',
            'page_title',
            'narrative',
            'analysisMeta',
            'windowStart'
        );
    }

    private function buildAdaptiveAnalysisInsights(Collection $allFaults, Carbon $now, Collection $todayFaults, Collection $yestFaults, array $meta): array
    {
        $insights = [];
        $periodPhrase = $meta['period_phrase'];
        $lastDayLabel = $meta['last_day_label'];
        $previousDayLabel = $meta['previous_day_label'];

        $areaTypeGroups = $allFaults
            ->filter(fn ($fault) => $fault->fault_area_id && $fault->fault_type_id)
            ->groupBy(fn ($fault) => $fault->fault_area_id . '|' . $fault->fault_type_id);

        foreach ($areaTypeGroups as $group) {
            $distinctDays = $group->pluck('created_at')->map(fn ($date) => $date->format('Y-m-d'))->unique()->values();
            if ($distinctDays->count() < 2) {
                continue;
            }

            $first = $group->first();
            $typeName = $first?->faultType?->name ?? 'Belirsiz arıza';
            $areaName = $first?->faultArea?->name ?? 'Belirtilmemiş alan';
            $locName = $first?->faultLocation?->name ?? 'Belirtilmemiş konum';
            $deptName = $first?->department?->name ?? 'İlgili departman';

            $insights[] = [
                'level' => $distinctDays->count() >= 3 ? 'critical' : 'warning',
                'icon' => $distinctDays->count() >= 3 ? 'fa-triangle-exclamation' : 'fa-rotate',
                'title' => '"' . $locName . ' – ' . $areaName . '" noktasında ' . $typeName . ' tekrarı',
                'body' => sprintf(
                    '<strong>%s</strong> konumundaki <strong>%s</strong> alanında <strong>%s</strong> kaydı %s içinde <strong>%d farklı günde</strong> tekrarlandı. En son kayıt <strong>%s</strong> tarihinde açıldı. Bu örüntü, <strong>%s</strong> tarafında geçici müdahale yerine kök neden kontrolü gerektiğini gösteriyor.',
                    $locName,
                    $areaName,
                    $typeName,
                    $periodPhrase,
                    $distinctDays->count(),
                    $group->sortByDesc('created_at')->first()?->created_at?->format('d.m.Y H:i') ?? '-',
                    $deptName
                ),
                'faults' => $group->sortByDesc('created_at')->values(),
                'metric' => ['value' => $group->count() . '×', 'label' => 'tekrar kaydı'],
                'tags' => [$typeName, $areaName, $distinctDays->count() >= 3 ? 'Sürekli tekrar' : 'Yinelenen arıza'],
            ];
        }

        $areaVolume = $allFaults->filter(fn ($fault) => $fault->fault_area_id)->groupBy('fault_area_id');
        foreach ($areaVolume as $group) {
            if ($group->count() < 3) {
                continue;
            }

            $first = $group->first();
            $openCount = $group->whereNotIn('status', ['resolved', 'closed'])->count();
            $typeList = $group->groupBy('fault_type_id')
                ->map(fn ($typedGroup) => $typedGroup->first()?->faultType?->name ?? 'Belirsiz')
                ->join(', ');

            $insights[] = [
                'level' => $openCount >= 2 ? 'critical' : 'warning',
                'icon' => 'fa-fire',
                'title' => '"' . ($first?->faultLocation?->name ?? 'Belirtilmemiş konum') . ' – ' . ($first?->faultArea?->name ?? 'Alan') . '" yoğun arıza bölgesi',
                'body' => sprintf(
                    '%s içinde bu alanda <strong>%d</strong> arıza kaydı oluştu. Tür dağılımı: <em>%s</em>. Bunların <strong>%d</strong> adedi hâlâ açık durumda; bu da alanın yalnız tekil arızalar değil, kümelenen operasyonel baskı ürettiğini gösteriyor.',
                    ucfirst($periodPhrase),
                    $group->count(),
                    $typeList ?: 'Belirsiz',
                    $openCount
                ),
                'faults' => $group->sortByDesc('created_at')->values(),
                'metric' => ['value' => $group->count(), 'label' => 'alan kaydı'],
                'tags' => [$first?->faultArea?->name ?? 'Alan', 'Yoğunlaşma', 'Sıcak bölge'],
            ];
        }

        $slowResolved = $allFaults->filter(fn ($fault) => $fault->resolved_at && $fault->created_at->diffInMinutes($fault->resolved_at) / 60 > 6);
        if ($slowResolved->isNotEmpty()) {
            $worst = $slowResolved->sortByDesc(fn ($fault) => $fault->created_at->diffInMinutes($fault->resolved_at))->first();
            $averageHours = round($slowResolved->avg(fn ($fault) => $fault->created_at->diffInMinutes($fault->resolved_at) / 60), 1);
            $worstHours = round($worst->created_at->diffInMinutes($worst->resolved_at) / 60, 1);

            $insights[] = [
                'level' => 'warning',
                'icon' => 'fa-clock',
                'title' => 'Çözüm süresi uzayan işler dikkat çekiyor',
                'body' => sprintf(
                    '%s içinde <strong>%d</strong> kayıt 6 saatin üzerinde çözüm süresi gerektirdi. Ortalama çözüm süresi <strong>%.1f saat</strong>. En yavaş kapanan iş <strong>%s</strong> için <strong>%.1f saat</strong> sürdü; bu tablo, kapasite veya tedarik gecikmesi sinyali veriyor.',
                    ucfirst($periodPhrase),
                    $slowResolved->count(),
                    $averageHours,
                    $worst->faultArea?->name ?? ($worst->faultLocation?->name ?? 'belirtilmemiş alan'),
                    $worstHours
                ),
                'faults' => $slowResolved->sortByDesc('created_at')->values(),
                'metric' => ['value' => Fault::formatHours($averageHours), 'label' => 'ort. çözüm'],
                'tags' => ['SLA riski', 'Çözüm süresi'],
            ];
        }

        $staleFaults = $allFaults->filter(fn ($fault) => in_array($fault->status, ['open', 'in_progress', 'waiting_material', 'winter_plan'], true) && $fault->created_at->diffInHours($now) > 12);
        if ($staleFaults->isNotEmpty()) {
            $oldest = $staleFaults->sortBy('created_at')->first();
            $oldestAge = round($oldest->created_at->diffInMinutes($now) / 60, 1);

            $insights[] = [
                'level' => 'warning',
                'icon' => 'fa-hourglass-half',
                'title' => 'Beklemede kalan açık işler birikiyor',
                'body' => sprintf(
                    '%s içinde açılan kayıtlar arasında <strong>%d</strong> arıza 12 saatin üzerinde açık kaldı. En eski kayıt <strong>%s</strong> tarihinde açıldı ve yaklaşık <strong>%.1f saat</strong> boyunca çözümsüz kaldı.',
                    ucfirst($periodPhrase),
                    $staleFaults->count(),
                    $oldest->created_at->format('d.m.Y H:i'),
                    $oldestAge
                ),
                'faults' => $staleFaults->sortByDesc('created_at')->values(),
                'metric' => ['value' => $staleFaults->count(), 'label' => 'bekleyen'],
                'tags' => ['Açık iş', 'Gecikme'],
            ];
        }

        $criticalOpen = $allFaults->filter(fn ($fault) => $fault->priority === 'critical' && !in_array($fault->status, ['resolved', 'closed'], true));
        if ($criticalOpen->isNotEmpty()) {
            $insights[] = [
                'level' => 'critical',
                'icon' => 'fa-skull-crossbones',
                'title' => 'Kritik öncelikli açık arızalar kapanmadı',
                'body' => sprintf(
                    '%s içinde <strong>%d</strong> kritik öncelikli kayıt hâlâ açık. Bu kayıtların en yaşlısı <strong>%s</strong> önce açıldı. Operasyon açısından bu grup, normal kuyruktan ayrı ele alınmalı.',
                    ucfirst($periodPhrase),
                    $criticalOpen->count(),
                    Fault::formatHours($criticalOpen->max(fn ($fault) => $fault->created_at->diffInMinutes($now) / 60))
                ),
                'faults' => $criticalOpen->sortByDesc('created_at')->values(),
                'metric' => ['value' => $criticalOpen->count(), 'label' => 'kritik açık'],
                'tags' => ['Kritik', 'Acil müdahale'],
            ];
        }

        $slaBreaches = $allFaults->filter(fn ($fault) => $fault->resolved_at && $fault->faultType && ($fault->created_at->diffInMinutes($fault->resolved_at) / 60) > $fault->faultType->completion_hours);
        if ($slaBreaches->isNotEmpty()) {
            $averageExcess = round($slaBreaches->avg(fn ($fault) => ($fault->created_at->diffInMinutes($fault->resolved_at) / 60) - $fault->faultType->completion_hours), 1);
            $insights[] = [
                'level' => 'warning',
                'icon' => 'fa-shield-halved',
                'title' => 'SLA hedefinin üzerinde kapanan işler var',
                'body' => sprintf(
                    '%s içinde <strong>%d</strong> kayıt kendi SLA süresini aştı. Ortalama aşım <strong>%.1f saat</strong>. Bu bulgu, işin yalnız yavaş değil, beklenen hizmet seviyesinin altında kapatıldığını gösteriyor.',
                    ucfirst($periodPhrase),
                    $slaBreaches->count(),
                    $averageExcess
                ),
                'faults' => $slaBreaches->sortByDesc('created_at')->values(),
                'metric' => ['value' => $slaBreaches->count(), 'label' => 'SLA ihlali'],
                'tags' => ['SLA', 'Servis seviyesi'],
            ];
        }

        if ($meta['comparison_enabled']) {
            $todayCount = $todayFaults->count();
            $yesterdayCount = $yestFaults->count();
            $difference = $todayCount - $yesterdayCount;
            $todayClosed = $todayFaults->whereIn('status', ['resolved', 'closed'])->count();

            if ($todayCount > 0 || $yesterdayCount > 0) {
                $insights[] = [
                    'level' => $difference >= 3 ? 'warning' : ($difference <= -2 ? 'positive' : 'info'),
                    'icon' => 'fa-chart-simple',
                    'title' => $lastDayLabel . ' ile ' . $previousDayLabel . ' karşılaştırması',
                    'body' => sprintf(
                        '<strong>%s</strong> gününde <strong>%d</strong> yeni arıza açıldı, bunların <strong>%d</strong> adedi aynı gün kapatıldı. Bir önceki gün olan <strong>%s</strong> ile fark <strong>%+d</strong> kayıt. Bu değişim, son gün operasyon temposundaki yönü net biçimde gösteriyor.',
                        $lastDayLabel,
                        $todayCount,
                        $todayClosed,
                        $previousDayLabel,
                        $difference
                    ),
                    'faults' => $todayFaults->sortByDesc('created_at')->values(),
                    'metric' => ['value' => ($difference >= 0 ? '+' : '') . $difference, 'label' => 'günlük fark'],
                    'tags' => ['Günlük trend', $lastDayLabel],
                ];
            }
        }

        $fastToday = $todayFaults->filter(fn ($fault) => $fault->resolved_at && $fault->created_at->diffInHours($fault->resolved_at) <= 2);
        if ($fastToday->isNotEmpty()) {
            $averageMinutes = round($fastToday->avg(fn ($fault) => $fault->created_at->diffInMinutes($fault->resolved_at)), 0);
            $insights[] = [
                'level' => 'positive',
                'icon' => 'fa-bolt',
                'title' => $lastDayLabel . ' gününde hızlı kapanan işler var',
                'body' => sprintf(
                    '<strong>%s</strong> gününde açılan <strong>%d</strong> arıza en fazla 2 saat içinde kapatıldı. Ortalama çözüm süresi <strong>%d dakika</strong>. Bu veri, ekip hazır bulunuşluğunun güçlü olduğu bir aralığı işaret ediyor.',
                    $lastDayLabel,
                    $fastToday->count(),
                    $averageMinutes
                ),
                'faults' => $fastToday->sortByDesc('created_at')->values(),
                'metric' => ['value' => $fastToday->count(), 'label' => 'hızlı çözüm'],
                'tags' => ['Hızlı çözüm', 'Olumlu sinyal'],
            ];
        }

        $nightFaults = $allFaults->filter(fn ($fault) => $fault->created_at->hour >= 22 || $fault->created_at->hour < 6);
        if ($nightFaults->count() >= 2) {
            $insights[] = [
                'level' => 'info',
                'icon' => 'fa-moon',
                'title' => 'Gece saatlerinde dikkat çeken arıza hacmi',
                'body' => sprintf(
                    '%s içinde <strong>%d</strong> kayıt gece vardiyası saatlerinde (22:00–06:00) açıldı. Bu dağılım, vardiya planı ve nöbet kapsamının gerçek yükle ne kadar örtüştüğünü kontrol etmeyi gerektiriyor.',
                    ucfirst($periodPhrase),
                    $nightFaults->count()
                ),
                'faults' => $nightFaults->sortByDesc('created_at')->values(),
                'metric' => ['value' => $nightFaults->count(), 'label' => 'gece kaydı'],
                'tags' => ['Gece vardiyası', 'Saat etkisi'],
            ];
        }

        $locationGroups = $allFaults->filter(fn ($fault) => $fault->faultLocation)->groupBy('fault_location_id');
        if ($locationGroups->isNotEmpty()) {
            $hotLocation = $locationGroups->sortByDesc(fn ($group) => $group->count())->first();
            if ($hotLocation && $hotLocation->count() >= 2) {
                $first = $hotLocation->first();
                $insights[] = [
                    'level' => 'info',
                    'icon' => 'fa-location-dot',
                    'title' => ($first?->faultLocation?->name ?? 'Belirtilmemiş konum') . ' öne çıkan sorun lokasyonu',
                    'body' => sprintf(
                        '%s içinde bu lokasyonda <strong>%d</strong> kayıt oluştu. Açık kalan iş sayısı <strong>%d</strong>. Konumsal kümelenme, saha turu veya önleyici bakım planı için güçlü bir sinyal üretir.',
                        ucfirst($periodPhrase),
                        $hotLocation->count(),
                        $hotLocation->whereNotIn('status', ['resolved', 'closed'])->count()
                    ),
                    'faults' => $hotLocation->sortByDesc('created_at')->values(),
                    'metric' => ['value' => $hotLocation->count(), 'label' => 'lokasyon kaydı'],
                    'tags' => [$first?->faultLocation?->name ?? 'Konum', 'Lokasyon yoğunluğu'],
                ];
            }
        }

        if ($allFaults->isEmpty()) {
            $insights[] = [
                'level' => 'positive',
                'icon' => 'fa-circle-check',
                'title' => 'Seçilen tarih aralığında arıza kaydı bulunmuyor',
                'body' => 'Seçilen analiz penceresi boyunca sisteme herhangi bir teknik arıza kaydı düşmemiştir. Bu sonuç, dönem içinde raporlanmış olay olmadığını gösterir.',
                'faults' => null,
                'metric' => ['value' => '0', 'label' => 'arıza'],
                'tags' => ['Sessiz dönem', 'Kayıt yok'],
            ];
        }

        $order = ['critical' => 0, 'warning' => 1, 'info' => 2, 'positive' => 3];
        usort($insights, fn ($left, $right) => ($order[$left['level']] ?? 9) <=> ($order[$right['level']] ?? 9));

        return $insights;
    }

    private function buildAdaptiveAnalysisNarrative(Collection $allFaults, array $insights, Collection $todayFaults, Collection $yestFaults, array $meta): array
    {
        $insightCollection = collect($insights);
        $totalFaults = $allFaults->count();
        $criticalCount = $insightCollection->where('level', 'critical')->count();
        $warningCount = $insightCollection->where('level', 'warning')->count();
        $infoCount = $insightCollection->where('level', 'info')->count();
        $positiveCount = $insightCollection->where('level', 'positive')->count();

        $openFaults = $allFaults->filter(fn ($fault) => in_array($fault->status, ['open', 'in_progress', 'waiting_material', 'winter_plan'], true));
        $criticalOpen = $openFaults->where('priority', 'critical')->count();
        $highOpen = $openFaults->filter(fn ($fault) => in_array($fault->priority, ['high', 'critical'], true))->count();
        $unresolvedRate = $totalFaults > 0 ? (int) round(($openFaults->count() / $totalFaults) * 100) : 0;

        $riskScore = (int) min(100, max(0,
            ($criticalCount * 22)
            + ($warningCount * 11)
            + ($infoCount * 3)
            - ($positiveCount * 5)
            + (int) round($unresolvedRate * .35)
            + ($criticalOpen * 10)
            + ($highOpen * 4)
        ));

        if ($riskScore >= 75) {
            [$riskLabel, $riskLevel, $riskColor] = ['Kritik takip', 'critical', '#ef4444'];
        } elseif ($riskScore >= 50) {
            [$riskLabel, $riskLevel, $riskColor] = ['Yakın izleme', 'warning', '#f59e0b'];
        } elseif ($riskScore >= 25) {
            [$riskLabel, $riskLevel, $riskColor] = ['Kontrollü seyir', 'info', '#3b82f6'];
        } else {
            [$riskLabel, $riskLevel, $riskColor] = ['Stabil görünüm', 'positive', '#10b981'];
        }

        if ($meta['comparison_enabled']) {
            $trendDiff = $todayFaults->count() - $yestFaults->count();
            $trendLabel = $trendDiff > 0
                ? $meta['last_day_label'] . ' gününde bir önceki güne göre ' . $trendDiff . ' kayıt artışı var'
                : ($trendDiff < 0
                    ? $meta['last_day_label'] . ' gününde bir önceki güne göre ' . abs($trendDiff) . ' kayıt düşüşü var'
                    : 'Son gün ile bir önceki gün aynı hacimde ilerledi');
        } else {
            $trendLabel = 'Karşılaştırmalı trend için en az 2 günlük aralık seçin';
        }

        $focusItems = $insightCollection
            ->filter(fn ($insight) => in_array($insight['level'] ?? 'info', ['critical', 'warning'], true))
            ->take(3)
            ->map(fn ($insight) => [
                'title' => $insight['title'] ?? 'Operasyonel bulgu',
                'level' => $insight['level'] ?? 'info',
                'metric' => $insight['metric']['value'] ?? null,
                'why' => \Illuminate\Support\Str::limit(strip_tags($insight['body'] ?? ''), 155),
            ])
            ->values()
            ->all();

        if (empty($focusItems)) {
            $focusItems[] = [
                'title' => $positiveCount > 0 ? 'Genel tablo dengeli, baskın risk deseni görünmüyor' : 'Henüz belirgin risk kümesi yok',
                'level' => $positiveCount > 0 ? 'positive' : 'info',
                'metric' => $totalFaults,
                'why' => $totalFaults > 0
                    ? 'Seçilen aralıkta kritik tekrar, yoğun bekleme veya açık iş baskısı sınırlı görünüyor.'
                    : 'Seçilen aralıkta kayıt bulunmadığı için sistem yalnızca izleme seviyesi üretiyor.',
            ];
        }

        $locationGroups = $allFaults->filter(fn ($fault) => $fault->faultLocation)->groupBy('fault_location_id');
        $hotLocation = $locationGroups->isNotEmpty()
            ? $locationGroups->sortByDesc(fn ($group) => $group->count())->first()
            : null;

        $departmentGroups = $allFaults->filter(fn ($fault) => $fault->department)->groupBy('assigned_department_id');
        $topDepartment = $departmentGroups->isNotEmpty()
            ? $departmentGroups->sortByDesc(fn ($group) => $group->count())->first()
            : null;

        $slaBreaches = $allFaults->filter(fn ($fault) => $fault->resolved_at && $fault->faultType && ($fault->created_at->diffInMinutes($fault->resolved_at) / 60) > $fault->faultType->completion_hours);

        $signalItems = [
            [
                'label' => 'Açık iş oranı',
                'value' => '%' . $unresolvedRate,
                'detail' => $openFaults->count() . ' açık kayıt',
            ],
            [
                'label' => 'Kritik açık',
                'value' => (string) $criticalOpen,
                'detail' => $criticalOpen > 0 ? 'Acil öncelik bekliyor' : 'Kritik açık yok',
            ],
            [
                'label' => 'SLA aşımı',
                'value' => (string) $slaBreaches->count(),
                'detail' => $slaBreaches->isNotEmpty() ? 'Hizmet seviyesi baskısı oluştu' : 'Aşım görünmüyor',
            ],
        ];

        if ($hotLocation) {
            $signalItems[] = [
                'label' => 'En yoğun lokasyon',
                'value' => $hotLocation->first()?->faultLocation?->name ?? '-',
                'detail' => $hotLocation->count() . ' kayıt',
            ];
        }

        if ($topDepartment) {
            $signalItems[] = [
                'label' => 'En yüklü departman',
                'value' => $topDepartment->first()?->department?->name ?? '-',
                'detail' => $topDepartment->count() . ' kayıt',
            ];
        }

        $confidenceLabel = $totalFaults >= 20 ? 'Yüksek' : ($totalFaults >= 8 ? 'Orta' : 'Düşük');
        $confidenceText = $totalFaults >= 20
            ? 'Seçilen aralık yeterli hacim üretti; desenler güçlü sinyal veriyor.'
            : ($totalFaults >= 8
                ? 'Veri yoğunluğu orta seviyede; saha teyidiyle birlikte okunması ideal.'
                : 'Kayıt hacmi düşük; değerlendirme erken uyarı mantığıyla okunmalı.');

        $summary = sprintf(
            '%s içinde %d arıza kaydı ve %d analiz bulgusu işlendi. Genel risk seviyesi %s; %s. Açık iş oranı %d%% ve kritik açık sayısı %d.',
            ucfirst($meta['period_phrase']),
            $totalFaults,
            count($insights),
            mb_strtolower($riskLabel),
            $trendLabel,
            $unresolvedRate,
            $criticalOpen
        );

        return [
            'risk_score' => $riskScore,
            'risk_label' => $riskLabel,
            'risk_level' => $riskLevel,
            'risk_color' => $riskColor,
            'summary' => $summary,
            'focus_items' => $focusItems,
            'signal_items' => array_slice($signalItems, 0, 5),
            'confidence_label' => $confidenceLabel,
            'confidence_text' => $confidenceText,
            'trend_label' => $trendLabel,
            'open_count' => $openFaults->count(),
            'unresolved_rate' => $unresolvedRate,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'positive_count' => $positiveCount,
        ];
    }

    private function buildAnalysisNarrative($allFaults, array $insights, $todayFaults, $yestFaults, $now): array
    {
        $insightCollection = collect($insights);
        $totalFaults = $allFaults->count();
        $todayCount = $todayFaults->count();
        $yesterdayCount = $yestFaults->count();
        $trendDiff = $todayCount - $yesterdayCount;

        $criticalCount = $insightCollection->where('level', 'critical')->count();
        $warningCount = $insightCollection->where('level', 'warning')->count();
        $infoCount = $insightCollection->where('level', 'info')->count();
        $positiveCount = $insightCollection->where('level', 'positive')->count();

        $activeStatuses = ['open', 'in_progress', 'winter_plan', 'waiting_material'];
        $openFaults = $allFaults->filter(fn($fault) => in_array($fault->status, $activeStatuses, true));
        $criticalOpen = $openFaults->where('priority', 'critical')->count();
        $highOpen = $openFaults->filter(fn($fault) => in_array($fault->priority, ['high', 'critical'], true))->count();
        $unresolvedRate = $totalFaults > 0 ? (int) round(($openFaults->count() / $totalFaults) * 100) : 0;

        $riskScore = (int) min(100, max(0,
            ($criticalCount * 22)
            + ($warningCount * 11)
            + ($infoCount * 3)
            - ($positiveCount * 5)
            + (int) round($unresolvedRate * .35)
            + ($criticalOpen * 10)
            + ($highOpen * 4)
        ));

        if ($riskScore >= 75) {
            $riskLabel = 'Kritik takip';
            $riskLevel = 'critical';
            $riskColor = '#ef4444';
        } elseif ($riskScore >= 50) {
            $riskLabel = 'Yakın izleme';
            $riskLevel = 'warning';
            $riskColor = '#f59e0b';
        } elseif ($riskScore >= 25) {
            $riskLabel = 'Kontrollü seyir';
            $riskLevel = 'info';
            $riskColor = '#3b82f6';
        } else {
            $riskLabel = 'Stabil görünüm';
            $riskLevel = 'positive';
            $riskColor = '#10b981';
        }

        $trendLabel = $trendDiff > 0
            ? 'bugün düne göre ' . $trendDiff . ' kayıt arttı'
            : ($trendDiff < 0
                ? 'bugün düne göre ' . abs($trendDiff) . ' kayıt azaldı'
                : 'bugün ve dün aynı yoğunlukta');

        $focusItems = $insightCollection
            ->filter(fn($insight) => in_array($insight['level'] ?? 'info', ['critical', 'warning'], true))
            ->take(3)
            ->map(fn($insight) => [
                'title' => $insight['title'] ?? 'Operasyonel bulgu',
                'level' => $insight['level'] ?? 'info',
                'metric' => $insight['metric']['value'] ?? null,
                'why' => \Illuminate\Support\Str::limit(strip_tags($insight['body'] ?? ''), 155),
            ])
            ->values()
            ->all();

        if (empty($focusItems)) {
            $focusItems[] = [
                'title' => $positiveCount > 0 ? 'Genel tablo olumlu; kontrol ritmini koruyun' : 'Henüz belirgin bir risk örüntüsü yok',
                'level' => $positiveCount > 0 ? 'positive' : 'info',
                'metric' => $totalFaults,
                'why' => $totalFaults > 0
                    ? 'Son 3 günlük veride kritik tekrar veya acil müdahale sinyali düşük görünüyor.'
                    : 'Analiz penceresinde arıza kaydı bulunmadığı için sistem yalnızca izleme önerisi üretiyor.',
            ];
        }

        $actionPlan = [];
        if ($criticalCount > 0 || $criticalOpen > 0) {
            $actionPlan[] = 'İlk 30 dakika içinde kritik/açık kayıtları sorumlu departmanla eşleştirip net hedef saat belirleyin.';
        }
        if ($highOpen > 0) {
            $actionPlan[] = 'Acil ve kritik öncelikli açık işlerde vardiya devri yapılmadan önce sahiplik ve bekleyen malzeme durumunu kontrol edin.';
        }
        if ($insightCollection->contains(fn($insight) => str_contains(mb_strtolower(($insight['title'] ?? '') . ' ' . implode(' ', $insight['tags'] ?? [])), 'tekrar'))) {
            $actionPlan[] = 'Tekrarlayan alan/tür kombinasyonlarında geçici müdahale yerine kök neden ve kalıcı onarım kontrolü açın.';
        }
        if ($insightCollection->contains(fn($insight) => str_contains(mb_strtolower($insight['title'] ?? ''), 'sla') || str_contains(mb_strtolower($insight['body'] ?? ''), 'sla'))) {
            $actionPlan[] = 'SLA aşımı görünen işlerde gecikme nedenini departman, malzeme ve lokasyon kırılımıyla notlayın.';
        }
        if ($trendDiff > 0) {
            $actionPlan[] = 'Bugünkü artışın saat aralığı ve lokasyon yoğunluğunu vardiya sorumlusuyla birlikte teyit edin.';
        }

        $actionPlan[] = 'Gün sonunda açık kalan kayıtlar için "neden açık kaldı, sonraki adım ne, sorumlu kim?" bilgisini zorunlu kapatma notu gibi takip edin.';
        $actionPlan = array_slice(array_values(array_unique($actionPlan)), 0, 5);

        $confidenceLabel = $totalFaults >= 15 ? 'Yüksek' : ($totalFaults >= 5 ? 'Orta' : 'Düşük');
        $confidenceText = $totalFaults >= 15
            ? 'Analiz yeterli veri yoğunluğuyla çalışıyor; örüntü yorumları daha güvenilir.'
            : ($totalFaults >= 5
                ? 'Veri miktarı orta seviyede; bulgular yön gösterir, saha teyidi önerilir.'
                : 'Veri hacmi düşük; bu nedenle yorumlar erken uyarı niteliğinde okunmalıdır.');

        $summary = sprintf(
            'Son 3 günlük pencerede %d arıza kaydı ve %d analiz bulgusu değerlendirildi. Genel risk seviyesi %s olarak okunuyor; %s. Açık iş oranı %d%%, kritik açık kayıt sayısı %d.',
            $totalFaults,
            count($insights),
            mb_strtolower($riskLabel),
            $trendLabel,
            $unresolvedRate,
            $criticalOpen
        );

        return [
            'risk_score' => $riskScore,
            'risk_label' => $riskLabel,
            'risk_level' => $riskLevel,
            'risk_color' => $riskColor,
            'summary' => $summary,
            'focus_items' => $focusItems,
            'action_plan' => $actionPlan,
            'confidence_label' => $confidenceLabel,
            'confidence_text' => $confidenceText,
            'trend_label' => $trendLabel,
            'open_count' => $openFaults->count(),
            'unresolved_rate' => $unresolvedRate,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'positive_count' => $positiveCount,
        ];
    }

    private function buildAnalysisInsights($allFaults, $now, $todayStart, $yestStart, $windowStart): array
    {
        $todayFaults = $allFaults->filter(fn($f) => $f->created_at->gte($todayStart));
        $yestFaults  = $allFaults->filter(fn($f) =>
            $f->created_at->gte($yestStart) && $f->created_at->lt($todayStart)
        );
        $insights = [];

        // 1. Aynı alan + aynı tür: ardışık günlerde tekrar
        $areaTypeGroups = $allFaults
            ->filter(fn($f) => $f->fault_area_id && $f->fault_type_id)
            ->groupBy(fn($f) => $f->fault_area_id . '|' . $f->fault_type_id);
        foreach ($areaTypeGroups as $group) {
            $hasToday = $group->filter(fn($f) => $f->created_at->gte($todayStart))->count() > 0;
            $hasYest  = $group->filter(fn($f) =>
                $f->created_at->gte($yestStart) && $f->created_at->lt($todayStart)
            )->count() > 0;
            if ($hasToday && $hasYest) {
                $first    = $group->first();
                $areaName = $first->faultArea?->name    ?? '—';
                $locName  = $first->faultLocation?->name ?? '—';
                $typeName = $first->faultType?->name    ?? '—';
                $deptName = $first->department?->name   ?? '—';
                $has3Day  = $group->filter(fn($f) => $f->created_at->lt($yestStart))->count() > 0;
                $extra    = $has3Day
                    ? ' <strong>3 gün üst üste aynı sorun kayıt altına alınmaktadır.</strong>'
                    : '';
                $insights[] = [
                    'level'  => 'critical',
                    'icon'   => 'fa-triangle-exclamation',
                    'title'  => '"' . $locName . ' — ' . $areaName . '" Alanında Ardışık Günlerde ' . $typeName . ' Tekrarı',
                    'body'   => sprintf(
                        '<strong>%s</strong> konumundaki <strong>%s</strong> alanında <strong>%s</strong> arızası hem dün hem bugün kayıt altına alınmıştır.%s '
                        . '<strong>%s</strong> departmanı bu vakayı köklü onarım perspektifiyle ele almalıdır. '
                        . 'Bu alan–tür kombinasyonu son 3 günde toplam <strong>%d</strong> kez bildirilmiştir.',
                        $locName, $areaName, $typeName, $extra, $deptName, $group->count()
                    ),
                    'faults' => $group,
                    'metric' => ['value' => $group->count() . '×', 'label' => '3 günde tekrar'],
                    'tags'   => [$typeName, $locName, $has3Day ? '3 Gün Üst Üste' : 'Ardışık Gün'],
                ];
            }
        }
        // 2. Alan hacmi
        $areaVolume = $allFaults->filter(fn($f) => $f->fault_area_id)->groupBy('fault_area_id');
        foreach ($areaVolume as $group) {
            $first    = $group->first();
            $areaName = $first->faultArea?->name    ?? '—';
            $locName  = $first->faultLocation?->name ?? '—';
            if ($group->count() >= 3) {
                $openCnt  = $group->whereNotIn('status', ['resolved', 'closed'])->count();
                $typeList = $group->groupBy('fault_type_id')
                    ->map(fn($g) => $g->first()->faultType?->name ?? '—')->join(', ');
                $insights[] = [
                    'level'  => 'critical', 'icon' => 'fa-fire',
                    'title'  => '"' . $locName . ' — ' . $areaName . '" Son 3 Günün Arıza Odak Noktası (' . $group->count() . ' Vaka)',
                    'body'   => sprintf(
                        '<strong>%s</strong> konumundaki <strong>%s</strong> alanında son 3 günde <strong>%d</strong> arıza kayıt altına alındı. '
                        . 'Arıza türleri: <em>%s</em>. Hâlâ <strong>%d</strong> arıza açık konumdadır.',
                        $locName, $areaName, $group->count(), $typeList, $openCnt
                    ),
                    'faults' => $group,
                    'metric' => ['value' => $group->count(), 'label' => 'arıza / 3 gün'],
                    'tags'   => [$locName, $areaName, 'Yoğun Alan'],
                ];
            } elseif ($group->count() === 2 && $group->pluck('fault_type_id')->unique()->count() === 1) {
                $typeName = $first->faultType?->name ?? '—';
                $deptName = $first->department?->name ?? '—';
                $sorted   = $group->sortBy('created_at');
                $insights[] = [
                    'level'  => 'warning', 'icon' => 'fa-rotate',
                    'title'  => '"' . $areaName . '" Alanında ' . $typeName . ' İki Kez Tekrarlandı',
                    'body'   => sprintf(
                        '<strong>%s</strong> konumundaki <strong>%s</strong> alanında <strong>%s</strong> arızası 3 günlük dönemde iki kez meydana geldi. '
                        . 'İlk vaka <strong>%s</strong>, ikinci vaka <strong>%s</strong>. '
                        . '<strong>%s</strong> departmanı ilk müdahaleyi gözden geçirmelidir.',
                        $locName, $areaName, $typeName,
                        $sorted->first()->created_at->format('d.m.Y H:i'),
                        $sorted->last()->created_at->format('d.m.Y H:i'),
                        $deptName
                    ),
                    'faults' => $group,
                    'metric' => ['value' => '2×', 'label' => 'tekrar'],
                    'tags'   => [$typeName, 'Yinelenen Arıza'],
                ];
            }
        }
        // 3. Yavaş çözüm (6 sa+)
        $slowResolved = $allFaults->filter(fn($f) =>
            $f->resolved_at && $f->created_at->diffInMinutes($f->resolved_at) / 60 > 6
        );
        if ($slowResolved->count() > 0) {
            $worst    = $slowResolved->sortByDesc(fn($f) => $f->created_at->diffInMinutes($f->resolved_at))->first();
            $wH       = round($worst->created_at->diffInMinutes($worst->resolved_at) / 60, 1);
            $wA       = $worst->faultArea?->name ?? ($worst->faultLocation?->name ?? 'Belirtilmemiş');
            $avgH     = round($slowResolved->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at) / 60), 1);
            $insights[] = [
                'level'  => 'warning', 'icon' => 'fa-clock',
                'title'  => $slowResolved->count() . ' Arıza 6 Saatin Üzerinde Çözüm Süresiyle Kapatıldı',
                'body'   => sprintf(
                    'Son 3 günde <strong>%d</strong> vaka 6 saatin üzerinde çözüm gerektirdi. '
                    . 'Ortalama çözüm süresi <strong>%.1f saat</strong>. '
                    . 'En yavaş vaka: <strong>%s</strong> alanındaki <strong>%s</strong> — <strong>%.1f saat</strong>.',
                    $slowResolved->count(), $avgH, $wA, $worst->faultType?->name ?? '—', $wH
                ),
                'faults' => $slowResolved,
                'metric' => ['value' => Fault::formatHours($avgH), 'label' => 'ort. gecikme'],
                'tags'   => ['Çözüm Süresi', 'SLA Riski'],
            ];
        }
        // 4. Askıda (12 sa+)
        $staleFaults = $allFaults->filter(fn($f) =>
            in_array($f->status, ['open', 'in_progress']) && $f->created_at->diffInHours($now) > 12
        );
        if ($staleFaults->count() > 0) {
            $oldest = $staleFaults->sortBy('created_at')->first();
            $insights[] = [
                'level'  => 'warning', 'icon' => 'fa-hourglass-half',
                'title'  => $staleFaults->count() . ' Arıza 12 Saatin Üzerinde Çözüm Bekliyor',
                'body'   => sprintf(
                    'Son 3 günde <strong>%d</strong> arıza 12 saatten fazla bekliyor. '
                    . 'En eski: <strong>%s</strong> tarihinde %s alanında açılan <strong>%s</strong> — <strong>%.1f saat</strong> geçti.',
                    $staleFaults->count(), $oldest->created_at->format('d.m.Y H:i'),
                    $oldest->faultArea?->name ?? ($oldest->faultLocation?->name ?? '—'),
                    $oldest->faultType?->name ?? '—',
                    round($oldest->created_at->diffInHours($now), 1)
                ),
                'faults' => $staleFaults,
                'metric' => ['value' => $staleFaults->count(), 'label' => 'bekleyen'],
                'tags'   => ['Uzun Bekleme', 'Aksiyon Gerekli'],
            ];
        }
        // 5. Kritik açık
        $criticalOpen = $allFaults->filter(fn($f) =>
            $f->priority === 'critical' && !in_array($f->status, ['resolved', 'closed'])
        );
        if ($criticalOpen->count() > 0) {
            $minAge = $criticalOpen->min(fn($f) => $f->created_at->diffInMinutes($now) / 60);
            $maxAge = $criticalOpen->max(fn($f) => $f->created_at->diffInMinutes($now) / 60);
            $insights[] = [
                'level'  => 'critical', 'icon' => 'fa-skull-crossbones',
                'title'  => 'KRİTİK: ' . $criticalOpen->count() . ' Acil Öncelikli Arıza Hâlâ Kapatılmadı',
                'body'   => sprintf(
                    'Son 3 günde <strong>%d</strong> kritik öncelikli arıza çözüme kavuşturulamamıştır. '
                    . 'Bekleyen süre <strong>%s</strong> ile <strong>%s</strong> arasında değişmektedir. İvedi müdahale ve yönetim bilgilendirmesi gereklidir.',
                    $criticalOpen->count(), Fault::formatHours($minAge), Fault::formatHours($maxAge)
                ),
                'faults' => $criticalOpen,
                'metric' => ['value' => $criticalOpen->count(), 'label' => 'kritik açık'],
                'tags'   => ['KRİTİK', 'Acil Müdahale'],
            ];
        }
        // 6. SLA ihlali
        $slaBreaches = $allFaults->filter(fn($f) =>
            $f->resolved_at && $f->faultType
            && ($f->created_at->diffInMinutes($f->resolved_at) / 60) > $f->faultType->completion_hours
        );
        if ($slaBreaches->count() > 0) {
            $avgEx = round($slaBreaches->avg(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at) / 60 - $f->faultType->completion_hours
            ), 1);
            $wB = $slaBreaches->sortByDesc(fn($f) =>
                $f->created_at->diffInMinutes($f->resolved_at) / 60 - $f->faultType->completion_hours
            )->first();
            $wEx = round($wB->created_at->diffInMinutes($wB->resolved_at) / 60 - $wB->faultType->completion_hours, 1);
            $insights[] = [
                'level'  => 'warning', 'icon' => 'fa-shield-halved',
                'title'  => $slaBreaches->count() . ' Arızada SLA Hedefi Aşıldı — Ort. ' . $avgEx . ' Sa. Fazla',
                'body'   => sprintf(
                    'Son 3 günde <strong>%d</strong> arıza SLA hedefini aşmıştır. Ortalama aşım <strong>%.1f saat</strong>. '
                    . 'En büyük ihlal <strong>%s</strong> türünde — hedefin <strong>%.1f saat</strong> üzerinde.',
                    $slaBreaches->count(), $avgEx, $wB->faultType?->name ?? '—', $wEx
                ),
                'faults' => $slaBreaches,
                'metric' => ['value' => $slaBreaches->count(), 'label' => 'SLA ihlali'],
                'tags'   => ['SLA İhlali', 'Servis Kalitesi'],
            ];
        }
        // 7. Bugün yanıtsız acil
        $urgentStale = $todayFaults->filter(fn($f) =>
            in_array($f->priority, ['critical', 'high']) && $f->status === 'open'
            && $f->created_at->diffInHours($now) >= 2
        );
        if ($urgentStale->count() > 0)
            $insights[] = [
                'level'  => 'critical', 'icon' => 'fa-bell-slash',
                'title'  => 'Bugün Açılan ' . $urgentStale->count() . ' Yüksek/Kritik Arıza 2+ Saattir Yanıtsız',
                'body'   => sprintf(
                    'Bugün açılan <strong>%d</strong> yüksek veya kritik öncelikli arıza, bildirimi takiben 2+ saat yanıtsız kalmaktadır. '
                    . 'İlgili departman yöneticilerine ivedilikle iletilmelidir.',
                    $urgentStale->count()
                ),
                'faults' => $urgentStale,
                'metric' => ['value' => $urgentStale->count(), 'label' => 'yanıtsız acil'],
                'tags'   => ['Acil', 'Yanıtsız', 'Bugün'],
            ];
        // 8. Departman yük
        if ($allFaults->count() >= 3) {
            $deptLoad = $allFaults->groupBy('assigned_department_id')
                ->map(fn($g) => [
                    'dept'       => $g->first()->department,
                    'count'      => $g->count(),
                    'open'       => $g->whereNotIn('status', ['resolved', 'closed'])->count(),
                    'todayCount' => $g->filter(fn($f) => $f->created_at->gte($todayStart))->count(),
                ])->sortByDesc('count');
            $topDept = $deptLoad->first();
            if ($topDept && $deptLoad->count() > 1) {
                $secondDept = $deptLoad->skip(1)->first();
                $loadRatio  = $allFaults->count() > 0 ? round($topDept['count'] / $allFaults->count() * 100) : 0;
                $insights[] = [
                    'level'  => 'info', 'icon' => 'fa-sitemap',
                    'title'  => ($topDept['dept']?->name ?? '—') . ' Departmanı En Yüksek Arıza Yükünü Taşıyor (%' . $loadRatio . ')',
                    'body'   => sprintf(
                        'Toplam arızanın <strong>%%%d</strong>\'i (<strong>%d arıza</strong>) <strong>%s</strong> departmanına yönlendirildi. '
                        . 'Bu departmanda <strong>%d</strong> açık arıza mevcut; bugün <strong>%d</strong> yeni arıza geldi. '
                        . 'İkinci sırada <strong>%s</strong> (%d arıza).',
                        $loadRatio, $topDept['count'], $topDept['dept']?->name ?? '—',
                        $topDept['open'], $topDept['todayCount'],
                        $secondDept['dept']?->name ?? '—', $secondDept['count']
                    ),
                    'faults' => null,
                    'metric' => ['value' => '%' . $loadRatio, 'label' => 'dept payı'],
                    'tags'   => ['Departman Yükü', $topDept['dept']?->name ?? '—'],
                ];
            }
        }
        // 9. Pik saat
        if ($allFaults->count() >= 3) {
            $hourDist  = $allFaults->groupBy(fn($f) => (int) $f->created_at->format('H'))
                ->map(fn($g) => $g->count())->sortByDesc(fn($v) => $v);
            $peakHour  = $hourDist->keys()->first();
            $peakCount = $hourDist->first();
            if ($peakCount >= 2)
                $insights[] = [
                    'level'  => 'info', 'icon' => 'fa-clock-rotate-left',
                    'title'  => sprintf('Arıza Bildirimlerinde Pik Saat: %02d:00–%02d:00', $peakHour, ($peakHour + 1) % 24),
                    'body'   => sprintf(
                        '<strong>%02d:00–%02d:00</strong> saatleri arasında <strong>%d</strong> arıza kaydı oluşturulmuştur; '
                        . 'bu dilimde ekip hazırlık düzeyi artırılmalıdır.',
                        $peakHour, ($peakHour + 1) % 24, $peakCount
                    ),
                    'faults' => null,
                    'metric' => ['value' => sprintf('%02d:00', $peakHour), 'label' => 'pik saat'],
                    'tags'   => ['Zaman Analizi', sprintf('%02d:00 Pik', $peakHour)],
                ];
        }
        // 10. Gece arıza
        $nightFaults = $allFaults->filter(fn($f) => $f->created_at->hour >= 22 || $f->created_at->hour < 6);
        if ($nightFaults->count() >= 2)
            $insights[] = [
                'level'  => 'info', 'icon' => 'fa-moon',
                'title'  => 'Gece Saatlerinde (22:00–06:00) ' . $nightFaults->count() . ' Arıza Bildirimi Alındı',
                'body'   => sprintf(
                    'Son 3 günde <strong>%d</strong> arıza gece saatlerinde iletilmiştir. '
                    . 'Gece nöbet çizelgesi ve teknik personel kapasitesi gözden geçirilmelidir.',
                    $nightFaults->count()
                ),
                'faults' => $nightFaults,
                'metric' => ['value' => $nightFaults->count(), 'label' => 'gece arızası'],
                'tags'   => ['Gece Vardiyası', 'Nöbet Analizi'],
            ];
        // 11. Sıcak konum
        $locGroups = $allFaults->groupBy('fault_location_id')
            ->map(fn($g) => [
                'name'     => $g->first()->faultLocation?->name ?? 'Belirtilmemiş',
                'count'    => $g->count(),
                'open'     => $g->whereNotIn('status', ['resolved', 'closed'])->count(),
                'typeList' => $g->groupBy('fault_type_id')
                    ->map(fn($tg) => $tg->first()->faultType?->name ?? '—')->join(', '),
            ])->sortByDesc('count');
        $hotLoc = $locGroups->first();
        if ($hotLoc && $hotLoc['count'] >= 2 && $locGroups->count() > 1)
            $insights[] = [
                'level'  => 'warning', 'icon' => 'fa-location-dot',
                'title'  => '"' . $hotLoc['name'] . '" Son 3 Günün En Sorunlu Konumu (' . $hotLoc['count'] . ' Arıza)',
                'body'   => sprintf(
                    '<strong>%s</strong> bölgesi son 3 günde <strong>%d</strong> arıza kaydıyla öne çıktı. '
                    . 'Türler: <em>%s</em>. Hâlâ <strong>%d</strong> açık arıza mevcut.',
                    $hotLoc['name'], $hotLoc['count'], $hotLoc['typeList'], $hotLoc['open']
                ),
                'faults' => null,
                'metric' => ['value' => $hotLoc['count'], 'label' => 'konum arızası'],
                'tags'   => [$hotLoc['name'], 'Sıcak Nokta'],
            ];
        // 12. Günlük karşılaştırma
        $todayCnt    = $todayFaults->count();
        $yestCnt     = $yestFaults->count();
        $todayClosed = $todayFaults->filter(fn($f) => in_array($f->status, ['resolved', 'closed']))->count();
        if ($todayCnt > 0 || $yestCnt > 0) {
            $diff  = $todayCnt - $yestCnt;
            $level = match (true) { $diff >= 4 => 'warning', $diff <= -3 => 'positive', default => 'info' };
            $trendMsg = match (true) {
                $diff > 0  => sprintf('Dünle kıyaslandığında <strong>%d</strong> arıza daha fazla bildirildi.', $diff),
                $diff < 0  => sprintf('Dünle kıyaslandığında <strong>%d</strong> arıza daha az bildirildi.', abs($diff)),
                default    => 'Bugünkü arıza sayısı dünkü ile aynı seviyede.',
            };
            $insights[] = [
                'level'  => $level, 'icon' => 'fa-chart-simple',
                'title'  => 'Günlük Karşılaştırma — Bugün: ' . $todayCnt . ' | Dün: ' . $yestCnt,
                'body'   => sprintf(
                    'Bugün <strong>%d</strong> arıza açıldı, <strong>%d</strong> adedi aynı gün kapatıldı. %s '
                    . 'Kritik: <strong>%d</strong>, Yüksek: <strong>%d</strong>.',
                    $todayCnt, $todayClosed, $trendMsg,
                    $todayFaults->where('priority', 'critical')->count(),
                    $todayFaults->where('priority', 'high')->count()
                ),
                'faults' => null,
                'metric' => ['value' => ($diff >= 0 ? '+' : '') . $diff, 'label' => 'dünden fark'],
                'tags'   => ['Günlük Özet', 'Trend'],
            ];
        }
        // 13. Hızlı çözüm (2 sa-)
        $fastToday = $todayFaults->filter(fn($f) =>
            $f->resolved_at && $f->created_at->diffInHours($f->resolved_at) <= 2
        );
        if ($fastToday->count() > 0) {
            $fastAvg = round($fastToday->avg(fn($f) => $f->created_at->diffInMinutes($f->resolved_at)), 0);
            $insights[] = [
                'level'  => 'positive', 'icon' => 'fa-bolt',
                'title'  => 'Bugün ' . $fastToday->count() . ' Arıza 2 Saat İçinde Çözüme Kavuşturuldu',
                'body'   => sprintf(
                    'Bugün açılan <strong>%d</strong> arıza en fazla 2 saat içinde kapatıldı. '
                    . 'Ortalama çözüm süresi <strong>%d dakika</strong>. Yüksek operasyonel hazırlık.',
                    $fastToday->count(), $fastAvg
                ),
                'faults' => $fastToday,
                'metric' => ['value' => $fastToday->count(), 'label' => 'hızlı çözüm'],
                'tags'   => ['Başarılı', 'Hızlı Yanıt', 'Olumlu'],
            ];
        }
        // 14. Sessiz gün
        if ($todayFaults->count() === 0 && $allFaults->count() > 0)
            $insights[] = [
                'level'  => 'positive', 'icon' => 'fa-circle-check',
                'title'  => 'Bugün Henüz Hiç Arıza Bildirimi Alınmadı',
                'body'   => 'Bugün sisteme arıza kaydı iletilmemiştir. Teknik altyapı ve ekipman bakımının etkili sürdürüldüğünün göstergesidir.',
                'faults' => null,
                'metric' => ['value' => '0', 'label' => 'bugün arıza'],
                'tags'   => ['Sessiz Gün', 'Olumlu'],
            ];
        // 15. Tamamen boş
        if ($allFaults->count() === 0)
            $insights[] = [
                'level'  => 'positive', 'icon' => 'fa-circle-check',
                'title'  => 'Son 3 Günde Herhangi Bir Arıza Kaydı Bulunmamaktadır',
                'body'   => 'Analiz edilen 3 günlük dönem boyunca hiçbir arıza bildirimi sisteme iletilmemiştir.',
                'faults' => null,
                'metric' => ['value' => '0', 'label' => '3 günde arıza'],
                'tags'   => ['Mükemmel', 'Sorunsuz'],
            ];

        $order = ['critical' => 0, 'warning' => 1, 'info' => 2, 'positive' => 3];
        usort($insights, fn($a, $b) => ($order[$a['level']] ?? 9) <=> ($order[$b['level']] ?? 9));
        return $insights;
    }
}
