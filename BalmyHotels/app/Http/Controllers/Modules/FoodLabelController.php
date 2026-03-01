<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FoodLabel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FoodLabelController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'food_labels',
            ['index', 'export'],
            ['printSingle'],
            ['create', 'store', 'printBulk'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    public function index(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $query = FoodLabel::with('branch')
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.tr') LIKE ?", ["%$search%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%$search%"]);
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $labels   = $query->paginate(60)->withQueryString();
        $branches = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yemek İsimlik';

        return view('modules.food_labels.index', compact('labels', 'branches', 'page_title'));
    }

    public function create()
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'Yeni Yemek İsimlik';

        return view('modules.food_labels.create', compact('branches', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $name = array_filter($request->name ?? []);
        if (empty($name)) {
            return back()->withInput()->withErrors(['name' => 'En az bir dilde yemek adı giriniz.']);
        }

        FoodLabel::create([
            'branch_id'     => $request->branch_id,
            'created_by'    => auth()->id(),
            'name'          => $request->name ?? [],
            'description'   => $request->description ?? [],
            'ingredients'   => $this->parseIngredients($request),
            'calories'      => $request->calories ?: null,
            'allergens'     => $request->allergens ?? [],
            'category'      => $request->category ?: null,
            'is_vegan'      => $request->boolean('is_vegan'),
            'is_vegetarian' => $request->boolean('is_vegetarian'),
            'is_halal'      => $request->boolean('is_halal'),
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int)$request->sort_order,
        ]);

        return redirect()->route('food-labels.index')
            ->with('success', 'Yemek isimlik oluşturuldu.');
    }

    public function edit(FoodLabel $foodLabel)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::whereIn('id', $branchIds)->orderBy('name')->get();
        $page_title = 'İsimlik Düzenle';

        return view('modules.food_labels.edit', compact('foodLabel', 'branches', 'page_title'));
    }

    public function update(Request $request, FoodLabel $foodLabel)
    {
        $request->validate([
            'name'      => 'required|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $name = array_filter($request->name ?? []);
        if (empty($name)) {
            return back()->withInput()->withErrors(['name' => 'En az bir dilde yemek adı giriniz.']);
        }

        $foodLabel->update([
            'branch_id'     => $request->branch_id,
            'name'          => $request->name ?? [],
            'description'   => $request->description ?? [],
            'ingredients'   => $this->parseIngredients($request),
            'calories'      => $request->calories ?: null,
            'allergens'     => $request->allergens ?? [],
            'category'      => $request->category ?: null,
            'is_vegan'      => $request->boolean('is_vegan'),
            'is_vegetarian' => $request->boolean('is_vegetarian'),
            'is_halal'      => $request->boolean('is_halal'),
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int)$request->sort_order,
        ]);

        return redirect()->route('food-labels.index')
            ->with('success', 'Yemek isimlik güncellendi.');
    }

    public function destroy(FoodLabel $foodLabel)
    {
        $foodLabel->delete();
        return back()->with('success', 'Silindi.');
    }

    /** Tek isimlik yazdır */
    public function printSingle(FoodLabel $foodLabel)
    {
        $labels = collect([$foodLabel]);
        return view('modules.food_labels.print', compact('labels'));
    }

    /** Seçili isimlikler yazdır (POST: ids[] veya GET: ?ids=1,2,3) */
    public function printBulk(Request $request)
    {
        $ids = $request->filled('ids')
            ? (is_array($request->ids) ? $request->ids : explode(',', $request->ids))
            : [];

        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return back()->withErrors(['ids' => 'Yazdırmak için en az bir isimlik seçin.']);
        }

        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $labels = FoodLabel::whereIn('id', $ids)
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('sort_order')
            ->get();

        return view('modules.food_labels.print', compact('labels'));
    }

    // -----------------------------------------------------------------------
    /** Excel'e aktar (mevcut filtreler uygulanır) */
    public function export(Request $request)
    {
        $user      = auth()->user();
        $branchIds = $user->visibleBranchIds();

        $query = FoodLabel::with('branch')
            ->where(fn($q) => $q->whereNull('branch_id')->orWhereIn('branch_id', $branchIds))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.tr') LIKE ?", ["%$search%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%$search%"]);
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $labels   = $query->get();
        $allergens = FoodLabel::ALLERGENS;
        $categories = FoodLabel::CATEGORIES;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Yemek İsimlikler');

        // ── Başlıklar ──
        $headers = [
            'A' => 'ID',
            'B' => 'TR İsim',
            'C' => 'EN İsim',
            'D' => 'DE İsim',
            'E' => 'RU İsim',
            'F' => 'Kategori',
            'G' => 'Kalori (kcal)',
            'H' => 'Vegan',
            'I' => 'Vejetaryen',
            'J' => 'Helal',
            'K' => 'Allerjenler (TR)',
            'L' => 'Allerjenler (EN)',
            'M' => 'Allerjenler (DE)',
            'N' => 'Allerjenler (RU)',
            'O' => 'İçindekiler (TR)',
            'P' => 'İçindekiler (EN)',
            'Q' => 'İçindekiler (DE)',
            'R' => 'İçindekiler (RU)',
            'S' => 'Şube',
            'T' => 'Aktif',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
        }

        // Header stili
        $headerRange = 'A1:T1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2d6a4f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF52b788']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(18);

        // ── Veri satırları ──
        $row = 2;
        foreach ($labels as $label) {
            $labelAllergens = $label->allergens ?? [];

            $allergenTr = collect($labelAllergens)->map(fn($k) => $allergens[$k]['label']    ?? '')->filter()->implode(', ');
            $allergenEn = collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_en'] ?? '')->filter()->implode(', ');
            $allergenDe = collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_de'] ?? '')->filter()->implode(', ');
            $allergenRu = collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_ru'] ?? '')->filter()->implode(', ');

            // ingredients: cast 'array' döner, fallback olarak raw JSON decode
            $rawIng = $label->ingredients;
            if (is_string($rawIng)) {
                $rawIng = json_decode($rawIng, true) ?? [];
            }
            if (!is_array($rawIng)) {
                $rawIng = [];
            }

            $sheet->setCellValue('A' . $row, $label->id);
            $sheet->setCellValue('B' . $row, $label->getName('tr'));
            $sheet->setCellValue('C' . $row, $label->getName('en'));
            $sheet->setCellValue('D' . $row, $label->getName('de'));
            $sheet->setCellValue('E' . $row, $label->getName('ru'));
            $sheet->setCellValue('F' . $row, $categories[$label->category ?? ''] ?? ($label->category ?? ''));
            $sheet->setCellValue('G' . $row, $label->calories);
            $sheet->setCellValue('H' . $row, $label->is_vegan  ? 'Evet' : 'Hayır');
            $sheet->setCellValue('I' . $row, $label->is_vegetarian ? 'Evet' : 'Hayır');
            $sheet->setCellValue('J' . $row, $label->is_halal  ? 'Evet' : 'Hayır');
            $sheet->setCellValue('K' . $row, $allergenTr ?: 'Yok');
            $sheet->setCellValue('L' . $row, $allergenEn ?: 'None');
            $sheet->setCellValue('M' . $row, $allergenDe ?: 'Keine');
            $sheet->setCellValue('N' . $row, $allergenRu ?: 'Нет');
            $sheet->setCellValue('O' . $row, implode(', ', $rawIng['tr'] ?? []));
            $sheet->setCellValue('P' . $row, implode(', ', $rawIng['en'] ?? []));
            $sheet->setCellValue('Q' . $row, implode(', ', $rawIng['de'] ?? []));
            $sheet->setCellValue('R' . $row, implode(', ', $rawIng['ru'] ?? []));
            $sheet->setCellValue('S' . $row, $label->branch?->name ?? 'Genel');
            $sheet->setCellValue('T' . $row, $label->is_active ? 'Aktif' : 'Pasif');

            // Zebra satır
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':T' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF0F4F0');
            }

            $row++;
        }

        // Sütun genişlikleri
        $colWidths = ['A'=>6,'B'=>22,'C'=>22,'D'=>22,'E'=>22,'F'=>14,'G'=>12,'H'=>8,'I'=>10,'J'=>7,'K'=>30,'L'=>30,'M'=>30,'N'=>30,'O'=>35,'P'=>35,'Q'=>35,'R'=>35,'S'=>16,'T'=>8];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Tüm veri border
        if ($row > 2) {
            $sheet->getStyle('A2:T' . ($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8E4DC']]],
            ]);
        }

        // Üstü dondur
        $sheet->freezePane('A2');

        // İndir
        $filename = 'yemek-isimlikler-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // -----------------------------------------------------------------------
    private function parseIngredients(Request $request): array
    {
        $result = [];
        $raw    = $request->ingredients ?? [];
        foreach ($raw as $lang => $text) {
            if (empty($text)) continue;
            // virgülle ya da satır sonu ile ayrılmış liste → array
            $items = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $text)));
            $result[$lang] = array_values($items);
        }
        return $result;
    }
}
