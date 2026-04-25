@extends('layouts.default')

@section('content')
<style>
    :root { --brand: #c19b77; --brand-light: #fdf5ee; --brand-mid: #e8d5c4; }

    .audit-step-card {
        border: 1px solid #f0ebe6;
        border-radius: 12px;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .audit-step-card .step-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 1rem 1.5rem;
        background: var(--brand-light);
        border-bottom: 1px solid var(--brand-mid);
    }
    .audit-step-card .step-header .step-num {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: var(--brand);
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .audit-step-card .step-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: .95rem;
        color: #3d2b1f;
    }
    .audit-step-card .step-header small {
        color: #9e836f;
        font-size: .78rem;
    }
    .audit-step-card .step-body {
        padding: 1.25rem 1.5rem;
        background: #fff;
    }

    .nc-item {
        border: 1px solid #e8d5c4;
        border-left: 4px solid var(--brand);
        border-radius: 10px;
        padding: 1.1rem 1.25rem;
        margin-bottom: .9rem;
        background: #fffcfa;
        position: relative;
        transition: box-shadow .15s;
    }
    .nc-item:hover { box-shadow: 0 2px 10px rgba(193,155,119,.15); }
    .nc-item .nc-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .78rem;
        font-weight: 600;
        color: var(--brand);
        background: var(--brand-light);
        border-radius: 6px;
        padding: 2px 10px;
        margin-bottom: .65rem;
    }
    .nc-item textarea, .nc-item input[type=file] {
        border-radius: 8px;
        font-size: .875rem;
    }
    .nc-item .remove-item-btn {
        position: absolute;
        top: .75rem; right: .75rem;
        padding: 3px 8px;
        font-size: .72rem;
        border-radius: 6px;
    }

    #emptyMessage {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #b0a09a;
    }
    #emptyMessage svg { display: block; margin: 0 auto .75rem; }

    .btn-brand {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        font-weight: 600;
        letter-spacing: .01em;
    }
    .btn-brand:hover { background: #a8835f; border-color: #a8835f; color: #fff; }
    .btn-brand-outline {
        border: 1.5px solid var(--brand);
        color: var(--brand);
        background: transparent;
        font-weight: 600;
        font-size: .8rem;
    }
    .btn-brand-outline:hover { background: var(--brand); color: #fff; }

    .form-label { font-size: .8rem; font-weight: 600; color: #5a4a3f; margin-bottom: .35rem; }
    .form-select, .form-control { border-radius: 8px; font-size: .875rem; border-color: #e0d6cf; }
    .form-select:focus, .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(193,155,119,.18); }

    .page-hero {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 1.5rem;
        padding: 1.1rem 1.5rem;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0ebe6;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .page-hero .hero-icon {
        width: 46px; height: 46px;
        border-radius: 10px;
        background: var(--brand-light);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .page-hero h5 { margin: 0; font-weight: 700; color: #2d1f17; font-size: 1rem; }
    .page-hero p  { margin: 0; font-size: .78rem; color: #9e836f; }

    .add-nc-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: .75rem 1.5rem;
        background: var(--brand-light);
        border-bottom: 1px solid var(--brand-mid);
    }
    .add-nc-bar .bar-title {
        display: flex; align-items: center; gap: 10px;
    }
    .nc-count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px;
        background: var(--brand); color: #fff;
        font-size: .68rem; font-weight: 700;
        border-radius: 10px; padding: 0 5px;
    }
</style>

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Denetim</h4>
                <span>Yeni bir iç denetim kaydı oluşturun</span>
            </div>
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
        <div class="col-xl-8 col-lg-10">

            @if($errors->any())
            <div class="alert alert-danger d-flex gap-2 align-items-start border-0 rounded-3 shadow-sm mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0 mt-1"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <ul class="mb-0 ps-2">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- Sayfa Hero Başlığı --}}
            <div class="page-hero">
                <div class="hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="#c19b77" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div>
                    <h5>Denetim Formu</h5>
                    <p>Aşağıdaki adımları doldurarak yeni bir denetim kaydı oluşturun.</p>
                </div>
                <a href="{{ route('audit.index') }}" class="ms-auto btn btn-light btn-sm d-flex align-items-center gap-1" style="font-size:.8rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Geri Dön
                </a>
            </div>

            <form action="{{ route('audit.store') }}" method="POST" enctype="multipart/form-data" id="auditForm">
                @csrf

                {{-- ADIM 1: Şube & Departman --}}
                <div class="audit-step-card">
                    <div class="step-header">
                        <div class="step-num">1</div>
                        <div>
                            <h6>Şube &amp; Departman</h6>
                            <small>Denetimin gerçekleştirileceği şube ve departmanı seçin</small>
                        </div>
                    </div>
                    <div class="step-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                @if($autoBranchId)
                                    <input type="hidden" name="branch_id" value="{{ $autoBranchId }}">
                                    <input type="text" class="form-control bg-light" value="{{ $branches->first()?->name }}" disabled>
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
                            <div class="col-md-6">
                                <label class="form-label">Departman <span class="text-danger">*</span></label>
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

                {{-- ADIM 2: Denetim Tipi & Not --}}
                <div class="audit-step-card">
                    <div class="step-header">
                        <div class="step-num">2</div>
                        <div>
                            <h6>Denetim Tipi &amp; Not</h6>
                            <small>Denetim türünü seçin, isterseniz genel bir not ekleyin</small>
                        </div>
                    </div>
                    <div class="step-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Denetim Tipi <span class="text-danger">*</span></label>
                                <select name="audit_type_id" id="auditTypeSelect"
                                        class="form-select @error('audit_type_id') is-invalid @enderror" required>
                                    <option value="">Denetim tipi seçin...</option>
                                    @foreach($auditTypes as $t)
                                        <option value="{{ $t->id }}" @selected(old('audit_type_id') == $t->id)>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @error('audit_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">
                                    Genel Not
                                    <span class="text-muted fw-normal ms-1" style="font-size:.72rem;">(opsiyonel)</span>
                                </label>
                                <textarea name="notes" rows="3" class="form-control"
                                          placeholder="Denetimle ilgili genel bir not...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ADIM 3: Uygunsuzluklar --}}
                <div class="audit-step-card">
                    <div class="add-nc-bar">
                        <div class="bar-title">
                            <div class="step-num" style="width:30px;height:30px;border-radius:50%;background:var(--brand);color:#fff;font-size:.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">3</div>
                            <div>
                                <span style="font-weight:600;font-size:.95rem;color:#3d2b1f;">Uygunsuzluklar</span>
                                <span id="ncCountBadge" class="nc-count-badge ms-2" style="display:none;">0</span>
                                <br><small style="color:#9e836f;font-size:.78rem;">Denetimde tespit ettiğiniz uygunsuzlukları ekleyin</small>
                            </div>
                        </div>
                        <button type="button" id="addItemBtn" class="btn btn-brand-outline d-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Uygunsuzluk Ekle
                        </button>
                    </div>
                    <div style="padding:1.25rem 1.5rem;background:#fff;">
                        <div id="itemsContainer"></div>
                        <div id="emptyMessage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" stroke="#ddd" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            <p class="mb-0" style="font-size:.85rem;">Henüz uygunsuzluk eklenmedi. Tespit ettiğiniz uygunsuzlukları yukarıdaki butona tıklayarak ekleyin.</p>
                        </div>
                    </div>
                </div>

                {{-- Form Aksiyonları --}}
                <div class="d-flex align-items-center justify-content-end gap-2 pb-4">
                    <a href="{{ route('audit.index') }}" class="btn btn-light px-4" style="font-size:.875rem;">
                        İptal
                    </a>
                    <button type="submit" class="btn btn-brand px-5 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Denetimi Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- UYGUNSUZLUK SATIRI TEMPLATE --}}
<template id="itemTemplate">
    <div class="nc-item">
        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Kaldır">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="nc-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="item-number"></span>. Uygunsuzluk
        </div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Açıklama <span class="text-danger">*</span></label>
                <textarea name="ITEM_PLACEHOLDER[description]" rows="2" class="form-control"
                          placeholder="Tespit edilen uygunsuzluğu kısaca açıklayın..." required></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">
                    Fotoğraf
                    <span class="text-muted fw-normal ms-1" style="font-size:.72rem;">(opsiyonel)</span>
                </label>
                <input type="file" name="ITEM_PLACEHOLDER[photo]" class="form-control photo-input"
                       accept="image/*" capture="environment">
                <div class="photo-preview mt-2" style="display:none;">
                    <img src="" alt="Önizleme" class="preview-img"
                         style="max-height:150px;border-radius:8px;border:1px solid #e8d5c4;object-fit:cover;">
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
    const container = document.getElementById('itemsContainer');
    const emptyMsg  = document.getElementById('emptyMessage');
    const template  = document.getElementById('itemTemplate');
    const addBtn    = document.getElementById('addItemBtn');
    const badge     = document.getElementById('ncCountBadge');

    @if($autoBranchId)
    // Tek şube — departmanlar önceden yüklü
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

    function updateUI() {
        const count = container.querySelectorAll('.nc-item').length;
        container.querySelectorAll('.nc-item').forEach((item, i) => {
            item.querySelector('.item-number').textContent = i + 1;
        });
        emptyMsg.style.display = count === 0 ? '' : 'none';
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    }

    addBtn.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const div   = clone.querySelector('.nc-item');
        div.innerHTML = div.innerHTML.replace(/ITEM_PLACEHOLDER/g, `items[${itemCount}]`);
        itemCount++;

        div.querySelector('.photo-input').addEventListener('change', function () {
            const preview = this.closest('.nc-item').querySelector('.photo-preview');
            const img     = preview.querySelector('.preview-img');
            if (this.files[0]) {
                img.src = URL.createObjectURL(this.files[0]);
                preview.style.display = '';
            }
        });

        div.querySelector('.remove-item-btn').addEventListener('click', function () {
            this.closest('.nc-item').remove();
            updateUI();
        });

        container.appendChild(div);
        updateUI();
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    updateUI();
})();
</script>
@endpush
