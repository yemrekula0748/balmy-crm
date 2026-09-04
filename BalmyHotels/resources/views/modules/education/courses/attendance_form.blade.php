<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $course->title }} - Eğitim Katılım Formu</title>
    <style>
        :root {
            --ink: #17202a;
            --muted: #66727f;
            --line: #2c343b;
            --soft-line: #b8c0c8;
            --brand: #9d7654;
            --brand-dark: #5f4430;
            --brand-soft: #f5eee7;
            --success: #1f7a4f;
            --warning: #fff8dd;
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
        .toolbar .back-btn { background: transparent; border-color: #72808d; }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 7mm 10mm 6mm;
            background: #fff;
            box-shadow: 0 8px 30px rgba(28, 39, 49, .18);
        }

        .document-header {
            display: grid;
            grid-template-columns: 1.05fr 1.45fr;
            gap: 6mm;
            align-items: end;
            margin-bottom: 2.5mm;
        }

        .brand-block { text-align: center; }
        .brand-logo { width: 33mm; height: auto; display: block; margin: 0 auto 1mm; }
        .brand-block h1 {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 16.5pt;
            line-height: 1.05;
            color: #111;
        }
        .brand-block .subtitle { margin-top: 1.5mm; color: var(--muted); font-size: 8pt; letter-spacing: .12em; }

        .training-types {
            border: 1.2pt solid var(--line);
            padding: 2.7mm 3.5mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm 4mm;
            min-height: 25mm;
            align-content: center;
        }
        .choice { display: flex; gap: 2mm; align-items: center; font-size: 9pt; font-weight: 700; }
        .choice input {
            appearance: none;
            width: 4mm;
            height: 4mm;
            border: 1pt solid #333;
            border-radius: 0;
            margin: 0;
            display: grid;
            place-content: center;
        }
        .choice input::before { content: ''; width: 2.2mm; height: 2.2mm; transform: scale(0); background: var(--brand-dark); }
        .choice input:checked::before { transform: scale(1); }

        .section {
            border: 1pt solid var(--line);
            margin-top: -1px;
        }
        .section-title {
            padding: 1.5mm 3mm;
            background: var(--brand-soft);
            border-bottom: 1pt solid var(--line);
            color: var(--brand-dark);
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 10.5pt;
            font-weight: 700;
            letter-spacing: .025em;
            text-align: center;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.8mm 5mm;
            padding: 2.3mm 3mm;
        }
        .field { display: grid; grid-template-columns: 34mm 1fr; gap: 2mm; align-items: center; min-height: 6mm; }
        .field.wide { grid-column: 1 / -1; grid-template-columns: 34mm 1fr; }
        .field label { font-size: 8.5pt; font-weight: 700; }

        input.line-input, textarea.line-input {
            width: 100%;
            border: 0;
            border-bottom: 1pt dotted #555;
            border-radius: 0;
            background: var(--warning);
            color: var(--ink);
            font: 9pt Arial, sans-serif;
            padding: 1mm 1.2mm;
            outline: none;
        }
        input.line-input:focus, textarea.line-input:focus { background: #fff4c2; box-shadow: 0 1px 0 var(--brand); }
        input.line-input[readonly] { background: #f4f6f7; font-weight: 700; }

        .topics { padding: 2mm 3mm 2.4mm; }
        .topic-row { display: grid; grid-template-columns: 6mm 1fr; gap: 1mm; align-items: center; margin-bottom: 1.2mm; }
        .topic-row:last-child { margin-bottom: 0; }
        .topic-row span { font-size: 8.5pt; font-weight: 700; text-align: center; }

        .participant-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
        }
        .participant-grid .field-box {
            min-height: 12mm;
            padding: 2.2mm 3mm;
            border-right: 1pt solid var(--line);
            border-bottom: 1pt solid var(--line);
        }
        .participant-grid .field-box:nth-child(2n) { border-right: 0; }
        .participant-grid .field-box:nth-last-child(-n+2) { border-bottom: 0; }
        .field-box label { display: block; margin-bottom: 1mm; color: var(--muted); font-size: 7.5pt; font-weight: 700; text-transform: uppercase; }
        .field-box input { font-size: 9pt; font-weight: 700; }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2mm;
            padding: 3mm;
        }
        .result-card {
            min-height: 16mm;
            padding: 2.2mm 2mm;
            border: 1pt solid var(--soft-line);
            background: #fafbfb;
            text-align: center;
        }
        .result-card .label { color: var(--muted); font-size: 7.2pt; font-weight: 700; text-transform: uppercase; }
        .result-card .value { margin-top: 1.7mm; font-size: 13pt; font-weight: 800; color: var(--brand-dark); }
        .result-card .detail { margin-top: .7mm; color: var(--muted); font-size: 6.8pt; }
        .result-card.success .value { color: var(--success); }

        .notes { padding: 2.5mm 3mm 3mm; }
        .notes textarea {
            height: 13mm;
            resize: none;
            border: 1pt dotted #777;
            line-height: 1.35;
        }

        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5mm; padding: 3mm; }
        .signature-card { position: relative; min-height: 31mm; border: 1pt solid var(--soft-line); padding: 2mm 2.5mm; }
        .signature-heading { display: flex; justify-content: space-between; align-items: center; font-size: 8pt; font-weight: 700; color: var(--brand-dark); }
        .signature-clear { border: 0; background: transparent; color: #a33; font-size: 7pt; cursor: pointer; text-decoration: underline; }
        .signature-pad { display: block; width: 100%; height: 16mm; margin-top: 1mm; touch-action: none; cursor: crosshair; }
        .signature-name { border-top: 1pt solid #555; padding-top: 1mm; text-align: center; font-size: 7.5pt; }

        .document-footer {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 4mm;
            align-items: center;
            margin-top: 3mm;
            padding: 1.5mm 1mm 0;
            border-top: 1pt solid #333;
            color: #4f5962;
            font: 6.7pt Georgia, 'Times New Roman', serif;
        }
        .document-footer div:nth-child(2) { text-align: center; }
        .document-footer div:last-child { text-align: right; }

        .screen-note { display: none; }

        @media screen and (max-width: 820px) {
            .toolbar { flex-wrap: wrap; }
            .toolbar .hint { width: 100%; text-align: center; margin: 0; }
            .sheet { width: 100%; min-height: auto; margin: 0; padding: 16px; box-shadow: none; }
            .document-header, .info-grid, .participant-grid, .signature-grid { grid-template-columns: 1fr; }
            .field.wide { grid-column: auto; }
            .participant-grid .field-box { border-right: 0; border-bottom: 1pt solid var(--line) !important; }
            .participant-grid .field-box:last-child { border-bottom: 0 !important; }
            .result-grid { grid-template-columns: 1fr 1fr; }
        }

        @page { size: A4 portrait; margin: 0; }
        @media print {
            html, body { width: 210mm; height: 297mm; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .toolbar, .signature-clear { display: none !important; }
            .sheet { width: 210mm; min-height: 297mm; margin: 0; padding: 7mm 10mm 6mm; box-shadow: none; page-break-after: avoid; }
            input.line-input, textarea.line-input { background: #fff !important; }
            .section-title, .result-card { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $learner = $assignment->learner;
    $trainer = $course->trainer;
    $durationMinutes = max(1, (int) ceil(max((int) $assignment->duration_seconds, (int) $course->duration_seconds) / 60));
    $completionPercent = min(100, round((float) $assignment->progress_percent, 1));
    $quizStatus = ! $course->has_quiz
        ? 'Quiz uygulanmadı'
        : ($quizAttempt?->passed ? 'Başarılı' : 'Başarısız');
    $logoPath = public_path('images/logo.png');
    $logoData = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : asset('images/logo.png');
@endphp

<div class="toolbar">
    <span class="hint">Sarı alanları düzenleyebilir, imzaları fare veya dokunmatik ekranla atabilirsiniz.</span>
    <button type="button" class="print-btn" onclick="window.print()">Formu Yazdır / PDF Kaydet</button>
    <a class="back-btn" href="{{ route('education.courses.show', $course) }}">Eğitime Dön</a>
</div>

<main class="sheet">
    <header class="document-header">
        <div class="brand-block">
            <img src="{{ $logoData }}" alt="Balmy Hotels" class="brand-logo">
            <h1>Eğitim Katılım<br>ve Tamamlama Formu</h1>
            <div class="subtitle">EĞİTİM VE GELİŞİM</div>
        </div>
        <div class="training-types" aria-label="Eğitim türü">
            <label class="choice"><input type="radio" name="training_type"><span>15 Dakikalık Eğitim</span></label>
            <label class="choice"><input type="radio" name="training_type"><span>Oryantasyon Eğitimi</span></label>
            <label class="choice"><input type="radio" name="training_type" checked><span>İç Eğitim</span></label>
            <label class="choice"><input type="radio" name="training_type"><span>Dış Eğitim</span></label>
        </div>
    </header>

    <section class="section">
        <div class="section-title">Eğitim Bilgileri</div>
        <div class="info-grid">
            <div class="field wide">
                <label>Eğitimin Adı</label>
                <input class="line-input" value="{{ $course->title }}">
            </div>
            <div class="field wide">
                <label>Eğitimci</label>
                <input class="line-input" value="{{ $trainer->name ?? '' }}">
            </div>
            <div class="field">
                <label>Eğitim Tarihi</label>
                <input class="line-input" type="date" value="{{ optional($completedAt)->format('Y-m-d') ?? now()->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label>Eğitim Yeri</label>
                <input class="line-input" value="{{ $learner->branch->name ?? '' }}">
            </div>
            <div class="field">
                <label>Süre</label>
                <input class="line-input" value="{{ $durationMinutes }} dakika">
            </div>
            <div class="field">
                <label>Eğitim Dili</label>
                <input class="line-input" value="{{ $course->language_label }}">
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Eğitim Konu Başlıkları</div>
        <div class="topics">
            @for($i = 0; $i < 5; $i++)
                <div class="topic-row">
                    <span>{{ $i + 1 }}.</span>
                    <input class="line-input" value="{{ $topicDefaults->get($i, '') }}" placeholder="Konu başlığı">
                </div>
            @endfor
        </div>
    </section>

    <section class="section">
        <div class="section-title">Katılımcı Bilgileri</div>
        <div class="participant-grid">
            <div class="field-box">
                <label>Adı Soyadı</label>
                <input class="line-input" value="{{ $learner->name ?? '' }}">
            </div>
            <div class="field-box">
                <label>Departmanı</label>
                <input class="line-input" value="{{ $learner->department->name ?? '' }}">
            </div>
            <div class="field-box">
                <label>Şubesi</label>
                <input class="line-input" value="{{ $learner->branch->name ?? '' }}">
            </div>
            <div class="field-box">
                <label>Unvanı / Görevi</label>
                <input class="line-input" value="{{ $learner->title ?? '' }}">
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Sistem Kayıtlı Eğitim Sonucu</div>
        <div class="result-grid">
            <div class="result-card success">
                <div class="label">Tamamlama</div>
                <div class="value">%{{ number_format($completionPercent, 1, ',', '.') }}</div>
                <div class="detail">{{ optional($completedAt)->format('d.m.Y H:i') ?? 'Tamamlandı' }}</div>
            </div>
            <div class="result-card {{ $quizAttempt?->passed ? 'success' : '' }}">
                <div class="label">Quiz Sonucu</div>
                <div class="value">
                    @if($quizAttempt)
                        {{ $quizAttempt->correct_answers }} / {{ $quizAttempt->total_questions }}
                    @else
                        -
                    @endif
                </div>
                <div class="detail">{{ $quizStatus }}</div>
            </div>
            <div class="result-card">
                <div class="label">Quiz Başarı Oranı</div>
                <div class="value">{{ $quizPercent !== null ? '%' . number_format($quizPercent, 1, ',', '.') : '-' }}</div>
                <div class="detail">
                    @if($quizAttempt)
                        Geçme: {{ $quizAttempt->min_correct }} doğru
                    @else
                        Quiz bulunmuyor
                    @endif
                </div>
            </div>
            <div class="result-card">
                <div class="label">Deneme / Dil</div>
                <div class="value">{{ $course->has_quiz ? $assignment->quizAttempts->count() : '-' }}</div>
                <div class="detail">{{ $course->language_label }}</div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Eğitmen Değerlendirmesi / Açıklamalar</div>
        <div class="notes">
            <textarea class="line-input" placeholder="Eğitimle ilgili değerlendirme, gözlem veya ek açıklamalar..."></textarea>
        </div>
    </section>

    <section class="section">
        <div class="section-title">Onay ve İmzalar</div>
        <div class="signature-grid">
            <div class="signature-card">
                <div class="signature-heading">
                    <span>EĞİTMEN İMZASI</span>
                    <button type="button" class="signature-clear" data-clear-signature="trainerSignature">İmzayı Temizle</button>
                </div>
                <canvas id="trainerSignature" class="signature-pad" width="900" height="260"></canvas>
                <div class="signature-name">{{ $trainer->name ?? 'Eğitmen Adı Soyadı' }}</div>
            </div>
            <div class="signature-card">
                <div class="signature-heading">
                    <span>KATILIMCI İMZASI</span>
                    <button type="button" class="signature-clear" data-clear-signature="learnerSignature">İmzayı Temizle</button>
                </div>
                <canvas id="learnerSignature" class="signature-pad" width="900" height="260"></canvas>
                <div class="signature-name">{{ $learner->name ?? 'Katılımcı Adı Soyadı' }}</div>
            </div>
        </div>
    </section>

    <footer class="document-footer">
        <div>BHS/İKA-F001 Eğitim Katılım ve Tamamlama Formu</div>
        <div>Rev:01</div>
        <div>Belge No: {{ $documentNumber }} &nbsp;|&nbsp; Sayfa 1 / 1</div>
    </footer>
</main>

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
    window.addEventListener('beforeprint', () => document.activeElement?.blur());
})();
</script>
</body>
</html>
