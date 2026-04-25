@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>İsimlik Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('food-labels.index') }}">Yemek İsimlik</a></li>
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

    <form action="{{ route('food-labels.update', $foodLabel) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3 align-items-start">

            {{-- SOL: Dil + İsim/Açıklama/Malzemeler --}}
            <div class="col-xl-5" style="position:sticky;top:1rem">

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-language me-2 text-primary"></i>İsimlik Dilleri</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Models\FoodLabel::LANGUAGES as $code => $info)
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input lang-check" type="checkbox"
                                       value="{{ $code }}" id="lang_{{ $code }}"
                                       @checked(array_key_exists($code, $foodLabel->name ?? []))
                                       onchange="handleLangChange()">
                                <label class="form-check-label small" for="lang_{{ $code }}">
                                    {{ $info['flag'] }} {{ $info['name'] }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-heading me-2 text-primary"></i>Yemek Adı & Açıklama</h6>
                    </div>
                    <div class="card-body">
                        <div id="nameTabs"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Malzemeler / İçindekiler</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Malzemeleri virgülle veya her satıra bir malzeme yazarak girin.</p>
                        <div id="ingredientsTabs"></div>
                    </div>
                </div>

            </div>

            {{-- SAĞ: Allerjenler + Besin + Ayarlar --}}
            <div class="col-xl-7">

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Allerjenler
                            <small class="text-muted fw-normal ms-2">(AB Standartı 14 Allerjen)</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach(\App\Models\FoodLabel::ALLERGENS as $key => $info)
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="allergen-card d-flex align-items-center gap-2 p-2 border rounded"
                                       style="cursor:pointer;transition:all .15s;{{ in_array($key, $foodLabel->allergens ?? []) ? 'background:#fff0f0;border-color:#ef4444 !important;' : '' }}"
                                       for="allergen_{{ $key }}">
                                    <input type="checkbox" class="form-check-input allergen-check flex-shrink-0"
                                           name="allergens[]" value="{{ $key }}" id="allergen_{{ $key }}"
                                           @checked(in_array($key, $foodLabel->allergens ?? []))
                                           onchange="styleAllergen(this)">
                                    <span style="font-size:1.2rem;line-height:1">{{ $info['icon'] }}</span>
                                    <div class="lh-sm">
                                        <div style="font-size:11px;font-weight:600">{{ $info['label'] }}</div>
                                        <div style="font-size:10px;color:#9ca3af">EU #{{ $info['eu'] }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="toggleAllAllergens(true)" style="font-size:11px;padding:2px 8px">Tümünü İşaretle</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAllAllergens(false)" style="font-size:11px;padding:2px 8px">Temizle</button>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-fire me-2" style="color:#f9a825"></i>Besin Bilgisi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Kalori (kcal)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="calories" class="form-control"
                                           placeholder="0" min="0" max="9999"
                                           value="{{ old('calories', $foodLabel->calories) }}">
                                    <span class="input-group-text">kcal</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Sıra No</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm"
                                       placeholder="0" min="0"
                                       value="{{ old('sort_order', $foodLabel->sort_order) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Diyet Etiketleri</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_vegan" id="isVegan" value="1"
                                               @checked(old('is_vegan', $foodLabel->is_vegan))>
                                        <label class="form-check-label" for="isVegan">🌱 Vegan</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_vegetarian" id="isVegetarian" value="1"
                                               @checked(old('is_vegetarian', $foodLabel->is_vegetarian))>
                                        <label class="form-check-label" for="isVegetarian">🥗 Vejetaryen</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_halal" id="isHalal" value="1"
                                               @checked(old('is_halal', $foodLabel->is_halal))>
                                        <label class="form-check-label" for="isHalal">☪ Helal</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Genel Ayarlar</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Kategori</label>
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">Kategori Seçin...</option>
                                    @foreach(\App\Models\FoodLabel::CATEGORIES as $key => $catLabel)
                                        <option value="{{ $key }}" @selected(old('category', $foodLabel->category) === $key)>{{ $catLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($branches->count() > 1)
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Şube</label>
                                <select name="branch_id" class="form-select form-select-sm">
                                    <option value="">Tüm Şubeler</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $foodLabel->branch_id) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                            @endif
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                                           @checked(old('is_active', $foodLabel->is_active))>
                                    <label class="form-check-label" for="isActive">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-between">
                    <a href="{{ route('food-labels.print-single', $foodLabel) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Önizle & Yazdır
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('food-labels.index') }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Güncelle
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
const LANGS     = @json(\App\Models\FoodLabel::LANGUAGES);
const EXIST_NAME = @json($foodLabel->name ?? []);
const EXIST_DESC = @json($foodLabel->description ?? []);
@php
$existIngFlat = [];
foreach(($foodLabel->ingredients ?? []) as $lang => $arr) {
    $existIngFlat[$lang] = is_array($arr) ? implode(', ', $arr) : $arr;
}
@endphp
const EXIST_ING  = @json($existIngFlat);

let languages   = [];
const nameState = {};
const descState = {};
const ingState  = {};

document.addEventListener('DOMContentLoaded', () => {
    readLanguages();
    renderAllTabs();
});

function readLanguages() {
    languages = [...document.querySelectorAll('.lang-check:checked')].map(c => c.value);
}
function handleLangChange() {
    readLanguages();
    if (!languages.length) { document.getElementById('lang_tr').checked = true; readLanguages(); }
    renderAllTabs();
}
function saveState() {
    document.querySelectorAll('#nameTabs input[name^="name["]').forEach(el => nameState[el.name.slice(5,-1)] = el.value);
    document.querySelectorAll('#nameTabs textarea[name^="description["]').forEach(el => descState[el.name.slice(12,-1)] = el.value);
    document.querySelectorAll('#ingredientsTabs textarea[name^="ingredients["]').forEach(el => ingState[el.name.slice(12,-1)] = el.value);
}
function renderAllTabs() {
    saveState();
    renderNameTabs();
    renderIngredientsTabs();
}
function renderNameTabs() {
    const container = document.getElementById('nameTabs');
    let tabs = '<ul class="nav nav-tabs nav-sm mb-3" role="tablist">';
    let content = '<div class="tab-content">';
    languages.forEach((lang, i) => {
        const info   = LANGS[lang] || { name: lang.toUpperCase(), flag: '🌐' };
        const active = i === 0 ? 'active' : '';
        const show   = i === 0 ? 'show active' : '';
        const name   = escHtml(nameState[lang] ?? EXIST_NAME[lang] ?? '');
        const desc   = escHtml(descState[lang] ?? EXIST_DESC[lang] ?? '');
        tabs    += `<li class="nav-item"><a class="nav-link ${active} py-1 px-2 small" data-bs-toggle="tab" href="#name_${lang}">${info.flag} ${info.name}</a></li>`;
        content += `<div class="tab-pane fade ${show}" id="name_${lang}">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Yemek Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name[${lang}]" class="form-control form-control-sm"
                                   placeholder="${info.name} dilinde yemek adı..." value="${name}" oninput="saveState()">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Kısa Açıklama</label>
                            <textarea name="description[${lang}]" class="form-control form-control-sm" rows="2"
                                      placeholder="Kısa açıklama..." oninput="saveState()">${desc}</textarea>
                        </div>
                    </div>`;
    });
    tabs += '</ul>'; content += '</div>';
    container.innerHTML = tabs + content;
}
function renderIngredientsTabs() {
    const container = document.getElementById('ingredientsTabs');
    let tabs = '<ul class="nav nav-tabs nav-sm mb-3" role="tablist">';
    let content = '<div class="tab-content">';
    languages.forEach((lang, i) => {
        const info   = LANGS[lang] || { name: lang.toUpperCase(), flag: '🌐' };
        const active = i === 0 ? 'active' : '';
        const show   = i === 0 ? 'show active' : '';
        const ing    = escHtml(ingState[lang] ?? EXIST_ING[lang] ?? '');
        tabs    += `<li class="nav-item"><a class="nav-link ${active} py-1 px-2 small" data-bs-toggle="tab" href="#ing_${lang}">${info.flag} ${info.name}</a></li>`;
        content += `<div class="tab-pane fade ${show}" id="ing_${lang}">
                        <textarea name="ingredients[${lang}]" class="form-control form-control-sm" rows="4"
                                  placeholder="${info.name} dilinde malzemeler..." oninput="saveState(); autoDetectAllergens()">${ing}</textarea>
                        <small class="text-muted">Örn: Un, Şeker, Yumurta, Tereyağı</small>
                    </div>`;
    });
    tabs += '</ul>'; content += '</div>';
    container.innerHTML = tabs + content;
}
function styleAllergen(el) {
    const card = el.closest('.allergen-card');
    card.style.background  = el.checked ? '#fff0f0' : '';
    card.style.borderColor = el.checked ? '#ef4444' : '';
    if (!el.checked) card.querySelector('.auto-tag')?.remove();
}
function toggleAllAllergens(check) {
    document.querySelectorAll('.allergen-check').forEach(el => { el.checked = check; styleAllergen(el); });
}

const ALLERGEN_KEYWORDS = {
    gluten:       ['un','ekmek','makarna','erişte','eriste','bulgur','arpa','buğday','bugday','yulaf','çavdar','börek','borek','yufka','simit','pide','galeta','kuskus','spaghetti','lazanya','ravioli','tortellini','milföy','milfoy','hamur','kraker','bisküvi','biskuvi','kek','tart','şehriye','sehriye','fide','flour','bread','wheat','barley','rye','oat','noodle','pasta','dough','crouton','focaccia','breadcrumb','cracker','biscuit','pastry','mehl','brot','teig','semmelbrösel','weizen','hafer','мука','хлеб','макарон'],
    crustaceans:  ['karides','ıstakoz','istakoz','yengeç','yengec','pavurya','shrimp','prawn','lobster','crab','crayfish','scampi','garnele','hummer','krebs','креветк','лобстер','краб'],
    eggs:         ['yumurta','mayonez','mayo','mayonnaise','egg','eggs','eier'],
    fish:         ['balık','balik','somon','levrek','çipura','cipura','tuna','barbun','hamsi','ançuez','ancuez','istavrit','palamut','alabalık','alabalik','kefal','mezgit','sardalya','uskumru','lüfer','lufer','kalkan','havyar','ikra','roe','fish','salmon','anchovy','trout','cod','lachs','fisch','forelle','thunfisch','рыб','лосос'],
    peanuts:      ['yer fıstığı','yer fistigi','yerfıstığı','yerfistigi','peanut','peanuts','erdnuss','арахис'],
    soybeans:     ['soya','tofu','tempeh','edamame','soybean','soja','соя'],
    milk:         ['süt','sut','tereyağı','tereyagi','peynir','yoğurt','yogurt','krema','kaymak','dondurma','labne','feta','mozzarella','parmesan','ricotta','mascarpone','muhallebi','sütlaç','sutlac','cacık','cacik','ayran','milk','butter','cheese','cream','yoghurt','milch','käse','sahne','joghurt','молоко','масл','сливк','сыр'],
    nuts:         ['ceviz','badem','fındık','findik','antepfıstığı','antepfistigi','kaju','pekan','krokant','walnut','almond','hazelnut','pistachio','cashew','pecan','walnuss','mandel','haselnuss','pistazie','орех','миндал','фундук'],
    celery:       ['kereviz','celery','sellerie','сельдерей'],
    mustard:      ['hardal','mustard','dijon','senf','горчиц'],
    sesame:       ['susam','tahin','sesame','tahini','sesam','кунжут'],
    sulphites:    ['şarap','sarap','balzamik','balsamic','wine','wein','sulphite','sulfite','вин'],
    lupin:        ['acı bakla','aci bakla','bakla','lupin','люпин'],
    molluscs:     ['ahtapot','kalamar','midye','istiridye','salyangoz','squid','octopus','oyster','mussel','clam','tintenfisch','oktopus','моллюск','кальмар'],
};

function autoDetectAllergens() {
    const texts = [...document.querySelectorAll('#ingredientsTabs textarea')].map(t => t.value.toLowerCase()).join(' ');
    if (!texts.trim()) return;

    document.querySelectorAll('.allergen-check').forEach(chk => {
        const key = chk.value;
        const keywords = ALLERGEN_KEYWORDS[key];
        if (!keywords) return;
        const detected = keywords.some(kw => {
            const re = new RegExp('\\b' + kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'u');
            return re.test(texts);
        });
        if (detected && !chk.checked) {
            chk.checked = true;
            styleAllergen(chk);
            const card = chk.closest('.allergen-card');
            if (!card.querySelector('.auto-tag')) {
                const tag = document.createElement('span');
                tag.className = 'auto-tag badge rounded-pill ms-auto';
                tag.style.cssText = 'background:#e0f2fe;color:#0369a1;font-size:9px;padding:2px 5px;font-weight:500';
                tag.textContent = 'oto';
                card.appendChild(tag);
            }
        }
    });
}
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
