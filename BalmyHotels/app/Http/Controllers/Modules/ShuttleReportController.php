<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleTrip;
use App\Models\ShuttleTripBranchMovement;
use App\Models\ShuttleVehicle;
use App\Services\ShuttleTripMergeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShuttleReportController extends BaseModuleController
{
    private ShuttleTripMergeService $tripMergeService;

    public function __construct()
    {
        $this->tripMergeService = app(ShuttleTripMergeService::class);

        $this->requirePermission(
            'shuttle_reports',
            ['index', 'pdf', 'excel'],
            [],
            [],
            [],
            []
        );

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless($user && ($user->isSuperAdmin() || $user->isHumanResources()), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        return view('modules.shuttle.reports.index', $this->buildReportPayload($request));
    }

    public function pdf(Request $request)
    {
        $payload = $this->buildReportPayload($request);
        $user = Auth::user();

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'dejavu sans',
            'defaultPaperSize' => 'a4',
        ])->loadView('modules.shuttle.reports.pdf', array_merge($payload, [
            'user' => $user,
        ]))->setPaper('a4', 'landscape');

        $filename = 'servis_raporu_' . $payload['from']->format('Y-m-d') . '_' . $payload['to']->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        $payload = $this->buildReportPayload($request);

        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ozet');

        $this->buildSummarySheet($summarySheet, $payload);
        $this->buildTripDetailSheet($spreadsheet, $payload);
        $this->buildBranchMovementSheet($spreadsheet, $payload);

        $filename = 'servis-raporu-' . $payload['from']->format('Y-m-d') . '-' . $payload['to']->format('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function buildReportPayload(Request $request): array
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();

        $branchId = $request->filled('branch_id') && in_array((int) $request->branch_id, $visibleBranchIds, true)
            ? (int) $request->branch_id
            : null;
        $period = $request->get('period', 'monthly');
        [$from, $to] = $this->resolveDateRange($request, $period);

        $reportBranchIds = $branchId ? [(int) $branchId] : array_map('intval', $branches->pluck('id')->all());
        $queryBranchIds = $branchId ? [(int) $branchId] : $visibleBranchIds;

        $visibleTripsQuery = ShuttleTrip::query()
            ->forPeriod($from->toDateString(), $to->toDateString());

        $vehicles = ShuttleVehicle::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $vehicleIds = $vehicles->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $vehicleId = $request->filled('vehicle_id') && in_array((int) $request->vehicle_id, $vehicleIds, true)
            ? (int) $request->vehicle_id
            : null;

        $trips = (clone $visibleTripsQuery)
            ->with(['vehicle', 'route', 'branch', 'branchMovements.branch'])
            ->when($vehicleId, fn ($q) => $q->where('shuttle_vehicle_id', $vehicleId))
            ->orderBy('trip_date')
            ->orderBy('shift')
            ->get();
        $trips = $this->tripMergeService
            ->mergeCollection($trips)
            ->filter(fn (ShuttleTrip $trip) => $this->tripMergeService->tripTouchesBranches($trip, $queryBranchIds))
            ->values();

        $totalTrips = $trips->count();
        $totalArrival = $this->sumMovements($trips, $reportBranchIds, 'arrival');
        $totalDeparture = $this->sumMovements($trips, $reportBranchIds, 'departure');
        $dayCount = max(1, $from->diffInDays($to) + 1);
        $transferTrips = $trips->where('is_transfer', true)->count();
        $differentVehicleTrips = $trips->where('arrived_with_different_vehicle', true)->count();
        $exceptionTrips = $trips->filter(fn ($trip) => $trip->is_transfer || $trip->arrived_with_different_vehicle)->count();

        $occupancyArr = [];
        $occupancyDep = [];
        foreach ($trips as $trip) {
            $cap = $trip->vehicle->capacity ?? 0;
            $tripArrivalCount = $this->sumTripMovements($trip, $reportBranchIds, 'arrival');
            $tripDepartureCount = $this->sumTripMovements($trip, $reportBranchIds, 'departure');
            if ($cap > 0) {
                $occupancyArr[] = $tripArrivalCount / $cap * 100;
                $occupancyDep[] = $tripDepartureCount / $cap * 100;
            }
        }

        $stats = [
            'total_arrival' => $totalArrival,
            'total_departure' => $totalDeparture,
            'total_trips' => $totalTrips,
            'avg_daily_arrival' => round($totalArrival / $dayCount, 1),
            'avg_daily_departure' => round($totalDeparture / $dayCount, 1),
            'avg_occupancy_arr' => count($occupancyArr) ? round(array_sum($occupancyArr) / count($occupancyArr), 1) : 0,
            'avg_occupancy_dep' => count($occupancyDep) ? round(array_sum($occupancyDep) / count($occupancyDep), 1) : 0,
            'total_transfer_trips' => $transferTrips,
            'total_different_vehicle_trips' => $differentVehicleTrips,
            'transfer_rate' => $totalTrips > 0 ? round($transferTrips / $totalTrips * 100, 1) : 0,
            'different_vehicle_rate' => $totalTrips > 0 ? round($differentVehicleTrips / $totalTrips * 100, 1) : 0,
            'exception_trips' => $exceptionTrips,
            'exception_rate' => $totalTrips > 0 ? round($exceptionTrips / $totalTrips * 100, 1) : 0,
        ];

        $byShift = [];
        foreach (ShuttleTrip::SHIFTS as $shift) {
            $subset = $trips->where('shift', $shift);
            $shiftTripCount = $subset->count();

            $byShift[$shift] = [
                'count' => $shiftTripCount,
                'arrival' => $this->sumMovements($subset, $reportBranchIds, 'arrival'),
                'departure' => $this->sumMovements($subset, $reportBranchIds, 'departure'),
                'transfer' => $subset->where('is_transfer', true)->count(),
                'different_vehicle' => $subset->where('arrived_with_different_vehicle', true)->count(),
                'transfer_rate' => $shiftTripCount > 0 ? round($subset->where('is_transfer', true)->count() / $shiftTripCount * 100, 1) : 0,
                'different_vehicle_rate' => $shiftTripCount > 0 ? round($subset->where('arrived_with_different_vehicle', true)->count() / $shiftTripCount * 100, 1) : 0,
            ];
        }

        $byVehicle = [];
        foreach ($vehicles as $vehicle) {
            $subset = $trips->where('shuttle_vehicle_id', $vehicle->id);
            $tripCount = $subset->count();
            $totalVehicleArrival = $this->sumMovements($subset, $reportBranchIds, 'arrival');
            $totalVehicleDeparture = $this->sumMovements($subset, $reportBranchIds, 'departure');
            $cap = $vehicle->capacity;

            $byVehicle[$vehicle->id] = [
                'vehicle' => $vehicle,
                'trips' => $tripCount,
                'arrival' => $totalVehicleArrival,
                'departure' => $totalVehicleDeparture,
                'transfer' => $subset->where('is_transfer', true)->count(),
                'different_vehicle' => $subset->where('arrived_with_different_vehicle', true)->count(),
                'occupancy_arr' => ($cap > 0 && $tripCount > 0) ? round($totalVehicleArrival / ($cap * $tripCount) * 100, 1) : 0,
                'occupancy_dep' => ($cap > 0 && $tripCount > 0) ? round($totalVehicleDeparture / ($cap * $tripCount) * 100, 1) : 0,
                'transfer_rate' => $tripCount > 0 ? round($subset->where('is_transfer', true)->count() / $tripCount * 100, 1) : 0,
                'different_vehicle_rate' => $tripCount > 0 ? round($subset->where('arrived_with_different_vehicle', true)->count() / $tripCount * 100, 1) : 0,
            ];
        }

        $dailyLabels = [];
        $dailyArrivals = [];
        $dailyDepartures = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $dateString = $current->toDateString();
            $dayTrips = $trips->filter(fn ($trip) => $trip->trip_date->toDateString() === $dateString);
            $dailyLabels[] = $current->format('d.m');
            $dailyArrivals[] = $this->sumMovements($dayTrips, $reportBranchIds, 'arrival');
            $dailyDepartures[] = $this->sumMovements($dayTrips, $reportBranchIds, 'departure');
            $current->addDay();
        }

        $summaryBranches = $branchId ? $branches->where('id', $branchId) : $branches;
        $branchMovementSummary = [];
        foreach ($summaryBranches as $branch) {
            $branchMovementSummary[$branch->id] = [
                'branch' => $branch,
                'arrival' => $this->sumMovements($trips, [(int) $branch->id], 'arrival'),
                'departure' => $this->sumMovements($trips, [(int) $branch->id], 'departure'),
            ];
        }

        $branchFilter = $branchId ? $branches->firstWhere('id', (int) $branchId) : null;
        $vehicleFilter = $vehicleId ? $vehicles->firstWhere('id', (int) $vehicleId) : null;

        return [
            'trips' => $trips,
            'stats' => $stats,
            'byShift' => $byShift,
            'byVehicle' => $byVehicle,
            'chartData' => [
                'labels' => $dailyLabels,
                'arrival' => $dailyArrivals,
                'departure' => $dailyDepartures,
            ],
            'branchMovementSummary' => $branchMovementSummary,
            'branches' => $branches,
            'vehicles' => $vehicles,
            'branchId' => $branchId,
            'vehicleId' => $vehicleId,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'branchFilter' => $branchFilter,
            'vehicleFilter' => $vehicleFilter,
            'reportBranchIds' => $reportBranchIds,
        ];
    }

    private function resolveDateRange(Request $request, string $period): array
    {
        return match ($period) {
            'daily' => [Carbon::today(), Carbon::today()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'custom' => [
                $request->from ? Carbon::parse($request->from) : Carbon::now()->subDays(30),
                $request->to ? Carbon::parse($request->to) : Carbon::today(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function buildSummarySheet($sheet, array $payload): void
    {
        $stats = $payload['stats'];

        $sheet->setCellValue('A1', 'Servis Raporu');
        $sheet->setCellValue('A2', 'Donem');
        $sheet->setCellValue('B2', $payload['from']->format('d.m.Y') . ' - ' . $payload['to']->format('d.m.Y'));
        $sheet->setCellValue('A3', 'Sube');
        $sheet->setCellValue('B3', $payload['branchFilter']?->name ?? 'Tum Subeler');
        $sheet->setCellValue('A4', 'Arac');
        $sheet->setCellValue('B4', $payload['vehicleFilter']?->name ?? 'Tum Araclar');

        $sheet->setCellValue('A6', 'Metric');
        $sheet->setCellValue('B6', 'Deger');
        $sheet->setCellValue('A7', 'Toplam Gelen');
        $sheet->setCellValue('B7', $stats['total_arrival']);
        $sheet->setCellValue('A8', 'Toplam Donen');
        $sheet->setCellValue('B8', $stats['total_departure']);
        $sheet->setCellValue('A9', 'Toplam Sefer');
        $sheet->setCellValue('B9', $stats['total_trips']);
        $sheet->setCellValue('A10', 'Gunluk Ort. Gelis');
        $sheet->setCellValue('B10', $stats['avg_daily_arrival']);
        $sheet->setCellValue('A11', 'Gunluk Ort. Donus');
        $sheet->setCellValue('B11', $stats['avg_daily_departure']);
        $sheet->setCellValue('A12', 'Aktarimli Sefer');
        $sheet->setCellValue('B12', $stats['total_transfer_trips']);
        $sheet->setCellValue('C12', $stats['transfer_rate'] . '%');
        $sheet->setCellValue('A13', 'Farkli Aracla Gelen');
        $sheet->setCellValue('B13', $stats['total_different_vehicle_trips']);
        $sheet->setCellValue('C13', $stats['different_vehicle_rate'] . '%');
        $sheet->setCellValue('A14', 'Toplam Istisna');
        $sheet->setCellValue('B14', $stats['exception_trips']);
        $sheet->setCellValue('C14', $stats['exception_rate'] . '%');

        $sheet->setCellValue('E6', 'Vardiya');
        $sheet->setCellValue('F6', 'Sefer');
        $sheet->setCellValue('G6', 'Gelis');
        $sheet->setCellValue('H6', 'Donus');
        $sheet->setCellValue('I6', 'Aktarim');
        $sheet->setCellValue('J6', 'Farkli Arac');

        $row = 7;
        foreach ($payload['byShift'] as $shiftName => $shiftData) {
            if ($shiftData['count'] === 0) {
                continue;
            }

            $sheet->setCellValue('E' . $row, $shiftName);
            $sheet->setCellValue('F' . $row, $shiftData['count']);
            $sheet->setCellValue('G' . $row, $shiftData['arrival']);
            $sheet->setCellValue('H' . $row, $shiftData['departure']);
            $sheet->setCellValue('I' . $row, $shiftData['transfer']);
            $sheet->setCellValue('J' . $row, $shiftData['different_vehicle']);
            $row++;
        }

        $sheet->setCellValue('L6', 'Sube');
        $sheet->setCellValue('M6', 'Gelen');
        $sheet->setCellValue('N6', 'Giden');

        $movementRow = 7;
        foreach ($payload['branchMovementSummary'] as $summary) {
            $sheet->setCellValue('L' . $movementRow, $summary['branch']->name);
            $sheet->setCellValue('M' . $movementRow, $summary['arrival']);
            $sheet->setCellValue('N' . $movementRow, $summary['departure']);
            $movementRow++;
        }

        $sheet->setCellValue('P6', 'Arac');
        $sheet->setCellValue('Q6', 'Plaka');
        $sheet->setCellValue('R6', 'Sefer');
        $sheet->setCellValue('S6', 'Gelen');
        $sheet->setCellValue('T6', 'Giden');
        $sheet->setCellValue('U6', 'Aktarim');
        $sheet->setCellValue('V6', 'Farkli Arac');

        $vehicleRow = 7;
        foreach ($payload['byVehicle'] as $data) {
            if ((int) $data['trips'] === 0) {
                continue;
            }

            $sheet->setCellValue('P' . $vehicleRow, $data['vehicle']->name);
            $sheet->setCellValue('Q' . $vehicleRow, $data['vehicle']->plate ?: '-');
            $sheet->setCellValue('R' . $vehicleRow, $data['trips']);
            $sheet->setCellValue('S' . $vehicleRow, $data['arrival'] . ' (%' . $data['occupancy_arr'] . ')');
            $sheet->setCellValue('T' . $vehicleRow, $data['departure'] . ' (%' . $data['occupancy_dep'] . ')');
            $sheet->setCellValue('U' . $vehicleRow, $data['transfer']);
            $sheet->setCellValue('V' . $vehicleRow, $data['different_vehicle']);
            $vehicleRow++;
        }

        $this->styleTitle($sheet, 'A1:V1');
        $this->styleHeader($sheet, 'A6:C6');
        $this->styleHeader($sheet, 'E6:J6');
        $this->styleHeader($sheet, 'L6:N6');
        $this->styleHeader($sheet, 'P6:V6');
        $this->styleBorders($sheet, 'A6:C14');
        if ($row > 7) {
            $this->styleBorders($sheet, 'E6:J' . ($row - 1));
        }
        if ($movementRow > 7) {
            $this->styleBorders($sheet, 'L6:N' . ($movementRow - 1));
        }
        if ($vehicleRow > 7) {
            $this->styleBorders($sheet, 'P6:V' . ($vehicleRow - 1));
        }

        foreach (['A' => 22, 'B' => 16, 'C' => 12, 'E' => 18, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 14, 'L' => 18, 'M' => 10, 'N' => 10, 'P' => 20, 'Q' => 14, 'R' => 10, 'S' => 14, 'T' => 14, 'U' => 10, 'V' => 12] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function buildTripDetailSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Sefer Bazli Detay');

        $reportBranchIds = array_map('intval', $payload['reportBranchIds']);
        $reportBranches = $payload['branches']
            ->filter(fn ($branch) => in_array((int) $branch->id, $reportBranchIds, true))
            ->values();

        $headers = [
            'Tarih',
            'Gun',
            'Vardiya',
            'Sefer ID',
            'Kaydi Acan Otel',
            'Arac',
            'Plaka',
            'Kapasite',
            'Guzergah',
            'Ilk Gelis',
            'Toplam Indi',
            'Gelis Doluluk %',
            'Son Cikis',
            'Toplam Bindi',
            'Donus Doluluk %',
            'Farkli Arac',
            'Aktarim',
            'Durum',
        ];

        foreach ($reportBranches as $branch) {
            $headers[] = $branch->name . ' Geldi Saatleri';
            $headers[] = $branch->name . ' Indi';
            $headers[] = $branch->name . ' Cikti Saatleri';
            $headers[] = $branch->name . ' Bindi';
            $headers[] = $branch->name . ' Hareket Detayi';
        }

        $headers = array_merge($headers, [
            'Gunduz Gelen Detay',
            'Gunduz Giden Detay',
            'Aksam Gelen Detay',
            'Aksam Giden Detay',
            'Genel Gelen Detay',
            'Genel Giden Detay',
            'Not',
        ]);

        $lastColumn = count($headers);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumn);

        $sheet->setCellValue('A1', 'Servis Sefer Bazli Detayli Excel Raporu');
        $sheet->setCellValue('A2', 'Donem');
        $sheet->setCellValue('B2', $payload['from']->format('d.m.Y') . ' - ' . $payload['to']->format('d.m.Y'));
        $sheet->setCellValue('D2', 'Sube Filtresi');
        $sheet->setCellValue('E2', $payload['branchFilter']?->name ?? 'Tum Subeler');
        $sheet->setCellValue('G2', 'Arac Filtresi');
        $sheet->setCellValue('H2', $payload['vehicleFilter']?->name ?? 'Tum Araclar');
        $sheet->setCellValue('J2', 'Olusturma');
        $sheet->setCellValue('K2', now()->format('d.m.Y H:i'));
        $sheet->setCellValue('A3', 'Not');
        $sheet->setCellValue('B3', 'Her satir tek bir seferi temsil eder. Otel kolonlari ay sonu toplam ve filtreleme icin sayisal tutuldu; detay kolonlarinda gunduz/aksam saat ve kisi bilgileri yer alir.');

        $headerRow = 5;
        foreach ($headers as $index => $header) {
            $this->setCell($sheet, $index + 1, $headerRow, $header);
        }

        $row = $headerRow + 1;
        foreach ($payload['trips'] as $trip) {
            $tripArrivalCount = $this->sumTripMovements($trip, $reportBranchIds, 'arrival');
            $tripDepartureCount = $this->sumTripMovements($trip, $reportBranchIds, 'departure');
            $capacity = $trip->vehicle->capacity ?? 0;
            $tripArrivalTime = $this->resolveTripMovementTime($trip, $reportBranchIds, 'arrival', 'min');
            $tripDepartureTime = $this->resolveTripMovementTime($trip, $reportBranchIds, 'departure', 'max');
            $movementMatrix = $this->buildTripMovementMatrix($trip, $reportBranchIds);
            $status = collect([
                $trip->arrived_with_different_vehicle ? 'Farkli Arac' : null,
                $trip->is_transfer ? 'Aktarim' : null,
            ])->filter()->implode(' + ') ?: 'Normal';

            $column = 1;
            $this->setCell($sheet, $column++, $row, $trip->trip_date->format('d.m.Y'));
            $this->setCell($sheet, $column++, $row, $this->turkishDayName($trip->trip_date));
            $this->setCell($sheet, $column++, $row, $trip->shift);
            $this->setCell($sheet, $column++, $row, $trip->id);
            $this->setCell($sheet, $column++, $row, $trip->branch->name ?? '-');
            $this->setCell($sheet, $column++, $row, $trip->vehicle->name ?? '-');
            $this->setCell($sheet, $column++, $row, $trip->vehicle->plate ?? '-');
            $this->setCell($sheet, $column++, $row, $capacity);
            $this->setCell($sheet, $column++, $row, $trip->route->name ?? '-');
            $this->setCell($sheet, $column++, $row, $tripArrivalTime ?: '-');
            $this->setCell($sheet, $column++, $row, $tripArrivalCount);
            $this->setCell($sheet, $column++, $row, ($capacity > 0) ? round($tripArrivalCount / $capacity * 100, 1) : 0);
            $this->setCell($sheet, $column++, $row, $tripDepartureTime ?: '-');
            $this->setCell($sheet, $column++, $row, $tripDepartureCount);
            $this->setCell($sheet, $column++, $row, ($capacity > 0) ? round($tripDepartureCount / $capacity * 100, 1) : 0);
            $this->setCell($sheet, $column++, $row, $trip->arrived_with_different_vehicle ? 'Evet' : 'Hayir');
            $this->setCell($sheet, $column++, $row, $trip->is_transfer ? 'Evet' : 'Hayir');
            $this->setCell($sheet, $column++, $row, $status);

            foreach ($reportBranches as $branch) {
                $branchId = (int) $branch->id;
                $this->setCell($sheet, $column++, $row, $this->branchMovementTimes($movementMatrix, $branchId, 'arrival'));
                $this->setCell($sheet, $column++, $row, $this->sumBranchMovement($movementMatrix, $branchId, 'arrival'));
                $this->setCell($sheet, $column++, $row, $this->branchMovementTimes($movementMatrix, $branchId, 'departure'));
                $this->setCell($sheet, $column++, $row, $this->sumBranchMovement($movementMatrix, $branchId, 'departure'));
                $this->setCell($sheet, $column++, $row, $this->branchMovementDetail($movementMatrix, $branchId));
            }

            $this->setCell($sheet, $column++, $row, $this->periodMovementSummary($movementMatrix, 'day', 'arrival'));
            $this->setCell($sheet, $column++, $row, $this->periodMovementSummary($movementMatrix, 'day', 'departure'));
            $this->setCell($sheet, $column++, $row, $this->periodMovementSummary($movementMatrix, 'evening', 'arrival'));
            $this->setCell($sheet, $column++, $row, $this->periodMovementSummary($movementMatrix, 'evening', 'departure'));
            $this->setCell($sheet, $column++, $row, $this->allMovementSummary($movementMatrix, 'arrival'));
            $this->setCell($sheet, $column++, $row, $this->allMovementSummary($movementMatrix, 'departure'));
            $this->setCell($sheet, $column++, $row, $trip->notes ?? '');
            $row++;
        }

        $lastDataRow = max($headerRow, $row - 1);
        $this->styleTitle($sheet, "A1:{$lastColumnLetter}1");
        $this->styleHeader($sheet, "A{$headerRow}:{$lastColumnLetter}{$headerRow}");
        $this->styleBorders($sheet, "A{$headerRow}:{$lastColumnLetter}{$lastDataRow}");

        $sheet->getStyle("A2:{$lastColumnLetter}3")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$lastDataRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        if ($lastDataRow >= $headerRow + 1) {
            $sheet->getStyle("K6:O{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        if ($row > $headerRow + 1) {
            $sheet->setAutoFilter("A{$headerRow}:{$lastColumnLetter}{$lastDataRow}");
        }

        $widths = [
            1 => 12, 2 => 12, 3 => 16, 4 => 10, 5 => 18, 6 => 18, 7 => 13, 8 => 10, 9 => 24,
            10 => 10, 11 => 11, 12 => 14, 13 => 10, 14 => 12, 15 => 14, 16 => 12, 17 => 10, 18 => 16,
        ];

        for ($column = 1; $column <= $lastColumn; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth($widths[$column] ?? 24);
        }

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(36);
        $sheet->freezePane('A6');
    }

    private function buildBranchMovementSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Sube Hareketleri');
        $reportBranchIds = $payload['reportBranchIds'];

        $headers = [
            'A1' => 'Tarih',
            'B1' => 'Vardiya',
            'C1' => 'Arac',
            'D1' => 'Plaka',
            'E1' => 'Guzergah',
            'F1' => 'Sube',
            'G1' => 'Donem',
            'H1' => 'Hareket Tipi',
            'I1' => 'Saat',
            'J1' => 'Kisi Sayisi',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $row = 2;
        foreach ($payload['trips'] as $trip) {
            if ($trip->branchMovements->isEmpty()) {
                if (in_array((int) $trip->branch_id, $reportBranchIds, true) && (int) $trip->arrival_count > 0) {
                    $sheet->setCellValue('A' . $row, $trip->trip_date->format('d.m.Y'));
                    $sheet->setCellValue('B' . $row, $trip->shift);
                    $sheet->setCellValue('C' . $row, $trip->vehicle->name ?? '-');
                    $sheet->setCellValue('D' . $row, $trip->vehicle->plate ?? '-');
                    $sheet->setCellValue('E' . $row, $trip->route->name ?? '-');
                    $sheet->setCellValue('F' . $row, $trip->branch->name ?? '-');
                    $sheet->setCellValue('G' . $row, ShuttleTripBranchMovement::PERIODS[ShuttleTripBranchMovement::DEFAULT_PERIOD]);
                    $sheet->setCellValue('H' . $row, 'Gelen');
                    $sheet->setCellValue('I' . $row, $trip->arrival_time ? substr($trip->arrival_time, 0, 5) : '-');
                    $sheet->setCellValue('J' . $row, $trip->arrival_count);
                    $row++;
                }

                if (in_array((int) $trip->branch_id, $reportBranchIds, true) && (int) $trip->departure_count > 0) {
                    $sheet->setCellValue('A' . $row, $trip->trip_date->format('d.m.Y'));
                    $sheet->setCellValue('B' . $row, $trip->shift);
                    $sheet->setCellValue('C' . $row, $trip->vehicle->name ?? '-');
                    $sheet->setCellValue('D' . $row, $trip->vehicle->plate ?? '-');
                    $sheet->setCellValue('E' . $row, $trip->route->name ?? '-');
                    $sheet->setCellValue('F' . $row, $trip->branch->name ?? '-');
                    $sheet->setCellValue('G' . $row, ShuttleTripBranchMovement::PERIODS[ShuttleTripBranchMovement::DEFAULT_PERIOD]);
                    $sheet->setCellValue('H' . $row, 'Giden');
                    $sheet->setCellValue('I' . $row, $trip->departure_time ? substr($trip->departure_time, 0, 5) : '-');
                    $sheet->setCellValue('J' . $row, $trip->departure_count);
                    $row++;
                }

                continue;
            }

            foreach ($trip->branchMovements as $movement) {
                if (! in_array((int) $movement->branch_id, $reportBranchIds, true)) {
                    continue;
                }

                if ((int) $movement->headcount <= 0 && empty($movement->movement_time)) {
                    continue;
                }

                $sheet->setCellValue('A' . $row, $trip->trip_date->format('d.m.Y'));
                $sheet->setCellValue('B' . $row, $trip->shift);
                $sheet->setCellValue('C' . $row, $trip->vehicle->name ?? '-');
                $sheet->setCellValue('D' . $row, $trip->vehicle->plate ?? '-');
                $sheet->setCellValue('E' . $row, $trip->route->name ?? '-');
                $sheet->setCellValue('F' . $row, $movement->branch->name ?? '-');
                $sheet->setCellValue('G' . $row, $movement->period_label);
                $sheet->setCellValue('H' . $row, $movement->type_label);
                $sheet->setCellValue('I' . $row, $movement->movement_time ? substr($movement->movement_time, 0, 5) : '-');
                $sheet->setCellValue('J' . $row, $movement->headcount);
                $row++;
            }
        }

        $this->styleHeader($sheet, 'A1:J1');
        if ($row > 2) {
            $this->styleBorders($sheet, 'A1:J' . ($row - 1));
        }

        foreach (['A' => 12, 'B' => 16, 'C' => 20, 'D' => 14, 'E' => 22, 'F' => 18, 'G' => 18, 'H' => 16, 'I' => 10, 'J' => 10] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A2');
    }

    private function setCell($sheet, int $column, int $row, $value): void
    {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($column) . $row, $value);
    }

    private function buildTripMovementMatrix($trip, array $branchIds): array
    {
        $matrix = [];

        if ($trip->branchMovements->isEmpty()) {
            $branchId = (int) $trip->branch_id;

            if (! in_array($branchId, $branchIds, true)) {
                return $matrix;
            }

            $period = ShuttleTripBranchMovement::DEFAULT_PERIOD;
            $matrix[$period][$branchId] = $this->emptyTripMovementRow($trip->branch->name ?? '-');
            $matrix[$period][$branchId]['arrival'] = (int) $trip->arrival_count;
            $matrix[$period][$branchId]['departure'] = (int) $trip->departure_count;

            if (! empty($trip->arrival_time)) {
                $matrix[$period][$branchId]['arrival_times'][] = $this->normaliseReportTime($trip->arrival_time);
            }

            if (! empty($trip->departure_time)) {
                $matrix[$period][$branchId]['departure_times'][] = $this->normaliseReportTime($trip->departure_time);
            }

            return $matrix;
        }

        foreach ($trip->branchMovements as $movement) {
            $branchId = (int) $movement->branch_id;

            if (! in_array($branchId, $branchIds, true)) {
                continue;
            }

            if ((int) $movement->headcount <= 0 && empty($movement->movement_time)) {
                continue;
            }

            $period = array_key_exists((string) $movement->movement_period, ShuttleTripBranchMovement::PERIODS)
                ? (string) $movement->movement_period
                : ShuttleTripBranchMovement::DEFAULT_PERIOD;
            $matrix[$period][$branchId] ??= $this->emptyTripMovementRow($movement->branch->name ?? '-');

            if ($movement->movement_type === 'arrival') {
                $matrix[$period][$branchId]['arrival'] += (int) $movement->headcount;

                if (! empty($movement->movement_time)) {
                    $matrix[$period][$branchId]['arrival_times'][] = $this->normaliseReportTime($movement->movement_time);
                }
            }

            if ($movement->movement_type === 'departure') {
                $matrix[$period][$branchId]['departure'] += (int) $movement->headcount;

                if (! empty($movement->movement_time)) {
                    $matrix[$period][$branchId]['departure_times'][] = $this->normaliseReportTime($movement->movement_time);
                }
            }
        }

        return $matrix;
    }

    private function emptyTripMovementRow(string $branchName): array
    {
        return [
            'branch_name' => $branchName,
            'arrival' => 0,
            'departure' => 0,
            'arrival_times' => [],
            'departure_times' => [],
        ];
    }

    private function normaliseReportTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    private function formatReportTimes(array $times): string
    {
        $times = collect($times)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return $times->isEmpty() ? '-' : $times->implode(', ');
    }

    private function branchMovementTimes(array $matrix, int $branchId, string $movementType): string
    {
        $timeKey = $movementType === 'arrival' ? 'arrival_times' : 'departure_times';
        $times = [];

        foreach ($matrix as $periodRows) {
            if (! isset($periodRows[$branchId])) {
                continue;
            }

            $times = array_merge($times, $periodRows[$branchId][$timeKey] ?? []);
        }

        return $this->formatReportTimes($times);
    }

    private function sumBranchMovement(array $matrix, int $branchId, string $movementType): int
    {
        $total = 0;

        foreach ($matrix as $periodRows) {
            if (! isset($periodRows[$branchId])) {
                continue;
            }

            $total += (int) ($periodRows[$branchId][$movementType] ?? 0);
        }

        return $total;
    }

    private function branchMovementDetail(array $matrix, int $branchId): string
    {
        $details = [];

        foreach (ShuttleTripBranchMovement::PERIODS as $period => $periodLabel) {
            if (! isset($matrix[$period][$branchId])) {
                continue;
            }

            $row = $matrix[$period][$branchId];
            $arrivalTime = $this->formatReportTimes($row['arrival_times'] ?? []);
            $departureTime = $this->formatReportTimes($row['departure_times'] ?? []);
            $arrival = (int) ($row['arrival'] ?? 0);
            $departure = (int) ($row['departure'] ?? 0);

            if ($arrival <= 0 && $departure <= 0 && $arrivalTime === '-' && $departureTime === '-') {
                continue;
            }

            $details[] = $periodLabel . ': Gelen ' . $arrivalTime . ' / ' . $arrival . ', Giden ' . $departureTime . ' / ' . $departure;
        }

        return empty($details) ? '-' : implode(' | ', $details);
    }

    private function periodMovementSummary(array $matrix, string $period, string $movementType): string
    {
        if (! isset($matrix[$period])) {
            return '-';
        }

        $countKey = $movementType === 'arrival' ? 'arrival' : 'departure';
        $timeKey = $movementType === 'arrival' ? 'arrival_times' : 'departure_times';
        $details = [];

        foreach ($matrix[$period] as $movementRow) {
            $count = (int) ($movementRow[$countKey] ?? 0);
            $time = $this->formatReportTimes($movementRow[$timeKey] ?? []);

            if ($count <= 0 && $time === '-') {
                continue;
            }

            $details[] = ($movementRow['branch_name'] ?? '-') . ': ' . $time . ' / ' . $count;
        }

        return empty($details) ? '-' : implode(' | ', $details);
    }

    private function allMovementSummary(array $matrix, string $movementType): string
    {
        $details = [];

        foreach (ShuttleTripBranchMovement::PERIODS as $period => $periodLabel) {
            if (! isset($matrix[$period])) {
                continue;
            }

            $summary = $this->periodMovementSummary($matrix, $period, $movementType);
            if ($summary === '-') {
                continue;
            }

            $details[] = $periodLabel . ': ' . $summary;
        }

        return empty($details) ? '-' : implode(' | ', $details);
    }

    private function turkishDayName($date): string
    {
        $days = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Sali',
            'Wednesday' => 'Carsamba',
            'Thursday' => 'Persembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar',
        ];

        return $days[$date->format('l')] ?? $date->format('l');
    }

    private function styleTitle($sheet, string $range): void
    {
        $sheet->mergeCells($range);
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFC19B77'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF7A5C3D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function styleBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD9D9D9'],
                ],
            ],
        ]);
    }

    private function sumMovements($trips, array $branchIds, string $movementType): int
    {
        return (int) collect($trips)->sum(
            fn ($trip) => $this->sumTripMovements($trip, $branchIds, $movementType)
        );
    }

    private function sumTripMovements($trip, array $branchIds, string $movementType): int
    {
        if ($trip->branchMovements->isEmpty()) {
            if (! in_array((int) $trip->branch_id, $branchIds, true)) {
                return 0;
            }

            return (int) ($movementType === 'arrival' ? $trip->arrival_count : $trip->departure_count);
        }

        return (int) $trip->branchMovements
            ->filter(fn ($movement) => in_array((int) $movement->branch_id, $branchIds, true) && $movement->movement_type === $movementType)
            ->sum('headcount');
    }

    private function resolveTripMovementTime($trip, array $branchIds, string $movementType, string $mode): ?string
    {
        if ($trip->branchMovements->isEmpty()) {
            if (! in_array((int) $trip->branch_id, $branchIds, true)) {
                return null;
            }

            $fallbackTime = $movementType === 'arrival' ? $trip->arrival_time : $trip->departure_time;

            return $fallbackTime ? substr($fallbackTime, 0, 5) : null;
        }

        $times = $trip->branchMovements
            ->filter(function ($movement) use ($branchIds, $movementType) {
                return in_array((int) $movement->branch_id, $branchIds, true)
                    && $movement->movement_type === $movementType
                    && ! empty($movement->movement_time);
            })
            ->pluck('movement_time')
            ->map(fn ($time) => substr($time, 0, 5))
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        return $mode === 'min' ? $times->first() : $times->last();
    }
}
