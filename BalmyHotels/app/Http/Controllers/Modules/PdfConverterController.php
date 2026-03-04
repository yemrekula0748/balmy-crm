<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;

class PdfConverterController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('pdf_converter', ['index'], [], ['convert'], [], []);
    }

    public function index()
    {
        $libreOfficePath = config('app.libreoffice_path');
        $isConfigured    = $libreOfficePath && file_exists($libreOfficePath);
        return view('modules.pdf-converter.index', compact('isConfigured', 'libreOfficePath'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:102400',
        ]);

        $libreOfficePath = config('app.libreoffice_path');

        if (!$libreOfficePath || !file_exists($libreOfficePath)) {
            return back()->withErrors(['pdf_file' => 'LibreOffice kurulu degil. Lutfen yukleyin ve LIBREOFFICE_PATH ayarlayin.'])->withInput();
        }

        try {
            $uploadedFile = $request->file('pdf_file');
            $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $pdfPath = $uploadedFile->getRealPath();

            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'libreoffice_' . uniqid();
            mkdir($tempDir, 0777, true);
            $userProfile = $tempDir . DIRECTORY_SEPARATOR . 'userprofile';
            mkdir($userProfile, 0777, true);
            $userProfileUri = 'file:///' . str_replace('\\', '/', $userProfile);

            $cmd = sprintf(
                '"%s" --headless --norestore --nofirststartwizard "-env:UserInstallation=%s" --infilter="writer_pdf_import" --convert-to docx --outdir "%s" "%s" 2>&1',
                $libreOfficePath, $userProfileUri, $tempDir, $pdfPath
            );

            exec($cmd, $output, $exitCode);

            $baseName   = pathinfo(basename($pdfPath), PATHINFO_FILENAME);
            $outputDocx = $tempDir . DIRECTORY_SEPARATOR . $baseName . '.docx';

            if ($exitCode !== 0 || !file_exists($outputDocx)) {
                $this->cleanDir($tempDir);
                return back()->withErrors(['pdf_file' => 'Donusturme basarisiz (exit: ' . $exitCode . '). ' . implode(' ', $output)])->withInput();
            }

            $fileContent  = file_get_contents($outputDocx);
            $safeFileName = preg_replace('/[^\w\-. ]/', '_', $originalName) . '.docx';
            $this->cleanDir($tempDir);

            return response($fileContent, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $safeFileName . '"',
                'Content-Length'      => strlen($fileContent),
            ]);

        } catch (\Throwable $e) {
            if (isset($tempDir) && is_dir($tempDir)) $this->cleanDir($tempDir);
            return back()->withErrors(['pdf_file' => 'Hata: ' . $e->getMessage()])->withInput();
        }
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $f) { $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath()); }
        rmdir($dir);
    }
}
