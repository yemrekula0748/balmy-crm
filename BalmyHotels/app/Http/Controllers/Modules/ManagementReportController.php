<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\DoorLog;
use App\Models\Fault;
use App\Models\ShuttleTrip;
use App\Models\ShuttleVehicle;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ManagementReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'yonetim_kurulu_rapor',
            ['index', 'managerDoorLogs', 'technicalFaults', 'shuttleServices'],
            [],
            [],
            [],
            []
        );
    }

    public function index()
    {
        return redirect()->route('management-reports.manager-door-logs');
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
}
