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
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index()
    {
        $categories = AssetCategory::withCount('assets')->orderBy('name')->get();
        $page_title = 'Demirbaş Kategorileri';

        return view('modules.assets.categories.index', compact('categories', 'page_title'));
    }

    public function create()
    {
        $page_title = 'Kategori Ekle';
        $fieldTypes = ['text' => 'Metin', 'number' => 'Sayı', 'date' => 'Tarih', 'select' => 'Seçim Listesi', 'textarea' => 'Uzun Metin'];

        return view('modules.assets.categories.create', compact('page_title', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:asset_categories,name',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        // Dinamik alan tanımlarını işle
        $fieldDefs = [];
        if ($request->has('fields')) {
            foreach ($request->fields as $field) {
                if (!empty($field['name']) && !empty($field['label'])) {
                    $fieldDefs[] = [
                        'name'     => \Str::slug($field['name'], '_'),
                        'label'    => $field['label'],
                        'type'     => $field['type'] ?? 'text',
                        'required' => isset($field['required']),
                        'options'  => !empty($field['options']) ? array_filter(explode(',', $field['options'])) : [],
                    ];
                }
            }
        }

        AssetCategory::create([
            'name'             => $request->name,
            'color'            => $request->color,
            'description'      => $request->description,
            'field_definitions'=> $fieldDefs ?: null,
        ]);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(AssetCategory $assetCategory)
    {
        $page_title = 'Kategori Düzenle';
        $fieldTypes = ['text' => 'Metin', 'number' => 'Sayı', 'date' => 'Tarih', 'select' => 'Seçim Listesi', 'textarea' => 'Uzun Metin'];

        return view('modules.assets.categories.edit', compact('assetCategory', 'page_title', 'fieldTypes'));
    }

    public function update(Request $request, AssetCategory $assetCategory)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:asset_categories,name,' . $assetCategory->id,
            'color' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $fieldDefs = [];
        if ($request->has('fields')) {
            foreach ($request->fields as $field) {
                if (!empty($field['name']) && !empty($field['label'])) {
                    $fieldDefs[] = [
                        'name'     => \Str::slug($field['name'], '_'),
                        'label'    => $field['label'],
                        'type'     => $field['type'] ?? 'text',
                        'required' => isset($field['required']),
                        'options'  => !empty($field['options']) ? array_filter(explode(',', $field['options'])) : [],
                    ];
                }
            }
        }

        $assetCategory->update([
            'name'             => $request->name,
            'color'            => $request->color,
            'description'      => $request->description,
            'field_definitions'=> $fieldDefs ?: null,
        ]);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroy(AssetCategory $assetCategory)
    {
        if ($assetCategory->assets()->count() > 0) {
            return back()->with('error', 'Bu kategoriye ait demirbaşlar mevcut, silinemez.');
        }

        $assetCategory->delete();
        return redirect()->route('asset-categories.index')
            ->with('success', 'Kategori silindi.');
    }
}
