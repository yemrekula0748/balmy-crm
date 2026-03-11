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
 * Karakter seti       : CP1254 — ESC/POS codepage 32 (PC1254 Turkish)
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
                $data = $this->buildReceipt($order, $items, $printer, $restaurant, $branch, $table);
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
        $table
    ): string {
        $E  = self::ESC;
        $G  = self::GS;
        $LF = self::LF;

        $buf = '';

        // ── Başlat + Türkçe codepage (PC1254 = 0x20) ─────────────────────────
        $buf .= $E . '@';            // Initialize
        $buf .= $E . 't' . "\x20";  // Codepage 32 = PC1254 (Turkish Windows)

        // ── Başlık ───────────────────────────────────────────────────────────
        $buf .= $E . 'a' . "\x01";  // Ortala

        if ($branch) {
            $buf .= $G . '!' . "\x00";           // Normal boyut
            $buf .= $E . 'E' . "\x01";           // Bold aç
            $buf .= $this->enc($branch->name) . $LF;
            $buf .= $E . 'E' . "\x00";           // Bold kapat
        }

        $buf .= $G . '!' . "\x11";               // 2×2 boyut
        $buf .= $E . 'E' . "\x01";
        $buf .= $this->enc($restaurant->name) . $LF;
        $buf .= $E . 'E' . "\x00";
        $buf .= $G . '!' . "\x00";               // Normal

        $buf .= $LF;

        // Yazıcı adı
        $buf .= $E . 'E' . "\x01";
        $buf .= $this->enc('[ ' . mb_strtoupper($printer->name) . ' ]') . $LF;
        $buf .= $E . 'E' . "\x00";

        $buf .= $LF;

        // ── Masa / Garson / Tarih / Sipariş No ───────────────────────────────
        $buf .= $E . 'a' . "\x00";              // Sola yasla
        $buf .= str_repeat('-', 32) . $LF;
        $buf .= $this->row('MASA',    $this->enc($table->name));
        $buf .= $this->row('GARSON',  $this->enc(optional($order->creator)->name ?? '-'));
        $buf .= $this->row('TARIH',   $order->created_at->format('d.m.Y H:i:s'));
        $buf .= $this->row('SIP.NO',  '#' . $order->id);
        $buf .= str_repeat('=', 32) . $LF;
        $buf .= $LF;

        // ── Ürünler ──────────────────────────────────────────────────────────
        foreach ($items as $item) {
            // Adet × Ürün adı — çift yükseklik + kalın
            $buf .= $G . '!' . "\x01";           // Çift yükseklik
            $buf .= $E . 'E' . "\x01";           // Bold
            $buf .= $this->enc($item->quantity . 'x  ' . $item->item_name) . $LF;
            $buf .= $E . 'E' . "\x00";
            $buf .= $G . '!' . "\x00";           // Normal

            if (!empty($item->note)) {
                $buf .= $this->enc('    >> ' . $item->note) . $LF;
            }
        }

        $buf .= $LF;

        // ── Sipariş notu ─────────────────────────────────────────────────────
        if (!empty($order->note)) {
            $buf .= str_repeat('-', 32) . $LF;
            $buf .= $E . 'E' . "\x01";
            $buf .= $this->enc('NOT: ') . $E . 'E' . "\x00";
            $buf .= $this->enc($order->note) . $LF;
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
     * UTF-8 → CP1254 (ESC/POS Türkçe codepage 32 = PC1254 Windows Turkish)
     * iconv başarısız olursa Türkçe→ASCII karşılığıyla devam et.
     */
    private function enc(string $text): string
    {
        $result = @iconv('UTF-8', 'CP1254//TRANSLIT//IGNORE', $text);

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
