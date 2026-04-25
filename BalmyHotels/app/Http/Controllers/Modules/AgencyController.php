<?php

namespace App\Http\Controllers\Modules;

use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('agencies',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index()
    {
        $agencies = Agency::orderBy('name')->get();
        return view('modules.acenteler.index', compact('agencies'));
    }

    public function create()
    {
        $nextCode = Agency::generateCode();
        return view('modules.acenteler.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agency_code'        => 'required|string|max:30|unique:agencies,agency_code',
            'name'               => 'required|string|max:200',
            'currency'           => 'required|in:TL,USD,EUR,GBP',
            'billing_address'    => 'required|string',
            'nationalities'      => 'nullable|array',
            'nationalities.*'    => 'string|max:100',
            'market'             => 'required|in:domestic,europe,middle_east,russia',
            'payment_type'       => 'required|in:agency_pay,guest_pay',
            'payment_method'     => 'required|in:city_ledger,cash,credit_card,bank_transfer',
            'accommodation_type' => 'required|in:sold,comp,house_use',
        ]);

        Agency::create($data);

        return redirect()->route('agencies.index')
            ->with('success', 'Acenta başarıyla eklendi.');
    }

    public function update(Request $request, Agency $agency)
    {
        $data = $request->validate([
            'agency_code'        => 'required|string|max:30|unique:agencies,agency_code,' . $agency->id,
            'name'               => 'required|string|max:200',
            'currency'           => 'required|in:TL,USD,EUR,GBP',
            'billing_address'    => 'required|string',
            'nationalities'      => 'nullable|array',
            'nationalities.*'    => 'string|max:100',
            'market'             => 'required|in:domestic,europe,middle_east,russia',
            'payment_type'       => 'required|in:agency_pay,guest_pay',
            'payment_method'     => 'required|in:city_ledger,cash,credit_card,bank_transfer',
            'accommodation_type' => 'required|in:sold,comp,house_use',
        ]);

        $agency->update($data);

        return back()->with('success', 'Acenta bilgileri güncellendi.');
    }

    public function destroy(Agency $agency)
    {
        $agency->delete();
        return back()->with('success', 'Acenta silindi.');
    }
}
