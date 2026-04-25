<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\QrMenu;
use App\Models\QrMenuLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrMenuController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'qrmenus',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'toggle', 'clone'],
            ['destroy']
        );
    }


    public function index()
    {
        $menus = QrMenu::with(['branch', 'languages', 'categories'])
            ->withCount(['items', 'categories'])
            ->latest()
            ->get();

        $stats = [
            'total'       => $menus->count(),
            'active'      => $menus->where('is_active', true)->count(),
            'inactive'    => $menus->where('is_active', false)->count(),
            'total_items' => $menus->sum('items_count'),
        ];

        $page_title = 'QR Menü Yönetimi';

        return view('modules.qrmenu.index', compact('menus', 'stats', 'page_title'));
    }

    public function create()
    {
        $branches     = Branch::orderBy('name')->get();
        $langPresets  = QrMenuLanguage::PRESETS;
        $page_title   = 'Yeni QR Menü';

        return view('modules.qrmenu.create', compact('branches', 'langPresets', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:qr_menus,name|regex:/^[a-z0-9\-]+$/',
            'branch_id'   => 'nullable|exists:branches,id',
            'theme_color' => 'nullable|string|max:20',
            'currency'    => 'required|string|max:10',
            'languages'   => 'required|array|min:1',
            'languages.*' => 'required|string|max:10',
            'default_lang'=> 'required|string|max:10',
        ]);

        $selectedLangs = $request->languages;
        $defaultLang   = $request->default_lang;
        $presets       = QrMenuLanguage::PRESETS;

        // Dillere göre başlık JSON oluştur
        $title = [];
        foreach ($selectedLangs as $code) {
            $title[$code] = $request->input("title_{$code}", '');
        }

        // Para birimi sembolü
        $symbols = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol  = $symbols[$request->currency] ?? $request->currency;

        $menu = QrMenu::create([
            'branch_id'       => $request->branch_id,
            'created_by'      => auth()->id(),
            'name'            => Str::slug($request->name, '-'),
            'title'           => $title,
            'theme_color'     => $request->theme_color ?? '#c19b77',
            'is_active'       => true,
            'currency'        => $request->currency,
            'currency_symbol' => $symbol,
        ]);

        // Görsel yükleme
        if ($request->hasFile('logo')) {
            $menu->update(['logo' => $request->file('logo')->store('qrmenu/logos', 'public')]);
        }
        if ($request->hasFile('cover_image')) {
            $menu->update(['cover_image' => $request->file('cover_image')->store('qrmenu/covers', 'public')]);
        }

        // Dilleri kaydet
        foreach ($selectedLangs as $i => $code) {
            $info = $presets[$code] ?? ['name' => $code, 'flag' => '🌐'];
            QrMenuLanguage::create([
                'qr_menu_id' => $menu->id,
                'code'       => $code,
                'name'       => $info['name'],
                'flag'       => $info['flag'],
                'is_default' => $code === $defaultLang,
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('qrmenus.show', $menu)
            ->with('success', 'QR Menü oluşturuldu.');
    }

    public function show(QrMenu $qrmenu)
    {
        $qrmenu->load(['branch', 'languages', 'categories.items.foodProduct', 'creator']);
        $menu       = $qrmenu;
        $page_title = $qrmenu->name;

        return view('modules.qrmenu.show', compact('menu', 'page_title'));
    }

    public function edit(QrMenu $qrmenu)
    {
        $qrmenu->load('languages');
        $menu            = $qrmenu;
        $branches        = Branch::orderBy('name')->get();
        $languagePresets = QrMenuLanguage::PRESETS;
        $langPresets     = $languagePresets;
        $page_title      = 'Menü Düzle';

        return view('modules.qrmenu.edit', compact('menu', 'branches', 'langPresets', 'languagePresets', 'page_title'));
    }

    public function update(Request $request, QrMenu $qrmenu)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:qr_menus,name,' . $qrmenu->id . '|regex:/^[a-z0-9\-]+$/',
            'branch_id'    => 'nullable|exists:branches,id',
            'theme_color'  => 'nullable|string|max:20',
            'theme'        => 'nullable|string|in:default,forest,bohemian,light',
            'currency'     => 'required|string|max:10',
            'languages'    => 'required|array|min:1',
            'languages.*'  => 'required|string|max:10',
            'default_lang' => 'required|string|max:10',
        ]);

        $selectedLangs = $request->languages;
        $defaultLang   = $request->default_lang;
        $presets       = QrMenuLanguage::PRESETS;

        $title = [];
        foreach ($selectedLangs as $code) {
            $title[$code] = $request->input("title_{$code}", '');
        }

        $symbols = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol  = $symbols[$request->currency] ?? $request->currency;

        $qrmenu->update([
            'branch_id'       => $request->branch_id,
            'name'            => Str::slug($request->name, '-'),
            'title'           => $title,
            'theme_color'     => $request->theme_color ?? $qrmenu->theme_color,
            'theme'           => $request->input('theme', 'default'),
            'is_active'       => $request->boolean('is_active'),
            'currency'        => $request->currency,
            'currency_symbol' => $symbol,
        ]);

        // Dilleri yeniden yaz
        $qrmenu->languages()->delete();
        foreach ($selectedLangs as $i => $code) {
            $info = $presets[$code] ?? ['name' => $code, 'flag' => '🌐'];
            QrMenuLanguage::create([
                'qr_menu_id' => $qrmenu->id,
                'code'       => $code,
                'name'       => $info['name'],
                'flag'       => $info['flag'],
                'is_default' => $code === $defaultLang,
                'sort_order' => $i,
            ]);
        }

        // Görsel güncelleme
        if ($request->hasFile('logo')) {
            if ($qrmenu->logo) Storage::disk('public')->delete($qrmenu->logo);
            $qrmenu->update(['logo' => $request->file('logo')->store('qrmenu/logos', 'public')]);
        }
        if ($request->hasFile('cover_image')) {
            if ($qrmenu->cover_image) Storage::disk('public')->delete($qrmenu->cover_image);
            $qrmenu->update(['cover_image' => $request->file('cover_image')->store('qrmenu/covers', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'QR Menü güncellendi.');
    }

    public function destroy(QrMenu $qrmenu)
    {
        $qrmenu->delete();
        return redirect()->route('qrmenus.index')
            ->with('success', 'QR Menü silindi.');
    }

    /**
     * Durum aç/kapat
     */
    public function toggle(QrMenu $qrmenu)
    {
        $qrmenu->update(['is_active' => !$qrmenu->is_active]);
        $status = $qrmenu->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Menü {$status} edildi.");
    }

    /**
     * Menüyü komple klonla (kategoriler + ürünler + diller)
     */
    public function clone(QrMenu $qrmenu)
    {
        $qrmenu->load('languages', 'categories.items');

        // Benzersiz name üret
        $baseSlug = $qrmenu->name . '-kopya';
        $slug = $baseSlug;
        $i = 2;
        while (QrMenu::where('name', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $clone = QrMenu::create([
            'branch_id'       => $qrmenu->branch_id,
            'created_by'      => auth()->id(),
            'name'            => $slug,
            'title'           => $qrmenu->title,
            'description'     => $qrmenu->description,
            'logo'            => $qrmenu->logo,
            'cover_image'     => $qrmenu->cover_image,
            'theme_color'     => $qrmenu->theme_color,
            'is_active'       => false,
            'currency'        => $qrmenu->currency,
            'currency_symbol' => $qrmenu->currency_symbol,
        ]);

        // Dilleri kopyala
        foreach ($qrmenu->languages as $lang) {
            QrMenuLanguage::create([
                'qr_menu_id' => $clone->id,
                'code'       => $lang->code,
                'name'       => $lang->name,
                'flag'       => $lang->flag,
                'is_default' => $lang->is_default,
                'sort_order' => $lang->sort_order,
            ]);
        }

        // Kategorileri ve ürünleri kopyala
        foreach ($qrmenu->categories as $category) {
            $newCategory = \App\Models\QrMenuCategory::create([
                'qr_menu_id'   => $clone->id,
                'title'        => $category->title,
                'description'  => $category->description,
                'icon'         => $category->icon,
                'image'        => $category->image,
                'sort_order'   => $category->sort_order,
                'is_active'    => $category->is_active,
                'sub_headings' => $category->sub_headings,
            ]);

            foreach ($category->items as $item) {
                \App\Models\QrMenuItem::create([
                    'category_id'     => $newCategory->id,
                    'food_product_id' => $item->food_product_id,
                    'title'           => $item->title,
                    'description'     => $item->description,
                    'price'           => $item->price,
                    'price_override'  => $item->price_override,
                    'image'           => $item->image,
                    'is_active'       => $item->is_active,
                    'is_featured'     => $item->is_featured,
                    'badges'          => $item->badges,
                    'sort_order'      => $item->sort_order,
                    'sub_heading'     => $item->sub_heading,
                    'price_glass'     => $item->price_glass,
                    'price_bottle'    => $item->price_bottle,
                    'cl_glass'        => $item->cl_glass,
                    'cl_bottle'       => $item->cl_bottle,
                ]);
            }
        }

        return redirect()->route('qrmenus.edit', $clone)
            ->with('success', '"' . $qrmenu->getTitle() . '" menüsü klonlandı. İsim ve ayarları güncelleyebilirsiniz.');
    }
}
