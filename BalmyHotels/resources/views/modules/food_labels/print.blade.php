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

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto 20px;
            padding: 8mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            display: flex;
            flex-wrap: wrap;
            gap: 5mm;
            align-content: flex-start;
            page-break-after: always;
        }

        .a4-page:last-child { page-break-after: auto; }

        /* =====================================================
           LABEL KARTI
           ===================================================== */
        .label-card {
            width: 60mm;
            height: 90mm;
            border: 0.5pt solid #d1d5db;
            border-radius: 3mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #fff;
            position: relative;
        }

        /* Üst renk bandı (kategori rengi) */
        .label-top {
            height: 7mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2mm;
            flex-shrink: 0;
        }

        .label-top .cat-name {
            font-size: 5pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.9);
        }

        .label-top .diet-badges {
            display: flex;
            gap: 1mm;
            align-items: center;
        }

        .diet-badge {
            font-size: 6pt;
            line-height: 1;
        }

        /* İsim alanı */
        .label-name {
            padding: 2mm 2.5mm 1mm;
            flex-shrink: 0;
        }

        .name-tr {
            font-size: 8pt;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.2;
            margin-bottom: 1mm;
        }

        .name-other {
            font-size: 5.5pt;
            color: #4b5563;
            line-height: 1.3;
        }

        .name-other span { display: block; }

        /* Kalori */
        .label-calories {
            padding: 0 2.5mm 1.5mm;
            flex-shrink: 0;
        }

        .cal-pill {
            display: inline-flex;
            align-items: center;
            gap: 1mm;
            background: #fff8e1;
            border: 0.5pt solid #f9a825;
            border-radius: 2mm;
            padding: 0.5mm 2mm;
            font-size: 5.5pt;
            font-weight: 700;
            color: #b45309;
        }

        /* Allerjen alanı */
        .label-allergens {
            padding: 0 2mm 1.5mm;
            flex-shrink: 0;
        }

        .allergen-title {
            font-size: 4.5pt;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1mm;
        }

        .allergen-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8mm;
        }

        .allergen-box {
            width: 7.5mm;
            height: 7.5mm;
            border-radius: 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            line-height: 1;
            position: relative;
            border: 0.4pt solid;
        }

        .allergen-box.present {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .allergen-box.absent {
            background: #f9fafb;
            border-color: #e5e7eb;
            opacity: 0.3;
        }

        .allergen-box .eu-num {
            font-size: 3.5pt;
            font-weight: 700;
            color: #6b7280;
            line-height: 1;
            margin-top: 0.3mm;
        }

        .allergen-box.present .eu-num { color: #b91c1c; }

        /* İçindekiler */
        .label-ingredients {
            padding: 0 2.5mm;
            flex: 1;
            overflow: hidden;
        }

        .ing-title {
            font-size: 4.5pt;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.5mm;
        }

        .ing-list {
            font-size: 4.5pt;
            color: #374151;
            line-height: 1.4;
        }

        /* Allerjen referans numaraları */
        .label-bottom {
            padding: 1.5mm 2.5mm;
            border-top: 0.5pt solid #f3f4f6;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1mm;
            background: linear-gradient(135deg, #fdfcfb 0%, #f9f5f0 100%);
        }

        .card-logo {
            height: 5mm;
            width: auto;
            opacity: 0.65;
            flex-shrink: 0;
            filter: sepia(0.3) saturate(1.5);
        }

        .allergen-refs {
            font-size: 4pt;
            color: #9ca3af;
            line-height: 1.3;
        }

        .allergen-refs strong {
            color: #ef4444;
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
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                page-break-after: always;
                overflow: hidden;
            }

            @page {
                size: A4 portrait;
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
        {{ ceil($labels->count() / 9) }} sayfa A4
        (sayfa başına 9 isimlik)
    </div>
    <button class="btn-print" onclick="window.print()">
        🖨️ Yazdır / PDF Kaydet
    </button>
    <a href="javascript:history.back()" class="btn-back">← Geri</a>
</div>

@php
    $chunks = $labels->chunk(9);

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

        // Allerjen numaraları (mevcut olanlar)
        $presentNums = collect($labelAllergens)->map(fn($k) => $allergens[$k]['eu'] ?? '')->filter()->sort()->values();

        // Malzemeler — TR önce, sonra EN
        $ing = $label->getIngredients('tr');
        if (empty($ing)) $ing = $label->getIngredients('en');
    @endphp
    <div class="label-card">

        {{-- Üst bant --}}
        <div class="label-top" style="background:{{ $color['bg'] }}">
            <span class="cat-name" style="color:{{ $color['text'] }}">{{ $catName }}</span>
            <div class="diet-badges">
                @if($label->is_vegan)<span class="diet-badge">🌱</span>@endif
                @if($label->is_vegetarian && !$label->is_vegan)<span class="diet-badge">🥗</span>@endif
                @if($label->is_halal)<span class="diet-badge">☪</span>@endif
            </div>
        </div>

        {{-- Yemek adı --}}
        <div class="label-name">
            <div class="name-tr">{{ $nameTr ?: ($nameOthers->first() ?: '—') }}</div>
            @if($nameOthers->isNotEmpty())
            <div class="name-other">
                @foreach($nameOthers as $lang => $n)
                    @php $info = \App\Models\FoodLabel::LANGUAGES[$lang] ?? ['flag'=>'🌐']; @endphp
                    <span>{{ $info['flag'] }} {{ $n }}</span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Kalori --}}
        @if($label->calories)
        <div class="label-calories">
            <span class="cal-pill">🔥 {{ $label->calories }} kcal</span>
        </div>
        @endif

        {{-- 14 AB Alerjeni --}}
        <div class="label-allergens">
            <div class="allergen-title">Allerjenler / Allergens</div>
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

        {{-- Malzemeler --}}
        @if(!empty($ing))
        <div class="label-ingredients">
            <div class="ing-title">İçindekiler / Ingredients</div>
            <div class="ing-list">{{ implode(', ', array_slice($ing, 0, 20)) }}{{ count($ing) > 20 ? '...' : '' }}</div>
        </div>
        @endif

        {{-- Alt bilgi: allerjen numaraları + logo --}}
        <div class="label-bottom">
            <div class="allergen-refs">
                @if($presentNums->isNotEmpty())
                    İçerir / Contains: <strong>{{ $presentNums->implode(', ') }}</strong>
                @else
                    <span style="color:#d1d5db">Allerjen içermez / Allergen free</span>
                @endif
            </div>
            <img src="{{ asset('images/logo.svg') }}" class="card-logo" alt="">
        </div>

    </div>
    @endforeach

    {{-- Boş hücre dolgusu (9'u tamamlamak için) --}}
    @for($i = $chunk->count(); $i < 9; $i++)
    <div class="label-card" style="border:0.5pt dashed #e5e7eb;background:transparent"></div>
    @endfor
</div>
@endforeach

</body>
</html>
