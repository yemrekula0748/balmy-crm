<?php

namespace App\Http\Controllers\Modules;

use App\Models\AuditType;
use Illuminate\Http\Request;

class AuditTypeController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'audit_types',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $types      = AuditType::orderBy('sort_order')->orderBy('name')->get();
        $page_title = 'Denetim Tipleri';

        return view('modules.audit.types.index', compact('types', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:191|unique:audit_types,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        AuditType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->input('sort_order', 0),
        ]);

        return back()->with('success', 'Denetim tipi eklendi.');
    }

    public function update(Request $request, AuditType $auditType)
    {
        $request->validate([
            'name'        => 'required|string|max:191|unique:audit_types,name,' . $auditType->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $auditType->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->input('sort_order', 0),
        ]);

        return back()->with('success', 'Denetim tipi güncellendi.');
    }

    public function destroy(AuditType $auditType)
    {
        if ($auditType->audits()->exists()) {
            return back()->with('error', 'Bu denetim tipine bağlı denetimler olduğu için silinemiyor.');
        }

        $auditType->delete();

        return back()->with('success', 'Denetim tipi silindi.');
    }
}
