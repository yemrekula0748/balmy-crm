@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>Personel Anketi Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff-surveys.index') }}">Personel Anketleri</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        // Mevcut soruları JS için hazırla
        $existingQuestions = $staffSurvey->questions->map(function($q, $idx) use ($staffSurvey) {
            // condition_question_id → condition_question_idx
            $condIdx = null;
            if ($q->condition_question_id) {
                $condIdx = $staffSurvey->questions->search(function($pq) use ($q) {
                    return $pq->id === $q->condition_question_id;
                });
                if ($condIdx === false) $condIdx = null;
            }
            return [
                'idx'                   => $idx,
                'type'                  => $q->type,
                'title'                 => $q->title ?? [],
                'options'               => $q->options ?? null,
                'required'              => $q->required,
                'hasCondition'          => !is_null($condIdx) && !is_null($q->condition_answer),
                'condition_question_idx'=> $condIdx,
                'condition_answer'      => $q->condition_answer ?? '',
            ];
        })->values();
    @endphp

    <form id="surveyForm" action="{{ route('staff-surveys.update', $staffSurvey) }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="questions_data" id="questions_data">

        <div class="row g-3 align-items-start">

            {{-- SOL KOLON --}}
            <div class="col-xl-4" style="position:sticky;top:1rem">

                {{-- Genel Ayarlar --}}
                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Genel Ayarlar</h6>
                    </div>
                    <div class="card-body">
                        @if($branches->count() > 1)
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Şube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($staffSurvey->branch_id == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                        @endif

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" @checked($staffSurvey->is_active)>
                            <label class="form-check-label small" for="isActive">Anket Aktif</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_anonymous" id="isAnon" value="1" @checked($staffSurvey->is_anonymous)>
                            <label class="form-check-label small" for="isAnon">Anonim Yanıt</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="show_dept_field" id="showDept" value="1" @checked($staffSurvey->show_dept_field)>
                            <label class="form-check-label small" for="showDept">Departman Alanı Göster</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="show_employee_id_field" id="showEmpId" value="1" @checked($staffSurvey->show_employee_id_field)>
                            <label class="form-check-label small" for="showEmpId">Personel No Alanı Göster</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="allow_multiple" id="allowMulti" value="1" @checked($staffSurvey->allow_multiple)>
                            <label class="form-check-label small" for="allowMulti">Birden Fazla Doldurulabilsin</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_language_select" id="showLangSel" value="1" @checked($staffSurvey->show_language_select)>
                            <label class="form-check-label small" for="showLangSel">Dil Seçim Ekranı İzin</label>
                        </div>
                    </div>
                </div>

                {{-- Dil Seçimi --}}
                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-language me-2 text-primary"></i>Diller</h6>
                    </div>
                    <div class="card-body">
                        @foreach(\App\Models\StaffSurvey::AVAILABLE_LANGUAGES as $code => $info)
                        <div class="form-check mb-2">
                            <input class="form-check-input lang-check" type="checkbox" name="languages[]"
                                   value="{{ $code }}" id="lang_{{ $code }}"
                                   @checked(in_array($code, $staffSurvey->languages ?? []))
                                   onchange="handleLangChange()">
                            <label class="form-check-label" for="lang_{{ $code }}">
                                <span class="me-1">{{ $info['flag'] }}</span>{{ $info['name'] }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Başlık --}}
                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-heading me-2 text-primary"></i>Anket Başlığı</h6>
                    </div>
                    <div class="card-body">
                        <div id="titleTabs"></div>
                        <div id="titleInputs"></div>
                    </div>
                </div>

                {{-- Butonlar --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100" onclick="prepareSubmit()">
                            <i class="fas fa-save me-2"></i>Güncelle
                        </button>
                        <a href="{{ route('staff-surveys.show', $staffSurvey) }}" class="btn btn-outline-secondary w-100 mt-2">
                            İptal
                        </a>
                    </div>
                </div>
            </div>

            {{-- SAĞ KOLON: Sorular --}}
            <div class="col-xl-8">
                <div class="card mb-3">
                    <div class="card-header py-3 d-flex align-items-center">
                        <h6 class="mb-0 fw-bold flex-grow-1"><i class="fas fa-list-ul me-2 text-primary"></i>Sorular</h6>
                        <span class="badge bg-primary rounded-pill" id="qCount">0</span>
                    </div>
                </div>

                <div id="questionsList"></div>

                <div class="card">
                    <div class="card-body">
                        <p class="text-muted small mb-2 fw-semibold">Soru Ekle:</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuestion('text')"><i class="fas fa-font me-1"></i>Kısa Metin</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuestion('textarea')"><i class="fas fa-align-left me-1"></i>Uzun Metin</button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addQuestion('yesno')"><i class="fas fa-toggle-on me-1"></i>Evet / Hayır</button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="addQuestion('radio')"><i class="fas fa-dot-circle me-1"></i>Tekli Seçim</button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="addQuestion('checkbox')"><i class="fas fa-check-square me-1"></i>Çoklu Seçim</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="addQuestion('rating')"><i class="fas fa-star me-1"></i>Puanlama</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="addQuestion('nps')"><i class="fas fa-tachometer-alt me-1"></i>NPS</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const EXIST_TITLE = @json($staffSurvey->title ?? []);
const EXIST_DESC  = @json($staffSurvey->description ?? []);
const EXIST_LANGS = @json($staffSurvey->languages ?? ['tr']);
const EXIST_QUESTIONS = @json($existingQuestions);

let selectedLangs  = [];
let titleState     = {};
let descState      = {};
let questions      = [];
let nextIdx        = 0;
let activeTitleTab = null;

function getSelectedLangs() {
    return [...document.querySelectorAll('.lang-check:checked')].map(el => el.value);
}

function handleLangChange() {
    saveTitleState();
    saveAllQuestionStates();
    selectedLangs = getSelectedLangs();
    if (selectedLangs.length === 0) {
        document.getElementById('lang_tr').checked = true;
        selectedLangs = ['tr'];
    }
    renderTitleTabs();
    renderAllQuestions();
}

function saveTitleState() {
    selectedLangs.forEach(lang => {
        const ti = document.getElementById(`title_${lang}`);
        const di = document.getElementById(`desc_${lang}`);
        if (ti) titleState[lang] = ti.value;
        if (di) descState[lang]  = di.value;
    });
}

function renderTitleTabs() {
    if (selectedLangs.length === 0) return;
    if (!activeTitleTab || !selectedLangs.includes(activeTitleTab)) activeTitleTab = selectedLangs[0];

    const LANGS = @json(\App\Models\StaffSurvey::AVAILABLE_LANGUAGES);
    let tabs = '<ul class="nav nav-tabs mb-2" style="font-size:12px">';
    selectedLangs.forEach(lang => {
        tabs += `<li class="nav-item"><a class="nav-link py-1 px-2 ${lang===activeTitleTab?'active':''}"
            href="#" onclick="switchTitleTab('${lang}');return false">
            ${LANGS[lang]?.flag||''} ${LANGS[lang]?.name||lang}</a></li>`;
    });
    tabs += '</ul>';

    let inputs = '';
    selectedLangs.forEach(lang => {
        const disp = lang===activeTitleTab?'':'display:none';
        inputs += `<div id="titleBlock_${lang}" style="${disp}">
            <input type="text" name="title[${lang}]" id="title_${lang}"
                   class="form-control form-control-sm mb-2"
                   placeholder="Anket Başlığı (${lang.toUpperCase()})"
                   value="${escHtml(titleState[lang] ?? EXIST_TITLE[lang] ?? '')}">
            <textarea name="description[${lang}]" id="desc_${lang}"
                      class="form-control form-control-sm" rows="2"
                      placeholder="Açıklama (${lang.toUpperCase()}, opsiyonel)">${escHtml(descState[lang] ?? EXIST_DESC[lang] ?? '')}</textarea>
        </div>`;
    });

    document.getElementById('titleTabs').innerHTML   = tabs;
    document.getElementById('titleInputs').innerHTML = inputs;
}

function switchTitleTab(lang) {
    saveTitleState();
    activeTitleTab = lang;
    renderTitleTabs();
}

const TYPE_LABELS = {
    text:'Kısa Metin',textarea:'Uzun Metin',yesno:'Evet / Hayır',
    radio:'Tekli Seçim',checkbox:'Çoklu Seçim',rating:'Puanlama (1-5)',nps:'NPS (0-10)'
};

function addQuestion(type) {
    saveTitleState();
    saveAllQuestionStates();
    questions.push({ idx:nextIdx++, type, qTitleState:{}, qOptionsState:{},
        activeQTab:selectedLangs[0]||'tr', required:false,
        hasCondition:false, condition_question_idx:null, condition_answer:'' });
    renderAllQuestions();
}

function removeQuestion(idx) {
    questions = questions.filter(q => q.idx !== idx);
    renderAllQuestions();
}

function moveQuestion(idx, dir) {
    const i = questions.findIndex(q => q.idx === idx);
    const j = i + dir;
    if (j < 0 || j >= questions.length) return;
    saveAllQuestionStates();
    [questions[i], questions[j]] = [questions[j], questions[i]];
    renderAllQuestions();
}

function saveAllQuestionStates() {
    questions.forEach(q => {
        selectedLangs.forEach(lang => {
            const ti = document.getElementById(`qtitle_${q.idx}_${lang}`);
            const oi = document.getElementById(`qopts_${q.idx}_${lang}`);
            if (ti) q.qTitleState[lang]   = ti.value;
            if (oi) q.qOptionsState[lang] = oi.value;
        });
        const at = document.querySelector(`#qcard_${q.idx} .nav-link.active`);
        if (at) q.activeQTab = at.dataset.lang;
        const req  = document.getElementById(`qreq_${q.idx}`);
        if (req)  q.required = req.checked;
        const cc   = document.getElementById(`qcondchk_${q.idx}`);
        if (cc)   q.hasCondition = cc.checked;
        const cs   = document.getElementById(`qcondsel_${q.idx}`);
        if (cs)   q.condition_question_idx = cs.value !== '' ? parseInt(cs.value) : null;
        const ca   = document.getElementById(`qcondans_${q.idx}`);
        if (ca)   q.condition_answer = ca.value;
    });
}

function renderAllQuestions() {
    const LANGS = @json(\App\Models\StaffSurvey::AVAILABLE_LANGUAGES);
    const container = document.getElementById('questionsList');
    document.getElementById('qCount').textContent = questions.length;

    if (questions.length === 0) {
        container.innerHTML = `<div class="card mb-3"><div class="card-body text-center text-muted py-4">
            Henüz soru eklenmedi.</div></div>`;
        return;
    }

    container.innerHTML = questions.map((q, pos) => {
        const isFirst = pos === 0, isLast = pos === questions.length - 1;
        const activeTab = selectedLangs.includes(q.activeQTab) ? q.activeQTab : (selectedLangs[0]||'tr');

        let tabs = '';
        if (selectedLangs.length > 1) {
            tabs = '<ul class="nav nav-tabs mb-2" style="font-size:11px">';
            selectedLangs.forEach(lang => {
                tabs += `<li class="nav-item"><a class="nav-link py-1 px-2 ${lang===activeTab?'active':''}"
                    data-lang="${lang}" href="#" onclick="switchQTab(${q.idx},'${lang}');return false">
                    ${LANGS[lang]?.flag||''} ${LANGS[lang]?.name||lang}</a></li>`;
            });
            tabs += '</ul>';
        }

        let langInputs = selectedLangs.map(lang => {
            const disp = lang===activeTab?'':'display:none';
            const hasOpts = ['radio','checkbox'].includes(q.type);
            return `<div id="qblock_${q.idx}_${lang}" style="${disp}">
                <input type="text" id="qtitle_${q.idx}_${lang}"
                    class="form-control form-control-sm mb-1"
                    placeholder="Soru metni (${lang.toUpperCase()})"
                    value="${escHtml(q.qTitleState[lang] || '')}">
                ${hasOpts ? `<textarea id="qopts_${q.idx}_${lang}"
                    class="form-control form-control-sm" rows="2"
                    placeholder="Seçenekler (${lang.toUpperCase()})">${escHtml(q.qOptionsState[lang] || '')}</textarea>` : ''}
            </div>`;
        }).join('');

        const prevYR = questions.slice(0, pos).filter(pq => ['yesno','radio'].includes(pq.type));
        const condSection = prevYR.length > 0 ? `
            <div class="mt-2 pt-2 border-top">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" id="qcondchk_${q.idx}"
                           ${q.hasCondition?'checked':''} onchange="toggleCondUI(${q.idx})">
                    <label class="form-check-label" style="font-size:11px" for="qcondchk_${q.idx}">
                        <i class="fas fa-code-branch me-1 text-warning"></i>Koşullu göster
                    </label>
                </div>
                <div id="qcondui_${q.idx}" style="${q.hasCondition?'':'display:none'}">
                    <select class="form-select form-select-sm mb-1" id="qcondsel_${q.idx}">
                        <option value="">— Hangi soruya bağlı? —</option>
                        ${prevYR.map(pq => {
                            const pt = pq.qTitleState[selectedLangs[0]] || pq.qTitleState['tr'] || `Soru ${pq.idx+1}`;
                            const sel = q.condition_question_idx === pq.idx ? 'selected' : '';
                            return `<option value="${pq.idx}" ${sel}>${pt.substring(0,50)||'[Başlıksız]'} (${TYPE_LABELS[pq.type]})</option>`;
                        }).join('')}
                    </select>
                    <input type="text" class="form-control form-control-sm" id="qcondans_${q.idx}"
                           placeholder="Tetikleyen cevap (ör: Evet)"
                           value="${escHtml(q.condition_answer||'')}">
                    <div class="text-muted" style="font-size:10px;margin-top:2px">
                        💡 Evet/Hayır için <strong>Evet</strong> veya <strong>Hayır</strong> yazın.
                    </div>
                </div>
            </div>` : '';

        return `<div class="card mb-2" id="qcard_${q.idx}">
            <div class="card-header py-2 d-flex align-items-center gap-2" style="background:#f8f9fa">
                <span class="badge bg-primary rounded-pill" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center">${pos+1}</span>
                <span class="fw-semibold small flex-grow-1">${TYPE_LABELS[q.type]}</span>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveQuestion(${q.idx},-1)" ${isFirst?'disabled':''}>▲</button>
                    <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveQuestion(${q.idx},1)"  ${isLast?'disabled':''}>▼</button>
                    <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeQuestion(${q.idx})">✕</button>
                </div>
            </div>
            <div class="card-body py-2 px-3">
                ${tabs}
                ${langInputs}
                <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="qreq_${q.idx}" ${q.required?'checked':''}>
                        <label class="form-check-label" style="font-size:11px" for="qreq_${q.idx}">Zorunlu</label>
                    </div>
                    ${q.type==='yesno' ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size:10px">Evet / Hayır</span>' : ''}
                </div>
                ${condSection}
            </div>
        </div>`;
    }).join('');
}

function switchQTab(idx, lang) {
    saveAllQuestionStates();
    const q = questions.find(q => q.idx === idx);
    if (q) q.activeQTab = lang;
    renderAllQuestions();
}

function toggleCondUI(idx) {
    const chk = document.getElementById(`qcondchk_${idx}`);
    const ui  = document.getElementById(`qcondui_${idx}`);
    if (ui) ui.style.display = chk?.checked ? '' : 'none';
}

function prepareSubmit() {
    saveTitleState();
    saveAllQuestionStates();
    const LANGS = selectedLangs.length ? selectedLangs : getSelectedLangs();
    const data = questions.map((q, pos) => {
        const title = {};
        LANGS.forEach(lang => { if (q.qTitleState[lang]) title[lang] = q.qTitleState[lang]; });
        let opts = null;
        if (['radio','checkbox'].includes(q.type)) {
            opts = {};
            LANGS.forEach(lang => {
                const raw = q.qOptionsState[lang] || '';
                opts[lang] = raw.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
            });
        }
        return { idx:pos, type:q.type, title, options:opts, required:q.required,
            condition_question_idx: q.hasCondition ? q.condition_question_idx : null,
            condition_answer:       q.hasCondition ? q.condition_answer : null };
    });
    document.getElementById('questions_data').value = JSON.stringify(data);
}

function escHtml(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ================================================================
// MEVCUT VERİLERİ YÜKlE
// ================================================================
document.addEventListener('DOMContentLoaded', () => {
    selectedLangs = getSelectedLangs();
    EXIST_LANGS.forEach(l => {
        if (!selectedLangs.includes(l)) {
            const cb = document.getElementById(`lang_${l}`);
            if (cb) { cb.checked = true; selectedLangs.push(l); }
        }
    });

    // titleState'i EXIST_TITLE ile başlat
    Object.assign(titleState, EXIST_TITLE);
    Object.assign(descState,  EXIST_DESC);
    activeTitleTab = selectedLangs[0];
    renderTitleTabs();

    // Soruları yükle
    EXIST_QUESTIONS.forEach((eq, i) => {
        const qObj = {
            idx: nextIdx++,
            type: eq.type,
            qTitleState:  {},
            qOptionsState:{},
            activeQTab: selectedLangs[0] || 'tr',
            required: eq.required,
            hasCondition: eq.hasCondition,
            condition_question_idx: eq.condition_question_idx,
            condition_answer: eq.condition_answer || '',
        };
        // Dil bazlı başlık
        Object.keys(eq.title || {}).forEach(lang => { qObj.qTitleState[lang] = eq.title[lang]; });
        // Seçenekler
        const opts = eq.options || {};
        Object.keys(opts).forEach(lang => {
            if (Array.isArray(opts[lang])) qObj.qOptionsState[lang] = opts[lang].join(', ');
        });
        questions.push(qObj);
    });

    renderAllQuestions();
});
</script>
@endpush
