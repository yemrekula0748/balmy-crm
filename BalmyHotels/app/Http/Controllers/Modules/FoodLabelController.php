<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FoodLabel;
use App\Models\FoodProduct;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = mb_strtolower($request->search, 'UTF-8');
            $query->where(function ($q) use ($search) {
                foreach (['tr', 'en', 'de', 'ru', 'ar', 'fr'] as $lang) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.$lang'))) LIKE ?",
                        ["%{$search}%"]
                    );
                }
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
            'is_local_food' => $request->boolean('is_local_food'),
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
            'is_local_food' => $request->boolean('is_local_food'),
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
            'U' => 'Yöresel',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
        }

        // Header stili
        $headerRange = 'A1:U1';
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
            $sheet->setCellValue('U' . $row, $label->is_local_food ? 'Evet' : 'Hayır');

            // Zebra satır
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':U' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF0F4F0');
            }

            $row++;
        }

        // Sütun genişlikleri
        $colWidths = ['A'=>6,'B'=>22,'C'=>22,'D'=>22,'E'=>22,'F'=>14,'G'=>12,'H'=>8,'I'=>10,'J'=>7,'K'=>30,'L'=>30,'M'=>30,'N'=>30,'O'=>35,'P'=>35,'Q'=>35,'R'=>35,'S'=>16,'T'=>8,'U'=>10];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Tüm veri border
        if ($row > 2) {
            $sheet->getStyle('A2:U' . ($row - 1))->applyFromArray([
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

    /**
     * Tüm yemek isimliklerini eksiksiz aktar.
     *
     * Şube kapsamı uygulanmaz. Hem arayüz hem bu uç nokta yalnızca süper
     * yöneticiye açıktır; URL doğrudan çağrılsa da diğer roller 403 alır.
     */
    public function exportAll()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        // PhpSpreadsheet büyük çalışma kitaplarında varsayılan 128 MB sınırını aşabilir.
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        $labels = FoodLabel::with(['branch', 'creator'])
            ->orderBy('id')
            ->get();
        $products = FoodProduct::with(['branch', 'foodCategory', 'printer'])
            ->orderBy('id')
            ->get();
        $productMatches = $this->matchFoodLabelsToProducts($labels, $products);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(auth()->user()->name ?? 'Balmy CRM')
            ->setTitle('Tüm Yemek İsimlikleri')
            ->setSubject('Süper yönetici tam veri dışa aktarımı')
            ->setDescription('Yemek isimliklerinin tüm alanlarını ve JSON içe aktarım nesnelerini içerir.');

        $this->buildCompleteFoodLabelSheet($spreadsheet->getActiveSheet(), $labels, $productMatches);
        $this->buildFoodLabelJsonSheet($spreadsheet->createSheet(), $labels);
        $this->buildFoodProductSheet($spreadsheet->createSheet(), $products);
        $this->buildFoodLabelReferenceSheet(
            $spreadsheet->createSheet(),
            $labels->count(),
            $products->count(),
            $products->filter(fn ($product) => $product->protein !== null || $product->carbs !== null || $product->fat !== null)->count(),
            count($productMatches)
        );
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'tum-yemek-isimlik-verileri-' . now()->format('Y-m-d-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function buildCompleteFoodLabelSheet(Worksheet $sheet, $labels, array $productMatches): void
    {
        $sheet->setTitle('Tüm Değerler');

        $headers = [
            'ID', 'QR Token', 'Public URL', 'Şube ID', 'Şube', 'Oluşturan ID', 'Oluşturan',
            'Ad TR', 'Ad EN', 'Ad DE', 'Ad RU', 'Ad AR', 'Ad FR',
            'Açıklama TR', 'Açıklama EN', 'Açıklama DE', 'Açıklama RU', 'Açıklama AR', 'Açıklama FR',
            'İçindekiler TR', 'İçindekiler EN', 'İçindekiler DE', 'İçindekiler RU', 'İçindekiler AR', 'İçindekiler FR',
            'Kalori (kcal)', 'Protein (g)', 'Karbonhidrat (g)', 'Yağ (g)', 'Yemek Kütüphanesi Ürün ID', 'Besin Değeri Kaynağı',
            'Alerjen Anahtarları', 'EU14 Numaraları', 'Alerjenler TR', 'Alerjenler EN', 'Alerjenler DE', 'Alerjenler RU',
            'Kategori Anahtarı', 'Kategori', 'Vegan', 'Vejetaryen', 'Helal', 'Yöresel', 'Aktif', 'Sıralama',
            'Oluşturulma', 'Güncellenme', 'Ad JSON', 'Açıklama JSON', 'İçindekiler JSON', 'Alerjenler JSON', 'JSON İçe Aktarım Objesi',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($labels as $label) {
            $allergenKeys = array_values(array_filter((array) ($label->allergens ?? []), 'is_string'));
            $knownAllergens = collect($allergenKeys)
                ->map(fn ($key) => FoodLabel::ALLERGENS[$key] ?? null)
                ->filter();
            $payload = $this->foodLabelImportPayload($label);
            $matchedProduct = $productMatches[$label->id] ?? null;

            $values = [
                $label->id,
                $label->qr_token,
                $label->publicUrl(),
                $label->branch_id,
                $label->branch?->name ?? 'Genel',
                $label->created_by,
                $label->creator?->name,
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($label->name, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($label->description, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($label->ingredients, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                $label->calories,
                $matchedProduct?->protein,
                $matchedProduct?->carbs,
                $matchedProduct?->fat,
                $matchedProduct?->id,
                $matchedProduct ? 'Yemek Kütüphanesi / eşleşen ürün' : null,
                implode(', ', $allergenKeys),
                $knownAllergens->map(fn ($item) => 'EU ' . $item['eu'])->implode(', '),
                $knownAllergens->pluck('label')->implode(', '),
                $knownAllergens->pluck('label_en')->implode(', '),
                $knownAllergens->pluck('label_de')->implode(', '),
                $knownAllergens->pluck('label_ru')->implode(', '),
                $label->category,
                FoodLabel::CATEGORIES[$label->category ?? ''] ?? ($label->category ?? ''),
                $label->is_vegan ? 'Evet' : 'Hayır',
                $label->is_vegetarian ? 'Evet' : 'Hayır',
                $label->is_halal ? 'Evet' : 'Hayır',
                $label->is_local_food ? 'Evet' : 'Hayır',
                $label->is_active ? 'Aktif' : 'Pasif',
                $label->sort_order,
                optional($label->created_at)->format('Y-m-d H:i:s'),
                optional($label->updated_at)->format('Y-m-d H:i:s'),
                $this->encodeFoodLabelJson($label->name ?? []),
                $this->encodeFoodLabelJson($label->description ?? []),
                $this->encodeFoodLabelJson($label->ingredients ?? []),
                $this->encodeFoodLabelJson($allergenKeys),
                $this->encodeFoodLabelJson($payload),
            ];

            $this->writeExportRow($sheet, $row, $values);
            $row++;
        }

        $lastRow = max(1, $row - 1);
        $this->styleFoodLabelExportSheet($sheet, 'AZ', $lastRow);

        $widths = [
            'A'=>8, 'B'=>38, 'C'=>45, 'D'=>10, 'E'=>22, 'F'=>12, 'G'=>22,
            'H'=>24, 'I'=>24, 'J'=>24, 'K'=>24, 'L'=>24, 'M'=>24,
            'N'=>36, 'O'=>36, 'P'=>36, 'Q'=>36, 'R'=>36, 'S'=>36,
            'T'=>36, 'U'=>36, 'V'=>36, 'W'=>36, 'X'=>36, 'Y'=>36,
            'Z'=>13, 'AA'=>13, 'AB'=>16, 'AC'=>13, 'AD'=>20, 'AE'=>30,
            'AF'=>28, 'AG'=>24, 'AH'=>32, 'AI'=>32, 'AJ'=>32, 'AK'=>32,
            'AL'=>24, 'AM'=>24, 'AN'=>12, 'AO'=>13, 'AP'=>12, 'AQ'=>12, 'AR'=>12, 'AS'=>12,
            'AT'=>20, 'AU'=>20, 'AV'=>45, 'AW'=>45, 'AX'=>55, 'AY'=>32, 'AZ'=>75,
        ];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        if ($lastRow > 1) {
            $sheet->getStyle('N2:Y' . $lastRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('AE2:AK' . $lastRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('AT2:AU' . $lastRow)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
        }
    }

    private function buildFoodLabelJsonSheet(Worksheet $sheet, $labels): void
    {
        $sheet->setTitle('JSON Uyumlu');
        $headers = [
            'ID', 'TR Ad', 'branch_id', 'name', 'description', 'ingredients', 'calories', 'allergens',
            'category', 'is_vegan', 'is_vegetarian', 'is_halal', 'is_local_food', 'is_active', 'sort_order',
            'JSON İçe Aktarım Objesi',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($labels as $label) {
            $payload = $this->foodLabelImportPayload($label);
            $this->writeExportRow($sheet, $row, [
                $label->id,
                $this->foodLabelLanguageText($label->name, 'tr'),
                $payload['branch_id'],
                $this->encodeFoodLabelJson($payload['name']),
                $this->encodeFoodLabelJson($payload['description']),
                $this->encodeFoodLabelJson($payload['ingredients']),
                $payload['calories'],
                $this->encodeFoodLabelJson($payload['allergens']),
                $payload['category'],
                $payload['is_vegan'] ? 'true' : 'false',
                $payload['is_vegetarian'] ? 'true' : 'false',
                $payload['is_halal'] ? 'true' : 'false',
                $payload['is_local_food'] ? 'true' : 'false',
                $payload['is_active'] ? 'true' : 'false',
                $payload['sort_order'],
                $this->encodeFoodLabelJson($payload),
            ]);
            $row++;
        }

        $lastRow = max(1, $row - 1);
        $this->styleFoodLabelExportSheet($sheet, 'P', $lastRow);
        foreach (['A'=>8, 'B'=>28, 'C'=>12, 'D'=>48, 'E'=>55, 'F'=>65, 'G'=>13, 'H'=>32, 'I'=>24,
                  'J'=>13, 'K'=>16, 'L'=>13, 'M'=>15, 'N'=>13, 'O'=>12, 'P'=>90] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    /** Yemek kütüphanesindeki ürünleri, besin değerleri dahil eksiksiz aktar. */
    private function buildFoodProductSheet(Worksheet $sheet, $products): void
    {
        $sheet->setTitle('Yemek Kütüphanesi');
        $headers = [
            'Ürün ID', 'Şube ID', 'Şube', 'Kategori ID', 'Kategori', 'Yazıcı ID', 'Yazıcı',
            'Başlık TR', 'Başlık EN', 'Başlık DE', 'Başlık RU', 'Başlık AR', 'Başlık FR',
            'Açıklama TR', 'Açıklama EN', 'Açıklama DE', 'Açıklama RU', 'Açıklama AR', 'Açıklama FR',
            'İçindekiler TR', 'İçindekiler EN', 'İçindekiler DE', 'İçindekiler RU', 'İçindekiler AR', 'İçindekiler FR',
            'Fiyat', 'Bardak Fiyatı', 'Şişe Fiyatı', 'Bardak cl', 'Şişe cl', 'Görsel Yolu', 'Görsel URL',
            'Rozetler JSON', 'Alerjenler JSON', 'Kalori (kcal)', 'Protein (g)', 'Karbonhidrat (g)', 'Yağ (g)',
            'Opsiyonlar JSON', 'Aktif', 'Sıralama', 'Oluşturulma', 'Güncellenme',
            'Başlık JSON', 'Açıklama JSON', 'İçindekiler JSON', 'Tam Ürün JSON Objesi',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($products as $product) {
            $payload = $this->foodProductExportPayload($product);
            $this->writeExportRow($sheet, $row, [
                $product->id,
                $product->branch_id,
                $product->branch?->name,
                $product->food_category_id,
                $product->foodCategory?->getTitle('tr'),
                $product->printer_id,
                $product->printer?->name,
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($product->title, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($product->description, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                ...array_map(fn ($lang) => $this->foodLabelLanguageText($product->ingredients, $lang), ['tr', 'en', 'de', 'ru', 'ar', 'fr']),
                $product->price,
                $product->price_glass,
                $product->price_bottle,
                $product->cl_glass,
                $product->cl_bottle,
                $product->image,
                $product->image ? asset('uploads/' . ltrim($product->image, '/')) : null,
                $this->encodeFoodLabelJson($product->badges ?? []),
                $this->encodeFoodLabelJson($product->allergens ?? []),
                $product->calories,
                $product->protein,
                $product->carbs,
                $product->fat,
                $this->encodeFoodLabelJson($product->options ?? []),
                $product->is_active ? 'Aktif' : 'Pasif',
                $product->sort_order,
                optional($product->created_at)->format('Y-m-d H:i:s'),
                optional($product->updated_at)->format('Y-m-d H:i:s'),
                $this->encodeFoodLabelJson($product->title ?? []),
                $this->encodeFoodLabelJson($product->description ?? []),
                $this->encodeFoodLabelJson($product->ingredients ?? []),
                $this->encodeFoodLabelJson($payload),
            ]);
            $row++;
        }

        $lastRow = max(1, $row - 1);
        $this->styleFoodLabelExportSheet($sheet, 'AU', $lastRow);
        $sheet->getDefaultColumnDimension()->setWidth(18);
        foreach ([
            'A'=>10, 'B'=>10, 'C'=>22, 'D'=>12, 'E'=>28, 'F'=>11, 'G'=>22,
            'H'=>26, 'I'=>26, 'J'=>26, 'K'=>26, 'L'=>26, 'M'=>26,
            'N'=>38, 'O'=>38, 'P'=>38, 'Q'=>38, 'R'=>38, 'S'=>38,
            'T'=>38, 'U'=>38, 'V'=>38, 'W'=>38, 'X'=>38, 'Y'=>38,
            'AE'=>38, 'AF'=>48, 'AG'=>35, 'AH'=>35, 'AM'=>50,
            'AP'=>20, 'AQ'=>20, 'AR'=>55, 'AS'=>55, 'AT'=>65, 'AU'=>90,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        if ($lastRow > 1) {
            $sheet->getStyle('N2:Y' . $lastRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('AP2:AQ' . $lastRow)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
        }
    }

    private function buildFoodLabelReferenceSheet(
        Worksheet $sheet,
        int $recordCount,
        int $productCount,
        int $nutritionProductCount,
        int $matchedNutritionCount
    ): void
    {
        $sheet->setTitle('Referans');
        $sheet->fromArray([
            ['TAM VERİ DIŞA AKTARIMI', null, null, null, null, null],
            ['Yemek isimlik kaydı', $recordCount],
            ['Yemek kütüphanesi ürün kaydı', $productCount],
            ['Besin değeri bulunan kütüphane ürünü', $nutritionProductCount],
            ['İsimlikle güvenli eşleşen besin değeri', $matchedNutritionCount],
            ['Oluşturulma zamanı', now()->format('Y-m-d H:i:s')],
            ['Kapsam', 'Tüm şubeler ve genel kayıtlar'],
            ['Yetki', 'Yalnızca super_admin'],
            [],
            ['JSON alanı', 'Beklenen tür', 'Açıklama'],
            ['branch_id', 'integer|null', 'Şube kimliği; genel kayıtlar için null'],
            ['name', 'object', 'tr, en, de, ru, ar, fr başlıkları'],
            ['description', 'object', 'Dillere göre QR menü açıklamaları'],
            ['ingredients', 'object', 'Dillere göre içerik dizileri veya metinleri'],
            ['calories', 'number|null', 'Ortalama enerji değeri (kcal)'],
            ['allergens', 'array', 'EU14 alerjen anahtarları'],
            ['category', 'string|null', 'Kategori anahtarı'],
            ['is_vegan', 'boolean', 'Vegan uygunluğu'],
            ['is_vegetarian', 'boolean', 'Vejetaryen uygunluğu'],
            ['is_halal', 'boolean', 'Helal uygunluğu'],
            ['is_local_food', 'boolean', 'Yöresel yemek işareti'],
            ['is_active', 'boolean', 'Aktiflik durumu'],
            ['sort_order', 'integer', 'Sıralama değeri'],
            [],
            ['EU No', 'Anahtar', 'Türkçe', 'English', 'Deutsch', 'Русский'],
        ], null, 'A1');

        $row = 26;
        foreach (FoodLabel::ALLERGENS as $key => $allergen) {
            $sheet->fromArray([[
                'EU ' . $allergen['eu'], $key, $allergen['label'], $allergen['label_en'],
                $allergen['label_de'], $allergen['label_ru'],
            ]], null, 'A' . $row);
            $row++;
        }

        $row += 2;
        $sheet->fromArray([['Kategori Anahtarı', 'Kategori']], null, 'A' . $row);
        $categoryHeaderRow = $row;
        foreach (FoodLabel::CATEGORIES as $key => $label) {
            $row++;
            $sheet->fromArray([[$key, $label]], null, 'A' . $row);
        }

        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1B4332']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        foreach ([10, 25, $categoryHeaderRow] as $headerRow) {
            $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray($this->foodLabelHeaderStyle());
        }
        foreach (['A'=>22, 'B'=>24, 'C'=>32, 'D'=>32, 'E'=>32, 'F'=>32] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A10');
        $sheet->getStyle('A1:F' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
    }

    /**
     * İsimlik ve kütüphane arasında doğrudan foreign key bulunmadığı için,
     * besin değerlerini yalnızca çok dilli adlardan güvenle tek ürüne
     * eşleşebilen kayıtlara bağla. Belirsiz eşleşmeler bilerek dışarıda kalır.
     */
    private function matchFoodLabelsToProducts($labels, $products): array
    {
        $languages = ['tr', 'en', 'de', 'ru', 'ar', 'fr'];
        $index = [];

        foreach ($products as $product) {
            if ($product->protein === null && $product->carbs === null && $product->fat === null) {
                continue;
            }

            foreach ($languages as $language) {
                $key = $this->normalizeFoodMatchValue(($product->title ?? [])[$language] ?? '');
                if ($key !== '') {
                    $index[$language][$key][$product->id] = $product;
                }
            }
        }

        $matches = [];
        foreach ($labels as $label) {
            $candidates = [];
            $scores = [];
            foreach ($languages as $language) {
                $key = $this->normalizeFoodMatchValue(($label->name ?? [])[$language] ?? '');
                foreach (($key !== '' ? ($index[$language][$key] ?? []) : []) as $productId => $product) {
                    $candidates[$productId] = $product;
                    $scores[$productId] = ($scores[$productId] ?? 0) + 1;
                }
            }

            if ($label->branch_id !== null) {
                $sameBranchIds = array_keys(array_filter(
                    $candidates,
                    fn ($product) => (int) $product->branch_id === (int) $label->branch_id
                ));
                if ($sameBranchIds !== []) {
                    $candidates = array_intersect_key($candidates, array_flip($sameBranchIds));
                    $scores = array_intersect_key($scores, array_flip($sameBranchIds));
                }
            }

            if ($scores === []) {
                continue;
            }

            $bestScore = max($scores);
            $bestIds = array_keys(array_filter($scores, fn ($score) => $score === $bestScore));
            if (count($bestIds) === 1) {
                $matches[$label->id] = $candidates[$bestIds[0]];
            }
        }

        return $matches;
    }

    private function normalizeFoodMatchValue($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';
    }

    private function foodProductExportPayload(FoodProduct $product): array
    {
        return [
            'branch_id' => $product->branch_id,
            'food_category_id' => $product->food_category_id,
            'printer_id' => $product->printer_id,
            'title' => (array) ($product->title ?? []),
            'description' => (array) ($product->description ?? []),
            'ingredients' => (array) ($product->ingredients ?? []),
            'price' => $product->price,
            'price_glass' => $product->price_glass,
            'price_bottle' => $product->price_bottle,
            'cl_glass' => $product->cl_glass,
            'cl_bottle' => $product->cl_bottle,
            'image' => $product->image,
            'badges' => array_values((array) ($product->badges ?? [])),
            'allergens' => array_values((array) ($product->allergens ?? [])),
            'options' => array_values((array) ($product->options ?? [])),
            'calories' => $product->calories,
            'protein' => $product->protein,
            'carbs' => $product->carbs,
            'fat' => $product->fat,
            'is_active' => (bool) $product->is_active,
            'sort_order' => (int) $product->sort_order,
        ];
    }

    private function foodLabelImportPayload(FoodLabel $label): array
    {
        return [
            'branch_id' => $label->branch_id,
            'name' => (array) ($label->name ?? []),
            'description' => (array) ($label->description ?? []),
            'ingredients' => (array) ($label->ingredients ?? []),
            'calories' => $label->calories,
            'allergens' => array_values((array) ($label->allergens ?? [])),
            'category' => $label->category,
            'is_vegan' => (bool) $label->is_vegan,
            'is_vegetarian' => (bool) $label->is_vegetarian,
            'is_halal' => (bool) $label->is_halal,
            'is_local_food' => (bool) $label->is_local_food,
            'is_active' => (bool) $label->is_active,
            'sort_order' => (int) $label->sort_order,
        ];
    }

    private function foodLabelLanguageText($values, string $language): string
    {
        $values = is_array($values) ? $values : [];
        $value = $values[$language] ?? '';

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->filter(fn ($item) => is_scalar($item) && trim((string) $item) !== '')
                ->map(fn ($item) => trim((string) $item))
                ->implode(', ');
        }

        return is_scalar($value) ? (string) $value : '';
    }

    private function encodeFoodLabelJson($value): string
    {
        $json = json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $json === false ? '' : $json;
    }

    private function writeExportRow(Worksheet $sheet, int $row, array $values): void
    {
        foreach (array_values($values) as $index => $value) {
            $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1) . $row;
            if (is_string($value)) {
                $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($coordinate, $value);
            }
        }
    }

    private function styleFoodLabelExportSheet(Worksheet $sheet, string $lastColumn, int $lastRow): void
    {
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($this->foodLabelHeaderStyle());
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        if ($lastRow > 1) {
            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                'borders' => [
                    'horizontal' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFD8E4DC']],
                ],
            ]);
        }
    }

    private function foodLabelHeaderStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D6A4F']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF52B788']],
            ],
        ];
    }

    // -----------------------------------------------------------------------
    /** JSON formatında tek veya birden fazla yemek ekle (AJAX) */
    public function importJson(Request $request)
    {
        $request->validate(['json_data' => 'required|string']);

        $data = json_decode($request->json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Geçersiz JSON: ' . json_last_error_msg()], 422);
        }

        // Tek obje veya dizi desteği
        $items = (isset($data[0]) && is_array($data[0])) ? $data : [$data];

        $created = 0;
        foreach ($items as $item) {
            $name = array_filter((array)($item['name'] ?? []));
            if (empty($name)) {
                continue;
            }

            FoodLabel::create([
                'branch_id'     => $item['branch_id'] ?? null,
                'created_by'    => auth()->id(),
                'name'          => $name,
                'description'   => $item['description'] ?? [],
                'ingredients'   => $item['ingredients'] ?? [],
                'calories'      => isset($item['calories']) && $item['calories'] !== null ? (int)$item['calories'] : null,
                'allergens'     => $item['allergens'] ?? [],
                'category'      => $item['category'] ?? null,
                'is_vegan'      => (bool)($item['is_vegan'] ?? false),
                'is_vegetarian' => (bool)($item['is_vegetarian'] ?? false),
                'is_halal'      => (bool)($item['is_halal'] ?? false),
                'is_local_food' => (bool)($item['is_local_food'] ?? false),
                'is_active'     => (bool)($item['is_active'] ?? true),
                'sort_order'    => (int)($item['sort_order'] ?? 0),
            ]);

            $created++;
        }

        if ($created === 0) {
            return response()->json(['error' => 'Hiçbir kayıt eklenemedi. JSON içinde geçerli "name" alanı bulunamadı.'], 422);
        }

        return response()->json([
            'success' => true,
            'count'   => $created,
            'message' => $created . ' yemek başarıyla eklendi.',
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
