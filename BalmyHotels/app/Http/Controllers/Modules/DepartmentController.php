<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'departments',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $query = Department::with('branch')->orderBy('name');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->paginate(20)->withQueryString();
        $branches    = Branch::orderBy('name')->get();
        $page_title  = 'Departmanlar';

        return view('modules.departments.index', compact('departments', 'branches', 'page_title'));
    }

    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Yeni Departman';

        return view('modules.departments.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'color'     => 'nullable|string|max:20',
        ]);

        Department::create([
            'name'             => $request->name,
            'branch_id'        => $request->branch_id,
            'color'            => $request->color ?? '#c19b77',
            'is_active'        => true,
            'fault_assignable' => $request->boolean('fault_assignable'),
        ]);

        return redirect()->route('departments.index')->with('success', 'Departman başarıyla oluşturuldu.');
    }

    public function edit(Department $department)
    {
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Departman Düzenle';

        return view('modules.departments.edit', compact('department', 'branches', 'page_title'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'color'     => 'nullable|string|max:20',
        ]);

        $department->update([
            'name'             => $request->name,
            'branch_id'        => $request->branch_id,
            'color'            => $request->color ?? '#c19b77',
            'is_active'        => $request->boolean('is_active'),
            'fault_assignable' => $request->boolean('fault_assignable'),
        ]);

        return redirect()->route('departments.index')->with('success', 'Departman başarıyla güncellendi.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Bu departmana bağlı çalışanlar var, silinemez.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Departman silindi.');
    }
}
