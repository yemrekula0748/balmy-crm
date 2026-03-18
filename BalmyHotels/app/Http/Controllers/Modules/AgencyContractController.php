<?php

namespace App\Http\Controllers\Modules;

use App\Models\Agency;
use App\Models\AgencyContract;
use App\Models\RoomType;
use Illuminate\Http\Request;

class AgencyContractController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('agency_contracts',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index()
    {
        $contracts = AgencyContract::with(['agency', 'roomType'])->orderBy('id', 'desc')->get();
        return view('modules.acenteler.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $agencies  = Agency::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('name')->get();
        $nextCode  = AgencyContract::generateCode();
        return view('modules.acenteler.contracts.create', compact('agencies', 'roomTypes', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agency_id'    => 'required|exists:agencies,id',
            'room_type_id' => 'required|exists:room_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'price_single' => 'required|numeric|min:0',
            'price_double' => 'required|numeric|min:0',
            'price_triple' => 'required|numeric|min:0',
            'price_quad'   => 'required|numeric|min:0',
            'price_baby1'  => 'required|numeric|min:0',
            'price_baby2'  => 'required|numeric|min:0',
            'price_child1' => 'required|numeric|min:0',
            'price_child2' => 'required|numeric|min:0',
        ]);

        $data['contract_code'] = AgencyContract::generateCode();
        AgencyContract::create($data);

        return redirect()->route('agencies.contracts.index')
            ->with('success', 'Kontrat başarıyla oluşturuldu. Kod: ' . $data['contract_code']);
    }

    public function update(Request $request, AgencyContract $contract)
    {
        $data = $request->validate([
            'agency_id'    => 'required|exists:agencies,id',
            'room_type_id' => 'required|exists:room_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'price_single' => 'required|numeric|min:0',
            'price_double' => 'required|numeric|min:0',
            'price_triple' => 'required|numeric|min:0',
            'price_quad'   => 'required|numeric|min:0',
            'price_baby1'  => 'required|numeric|min:0',
            'price_baby2'  => 'required|numeric|min:0',
            'price_child1' => 'required|numeric|min:0',
            'price_child2' => 'required|numeric|min:0',
        ]);

        $contract->update($data);

        return back()->with('success', 'Kontrat güncellendi.');
    }

    public function destroy(AgencyContract $contract)
    {
        $contract->delete();
        return back()->with('success', 'Kontrat silindi.');
    }
}
