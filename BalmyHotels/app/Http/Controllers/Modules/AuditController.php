<?php

namespace App\Http\Controllers\Modules;

use App\Models\Audit;
use App\Models\AuditNonconformity;
use App\Models\AuditType;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'audits',
            ['index', 'ajaxDepartments'],
            ['show'],
            ['create', 'store'],
            [],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $query = Audit::with(['branch', 'department', 'auditType', 'auditor', 'nonconformities'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('audit_type_id')) $query->where('audit_type_id', $request->audit_type_id);
        if ($request->filled('status'))        $query->where('status', $request->status);

        $audits      = $query->paginate(20)->withQueryString();
        $branches    = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $auditTypes  = AuditType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        $base = Audit::query();
        if ($request->filled('branch_id')) $base->where('branch_id', $request->branch_id);

        $totalAudits          = (clone $base)->count();
        $totalNonconformities = AuditNonconformity::count();
        $openNonconformities  = AuditNonconformity::where('status', 'open')->count();

        $page_title = 'İç Denetimler';

        return view('modules.audit.index', compact(
            'audits', 'branches', 'departments', 'auditTypes',
            'totalAudits', 'totalNonconformities', 'openNonconformities',
            'page_title'
        ));
    }

    public function create()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $autoBranchId = count($branchIds) === 1 ? $branchIds[0] : null;
        $branches     = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments  = $autoBranchId
            ? Department::where('branch_id', $autoBranchId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $auditTypes   = AuditType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        $page_title = 'Yeni Denetim Oluştur';

        return view('modules.audit.create', compact(
            'branches', 'departments', 'auditTypes', 'autoBranchId', 'page_title'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'       => 'required|exists:branches,id',
            'department_id'   => 'required|exists:departments,id',
            'audit_type_id'   => 'required|exists:audit_types,id',
            'notes'           => 'nullable|string|max:2000',
            'items'           => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:1000',
            'items.*.photo'       => 'nullable|image|max:4096',
        ]);

        $user = auth()->user();
        abort_if(!in_array($request->branch_id, $user->visibleBranchIds()), 403);

        DB::transaction(function () use ($request, $user) {
            $audit = Audit::create([
                'branch_id'     => $request->branch_id,
                'department_id' => $request->department_id,
                'audit_type_id' => $request->audit_type_id,
                'audited_by'    => $user->id,
                'notes'         => $request->notes,
                'status'        => 'open',
            ]);

            foreach ($request->input('items', []) as $index => $item) {
                if (empty(trim($item['description'] ?? ''))) continue;

                $photoPath = null;
                if ($request->hasFile("items.{$index}.photo")) {
                    $photoPath = $request->file("items.{$index}.photo")->store('audit_photos', 'public');
                }

                AuditNonconformity::create([
                    'audit_id'      => $audit->id,
                    'branch_id'     => $request->branch_id,
                    'department_id' => $request->department_id,
                    'description'   => $item['description'],
                    'photo_path'    => $photoPath,
                    'status'        => 'open',
                ]);
            }
        });

        return redirect()->route('audit.index')->with('success', 'Denetim kaydı başarıyla oluşturuldu.');
    }

    public function show(Audit $audit)
    {
        $user = auth()->user();
        abort_if(!in_array($audit->branch_id, $user->visibleBranchIds()), 403);

        $audit->load(['branch', 'department', 'auditType', 'auditor', 'nonconformities.resolver']);

        $page_title = 'Denetim Detayı #' . $audit->id;

        return view('modules.audit.show', compact('audit', 'page_title'));
    }

    public function destroy(Audit $audit)
    {
        $user = auth()->user();
        abort_if(!in_array($audit->branch_id, $user->visibleBranchIds()), 403);

        foreach ($audit->nonconformities as $nc) {
            if ($nc->photo_path) {
                Storage::disk('public')->delete($nc->photo_path);
            }
        }

        $audit->delete();

        return redirect()->route('audit.index')->with('success', 'Denetim kaydı silindi.');
    }

    public function ajaxDepartments(Request $request)
    {
        $departments = Department::where('branch_id', $request->branch_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($departments);
    }
}
