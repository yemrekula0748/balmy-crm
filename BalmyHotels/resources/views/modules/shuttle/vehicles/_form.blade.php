{{-- Shared form fields for vehicle create/edit --}}
<div class="alert border-0 mb-3" style="background:#fbf6ef;color:#7a5c3d;border-radius:10px">
    <i class="fas fa-info-circle me-1"></i>
    Servis araci Beach / Foresta ayrimi olmadan ortak kullanilir.
</div>

<div class="mb-3">
    <label class="form-label">Arac Adi / Tanimi <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $vehicle->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror"
           placeholder="Orn: 34 ABC 123 - Minibus" required maxlength="100">
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
        <label class="form-label">Arac Turu <span class="text-danger">*</span></label>
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
           min="1" max="200" placeholder="Orn: 14" required>
    <div class="form-text">Aracta maksimum tasinabilecek yolcu sayisi.</div>
    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@php
    $selectedRouteIds = collect(old('route_ids', isset($vehicle) ? $vehicle->routes->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $availableRoutes = collect($routes ?? [])->sortBy('name')->values();
    $routeColumnSize = max(1, (int) ceil($availableRoutes->count() / 2));
@endphp

<div class="mb-3">
    <label class="form-label">Gorevli Guzergahlar</label>
    <div class="card border-0 shadow-sm" style="background:#faf7f2;border-radius:12px">
        <div class="card-body">
            <div class="small text-muted mb-3">
                Guzergahlar otel ayrimi olmadan ortaktir. Operasyon ekraninda bu araca yalnizca burada secilen guzergahlar gosterilir.
            </div>

            @if($availableRoutes->isEmpty())
                <div class="alert alert-warning mb-0 py-2 px-3">
                    Once guzergah tanimi eklemelisin.
                </div>
            @else
                <div class="row g-3">
                    @foreach($availableRoutes->chunk($routeColumnSize) as $routeColumn)
                        <div class="col-md-6">
                            <div class="border rounded-3 h-100 p-3 bg-white">
                                @foreach($routeColumn as $route)
                                    <div class="form-check mb-2">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="route_ids[]"
                                            value="{{ $route->id }}"
                                            id="route_{{ $route->id }}"
                                            @checked(in_array((int) $route->id, $selectedRouteIds, true))
                                        >
                                        <label class="form-check-label" for="route_{{ $route->id }}">
                                            {{ $route->name }}
                                            @if($route->description)
                                                <small class="text-muted d-block">{{ $route->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @error('route_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('route_ids.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
               @checked(old('is_active', $vehicle->is_active ?? true))>
        <label class="form-check-label" for="is_active">Aktif</label>
    </div>
</div>
