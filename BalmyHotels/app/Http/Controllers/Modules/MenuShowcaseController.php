<?php

namespace App\Http\Controllers\Modules;

use App\Models\MenuShowcase;
use App\Models\MenuShowcaseItem;
use App\Models\QrMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuShowcaseController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'qrmenus',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    // -------------------------------------------------------------------------

    public function index()
    {
        $showcases = MenuShowcase::with(['creator', 'items'])->latest()->get();
        $page_title = 'Menü Vitrinleri';
        return view('modules.qrmenu.showcases.index', compact('showcases', 'page_title'));
    }

    public function create()
    {
        $menus = QrMenu::where('is_active', true)->orderBy('name')->get();
        $page_title = 'Yeni Vitrin';
        return view('modules.qrmenu.showcases.create', compact('menus', 'page_title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:120',
            'slug'         => 'nullable|string|max:80|unique:menu_showcases,slug',
            'subtitle'     => 'nullable|string|max:300',
            'accent_color' => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'menus'        => 'nullable|array',
            'menus.*'      => 'exists:qr_menus,id',
            'labels'       => 'nullable|array',
            'labels.*'     => 'nullable|string|max:80',
        ]);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        // ensure uniqueness
        $base = $slug;
        $i = 2;
        while (MenuShowcase::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $showcase = MenuShowcase::create([
            'title'        => $data['title'],
            'slug'         => $slug,
            'subtitle'     => $data['subtitle'] ?? null,
            'accent_color' => $data['accent_color'] ?? '#c19b77',
            'is_active'    => $request->boolean('is_active', true),
            'created_by'   => auth()->id(),
        ]);

        $this->syncMenus($showcase, $request->input('menus', []), $request->input('labels', []));

        return redirect()->route('qrmenus.showcases.index')
            ->with('success', '"' . $showcase->title . '" vitrini oluşturuldu.');
    }

    public function edit(MenuShowcase $showcase)
    {
        $showcase->load('items.menu');
        $menus      = QrMenu::where('is_active', true)->orderBy('name')->get();
        $page_title = 'Vitrin Düzenle: ' . $showcase->title;
        return view('modules.qrmenu.showcases.edit', compact('showcase', 'menus', 'page_title'));
    }

    public function update(Request $request, MenuShowcase $showcase)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:120',
            'slug'         => 'nullable|string|max:80|unique:menu_showcases,slug,' . $showcase->id,
            'subtitle'     => 'nullable|string|max:300',
            'accent_color' => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'menus'        => 'nullable|array',
            'menus.*'      => 'exists:qr_menus,id',
            'labels'       => 'nullable|array',
            'labels.*'     => 'nullable|string|max:80',
        ]);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        $base = $slug;
        $i = 2;
        while (MenuShowcase::where('slug', $slug)->where('id', '!=', $showcase->id)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $showcase->update([
            'title'        => $data['title'],
            'slug'         => $slug,
            'subtitle'     => $data['subtitle'] ?? null,
            'accent_color' => $data['accent_color'] ?? '#c19b77',
            'is_active'    => $request->boolean('is_active', true),
        ]);

        $this->syncMenus($showcase, $request->input('menus', []), $request->input('labels', []));

        return redirect()->route('qrmenus.showcases.index')
            ->with('success', 'Vitrin güncellendi.');
    }

    public function destroy(MenuShowcase $showcase)
    {
        $showcase->delete();
        return back()->with('success', 'Vitrin silindi.');
    }

    // -------------------------------------------------------------------------

    private function syncMenus(MenuShowcase $showcase, array $menuIds, array $labels): void
    {
        $showcase->items()->delete();

        foreach (array_values($menuIds) as $idx => $menuId) {
            MenuShowcaseItem::create([
                'showcase_id'  => $showcase->id,
                'qr_menu_id'   => (int)$menuId,
                'label'        => $labels[$idx] ?? null,
                'sort_order'   => $idx,
            ]);
        }
    }
}
