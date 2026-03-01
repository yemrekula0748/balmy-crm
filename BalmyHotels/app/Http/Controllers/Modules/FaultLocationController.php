<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FaultArea;
use App\Models\FaultLocation;
use Illuminate\Http\Request;

class FaultLocationController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'fault_locations',
            ['index'],
            [],
            ['create', 'store', 'storeArea'],
            ['edit', 'update'],
            ['destroy', 'destroyArea']
        );
    }


    public function index(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $query = FaultLocation::with(['branch', 'areas'])
            ->whereIn('branch_id', $branchIds)->orderBy('name');

        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);

        $locations  = $query->paginate(30)->withQueryString();
        $branches   = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Arıza Konumları';

        return view('modules.faults.locations.index', compact('locations', 'branches', 'page_title'));
    }

    public function create()
    {
        $branches   = Branch::whereIn('id', auth()->user()->visibleBranchIds())->orderBy('name')->get();
        $page_title = 'Yeni Konum';
        return view('modules.faults.locations.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:255',
        ]);
        FaultLocation::create(['branch_id' => $request->branch_id, 'name' => $request->name, 'is_active' => true]);
        return redirect()->route('faults.locations.index')->with('success', 'Konum eklendi.');
    }

    public function edit(FaultLocation $location)
    {
        $branches   = Branch::whereIn('id', auth()->user()->visibleBranchIds())->orderBy('name')->get();
        $page_title = 'Konum Düzenle';
        return view('modules.faults.locations.edit', compact('location', 'branches', 'page_title'));
    }

    public function update(Request $request, FaultLocation $location)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $location->update(['name' => $request->name, 'is_active' => $request->boolean('is_active', true)]);
        return redirect()->route('faults.locations.index')->with('success', 'Konum güncellendi.');
    }

    public function destroy(FaultLocation $location)
    {
        $location->delete();
        return back()->with('success', 'Konum silindi.');
    }

    /* --- Alan (Area) CRUD — konum altında --- */
    public function storeArea(Request $request, FaultLocation $location)
    {
        $request->validate(['name' => 'required|string|max:255']);
        FaultArea::create(['fault_location_id' => $location->id, 'name' => $request->name, 'is_active' => true]);
        return back()->with('success', 'Alan eklendi.');
    }

    public function destroyArea(FaultArea $area)
    {
        $area->delete();
        return back()->with('success', 'Alan silindi.');
    }
}
