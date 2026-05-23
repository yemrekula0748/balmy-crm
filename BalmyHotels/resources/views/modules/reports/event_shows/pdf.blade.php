<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Etkinlik/Show Raporu</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #172033;
            font-size: 9px;
            line-height: 1.45;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .brand {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: bold;
        }
        h1 {
            font-size: 20px;
            margin: 5px 0 4px;
            color: #111827;
        }
        .meta {
            color: #64748b;
            font-size: 9px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            width: 20%;
            vertical-align: top;
        }
        .summary-label {
            color: #64748b;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-top: 4px;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
        }
        table.report th {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 7px;
            text-align: left;
        }
        table.report td {
            border: 1px solid #e2e8f0;
            padding: 7px;
            vertical-align: top;
        }
        .center { text-align: center; }
        .num { font-size: 12px; font-weight: bold; }
        .present { color: #047857; }
        .missing { color: #b91c1c; }
        .names {
            color: #334155;
            font-size: 8.5px;
        }
        .muted { color: #64748b; }
        .empty {
            text-align: center;
            color: #64748b;
            padding: 22px;
            border: 1px solid #e2e8f0;
        }
        .footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 8px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Balmy CRM · Genel Raporlar</div>
        <h1>Etkinlik/Show Katılım Raporu</h1>
        <div class="meta">
            Dönem: {{ \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d.m.Y') }}
            · Oluşturma: {{ ($generatedAt ?? now())->format('d.m.Y H:i') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Show Sayısı</div>
                <div class="summary-value">{{ $summary['show_count'] }}</div>
            </td>
            <td>
                <div class="summary-label">Beklenen</div>
                <div class="summary-value">{{ $summary['expected_count'] }}</div>
            </td>
            <td>
                <div class="summary-label">Gelen</div>
                <div class="summary-value present">{{ $summary['present_count'] }}</div>
            </td>
            <td>
                <div class="summary-label">Gelmedi</div>
                <div class="summary-value missing">{{ $summary['missing_count'] }}</div>
            </td>
            <td>
                <div class="summary-label">Katılım Oranı</div>
                <div class="summary-value">%{{ $summary['attendance_rate'] }}</div>
            </td>
        </tr>
    </table>

    @if($rows->count() > 0)
    <table class="report">
        <thead>
            <tr>
                <th style="width:9%">Tarih</th>
                <th style="width:18%">Etkinlik</th>
                <th style="width:8%" class="center">Beklenen</th>
                <th style="width:8%" class="center">Gelen</th>
                <th style="width:8%" class="center">Gelmedi</th>
                <th style="width:24%">Gelen İsimler</th>
                <th style="width:25%">Gelmeyen İsimler</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td><strong>{{ $row['event_date']->event_date->format('d.m.Y') }}</strong></td>
                <td>
                    <strong>{{ $row['event']->name }}</strong><br>
                    <span class="muted">{{ $row['event']->branch?->name ?? '-' }}</span>
                </td>
                <td class="center num">{{ $row['expected_count'] }}</td>
                <td class="center num present">{{ $row['present_count'] }}</td>
                <td class="center num missing">{{ $row['missing_count'] }}</td>
                <td class="names">
                    {{ $row['present_names']->count() ? $row['present_names']->implode(', ') : '-' }}
                </td>
                <td class="names">
                    {{ $row['missing_names']->count() ? $row['missing_names']->implode(', ') : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty">Seçili tarih/filtre aralığında etkinlik kaydı bulunamadı.</div>
    @endif

    <div class="footer">
        BalmyCRM · Etkinlik/Show Raporları
    </div>
</body>
</html>
