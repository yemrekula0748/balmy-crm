<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $carbon->title }} — Karbon Ayak İzi Raporu</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:9pt; color:#2c3e50; background:#fff; }

/* KAPAK SAYFASI */
.cover-page { page-break-after:always; position:relative; }
.cover-header { background:linear-gradient(135deg, #1a3c2e 0%, #27ae60 60%, #2ecc71 100%); padding:50px 45px 35px; }
.cover-logo-area { display:table; width:100%; margin-bottom:30px; }
.cover-logo-left { display:table-cell; vertical-align:middle; }
.cover-logo-right { display:table-cell; vertical-align:middle; text-align:right; }
.hotel-name { font-size:22pt; font-weight:bold; color:#fff; letter-spacing:1px; }
.hotel-sub { font-size:10pt; color:rgba(255,255,255,0.8); margin-top:4px; }
.report-type-badge { background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.5); border-radius:20px; color:#fff; padding:4px 16px; font-size:8pt; display:inline-block; }
.cover-title-area { padding:40px 45px 30px; }
.report-main-title { font-size:26pt; font-weight:bold; color:#1a3c2e; line-height:1.2; margin-bottom:8px; }
.report-subtitle { font-size:12pt; color:#555; margin-bottom:20px; }
.cover-divider { height:4px; background:linear-gradient(90deg,#27ae60,#2ecc71,#f39c12); border-radius:2px; margin:20px 0; }
.cover-meta-grid { display:table; width:100%; margin-top:20px; }
.cover-meta-cell { display:table-cell; width:33%; vertical-align:top; padding:0 10px 0 0; }
.cover-meta-cell:first-child { padding-left:0; }
.cover-meta-label { font-size:7pt; text-transform:uppercase; letter-spacing:1px; color:#888; margin-bottom:3px; }
.cover-meta-value { font-size:10pt; font-weight:bold; color:#1a3c2e; }

/* STANDART ROZETLER */
.standards-strip { background:#f8fffe; border:1px solid #d4efdf; padding:15px 45px; border-radius:0; }
.std-table { display:table; width:100%; }
.std-cell { display:table-cell; text-align:center; padding:4px; }
.std-badge-pdf { background:#1a3c2e; color:#fff; border-radius:10px; padding:3px 10px; font-size:7pt; font-weight:bold; display:inline-block; }

/* HCMI GAUGE */
.hcmi-box { background:linear-gradient(135deg,#f0fff4,#d4efdf); border:2px solid #27ae60; border-radius:10px; padding:20px; text-align:center; margin:0 auto; width:160px; }
.hcmi-rating-big { font-size:48pt; font-weight:900; line-height:1; }
.hcmi-score-line { font-size:11pt; font-weight:bold; color:#1a3c2e; margin-top:4px; }
.hcmi-label { font-size:7pt; color:#666; margin-top:2px; }

/* GENEL LAYOUT */
.page { padding:30px 45px; }
.page-break { page-break-before:always; padding:30px 45px; }

/* BÖLÜM BAŞLIKLARI */
.section-title { font-size:13pt; font-weight:bold; color:#1a3c2e; border-bottom:2px solid #27ae60; padding-bottom:6px; margin-bottom:14px; margin-top:20px; }
.section-title-small { font-size:10pt; font-weight:bold; color:#2c3e50; border-left:4px solid #27ae60; padding-left:8px; margin:12px 0 8px; }

/* METRİK KARTLAR */
.metric-grid { display:table; width:100%; border-spacing:10px; }
.metric-cell { display:table-cell; width:25%; }
.metric-box { border-radius:8px; padding:14px 10px; text-align:center; }
.metric-box.red   { background:#ffeaea; border:1px solid #e74c3c; }
.metric-box.orange{ background:#fff3e0; border:1px solid #f39c12; }
.metric-box.blue  { background:#e3f2fd; border:1px solid #3498db; }
.metric-box.green { background:#e8f5e9; border:1px solid #27ae60; }
.metric-num { font-size:16pt; font-weight:900; line-height:1; }
.metric-num.red   { color:#c0392b; }
.metric-num.orange{ color:#d35400; }
.metric-num.blue  { color:#2980b9; }
.metric-num.green { color:#1a6b3c; }
.metric-unit { font-size:7.5pt; font-weight:bold; margin-top:2px; }
.metric-desc { font-size:7pt; color:#888; margin-top:3px; }

/* SCOPE ÇUBUKLAR */
.scope-bar-pdf { height:18px; border-radius:4px; margin-bottom:2px; display:block; }
.scope-row { display:table; width:100%; margin-bottom:6px; }
.scope-label-cell { display:table-cell; width:120px; vertical-align:middle; font-size:8.5pt; font-weight:bold; }
.scope-bar-cell { display:table-cell; vertical-align:middle; }
.scope-value-cell { display:table-cell; width:90px; text-align:right; vertical-align:middle; font-size:8.5pt; font-weight:bold; }

/* TABLOLAR */
.data-table { width:100%; border-collapse:collapse; font-size:8pt; }
.data-table thead tr { background:#1a3c2e; color:#fff; }
.data-table thead th { padding:7px 8px; text-align:left; }
.data-table thead th.right { text-align:right; }
.data-table tbody tr:nth-child(even) { background:#f8fffe; }
.data-table tbody tr:hover { background:#e8f5e9; }
.data-table tbody td { padding:6px 8px; border-bottom:1px solid #ecf0f1; vertical-align:middle; }
.data-table tbody td.right { text-align:right; }
.data-table .group-row td { background:linear-gradient(90deg,#f0fff4,#fff); font-weight:bold; font-size:8.5pt; border-left:3px solid; padding:5px 8px; }
.data-table .group-row.scope1 td { border-left-color:#e74c3c; color:#c0392b; }
.data-table .group-row.scope2 td { border-left-color:#f39c12; color:#b7770d; }
.data-table .group-row.scope3 td { border-left-color:#27ae60; color:#1a6b3c; }
.data-table .subtotal-row td { background:#ecf0f1; font-weight:bold; font-size:8pt; }
.data-table .total-row td { background:#1a3c2e; color:#fff; font-weight:bold; font-size:9pt; }

/* BADGE SCOPE */
.sc-badge { border-radius:3px; padding:1px 5px; font-size:7pt; font-weight:bold; }
.sc-badge-1 { background:#e74c3c; color:#fff; }
.sc-badge-2 { background:#f39c12; color:#fff; }
.sc-badge-3 { background:#27ae60; color:#fff; }

/* YOĞUNLUK TABLOSU */
.intensity-table { width:100%; border-collapse:collapse; font-size:8.5pt; }
.intensity-table td { padding:6px 10px; border-bottom:1px solid #ecf0f1; }
.intensity-table tr:nth-child(even) { background:#f8fffe; }
.intensity-label { color:#555; }
.intensity-value { font-weight:bold; text-align:right; color:#1a3c2e; }

/* STANDART LİSTESİ */
.std-list-item { padding:5px 0; border-bottom:1px solid #ecf0f1; display:table; width:100%; }
.std-tick { display:table-cell; width:20px; color:#27ae60; font-weight:bold; }
.std-text-cell { display:table-cell; }
.std-name { font-weight:bold; color:#1a3c2e; font-size:8.5pt; }
.std-desc { color:#888; font-size:7.5pt; }

/* NOTLAR */
.note-box { background:#f8fffe; border:1px solid #d4efdf; border-radius:6px; padding:12px; margin-bottom:10px; }
.note-title { font-size:8.5pt; font-weight:bold; color:#1a3c2e; margin-bottom:5px; }
.note-text { font-size:8pt; color:#555; line-height:1.5; }

/* FOOTER */
.pdf-footer { position:fixed; bottom:0; left:0; right:0; background:#1a3c2e; color:rgba(255,255,255,0.7); padding:6px 45px; font-size:7pt; display:table; width:100%; }
.footer-left { display:table-cell; }
.footer-right { display:table-cell; text-align:right; }
.footer-center { display:table-cell; text-align:center; }

/* UYARI KUTUSU */
.info-box { background:#fff8e1; border:1px solid #f39c12; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:8pt; }
.info-box.green { background:#e8f5e9; border-color:#27ae60; }
.info-box.red { background:#ffeaea; border-color:#e74c3c; }

/* İKİ SÜTUN */
.two-col { display:table; width:100%; }
.col-left { display:table-cell; width:55%; padding-right:10px; vertical-align:top; }
.col-right { display:table-cell; width:45%; padding-left:10px; vertical-align:top; }
</style>
</head>
<body>

<!-- ================================================================
     KAPAK SAYFASI
================================================================ -->
<div class="cover-page">

    <div class="cover-header">
        <div class="cover-logo-area">
            <div class="cover-logo-left">
                <div class="hotel-name">BALMY HOTELS</div>
                <div class="hotel-sub">Sürdürülebilirlik & Çevre Yönetimi</div>
            </div>
            <div class="cover-logo-right">
                <span class="report-type-badge">
                    {{ strtoupper(match($carbon->report_type) { 'monthly' => 'AYLIK RAPOR', 'quarterly' => 'ÇEYREKLİK RAPOR', 'annual' => 'YILLIK RAPOR', default => 'RAPOR' }) }}
                </span>
            </div>
        </div>

        @if($carbon->hcmi_rating)
        <div style="float:right; margin-top:-10px;">
            <div class="hcmi-box">
                <div style="font-size:7pt; color:#1a3c2e; font-weight:bold; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">HCMI Rating</div>
                <div class="hcmi-rating-big" style="color:{{ $carbon->rating_color }}">{{ $carbon->hcmi_rating }}</div>
                <div class="hcmi-score-line">{{ number_format($carbon->hcmi_score, 0) }} / 100 puan</div>
                <div class="hcmi-label">Hotel Carbon Measurement Initiative</div>
            </div>
        </div>
        @endif
    </div>

    <div class="cover-title-area" style="padding:35px 45px 20px;">
        <div class="report-main-title">Karbon Ayak İzi<br>Değerlendirme Raporu</div>
        <div class="report-subtitle">{{ $carbon->title }}</div>
        <div class="cover-divider"></div>

        <div class="cover-meta-grid">
            <div class="cover-meta-cell">
                <div class="cover-meta-label">Raporlama Dönemi</div>
                <div class="cover-meta-value">{{ $carbon->period_start->format('d M Y') }}</div>
                <div style="font-size:8pt;color:#888;">— {{ $carbon->period_end->format('d M Y') }}</div>
            </div>
            <div class="cover-meta-cell">
                <div class="cover-meta-label">Şube / Tesis</div>
                <div class="cover-meta-value">{{ $carbon->branch?->name ?? 'Tüm Otel' }}</div>
            </div>
            <div class="cover-meta-cell">
                <div class="cover-meta-label">Rapor Durumu</div>
                <div class="cover-meta-value" style="color:{{ $carbon->status === 'final' || $carbon->status === 'verified' ? '#27ae60' : '#e67e22' }}">
                    {{ strtoupper(match($carbon->status) { 'draft' => 'TASLAK', 'final' => 'FİNAL', 'verified' => 'DOĞRULANMIŞ', default => $carbon->status }) }}
                </div>
            </div>
            <div class="cover-meta-cell" style="width:auto">
                <div class="cover-meta-label">Toplam Emisyon</div>
                <div class="cover-meta-value" style="color:#c0392b;font-size:14pt;">{{ number_format($carbon->total_co2_total/1000, 3) }} tCO₂e</div>
            </div>
        </div>
    </div>

    {{-- Standartlar Şeridi --}}
    @if($carbon->standards_applied && count($carbon->standards_applied))
    <div class="standards-strip">
        <div style="font-size:7pt;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Uygulanan Standartlar & Çerçeveler</div>
        <div class="std-table">
            @foreach(array_chunk($carbon->standards_applied, 6) as $chunk)
            <div>
                @foreach($chunk as $s)
                <span class="std-badge-pdf">✓ {{ $s }}</span>&nbsp;
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="padding:20px 45px 10px; border-top:1px solid #ecf0f1; margin-top:auto;">
        <div style="display:table;width:100%;">
            <div style="display:table-cell;font-size:7.5pt;color:#888;">
                Hazırlayan: <strong>{{ $carbon->user->name ?? '—' }}</strong> &nbsp;|&nbsp;
                Hazırlanma Tarihi: <strong>{{ $generatedAt }}</strong>
                @if($carbon->finalized_at)
                &nbsp;|&nbsp; Finalize: <strong>{{ $carbon->finalized_at->format('d.m.Y') }}</strong>
                @endif
            </div>
            <div style="display:table-cell;text-align:right;font-size:7pt;color:#aaa;">
                GHG Protocol · ISO 14064-1:2018 · HCMI
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     SAYFA 2: YÖNETİCİ ÖZETİ
================================================================ -->
<div class="page">

    <div class="section-title">1. Yönetici Özeti</div>

    <div class="info-box green" style="margin-bottom:14px;">
        <strong>Rapor Kapsamı:</strong> Bu rapor, GHG Protocol Corporate Standard ve ISO 14064-1:2018 standartları çerçevesinde
        <strong>{{ $carbon->period_start->format('d.m.Y') }} — {{ $carbon->period_end->format('d.m.Y') }}</strong>
        dönemini kapsamaktadır. Tüm Scope 1, 2 ve 3 emisyonları hesaplanmış ve raporlanmıştır.
    </div>

    {{-- Ana Metrikler --}}
    <table class="metric-grid" style="margin-bottom:16px;">
        <tr>
            <td class="metric-cell">
                <div class="metric-box red">
                    <div class="metric-num red">{{ number_format($carbon->total_co2_total/1000, 2) }}</div>
                    <div class="metric-unit" style="color:#c0392b;">tCO₂e Toplam</div>
                    <div class="metric-desc">Scope 1+2+3 emisyon</div>
                </div>
            </td>
            <td class="metric-cell">
                <div class="metric-box orange">
                    <div class="metric-num orange">{{ number_format($carbon->co2_per_room_night, 2) }}</div>
                    <div class="metric-unit" style="color:#d35400;">kgCO₂e / Oda-Gece</div>
                    <div class="metric-desc">HCMI temel metriği</div>
                </div>
            </td>
            <td class="metric-cell">
                <div class="metric-box blue">
                    <div class="metric-num blue">{{ number_format($carbon->co2_per_guest, 2) }}</div>
                    <div class="metric-unit" style="color:#2980b9;">kgCO₂e / Misafir</div>
                    <div class="metric-desc">{{ number_format($carbon->total_guests) }} misafir</div>
                </div>
            </td>
            <td class="metric-cell">
                <div class="metric-box green">
                    <div class="metric-num green">{{ number_format($carbon->renewable_energy_pct, 1) }}%</div>
                    <div class="metric-unit" style="color:#1a6b3c;">Yenilenebilir Enerji</div>
                    <div class="metric-desc">ISO 50001 / EU Taxonomy</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Scope Dağılımı --}}
    <div class="section-title-small">GHG Protocol Scope Dağılımı</div>
    @php
        $totalPdf = max(1, $carbon->total_co2_total);
        $pct1Pdf  = $carbon->total_co2_total > 0 ? round($carbon->total_co2_scope1 / $totalPdf * 100, 1) : 0;
        $pct2Pdf  = $carbon->total_co2_total > 0 ? round($carbon->total_co2_scope2 / $totalPdf * 100, 1) : 0;
        $pct3Pdf  = $carbon->total_co2_total > 0 ? round($carbon->total_co2_scope3 / $totalPdf * 100, 1) : 0;
    @endphp

    <div class="two-col" style="margin-bottom:14px;">
        <div class="col-left">
            <div class="scope-row" style="margin-bottom:8px;">
                <div class="scope-label-cell" style="color:#c0392b;">Scope 1</div>
                <div class="scope-bar-cell">
                    <div style="background:#ecf0f1;border-radius:4px;height:18px;overflow:hidden;">
                        <div style="background:#e74c3c;height:100%;width:{{ $pct1Pdf }}%;border-radius:4px;"></div>
                    </div>
                </div>
                <div class="scope-value-cell" style="color:#c0392b;">{{ $pct1Pdf }}%</div>
            </div>
            <div class="scope-row" style="margin-bottom:8px;">
                <div class="scope-label-cell" style="color:#b7770d;">Scope 2</div>
                <div class="scope-bar-cell">
                    <div style="background:#ecf0f1;border-radius:4px;height:18px;overflow:hidden;">
                        <div style="background:#f39c12;height:100%;width:{{ $pct2Pdf }}%;border-radius:4px;"></div>
                    </div>
                </div>
                <div class="scope-value-cell" style="color:#b7770d;">{{ $pct2Pdf }}%</div>
            </div>
            <div class="scope-row" style="margin-bottom:8px;">
                <div class="scope-label-cell" style="color:#1a6b3c;">Scope 3</div>
                <div class="scope-bar-cell">
                    <div style="background:#ecf0f1;border-radius:4px;height:18px;overflow:hidden;">
                        <div style="background:#27ae60;height:100%;width:{{ $pct3Pdf }}%;border-radius:4px;"></div>
                    </div>
                </div>
                <div class="scope-value-cell" style="color:#1a6b3c;">{{ $pct3Pdf }}%</div>
            </div>
        </div>
        <div class="col-right">
            <table class="intensity-table">
                <tr>
                    <td class="intensity-label">Scope 1 — Doğrudan</td>
                    <td class="intensity-value" style="color:#c0392b;">{{ number_format($carbon->total_co2_scope1, 1) }} kg</td>
                </tr>
                <tr>
                    <td class="intensity-label">Scope 2 — Enerji</td>
                    <td class="intensity-value" style="color:#b7770d;">{{ number_format($carbon->total_co2_scope2, 1) }} kg</td>
                </tr>
                <tr>
                    <td class="intensity-label">Scope 3 — Değer Zinciri</td>
                    <td class="intensity-value" style="color:#1a6b3c;">{{ number_format($carbon->total_co2_scope3, 1) }} kg</td>
                </tr>
                <tr style="border-top:2px solid #1a3c2e;">
                    <td class="intensity-label" style="font-weight:bold;">TOPLAM CO₂e</td>
                    <td class="intensity-value" style="font-size:10pt;color:#1a3c2e;">{{ number_format($carbon->total_co2_total, 1) }} kg</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Operasyonel Veriler --}}
    <div class="section-title-small">Operasyonel Veriler</div>
    <table class="intensity-table" style="margin-bottom:14px;">
        <tr><td class="intensity-label">Raporlama Dönemi</td><td class="intensity-value">{{ $carbon->period_start->format('d.m.Y') }} — {{ $carbon->period_end->format('d.m.Y') }}</td></tr>
        <tr><td class="intensity-label">Toplam Misafir</td><td class="intensity-value">{{ number_format($carbon->total_guests) }} kişi</td></tr>
        <tr><td class="intensity-label">Oda-Gece (Sold)</td><td class="intensity-value">{{ number_format($carbon->occupied_rooms) }}</td></tr>
        <tr><td class="intensity-label">Toplam Oda Kapasitesi</td><td class="intensity-value">{{ number_format($carbon->total_rooms) }}</td></tr>
        <tr><td class="intensity-label">Personel</td><td class="intensity-value">{{ number_format($carbon->staff_count) }} kişi</td></tr>
        <tr><td class="intensity-label">Toplam Alan</td><td class="intensity-value">{{ number_format($carbon->total_area_sqm, 0) }} m²</td></tr>
        <tr><td class="intensity-label">Doluluk Oranı</td><td class="intensity-value">{{ $carbon->total_rooms > 0 ? number_format($carbon->occupied_rooms / ($carbon->total_rooms * 30) * 100, 1) : '—' }}%</td></tr>
        <tr><td class="intensity-label">Su Yoğunluğu</td><td class="intensity-value">{{ number_format($carbon->water_intensity, 3) }} m³/oda-gece</td></tr>
        <tr><td class="intensity-label">Atık Geri Dönüşüm Oranı</td><td class="intensity-value">%{{ number_format($carbon->waste_recycling_rate, 1) }}</td></tr>
    </table>

</div>

<!-- ================================================================
     SAYFA 3: DETAYLI EMİSYON TABLOSU
================================================================ -->
<div class="page-break">

    <div class="section-title">2. Detaylı Emisyon Dökümü</div>

    <div class="info-box" style="margin-bottom:10px;">
        <strong>Metodoloji:</strong> Emisyon faktörleri IPCC AR6 (2023), DEFRA UK GHGCF (2023), IEA ve Ecoinvent 3.9 kaynaklarından alınmıştır.
        Türkiye elektrik emisyon faktörü için IEA Turkey 2023 grid factor (0.649 kgCO₂e/kWh) kullanılmıştır.
        GWP değerleri IPCC AR6 100-yıl perspektifine göre belirlenmiştir.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Kapsam</th>
                <th>Kategori</th>
                <th class="right">Miktar</th>
                <th>Birim</th>
                <th class="right">EF (kgCO₂e/birim)</th>
                <th class="right">CO₂e (kg)</th>
                <th class="right">CO₂e (tCO₂e)</th>
                <th>EF Kaynağı</th>
            </tr>
        </thead>
        <tbody>
            {{-- SCOPE 1 --}}
            @if($scope1Entries->count())
            <tr class="group-row scope1">
                <td colspan="8">SCOPE 1 — DOĞRUDAN EMİSYONLAR (Yakıt Yanması, Soğutucu Gaz Kaçakları)</td>
            </tr>
            @foreach($scope1Entries as $e)
            @if($e->quantity > 0)
            @php
                $allCats2 = \App\Models\CarbonFootprintReport::CATEGORIES;
                $catLabelPdf = $allCats2['scope1'][$e->category]['label'] ?? $e->category;
            @endphp
            <tr>
                <td><span class="sc-badge sc-badge-1">S1</span></td>
                <td>{{ $catLabelPdf }}</td>
                <td class="right">{{ number_format($e->quantity, 3) }}</td>
                <td>{{ $e->unit }}</td>
                <td class="right">{{ number_format($e->emission_factor, 4) }}</td>
                <td class="right" style="font-weight:bold;color:#c0392b;">{{ number_format($e->co2_kg, 3) }}</td>
                <td class="right">{{ number_format($e->co2_kg/1000, 5) }}</td>
                <td style="font-size:7pt;color:#888;">{{ $e->ef_source }}</td>
            </tr>
            @endif
            @endforeach
            <tr class="subtotal-row">
                <td colspan="5" style="text-align:right;font-style:italic;">Scope 1 Alt Toplam:</td>
                <td class="right" style="color:#c0392b;">{{ number_format($scope1Total, 3) }} kg</td>
                <td class="right" style="color:#c0392b;">{{ number_format($scope1Total/1000, 4) }} t</td>
                <td></td>
            </tr>
            @endif

            {{-- SCOPE 2 --}}
            @if($scope2Entries->count())
            <tr class="group-row scope2">
                <td colspan="8">SCOPE 2 — DOLAYLI ENERJİ EMİSYONLARI (Satın Alınan Elektrik, Isı, Soğutma)</td>
            </tr>
            @foreach($scope2Entries as $e)
            @if($e->quantity > 0)
            @php $catLabelPdf = $allCats2['scope2'][$e->category]['label'] ?? $e->category; @endphp
            <tr>
                <td><span class="sc-badge sc-badge-2">S2</span></td>
                <td>{{ $catLabelPdf }}</td>
                <td class="right">{{ number_format($e->quantity, 3) }}</td>
                <td>{{ $e->unit }}</td>
                <td class="right">{{ number_format($e->emission_factor, 4) }}</td>
                <td class="right" style="font-weight:bold;color:#b7770d;">{{ number_format($e->co2_kg, 3) }}</td>
                <td class="right">{{ number_format($e->co2_kg/1000, 5) }}</td>
                <td style="font-size:7pt;color:#888;">{{ $e->ef_source }}</td>
            </tr>
            @endif
            @endforeach
            <tr class="subtotal-row">
                <td colspan="5" style="text-align:right;font-style:italic;">Scope 2 Alt Toplam:</td>
                <td class="right" style="color:#b7770d;">{{ number_format($scope2Total, 3) }} kg</td>
                <td class="right" style="color:#b7770d;">{{ number_format($scope2Total/1000, 4) }} t</td>
                <td></td>
            </tr>
            @endif

            {{-- SCOPE 3 --}}
            @if($scope3Entries->count())
            <tr class="group-row scope3">
                <td colspan="8">SCOPE 3 — DİĞER DOLAYLI EMİSYONLAR (Su, Atık, Gıda, Ulaşım, Tedarik Zinciri)</td>
            </tr>
            @foreach($scope3Entries as $e)
            @if($e->quantity > 0)
            @php $catLabelPdf = $allCats2['scope3'][$e->category]['label'] ?? $e->category; @endphp
            <tr>
                <td><span class="sc-badge sc-badge-3">S3</span></td>
                <td>{{ $catLabelPdf }}</td>
                <td class="right">{{ number_format($e->quantity, 3) }}</td>
                <td>{{ $e->unit }}</td>
                <td class="right">{{ number_format($e->emission_factor, 4) }}</td>
                <td class="right" style="font-weight:bold;color:#1a6b3c;">{{ number_format($e->co2_kg, 3) }}</td>
                <td class="right">{{ number_format($e->co2_kg/1000, 5) }}</td>
                <td style="font-size:7pt;color:#888;">{{ $e->ef_source }}</td>
            </tr>
            @endif
            @endforeach
            <tr class="subtotal-row">
                <td colspan="5" style="text-align:right;font-style:italic;">Scope 3 Alt Toplam:</td>
                <td class="right" style="color:#1a6b3c;">{{ number_format($scope3Total, 3) }} kg</td>
                <td class="right" style="color:#1a6b3c;">{{ number_format($scope3Total/1000, 4) }} t</td>
                <td></td>
            </tr>
            @endif

            {{-- GENEL TOPLAM --}}
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">GENEL TOPLAM CO₂e:</td>
                <td class="right">{{ number_format($carbon->total_co2_total, 3) }} kg</td>
                <td class="right">{{ number_format($carbon->total_co2_total/1000, 4) }} t</td>
                <td></td>
            </tr>
        </tbody>
    </table>

</div>

<!-- ================================================================
     SAYFA 4: YOĞUNLUK METRİKLERİ & STANDARTLAR
================================================================ -->
<div class="page-break">

    <div class="section-title">3. Performans & Yoğunluk Metrikleri (HCMI / ISO Uyumlu)</div>

    <div class="two-col" style="margin-bottom:20px;">
        <div class="col-left">
            <div class="section-title-small">Karbon Yoğunluk Göstergeleri</div>
            <table class="intensity-table">
                <tr>
                    <td class="intensity-label">kgCO₂e / Oda-Gece (HCMI KPI)</td>
                    <td class="intensity-value">{{ number_format($carbon->co2_per_room_night, 3) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">kgCO₂e / Misafir (Guest Night)</td>
                    <td class="intensity-value">{{ number_format($carbon->co2_per_guest, 3) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">kgCO₂e / m² Alan (GRI 305)</td>
                    <td class="intensity-value">{{ number_format($carbon->co2_per_sqm, 3) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">kgCO₂e / Personel (Scope 1+2)</td>
                    <td class="intensity-value">{{ number_format($carbon->co2_per_staff, 3) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">Su Yoğunluğu (m³/oda-gece)</td>
                    <td class="intensity-value">{{ number_format($carbon->water_intensity, 3) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">Yenilenebilir Enerji Oranı</td>
                    <td class="intensity-value" style="color:#1a6b3c; font-weight:bold;">%{{ number_format($carbon->renewable_energy_pct, 1) }}</td>
                </tr>
                <tr>
                    <td class="intensity-label">Atık Geri Dönüşüm Oranı</td>
                    <td class="intensity-value">%{{ number_format($carbon->waste_recycling_rate, 1) }}</td>
                </tr>
            </table>

            @if($carbon->hcmi_rating)
            <div style="margin-top:16px;">
                <div class="section-title-small">HCMI Benchmark Değerlendirmesi</div>
                <table class="intensity-table">
                    <tr>
                        <td class="intensity-label">HCMI Skoru</td>
                        <td class="intensity-value">{{ number_format($carbon->hcmi_score, 1) }} / 100</td>
                    </tr>
                    <tr>
                        <td class="intensity-label">Performans Derecesi</td>
                        <td class="intensity-value" style="color:{{ $carbon->rating_color }};font-size:14pt;">{{ $carbon->hcmi_rating }}</td>
                    </tr>
                    <tr>
                        <td class="intensity-label">Referans: En İyi Sınıf</td>
                        <td class="intensity-value">≤ 5 kgCO₂e/oda-gece</td>
                    </tr>
                    <tr>
                        <td class="intensity-label">Referans: Orta Sınıf</td>
                        <td class="intensity-value">≈ 30 kgCO₂e/oda-gece</td>
                    </tr>
                    <tr>
                        <td class="intensity-label">Metodoloji</td>
                        <td class="intensity-value" style="font-size:7.5pt;color:#888;">HCMI v2 (2023)</td>
                    </tr>
                </table>
            </div>
            @endif
        </div>
        <div class="col-right">
            <div class="section-title-small">AB Uyumlu Standartlar & Çerçeveler</div>
            @if($carbon->standards_applied && count($carbon->standards_applied))
                @foreach($carbon->standards_applied as $s)
                <div class="std-list-item">
                    <div class="std-tick">✓</div>
                    <div class="std-text-cell">
                        <div class="std-name">{{ $s }}</div>
                        @if(isset($standards[$s]))
                        <div class="std-desc">{{ $standards[$s] }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div style="color:#888;font-size:8pt;">Standart belirtilmemiş.</div>
            @endif
        </div>
    </div>

    {{-- Metodoloji Notları --}}
    <div class="section-title">4. Metodoloji, Sınırlar & Beyanlar</div>

    <div class="note-box" style="margin-bottom:10px;">
        <div class="note-title">📋 Raporlama Sınırları</div>
        <div class="note-text">
            Bu rapor GHG Protocol Corporate Standard çerçevesinde operasyonel kontrol yaklaşımı esas alınarak hazırlanmıştır.
            Kapsam 1 emisyonları {{ $carbon->branch?->name ?? 'tesis' }} sınırları içindeki doğrudan yakıt tüketimini ve soğutucu gaz kaçaklarını kapsamaktadır.
            Kapsam 2 emisyonları piyasa bazlı yöntemle (market-based method) hesaplanmıştır.
            Kapsam 3 ilgili kategoriler (ISO 14064, HCMI v2 kapsamındaki) dahil edilmiştir.
        </div>
    </div>

    <div class="note-box" style="margin-bottom:10px;">
        <div class="note-title">⚗️ Emisyon Faktörü Kaynakları</div>
        <div class="note-text">
            • <strong>IPCC AR6 (2023):</strong> Yakıt yanma emisyon faktörleri ve GWP değerleri<br>
            • <strong>DEFRA UK GHGCF (2023):</strong> Ulaşım, atık, su, tedarik kategorileri<br>
            • <strong>IEA Turkey Grid (2023):</strong> Türkiye elektrik şebekesi emisyon faktörü (0.649 kgCO₂e/kWh)<br>
            • <strong>ICAO Carbon Calculator (2023):</strong> Uçak yolculukları<br>
            • <strong>Ecoinvent 3.9:</strong> Tedarik zinciri ve malzeme kategorileri<br>
            • <strong>FAO/IPCC (2023):</strong> Gıda kategorileri için emisyon faktörleri
        </div>
    </div>

    @if($carbon->methodology_notes)
    <div class="note-box" style="margin-bottom:10px;">
        <div class="note-title">📝 Metodoloji Notları</div>
        <div class="note-text">{{ $carbon->methodology_notes }}</div>
    </div>
    @endif

    @if($carbon->improvement_notes)
    <div class="note-box" style="border-color:#27ae60;background:#f0fff4;">
        <div class="note-title" style="color:#1a6b3c;">🎯 İyileştirme Önerileri & Hedefler</div>
        <div class="note-text">{{ $carbon->improvement_notes }}</div>
    </div>
    @endif

    {{-- Uygunluk Beyanı --}}
    <div class="section-title" style="margin-top:20px;">5. Uygunluk Beyanı</div>
    <div class="note-box" style="border-color:#3498db;background:#e8f4fd;">
        <div class="note-title" style="color:#2980b9;">⚖️ Yasal & Standart Uygunluk Beyanı</div>
        <div class="note-text">
            Bu rapor aşağıdaki standart ve düzenlemeler kapsamında hazırlanmıştır:<br><br>
            • <strong>AB CSRD (Corporate Sustainability Reporting Directive):</strong> 2014/95/EU ve revize 2022/2464 direktifi kapsamında sürdürülebilirlik açıklamaları<br>
            • <strong>ISO 14064-1:2018:</strong> Kuruluş düzeyinde sera gazı envanterinin nicelleştirilmesi ve raporlanması<br>
            • <strong>ISO 14001:2015:</strong> Çevre yönetim sistemi çerçevesi<br>
            • <strong>GHG Protocol Corporate Standard:</strong> Scope 1, 2 ve 3 sınıflandırması<br>
            • <strong>HCMI v2:</strong> Otel sektörüne özel karbon ölçüm metodolojisi<br>
            • <strong>EU Taxonomy:</strong> Çevresel sürdürülebilirlik kriterleri (İklim Değişikliğinin Azaltılması hedefi)<br><br>
            <em>Bu belge elektronik olarak oluşturulmuştur ve bilgilendirme amaçlıdır. Doğrulama için yetkili çevre denetim kuruluşuna başvurunuz.</em>
        </div>
    </div>

    {{-- Alt Bilgi --}}
    <div style="margin-top:30px;padding-top:12px;border-top:2px solid #1a3c2e;display:table;width:100%;font-size:7.5pt;color:#888;">
        <div style="display:table-cell;">
            <strong style="color:#1a3c2e;">Balmy Hotels</strong> — Karbon Ayak İzi Raporu<br>
            Raporlama Sistemi: BalmyCRM v1.0 &nbsp;|&nbsp; Rapor Yöneticisi: {{ $carbon->user->name ?? '—' }}
        </div>
        <div style="display:table-cell;text-align:right;">
            Rapor No: #{{ $carbon->id }} &nbsp;|&nbsp; Üretim: {{ $generatedAt }}<br>
            {{ $carbon->period_start->format('d.m.Y') }} — {{ $carbon->period_end->format('d.m.Y') }}
        </div>
    </div>

</div>

</body>
</html>
