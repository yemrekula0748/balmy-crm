<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;

class OcrController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('ocr', ['index'], [], ['extract'], [], []);
    }

    // -------------------------------------------------------------------------
    // Form sayfası
    // -------------------------------------------------------------------------

    public function index()
    {
        $tesseractPath = config('app.tesseract_path');
        $pdftoppmPath  = config('app.pdftoppm_path');

        $tesseractOk = $tesseractPath && file_exists($tesseractPath);
        $pdftoppmOk  = $pdftoppmPath  && file_exists($pdftoppmPath);

        return view('modules.ocr.index', compact(
            'tesseractPath', 'tesseractOk', 'pdftoppmPath', 'pdftoppmOk'
        ));
    }

    // -------------------------------------------------------------------------
    // OCR işlemi
    // -------------------------------------------------------------------------

    public function extract(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,bmp,tiff,tif,webp|max:51200',
            'lang' => 'nullable|string|in:tur,eng,tur+eng',
        ]);

        $tesseractPath = config('app.tesseract_path');
        $pdftoppmPath  = config('app.pdftoppm_path');
        $tesseractOk   = $tesseractPath && file_exists($tesseractPath);
        $pdftoppmOk    = $pdftoppmPath  && file_exists($pdftoppmPath);

        if (!$tesseractOk) {
            return view('modules.ocr.index', [
                'tesseractPath' => $tesseractPath,
                'tesseractOk'   => false,
                'pdftoppmPath'  => $pdftoppmPath,
                'pdftoppmOk'    => $pdftoppmOk,
                'ocr_result'    => null,
                'ocr_error'     => 'Tesseract OCR kurulu değil veya yolu hatalı.',
            ]);
        }

        $file = $request->file('file');
        $lang = $request->lang ?? 'tur+eng';
        $ext  = strtolower($file->getClientOriginalExtension());

        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
        @mkdir($tempDir, 0777, true);

        try {
            $imagePaths = [];

            if ($ext === 'pdf') {
                if (!$pdftoppmOk) {
                    return view('modules.ocr.index', [
                        'tesseractPath' => $tesseractPath,
                        'tesseractOk'   => true,
                        'pdftoppmPath'  => $pdftoppmPath,
                        'pdftoppmOk'    => false,
                        'ocr_result'    => null,
                        'ocr_error'     => 'PDF desteği için pdftoppm (Poppler) kurulu değil veya yolu hatalı.',
                        'selected_lang' => $lang,
                    ]);
                }

                $pdfPath    = $file->getRealPath();
                $outPrefix  = $tempDir . DIRECTORY_SEPARATOR . 'page';

                // pdftoppm -r 300 -png "input.pdf" "output_prefix"
                $cmd = sprintf(
                    '"%s" -r 300 -png "%s" "%s" 2>&1',
                    $pdftoppmPath, $pdfPath, $outPrefix
                );
                exec($cmd, $ppOut, $ppCode);

                // pdftoppm generates: page-1.png, page-2.png, ...
                $imagePaths = glob($tempDir . DIRECTORY_SEPARATOR . 'page-*.png');
                if (empty($imagePaths)) {
                    // some versions use page-01.png etc., catch all
                    $imagePaths = glob($tempDir . DIRECTORY_SEPARATOR . 'page*.png');
                }
                if ($imagePaths) {
                    natsort($imagePaths);
                    $imagePaths = array_values($imagePaths);
                }

                if (empty($imagePaths)) {
                    return view('modules.ocr.index', [
                        'tesseractPath' => $tesseractPath,
                        'tesseractOk'   => true,
                        'pdftoppmPath'  => $pdftoppmPath,
                        'pdftoppmOk'    => true,
                        'ocr_result'    => null,
                        'ocr_error'     => 'PDF sayfalarına dönüşüm başarısız. pdftoppm çıktısı: ' . implode(' ', array_slice($ppOut, 0, 3)),
                        'selected_lang' => $lang,
                    ]);
                }
            } else {
                $fileName = 'input.' . $ext;
                $file->move($tempDir, $fileName);
                $imagePaths = [$tempDir . DIRECTORY_SEPARATOR . $fileName];
            }

            $pageTexts  = [];
            $pageCount  = count($imagePaths);

            foreach ($imagePaths as $pageIdx => $imagePath) {
                $outputBase = $tempDir . DIRECTORY_SEPARATOR . 'out_' . $pageIdx;
                $cmd = sprintf(
                    '"%s" "%s" "%s" -l %s 2>&1',
                    $tesseractPath, $imagePath, $outputBase, $lang
                );
                exec($cmd, $tOut, $tCode);

                $textFile = $outputBase . '.txt';
                if (file_exists($textFile)) {
                    $text = trim(file_get_contents($textFile));
                    if ($text !== '') {
                        $header      = $pageCount > 1 ? "━━━ Sayfa " . ($pageIdx + 1) . " / $pageCount ━━━\n\n" : '';
                        $pageTexts[] = $header . $text;
                    }
                }
            }

            $ocrResult = implode("\n\n", $pageTexts);

            return view('modules.ocr.index', [
                'tesseractPath' => $tesseractPath,
                'tesseractOk'   => true,
                'pdftoppmPath'  => $pdftoppmPath,
                'pdftoppmOk'    => $pdftoppmOk,
                'ocr_result'    => $ocrResult,
                'ocr_error'     => null,
                'selected_lang' => $lang,
                'original_name' => $file->getClientOriginalName(),
                'page_count'    => $pageCount,
            ]);
        } finally {
            $this->cleanTemp($tempDir);
        }
    }

    // -------------------------------------------------------------------------
    // Geçici dizini temizle
    // -------------------------------------------------------------------------

    private function cleanTemp(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}
