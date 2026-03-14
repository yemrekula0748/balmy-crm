<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Printer;
use Illuminate\Http\Request;

class PrinterController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'printers',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index()
    {
        $authUser  = auth()->user();
        $branchIds = $authUser->visibleBranchIds();

        $printers = Printer::with('branch')
            ->when(!$authUser->isSuperAdmin(), fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $branches = $authUser->isSuperAdmin()
            ? Branch::orderBy('name')->get()
            : Branch::whereIn('id', $branchIds)->orderBy('name')->get();

        $page_title = 'Yazıcılar';

        return view('modules.printers.index', compact('printers', 'branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'  => 'required|exists:branches,id',
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'codepage'   => 'required|integer|min:0|max:255',
        ]);

        Printer::create([
            'branch_id'  => $request->branch_id,
            'name'       => $request->name,
            'ip_address' => $request->ip_address,
            'codepage'   => $request->codepage,
            'is_active'  => true,
        ]);

        return redirect()->route('printers.index')->with('success', 'Yazıcı eklendi.');
    }

    public function edit(Printer $printer)
    {
        $authUser  = auth()->user();
        $branchIds = $authUser->visibleBranchIds();

        $branches = $authUser->isSuperAdmin()
            ? Branch::orderBy('name')->get()
            : Branch::whereIn('id', $branchIds)->orderBy('name')->get();

        $page_title = 'Yazıcı Düzenle';

        return view('modules.printers.edit', compact('printer', 'branches', 'page_title'));
    }

    public function update(Request $request, Printer $printer)
    {
        $request->validate([
            'branch_id'  => 'required|exists:branches,id',
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'codepage'   => 'required|integer|min:0|max:255',
            'is_active'  => 'boolean',
        ]);

        $printer->update([
            'branch_id'  => $request->branch_id,
            'name'       => $request->name,
            'ip_address' => $request->ip_address,
            'codepage'   => $request->codepage,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('printers.index')->with('success', 'Yazıcı güncellendi.');
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();

        return redirect()->route('printers.index')->with('success', 'Yazıcı silindi.');
    }
}
