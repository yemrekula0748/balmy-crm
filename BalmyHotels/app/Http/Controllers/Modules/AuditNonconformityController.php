<?php

namespace App\Http\Controllers\Modules;

use App\Models\AuditNonconformity;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;

class AuditNonconformityController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'audit_nonconformities',
            ['index'],
            [],
            [],
            ['resolve'],
            []
        );
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AuditNonconformity::with(['audit.auditType', 'branch', 'department', 'resolver'])
            ->orderBy('created_at', 'desc');

        // Şube bazlı erişim: yöneticiler kendi şubesini, departmanlı kullanıcılar kendi departmanını görür
        if (!$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);
            if ($user->department_id && !$user->isBranchManager()) {
                $query->where('department_id', $user->department_id);
            }
        }

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('status'))        $query->where('status', $request->status);

        $nonconformities = $query->paginate(20)->withQueryString();

        $branchIds   = $user->visibleBranchIds();
        $branches    = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::whereIn('branch_id', $branchIds)->orderBy('name')->get();

        $page_title = 'Uygunsuzluklarım';

        return view('modules.audit.nonconformities', compact(
            'nonconformities', 'branches', 'departments', 'page_title'
        ));
    }

    public function resolve(Request $request, AuditNonconformity $nonconformity)
    {
        $user = auth()->user();

        // Erişim kontrolü: kendi şubesinden olmalı
        abort_if(!$user->isSuperAdmin() && $user->branch_id !== $nonconformity->branch_id, 403);

        $nonconformity->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);

        return back()->with('success', 'Uygunsuzluk çözüldü olarak işaretlendi.');
    }
}
