@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Yeni Denetim Oluştur</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">İç Denetim</a></li>
                <li class="breadcrumb-item active">Yeni Denetim</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul></div>
            @endif

            <form action="{{ route('audit.store') }}" method="POST" enctype="multipart/form-data" id="auditForm">
                @csrf

                {{-- ADIM 1: Şube & Departman --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <span class="badge bg-white text-primary me-2">1</span>
                            Şube & Departman Seçimi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- ŞUBE --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                @if($autoBranchId)
                                    <input type="hidden" name="branch_id" value="{{ $autoBranchId }}">
                                    <input type="text" class="form-control" value="{{ $branches->first()?->name }}" disabled>
                                @else
                                    <select name="branch_id" id="branchSelect"
                                            class="form-select @error('branch_id') is-invalid @enderror" required>
                                        <option value="">Şube seçin...</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @endif
                            </div>

                            {{-- DEPARTMAN --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Departman <span class="text-danger">*</span></label>
                                <select name="department_id" id="deptSelect"
                                        class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Departman seçin...</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ADIM 2: Denetim Tipi --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <span class="badge bg-white text-primary me-2">2</span>
                            Denetim Tipi Seçimi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Denetim Tipi <span class="text-danger">*</span></label>
                                <select name="audit_type_id" id="auditTypeSelect"
                                        class="form-select @error('audit_type_id') is-invalid @enderror" required>
                                    <option value="">Denetim tipi seçin...</option>
                                    @foreach($auditTypes as $t)
                                        <option value="{{ $t->id }}" @selected(old('audit_type_id') == $t->id)>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('audit_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Denetim Notu</label>
                                <textarea name="notes" rows="2" class="form-control"
                                          placeholder="Opsiyonel genel not...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ADIM 3: Uygunsuzluklar --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white">
                            <span class="badge bg-white text-primary me-2">3</span>
                            Uygunsuzluklar
                            <small class="ms-2 fw-normal opacity-75">(opsiyonel — denetim tespitlerini ekleyin)</small>
                        </h5>
                        <button type="button" id="addItemBtn" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Uygunsuzluk Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            {{-- Dinamik satırlar JS ile eklenir --}}
                        </div>
                        <div id="emptyMessage" class="text-center text-muted py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#dee2e6" stroke-width="1.5" viewBox="0 0 24 24" class="mb-2">
                                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            <p class="mb-0">Henüz uygunsuzluk eklenmedi. Denetimde tespit ettiğiniz uygunsuzlukları buraya ekleyin.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary px-4">İptal</a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-1"></i> Denetimi Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- UYGUNSUZLUK SATIRI TEMPLATE --}}
<template id="itemTemplate">
    <div class="nc-item border rounded p-3 mb-3 position-relative" style="background:#fafafa;">
        <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 mt-2 me-2 remove-item-btn" title="Kaldır">
            <i class="fas fa-times"></i>
        </button>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold small">
                    <span class="item-number text-primary fw-bold"></span>. Uygunsuzluk Açıklaması <span class="text-danger">*</span>
                </label>
                <textarea name="ITEM_PLACEHOLDER[description]" rows="2" class="form-control"
                          placeholder="Tespit edilen uygunsuzluğu açıklayın..." required></textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold small">Fotoğraf
                    <span class="text-muted fw-normal">(opsiyonel — kamera veya galeriden seçin)</span>
                </label>
                <input type="file" name="ITEM_PLACEHOLDER[photo]" class="form-control photo-input"
                       accept="image/*" capture="environment">
                <div class="photo-preview mt-2" style="display:none;">
                    <img src="" alt="Önizleme" class="preview-img"
                         style="max-height:160px;border-radius:8px;border:1px solid #dee2e6;">
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
(function () {
    let itemCount = 0;
    const container   = document.getElementById('itemsContainer');
    const emptyMsg    = document.getElementById('emptyMessage');
    const template    = document.getElementById('itemTemplate');
    const addBtn      = document.getElementById('addItemBtn');

    @if($autoBranchId)
    // Tek şube — departmanları önceden yüklenmiş durumda
    @else
    const branchSel = document.getElementById('branchSelect');
    const deptSel   = document.getElementById('deptSelect');

    branchSel?.addEventListener('change', function () {
        const branchId = this.value;
        deptSel.innerHTML = '<option value="">Yükleniyor...</option>';
        if (!branchId) {
            deptSel.innerHTML = '<option value="">Departman seçin...</option>';
            return;
        }
        fetch(`/ic-denetim/ajax/departmanlar?branch_id=${branchId}`)
            .then(r => r.json())
            .then(list => {
                deptSel.innerHTML = '<option value="">Departman seçin...</option>';
                list.forEach(d => deptSel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
            });
    });
    @endif

    function updateNumbers() {
        container.querySelectorAll('.nc-item').forEach((item, i) => {
            item.querySelector('.item-number').textContent = i + 1;
        });
        emptyMsg.style.display = container.children.length === 0 ? '' : 'none';
    }

    addBtn.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const div   = clone.querySelector('.nc-item');

        // Replace ITEM_PLACEHOLDER with actual index
        div.innerHTML = div.innerHTML.replace(/ITEM_PLACEHOLDER/g, `items[${itemCount}]`);
        itemCount++;

        // Photo preview
        div.querySelector('.photo-input').addEventListener('change', function () {
            const preview = this.closest('.nc-item').querySelector('.photo-preview');
            const img     = preview.querySelector('.preview-img');
            if (this.files[0]) {
                img.src = URL.createObjectURL(this.files[0]);
                preview.style.display = '';
            }
        });

        // Remove button
        div.querySelector('.remove-item-btn').addEventListener('click', function () {
            this.closest('.nc-item').remove();
            updateNumbers();
        });

        container.appendChild(div);
        updateNumbers();
    });
})();
</script>
@endpush
