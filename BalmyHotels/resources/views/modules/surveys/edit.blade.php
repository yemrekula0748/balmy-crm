@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>Anketi Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">Anketler</a></li>
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

    <form id="surveyForm" action="{{ route('surveys.update', $survey) }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="questions_data" id="questions_data">

        <div class="row g-3 align-items-start">

            <div class="col-xl-4" style="position:sticky;top:1rem">

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Genel Ayarlar</h6>
                    </div>
                    <div class="card-body">
                        @if($branches->count() > 1)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($survey->branch_id == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                        @endif

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                   value="1" @checked($survey->is_active)>
                            <label class="form-check-label" for="isActive">Anket Aktif</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="show_language_select" id="showLang"
                                   value="1" @checked($survey->show_language_select)>
                            <label class="form-check-label" for="showLang">Dil seçim ekranı göster</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-language me-2 text-primary"></i>Anket Dilleri</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Dili kaldırmak o dildeki soruları silmez, sadece form dışında bırakır.</p>
                        <div id="langCheckboxes">
                            @foreach(\App\Models\Survey::AVAILABLE_LANGUAGES as $code => $info)
                            <div class="form-check mb-2">
                                <input class="form-check-input lang-check" type="checkbox" name="languages[]"
                                       value="{{ $code }}" id="lang_{{ $code }}"
                                       @checked(in_array($code, $survey->languages ?? []))
                                       onchange="handleLangChange()">
                                <label class="form-check-label" for="lang_{{ $code }}">
                                    {{ $info['flag'] }} {{ $info['name'] }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-heading me-2 text-primary"></i>Başlık & Açıklama</h6>
                    </div>
                    <div class="card-body">
                        <div id="surveyTitleTabs"></div>
                    </div>
                </div>

            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Sorular</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addQuestion()">
                            <i class="fas fa-plus me-1"></i>Soru Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="questionsContainer"></div>
                        <div id="emptyMsg" class="text-center text-muted py-5" style="display:none">
                            <i class="fas fa-question-circle fa-3x opacity-25 mb-3 d-block"></i>
                            Henüz soru eklenmedi.
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('surveys.show', $survey) }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary px-4" onclick="prepareSubmit()">
                            <i class="fas fa-save me-2"></i>Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let languages   = [];
let questions   = [];
let qCounter    = 0;
const titleState = {}; // lang → { title, desc }
const activeQTab = {}; // tempId → lang

const AVAILABLE_LANGS = @json(\App\Models\Survey::AVAILABLE_LANGUAGES);
const QUESTION_TYPES  = @json(\App\Models\SurveyQuestion::TYPES);

// Pre-existing data
const EXISTING_TITLE   = @json($survey->title ?? []);
const EXISTING_DESC    = @json($survey->description ?? []);
@php
$existingQuestionsData = $survey->questions->map(function($q) {
    return [
        'temp_id'                  => 'existing_' . $q->id,
        'db_id'                    => $q->id,
        'type'                     => $q->type,
        'is_required'              => $q->is_required,
        'translations'             => $q->translations,
        'conditional_temp_id'      => $q->conditional_question_id ? 'existing_' . $q->conditional_question_id : null,
        'conditional_answer_value' => $q->conditional_answer_value,
    ];
})->values();
@endphp
const EXISTING_QUESTIONS = @json($existingQuestionsData);

document.addEventListener('DOMContentLoaded', () => {
    readLanguages();
    renderTitleTabs();
    // Load existing questions
    questions = JSON.parse(JSON.stringify(EXISTING_QUESTIONS));
    qCounter  = questions.length;
    renderAllQuestions();
});

function readLanguages() {
    languages = [...document.querySelectorAll('.lang-check:checked')].map(c => c.value);
}

function handleLangChange() {
    readLanguages();
    if (languages.length === 0) {
        document.getElementById('lang_tr').checked = true;
        readLanguages();
    }
    renderTitleTabs();
    renderAllQuestions();
}

function renderTitleTabs() {
    saveTitleState();
    const container = document.getElementById('surveyTitleTabs');
    if (!languages.length) { container.innerHTML = '<p class="text-muted small">Lütfen en az bir dil seçin.</p>'; return; }

    let tabs = '<ul class="nav nav-tabs nav-sm mb-3" role="tablist">';
    let content = '<div class="tab-content">';

    languages.forEach((lang, i) => {
        const info  = AVAILABLE_LANGS[lang] || { name: lang.toUpperCase(), flag: '🌐' };
        const active = i === 0 ? 'active' : '';
        const show   = i === 0 ? 'show active' : '';
        const savedTitle = titleState[lang]?.title ?? EXISTING_TITLE[lang] ?? '';
        const savedDesc  = titleState[lang]?.desc  ?? EXISTING_DESC[lang]  ?? '';

        tabs    += `<li class="nav-item"><a class="nav-link ${active} py-1 px-2 small" data-bs-toggle="tab" href="#title_tab_${lang}">${info.flag} ${info.name}</a></li>`;
        content += `<div class="tab-pane fade ${show}" id="title_tab_${lang}">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Başlık <span class="text-danger">*</span></label>
                            <input type="text" name="title[${lang}]" class="form-control form-control-sm"
                                   placeholder="${info.name} dilinde başlık..." value="${escHtml(savedTitle)}"
                                   oninput="saveTitleState()">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Açıklama</label>
                            <textarea name="description[${lang}]" class="form-control form-control-sm" rows="2"
                                      oninput="saveTitleState()">${escHtml(savedDesc)}</textarea>
                        </div>
                    </div>`;
    });

    tabs    += '</ul>';
    content += '</div>';
    container.innerHTML = tabs + content;
}

function saveTitleState() {
    document.querySelectorAll('#surveyTitleTabs input[name^="title["]').forEach(el => {
        const lang = el.name.slice(6, -1);
        if (!titleState[lang]) titleState[lang] = {};
        titleState[lang].title = el.value;
    });
    document.querySelectorAll('#surveyTitleTabs textarea[name^="description["]').forEach(el => {
        const lang = el.name.slice(12, -1);
        if (!titleState[lang]) titleState[lang] = {};
        titleState[lang].desc = el.value;
    });
}

function addQuestion(data = null) {
    qCounter++;
    const tempId = 'q_' + Date.now() + '_' + qCounter;
    const q = data || { temp_id: tempId, type: 'radio', is_required: true, translations: {}, conditional_temp_id: null, conditional_answer_value: null };
    if (!q.temp_id) q.temp_id = tempId;
    questions.push(q);
    renderAllQuestions();
}

function removeQuestion(tempId) {
    questions = questions.filter(q => q.temp_id !== tempId);
    questions.forEach(q => { if (q.conditional_temp_id === tempId) { q.conditional_temp_id = null; q.conditional_answer_value = null; } });
    renderAllQuestions();
}

function renderAllQuestions() {
    const container = document.getElementById('questionsContainer');
    const emptyMsg  = document.getElementById('emptyMsg');

    container.querySelectorAll('.question-card').forEach(card => {
        const activeLink = card.querySelector('.nav-link.active[href]');
        if (activeLink) {
            const m = activeLink.getAttribute('href').match(/_([a-z]{2})$/);
            if (m) activeQTab[card.id.replace('qcard_', '')] = m[1];
        }
    });

    emptyMsg.style.display = questions.length ? 'none' : 'block';
    container.innerHTML = questions.map((q, i) => renderQuestionCard(q, i)).join('');

    questions.forEach(q => {
        const saved = activeQTab[q.temp_id];
        if (!saved || !languages.includes(saved) || saved === languages[0]) return;
        const card   = document.getElementById('qcard_' + q.temp_id);
        if (!card) return;
        const oldA = card.querySelector('.nav-link.active');
        const newA = card.querySelector(`[href="#q_${q.temp_id}_${saved}"]`);
        const oldP = card.querySelector('.tab-pane.active');
        const newP = card.querySelector(`#q_${q.temp_id}_${saved}`);
        if (oldA) oldA.classList.remove('active');
        if (newA) newA.classList.add('active');
        if (oldP) oldP.classList.remove('show', 'active');
        if (newP) newP.classList.add('show', 'active');
    });
}

function renderQuestionCard(q, index) {
    const hasOptions = ['radio', 'checkbox'].includes(q.type);

    let langTabs = '', langContent = '';
    languages.forEach((lang, li) => {
        const info   = AVAILABLE_LANGS[lang] || { name: lang.toUpperCase(), flag: '🌐' };
        const active = li === 0 ? 'active' : '';
        const show   = li === 0 ? 'show active' : '';
        const text   = q.translations[lang]?.text || '';

        langTabs    += `<li class="nav-item"><a class="nav-link ${active} py-1 px-2 small" data-bs-toggle="tab" href="#q_${q.temp_id}_${lang}">${info.flag} ${info.name}</a></li>`;
        langContent += `<div class="tab-pane fade ${show}" id="q_${q.temp_id}_${lang}">
                            <input type="text" class="form-control form-control-sm mb-2"
                                   placeholder="Soru metni (${info.name})..."
                                   value="${escHtml(text)}"
                                   oninput="setQuestionText('${q.temp_id}','${lang}',this.value)">
                            ${hasOptions ? renderOptionsEditor(q, lang) : ''}
                        </div>`;
    });

    const prevWithOptions = questions.slice(0, index).filter(pq => ['radio'].includes(pq.type));
    let conditionalHtml = '';
    if (prevWithOptions.length > 0) {
        const selQ = q.conditional_temp_id || '';
        const selV = q.conditional_answer_value || '';
        conditionalHtml = `<div class="border rounded p-2 mt-2 bg-light">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="cond_enable_${q.temp_id}"
                       ${q.conditional_temp_id?'checked':''}
                       onchange="toggleCondPanel('${q.temp_id}',this.checked)">
                <label class="form-check-label small" for="cond_enable_${q.temp_id}"><i class="fas fa-code-branch me-1"></i>Koşullu göster</label>
            </div>
            <div id="cond_panel_${q.temp_id}" style="display:${q.conditional_temp_id?'block':'none'}">
                <select class="form-select form-select-sm mb-2" onchange="setCondQuestion('${q.temp_id}',this.value)">
                    <option value="">Soru seçin...</option>
                    ${prevWithOptions.map(pq => `<option value="${pq.temp_id}" ${pq.temp_id===selQ?'selected':''}>${questions.indexOf(pq)+1}. ${escHtml(pq.translations[languages[0]]?.text||'Soru '+(questions.indexOf(pq)+1))}</option>`).join('')}
                </select>
                <input type="text" class="form-control form-control-sm mb-1" placeholder="Göster eğer cevap şuysa..."
                       value="${escHtml(selV)}"
                       oninput="setCondValue('${q.temp_id}',this.value)">
                <small class="text-muted">Bu değer girildiğinde soru görünür hale gelir.</small>
            </div>
        </div>`;
    }

    return `<div class="card mb-3 border question-card" id="qcard_${q.temp_id}">
        <div class="card-header py-2 d-flex align-items-center gap-2" style="background:#f8f9fc">
            <span class="badge bg-primary rounded-pill">${index+1}</span>
            <select class="form-select form-select-sm" style="max-width:220px" onchange="setQuestionType('${q.temp_id}',this.value)">
                ${Object.entries(QUESTION_TYPES).map(([val,info])=>`<option value="${val}" ${q.type===val?'selected':''}>${info.label}</option>`).join('')}
            </select>
            <div class="form-check form-switch ms-2 mb-0">
                <input class="form-check-input" type="checkbox" id="req_${q.temp_id}"
                       ${q.is_required?'checked':''} onchange="setRequired('${q.temp_id}',this.checked)">
                <label class="form-check-label small" for="req_${q.temp_id}">Zorunlu</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeQuestion('${q.temp_id}')"><i class="fas fa-trash"></i></button>
        </div>
        <div class="card-body py-2">
            <ul class="nav nav-tabs nav-sm mb-2" role="tablist">${langTabs}</ul>
            <div class="tab-content">${langContent}</div>
            ${conditionalHtml}
        </div>
    </div>`;
}

function renderOptionsEditor(q, lang) {
    const options = q.translations[lang]?.options || [];
    let html = `<div class="options-editor">`;
    options.forEach((opt, oi) => {
        html += `<div class="input-group input-group-sm mb-1">
                     <span class="input-group-text"><i class="fas fa-grip-lines text-muted"></i></span>
                     <input type="text" class="form-control" value="${escHtml(opt)}"
                            oninput="setOption('${q.temp_id}','${lang}',${oi},this.value)"
                            placeholder="Seçenek ${oi+1}">
                     <button type="button" class="btn btn-outline-danger" onclick="removeOption('${q.temp_id}','${lang}',${oi})"><i class="fas fa-times"></i></button>
                 </div>`;
    });
    html += `<button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="addOption('${q.temp_id}','${lang}')"><i class="fas fa-plus me-1"></i>Seçenek Ekle</button></div>`;
    return html;
}

function getQuestion(tid) { return questions.find(q => q.temp_id === tid); }
function setQuestionType(tid, type) { const q=getQuestion(tid); if(q){q.type=type; renderAllQuestions(); } }
function setRequired(tid, val) { const q=getQuestion(tid); if(q) q.is_required=val; }
function setQuestionText(tid, lang, val) { const q=getQuestion(tid); if(!q) return; if(!q.translations[lang]) q.translations[lang]={text:'',options:[]}; q.translations[lang].text=val; }
function addOption(tid, lang) { const q=getQuestion(tid); if(!q) return; if(!q.translations[lang]) q.translations[lang]={text:'',options:[]}; if(!q.translations[lang].options) q.translations[lang].options=[]; q.translations[lang].options.push(''); renderAllQuestions(); }
function removeOption(tid, lang, idx) { const q=getQuestion(tid); if(!q) return; q.translations[lang]?.options?.splice(idx,1); renderAllQuestions(); }
function setOption(tid, lang, idx, val) { const q=getQuestion(tid); if(!q) return; if(!q.translations[lang]) q.translations[lang]={text:'',options:[]}; if(!q.translations[lang].options) q.translations[lang].options=[]; q.translations[lang].options[idx]=val; }
function toggleCondPanel(tid, show) { const p=document.getElementById('cond_panel_'+tid); if(p) p.style.display=show?'block':'none'; if(!show){const q=getQuestion(tid); if(q){q.conditional_temp_id=null;q.conditional_answer_value=null;}} }
function setCondQuestion(tid, val) { const q=getQuestion(tid); if(q) q.conditional_temp_id=val||null; }
function setCondValue(tid, val) { const q=getQuestion(tid); if(q) q.conditional_answer_value=val||null; }
function prepareSubmit() { document.getElementById('questions_data').value = JSON.stringify(questions); }
function escHtml(str) { if(!str) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
@endpush
