<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>İç Denetim Analiz Raporu</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 11pt;
        color: #2c2c2c;
        background: #fff;
    }

    /* ─── HEADER ─── */
    .header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #c19b77 100%);
        color: #fff;
        padding: 28px 32px 22px;
        margin-bottom: 0;
    }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .header-brand { font-size: 9pt; letter-spacing: 2px; text-transform: uppercase; opacity: 0.7; margin-bottom: 6px; }
    .header-title { font-size: 20pt; font-weight: bold; letter-spacing: -0.5px; }
    .header-subtitle { font-size: 10pt; opacity: 0.8; margin-top: 4px; }
    .header-meta { text-align: right; font-size: 9pt; opacity: 0.75; }

    /* ─── DIVIDER ─── */
    .gold-bar { height: 4px; background: #c19b77; margin-bottom: 24px; }

    /* ─── FILTERS ─── */
    .filter-section {
        background: #f8f5f2;
        border-left: 4px solid #c19b77;
        padding: 10px 16px;
        margin-bottom: 24px;
        font-size: 9pt;
    }
    .filter-section span { margin-right: 16px; }
    .filter-label { font-weight: bold; color: #888; text-transform: uppercase; font-size: 8pt; }

    /* ─── SUMMARY CARDS ─── */
    .summary-grid { display: flex; gap: 12px; margin-bottom: 28px; }
    .summary-card {
        flex: 1;
        background: #fff;
        border: 1px solid #e0d5cc;
        border-radius: 8px;
        padding: 14px 16px;
        text-align: center;
    }
    .summary-card .label { font-size: 8pt; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .summary-card .value { font-size: 22pt; font-weight: bold; }
    .summary-card.gold .value   { color: #c19b77; }
    .summary-card.warning .value { color: #e6a817; }
    .summary-card.danger .value  { color: #c0392b; }
    .summary-card.success .value { color: #27ae60; }

    /* ─── SECTION HEADERS ─── */
    .section-title {
        font-size: 11pt;
        font-weight: bold;
        color: #1a1a2e;
        border-bottom: 2px solid #c19b77;
        padding-bottom: 6px;
        margin-bottom: 14px;
        margin-top: 28px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ─── TABLES ─── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9.5pt; }
    thead th {
        background: #1a1a2e;
        color: #fff;
        padding: 8px 10px;
        text-align: left;
        font-size: 9pt;
        font-weight: bold;
        letter-spacing: 0.3px;
    }
    thead th.center { text-align: center; }
    tbody tr:nth-child(even) { background: #faf8f6; }
    tbody tr:nth-child(odd)  { background: #fff; }
    tbody td { padding: 7px 10px; border-bottom: 1px solid #ede8e3; vertical-align: middle; }
    tbody td.center { text-align: center; }
    .badge-open     { background: #fdecea; color: #c0392b; padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }
    .badge-resolved { background: #e8f5e9; color: #27ae60; padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }
    .badge-gold     { background: #fdf5ee; color: #b5935a; padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }

    /* ─── PROGRESS BAR ─── */
    .progress-wrap { background: #ede8e3; border-radius: 4px; height: 8px; width: 100%; }
    .progress-fill { background: #c19b77; border-radius: 4px; height: 8px; }

    /* ─── FOOTER ─── */
    .footer {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #1a1a2e;
        color: rgba(255,255,255,0.6);
        font-size: 8pt;
        padding: 8px 32px;
        display: flex;
        justify-content: space-between;
    }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-brand">Balmy Hotels &bull; İç Denetim Sistemi</div>
            <div class="header-title">Denetim Analiz Raporu</div>
            <div class="header-subtitle">İç Denetim Özet ve İstatistikleri</div>
        </div>
        <div class="header-meta">
            <div style="font-size:16pt;font-weight:bold;opacity:0.9;">{{ now()->format('Y') }}</div>
            <div>Oluşturuldu: {{ $generatedAt }}</div>
        </div>
    </div>
</div>
<div class="gold-bar"></div>

<!-- FİLTRELER -->
@if(array_filter($filters))
<div class="filter-section">
    <span class="filter-label">Filtreler:</span>
    @if($filters['branch'])   <span><strong>Şube:</strong> {{ $filters['branch'] }}</span>@endif
    @if($filters['department'])<span><strong>Departman:</strong> {{ $filters['department'] }}</span>@endif
    @if($filters['type'])     <span><strong>Tip:</strong> {{ $filters['type'] }}</span>@endif
    @if($filters['date_from'])<span><strong>Başlangıç:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('d.m.Y') }}</span>@endif
    @if($filters['date_to'])  <span><strong>Bitiş:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('d.m.Y') }}</span>@endif
</div>
@endif

<!-- ÖZET KARTLAR -->
<div class="summary-grid">
    <div class="summary-card gold">
        <div class="label">Toplam Denetim</div>
        <div class="value">{{ $totalAudits }}</div>
    </div>
    <div class="summary-card warning">
        <div class="label">Toplam Uygunsuzluk</div>
        <div class="value">{{ $totalNonconformities }}</div>
    </div>
    <div class="summary-card danger">
        <div class="label">Açık Uygunsuzluk</div>
        <div class="value">{{ $openNonconformities }}</div>
    </div>
    <div class="summary-card success">
        <div class="label">Çözülen Uygunsuzluk</div>
        <div class="value">{{ $resolvedNonconformities }}</div>
    </div>
</div>

<!-- DENETİM TİPİ TABLOSU -->
<div class="section-title">Denetim Tipi Bazında Performans</div>
<table>
    <thead>
        <tr>
            <th>Denetim Tipi</th>
            <th class="center">Denetim</th>
            <th class="center">Uygunsuzluk</th>
            <th class="center">Açık</th>
            <th class="center">Ort. Uyg./Denetim</th>
        </tr>
    </thead>
    <tbody>
        @forelse($byType as $row)
        <tr>
            <td><strong>{{ $row['type_name'] }}</strong></td>
            <td class="center"><span class="badge-gold">{{ $row['audit_count'] }}</span></td>
            <td class="center">{{ $row['nc_count'] }}</td>
            <td class="center">
                @if($row['open_nc'] > 0)
                    <span class="badge-open">{{ $row['open_nc'] }}</span>
                @else
                    <span class="badge-resolved">0</span>
                @endif
            </td>
            <td class="center">{{ $row['audit_count'] > 0 ? round($row['nc_count'] / $row['audit_count'], 1) : '0' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#aaa;padding:12px;">Kayıt bulunamadı.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- ŞUBE TABLOSU -->
<div class="section-title">Şube Bazında İstatistik</div>
<table>
    <thead>
        <tr>
            <th>Şube</th>
            <th class="center">Denetim</th>
            <th class="center">Uygunsuzluk</th>
            <th class="center">Açık Uyg.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($byBranch as $row)
        @php $maxA = $byBranch->max('audit_count'); $pct = $maxA > 0 ? round($row['audit_count'] / $maxA * 100) : 0; @endphp
        <tr>
            <td>
                <strong>{{ $row['branch_name'] }}</strong><br>
                <div class="progress-wrap" style="margin-top:4px;">
                    <div class="progress-fill" style="width:{{ $pct }}%;"></div>
                </div>
            </td>
            <td class="center"><span class="badge-gold">{{ $row['audit_count'] }}</span></td>
            <td class="center">{{ $row['nc_count'] }}</td>
            <td class="center">
                @if($row['open_nc'] > 0)
                    <span class="badge-open">{{ $row['open_nc'] }}</span>
                @else
                    <span class="badge-resolved">0</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:#aaa;padding:12px;">Kayıt bulunamadı.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- DEPARTMAN TABLOSU -->
<div class="section-title">Departman Bazında İstatistik</div>
<table>
    <thead>
        <tr>
            <th>Departman</th>
            <th>Şube</th>
            <th class="center">Denetim</th>
            <th class="center">Uygunsuzluk</th>
            <th class="center">Açık Uyg.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($byDepartment as $row)
        <tr>
            <td><strong>{{ $row['dept_name'] }}</strong></td>
            <td><small style="color:#888;">{{ $row['branch_name'] }}</small></td>
            <td class="center"><span class="badge-gold">{{ $row['audit_count'] }}</span></td>
            <td class="center">{{ $row['nc_count'] }}</td>
            <td class="center">
                @if($row['open_nc'] > 0)
                    <span class="badge-open">{{ $row['open_nc'] }}</span>
                @else
                    <span class="badge-resolved">0</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#aaa;padding:12px;">Kayıt bulunamadı.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- SON DENETİMLER -->
@if($allAudits->count() > 0)
<div class="section-title page-break">Son Denetimler</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Denetim Tipi</th>
            <th>Şube</th>
            <th>Departman</th>
            <th>Denetçi</th>
            <th class="center">Uyg.</th>
            <th>Tarih</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allAudits->take(40) as $audit)
        <tr>
            <td style="color:#aaa;font-size:8pt;">{{ $audit->id }}</td>
            <td><strong>{{ $audit->auditType?->name ?? '—' }}</strong></td>
            <td><small>{{ $audit->branch?->name ?? '—' }}</small></td>
            <td><small>{{ $audit->department?->name ?? '—' }}</small></td>
            <td><small>{{ $audit->auditor?->name ?? '—' }}</small></td>
            <td class="center">
                @php $ncC = $audit->nonconformities->count(); $openC = $audit->nonconformities->where('status','open')->count(); @endphp
                @if($ncC > 0)
                    <span class="{{ $openC > 0 ? 'badge-open' : 'badge-resolved' }}">{{ $ncC }}</span>
                @else
                    <span style="color:#ccc;">—</span>
                @endif
            </td>
            <td style="font-size:9pt;">{{ $audit->created_at->format('d.m.Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@if($allAudits->count() > 40)
<p style="font-size:8pt;color:#aaa;text-align:center;">... ve {{ $allAudits->count() - 40 }} kayıt daha. Tam listeyi sistemden görüntüleyebilirsiniz.</p>
@endif
@endif

<!-- FOOTER -->
<div class="footer">
    <span>Balmy Hotels &bull; İç Denetim Sistemi</span>
    <span>Oluşturuldu: {{ $generatedAt }}</span>
    <span>Gizlilik: Dahili Kullanım</span>
</div>

</body>
</html>
