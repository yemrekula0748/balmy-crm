<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $categories = AssetCategory::whereNull('parent_id')
            ->withCount('children')
            ->orderBy('name')
            ->get();
        $branches   = Branch::orderBy('name')->get();
        $page_title = 'Demirbaş Ekle';
        $nextCode   = Asset::generateCode();
        $showSubSelect = false;
        return view('modules.assets.create', compact('categories', 'branches', 'page_title', 'nextCode', 'showSubSelect'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code'    => 'required|string|max:50|unique:assets,asset_code',
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
            'photo'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Kategori dinamik alanları
        $category = AssetCategory::find($request->category_id);
        $properties = [];
        if ($category && $category->field_definitions) {
            foreach ($category->field_definitions as $field) {
                $properties[$field['name']] = $request->input('prop_' . $field['name']);
            }
        }

        // Per-asset custom fields
        $customFields = [];
        if ($request->has('cf_label')) {
            foreach ($request->cf_label as $i => $label) {
                $label = trim($label ?? '');
                if ($label === '') continue;
                $customFields[] = [
                    'label' => $label,
                    'value' => $request->cf_value[$i] ?? '',
                    'unit'  => $request->cf_unit[$i] ?? '',
                ];
            }
        }

        // Fotoğraf yükle
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('assets', 'public');
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
            'photo'         => $photoPath,
            'properties'    => $properties ?: null,
            'custom_fields' => $customFields ?: null,
            'qr_token'      => Str::uuid()->toString(),
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
        $categories = AssetCategory::whereNull('parent_id')
            ->withCount('children')
            ->orderBy('name')
            ->get();
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
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $category = AssetCategory::find($request->category_id);
        $properties = [];
        if ($category && $category->field_definitions) {
            foreach ($category->field_definitions as $field) {
                $properties[$field['name']] = $request->input('prop_' . $field['name']);
            }
        }

        // Per-asset custom fields
        $customFields = [];
        if ($request->has('cf_label')) {
            foreach ($request->cf_label as $i => $label) {
                $label = trim($label ?? '');
                if ($label === '') continue;
                $customFields[] = [
                    'label' => $label,
                    'value' => $request->cf_value[$i] ?? '',
                    'unit'  => $request->cf_unit[$i] ?? '',
                ];
            }
        }

        // Fotoğraf güncelle
        $photoPath = $asset->photo;
        if ($request->hasFile('photo')) {
            if ($asset->photo) {
                \Storage::disk('public')->delete($asset->photo);
            }
            $photoPath = $request->file('photo')->store('assets', 'public');
        } elseif ($request->input('remove_photo') === '1' && $asset->photo) {
            \Storage::disk('public')->delete($asset->photo);
            $photoPath = null;
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
            'photo'         => $photoPath,
            'properties'    => $properties ?: null,
            'custom_fields' => $customFields ?: null,
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

    /**
     * 80mm termal yazıcı için QR yazdırma sayfası
     */
    public function qrPrint(Asset $asset)
    {
        return view('modules.assets.qr-print', compact('asset'));
    }
}
