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
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" id="categorySelect"
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Kategori seçin...</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
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
const categoryFieldsUrl = '{{ url('demirbaslar/kategori') }}';

document.getElementById('categorySelect').addEventListener('change', function() {
    const catId = this.value;
    const container = document.getElementById('dynamicFieldsContainer');
    const fieldsDiv = document.getElementById('dynamicFields');

    if (!catId) { container.style.display = 'none'; fieldsDiv.innerHTML = ''; return; }

    fetch(`${categoryFieldsUrl}/${catId}/alanlar`)
        .then(r => r.json())
        .then(fields => {
            if (!fields || fields.length === 0) { container.style.display = 'none'; fieldsDiv.innerHTML = ''; return; }
            fieldsDiv.innerHTML = '';
            fields.forEach(field => {
                let input = '';
                const req = field.required ? 'required' : '';
                const name = `prop_${field.name}`;

                if (field.type === 'select' && field.options?.length > 0) {
                    const opts = field.options.map(o => `<option value="${o.trim()}">${o.trim()}</option>`).join('');
                    input = `<select name="${name}" class="form-select form-select-sm" ${req}><option value="">Seçin...</option>${opts}</select>`;
                } else if (field.type === 'textarea') {
                    input = `<textarea name="${name}" class="form-control form-control-sm" rows="2" ${req}></textarea>`;
                } else {
                    input = `<input type="${field.type === 'number' ? 'number' : (field.type === 'date' ? 'date' : 'text')}"
                                   name="${name}" class="form-control form-control-sm" ${req}>`;
                }

                fieldsDiv.innerHTML += `
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">
                            ${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}
                        </label>
                        ${input}
                    </div>`;
            });
            container.style.display = 'block';
        });
});
</script>
@endpush
@endsection
