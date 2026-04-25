<?php
/**
 * MikroTik Kullanım Kaydı — Cron Scripti
 * ----------------------------------------
 * Cron job örneği (her 5 dakika):
 *   *\/5 * * * * php /path/to/BalmyHotels/cron_mikrotik_poll.php >> /var/log/mikrotik_poll.log 2>&1
 *
 * Windows Task Scheduler örneği (her 5 dakika):
 *   Program : php.exe
 *   Argüman : C:\path\to\BalmyHotels\cron_mikrotik_poll.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// -------------------------------------------------------------------------

use App\Models\MikroTikUsageLog;
use App\Services\MikroTikService;
use Carbon\Carbon;

$log = function (string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log('MikroTik poll başlıyor...');

$mikrotik = new MikroTikService();

if (!$mikrotik->connect()) {
    $log('HATA: MikroTik bağlantısı kurulamadı. Host/kullanıcı/şifre ayarlarını kontrol edin.');
    exit(1);
}

try {
    $active = $mikrotik->getHotspotActive();
    $now    = Carbon::now();
    $count  = 0;

    foreach ($active as $session) {
        $username = $session['user']        ?? null;
        $mac      = $session['mac-address'] ?? null;

        if (!$username || !$mac) {
            continue;
        }

        // RouterOS uptime string'ini saniyeye çevir (örn: "3d2h15m30s")
        $uptimeStr = $session['uptime'] ?? '0s';
        $seconds   = 0;
        if (preg_match('/(\d+)w/', $uptimeStr, $m)) $seconds += (int)$m[1] * 604800;
        if (preg_match('/(\d+)d/', $uptimeStr, $m)) $seconds += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $uptimeStr, $m)) $seconds += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $uptimeStr, $m)) $seconds += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $uptimeStr, $m)) $seconds += (int)$m[1];

        // Oturum başlangıcını dakikaya yuvarlıyoruz → aynı oturum her yoklamada aynı key'i verir
        $sessionStarted = $now->copy()->subSeconds($seconds)->startOfMinute();

        MikroTikUsageLog::updateOrCreate(
            [
                'username'           => $username,
                'mac_address'        => strtoupper($mac),
                'session_started_at' => $sessionStarted,
            ],
            [
                'ip_address'   => $session['address'] ?? null,
                'bytes_in'     => (int)($session['bytes-in']  ?? 0),
                'bytes_out'    => (int)($session['bytes-out'] ?? 0),
                'last_seen_at' => $now,
            ]
        );

        $count++;
    }

    $mikrotik->disconnect();
    $log("Tamam — {$count} aktif oturum kaydedildi.");
    exit(0);

} catch (Throwable $e) {
    $mikrotik->disconnect();
    $log('HATA: ' . $e->getMessage());
    exit(1);
}
