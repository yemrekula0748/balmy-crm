@extends('layouts.default')

@push('styles')
<style>
.fault-form-hero {
    background: linear-gradient(135deg, #dc3545 0%, #c0392b 100%);
    border-radius: 16px 16px 0 0;
    padding: 2rem 2rem 1.5rem;
    color: #fff;
}
.fault-form-hero .hero-icon {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.18);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
}
.fault-form-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    overflow: hidden;
}
.fault-form-card .card-body { padding: 2rem; }
.fault-section-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0.75rem;
    padding-bottom: 0.4rem;
    border-bottom: 2px solid #f1f3f5;
}
.fault-form-card .form-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #344054;
    margin-bottom: 0.4rem;
}
.fault-form-card .form-select,
.fault-form-card .form-control {
    border-radius: 10px;
    border-color: #d0d5dd;
    font-size: 0.9rem;
    padding: 0.55rem 0.85rem;
    transition: border-color .2s, box-shadow .2s;
}
.fault-form-card .form-select:focus,
.fault-form-card .form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220,53,69,0.12);
}
.fault-form-card .form-select:disabled {
    background-color: #f8f9fa;
    color: #adb5bd;
}
.completion-badge {
    background: #fff3e0;
    border-left: 3px solid #f97316;
    border-radius: 6px;
    padding: 0.4rem 0.75rem;
    font-size: 0.82rem;
    color: #7c4710;
}
.image-drop-zone {
    border: 2px dashed #d0d5dd;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.image-drop-zone:hover { border-color: #dc3545; background: #fff5f5; }
.image-preview-img {
    max-height: 160px;
    border-radius: 10px;
    border: 2px solid #dee2e6;
    object-fit: cover;
}
.breadcrumb-item + .breadcrumb-item::before { color: #adb5bd; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-exclamation-triangle text-danger me-2"></i>Arıza Bildir</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Arıza Bildir</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            {{-- Form Kartı --}}
            <div class="fault-form-card card">
                {{-- Hero Header --}}
                <div class="fault-form-hero">
                    <div class="d-flex align-items-center gap-3">
                        <div class="hero-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Yeni Arıza Bildirimi</h4>
                            <p class="mb-0 opacity-75 small mt-1">Tüm alanları eksiksiz doldurunuz.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger d-flex gap-2 align-items-start border-0 rounded-3 mb-4"
                             style="background:#fdecea;">
                            <i class="fas fa-circle-xmark mt-1 text-danger"></i>
                            <ul class="mb-0 ps-2">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('faults.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ── BÖLÜM 1: Temel Bilgiler ── --}}
                        <p class="fault-section-label"><i class="fas fa-info-circle me-1"></i>Temel Bilgiler</p>
                        <div class="row g-3 mb-4">
                            {{-- ŞUBE --}}
                            <div class="col-md-6">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branchSelect"
                                        class="form-select @error('branch_id') is-invalid @enderror" required
                                        @if($autoBranchId) disabled @endif>
                                    @if(!$autoBranchId)<option value="">Şube seçin...</option>@endif
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @selected(old('branch_id', $autoBranchId) == $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($autoBranchId)
                                    <input type="hidden" name="branch_id" value="{{ $autoBranchId }}">
                                @endif
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- İLGİLİ DEPARTMAN --}}
                            <div class="col-md-6">
                                <label class="form-label">İlgili Departman <span class="text-danger">*</span></label>
                                <select name="assigned_department_id" id="deptSelect"
                                        class="form-select @error('assigned_department_id') is-invalid @enderror" required>
                                    <option value="">Departman seçin...</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" @selected(old('assigned_department_id') == $dept->id)>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- ARIZA TÜRÜ --}}
                            <div class="col-12">
                                <label class="form-label">Arıza Türü <span class="text-danger">*</span></label>
                                <select name="fault_type_id" id="typeSelect"
                                        class="form-select @error('fault_type_id') is-invalid @enderror" required
                                        @if(!old('assigned_department_id')) disabled @endif>
                                    <option value="">
                                        {{ old('assigned_department_id') ? 'Yükleniyor...' : 'Önce departman seçin...' }}
                                    </option>
                                </select>
                                <div id="completionInfo" class="completion-badge mt-2" style="display:none">
                                    <i class="fas fa-clock me-1 text-warning"></i>
                                    Hedef tamamlanma süresi: <strong id="completionHours"></strong> saat
                                </div>
                                @error('fault_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ── BÖLÜM 2: Konum ── --}}
                        <p class="fault-section-label"><i class="fas fa-map-marker-alt me-1"></i>Konum Bilgisi</p>
                        <div class="row g-3 mb-4">
                            {{-- KONUM --}}
                            <div class="col-md-6">
                                <label class="form-label">Konum <span class="text-danger">*</span></label>
                                <select name="fault_location_id" id="locationSelect"
                                        class="form-select @error('fault_location_id') is-invalid @enderror" required>
                                    <option value="">Konum seçin...</option>
                                    @foreach($faultLocations as $loc)
                                        <option value="{{ $loc->id }}" @selected(old('fault_location_id') == $loc->id)>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fault_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- ALAN --}}
                            <div class="col-md-6">
                                <label class="form-label">Alan <span class="text-muted small fw-normal">(opsiyonel)</span></label>
                                <select name="fault_area_id" id="areaSelect"
                                        class="form-select @error('fault_area_id') is-invalid @enderror" disabled>
                                    <option value="">Önce konum seçin...</option>
                                    @php $oldLoc = old('fault_location_id'); @endphp
                                    @if($oldLoc)
                                        @foreach($faultLocations->firstWhere('id', $oldLoc)?->areas ?? [] as $area)
                                            <option value="{{ $area->id }}" @selected(old('fault_area_id') == $area->id)>{{ $area->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('fault_area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ── BÖLÜM 2.5: Aciliyet Durumu ── --}}
                        <p class="fault-section-label"><i class="fas fa-exclamation-circle me-1"></i>Aciliyet Durumu</p>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="d-flex gap-3 flex-wrap" id="prioritySelector">
                                    @php
                                        $priorities = [
                                            'low'    => ['Normal', '#10b981', 'fa-circle-check', 'Standart öncelik, normal sürede çözülebilir.'],
                                            'medium' => ['Orta',   '#f59e0b', 'fa-triangle-exclamation', 'Yakın takip gerektirir, orta öncelikli.'],
                                            'high'   => ['Acil',   '#ef4444', 'fa-bolt', 'İvedi müdahale gerektirir, yüksek öncelikli.'],
                                        ];
                                    @endphp
                                    @foreach($priorities as $val => [$lbl, $clr, $ico, $desc])
                                    <label class="priority-option flex-fill" style="cursor:pointer;min-width:160px">
                                        <input type="radio" name="priority" value="{{ $val }}" class="d-none priority-radio"
                                               {{ old('priority','medium') === $val ? 'checked' : '' }}>
                                        <div class="priority-opt-box rounded-3 p-3 border-2 text-center transition-all"
                                             style="border:2px solid {{ old('priority','medium') === $val ? $clr : '#e5e7eb' }};
                                                    background:{{ old('priority','medium') === $val ? $clr.'18' : '#fafafa' }};
                                                    border-radius:12px;">
                                            <i class="fas {{ $ico }} fa-lg mb-1 d-block" style="color:{{ $clr }}"></i>
                                            <div class="fw-bold" style="font-size:.85rem;color:{{ $clr }}">{{ $lbl }}</div>
                                            <div class="text-muted mt-1" style="font-size:.68rem;line-height:1.3">{{ $desc }}</div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ── BÖLÜM 3: Açıklama & Fotoğraf ── --}}
                        <p class="fault-section-label"><i class="fas fa-align-left me-1"></i>Detaylar</p>
                        <div class="row g-3 mb-4">
                            {{-- AÇIKLAMA --}}
                            <div class="col-12">
                                <label class="form-label">Açıklama <span class="text-danger">*</span></label>
                                <textarea name="description" rows="4"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Arızayı detaylıca açıklayın..." required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- FOTOĞRAF --}}
                            <div class="col-12">
                                <label class="form-label">Fotoğraf <span class="text-muted small fw-normal">(opsiyonel, max 4 MB)</span></label>
                                <label class="image-drop-zone d-block" for="imageInput" id="imageDropZone">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                                    <span class="text-muted small" id="imageDropText">Fotoğraf seçmek için tıklayın veya sürükleyin</span>
                                    <input type="file" name="image" id="imageInput"
                                           class="d-none @error('image') is-invalid @enderror"
                                           accept="image/*">
                                </label>
                                @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div id="imagePreviewWrap" class="mt-2 text-center" style="display:none">
                                    <img id="imagePreview" src="" alt="Önizleme" class="image-preview-img">
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeImage">
                                            <i class="fas fa-times me-1"></i>Kaldır
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Butonlar --}}
                        <div class="d-flex gap-2 pt-2 border-top">
                            <button type="submit" id="faultSubmitBtn" class="btn btn-danger px-4 py-2 fw-semibold">
                                <i class="fas fa-paper-plane me-2"></i>Arızayı Bildir
                            </button>
                            <a href="{{ route('faults.index') }}" class="btn btn-outline-secondary py-2">
                                <i class="fas fa-times me-1"></i>İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Konum → Alan dropdown
    @php
        $locationsJson = $faultLocations->map(fn($l) => [
            'id'    => $l->id,
            'areas' => $l->areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values()
        ])->keyBy('id');
    @endphp
    const locationsData = @json($locationsJson);

    const locationSel = document.getElementById('locationSelect');
    const areaSel     = document.getElementById('areaSelect');
    const branchSel   = document.getElementById('branchSelect');
    const deptSel     = document.getElementById('deptSelect');

    // Konum değişince alanları güncelle
    locationSel.addEventListener('change', function () {
        const locId = this.value;
        areaSel.innerHTML = '<option value="">Alan seçin... (opsiyonel)</option>';
        if (locId && locationsData[locId]) {
            locationsData[locId].areas.forEach(a => {
                areaSel.innerHTML += `<option value="${a.id}">${a.name}</option>`;
            });
            areaSel.disabled = false;
        } else {
            areaSel.disabled = true;
            areaSel.innerHTML = '<option value="">Önce konum seçin...</option>';
        }
    });

    // Şube değişince departman ve konumları güncelle (AJAX)
    if (branchSel && !branchSel.disabled) {
        branchSel.addEventListener('change', function () {
            const branchId = this.value;

            // Departmanları güncelle
            fetch(`/arizalar/ajax/departmanlar?branch_id=${branchId}`)
                .then(r => r.json())
                .then(list => {
                    deptSel.innerHTML = '<option value="">Departman seçin...</option>';
                    list.forEach(d => deptSel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
                    // Şube değişince ariza türü listesini sıfırla
                    typeSel.innerHTML = '<option value="">Önce departman seçin...</option>';
                    typeSel.disabled = true;
                    completionInfo.style.display = 'none';
                });

            // Konumları güncelle
            fetch(`/arizalar/ajax/konumlar?branch_id=${branchId}`)
                .then(r => r.json())
                .then(list => {
                    locationSel.innerHTML = '<option value="">Konum seçin...</option>';
                    list.forEach(l => locationSel.innerHTML += `<option value="${l.id}">${l.name}</option>`);
                    areaSel.innerHTML = '<option value="">Önce konum seçin...</option>';
                    areaSel.disabled = true;
                    // locationsData'yı güncelle
                    list.forEach(l => { locationsData[l.id] = l; });
                });
        });
    }

    // Arıza türü seçilince hedef süre göster
    const typeSel        = document.getElementById('typeSelect');
    const completionInfo = document.getElementById('completionInfo');
    const completionHrs  = document.getElementById('completionHours');
    typeSel.addEventListener('change', function () {
        const sel = this.options[this.selectedIndex];
        const hrs = sel.dataset.hours;
        if (hrs) {
            completionHrs.textContent = hrs;
            completionInfo.style.display = '';
        } else {
            completionInfo.style.display = 'none';
        }
    });

    // Departman değişince arıza türlerini filtrele
    const oldFaultTypeId = '{{ old('fault_type_id') }}';
    function loadFaultTypes(deptId, selectedId) {
        if (!deptId) {
            typeSel.innerHTML = '<option value="">Önce departman seçin...</option>';
            typeSel.disabled = true;
            completionInfo.style.display = 'none';
            return;
        }
        fetch(`/arizalar/ajax/ariza-turleri?department_id=${deptId}`)
            .then(r => r.json())
            .then(list => {
                typeSel.innerHTML = '<option value="">Arıza türünü seçin...</option>';
                list.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    opt.dataset.hours = t.completion_hours;
                    if (String(t.id) === String(selectedId)) opt.selected = true;
                    typeSel.appendChild(opt);
                });
                typeSel.disabled = false;
                // Seçili değer varsa saat bilgisini göster
                if (typeSel.value) typeSel.dispatchEvent(new Event('change'));
            });
    }
    deptSel.addEventListener('change', function () {
        loadFaultTypes(this.value, '');
    });
    // Sayfa ilk yüklenince eski departman değeri varsa türleri yükle
    if (deptSel.value) loadFaultTypes(deptSel.value, oldFaultTypeId);

    // Fotoğraf önizleme
    document.getElementById('imageInput').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
            document.getElementById('imagePreviewWrap').style.display = '';
            document.getElementById('imageDropText').textContent = file.name;
        }
    });
    document.getElementById('removeImage')?.addEventListener('click', function () {
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreviewWrap').style.display = 'none';
        document.getElementById('imageDropText').textContent = 'Fotoğraf seçmek için tıklayın veya sürükleyin';
    });

    // Sayfa yüklenince konum önceki değere göre alanları doldur
    if (locationSel.value) locationSel.dispatchEvent(new Event('change'));

    // Aciliyet seçici highlight
    document.querySelectorAll('.priority-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const colors = { low: '#10b981', medium: '#f59e0b', high: '#ef4444' };
            document.querySelectorAll('.priority-radio').forEach(function(r) {
                const box = r.closest('label').querySelector('.priority-opt-box');
                box.style.borderColor = '#e5e7eb';
                box.style.background = '#fafafa';
            });
            const box = this.closest('label').querySelector('.priority-opt-box');
            const c = colors[this.value] || '#6b7280';
            box.style.borderColor = c;
            box.style.background = c + '18';
        });
    });

    // Form submit → loading overlay
    document.querySelector('form[action="{{ route('faults.store') }}"]').addEventListener('submit', function(e) {
        const btn = document.getElementById('faultSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Gönderiliyor...';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Arıza bildiriliyor...',
                html: 'Lütfen bekleyin, arıza kaydınız oluşturuluyor ve ilgili departmana bildirim gönderiliyor.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function() { Swal.showLoading(); }
            });
        }
    });
})();
</script>
@endpush
