<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ItBackupController extends BaseModuleController
{
    private string $backupDir;

    public function __construct()
    {
        $this->requirePermission('it_backup',
            ['index'],
            [],
            ['run'],
            [],
            ['deleteFile']
        );
        $this->backupDir = storage_path('app/backups');
    }

    public function index()
    {
        // Tüm tabloları DB'den al
        $tables = $this->getAllTables();

        // Mevcut yedek dosyalarını listele
        $files = $this->listBackupFiles();

        return view('modules.bilgi_islem.backup.index', compact('tables', 'files'));
    }

    public function run(Request $request)
    {
        $request->validate([
            'backup_type' => 'required|in:full,tables',
            'tables'      => 'array',
            'tables.*'    => 'string',
        ]);

        $allTables  = $this->getAllTables();
        $mysqldump  = $this->findMysqldump();

        if (!$mysqldump) {
            return back()->withErrors(['backup' => 'mysqldump komutu bulunamadı. Sunucuda MySQL Client kurulu olduğundan emin olun.']);
        }

        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        if (empty($database)) {
            return back()->withErrors(['backup' => 'Veritabanı adı tanımlı değil.']);
        }

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $label     = $request->backup_type === 'full' ? 'full' : 'tables';
        $filename  = "backup_{$label}_{$timestamp}.sql";
        $filepath  = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if ($request->backup_type === 'tables') {
            // Seçilen tabloları whitelist ile doğrula
            $selected = array_filter(
                (array) $request->input('tables', []),
                fn($t) => in_array($t, $allTables, true)
            );

            if (empty($selected)) {
                return back()->withErrors(['backup' => 'Lütfen en az bir tablo seçin.']);
            }

            $tableArgs = implode(' ', array_map('escapeshellarg', $selected));
        } else {
            $tableArgs = '';
        }

        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s %s > %s 2>&1',
            escapeshellcmd($mysqldump),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($database),
            $tableArgs,
            escapeshellarg($filepath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
            return back()->withErrors(['backup' => 'Yedekleme başarısız oldu. Sunucu loglarını kontrol edin.']);
        }

        return back()->with('success', "Yedek başarıyla oluşturuldu: {$filename}");
    }

    public function download(string $filename)
    {
        // Path traversal önlemi: sadece dosya adı (yol yok)
        $filename = basename($filename);

        // Sadece .sql uzantısı ve güvenli karakter seti
        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql$/', $filename)) {
            abort(400, 'Geçersiz dosya adı.');
        }

        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filepath)) {
            abort(404, 'Dosya bulunamadı.');
        }

        return response()->download($filepath);
    }

    public function deleteFile(string $filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql$/', $filename)) {
            abort(400, 'Geçersiz dosya adı.');
        }

        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filepath)) {
            @unlink($filepath);
        }

        return back()->with('success', 'Yedek dosyası silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function getAllTables(): array
    {
        $results = DB::select('SHOW TABLES');
        $key     = 'Tables_in_' . config('database.connections.mysql.database');
        return array_map(fn($r) => $r->$key, $results);
    }

    private function listBackupFiles(): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        if (!$files) return [];

        $list = [];
        foreach ($files as $f) {
            $list[] = [
                'name'       => basename($f),
                'size'       => $this->humanFilesize(filesize($f)),
                'created_at' => date('d.m.Y H:i:s', filemtime($f)),
            ];
        }

        // En yeni önce
        usort($list, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $list;
    }

    private function humanFilesize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function findMysqldump(): string|false
    {
        $candidates = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
        foreach ($candidates as $cmd) {
            $out = [];
            exec($cmd . ' --version 2>&1', $out, $rc);
            if ($rc === 0) {
                return $cmd;
            }
        }
        return false;
    }
}
