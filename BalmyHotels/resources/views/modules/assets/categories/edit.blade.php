@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Kategori Düzenle</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('asset-categories.index') }}">Kategoriler</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">{{ $assetCategory->name }}</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('asset-categories.update', $assetCategory) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $assetCategory->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Renk</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', $assetCategory->color) }}" style="width:60px;height:38px">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Açıklama</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $assetCategory->description) }}</textarea>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Özel Alanlar</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addField">+ Alan Ekle</button>
                        </div>

                        <div id="fieldContainer">
                            @if($assetCategory->field_definitions)
                                @foreach($assetCategory->field_definitions as $i => $field)
                                    <div class="card border mb-2 field-row">
                                        <div class="card-body p-3">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Alan Adı</label>
                                                    <input type="text" name="fields[{{ $i }}][name]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $field['name'] }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Etiket</label>
                                                    <input type="text" name="fields[{{ $i }}][label]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $field['label'] }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small mb-1">Tür</label>
                                                    <select name="fields[{{ $i }}][type]"
                                                            class="form-select form-select-sm field-type-select">
                                                        @foreach($fieldTypes as $val => $label)
                                                            <option value="{{ $val }}" @selected(($field['type'] ?? 'text') === $val)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-field">✕</button>
                                                </div>
                                                <div class="col-12 select-options @if(($field['type'] ?? '') !== 'select') d-none @endif">
                                                    <label class="form-label small mb-1">Seçenekler</label>
                                                    <input type="text" name="fields[{{ $i }}][options]"
                                                           class="form-control form-control-sm"
                                                           value="{{ implode(',', $field['options'] ?? []) }}">
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="fields[{{ $i }}][required]"
                                                               id="req_{{ $i }}" value="1"
                                                               @checked($field['required'] ?? false)>
                                                        <label class="form-check-label small" for="req_{{ $i }}">Zorunlu alan</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn" style="background:#c19b77;color:#fff">Güncelle</button>
                            <a href="{{ route('asset-categories.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Mevcut satırlar için event listener
document.querySelectorAll('.remove-field').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.field-row').remove());
});
document.querySelectorAll('.field-type-select').forEach(sel => {
    sel.addEventListener('change', function() {
        this.closest('.field-row').querySelector('.select-options').classList.toggle('d-none', this.value !== 'select');
    });
});

// Yeni alan ekle
let fieldIndex = {{ $assetCategory->field_definitions ? count($assetCategory->field_definitions) : 0 }};
const fieldTypes = @json($fieldTypes);

document.getElementById('addField').addEventListener('click', () => {
    const idx = fieldIndex++;
    const typeOpts = Object.entries(fieldTypes).map(([v,l]) => `<option value="${v}">${l}</option>`).join('');
    const row = document.createElement('div');
    row.className = 'card border mb-2 field-row';
    row.innerHTML = `
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Alan Adı</label>
                    <input type="text" name="fields[${idx}][name]" class="form-control form-control-sm" placeholder="ör. marka">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Etiket</label>
                    <input type="text" name="fields[${idx}][label]" class="form-control form-control-sm" placeholder="ör. Marka">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tür</label>
                    <select name="fields[${idx}][type]" class="form-select form-select-sm field-type-select">${typeOpts}</select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-field">✕</button>
                </div>
                <div class="col-12 select-options d-none">
                    <input type="text" name="fields[${idx}][options]" class="form-control form-control-sm" placeholder="Seçenek1, Seçenek2">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="fields[${idx}][required]" id="req_${idx}" value="1">
                        <label class="form-check-label small" for="req_${idx}">Zorunlu alan</label>
                    </div>
                </div>
            </div>
        </div>`;
    row.querySelector('.remove-field').addEventListener('click', () => row.remove());
    row.querySelector('.field-type-select').addEventListener('change', function() {
        row.querySelector('.select-options').classList.toggle('d-none', this.value !== 'select');
    });
    document.getElementById('fieldContainer').appendChild(row);
});
</script>
@endpush
@endsection
