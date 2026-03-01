<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetExit;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class AssetExitController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'asset_exits',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['approve', 'reject', 'returnItem'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $query = AssetExit::with(['asset.category', 'branch', 'staff', 'approver'])->latest();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->taker_type) {
            $query->where('taker_type', $request->taker_type);
        }
        if ($request->search) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('guest_name', 'like', "%{$q}%")
                    ->orWhere('guest_room', 'like', "%{$q}%")
                    ->orWhereHas('asset', fn($a) => $a->where('name', 'like', "%{$q}%")->orWhere('asset_code', 'like', "%{$q}%"))
                    ->orWhereHas('staff', fn($s) => $s->where('name', 'like', "%{$q}%"));
            });
        }

        $exits    = $query->paginate(20)->withQueryString();
        $branches = Branch::orderBy('name')->get();

        // İstatistikler
        $stats = [
            'pending'  => AssetExit::where('status', 'pending')->count(),
            'approved' => AssetExit::where('status', 'approved')->whereNull('returned_at')->count(),
            'overdue'  => AssetExit::where('status', 'approved')
                              ->whereNull('returned_at')
                              ->where('expected_return_at', '<', now())
                              ->whereNotNull('expected_return_at')
                              ->count(),
            'returned' => AssetExit::where('status', 'returned')->count(),
        ];

        $page_title = 'Eşya Çıkış Formları';

        return view('modules.assets.exits.index', compact(
            'exits', 'branches', 'stats', 'page_title'
        ));
    }

    public function create(Request $request)
    {
        $assets = Asset::with('category')
            ->whereIn('status', ['available', 'in_use'])
            ->orderBy('name')
            ->get();
        $branches = Branch::orderBy('name')->get();
        $staff    = User::where('is_active', true)->orderBy('name')->get();
        $page_title = 'Çıkış Formu Oluştur';

        // Önceden seçili demirbaş (assets.show'dan link ile gelebilir)
        $selectedAsset = $request->asset_id ? Asset::find($request->asset_id) : null;

        return view('modules.assets.exits.create', compact(
            'assets', 'branches', 'staff', 'page_title', 'selectedAsset'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'branch_id'          => 'required|exists:branches,id',
            'taker_type'         => 'required|in:staff,guest',
            'staff_id'           => 'required_if:taker_type,staff|nullable|exists:users,id',
            'guest_name'         => 'required_if:taker_type,guest|nullable|string|max:255',
            'guest_room'         => 'nullable|string|max:50',
            'guest_id_no'        => 'nullable|string|max:50',
            'guest_phone'        => 'nullable|string|max:20',
            'purpose'            => 'required|string',
            'taken_at'           => 'required|date',
            'expected_return_at' => 'nullable|date|after:taken_at',
            'notes'              => 'nullable|string',
        ]);

        AssetExit::create([
            'asset_id'           => $request->asset_id,
            'branch_id'          => $request->branch_id,
            'taker_type'         => $request->taker_type,
            'staff_id'           => $request->taker_type === 'staff' ? $request->staff_id : null,
            'guest_name'         => $request->taker_type === 'guest' ? $request->guest_name : null,
            'guest_room'         => $request->taker_type === 'guest' ? $request->guest_room : null,
            'guest_id_no'        => $request->taker_type === 'guest' ? $request->guest_id_no : null,
            'guest_phone'        => $request->taker_type === 'guest' ? $request->guest_phone : null,
            'purpose'            => $request->purpose,
            'taken_at'           => $request->taken_at,
            'expected_return_at' => $request->expected_return_at,
            'notes'              => $request->notes,
            'status'             => 'pending',
        ]);

        return redirect()->route('asset-exits.index')
            ->with('success', 'Çıkış formu oluşturuldu. Onay bekleniyor.');
    }

    public function show(AssetExit $assetExit)
    {
        $assetExit->load(['asset.category', 'branch', 'staff', 'approver']);
        $page_title = 'Çıkış Formu #' . $assetExit->id;

        return view('modules.assets.exits.show', compact('assetExit', 'page_title'));
    }

    /**
     * Onayla
     */
    public function approve(AssetExit $assetExit)
    {
        if ($assetExit->status !== 'pending') {
            return back()->with('error', 'Bu form zaten işleme alınmış.');
        }

        $assetExit->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Demirbaşı "kullanımda" yap
        $assetExit->asset->update(['status' => 'in_use']);

        return back()->with('success', 'Çıkış formu onaylandı.');
    }

    /**
     * Reddet
     */
    public function reject(Request $request, AssetExit $assetExit)
    {
        $request->validate(['rejected_reason' => 'required|string']);

        if ($assetExit->status !== 'pending') {
            return back()->with('error', 'Bu form zaten işleme alınmış.');
        }

        $assetExit->update([
            'status'          => 'rejected',
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
            'rejected_reason' => $request->rejected_reason,
        ]);

        return back()->with('success', 'Çıkış formu reddedildi.');
    }

    /**
     * İade al
     */
    public function returnItem(AssetExit $assetExit)
    {
        if ($assetExit->status !== 'approved') {
            return back()->with('error', 'Yalnızca onaylanmış formlar iade alınabilir.');
        }

        $assetExit->update([
            'status'      => 'returned',
            'returned_at' => now(),
        ]);

        // Demirbaşı tekrar "mevcut" yap (başka aktif çıkış yoksa)
        $activeCount = $assetExit->asset->exits()
            ->where('status', 'approved')
            ->whereNull('returned_at')
            ->where('id', '!=', $assetExit->id)
            ->count();

        if ($activeCount === 0) {
            $assetExit->asset->update(['status' => 'available']);
        }

        return back()->with('success', 'Eşya iade alındı.');
    }

    public function destroy(AssetExit $assetExit)
    {
        if ($assetExit->status === 'approved') {
            return back()->with('error', 'Onaylanmış form silinemez, önce iade alın.');
        }
        $assetExit->delete();
        return redirect()->route('asset-exits.index')->with('success', 'Form silindi.');
    }
}
