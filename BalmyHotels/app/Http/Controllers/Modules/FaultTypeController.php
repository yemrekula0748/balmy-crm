<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FaultType;
use Illuminate\Http\Request;

class FaultTypeController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'fault_types',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index()
    {
        $types      = FaultType::with('branch')->orderBy('name')->paginate(30);
        $page_title = 'Arıza Türleri';
        return view('modules.faults.types.index', compact('types', 'page_title'));
    }

    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Yeni Arıza Türü';
        return view('modules.faults.types.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'completion_hours' => 'required|integer|min:1|max:720',
            'branch_id'        => 'nullable|exists:branches,id',
        ]);
        FaultType::create([
            'name'             => $request->name,
            'completion_hours' => $request->completion_hours,
            'branch_id'        => $request->branch_id ?: null,
            'is_active'        => true,
        ]);
        return redirect()->route('faults.types.index')->with('success', 'Arıza türü eklendi.');
    }

    public function edit(FaultType $type)
    {
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Arıza Türü Düzenle';
        return view('modules.faults.types.edit', compact('type', 'branches', 'page_title'));
    }

    public function update(Request $request, FaultType $type)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'completion_hours' => 'required|integer|min:1|max:720',
            'branch_id'        => 'nullable|exists:branches,id',
        ]);
        $type->update([
            'name'             => $request->name,
            'completion_hours' => $request->completion_hours,
            'branch_id'        => $request->branch_id ?: null,
            'is_active'        => $request->boolean('is_active', true),
        ]);
        return redirect()->route('faults.types.index')->with('success', 'Arıza türü güncellendi.');
    }

    public function destroy(FaultType $type)
    {
        $type->delete();
        return back()->with('success', 'Arıza türü silindi.');
    }
}
