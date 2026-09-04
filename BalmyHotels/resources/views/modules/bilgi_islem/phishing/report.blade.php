<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->name }} - Bilgi Güvenliği Duyarlılık Testi Formu</title>
    <style>
        :root {
            --ink: #17202a;
            --muted: #66727f;
            --line: #2c343b;
            --soft-line: #b8c0c8;
            --brand: #9d7654;
            --brand-dark: #5f4430;
            --brand-soft: #f5eee7;
            --warning: #fff8dd;
            --success: #1f7a4f;
            --danger: #b42318;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            background: #e9edf1;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(23, 32, 42, .96);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .18);
        }

        .toolbar .hint { color: #dce2e8; font-size: 13px; margin-right: 12px; }
        .toolbar button, .toolbar a {
            border: 1px solid transparent;
            border-radius: 7px;
            padding: 9px 15px;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .toolbar .print-btn { background: var(--brand); }
        .toolbar .word-btn { background: #246b45; }
        .toolbar .back-btn { background: transparent; border-color: #72808d; }

        .settings-panel {
            display: grid;
            grid-template-columns: 1.05fr 1.5fr .9fr .55fr 1.1fr;
            gap: 10px;
            width: min(1180px, calc(100% - 24px));
            margin: 12px auto 0;
            padding: 12px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 3px 15px rgba(28, 39, 49, .14);
        }
        .settings-field label { display: block; margin-bottom: 4px; color: #52606d; font-size: 11px; font-weight: 700; }
        .settings-field input { width: 100%; border: 1px solid #c9d0d7; border-radius: 5px; padding: 7px 8px; font: 12px Arial, sans-serif; }
        .settings-help { grid-column: 1 / -1; margin: 0; color: #66727f; font-size: 11px; }

        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 8mm 10mm 21mm;
            background: #fff;
            box-shadow: 0 8px 30px rgba(28, 39, 49, .18);
        }

        .document-header {
            display: grid;
            grid-template-columns: 48mm 1fr;
            align-items: center;
            min-height: 28mm;
            border: 1pt solid var(--line);
        }
        .brand-block { padding: 3mm; text-align: center; border-right: 1pt solid var(--line); }
        .brand-logo { display: block; width: 35mm; max-height: 21mm; object-fit: contain; margin: auto; filter: grayscale(1); opacity: .78; }
        .document-title { padding: 3mm; text-align: center; }
        .document-title h1 { margin: 0; color: var(--brand-dark); font: 700 15pt/1.15 Georgia, 'Times New Roman', serif; }
        .document-title div { margin-top: 1.5mm; color: var(--muted); font-size: 7.5pt; letter-spacing: .12em; }

        .section { border: 1pt solid var(--line); margin-top: 3mm; }
        .section-title {
            padding: 1.6mm 3mm;
            color: var(--brand-dark);
            background: var(--brand-soft);
            border-bottom: 1pt solid var(--line);
            font: 700 10pt Georgia, 'Times New Roman', serif;
            letter-spacing: .025em;
            text-align: center;
            text-transform: uppercase;
        }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5mm 5mm; padding: 2.5mm 3mm; }
        .field { display: grid; grid-template-columns: 32mm 1fr; gap: 2mm; align-items: center; min-height: 6mm; }
        .field.wide { grid-column: 1 / -1; }
        .field label { font-size: 8pt; font-weight: 700; }
        .line-input {
            width: 100%;
            min-width: 0;
            border: 0;
            border-bottom: 1pt dotted #555;
            border-radius: 0;
            padding: 1mm 1.2mm;
            color: var(--ink);
            background: var(--warning);
            font: 8.5pt Arial, sans-serif;
            outline: none;
        }
        .line-input:focus { background: #fff4c2; box-shadow: 0 1px 0 var(--brand); }
        textarea.line-input { min-height: 17mm; resize: vertical; line-height: 1.35; border: 1pt dotted #777; }

        .result-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2mm; padding: 3mm; }
        .result-card { padding: 2.5mm 2mm; border: 1pt solid var(--soft-line); background: #fafbfb; text-align: center; }
        .result-card .label { min-height: 7mm; color: var(--muted); font-size: 7pt; font-weight: 700; text-transform: uppercase; }
        .result-card .value { margin-top: 1mm; color: var(--brand-dark); font-size: 14pt; font-weight: 800; }
        .result-card .detail { margin-top: .7mm; color: var(--muted); font-size: 7pt; }
        .result-card.success .value { color: var(--success); }
        .result-card.danger .value { color: var(--danger); }

        .scope-text { padding: 2.5mm 3mm; font-size: 8pt; line-height: 1.45; }
        .notes { padding: 2.5mm 3mm; }

        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5mm; padding: 3mm; }
        .signature-card { position: relative; min-height: 37mm; border: 1pt solid var(--soft-line); padding: 2mm 2.5mm; }
        .signature-heading { display: flex; justify-content: space-between; align-items: center; color: var(--brand-dark); font-size: 8pt; font-weight: 700; }
        .signature-clear { border: 0; background: transparent; color: #a33; font-size: 7pt; cursor: pointer; text-decoration: underline; }
        .signature-pad { display: block; width: 100%; height: 19mm; margin-top: 1mm; touch-action: none; cursor: crosshair; }
        .signature-name-input {
            display: block;
            width: 100%;
            border: 0;
            border-top: 1pt solid #555;
            border-radius: 0;
            padding: 1mm 1mm .5mm;
            color: var(--ink);
            background: var(--warning);
            text-align: center;
            font: 7.5pt Arial, sans-serif;
            outline: none;
        }
        .signature-role { margin-top: .8mm; color: var(--muted); text-align: center; font-size: 6.8pt; }

        .participant-heading { margin: 3mm 0 2mm; text-align: center; }
        .participant-heading h2 { margin: 0; color: var(--brand-dark); font: 700 13pt Georgia, 'Times New Roman', serif; }
        .participant-heading p { margin: 1mm 0 0; color: var(--muted); font-size: 7.5pt; }
        .participants { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .participants th, .participants td { border: 1pt solid var(--line); padding: 1.5mm 1.6mm; vertical-align: middle; }
        .participants th { background: var(--brand-soft); color: var(--brand-dark); font-size: 7.2pt; text-align: left; }
        .participants td { height: 9mm; font-size: 7.4pt; overflow-wrap: anywhere; }
        .participants .number { width: 9mm; text-align: center; }
        .participants .person { width: 52mm; }
        .participants .branch { width: 32mm; }
        .participants .department { width: 44mm; }
        .participants .result { width: 43mm; }
        .status-safe { color: var(--success); font-weight: 700; }
        .status-clicked { color: #9a6700; font-weight: 700; }
        .status-attempted { color: var(--danger); font-weight: 700; }

        .document-footer {
            position: absolute;
            right: 10mm;
            bottom: 5mm;
            left: 10mm;
            padding-top: 1.5mm;
            border-top: 1pt solid #333;
            color: #4f5962;
            font: 6.7pt Georgia, 'Times New Roman', serif;
        }
        .footer-row { display: grid; grid-template-columns: 1.7fr .9fr .4fr; gap: 3mm; align-items: center; min-height: 4mm; }
        .footer-row + .footer-row { margin-top: .6mm; padding-top: .6mm; border-top: .5pt solid #bbb; }
        .footer-row > div:nth-child(2) { text-align: center; }
        .footer-row > div:last-child { text-align: right; }

        @media screen and (max-width: 820px) {
            .toolbar { flex-wrap: wrap; }
            .toolbar .hint { width: 100%; margin: 0; text-align: center; }
            .settings-panel { grid-template-columns: 1fr 1fr; }
            .settings-help { grid-column: 1 / -1; }
            .sheet { width: 100%; min-height: auto; margin: 0 0 12px; padding: 16px 16px 75px; box-shadow: none; }
            .document-header { grid-template-columns: 1fr; }
            .brand-block { border: 0; border-bottom: 1pt solid var(--line); }
            .info-grid, .signature-grid { grid-template-columns: 1fr; }
            .field.wide { grid-column: auto; }
            .result-grid { grid-template-columns: 1fr 1fr; }
            .participant-page { overflow-x: auto; }
            .participants { min-width: 720px; }
        }

        @page { size: A4 portrait; margin: 0; }
        @media print {
            html, body { width: 210mm; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .toolbar, .settings-panel, .signature-clear { display: none !important; }
            .sheet { width: 210mm; min-height: 297mm; margin: 0; padding: 8mm 10mm 21mm; box-shadow: none; break-after: page; page-break-after: always; }
            .sheet:last-child { break-after: auto; page-break-after: auto; }
            .line-input, .signature-name-input { background: #fff !important; }
            .section-title, .result-card, .participants th { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $testDate = $campaign->started_at ?: $campaign->created_at;
    $closedAt = $campaign->ended_at;
    $clickedWithoutAttempt = max(0, $stats['clicked'] - $stats['attempted']);
    $notAttempted = max(0, $stats['total'] - $stats['attempted']);
    $notAttemptedRate = $stats['total'] ? round(($notAttempted / $stats['total']) * 100, 1) : 0;
    $participantPages = $targets->isEmpty() ? collect([collect()]) : $targets->chunk(20)->values();
    $totalPages = 1 + $participantPages->count();
    $logoPath = public_path('images/logo.png');
    $logoData = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : asset('images/logo.png');
@endphp

<div class="toolbar">
    <span class="hint">Form alanlarını düzenleyebilir, imzaları fare veya dokunmatik ekranla atabilirsiniz.</span>
    <button type="button" class="print-btn" onclick="window.print()">Formu Yazdır / PDF Kaydet</button>
    <button type="button" class="word-btn" id="downloadWordButton">Düzenlenebilir Word İndir</button>
    <a class="back-btn" href="{{ route('it.phishing.show', $campaign) }}">Kampanyaya Dön</a>
</div>

<div class="settings-panel" aria-label="Alt bilgi ayarları">
    <div class="settings-field">
        <label for="configFormCode">Form Kodu</label>
        <input id="configFormCode" value="{{ $reportOptions['form_code'] }}">
    </div>
    <div class="settings-field">
        <label for="configFormTitle">Form Adı</label>
        <input id="configFormTitle" value="{{ $reportOptions['form_title'] }}">
    </div>
    <div class="settings-field">
        <label for="configPublicationDate">Yayın Tarihi</label>
        <input id="configPublicationDate" value="{{ $reportOptions['publication_date'] }}">
    </div>
    <div class="settings-field">
        <label for="configRevision">Revizyon</label>
        <input id="configRevision" value="{{ $reportOptions['revision'] }}">
    </div>
    <div class="settings-field">
        <label for="configSecondaryCode">Alt Doküman Kodu</label>
        <input id="configSecondaryCode" value="{{ $reportOptions['secondary_code'] }}">
    </div>
    <p class="settings-help">Buradaki değerler PDF alt bilgisine ve Word dosyasına otomatik uygulanır. Bu ayar kutusu çıktıda görünmez.</p>
</div>

<form method="POST" action="{{ route('it.phishing.report.word', $campaign) }}" id="wordExportForm" hidden>
    @csrf
    <input type="hidden" name="test_name">
    <input type="hidden" name="form_code">
    <input type="hidden" name="form_title">
    <input type="hidden" name="publication_date">
    <input type="hidden" name="revision">
    <input type="hidden" name="secondary_code">
    <input type="hidden" name="prepared_by_name">
    <input type="hidden" name="approved_by_name">
    <input type="hidden" name="notes">
</form>

<main class="sheet">
    <header class="document-header">
        <div class="brand-block">
            <img src="{{ $logoData }}" alt="Balmy Hotels" class="brand-logo">
        </div>
        <div class="document-title">
            <h1>Bilgi Güvenliği<br>Duyarlılık Testi Formu</h1>
            <div>OLTALAMA FARKINDALIK SİMÜLASYONU</div>
        </div>
    </header>

    <section class="section">
        <div class="section-title">Test Bilgileri</div>
        <div class="info-grid">
            <div class="field wide">
                <label>Testin Adı</label>
                <input class="line-input" id="reportTestName" value="{{ $campaign->name }}">
            </div>
            <div class="field">
                <label>Test Tarihi</label>
                <input class="line-input" type="date" value="{{ $testDate->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label>Rapor Tarihi</label>
                <input class="line-input" type="date" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label>Durumu</label>
                <input class="line-input" value="{{ $campaign->status === 'active' ? 'Devam ediyor' : 'Tamamlandı' }}">
            </div>
            <div class="field">
                <label>Bitiş Tarihi</label>
                <input class="line-input" value="{{ $closedAt?->format('d.m.Y H:i') ?? '—' }}">
            </div>
            <div class="field wide">
                <label>Uygulayan</label>
                <input class="line-input" value="{{ $preparedBy?->name ?? $campaign->creator?->name ?? '' }}">
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Sistem Kayıtlı Test Sonuçları</div>
        <div class="result-grid">
            <div class="result-card">
                <div class="label">Toplam Hedef</div>
                <div class="value">{{ $stats['total'] }}</div>
                <div class="detail">çalışan</div>
            </div>
            <div class="result-card success">
                <div class="label">Bağlantıyı Açmadı</div>
                <div class="value">{{ $stats['unopened'] }}</div>
                <div class="detail">%{{ number_format(100 - $stats['click_rate'], 1, ',', '.') }}</div>
            </div>
            <div class="result-card">
                <div class="label">Bağlantıyı Açtı</div>
                <div class="value">{{ $stats['clicked'] }}</div>
                <div class="detail">%{{ number_format($stats['click_rate'], 1, ',', '.') }} · Bilgi denemeyen: {{ $clickedWithoutAttempt }}</div>
            </div>
            <div class="result-card danger">
                <div class="label">Bilgi Göndermeyi Denedi</div>
                <div class="value">{{ $stats['attempted'] }}</div>
                <div class="detail">%{{ number_format($stats['attempt_rate'], 1, ',', '.') }}</div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Amaç ve Değerlendirme</div>
        <div class="scope-text">
            Bu çalışma, çalışanların şüpheli e-posta ve bağlantılara karşı bilgi güvenliği farkındalığını ölçmek amacıyla kontrollü bir simülasyon olarak uygulanmıştır.
            Test kapsamında gerçek kullanıcı adı veya parola kaydedilmemiştir. Toplam <strong>{{ $notAttempted }} kişi (%{{ number_format($notAttemptedRate, 1, ',', '.') }})</strong> bilgi gönderme girişiminde bulunmamıştır.
            Katılımcı ve sonuç listesi takip eden sayfalarda yer almaktadır.
        </div>
    </section>

    <section class="section">
        <div class="section-title">Açıklamalar / Düzeltici Faaliyetler</div>
        <div class="notes">
            <textarea class="line-input" id="reportNotes" placeholder="Test sonucu değerlendirmesi, planlanan farkındalık çalışmaları veya ek açıklamalar...">Bilgi güvenliği farkındalığının sürdürülmesi amacıyla şüpheli e-posta, bağlantı ve parola taleplerine karşı periyodik bilgilendirme yapılması önerilir.</textarea>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Onay ve İmzalar</div>
        <div class="signature-grid">
            <div class="signature-card">
                <div class="signature-heading">
                    <span>TESTİ GERÇEKLEŞTİREN</span>
                    <button type="button" class="signature-clear" data-clear-signature="preparedBySignature">İmzayı Temizle</button>
                </div>
                <canvas id="preparedBySignature" class="signature-pad" width="900" height="260"></canvas>
                <input class="signature-name-input" id="preparedByName" value="{{ $reportOptions['prepared_by_name'] }}" aria-label="Testi gerçekleştiren adı soyadı">
                <div class="signature-role">{{ $preparedBy?->department?->name ?? 'Bilgi İşlem' }} · Tarih / İmza</div>
            </div>
            <div class="signature-card">
                <div class="signature-heading">
                    <span>KONTROL / ONAY</span>
                    <button type="button" class="signature-clear" data-clear-signature="approvalSignature">İmzayı Temizle</button>
                </div>
                <canvas id="approvalSignature" class="signature-pad" width="900" height="260"></canvas>
                <input class="signature-name-input" id="approvalName" value="{{ $reportOptions['approved_by_name'] }}" aria-label="Kontrol eden adı soyadı">
                <div class="signature-role">Unvan · Tarih / İmza</div>
            </div>
        </div>
    </section>

    <footer class="document-footer">
        <div class="footer-row">
            <div><strong class="footer-form-code">{{ $reportOptions['form_code'] }}</strong> <span class="footer-form-title">{{ $reportOptions['form_title'] }}</span></div>
            <div>Yayın Tarihi <span class="footer-publication-date">{{ $reportOptions['publication_date'] }}</span></div>
            <div class="footer-revision">{{ $reportOptions['revision'] }}</div>
        </div>
        <div class="footer-row">
            <div class="footer-secondary-code">{{ $reportOptions['secondary_code'] }}</div>
            <div class="footer-revision">{{ $reportOptions['revision'] }}</div>
            <div>Syf 1/{{ $totalPages }}</div>
        </div>
    </footer>
</main>

@foreach($participantPages as $pageIndex => $pageTargets)
    <main class="sheet participant-page">
        <header class="document-header">
            <div class="brand-block">
                <img src="{{ $logoData }}" alt="Balmy Hotels" class="brand-logo">
            </div>
            <div class="document-title">
                <h1>Hedef Çalışan ve<br>Sonuç Listesi</h1>
                <div>{{ mb_strtoupper($campaign->name) }}</div>
            </div>
        </header>

        <div class="participant-heading">
            <h2>Teste Dahil Edilen Çalışanlar</h2>
            <p>Bu liste yalnızca kurum içi farkındalık değerlendirmesi amacıyla kullanılmalıdır.</p>
        </div>

        <table class="participants">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th class="person">Adı Soyadı</th>
                    <th class="branch">Şube</th>
                    <th class="department">Departman</th>
                    <th class="result">Sistem Sonucu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pageTargets as $target)
                    @php
                        $rowNumber = ($pageIndex * 20) + $loop->iteration;
                        $attempted = (bool) $target->first_credential_attempted_at;
                        $clicked = (bool) $target->first_clicked_at;
                    @endphp
                    <tr>
                        <td class="number">{{ $rowNumber }}</td>
                        <td class="person"><strong>{{ $target->target_name }}</strong></td>
                        <td class="branch">{{ $target->user?->branch?->name ?? '—' }}</td>
                        <td class="department">{{ $target->user?->department?->name ?? '—' }}</td>
                        <td class="result">
                            @if($attempted)
                                <span class="status-attempted">Bilgi göndermeyi denedi</span>
                            @elseif($clicked)
                                <span class="status-clicked">Bağlantıyı açtı</span>
                            @else
                                <span class="status-safe">Bağlantıyı açmadı</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="height:20mm;text-align:center;color:#66727f">Kampanyada hedef çalışan bulunmuyor.</td></tr>
                @endforelse
            </tbody>
        </table>

        <footer class="document-footer">
            <div class="footer-row">
                <div><strong class="footer-form-code">{{ $reportOptions['form_code'] }}</strong> <span class="footer-form-title">{{ $reportOptions['form_title'] }}</span></div>
                <div>Yayın Tarihi <span class="footer-publication-date">{{ $reportOptions['publication_date'] }}</span></div>
                <div class="footer-revision">{{ $reportOptions['revision'] }}</div>
            </div>
            <div class="footer-row">
                <div class="footer-secondary-code">{{ $reportOptions['secondary_code'] }}</div>
                <div class="footer-revision">{{ $reportOptions['revision'] }}</div>
                <div>Syf {{ $pageIndex + 2 }}/{{ $totalPages }}</div>
            </div>
        </footer>
    </main>
@endforeach

<script>
(() => {
    function signaturePad(canvas) {
        const context = canvas.getContext('2d');
        context.lineWidth = 4;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#15212b';
        let drawing = false;
        let previous = null;

        function point(event) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: (event.clientX - rect.left) * canvas.width / rect.width,
                y: (event.clientY - rect.top) * canvas.height / rect.height,
            };
        }

        canvas.addEventListener('pointerdown', event => {
            drawing = true;
            previous = point(event);
            canvas.setPointerCapture(event.pointerId);
            event.preventDefault();
        });
        canvas.addEventListener('pointermove', event => {
            if (!drawing) return;
            const current = point(event);
            context.beginPath();
            context.moveTo(previous.x, previous.y);
            context.lineTo(current.x, current.y);
            context.stroke();
            previous = current;
            event.preventDefault();
        });
        const stop = event => {
            drawing = false;
            previous = null;
            if (event.pointerId !== undefined && canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
        };
        canvas.addEventListener('pointerup', stop);
        canvas.addEventListener('pointercancel', stop);
        canvas.addEventListener('pointerleave', event => { if (drawing) stop(event); });

        return () => context.clearRect(0, 0, canvas.width, canvas.height);
    }

    const clearFunctions = {};
    document.querySelectorAll('.signature-pad').forEach(canvas => {
        clearFunctions[canvas.id] = signaturePad(canvas);
    });
    document.querySelectorAll('[data-clear-signature]').forEach(button => {
        button.addEventListener('click', () => clearFunctions[button.dataset.clearSignature]?.());
    });

    const footerBindings = [
        ['configFormCode', '.footer-form-code'],
        ['configFormTitle', '.footer-form-title'],
        ['configPublicationDate', '.footer-publication-date'],
        ['configRevision', '.footer-revision'],
        ['configSecondaryCode', '.footer-secondary-code'],
    ];
    footerBindings.forEach(([inputId, selector]) => {
        const input = document.getElementById(inputId);
        const update = () => document.querySelectorAll(selector).forEach(element => {
            element.textContent = input.value;
        });
        input.addEventListener('input', update);
        update();
    });

    const wordExportForm = document.getElementById('wordExportForm');
    document.getElementById('downloadWordButton').addEventListener('click', () => {
        const values = {
            test_name: document.getElementById('reportTestName').value,
            form_code: document.getElementById('configFormCode').value,
            form_title: document.getElementById('configFormTitle').value,
            publication_date: document.getElementById('configPublicationDate').value,
            revision: document.getElementById('configRevision').value,
            secondary_code: document.getElementById('configSecondaryCode').value,
            prepared_by_name: document.getElementById('preparedByName').value,
            approved_by_name: document.getElementById('approvalName').value,
            notes: document.getElementById('reportNotes').value,
        };
        Object.entries(values).forEach(([name, value]) => {
            wordExportForm.querySelector(`[name="${name}"]`).value = value;
        });
        wordExportForm.requestSubmit();
    });

    window.addEventListener('beforeprint', () => document.activeElement?.blur());
})();
</script>
</body>
</html>
