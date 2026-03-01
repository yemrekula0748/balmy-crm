<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\QrMenu;
use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrMenuCategoryController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'qrmenus',
            [],
            [],
            ['createCategory', 'storeCategory', 'createItem', 'storeItem'],
            ['editCategory', 'updateCategory', 'editItem', 'updateItem'],
            ['destroyCategory', 'destroyItem']
        );
    }


    /* ═══════════════════════════════
     |  KATEGORİLER
     ═══════════════════════════════ */

    public function createCategory(QrMenu $qrmenu)
    {
        $qrmenu->load('languages');
        $menu       = $qrmenu;
        $page_title = 'Kategori Ekle';

        return view('modules.qrmenu.category_form', compact('menu', 'page_title'));
    }

    public function storeCategory(Request $request, QrMenu $qrmenu)
    {
        $qrmenu->load('languages');
        $request->validate([
            'icon'       => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        $category = $qrmenu->categories()->create([
            'title'       => $title,
            'description' => array_filter($description) ?: null,
            'icon'        => $request->icon,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => true,
        ]);

        if ($request->hasFile('image')) {
            $category->update(['image' => $request->file('image')->store('qrmenu/categories', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Kategori eklendi.');
    }

    public function editCategory(QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');
        $menu       = $qrmenu;
        $page_title = 'Kategori Düzle';

        return view('modules.qrmenu.category_form', compact('menu', 'category', 'page_title'));
    }

    public function updateCategory(Request $request, QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        $category->update([
            'title'       => $title,
            'description' => array_filter($description) ?: null,
            'icon'        => $request->icon,
            'sort_order'  => $request->sort_order ?? $category->sort_order,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $category->update(['image' => $request->file('image')->store('qrmenu/categories', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroyCategory(QrMenu $qrmenu, QrMenuCategory $category)
    {
        $category->delete();
        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Kategori silindi.');
    }

    /* ═══════════════════════════════
     |  ÜRÜNLER
     ═══════════════════════════════ */

    public function createItem(QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');
        $menu         = $qrmenu;
        $badgeOptions = QrMenuItem::BADGE_OPTIONS;
        $page_title   = 'Ürün Ekle';

        return view('modules.qrmenu.item_form', compact('menu', 'category', 'badgeOptions', 'page_title'));
    }

    public function storeItem(Request $request, QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');
        $request->validate([
            'price'      => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        // En az bir dilde başlık zorunlu
        if (array_filter($title) === []) {
            return back()->withErrors(['title' => 'En az bir dilde ürün adı girilmelidir.'])->withInput();
        }

        $item = $category->items()->create([
            'title'       => $title,
            'description' => array_filter($description) ?: null,
            'price'       => $request->price,
            'is_active'   => true,
            'is_featured' => $request->boolean('is_featured'),
            'badges'      => $request->badges ?: null,
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        if ($request->hasFile('image')) {
            $item->update(['image' => $request->file('image')->store('qrmenu/items', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Ürün eklendi.');
    }

    public function editItem(QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $qrmenu->load('languages');
        $menu         = $qrmenu;
        $badgeOptions = QrMenuItem::BADGE_OPTIONS;
        $page_title   = 'Ürün Düzenle';

        return view('modules.qrmenu.item_form', compact('menu', 'category', 'item', 'badgeOptions', 'page_title'));
    }

    public function updateItem(Request $request, QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $qrmenu->load('languages');
        $request->validate([
            'price' => 'nullable|numeric|min:0',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        $item->update([
            'title'       => $title,
            'description' => array_filter($description) ?: null,
            'price'       => $request->price,
            'is_active'   => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'badges'      => $request->badges ?: null,
            'sort_order'  => $request->sort_order ?? $item->sort_order,
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $item->update(['image' => $request->file('image')->store('qrmenu/items', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Ürün güncellendi.');
    }

    public function destroyItem(QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $item->delete();
        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Ürün silindi.');
    }
}
