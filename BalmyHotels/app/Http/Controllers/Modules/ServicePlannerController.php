<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\ServicePlannerAssignment;
use App\Models\ServicePlannerPlan;
use App\Services\ServicePlannerRouteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ServicePlannerController extends BaseModuleController
{
    public function __construct(
        private readonly ServicePlannerRouteService $routeService
    ) {
        $this->requirePermission(
            'service_planner',
            ['index', 'template'],
            ['show'],
            ['create', 'store', 'importStops', 'calculate', 'pdf'],
            ['edit', 'update'],
            ['destroy']
        );

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless($user && ($user->isSuperAdmin() || $user->isHumanResources()), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $plans = ServicePlannerPlan::query()
            ->with(['branch', 'vehicles', 'stops'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('start_location_name', 'like', '%' . $search . '%')
                        ->orWhere('start_address', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderByDesc('plan_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();

        return view('modules.service_planner.index', compact('plans', 'branches'));
    }

    public function create()
    {
        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();

        $plan = new ServicePlannerPlan([
            'plan_date' => now()->toDateString(),
            'start_location_name' => 'Personel Toplama Noktasi',
        ]);

        return view('modules.service_planner.create', compact('plan', 'branches'));
    }

    public function store(Request $request)
    {
        [$data, $vehicles] = $this->validatePlan($request);

        $plan = DB::transaction(function () use ($data, $vehicles) {
            $plan = ServicePlannerPlan::create($data + [
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            $this->syncVehicles($plan, $vehicles);

            return $plan;
        });

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', 'Servis plani olusturuldu. Simdi Excel yukleyip guzergah hesaplayabilirsiniz.');
    }

    public function show(ServicePlannerPlan $plan)
    {
        $plan->load([
            'branch',
            'creator',
            'vehicles.assignments.stop',
            'stops',
        ]);

        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();

        $assignmentsByVehicle = $plan->vehicles->map(function ($vehicle) {
            $assignments = $vehicle->assignments->sortBy('stop_order')->values();

            return [
                'vehicle' => $vehicle,
                'assignments' => $assignments,
                'passenger_count' => $assignments->count(),
                'total_distance_km' => round((float) $assignments->sum('leg_distance_km'), 2),
                'estimated_minutes' => (int) $assignments->sum('travel_minutes'),
            ];
        });

        $stats = [
            'stop_count' => $plan->stops->count(),
            'vehicle_count' => $plan->vehicles->count(),
            'seat_capacity' => (int) $plan->vehicles->sum('seat_capacity'),
            'geocoded_count' => $plan->stops->where('geocode_status', 'success')->count(),
            'failed_geocode_count' => $plan->stops->where('geocode_status', 'failed')->count(),
            'assigned_count' => (int) $assignmentsByVehicle->sum('passenger_count'),
            'total_distance_km' => round((float) $assignmentsByVehicle->sum('total_distance_km'), 2),
        ];

        return view('modules.service_planner.show', compact('plan', 'branches', 'assignmentsByVehicle', 'stats'));
    }

    public function edit(ServicePlannerPlan $plan)
    {
        $plan->load('vehicles');
        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();

        return view('modules.service_planner.edit', compact('plan', 'branches'));
    }

    public function update(Request $request, ServicePlannerPlan $plan)
    {
        [$data, $vehicles] = $this->validatePlan($request);

        DB::transaction(function () use ($plan, $data, $vehicles) {
            $plan->update($data + [
                'status' => 'draft',
                'route_generated_at' => null,
                'start_latitude' => null,
                'start_longitude' => null,
            ]);

            $this->clearAssignments($plan);
            $this->syncVehicles($plan, $vehicles);
        });

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', 'Servis plani guncellendi. Degisikliklerden sonra rota yeniden hesaplanabilir.');
    }

    public function destroy(ServicePlannerPlan $plan)
    {
        $plan->delete();

        return redirect()
            ->route('service-planner.index')
            ->with('success', 'Servis plani silindi.');
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Personel Listesi');

        $headers = ['Ad Soyad', 'Adres', 'Telefon', 'Ilce/Semt', 'Not'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $examples = [
            ['Ayse Yilmaz', 'Yeni Mah. Ataturk Cad. No:12 Kemer Antalya', '05550000001', 'Kemer', 'Sabah servisi'],
            ['Mehmet Demir', 'Arslanbucak Mah. 401 Sok. No:8 Kemer Antalya', '05550000002', 'Arslanbucak', 'Aksam servisi'],
        ];

        foreach ($examples as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 2),
                    $value
                );
            }
        }

        foreach (['A' => 24, 'B' => 54, 'C' => 18, 'D' => 18, 'E' => 22] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $filename = 'servis-planlayici-sablon.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importStops(Request $request, ServicePlannerPlan $plan)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'excel_file' => 'Excel dosyasinda veri bulunamadi.',
            ]);
        }

        $headerRow = array_shift($rows);
        $mappedColumns = $this->mapImportColumns($headerRow);

        $stops = [];
        foreach ($rows as $rowNumber => $row) {
            $name = trim((string) ($row[$mappedColumns['name']] ?? ''));
            $address = trim((string) ($row[$mappedColumns['address']] ?? ''));

            if ($name === '' && $address === '') {
                continue;
            }

            if ($name === '' || $address === '') {
                continue;
            }

            $stops[] = [
                'row_number' => $rowNumber + 2,
                'passenger_name' => $name,
                'address' => $address,
                'phone' => $mappedColumns['phone'] ? trim((string) ($row[$mappedColumns['phone']] ?? '')) : null,
                'district' => $mappedColumns['district'] ? trim((string) ($row[$mappedColumns['district']] ?? '')) : null,
                'notes' => $mappedColumns['notes'] ? trim((string) ($row[$mappedColumns['notes']] ?? '')) : null,
                'geocode_status' => 'pending',
            ];
        }

        if (empty($stops)) {
            throw ValidationException::withMessages([
                'excel_file' => 'Gecerli satir bulunamadi. Excelde en az Ad Soyad ve Adres kolonlari dolu olmali.',
            ]);
        }

        DB::transaction(function () use ($plan, $stops) {
            $this->clearAssignments($plan);
            $plan->stops()->delete();
            $plan->stops()->createMany($stops);
            $plan->update([
                'status' => 'draft',
                'route_generated_at' => null,
            ]);
        });

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', count($stops) . ' personel kaydi Excel dosyasindan ice aktarildi.');
    }

    public function calculate(ServicePlannerPlan $plan)
    {
        $this->routeService->calculate($plan);

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', 'Guzergah otomatik olarak hesaplandi ve servis dagilimi olusturuldu.');
    }

    public function pdf(ServicePlannerPlan $plan)
    {
        $plan->load([
            'branch',
            'creator',
            'vehicles.assignments.stop',
            'stops',
        ]);

        if ($plan->vehicles->every(fn ($vehicle) => $vehicle->assignments->isEmpty())) {
            return redirect()
                ->route('service-planner.show', $plan)
                ->with('error', 'PDF almak icin once guzergah hesaplamasi yapin.');
        }

        $routes = $plan->vehicles->map(function ($vehicle) use ($plan) {
            $assignments = $vehicle->assignments->sortBy('stop_order')->values();
            $waypoints = $assignments
                ->map(fn ($assignment) => $assignment->stop?->latitude && $assignment->stop?->longitude
                    ? $assignment->stop->latitude . ',' . $assignment->stop->longitude
                    : null)
                ->filter()
                ->values();

            return [
                'vehicle' => $vehicle,
                'assignments' => $assignments,
                'total_distance_km' => round((float) $assignments->sum('leg_distance_km'), 2),
                'estimated_minutes' => (int) $assignments->sum('travel_minutes'),
                'maps_url' => $this->buildDirectionsUrl($plan, $waypoints->all()),
            ];
        });

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'dejavu sans',
        ])->loadView('modules.service_planner.pdf', [
            'plan' => $plan,
            'routes' => $routes,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('servis-planlayici-' . Str::slug($plan->name) . '.pdf');
    }

    private function validatePlan(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'branch_id' => 'nullable|exists:branches,id',
            'plan_date' => 'nullable|date',
            'start_location_name' => 'required|string|max:150',
            'start_address' => 'required|string|max:2000',
            'planning_notes' => 'nullable|string|max:2000',
            'vehicle_names' => 'required|array|min:1',
            'vehicle_names.*' => 'nullable|string|max:80',
            'vehicle_capacities' => 'required|array|min:1',
            'vehicle_capacities.*' => 'nullable|integer|min:1|max:100',
            'vehicle_colors' => 'nullable|array',
            'vehicle_colors.*' => 'nullable|string|max:20',
        ]);

        $vehicles = [];
        foreach ($request->input('vehicle_capacities', []) as $index => $capacity) {
            if (! $capacity) {
                continue;
            }

            $vehicles[] = [
                'name' => trim((string) ($request->input('vehicle_names.' . $index) ?: 'Servis ' . ($index + 1))),
                'seat_capacity' => (int) $capacity,
                'vehicle_order' => count($vehicles) + 1,
                'color' => $request->input('vehicle_colors.' . $index) ?: $this->defaultVehicleColor(count($vehicles)),
            ];
        }

        if (empty($vehicles)) {
            throw ValidationException::withMessages([
                'vehicle_capacities' => 'En az bir servis ve koltuk kapasitesi girin.',
            ]);
        }

        return [[
            'name' => $data['name'],
            'branch_id' => $data['branch_id'] ?? null,
            'plan_date' => $data['plan_date'] ?? null,
            'start_location_name' => $data['start_location_name'],
            'start_address' => $data['start_address'],
            'planning_notes' => $data['planning_notes'] ?? null,
        ], $vehicles];
    }

    private function syncVehicles(ServicePlannerPlan $plan, array $vehicles): void
    {
        $plan->vehicles()->delete();
        $plan->vehicles()->createMany($vehicles);
    }

    private function clearAssignments(ServicePlannerPlan $plan): void
    {
        ServicePlannerAssignment::query()
            ->whereIn('service_planner_vehicle_id', $plan->vehicles()->pluck('id'))
            ->delete();
    }

    private function mapImportColumns(array $headerRow): array
    {
        $normalized = [];
        foreach ($headerRow as $column => $label) {
            $normalized[$column] = Str::of((string) $label)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '')
                ->toString();
        }

        $findColumn = function (array $aliases) use ($normalized) {
            foreach ($normalized as $column => $label) {
                if (in_array($label, $aliases, true)) {
                    return $column;
                }
            }

            return null;
        };

        $nameColumn = $findColumn(['adsoyad', 'isim', 'ad', 'name', 'personel', 'personeladi']) ?? 'A';
        $addressColumn = $findColumn(['adres', 'address', 'konum', 'ikametadres']) ?? 'B';

        return [
            'name' => $nameColumn,
            'address' => $addressColumn,
            'phone' => $findColumn(['telefon', 'gsm', 'phone', 'ceptelefonu']),
            'district' => $findColumn(['ilce', 'semt', 'district', 'bolge']),
            'notes' => $findColumn(['not', 'aciklama', 'notes']),
        ];
    }

    private function buildDirectionsUrl(ServicePlannerPlan $plan, array $waypoints): ?string
    {
        if (empty($waypoints)) {
            return null;
        }

        $origin = $plan->start_latitude . ',' . $plan->start_longitude;
        $destination = end($waypoints);
        $middleWaypoints = array_slice($waypoints, 0, -1);

        $query = [
            'api' => 1,
            'origin' => $origin,
            'destination' => $destination,
            'travelmode' => 'driving',
        ];

        if (! empty($middleWaypoints)) {
            $query['waypoints'] = implode('|', $middleWaypoints);
        }

        return 'https://www.google.com/maps/dir/?' . http_build_query($query);
    }

    private function defaultVehicleColor(int $index): string
    {
        $palette = ['#C19B77', '#5D7FA3', '#4E8D7C', '#B76E79', '#8A6FB5', '#D4904F'];

        return $palette[$index % count($palette)];
    }
}
