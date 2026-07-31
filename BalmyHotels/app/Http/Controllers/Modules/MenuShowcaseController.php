<?php

namespace App\Http\Controllers\Modules;

use App\Models\MenuShowcase;
use App\Models\MenuShowcaseItem;
use App\Models\QrMenu;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        $menus = QrMenu::with('branch')->where('is_active', true)->orderBy('name')->get();
        $surveys = Survey::with('branch')->where('is_active', true)->latest()->get();
        $page_title = 'Yeni Vitrin';
        return view('modules.qrmenu.showcases.create', compact('menus', 'surveys', 'page_title'));
    }

    public function store(Request $request)
    {
        $this->normaliseLegacyMenuInput($request);
        $data = $this->validatedData($request);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        // ensure uniqueness
        $base = $slug;
        $i = 2;
        while (MenuShowcase::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $showcase = DB::transaction(function () use ($data, $slug, $request) {
            $showcase = MenuShowcase::create([
                'title'        => $data['title'],
                'slug'         => $slug,
                'subtitle'     => $data['subtitle'] ?? null,
                'accent_color' => $data['accent_color'] ?? '#c19b77',
                'is_active'    => $request->boolean('is_active'),
                'created_by'   => auth()->id(),
            ]);

            $this->syncItems(
                $showcase,
                $data['item_types'] ?? [],
                $data['item_ids'] ?? [],
                $data['labels'] ?? []
            );

            return $showcase;
        });

        return redirect()->route('qrmenus.showcases.index')
            ->with('success', '"' . $showcase->title . '" vitrini oluşturuldu.');
    }

    public function edit(MenuShowcase $showcase)
    {
        $showcase->load(['items.menu', 'items.survey']);
        $menus = QrMenu::with('branch')->orderBy('name')->get();
        $surveys = Survey::with('branch')->latest()->get();
        $page_title = 'Vitrin Düzenle: ' . $showcase->title;
        return view('modules.qrmenu.showcases.edit', compact('showcase', 'menus', 'surveys', 'page_title'));
    }

    public function update(Request $request, MenuShowcase $showcase)
    {
        $this->normaliseLegacyMenuInput($request);
        $data = $this->validatedData($request, $showcase);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        $base = $slug;
        $i = 2;
        while (MenuShowcase::where('slug', $slug)->where('id', '!=', $showcase->id)->exists()) {
            $slug = $base . '-' . $i++;
        }

        DB::transaction(function () use ($showcase, $data, $slug, $request) {
            $showcase->update([
                'title'        => $data['title'],
                'slug'         => $slug,
                'subtitle'     => $data['subtitle'] ?? null,
                'accent_color' => $data['accent_color'] ?? '#c19b77',
                'is_active'    => $request->boolean('is_active'),
            ]);

            $this->syncItems(
                $showcase,
                $data['item_types'] ?? [],
                $data['item_ids'] ?? [],
                $data['labels'] ?? []
            );
        });

        return redirect()->route('qrmenus.showcases.index')
            ->with('success', 'Vitrin güncellendi.');
    }

    public function destroy(MenuShowcase $showcase)
    {
        $showcase->delete();
        return back()->with('success', 'Vitrin silindi.');
    }

    // -------------------------------------------------------------------------

    private function validatedData(Request $request, ?MenuShowcase $showcase = null): array
    {
        $slugRule = 'nullable|string|max:80|unique:menu_showcases,slug';
        if ($showcase) {
            $slugRule .= ',' . $showcase->id;
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:120',
            'slug'           => $slugRule,
            'subtitle'       => 'nullable|string|max:300',
            'accent_color'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'      => 'boolean',
            'item_types'     => 'nullable|array|max:100',
            'item_types.*'   => 'required|in:menu,survey',
            'item_ids'       => 'nullable|array|max:100',
            'item_ids.*'     => 'required|integer|min:1',
            'labels'         => 'nullable|array|max:100',
            'labels.*'       => 'nullable|string|max:80',
        ]);

        $validator->after(function ($validator) use ($request) {
            $types = array_values($request->input('item_types', []));
            $ids = array_values($request->input('item_ids', []));

            if (count($types) !== count($ids)) {
                $validator->errors()->add('item_ids', 'Seçili içerik bilgileri eksik veya hatalı.');
                return;
            }

            $menuIds = [];
            $surveyIds = [];
            foreach ($types as $index => $type) {
                if (!isset($ids[$index]) || !is_numeric($ids[$index])) {
                    continue;
                }

                if ($type === 'menu') {
                    $menuIds[] = (int) $ids[$index];
                } elseif ($type === 'survey') {
                    $surveyIds[] = (int) $ids[$index];
                }
            }

            $existingMenuIds = QrMenu::whereIn('id', array_unique($menuIds))->pluck('id')->all();
            $existingSurveyIds = Survey::whereIn('id', array_unique($surveyIds))->pluck('id')->all();

            if (array_diff(array_unique($menuIds), $existingMenuIds)) {
                $validator->errors()->add('item_ids', 'Seçilen QR menülerden biri artık mevcut değil.');
            }

            if (array_diff(array_unique($surveyIds), $existingSurveyIds)) {
                $validator->errors()->add('item_ids', 'Seçilen anketlerden biri artık mevcut değil.');
            }
        });

        return $validator->validate();
    }

    private function normaliseLegacyMenuInput(Request $request): void
    {
        if ($request->has('item_types') || !$request->has('menus')) {
            return;
        }

        $menuIds = array_values((array) $request->input('menus', []));
        $request->merge([
            'item_types' => array_fill(0, count($menuIds), 'menu'),
            'item_ids'   => $menuIds,
        ]);
    }

    private function syncItems(
        MenuShowcase $showcase,
        array $itemTypes,
        array $itemIds,
        array $labels
    ): void
    {
        $showcase->items()->delete();

        foreach (array_values($itemIds) as $idx => $itemId) {
            $type = $itemTypes[$idx] ?? null;
            if (!in_array($type, ['menu', 'survey'], true)) {
                continue;
            }

            MenuShowcaseItem::create([
                'showcase_id'  => $showcase->id,
                'qr_menu_id'   => $type === 'menu' ? (int) $itemId : null,
                'survey_id'    => $type === 'survey' ? (int) $itemId : null,
                'label'        => $labels[$idx] ?? null,
                'sort_order'   => $idx,
            ]);
        }
    }
}
