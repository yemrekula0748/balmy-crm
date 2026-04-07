<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\FoodCategory;
use App\Models\FoodProduct;
use App\Models\Printer;
use App\Models\QrMenu;
use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FoodLibraryController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'food_library',
            ['index', 'products', 'apiProducts'],
            [],
            ['createCategory', 'storeCategory', 'createProduct', 'storeProduct'],
            ['editCategory', 'updateCategory', 'editProduct', 'updateProduct'],
            ['destroyCategory', 'destroyProduct']
        );
    }

    /* ═══════════════════════════════════════
     |  KATEGORİLER
     ═══════════════════════════════════════ */

    public function index(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $branchId  = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);

        $categories = FoodCategory::with(['branch', 'products'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('branch_id', $branchIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $page_title = 'Yemek Kütüphanesi';
        return view('modules.food_library.index', compact(
            'categories', 'branches', 'branchId', 'page_title'
        ));
    }

    public function createCategory()
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yeni Kategori';
        return view('modules.food_library.category_form', compact('branches', 'page_title'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'branch_id'  => 'nullable|exists:branches,id',
            'title_tr'   => 'required|string|max:120',
            'icon'       => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
        ]);

        $title = [];
        foreach (['tr','en','de','fr','ar','ru'] as $lang) {
            $val = $request->input("title_{$lang}", '');
            if ($val !== '') $title[$lang] = $val;
        }

        FoodCategory::create([
            'branch_id'  => $request->branch_id,
            'title'      => $title,
            'icon'       => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => true,
        ]);

        return redirect()->route('food-library.index')
            ->with('success', 'Kategori oluşturuldu.');
    }

    public function editCategory(FoodCategory $category)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Kategori Düzenle';
        return view('modules.food_library.category_form', compact('category', 'branches', 'page_title'));
    }

    public function updateCategory(Request $request, FoodCategory $category)
    {
        $request->validate([
            'branch_id'  => 'nullable|exists:branches,id',
            'title_tr'   => 'required|string|max:120',
            'icon'       => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
        ]);

        $title = $category->title ?? [];
        foreach (['tr','en','de','fr','ar','ru'] as $lang) {
            $val = $request->input("title_{$lang}", '');
            if ($val !== '') {
                $title[$lang] = $val;
            } else {
                unset($title[$lang]);
            }
        }

        $category->update([
            'branch_id'  => $request->branch_id,
            'title'      => $title,
            'icon'       => $request->icon,
            'sort_order' => $request->sort_order ?? $category->sort_order,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('food-library.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function importCategoriesJson(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        $decoded = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->withErrors(['json_data' => 'Geçersiz JSON formatı.'])->withInput();
        }

        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();

        $added   = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($decoded as $index => $item) {
            $rowNum = $index + 1;

            if (empty($item['title']['tr'])) {
                $errors[] = "Satır {$rowNum}: 'title.tr' (Türkçe ad) zorunludur.";
                continue;
            }

            $branchId = $item['branch_id'] ?? null;

            // Kullanıcı bu şubeye erişebilmeli
            if ($branchId && !in_array($branchId, $branchIds)) {
                $errors[] = "Satır {$rowNum}: branch_id={$branchId} için erişim yetkiniz yok.";
                continue;
            }

            $titleTr = trim($item['title']['tr']);

            // Aynı şube + Türkçe ad kombinasyonu var mı?
            $exists = FoodCategory::where('branch_id', $branchId)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$titleTr])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $title = [];
            foreach (['tr', 'en', 'de', 'fr', 'ar', 'ru'] as $lang) {
                $val = trim($item['title'][$lang] ?? '');
                if ($val !== '') $title[$lang] = $val;
            }

            FoodCategory::create([
                'branch_id'  => $branchId,
                'title'      => $title,
                'icon'       => $item['icon'] ?? null,
                'sort_order' => $item['sort_order'] ?? 0,
                'is_active'  => true,
            ]);

            $added++;
        }

        $msg = "{$added} kategori eklendi, {$skipped} kategori atlandı (zaten mevcut).";
        if (!empty($errors)) {
            $msg .= ' Hatalar: ' . implode(' | ', $errors);
        }

        return redirect()->route('food-library.categories.create')
            ->with($added > 0 || $skipped > 0 ? 'success' : 'warning', $msg);
    }

    public function destroyCategory(FoodCategory $category)
    {
        // Bağlı ürünlerin kategori_id'sini null yap
        $category->products()->update(['food_category_id' => null]);
        $category->delete();
        return back()->with('success', 'Kategori silindi.');
    }

    public function destroyCategoryWithProducts(FoodCategory $category)
    {
        $productCount = $category->products()->count();
        $category->products()->delete();
        $category->delete();
        return back()->with('success', "Kategori ve {$productCount} ürün kalıcı olarak silindi.");
    }

    /* ═══════════════════════════════════════
     |  ÜRÜNLER
     ═══════════════════════════════════════ */

    public function products(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $branchId  = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);

        $categories = FoodCategory::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('branch_id', $branchIds)
            ->orderBy('sort_order')->get();

        $products = FoodProduct::with(['foodCategory', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('branch_id', $branchIds)
            ->when($request->category_id, fn($q) => $q->where('food_category_id', $request->category_id))
            ->when($request->search, function ($q) use ($request) {
                $s = strtolower($request->search);
                $q->where(function ($q2) use ($s) {
                    $q2->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr'))) LIKE ?", ["%{$s}%"])
                       ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ["%{$s}%"]);
                });
            })
            ->orderBy('food_category_id')
            ->orderBy('sort_order')
            ->paginate(24);

        $page_title = 'Ürünler';
        return view('modules.food_library.products', compact(
            'products', 'categories', 'branches', 'branchId', 'page_title'
        ));
    }

    public function createProduct()
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $categories = FoodCategory::whereIn('branch_id', $branchIds)->orderBy('sort_order')->get();
        $printers   = Printer::whereIn('branch_id', $branchIds)->where('is_active', true)->orderBy('name')->get();
        $badgeOptions = FoodProduct::BADGE_OPTIONS;
        $optionTypes  = FoodProduct::OPTION_TYPES;
        $page_title = 'Yeni Ürün';
        return view('modules.food_library.product_form', compact(
            'branches', 'categories', 'printers', 'badgeOptions', 'optionTypes', 'page_title'
        ));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'branch_id'        => 'nullable|exists:branches,id',
            'food_category_id' => 'nullable|exists:food_categories,id',
            'title_tr'         => 'required|string|max:200',
            'price'            => 'nullable|numeric|min:0',
            'sort_order'       => 'nullable|integer',
        ]);

        $title = [];
        $description = [];
        $ingredients = [];
        foreach (['tr','en','de','fr','ar','ru'] as $lang) {
            $t = $request->input("title_{$lang}", '');
            $d = $request->input("description_{$lang}", '');
            $ing = $request->input("ingredients_{$lang}", '');
            if ($t !== '') $title[$lang] = $t;
            if ($d !== '') $description[$lang] = $d;
            if ($ing !== '') $ingredients[$lang] = $ing;
        }

        // Dinamik opsiyonlar (çok dilli etiket)
        $options = [];
        $optLabelsTr = $request->input('opt_label_tr', []);
        $optLabelsEn = $request->input('opt_label_en', []);
        $optLabelsDe = $request->input('opt_label_de', []);
        $optLabelsRu = $request->input('opt_label_ru', []);
        $optTypes    = $request->input('opt_type', []);
        $optValues   = $request->input('opt_value', []);
        foreach ($optLabelsTr as $i => $tr) {
            $label = array_filter([
                'tr' => trim($tr ?? ''),
                'en' => trim($optLabelsEn[$i] ?? ''),
                'de' => trim($optLabelsDe[$i] ?? ''),
                'ru' => trim($optLabelsRu[$i] ?? ''),
            ]);
            if (empty($label)) continue;
            $options[] = [
                'label' => $label,
                'type'  => $optTypes[$i] ?? 'text',
                'value' => $optValues[$i] ?? '',
            ];
        }

        $product = FoodProduct::create([
            'branch_id'        => $request->branch_id,
            'food_category_id' => $request->food_category_id,
            'printer_id'       => $request->printer_id ?: null,
            'title'            => $title,
            'description'      => $description ?: null,
            'ingredients'      => $ingredients ?: null,
            'price'            => $request->price,
            'badges'           => $request->badges ?: null,
            'allergens'        => $request->allergens ?: null,
            'options'          => $options ?: null,
            'calories'         => $request->calories ?: null,
            'protein'          => $request->protein ?: null,
            'carbs'            => $request->carbs ?: null,
            'fat'              => $request->fat ?: null,
            'is_active'        => true,
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        if ($request->hasFile('image')) {
            $product->update(['image' => $request->file('image')->store('food_library', 'public')]);
        }

        return redirect()->route('food-library.products')
            ->with('success', 'Ürün oluşturuldu.');
    }

    public function editProduct(FoodProduct $product)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $categories = FoodCategory::whereIn('branch_id', $branchIds)->orderBy('sort_order')->get();
        $printers   = Printer::whereIn('branch_id', $branchIds)->where('is_active', true)->orderBy('name')->get();
        $badgeOptions = FoodProduct::BADGE_OPTIONS;
        $optionTypes  = FoodProduct::OPTION_TYPES;
        $page_title = 'Ürün Düzenle';
        return view('modules.food_library.product_form', compact(
            'product', 'branches', 'categories', 'printers', 'badgeOptions', 'optionTypes', 'page_title'
        ));
    }

    public function updateProduct(Request $request, FoodProduct $product)
    {
        $request->validate([
            'branch_id'        => 'nullable|exists:branches,id',
            'food_category_id' => 'nullable|exists:food_categories,id',
            'title_tr'         => 'required|string|max:200',
            'price'            => 'nullable|numeric|min:0',
            'sort_order'       => 'nullable|integer',
        ]);

        $title = $product->title ?? [];
        $description = $product->description ?? [];
        $ingredients = $product->ingredients ?? [];
        foreach (['tr','en','de','fr','ar','ru'] as $lang) {
            $t   = $request->input("title_{$lang}", '');
            $d   = $request->input("description_{$lang}", '');
            $ing = $request->input("ingredients_{$lang}", '');
            if ($t   !== '') { $title[$lang]       = $t;   } else { unset($title[$lang]); }
            if ($d   !== '') { $description[$lang] = $d;   } else { unset($description[$lang]); }
            if ($ing !== '') { $ingredients[$lang] = $ing; } else { unset($ingredients[$lang]); }
        }

        $options = [];
        $optLabelsTr = $request->input('opt_label_tr', []);
        $optLabelsEn = $request->input('opt_label_en', []);
        $optLabelsDe = $request->input('opt_label_de', []);
        $optLabelsRu = $request->input('opt_label_ru', []);
        $optTypes    = $request->input('opt_type', []);
        $optValues   = $request->input('opt_value', []);
        foreach ($optLabelsTr as $i => $tr) {
            $label = array_filter([
                'tr' => trim($tr ?? ''),
                'en' => trim($optLabelsEn[$i] ?? ''),
                'de' => trim($optLabelsDe[$i] ?? ''),
                'ru' => trim($optLabelsRu[$i] ?? ''),
            ]);
            if (empty($label)) continue;
            $options[] = [
                'label' => $label,
                'type'  => $optTypes[$i] ?? 'text',
                'value' => $optValues[$i] ?? '',
            ];
        }

        $product->update([
            'branch_id'        => $request->branch_id,
            'food_category_id' => $request->food_category_id,
            'printer_id'       => $request->printer_id ?: null,
            'title'            => $title,
            'description'      => $description ?: null,
            'ingredients'      => $ingredients ?: null,
            'price'            => $request->price,
            'badges'           => $request->badges ?: null,
            'allergens'        => $request->allergens ?: null,
            'options'          => $options ?: null,
            'calories'         => $request->calories ?: null,
            'protein'          => $request->protein ?: null,
            'carbs'            => $request->carbs ?: null,
            'fat'              => $request->fat ?: null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => $request->sort_order ?? $product->sort_order,
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $product->update(['image' => $request->file('image')->store('food_library', 'public')]);
        }

        return redirect()->route('food-library.products')
            ->with('success', 'Ürün güncellendi.');
    }

    public function importProductsJson(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        $decoded = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->withErrors(['json_data' => 'Geçersiz JSON formatı.'])->withInput();
        }

        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();

        $added   = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($decoded as $index => $item) {
            $rowNum = $index + 1;

            if (empty($item['title']['tr'])) {
                $errors[] = "Satır {$rowNum}: 'title.tr' (Türkçe ad) zorunludur.";
                continue;
            }

            $branchId = $item['branch_id'] ?? null;

            if ($branchId && !in_array($branchId, $branchIds)) {
                $errors[] = "Satır {$rowNum}: branch_id={$branchId} için erişim yetkiniz yok.";
                continue;
            }

            $titleTr = trim($item['title']['tr']);

            // Aynı şube + Türkçe ad var mı?
            $exists = FoodProduct::where('branch_id', $branchId)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$titleTr])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $title = []; $description = []; $ingredients = [];
            foreach (['tr','en','de','fr','ar','ru'] as $lang) {
                $t   = trim($item['title'][$lang] ?? '');
                $d   = trim($item['description'][$lang] ?? '');
                $ing = trim($item['ingredients'][$lang] ?? '');
                if ($t   !== '') $title[$lang]       = $t;
                if ($d   !== '') $description[$lang] = $d;
                if ($ing !== '') $ingredients[$lang] = $ing;
            }

            // Validate opsiyonel ilişki id'leri
            $categoryId = $item['food_category_id'] ?? null;
            $printerId  = $item['printer_id'] ?? null;

            FoodProduct::create([
                'branch_id'        => $branchId,
                'food_category_id' => $categoryId,
                'printer_id'       => $printerId,
                'title'            => $title,
                'description'      => $description ?: null,
                'ingredients'      => $ingredients ?: null,
                'price'            => $item['price'] ?? null,
                'badges'           => $item['badges'] ?? null,
                'allergens'        => $item['allergens'] ?? null,
                'options'          => $item['options'] ?? null,
                'calories'         => $item['calories'] ?? null,
                'protein'          => $item['protein'] ?? null,
                'carbs'            => $item['carbs'] ?? null,
                'fat'              => $item['fat'] ?? null,
                'sort_order'       => $item['sort_order'] ?? 0,
                'is_active'        => true,
            ]);

            $added++;
        }

        $msg = "{$added} ürün eklendi, {$skipped} ürün atlandı (zaten mevcut).";
        if (!empty($errors)) {
            $msg .= ' Hatalar: ' . implode(' | ', $errors);
        }

        return redirect()->route('food-library.product.create')
            ->with($added > 0 || $skipped > 0 ? 'success' : 'warning', $msg);
    }

    public function destroyProduct(FoodProduct $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Ürün silindi.');
    }

    /* ═══════════════════════════════════════
     |  API — Menü entegrasyonu için
     ═══════════════════════════════════════ */

    /** Belirli şube+kategori için ürünleri JSON döndür (modal için) */
    public function apiProducts(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();

        $products = FoodProduct::with('foodCategory')
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->category_id, fn($q) => $q->where('food_category_id', $request->category_id))
            ->when($request->search, function ($q) use ($request) {
                $s = strtolower($request->search);
                $q->where(function ($q2) use ($s) {
                    $q2->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr'))) LIKE ?", ["%{$s}%"])
                       ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ["%{$s}%"]);
                });
            })
            ->orderBy('food_category_id')
            ->orderBy('sort_order')
            ->get();

        $categories = $products->pluck('foodCategory')->filter()->unique('id')->map(fn($c) => [
            'id'      => $c->id,
            'icon'    => $c->icon,
            'title_tr'=> $c->getTitle('tr'),
        ])->values();

        $mapped = $products->map(fn($p) => [
            'id'            => $p->id,
            'title_tr'      => $p->getTitle('tr'),
            'price'         => $p->price,
            'image_url'     => $p->image ? asset('uploads/'.$p->image) : null,
            'badges'        => $p->badges ?? [],
            'category_name' => $p->foodCategory?->getTitle('tr'),
        ]);

        return response()->json(['products' => $mapped, 'categories' => $categories]);
    }

    public function apiProduct(Request $request, FoodProduct $product)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();

        if (!in_array($product->branch_id, $branchIds)) {
            abort(403);
        }

        return response()->json([
            'id'               => $product->id,
            'branch_id'        => $product->branch_id,
            'food_category_id' => $product->food_category_id,
            'printer_id'       => $product->printer_id,
            'title'            => $product->title ?? [],
            'description'      => $product->description ?? [],
            'ingredients'      => $product->ingredients ?? [],
            'price'            => $product->price,
            'badges'           => $product->badges ?? [],
            'allergens'        => $product->allergens ?? [],
            'options'          => $product->options ?? [],
            'calories'         => $product->calories,
            'protein'          => $product->protein,
            'carbs'            => $product->carbs,
            'fat'              => $product->fat,
            'sort_order'       => $product->sort_order,
        ]);
    }
}
