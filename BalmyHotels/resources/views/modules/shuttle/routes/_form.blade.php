{{-- Shared form fields for create/edit --}}
<div class="mb-3">
    <label class="form-label">Şube <span class="text-danger">*</span></label>
    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
        <option value="">— Seçiniz —</option>
        @foreach($branches as $b)
            <option value="{{ $b->id }}" @selected(old('branch_id', $route->branch_id ?? '') == $b->id)>
                {{ $b->name }}
            </option>
        @endforeach
    </select>
    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Güzergah Adı <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $route->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror"
           placeholder="örn: Kemer Merkez — Otel" required maxlength="100">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Açıklama</label>
    <input type="text" name="description" value="{{ old('description', $route->description ?? '') }}"
           class="form-control @error('description') is-invalid @enderror"
           placeholder="İsteğe bağlı açıklama" maxlength="255">
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
               @checked(old('is_active', $route->is_active ?? true))>
        <label class="form-check-label" for="is_active">Aktif</label>
    </div>
</div>
