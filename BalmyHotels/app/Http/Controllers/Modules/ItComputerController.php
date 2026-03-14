<?php

namespace App\Http\Controllers\Modules;

use App\Models\Computer;
use App\Models\Branch;
use Illuminate\Http\Request;

class ItComputerController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('it_computers',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $computers = Computer::with('branch')->orderBy('name')->get();
        $branches  = Branch::orderBy('name')->get();
        return view('modules.bilgi_islem.computers.index', compact('computers', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'ip_address'    => 'nullable|ip',
            'location'      => 'nullable|string|max:200',
            'assigned_user' => 'nullable|string|max:200',
            'specs'         => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
            'branch_id'     => 'nullable|exists:branches,id',
        ]);

        Computer::create($data);

        return back()->with('success', 'Bilgisayar başarıyla eklendi.');
    }

    public function update(Request $request, Computer $computer)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'ip_address'    => 'nullable|ip',
            'location'      => 'nullable|string|max:200',
            'assigned_user' => 'nullable|string|max:200',
            'specs'         => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
            'branch_id'     => 'nullable|exists:branches,id',
        ]);

        $computer->update($data);

        return back()->with('success', 'Bilgisayar güncellendi.');
    }

    public function destroy(Computer $computer)
    {
        $computer->delete();
        return back()->with('success', 'Bilgisayar silindi.');
    }
}
