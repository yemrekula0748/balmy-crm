@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $menu->getTitle() }}</h4>
                <p class="mb-0 text-muted">/menu/{{ $menu->name }}</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item active">{{ $menu->getTitle() }}</li>
            </ol>
        </div>
    </div>

    {{-- Flash mesaj --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- SOL KOLON: Kategoriler & Ürünler --}}
        <div class="col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Kategoriler &amp; Ürünler</h6>
                <a href="{{ route('qrmenus.category.create', $menu) }}"
                   class="btn btn-sm text-white" style="background:#c19b77">
                    <i class="fa fa-plus me-1"></i> Kategori Ekle
                </a>
            </div>

            @forelse($menu->categories as $category)
            <div class="card mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        @if($category->icon)
                            <span>{{ $category->icon }}</span>
                        @endif
                        <strong>{{ $category->getTitle() }}</strong>
                        @if(!$category->is_active)
                            <span class="badge bg-secondary small">Pasif</span>
                        @endif
                        <span class="badge bg-light text-dark border small">{{ $category->items->count() }} ürün</span>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('qrmenus.item.create', [$menu, $category]) }}"
                           class="btn btn-outline-success btn-sm py-0 px-2" title="Ürün ekle">
                            <i class="fa fa-plus"></i> Ürün
                        </a>
                        @if(auth()->user()->hasPermission('food_library', 'index'))
                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2"
                                title="Kütüphaneden ekle"
                                data-bs-toggle="modal" data-bs-target="#libraryModal-{{ $category->id }}">
                            <i class="fa fa-book"></i> Kütüphane
                        </button>
                        @endif
                        <a href="{{ route('qrmenus.category.edit', [$menu, $category]) }}"
                           class="btn btn-outline-warning btn-sm py-0 px-2">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('qrmenus.category.destroy', [$menu, $category]) }}"
                              onsubmit="return confirm('Kategori ve tüm ürünleri silinecek. Emin misiniz?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                @if($category->items->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($category->items->sortBy('sort_order') as $item)
                    <div class="list-group-item py-2">
                        <div class="d-flex align-items-center gap-2">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" alt=""
                                     class="rounded" style="width:44px;height:44px;object-fit:cover">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;background:#f0e6d9;color:#8b6a4f">
                                    <i class="fa fa-image"></i>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="fw-semibold">{{ $item->getTitle() }}</span>
                                    @if($item->is_featured)
                                        <span class="badge" style="background:#c19b77;font-size:.65rem">⭐ Öne Çıkan</span>
                                    @endif
                                    @if($item->badges)
                                        @foreach(array_slice($item->badges, 0, 3) as $badge)
                                            <span class="badge bg-light text-dark border" style="font-size:.65rem">{{ $badge }}</span>
                                        @endforeach
                                    @endif
                                    @if(!$item->is_active)
                                        <span class="badge bg-secondary" style="font-size:.65rem">Pasif</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                @if($item->price)
                                    <div class="fw-bold" style="color:#c19b77">
                                        {{ $item->formattedPrice($menu->currency_symbol) }}
                                    </div>
                                @endif
                                <div class="d-flex gap-1 justify-content-end mt-1">
                                    <a href="{{ route('qrmenus.item.edit', [$menu, $category, $item]) }}"
                                       class="btn btn-outline-warning btn-sm py-0 px-2">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('qrmenus.item.destroy', [$menu, $category, $item]) }}"
                                          onsubmit="return confirm('Ürün silinecek?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="card-body py-2 text-center text-muted small">
                    Henüz ürün yok.
                    <a href="{{ route('qrmenus.item.create', [$menu, $category]) }}" style="color:#c19b77">
                        İlk ürünü ekle
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">Henüz kategori eklenmemiş.</p>
                    <a href="{{ route('qrmenus.category.create', $menu) }}"
                       class="btn text-white" style="background:#c19b77">
                        İlk Kategoriyi Ekle
                    </a>
                </div>
            </div>
            @endforelse

        </div>

        {{-- SAĞ KOLON: Menü Bilgisi + QR + İstatistik --}}
        <div class="col-md-4">

            {{-- Menü Bilgi Kartı --}}
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($menu->logo)
                        <img src="{{ asset('storage/'.$menu->logo) }}" alt="logo"
                             class="rounded-circle mb-3" style="width:72px;height:72px;object-fit:cover">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center
                                    fw-bold fs-3 mx-auto mb-3 text-white"
                             style="width:72px;height:72px;background:#c19b77">
                            {{ strtoupper(substr($menu->name, 0, 1)) }}
                        </div>
                    @endif

                    <h6 class="mb-1">{{ $menu->getTitle() }}</h6>
                    <div class="small text-muted mb-2">
                        @if($menu->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Pasif</span>
                        @endif
                        <span class="ms-1">{{ $menu->currency }}</span>
                    </div>

                    <div class="d-flex gap-1 justify-content-center flex-wrap mb-3">
                        @foreach($menu->languages as $lang)
                            <span class="badge" style="background:#f0e6d9;color:#8b6a4f">
                                {{ $lang->flag }} {{ $lang->name }}
                                @if($lang->is_default) <small>(varsayılan)</small> @endif
                            </span>
                        @endforeach
                    </div>

                    <a href="{{ route('qrmenus.edit', $menu) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mb-2">
                        <i class="fa fa-edit me-1"></i> Ayarları Düzenle
                    </a>
                    <a href="{{ route('qrmenu.show', $menu->name) }}" target="_blank"
                       class="btn btn-sm w-100 text-white" style="background:#c19b77">
                        <i class="fa fa-external-link me-1"></i> Müşteri Görünümü
                    </a>
                </div>
            </div>

            {{-- QR Kod Kartı --}}
            <div class="card mb-4">
                <div class="card-header py-3"><h6 class="mb-0">QR Kod</h6></div>
                <div class="card-body text-center">
                    <div id="qrcode" class="mx-auto mb-3" style="width:160px;height:160px"></div>
                    <div class="small text-muted text-break mb-3">{{ url('/menu/'.$menu->name) }}</div>
                    <button onclick="downloadQr()" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa fa-download me-1"></i> QR İndir
                    </button>
                </div>
            </div>

            {{-- İstatistik Kartı --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Kategori</span>
                        <strong>{{ $menu->categories->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Toplam Ürün</span>
                        <strong>{{ $menu->items()->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Öne Çıkan</span>
                        <strong>{{ $menu->items()->where('is_featured', true)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Dil Sayısı</span>
                        <strong>{{ $menu->languages->count() }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Yemek Kütüphanesi Modalleri --}}
@if(auth()->user()->hasPermission('food_library', 'index'))
@foreach($menu->categories as $category)
<div class="modal fade" id="libraryModal-{{ $category->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius:12px">
            <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#1e2d3d,#2c3e50)">
                <h6 class="modal-title text-white fw-semibold mb-0">
                    <i class="fa fa-book me-2"></i>Kütüphaneden Ürün Ekle —
                    <span class="opacity-75">{{ $category->getTitle() }}</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Filtreler --}}
                <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
                    <select class="form-select form-select-sm lib-cat-filter" data-modal="{{ $category->id }}" style="max-width:200px">
                        <option value="">— Tüm Kategoriler —</option>
                    </select>
                    <input type="text" class="form-control form-control-sm lib-search" data-modal="{{ $category->id }}"
                           placeholder="Ürün ara..." style="max-width:220px">
                    <button type="button" class="btn btn-sm btn-secondary lib-fetch-btn" data-modal="{{ $category->id }}"
                            data-branch="{{ $menu->branch_id }}">
                        <i class="fa fa-search me-1"></i> Filtrele
                    </button>
                </div>

                {{-- Ürün Grid --}}
                <div class="row g-3 lib-products-grid" id="libGrid-{{ $category->id }}">
                    <div class="col-12 text-center text-muted py-4 lib-placeholder">
                        <i class="fa fa-book-open fa-2x opacity-25 mb-2 d-block"></i>
                        Filtreleme yapın veya modal açıldığında ürünler yüklenir.
                    </div>
                </div>
            </div>

            {{-- Seçilen ürünü onayla --}}
            <div class="modal-footer border-0 p-4 pt-0">
                <form id="libForm-{{ $category->id }}" method="POST"
                      action="{{ route('qrmenus.category.addFromLibrary', [$menu, $category]) }}">
                    @csrf
                    <input type="hidden" name="food_product_id" class="lib-selected-id">
                    <div class="d-flex gap-2 align-items-end flex-wrap">
                        <div>
                            <label class="form-label small fw-semibold mb-1">Seçilen Ürün</label>
                            <input type="text" class="form-control form-control-sm lib-selected-name"
                                   readonly placeholder="(ürün seçilmedi)" style="min-width:160px">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold mb-1">Fiyat Geçersizme (₺)</label>
                            <input type="number" name="price_override" class="form-control form-control-sm"
                                   placeholder="Boş bırakılırsa kütüphane fiyatı" step="0.01" min="0" style="max-width:200px">
                        </div>
                        <button type="submit" class="btn btn-sm fw-semibold px-3 mb-0"
                                style="background:linear-gradient(135deg,#1e2d3d,#2c3e50);color:#fff;border-radius:7px"
                                onclick="return document.querySelector('#libForm-{{ $category->id }} .lib-selected-id').value !== ''
                                         || (alert('Lütfen bir ürün seçin.'), false)">
                            <i class="fa fa-plus me-1"></i> Menüye Ekle
                        </button>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">İptal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var qrUrl = '{{ url('/menu/'.$menu->name) }}';
    var qr = new QRCode(document.getElementById('qrcode'), {
        text: qrUrl,
        width: 160,
        height: 160,
        colorDark: '{{ $menu->theme_color ?? "#1a1a2e" }}',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    window.downloadQr = function () {
        var img = document.querySelector('#qrcode img');
        if (!img) return;
        var a = document.createElement('a');
        a.href = img.src;
        a.download = '{{ $menu->name }}-qr.png';
        a.click();
    };
})();
</script>

<script>
// Yemek Kütüphanesi Modal JS
(function() {
    const apiBase = '{{ route('food-library.api.products') }}';

    function loadLibraryProducts(modalId, branchId, categoryId, search) {
        const grid = document.getElementById('libGrid-' + modalId);
        grid.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>';

        let url = apiBase + '?branch_id=' + (branchId || '');
        if (categoryId) url += '&category_id=' + categoryId;
        if (search)     url += '&search=' + encodeURIComponent(search);

        fetch(url).then(r => r.json()).then(data => {
            if (!data.products || data.products.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-4">Ürün bulunamadı.</div>';
                return;
            }

            // Populate category filter
            const catSel = document.querySelector('.lib-cat-filter[data-modal="' + modalId + '"]');
            if (catSel && data.categories) {
                const existing = [...catSel.options].map(o => o.value);
                data.categories.forEach(cat => {
                    if (!existing.includes(String(cat.id))) {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = (cat.icon || '') + ' ' + (cat.title_tr || cat.title || '');
                        catSel.appendChild(opt);
                    }
                });
            }

            grid.innerHTML = '';
            data.products.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-xl-3 col-lg-4 col-md-6';
                col.innerHTML = `
                    <div class="card h-100 border-0 shadow-sm lib-product-card" style="border-radius:10px;cursor:pointer;transition:box-shadow .15s"
                         data-id="${p.id}" data-name="${p.title_tr || p.title || ''}" data-price="${p.price}">
                        ${p.image_url ? `<div style="height:110px;overflow:hidden"><img src="${p.image_url}" style="width:100%;height:100%;object-fit:cover"></div>` : ''}
                        <div class="card-body p-3">
                            <div class="fw-semibold" style="font-size:.88rem">${p.title_tr || ''}</div>
                            ${p.category_name ? `<span style="font-size:.7rem;background:#eef3fb;color:#2a5298;font-weight:600;padding:2px 7px;border-radius:10px">${p.category_name}</span>` : ''}
                            <div class="fw-bold mt-1" style="color:#1e2d3d;font-size:.9rem">${parseFloat(p.price).toLocaleString('tr-TR',{minimumFractionDigits:2})} ₺</div>
                        </div>
                    </div>
                `;

                col.querySelector('.lib-product-card').addEventListener('click', function() {
                    // Deselect all
                    document.querySelectorAll('#libGrid-' + modalId + ' .lib-product-card').forEach(c => {
                        c.style.outline = '';
                        c.style.boxShadow = '';
                    });
                    // Select this
                    this.style.outline = '2px solid #1e2d3d';
                    this.style.boxShadow = '0 0 0 4px rgba(30,45,61,0.12)';

                    const form = document.getElementById('libForm-' + modalId);
                    form.querySelector('.lib-selected-id').value = this.dataset.id;
                    form.querySelector('.lib-selected-name').value = this.dataset.name + ' — ' + parseFloat(this.dataset.price).toLocaleString('tr-TR',{minimumFractionDigits:2}) + ' ₺';
                });

                grid.appendChild(col);
            });
        }).catch(() => {
            grid.innerHTML = '<div class="col-12 text-center text-danger py-4">Ürünler yüklenemedi.</div>';
        });
    }

    // Per-modal init
    document.querySelectorAll('[id^="libraryModal-"]').forEach(modal => {
        const modalId = modal.id.replace('libraryModal-', '');
        const branchId = '{{ $menu->branch_id }}';

        // Load on first open
        modal.addEventListener('show.bs.modal', function() {
            if (!this._libLoaded) {
                loadLibraryProducts(modalId, branchId, '', '');
                this._libLoaded = true;
            }
        });

        // Filter button
        const fetchBtn = document.querySelector('.lib-fetch-btn[data-modal="' + modalId + '"]');
        if (fetchBtn) {
            fetchBtn.addEventListener('click', function() {
                const cat   = document.querySelector('.lib-cat-filter[data-modal="' + modalId + '"]').value;
                const srch  = document.querySelector('.lib-search[data-modal="'     + modalId + '"]').value;
                loadLibraryProducts(modalId, branchId, cat, srch);
            });
        }

        // Search enter key
        const searchInput = document.querySelector('.lib-search[data-modal="' + modalId + '"]');
        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.querySelector('.lib-fetch-btn[data-modal="' + modalId + '"]')?.click();
                }
            });
        }
    });
})();
</script>
@endpush
@endsection
