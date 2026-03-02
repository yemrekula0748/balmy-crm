<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek İsimlik Baskı</title>
    <style>
        /* =====================================================
           EKRAN PREVİEW
           ===================================================== */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #e9ecef;
            padding: 20px;
        }

        .print-toolbar {
            text-align: center;
            margin-bottom: 20px;
            padding: 16px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .print-toolbar h5 {
            margin-bottom: 8px;
            font-size: 16px;
            color: #1a1a2e;
        }

        .print-toolbar .meta {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 12px;
        }

        /* Yatay A4 sayfa */
        .a4-page {
            width: 297mm;
            min-height: 210mm;
            background: #fff;
            margin: 0 auto 20px;
            padding: 8mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            display: flex;
            flex-wrap: wrap;
            gap: 2.5mm;
            align-content: flex-start;
            page-break-after: always;
        }

        .a4-page:last-child { page-break-after: auto; }

        /* =====================================================
           LABEL KARTI — 138mm × 95mm  (sayfa başına 4 kart, 2×2)
           ===================================================== */
        .label-card {
            width: 138mm;
            height: 95mm;
            border: 0.5pt solid #d1d5db;
            border-radius: 2.5mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        /* Üst renk bandı */
        .label-top {
            height: 9mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5mm;
            flex-shrink: 0;
        }

        .label-top .cat-name {
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.95);
        }

        .label-top .diet-badges { display: flex; gap: 1mm; align-items: center; }
        .diet-badge { font-size: 10pt; line-height: 1; }

        .diet-pills { display: flex; flex-wrap: wrap; gap: 0.8mm; }
        .diet-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5mm;
            background: #f0fdf4;
            border: 0.4pt solid #86efac;
            border-radius: 1.5mm;
            padding: 0.3mm 1.2mm;
            font-size: 6pt;
            font-weight: 700;
            color: #166534;
            white-space: nowrap;
        }
        .diet-pill.halal {
            background: #fefce8;
            border-color: #fde047;
            color: #713f12;
        }

        /* Gövde: sol + sağ iki sütun */
        .label-body {
            display: flex;
            flex-direction: row;
            flex: 1;
            overflow: hidden;
            border-bottom: 0.5pt solid #f3f4f6;
        }

        .label-left {
            flex: 1;
            padding: 3mm 3mm 2mm 3.5mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 1.2mm;
            min-height: 0;
        }

        .name-tr {
            font-size: 15pt;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.2;
        }

        .name-other { font-size: 10pt; color: #4b5563; line-height: 1.35; }
        .name-other span { display: block; }

        .cal-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.8mm;
            background: #fff8e1;
            border: 0.5pt solid #f9a825;
            border-radius: 1.5mm;
            padding: 0.6mm 2mm;
            font-size: 7.5pt;
            font-weight: 700;
            color: #b45309;
        }

        .ing-title {
            font-size: 5.5pt;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.5mm;
        }

        .ing-text {
            font-size: 5.5pt;
            color: #6b7280;
            line-height: 1.45;
            overflow: hidden;
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
        }
        .ing-text .lang-row { display: flex; align-items: baseline; gap: 0.8mm; overflow: hidden; }
        .ing-text .lang-row span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0; }
        .ing-text .lang-code { font-size: 4.8pt; font-weight: 800; color: #9ca3af; letter-spacing: 0.2px; min-width: 5mm; flex-shrink: 0; }

        /* Sağ sütun: allerjen ızgarası */
        .label-right {
            width: 46mm;
            flex-shrink: 0;
            padding: 3mm 3mm 2mm 2mm;
            border-left: 0.5pt solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 1mm;
        }

        .allergen-title {
            font-size: 6pt;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .allergen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(8mm, 1fr));
            gap: 1mm;
        }

        .allergen-box {
            height: 9mm;
            border-radius: 1.2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 11pt;
            line-height: 1;
            border: 0.4pt solid;
        }

        .allergen-box.present { background: #fef2f2; border-color: #fca5a5; }
        .allergen-box.absent  { background: #f9fafb; border-color: #e5e7eb; opacity: 0.25; }

        .allergen-box .eu-num {
            font-size: 4.5pt;
            font-weight: 700;
            color: #6b7280;
            line-height: 1;
            margin-top: 0.2mm;
        }

        .allergen-box.present .eu-num { color: #b91c1c; }

        /* Alt şerit: allerjen refs + logo + QR */
        .label-bottom {
            height: 20mm;
            padding: 2mm 3mm 2mm 3.5mm;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5mm;
            background: linear-gradient(135deg, #fdfcfb 0%, #f9f5f0 100%);
        }

        .allergen-refs { font-size: 5.5pt; color: #9ca3af; line-height: 1.45; flex: 1; overflow: hidden; }
        .allergen-refs .lang-row { display: flex; align-items: baseline; gap: 0.8mm; margin-bottom: 0.2mm; }
        .allergen-refs .lang-code { font-size: 4.8pt; font-weight: 800; color: #6b7280; letter-spacing: 0.2px; min-width: 5mm; flex-shrink: 0; }
        .allergen-refs strong { color: #ef4444; font-weight: 600; }

        .bottom-right { display: flex; align-items: center; gap: 2mm; flex-shrink: 0; }

        .card-logo {
            height: 12mm;
            width: auto;
            opacity: 0.6;
            flex-shrink: 0;
            filter: sepia(0.3) saturate(1.5);
        }

        .qr-container { width: 16mm; height: 16mm; flex-shrink: 0; }
        .qr-container canvas, .qr-container img {
            width: 16mm !important;
            height: 16mm !important;
            display: block;
        }

        /* =====================================================
           BUTONLAR (sadece ekran)
           ===================================================== */
        .btn-print {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: #fff;
            border: none;
            padding: 10px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 8px;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 15px;
            text-decoration: none;
            display: inline-block;
        }

        /* =====================================================
           BASKIYA ÖZEL CSS
           ===================================================== */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }

            .print-toolbar { display: none; }

            .a4-page {
                width: 297mm;
                height: 210mm;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                page-break-after: always;
                overflow: hidden;
            }

            /* İçindekiler başlığı */
            .ing-title {
                color: #111;
                font-weight: 900;
                font-size: 6pt;
            }

            /* İçindekiler satırları */
            .ing-text {
                color: #111;
                font-weight: 700;
                font-size: 6pt;
            }

            /* Dil kodu (TR / GB / DE / RU) */
            .ing-text .lang-code {
                color: #000;
                font-weight: 900;
                font-size: 5pt;
            }

            /* Allerjen alt-şerit metni */
            .allergen-refs {
                color: #333;
                font-weight: 700;
                font-size: 6pt;
            }

            .allergen-refs .lang-code {
                color: #000;
                font-weight: 900;
                font-size: 5pt;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

{{-- Ekran toolbar --}}
<div class="print-toolbar" id="toolbar">
    <h5>🖨️ Yemek İsimlik Baskı Önizleme</h5>
    <div class="meta">
        {{ $labels->count() }} isimlik —
        {{ ceil($labels->count() / 4) }} sayfa (yatay A4, sayfa başına 4 kart)
    </div>
    <button class="btn-print" onclick="window.print()">
        🖨️ Yazdır / PDF Kaydet
    </button>
    <a href="javascript:history.back()" class="btn-back">← Geri</a>
</div>

@php
    $chunks = $labels->chunk(4);

    // Kategori renkleri
    $catColors = [
        'soup'      => ['bg' => '#0ea5e9', 'text' => '#fff'],
        'salad'     => ['bg' => '#22c55e', 'text' => '#fff'],
        'appetizer' => ['bg' => '#f59e0b', 'text' => '#fff'],
        'main'      => ['bg' => '#4361ee', 'text' => '#fff'],
        'side'      => ['bg' => '#8b5cf6', 'text' => '#fff'],
        'dessert'   => ['bg' => '#ec4899', 'text' => '#fff'],
        'beverage'  => ['bg' => '#06b6d4', 'text' => '#fff'],
        'breakfast' => ['bg' => '#f97316', 'text' => '#fff'],
        'other'     => ['bg' => '#6b7280', 'text' => '#fff'],
        ''          => ['bg' => '#4361ee', 'text' => '#fff'],
    ];

    $allergens = \App\Models\FoodLabel::ALLERGENS;
@endphp

@foreach($chunks as $chunk)
<div class="a4-page">
    @foreach($chunk as $label)
    @php
        $catKey    = $label->category ?? '';
        $color     = $catColors[$catKey] ?? $catColors[''];
        $catName   = \App\Models\FoodLabel::CATEGORIES[$catKey] ?? 'Yemek';
        $labelAllergens = $label->allergens ?? [];

        // İsim dilleri — TR hariç 5'e kadar
        $names     = $label->name ?? [];
        $nameTr    = $names['tr'] ?? '';
        $nameOthers = collect($names)->filter(fn($v, $k) => $k !== 'tr' && !empty($v))->take(4);

        // Mevcut allerjen adları — 4 dil
        $presentByLang = [
            'TR' => collect($labelAllergens)->map(fn($k) => $allergens[$k]['label']    ?? '')->filter()->values(),
            'GB' => collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_en'] ?? '')->filter()->values(),
            'DE' => collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_de'] ?? '')->filter()->values(),
            'RU' => collect($labelAllergens)->map(fn($k) => $allergens[$k]['label_ru'] ?? '')->filter()->values(),
        ];

        // Malzemeler — 4 dil (sadece gerçekten dolu olanlar)
        $rawIng = $label->ingredients ?? [];
        $ingByLang = collect([
            'TR' => $rawIng['tr'] ?? [],
            'GB' => $rawIng['en'] ?? [],
            'DE' => $rawIng['de'] ?? [],
            'RU' => $rawIng['ru'] ?? [],
        ])->filter(fn($v) => !empty($v));
    @endphp
    <div class="label-card">

        {{-- Üst bant --}}
        <div class="label-top" style="background:{{ $color['bg'] }}">
            <span class="cat-name" style="color:{{ $color['text'] }}">{{ $catName }}</span>
        </div>

        {{-- Gövde: sol (isim/kalori/malzeme) + sağ (allerjenler) --}}
        <div class="label-body">

            {{-- Sol sütun --}}
            <div class="label-left">
                <div class="name-tr">{{ $nameTr ?: ($nameOthers->first() ?: '—') }}</div>
                @if($nameOthers->isNotEmpty())
                <div class="name-other">
                    @foreach($nameOthers as $lang => $n)
                        @php $info = \App\Models\FoodLabel::LANGUAGES[$lang] ?? ['flag'=>'🌐']; @endphp
                        <span>{{ $info['flag'] }} {{ $n }}</span>
                    @endforeach
                </div>
                @endif
                @if($label->is_vegan || $label->is_vegetarian || $label->is_halal)
                <div class="diet-pills">
                    @if($label->is_vegan)<span class="diet-pill">🌱 Vegan</span>@endif
                    @if($label->is_vegetarian && !$label->is_vegan)<span class="diet-pill">🥗 Vejetaryen</span>@endif
                    @if($label->is_halal)<span class="diet-pill halal">☪ Helal</span>@endif
                </div>
                @endif
                @if($label->calories)
                <div><span class="cal-pill">🔥 {{ $label->calories }} kcal</span></div>
                @endif
                @if($ingByLang->isNotEmpty())
                <div class="ing-title">İçindekiler / Ingredients / Zutaten / Состав</div>
                <div class="ing-text">
                    @foreach($ingByLang as $langCode => $ing)
                    <div class="lang-row">
                        <span class="lang-code">{{ $langCode }}</span>
                        <span>{{ implode(', ', array_slice($ing, 0, 10)) }}{{ count($ing) > 10 ? '…' : '' }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Sağ sütun: 14 allerjen --}}
            <div class="label-right">
                <div class="allergen-title">Allergens</div>
                <div class="allergen-grid">
                    @foreach($allergens as $key => $info)
                    <div class="allergen-box {{ in_array($key, $labelAllergens) ? 'present' : 'absent' }}"
                         title="{{ $info['label_en'] }} ({{ $info['label'] }})">
                        <span>{{ $info['icon'] }}</span>
                        <span class="eu-num">{{ $info['eu'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Alt şerit: allerjen refs + logo + QR --}}
        <div class="label-bottom">
            <div class="allergen-refs">
                @if(!empty($labelAllergens))
                    @foreach($presentByLang as $langCode => $names)
                        @if($names->isNotEmpty())
                        <div class="lang-row">
                            <span class="lang-code">{{ $langCode }}</span>
                            <strong>{{ $names->implode(', ') }}</strong>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
            <div class="bottom-right">
                <img src="{{ asset('images/logo.svg') }}" class="card-logo" alt="">
                <div class="qr-container" id="qr-{{ $label->id }}"
                     data-url="{{ route('food-labels.public', $label->qr_token) }}"></div>
            </div>
        </div>

    </div>
    @endforeach

    {{-- Boş hücre dolgusu (9'u tamamlamak için) --}}
    @for($i = $chunk->count(); $i < 9; $i++)
    <div class="label-card" style="border:0.5pt dashed #e5e7eb;background:transparent"></div>
    @endfor
</div>
@endforeach

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.querySelectorAll('.qr-container[data-url]').forEach(function(el) {
        new QRCode(el, {
            text: el.dataset.url,
            width: 120,
            height: 120,
            colorDark: '#1a1a2e',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    });
</script>
</body>
</html>
