<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * MikroTik RouterOS API istemcisi (pure PHP, ekstra bağımlılık yok).
 * RouterOS API protokolü: TCP 8728 üzerinden word-length-encoded paketler.
 */
class MikroTikService
{
    protected $socket = null;
    protected string $host;
    protected int    $port;
    protected string $user;
    protected string $password;
    protected int    $timeout;

    public function __construct()
    {
        $this->host     = config('mikrotik.host');
        $this->port     = config('mikrotik.port');
        $this->user     = config('mikrotik.user');
        $this->password = config('mikrotik.password');
        $this->timeout  = config('mikrotik.timeout', 5);
    }

    /* ------------------------------------------------------------------ */
    /*  Bağlantı                                                            */
    /* ------------------------------------------------------------------ */

    public function connect(): bool
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            Log::warning("MikroTik bağlantı hatası: {$errstr} ({$errno})");
            return false;
        }
        stream_set_timeout($this->socket, $this->timeout);
        return $this->login();
    }

    private function login(): bool
    {
        // challenge-response (MD5) login
        $this->writeSentence(['/login', "=name={$this->user}", "=password={$this->password}"]);
        $response = $this->readSentence();

        // RouterOS 6.43+ supports plain password login directly
        if (isset($response[0]) && $response[0] === '!done') {
            return true;
        }

        // Older: challenge-response MD5
        $challenge = null;
        foreach ($response as $word) {
            if (str_starts_with($word, '=ret=')) {
                $challenge = substr($word, 5);
                break;
            }
        }

        if ($challenge !== null) {
            $md5 = md5(chr(0) . $this->password . pack('H*', $challenge));
            $this->writeSentence(['/login', "=name={$this->user}", "=response=00{$md5}"]);
            $response = $this->readSentence();
            return isset($response[0]) && $response[0] === '!done';
        }

        return false;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Veri Çekme                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Komut gönder, sonuçları dizi olarak döndür.
     * Her eleman bir row (key=value array).
     */
    public function query(string $command, array $params = []): array
    {
        $sentence = [$command, ...$params];
        $this->writeSentence($sentence);

        $results = [];
        $current = [];

        while (true) {
            $sentence = $this->readSentence();
            if (empty($sentence)) break;

            $type = $sentence[0] ?? '';

            if ($type === '!re') {
                $row = [];
                foreach (array_slice($sentence, 1) as $word) {
                    if (str_starts_with($word, '=')) {
                        [$k, $v] = array_pad(explode('=', substr($word, 1), 2), 2, '');
                        $row[$k] = $v;
                    }
                }
                $results[] = $row;
            } elseif ($type === '!done' || $type === '!trap' || $type === '!fatal') {
                break;
            }
        }

        return $results;
    }

    /* ------------------------------------------------------------------ */
    /*  Dashboard Verileri                                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Sistem kaynakları: CPU, RAM, uptime, versiyon vb.
     */
    public function getSystemResources(): array
    {
        $rows = $this->query('/system/resource/print');
        return $rows[0] ?? [];
    }

    /**
     * Sistem kimliği (hostname).
     */
    public function getSystemIdentity(): array
    {
        $rows = $this->query('/system/identity/print');
        return $rows[0] ?? [];
    }

    /**
     * Arayüz listesi ve trafik bilgileri.
     */
    public function getInterfaces(): array
    {
        return $this->query('/interface/print');
    }

    /**
     * Hotspot aktif oturumlar.
     */
    public function getHotspotActive(): array
    {
        return $this->query('/ip/hotspot/active/print');
    }

    /**
     * Hotspot kullanıcı listesi.
     */
    public function getHotspotUsers(): array
    {
        return $this->query('/ip/hotspot/user/print');
    }

    /**
     * Hotspot profilleri.
     */
    public function getHotspotProfiles(): array
    {
        return $this->query('/ip/hotspot/user/profile/print');
    }

    /**
     * DHCP leases (IP–MAC–hostname eşleşmeleri).
     */
    public function getDhcpLeases(): array
    {
        return $this->query('/ip/dhcp-server/lease/print');
    }

    /**
     * Wireless clients (WiFi bağlı cihazlar).
     */
    public function getWirelessClients(): array
    {
        return $this->query('/interface/wireless/registration-table/print');
    }

    /**
     * Sistem logları (son 50 kayıt).
     */
    public function getLogs(): array
    {
        return $this->query('/log/print', ['=count=50']);
    }

    /**
     * Arayüz trafik monitörü (anlık RX/TX).
     */
    public function getInterfaceTraffic(string $interfaceName): array
    {
        $rows = $this->query('/interface/monitor-traffic', [
            "=interface={$interfaceName}",
            '=once=',
        ]);
        return $rows[0] ?? [];
    }

    /**
     * Tüm dashboard verilerini tek seferde toplar.
     * Bağlantı hatasında ['error' => '...'] döner.
     */
    public function getDashboardData(): array
    {
        try {
            if (!$this->connect()) {
                return ['error' => 'MikroTik\'e bağlanılamadı. Host/kullanıcı/şifre ayarlarını kontrol edin.'];
            }

            $data = [
                'identity'       => $this->getSystemIdentity(),
                'resources'      => $this->getSystemResources(),
                'interfaces'     => $this->getInterfaces(),
                'hotspot_active' => $this->getHotspotActive(),
                'hotspot_users'  => $this->getHotspotUsers(),
                'dhcp_leases'    => $this->getDhcpLeases(),
                'logs'           => $this->getLogs(),
            ];

            $this->disconnect();
            return $data;

        } catch (\Throwable $e) {
            $this->disconnect();
            Log::error('MikroTikService hata: ' . $e->getMessage());
            return ['error' => 'MikroTik sorgu hatası: ' . $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  RouterOS API Protokol Yardımcıları                                  */
    /* ------------------------------------------------------------------ */

    private function writeSentence(array $words): void
    {
        foreach ($words as $word) {
            $this->writeWord($word);
        }
        $this->writeWord(''); // end of sentence
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        $this->writeLength($len);
        if ($len > 0) {
            fwrite($this->socket, $word, $len);
        }
    }

    private function writeLength(int $len): void
    {
        if ($len < 0x80) {
            fwrite($this->socket, chr($len), 1);
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF) . chr($len & 0xFF), 2);
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket, chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF), 3);
        } elseif ($len < 0x10000000) {
            $len |= 0xE0000000;
            fwrite($this->socket, chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF), 4);
        } else {
            fwrite($this->socket, chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF), 5);
        }
    }

    private function readSentence(): array
    {
        $sentence = [];
        while (true) {
            $word = $this->readWord();
            if ($word === '') break;
            $sentence[] = $word;
        }
        return $sentence;
    }

    private function readWord(): string
    {
        $len = $this->readLength();
        if ($len === 0) return '';
        $data = '';
        $remaining = $len;
        while ($remaining > 0) {
            $chunk = fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') break;
            $data .= $chunk;
            $remaining -= strlen($chunk);
        }
        return $data;
    }

    private function readLength(): int
    {
        $byte = ord(fread($this->socket, 1));
        if (($byte & 0x80) === 0x00) {
            return $byte;
        } elseif (($byte & 0xC0) === 0x80) {
            $b2 = ord(fread($this->socket, 1));
            return (($byte & ~0x80) << 8) | $b2;
        } elseif (($byte & 0xE0) === 0xC0) {
            $b2 = ord(fread($this->socket, 1));
            $b3 = ord(fread($this->socket, 1));
            return (($byte & ~0xC0) << 16) | ($b2 << 8) | $b3;
        } elseif (($byte & 0xF0) === 0xE0) {
            $b2 = ord(fread($this->socket, 1));
            $b3 = ord(fread($this->socket, 1));
            $b4 = ord(fread($this->socket, 1));
            return (($byte & ~0xE0) << 24) | ($b2 << 16) | ($b3 << 8) | $b4;
        } else {
            $b2 = ord(fread($this->socket, 1));
            $b3 = ord(fread($this->socket, 1));
            $b4 = ord(fread($this->socket, 1));
            $b5 = ord(fread($this->socket, 1));
            return ($b2 << 24) | ($b3 << 16) | ($b4 << 8) | $b5;
        }
    }
}
