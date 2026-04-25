<?php

namespace App\Http\Controllers\Modules;

use App\Models\BedType;
use Illuminate\Http\Request;

class FrontDeskBedTypeController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('bed_types',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $bedTypes = BedType::orderBy('name')->get();
        return view('modules.onburo.bed_types.index', compact('bedTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'abbreviation' => 'required|string|max:20',
        ]);

        BedType::create($data);
        return back()->with('success', 'Yatak tipi eklendi.');
    }

    public function update(Request $request, BedType $bedType)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'abbreviation' => 'required|string|max:20',
        ]);

        $bedType->update($data);
        return back()->with('success', 'Yatak tipi güncellendi.');
    }

    public function destroy(BedType $bedType)
    {
        $bedType->delete();
        return back()->with('success', 'Yatak tipi silindi.');
    }
}
