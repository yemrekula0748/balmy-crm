@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Kategori Ekle</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('asset-categories.index') }}">Kategoriler</a></li>
                <li class="breadcrumb-item active">Ekle</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Kategori</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('asset-categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="ör. Mobilya, Elektronik...">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Renk</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', '#c19b77') }}" style="width:60px;height:38px">
                                <small class="text-muted">Kategori rozeti rengi</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Açıklama</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>

                        {{-- DİNAMİK ALAN TANIMLAYICI --}}
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Özel Alanlar <small class="text-muted">(opsiyonel)</small></h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addField">
                                + Alan Ekle
                            </button>
                        </div>
                        <div id="fieldContainer">
                            {{-- JS ile satırlar eklenir --}}
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn" style="background:#c19b77;color:#fff">Kaydet</button>
                            <a href="{{ route('asset-categories.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Özel Alan Türleri</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-1"><strong>Metin</strong> — Kısa yazı (marka, model vb.)</li>
                        <li class="mb-1"><strong>Sayı</strong> — Nümerik değer (kapasite, adet vb.)</li>
                        <li class="mb-1"><strong>Tarih</strong> — Tarih seçici (garanti bitiş vb.)</li>
                        <li class="mb-1"><strong>Seçim Listesi</strong> — Önceden tanımlı seçenekler</li>
                        <li class="mb-1"><strong>Uzun Metin</strong> — Çok satırlı metin</li>
                    </ul>
                    <hr>
                    <p class="small text-muted mb-0">
                        Burada tanımladığınız alanlar, bu kategorideki her demirbaş için doldurulabilir
                        özel bilgi alanı oluşturur.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let fieldIndex = 0;
const fieldTypes = @json($fieldTypes);

document.getElementById('addField').addEventListener('click', () => {
    const idx = fieldIndex++;
    const typeOptions = Object.entries(fieldTypes)
        .map(([val, label]) => `<option value="${val}">${label}</option>`).join('');

    const row = document.createElement('div');
    row.className = 'card border mb-2 field-row';
    row.innerHTML = `
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Alan Adı (kod)</label>
                    <input type="text" name="fields[${idx}][name]" class="form-control form-control-sm"
                           placeholder="ör. marka" pattern="[a-zA-Z0-9_]+" title="Harf, rakam, _ kullanın">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Etiket</label>
                    <input type="text" name="fields[${idx}][label]" class="form-control form-control-sm"
                           placeholder="ör. Marka">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tür</label>
                    <select name="fields[${idx}][type]" class="form-select form-select-sm field-type-select">
                        ${typeOptions}
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-field">✕</button>
                </div>
                <div class="col-12 select-options d-none">
                    <label class="form-label small mb-1">Seçenekler <small class="text-muted">(virgülle ayır)</small></label>
                    <input type="text" name="fields[${idx}][options]" class="form-control form-control-sm"
                           placeholder="ör. Siyah, Beyaz, Gri">
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
