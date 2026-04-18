<?php

namespace App\Console\Commands;

use App\Models\MikroTikUsageLog;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollMikroTikUsage extends Command
{
    protected $signature   = 'mikrotik:poll-usage';
    protected $description = 'Aktif hotspot oturumlarını çekip mikrotik_usage_logs tablosuna kaydeder.';

    public function handle(): int
    {
        $mikrotik = new MikroTikService();

        if (!$mikrotik->connect()) {
            Log::warning('mikrotik:poll-usage — MikroTik bağlantısı kurulamadı.');
            $this->error('MikroTik bağlantısı kurulamadı.');
            return self::FAILURE;
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

                $uptimeSeconds  = $this->parseUptime($session['uptime'] ?? '0s');
                // Oturum başlangıcını dakikaya yuvarlıyoruz → aynı oturum her yoklamada aynı key'i verir
                $sessionStarted = $now->copy()->subSeconds($uptimeSeconds)->startOfMinute();

                MikroTikUsageLog::updateOrCreate(
                    [
                        'username'          => $username,
                        'mac_address'       => strtoupper($mac),
                        'session_started_at' => $sessionStarted,
                    ],
                    [
                        'ip_address'  => $session['address'] ?? null,
                        'bytes_in'    => (int)($session['bytes-in']  ?? 0),
                        'bytes_out'   => (int)($session['bytes-out'] ?? 0),
                        'last_seen_at' => $now,
                    ]
                );

                $count++;
            }

            $mikrotik->disconnect();
            $this->info("Tamam — {$count} aktif oturum kaydedildi.");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $mikrotik->disconnect();
            Log::error('mikrotik:poll-usage hatası: ' . $e->getMessage());
            $this->error('Hata: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * RouterOS uptime string'ini saniyeye çevirir.
     * Örnek: "3d2h15m30s", "2h15m30s", "15m30s", "30s"
     */
    private function parseUptime(string $uptime): int
    {
        $seconds = 0;
        if (preg_match('/(\d+)w/', $uptime, $m)) $seconds += (int)$m[1] * 604800;
        if (preg_match('/(\d+)d/', $uptime, $m)) $seconds += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $uptime, $m)) $seconds += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $uptime, $m)) $seconds += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $uptime, $m)) $seconds += (int)$m[1];
        return $seconds;
    }
}
