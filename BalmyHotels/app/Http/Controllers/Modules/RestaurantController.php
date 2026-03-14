<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\QrMenu;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class RestaurantController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('restaurant_settings');
    }

    public function index()
    {
        $restaurants = Restaurant::with(['branch', 'qrMenu', 'tables'])->orderBy('name')->get();
        $page_title  = 'Restoranlar';
        return view('modules.orders.restaurants.index', compact('restaurants', 'page_title'));
    }

    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $menus      = QrMenu::where('is_active', true)->orderBy('name')->get();
        $page_title = 'Yeni Restoran';
        return view('modules.orders.restaurants.create', compact('branches', 'menus', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'branch_id'  => 'nullable|exists:branches,id',
            'qr_menu_id' => 'nullable|exists:qr_menus,id',
        ]);

        Restaurant::create([
            'name'       => $request->name,
            'branch_id'  => $request->branch_id,
            'qr_menu_id' => $request->qr_menu_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('orders.restaurants.index')->with('success', 'Restoran başarıyla oluşturuldu.');
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['branch', 'qrMenu', 'tables']);
        $page_title = $restaurant->name;
        return view('modules.orders.restaurants.show', compact('restaurant', 'page_title'));
    }

    public function edit(Restaurant $restaurant)
    {
        $branches   = Branch::orderBy('name')->get();
        $menus      = QrMenu::where('is_active', true)->orderBy('name')->get();
        $page_title = 'Restoran Düzenle: ' . $restaurant->name;
        return view('modules.orders.restaurants.edit', compact('restaurant', 'branches', 'menus', 'page_title'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'branch_id'  => 'nullable|exists:branches,id',
            'qr_menu_id' => 'nullable|exists:qr_menus,id',
        ]);

        $restaurant->update([
            'name'       => $request->name,
            'branch_id'  => $request->branch_id,
            'qr_menu_id' => $request->qr_menu_id,
        ]);

        return redirect()->route('orders.restaurants.show', $restaurant)->with('success', 'Restoran güncellendi.');
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return redirect()->route('orders.restaurants.index')->with('success', 'Restoran silindi.');
    }

    // -------------------------------------------------------------------------
    // Masa CRUD (inline)
    // -------------------------------------------------------------------------

    public function storeTable(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name'       => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $restaurant->tables()->create([
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'Masa eklendi.');
    }

    public function destroyTable(Restaurant $restaurant, RestaurantTable $table)
    {
        abort_if($table->restaurant_id !== $restaurant->id, 403);
        $table->delete();
        return back()->with('success', 'Masa silindi.');
    }
}
