<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FoodLabel;
use Illuminate\Http\Request;

class FoodLabelController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'food_labels',
            ['index'],
            ['printSingle'],
            ['create', 'store', 'printBulk'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $query = FoodLabel::with('branch')
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.tr') LIKE ?", ["%$search%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%$search%"]);
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $labels   = $query->get();
        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yemek İsimlik';

        return view('modules.food_labels.index', compact('labels', 'branches', 'page_title'));
    }

    public function create()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yeni Yemek İsimlik';

        return view('modules.food_labels.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $name = array_filter($request->name ?? []);
        if (empty($name)) {
            return back()->withInput()->withErrors(['name' => 'En az bir dilde yemek adı giriniz.']);
        }

        FoodLabel::create([
            'branch_id'     => $request->branch_id,
            'created_by'    => auth()->id(),
            'name'          => $request->name ?? [],
            'description'   => $request->description ?? [],
            'ingredients'   => $this->parseIngredients($request),
            'calories'      => $request->calories ?: null,
            'allergens'     => $request->allergens ?? [],
            'category'      => $request->category ?: null,
            'is_vegan'      => $request->boolean('is_vegan'),
            'is_vegetarian' => $request->boolean('is_vegetarian'),
            'is_halal'      => $request->boolean('is_halal'),
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int)$request->sort_order,
        ]);

        return redirect()->route('food-labels.index')
            ->with('success', 'Yemek isimlik oluşturuldu.');
    }

    public function edit(FoodLabel $foodLabel)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'İsimlik Düzenle';

        return view('modules.food_labels.edit', compact('foodLabel', 'branches', 'page_title'));
    }

    public function update(Request $request, FoodLabel $foodLabel)
    {
        $request->validate([
            'name'      => 'required|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $name = array_filter($request->name ?? []);
        if (empty($name)) {
            return back()->withInput()->withErrors(['name' => 'En az bir dilde yemek adı giriniz.']);
        }

        $foodLabel->update([
            'branch_id'     => $request->branch_id,
            'name'          => $request->name ?? [],
            'description'   => $request->description ?? [],
            'ingredients'   => $this->parseIngredients($request),
            'calories'      => $request->calories ?: null,
            'allergens'     => $request->allergens ?? [],
            'category'      => $request->category ?: null,
            'is_vegan'      => $request->boolean('is_vegan'),
            'is_vegetarian' => $request->boolean('is_vegetarian'),
            'is_halal'      => $request->boolean('is_halal'),
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int)$request->sort_order,
        ]);

        return redirect()->route('food-labels.index')
            ->with('success', 'Yemek isimlik güncellendi.');
    }

    public function destroy(FoodLabel $foodLabel)
    {
        $foodLabel->delete();
        return back()->with('success', 'Silindi.');
    }

    /** Tek isimlik yazdır */
    public function printSingle(FoodLabel $foodLabel)
    {
        $labels = collect([$foodLabel]);
        return view('modules.food_labels.print', compact('labels'));
    }

    /** Seçili isimlikler yazdır (POST: ids[] veya GET: ?ids=1,2,3) */
    public function printBulk(Request $request)
    {
        $ids = $request->filled('ids')
            ? (is_array($request->ids) ? $request->ids : explode(',', $request->ids))
            : [];

        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return back()->withErrors(['ids' => 'Yazdırmak için en az bir isimlik seçin.']);
        }

        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $labels = FoodLabel::whereIn('id', $ids)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('sort_order')
            ->get();

        return view('modules.food_labels.print', compact('labels'));
    }

    // -----------------------------------------------------------------------
    private function parseIngredients(Request $request): array
    {
        $result = [];
        $raw    = $request->ingredients ?? [];
        foreach ($raw as $lang => $text) {
            if (empty($text)) continue;
            // virgülle ya da satır sonu ile ayrılmış liste → array
            $items = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $text)));
            $result[$lang] = array_values($items);
        }
        return $result;
    }
}
