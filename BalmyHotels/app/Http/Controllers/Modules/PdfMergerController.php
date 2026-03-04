<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfMergerController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('pdf_merger', ['index'], [], ['merge'], [], []);
    }

    public function index()
    {
        return view('modules.pdf-merger.index');
    }

    public function merge(Request $request)
    {
        $request->validate([
            'pdf_files'   => 'required|array|min:2|max:20',
            'pdf_files.*' => 'required|file|mimes:pdf|max:51200',
            'output_name' => 'nullable|string|max:100',
        ], [
            'pdf_files.required' => 'En az 2 PDF dosyası seçmelisiniz.',
            'pdf_files.min'      => 'En az 2 PDF dosyası gereklidir.',
            'pdf_files.max'      => 'En fazla 20 PDF dosyası birleştirilebilir.',
            'pdf_files.*.mimes'  => 'Yalnızca PDF dosyaları yüklenebilir.',
            'pdf_files.*.max'    => 'Her dosya en fazla 50 MB olabilir.',
        ]);

        try {
            $files      = $request->file('pdf_files');
            $outputName = trim($request->input('output_name', '')) ?: 'birlestirilmis-' . date('Ymd-His');
            $outputName = preg_replace('/[^a-zA-Z0-9\-_\. ğüşıöçĞÜŞİÖÇ]/u', '', $outputName);

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $totalPages = 0;

            foreach ($files as $file) {
                try {
                    $pageCount = $pdf->setSourceFile($file->getRealPath());
                } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                    // PDF 1.5+ compressed xref — FPDI (ücretsiz) bu formatı desteklemiyor
                    $fileName = $file->getClientOriginalName();
                    return back()->withErrors([
                        'pdf_files' => "\"$fileName\" dosyası PDF 1.5+ formatındadır (sıkıştırılmış). "
                            . "Bu dosyayı birleştirmek için önce PDF/A veya PDF 1.4 formatına dönüştürün. "
                            . "(Ör: Adobe Acrobat → Farklı Kaydet → PDF 1.4)",
                    ])->withInput();
                } catch (\Throwable $e) {
                    $fileName = $file->getClientOriginalName();
                    return back()->withErrors([
                        'pdf_files' => "\"$fileName\" dosyası işlenemedi: " . $e->getMessage(),
                    ])->withInput();
                }

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl  = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);

                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);
                    $totalPages++;
                }
            }

            if ($totalPages === 0) {
                return back()->withErrors(['pdf_files' => 'Birleştirilecek sayfa bulunamadı.'])->withInput();
            }

            $pdfData  = $pdf->Output('S'); // S = string olarak döndür
            $fileName = $outputName . '.pdf';

            return response($pdfData, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length'      => strlen($pdfData),
            ]);

        } catch (\Throwable $e) {
            return back()->withErrors(['pdf_files' => 'Birleştirme hatası: ' . $e->getMessage()])->withInput();
        }
    }
}