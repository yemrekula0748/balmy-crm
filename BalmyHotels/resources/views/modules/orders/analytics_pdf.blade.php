<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Gün Bazlı Sipariş Raporu</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 10pt;
        color: #2c2c2c;
        background: #fff;
    }

    /* ─── HEADER ─── */
    .header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #c19b77 100%);
        color: #fff;
        padding: 26px 30px 20px;
    }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .header-brand { font-size: 8pt; letter-spacing: 2px; text-transform: uppercase; opacity: 0.65; margin-bottom: 5px; }
    .header-title { font-size: 18pt; font-weight: bold; letter-spacing: -0.5px; }
    .header-subtitle { font-size: 9pt; opacity: 0.75; margin-top: 4px; }
    .header-meta { text-align: right; font-size: 9pt; opacity: 0.8; }
    .header-meta-year { font-size: 22pt; font-weight: bold; opacity: 0.9; }

    /* ─── GOLD BAR ─── */
    .gold-bar { height: 4px; background: #c19b77; margin-bottom: 20px; }

    /* ─── TARIH BÖLÜMÜ ─── */
    .date-band {
        background: #f8f5f2;
        border-left: 4px solid #c19b77;
        padding: 9px 16px;
        margin-bottom: 20px;
        font-size: 9pt;
        display: flex;
        gap: 24px;
    }
    .date-band strong { color: #666; }

    /* ─── ÖZET KARTLAR ─── */
    .summary-grid { display: flex; gap: 10px; margin-bottom: 24px; }
    .summary-card {
        flex: 1;
        border: 1px solid #e0d5cc;
        border-radius: 6px;
        padding: 12px 14px;
        text-align: center;
    }
    .summary-card .s-label { font-size: 7.5pt; color: #999; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }
    .summary-card .s-value { font-size: 19pt; font-weight: bold; }
    .c-gold    .s-value { color: #c19b77; }
    .c-blue    .s-value { color: #4e8fcb; }
    .c-green   .s-value { color: #5c9e6e; }
    .c-orange  .s-value { color: #e68a3c; }

    /* ─── BÖLÜM BAŞLIĞI ─── */
    .section-title {
        font-size: 10.5pt;
        font-weight: bold;
        color: #1a1a2e;
        border-bottom: 2px solid #c19b77;
        padding-bottom: 5px;
        margin-bottom: 12px;
        margin-top: 22px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* ─── GENEL TABLOLAR ─── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 9pt; }
    thead th {
        background: #1a1a2e;
        color: #fff;
        padding: 7px 9px;
        text-align: left;
        font-size: 8.5pt;
        font-weight: bold;
        letter-spacing: 0.3px;
    }
    thead th.r { text-align: right; }
    thead th.c { text-align: center; }
    tbody tr:nth-child(even) { background: #faf8f6; }
    tbody tr:nth-child(odd)  { background: #fff; }
    tbody td { padding: 6px 9px; border-bottom: 1px solid #ece7e2; vertical-align: middle; }
    tbody td.r { text-align: right; }
    tbody td.c { text-align: center; }
    tfoot td { padding: 7px 9px; font-weight: bold; border-top: 2px solid #c19b77; font-size: 9pt; background: #fdf5ee; }
    tfoot td.r { text-align: right; }

    /* ─── RESTORAN BAŞLIĞI ─── */
    .resto-header {
        background: #16213e;
        color: #fff;
        padding: 9px 14px;
        border-radius: 4px 4px 0 0;
        font-size: 10pt;
        font-weight: bold;
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .resto-header .resto-badge {
        background: rgba(193,155,119,0.3);
        color: #f0d9be;
        font-size: 8pt;
        padding: 3px 10px;
        border-radius: 10px;
    }

    /* ─── GÜN BAŞLIĞI ─── */
    .day-header {
        background: #e8e4df;
        padding: 5px 12px;
        font-size: 8.5pt;
        font-weight: bold;
        color: #555;
        letter-spacing: 0.3px;
        border-left: 3px solid #c19b77;
    }

    /* ─── GÜN TABLOSU ─── */
    .day-table thead th {
        background: #3a3a4e;
        font-size: 8pt;
        padding: 5px 9px;
    }
    .day-table tbody tr:nth-child(even) { background: #f5f3f0; }

    /* ─── GÜN TOPLAMI ─── */
    .day-total-row td { background: #fdf5ee; font-weight: bold; border-top: 1px solid #c19b77; font-size: 8.5pt; padding: 5px 9px; }
    .day-total-row td.r { text-align: right; }

    /* ─── ÜCRETSIZ BADGEı ─── */
    .badge-free { background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
    .badge-paid { background: #fdf5ee; color: #b5935a; padding: 2px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }

    /* ─── FOOTER ─── */
    .footer {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #1a1a2e;
        color: rgba(255,255,255,0.55);
        font-size: 7.5pt;
        padding: 7px 30px;
        display: flex;
        justify-content: space-between;
    }

    /* ─── SAYFA SONU ─── */
    .page-break { page-break-before: always; }

    /* ─── PROGRESS ─── */
    .prog-wrap { background: #ede8e3; border-radius: 3px; height: 7px; width: 100%; }
    .prog-fill { background: #c19b77; border-radius: 3px; height: 7px; }

    /* ─── BODY PADDING (footer için) ─── */
    body { padding-bottom: 32px; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════ -->
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-brand">Balmy Hotels &bull; Restoran Sipariş Sistemi</div>
            <div class="header-title">Gün Bazlı Sipariş Raporu</div>
            <div class="header-subtitle">Restoran &bull; Ürün &bull; Miktar Dökümü</div>
        </div>
        <div class="header-meta">
            <div class="header-meta-year">{{ now()->format('Y') }}</div>
            <div>Oluşturuldu: {{ $generatedAt }}</div>
        </div>
    </div>
</div>
<div class="gold-bar"></div>

<!-- TARİH BANDI -->
<div class="date-band">
    <span><strong>Başlangıç:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('d.m.Y') }}</span>
    <span><strong>Bitiş:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('d.m.Y') }}</span>
    @if($filters['restaurant'])
    <span><strong>Restoran:</strong> {{ $filters['restaurant'] }}</span>
    @endif
    @php
        $dayCount = \Carbon\Carbon::parse($filters['date_from'])->diffInDays(\Carbon\Carbon::parse($filters['date_to'])) + 1;
    @endphp
    <span><strong>Süre:</strong> {{ $dayCount }} gün</span>
</div>

<!-- ═══════════════════════════════════════════════════════════
     ÖZET KARTLAR
═══════════════════════════════════════════════════════════ -->
<div class="summary-grid">
    <div class="summary-card c-gold">
        <div class="s-label">Toplam Hasılat</div>
        <div class="s-value">₺{{ number_format($summary['paid_revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="summary-card c-blue">
        <div class="s-label">Toplam Sipariş</div>
        <div class="s-value">{{ number_format($summary['total_orders']) }}</div>
    </div>
    <div class="summary-card c-green">
        <div class="s-label">Toplam Seans</div>
        <div class="s-value">{{ number_format($summary['total_sessions']) }}</div>
    </div>
    <div class="summary-card c-orange">
        <div class="s-label">Toplam Kalem</div>
        <div class="s-value">{{ number_format($summary['total_qty']) }}</div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     RESTORAN ÖZET TABLOSU
═══════════════════════════════════════════════════════════ -->
<div class="section-title">Restoran Bazında Özet</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Restoran</th>
            <th class="c">Seans</th>
            <th class="r">Toplam Kalem</th>
            <th class="r">Hasılat</th>
            <th class="r">Pay</th>
        </tr>
    </thead>
    <tbody>
        @php $grandRevenue = $restaurantTotals->sum('total_revenue'); $i = 1; @endphp
        @forelse($restaurantTotals as $rt)
        @php
            $pct = $grandRevenue > 0 ? round(($rt->total_revenue / $grandRevenue) * 100, 1) : 0;
        @endphp
        <tr>
            <td class="c" style="color:#999;font-size:8pt;">{{ $i++ }}</td>
            <td><strong>{{ $rt->restaurant_name }}</strong></td>
            <td class="c">{{ number_format($rt->sessions) }}</td>
            <td class="r">{{ number_format($rt->total_qty) }}</td>
            <td class="r"><strong style="color:#c19b77;">₺{{ number_format($rt->total_revenue, 2, ',', '.') }}</strong></td>
            <td class="r" style="width:100px;">
                <div style="display:flex;align-items:center;gap:5px;justify-content:flex-end;">
                    <span style="font-size:8pt;color:#888;">{{ $pct }}%</span>
                    <div class="prog-wrap" style="width:60px;">
                        <div class="prog-fill" style="width:{{ $pct }}%;"></div>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#aaa;padding:12px;">Bu dönemde sipariş kaydı bulunamadı.</td></tr>
        @endforelse
    </tbody>
    @if($restaurantTotals->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="3"></td>
            <td class="r">{{ number_format($restaurantTotals->sum('total_qty')) }} kalem</td>
            <td class="r">₺{{ number_format($restaurantTotals->sum('total_revenue'), 2, ',', '.') }}</td>
            <td class="r">100%</td>
        </tr>
    </tfoot>
    @endif
</table>

<!-- ═══════════════════════════════════════════════════════════
     DETAY: RESTORAN × GÜN × ÜRÜN
═══════════════════════════════════════════════════════════ -->
<div class="section-title">Gün &amp; Ürün Bazlı Detay Döküm</div>

@forelse($byRestaurant as $restaurantId => $dateGroups)
@php
    $restoName    = $dateGroups->first()->first()->restaurant_name ?? '—';
    $restoTotal   = $restaurantTotals->get($restaurantId);
    $restoRevenue = $restoTotal?->total_revenue ?? 0;
    $restoQty     = $restoTotal?->total_qty ?? 0;
@endphp

<!-- RESTORAN BAŞLIĞI -->
<div class="resto-header">
    <span>{{ $restoName }}</span>
    <span class="resto-badge">{{ number_format($restoQty) }} kalem &bull; ₺{{ number_format($restoRevenue, 2, ',', '.') }}</span>
</div>

@foreach($dateGroups as $date => $rows)
@php
    $dayQty     = $rows->sum('qty');
    $dayRevenue = $rows->sum('line_total');
    $dateFormatted = \Carbon\Carbon::parse($date)->locale('tr')->isoFormat('D MMMM YYYY, dddd');
@endphp

<!-- GÜN BAŞLIĞI -->
<div class="day-header">{{ $dateFormatted }} &nbsp;&mdash;&nbsp; {{ number_format($dayQty) }} kalem &bull; ₺{{ number_format($dayRevenue, 2, ',', '.') }}</div>

<table class="day-table" style="margin-bottom:0;">
    <thead>
        <tr>
            <th style="width:40%;">Ürün Adı</th>
            <th class="c" style="width:10%;">Adet</th>
            <th class="r" style="width:15%;">Birim Fiyat</th>
            <th class="r" style="width:15%;">Toplam</th>
            <th class="c" style="width:10%;">Tür</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row->item_name }}</td>
            <td class="c"><strong>{{ number_format($row->qty) }}</strong></td>
            <td class="r">
                @if($row->avg_price > 0)
                    ₺{{ number_format($row->avg_price, 2, ',', '.') }}
                @else
                    <span style="color:#aaa;">—</span>
                @endif
            </td>
            <td class="r">
                @if($row->line_total > 0)
                    <strong style="color:#c19b77;">₺{{ number_format($row->line_total, 2, ',', '.') }}</strong>
                @else
                    <span style="color:#aaa;">—</span>
                @endif
            </td>
            <td class="c">
                @if($row->avg_price > 0)
                    <span class="badge-paid">Ücretli</span>
                @else
                    <span class="badge-free">Ücretsiz</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="day-total-row">
            <td><strong>Gün Toplamı</strong></td>
            <td class="r"><strong>{{ number_format($dayQty) }} adet</strong></td>
            <td></td>
            <td class="r"><strong style="color:#c19b77;">₺{{ number_format($dayRevenue, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div style="margin-bottom:8px;"></div>
@endforeach

<!-- RESTORAN GENELİ TOPLAMI -->
@if($byRestaurant->count() > 1)
<table style="margin-bottom:4px;">
    <tfoot>
        <tr>
            <td colspan="3"><strong>{{ $restoName }} — Dönem Toplamı</strong></td>
            <td class="r"><strong>{{ number_format($restoQty) }} kalem</strong></td>
            <td class="r"><strong style="color:#c19b77;">₺{{ number_format($restoRevenue, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endif

@if(!$loop->last)
<div class="page-break"></div>
@endif

@empty
<p style="text-align:center;color:#aaa;padding:20px 0;font-size:10pt;">Bu dönemde sipariş kaydı bulunamadı.</p>
@endforelse

<!-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ -->
<div class="footer">
    <span>Balmy Hotels &bull; Restoran Yönetim Sistemi</span>
    <span>{{ $filters['date_from'] }} – {{ $filters['date_to'] }} &bull; Oluşturuldu: {{ $generatedAt }}</span>
</div>

</body>
</html>
