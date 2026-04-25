<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Printer;
use App\Models\QrMenu;
use App\Models\Restaurant;
use App\Models\RestaurantItemPrinter;
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

        $categories    = collect();
        $printerMap    = collect(); // qr_menu_item_id → printer_id
        $printers      = Printer::where('is_active', true)->orderBy('name')->get();

        if ($restaurant->qrMenu) {
            $categories = $restaurant->qrMenu->categories()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->with(['items' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->get();

            $printerMap = RestaurantItemPrinter::where('restaurant_id', $restaurant->id)
                ->pluck('printer_id', 'qr_menu_item_id');
        }

        $page_title = $restaurant->name;
        return view('modules.orders.restaurants.show', compact(
            'restaurant', 'categories', 'printerMap', 'printers', 'page_title'
        ));
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

    public function storeBulkTables(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'bulk_mode'  => 'required|in:auto,manual',
            'prefix'     => 'required_if:bulk_mode,auto|nullable|string|max:30',
            'from'       => 'required_if:bulk_mode,auto|nullable|integer|min:1|max:999',
            'to'         => 'required_if:bulk_mode,auto|nullable|integer|min:1|max:999',
            'names'      => 'required_if:bulk_mode,manual|nullable|string|max:5000',
        ]);

        $names = [];

        if ($request->bulk_mode === 'auto') {
            $from   = (int)$request->from;
            $to     = (int)$request->to;
            $prefix = trim($request->prefix);
            if ($from > $to) [$from, $to] = [$to, $from];
            if (($to - $from) > 199) {
                return back()->withErrors(['to' => 'En fazla 200 masa tek seferde eklenebilir.']);
            }
            for ($i = $from; $i <= $to; $i++) {
                $names[] = $prefix . ' ' . $i;
            }
        } else {
            $lines = preg_split('/\r?\n/', $request->names ?? '');
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $names[] = mb_substr($line, 0, 50);
                }
            }
            if (count($names) > 200) {
                return back()->withErrors(['names' => 'En fazla 200 masa tek seferde eklenebilir.']);
            }
        }

        if (empty($names)) {
            return back()->withErrors(['bulk_mode' => 'Eklenecek masa adı bulunamadı.']);
        }

        // Sıra numarasını mevcut en yüksekten devam ettir
        $sortStart = $restaurant->tables()->max('sort_order') + 1;

        foreach (array_values($names) as $idx => $name) {
            $restaurant->tables()->create([
                'name'       => $name,
                'sort_order' => $sortStart + $idx,
            ]);
        }

        return back()->with('success', count($names) . ' masa başarıyla eklendi.');
    }

    public function destroyTable(Restaurant $restaurant, RestaurantTable $table)
    {
        abort_if($table->restaurant_id !== $restaurant->id, 403);
        $table->delete();
        return back()->with('success', 'Masa silindi.');
    }

    // -------------------------------------------------------------------------
    // Yazıcı ataması (restoran × menü kalemi)
    // -------------------------------------------------------------------------

    public function savePrinters(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'printers'   => 'nullable|array',
            'printers.*' => 'nullable|exists:printers,id',
        ]);

        $assignments = $request->input('printers', []);

        foreach ($assignments as $itemId => $printerId) {
            if (empty($printerId)) {
                // Atama kaldır
                RestaurantItemPrinter::where('restaurant_id', $restaurant->id)
                    ->where('qr_menu_item_id', (int)$itemId)
                    ->delete();
            } else {
                RestaurantItemPrinter::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'qr_menu_item_id' => (int)$itemId],
                    ['printer_id'    => (int)$printerId]
                );
            }
        }

        return back()->with('success', 'Yazıcı atamaları kaydedildi.');
    }
}
