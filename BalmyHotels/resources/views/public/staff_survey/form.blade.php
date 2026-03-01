<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ \App\Models\StaffSurvey::AVAILABLE_LANGUAGES[$lang]['dir'] ?? 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->getTitle($lang) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .survey-wrapper {
            max-width: 680px;
            margin: 0 auto;
            padding: 32px 16px 80px;
        }

        .survey-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .survey-header img {
            height: 48px;
            margin-bottom: 20px;
            filter: brightness(0) invert(1);
            opacity: .85;
        }

        .survey-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .survey-header p {
            color: rgba(255,255,255,.65);
            font-size: 14px;
        }

        .q-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            transition: all .2s;
        }

        .q-card.q-hidden {
            display: none !important;
        }

        .q-card.q-conditional {
            border-color: rgba(249,168,37,.25);
        }

        .q-label {
            font-weight: 700;
            color: #fff;
            font-size: 15px;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .q-label .q-num {
            display: inline-block;
            width: 26px;
            height: 26px;
            background: #4361ee;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            text-align: center;
            line-height: 26px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .q-required { color: #ef4444; margin-left: 4px; }

        /* Metin inputlar */
        .q-card .form-control, .q-card .form-select {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
            border-radius: 10px;
        }
        .q-card .form-control::placeholder { color: rgba(255,255,255,.35); }
        .q-card .form-control:focus { background: rgba(255,255,255,.12); border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,.2); color: #fff; }

        /* Radio / Checkbox */
        .choice-btn {
            display: block;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: 10px;
            padding: 12px 16px;
            color: rgba(255,255,255,.8);
            font-size: 14px;
            cursor: pointer;
            transition: all .15s;
            margin-bottom: 8px;
            user-select: none;
        }
        .choice-btn:hover { background: rgba(255,255,255,.1); border-color: rgba(67,97,238,.4); }
        .choice-btn.selected {
            background: rgba(67,97,238,.2);
            border-color: #4361ee;
            color: #fff;
            font-weight: 600;
        }
        .choice-btn input { display: none; }

        /* Evet/Hayır */
        .yesno-wrap { display: flex; gap: 12px; }
        .yesno-btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.8);
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            cursor: pointer;
            transition: all .15s;
        }
        .yesno-btn:hover { transform: translateY(-2px); }
        .yesno-btn.yes-btn.selected { background: rgba(16,185,129,.2); border-color: #10b981; color: #10b981; }
        .yesno-btn.no-btn.selected  { background: rgba(239,68,68,.2);  border-color: #ef4444; color: #ef4444; }
        .yesno-btn input { display: none; }

        /* Rating yıldızlar */
        .rating-wrap { display: flex; gap: 8px; flex-wrap: wrap; }
        .star-btn {
            width: 48px; height: 48px;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.4);
            font-size: 22px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all .15s;
        }
        .star-btn:hover, .star-btn.selected { background: rgba(249,168,37,.15); border-color: #f9a825; color: #f9a825; }
        .star-btn input { display: none; }

        /* NPS */
        .nps-wrap { display: flex; gap: 4px; flex-wrap: wrap; }
        .nps-btn {
            flex: 1; min-width: 40px; height: 44px;
            border-radius: 8px;
            border: 1.5px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.7);
            font-size: 14px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all .12s;
        }
        .nps-btn:hover { transform: scale(1.05); }
        .nps-btn.selected { border-color: #4361ee; background: rgba(67,97,238,.25); color: #fff; }
        .nps-btn input { display: none; }
        .nps-labels { display: flex; justify-content: space-between; color: rgba(255,255,255,.4); font-size: 11px; margin-top: 6px; }

        /* Personel bilgi alanı */
        .respondent-card {
            background: rgba(67,97,238,.1);
            border: 1px solid rgba(67,97,238,.25);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 16px;
        }

        .respondent-card h6 {
            color: #818cfc;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        /* Koşullu soru göstergesi */
        .cond-indicator {
            display: inline-block;
            font-size: 10px;
            color: #f9a825;
            border: 1px solid rgba(249,168,37,.3);
            border-radius: 4px;
            padding: 1px 6px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s;
            margin-top: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); }
        .btn-submit:active { transform: translateY(0); }
    </style>
</head>
<body>

<div class="survey-wrapper">

    {{-- Logo --}}
    <div class="survey-header">
        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
        <h1>{{ $survey->getTitle($lang) }}</h1>
        @if($desc = $survey->getDescription($lang))
        <p>{{ $desc }}</p>
        @endif
    </div>

    <form action="{{ route('staff-surveys.public.submit', $survey->slug) }}" method="POST" id="surveyForm">
        @csrf
        <input type="hidden" name="lang" value="{{ $lang }}">

        {{-- Personel Bilgileri --}}
        @if(!$survey->is_anonymous || $survey->show_dept_field || $survey->show_employee_id_field)
        <div class="respondent-card">
            <h6><i class="fas fa-user-tie me-2"></i>Personel Bilgileri
                @if($survey->is_anonymous)<span style="font-size:10px;color:#9ca3af;font-weight:400;text-transform:none">(isteğe bağlı)</span>@endif
            </h6>
            @if(!$survey->is_anonymous)
            <div class="mb-2">
                <input type="text" name="respondent_name" class="form-control"
                       placeholder="Ad Soyad" value="{{ old('respondent_name') }}">
            </div>
            @endif
            @if($survey->show_dept_field)
            <div class="mb-2">
                <input type="text" name="respondent_dept" class="form-control"
                       placeholder="Departmanınız" value="{{ old('respondent_dept') }}">
            </div>
            @endif
            @if($survey->show_employee_id_field)
            <div>
                <input type="text" name="respondent_employee_id" class="form-control"
                       placeholder="Personel Numaranız" value="{{ old('respondent_employee_id') }}">
            </div>
            @endif
        </div>
        @endif

        {{-- Sorular --}}
        @php $visibleNum = 0; @endphp
        @foreach($survey->questions as $q)
        @php
            $visibleNum++;
            $isConditional = $q->hasCondition();
        @endphp
        <div class="q-card @if($isConditional) q-conditional q-hidden @endif"
             id="qwrap_{{ $q->id }}"
             @if($isConditional)
                 data-condition-question="{{ $q->condition_question_id }}"
                 data-condition-answer="{{ $q->condition_answer }}"
             @endif>

            <div class="q-label">
                <span class="q-num">{{ $visibleNum }}</span>
                {{ $q->getTitle($lang) }}
                @if($q->required)<span class="q-required">*</span>@endif
                @if($isConditional)<span class="cond-indicator"><i class="fas fa-code-branch"></i></span>@endif
            </div>

            {{-- TEXT / TEXTAREA --}}
            @if($q->type === 'text')
                <input type="text" name="q_{{ $q->id }}" class="form-control"
                       @if($q->required) required @endif>

            @elseif($q->type === 'textarea')
                <textarea name="q_{{ $q->id }}" class="form-control" rows="3"
                          @if($q->required) required @endif></textarea>

            {{-- EVET / HAYIR --}}
            @elseif($q->type === 'yesno')
                <div class="yesno-wrap" data-question-id="{{ $q->id }}">
                    <label class="yesno-btn yes-btn" id="yesBtn_{{ $q->id }}">
                        <input type="radio" name="q_{{ $q->id }}" value="Evet"
                               @if($q->required) required @endif
                               onchange="selectYesNo({{ $q->id }}, 'Evet')">
                        ✓ Evet
                    </label>
                    <label class="yesno-btn no-btn" id="noBtn_{{ $q->id }}">
                        <input type="radio" name="q_{{ $q->id }}" value="Hayır"
                               onchange="selectYesNo({{ $q->id }}, 'Hayır')">
                        ✗ Hayır
                    </label>
                </div>

            {{-- TEKLİ SEÇİM --}}
            @elseif($q->type === 'radio')
                <div data-question-id="{{ $q->id }}">
                    @foreach($q->getOptions($lang) as $opt)
                    <label class="choice-btn" onclick="selectRadio({{ $q->id }}, '{{ addslashes($opt) }}', this)">
                        <input type="radio" name="q_{{ $q->id }}" value="{{ $opt }}"
                               @if($q->required) required @endif>
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

            {{-- ÇOKLU SEÇİM --}}
            @elseif($q->type === 'checkbox')
                <div>
                    @foreach($q->getOptions($lang) as $opt)
                    <label class="choice-btn" onclick="toggleCheckbox(this)">
                        <input type="checkbox" name="q_{{ $q->id }}[]" value="{{ $opt }}">
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

            {{-- PUANLAMA 1-5 --}}
            @elseif($q->type === 'rating')
                <div class="rating-wrap" data-question-id="{{ $q->id }}">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="star-btn" onclick="selectRating({{ $q->id }}, {{ $i }})">
                        <input type="radio" name="q_{{ $q->id }}" value="{{ $i }}"
                               @if($q->required) required @endif>
                        ★
                    </label>
                    @endfor
                </div>
                <div class="nps-labels" style="font-size:11px">
                    <span>😞 Kötü</span><span>😊 Mükemmel</span>
                </div>

            {{-- NPS 0-10 --}}
            @elseif($q->type === 'nps')
                <div class="nps-wrap" data-question-id="{{ $q->id }}">
                    @for($i = 0; $i <= 10; $i++)
                    <label class="nps-btn" onclick="selectNps({{ $q->id }}, {{ $i }})">
                        <input type="radio" name="q_{{ $q->id }}" value="{{ $i }}"
                               @if($q->required) required @endif>
                        {{ $i }}
                    </label>
                    @endfor
                </div>
                <div class="nps-labels">
                    <span>😞 Kesinlikle Tavsiye Etmem</span>
                    <span>😊 Kesinlikle Tavsiye Ederim</span>
                </div>
            @endif

        </div>
        @endforeach

        <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane me-2"></i>Anketi Gönder
        </button>
    </form>
</div>

<script>
// ===============================================================
// 1. SORU SEÇİM FONKSİYONLARI
// ===============================================================
function selectYesNo(qId, val) {
    document.getElementById(`yesBtn_${qId}`).classList.toggle('selected', val === 'Evet');
    document.getElementById(`noBtn_${qId}`).classList.toggle('selected', val === 'Hayır');
    evaluateConditions();
}

function selectRadio(qId, val, clickedLabel) {
    document.querySelectorAll(`[data-question-id="${qId}"] .choice-btn`).forEach(l => l.classList.remove('selected'));
    clickedLabel.classList.add('selected');
    evaluateConditions();
}

function toggleCheckbox(label) {
    const cb = label.querySelector('input[type=checkbox]');
    label.classList.toggle('selected', cb.checked);
}

function selectRating(qId, val) {
    document.querySelectorAll(`[data-question-id="${qId}"] .star-btn`).forEach((btn, i) => {
        btn.classList.toggle('selected', i < val);
    });
    evaluateConditions();
}

function selectNps(qId, val) {
    document.querySelectorAll(`[data-question-id="${qId}"] .nps-btn`).forEach((btn, i) => {
        btn.classList.toggle('selected', i === val);
    });
    evaluateConditions();
}

// ===============================================================
// 2. KOŞULLU SORU MANTIĞI
// ===============================================================
function getQuestionValue(qId) {
    // Radio/yesno
    const checked = document.querySelector(`input[name="q_${qId}"]:checked`);
    if (checked) return checked.value;
    return null;
}

function evaluateConditions() {
    document.querySelectorAll('.q-card[data-condition-question]').forEach(card => {
        const condQId  = card.dataset.conditionQuestion;
        const condAns  = (card.dataset.conditionAnswer || '').trim();
        const curVal   = getQuestionValue(condQId);

        const shouldShow = curVal !== null && curVal.trim() === condAns;
        card.classList.toggle('q-hidden', !shouldShow);

        // Gizlenince input'ları sıfırla
        if (!shouldShow) {
            card.querySelectorAll('input[type=radio],input[type=checkbox]').forEach(inp => { inp.checked = false; });
            card.querySelectorAll('.choice-btn,.yesno-btn,.star-btn,.nps-btn').forEach(b => b.classList.remove('selected'));
            card.querySelectorAll('input[type=text],textarea').forEach(inp => { inp.value = ''; });
            // Kaskad: bu soru gizlenince ona bağlı soruları da gizle
            evaluateConditions();
        }
    });
}

// Sayfa yüklenince değerlendir
document.addEventListener('DOMContentLoaded', evaluateConditions);
</script>
</body>
</html>
