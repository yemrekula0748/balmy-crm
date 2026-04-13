{{-- Shared form for create & edit --}}
@php
    $selectedMenuIds = $showcase ? $showcase->items->pluck('qr_menu_id')->toArray() : old('menus', []);
    $labelsMap       = [];
    if ($showcase) {
        foreach ($showcase->items as $item) {
            $labelsMap[$item->qr_menu_id] = $item->label;
        }
    }
@endphp

<div class="row g-3">
    {{-- Sol: Vitrin Ayarları --}}
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">Vitrin Ayarları</h6></div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Başlık <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $showcase?->title) }}"
                           class="form-control @error('title') is-invalid @enderror"
                           placeholder="Ör: Balmy Beach Menüleri" id="title-input">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Slug (URL)
                        <span class="text-muted fw-normal">— boş bırakılırsa başlıktan otomatik üretilir</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text text-muted small">/vitrin/</span>
                        <input type="text" name="slug" id="slug-input"
                               value="{{ old('slug', $showcase?->slug) }}"
                               class="form-control @error('slug') is-invalid @enderror"
                               placeholder="balmy-beach">
                    </div>
                    @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Alt Başlık / Açıklama</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $showcase?->subtitle) }}"
                           class="form-control @error('subtitle') is-invalid @enderror"
                           placeholder="Ör: Tüm restoranlarımızın dijital menülerine göz atın">
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Vurgu Rengi</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="accent_color" id="accent-color"
                               value="{{ old('accent_color', $showcase?->accent_color ?? '#c19b77') }}"
                               class="form-control form-control-color" style="width:48px;height:38px">
                        <input type="text" id="accent-hex" value="{{ old('accent_color', $showcase?->accent_color ?? '#c19b77') }}"
                               class="form-control" style="max-width:120px" placeholder="#c19b77">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active"
                               {{ old('is_active', $showcase?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is-active">Aktif (public erişime açık)</label>
                    </div>
                </div>

                @if($showcase)
                <div class="mb-1 p-2 rounded" style="background:#f8f4f0">
                    <div class="small text-muted mb-1">Public URL:</div>
                    <a href="{{ $showcase->publicUrl() }}" target="_blank" class="small fw-semibold" style="color:#c19b77">
                        {{ $showcase->publicUrl() }}
                        <i class="fa fa-external-link-alt ms-1" style="font-size:.7rem"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sağ: Menü Seçimi --}}
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Vitrine Eklenecek Menüler</h6>
                <span class="badge bg-secondary" id="selected-count">0 seçili</span>
            </div>
            <div class="card-body p-0">
                {{-- Filtre --}}
                <div class="p-3 border-bottom">
                    <input type="text" id="menu-search" class="form-control form-control-sm" placeholder="🔍  Menü ara…">
                </div>

                <div id="menu-list" style="max-height:420px;overflow-y:auto">
                    @forelse($menus as $menu)
                    @php
                        $isSelected = in_array($menu->id, (array)$selectedMenuIds);
                        $label      = $labelsMap[$menu->id] ?? '';
                    @endphp
                    <div class="menu-choice-row d-flex align-items-center gap-3 p-3 border-bottom {{ $isSelected ? 'bg-selected' : '' }}"
                         data-name="{{ strtolower($menu->name . ' ' . ($menu->getTitle('tr') ?? '')) }}">

                        {{-- Checkbox --}}
                        <div style="flex-shrink:0">
                            <input class="form-check-input menu-checkbox" type="checkbox"
                                   name="menus[]" value="{{ $menu->id }}"
                                   id="menu-{{ $menu->id }}"
                                   {{ $isSelected ? 'checked' : '' }}>
                        </div>

                        {{-- Logo --}}
                        <div style="flex-shrink:0">
                            @if($menu->logo)
                            <img src="{{ asset('uploads/'.$menu->logo) }}" alt=""
                                 class="rounded-circle" style="width:40px;height:40px;object-fit:cover">
                            @elseif($menu->cover_image)
                            <img src="{{ asset('uploads/'.$menu->cover_image) }}" alt=""
                                 class="rounded" style="width:40px;height:40px;object-fit:cover">
                            @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                 style="width:40px;height:40px;background:{{ $menu->theme_color ?? '#c19b77' }};font-size:.9rem;font-weight:700">
                                {{ mb_substr($menu->name, 0, 1) }}
                            </div>
                            @endif
                        </div>

                        {{-- Bilgi --}}
                        <label class="flex-grow-1 mb-0" for="menu-{{ $menu->id }}" style="cursor:pointer">
                            <div class="fw-semibold small">{{ $menu->getTitle('tr') ?? $menu->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">/menu/{{ $menu->name }}</div>
                        </label>

                        {{-- Etiket override --}}
                        <div class="label-field" style="min-width:150px;{{ $isSelected ? '' : 'opacity:.35;pointer-events:none' }}">
                            <input type="text" name="labels[{{ $menu->id }}]"
                                   value="{{ old('labels.'.$menu->id, $label) }}"
                                   class="form-control form-control-sm"
                                   placeholder="Görünen ad (opsiyonel)">
                        </div>
                    </div>
                    @empty
                    <div class="text-muted text-center py-5">Aktif QR menü bulunamadı.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('qrmenus.showcases.index') }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn text-white px-4" style="background:#c19b77">
                <i class="fa fa-save me-1"></i>
                {{ $showcase ? 'Güncelle' : 'Oluştur' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .bg-selected { background: #fff8f0 !important; }
    .menu-choice-row { transition: background .15s; }
</style>
<script>
(function(){
    // Renk senkronizasyonu
    const colorPicker = document.getElementById('accent-color');
    const colorHex    = document.getElementById('accent-hex');
    colorPicker?.addEventListener('input', () => { colorHex.value = colorPicker.value; });
    colorHex?.addEventListener('input', () => {
        const v = colorHex.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) colorPicker.value = v;
    });

    // Slug otomatik üret
    const titleInput = document.getElementById('title-input');
    const slugInput  = document.getElementById('slug-input');
    let slugEdited = slugInput?.value?.length > 0;
    slugInput?.addEventListener('input', () => { slugEdited = true; });
    titleInput?.addEventListener('input', () => {
        if (!slugEdited) {
            slugInput.value = titleInput.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });

    // Checkbox → label field aktif/pasif + highlight
    function updateCount() {
        const n = document.querySelectorAll('.menu-checkbox:checked').length;
        document.getElementById('selected-count').textContent = n + ' seçili';
    }
    document.querySelectorAll('.menu-checkbox').forEach(cb => {
        cb.addEventListener('change', function(){
            const row   = this.closest('.menu-choice-row');
            const label = row.querySelector('.label-field');
            if (this.checked) {
                row.classList.add('bg-selected');
                label.style.opacity = '1';
                label.style.pointerEvents = 'auto';
            } else {
                row.classList.remove('bg-selected');
                label.style.opacity = '.35';
                label.style.pointerEvents = 'none';
            }
            updateCount();
        });
    });
    updateCount();

    // Arama
    document.getElementById('menu-search')?.addEventListener('input', function(){
        const q = this.value.toLowerCase();
        document.querySelectorAll('.menu-choice-row').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
