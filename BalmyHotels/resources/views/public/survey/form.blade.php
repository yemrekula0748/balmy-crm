<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ \App\Models\Survey::AVAILABLE_LANGUAGES[$lang]['dir'] ?? 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->getTitle($lang) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: #f3f4f8;
            padding: 0 0 60px;
        }
        .survey-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff; padding: 40px 20px 80px;
            text-align: center;
        }
        .survey-header h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
        .survey-header p  { opacity: 0.75; font-size: 0.95rem; line-height: 1.5; }
        .survey-body { max-width: 680px; margin: -50px auto 0; padding: 0 16px; }
        .progress-bar-wrap { background: rgba(255,255,255,0.2); border-radius: 4px; height: 6px; margin-top: 20px; overflow:hidden; }
        .progress-bar-fill { height: 100%; background: #7dd3fc; border-radius: 4px; transition: width 0.3s; }

        /* Question Card */
        .q-card {
            background: #fff; border-radius: 16px; padding: 24px;
            margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s;
        }
        .q-card:focus-within { box-shadow: 0 4px 20px rgba(67,97,238,0.12); }
        .q-num { display: inline-block; background: #4361ee; color: #fff; font-size: 12px; font-weight: 700;
                 padding: 2px 8px; border-radius: 20px; margin-bottom: 10px; }
        .q-text { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; line-height: 1.5; }
        .q-required { color: #ef4444; margin-left: 4px; }

        /* Text inputs */
        .q-input, .q-textarea {
            width: 100%; padding: 12px 14px;
            border: 2px solid #e5e7eb; border-radius: 10px;
            font-size: 0.95rem; outline: none; transition: border 0.2s;
            font-family: inherit;
        }
        .q-input:focus, .q-textarea:focus { border-color: #4361ee; }
        .q-textarea { resize: vertical; min-height: 100px; }

        /* Radio / Checkbox */
        .option-list { display: flex; flex-direction: column; gap: 10px; }
        .option-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;
            cursor: pointer; transition: all 0.15s; user-select: none;
        }
        .option-item:hover { border-color: #4361ee; background: #f0f3ff; }
        .option-item input { display: none; }
        .option-item .mark {
            width: 20px; height: 20px; flex-shrink: 0;
            border: 2px solid #d1d5db; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .option-item.checkbox .mark { border-radius: 6px; }
        .option-item.selected { border-color: #4361ee; background: #eef2ff; }
        .option-item.selected .mark { background: #4361ee; border-color: #4361ee; }
        .option-item.selected .mark::after { content: '✓'; color: #fff; font-size: 12px; font-weight: 700; }
        .option-label { font-size: 0.95rem; color: #374151; }

        /* Star Rating */
        .star-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .star-btn {
            font-size: 2.2rem; cursor: pointer; color: #d1d5db;
            transition: color 0.15s, transform 0.1s; line-height: 1;
            background: none; border: none; padding: 0;
        }
        .star-btn:hover, .star-btn.lit { color: #fbbf24; }
        .star-btn:hover { transform: scale(1.15); }
        .star-label { font-size: 0.85rem; color: #6b7280; margin-top: 6px; }

        /* NPS */
        .nps-row { display: flex; gap: 4px; flex-wrap: wrap; }
        .nps-btn {
            width: 42px; height: 42px; border: 2px solid #e5e7eb; border-radius: 8px;
            background: #fff; font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: all 0.15s; color: #374151;
        }
        .nps-btn:hover { border-color: #4361ee; background: #eef2ff; }
        .nps-btn.selected { background: #4361ee; border-color: #4361ee; color: #fff; }
        .nps-labels { display: flex; justify-content: space-between; margin-top: 4px; }
        .nps-labels small { font-size: 0.75rem; color: #9ca3af; }

        /* Submit button */
        .submit-btn {
            width: 100%; padding: 16px; border: none; border-radius: 14px;
            background: linear-gradient(135deg,#4361ee,#3a0ca3); color: #fff;
            font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 8px;
            transition: opacity 0.2s, transform 0.1s; letter-spacing: 0.3px;
        }
        .submit-btn:hover { opacity: 0.92; transform: translateY(-1px); }
        .submit-btn:active { transform: translateY(0); }
        .lang-switch { position: fixed; top: 16px; right: 16px; }
        .lang-switch a { background: rgba(255,255,255,0.15); color: #fff; padding: 6px 12px;
                          border-radius: 20px; text-decoration: none; font-size: 0.85rem; backdrop-filter: blur(4px); }
        .q-card.hidden { display: none; }
        .error-msg { color: #ef4444; font-size: 0.83rem; margin-top: 6px; }
    </style>
</head>
<body>

    {{-- Dil değiştirme butonu --}}
    @if(count($survey->languages) > 1)
    <div class="lang-switch">
        <a href="{{ route('surveys.public.splash', $survey->slug) }}">
            🌐 {{ \App\Models\Survey::AVAILABLE_LANGUAGES[$lang]['name'] ?? strtoupper($lang) }}
        </a>
    </div>
    @endif

    <div class="survey-header">
        <div style="font-size:2.5rem;margin-bottom:12px">📋</div>
        <h1>{{ $survey->getTitle($lang) }}</h1>
        @if($desc = $survey->getDescription($lang))
            <p>{{ $desc }}</p>
        @endif
        <div class="progress-bar-wrap" style="max-width:400px;margin:16px auto 0">
            <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
        </div>
    </div>

    <div class="survey-body">
        <form action="{{ route('surveys.public.submit', [$survey->slug, $lang]) }}" method="POST" id="surveyForm">
            @csrf

            @php $visibleCount = 0; @endphp
            @foreach($survey->questions as $i => $q)
                @php
                    $isConditional = $q->conditional_question_id != null;
                    $condQuestion  = $isConditional ? $survey->questions->firstWhere('id', $q->conditional_question_id) : null;
                    $visibleCount++;
                @endphp
                <div class="q-card {{ $isConditional ? 'hidden' : '' }}"
                     id="qcard_{{ $q->id }}"
                     @if($isConditional)
                         data-cond-question="{{ $q->conditional_question_id }}"
                         data-cond-value="{{ $q->conditional_answer_value }}"
                     @endif>

                    <div class="q-num">{{ $i + 1 }}</div>
                    <p class="q-text">
                        {{ $q->getText($lang) }}
                        @if($q->is_required)<span class="q-required">*</span>@endif
                    </p>

                    @if($q->type === 'text')
                        <input type="text" name="q_{{ $q->id }}" class="q-input"
                               placeholder="Yanıtınızı buraya yazın..."
                               @if($q->is_required) required @endif
                               oninput="updateProgress()">

                    @elseif($q->type === 'textarea')
                        <textarea name="q_{{ $q->id }}" class="q-textarea"
                                  placeholder="Yanıtınızı buraya yazın..."
                                  @if($q->is_required) required @endif
                                  oninput="updateProgress()"></textarea>

                    @elseif($q->type === 'radio')
                        <div class="option-list">
                            @foreach($q->getOptions($lang) as $opt)
                            <label class="option-item" onclick="selectOption(this,'radio')">
                                <input type="radio" name="q_{{ $q->id }}" value="{{ $opt }}"
                                       @if($q->is_required) required @endif
                                       onchange="updateProgress(); triggerConditional({{ $q->id }}, this.value)">
                                <span class="mark"></span>
                                <span class="option-label">{{ $opt }}</span>
                            </label>
                            @endforeach
                        </div>

                    @elseif($q->type === 'checkbox')
                        <div class="option-list">
                            @foreach($q->getOptions($lang) as $opt)
                            <label class="option-item checkbox" onclick="selectOption(this,'checkbox')">
                                <input type="checkbox" name="q_{{ $q->id }}[]" value="{{ $opt }}"
                                       onchange="updateProgress()">
                                <span class="mark"></span>
                                <span class="option-label">{{ $opt }}</span>
                            </label>
                            @endforeach
                        </div>

                    @elseif($q->type === 'rating')
                        <input type="hidden" name="q_{{ $q->id }}" id="rating_val_{{ $q->id }}" value="">
                        <div class="star-row" id="stars_{{ $q->id }}">
                            @for($s=1;$s<=5;$s++)
                            <button type="button" class="star-btn" data-val="{{ $s }}"
                                    onclick="setRating({{ $q->id }}, {{ $s }})">★</button>
                            @endfor
                        </div>
                        <div class="star-label">1 = Çok Kötü, 5 = Mükemmel</div>

                    @elseif($q->type === 'nps')
                        <input type="hidden" name="q_{{ $q->id }}" id="nps_val_{{ $q->id }}" value="">
                        <div class="nps-row" id="nps_{{ $q->id }}">
                            @for($n=0;$n<=10;$n++)
                            <button type="button" class="nps-btn" data-val="{{ $n }}"
                                    onclick="setNps({{ $q->id }}, {{ $n }})">{{ $n }}</button>
                            @endfor
                        </div>
                        <div class="nps-labels">
                            <small>😞 Kesinlikle Hayır</small>
                            <small>😍 Kesinlikle Evet</small>
                        </div>
                    @endif

                    <div class="error-msg" id="err_{{ $q->id }}" style="display:none">Bu alan zorunludur.</div>
                </div>
            @endforeach

            <button type="submit" class="submit-btn" onclick="return validateForm()">
                <i class="fas fa-paper-plane me-2"></i>
                @php
                    $submitLabels = ['tr'=>'Anketi Gönder','en'=>'Submit','de'=>'Senden','ru'=>'Отправить','ar'=>'إرسال','fr'=>'Envoyer'];
                @endphp
                {{ $submitLabels[$lang] ?? 'Gönder' }}
            </button>

            <p style="text-align:center;color:#9ca3af;font-size:0.8rem;margin-top:16px">
                <i class="fas fa-shield-alt me-1"></i>
                Yanıtlarınız gizli tutulur.
            </p>
        </form>
    </div>

<script>
// Conditional logic data
const conditionalCards = {};
document.querySelectorAll('.q-card[data-cond-question]').forEach(card => {
    const qId  = card.getAttribute('data-cond-question');
    const val  = card.getAttribute('data-cond-value');
    if (!conditionalCards[qId]) conditionalCards[qId] = [];
    conditionalCards[qId].push({ card, expectedValue: val });
});

function triggerConditional(questionId, selectedValue) {
    const deps = conditionalCards[questionId] || [];
    deps.forEach(({ card, expectedValue }) => {
        const show = selectedValue.trim().toLowerCase() === expectedValue.trim().toLowerCase();
        card.classList.toggle('hidden', !show);
    });
    updateProgress();
}

function selectOption(label, type) {
    if (type === 'radio') {
        const allInGroup = label.closest('.option-list').querySelectorAll('.option-item');
        allInGroup.forEach(l => l.classList.remove('selected'));
    }
    label.classList.toggle('selected', type === 'radio' || label.querySelector('input').checked);
    setTimeout(updateProgress, 50);
}

function setRating(qId, val) {
    document.getElementById('rating_val_' + qId).value = val;
    const stars = document.querySelectorAll('#stars_' + qId + ' .star-btn');
    stars.forEach(s => s.classList.toggle('lit', parseInt(s.dataset.val) <= val));
    updateProgress();
}

function setNps(qId, val) {
    document.getElementById('nps_val_' + qId).value = val;
    const btns = document.querySelectorAll('#nps_' + qId + ' .nps-btn');
    btns.forEach(b => b.classList.toggle('selected', parseInt(b.dataset.val) === val));
    updateProgress();
}

function updateProgress() {
    const cards = [...document.querySelectorAll('.q-card:not(.hidden)')];
    let answered = 0;
    cards.forEach(card => {
        const id = card.id.replace('qcard_', '');
        const inputs = card.querySelectorAll('input[name],textarea[name]');
        let hasValue = false;
        inputs.forEach(inp => {
            if (inp.type === 'radio' && inp.checked) hasValue = true;
            if (inp.type === 'checkbox' && inp.checked) hasValue = true;
            if (inp.type === 'hidden' && inp.value) hasValue = true;
            if ((inp.type === 'text' || inp.tagName === 'TEXTAREA') && inp.value.trim()) hasValue = true;
        });
        if (hasValue) answered++;
    });
    const pct = cards.length > 0 ? Math.round((answered / cards.length) * 100) : 0;
    document.getElementById('progressFill').style.width = pct + '%';
}

function validateForm() {
    let valid = true;
    document.querySelectorAll('.q-card:not(.hidden)').forEach(card => {
        const id = card.id.replace('qcard_', '');
        const errEl = document.getElementById('err_' + id);
        if (!errEl) return;
        errEl.style.display = 'none';

        // Check required
        const reqInputs = card.querySelectorAll('[required]');
        if (reqInputs.length === 0) return; // not required

        let answered = false;
        const allInputs = card.querySelectorAll('input[name],textarea[name]');
        allInputs.forEach(inp => {
            if (inp.type === 'radio' && inp.checked) answered = true;
            if (inp.type === 'text' && inp.value.trim()) answered = true;
            if (inp.type === 'hidden' && inp.value) answered = true;
            if (inp.tagName === 'TEXTAREA' && inp.value.trim()) answered = true;
        });
        const checkedBoxes = card.querySelectorAll('input[type=checkbox]:checked');
        if (checkedBoxes.length > 0) answered = true;

        if (!answered) {
            errEl.style.display = 'block';
            if (valid) { card.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            valid = false;
        }
    });
    return valid;
}
</script>
</body>
</html>
