<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İ.K Raporu — {{ $filters['dateFrom'] }} / {{ $filters['dateTo'] }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            padding: 20px 28px;
        }

        /* ── Başlık Bölümü ── */
        .report-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 3px solid #4361ee;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .report-header .brand { font-size: 22px; font-weight: 800; color: #4361ee; letter-spacing: -0.5px; }
        .report-header .subtitle { font-size: 12px; color: #64748b; margin-top: 2px; }
        .report-header .meta { text-align: right; font-size: 11px; color: #64748b; }
        .report-header .meta strong { color: #1e293b; }

        /* ── KPI Özet ── */
        .kpi-row {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .kpi-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }
        .kpi-box .kv { font-size: 20px; font-weight: 800; color: #4361ee; line-height: 1; }
        .kpi-box .kl { font-size: 10px; color: #64748b; margin-top: 3px; text-transform: uppercase; letter-spacing: .3px; }

        /* ── Bölüm Başlığı ── */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #4361ee;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-left: 3px solid #4361ee;
            padding-left: 8px;
            margin: 20px 0 10px;
        }

        /* ── Tablo Stilleri ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f1f5f9;
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 7px 8px;
            border: 1px solid #e2e8f0;
        }
        tbody td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td { background: #fafbff; }

        /* ── Shift Badge ── */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-blue   { background: rgba(59,130,246,.1);  color: #2563eb; }
        .badge-amber  { background: rgba(245,158,11,.1);  color: #b45309; }
        .badge-purple { background: rgba(139,92,246,.1);  color: #7c3aed; }
        .badge-green  { background: rgba(16,185,129,.1);  color: #059669; }
        .badge-red    { background: rgba(239,68,68,.1);   color: #dc2626; }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        /* ── Devam Takvimi ── */
        .atd-both  { background: #d1fae5; color: #065f46; font-weight: 700; font-size: 9px; text-align:center; }
        .atd-entry { background: #dbeafe; color: #1e40af; font-weight: 700; font-size: 9px; text-align:center; }
        .atd-exit  { background: #ffedd5; color: #9a3412; font-weight: 700; font-size: 9px; text-align:center; }
        .atd-none  { background: #f1f5f9; color: #cbd5e1; font-size: 9px; text-align:center; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #94a3b8;
        }

        /* ── Sayfa Bölme ── */
        .page-break { page-break-before: always; }

        @media print {
            body { padding: 10px 16px; }
            @page { margin: 12mm 10mm; size: A4 landscape; }
        }
    </style>
</head>
<body>

    {{-- BAŞLIK --}}
    <div class="report-header">
        <div>
            <div class="brand">İ.K Raporu</div>
            <div class="subtitle">İnsan Kaynakları — Personel Devam &amp; Çalışma Analizi</div>
        </div>
        <div class="meta">
            <div><strong>Dönem:</strong> {{ $filters['dateFrom'] }} – {{ $filters['dateTo'] }}</div>
            @if(!empty($filters['branchId']))
            @php $br = $branches->firstWhere('id', $filters['branchId']); @endphp
            <div><strong>Şube:</strong> {{ optional($br)->name ?? '—' }}</div>
            @else
            <div><strong>Şube:</strong> Tüm Şubeler</div>
            @endif
            <div><strong>Oluşturuldu:</strong> {{ now()->format('d.m.Y H:i') }}</div>
        </div>
    </div>

    {{-- KPI --}}
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kv">{{ $summary['total_staff'] }}</div>
            <div class="kl">Aktif Personel</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#10b981">{{ $summary['total_entries'] }}</div>
            <div class="kl">Toplam Giriş</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#f97316">{{ $summary['total_exits'] }}</div>
            <div class="kl">Toplam Çıkış</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#6366f1">{{ $summary['total_hours'] }}</div>
            <div class="kl">Toplam Çalışma (sa)</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#f59e0b">{{ $summary['total_overtime'] }}</div>
            <div class="kl">Fazla Mesai (sa)</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#ef4444">{{ $summary['total_late'] }}</div>
            <div class="kl">Geç Giriş</div>
        </div>
        <div class="kpi-box">
            <div class="kv" style="color:#14b8a6">{{ $summary['avg_daily'] }}</div>
            <div class="kl">Günlük Ort. (sa)</div>
        </div>
    </div>

    {{-- DEPARTMAN ÖZETİ --}}
    @if($deptSummary->count() > 0)
    <div class="section-title">Departman Özeti</div>
    <table>
        <thead>
            <tr>
                <th>Departman</th>
                <th class="text-center">Personel</th>
                <th class="text-center">Toplam (sa)</th>
                <th class="text-center">Kişi Ort. (sa)</th>
                <th class="text-center">Fazla Mesai (sa)</th>
                <th class="text-center">Geç Giriş</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deptSummary as $ds)
            <tr>
                <td><strong>{{ $ds['department'] }}</strong></td>
                <td class="text-center">{{ $ds['count'] }}</td>
                <td class="text-center"><span class="badge badge-blue">{{ $ds['total_hours'] }}</span></td>
                <td class="text-center">{{ $ds['avg_hours'] }}</td>
                <td class="text-center">
                    @if($ds['overtime_hrs'] > 0)
                        <span class="badge badge-amber">+{{ $ds['overtime_hrs'] }}</span>
                    @else —
                    @endif
                </td>
                <td class="text-center">
                    @if($ds['late_entries'] > 0)
                        <span class="badge badge-red">{{ $ds['late_entries'] }} kez</span>
                    @else <span style="color:#10b981">✓</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- PERSONEL BAZLI RAPOR --}}
    <div class="section-title" style="margin-top:24px">Personel Bazlı Çalışma Raporu</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ad Soyad</th>
                <th>Şube</th>
                <th>Departman</th>
                <th class="text-center">Çalışılan Gün</th>
                <th class="text-center">Giriş / Çıkış</th>
                <th class="text-center">Toplam (sa)</th>
                <th class="text-center">Günlük Ort.</th>
                <th class="text-center">Fazla (sa)</th>
                <th class="text-center">Geç Giriş</th>
                <th class="text-center">Shift</th>
                <th class="text-center">İlk – Son</th>
            </tr>
        </thead>
        <tbody>
            @forelse($userStats as $i => $s)
            <tr>
                <td class="text-center" style="color:#94a3b8">{{ $i + 1 }}</td>
                <td><strong>{{ optional($s['user'])->name ?? '-' }}</strong></td>
                <td>{{ $s['branch'] }}</td>
                <td>{{ $s['department'] }}</td>
                <td class="text-center">
                    <span class="badge badge-blue">{{ $s['worked_days'] }}</span>
                </td>
                <td class="text-center">{{ $s['entry_count'] }} / {{ $s['exit_count'] }}</td>
                <td class="text-center"><strong>{{ $s['total_hours'] }}</strong></td>
                <td class="text-center">{{ $s['avg_hours'] }}</td>
                <td class="text-center">
                    @if($s['overtime_hrs'] > 0)
                        <span class="badge badge-amber">+{{ $s['overtime_hrs'] }}</span>
                    @else —
                    @endif
                </td>
                <td class="text-center">
                    @if($s['late_entries'] > 0)
                        <span class="badge badge-red">{{ $s['late_entries'] }} kez</span>
                    @else <span style="color:#10b981">✓</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $s['dominant_shift'] === '09-17' ? 'badge-blue' : ($s['dominant_shift'] === '16-24' ? 'badge-amber' : 'badge-purple') }}">
                        {{ $s['dominant_shift'] }}
                    </span>
                </td>
                <td class="text-center" style="color:#64748b">{{ $s['first_entry'] }} – {{ $s['last_exit'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding:16px;color:#94a3b8">Kayıt bulunamadı.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- DEVAM TAKVİMİ --}}
    @if(count($calDays) <= 31 && count($userStats) > 0)
    <div class="page-break"></div>
    <div class="report-header" style="margin-top:0">
        <div>
            <div class="brand">İ.K Raporu — Devam Takvimi</div>
            <div class="subtitle">{{ $filters['dateFrom'] }} – {{ $filters['dateTo'] }}</div>
        </div>
        <div class="meta"><div>{{ now()->format('d.m.Y H:i') }}</div></div>
    </div>

    <div class="section-title">Günlük Devam Durumu</div>
    <table style="table-layout:fixed">
        <thead>
            <tr>
                <th style="min-width:130px;width:130px">Personel</th>
                @foreach($calDays as $day)
                <th class="text-center" style="width:30px;padding:3px 1px;font-size:9px">
                    {{ \Carbon\Carbon::parse($day)->format('d') }}<br>
                    <span style="font-weight:400;color:#94a3b8">{{ \Carbon\Carbon::parse($day)->locale('tr')->isoFormat('ddd') }}</span>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($userStats as $s)
            @php $uid = optional($s['user'])->id; @endphp
            <tr>
                <td style="font-size:10px">
                    <strong>{{ optional($s['user'])->name ?? '-' }}</strong>
                    <div style="font-size:9px;color:#64748b">{{ $s['department'] }}</div>
                </td>
                @foreach($calDays as $day)
                @php $status = $attendance[$uid][$day] ?? null; @endphp
                <td style="padding:2px;height:24px"
                    class="{{ $status === 'both' ? 'atd-both' : ($status === 'entry' ? 'atd-entry' : ($status === 'exit' ? 'atd-exit' : 'atd-none')) }}">
                    {{ $status === 'both' ? '✓' : ($status === 'entry' ? 'G' : ($status === 'exit' ? 'Ç' : '–')) }}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:12px;display:flex;gap:16px;font-size:10px;color:#64748b">
        <span><span style="background:#d1fae5;color:#065f46;padding:1px 6px;border-radius:3px;font-weight:700">✓</span> Giriş &amp; Çıkış</span>
        <span><span style="background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:3px;font-weight:700">G</span> Sadece Giriş</span>
        <span><span style="background:#ffedd5;color:#9a3412;padding:1px 6px;border-radius:3px;font-weight:700">Ç</span> Sadece Çıkış</span>
        <span><span style="background:#f1f5f9;color:#cbd5e1;padding:1px 6px;border-radius:3px;font-weight:700">–</span> Kayıt Yok</span>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="report-footer">
        <span>Balmy Hotels — İnsan Kaynakları Sistemi</span>
        <span>{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu</span>
    </div>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>
