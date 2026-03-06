<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleTrip;
use App\Models\ShuttleVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ShuttleReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'shuttle_reports',
            ['index', 'pdf'],
            [],
            [],
            [],
            []
        );
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();

        // Filtreler
        $branchId  = $request->branch_id;
        $vehicleId = $request->vehicle_id;
        $period    = $request->get('period', 'monthly'); // daily, weekly, monthly, custom
        $from      = null;
        $to        = null;

        switch ($period) {
            case 'daily':
                $from = Carbon::today();
                $to   = Carbon::today();
                break;
            case 'weekly':
                $from = Carbon::now()->startOfWeek();
                $to   = Carbon::now()->endOfWeek();
                break;
            case 'custom':
                $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->subDays(30);
                $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::today();
                break;
            default: // monthly
                $from = Carbon::now()->startOfMonth();
                $to   = Carbon::now()->endOfMonth();
                break;
        }

        // Araç listesi
        $vehicles = ShuttleVehicle::whereIn('branch_id', $branchIds)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')->get();

        // Sefer verileri
        $tripsQuery = ShuttleTrip::with(['vehicle', 'route', 'branch'])
            ->whereIn('branch_id', $branchIds)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->when($branchId,  fn($q) => $q->where('branch_id', $branchId))
            ->when($vehicleId, fn($q) => $q->where('shuttle_vehicle_id', $vehicleId))
            ->orderBy('trip_date')
            ->orderBy('shift');

        $trips = $tripsQuery->get();

        // Genel istatistikler
        $stats = [
            'total_arrival'      => $trips->sum('arrival_count'),
            'total_departure'    => $trips->sum('departure_count'),
            'total_trips'        => $trips->count(),
            'avg_daily_arrival'  => 0,
            'avg_daily_departure'=> 0,
            'avg_occupancy_arr'  => 0,
            'avg_occupancy_dep'  => 0,
        ];

        $dayCount = max(1, $from->diffInDays($to) + 1);
        $stats['avg_daily_arrival']   = round($stats['total_arrival']  / $dayCount, 1);
        $stats['avg_daily_departure'] = round($stats['total_departure'] / $dayCount, 1);

        // Doluluk oranları (kapasite olan araçlar için)
        $occupancyArr = [];
        $occupancyDep = [];
        foreach ($trips as $t) {
            $cap = $t->vehicle->capacity ?? 0;
            if ($cap > 0) {
                $occupancyArr[] = $t->arrival_count   / $cap * 100;
                $occupancyDep[] = $t->departure_count / $cap * 100;
            }
        }
        $stats['avg_occupancy_arr'] = count($occupancyArr) ? round(array_sum($occupancyArr) / count($occupancyArr), 1) : 0;
        $stats['avg_occupancy_dep'] = count($occupancyDep) ? round(array_sum($occupancyDep) / count($occupancyDep), 1) : 0;

        // Vardiya bazlı özet
        $byShift = [];
        foreach (ShuttleTrip::SHIFTS as $shift) {
            $subset = $trips->where('shift', $shift);
            $byShift[$shift] = [
                'count'     => $subset->count(),
                'arrival'   => $subset->sum('arrival_count'),
                'departure' => $subset->sum('departure_count'),
            ];
        }

        // Araç bazlı özet
        $byVehicle = [];
        foreach ($vehicles as $v) {
            $subset = $trips->where('shuttle_vehicle_id', $v->id);
            $totalArr = $subset->sum('arrival_count');
            $totalDep = $subset->sum('departure_count');
            $cap      = $v->capacity;
            $tripCnt  = $subset->count();

            $byVehicle[$v->id] = [
                'vehicle'         => $v,
                'trips'           => $tripCnt,
                'arrival'         => $totalArr,
                'departure'       => $totalDep,
                'occupancy_arr'   => ($cap > 0 && $tripCnt > 0) ? round($totalArr  / ($cap * $tripCnt) * 100, 1) : 0,
                'occupancy_dep'   => ($cap > 0 && $tripCnt > 0) ? round($totalDep / ($cap * $tripCnt) * 100, 1) : 0,
            ];
        }

        // Günlük grafik verisi
        $dailyLabels  = [];
        $dailyArr     = [];
        $dailyDep     = [];
        $current      = $from->copy();
        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $dayTrips = $trips->filter(fn($t) => $t->trip_date->toDateString() === $dateStr);
            $dailyLabels[] = $current->format('d.m');
            $dailyArr[]    = $dayTrips->sum('arrival_count');
            $dailyDep[]    = $dayTrips->sum('departure_count');
            $current->addDay();
        }

        $chartData = [
            'labels'    => $dailyLabels,
            'arrival'   => $dailyArr,
            'departure' => $dailyDep,
        ];

        return view('modules.shuttle.reports.index', compact(
            'trips', 'stats', 'byShift', 'byVehicle', 'chartData',
            'branches', 'vehicles', 'branchId', 'vehicleId',
            'period', 'from', 'to'
        ));
    }

    public function pdf(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();

        $branchId  = $request->branch_id;
        $vehicleId = $request->vehicle_id;
        $from      = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to        = $request->to   ? Carbon::parse($request->to)   : Carbon::today();

        $branches = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();
        $vehicles = ShuttleVehicle::whereIn('branch_id', $branchIds)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')->get();

        $trips = ShuttleTrip::with(['vehicle', 'route', 'branch'])
            ->whereIn('branch_id', $branchIds)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->when($branchId,  fn($q) => $q->where('branch_id', $branchId))
            ->when($vehicleId, fn($q) => $q->where('shuttle_vehicle_id', $vehicleId))
            ->orderBy('trip_date')
            ->orderBy('shift')
            ->get();

        // İstatistikler
        $dayCount = max(1, $from->diffInDays($to) + 1);
        $totalArr = $trips->sum('arrival_count');
        $totalDep = $trips->sum('departure_count');
        $stats = [
            'total_arrival'       => $totalArr,
            'total_departure'     => $totalDep,
            'total_trips'         => $trips->count(),
            'avg_daily_arrival'   => round($totalArr  / $dayCount, 1),
            'avg_daily_departure' => round($totalDep / $dayCount, 1),
        ];

        $byShift = [];
        foreach (ShuttleTrip::SHIFTS as $shift) {
            $subset = $trips->where('shift', $shift);
            $byShift[$shift] = [
                'count'     => $subset->count(),
                'arrival'   => $subset->sum('arrival_count'),
                'departure' => $subset->sum('departure_count'),
            ];
        }

        $byVehicle = [];
        foreach ($vehicles as $v) {
            $subset  = $trips->where('shuttle_vehicle_id', $v->id);
            $tripCnt = $subset->count();
            $cap     = $v->capacity;
            $tArr    = $subset->sum('arrival_count');
            $tDep    = $subset->sum('departure_count');
            $byVehicle[$v->id] = [
                'vehicle'       => $v,
                'trips'         => $tripCnt,
                'arrival'       => $tArr,
                'departure'     => $tDep,
                'occupancy_arr' => ($cap > 0 && $tripCnt > 0) ? round($tArr  / ($cap * $tripCnt) * 100, 1) : 0,
                'occupancy_dep' => ($cap > 0 && $tripCnt > 0) ? round($tDep / ($cap * $tripCnt) * 100, 1) : 0,
            ];
        }

        $branchFilter = $branchId ? Branch::find($branchId) : null;

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'dejavu sans',
            'defaultPaperSize'     => 'a4',
        ])->loadView('modules.shuttle.reports.pdf', compact(
            'trips', 'stats', 'byShift', 'byVehicle',
            'from', 'to', 'branchFilter', 'vehicles', 'user'
        ))->setPaper('a4', 'landscape');

        $filename = 'servis_raporu_' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
