<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\FoodCategory;
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

        $category->load(['items.foodProduct.foodCategory']);

        $subHeadingMap = $this->resolveLibrarySubHeadingMap($qrmenu, $category);

        if ($subHeadingMap === []) {
            return redirect()->route('qrmenus.show', $qrmenu)
                ->with('warning', 'Bu kategori icin kutuphane alt grup eslesmesi bulunamadi.');
        }

        $products = FoodProduct::with('foodCategory')
            ->where('is_active', true)
            ->whereIn('food_category_id', array_keys($subHeadingMap))
            ->when($qrmenu->branch_id, fn($q) => $q->where('branch_id', $qrmenu->branch_id))
            ->orderBy('food_category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            return redirect()->route('qrmenus.show', $qrmenu)
                ->with('warning', 'Kutuphane tarafinda senkronlanacak aktif urun bulunamadi.');
        }

        $stats = DB::transaction(function () use ($category, $products, $subHeadingMap) {
            $existingItems = $category->items()->with('foodProduct.foodCategory')->orderBy('sort_order')->get();

            $existingByProductId = [];
            $duplicateIds = [];

            foreach ($existingItems as $existingItem) {
                if (!$existingItem->food_product_id) {
                    continue;
                }

                if (!isset($existingByProductId[$existingItem->food_product_id])) {
                    $existingByProductId[$existingItem->food_product_id] = $existingItem;
                    continue;
                }

                $duplicateIds[] = $existingItem->id;
            }

            $orphanByTitle = [];
            foreach ($existingItems as $existingItem) {
                if ($existingItem->food_product_id) {
                    continue;
                }

                $titleKey = $this->normalizeSyncKey($existingItem->getTitle('tr'));
                if ($titleKey !== '' && !isset($orphanByTitle[$titleKey])) {
                    $orphanByTitle[$titleKey] = $existingItem;
                }
            }

            $orderedItems = [];
            $seenItemIds = [];
            $created = 0;
            $updated = 0;

            foreach ($products as $product) {
                $subHeading = $subHeadingMap[$product->food_category_id] ?? null;
                if (!$subHeading) {
                    continue;
                }

                $item = $existingByProductId[$product->id] ?? null;

                if (!$item) {
                    $titleKey = $this->normalizeSyncKey($product->getTitle('tr'));
                    if ($titleKey !== '' && isset($orphanByTitle[$titleKey])) {
                        $item = $orphanByTitle[$titleKey];
                        unset($orphanByTitle[$titleKey]);
                    }
                }

                $payload = [
                    'food_product_id' => $product->id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                    'price_glass' => $product->price_glass,
                    'price_bottle' => $product->price_bottle,
                    'cl_glass' => $product->cl_glass,
                    'cl_bottle' => $product->cl_bottle,
                    'sub_heading' => $subHeading,
                    'badges' => $product->badges,
                    'is_active' => true,
                ];

                if ($item) {
                    $rawImage = (string) $item->getRawOriginal('image');
                    if ($rawImage === '' || !str_starts_with($rawImage, 'qrmenu/items/')) {
                        $payload['image'] = $product->getRawOriginal('image');
                    }

                    $item->fill($payload);
                    if ($item->isDirty()) {
                        $item->save();
                        $updated++;
                    }
                } else {
                    $item = $category->items()->create($payload + [
                        'image' => $product->getRawOriginal('image'),
                        'is_featured' => false,
                        'sort_order' => 0,
                    ]);
                    $created++;
                }

                $orderedItems[] = $item;
                $seenItemIds[$item->id] = true;
            }

            $remainingItems = $category->items()
                ->whereNotIn('id', array_keys($seenItemIds))
                ->orderBy('sort_order')
                ->get();

            $sortOrder = 0;
            foreach (array_merge($orderedItems, $remainingItems->all()) as $orderedItem) {
                if ((int) $orderedItem->sort_order !== $sortOrder) {
                    $orderedItem->update(['sort_order' => $sortOrder]);
                }
                $sortOrder++;
            }

            if ($duplicateIds !== []) {
                QrMenuItem::whereIn('id', $duplicateIds)->delete();
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'duplicates_removed' => count($duplicateIds),
            ];
        });

        $message = $stats['created'] . ' yeni urun eklendi, ' . $stats['updated'] . ' urun guncellendi.';
        if ($stats['duplicates_removed'] > 0) {
            $message .= ' ' . $stats['duplicates_removed'] . ' mukerrer kayit temizlendi.';
        }

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

    private function resolveLibrarySubHeadingMap(QrMenu $qrmenu, QrMenuCategory $category): array
    {
        $map = [];

        foreach ($category->items as $item) {
            $foodCategoryId = $item->foodProduct?->food_category_id;
            $subHeading = $item->sub_heading;

            if (!$foodCategoryId || !$this->subHeadingHasText($subHeading) || isset($map[$foodCategoryId])) {
                continue;
            }

            $map[$foodCategoryId] = $subHeading;
        }

        if (!empty($category->sub_headings)) {
            $libraryCategories = FoodCategory::query()
                ->where('is_active', true)
                ->when($qrmenu->branch_id, fn($q) => $q->where('branch_id', $qrmenu->branch_id))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ((array) $category->sub_headings as $subHeading) {
                $subHeadingKey = $this->normalizeSyncKey($subHeading['tr'] ?? '');
                if ($subHeadingKey === '') {
                    continue;
                }

                $alreadyMapped = collect($map)->contains(function ($mappedSubHeading) use ($subHeadingKey) {
                    return $this->normalizeSyncKey($mappedSubHeading['tr'] ?? '') === $subHeadingKey;
                });

                if ($alreadyMapped) {
                    continue;
                }

                $matchedCategory = $libraryCategories->first(function ($libraryCategory) use ($subHeadingKey) {
                    return $this->normalizeSyncKey($libraryCategory->getTitle('tr')) === $subHeadingKey;
                });

                if ($matchedCategory) {
                    $map[$matchedCategory->id] = $subHeading;
                }
            }
        }

        return $map;
    }

    private function normalizeSyncKey(?string $value): string
    {
        return Str::slug(trim((string) $value));
    }

    private function subHeadingHasText($subHeading): bool
    {
        foreach ((array) $subHeading as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
