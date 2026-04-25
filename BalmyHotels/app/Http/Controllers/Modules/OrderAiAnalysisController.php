<?php

namespace App\Http\Controllers\Modules;

use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\TableSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAiAnalysisController extends BaseModuleController
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
        $dateFrom     = $request->date_from ?? now()->subDays(29)->toDateString();
        $dateTo       = $request->date_to   ?? now()->toDateString();
        $restaurantId = $request->restaurant_id;
        $restaurants  = Restaurant::orderBy('name')->get();

        // Base join chain — kullanılan tüm sorgularda aynı
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

        // ── 1. Genel Özet ──────────────────────────────────────────────────
        $summaryRaw = (clone $itemBase)->selectRaw('
            SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as total_revenue,
            SUM(CASE WHEN restaurant_order_items.unit_price > 0
                THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as paid_revenue,
            SUM(restaurant_order_items.quantity) as total_qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as total_sessions,
            COUNT(DISTINCT restaurant_orders.id) as total_orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days
        ')->first();

        $totalSessions   = (int)($summaryRaw->total_sessions ?? 0);
        $paidRevenue     = round($summaryRaw->paid_revenue ?? 0, 2);
        $totalQty        = (int)($summaryRaw->total_qty ?? 0);
        $totalOrders     = (int)($summaryRaw->total_orders ?? 0);
        $activeDays      = (int)($summaryRaw->active_days ?? 0);
        $avgSessionRev   = $totalSessions > 0 ? round($paidRevenue / $totalSessions, 2) : 0;
        $dayCount        = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        $avgDailyRev     = $activeDays > 0 ? round($paidRevenue / $activeDays, 2) : 0;
        $avgOrdersPerDay = $activeDays > 0 ? round($totalOrders / $activeDays, 1) : 0;
        $avgQtyPerOrder  = $totalOrders > 0 ? round($totalQty / $totalOrders, 1) : 0;

        // ── 2. Restoran bazında performans ─────────────────────────────────
        $restoStats = (clone $itemBase)->selectRaw('
            restaurants.id as restaurant_id,
            restaurants.name as restaurant_name,
            SUM(CASE WHEN restaurant_order_items.unit_price > 0
                THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue,
            SUM(restaurant_order_items.quantity) as qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as sessions,
            COUNT(DISTINCT restaurant_orders.id) as orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days
        ')
        ->groupBy('restaurants.id', 'restaurants.name')
        ->orderByDesc('revenue')
        ->get();

        $topResto   = $restoStats->first();
        $bottomResto = $restoStats->last();

        // ── 3. Top ürünler & kategori analizi ─────────────────────────────
        $topPaidProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            SUM(restaurant_order_items.quantity) as qty,
            SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue,
            AVG(restaurant_order_items.unit_price) as avg_price
        ')
        ->where('restaurant_order_items.unit_price', '>', 0)
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('revenue')
        ->limit(15)
        ->get();

        $topFreeProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            SUM(restaurant_order_items.quantity) as qty
        ')
        ->where(fn($q) => $q->whereNull('restaurant_order_items.unit_price')
                            ->orWhere('restaurant_order_items.unit_price', '<=', 0))
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('qty')
        ->limit(10)
        ->get();

        // Ürün çeşitliliği (distinct item_name sayısı)
        $distinctPaidItems = (clone $itemBase)
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->distinct()
            ->count('restaurant_order_items.item_name');

        $distinctFreeItems = (clone $itemBase)
            ->where(fn($q) => $q->whereNull('restaurant_order_items.unit_price')
                                ->orWhere('restaurant_order_items.unit_price', '<=', 0))
            ->distinct()
            ->count('restaurant_order_items.item_name');

        // ── 4. Saatlik yoğunluk ────────────────────────────────────────────
        $hourlyRaw = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('HOUR(restaurant_orders.created_at) as hour, COUNT(*) as cnt')
            ->groupByRaw('HOUR(restaurant_orders.created_at)')
            ->pluck('cnt', 'hour');

        $hourlyData  = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => (int)$hourlyRaw->get($h, 0)]);
        $peakHour    = $hourlyData->sortDesc()->keys()->first();
        $peakHourCnt = $hourlyData->max();

        // Sabah (6–11), öğlen (12–14), öğleden sonra (15–17), akşam (18–23)
        $morningCnt   = $hourlyData->filter(fn($v, $k) => $k >= 6  && $k <= 11)->sum();
        $lunchCnt     = $hourlyData->filter(fn($v, $k) => $k >= 12 && $k <= 14)->sum();
        $afternoonCnt = $hourlyData->filter(fn($v, $k) => $k >= 15 && $k <= 17)->sum();
        $eveningCnt   = $hourlyData->filter(fn($v, $k) => $k >= 18 && $k <= 23)->sum();
        $totalHourlyCnt = $morningCnt + $lunchCnt + $afternoonCnt + $eveningCnt;

        $morningPct   = $totalHourlyCnt > 0 ? round($morningCnt   / $totalHourlyCnt * 100, 1) : 0;
        $lunchPct     = $totalHourlyCnt > 0 ? round($lunchCnt     / $totalHourlyCnt * 100, 1) : 0;
        $afternoonPct = $totalHourlyCnt > 0 ? round($afternoonCnt / $totalHourlyCnt * 100, 1) : 0;
        $eveningPct   = $totalHourlyCnt > 0 ? round($eveningCnt   / $totalHourlyCnt * 100, 1) : 0;

        // ── 5. Haftanın günleri dağılımı ──────────────────────────────────
        $weekdayRaw = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('DAYOFWEEK(restaurant_orders.created_at) as dow, COUNT(*) as cnt')
            ->groupByRaw('DAYOFWEEK(restaurant_orders.created_at)')
            ->pluck('cnt', 'dow');

        // MySQL: 1=Pazar, 2=Pazartesi, ..., 7=Cumartesi
        $dowLabels    = [1 => 'Pazar', 2 => 'Pazartesi', 3 => 'Salı', 4 => 'Çarşamba', 5 => 'Perşembe', 6 => 'Cuma', 7 => 'Cumartesi'];
        $weekdayData  = collect(range(1,7))->mapWithKeys(fn($d) => [$dowLabels[$d] => (int)$weekdayRaw->get($d, 0)]);
        $busiestDay   = $weekdayData->sortDesc()->keys()->first();
        $quietestDay  = $weekdayData->filter(fn($v) => $v > 0)->sortKeys()->sortBy(fn($v) => $v)->keys()->first() ?? '—';

        // ── 6. Masa oturma süreleri ────────────────────────────────────────
        $sessionDurRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('
                restaurants.name as restaurant_name,
                restaurants.id as restaurant_id,
                AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as avg_min,
                MIN(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as min_min,
                MAX(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as max_min,
                COUNT(*) as session_count
            ')
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('avg_min')
            ->get();

        $overallAvgDurRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as avg_min')
            ->value('avg_min');

        $overallAvgDur     = round($overallAvgDurRaw ?? 0, 1);
        $longestStayResto  = $sessionDurRaw->sortByDesc('avg_min')->first();
        $shortestStayResto = $sessionDurRaw->filter(fn($r) => $r->session_count >= 2)->sortBy('avg_min')->first();

        // ── 7. Günlük trend & büyüme ───────────────────────────────────────
        $dailyRevRaw = (clone $itemBase)
            ->selectRaw('DATE(table_sessions.opened_at) as day, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->groupByRaw('DATE(table_sessions.opened_at)')
            ->orderBy('day')
            ->pluck('revenue', 'day');

        $period     = CarbonPeriod::create($dateFrom, $dateTo);
        $dailyRevs  = collect();
        foreach ($period as $date) {
            $dailyRevs->push((float)($dailyRevRaw->get($date->toDateString(), 0)));
        }

        $peakDayRev   = $dailyRevRaw->max() ?? 0;
        $peakDayDate  = $dailyRevRaw->filter(fn($v) => $v == $peakDayRev)->keys()->first();
        $nonZeroDays  = $dailyRevRaw->filter(fn($v) => $v > 0);

        // Haftalık büyüme: son 7 gün vs önceki 7 gün
        $week2End   = Carbon::parse($dateTo);
        $week2Start = $week2End->copy()->subDays(6);
        $week1End   = $week2Start->copy()->subDay();
        $week1Start = $week1End->copy()->subDays(6);

        $week1Rev = $dailyRevRaw->filter(fn($v, $k) => $k >= $week1Start->toDateString() && $k <= $week1End->toDateString())->sum();
        $week2Rev = $dailyRevRaw->filter(fn($v, $k) => $k >= $week2Start->toDateString() && $k <= $week2End->toDateString())->sum();
        $weeklyGrowth = ($week1Rev > 0) ? round((($week2Rev - $week1Rev) / $week1Rev) * 100, 1) : null;

        // ── 8. Garson performansı ──────────────────────────────────────────
        $waiterStats = RestaurantOrder::query()
            ->join('users',             'users.id',             '=', 'restaurant_orders.created_by')
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->join('restaurant_order_items', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('
                users.name as waiter_name,
                COUNT(DISTINCT restaurant_orders.id) as order_count,
                SUM(restaurant_order_items.quantity) as item_qty,
                COUNT(DISTINCT restaurant_orders.table_session_id) as session_count,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0
                    THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        $topWaiter      = $waiterStats->first();
        $waiterCount    = $waiterStats->count();
        $avgOrdersPerWaiter = $waiterCount > 0 ? round($totalOrders / $waiterCount, 1) : 0;

        // ── 9. Ücretli vs ücretsiz dağılımı ───────────────────────────────
        $paidQtyTotal = (clone $itemBase)->where('restaurant_order_items.unit_price', '>', 0)->sum('restaurant_order_items.quantity');
        $freeQtyTotal = (clone $itemBase)
            ->where(fn($q) => $q->whereNull('restaurant_order_items.unit_price')
                                ->orWhere('restaurant_order_items.unit_price', '<=', 0))
            ->sum('restaurant_order_items.quantity');

        $paidQtyPct = $totalQty > 0 ? round($paidQtyTotal / $totalQty * 100, 1) : 0;
        $freeQtyPct = $totalQty > 0 ? round($freeQtyTotal / $totalQty * 100, 1) : 0;

        // ── 10. Restoran-ürün çakışma analizi (cross-sell tespiti) ─────────
        $productsByResto = (clone $itemBase)
            ->selectRaw('
                restaurants.name as restaurant_name,
                restaurant_order_items.item_name,
                SUM(restaurant_order_items.quantity) as qty,
                SUM(CASE WHEN restaurant_order_items.unit_price > 0
                    THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue
            ')
            ->groupBy('restaurants.name', 'restaurant_order_items.item_name')
            ->orderBy('restaurants.name')
            ->orderByDesc('qty')
            ->get()
            ->groupBy('restaurant_name');

        // ── 11. Yüksek değerli &  düşük adet ürünler (premium) ────────────
        $premiumProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            AVG(restaurant_order_items.unit_price) as avg_price,
            SUM(restaurant_order_items.quantity) as qty,
            SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue
        ')
        ->where('restaurant_order_items.unit_price', '>', 0)
        ->groupBy('restaurant_order_items.item_name')
        ->having('avg_price', '>', 0)
        ->orderByDesc('avg_price')
        ->limit(10)
        ->get();

        // ── 12. Sipariş yoğunluğu: sipariş başına ortalama kalem ──────────
        $orderItemDist = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->join('restaurant_order_items', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('restaurant_orders.id as order_id, SUM(restaurant_order_items.quantity) as item_cnt')
            ->groupBy('restaurant_orders.id')
            ->pluck('item_cnt');

        $singleItemOrders = $orderItemDist->filter(fn($c) => $c == 1)->count();
        $multiItemOrders  = $orderItemDist->filter(fn($c) => $c > 1)->count();
        $singleItemPct    = $orderItemDist->count() > 0 ? round($singleItemOrders / $orderItemDist->count() * 100, 1) : 0;

        $page_title = 'Stratejik Sipariş Analizi';

        return view('modules.orders.ai_analysis', compact(
            'restaurants', 'restaurantId', 'dateFrom', 'dateTo', 'dayCount',
            'page_title',
            // Özet
            'paidRevenue', 'totalSessions', 'totalOrders', 'totalQty',
            'activeDays', 'avgSessionRev', 'avgDailyRev', 'avgOrdersPerDay', 'avgQtyPerOrder',
            // Restoranlar
            'restoStats', 'topResto', 'bottomResto',
            // Ürünler
            'topPaidProducts', 'topFreeProducts',
            'distinctPaidItems', 'distinctFreeItems',
            'premiumProducts',
            // Saatlik
            'hourlyData', 'peakHour', 'peakHourCnt',
            'morningPct', 'lunchPct', 'afternoonPct', 'eveningPct',
            // Haftalık
            'weekdayData', 'busiestDay', 'quietestDay',
            // Oturma süreleri
            'sessionDurRaw', 'overallAvgDur', 'longestStayResto', 'shortestStayResto',
            // Trend
            'dailyRevRaw', 'peakDayRev', 'peakDayDate', 'nonZeroDays',
            'week1Rev', 'week2Rev', 'weeklyGrowth',
            // Garson
            'waiterStats', 'topWaiter', 'waiterCount', 'avgOrdersPerWaiter',
            // Dağılım
            'paidQtyTotal', 'freeQtyTotal', 'paidQtyPct', 'freeQtyPct',
            // Ürün-Restoran
            'productsByResto',
            // Sipariş yoğunluğu
            'singleItemPct', 'multiItemOrders', 'singleItemOrders'
        ));
    }
}
