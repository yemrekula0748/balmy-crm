<?php

namespace App\Http\Controllers\Modules;

use App\Models\AuditNonconformity;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class AuditNonconformityController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'audit_nonconformities',
            ['index'],
            ['show'],
            [],
            [],
            []
        );
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AuditNonconformity::with(['audit.auditType', 'branch', 'department', 'resolver'])
            ->orderBy('created_at', 'desc');

        // Sube bazli erisim: yoneticiler kendi subesini, departmanli kullanicilar kendi departmanini gorur.
        if (!$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);

            if ($user->department_id && !$user->isBranchManager()) {
                $query->where('department_id', $user->department_id);
            }
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nonconformities = $query->paginate(20)->withQueryString();

        $branchIds = $user->visibleBranchIds();
        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $departments = Department::whereIn('branch_id', $branchIds)->orderBy('name')->get();

        $page_title = 'Uygunsuzluklarim';

        return view('modules.audit.nonconformities', compact(
            'nonconformities',
            'branches',
            'departments',
            'page_title'
        ));
    }

    public function show(AuditNonconformity $nonconformity)
    {
        $user = auth()->user();

        $nonconformity->load(['audit.auditType', 'audit.auditor', 'branch', 'department', 'resolver']);
        $this->ensureNonconformityVisibility($user, $nonconformity);

        $page_title = 'Uygunsuzluk Detayi #' . $nonconformity->id;

        return view('modules.audit.nonconformity-show', compact('nonconformity', 'page_title'));
    }

    public function resolve(Request $request, AuditNonconformity $nonconformity)
    {
        $user = auth()->user();

        $this->ensureNonconformityVisibility($user, $nonconformity);
        abort_unless($this->canResolveNonconformity($user), 403, 'Bu islem icin yetkiniz bulunmamaktadir.');

        if ($nonconformity->status !== 'resolved') {
            $nonconformity->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $user->id,
            ]);
        }

        $nonconformity->loadMissing('audit');
        $nonconformity->audit?->syncStatusFromNonconformities();

        return back()->with('success', 'Uygunsuzluk cozuldu olarak isaretlendi.');
    }

    private function ensureNonconformityVisibility(User $user, AuditNonconformity $nonconformity): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        abort_if($user->branch_id !== $nonconformity->branch_id, 403);

        if ($user->department_id && !$user->isBranchManager()) {
            abort_if($user->department_id !== $nonconformity->department_id, 403);
        }
    }

    private function canResolveNonconformity(User $user): bool
    {
        return $user->hasPermission('audit_nonconformities', 'edit')
            || $user->hasPermission('audits', 'show');
    }
}
