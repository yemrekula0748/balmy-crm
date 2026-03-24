@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Ekle</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Ekle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Demirbaş Kaydı</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('assets.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Demirbaş Kodu <span class="text-danger">*</span></label>
                                <input type="text" name="asset_code"
                                       class="form-control @error('asset_code') is-invalid @enderror"
                                       value="{{ old('asset_code', $nextCode) }}">
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ana Kategori <span class="text-danger">*</span></label>
                                <select id="mainCategorySelect"
                                        class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Ana kategori seçin...</option>
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
                            <div class="col-md-4" id="subCategoryWrapper" style="{{ $showSubSelect ? '' : 'display:none' }}">
                                <label class="form-label fw-semibold">Alt Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" id="subCategorySelect"
                                        class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Alt kategori seçin...</option>
                                    @if(isset($subCategories))
                                        @foreach($subCategories as $sc)
                                            <option value="{{ $sc->id }}" @selected(old('category_id') == $sc->id)>{{ $sc->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- Eğer alt kategori yoksa doğrudan ana kategori kullanılır --}}
                            <input type="hidden" name="category_id" id="finalCategoryId" value="{{ old('category_id') }}">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">Şube seçin...</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Demirbaş Adı <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="ör. Samsung 55' Smart TV">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Durum</label>
                                <select name="status" class="form-select">
                                    @foreach(\App\Models\Asset::STATUSES as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', 'available') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konum / Bölüm</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}"
                                       placeholder="ör. 203 No'lu Oda, Lobi...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Seri No</label>
                                <input type="text" name="serial_no" class="form-control" value="{{ old('serial_no') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alış Tarihi</label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alış Fiyatı (₺)</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01" min="0"
                                       value="{{ old('purchase_price') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Garanti Bitiş</label>
                                <input type="date" name="warranty_until" class="form-control" value="{{ old('warranty_until') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>

                            {{-- DİNAMİK KATEGORİ ALANLARI --}}
                            <div class="col-12" id="dynamicFieldsContainer" style="display:none">
                                <hr>
                                <h6 class="mb-3 text-muted">Kategori Özel Alanları</h6>
                                <div id="dynamicFields" class="row g-3"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn" style="background:#c19b77;color:#fff">Kaydet</button>
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const subCatsBaseUrl  = '{{ url('demirbaslar/kategoriler') }}';
const categoryFieldsUrl = '{{ url('demirbaslar/kategori') }}';

const mainSelect      = document.getElementById('mainCategorySelect');
const subWrapper      = document.getElementById('subCategoryWrapper');
const subSelect       = document.getElementById('subCategorySelect');
const finalInput      = document.getElementById('finalCategoryId');
const dynamicContainer = document.getElementById('dynamicFieldsContainer');
const dynamicFields   = document.getElementById('dynamicFields');

function fetchCategoryFields(catId) {
    if (!catId) { dynamicContainer.style.display = 'none'; dynamicFields.innerHTML = ''; return; }
    fetch(`${categoryFieldsUrl}/${catId}/alanlar`)
        .then(r => r.json())
        .then(fields => {
            if (!fields || fields.length === 0) { dynamicContainer.style.display = 'none'; dynamicFields.innerHTML = ''; return; }
            dynamicFields.innerHTML = '';
            fields.forEach(field => {
                let input = '';
                const req  = field.required ? 'required' : '';
                const name = `prop_${field.name}`;
                if (field.type === 'select' && field.options?.length > 0) {
                    const opts = field.options.map(o => `<option value="${o.trim()}">${o.trim()}</option>`).join('');
                    input = `<select name="${name}" class="form-select form-select-sm" ${req}><option value="">Seçin...</option>${opts}</select>`;
                } else if (field.type === 'textarea') {
                    input = `<textarea name="${name}" class="form-control form-control-sm" rows="2" ${req}></textarea>`;
                } else {
                    const t = field.type === 'number' ? 'number' : (field.type === 'date' ? 'date' : 'text');
                    input = `<input type="${t}" name="${name}" class="form-control form-control-sm" ${req}>`;
                }
                dynamicFields.innerHTML += `
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">
                            ${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}
                        </label>${input}
                    </div>`;
            });
            dynamicContainer.style.display = 'block';
        });
}

mainSelect.addEventListener('change', function() {
    const catId       = this.value;
    const hasChildren = this.options[this.selectedIndex]?.dataset?.hasChildren === '1';

    subWrapper.style.display = 'none';
    subSelect.innerHTML = '<option value="">Alt kategori seçin...</option>';
    finalInput.value = '';
    dynamicContainer.style.display = 'none';

    if (!catId) return;

    if (hasChildren) {
        // Fetch subcategories
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
                    // Don't set finalInput yet — wait for user to pick subcategory
                } else {
                    // No children found (race condition guard) — use main cat
                    finalInput.value = catId;
                    fetchCategoryFields(catId);
                }
            });
    } else {
        // No subcategories — use main category directly
        finalInput.value = catId;
        fetchCategoryFields(catId);
    }
});

subSelect.addEventListener('change', function() {
    finalInput.value = this.value;
    if (this.value) fetchCategoryFields(this.value);
    else { dynamicContainer.style.display = 'none'; dynamicFields.innerHTML = ''; }
});

// Ensure only one category_id field is submitted
document.querySelector('form').addEventListener('submit', function() {
    // If subWrapper is visible and a sub is selected, use sub; else use main
    if (subWrapper.style.display !== 'none' && subSelect.value) {
        finalInput.value = subSelect.value;
        subSelect.removeAttribute('name');
    } else if (!finalInput.value && mainSelect.value) {
        finalInput.value = mainSelect.value;
    }
    subSelect.removeAttribute('name');
});
</script>
@endpush
@endsection
