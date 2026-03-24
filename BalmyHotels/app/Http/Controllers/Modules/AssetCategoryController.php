<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'asset_categories',
            ['index', 'subcategories'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index()
    {
        $categories = AssetCategory::whereNull('parent_id')
            ->with(['children' => fn($q) => $q->withCount('assets')])
            ->withCount('assets')
            ->orderBy('name')
            ->get();
        $page_title = 'Demirbaş Kategorileri';
        return view('modules.assets.categories.index', compact('categories', 'page_title'));
    }

    /** AJAX: alt kategorileri döndürür */
    public function subcategories(AssetCategory $assetCategory)
    {
        $children = $assetCategory->children()->get(['id', 'name', 'color']);
        return response()->json($children);
    }

    public function create()
    {
        $page_title = 'Kategori Ekle';
        $fieldTypes = ['text' => 'Metin', 'number' => 'Sayı', 'date' => 'Tarih', 'select' => 'Seçim Listesi', 'textarea' => 'Uzun Metin'];
        $parentCats = AssetCategory::whereNull('parent_id')->orderBy('name')->get(['id', 'name', 'color']);
        return view('modules.assets.categories.create', compact('page_title', 'fieldTypes', 'parentCats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:asset_categories,name',
            'color'       => 'required|string|max:20',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:asset_categories,id',
        ]);

        if ($request->parent_id) {
            $parent = AssetCategory::find($request->parent_id);
            if ($parent && !is_null($parent->parent_id)) {
                return back()->withInput()->with('error', 'Sadece ana kategoriler üst kategori olarak seçilebilir.');
            }
        }

        $fieldDefs = $this->parseFieldDefs($request);

        AssetCategory::create([
            'name'              => $request->name,
            'color'             => $request->color,
            'description'       => $request->description,
            'parent_id'         => $request->parent_id ?: null,
            'field_definitions' => $fieldDefs ?: null,
        ]);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(AssetCategory $assetCategory)
    {
        $page_title = 'Kategori Düzenle';
        $fieldTypes = ['text' => 'Metin', 'number' => 'Sayı', 'date' => 'Tarih', 'select' => 'Seçim Listesi', 'textarea' => 'Uzun Metin'];
        $parentCats = AssetCategory::whereNull('parent_id')
            ->where('id', '!=', $assetCategory->id)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);
        return view('modules.assets.categories.edit', compact('assetCategory', 'page_title', 'fieldTypes', 'parentCats'));
    }

    public function update(Request $request, AssetCategory $assetCategory)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:asset_categories,name,' . $assetCategory->id,
            'color'       => 'required|string|max:20',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:asset_categories,id',
        ]);

        if ($request->parent_id) {
            $parent = AssetCategory::find($request->parent_id);
            if ($parent && !is_null($parent->parent_id)) {
                return back()->withInput()->with('error', 'Sadece ana kategoriler üst kategori olarak seçilebilir.');
            }
            if ((int)$request->parent_id === $assetCategory->id) {
                return back()->withInput()->with('error', 'Bir kategori kendisinin üst kategorisi olamaz.');
            }
        }

        $fieldDefs = $this->parseFieldDefs($request);

        $assetCategory->update([
            'name'              => $request->name,
            'color'             => $request->color,
            'description'       => $request->description,
            'parent_id'         => $request->parent_id ?: null,
            'field_definitions' => $fieldDefs ?: null,
        ]);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroy(AssetCategory $assetCategory)
    {
        if ($assetCategory->children()->exists()) {
            return back()->with('error', 'Bu kategorinin alt kategorileri mevcut, önce alt kategorileri silin.');
        }
        if ($assetCategory->assets()->count() > 0) {
            return back()->with('error', 'Bu kategoriye ait demirbaşlar mevcut, silinemez.');
        }
        $assetCategory->delete();
        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori silindi.');
    }

    private function parseFieldDefs(Request $request): array
    {
        $fieldDefs = [];
        if ($request->has('fields')) {
            foreach ($request->fields as $field) {
                if (!empty($field['name']) && !empty($field['label'])) {
                    $fieldDefs[] = [
                        'name'     => \Str::slug($field['name'], '_'),
                        'label'    => $field['label'],
                        'type'     => $field['type'] ?? 'text',
                        'required' => isset($field['required']),
                        'options'  => !empty($field['options'])
                            ? array_values(array_filter(array_map('trim', explode(',', $field['options']))))
                            : [],
                    ];
                }
            }
        }
        return $fieldDefs;
    }
}
