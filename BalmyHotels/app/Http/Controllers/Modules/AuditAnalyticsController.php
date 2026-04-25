<?php

namespace App\Http\Controllers\Modules;

use App\Models\Audit;
use App\Models\AuditNonconformity;
use App\Models\AuditType;
use App\Models\Branch;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditAnalyticsController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'audit_analytics',
            ['index', 'pdf'],
            [],
            [],
            [],
            []
        );
    }

    public function index(Request $request)
    {
        // Analytics sayfası tüm şubeleri görür — yetki filtresi permissiona bırakılır
        $branchIds = Branch::orderBy('name')->pluck('id');

        $query = Audit::with(['branch', 'department', 'auditType', 'nonconformities']);

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('audit_type_id')) $query->where('audit_type_id', $request->audit_type_id);
        if ($request->filled('date_from'))     $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('created_at', '<=', $request->date_to);

        $allAudits = $query->orderBy('created_at', 'desc')->get();

        // Özet
        $totalAudits          = $allAudits->count();
        $totalNonconformities = $allAudits->sum(fn($a) => $a->nonconformities->count());
        $openNonconformities  = $allAudits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count());
        $resolvedNonconformities = $allAudits->sum(fn($a) => $a->nonconformities->where('status', 'resolved')->count());

        // Denetim tipi bazında
        $byType = $allAudits->groupBy('audit_type_id')->map(function ($audits) {
            return [
                'type_name'       => $audits->first()->auditType?->name ?? '—',
                'audit_count'     => $audits->count(),
                'nc_count'        => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'         => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('audit_count')->values();

        // Şube bazında
        $byBranch = $allAudits->groupBy('branch_id')->map(function ($audits) {
            return [
                'branch_name'  => $audits->first()->branch?->name ?? '—',
                'audit_count'  => $audits->count(),
                'nc_count'     => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'      => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('audit_count')->values();

        // Departman bazında
        $byDepartment = $allAudits->groupBy('department_id')->map(function ($audits) {
            return [
                'dept_name'   => $audits->first()->department?->name ?? '—',
                'dept_color'  => $audits->first()->department?->color ?? '#c19b77',
                'branch_name' => $audits->first()->branch?->name ?? '—',
                'audit_count' => $audits->count(),
                'nc_count'    => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'     => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('nc_count')->values();

        // Aylık trend (son 6 ay)
        $monthlyTrend = $allAudits
            ->groupBy(fn($a) => $a->created_at->format('Y-m'))
            ->map(fn($g, $m) => [
                'month'    => $m,
                'audits'   => $g->count(),
                'nc_total' => $g->sum(fn($a) => $a->nonconformities->count()),
            ])
            ->sortKeys()
            ->values();

        $branches    = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $auditTypes  = AuditType::orderBy('sort_order')->orderBy('name')->get();

        $page_title = 'Denetim Analiz & İstatistik';

        return view('modules.audit.analytics', compact(
            'allAudits', 'branches', 'departments', 'auditTypes',
            'totalAudits', 'totalNonconformities', 'openNonconformities', 'resolvedNonconformities',
            'byType', 'byBranch', 'byDepartment', 'monthlyTrend',
            'page_title'
        ));
    }

    public function pdf(Request $request)
    {
        $query = Audit::with(['branch', 'department', 'auditType', 'nonconformities.department']);

        if ($request->filled('branch_id'))     $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('audit_type_id')) $query->where('audit_type_id', $request->audit_type_id);
        if ($request->filled('date_from'))     $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('created_at', '<=', $request->date_to);

        $allAudits = $query->orderBy('created_at', 'desc')->get();

        $totalAudits             = $allAudits->count();
        $totalNonconformities    = $allAudits->sum(fn($a) => $a->nonconformities->count());
        $openNonconformities     = $allAudits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count());
        $resolvedNonconformities = $allAudits->sum(fn($a) => $a->nonconformities->where('status', 'resolved')->count());

        $byType = $allAudits->groupBy('audit_type_id')->map(function ($audits) {
            return [
                'type_name'   => $audits->first()->auditType?->name ?? '—',
                'audit_count' => $audits->count(),
                'nc_count'    => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'     => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('audit_count')->values();

        $byBranch = $allAudits->groupBy('branch_id')->map(function ($audits) {
            return [
                'branch_name'  => $audits->first()->branch?->name ?? '—',
                'audit_count'  => $audits->count(),
                'nc_count'     => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'      => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('audit_count')->values();

        $byDepartment = $allAudits->groupBy('department_id')->map(function ($audits) {
            return [
                'dept_name'   => $audits->first()->department?->name ?? '—',
                'branch_name' => $audits->first()->branch?->name ?? '—',
                'audit_count' => $audits->count(),
                'nc_count'    => $audits->sum(fn($a) => $a->nonconformities->count()),
                'open_nc'     => $audits->sum(fn($a) => $a->nonconformities->where('status', 'open')->count()),
            ];
        })->sortByDesc('nc_count')->values();

        $filters = [
            'branch'     => $request->filled('branch_id')     ? Branch::find($request->branch_id)?->name : null,
            'department' => $request->filled('department_id') ? Department::find($request->department_id)?->name : null,
            'type'       => $request->filled('audit_type_id') ? AuditType::find($request->audit_type_id)?->name : null,
            'date_from'  => $request->date_from,
            'date_to'    => $request->date_to,
        ];

        $generatedAt = now()->format('d.m.Y H:i');

        $fontCache = storage_path('fonts');
        if (!is_dir($fontCache)) mkdir($fontCache, 0755, true);

        $pdf = Pdf::loadView('modules.audit.analytics_pdf', compact(
            'allAudits', 'totalAudits', 'totalNonconformities',
            'openNonconformities', 'resolvedNonconformities',
            'byType', 'byBranch', 'byDepartment',
            'filters', 'generatedAt'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption([
            'defaultFont'          => 'dejavu sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'fontCache'            => $fontCache,
        ]);

        $filename = 'ic-denetim-analiz-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
