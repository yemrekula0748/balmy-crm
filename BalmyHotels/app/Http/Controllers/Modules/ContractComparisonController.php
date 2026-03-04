<?php

namespace App\Http\Controllers\Modules;

use App\Models\ContractComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractComparisonController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'contract_compare',
            ['index'],
            ['show'],
            ['create', 'compare'],
            [],
            ['destroy']
        );
    }

    /* ---------------------------------------------------------------
     | LİSTELE
     --------------------------------------------------------------- */
    public function index()
    {
        $comparisons = ContractComparison::where('user_id', Auth::id())
            ->latest()->paginate(15);

        $page_title = 'Sözleşme Karşılaştırma';
        return view('modules.contracts.index', compact('comparisons', 'page_title'));
    }

    /* ---------------------------------------------------------------
     | FORM
     --------------------------------------------------------------- */
    public function create()
    {
        $page_title = 'Yeni Karşılaştırma';
        return view('modules.contracts.create', compact('page_title'));
    }

    /* ---------------------------------------------------------------
     | KARŞILAŞTIR (POST)
     --------------------------------------------------------------- */
    public function compare(Request $request)
    {
        $request->validate([
            'title'  => 'nullable|string|max:255',
            'file_a' => 'required|file|extensions:pdf,docx|max:32768',
            'file_b' => 'required|file|extensions:pdf,docx|max:32768',
        ], [
            'file_a.required'    => 'İlk dosyayı seçin.',
            'file_b.required'    => 'İkinci dosyayı seçin.',
            'file_a.extensions'  => 'Sadece PDF veya DOCX yükleyin.',
            'file_b.extensions'  => 'Sadece PDF veya DOCX yükleyin.',
            'file_a.max'         => 'Dosya boyutu 20MB\'ı geçemez.',
            'file_b.max'         => 'Dosya boyutu 20MB\'ı geçemez.',
        ]);

        $fileA = $request->file('file_a');
        $fileB = $request->file('file_b');

        $typeA = strtolower($fileA->getClientOriginalExtension());
        $typeB = strtolower($fileB->getClientOriginalExtension());

        if (!in_array($typeA, ['pdf', 'docx'])) {
            return back()->withErrors(['file_a' => 'Desteklenmeyen format. Lütfen PDF veya DOCX yükleyin.']);
        }
        if (!in_array($typeB, ['pdf', 'docx'])) {
            return back()->withErrors(['file_b' => 'Desteklenmeyen format. Lütfen PDF veya DOCX yükleyin.']);
        }

        // Temp konuma kopyala — upload temp dosyası silinebilir
        $tempA = tempnam(sys_get_temp_dir(), 'cmp_a_') . '.' . $typeA;
        $tempB = tempnam(sys_get_temp_dir(), 'cmp_b_') . '.' . $typeB;
        copy($fileA->getRealPath(), $tempA);
        copy($fileB->getRealPath(), $tempB);

        try {
            $textA = $this->extractText($tempA, $typeA);
            $textB = $this->extractText($tempB, $typeB);
        } catch (\Throwable $e) {
            @unlink($tempA);
            @unlink($tempB);
            return back()->withErrors(['file_a' => 'Dosya okunamadı: ' . $e->getMessage()]);
        } finally {
            @unlink($tempA);
            @unlink($tempB);
        }

        // Geçersiz UTF-8 baytlarını temizle — JSON encode hatasını önler
        $textA = $this->sanitizeUtf8($textA);
        $textB = $this->sanitizeUtf8($textB);

        $linesA = $this->splitLines($textA);
        $linesB = $this->splitLines($textB);

        $diff = $this->computeDiff($linesA, $linesB);

        $added   = collect($diff)->where('type', 'insert')->count();
        $removed = collect($diff)->where('type', 'delete')->count();
        $equal   = collect($diff)->where('type', 'equal')->count();
        $changed = collect($diff)->where('type', 'change')->count();
        $total   = $added + $removed + $equal + $changed;
        $similarity = $total > 0 ? (int) round(($equal / $total) * 100) : 100;

        $comparison = ContractComparison::create([
            'user_id'       => Auth::id(),
            'title'         => $request->input('title') ?: null,
            'file_a_name'   => $fileA->getClientOriginalName(),
            'file_b_name'   => $fileB->getClientOriginalName(),
            'file_a_type'   => $typeA,
            'file_b_type'   => $typeB,
            'lines_added'   => $added,
            'lines_removed' => $removed,
            'lines_equal'   => $equal,
            'similarity'    => $similarity,
            'diff_json'     => $diff,
        ]);

        return redirect()->route('contracts.show', $comparison)
            ->with('success', 'Karşılaştırma tamamlandı.');
    }

    /* ---------------------------------------------------------------
     | DETAY
     --------------------------------------------------------------- */
    public function show(ContractComparison $contract)
    {
        $page_title = $contract->title ?: 'Karşılaştırma #' . $contract->id;
        return view('modules.contracts.show', compact('contract', 'page_title'));
    }

    /* ---------------------------------------------------------------
     | SİL
     --------------------------------------------------------------- */
    public function destroy(ContractComparison $contract)
    {
        $contract->delete();
        return back()->with('success', 'Kayıt silindi.');
    }

    /* ===============================================================
     | YARDIMCI: Metin çıkar
     =============================================================== */
    private function extractText(string $path, string $type): string
    {
        if ($type === 'pdf') {
            return $this->extractPdf($path);
        }
        return $this->extractDocx($path);
    }

    private function extractPdf(string $path): string
    {
        $parser  = new \Smalot\PdfParser\Parser();
        $pdf     = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function extractDocx(string $path): string
    {
        // zip extension yoksa dinamik yüklemeyi dene (geliştirme ortamı)
        if (!extension_loaded('zip') && function_exists('dl')) {
            @dl(PHP_OS_FAMILY === 'Windows' ? 'php_zip.dll' : 'zip.so');
        }

        if (extension_loaded('zip')) {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $text    = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $el) {
                    $text .= $this->extractElement($el) . "\n";
                }
            }
            return $text;
        }

        // Saf-PHP yedek: ZIP merkezi dizin üzerinden oku
        // (local header'daki boyut sıfır olabileceğinden merkezi dizin şarttır)
        $xml = $this->readZipEntry($path, 'word/document.xml');
        if ($xml === null) {
            throw new \RuntimeException(
                'DOCX dosyası okunamadı. PHP zip eklentisi etkin değil. ' .
                'php.ini dosyasında "extension=zip" satırını etkinleştirin.'
            );
        }

        // Paragraf başlarını satır ayracına çevir, sonra XML etiketlerini temizle
        $xml = preg_replace('/<w:p[ \/>]/', "\n\$0", $xml);
        $xml = preg_replace('/<w:br[^>]*\/>/', "\n", $xml);
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        return $text;
    }

    /**
     * ZIP dosyasından tek bir girdiyi çıkarır — zip extension gerektirmez.
     * Merkezi dizin (Central Directory) kullanır: buradaki boyutlar her zaman doğrudur,
     * local header'daki sıfır "data descriptor" durumlarını bypass eder.
     */
    private function readZipEntry(string $zipPath, string $entryName): ?string
    {
        $data = file_get_contents($zipPath);
        if ($data === false) return null;
        $len = strlen($data);

        // 1. End of Central Directory kaydını bul (PK\x05\x06), sondan başla
        $eocdPos = false;
        for ($i = $len - 22; $i >= max(0, $len - 65558); $i--) {
            if (substr($data, $i, 4) === "\x50\x4b\x05\x06") {
                $eocdPos = $i;
                break;
            }
        }
        if ($eocdPos === false) return null;

        $cdOffset     = unpack('V', substr($data, $eocdPos + 16, 4))[1];
        $totalEntries = unpack('v', substr($data, $eocdPos + 10, 2))[1];

        // 2. Merkezi dizini tara
        $cdPos = $cdOffset;
        for ($i = 0; $i < $totalEntries; $i++) {
            if (substr($data, $cdPos, 4) !== "\x50\x4b\x01\x02") break;

            $method           = unpack('v', substr($data, $cdPos + 10, 2))[1];
            $compressedSize   = unpack('V', substr($data, $cdPos + 20, 4))[1];
            $fileNameLen      = unpack('v', substr($data, $cdPos + 28, 2))[1];
            $extraLen         = unpack('v', substr($data, $cdPos + 30, 2))[1];
            $commentLen       = unpack('v', substr($data, $cdPos + 32, 2))[1];
            $localOffset      = unpack('V', substr($data, $cdPos + 42, 4))[1];
            $fileName         = substr($data, $cdPos + 46, $fileNameLen);

            if ($fileName === $entryName) {
                // Local header'dan gerçek veri ofsetini hesapla
                $localFileNameLen = unpack('v', substr($data, $localOffset + 26, 2))[1];
                $localExtraLen    = unpack('v', substr($data, $localOffset + 28, 2))[1];
                $dataOffset       = $localOffset + 30 + $localFileNameLen + $localExtraLen;

                $compressed = substr($data, $dataOffset, $compressedSize);
                if ($method === 0) return $compressed;            // stored
                if ($method === 8) return gzinflate($compressed); // deflate
                return null;
            }

            $cdPos += 46 + $fileNameLen + $extraLen + $commentLen;
        }

        return null;
    }

    private function extractElement($element): string
    {
        $text = '';
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $child) {
                if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $child->getText();
                }
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            $text .= $element->getText();
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $cellEl) {
                        $text .= $this->extractElement($cellEl) . ' ';
                    }
                }
                $text .= "\n";
            }
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractElement($child);
            }
        }
        return $text;
    }

    /* ===============================================================
     | YARDIMCI: UTF-8 temizle
     =============================================================== */
    private function sanitizeUtf8(string $text): string
    {
        // 1. iconv ile geçersiz UTF-8 baytlarını at (IGNORE)
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($clean === false) {
            // iconv yoksa mb_convert_encoding fallback
            $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        // 2. NULL baytları ve JSON'un yutamadığı kontrol karakterlerini çıkar
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        return $clean ?? '';
    }

    /* ===============================================================
     | YARDIMCI: Satırlara böl
     =============================================================== */
    private function splitLines(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $result[] = $line;
            }
        }
        return $result;
    }

    /* ===============================================================
     | YARDIMCI: LCS tabanlı diff
     | Her eleman: ['type'=>'equal|insert|delete|change', 'a'=>..., 'b'=>...,
     |              'words_a'=>[], 'words_b'=>[]]
     =============================================================== */
    private function computeDiff(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);

        // Büyük dosyalar için satır limitini uygulamak hafızayı korur
        if ($n > 2000 || $m > 2000) {
            $a = array_slice($a, 0, 2000);
            $b = array_slice($b, 0, 2000);
            $n = count($a);
            $m = count($b);
        }

        // LCS dp tablosu
        $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $m; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Geri izleme
        $diff = [];
        $i = $n;
        $j = $m;
        $stack = [];

        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $a[$i - 1] === $b[$j - 1]) {
                $stack[] = ['type' => 'equal', 'a' => $a[$i - 1], 'b' => $b[$j - 1]];
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $dp[$i][$j - 1] >= $dp[$i - 1][$j])) {
                $stack[] = ['type' => 'insert', 'a' => null, 'b' => $b[$j - 1]];
                $j--;
            } else {
                $stack[] = ['type' => 'delete', 'a' => $a[$i - 1], 'b' => null];
                $i--;
            }
        }

        $stack = array_reverse($stack);

        // Ardışık delete+insert çiftlerini 'change' olarak birleştir
        $merged = [];
        $k = 0;
        $total = count($stack);
        while ($k < $total) {
            if (
                $k + 1 < $total &&
                $stack[$k]['type'] === 'delete' &&
                $stack[$k + 1]['type'] === 'insert'
            ) {
                $wordDiff = $this->wordDiff($stack[$k]['a'], $stack[$k + 1]['b']);
                $merged[] = [
                    'type'    => 'change',
                    'a'       => $stack[$k]['a'],
                    'b'       => $stack[$k + 1]['b'],
                    'words_a' => $wordDiff['a'],
                    'words_b' => $wordDiff['b'],
                ];
                $k += 2;
            } else {
                $merged[] = $stack[$k];
                $k++;
            }
        }

        return $merged;
    }

    /* ===============================================================
     | YARDIMCI: Kelime seviyesi diff
     =============================================================== */
    private function wordDiff(string $lineA, string $lineB): array
    {
        $wordsA = preg_split('/(\s+)/', $lineA, -1, PREG_SPLIT_DELIM_CAPTURE);
        $wordsB = preg_split('/(\s+)/', $lineB, -1, PREG_SPLIT_DELIM_CAPTURE);

        $n  = count($wordsA);
        $m  = count($wordsB);
        $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $m; $j++) {
                $dp[$i][$j] = $wordsA[$i - 1] === $wordsB[$j - 1]
                    ? $dp[$i - 1][$j - 1] + 1
                    : max($dp[$i - 1][$j], $dp[$i][$j - 1]);
            }
        }

        $resultA = [];
        $resultB = [];
        $i = $n;
        $j = $m;
        $stack = [];

        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $wordsA[$i - 1] === $wordsB[$j - 1]) {
                $stack[] = ['type' => 'equal', 'w' => $wordsA[$i - 1]];
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $dp[$i][$j - 1] >= $dp[$i - 1][$j])) {
                $stack[] = ['type' => 'insert', 'w' => $wordsB[$j - 1]];
                $j--;
            } else {
                $stack[] = ['type' => 'delete', 'w' => $wordsA[$i - 1]];
                $i--;
            }
        }

        $stack = array_reverse($stack);
        foreach ($stack as $item) {
            if ($item['type'] === 'equal') {
                $resultA[] = ['type' => 'equal',  'word' => $item['w']];
                $resultB[] = ['type' => 'equal',  'word' => $item['w']];
            } elseif ($item['type'] === 'delete') {
                $resultA[] = ['type' => 'delete', 'word' => $item['w']];
            } elseif ($item['type'] === 'insert') {
                $resultB[] = ['type' => 'insert', 'word' => $item['w']];
            }
        }

        return ['a' => $resultA, 'b' => $resultB];
    }
}
