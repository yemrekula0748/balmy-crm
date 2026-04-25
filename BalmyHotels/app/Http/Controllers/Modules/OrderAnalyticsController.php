<?php

namespace App\Http\Controllers\Modules;

use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\TableSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAnalyticsController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'order_analytics',
            ['index'],
            [],
            [],
            [],
            []
        );
    }

    public function index(Request $request)
    {
        // ── Filtre parametreleri ─────────────────────────────────────────────
        $restaurantId = $request->restaurant_id;
        $dateFrom     = $request->date_from  ?? now()->subDays(29)->toDateString();
        $dateTo       = $request->date_to    ?? now()->toDateString();

        $restaurants = Restaurant::orderBy('name')->get();

        // ── Baz query: tüm order items bu tarih aralığında ─────────────────
        $itemBase = RestaurantOrderItem::query()
            ->join('restaurant_orders',  'restaurant_orders.id',  '=', 'restaurant_order_items.order_id')
            ->join('table_sessions',     'table_sessions.id',     '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables',  'restaurant_tables.id',  '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',        'restaurants.id',        '=', 'restaurant_tables.restaurant_id')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo);

        if ($restaurantId) {
            $itemBase->where('restaurants.id', $restaurantId);
        }

        // ── 1. Özet kartlar ──────────────────────────────────────────────────
        $summaryRaw = (clone $itemBase)
            ->selectRaw('
                SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as total_revenue,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as paid_revenue,
                SUM(restaurant_order_items.quantity) as total_qty,
                COUNT(DISTINCT restaurant_orders.table_session_id) as total_sessions,
                COUNT(DISTINCT restaurant_orders.id) as total_orders
            ')
            ->first();

        $summary = [
            'total_revenue'  => round($summaryRaw->total_revenue  ?? 0, 2),
            'paid_revenue'   => round($summaryRaw->paid_revenue   ?? 0, 2),
            'total_qty'      => (int)($summaryRaw->total_qty       ?? 0),
            'total_sessions' => (int)($summaryRaw->total_sessions  ?? 0),
            'total_orders'   => (int)($summaryRaw->total_orders    ?? 0),
        ];
        $summary['avg_session_revenue'] = $summary['total_sessions'] > 0
            ? round($summary['paid_revenue'] / $summary['total_sessions'], 2)
            : 0;

        // ── 2. En çok kazandıran ücretli ürünler (Top 10) ───────────────────
        $topEarningProducts = (clone $itemBase)
            ->selectRaw('
                restaurant_order_items.item_name,
                SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue,
                SUM(restaurant_order_items.quantity) as qty,
                AVG(restaurant_order_items.unit_price) as avg_price
            ')
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ── 3. En çok kazandıran restoranlar ────────────────────────────────
        $topRestaurants = (clone $itemBase)
            ->selectRaw('
                restaurants.name as restaurant_name,
                SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue,
                SUM(restaurant_order_items.quantity) as qty,
                COUNT(DISTINCT restaurant_orders.table_session_id) as sessions
            ')
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('revenue')
            ->get();

        // ── 4. En çok tüketilen ÜCRETSİZ ürünler (Top 10) ──────────────────
        $topFreeProducts = (clone $itemBase)
            ->selectRaw('
                restaurant_order_items.item_name,
                SUM(restaurant_order_items.quantity) as qty
            ')
            ->where(function ($q) {
                $q->whereNull('restaurant_order_items.unit_price')
                  ->orWhere('restaurant_order_items.unit_price', '<=', 0);
            })
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // ── 5. En çok tüketilen ÜCRETLİ ürünler (adete göre, Top 10) ───────
        $topPaidProductsByQty = (clone $itemBase)
            ->selectRaw('
                restaurant_order_items.item_name,
                SUM(restaurant_order_items.quantity) as qty,
                AVG(restaurant_order_items.unit_price) as avg_price
            ')
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // ── 6. Saatlik yoğunluk (son 30 gün, sipariş sayısına göre) ─────────
        $hourlyOrders = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('HOUR(restaurant_orders.created_at) as hour, COUNT(*) as cnt')
            ->groupByRaw('HOUR(restaurant_orders.created_at)')
            ->orderBy('hour')
            ->pluck('cnt', 'hour');

        // Tüm 24 saati sıfırla, sonra doldur
        $hourlyData = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => $hourlyOrders->get($h, 0)]);

        // ── 7. Günlük hasılat trendi (line chart) ────────────────────────────
        $dailyRevenue = (clone $itemBase)
            ->selectRaw('DATE(table_sessions.opened_at) as day, SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue')
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->groupByRaw('DATE(table_sessions.opened_at)')
            ->orderBy('day')
            ->pluck('revenue', 'day');

        // Fill missing days with 0
        $period = \Carbon\CarbonPeriod::create($dateFrom, $dateTo);
        $dailyLabels  = [];
        $dailyValues  = [];
        foreach ($period as $date) {
            $d = $date->toDateString();
            $dailyLabels[] = $date->format('d.m');
            $dailyValues[] = round((float)($dailyRevenue->get($d, 0)), 2);
        }

        // ── 8. En aktif garsonlar (sipariş kaydeden kullanıcılar) ───────────
        $topWaiters = RestaurantOrder::query()
            ->join('users',             'users.id',             '=', 'restaurant_orders.created_by')
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('
                users.name,
                COUNT(DISTINCT restaurant_orders.id) as order_count,
                SUM(restaurant_order_items.quantity) as item_qty
            ')
            ->join('restaurant_order_items', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        // ── 9. Günün en yoğun günleri (haftanın günleri) ────────────────────
        $weekdayOrders = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('DAYOFWEEK(restaurant_orders.created_at) as dow, COUNT(*) as cnt')
            ->groupByRaw('DAYOFWEEK(restaurant_orders.created_at)')
            ->pluck('cnt', 'dow');

        // MySQL DAYOFWEEK: 1=Pazar...7=Cumartesi → TR sıralaması
        $dowLabels = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cts'];
        $weekdayData = collect(range(1, 7))->map(fn($d) => (int)$weekdayOrders->get($d, 0));

        // ── 10. Ortalama masa süresi (dakika) restoran bazında ───────────────
        $avgDuration = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('restaurants.name as restaurant_name, AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as avg_min')
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('avg_min')
            ->get();

        $page_title = 'Sipariş Analizi';
        return view('modules.orders.analytics', compact(
            'restaurants', 'restaurantId', 'dateFrom', 'dateTo',
            'summary',
            'topEarningProducts',
            'topRestaurants',
            'topFreeProducts',
            'topPaidProductsByQty',
            'hourlyData',
            'dailyLabels', 'dailyValues',
            'topWaiters',
            'dowLabels', 'weekdayData',
            'avgDuration',
            'page_title'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PDF: Gün Bazlı Sipariş Raporu
    // ─────────────────────────────────────────────────────────────────────────
    public function pdf(Request $request)
    {
        $dateFrom     = $request->date_from ?? now()->startOfWeek()->toDateString();
        $dateTo       = $request->date_to   ?? now()->toDateString();
        $restaurantId = $request->restaurant_id;

        // ── Baz query ────────────────────────────────────────────────────────
        $itemBase = RestaurantOrderItem::query()
            ->join('restaurant_orders',  'restaurant_orders.id',  '=', 'restaurant_order_items.order_id')
            ->join('table_sessions',     'table_sessions.id',     '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables',  'restaurant_tables.id',  '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',        'restaurants.id',        '=', 'restaurant_tables.restaurant_id')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo);

        if ($restaurantId) {
            $itemBase->where('restaurants.id', $restaurantId);
        }

        // ── Özet ─────────────────────────────────────────────────────────────
        $summaryRaw = (clone $itemBase)
            ->selectRaw('
                SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as total_revenue,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as paid_revenue,
                SUM(restaurant_order_items.quantity) as total_qty,
                COUNT(DISTINCT restaurant_orders.table_session_id) as total_sessions,
                COUNT(DISTINCT restaurant_orders.id) as total_orders
            ')
            ->first();

        $summary = [
            'paid_revenue'   => round($summaryRaw->paid_revenue   ?? 0, 2),
            'total_qty'      => (int)($summaryRaw->total_qty       ?? 0),
            'total_sessions' => (int)($summaryRaw->total_sessions  ?? 0),
            'total_orders'   => (int)($summaryRaw->total_orders    ?? 0),
        ];

        // ── Restoran bazında toplamlar ────────────────────────────────────────
        $restaurantTotals = (clone $itemBase)
            ->selectRaw('
                restaurants.id as restaurant_id,
                restaurants.name as restaurant_name,
                SUM(restaurant_order_items.quantity) as total_qty,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0
                    THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as total_revenue,
                COUNT(DISTINCT restaurant_orders.table_session_id) as sessions
            ')
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->keyBy('restaurant_id');

        // ── Detay: restoran × gün × ürün ─────────────────────────────────────
        $detailRows = (clone $itemBase)
            ->selectRaw('
                restaurants.id as restaurant_id,
                restaurants.name as restaurant_name,
                DATE(table_sessions.opened_at) as order_date,
                restaurant_order_items.item_name,
                SUM(restaurant_order_items.quantity) as qty,
                AVG(restaurant_order_items.unit_price) as avg_price,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0
                    THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as line_total
            ')
            ->groupBy('restaurants.id', 'restaurants.name', DB::raw('DATE(table_sessions.opened_at)'), 'restaurant_order_items.item_name')
            ->orderBy('restaurants.name')
            ->orderBy(DB::raw('DATE(table_sessions.opened_at)'))
            ->orderByDesc('qty')
            ->get();

        // Gruplama: restaurant_id → order_date → rows[]
        $byRestaurant = $detailRows->groupBy('restaurant_id')->map(fn($rows) => $rows->groupBy('order_date'));

        $filters = [
            'restaurant' => $restaurantId ? Restaurant::find($restaurantId)?->name : null,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
        ];

        $generatedAt = now()->format('d.m.Y H:i');

        $fontCache = storage_path('fonts');
        if (!is_dir($fontCache)) mkdir($fontCache, 0755, true);

        $pdf = Pdf::loadView('modules.orders.analytics_pdf', compact(
            'summary', 'restaurantTotals', 'byRestaurant', 'filters', 'generatedAt'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption([
            'defaultFont'          => 'dejavu sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'fontCache'            => $fontCache,
        ]);

        $filename = 'siparis-raporu-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
