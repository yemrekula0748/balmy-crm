@extends('layouts.default')

@push('styles')
<style>
/* ── Hero ──────────────────────────────────────────────────── */
.form-hero {
    background: linear-gradient(135deg, #c19b77 0%, #a07850 100%);
    border-radius: 16px;
    padding: 26px 32px;
    margin-bottom: 28px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.form-hero::before {
    content: '\f187';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 28px; top: 50%;
    transform: translateY(-50%);
    font-size: 82px;
    opacity: .12;
    line-height: 1;
}
.form-hero h3 { font-size: 1.5rem; font-weight: 700; margin: 0 0 4px; }
.form-hero p  { margin: 0; opacity: .85; font-size: .9rem; }

/* ── Section card ──────────────────────────────────────────── */
.form-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    margin-bottom: 20px;
    overflow: hidden;
}
.form-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 22px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}
.form-section-header .sec-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}
.form-section-header h6 {
    font-size: .9rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    letter-spacing: .02em;
}
.form-section-header span.sub {
    font-size: .75rem;
    color: #9ca3af;
    margin-top: 1px;
    display: block;
}
.form-section-body { padding: 22px 22px 20px; }

/* ── Form controls ─────────────────────────────────────────── */
.form-label {
    font-size: .78rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 6px;
}
.form-control, .form-select {
    border-radius: 9px;
    border: 1.5px solid #e5e7eb;
    font-size: .88rem;
    color: #1f2937;
    padding: 9px 13px;
    transition: border-color .2s, box-shadow .2s;
    background: #fff;
}
.form-control:focus, .form-select:focus {
    border-color: #c19b77;
    box-shadow: 0 0 0 3px rgba(193,155,119,.18);
    outline: none;
}
.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
}
.input-with-icon { position: relative; }
.input-with-icon .field-icon {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: .8rem;
    pointer-events: none;
}
.input-with-icon .form-control,
.input-with-icon .form-select { padding-left: 34px; }

/* ── Code badge ─────────────────────────────────────────────── */
.code-input-wrap { position: relative; }
.code-input-wrap .code-prefix {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    display: flex; align-items: center;
    padding: 0 12px;
    background: #f3f4f6;
    border: 1.5px solid #e5e7eb;
    border-right: none;
    border-radius: 9px 0 0 9px;
    font-size: .78rem;
    font-weight: 700;
    color: #6b7280;
    letter-spacing: .05em;
}
.code-input-wrap .form-control {
    padding-left: 70px;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    letter-spacing: .08em;
    color: #a07850;
}

/* ── Status selector ────────────────────────────────────────── */
.status-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    font-size: .82rem;
    font-weight: 600;
    color: #6b7280;
    transition: all .15s;
    user-select: none;
    background: #fff;
}
.status-pill:hover { border-color: #d1d5db; background: #f9fafb; }
.status-pill.active-available   { border-color: #10b981; background: rgba(16,185,129,.08); color: #059669; }
.status-pill.active-in_use      { border-color: #f59e0b; background: rgba(245,158,11,.09); color: #d97706; }
.status-pill.active-maintenance { border-color: #3b82f6; background: rgba(59,130,246,.09); color: #2563eb; }
.status-pill.active-retired     { border-color: #9ca3af; background: rgba(156,163,175,.1); color: #6b7280; }

/* ── Dynamic fields panel ───────────────────────────────────── */
.dynamic-panel {
    border-radius: 10px;
    border: 1.5px dashed #e5e7eb;
    background: #f9fafb;
    padding: 18px 18px 14px;
    margin-top: 4px;
    display: none;
}
.dynamic-panel.visible { display: block; }
.dynamic-panel h6 {
    font-size: .78rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 7px;
}

/* ── Category select animation ───────────────────────────────── */
#subCategoryWrapper {
    transition: opacity .2s;
}
#subCategoryWrapper.fading { opacity: 0; }

/* ── Submit zone ─────────────────────────────────────────────── */
.submit-zone {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    padding: 20px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-save {
    background: linear-gradient(135deg,#c19b77,#a07850);
    color: #fff;
    border: 0;
    border-radius: 9px;
    padding: 10px 28px;
    font-size: .88rem;
    font-weight: 600;
    box-shadow: 0 3px 10px rgba(193,155,119,.45);
    transition: opacity .15s, transform .1s;
}
.btn-save:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
.btn-save:active { transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Ekle</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Ekle</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Lütfen aşağıdaki hataları düzeltin:</strong>
            <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="form-hero">
        <h3><i class="fas fa-plus-circle me-2" style="opacity:.9"></i>Yeni Demirbaş Kaydı</h3>
        <p>Aşağıdaki bilgileri doldurarak yeni bir demirbaş ekleyin</p>
    </div>

    <form action="{{ route('assets.store') }}" method="POST" id="assetCreateForm">
        @csrf
        <div class="row g-4">

            {{-- LEFT COLUMN --}}
            <div class="col-xl-8">

                {{-- Section 1: Kimlik --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(193,155,119,.15);color:#c19b77">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <div>
                            <h6>Kimlik Bilgileri</h6>
                            <span class="sub">Kod, kategori ve şube</span>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Demirbaş Kodu <span class="text-danger">*</span></label>
                                <div class="code-input-wrap">
                                    <span class="code-prefix">DMB</span>
                                    <input type="text" name="asset_code"
                                           class="form-control @error('asset_code') is-invalid @enderror"
                                           value="{{ old('asset_code', $nextCode) }}"
                                           placeholder="—0001">
                                    @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ana Kategori <span class="text-danger">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-folder-open field-icon"></i>
                                    <select id="mainCategorySelect"
                                            class="form-select @error('category_id') is-invalid @enderror">
                                        <option value="">Kategori seçin…</option>
                                        @foreach($categories as $c)
                                            <option value="{{ $c->id }}"
                                                data-has-children="{{ $c->children_count > 0 ? '1' : '0' }}"
                                                @selected(old('_main_cat', $mainCatId ?? '') == $c->id)>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4" id="subCategoryWrapper" style="{{ ($showSubSelect ?? false) ? '' : 'display:none' }}">
                                <label class="form-label">Alt Kategori <span class="text-danger">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-folder field-icon"></i>
                                    <select name="category_id" id="subCategorySelect"
                                            class="form-select @error('category_id') is-invalid @enderror">
                                        <option value="">Alt kategori seçin…</option>
                                        @if(isset($subCategories))
                                            @foreach($subCategories as $sc)
                                                <option value="{{ $sc->id }}" @selected(old('category_id') == $sc->id)>{{ $sc->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="category_id" id="finalCategoryId" value="{{ old('category_id') }}">
                            <div class="col-md-4">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-building field-icon"></i>
                                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                        <option value="">Şube seçin…</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Demirbaş Adı & Konum --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(67,97,238,.12);color:#4361ee">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <h6>Tanım & Konum</h6>
                            <span class="sub">Ad, açıklama ve konumlandırma</span>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Demirbaş Adı <span class="text-danger">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-cube field-icon"></i>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}"
                                           placeholder="ör. Samsung 55ʺ Smart TV — Model QN55">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Seri No</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-barcode field-icon"></i>
                                    <input type="text" name="serial_no" class="form-control"
                                           value="{{ old('serial_no') }}" placeholder="SN / IMEI…">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Konum / Bölüm</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt field-icon"></i>
                                    <input type="text" name="location" class="form-control"
                                           value="{{ old('location') }}"
                                           placeholder="ör. 203 No'lu Oda, Lobi, Toplantı Salonu…">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Açıklama</label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="İsteğe bağlı notlar, teknik özellikler…">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Satın Alma --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <h6>Satın Alma Bilgileri</h6>
                            <span class="sub">Tarih, fiyat ve garanti</span>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Alış Tarihi</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar-alt field-icon"></i>
                                    <input type="date" name="purchase_date" class="form-control"
                                           value="{{ old('purchase_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Alış Fiyatı</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="border-radius:9px 0 0 9px;border:1.5px solid #e5e7eb;border-right:0;background:#f3f4f6;font-size:.82rem;color:#6b7280;font-weight:700">₺</span>
                                    <input type="number" name="purchase_price" class="form-control"
                                           step="0.01" min="0" value="{{ old('purchase_price') }}"
                                           placeholder="0.00"
                                           style="border-radius:0 9px 9px 0;border-left:0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Garanti Bitiş</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-shield-alt field-icon"></i>
                                    <input type="date" name="warranty_until" class="form-control"
                                           value="{{ old('warranty_until') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Dynamic Category Fields --}}
                <div class="form-section" id="dynamicFieldsContainer" style="display:none">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(139,92,246,.12);color:#7c3aed">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <div>
                            <h6>Kategori Özel Alanları</h6>
                            <span class="sub">Seçili kategoriye ait ek bilgiler</span>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div id="dynamicFields" class="row g-3"></div>
                    </div>
                </div>

            </div>{{-- /col-xl-8 --}}

            {{-- RIGHT COLUMN --}}
            <div class="col-xl-4">

                {{-- Durum --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(245,158,11,.12);color:#d97706">
                            <i class="fas fa-traffic-light"></i>
                        </div>
                        <div>
                            <h6>Demirbaş Durumu</h6>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <input type="hidden" name="status" id="statusInput" value="{{ old('status', 'available') }}">
                        <div class="status-pills" id="statusPills">
                            @foreach(\App\Models\Asset::STATUSES as $val => $label)
                            @php
                                $statusColors = [
                                    'available'   => ['#10b981','rgba(16,185,129,.12)'],
                                    'in_use'      => ['#d97706','rgba(245,158,11,.12)'],
                                    'maintenance' => ['#2563eb','rgba(59,130,246,.12)'],
                                    'retired'     => ['#6b7280','rgba(156,163,175,.12)'],
                                ];
                                $statusIcons = [
                                    'available'   => 'fas fa-check-circle',
                                    'in_use'      => 'fas fa-arrow-circle-right',
                                    'maintenance' => 'fas fa-tools',
                                    'retired'     => 'fas fa-times-circle',
                                ];
                                $sc = $statusColors[$val];
                            @endphp
                            <button type="button"
                                    class="status-pill {{ old('status','available') === $val ? 'active-'.$val : '' }}"
                                    data-status="{{ $val }}"
                                    data-color="{{ $sc[0] }}" data-bg="{{ $sc[1] }}"
                                    style="flex:1 1 calc(50% - 4px)">
                                <i class="{{ $statusIcons[$val] }}"></i>{{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Quick tips --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="sec-icon" style="background:rgba(193,155,119,.12);color:#c19b77">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div><h6>İpuçları</h6></div>
                    </div>
                    <div class="form-section-body">
                        <ul class="list-unstyled mb-0" style="font-size:.8rem;color:#6b7280;line-height:1.7">
                            <li class="mb-2"><i class="fas fa-info-circle me-2" style="color:#c19b77"></i>Kod otomatik üretilir, değiştirebilirsiniz.</li>
                            <li class="mb-2"><i class="fas fa-info-circle me-2" style="color:#c19b77"></i>Alt kategorisi olan bir ana kategori seçildiğinde alt kategori listesi otomatik gelir.</li>
                            <li class="mb-2"><i class="fas fa-info-circle me-2" style="color:#c19b77"></i>Kategoriye özel ek alanlar, kategori seçiminin ardından aşağıda belirir.</li>
                            <li><i class="fas fa-info-circle me-2" style="color:#c19b77"></i>Tüm demirbaşlara sonradan fotoğraf ekleyebilirsiniz.</li>
                        </ul>
                    </div>
                </div>

            </div>{{-- /col-xl-4 --}}

        </div>{{-- /row --}}

        {{-- Submit zone --}}
        <div class="submit-zone mt-2">
            <div class="text-muted" style="font-size:.82rem">
                <i class="fas fa-asterisk text-danger me-1" style="font-size:.6rem;vertical-align:middle"></i>
                ile işaretli alanlar zorunludur.
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary" style="border-radius:9px;padding:10px 20px;font-size:.88rem">
                    <i class="fas fa-times me-1"></i>İptal
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save me-2"></i>Kaydet
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
const subCatsBaseUrl    = '{{ url('demirbaslar/kategoriler') }}';
const categoryFieldsUrl = '{{ url('demirbaslar/kategori') }}';

const mainSelect       = document.getElementById('mainCategorySelect');
const subWrapper       = document.getElementById('subCategoryWrapper');
const subSelect        = document.getElementById('subCategorySelect');
const finalInput       = document.getElementById('finalCategoryId');
const dynContainer     = document.getElementById('dynamicFieldsContainer');
const dynFields        = document.getElementById('dynamicFields');

/* ── Dynamic fields ── */
function fetchCategoryFields(catId) {
    if (!catId) { dynContainer.style.display = 'none'; dynFields.innerHTML = ''; return; }
    fetch(`${categoryFieldsUrl}/${catId}/alanlar`)
        .then(r => r.json())
        .then(fields => {
            if (!fields || fields.length === 0) { dynContainer.style.display = 'none'; dynFields.innerHTML = ''; return; }
            dynFields.innerHTML = '';
            fields.forEach(field => {
                const req  = field.required ? 'required' : '';
                const name = `prop_${field.name}`;
                let input  = '';
                if (field.type === 'select' && field.options?.length > 0) {
                    const opts = field.options.map(o => `<option value="${o.trim()}">${o.trim()}</option>`).join('');
                    input = `<select name="${name}" class="form-select" ${req}><option value="">Seçin…</option>${opts}</select>`;
                } else if (field.type === 'textarea') {
                    input = `<textarea name="${name}" class="form-control" rows="2" ${req}></textarea>`;
                } else {
                    const t = field.type === 'number' ? 'number' : (field.type === 'date' ? 'date' : 'text');
                    input = `<input type="${t}" name="${name}" class="form-control" ${req}>`;
                }
                dynFields.innerHTML += `
                    <div class="col-md-4">
                        <label class="form-label">${field.label}${field.required ? ' <span class="text-danger">*</span>' : ''}</label>
                        ${input}
                    </div>`;
            });
            dynContainer.style.display = 'block';
        });
}

/* ── Main category change ── */
mainSelect.addEventListener('change', function() {
    const catId       = this.value;
    const hasChildren = this.options[this.selectedIndex]?.dataset?.hasChildren === '1';

    subWrapper.style.display = 'none';
    subSelect.innerHTML = '<option value="">Alt kategori seçin…</option>';
    finalInput.value = '';
    dynContainer.style.display = 'none';

    if (!catId) return;

    if (hasChildren) {
        fetch(`${subCatsBaseUrl}/${catId}/alt-kategoriler`)
            .then(r => r.json())
            .then(subs => {
                if (subs && subs.length > 0) {
                    subs.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        subSelect.appendChild(opt);
                    });
                    subWrapper.style.display = '';
                } else {
                    finalInput.value = catId;
                    fetchCategoryFields(catId);
                }
            });
    } else {
        finalInput.value = catId;
        fetchCategoryFields(catId);
    }
});

/* ── Sub category change ── */
subSelect.addEventListener('change', function() {
    finalInput.value = this.value;
    if (this.value) fetchCategoryFields(this.value);
    else { dynContainer.style.display = 'none'; dynFields.innerHTML = ''; }
});

/* ── Status pills ── */
document.querySelectorAll('.status-pill').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.status-pill').forEach(b => {
            b.className = 'status-pill';
        });
        const st = this.dataset.status;
        this.classList.add('active-' + st);
        document.getElementById('statusInput').value = st;
    });
});

/* ── Form submit: clean up duplicate category_id ── */
document.getElementById('assetCreateForm').addEventListener('submit', function() {
    if (subWrapper.style.display !== 'none' && subSelect.value) {
        finalInput.value = subSelect.value;
    } else if (!finalInput.value && mainSelect.value) {
        finalInput.value = mainSelect.value;
    }
    subSelect.removeAttribute('name');
});
</script>
@endpush

