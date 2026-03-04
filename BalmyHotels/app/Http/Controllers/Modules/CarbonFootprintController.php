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
        $page_title = 'Yeni Karbon Ayak İzi Raporu';

        return view('modules.carbon.create', compact(
            'branches', 'categories', 'standards', 'page_title'
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
            'report_type'        => 'required|in:monthly,quarterly,annual',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'total_guests'       => 'required|integer|min:0',
            'occupied_rooms'     => 'required|integer|min:0',
            'total_rooms'        => 'required|integer|min:0',
            'staff_count'        => 'required|integer|min:0',
            'total_area_sqm'     => 'required|numeric|min:0',
            'renewable_energy_pct' => 'nullable|numeric|min:0|max:100',
            'waste_recycling_rate' => 'nullable|numeric|min:0|max:100',
            'standards_applied'  => 'nullable|array',
            'methodology_notes'  => 'nullable|string',
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
            'entries.*.is_renewable'=> 'nullable|boolean',
            'entries.*.notes'       => 'nullable|string',
        ]);

        $report = CarbonFootprintReport::create([
            'user_id'             => Auth::id(),
            'branch_id'           => $validated['branch_id'] ?? null,
            'title'               => $validated['title'],
            'report_type'         => $validated['report_type'],
            'period_start'        => $validated['period_start'],
            'period_end'          => $validated['period_end'],
            'total_guests'        => $validated['total_guests'],
            'occupied_rooms'      => $validated['occupied_rooms'],
            'total_rooms'         => $validated['total_rooms'],
            'staff_count'         => $validated['staff_count'],
            'total_area_sqm'      => $validated['total_area_sqm'],
            'renewable_energy_pct'=> $validated['renewable_energy_pct'] ?? 0,
            'waste_recycling_rate'=> $validated['waste_recycling_rate'] ?? 0,
            'standards_applied'   => $validated['standards_applied'] ?? [],
            'methodology_notes'   => $validated['methodology_notes'] ?? null,
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
        $page_title  = $carbon->title;

        return view('modules.carbon.show', compact(
            'carbon', 'scope1Entries', 'scope2Entries', 'scope3Entries',
            'byCategory', 'scope1Total', 'scope2Total', 'scope3Total',
            'categories', 'standards', 'page_title'
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
        $page_title = 'Raporu Düzenle: ' . $carbon->title;

        return view('modules.carbon.edit', compact(
            'carbon', 'branches', 'categories', 'standards', 'page_title'
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
            'report_type'        => 'required|in:monthly,quarterly,annual',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'total_guests'       => 'required|integer|min:0',
            'occupied_rooms'     => 'required|integer|min:0',
            'total_rooms'        => 'required|integer|min:0',
            'staff_count'        => 'required|integer|min:0',
            'total_area_sqm'     => 'required|numeric|min:0',
            'renewable_energy_pct' => 'nullable|numeric|min:0|max:100',
            'waste_recycling_rate' => 'nullable|numeric|min:0|max:100',
            'standards_applied'  => 'nullable|array',
            'methodology_notes'  => 'nullable|string',
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
            'entries.*.is_renewable' => 'nullable|boolean',
            'entries.*.notes'    => 'nullable|string',
        ]);

        $carbon->update([
            'branch_id'           => $validated['branch_id'] ?? null,
            'title'               => $validated['title'],
            'report_type'         => $validated['report_type'],
            'period_start'        => $validated['period_start'],
            'period_end'          => $validated['period_end'],
            'total_guests'        => $validated['total_guests'],
            'occupied_rooms'      => $validated['occupied_rooms'],
            'total_rooms'         => $validated['total_rooms'],
            'staff_count'         => $validated['staff_count'],
            'total_area_sqm'      => $validated['total_area_sqm'],
            'renewable_energy_pct'=> $validated['renewable_energy_pct'] ?? 0,
            'waste_recycling_rate'=> $validated['waste_recycling_rate'] ?? 0,
            'standards_applied'   => $validated['standards_applied'] ?? [],
            'methodology_notes'   => $validated['methodology_notes'] ?? null,
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
        $generatedAt   = now()->format('d.m.Y H:i');

        $pdf = Pdf::loadView('modules.carbon.pdf', compact(
            'carbon', 'scope1Entries', 'scope2Entries', 'scope3Entries',
            'scope1Total', 'scope2Total', 'scope3Total',
            'categories', 'standards', 'generatedAt'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

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
            CarbonFootprintEntry::create([
                'report_id'          => $report->id,
                'scope'              => $entry['scope'],
                'category'           => $entry['category'],
                'sub_category'       => $entry['sub_category'] ?? null,
                'source_description' => $entry['source_description'] ?? null,
                'quantity'           => $entry['quantity'],
                'unit'               => $entry['unit'],
                'emission_factor'    => $entry['emission_factor'],
                'ef_source'          => $entry['ef_source'] ?? null,
                'co2_kg'             => $co2,
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

        // Su yoğunluğu m³/oda-gece
        $waterEntries = $entries->whereIn('category', ['water_municipal', 'water_wastewater']);
        $totalWaterM3 = 0;
        foreach ($waterEntries as $we) {
            if ($we->unit === 'm³' || $we->unit === 'm3') {
                $totalWaterM3 += $we->quantity;
            }
        }
        $waterIntensity = $report->occupied_rooms > 0 ? round($totalWaterM3 / $report->occupied_rooms, 4) : 0;

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
            'hcmi_score'         => $hcmiScore,
            'hcmi_rating'        => $hcmiRating,
        ]);
    }
}
