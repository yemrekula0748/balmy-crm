<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Gün Bazlı Sipariş Raporu</title>
<style>
/* ═══════════════════════════════════════════════════════
   RESET & BASE  (DomPDF uyumlu — gradient/flex yok)
═══════════════════════════════════════════════════════ */
* { margin: 0; padding: 0; }
body {
    font-family: "DejaVu Sans", sans-serif;
    font-size: 9.5pt;
    color: #2e2318;
    background: #fdfaf6;
    padding-bottom: 36px;
}

/* ═══════════════════════════════════════════════════════
   HEADER
═══════════════════════════════════════════════════════ */
.header-accent-bar {
    background: #C19B77;
    height: 10px;
}
.header-card {
    background: #ffffff;
    border-left: 6px solid #C19B77;
    border-bottom: 2px solid #e8dfd0;
    padding: 18px 28px 14px 22px;
}
.header-brand {
    font-size: 7.5pt;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #b3a08a;
    margin-bottom: 5px;
}
.header-title {
    font-family: "DejaVu Serif", serif;
    font-size: 20pt;
    font-weight: bold;
    color: #2e2318;
    margin-bottom: 3px;
}
.header-subtitle {
    font-size: 9pt;
    color: #7a6552;
    letter-spacing: 1px;
}
.header-meta-year {
    font-family: "DejaVu Serif", serif;
    font-size: 28pt;
    font-weight: bold;
    color: #C19B77;
    text-align: right;
}
.header-meta-date {
    font-size: 8pt;
    color: #b3a08a;
    text-align: right;
    margin-top: 2px;
    line-height: 1.5;
}
.header-rule {
    border: none;
    border-top: 1px solid #d9ccba;
    margin-top: 12px;
    margin-bottom: 10px;
}

/* ═══════════════════════════════════════════════════════
   BÖLÜM BAŞLIĞI
═══════════════════════════════════════════════════════ */
.section-title {
    font-size: 7.5pt;
    font-weight: bold;
    color: #b3a08a;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    border-bottom: 1px solid #d9ccba;
    padding-bottom: 5px;
    margin-bottom: 12px;
    margin-top: 20px;
}

/* ═══════════════════════════════════════════════════════
   ÖZET KARTLAR (tablo ile 4 kolon)
═══════════════════════════════════════════════════════ */
table.summary-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 6px 0;
    margin-bottom: 20px;
}
table.summary-table td.s-card {
    background: #ffffff;
    border: 1px solid #e8dfd0;
    border-top: 3px solid #C19B77;
    padding: 12px 8px;
    text-align: center;
    width: 25%;
}
.s-label {
    font-size: 7pt;
    color: #b3a08a;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 6px;
}
.s-value {
    font-family: "DejaVu Serif", serif;
    font-size: 20pt;
    font-weight: bold;
    color: #C19B77;
}
.v-blue  { color: #4e7dab !important; }
.v-green { color: #5a8c6a !important; }
.v-brown { color: #7a6552 !important; }

/* ═══════════════════════════════════════════════════════
   GENEL VERİ TABLOSU
═══════════════════════════════════════════════════════ */
table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
    font-size: 9pt;
}
table.data-table thead th {
    background: #2e2318;
    color: #f5f0e8;
    padding: 7px 10px;
    text-align: left;
    font-size: 8pt;
    font-weight: bold;
    letter-spacing: 0.4px;
}
table.data-table thead th.r { text-align: right; }
table.data-table thead th.c { text-align: center; }
table.data-table tbody tr.even td { background: #fdfaf6; }
table.data-table tbody tr.odd  td { background: #ffffff; }
table.data-table tbody td {
    padding: 6px 10px;
    border-bottom: 1px solid #e8dfd0;
    vertical-align: middle;
}
table.data-table tbody td.r { text-align: right; }
table.data-table tbody td.c { text-align: center; }
table.data-table tfoot td {
    padding: 7px 10px;
    font-weight: bold;
    border-top: 2px solid #C19B77;
    background: #f5f0e8;
    font-size: 9pt;
    border-bottom: 1px solid #d9ccba;
}
table.data-table tfoot td.r { text-align: right; }

/* ═══════════════════════════════════════════════════════
   RESTORAN BAŞLIĞI
═══════════════════════════════════════════════════════ */
.resto-header {
    background: #2e2318;
    color: #f5f0e8;
    padding: 9px 14px;
    font-size: 10pt;
    font-weight: bold;
    font-family: "DejaVu Serif", serif;
    margin-top: 22px;
    border-top: 3px solid #C19B77;
}
.resto-meta {
    font-size: 8pt;
    color: #b3a08a;
    font-weight: normal;
    font-family: "DejaVu Sans", sans-serif;
}
.resto-revenue {
    font-size: 9pt;
    font-family: "DejaVu Sans", sans-serif;
    font-weight: normal;
    color: #C19B77;
}

/* ═══════════════════════════════════════════════════════
   GÜN BAŞLIĞI
═══════════════════════════════════════════════════════ */
.day-header {
    background: #f5f0e8;
    border-left: 4px solid #C19B77;
    border-top: 1px solid #e8dfd0;
    border-bottom: 1px solid #e8dfd0;
    padding: 5px 10px 5px 12px;
    font-size: 8.5pt;
    font-weight: bold;
    color: #7a6552;
    margin-top: 8px;
}
.day-totals { color: #C19B77; }

/* ═══════════════════════════════════════════════════════
   GÜN TABLOSU
═══════════════════════════════════════════════════════ */
table.day-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
    margin-bottom: 0;
}
table.day-table thead th {
    background: #f5f0e8;
    color: #7a6552;
    padding: 5px 10px;
    text-align: left;
    font-size: 7.5pt;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    border-bottom: 1px solid #d9ccba;
    border-top: 1px solid #e8dfd0;
}
table.day-table thead th.r { text-align: right; }
table.day-table thead th.c { text-align: center; }
table.day-table tbody tr.even td { background: #fdfaf6; }
table.day-table tbody tr.odd  td { background: #ffffff; }
table.day-table tbody td {
    padding: 5px 10px;
    border-bottom: 1px solid #e8dfd0;
    vertical-align: middle;
}
table.day-table tbody td.r { text-align: right; }
table.day-table tbody td.c { text-align: center; }
table.day-table tfoot td {
    padding: 6px 10px;
    font-weight: bold;
    background: #f5f0e8;
    border-top: 1.5px solid #C19B77;
    border-bottom: 2px solid #d9ccba;
    font-size: 8.5pt;
}
table.day-table tfoot td.r { text-align: right; color: #C19B77; }

/* ═══════════════════════════════════════════════════════
   BADGES
═══════════════════════════════════════════════════════ */
.badge-free {
    background: #e8f5e9;
    color: #4a7c59;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 7pt;
    font-weight: bold;
    border: 1px solid #c3ddc8;
}
.badge-paid {
    background: #fdf5ee;
    color: #a07742;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 7pt;
    font-weight: bold;
    border: 1px solid #e8dfd0;
}

/* ═══════════════════════════════════════════════════════
   RESTORAN DÖNEM TOPLAM BANDI
═══════════════════════════════════════════════════════ */
.resto-total-bar {
    background: #fdf5ee;
    border: 1px solid #d9ccba;
    border-top: 2px solid #C19B77;
    padding: 7px 14px;
    font-size: 8.5pt;
    margin-bottom: 4px;
}

/* ═══════════════════════════════════════════════════════
   FOOTER (fixed)
═══════════════════════════════════════════════════════ */
.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 2px solid #C19B77;
    padding: 6px 28px;
    font-size: 7.5pt;
    color: #b3a08a;
    letter-spacing: 0.5px;
}

/* ═══════════════════════════════════════════════════════
   YARDIMCILAR
═══════════════════════════════════════════════════════ */
.page-break { page-break-before: always; }
.deco-rule  { border: none; border-top: 1px solid #d9ccba; margin: 16px 0; }
.accent  { color: #C19B77; }
.muted   { color: #b3a08a; }
.sub     { color: #7a6552; }
</style>
</head>
<body>

{{-- FOOTER (fixed — her sayfada) --}}
<div class="footer">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td style="padding:0; text-align:left; color:#b3a08a;">Balmy Hotels &bull; Restoran Y&ouml;netim Sistemi</td>
            <td style="padding:0; text-align:right; color:#b3a08a;">{{ $filters['date_from'] }} &ndash; {{ $filters['date_to'] }} &bull; Olu&#351;turuldu: {{ $generatedAt }}</td>
        </tr>
    </table>
</div>

{{-- ╔══════════════════════════════╗
     ║  HEADER                     ║
     ╚══════════════════════════════╝ --}}
<div class="header-accent-bar"></div>
<div class="header-card">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top; padding:0;">
                <div class="header-brand">Balmy Hotels &bull; Restoran Sipari&#351; Sistemi</div>
                <div class="header-title">G&uuml;n Bazl&#305; Sipari&#351; Raporu</div>
                <div class="header-subtitle">Restoran &bull; &Uuml;r&uuml;n &bull; Miktar D&ouml;k&uuml;m&uuml;</div>
            </td>
            <td style="vertical-align:top; padding:0; width:130px;">
                <div class="header-meta-year">{{ now()->format('Y') }}</div>
                <div class="header-meta-date">Olu&#351;turuldu<br>{{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
    <hr class="header-rule">
    <div style="font-size:7.5pt; color:#b3a08a;">
        <strong style="color:#2e2318;">D&ouml;nem:</strong>
        {{ \Carbon\Carbon::parse($filters['date_from'])->format('d.m.Y') }}
        &nbsp;&mdash;&nbsp;
        {{ \Carbon\Carbon::parse($filters['date_to'])->format('d.m.Y') }}
        @if($filters['restaurant'])
        &nbsp;&bull;&nbsp;
        <strong style="color:#2e2318;">Restoran:</strong> {{ $filters['restaurant'] }}
        @endif
        @php
            $dayCount = \Carbon\Carbon::parse($filters['date_from'])->diffInDays(\Carbon\Carbon::parse($filters['date_to'])) + 1;
        @endphp
        &nbsp;&bull;&nbsp;
        <strong style="color:#2e2318;">S&uuml;re:</strong> {{ $dayCount }} g&uuml;n
    </div>
</div>

{{-- ╔══════════════════════════════╗
     ║  ÖZET KARTLAR               ║
     ╚══════════════════════════════╝ --}}
<div class="section-title" style="margin-top:18px;">D&ouml;nem &Ouml;zeti</div>
<table class="summary-table">
    <tr>
        <td class="s-card">
            <div class="s-label">Toplam Has&#305;lat</div>
            <div class="s-value">&#8378;{{ number_format($summary['paid_revenue'], 0, ',', '.') }}</div>
        </td>
        <td class="s-card">
            <div class="s-label">Toplam Sipari&#351;</div>
            <div class="s-value v-blue">{{ number_format($summary['total_orders']) }}</div>
        </td>
        <td class="s-card">
            <div class="s-label">Toplam Seans</div>
            <div class="s-value v-green">{{ number_format($summary['total_sessions']) }}</div>
        </td>
        <td class="s-card">
            <div class="s-label">Toplam Kalem</div>
            <div class="s-value v-brown">{{ number_format($summary['total_qty']) }}</div>
        </td>
    </tr>
</table>

{{-- ╔══════════════════════════════╗
     ║  RESTORAN ÖZET TABLOSU      ║
     ╚══════════════════════════════╝ --}}
<div class="section-title">Restoran Bazında &Ouml;zet</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:24px;">#</th>
            <th>Restoran</th>
            <th class="c" style="width:60px;">Seans</th>
            <th class="r" style="width:90px;">Toplam Kalem</th>
            <th class="r" style="width:110px;">Has&#305;lat</th>
            <th class="r" style="width:90px;">Pay</th>
        </tr>
    </thead>
    <tbody>
        @php $grandRevenue = $restaurantTotals->sum('total_revenue'); $i = 1; @endphp
        @forelse($restaurantTotals as $rt)
        @php
            $pct  = $grandRevenue > 0 ? round(($rt->total_revenue / $grandRevenue) * 100, 1) : 0;
            $barW = max(2, (int)$pct);
            $rowClass = ($i % 2 === 0) ? 'even' : 'odd';
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="c" style="color:#b3a08a; font-size:8pt;">{{ $i++ }}</td>
            <td><strong>{{ $rt->restaurant_name }}</strong></td>
            <td class="c" style="color:#7a6552;">{{ number_format($rt->sessions) }}</td>
            <td class="r" style="color:#7a6552;">{{ number_format($rt->total_qty) }}</td>
            <td class="r"><strong class="accent">&#8378;{{ number_format($rt->total_revenue, 2, ',', '.') }}</strong></td>
            <td class="r" style="padding-right:10px;">
                <table style="border-collapse:collapse; width:100%;">
                    <tr>
                        <td style="text-align:right; padding:0 4px 0 0; font-size:7.5pt; color:#7a6552; white-space:nowrap;">{{ $pct }}%</td>
                        <td style="padding:0; width:44px;">
                            <table style="border-collapse:collapse; width:44px; background:#e8dfd0; height:7px;">
                                <tr><td style="background:#C19B77; width:{{ $barW }}%; height:7px; padding:0;"></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @empty
        <tr class="odd"><td colspan="6" style="text-align:center; color:#b3a08a; padding:14px; font-style:italic;">Bu d&ouml;nemde sipari&#351; kayd&#305; bulunamad&#305;.</td></tr>
        @endforelse
    </tbody>
    @if($restaurantTotals->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="3"></td>
            <td class="r">{{ number_format($restaurantTotals->sum('total_qty')) }} kalem</td>
            <td class="r" style="color:#C19B77;">&#8378;{{ number_format($restaurantTotals->sum('total_revenue'), 2, ',', '.') }}</td>
            <td class="r">100%</td>
        </tr>
    </tfoot>
    @endif
</table>

<hr class="deco-rule">

{{-- ╔══════════════════════════════╗
     ║  DETAY: RESTO × GÜN × ÜRÜN  ║
     ╚══════════════════════════════╝ --}}
<div class="section-title">G&uuml;n &amp; &Uuml;r&uuml;n Bazl&#305; Detay D&ouml;k&uuml;m</div>

@forelse($byRestaurant as $restaurantId => $dateGroups)
@php
    $restoName     = $dateGroups->first()->first()->restaurant_name ?? '—';
    $restoTotal    = $restaurantTotals->get($restaurantId);
    $restoRevenue  = $restoTotal?->total_revenue ?? 0;
    $restoQty      = $restoTotal?->total_qty ?? 0;
    $restoSessions = $restoTotal?->sessions ?? 0;
@endphp

<div class="resto-header">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td style="padding:0; vertical-align:middle;">
                {{ $restoName }}
                <span class="resto-meta">&nbsp;&bull;&nbsp;{{ $restoSessions }} seans</span>
            </td>
            <td style="text-align:right; padding:0; vertical-align:middle;">
                <span class="resto-revenue">
                    {{ number_format($restoQty) }} kalem
                    &nbsp;&bull;&nbsp;
                    &#8378;{{ number_format($restoRevenue, 2, ',', '.') }}
                </span>
            </td>
        </tr>
    </table>
</div>

@foreach($dateGroups as $date => $rows)
@php
    $dayQty        = $rows->sum('qty');
    $dayRevenue    = $rows->sum('line_total');
    $dateFormatted = \Carbon\Carbon::parse($date)->locale('tr')->isoFormat('D MMMM YYYY, dddd');
    $rowIdx        = 0;
@endphp

<div class="day-header">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td style="padding:0; vertical-align:middle;">{{ $dateFormatted }}</td>
            <td style="text-align:right; padding:0; vertical-align:middle;">
                <span class="day-totals">{{ number_format($dayQty) }} kalem &bull; &#8378;{{ number_format($dayRevenue, 2, ',', '.') }}</span>
            </td>
        </tr>
    </table>
</div>

<table class="day-table">
    <thead>
        <tr>
            <th style="width:44%;">Ürün Adı</th>
            <th class="c" style="width:9%;">Adet</th>
            <th class="r" style="width:16%;">Birim Fiyat</th>
            <th class="r" style="width:16%;">Toplam</th>
            <th class="c" style="width:15%;">Tür</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        @php $rowClass = (++$rowIdx % 2 === 0) ? 'even' : 'odd'; @endphp
        <tr class="{{ $rowClass }}">
            <td>{{ $row->item_name }}</td>
            <td class="c"><strong>{{ number_format($row->qty) }}</strong></td>
            <td class="r" style="color:#7a6552;">
                @if($row->avg_price > 0)
                    &#8378;{{ number_format($row->avg_price, 2, ',', '.') }}
                @else
                    <span class="muted">&mdash;</span>
                @endif
            </td>
            <td class="r">
                @if($row->line_total > 0)
                    <strong class="accent">&#8378;{{ number_format($row->line_total, 2, ',', '.') }}</strong>
                @else
                    <span class="muted">&mdash;</span>
                @endif
            </td>
            <td class="c">
                @if($row->avg_price > 0)
                    <span class="badge-paid">&#220;cretli</span>
                @else
                    <span class="badge-free">&#220;cretsiz</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><strong>G&uuml;n Toplam&#305;</strong></td>
            <td class="c" style="font-weight:bold; color:#7a6552;">{{ number_format($dayQty) }}</td>
            <td></td>
            <td class="r"><strong>&#8378;{{ number_format($dayRevenue, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>

@endforeach

@if($byRestaurant->count() > 1)
<div class="resto-total-bar">
    <table width="100%" style="border-collapse:collapse; font-size:8.5pt;">
        <tr>
            <td style="padding:0; vertical-align:middle; color:#7a6552;">
                <strong style="color:#2e2318;">{{ $restoName }}</strong> &mdash; D&ouml;nem Toplam&#305;
            </td>
            <td style="text-align:right; padding:0; vertical-align:middle;">
                <span style="color:#7a6552;">{{ number_format($restoQty) }} kalem</span>
                &nbsp;&bull;&nbsp;
                <strong class="accent">&#8378;{{ number_format($restoRevenue, 2, ',', '.') }}</strong>
            </td>
        </tr>
    </table>
</div>
@endif

@if(!$loop->last)
<div class="page-break"></div>
@else
<hr class="deco-rule" style="margin-top:18px;">
<div style="text-align:center; font-size:7.5pt; color:#b3a08a; letter-spacing:2px; text-transform:uppercase; padding-bottom:24px;">
    &mdash;&nbsp; Raporun Sonu &nbsp;&mdash;
</div>
@endif

@empty
<div style="text-align:center; color:#b3a08a; padding:24px 0; font-style:italic; font-size:10pt; border:1px solid #e8dfd0; background:#fdfaf6;">
    Bu d&ouml;nemde sipari&#351; kayd&#305; bulunamad&#305;.
</div>
@endforelse

</body>
</html>
