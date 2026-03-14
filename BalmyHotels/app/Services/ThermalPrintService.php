<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\RestaurantOrder;
use Illuminate\Support\Facades\Log;

/**
 * Sipariş kalemlerini ESC/POS protokolüyle TCP üzerinden
 * termal yazıcılara gönderir. Harici kütüphane gerektirmez.
 *
 * Yazıcı varsayılan portu: 9100 (Epson / Star / Generic standart)
 * Karakter seti       : Her yazıcının kendi codepage değeri (varsayılan 32 = PC1254 Turkish)
 */
class ThermalPrintService
{
    private const PORT    = 9100;
    private const TIMEOUT = 5;   // saniye

    // ESC/POS sabit baytlar
    private const ESC = "\x1B";
    private const GS  = "\x1D";
    private const LF  = "\x0A";

    // -------------------------------------------------------------------------
    // Ana metot: siparişi yazıcı başına gruplar, her birine gönderir
    // -------------------------------------------------------------------------

    /**
     * @return array{success: string[], failed: string[]}
     */
    public function printOrder(RestaurantOrder $order): array
    {
        $order->loadMissing([
            'items.menuItem.foodProduct.printer',
            'creator',
            'session.table.restaurant.branch',
        ]);

        $session    = $order->session;
        $table      = $session->table;
        $restaurant = $table->restaurant;
        $branch     = $restaurant->branch;

        // Kalemleri printer_id'ye göre grupla (0 = yazıcı tanımsız)
        $groups = $order->items->groupBy(function ($item) {
            return optional($item->menuItem?->foodProduct)->printer_id ?? 0;
        });

        $result = ['success' => [], 'failed' => []];

        foreach ($groups as $printerId => $items) {
            if ($printerId == 0) {
                // Yazıcısız kalemleri logla ama hata sayma
                Log::info("Sipariş #{$order->id}: {$items->count()} kalem yazıcısız — atlandı.");
                continue;
            }

            $printer = Printer::find($printerId);

            if (!$printer || !$printer->is_active || !$printer->ip_address) {
                $result['failed'][] = $printer?->name ?? "Yazıcı #$printerId";
                continue;
            }

            try {
                $otherItems = $order->items->filter(function ($item) use ($printerId) {
                    $pid = optional($item->menuItem?->foodProduct)->printer_id ?? 0;
                    return $pid !== $printerId;
                });

                $data = $this->buildReceipt($order, $items, $printer, $restaurant, $branch, $table, $otherItems);
                $this->sendTcp($printer->ip_address, self::PORT, $data);
                $result['success'][] = $printer->name;
            } catch (\Throwable $e) {
                Log::warning("Termal yazıcı hatası [{$printer->name} | {$printer->ip_address}]: {$e->getMessage()}");
                $result['failed'][] = $printer->name;
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // ESC/POS fiş oluştur
    // -------------------------------------------------------------------------

    private function buildReceipt(
        RestaurantOrder $order,
        $items,
        Printer $printer,
        $restaurant,
        $branch,
        $table,
        $otherItems = null
    ): string {
        $E  = self::ESC;
        $G  = self::GS;
        $LF = self::LF;

        // ESC/POS codepage (yazıcıya özgü, varsayılan 32 = PC1254)
        $codepage = $printer->codepage ?? 32;

        $buf = '';

        // ── Başlat + codepage ──────────────────────────────────────────────
        $buf .= $E . '@';                      // Initialize
        $buf .= $E . 't' . chr($codepage);     // Codepage komutu

        // ── Başlık ───────────────────────────────────────────────────────────
        $buf .= $E . 'a' . "\x01";  // Ortala

        if ($branch) {
            $buf .= $G . '!' . "\x00";           // Normal boyut
            $buf .= $E . 'E' . "\x01";           // Bold aç
            $buf .= $this->enc($branch->name, $codepage) . $LF;
            $buf .= $E . 'E' . "\x00";           // Bold kapat
        }

        $buf .= $G . '!' . "\x11";               // 2×2 boyut
        $buf .= $E . 'E' . "\x01";
        $buf .= $this->enc($restaurant->name, $codepage) . $LF;
        $buf .= $E . 'E' . "\x00";
        $buf .= $G . '!' . "\x00";               // Normal

        $buf .= $LF;

        // Yazıcı adı
        $buf .= $E . 'E' . "\x01";
        $buf .= $this->enc('[ ' . mb_strtoupper($printer->name) . ' ]', $codepage) . $LF;
        $buf .= $E . 'E' . "\x00";

        $buf .= $LF;

        // ── Masa / Garson / Tarih / Sipariş No ───────────────────────────────
        $buf .= $E . 'a' . "\x00";              // Sola yasla
        $buf .= str_repeat('-', 32) . $LF;
        $buf .= $this->row('MASA',    $this->enc($table->name, $codepage));
        $buf .= $this->row('GARSON',  $this->enc(optional($order->creator)->name ?? '-', $codepage));
        $buf .= $this->row('TARIH',   $order->created_at->format('d.m.Y H:i:s'));
        $buf .= $this->row('SIP.NO',  '#' . $order->id);
        $buf .= str_repeat('=', 32) . $LF;
        $buf .= $LF;

        // ── Ürünler ──────────────────────────────────────────────────────────
        foreach ($items as $item) {
            // Adet × Ürün adı — çift yükseklik + kalın
            $buf .= $G . '!' . "\x01";           // Çift yükseklik
            $buf .= $E . 'E' . "\x01";           // Bold
            $buf .= $this->enc($item->quantity . 'x  ' . $item->item_name, $codepage) . $LF;
            $buf .= $E . 'E' . "\x00";
            $buf .= $G . '!' . "\x00";           // Normal

            if (!empty($item->note)) {
                $buf .= $this->enc('    >> ' . $item->note, $codepage) . $LF;
            }
        }

        $buf .= $LF;

        // ── Sipariş notu ─────────────────────────────────────────────────────
        if (!empty($order->note)) {
            $buf .= str_repeat('-', 32) . $LF;
            $buf .= $E . 'E' . "\x01";
            $buf .= $this->enc('NOT: ', $codepage) . $E . 'E' . "\x00";
            $buf .= $this->enc($order->note, $codepage) . $LF;
            $buf .= $LF;
        }
        // ── Diğer Siparişler ──────────────────────────────────────────────
        if ($otherItems && $otherItems->isNotEmpty()) {
            $buf .= str_repeat('-', 32) . $LF;
            $buf .= $E . 'a' . "\x01";          // Ortala
            $buf .= $E . 'E' . "\x01";          // Bold
            $buf .= $this->enc('DIGER SIPARISLER') . $LF;
            $buf .= $E . 'E' . "\x00";          // Bold kapat
            $buf .= $E . 'a' . "\x00";          // Sola yasla
            $buf .= $LF;

            foreach ($otherItems as $item) {
                // Normal (ince) font — ESC/POS varsayılan boyut
                $buf .= $G . '!' . "\x00";      // Normal boyut
                $buf .= $E . 'E' . "\x00";      // Bold kapalı
                $buf .= $this->enc($item->quantity . 'x  ' . $item->item_name, $codepage) . $LF;

                if (!empty($item->note)) {
                    $buf .= $this->enc('    >> ' . $item->note, $codepage) . $LF;
                }
            }

            $buf .= $LF;
        }
        // ── Kes ──────────────────────────────────────────────────────────────
        $buf .= $E . 'd' . "\x04";               // 4 satır besle
        $buf .= $G . 'V' . "\x42" . "\x00";      // Kısmi kes (partial cut)

        return $buf;
    }

    // -------------------------------------------------------------------------
    // TCP soketi aç, baytları gönder, kapat
    // -------------------------------------------------------------------------

    private function sendTcp(string $ip, int $port, string $data): void
    {
        $socket = @fsockopen($ip, $port, $errno, $errstr, self::TIMEOUT);

        if ($socket === false) {
            throw new \RuntimeException(
                "$ip:$port bağlantısı kurulamadı — $errstr (errno: $errno)"
            );
        }

        stream_set_timeout($socket, self::TIMEOUT);

        $written = fwrite($socket, $data);
        fclose($socket);

        if ($written === false) {
            throw new \RuntimeException("$ip:$port yazıcıya veri gönderilemedi.");
        }
    }

    // -------------------------------------------------------------------------
    // Yardımcı metodlar
    // -------------------------------------------------------------------------

    /**
     * UTF-8 → yazıcının codepage'ine dönüştür.
     * Desteklenen Türkçe codepage'ler:
     *   12 (0x0C) = CP857   (DOS Turkish)
     *   32 (0x20) = CP1254  (Windows-1254 / Windows Turkish)
     * iconv başarısız olursa Türkçe→ASCII karşılığıyla devam et.
     */
    private function enc(string $text, int $codepage = 32): string
    {
        // ESC/POS codepage → iconv encoding tablosu (0–47)
        $iconvMap = [
            0  => 'CP437',
            1  => 'CP437',   // Katakana — ASCII kısmı aynı
            2  => 'CP850',
            3  => 'CP860',
            4  => 'CP863',
            5  => 'CP865',
            6  => 'CP851',
            7  => 'CP852',
            8  => 'CP858',
            9  => 'CP866',
            10 => 'CP437',   // CP928 — iconv desteği yok, fallback
            11 => 'CP437',   // CP770 — iconv desteği yok, fallback
            12 => 'CP857',
            13 => 'CP737',
            14 => 'ISO-8859-7',
            15 => 'CP1252',
            16 => 'CP866',
            17 => 'CP852',
            18 => 'CP858',
            19 => 'CP874',   // Thai
            20 => 'CP874',
            21 => 'CP874',
            22 => 'CP874',
            23 => 'CP874',
            24 => 'CP874',
            25 => 'CP874',
            26 => 'CP437',   // TCVN-3 — iconv desteği yok, fallback
            27 => 'CP720',
            28 => 'CP775',
            29 => 'CP855',
            30 => 'CP861',
            31 => 'CP862',
            32 => 'CP1254',
            33 => 'CP869',
            34 => 'ISO-8859-2',
            35 => 'ISO-8859-15',
            36 => 'CP1098',
            37 => 'CP437',   // PC1118 — iconv desteği yok, fallback
            38 => 'CP437',   // PC1119 — iconv desteği yok, fallback
            39 => 'CP1125',
            40 => 'CP1250',
            41 => 'CP1251',
            42 => 'CP1253',
            43 => 'CP1255',
            44 => 'CP1256',
            45 => 'CP1257',
            46 => 'CP1258',
            47 => 'CP437',   // KZ1048 — iconv desteği sınırlı, fallback
        ];
        $targetEncoding = $iconvMap[$codepage] ?? 'CP1254';

        $result = @iconv('UTF-8', $targetEncoding . '//TRANSLIT//IGNORE', $text);

        if ($result !== false) {
            return $result;
        }

        // Fallback: Türkçe karakterleri ASCII'ye çevir
        return str_replace(
            ['ğ','Ğ','ü','Ü','ş','Ş','ı','İ','ö','Ö','ç','Ç'],
            ['g','G','u','U','s','S','i','I','o','O','c','C'],
            $text
        );
    }

    /**
     * Sabit genişlikte (32 karakter) etiket : değer satırı
     */
    private function row(string $label, string $value, int $width = 32): string
    {
        $label = str_pad($label . ':', 9);
        $value = mb_substr($value, 0, $width - 10);
        return $label . $value . self::LF;
    }
}
