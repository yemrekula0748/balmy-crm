<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use Illuminate\Http\Request;

class AssetController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'assets',
            ['index', 'categoryFields'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $query = Asset::with(['category', 'branch'])->latest();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('asset_code', 'like', "%{$q}%")
                    ->orWhere('serial_no', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }

        $assets     = $query->paginate(20)->withQueryString();
        $branches   = Branch::orderBy('name')->get();
        $categories = AssetCategory::orderBy('name')->get();

        // İstatistikler
        $stats = [
            'total'       => Asset::count(),
            'available'   => Asset::where('status', 'available')->count(),
            'in_use'      => Asset::where('status', 'in_use')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'retired'     => Asset::where('status', 'retired')->count(),
        ];

        $page_title = 'Demirbaş Yönetimi';

        return view('modules.assets.index', compact(
            'assets', 'branches', 'categories', 'stats', 'page_title'
        ));
    }

    public function create()
    {
        $categories = AssetCategory::orderBy('name')->get();
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Demirbaş Ekle';
        $nextCode   = Asset::generateCode();

        return view('modules.assets.create', compact('categories', 'branches', 'page_title', 'nextCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code'   => 'required|string|max:50|unique:assets,asset_code',
            'category_id'  => 'required|exists:asset_categories,id',
            'branch_id'    => 'required|exists:branches,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'location'     => 'nullable|string|max:255',
            'status'       => 'required|in:available,in_use,maintenance,retired',
            'purchase_date'=> 'nullable|date',
            'purchase_price'=> 'nullable|numeric|min:0',
            'serial_no'    => 'nullable|string|max:255',
            'warranty_until'=> 'nullable|date',
        ]);

        // Dinamik alanları işle
        $category = AssetCategory::find($request->category_id);
        $properties = [];
        if ($category && $category->field_definitions) {
            foreach ($category->field_definitions as $field) {
                $properties[$field['name']] = $request->input('prop_' . $field['name']);
            }
        }

        Asset::create([
            'asset_code'    => strtoupper($request->asset_code),
            'category_id'   => $request->category_id,
            'branch_id'     => $request->branch_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'location'      => $request->location,
            'status'        => $request->status,
            'purchase_date' => $request->purchase_date,
            'purchase_price'=> $request->purchase_price,
            'serial_no'     => $request->serial_no,
            'warranty_until'=> $request->warranty_until,
            'properties'    => $properties ?: null,
        ]);

        return redirect()->route('assets.index')
            ->with('success', 'Demirbaş kaydı oluşturuldu.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'branch', 'exits.staff', 'exits.approver', 'exits.branch']);
        $page_title = $asset->name;

        return view('modules.assets.show', compact('asset', 'page_title'));
    }

    public function edit(Asset $asset)
    {
        $categories = AssetCategory::orderBy('name')->get();
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Demirbaş Düzenle';

        return view('modules.assets.edit', compact('asset', 'categories', 'branches', 'page_title'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_code'    => 'required|string|max:50|unique:assets,asset_code,' . $asset->id,
            'category_id'   => 'required|exists:asset_categories,id',
            'branch_id'     => 'required|exists:branches,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location'      => 'nullable|string|max:255',
            'status'        => 'required|in:available,in_use,maintenance,retired',
            'purchase_date' => 'nullable|date',
            'purchase_price'=> 'nullable|numeric|min:0',
            'serial_no'     => 'nullable|string|max:255',
            'warranty_until'=> 'nullable|date',
        ]);

        $category = AssetCategory::find($request->category_id);
        $properties = [];
        if ($category && $category->field_definitions) {
            foreach ($category->field_definitions as $field) {
                $properties[$field['name']] = $request->input('prop_' . $field['name']);
            }
        }

        $asset->update([
            'asset_code'    => strtoupper($request->asset_code),
            'category_id'   => $request->category_id,
            'branch_id'     => $request->branch_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'location'      => $request->location,
            'status'        => $request->status,
            'purchase_date' => $request->purchase_date,
            'purchase_price'=> $request->purchase_price,
            'serial_no'     => $request->serial_no,
            'warranty_until'=> $request->warranty_until,
            'properties'    => $properties ?: null,
        ]);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Demirbaş güncellendi.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->exits()->whereIn('status', ['pending', 'approved'])->count() > 0) {
            return back()->with('error', 'Aktif çıkış kaydı olan demirbaş silinemez.');
        }
        $asset->delete();
        return redirect()->route('assets.index')
            ->with('success', 'Demirbaş silindi.');
    }

    /**
     * AJAX: kategori değişince dinamik alanları döndür
     */
    public function categoryFields(AssetCategory $assetCategory)
    {
        return response()->json($assetCategory->field_definitions ?? []);
    }
}
