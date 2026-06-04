<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\CarbonFootprintEntry;
use App\Models\CarbonFootprintReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CarbonFootprintController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'carbon_footprint',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'finalize'],
            ['destroy']
        );
    }

    /* ===================================================================
     | INDEX — Rapor listesi
     =================================================================== */
    public function index(Request $request)
    {
        $query = CarbonFootprintReport::with(['branch', 'user'])->latest();

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('year')) {
            $query->whereYear('period_start', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        $reports  = $query->paginate(15)->withQueryString();
        $branches = Branch::orderBy('name')->get();
        $page_title = 'Karbon Ayak İzi Raporları';

        // Özet istatistikler
        $totalReports = CarbonFootprintReport::count();
        $totalCo2     = CarbonFootprintReport::where('status', 'final')->sum('total_co2_total');
        $avgScore     = CarbonFootprintReport::where('status', 'final')->avg('hcmi_score');
        $latestReport = CarbonFootprintReport::where('status', 'final')->latest()->first();

        return view('modules.carbon.index', compact(
            'reports', 'branches', 'page_title',
            'totalReports', 'totalCo2', 'avgScore', 'latestReport'
        ));
    }

    /* ===================================================================
     | CREATE — Yeni rapor formu
     =================================================================== */
    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $categories = CarbonFootprintReport::CATEGORIES;
        $standards  = CarbonFootprintReport::STANDARDS;
        $sourceReferences = CarbonFootprintReport::SOURCE_REFERENCES;
        $inputSchema = CarbonFootprintReport::INPUT_SCHEMA;
        $factorDatasetVersion = CarbonFootprintReport::DEFAULT_FACTOR_DATASET_VERSION;
        $page_title = 'Yeni Karbon Ayak İzi Raporu';

        return view('modules.carbon.create', compact(
            'branches', 'categories', 'standards', 'sourceReferences',
            'inputSchema', 'factorDatasetVersion', 'page_title'
        ));
    }

    /* ===================================================================
     | STORE — Raporu kaydet
     =================================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:200',
            'branch_id'          => 'nullable|exists:branches,id',
            'hotel_name'         => 'nullable|string|max:200',
            'location'           => 'nullable|string|max:200',
            'report_type'        => 'required|in:monthly,quarterly,annual',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'total_guests'       => 'required|integer|min:0',
            'occupied_rooms'     => 'required|integer|min:0',
            'total_rooms'        => 'required|integer|min:0',
            'total_beds'         => 'nullable|integer|min:0',
            'staff_count'        => 'required|integer|min:0',
            'female_staff_count' => 'nullable|integer|min:0',
            'male_staff_count'   => 'nullable|integer|min:0',
            'total_area_sqm'     => 'required|numeric|min:0',
            'open_area_sqm'      => 'nullable|numeric|min:0',
            'occupancy_rate'     => 'nullable|numeric|min:0|max:100',
            'average_stay_days'  => 'nullable|numeric|min:0',
            'renewable_energy_pct' => 'nullable|numeric|min:0|max:100',
            'waste_recycling_rate' => 'nullable|numeric|min:0|max:100',
            'standards_applied'  => 'nullable|array',
            'factor_dataset_version' => 'nullable|string|max:255',
            'methodology_notes'  => 'nullable|string',
            'verification_notes' => 'nullable|string',
            'iso_14001_notes'    => 'nullable|string',
            'improvement_notes'  => 'nullable|string',

            // Entries
            'entries'               => 'required|array|min:1',
            'entries.*.scope'       => 'required|integer|in:1,2,3',
            'entries.*.category'    => 'required|string|max:60',
            'entries.*.sub_category'=> 'nullable|string|max:100',
            'entries.*.source_description' => 'nullable|string|max:200',
            'entries.*.quantity'    => 'required|numeric|min:0',
            'entries.*.unit'        => 'required|string|max:20',
            'entries.*.emission_factor' => 'required|numeric|min:0',
            'entries.*.ef_source'   => 'nullable|string|max:100',
            'entries.*.standard_code' => 'nullable|string|max:60',
            'entries.*.frequency'   => 'nullable|string|max:30',
            'entries.*.calculation_method' => 'nullable|string',
            'entries.*.evidence_reference' => 'nullable|string|max:255',
            'entries.*.is_renewable'=> 'nullable|boolean',
            'entries.*.notes'       => 'nullable|string',
        ]);

        $report = CarbonFootprintReport::create([
            'user_id'             => Auth::id(),
            'branch_id'           => $validated['branch_id'] ?? null,
            'title'               => $validated['title'],
            'hotel_name'          => $validated['hotel_name'] ?? null,
            'location'            => $validated['location'] ?? null,
            'report_type'         => $validated['report_type'],
            'period_start'        => $validated['period_start'],
            'period_end'          => $validated['period_end'],
            'total_guests'        => $validated['total_guests'],
            'occupied_rooms'      => $validated['occupied_rooms'],
            'total_rooms'         => $validated['total_rooms'],
            'total_beds'          => $validated['total_beds'] ?? 0,
            'staff_count'         => $validated['staff_count'],
            'female_staff_count'  => $validated['female_staff_count'] ?? 0,
            'male_staff_count'    => $validated['male_staff_count'] ?? 0,
            'total_area_sqm'      => $validated['total_area_sqm'],
            'open_area_sqm'       => $validated['open_area_sqm'] ?? 0,
            'occupancy_rate'      => $validated['occupancy_rate'] ?? 0,
            'average_stay_days'   => $validated['average_stay_days'] ?? 0,
            'renewable_energy_pct'=> $validated['renewable_energy_pct'] ?? 0,
            'waste_recycling_rate'=> $validated['waste_recycling_rate'] ?? 0,
            'standards_applied'   => $validated['standards_applied'] ?? [],
            'factor_dataset_version' => $validated['factor_dataset_version'] ?? CarbonFootprintReport::DEFAULT_FACTOR_DATASET_VERSION,
            'methodology_notes'   => $validated['methodology_notes'] ?? null,
            'verification_notes'  => $validated['verification_notes'] ?? null,
            'iso_14001_notes'     => $validated['iso_14001_notes'] ?? null,
            'improvement_notes'   => $validated['improvement_notes'] ?? null,
            'status'              => 'draft',
        ]);

        $this->saveEntries($report, $validated['entries']);
        $this->recalculateTotals($report);

        return redirect()->route('carbon.show', $report)
            ->with('success', 'Rapor oluşturuldu. Verileri kontrol edin ve raporu finalize edin.');
    }

    /* ===================================================================
     | SHOW — Rapor detayı
     =================================================================== */
    public function show(CarbonFootprintReport $carbon)
    {
        $carbon->load(['entries' => fn($q) => $q->orderBy('scope')->orderBy('category'), 'branch', 'user']);

        $scope1Entries = $carbon->entries->where('scope', 1);
        $scope2Entries = $carbon->entries->where('scope', 2);
        $scope3Entries = $carbon->entries->where('scope', 3);

        $byCategory = $carbon->entries->groupBy('category');
        $scope1Total = $scope1Entries->sum('co2_kg');
        $scope2Total = $scope2Entries->sum('co2_kg');
        $scope3Total = $scope3Entries->sum('co2_kg');

        $categories  = CarbonFootprintReport::CATEGORIES;
        $standards   = CarbonFootprintReport::STANDARDS;
        $sourceReferences = CarbonFootprintReport::SOURCE_REFERENCES;
        $inputSchema = CarbonFootprintReport::INPUT_SCHEMA;
        $auditChecks = $carbon->auditChecks();
        $page_title  = $carbon->title;

        return view('modules.carbon.show', compact(
            'carbon', 'scope1Entries', 'scope2Entries', 'scope3Entries',
            'byCategory', 'scope1Total', 'scope2Total', 'scope3Total',
            'categories', 'standards', 'sourceReferences', 'inputSchema',
            'auditChecks', 'page_title'
        ));
    }

    /* ===================================================================
     | EDIT
     =================================================================== */
    public function edit(CarbonFootprintReport $carbon)
    {
        $carbon->load('entries');
        $branches   = Branch::orderBy('name')->get();
        $categories = CarbonFootprintReport::CATEGORIES;
        $standards  = CarbonFootprintReport::STANDARDS;
        $sourceReferences = CarbonFootprintReport::SOURCE_REFERENCES;
        $inputSchema = CarbonFootprintReport::INPUT_SCHEMA;
        $factorDatasetVersion = $carbon->factor_dataset_version ?: CarbonFootprintReport::DEFAULT_FACTOR_DATASET_VERSION;
        $page_title = 'Raporu Düzenle: ' . $carbon->title;

        return view('modules.carbon.edit', compact(
            'carbon', 'branches', 'categories', 'standards', 'sourceReferences',
            'inputSchema', 'factorDatasetVersion', 'page_title'
        ));
    }

    /* ===================================================================
     | UPDATE
     =================================================================== */
    public function update(Request $request, CarbonFootprintReport $carbon)
    {
        abort_if($carbon->status === 'verified', 403, 'Doğrulanmış raporlar düzenlenemez.');

        $validated = $request->validate([
            'title'              => 'required|string|max:200',
            'branch_id'          => 'nullable|exists:branches,id',
            'hotel_name'         => 'nullable|string|max:200',
            'location'           => 'nullable|string|max:200',
            'report_type'        => 'required|in:monthly,quarterly,annual',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'total_guests'       => 'required|integer|min:0',
            'occupied_rooms'     => 'required|integer|min:0',
            'total_rooms'        => 'required|integer|min:0',
            'total_beds'         => 'nullable|integer|min:0',
            'staff_count'        => 'required|integer|min:0',
            'female_staff_count' => 'nullable|integer|min:0',
            'male_staff_count'   => 'nullable|integer|min:0',
            'total_area_sqm'     => 'required|numeric|min:0',
            'open_area_sqm'      => 'nullable|numeric|min:0',
            'occupancy_rate'     => 'nullable|numeric|min:0|max:100',
            'average_stay_days'  => 'nullable|numeric|min:0',
            'renewable_energy_pct' => 'nullable|numeric|min:0|max:100',
            'waste_recycling_rate' => 'nullable|numeric|min:0|max:100',
            'standards_applied'  => 'nullable|array',
            'factor_dataset_version' => 'nullable|string|max:255',
            'methodology_notes'  => 'nullable|string',
            'verification_notes' => 'nullable|string',
            'iso_14001_notes'    => 'nullable|string',
            'improvement_notes'  => 'nullable|string',
            'entries'            => 'required|array|min:1',
            'entries.*.scope'    => 'required|integer|in:1,2,3',
            'entries.*.category' => 'required|string|max:60',
            'entries.*.sub_category' => 'nullable|string|max:100',
            'entries.*.source_description' => 'nullable|string|max:200',
            'entries.*.quantity' => 'required|numeric|min:0',
            'entries.*.unit'     => 'required|string|max:20',
            'entries.*.emission_factor' => 'required|numeric|min:0',
            'entries.*.ef_source'=> 'nullable|string|max:100',
            'entries.*.standard_code' => 'nullable|string|max:60',
            'entries.*.frequency' => 'nullable|string|max:30',
            'entries.*.calculation_method' => 'nullable|string',
            'entries.*.evidence_reference' => 'nullable|string|max:255',
            'entries.*.is_renewable' => 'nullable|boolean',
            'entries.*.notes'    => 'nullable|string',
        ]);

        $carbon->update([
            'branch_id'           => $validated['branch_id'] ?? null,
            'title'               => $validated['title'],
            'hotel_name'          => $validated['hotel_name'] ?? null,
            'location'            => $validated['location'] ?? null,
            'report_type'         => $validated['report_type'],
            'period_start'        => $validated['period_start'],
            'period_end'          => $validated['period_end'],
            'total_guests'        => $validated['total_guests'],
            'occupied_rooms'      => $validated['occupied_rooms'],
            'total_rooms'         => $validated['total_rooms'],
            'total_beds'          => $validated['total_beds'] ?? 0,
            'staff_count'         => $validated['staff_count'],
            'female_staff_count'  => $validated['female_staff_count'] ?? 0,
            'male_staff_count'    => $validated['male_staff_count'] ?? 0,
            'total_area_sqm'      => $validated['total_area_sqm'],
            'open_area_sqm'       => $validated['open_area_sqm'] ?? 0,
            'occupancy_rate'      => $validated['occupancy_rate'] ?? 0,
            'average_stay_days'   => $validated['average_stay_days'] ?? 0,
            'renewable_energy_pct'=> $validated['renewable_energy_pct'] ?? 0,
            'waste_recycling_rate'=> $validated['waste_recycling_rate'] ?? 0,
            'standards_applied'   => $validated['standards_applied'] ?? [],
            'factor_dataset_version' => $validated['factor_dataset_version'] ?? CarbonFootprintReport::DEFAULT_FACTOR_DATASET_VERSION,
            'methodology_notes'   => $validated['methodology_notes'] ?? null,
            'verification_notes'  => $validated['verification_notes'] ?? null,
            'iso_14001_notes'     => $validated['iso_14001_notes'] ?? null,
            'improvement_notes'   => $validated['improvement_notes'] ?? null,
            'status'              => 'draft',
            'pdf_path'            => null,  // PDF sıfırla
        ]);

        $carbon->entries()->delete();
        $this->saveEntries($carbon, $validated['entries']);
        $this->recalculateTotals($carbon);

        return redirect()->route('carbon.show', $carbon)
            ->with('success', 'Rapor güncellendi.');
    }

    /* ===================================================================
     | FİNALİZE — Raporu finalize et
     =================================================================== */
    public function finalize(CarbonFootprintReport $carbon)
    {
        abort_if($carbon->status === 'verified', 403);

        $this->recalculateTotals($carbon);
        $carbon->update([
            'status'       => 'final',
            'finalized_at' => now(),
        ]);

        return redirect()->route('carbon.show', $carbon)
            ->with('success', 'Rapor finalize edildi.');
    }

    /* ===================================================================
     | PDF — Rapor PDF oluştur ve indir
     =================================================================== */
    public function pdf(CarbonFootprintReport $carbon)
    {
        $carbon->load(['entries' => fn($q) => $q->orderBy('scope')->orderBy('category'), 'branch', 'user']);

        $scope1Entries = $carbon->entries->where('scope', 1);
        $scope2Entries = $carbon->entries->where('scope', 2);
        $scope3Entries = $carbon->entries->where('scope', 3);
        $scope1Total   = $scope1Entries->sum('co2_kg');
        $scope2Total   = $scope2Entries->sum('co2_kg');
        $scope3Total   = $scope3Entries->sum('co2_kg');
        $categories    = CarbonFootprintReport::CATEGORIES;
        $standards     = CarbonFootprintReport::STANDARDS;
        $sourceReferences = CarbonFootprintReport::SOURCE_REFERENCES;
        $inputSchema = CarbonFootprintReport::INPUT_SCHEMA;
        $auditChecks = $carbon->auditChecks();
        $generatedAt   = now()->format('d.m.Y H:i');

        // Logo base64
        $logoFile   = public_path('images/logo.png');
        $logoBase64 = file_exists($logoFile)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
            : null;

        // DomPDF font dizinlerini hazırla
        $fontDir   = base_path('vendor/dompdf/dompdf/lib/fonts');
        $fontCache = storage_path('fonts');
        if (!is_dir($fontCache)) {
            mkdir($fontCache, 0755, true);
        }

        $pdf = Pdf::loadView('modules.carbon.pdf', compact(
            'carbon', 'scope1Entries', 'scope2Entries', 'scope3Entries',
            'scope1Total', 'scope2Total', 'scope3Total',
            'categories', 'standards', 'sourceReferences', 'inputSchema',
            'auditChecks', 'generatedAt', 'logoBase64'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption([
            'defaultFont'            => 'dejavu sans',
            'isHtml5ParserEnabled'   => true,
            'isRemoteEnabled'        => true,
            'enable_font_subsetting' => true,
            'fontDir'                => $fontDir,
            'fontCache'              => $fontCache,
        ]);

        $filename = 'karbon-raporu-' . $carbon->id . '-' . now()->format('Ymd') . '.pdf';

        // PDF'i storage'a kaydet
        $pdfContent = $pdf->output();
        $storagePath = 'carbon_reports/' . $filename;
        Storage::put($storagePath, $pdfContent);

        // Raporda kaydet
        $carbon->update(['pdf_path' => $storagePath]);

        return $pdf->download($filename);
    }

    /* ===================================================================
     | DESTROY
     =================================================================== */
    public function destroy(CarbonFootprintReport $carbon)
    {
        if ($carbon->pdf_path) {
            Storage::delete($carbon->pdf_path);
        }
        $carbon->delete();

        return redirect()->route('carbon.index')
            ->with('success', 'Rapor silindi.');
    }

    /* ===================================================================
     | AJAX — Emisyon faktörü getir
     =================================================================== */
    public function emissionFactor(Request $request)
    {
        $category = $request->category;
        $allCats  = CarbonFootprintReport::CATEGORIES;

        foreach ($allCats as $scopeKey => $cats) {
            if (isset($cats[$category])) {
                return response()->json($cats[$category]);
            }
        }

        return response()->json(['ef' => 0, 'unit' => '', 'ef_source' => '']);
    }

    /* ===================================================================
     | YARDIMCI: Entries kaydet
     =================================================================== */
    private function saveEntries(CarbonFootprintReport $report, array $entries): void
    {
        foreach ($entries as $entry) {
            $co2 = round(($entry['quantity'] ?? 0) * ($entry['emission_factor'] ?? 0), 3);
            $definition = CarbonFootprintReport::categoryDefinition($entry['category'] ?? null) ?? [];
            $defaultStandard = $definition['standard']
                ?? CarbonFootprintReport::standardForCategory($entry['category'] ?? null, (int) ($entry['scope'] ?? 0));
            CarbonFootprintEntry::create([
                'report_id'          => $report->id,
                'scope'              => $entry['scope'],
                'category'           => $entry['category'],
                'sub_category'       => $entry['sub_category'] ?? ($definition['sub_category'] ?? null),
                'source_description' => $entry['source_description'] ?? null,
                'quantity'           => $entry['quantity'],
                'unit'               => $entry['unit'],
                'emission_factor'    => $entry['emission_factor'],
                'ef_source'          => $entry['ef_source'] ?? null,
                'co2_kg'             => $co2,
                'standard_code'      => $entry['standard_code'] ?? $defaultStandard,
                'frequency'          => $entry['frequency'] ?? ($definition['frequency'] ?? 'aylık'),
                'calculation_method' => $entry['calculation_method'] ?? CarbonFootprintReport::calculationMethodFor($entry['category'] ?? null),
                'evidence_reference' => $entry['evidence_reference'] ?? null,
                'is_renewable'       => !empty($entry['is_renewable']),
                'notes'              => $entry['notes'] ?? null,
            ]);
        }
    }

    /* ===================================================================
     | YARDIMCI: Toplamları yeniden hesapla
     =================================================================== */
    private function recalculateTotals(CarbonFootprintReport $report): void
    {
        $entries = $report->entries()->get();

        $scope1 = $entries->where('scope', 1)->sum('co2_kg');
        $scope2 = $entries->where('scope', 2)->sum('co2_kg');
        $scope3 = $entries->where('scope', 3)->sum('co2_kg');
        $total  = $scope1 + $scope2 + $scope3;

        $perGuest    = $report->total_guests > 0     ? round($total / $report->total_guests, 4)     : 0;
        $perRoom     = $report->occupied_rooms > 0   ? round($total / $report->occupied_rooms, 4)   : 0;
        $perSqm      = $report->total_area_sqm > 0   ? round($total / $report->total_area_sqm, 4)   : 0;
        $perStaff    = $report->staff_count > 0      ? round($total / $report->staff_count, 4)      : 0;
        $days = $report->period_start && $report->period_end
            ? max(1, $report->period_start->diffInDays($report->period_end) + 1)
            : 1;
        $maxRoomNights = $report->total_rooms > 0 ? $report->total_rooms * $days : 0;
        $occupancyRate = $maxRoomNights > 0 ? round(($report->occupied_rooms / $maxRoomNights) * 100, 2) : (float) $report->occupancy_rate;
        $averageStayDays = $report->total_guests > 0 ? round($report->occupied_rooms / $report->total_guests, 2) : (float) $report->average_stay_days;

        // Su yoğunluğu m³/oda-gece
        $waterEntries = $entries->whereIn('category', ['water_municipal', 'water_wastewater']);
        $totalWaterM3 = 0;
        foreach ($waterEntries as $we) {
            if ($we->unit === 'm³' || $we->unit === 'm3') {
                $totalWaterM3 += $we->quantity;
            }
        }
        $waterIntensity = $report->occupied_rooms > 0 ? round($totalWaterM3 / $report->occupied_rooms, 4) : 0;
        $electricityKwh = (float) $entries
            ->whereIn('category', ['energy_electricity', 'energy_electricity_distribution'])
            ->sum('quantity');
        $renewableKwh = (float) $entries
            ->whereIn('category', ['energy_electricity_re', 'energy_electricity_irec', 'energy_onsite_solar', 'energy_renewable'])
            ->sum('quantity');
        $renewablePct = ($electricityKwh + $renewableKwh) > 0
            ? round(($renewableKwh / ($electricityKwh + $renewableKwh)) * 100, 2)
            : (float) $report->renewable_energy_pct;
        $recycledWasteKg = (float) $entries
            ->whereIn('category', ['waste_recycled', 'waste_plastic', 'waste_glass', 'waste_paper', 'waste_metal'])
            ->sum('quantity');
        $totalWasteKg = (float) $entries
            ->whereIn('category', [
                'waste_general', 'waste_food', 'waste_organic', 'waste_recycled',
                'waste_plastic', 'waste_glass', 'waste_paper', 'waste_metal',
                'waste_hazardous', 'waste_electronic', 'waste_battery',
            ])
            ->sum('quantity');
        $wasteRecyclingRate = $totalWasteKg > 0
            ? round(($recycledWasteKg / $totalWasteKg) * 100, 2)
            : (float) $report->waste_recycling_rate;

        // HCMI Skor (basit benchmark kalkülatör)
        // HCMI referans: ~30 kgCO2e/oda-gece ortalama otel, en iyi ~5 kgCO2e/oda-gece
        $hcmiScore = null;
        $hcmiRating = null;
        if ($perRoom > 0) {
            // Skala: 5 kg = 100p, 80 kg = 0p
            $hcmiScore  = max(0, min(100, round(100 - (($perRoom - 5) / 75) * 100, 2)));
            $hcmiRating = CarbonFootprintReport::computeHcmiRating($hcmiScore);
        }

        $report->update([
            'total_co2_scope1'   => round($scope1, 3),
            'total_co2_scope2'   => round($scope2, 3),
            'total_co2_scope3'   => round($scope3, 3),
            'total_co2_total'    => round($total, 3),
            'co2_per_guest'      => $perGuest,
            'co2_per_room_night' => $perRoom,
            'co2_per_sqm'        => $perSqm,
            'co2_per_staff'      => $perStaff,
            'water_intensity'    => $waterIntensity,
            'renewable_energy_pct' => $renewablePct,
            'waste_recycling_rate' => $wasteRecyclingRate,
            'occupancy_rate'     => $occupancyRate,
            'average_stay_days'  => $averageStayDays,
            'hcmi_score'         => $hcmiScore,
            'hcmi_rating'        => $hcmiRating,
        ]);
    }
}
