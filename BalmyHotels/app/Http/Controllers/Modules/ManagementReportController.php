<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\DoorLog;
use App\Models\Fault;
use App\Models\Restaurant;
use App\Models\RestaurantOrderItem;
use App\Models\ShuttleTrip;
use App\Models\ShuttleVehicle;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagementReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'yonetim_kurulu_rapor',
            ['managerDoorLogs', 'technicalFaults', 'shuttleServices'],
            [],
            [],
            [],
            []
        );

        $this->middleware('perm:yonetim_siparis_raporu,index')->only(['orderConsumption']);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasPermission('yonetim_kurulu_rapor', 'index')) {
            return redirect()->route('management-reports.manager-door-logs');
        }

        if ($user->hasPermission('yonetim_siparis_raporu', 'index')) {
            return redirect()->route('management-reports.order-consumption');
        }

        abort(403);
    }

    public function managerDoorLogs(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        [$dateFrom, $dateTo] = $this->datePeriod($request, 'month');
        $branchId = $this->selectedBranchId($request, $branchIds);
        $branches = $this->branchList($branchIds);
        $periodDays = $this->periodDays($dateFrom, $dateTo);

        $managers = User::with(['branch', 'department', 'userRoles'])
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->whereHas('userRoles', fn ($q) => $q->whereIn('role_name', ['dept_manager', 'branch_manager', 'super_admin']))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $managerIds = $managers->pluck('id');
        $logs = $managerIds->isEmpty()
            ? collect()
            : DoorLog::with(['user.branch', 'user.department', 'branch'])
                ->whereIn('user_id', $managerIds)
                ->whereIn('branch_id', $branchIds)
                ->whereBetween('logged_at', [$dateFrom, $dateTo])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('user_id')
                ->orderBy('logged_at')
                ->get();

        $latestLogIds = $managerIds->isEmpty()
            ? collect()
            : DoorLog::selectRaw('MAX(id) as max_id')
                ->whereIn('user_id', $managerIds)
                ->whereIn('branch_id', $branchIds)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->groupBy('user_id')
                ->pluck('max_id');

        $latestLogs = $latestLogIds->isEmpty()
            ? collect()
            : DoorLog::whereIn('id', $latestLogIds)->get()->keyBy('user_id');

        $dailyMinutes = [];
        $managerStats = collect();

        foreach ($managers as $manager) {
            $userLogs  = $logs->where('user_id', $manager->id)->sortBy('logged_at')->values();
            $entrances = $userLogs->where('type', 'giris')->sortBy('logged_at')->values();
            $exits     = $userLogs->where('type', 'cikis')->sortBy('logged_at')->values();
            $work      = $this->calculateDoorWork($entrances, $exits);

            foreach ($work['daily_minutes'] as $day => $minutes) {
                $dailyMinutes[$day] = ($dailyMinutes[$day] ?? 0) + $minutes;
            }

            $workedDays = $entrances
                ->map(fn ($log) => Carbon::parse($log->logged_at)->format('Y-m-d'))
                ->unique()
                ->count();

            $latest = $latestLogs->get($manager->id);

            $managerStats->push([
                'manager'          => $manager,
                'branch'           => $manager->branch?->name ?? '-',
                'department'       => $manager->department?->name ?? '-',
                'entry_count'      => $entrances->count(),
                'exit_count'       => $exits->count(),
                'worked_days'      => $workedDays,
                'attendance_rate'  => $periodDays > 0 ? round($workedDays / $periodDays * 100) : 0,
                'total_minutes'    => $work['total_minutes'],
                'total_hours'      => round($work['total_minutes'] / 60, 1),
                'avg_daily_hours'  => $workedDays > 0 ? round(($work['total_minutes'] / 60) / $workedDays, 1) : 0,
                'missing_exits'    => max(0, $entrances->count() - $exits->count()),
                'inside_now'       => $latest?->type === 'giris',
                'last_seen'        => $latest?->logged_at,
                'avg_entry_time'   => $this->averageLogTime($entrances),
                'avg_exit_time'    => $this->averageLogTime($exits),
            ]);
        }

        $managerStats = $managerStats
            ->sortByDesc('total_minutes')
            ->values();

        $activeManagers = $managerStats->filter(fn ($row) => ($row['entry_count'] + $row['exit_count']) > 0);

        $summary = [
            'total_managers'  => $managers->count(),
            'active_managers' => $activeManagers->count(),
            'inside_now'      => $managerStats->where('inside_now', true)->count(),
            'total_entries'   => $logs->where('type', 'giris')->count(),
            'total_exits'     => $logs->where('type', 'cikis')->count(),
            'total_hours'     => round($managerStats->sum('total_minutes') / 60, 1),
            'missing_exits'   => $managerStats->sum('missing_exits'),
            'avg_hours'       => $activeManagers->count() > 0
                ? round(($managerStats->sum('total_minutes') / 60) / $activeManagers->count(), 1)
                : 0,
        ];

        $departmentSummary = $managerStats
            ->groupBy('department')
            ->map(fn ($rows, $department) => [
                'department'      => $department,
                'manager_count'   => $rows->count(),
                'active_count'    => $rows->filter(fn ($row) => ($row['entry_count'] + $row['exit_count']) > 0)->count(),
                'total_hours'     => round($rows->sum('total_minutes') / 60, 1),
                'avg_daily_hours' => $rows->sum('worked_days') > 0
                    ? round(($rows->sum('total_minutes') / 60) / $rows->sum('worked_days'), 1)
                    : 0,
                'inside_now'      => $rows->where('inside_now', true)->count(),
                'missing_exits'   => $rows->sum('missing_exits'),
            ])
            ->sortByDesc('total_hours')
            ->values();

        $branchSummary = $managerStats
            ->groupBy('branch')
            ->map(fn ($rows, $branch) => [
                'branch'          => $branch,
                'manager_count'   => $rows->count(),
                'active_count'    => $rows->filter(fn ($row) => ($row['entry_count'] + $row['exit_count']) > 0)->count(),
                'total_hours'     => round($rows->sum('total_minutes') / 60, 1),
                'inside_now'      => $rows->where('inside_now', true)->count(),
                'missing_exits'   => $rows->sum('missing_exits'),
            ])
            ->sortByDesc('total_hours')
            ->values();

        $dailyLabels = [];
        $dailyHours = [];
        $dailyManagers = [];
        foreach (CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $dayLogs = $logs->filter(fn ($log) => Carbon::parse($log->logged_at)->format('Y-m-d') === $key);
            $dailyLabels[]   = $day->locale('tr')->isoFormat('D MMM');
            $dailyHours[]    = round(($dailyMinutes[$key] ?? 0) / 60, 1);
            $dailyManagers[] = $dayLogs->pluck('user_id')->unique()->count();
        }

        $insideManagers = $managerStats
            ->where('inside_now', true)
            ->sortBy(fn ($row) => $row['manager']->name)
            ->values();

        $insights = $this->managerDoorInsights($summary, $dateFrom, $dateTo);
        $page_title = 'Müdür Giriş Çıkışları';

        return view('modules.management_reports.manager_door_logs', compact(
            'page_title',
            'dateFrom',
            'dateTo',
            'branchId',
            'branches',
            'summary',
            'managerStats',
            'departmentSummary',
            'branchSummary',
            'insideManagers',
            'dailyLabels',
            'dailyHours',
            'dailyManagers',
            'insights'
        ));
    }

    public function technicalFaults(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        [$dateFrom, $dateTo] = $this->datePeriod($request, 'last_30');
        $branchId = $this->selectedBranchId($request, $branchIds);
        $branches = $this->branchList($branchIds);
        $periodDays = $this->periodDays($dateFrom, $dateTo);

        $faults = Fault::with(['branch', 'department', 'faultType', 'faultLocation', 'faultArea', 'reporter'])
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('created_at', 'desc')
            ->get();

        $active = $faults->reject(fn ($fault) => in_array($fault->status, ['resolved', 'closed'], true));
        $resolved = $faults->filter(fn ($fault) => $fault->resolved_at);
        $closed = $faults->filter(fn ($fault) => in_array($fault->status, ['resolved', 'closed'], true));
        $slaOnTime = $resolved->filter(fn ($fault) =>
            $fault->created_at->diffInMinutes($fault->resolved_at) / 60 <= ($fault->faultType?->completion_hours ?? 24)
        )->count();

        $previousCount = Fault::whereIn('branch_id', $branchIds)
            ->whereBetween('created_at', [
                $dateFrom->copy()->subDays($periodDays),
                $dateFrom->copy()->subSecond(),
            ])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $summary = [
            'total'           => $faults->count(),
            'active'          => $active->count(),
            'closed'          => $closed->count(),
            'critical_active' => $active->whereIn('priority', ['high', 'critical'])->count(),
            'avg_hours'       => $resolved->count() > 0
                ? round($resolved->avg(fn ($fault) => $fault->created_at->diffInMinutes($fault->resolved_at) / 60), 1)
                : null,
            'sla_pct'         => $resolved->count() > 0 ? round($slaOnTime / $resolved->count() * 100) : null,
            'closed_pct'      => $faults->count() > 0 ? round($closed->count() / $faults->count() * 100) : 0,
            'previous_total'  => $previousCount,
            'period_diff'     => $faults->count() - $previousCount,
        ];

        $byDepartment = $faults
            ->groupBy('assigned_department_id')
            ->map(fn ($rows) => array_merge([
                'name' => $rows->first()->department?->name ?? 'Belirtilmemiş',
            ], $this->faultGroupMetrics($rows)))
            ->sortByDesc('total')
            ->values();

        $byType = $faults
            ->groupBy('fault_type_id')
            ->map(fn ($rows) => array_merge([
                'name' => $rows->first()->faultType?->name ?? 'Belirtilmemiş',
                'target_hours' => $rows->first()->faultType?->completion_hours ?? 24,
            ], $this->faultGroupMetrics($rows)))
            ->sortByDesc('total')
            ->values();

        $byLocation = $faults
            ->groupBy('fault_location_id')
            ->map(function ($rows) {
                $topType = $rows->groupBy('fault_type_id')
                    ->map(fn ($typeRows) => [
                        'name' => $typeRows->first()->faultType?->name ?? 'Belirtilmemiş',
                        'count' => $typeRows->count(),
                    ])
                    ->sortByDesc('count')
                    ->first();

                return array_merge([
                    'name' => $rows->first()->faultLocation?->name ?? 'Belirtilmemiş',
                    'top_type' => $topType['name'] ?? '-',
                ], $this->faultGroupMetrics($rows));
            })
            ->sortByDesc('total')
            ->values();

        $byArea = $faults
            ->filter(fn ($fault) => $fault->fault_area_id)
            ->groupBy('fault_area_id')
            ->map(function ($rows) {
                $topType = $rows->groupBy('fault_type_id')
                    ->map(fn ($typeRows) => [
                        'name' => $typeRows->first()->faultType?->name ?? 'Belirtilmemiş',
                        'count' => $typeRows->count(),
                    ])
                    ->sortByDesc('count')
                    ->first();

                return array_merge([
                    'name' => $rows->first()->faultArea?->name ?? 'Belirtilmemiş',
                    'location' => $rows->first()->faultLocation?->name ?? '-',
                    'top_type' => $topType['name'] ?? '-',
                ], $this->faultGroupMetrics($rows));
            })
            ->sortByDesc('total')
            ->take(15)
            ->values();

        $statusDistribution = $faults
            ->groupBy('status')
            ->map(fn ($rows, $status) => [
                'status' => $status,
                'label' => Fault::STATUSES[$status] ?? $status,
                'total' => $rows->count(),
            ])
            ->values();

        $priorityDistribution = $faults
            ->groupBy('priority')
            ->map(fn ($rows, $priority) => [
                'priority' => $priority,
                'label' => Fault::PRIORITIES[$priority] ?? $priority,
                'total' => $rows->count(),
            ])
            ->values();

        $dailyLabels = [];
        $dailyOpened = [];
        $dailyClosed = [];
        foreach (CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $dayFaults = $faults->filter(fn ($fault) => $fault->created_at->format('Y-m-d') === $key);
            $dailyLabels[] = $day->locale('tr')->isoFormat('D MMM');
            $dailyOpened[] = $dayFaults->count();
            $dailyClosed[] = $dayFaults->filter(fn ($fault) => $fault->resolved_at && $fault->resolved_at->format('Y-m-d') === $key)->count();
        }

        $criticalActiveFaults = $active
            ->whereIn('priority', ['high', 'critical'])
            ->sortBy('created_at')
            ->take(8)
            ->values();

        $insights = $this->technicalFaultInsights($summary, $byDepartment, $byType, $byLocation);
        $page_title = 'Teknik Arıza Raporu';

        return view('modules.management_reports.technical_faults', compact(
            'page_title',
            'dateFrom',
            'dateTo',
            'branchId',
            'branches',
            'summary',
            'byDepartment',
            'byType',
            'byLocation',
            'byArea',
            'statusDistribution',
            'priorityDistribution',
            'dailyLabels',
            'dailyOpened',
            'dailyClosed',
            'criticalActiveFaults',
            'insights'
        ));
    }

    public function shuttleServices(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        [$dateFrom, $dateTo] = $this->datePeriod($request, 'month');
        $branchId = $this->selectedBranchId($request, $branchIds);
        $branches = $this->branchList($branchIds);
        $periodDays = $this->periodDays($dateFrom, $dateTo);

        $trips = ShuttleTrip::with(['vehicle', 'route', 'branch'])
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('trip_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('trip_date')
            ->orderBy('shift')
            ->get();

        $vehicles = ShuttleVehicle::whereIn('branch_id', $branchIds)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $occupancy = $trips
            ->map(function ($trip) {
                $capacity = $trip->vehicle?->capacity ?? 0;
                if ($capacity <= 0) {
                    return null;
                }

                return (($trip->arrival_count + $trip->departure_count) / ($capacity * 2)) * 100;
            })
            ->filter(fn ($value) => $value !== null);

        $summary = [
            'total_trips'         => $trips->count(),
            'total_arrival'       => $trips->sum('arrival_count'),
            'total_departure'     => $trips->sum('departure_count'),
            'total_movement'      => $trips->sum('arrival_count') + $trips->sum('departure_count'),
            'avg_daily_arrival'   => round($trips->sum('arrival_count') / max(1, $periodDays), 1),
            'avg_daily_departure' => round($trips->sum('departure_count') / max(1, $periodDays), 1),
            'avg_occupancy'       => $occupancy->count() > 0 ? round($occupancy->avg(), 1) : null,
            'active_vehicle_count'=> $trips->pluck('shuttle_vehicle_id')->unique()->count(),
            'vehicle_count'       => $vehicles->count(),
        ];

        $byVehicle = $trips
            ->groupBy('shuttle_vehicle_id')
            ->map(function ($rows) {
                $vehicle = $rows->first()->vehicle;
                $capacity = $vehicle?->capacity ?? 0;
                $movement = $rows->sum('arrival_count') + $rows->sum('departure_count');

                return [
                    'name' => $vehicle?->name ?? 'Belirtilmemiş araç',
                    'plate' => $vehicle?->plate,
                    'capacity' => $capacity,
                    'trips' => $rows->count(),
                    'arrival' => $rows->sum('arrival_count'),
                    'departure' => $rows->sum('departure_count'),
                    'movement' => $movement,
                    'occupancy' => $capacity > 0 && $rows->count() > 0
                        ? round($movement / ($capacity * 2 * $rows->count()) * 100, 1)
                        : null,
                ];
            })
            ->sortByDesc('movement')
            ->values();

        $byRoute = $trips
            ->groupBy(fn ($trip) => $trip->route_id ?: 'none')
            ->map(fn ($rows) => [
                'name' => $rows->first()->route?->name ?? 'Güzergah belirtilmemiş',
                'trips' => $rows->count(),
                'arrival' => $rows->sum('arrival_count'),
                'departure' => $rows->sum('departure_count'),
                'movement' => $rows->sum('arrival_count') + $rows->sum('departure_count'),
            ])
            ->sortByDesc('movement')
            ->values();

        $byShift = $trips
            ->groupBy('shift')
            ->map(fn ($rows, $shift) => [
                'name' => $shift ?: 'Belirtilmemiş',
                'trips' => $rows->count(),
                'arrival' => $rows->sum('arrival_count'),
                'departure' => $rows->sum('departure_count'),
                'movement' => $rows->sum('arrival_count') + $rows->sum('departure_count'),
            ])
            ->sortByDesc('movement')
            ->values();

        $branchSummary = $trips
            ->groupBy('branch_id')
            ->map(fn ($rows) => [
                'name' => $rows->first()->branch?->name ?? 'Belirtilmemiş',
                'trips' => $rows->count(),
                'arrival' => $rows->sum('arrival_count'),
                'departure' => $rows->sum('departure_count'),
                'movement' => $rows->sum('arrival_count') + $rows->sum('departure_count'),
            ])
            ->sortByDesc('movement')
            ->values();

        $dailyLabels = [];
        $dailyArrival = [];
        $dailyDeparture = [];
        foreach (CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $dayTrips = $trips->filter(fn ($trip) => $trip->trip_date->format('Y-m-d') === $key);
            $dailyLabels[] = $day->locale('tr')->isoFormat('D MMM');
            $dailyArrival[] = $dayTrips->sum('arrival_count');
            $dailyDeparture[] = $dayTrips->sum('departure_count');
        }

        $insights = $this->shuttleInsights($summary, $byVehicle, $byRoute, $byShift);
        $page_title = 'Servis Raporu';

        return view('modules.management_reports.shuttle_services', compact(
            'page_title',
            'dateFrom',
            'dateTo',
            'branchId',
            'branches',
            'summary',
            'byVehicle',
            'byRoute',
            'byShift',
            'branchSummary',
            'dailyLabels',
            'dailyArrival',
            'dailyDeparture',
            'insights'
        ));
    }

    public function orderConsumption(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        [$dateFrom, $dateTo] = $this->datePeriod($request, 'month');
        $branchId = $this->selectedBranchId($request, $branchIds);
        $restaurantId = $this->selectedRestaurantId($request, $branchIds, $branchId);
        $branches = $this->branchList($branchIds);
        $restaurants = Restaurant::with('branch')
            ->whereIn('branch_id', $branchIds)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();
        $periodDays = $this->periodDays($dateFrom, $dateTo);

        $itemBase = RestaurantOrderItem::query()
            ->join('restaurant_orders', 'restaurant_orders.id', '=', 'restaurant_order_items.order_id')
            ->join('table_sessions', 'table_sessions.id', '=', 'restaurant_orders.table_session_id')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'table_sessions.restaurant_table_id')
            ->join('restaurants', 'restaurants.id', '=', 'restaurant_tables.restaurant_id')
            ->leftJoin('branches', 'branches.id', '=', 'restaurants.branch_id')
            ->leftJoin('qr_menus', 'qr_menus.id', '=', 'restaurants.qr_menu_id')
            ->whereIn('restaurants.branch_id', $branchIds)
            ->whereDate('table_sessions.opened_at', '>=', $dateFrom->toDateString())
            ->whereDate('table_sessions.opened_at', '<=', $dateTo->toDateString())
            ->when($branchId, fn ($q) => $q->where('restaurants.branch_id', $branchId))
            ->when($restaurantId, fn ($q) => $q->where('restaurants.id', $restaurantId));

        $summaryRaw = (clone $itemBase)
            ->selectRaw('
                COALESCE(SUM(restaurant_order_items.quantity), 0) as total_qty,
                COUNT(DISTINCT restaurant_orders.id) as total_orders,
                COUNT(DISTINCT table_sessions.id) as total_sessions,
                COUNT(DISTINCT restaurants.id) as active_restaurants,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as priced_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price IS NULL OR restaurant_order_items.unit_price <= 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as inclusive_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END), 0) as recorded_value
            ')
            ->first();

        $totalQty = (int) ($summaryRaw->total_qty ?? 0);
        $totalOrders = (int) ($summaryRaw->total_orders ?? 0);
        $totalSessions = (int) ($summaryRaw->total_sessions ?? 0);
        $inclusiveQty = (int) ($summaryRaw->inclusive_qty ?? 0);
        $pricedQty = (int) ($summaryRaw->priced_qty ?? 0);

        $summary = [
            'total_qty'              => $totalQty,
            'total_orders'           => $totalOrders,
            'total_sessions'         => $totalSessions,
            'active_restaurants'     => (int) ($summaryRaw->active_restaurants ?? 0),
            'inclusive_qty'          => $inclusiveQty,
            'priced_qty'             => $pricedQty,
            'inclusive_pct'          => $totalQty > 0 ? round($inclusiveQty / $totalQty * 100) : 0,
            'priced_pct'             => $totalQty > 0 ? round($pricedQty / $totalQty * 100) : 0,
            'recorded_value'         => round((float) ($summaryRaw->recorded_value ?? 0), 2),
            'avg_qty_per_session'    => $totalSessions > 0 ? round($totalQty / $totalSessions, 1) : 0,
            'avg_orders_per_session' => $totalSessions > 0 ? round($totalOrders / $totalSessions, 1) : 0,
            'avg_daily_qty'          => round($totalQty / max(1, $periodDays), 1),
        ];

        $byRestaurant = (clone $itemBase)
            ->selectRaw('
                restaurants.id as restaurant_id,
                restaurants.name as restaurant_name,
                COALESCE(branches.name, \'-\') as branch_name,
                COALESCE(qr_menus.name, \'\') as menu_name,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as total_qty,
                COUNT(DISTINCT restaurant_orders.id) as total_orders,
                COUNT(DISTINCT table_sessions.id) as total_sessions,
                COUNT(DISTINCT restaurant_order_items.item_name) as item_variety,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as priced_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price IS NULL OR restaurant_order_items.unit_price <= 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as inclusive_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END), 0) as recorded_value
            ')
            ->groupBy('restaurants.id', 'restaurants.name', 'branches.name', 'qr_menus.name')
            ->get()
            ->map(function ($row) {
                $row->total_qty = (int) $row->total_qty;
                $row->total_orders = (int) $row->total_orders;
                $row->total_sessions = (int) $row->total_sessions;
                $row->item_variety = (int) $row->item_variety;
                $row->priced_qty = (int) $row->priced_qty;
                $row->inclusive_qty = (int) $row->inclusive_qty;
                $row->recorded_value = round((float) $row->recorded_value, 2);
                $row->avg_qty_per_session = $row->total_sessions > 0 ? round($row->total_qty / $row->total_sessions, 1) : 0;
                $row->avg_orders_per_session = $row->total_sessions > 0 ? round($row->total_orders / $row->total_sessions, 1) : 0;
                $row->inclusive_pct = $row->total_qty > 0 ? round($row->inclusive_qty / $row->total_qty * 100) : 0;
                $row->outlet_type = $this->outletType($row->restaurant_name, $row->menu_name);

                return $row;
            })
            ->sortByDesc('total_qty')
            ->values();

        $outletSummary = $byRestaurant
            ->groupBy('outlet_type')
            ->map(fn ($rows, $type) => [
                'type' => $type,
                'outlets' => $rows->count(),
                'qty' => $rows->sum('total_qty'),
                'orders' => $rows->sum('total_orders'),
                'sessions' => $rows->sum('total_sessions'),
                'inclusive_qty' => $rows->sum('inclusive_qty'),
                'recorded_value' => round($rows->sum('recorded_value'), 2),
                'avg_qty_per_session' => $rows->sum('total_sessions') > 0
                    ? round($rows->sum('total_qty') / $rows->sum('total_sessions'), 1)
                    : 0,
            ])
            ->sortByDesc('qty')
            ->values();

        $topProducts = (clone $itemBase)
            ->selectRaw('
                restaurant_order_items.item_name,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as total_qty,
                COUNT(DISTINCT restaurants.id) as restaurant_count,
                COUNT(DISTINCT table_sessions.id) as session_count,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as priced_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price IS NULL OR restaurant_order_items.unit_price <= 0 THEN restaurant_order_items.quantity ELSE 0 END), 0) as inclusive_qty,
                COALESCE(SUM(CASE WHEN restaurant_order_items.unit_price > 0 THEN restaurant_order_items.unit_price * restaurant_order_items.quantity ELSE 0 END), 0) as recorded_value
            ')
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('total_qty')
            ->limit(15)
            ->get();

        $topInclusiveProducts = (clone $itemBase)
            ->where(function ($q) {
                $q->whereNull('restaurant_order_items.unit_price')
                  ->orWhere('restaurant_order_items.unit_price', '<=', 0);
            })
            ->selectRaw('
                restaurant_order_items.item_name,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as total_qty,
                COUNT(DISTINCT restaurants.id) as restaurant_count,
                COUNT(DISTINCT table_sessions.id) as session_count
            ')
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $topRecordedValueProducts = (clone $itemBase)
            ->where('restaurant_order_items.unit_price', '>', 0)
            ->selectRaw('
                restaurant_order_items.item_name,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as total_qty,
                COALESCE(SUM(restaurant_order_items.unit_price * restaurant_order_items.quantity), 0) as recorded_value,
                AVG(restaurant_order_items.unit_price) as avg_unit_price
            ')
            ->groupBy('restaurant_order_items.item_name')
            ->orderByDesc('recorded_value')
            ->limit(10)
            ->get();

        $dailyRows = (clone $itemBase)
            ->selectRaw('
                DATE(table_sessions.opened_at) as day,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as qty,
                COUNT(DISTINCT restaurant_orders.id) as orders,
                COUNT(DISTINCT table_sessions.id) as sessions
            ')
            ->groupByRaw('DATE(table_sessions.opened_at)')
            ->get()
            ->keyBy('day');

        $dailyLabels = [];
        $dailyQty = [];
        $dailyOrders = [];
        $dailySessions = [];
        foreach (CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $row = $dailyRows->get($key);
            $dailyLabels[] = $day->locale('tr')->isoFormat('D MMM');
            $dailyQty[] = (int) ($row->qty ?? 0);
            $dailyOrders[] = (int) ($row->orders ?? 0);
            $dailySessions[] = (int) ($row->sessions ?? 0);
        }

        $hourlyRows = (clone $itemBase)
            ->selectRaw('HOUR(restaurant_orders.created_at) as hour, COUNT(DISTINCT restaurant_orders.id) as orders')
            ->groupByRaw('HOUR(restaurant_orders.created_at)')
            ->pluck('orders', 'hour');
        $hourlyLabels = collect(range(0, 23))->map(fn ($hour) => sprintf('%02d:00', $hour))->values();
        $hourlyOrders = collect(range(0, 23))->map(fn ($hour) => (int) $hourlyRows->get($hour, 0))->values();

        $topWaiters = (clone $itemBase)
            ->join('users', 'users.id', '=', 'restaurant_orders.created_by')
            ->selectRaw('
                users.name as waiter_name,
                COUNT(DISTINCT restaurant_orders.id) as order_count,
                COUNT(DISTINCT table_sessions.id) as session_count,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as item_qty
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        $branchSummary = (clone $itemBase)
            ->selectRaw('
                COALESCE(branches.name, \'-\') as branch_name,
                COUNT(DISTINCT restaurants.id) as restaurant_count,
                COUNT(DISTINCT table_sessions.id) as session_count,
                COUNT(DISTINCT restaurant_orders.id) as order_count,
                COALESCE(SUM(restaurant_order_items.quantity), 0) as item_qty
            ')
            ->groupBy('branches.name')
            ->orderByDesc('item_qty')
            ->get();

        $costRecommendations = $this->orderConsumptionInsights($summary, $byRestaurant, $topProducts, $topInclusiveProducts, $outletSummary);
        $page_title = 'Sipariş Tüketim Raporu';

        return view('modules.management_reports.order_consumption', compact(
            'page_title',
            'dateFrom',
            'dateTo',
            'branchId',
            'restaurantId',
            'branches',
            'restaurants',
            'summary',
            'byRestaurant',
            'outletSummary',
            'topProducts',
            'topInclusiveProducts',
            'topRecordedValueProducts',
            'dailyLabels',
            'dailyQty',
            'dailyOrders',
            'dailySessions',
            'hourlyLabels',
            'hourlyOrders',
            'topWaiters',
            'branchSummary',
            'costRecommendations'
        ));
    }

    private function datePeriod(Request $request, string $default): array
    {
        $defaultFrom = match ($default) {
            'last_30' => Carbon::today()->subDays(29),
            default => Carbon::today()->startOfMonth(),
        };

        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : $defaultFrom->copy()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::today()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function selectedBranchId(Request $request, array $branchIds): ?int
    {
        if (!$request->filled('branch_id')) {
            return null;
        }

        $branchId = (int) $request->input('branch_id');
        abort_if(!in_array($branchId, $branchIds, true), 403);

        return $branchId;
    }

    private function selectedRestaurantId(Request $request, array $branchIds, ?int $branchId): ?int
    {
        if (!$request->filled('restaurant_id')) {
            return null;
        }

        $restaurantId = (int) $request->input('restaurant_id');
        $exists = Restaurant::whereKey($restaurantId)
            ->whereIn('branch_id', $branchIds)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->exists();

        abort_if(!$exists, 403);

        return $restaurantId;
    }

    private function branchList(array $branchIds)
    {
        return Branch::whereIn('id', $branchIds)->orderBy('name')->get();
    }

    private function periodDays(Carbon $dateFrom, Carbon $dateTo): int
    {
        return max(1, $dateFrom->copy()->startOfDay()->diffInDays($dateTo->copy()->startOfDay()) + 1);
    }

    private function calculateDoorWork($entrances, $exits): array
    {
        $totalMinutes = 0;
        $dailyMinutes = [];
        $exitIndex = 0;

        foreach ($entrances as $entry) {
            $entryAt = Carbon::parse($entry->logged_at);

            while ($exitIndex < $exits->count() && Carbon::parse($exits[$exitIndex]->logged_at)->lte($entryAt)) {
                $exitIndex++;
            }

            if ($exitIndex >= $exits->count()) {
                continue;
            }

            $exitAt = Carbon::parse($exits[$exitIndex]->logged_at);
            $minutes = $entryAt->diffInMinutes($exitAt);

            if ($minutes <= 960) {
                $totalMinutes += $minutes;
                $day = $entryAt->format('Y-m-d');
                $dailyMinutes[$day] = ($dailyMinutes[$day] ?? 0) + $minutes;
            }

            $exitIndex++;
        }

        return [
            'total_minutes' => $totalMinutes,
            'daily_minutes' => $dailyMinutes,
        ];
    }

    private function averageLogTime($logs): string
    {
        if ($logs->isEmpty()) {
            return '-';
        }

        $avgMinutes = (int) round($logs->avg(function ($log) {
            $time = Carbon::parse($log->logged_at);
            return $time->hour * 60 + $time->minute;
        }));

        return sprintf('%02d:%02d', intdiv($avgMinutes, 60) % 24, $avgMinutes % 60);
    }

    private function faultGroupMetrics($faults): array
    {
        $active = $faults->reject(fn ($fault) => in_array($fault->status, ['resolved', 'closed'], true));
        $closed = $faults->filter(fn ($fault) => in_array($fault->status, ['resolved', 'closed'], true));
        $resolved = $faults->filter(fn ($fault) => $fault->resolved_at);
        $slaOnTime = $resolved->filter(fn ($fault) =>
            $fault->created_at->diffInMinutes($fault->resolved_at) / 60 <= ($fault->faultType?->completion_hours ?? 24)
        )->count();

        return [
            'total' => $faults->count(),
            'active' => $active->count(),
            'closed' => $closed->count(),
            'critical_active' => $active->whereIn('priority', ['high', 'critical'])->count(),
            'avg_hours' => $resolved->count() > 0
                ? round($resolved->avg(fn ($fault) => $fault->created_at->diffInMinutes($fault->resolved_at) / 60), 1)
                : null,
            'sla_pct' => $resolved->count() > 0 ? round($slaOnTime / $resolved->count() * 100) : null,
        ];
    }

    private function managerDoorInsights(array $summary, Carbon $dateFrom, Carbon $dateTo): array
    {
        $range = $dateFrom->format('d.m.Y') . ' - ' . $dateTo->format('d.m.Y');

        if ($summary['total_managers'] === 0) {
            return ["{$range} döneminde seçili şube için aktif müdür kaydı bulunamadı."];
        }

        if ($summary['active_managers'] === 0) {
            return ["{$range} döneminde {$summary['total_managers']} müdür için giriş veya çıkış kaydı oluşmamış."];
        }

        $lines = [];
        $lines[] = "{$range} döneminde {$summary['active_managers']} müdür için toplam {$summary['total_entries']} giriş ve {$summary['total_exits']} çıkış kaydı oluştu.";
        $lines[] = "Kayıtlardan hesaplanan toplam bulunma süresi {$summary['total_hours']} saat; aktif müdür başına ortalama {$summary['avg_hours']} saat.";

        if ($summary['inside_now'] > 0) {
            $lines[] = "Son kapı kaydına göre şu anda içeride görünen müdür sayısı {$summary['inside_now']}.";
        }

        if ($summary['missing_exits'] > 0) {
            $lines[] = "{$summary['missing_exits']} giriş kaydı için aynı dönem içinde eşleşen çıkış kaydı görünmüyor; bu kayıtların kontrol edilmesi iyi olur.";
        }

        return $lines;
    }

    private function technicalFaultInsights($summary, $byDepartment, $byType, $byLocation): array
    {
        if ($summary['total'] === 0) {
            return ['Seçili dönemde teknik arıza kaydı bulunmuyor. Bu durum operasyon açısından olumlu bir tablo gösterir.'];
        }

        $lines = [];
        $lines[] = "Seçili dönemde {$summary['total']} teknik arıza kaydı açıldı; bunların {$summary['closed']} adedi kapandı, {$summary['active']} adedi halen takipte.";

        if ($summary['critical_active'] > 0) {
            $lines[] = "{$summary['critical_active']} yüksek veya kritik öncelikli arıza halen açık. Yönetim takibi önerilir.";
        }

        if ($summary['period_diff'] > 0) {
            $lines[] = "Bir önceki aynı uzunluktaki döneme göre {$summary['period_diff']} adet daha fazla arıza kaydı var.";
        } elseif ($summary['period_diff'] < 0) {
            $lines[] = "Bir önceki aynı uzunluktaki döneme göre " . abs($summary['period_diff']) . " adet daha az arıza kaydı var.";
        }

        $topDepartment = $byDepartment->first();
        if ($topDepartment) {
            $lines[] = "En yüksek iş yükü {$topDepartment['name']} departmanında: {$topDepartment['total']} kayıt, {$topDepartment['active']} aktif arıza.";
        }

        $topType = $byType->first();
        if ($topType) {
            $lines[] = "En sık bildirilen arıza tipi {$topType['name']}; toplam {$topType['total']} kayıt.";
        }

        $topLocation = $byLocation->first();
        if ($topLocation) {
            $lines[] = "En yoğun konum {$topLocation['name']}; bu alanda öne çıkan arıza tipi {$topLocation['top_type']}.";
        }

        return $lines;
    }

    private function shuttleInsights(array $summary, $byVehicle, $byRoute, $byShift): array
    {
        if ($summary['total_trips'] === 0) {
            return ['Seçili dönemde servis operasyon kaydı bulunmuyor; bu dönem için ölçülebilir servis hareketi oluşmamış.'];
        }

        $lines = [];
        $lines[] = "Seçili dönemde {$summary['total_trips']} servis kaydıyla toplam {$summary['total_movement']} personel hareketi takip edildi.";
        $lines[] = "Günlük ortalama geliş {$summary['avg_daily_arrival']}, dönüş {$summary['avg_daily_departure']} kişi.";

        if ($summary['avg_occupancy'] !== null) {
            $lines[] = "Araç kapasitesi girilen kayıtlarda ortalama doluluk yaklaşık %{$summary['avg_occupancy']}.";
        }

        $topVehicle = $byVehicle->first();
        if ($topVehicle) {
            $lines[] = "En yoğun kullanılan araç {$topVehicle['name']}; {$topVehicle['movement']} personel hareketi ve {$topVehicle['trips']} sefer kaydı var.";
        }

        $topRoute = $byRoute->first();
        if ($topRoute) {
            $lines[] = "En yoğun güzergah {$topRoute['name']}; toplam {$topRoute['movement']} hareket.";
        }

        $topShift = $byShift->first();
        if ($topShift) {
            $lines[] = "En yoğun vardiya {$topShift['name']}; toplam {$topShift['movement']} hareket.";
        }

        return $lines;
    }

    private function outletType(?string $restaurantName, ?string $menuName): string
    {
        $text = Str::of(trim(($restaurantName ?? '') . ' ' . ($menuName ?? '')))->lower()->toString();

        if (Str::contains($text, ['bar', 'lobby', 'pool', 'beach', 'pub', 'disco', 'snack'])) {
            return 'Bar / ücretsiz tüketim';
        }

        if (Str::contains($text, ['restoran', 'restaurant', 'a la carte', 'alacarte', 'steak', 'fish', 'balik', 'balık', 'italian', 'mexican', 'ottoman'])) {
            return 'Restoran / girişli kullanım';
        }

        return 'Genel outlet';
    }

    private function orderConsumptionInsights(array $summary, $byRestaurant, $topProducts, $topInclusiveProducts, $outletSummary): array
    {
        if ($summary['total_qty'] === 0) {
            return ['Seçili dönemde sipariş kaydı bulunmuyor. Bu dönem için ölçülebilir restoran veya bar tüketimi oluşmamış.'];
        }

        $lines = [];
        $lines[] = "Seçili dönemde {$summary['total_orders']} sipariş, {$summary['total_sessions']} masa/seans ve toplam {$summary['total_qty']} ürün adedi takip edildi.";
        $lines[] = "Her şey dahil düzende bu ekranı net gelir raporu gibi değil, tüketim yükü ve maliyet baskısı göstergesi olarak okumak gerekir.";

        if ($summary['inclusive_qty'] > 0) {
            $lines[] = "Ücretsiz/ikram veya fiyatı sıfır girilen ürün adedi {$summary['inclusive_qty']} ve toplam tüketimin yaklaşık %{$summary['inclusive_pct']} oranında.";
        }

        if ($summary['avg_qty_per_session'] > 0) {
            $lines[] = "Seans başına ortalama {$summary['avg_qty_per_session']} ürün tüketilmiş. Giriş/kuver bedeli ayrı takip edildiği için kişi başı karlılık hesabı ayrıca cover sayısıyla eşleştirilmeli.";
        }

        $topRestaurant = $byRestaurant->first();
        if ($topRestaurant) {
            $lines[] = "En yüksek tüketim {$topRestaurant->restaurant_name} alanında: {$topRestaurant->total_qty} ürün, {$topRestaurant->total_sessions} seans, seans başına {$topRestaurant->avg_qty_per_session} ürün.";
        }

        $topProduct = $topProducts->first();
        if ($topProduct) {
            $lines[] = "En çok çıkan ürün {$topProduct->item_name}; toplam {$topProduct->total_qty} adet. Bu ürün için porsiyon, reçete ve hazırlık fireleri kontrol edilmeli.";
        }

        $topInclusive = $topInclusiveProducts->first();
        if ($topInclusive) {
            $lines[] = "Fiyatı sıfır/ikram görünen ürünlerde ilk sırada {$topInclusive->item_name} var: {$topInclusive->total_qty} adet. Bar ve ikram ürünlerinde stok tüketimiyle karşılaştırmak maliyeti düşürür.";
        }

        $barSummary = $outletSummary->firstWhere('type', 'Bar / ücretsiz tüketim');
        if ($barSummary) {
            $lines[] = "Bar/ücretsiz tüketim alanlarında {$barSummary['qty']} ürün hareketi var. Yüksek adetli içeceklerde reçete standardı, ölçü kullanımı ve depo çıkışı birlikte izlenmeli.";
        }

        return $lines;
    }
}
