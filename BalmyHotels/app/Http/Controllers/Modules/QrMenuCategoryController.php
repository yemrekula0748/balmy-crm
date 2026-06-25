<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\FoodProduct;
use App\Models\QrMenu;
use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrMenuCategoryController extends BaseModuleController
{
    private const DEFAULT_DRINK_SOURCE_MENU_ID = 18;

    public function __construct()
    {
        $this->requirePermission(
            'qrmenus',
            [],
            [],
            ['createCategory', 'storeCategory', 'createItem', 'storeItem'],
            ['editCategory', 'updateCategory', 'editItem', 'updateItem', 'syncCategoryFromLibrary'],
            ['destroyCategory', 'destroyItem']
        );
    }

    public function createCategory(QrMenu $qrmenu)
    {
        $qrmenu->load('languages');
        $menu = $qrmenu;
        $page_title = 'Kategori Ekle';

        return view('modules.qrmenu.category_form', compact('menu', 'page_title'));
    }

    public function storeCategory(Request $request, QrMenu $qrmenu)
    {
        $qrmenu->load('languages');
        $request->validate([
            'icon' => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        $sub_headings = [];
        foreach ((array) $request->input('sub_headings', []) as $sh) {
            $row = array_filter(array_map('trim', $sh));
            if (!empty($row['tr'] ?? '')) {
                $sub_headings[] = $row;
            }
        }

        $category = $qrmenu->categories()->create([
            'title' => $title,
            'description' => array_filter($description) ?: null,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
            'sub_headings' => $sub_headings ?: null,
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
        $menu = $qrmenu;
        $page_title = 'Kategori Duzenle';

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

        $sub_headings = [];
        foreach ((array) $request->input('sub_headings', []) as $sh) {
            $row = array_filter(array_map('trim', $sh));
            if (!empty($row['tr'] ?? '')) {
                $sub_headings[] = $row;
            }
        }

        $category->update([
            'title' => $title,
            'description' => array_filter($description) ?: null,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? $category->sort_order,
            'is_active' => $request->boolean('is_active', true),
            'sub_headings' => $sub_headings ?: null,
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $category->update(['image' => $request->file('image')->store('qrmenu/categories', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Kategori guncellendi.');
    }

    public function destroyCategory(QrMenu $qrmenu, QrMenuCategory $category)
    {
        $category->delete();

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Kategori silindi.');
    }

    public function createItem(QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');
        $menu = $qrmenu;
        $badgeOptions = QrMenuItem::BADGE_OPTIONS;
        $page_title = 'Urun Ekle';

        return view('modules.qrmenu.item_form', compact('menu', 'category', 'badgeOptions', 'page_title'));
    }

    public function storeItem(Request $request, QrMenu $qrmenu, QrMenuCategory $category)
    {
        $qrmenu->load('languages');
        $request->validate([
            'price' => 'nullable|numeric|min:0',
            'price_glass' => 'nullable|numeric|min:0',
            'price_bottle' => 'nullable|numeric|min:0',
            'cl_glass' => 'nullable|integer|min:0',
            'cl_bottle' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        if (array_filter($title) === []) {
            return back()->withErrors(['title' => 'En az bir dilde urun adi girilmelidir.'])->withInput();
        }

        $sub_heading_raw = $request->input('sub_heading');
        $sub_heading = ($sub_heading_raw && $sub_heading_raw !== '') ? json_decode($sub_heading_raw, true) : null;

        $item = $category->items()->create([
            'title' => $title,
            'description' => array_filter($description) ?: null,
            'sub_heading' => $sub_heading,
            'price' => $request->price,
            'price_glass' => $request->price_glass ?: null,
            'price_bottle' => $request->price_bottle ?: null,
            'cl_glass' => $request->cl_glass ?: null,
            'cl_bottle' => $request->cl_bottle ?: null,
            'is_active' => true,
            'is_featured' => $request->boolean('is_featured'),
            'badges' => $request->badges ?: null,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        if ($request->hasFile('image')) {
            $item->update(['image' => $request->file('image')->store('qrmenu/items', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Urun eklendi.');
    }

    public function editItem(QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $qrmenu->load('languages');
        $menu = $qrmenu;
        $badgeOptions = QrMenuItem::BADGE_OPTIONS;
        $page_title = 'Urun Duzenle';

        return view('modules.qrmenu.item_form', compact('menu', 'category', 'item', 'badgeOptions', 'page_title'));
    }

    public function updateItem(Request $request, QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $qrmenu->load('languages');
        $request->validate([
            'price' => 'nullable|numeric|min:0',
            'price_glass' => 'nullable|numeric|min:0',
            'price_bottle' => 'nullable|numeric|min:0',
            'cl_glass' => 'nullable|integer|min:0',
            'cl_bottle' => 'nullable|integer|min:0',
        ]);

        $title = [];
        $description = [];
        foreach ($qrmenu->languages as $lang) {
            $title[$lang->code] = $request->input("title_{$lang->code}", '');
            $description[$lang->code] = $request->input("description_{$lang->code}", '');
        }

        $sub_heading_raw = $request->input('sub_heading');
        $sub_heading = ($sub_heading_raw && $sub_heading_raw !== '') ? json_decode($sub_heading_raw, true) : null;

        $item->update([
            'title' => $title,
            'description' => array_filter($description) ?: null,
            'sub_heading' => $sub_heading,
            'price' => $request->price,
            'price_glass' => $request->price_glass ?: null,
            'price_bottle' => $request->price_bottle ?: null,
            'cl_glass' => $request->cl_glass ?: null,
            'cl_bottle' => $request->cl_bottle ?: null,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'badges' => $request->badges ?: null,
            'sort_order' => $request->sort_order ?? $item->sort_order,
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->update(['image' => $request->file('image')->store('qrmenu/items', 'public')]);
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Urun guncellendi.');
    }

    public function destroyItem(QrMenu $qrmenu, QrMenuCategory $category, QrMenuItem $item)
    {
        $item->delete();

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', 'Urun silindi.');
    }

    public function syncCategoryFromLibrary(QrMenu $qrmenu, QrMenuCategory $category)
    {
        abort_if($category->qr_menu_id !== $qrmenu->id, 404);

        $sourceMenuId = (int) request('source_menu_id', self::DEFAULT_DRINK_SOURCE_MENU_ID);

        $sourceMenu = QrMenu::with([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
            },
            'categories.items' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
            },
        ])->find($sourceMenuId);

        if (!$sourceMenu) {
            return redirect()->route('qrmenus.show', $qrmenu)
                ->with('warning', 'Kaynak icecek menusu bulunamadi. Beklenen menu ID: ' . $sourceMenuId);
        }

        $sourceCategories = $this->resolveSourceCategories($sourceMenu);

        if ($sourceCategories->isEmpty()) {
            return redirect()->route('qrmenus.show', $qrmenu)
                ->with('warning', 'Kaynak menude aktarilacak aktif kategori bulunamadi. Kaynak menu ID: ' . $sourceMenuId);
        }

        $stats = DB::transaction(function () use ($category, $sourceCategories, $sourceMenuId) {
            $category->update([
                'sub_headings' => $sourceCategories
                    ->map(fn ($sourceCategory) => $sourceCategory->title)
                    ->values()
                    ->all(),
            ]);

            $existingItems = $category->items()->orderBy('sort_order')->get();
            $existingByProductId = [];
            $existingByTitle = [];

            foreach ($existingItems as $existingItem) {
                if ($existingItem->food_product_id && !isset($existingByProductId[$existingItem->food_product_id])) {
                    $existingByProductId[$existingItem->food_product_id] = $existingItem;
                }

                $titleKey = $this->normalizeSyncKey($existingItem->getTitle('tr'));
                if ($titleKey !== '' && !isset($existingByTitle[$titleKey])) {
                    $existingByTitle[$titleKey] = $existingItem;
                }
            }

            $matchedIds = [];
            $created = 0;
            $updated = 0;
            $sortOrder = 0;
            $sourceItemCount = 0;

            foreach ($sourceCategories as $sourceCategory) {
                foreach ($sourceCategory->items as $sourceItem) {
                    $sourceItemCount++;
                    $targetItem = null;

                    if ($sourceItem->food_product_id && isset($existingByProductId[$sourceItem->food_product_id])) {
                        $targetItem = $existingByProductId[$sourceItem->food_product_id];
                    }

                    if (!$targetItem) {
                        $titleKey = $this->normalizeSyncKey($sourceItem->getTitle('tr'));
                        if ($titleKey !== '' && isset($existingByTitle[$titleKey])) {
                            $targetItem = $existingByTitle[$titleKey];
                        }
                    }

                    $payload = [
                        'food_product_id' => $sourceItem->food_product_id,
                        'title' => $sourceItem->title,
                        'description' => $sourceItem->description,
                        'price' => $sourceItem->price,
                        'price_override' => $sourceItem->price_override,
                        'image' => $sourceItem->getRawOriginal('image'),
                        'is_active' => $sourceItem->is_active,
                        'is_featured' => $sourceItem->is_featured,
                        'badges' => $sourceItem->badges,
                        'sort_order' => $sortOrder,
                        'sub_heading' => $sourceCategory->title,
                        'price_glass' => $sourceItem->price_glass,
                        'price_bottle' => $sourceItem->price_bottle,
                        'cl_glass' => $sourceItem->cl_glass,
                        'cl_bottle' => $sourceItem->cl_bottle,
                    ];

                    if ($targetItem) {
                        $targetItem->fill($payload);
                        if ($targetItem->isDirty()) {
                            $targetItem->save();
                            $updated++;
                        }
                    } else {
                        $targetItem = $category->items()->create($payload);
                        $created++;
                    }

                    $matchedIds[] = $targetItem->id;
                    $sortOrder++;
                }
            }

            $deleted = 0;
            if ($matchedIds !== []) {
                $deleted = $category->items()->whereNotIn('id', $matchedIds)->delete();
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'deleted' => $deleted,
                'source_menu_id' => $sourceMenuId,
                'source_category_count' => $sourceCategories->count(),
                'source_item_count' => $sourceItemCount,
            ];
        });

        $message = 'Kaynak menu #' . $stats['source_menu_id'] . ' icindeki ' . $stats['source_category_count'] . ' kategori ve ' . $stats['source_item_count'] . ' urun baz alinarak ';
        $message .= $stats['created'] . ' urun eklendi, ' . $stats['updated'] . ' urun guncellendi';
        if ($stats['deleted'] > 0) {
            $message .= ', ' . $stats['deleted'] . ' eski urun kaldirildi';
        }
        $message .= '.';

        return redirect()->route('qrmenus.show', $qrmenu)->with('success', $message);
    }

    public function addFromLibrary(Request $request, QrMenu $qrmenu, QrMenuCategory $category)
    {
        $request->validate([
            'food_product_ids' => 'required|array|min:1',
            'food_product_ids.*' => 'required|integer|exists:food_products,id',
            'price_override' => 'nullable|numeric|min:0',
            'sub_heading' => 'nullable|string',
        ]);

        $sub_heading_raw = $request->input('sub_heading');
        $sub_heading = ($sub_heading_raw && $sub_heading_raw !== '') ? json_decode($sub_heading_raw, true) : null;

        $added = 0;
        foreach ($request->food_product_ids as $productId) {
            $product = FoodProduct::find((int) $productId);
            if (!$product) {
                continue;
            }

            $category->items()->create([
                'food_product_id' => $product->id,
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'price_override' => $request->price_override,
                'price_glass' => $product->price_glass,
                'price_bottle' => $product->price_bottle,
                'cl_glass' => $product->cl_glass,
                'cl_bottle' => $product->cl_bottle,
                'sub_heading' => $sub_heading,
                'image' => $product->image,
                'badges' => $product->badges,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $category->items()->count(),
            ]);
            $added++;
        }

        return redirect()->route('qrmenus.show', $qrmenu)
            ->with('success', $added . ' urun menuye eklendi.');
    }

    private function resolveSourceCategories(QrMenu $sourceMenu)
    {
        return $sourceMenu->categories
            ->filter(fn ($sourceCategory) => $sourceCategory->items->isNotEmpty())
            ->values();
    }

    private function normalizeSyncKey(?string $value): string
    {
        return Str::slug(trim((string) $value));
    }
}
