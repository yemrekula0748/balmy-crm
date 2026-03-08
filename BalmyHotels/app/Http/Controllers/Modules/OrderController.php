<?php

namespace App\Http\Controllers\Modules;

use App\Models\QrMenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\Request;

class OrderController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'orders',
            ['take', 'session'],   // index → these are the "view" methods
            [],
            ['openTable', 'storeOrder'],
            ['closeTable'],
            ['destroyOrderItem']
        );
    }

    // -------------------------------------------------------------------------
    // Ana sayfa: restoran ve masa seçimi
    // -------------------------------------------------------------------------

    public function take(Request $request)
    {
        $restaurants = Restaurant::with(['branch'])->orderBy('name')->get();

        $restaurantId      = $request->restaurant_id ?? $restaurants->first()?->id;
        $selectedRestaurant = null;
        $tables            = collect();

        if ($restaurantId) {
            $selectedRestaurant = Restaurant::with(['branch', 'qrMenu'])->find($restaurantId);
            if ($selectedRestaurant) {
                $tables = RestaurantTable::where('restaurant_id', $selectedRestaurant->id)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->with(['activeSession.opener'])
                    ->get();
            }
        }

        $page_title = 'Sipariş Al';
        return view('modules.orders.take', compact(
            'restaurants', 'selectedRestaurant', 'tables', 'page_title'
        ));
    }

    // -------------------------------------------------------------------------
    // Masayı aç
    // -------------------------------------------------------------------------

    public function openTable(RestaurantTable $table)
    {
        if ($table->isOpen()) {
            return back()->with('error', 'Bu masa zaten açık.');
        }

        $session = TableSession::create([
            'restaurant_table_id' => $table->id,
            'opened_by'           => auth()->id(),
            'opened_at'           => now(),
            'is_open'             => true,
        ]);

        return redirect()->route('orders.session', $session)
            ->with('success', $table->name . ' açıldı.');
    }

    // -------------------------------------------------------------------------
    // Aktif seans / sipariş alma sayfası
    // -------------------------------------------------------------------------

    public function session(TableSession $session)
    {
        abort_if(!$session->is_open, 410, 'Bu masa/seans kapalı.');

        $session->load(['table.restaurant.qrMenu', 'opener']);

        $qrMenu     = $session->table->restaurant->qrMenu;
        $categories = collect();
        $currency   = '₺';

        if ($qrMenu) {
            $categories = $qrMenu->categories()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->with(['items' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->get();
            $currency = $qrMenu->currency_symbol ?? '₺';
        }

        $orders = $session->orders()
            ->with(['items', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        $page_title = $session->table->name . ' — Sipariş';
        return view('modules.orders.session', compact(
            'session', 'categories', 'orders', 'currency', 'page_title'
        ));
    }

    // -------------------------------------------------------------------------
    // Sipariş kaydet
    // -------------------------------------------------------------------------

    public function storeOrder(Request $request, TableSession $session)
    {
        abort_if(!$session->is_open, 403, 'Kapalı masaya sipariş eklenemez.');

        $request->validate([
            'items'                       => 'required|array|min:1',
            'items.*.qr_menu_item_id'     => 'required|exists:qr_menu_items,id',
            'items.*.quantity'            => 'required|integer|min:1|max:99',
            'items.*.note'                => 'nullable|string|max:500',
            'note'                        => 'nullable|string|max:500',
        ]);

        $order = RestaurantOrder::create([
            'table_session_id' => $session->id,
            'note'             => $request->note,
            'created_by'       => auth()->id(),
        ]);

        foreach ($request->items as $itemData) {
            $menuItem = QrMenuItem::find($itemData['qr_menu_item_id']);
            if (!$menuItem) continue;

            RestaurantOrderItem::create([
                'order_id'        => $order->id,
                'qr_menu_item_id' => $menuItem->id,
                'item_name'       => $menuItem->getTitle('tr'),
                'unit_price'      => $menuItem->effectivePrice(),
                'quantity'        => (int)$itemData['quantity'],
                'note'            => $itemData['note'] ?? null,
            ]);
        }

        $restaurantId = $session->table->restaurant_id;

        return redirect()
            ->route('orders.take', ['restaurant_id' => $restaurantId])
            ->with('success', 'Sipariş kaydedildi.');
    }

    // -------------------------------------------------------------------------
    // Sipariş kalemi sil
    // -------------------------------------------------------------------------

    public function destroyOrderItem(TableSession $session, RestaurantOrderItem $item)
    {
        abort_if(!$session->is_open, 403);
        abort_if($item->order->table_session_id !== $session->id, 403);

        $orderId = $item->order_id;
        $item->delete();

        // Sipariş boş kaldıysa onu da sil
        $order = RestaurantOrder::withCount('items')->find($orderId);
        if ($order && $order->items_count === 0) {
            $order->delete();
        }

        return back()->with('success', 'Kalem silindi.');
    }

    // -------------------------------------------------------------------------
    // Masayı kapat
    // -------------------------------------------------------------------------

    public function closeTable(TableSession $session)
    {
        abort_if(!$session->is_open, 403, 'Seans zaten kapalı.');

        $session->update([
            'closed_at' => now(),
            'is_open'   => false,
        ]);

        $restaurantId = $session->table->restaurant_id;
        return redirect()
            ->route('orders.take', ['restaurant_id' => $restaurantId])
            ->with('success', $session->table->name . ' kapatıldı.');
    }
}
