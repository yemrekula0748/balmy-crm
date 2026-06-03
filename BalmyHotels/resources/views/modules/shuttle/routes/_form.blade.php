{{-- Shared form fields for create/edit --}}
<div class="alert border-0 mb-3" style="background:#fbf6ef;color:#7a5c3d;border-radius:10px">
    <i class="fas fa-info-circle me-1"></i>
    Bu guzergah tum oteller icin ortak kullanilir; otel secimi yapilmaz.
</div>

<div class="mb-3">
    <label class="form-label">Guzergah Adi <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $route->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror"
           placeholder="Orn: Kemer Merkez - Otel" required maxlength="100">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Aciklama</label>
    <input type="text" name="description" value="{{ old('description', $route->description ?? '') }}"
           class="form-control @error('description') is-invalid @enderror"
           placeholder="Istege bagli aciklama" maxlength="255">
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
               @checked(old('is_active', $route->is_active ?? true))>
        <label class="form-check-label" for="is_active">Aktif</label>
    </div>
</div>
