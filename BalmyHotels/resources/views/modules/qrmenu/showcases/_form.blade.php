{{-- Shared form for create & edit --}}
@php
    // Validation hatasında gönderilen sıra; normal açılışta veritabanındaki karma içerik sırası.
    if (old('item_types') !== null) {
        $initialItems = collect(array_values((array) old('item_types')))
            ->map(fn($type, $index) => [
                'type'  => $type,
                'id'    => (int) (old('item_ids')[$index] ?? 0),
                'label' => old('labels')[$index] ?? '',
            ])
            ->filter(fn($item) => in_array($item['type'], ['menu', 'survey'], true) && $item['id'] > 0)
            ->values()
            ->all();
    } else {
        $initialItems = $showcase
            ? $showcase->items->sortBy('sort_order')->map(fn($item) => [
                'type'  => $item->survey_id ? 'survey' : 'menu',
                'id'    => (int) ($item->survey_id ?: $item->qr_menu_id),
                'label' => $item->label ?? '',
            ])->values()->all()
            : [];
    }

    $selectedKeys = collect($initialItems)
        ->map(fn($item) => $item['type'] . ':' . $item['id'])
        ->all();
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

    {{-- Sağ: Menü / Anket Seçimi & Sıralama --}}
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Vitrine Eklenecek İçerikler</h6>
                <span class="badge bg-secondary" id="selected-count">0 seçili</span>
            </div>
            <div class="card-body p-0">

                {{-- SIRALAMA ALANI --}}
                <div class="p-3 border-bottom" style="background:#f8f9fa">
                    <div class="small text-muted mb-2">
                        <i class="fa fa-arrows-alt me-1"></i>
                        <strong>Seçili Menü ve Anketler</strong> &mdash; tutup sürükleyerek sıralayabilirsiniz
                    </div>
                    <div id="sort-zone" style="min-height:44px">
                        <div id="sort-empty" class="text-center text-muted py-3 small"
                             style="border:2px dashed #dee2e6;border-radius:6px">
                            Henüz içerik seçilmedi — aşağıdan menü veya anket ekleyin
                        </div>
                    </div>
                </div>

                {{-- İÇERİK ARAMA --}}
                <div class="p-3 border-bottom">
                    <input type="text" id="content-search" class="form-control form-control-sm"
                           placeholder="🔍  Menü veya anket ara…">
                </div>

                {{-- MENÜ SEÇİM LİSTESİ --}}
                <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between"
                     style="background:#f8f9fa">
                    <strong class="small"><i class="fa fa-utensils me-1 text-muted"></i> QR Menüler</strong>
                    <span class="badge bg-light text-dark">{{ $menus->count() }}</span>
                </div>
                <div class="content-list" style="max-height:250px;overflow-y:auto">
                    @forelse($menus as $menu)
                    <div class="content-choice-row d-flex align-items-center gap-3 p-3 border-bottom"
                         data-type="menu"
                         data-id="{{ $menu->id }}"
                         data-name="{{ strtolower($menu->name . ' ' . ($menu->getTitle('tr') ?? '')) }}">

                        <div style="flex-shrink:0">
                            <input class="form-check-input content-checkbox" type="checkbox"
                                   id="content-cb-menu-{{ $menu->id }}"
                                   {{ in_array('menu:'.$menu->id, $selectedKeys, true) ? 'checked' : '' }}>
                        </div>

                        <div style="flex-shrink:0">
                            @if($menu->logo)
                            <img src="{{ asset('uploads/'.$menu->logo) }}" alt=""
                                 class="rounded-circle" style="width:38px;height:38px;object-fit:cover">
                            @elseif($menu->cover_image)
                            <img src="{{ asset('uploads/'.$menu->cover_image) }}" alt=""
                                 class="rounded" style="width:38px;height:38px;object-fit:cover">
                            @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                 style="width:38px;height:38px;background:{{ $menu->theme_color ?? '#c19b77' }};font-size:.85rem;font-weight:700">
                                {{ mb_substr($menu->name, 0, 1) }}
                            </div>
                            @endif
                        </div>

                        <label class="flex-grow-1 mb-0" for="content-cb-menu-{{ $menu->id }}" style="cursor:pointer">
                            <div class="fw-semibold small">
                                {{ $menu->getTitle('tr') ?? $menu->name }}
                                @unless($menu->is_active)
                                    <span class="badge bg-secondary ms-1" style="font-size:.65rem">Pasif</span>
                                @endunless
                                @if($menu->branch)
                                    <span class="badge bg-light text-dark ms-1" style="font-size:.65rem">{{ $menu->branch->name }}</span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:.75rem">/menu/{{ $menu->name }}</div>
                        </label>
                    </div>
                    @empty
                    <div class="text-muted text-center py-5">Hiç QR menü bulunamadı.</div>
                    @endforelse
                </div>

                {{-- ANKET SEÇİM LİSTESİ --}}
                <div class="px-3 py-2 border-top border-bottom d-flex align-items-center justify-content-between"
                     style="background:#f8f9fa">
                    <strong class="small"><i class="fa fa-clipboard-list me-1 text-muted"></i> Anketler</strong>
                    <span class="badge bg-light text-dark">{{ $surveys->count() }}</span>
                </div>
                <div class="content-list" style="max-height:250px;overflow-y:auto">
                    @forelse($surveys as $survey)
                    <div class="content-choice-row d-flex align-items-center gap-3 p-3 border-bottom"
                         data-type="survey"
                         data-id="{{ $survey->id }}"
                         data-name="{{ strtolower($survey->slug . ' ' . $survey->getTitle('tr')) }}">

                        <div style="flex-shrink:0">
                            <input class="form-check-input content-checkbox" type="checkbox"
                                   id="content-cb-survey-{{ $survey->id }}"
                                   {{ in_array('survey:'.$survey->id, $selectedKeys, true) ? 'checked' : '' }}>
                        </div>

                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                             style="width:38px;height:38px;background:#4361ee;font-size:1rem;flex-shrink:0">
                            <i class="fa fa-clipboard-check"></i>
                        </div>

                        <label class="flex-grow-1 mb-0" for="content-cb-survey-{{ $survey->id }}" style="cursor:pointer">
                            <div class="fw-semibold small">
                                {{ $survey->getTitle('tr') }}
                                @unless($survey->is_active)
                                    <span class="badge bg-secondary ms-1" style="font-size:.65rem">Pasif</span>
                                @endunless
                                @if($survey->branch)
                                    <span class="badge bg-light text-dark ms-1" style="font-size:.65rem">{{ $survey->branch->name }}</span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:.75rem">
                                /anket/{{ $survey->slug }} · {{ count($survey->languages ?? []) }} dil
                            </div>
                        </label>
                    </div>
                    @empty
                    <div class="text-muted text-center py-5">Hiç anket bulunamadı.</div>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<style>
.sort-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 6px;
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sort-card .drag-handle {
    cursor: grab;
    color: #bbb;
    font-size: 1.25rem;
    user-select: none;
    flex-shrink: 0;
    line-height: 1;
}
.sort-card .drag-handle:active { cursor: grabbing; }
.sort-card.sortable-ghost  { opacity: .35; background: #fff8f0; }
.sort-card.sortable-chosen { box-shadow: 0 4px 14px rgba(0,0,0,.15); }
.content-choice-row { transition: background .15s; }
.content-choice-row.is-selected { background: #fff8f0; }
</style>
<script>
(function(){
    // ── Renk senkronizasyonu ──────────────────────────────────────────────────
    const colorPicker = document.getElementById('accent-color');
    const colorHex    = document.getElementById('accent-hex');
    colorPicker?.addEventListener('input', () => { colorHex.value = colorPicker.value; });
    colorHex?.addEventListener('input', () => {
        const v = colorHex.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) colorPicker.value = v;
    });

    // ── Slug otomatik üret ────────────────────────────────────────────────────
    const titleInput = document.getElementById('title-input');
    const slugInput  = document.getElementById('slug-input');
    let slugEdited = (slugInput?.value?.length ?? 0) > 0;
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

    // ── Menü ve anket verileri (PHP'den) ─────────────────────────────────────
    @php
        $menuDataArr = $menus->mapWithKeys(fn($m) => ['menu:'.$m->id => [
            'type'   => 'menu',
            'id'     => $m->id,
            'title'  => $m->getTitle('tr') ?? $m->name,
            'url'    => '/menu/' . $m->name,
            'logo'   => $m->logo ? asset('uploads/'.$m->logo) : ($m->cover_image ? asset('uploads/'.$m->cover_image) : null),
            'color'  => $m->theme_color ?? '#c19b77',
            'letter' => mb_substr($m->name, 0, 1),
            'badge'  => 'Menü',
        ]]);

        $surveyDataArr = $surveys->mapWithKeys(fn($s) => ['survey:'.$s->id => [
            'type'   => 'survey',
            'id'     => $s->id,
            'title'  => $s->getTitle('tr'),
            'url'    => '/anket/' . $s->slug,
            'logo'   => null,
            'color'  => '#4361ee',
            'letter' => '📋',
            'badge'  => 'Anket',
        ]]);

        $contentDataArr = $menuDataArr->merge($surveyDataArr);
    @endphp
    const contentData = @json($contentDataArr);
    const initialItems = @json(array_values($initialItems));

    // ── Sort-zone DOM ─────────────────────────────────────────────────────────
    const sortZone  = document.getElementById('sort-zone');
    const sortEmpty = document.getElementById('sort-empty');
    const countBadge = document.getElementById('selected-count');

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function makeAvatar(d) {
        if (d.logo) {
            return `<img src="${escapeHtml(d.logo)}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover" alt="">`;
        }
        return `<div class="rounded-circle d-flex align-items-center justify-content-center text-white"
            style="width:32px;height:32px;background:${escapeHtml(d.color)};font-size:.8rem;font-weight:700;flex-shrink:0">${escapeHtml(d.letter)}</div>`;
    }

    function createCard(type, itemId, labelVal) {
        const key = `${type}:${itemId}`;
        const d = contentData[key];
        if (!d) return null;

        const card = document.createElement('div');
        card.className = 'sort-card';
        card.dataset.type = type;
        card.dataset.id = String(itemId);
        card.innerHTML = `
            <span class="drag-handle" title="Sürükle">&#8942;&#8942;</span>
            <input type="hidden" name="item_types[]" value="${escapeHtml(type)}">
            <input type="hidden" name="item_ids[]" value="${escapeHtml(itemId)}">
            ${makeAvatar(d)}
            <div class="flex-grow-1" style="min-width:0">
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-light text-dark" style="font-size:.6rem">${escapeHtml(d.badge)}</span>
                    <div class="fw-semibold small text-truncate">${escapeHtml(d.title)}</div>
                </div>
                <div class="text-muted" style="font-size:.72rem">${escapeHtml(d.url)}</div>
            </div>
            <input type="text" name="labels[]" value="${escapeHtml(labelVal)}"
                   class="form-control form-control-sm" style="min-width:110px;max-width:145px"
                   placeholder="Görünen ad (ops.)">
            <button type="button" class="btn btn-link text-muted p-0 duplicate-btn"
                    title="Aynı içeriği tekrar ekle" style="font-size:.9rem;line-height:1">
                <i class="fa fa-plus-circle"></i>
            </button>
            <button type="button" class="btn btn-link text-danger p-0 ms-1 remove-btn"
                    title="Kaldır" style="font-size:.9rem;line-height:1">
                <i class="fa fa-times"></i>
            </button>`;
        card.querySelector('.duplicate-btn').addEventListener('click', () => addItem(type, itemId, ''));
        card.querySelector('.remove-btn').addEventListener('click', () => removeCard(card));
        return card;
    }

    function addItem(type, itemId, labelVal) {
        const card = createCard(type, itemId, labelVal ?? '');
        if (!card) return;

        sortZone.appendChild(card);
        const id = String(itemId);
        const cb = document.getElementById(`content-cb-${type}-${id}`);
        if (cb && !cb.checked) cb.checked = true;
        document.querySelector(`.content-choice-row[data-type="${type}"][data-id="${id}"]`)?.classList.add('is-selected');
        updateUI();
    }

    function removeCard(card) {
        const type = card.dataset.type;
        const id = card.dataset.id;
        card.remove();
        const remaining = sortZone.querySelectorAll(`.sort-card[data-type="${type}"][data-id="${id}"]`).length;
        if (remaining === 0) {
            const cb = document.getElementById(`content-cb-${type}-${id}`);
            if (cb) cb.checked = false;
            document.querySelector(`.content-choice-row[data-type="${type}"][data-id="${id}"]`)?.classList.remove('is-selected');
        }
        updateUI();
    }

    function updateUI() {
        const n = sortZone.querySelectorAll('.sort-card').length;
        sortEmpty.style.display  = n ? 'none' : 'block';
        countBadge.textContent   = n + ' seçili';
    }

    // ── Başlangıç verilerini yükle (sort_order sırasıyla) ────────────────────
    initialItems.forEach(item => {
        addItem(item.type, item.id, item.label ?? '');
    });
    updateUI();

    // ── SortableJS ────────────────────────────────────────────────────────────
    Sortable.create(sortZone, {
        animation: 150,
        handle:     '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass:'sortable-chosen',
    });

    // ── Checkbox → sort-zone bağlantısı ──────────────────────────────────────
    document.querySelectorAll('.content-checkbox').forEach(cb => {
        cb.addEventListener('change', function(){
            const row = this.closest('.content-choice-row');
            const type = row.dataset.type;
            const id = row.dataset.id;
            if (this.checked) {
                addItem(type, id, '');
            } else {
                // Bu içeriğin tüm tekrarlarını sıralama alanından kaldır.
                sortZone.querySelectorAll(`.sort-card[data-type="${type}"][data-id="${id}"]`).forEach(c => c.remove());
                row.classList.remove('is-selected');
                updateUI();
            }
        });
    });

    // ── Menü / anket arama ────────────────────────────────────────────────────
    document.getElementById('content-search')?.addEventListener('input', function(){
        const q = this.value.toLowerCase();
        document.querySelectorAll('.content-choice-row').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
