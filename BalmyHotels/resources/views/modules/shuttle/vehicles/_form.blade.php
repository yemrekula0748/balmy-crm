{{-- Shared form fields for vehicle create/edit --}}
<div class="mb-3">
    <label class="form-label">Şube <span class="text-danger">*</span></label>
    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
        <option value="">— Seçiniz —</option>
        @foreach($branches as $b)
            <option value="{{ $b->id }}" @selected(old('branch_id', $vehicle->branch_id ?? '') == $b->id)>
                {{ $b->name }}
            </option>
        @endforeach
    </select>
    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Araç Adı / Tanımı <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $vehicle->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror"
           placeholder="örn: 34 ABC 123 - Minibüs" required maxlength="100">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Plaka</label>
        <input type="text" name="plate" value="{{ old('plate', $vehicle->plate ?? '') }}"
               class="form-control @error('plate') is-invalid @enderror"
               placeholder="34ABC123" maxlength="20">
        @error('plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Araç Türü <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach($types as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $vehicle->type ?? '') == $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Yolcu Kapasitesi <span class="text-danger">*</span></label>
    <input type="number" name="capacity" value="{{ old('capacity', $vehicle->capacity ?? '') }}"
           class="form-control @error('capacity') is-invalid @enderror"
           min="1" max="200" placeholder="örn: 14" required>
    <div class="form-text">Araçta maksimum taşınabilecek yolcu sayısı.</div>
    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
               @checked(old('is_active', $vehicle->is_active ?? true))>
        <label class="form-check-label" for="is_active">Aktif</label>
    </div>
</div>
