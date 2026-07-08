<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Department;
use App\Models\ServicePlannerAssignment;
use App\Models\ServicePlannerPlan;
use App\Models\ServicePlannerServiceDefinition;
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
            ['create', 'store', 'importStops', 'calculate', 'pdf', 'storeServiceDefinition', 'storeStop'],
            ['edit', 'update', 'updateServiceDefinition', 'destroyServiceDefinition'],
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

        $serviceDefinitions = ServicePlannerServiceDefinition::query()
            ->with('branch')
            ->where(function ($query) use ($branches) {
                $query->whereNull('branch_id')
                    ->orWhereIn('branch_id', $branches->pluck('id'));
            })
            ->orderByRaw('branch_id is null desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modules.service_planner.index', compact('plans', 'branches', 'serviceDefinitions'));
    }

    public function create()
    {
        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();

        $serviceDefinitions = $this->serviceDefinitionsForBranch(old('branch_id'));

        $plan = new ServicePlannerPlan([
            'plan_date' => now()->toDateString(),
            'start_location_name' => 'Personel Toplama Noktasi',
        ]);

        return view('modules.service_planner.create', compact('plan', 'branches', 'serviceDefinitions'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        $definitions = $this->serviceDefinitionsForBranch($data['branch_id'] ?? null);

        if ($definitions->isEmpty()) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu modulde once sabit servis tanimi yapmalisiniz.',
            ]);
        }

        $plan = DB::transaction(function () use ($data, $definitions) {
            $plan = ServicePlannerPlan::create($data + [
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            $this->syncVehiclesFromDefinitions($plan, $definitions);

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
            'vehicles.assignments.stop.department',
            'stops.department',
        ]);

        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();
        $departments = Department::query()
            ->where(function ($query) use ($plan) {
                if ($plan->branch_id) {
                    $query->where('branch_id', $plan->branch_id)->orWhereNull('branch_id');
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $serviceDefinitions = $this->serviceDefinitionsForBranch($plan->branch_id);

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
            'geocoded_count' => $plan->stops->whereIn('geocode_status', ['success', 'approximate'])->count(),
            'approximate_count' => $plan->stops->where('geocode_status', 'approximate')->count(),
            'failed_geocode_count' => $plan->stops->where('geocode_status', 'failed')->count(),
            'assigned_count' => (int) $assignmentsByVehicle->sum('passenger_count'),
            'total_distance_km' => round((float) $assignmentsByVehicle->sum('total_distance_km'), 2),
        ];

        return view('modules.service_planner.show', compact('plan', 'branches', 'departments', 'serviceDefinitions', 'assignmentsByVehicle', 'stats'));
    }

    public function edit(ServicePlannerPlan $plan)
    {
        $plan->load('vehicles');
        $branches = Branch::query()
            ->whereIn('id', auth()->user()->visibleShuttleBranchIds())
            ->orderBy('name')
            ->get();
        $serviceDefinitions = $this->serviceDefinitionsForBranch($plan->branch_id);

        return view('modules.service_planner.edit', compact('plan', 'branches', 'serviceDefinitions'));
    }

    public function update(Request $request, ServicePlannerPlan $plan)
    {
        $data = $this->validatePlan($request);
        $definitions = $this->serviceDefinitionsForBranch($data['branch_id'] ?? null);

        if ($definitions->isEmpty()) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu plan icin kullanilacak sabit servis tanimi bulunamadi.',
            ]);
        }

        DB::transaction(function () use ($plan, $data, $definitions) {
            $plan->update($data + [
                'status' => 'draft',
                'route_generated_at' => null,
                'start_latitude' => null,
                'start_longitude' => null,
            ]);

            $this->clearAssignments($plan);
            $this->syncVehiclesFromDefinitions($plan, $definitions);
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

        $headers = ['Ad Soyad', 'Departman', 'Acik Adres', 'Telefon', 'Ilce/Semt', 'Not'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $examples = [
            ['Ayse Yilmaz', 'Mutfak', 'Yeni Mah. Ataturk Cad. No:12 Kemer Antalya', '05550000001', 'Kemer', 'Sabah servisi'],
            ['Mehmet Demir', 'Kat Hizmetleri', 'Arslanbucak Mah. 401 Sok. No:8 Kemer Antalya', '05550000002', 'Arslanbucak', 'Aksam servisi'],
        ];

        foreach ($examples as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 2),
                    $value
                );
            }
        }

        foreach (['A' => 24, 'B' => 22, 'C' => 54, 'D' => 18, 'E' => 18, 'F' => 22] as $column => $width) {
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
                'department_id' => $this->findDepartmentIdByName($row[$mappedColumns['department']] ?? null, $plan->branch_id),
                'department_name' => $mappedColumns['department'] ? trim((string) ($row[$mappedColumns['department']] ?? '')) : null,
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

        $this->routeService->calculate($plan->fresh());

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', count($stops) . ' personel kaydi Excel dosyasindan ice aktarildi ve servis atamalari otomatik guncellendi.');
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
            'vehicles.assignments.stop.department',
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

    public function storeServiceDefinition(Request $request)
    {
        $data = $request->validate([
            'service_branch_id' => 'nullable|exists:branches,id',
            'service_name' => 'required|string|max:100',
            'service_capacity' => 'required|integer|min:1|max:100',
            'service_color' => 'nullable|string|max:20',
            'service_sort_order' => 'nullable|integer|min:1|max:999',
        ]);

        ServicePlannerServiceDefinition::create([
            'branch_id' => $data['service_branch_id'] ?? null,
            'name' => $data['service_name'],
            'seat_capacity' => $data['service_capacity'],
            'color' => $data['service_color'] ?: $this->defaultVehicleColor((int) ($data['service_sort_order'] ?? 1) - 1),
            'sort_order' => $data['service_sort_order'] ?? 1,
            'is_active' => true,
        ]);

        return redirect()->route('service-planner.index')->with('success', 'Sabit servis tanimi eklendi.');
    }

    public function updateServiceDefinition(Request $request, ServicePlannerServiceDefinition $serviceDefinition)
    {
        $data = $request->validate([
            'service_name' => 'required|string|max:100',
            'service_capacity' => 'required|integer|min:1|max:100',
            'service_color' => 'nullable|string|max:20',
            'service_sort_order' => 'nullable|integer|min:1|max:999',
            'service_is_active' => 'nullable|boolean',
        ]);

        $serviceDefinition->update([
            'name' => $data['service_name'],
            'seat_capacity' => $data['service_capacity'],
            'color' => $data['service_color'] ?: $serviceDefinition->color,
            'sort_order' => $data['service_sort_order'] ?? $serviceDefinition->sort_order,
            'is_active' => $request->boolean('service_is_active', true),
        ]);

        return redirect()->route('service-planner.index')->with('success', 'Sabit servis tanimi guncellendi.');
    }

    public function destroyServiceDefinition(ServicePlannerServiceDefinition $serviceDefinition)
    {
        $serviceDefinition->delete();

        return redirect()->route('service-planner.index')->with('success', 'Sabit servis tanimi silindi.');
    }

    public function storeStop(Request $request, ServicePlannerPlan $plan)
    {
        $data = $request->validate([
            'passenger_name' => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'address' => 'required|string|max:2000',
            'phone' => 'nullable|string|max:40',
            'district' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $department = ! empty($data['department_id'])
            ? Department::find($data['department_id'])
            : null;

        $nextRowNumber = ((int) $plan->stops()->max('row_number')) + 1;

        $plan->stops()->create([
            'row_number' => $nextRowNumber > 0 ? $nextRowNumber : 1,
            'passenger_name' => $data['passenger_name'],
            'department_id' => $department?->id,
            'department_name' => $department?->name,
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'district' => $data['district'] ?? null,
            'notes' => $data['notes'] ?? null,
            'geocode_status' => 'pending',
        ]);

        $this->routeService->calculate($plan->fresh());

        return redirect()
            ->route('service-planner.show', $plan)
            ->with('success', 'Personel eklendi ve en uygun servise otomatik atama yeniden hesaplandi.');
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'branch_id' => 'nullable|exists:branches,id',
            'plan_date' => 'nullable|date',
            'start_location_name' => 'required|string|max:150',
            'start_address' => 'required|string|max:2000',
            'planning_notes' => 'nullable|string|max:2000',
        ]);
    }

    private function syncVehiclesFromDefinitions(ServicePlannerPlan $plan, $definitions): void
    {
        $plan->vehicles()->delete();
        $plan->vehicles()->createMany(
            $definitions->values()->map(function ($definition, $index) {
                return [
                    'name' => $definition->name,
                    'seat_capacity' => (int) $definition->seat_capacity,
                    'vehicle_order' => $index + 1,
                    'color' => $definition->color ?: $this->defaultVehicleColor($index),
                ];
            })->all()
        );
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

        $departmentColumn = $findColumn(['departman', 'departmani', 'department', 'birim']);
        $nameColumn = $findColumn(['adsoyad', 'isim', 'ad', 'name', 'personel', 'personeladi', 'personeladisoyadi']) ?? 'A';
        $addressColumn = $findColumn(['adres', 'acikadres', 'address', 'konum', 'ikametadres']) ?? ($departmentColumn ? 'C' : 'B');

        return [
            'name' => $nameColumn,
            'department' => $departmentColumn,
            'address' => $addressColumn,
            'phone' => $findColumn(['telefon', 'gsm', 'phone', 'ceptelefonu']),
            'district' => $findColumn(['ilce', 'semt', 'ilcesemt', 'district', 'bolge']),
            'notes' => $findColumn(['not', 'aciklama', 'notes']),
        ];
    }

    private function serviceDefinitionsForBranch($branchId)
    {
        $branchId = $branchId ? (int) $branchId : null;

        return ServicePlannerServiceDefinition::query()
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($subQuery) use ($branchId) {
                    $subQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }, fn ($query) => $query->whereNull('branch_id'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function findDepartmentIdByName($value, ?int $branchId): ?int
    {
        $name = trim((string) $value);
        if ($name === '') {
            return null;
        }

        return Department::query()
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($subQuery) use ($branchId) {
                    $subQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->value('id');
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
