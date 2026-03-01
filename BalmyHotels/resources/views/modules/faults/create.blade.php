@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Bildir</h4></div>
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Yeni Arıza Bildirimi</h4>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul></div>
                    @endif

                    <form action="{{ route('faults.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            {{-- ŞUBe --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
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
                                <label class="form-label fw-semibold">İlgili Departman <span class="text-danger">*</span></label>
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
                                <label class="form-label fw-semibold">Arıza Türü <span class="text-danger">*</span></label>
                                <select name="fault_type_id" id="typeSelect"
                                        class="form-select @error('fault_type_id') is-invalid @enderror" required>
                                    <option value="">Arıza türünü seçin...</option>
                                    @foreach($faultTypes as $ft)
                                        <option value="{{ $ft->id }}"
                                                data-hours="{{ $ft->completion_hours }}"
                                                @selected(old('fault_type_id') == $ft->id)>
                                            {{ $ft->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="completionInfo" class="small text-muted mt-1" style="display:none">
                                    <i class="fas fa-clock me-1"></i>
                                    Bu arıza türü için hedef tamamlanma süresi: <strong id="completionHours"></strong> saat
                                </div>
                                @error('fault_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- KONUM --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konum <span class="text-danger">*</span></label>
                                <select name="fault_location_id" id="locationSelect" class="form-select @error('fault_location_id') is-invalid @enderror" required>
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
                                <label class="form-label fw-semibold">Alan</label>
                                <select name="fault_area_id" id="areaSelect" class="form-select @error('fault_area_id') is-invalid @enderror" disabled>
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

                            {{-- AÇIKLAMA --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama <span class="text-danger">*</span></label>
                                <textarea name="description" rows="4"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Arızayı detaylıca açıklayın..." required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- FOTOĞRAF --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Fotoğraf <span class="text-muted small">(opsiyonel, max 4 MB)</span></label>
                                <input type="file" name="image" id="imageInput"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/*">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="imagePreviewWrap" class="mt-2" style="display:none">
                                    <img id="imagePreview" src="" alt="Önizleme" style="max-height:140px;border-radius:8px;border:1px solid #dee2e6;">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-paper-plane me-1"></i> Arızayı Bildir
                            </button>
                            <a href="{{ route('faults.index') }}" class="btn btn-secondary">İptal</a>
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

    // Fotoğraf önizleme
    document.getElementById('imageInput').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
            document.getElementById('imagePreviewWrap').style.display = '';
        }
    });

    // Sayfa yüklenince konum önceki değere göre alanları doldur
    if (locationSel.value) locationSel.dispatchEvent(new Event('change'));
})();
</script>
@endpush
