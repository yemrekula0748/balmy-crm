<?php

namespace App\Http\Controllers\Modules;

use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\TableSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderGuestAnalysisController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('order_analytics', ['index'], [], [], [], []);
    }

    public function index(Request $request)
    {
        $dateFrom     = $request->date_from ?? now()->subDays(29)->toDateString();
        $dateTo       = $request->date_to   ?? now()->toDateString();
        $restaurantId = $request->restaurant_id;
        $restaurants  = Restaurant::orderBy('name')->get();
        $dayCount     = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;

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

        // ── 1. Genel hacim özeti (tek fiyat bilgisi yok) ──────────────────
        $summaryRaw = (clone $itemBase)->selectRaw('
            SUM(restaurant_order_items.quantity) as total_qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as total_sessions,
            COUNT(DISTINCT restaurant_orders.id) as total_orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days,
            COUNT(DISTINCT restaurant_order_items.item_name) as distinct_items
        ')->first();

        $totalSessions = (int)($summaryRaw->total_sessions ?? 0);
        $totalOrders   = (int)($summaryRaw->total_orders   ?? 0);
        $totalQty      = (int)($summaryRaw->total_qty      ?? 0);
        $activeDays    = (int)($summaryRaw->active_days    ?? 0);
        $distinctItems = (int)($summaryRaw->distinct_items ?? 0);

        $avgOrdersPerDay  = $activeDays    > 0 ? round($totalOrders  / $activeDays, 1)    : 0;
        $avgQtyPerSession = $totalSessions > 0 ? round($totalQty     / $totalSessions, 1) : 0;
        $avgQtyPerOrder   = $totalOrders   > 0 ? round($totalQty     / $totalOrders, 2)   : 0;
        $sessionIntensity = $totalSessions > 0 ? round($totalOrders  / $totalSessions, 2) : 0;

        // ── 2. Günlük kalem & sipariş trendi ──────────────────────────────
        $dailyQtyRaw = (clone $itemBase)
            ->selectRaw('DATE(table_sessions.opened_at) as day, SUM(restaurant_order_items.quantity) as qty')
            ->groupByRaw('DATE(table_sessions.opened_at)')
            ->orderBy('day')
            ->pluck('qty', 'day');

        $dailyOrderCountRaw = RestaurantOrder::query()
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('DATE(restaurant_orders.created_at) as day, COUNT(*) as cnt')
            ->groupByRaw('DATE(restaurant_orders.created_at)')
            ->pluck('cnt', 'day');

        $nonZeroDays   = $dailyQtyRaw->filter(fn($v) => $v > 0);
        $peakDayQty    = $dailyQtyRaw->max() ?? 0;
        $peakDayDate   = $dailyQtyRaw->filter(fn($v) => $v == $peakDayQty)->keys()->first();
        $lowestDayQty  = $nonZeroDays->min() ?? 0;
        $lowestDayDate = $nonZeroDays->filter(fn($v) => $v == $lowestDayQty)->keys()->first();
        $zeroQtyDays   = $dayCount - $nonZeroDays->count();

        $qtyValues   = $nonZeroDays->values();
        $qtyMean     = $qtyValues->count() > 0 ? $qtyValues->avg() : 0;
        $qtyVariance = $qtyValues->count() > 1
            ? $qtyValues->reduce(fn($c, $v) => $c + pow($v - $qtyMean, 2), 0) / ($qtyValues->count() - 1)
            : 0;
        $qtyStdDev  = round(sqrt($qtyVariance), 1);
        $coeffVar   = $qtyMean > 0 ? round(($qtyStdDev / $qtyMean) * 100, 1) : 0;
        $consistencyTR = $coeffVar <= 20 ? 'Çok Tutarlı' : ($coeffVar <= 40 ? 'Tutarlı' : ($coeffVar <= 65 ? 'Dalgalı' : 'Yüksek Volatil'));
        $consistencyColor = $coeffVar <= 20 ? '#15803d' : ($coeffVar <= 40 ? '#0ea5e9' : ($coeffVar <= 65 ? '#d97706' : '#dc2626'));

        $w2End   = Carbon::parse($dateTo);
        $w2Start = $w2End->copy()->subDays(6);
        $w1End   = $w2Start->copy()->subDay();
        $w1Start = $w1End->copy()->subDays(6);
        $week1Qty     = $dailyQtyRaw->filter(fn($v, $k) => $k >= $w1Start->toDateString() && $k <= $w1End->toDateString())->sum();
        $week2Qty     = $dailyQtyRaw->filter(fn($v, $k) => $k >= $w2Start->toDateString() && $k <= $w2End->toDateString())->sum();
        $weeklyGrowth = ($week1Qty > 0) ? round((($week2Qty - $week1Qty) / $week1Qty) * 100, 1) : null;

        // ── 3. Restoran bazında dağılım ───────────────────────────────────
        $restoStats = (clone $itemBase)->selectRaw('
            restaurants.id as restaurant_id,
            restaurants.name as restaurant_name,
            SUM(restaurant_order_items.quantity) as qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as sessions,
            COUNT(DISTINCT restaurant_orders.id) as orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days,
            COUNT(DISTINCT restaurant_order_items.item_name) as distinct_items
        ')
        ->groupBy('restaurants.id', 'restaurants.name')
        ->orderByDesc('qty')
        ->get()
        ->map(function ($r) use ($activeDays) {
            $r->qty_per_session = $r->sessions    > 0 ? round($r->qty / $r->sessions, 1)    : 0;
            $r->orders_per_day  = $activeDays     > 0 ? round($r->orders / $activeDays, 1)  : 0;
            $r->intensity       = $r->sessions    > 0 ? round($r->orders / $r->sessions, 2) : 0;
            return $r;
        });

        $topResto = $restoStats->first();

        // ── 4. En çok tüketilen ürünler ───────────────────────────────────
        $topProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            SUM(restaurant_order_items.quantity) as qty,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days_sold,
            COUNT(DISTINCT restaurant_orders.table_session_id) as session_count
        ')
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('qty')
        ->limit(25)
        ->get()
        ->map(function ($p) use ($activeDays) {
            $p->velocity = $activeDays > 0 ? round($p->qty / $activeDays, 2) : 0;
            return $p;
        });

        // ── 5. Saatlik & günlük yoğunluk ──────────────────────────────────
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

        $hourlyQtyRaw = (clone $itemBase)
            ->selectRaw('HOUR(table_sessions.opened_at) as hour, SUM(restaurant_order_items.quantity) as qty')
            ->groupByRaw('HOUR(table_sessions.opened_at)')
            ->pluck('qty', 'hour');

        $hourlyData    = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => (int)$hourlyRaw->get($h, 0)]);
        $hourlyQtyData = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => (int)($hourlyQtyRaw->get($h, 0))]);
        $peakHour      = $hourlyData->sortDesc()->keys()->first();
        $peakHourCnt   = $hourlyData->max();

        $morningCnt     = $hourlyData->filter(fn($v, $k) => $k >= 6  && $k <= 11)->sum();
        $lunchCnt       = $hourlyData->filter(fn($v, $k) => $k >= 12 && $k <= 14)->sum();
        $afternoonCnt   = $hourlyData->filter(fn($v, $k) => $k >= 15 && $k <= 17)->sum();
        $eveningCnt     = $hourlyData->filter(fn($v, $k) => $k >= 18 && $k <= 23)->sum();
        $totalHourlyCnt = $morningCnt + $lunchCnt + $afternoonCnt + $eveningCnt;
        $morningPct     = $totalHourlyCnt > 0 ? round($morningCnt   / $totalHourlyCnt * 100, 1) : 0;
        $lunchPct       = $totalHourlyCnt > 0 ? round($lunchCnt     / $totalHourlyCnt * 100, 1) : 0;
        $afternoonPct   = $totalHourlyCnt > 0 ? round($afternoonCnt / $totalHourlyCnt * 100, 1) : 0;
        $eveningPct     = $totalHourlyCnt > 0 ? round($eveningCnt   / $totalHourlyCnt * 100, 1) : 0;

        // ── 6. Haftanın günleri ────────────────────────────────────────────
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

        $weekdayQtyRaw = (clone $itemBase)
            ->selectRaw('DAYOFWEEK(table_sessions.opened_at) as dow, SUM(restaurant_order_items.quantity) as qty')
            ->groupByRaw('DAYOFWEEK(table_sessions.opened_at)')
            ->pluck('qty', 'dow');

        $dowLabelsTR    = [1=>'Pazar',2=>'Pazartesi',3=>'Salı',4=>'Çarşamba',5=>'Perşembe',6=>'Cuma',7=>'Cumartesi'];
        $weekdayData    = collect(range(1,7))->mapWithKeys(fn($d) => [$dowLabelsTR[$d] => (int)$weekdayRaw->get($d, 0)]);
        $weekdayQtyData = collect(range(1,7))->mapWithKeys(fn($d) => [$dowLabelsTR[$d] => (int)$weekdayQtyRaw->get($d, 0)]);
        $busiestDay     = $weekdayData->sortDesc()->keys()->first();
        $quietestDay    = $weekdayData->filter(fn($v) => $v > 0)->sortBy(fn($v) => $v)->keys()->first() ?? '-';

        // ── 7. Masa oturma süreleri + buckets ─────────────────────────────
        $sessionDurRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('restaurants.id as restaurant_id, restaurants.name as restaurant_name, ROUND(AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)),1) as avg_min, MIN(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as min_min, MAX(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)) as max_min, COUNT(*) as session_count, ROUND(STDDEV(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)),1) as std_min')
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('avg_min')
            ->get();

        $overallDurRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)),1) as avg_min, ROUND(STDDEV(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)),1) as std_min')
            ->first();

        $overallAvgDur  = round($overallDurRaw->avg_min ?? 0, 1);
        $overallStdDur  = round($overallDurRaw->std_min ?? 0, 1);
        $durCoeffVar    = $overallAvgDur > 0 ? round($overallStdDur / $overallAvgDur * 100, 1) : 0;

        $durBucketsRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw("SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) < 15 THEN 1 ELSE 0 END) as b_under15, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 15 AND 29 THEN 1 ELSE 0 END) as b_15_30, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 30 AND 59 THEN 1 ELSE 0 END) as b_30_60, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 60 AND 89 THEN 1 ELSE 0 END) as b_60_90, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 90 AND 119 THEN 1 ELSE 0 END) as b_90_120, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) >= 120 THEN 1 ELSE 0 END) as b_over120")
            ->first();

        $durBuckets     = ['<15 dk' => (int)($durBucketsRaw->b_under15??0), '15-30 dk' => (int)($durBucketsRaw->b_15_30??0), '30-60 dk' => (int)($durBucketsRaw->b_30_60??0), '60-90 dk' => (int)($durBucketsRaw->b_60_90??0), '90-120 dk' => (int)($durBucketsRaw->b_90_120??0), '>120 dk' => (int)($durBucketsRaw->b_over120??0)];
        $durBucketTotal = array_sum($durBuckets);

        // ── 8. Garson analizi (sadece hacim) ──────────────────────────────
        $waiterStats = RestaurantOrder::query()
            ->join('users',             'users.id',             '=', 'restaurant_orders.created_by')
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->join('restaurant_order_items', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('users.name as waiter_name, COUNT(DISTINCT restaurant_orders.id) as order_count, SUM(restaurant_order_items.quantity) as item_qty, COUNT(DISTINCT restaurant_orders.table_session_id) as session_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('order_count')
            ->limit(15)
            ->get()
            ->map(function ($w) use ($activeDays) {
                $w->orders_per_day  = $activeDays > 0 ? round($w->order_count / $activeDays, 1) : 0;
                $w->items_per_order = $w->order_count > 0 ? round($w->item_qty / $w->order_count, 1) : 0;
                $w->qty_per_session = $w->session_count > 0 ? round($w->item_qty / $w->session_count, 1) : 0;
                return $w;
            });

        $topWaiter         = $waiterStats->first();
        $waiterCount       = $waiterStats->count();
        $avgOrdersPerWaiter = $waiterCount > 0 ? round($totalOrders / $waiterCount, 1) : 0;

        $page_title = 'Misafir Tüketim Analizi';

        return view('modules.orders.guest_analysis', compact(
            'restaurants','restaurantId','dateFrom','dateTo','dayCount','page_title',
            'totalSessions','totalOrders','totalQty','activeDays','distinctItems',
            'avgOrdersPerDay','avgQtyPerSession','avgQtyPerOrder','sessionIntensity',
            'dailyQtyRaw','dailyOrderCountRaw',
            'peakDayQty','peakDayDate','lowestDayQty','lowestDayDate','zeroQtyDays',
            'qtyStdDev','coeffVar','consistencyTR','consistencyColor',
            'week1Qty','week2Qty','weeklyGrowth',
            'restoStats','topResto',
            'topProducts',
            'hourlyData','hourlyQtyData','peakHour','peakHourCnt',
            'morningPct','lunchPct','afternoonPct','eveningPct',
            'weekdayData','weekdayQtyData','busiestDay','quietestDay',
            'sessionDurRaw','overallAvgDur','overallStdDur','durCoeffVar',
            'durBuckets','durBucketTotal',
            'waiterStats','topWaiter','waiterCount','avgOrdersPerWaiter'
        ));
    }
}
