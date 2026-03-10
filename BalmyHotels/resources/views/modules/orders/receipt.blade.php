<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title }}</title>
    <style>
        :root {
            --ink: #0a0a0a;
            --ink-light: #444;
            --border: #222;
            --accent: #1a1a2e;
            --ticket-width: 340px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #e8e8e8;
            font-family: 'Courier New', Courier, monospace;
            min-height: 100vh;
            padding: 24px 16px 48px;
        }

        .screen-header {
            max-width: 960px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .screen-header h2 {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .screen-header .meta {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: .8rem;
            color: #64748b;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: .82rem;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary { background: #1e2d3d; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-success { background: #166534; color: #fff; }

        /* Fişler konteyneri */
        .tickets-container {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
            justify-content: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Tek fiş */
        .ticket {
            width: var(--ticket-width);
            background: #fff;
            position: relative;
            box-shadow: 0 2px 12px rgba(0,0,0,.18), 0 1px 4px rgba(0,0,0,.12);
            border-top: 5px solid var(--accent);
        }

        .ticket.no-printer {
            border-top-color: #94a3b8;
        }

        /* Perforasyon üstü */
        .ticket::before {
            content: '';
            display: block;
            height: 12px;
            background:
                radial-gradient(circle at 50% 0%, #e8e8e8 6px, transparent 6px),
                repeating-linear-gradient(90deg, transparent 0, transparent 18px, #e8e8e8 18px, #e8e8e8 18px);
            background-size: 20px 12px, 20px 12px;
        }

        /* Perforasyon altı */
        .ticket::after {
            content: '';
            display: block;
            height: 12px;
            background:
                radial-gradient(circle at 50% 100%, #e8e8e8 6px, transparent 6px),
                repeating-linear-gradient(90deg, transparent 0, transparent 18px, #e8e8e8 18px, #e8e8e8 18px);
            background-size: 20px 12px, 20px 12px;
        }

        .ticket-inner { padding: 4px 18px 10px; }

        /* Başlık */
        .ticket-header {
            text-align: center;
            padding: 14px 0 10px;
            border-bottom: 2px dashed #000;
            margin-bottom: 10px;
        }

        .ticket-header .hotel-name {
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink);
        }

        .ticket-header .restaurant-name {
            font-size: .78rem;
            color: var(--ink-light);
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .printer-badge {
            display: inline-block;
            background: var(--accent);
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 3px 10px;
            margin-top: 8px;
        }

        .ticket.no-printer .printer-badge {
            background: #64748b;
        }

        /* Bilgi satırları */
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: .72rem;
            line-height: 1.7;
            color: var(--ink);
        }

        .info-row .lbl { color: var(--ink-light); }
        .info-row .val { font-weight: 700; text-align: right; max-width: 60%; }

        .info-block {
            padding: 8px 0 6px;
            border-bottom: 1px dashed #999;
            margin-bottom: 8px;
        }

        /* Ürün tablosu */
        .items-header {
            display: grid;
            grid-template-columns: 1fr 2.5fr 1fr;
            font-size: .67rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-light);
            border-bottom: 2px solid var(--border);
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .items-header .col-qty { text-align: center; }
        .items-header .col-price { text-align: right; }

        .item-row {
            display: grid;
            grid-template-columns: 1fr 2.5fr 1fr;
            font-size: .78rem;
            line-height: 1.5;
            padding: 3px 0;
            border-bottom: 1px dotted #ddd;
        }

        .item-row.this-printer {
            font-size: .88rem;
            font-weight: 900;
            color: #000;
            background: #f8f8f8;
            padding: 5px 4px;
            border-bottom: 1px solid #ccc;
            border-radius: 2px;
            margin: 1px 0;
        }

        .item-row.other-printer {
            opacity: .42;
            font-size: .72rem;
        }

        .item-row .col-qty {
            text-align: center;
            font-weight: 700;
        }

        .item-row .col-name { padding: 0 4px; }
        .item-row .col-price { text-align: right; }

        .item-note {
            grid-column: 1 / -1;
            font-size: .68rem;
            color: #666;
            font-style: italic;
            padding: 0 4px 2px 24px;
        }

        /* Toplam */
        .total-block {
            margin-top: 8px;
            border-top: 2px solid var(--border);
            padding-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: .75rem;
            line-height: 1.8;
        }

        .total-row.grand {
            font-size: .95rem;
            font-weight: 900;
            border-top: 1px dashed #000;
            margin-top: 4px;
            padding-top: 4px;
        }

        /* Not */
        .order-note {
            margin-top: 10px;
            border: 1px dashed #999;
            padding: 6px 8px;
            font-size: .72rem;
            color: var(--ink);
        }

        .order-note .note-label {
            font-size: .65rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-light);
            margin-bottom: 2px;
        }

        /* Altbilgi */
        .ticket-footer {
            text-align: center;
            font-size: .65rem;
            color: var(--ink-light);
            padding: 10px 0 4px;
            border-top: 1px dashed #999;
            margin-top: 10px;
            letter-spacing: .05em;
        }

        /* Yazdır butonu fiş içi */
        .print-btn-wrap {
            text-align: center;
            padding: 10px 0 4px;
        }

        .print-single-btn {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: .75rem;
            font-weight: 700;
            padding: 6px 16px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            letter-spacing: .04em;
        }

        .ticket.no-printer .print-single-btn { background: #64748b; }

        /* ============================
           BASKI MODU
           ============================ */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .screen-header,
            .action-bar,
            .print-btn-wrap {
                display: none !important;
            }

            .tickets-container {
                gap: 0;
            }

            .ticket {
                width: 80mm;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
                border-top-width: 3px;
            }

            .ticket:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }

            .item-row.other-printer { display: none !important; }
        }
    </style>
</head>
<body>

@php
    $restaurant  = $session->table->restaurant;
    $branch      = $restaurant->branch ?? null;
    $now         = now();
@endphp

{{-- Ekran üstü navigasyon --}}
<div class="screen-header">
    <div>
        <h2>🧾 Sipariş Fişi #{{ $order->id }}</h2>
        <div class="meta">
            {{ $restaurant->name }} &mdash; {{ $session->table->name }}
            &bull; {{ $now->format('d.m.Y H:i') }}
        </div>
    </div>
    <div class="action-bar">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Tümünü Yazdır
        </button>
        <a href="{{ route('orders.session', $session) }}" class="btn btn-secondary">
            ← Masaya Dön
        </a>
        <a href="{{ route('orders.take', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-secondary">
            Masa Seçimi
        </a>
    </div>
</div>

{{-- Fişler --}}
<div class="tickets-container">
    @foreach($groups as $printerId => $items)
    @php
        $printer     = $printerId ? ($printerMap[$printerId] ?? null) : null;
        $printerName = $printer ? $printer->name : 'Yazıcısız';
        $printerIp   = $printer ? $printer->ip_address : null;
        $subtotal    = $items->sum(fn($i) => $i->unit_price * $i->quantity);
        $orderTotal  = $order->items->sum(fn($i) => $i->unit_price * $i->quantity);
    @endphp

    <div class="ticket {{ $printerId ? '' : 'no-printer' }}" id="ticket-{{ $printerId ?: 'none' }}">
        <div class="ticket-inner">

            {{-- Başlık --}}
            <div class="ticket-header">
                @if($branch)
                <div class="hotel-name">{{ strtoupper($branch->name) }}</div>
                @endif
                <div class="restaurant-name">{{ $restaurant->name }}</div>
                <div>
                    <span class="printer-badge">
                        🖨️ {{ strtoupper($printerName) }}
                        @if($printerIp) &nbsp;|&nbsp; {{ $printerIp }} @endif
                    </span>
                </div>
            </div>

            {{-- Masa ve sipariş bilgileri --}}
            <div class="info-block">
                <div class="info-row">
                    <span class="lbl">MASA</span>
                    <span class="val">{{ $session->table->name }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">SIPARIŞ NO</span>
                    <span class="val">#{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">ALINAN</span>
                    <span class="val">{{ optional($order->creator)->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">TARIH / SAAT</span>
                    <span class="val">{{ $order->created_at->format('d.m.Y') }} {{ $order->created_at->format('H:i:s') }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">MASA ACILISI</span>
                    <span class="val">{{ $session->opened_at ? \Carbon\Carbon::parse($session->opened_at)->format('H:i') : '—' }}</span>
                </div>
            </div>

            {{-- Ürün listesi --}}
            <div class="items-header">
                <span class="col-qty">AD</span>
                <span class="col-name">ÜRÜN</span>
                <span class="col-price">TUTAR</span>
            </div>

            @foreach($order->items->sortBy(fn($i) => optional($i->menuItem?->foodProduct)->printer_id !== $printerId) as $item)
            @php
                $itemPrinterId = optional($item->menuItem?->foodProduct)->printer_id;
                $isThisPrinter = ($itemPrinterId == $printerId) || ($printerId == 0 && !$itemPrinterId);
            @endphp
            <div class="item-row {{ $isThisPrinter ? 'this-printer' : 'other-printer' }}">
                <div class="col-qty">{{ $item->quantity }}x</div>
                <div class="col-name">{{ $item->item_name }}</div>
                <div class="col-price">{{ number_format($item->unit_price * $item->quantity, 2) }}</div>
                @if($item->note)
                <div class="item-note">↳ {{ $item->note }}</div>
                @endif
            </div>
            @endforeach

            {{-- Toplam --}}
            <div class="total-block">
                <div class="total-row">
                    <span>Bu yazıcı alt toplam</span>
                    <span>{{ number_format($subtotal, 2) }} ₺</span>
                </div>
                <div class="total-row grand">
                    <span>TOPLAM SİPARİŞ</span>
                    <span>{{ number_format($orderTotal, 2) }} ₺</span>
                </div>
            </div>

            {{-- Sipariş notu --}}
            @if($order->note)
            <div class="order-note">
                <div class="note-label">Not:</div>
                {{ $order->note }}
            </div>
            @endif

            {{-- Altbilgi --}}
            <div class="ticket-footer">
                SIPARIS #{{ $order->id }} &bull; {{ $now->format('d.m.Y H:i') }}<br>
                {{ optional($order->creator)->name ?? '' }}
            </div>

        </div>{{-- /ticket-inner --}}

        {{-- Yazdır butonu (ekranda) --}}
        <div class="print-btn-wrap">
            <button class="print-single-btn" onclick="printTicket('ticket-{{ $printerId ?: 'none' }}')">
                🖨️ Sadece Bu Fişi Yazdır
            </button>
        </div>

    </div>{{-- /ticket --}}
    @endforeach
</div>

<script>
function printTicket(ticketId) {
    const ticket = document.getElementById(ticketId);
    if (!ticket) return;

    const allTickets = document.querySelectorAll('.ticket');
    allTickets.forEach(t => {
        if (t.id !== ticketId) {
            t.dataset.hidden = 'true';
            t.style.display = 'none';
        }
    });

    const header = document.querySelector('.screen-header');
    if (header) header.style.display = 'none';

    window.print();

    allTickets.forEach(t => {
        if (t.dataset.hidden === 'true') {
            t.style.display = '';
            delete t.dataset.hidden;
        }
    });

    if (header) header.style.display = '';
}
</script>

</body>
</html>
