<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kapı Giriş/Çıkış Raporu</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 10pt;
            color: #111;
            background: #fff;
            padding: 10mm;
        }

        /* ── Ekran toolbar ── */
        .toolbar {
            background: #1e293b;
            color: #fff;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .toolbar h5 { font-size: 15px; font-weight: 700; margin: 0; }
        .toolbar .meta { font-size: 12px; color: #94a3b8; }
        .btn-print {
            background: #ef4444; color: #fff; border: none;
            padding: 9px 22px; border-radius: 6px; font-size: 13px;
            font-weight: 600; cursor: pointer;
        }
        .btn-back {
            background: #f1f5f9; color: #374151;
            border: 1px solid #cbd5e1;
            padding: 9px 16px; border-radius: 6px; font-size: 13px;
            text-decoration: none; display: inline-block;
        }

        /* ── Rapor başlığı ── */
        .report-header {
            border-bottom: 2pt solid #1e293b;
            padding-bottom: 8mm;
            margin-bottom: 7mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .report-header .title { font-size: 18pt; font-weight: 900; color: #1e293b; }
        .report-header .subtitle { font-size: 9pt; color: #64748b; margin-top: 2px; }
        .report-header .meta-box { text-align: right; font-size: 8.5pt; color: #475569; line-height: 1.7; }
        .report-header .meta-box strong { color: #1e293b; }

        /* ── Özet kartlar ── */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5mm;
            margin-bottom: 7mm;
        }
        .summary-card {
            border: 1pt solid #e2e8f0;
            border-radius: 4mm;
            padding: 4mm 5mm;
            background: #f8fafc;
        }
        .summary-card .s-label { font-size: 7.5pt; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
        .summary-card .s-value { font-size: 20pt; font-weight: 900; color: #1e293b; line-height: 1.1; margin-top: 1mm; }
        .summary-card.green  { background: #f0fdf4; border-color: #86efac; }
        .summary-card.green  .s-value { color: #15803d; }
        .summary-card.red    { background: #fff1f2; border-color: #fca5a5; }
        .summary-card.red    .s-value { color: #b91c1c; }
        .summary-card.yellow { background: #fffbeb; border-color: #fcd34d; }
        .summary-card.yellow .s-value { color: #92400e; }

        /* ── Tablo ── */
        .section-title {
            font-size: 10pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #1e293b;
            border-left: 3pt solid #4361ee;
            padding-left: 4mm;
            margin-bottom: 4mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        thead tr {
            background: #1e293b;
            color: #fff;
        }
        thead th {
            padding: 3mm 3mm;
            font-weight: 700;
            text-align: left;
            white-space: nowrap;
        }
        thead th.center { text-align: center; }
        tbody tr { border-bottom: 0.5pt solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 2.5mm 3mm; vertical-align: middle; }
        tbody td.center { text-align: center; }
        tfoot tr { background: #f1f5f9; border-top: 1.5pt solid #1e293b; }
        tfoot td { padding: 3mm; font-weight: 900; font-size: 9pt; }

        .badge-g { background: #dcfce7; color: #166534; padding: 1mm 2.5mm; border-radius: 2mm; font-weight: 700; font-size: 8pt; }
        .badge-r { background: #fee2e2; color: #991b1b; padding: 1mm 2.5mm; border-radius: 2mm; font-weight: 700; font-size: 8pt; }
        .badge-b { background: #dbeafe; color: #1e40af; padding: 1mm 2.5mm; border-radius: 2mm; font-weight: 700; font-size: 8pt; }

        .name-cell { font-weight: 700; }
        .sub-cell  { font-size: 7.5pt; color: #64748b; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 8mm;
            padding-top: 4mm;
            border-top: 0.5pt solid #e2e8f0;
            font-size: 7.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ── Baskıya özel ── */
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

{{-- Ekran toolbar --}}
<div class="toolbar">
    <div>
        <h5>🖨️ Kapı Giriş/Çıkış Raporu — Önizleme</h5>
        <div class="meta">{{ $filters['dateFrom'] }} – {{ $filters['dateTo'] }} &nbsp;|&nbsp; {{ count($data['userStats']) }} personel</div>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn-print" onclick="window.print()">🖨️ Yazdır / PDF Kaydet</button>
        <a class="btn-back" href="javascript:history.back()">← Geri</a>
    </div>
</div>

{{-- Rapor Başlığı --}}
<div class="report-header">
    <div>
        <div class="title">Kapı Giriş/Çıkış Raporu</div>
        <div class="subtitle">Personel Çalışma Süresi & Giriş/Çıkış İstatistikleri</div>
    </div>
    <div class="meta-box">
        <div>Dönem: <strong>{{ \Carbon\Carbon::parse($filters['dateFrom'])->locale('tr')->isoFormat('D MMMM YYYY') }} – {{ \Carbon\Carbon::parse($filters['dateTo'])->locale('tr')->isoFormat('D MMMM YYYY') }}</strong></div>
        @if($filters['branchId'])
            <div>Şube: <strong>{{ $branches->firstWhere('id', $filters['branchId'])?->name ?? '-' }}</strong></div>
        @endif
        @if($filters['deptId'])
            <div>Departman: <strong>{{ $departments->firstWhere('id', $filters['deptId'])?->name ?? '-' }}</strong></div>
        @endif
        <div>Oluşturulma: <strong>{{ now()->locale('tr')->translatedFormat('d F Y, H:i') }}</strong></div>
        <div>Oluşturan: <strong>{{ auth()->user()->name }}</strong></div>
    </div>
</div>

{{-- Özet Kartlar --}}
<div class="summary-row">
    <div class="summary-card">
        <div class="s-label">Aktif Personel</div>
        <div class="s-value">{{ $data['summary']['total_users'] }}</div>
    </div>
    <div class="summary-card green">
        <div class="s-label">Toplam Giriş</div>
        <div class="s-value">{{ $data['summary']['total_entries'] }}</div>
    </div>
    <div class="summary-card red">
        <div class="s-label">Toplam Çıkış</div>
        <div class="s-value">{{ $data['summary']['total_exits'] }}</div>
    </div>
    <div class="summary-card yellow">
        <div class="s-label">Toplam Çalışma</div>
        <div class="s-value">{{ $data['summary']['total_hours'] }} sa.</div>
    </div>
</div>

{{-- Personel Tablosu --}}
<div class="section-title">Personel Bazlı Detay</div>

@if(empty($data['userStats']))
    <p style="color:#64748b;text-align:center;padding:10mm 0;">Seçilen filtrelere ait kayıt bulunamadı.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Personel</th>
            <th>Şube</th>
            <th>Departman</th>
            <th class="center">Çalışılan Gün</th>
            <th class="center">Toplam Saat</th>
            <th class="center">Ort. Saat/Gün</th>
            <th class="center">Giriş</th>
            <th class="center">Çıkış</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['userStats'] as $i => $stat)
        <tr>
            <td class="sub-cell">{{ $i + 1 }}</td>
            <td>
                <div class="name-cell">{{ optional($stat['user'])->name ?? '-' }}</div>
                @if(optional($stat['user'])->title)
                    <div class="sub-cell">{{ $stat['user']->title }}</div>
                @endif
            </td>
            <td>{{ $stat['branch'] }}</td>
            <td>{{ $stat['department'] }}</td>
            <td class="center"><span class="badge-b">{{ $stat['worked_days'] }} gün</span></td>
            <td class="center">
                @php
                    $h = floor($stat['total_minutes'] / 60);
                    $m = $stat['total_minutes'] % 60;
                @endphp
                <strong>{{ $h }}s {{ $m }}dk</strong>
            </td>
            <td class="center">
                @php
                    $avg = $stat['worked_days'] > 0
                        ? round($stat['total_hours'] / $stat['worked_days'], 1) : 0;
                @endphp
                {{ $avg }} sa.
            </td>
            <td class="center"><span class="badge-g">{{ $stat['entry_count'] }}</span></td>
            <td class="center"><span class="badge-r">{{ $stat['exit_count'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">TOPLAM / ORTALAMA</td>
            <td class="center">{{ $data['summary']['total_hours'] }} sa.</td>
            <td class="center">
                {{ count($data['userStats']) > 0
                    ? round($data['summary']['total_hours'] / count($data['userStats']), 1)
                    : 0 }} sa.
            </td>
            <td class="center">{{ $data['summary']['total_entries'] }}</td>
            <td class="center">{{ $data['summary']['total_exits'] }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- Footer --}}
<div class="report-footer">
    <span>BalmyHotels CRM &mdash; Kapı Giriş/Çıkış Modülü</span>
    <span>{{ now()->format('d.m.Y H:i') }}</span>
</div>

</body>
</html>
