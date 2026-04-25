<?php

namespace App\Http\Controllers\Modules;

use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\TableSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAiAnalysisController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('order_ai_analysis', ['index'], [], [], [], []);
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

        $summaryRaw = (clone $itemBase)->selectRaw('
            SUM(CASE WHEN restaurant_order_items.unit_price > 0
                THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as paid_revenue,
            SUM(CASE WHEN restaurant_order_items.unit_price <= 0 OR restaurant_order_items.unit_price IS NULL
                THEN restaurant_order_items.quantity ELSE 0 END) as free_qty,
            SUM(CASE WHEN restaurant_order_items.unit_price > 0
                THEN restaurant_order_items.quantity ELSE 0 END) as paid_qty,
            SUM(restaurant_order_items.quantity) as total_qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as total_sessions,
            COUNT(DISTINCT restaurant_orders.id) as total_orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days,
            COUNT(DISTINCT restaurant_order_items.item_name) as distinct_items
        ')->first();

        $paidRevenue   = round($summaryRaw->paid_revenue   ?? 0, 2);
        $totalSessions = (int)($summaryRaw->total_sessions ?? 0);
        $totalOrders   = (int)($summaryRaw->total_orders   ?? 0);
        $totalQty      = (int)($summaryRaw->total_qty      ?? 0);
        $paidQtyTotal  = (int)($summaryRaw->paid_qty       ?? 0);
        $freeQtyTotal  = (int)($summaryRaw->free_qty       ?? 0);
        $activeDays    = (int)($summaryRaw->active_days    ?? 0);
        $distinctItems = (int)($summaryRaw->distinct_items ?? 0);
        $avgSessionRev    = $totalSessions > 0 ? round($paidRevenue / $totalSessions, 2)  : 0;
        $avgDailyRev      = $activeDays    > 0 ? round($paidRevenue / $activeDays, 2)      : 0;
        $avgOrdersPerDay  = $activeDays    > 0 ? round($totalOrders  / $activeDays, 1)     : 0;
        $avgQtyPerSession = $totalSessions > 0 ? round($totalQty    / $totalSessions, 1)   : 0;
        $avgQtyPerOrder   = $totalOrders   > 0 ? round($totalQty     / $totalOrders, 2)    : 0;
        $sessionIntensity = $totalSessions > 0 ? round($totalOrders  / $totalSessions, 2)  : 0;
        $paidQtyPct       = $totalQty      > 0 ? round($paidQtyTotal / $totalQty * 100, 1) : 0;
        $freeQtyPct       = $totalQty      > 0 ? round($freeQtyTotal / $totalQty * 100, 1) : 0;

        $dailyRevRaw = (clone $itemBase)
            ->selectRaw('DATE(table_sessions.opened_at) as day, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->groupByRaw('DATE(table_sessions.opened_at)')
            ->orderBy('day')
            ->pluck('revenue', 'day');

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

        $nonZeroDays   = $dailyRevRaw->filter(fn($v) => $v > 0);
        $peakDayRev    = $dailyRevRaw->max() ?? 0;
        $peakDayDate   = $dailyRevRaw->filter(fn($v) => $v == $peakDayRev)->keys()->first();
        $lowestDayRev  = $nonZeroDays->min() ?? 0;
        $lowestDayDate = $nonZeroDays->filter(fn($v) => $v == $lowestDayRev)->keys()->first();
        $zeroRevDays   = $dayCount - $nonZeroDays->count();

        $revValues   = $nonZeroDays->values();
        $revMean     = $revValues->count() > 0 ? $revValues->avg() : 0;
        $revVariance = $revValues->count() > 1
            ? $revValues->reduce(fn($c, $v) => $c + pow($v - $revMean, 2), 0) / ($revValues->count() - 1)
            : 0;
        $revStdDev    = round(sqrt($revVariance), 2);
        $coeffVar     = $revMean > 0 ? round(($revStdDev / $revMean) * 100, 1) : 0;
        $consistency  = $coeffVar <= 20 ? 'Cok Tutarli' : ($coeffVar <= 40 ? 'Tutarli' : ($coeffVar <= 65 ? 'Dalgali' : 'Yuksek Volatil'));
        $consistencyTR = $coeffVar <= 20 ? 'Çok Tutarlı' : ($coeffVar <= 40 ? 'Tutarlı' : ($coeffVar <= 65 ? 'Dalgalı' : 'Yüksek Volatil'));
        $consistencyColor = $coeffVar <= 20 ? '#15803d' : ($coeffVar <= 40 ? '#0ea5e9' : ($coeffVar <= 65 ? '#d97706' : '#dc2626'));
        $peakToAvgRatio   = $avgDailyRev > 0 ? round($peakDayRev / $avgDailyRev, 2) : 0;

        $w2End   = Carbon::parse($dateTo);
        $w2Start = $w2End->copy()->subDays(6);
        $w1End   = $w2Start->copy()->subDay();
        $w1Start = $w1End->copy()->subDays(6);
        $week1Rev     = $dailyRevRaw->filter(fn($v, $k) => $k >= $w1Start->toDateString() && $k <= $w1End->toDateString())->sum();
        $week2Rev     = $dailyRevRaw->filter(fn($v, $k) => $k >= $w2Start->toDateString() && $k <= $w2End->toDateString())->sum();
        $weeklyGrowth = ($week1Rev > 0) ? round((($week2Rev - $week1Rev) / $week1Rev) * 100, 1) : null;

        $restoStats = (clone $itemBase)->selectRaw('
            restaurants.id as restaurant_id,
            restaurants.name as restaurant_name,
            SUM(CASE WHEN restaurant_order_items.unit_price > 0
                THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue,
            SUM(restaurant_order_items.quantity) as qty,
            COUNT(DISTINCT restaurant_orders.table_session_id) as sessions,
            COUNT(DISTINCT restaurant_orders.id) as orders,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days,
            COUNT(DISTINCT restaurant_order_items.item_name) as distinct_items,
            SUM(CASE WHEN restaurant_order_items.unit_price <= 0 OR restaurant_order_items.unit_price IS NULL
                THEN restaurant_order_items.quantity ELSE 0 END) as free_qty
        ')
        ->groupBy('restaurants.id', 'restaurants.name')
        ->orderByDesc('revenue')
        ->get()
        ->map(function ($r) {
            $r->per_session = $r->sessions    > 0 ? round($r->revenue / $r->sessions, 2)    : 0;
            $r->per_order   = $r->orders      > 0 ? round($r->revenue / $r->orders, 2)      : 0;
            $r->per_day     = $r->active_days > 0 ? round($r->revenue / $r->active_days, 2) : 0;
            $r->free_pct    = $r->qty         > 0 ? round($r->free_qty / $r->qty * 100, 1)  : 0;
            $r->intensity   = $r->sessions    > 0 ? round($r->orders  / $r->sessions, 2)    : 0;
            return $r;
        });

        $topResto    = $restoStats->first();
        $bottomResto = $restoStats->count() > 1 ? $restoStats->last() : null;

        $hhiIndex = $paidRevenue > 0
            ? round($restoStats->sum(fn($r) => pow($r->revenue / $paidRevenue * 100, 2)))
            : 0;
        $hhiLabel = $hhiIndex > 5000 ? 'Yuksek yogunlasma' : ($hhiIndex > 2500 ? 'Orta yogunlasma' : 'Dengeli');
        $hhiLabelTR = $hhiIndex > 5000 ? 'Yüksek yoğunlaşma' : ($hhiIndex > 2500 ? 'Orta yoğunlaşma' : 'Dengeli dağılım');

        $tableCountByResto = DB::table('restaurant_tables')
            ->selectRaw('restaurant_id, COUNT(*) as table_count')
            ->groupBy('restaurant_id')
            ->pluck('table_count', 'restaurant_id');

        $healthScores = $restoStats->map(function ($r) use ($paidRevenue, $activeDays) {
            $score = 0;
            $revShare  = $paidRevenue > 0 ? $r->revenue / $paidRevenue * 100 : 0;
            $score += $revShare >= 40 ? 20 : ($revShare >= 20 ? 15 : ($revShare >= 5 ? 10 : 5));
            $score += $r->per_session >= 500 ? 20 : ($r->per_session >= 200 ? 15 : ($r->per_session >= 100 ? 10 : 5));
            $activePct = $activeDays > 0 ? $r->active_days / $activeDays * 100 : 0;
            $score += $activePct >= 85 ? 20 : ($activePct >= 60 ? 15 : ($activePct >= 40 ? 10 : 5));
            $score += $r->free_pct < 15 ? 20 : ($r->free_pct < 30 ? 15 : ($r->free_pct < 50 ? 10 : 5));
            $score += $r->intensity >= 2 ? 20 : ($r->intensity >= 1.5 ? 15 : ($r->intensity >= 1 ? 10 : 5));
            $r->health_score = $score;
            $r->health_label = $score >= 80 ? 'Mukemmel' : ($score >= 65 ? 'Iyi' : ($score >= 50 ? 'Orta' : 'Iyilestirme Gerekli'));
            $r->health_labelTR = $score >= 80 ? 'Mükemmel' : ($score >= 65 ? 'İyi' : ($score >= 50 ? 'Orta' : 'İyileştirme Gerekli'));
            $r->health_color = $score >= 80 ? '#15803d' : ($score >= 65 ? '#0ea5e9' : ($score >= 50 ? '#d97706' : '#dc2626'));
            return $r;
        });

        $topPaidProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            SUM(restaurant_order_items.quantity) as qty,
            SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue,
            AVG(restaurant_order_items.unit_price) as avg_price,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days_sold
        ')
        ->where('restaurant_order_items.unit_price', '>', 0)
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('revenue')
        ->limit(20)
        ->get()
        ->map(function ($p) use ($activeDays) {
            $p->velocity = $activeDays > 0 ? round($p->qty / $activeDays, 2) : 0;
            return $p;
        });

        $topFreeProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            SUM(restaurant_order_items.quantity) as qty,
            COUNT(DISTINCT DATE(table_sessions.opened_at)) as active_days_sold
        ')
        ->where(fn($q) => $q->whereNull('restaurant_order_items.unit_price')
                            ->orWhere('restaurant_order_items.unit_price', '<=', 0))
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('qty')
        ->limit(15)
        ->get();

        $distinctPaidItems = (clone $itemBase)
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->distinct()->count('restaurant_order_items.item_name');
        $distinctFreeItems = (clone $itemBase)
            ->where(fn($q) => $q->whereNull('restaurant_order_items.unit_price')
                                ->orWhere('restaurant_order_items.unit_price', '<=', 0))
            ->distinct()->count('restaurant_order_items.item_name');

        $paretoProductCount = 0;
        $paretoAcc = 0;
        foreach ($topPaidProducts as $p) {
            $paretoAcc += $p->revenue;
            $paretoProductCount++;
            if ($paidRevenue > 0 && $paretoAcc >= $paidRevenue * 0.8) break;
        }
        $paretoRatio = $distinctPaidItems > 0 ? round($paretoProductCount / $distinctPaidItems * 100, 1) : 0;

        $premiumProducts = (clone $itemBase)->selectRaw('
            restaurant_order_items.item_name,
            AVG(restaurant_order_items.unit_price) as avg_price,
            SUM(restaurant_order_items.quantity) as qty,
            SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity) as revenue
        ')
        ->where('restaurant_order_items.unit_price', '>', 0)
        ->groupBy('restaurant_order_items.item_name')
        ->orderByDesc('avg_price')
        ->limit(10)
        ->get();

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

        $hourlyRevRaw = (clone $itemBase)
            ->selectRaw('HOUR(table_sessions.opened_at) as hour, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->groupByRaw('HOUR(table_sessions.opened_at)')
            ->pluck('revenue', 'hour');

        $hourlyData    = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => (int)$hourlyRaw->get($h, 0)]);
        $hourlyRevData = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => (float)($hourlyRevRaw->get($h, 0))]);
        $peakHour      = $hourlyData->sortDesc()->keys()->first();
        $peakHourCnt   = $hourlyData->max();
        $peakRevHour   = $hourlyRevData->sortDesc()->keys()->first();

        $morningCnt     = $hourlyData->filter(fn($v, $k) => $k >= 6  && $k <= 11)->sum();
        $lunchCnt       = $hourlyData->filter(fn($v, $k) => $k >= 12 && $k <= 14)->sum();
        $afternoonCnt   = $hourlyData->filter(fn($v, $k) => $k >= 15 && $k <= 17)->sum();
        $eveningCnt     = $hourlyData->filter(fn($v, $k) => $k >= 18 && $k <= 23)->sum();
        $totalHourlyCnt = $morningCnt + $lunchCnt + $afternoonCnt + $eveningCnt;
        $morningPct     = $totalHourlyCnt > 0 ? round($morningCnt   / $totalHourlyCnt * 100, 1) : 0;
        $lunchPct       = $totalHourlyCnt > 0 ? round($lunchCnt     / $totalHourlyCnt * 100, 1) : 0;
        $afternoonPct   = $totalHourlyCnt > 0 ? round($afternoonCnt / $totalHourlyCnt * 100, 1) : 0;
        $eveningPct     = $totalHourlyCnt > 0 ? round($eveningCnt   / $totalHourlyCnt * 100, 1) : 0;

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

        $weekdayRevRaw = (clone $itemBase)
            ->selectRaw('DAYOFWEEK(table_sessions.opened_at) as dow, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->groupByRaw('DAYOFWEEK(table_sessions.opened_at)')
            ->pluck('revenue', 'dow');

        $dowLabels      = [1=>'Pazar',2=>'Pazartesi',3=>'Sali',4=>'Carsamba',5=>'Persembe',6=>'Cuma',7=>'Cumartesi'];
        $dowLabelsTR    = [1=>'Pazar',2=>'Pazartesi',3=>'Salı',4=>'Çarşamba',5=>'Perşembe',6=>'Cuma',7=>'Cumartesi'];
        $weekdayData    = collect(range(1,7))->mapWithKeys(fn($d) => [$dowLabelsTR[$d] => (int)$weekdayRaw->get($d, 0)]);
        $weekdayRevData = collect(range(1,7))->mapWithKeys(fn($d) => [$dowLabelsTR[$d] => round((float)$weekdayRevRaw->get($d, 0), 2)]);
        $busiestDay     = $weekdayData->sortDesc()->keys()->first();
        $quietestDay    = $weekdayData->filter(fn($v) => $v > 0)->sortBy(fn($v) => $v)->keys()->first() ?? '-';
        $busiestRevDay  = $weekdayRevData->sortDesc()->keys()->first();

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
        $longestStayResto  = $sessionDurRaw->sortByDesc('avg_min')->first();
        $shortestStayResto = $sessionDurRaw->filter(fn($r) => $r->session_count >= 2)->sortBy('avg_min')->first();

        $durBucketsRaw = TableSession::query()
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->whereNotNull('table_sessions.closed_at')
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom)
            ->whereDate('table_sessions.opened_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw("SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) < 15 THEN 1 ELSE 0 END) as b_under15, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 15 AND 29 THEN 1 ELSE 0 END) as b_15_30, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 30 AND 59 THEN 1 ELSE 0 END) as b_30_60, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 60 AND 89 THEN 1 ELSE 0 END) as b_60_90, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) BETWEEN 90 AND 119 THEN 1 ELSE 0 END) as b_90_120, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at) >= 120 THEN 1 ELSE 0 END) as b_over120")
            ->first();

        $durBuckets = ['<15 dk' => (int)($durBucketsRaw->b_under15??0), '15-30 dk' => (int)($durBucketsRaw->b_15_30??0), '30-60 dk' => (int)($durBucketsRaw->b_30_60??0), '60-90 dk' => (int)($durBucketsRaw->b_60_90??0), '90-120 dk' => (int)($durBucketsRaw->b_90_120??0), '>120 dk' => (int)($durBucketsRaw->b_over120??0)];
        $durBucketTotal = array_sum($durBuckets);

        $waiterStats = RestaurantOrder::query()
            ->join('users',             'users.id',             '=', 'restaurant_orders.created_by')
            ->join('table_sessions',    'table_sessions.id',    '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants',       'restaurants.id',       '=', 'restaurant_tables.restaurant_id')
            ->join('restaurant_order_items', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
            ->whereDate('restaurant_orders.created_at', '>=', $dateFrom)
            ->whereDate('restaurant_orders.created_at', '<=', $dateTo)
            ->when($restaurantId, fn($q) => $q->where('restaurants.id', $restaurantId))
            ->selectRaw('users.name as waiter_name, COUNT(DISTINCT restaurant_orders.id) as order_count, SUM(restaurant_order_items.quantity) as item_qty, COUNT(DISTINCT restaurant_orders.table_session_id) as session_count, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(15)
            ->get()
            ->map(function ($w) use ($activeDays) {
                $w->orders_per_day  = $activeDays > 0 ? round($w->order_count / $activeDays, 1) : 0;
                $w->rev_per_session = $w->session_count > 0 ? round($w->revenue / $w->session_count, 2) : 0;
                $w->items_per_order = $w->order_count  > 0 ? round($w->item_qty / $w->order_count, 1)   : 0;
                return $w;
            });

        $topWaiter         = $waiterStats->first();
        $waiterCount       = $waiterStats->count();
        $avgOrdersPerWaiter = $waiterCount > 0 ? round($totalOrders  / $waiterCount, 1) : 0;
        $avgRevPerWaiter   = $waiterCount > 0 ? round($paidRevenue   / $waiterCount, 2) : 0;

        $productsByResto = (clone $itemBase)
            ->selectRaw('restaurants.name as restaurant_name, restaurant_order_items.item_name, SUM(restaurant_order_items.quantity) as qty, SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END) as revenue')
            ->groupBy('restaurants.name', 'restaurant_order_items.item_name')
            ->orderBy('restaurants.name')->orderByDesc('qty')
            ->get()->groupBy('restaurant_name');

        $page_title = 'Stratejik Sipariş Analizi';

        return view('modules.orders.ai_analysis', compact(
            'restaurants','restaurantId','dateFrom','dateTo','dayCount','page_title',
            'paidRevenue','totalSessions','totalOrders','totalQty','activeDays',
            'avgSessionRev','avgDailyRev','avgOrdersPerDay','avgQtyPerOrder',
            'avgQtyPerSession','sessionIntensity','paidQtyTotal','freeQtyTotal','paidQtyPct','freeQtyPct','distinctItems',
            'dailyRevRaw','dailyOrderCountRaw','peakDayRev','peakDayDate','lowestDayRev','lowestDayDate',
            'zeroRevDays','nonZeroDays','revStdDev','coeffVar','consistencyTR','consistencyColor','peakToAvgRatio',
            'week1Rev','week2Rev','weeklyGrowth',
            'restoStats','healthScores','topResto','bottomResto','hhiIndex','hhiLabelTR','tableCountByResto',
            'topPaidProducts','topFreeProducts','premiumProducts','distinctPaidItems','distinctFreeItems','paretoProductCount','paretoRatio',
            'hourlyData','hourlyRevData','peakHour','peakHourCnt','peakRevHour',
            'morningPct','lunchPct','afternoonPct','eveningPct',
            'weekdayData','weekdayRevData','busiestDay','quietestDay','busiestRevDay',
            'sessionDurRaw','overallAvgDur','overallStdDur','durCoeffVar','longestStayResto','shortestStayResto','durBuckets','durBucketTotal',
            'waiterStats','topWaiter','waiterCount','avgOrdersPerWaiter','avgRevPerWaiter',
            'productsByResto'
        ));
    }
}
