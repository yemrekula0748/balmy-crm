@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>{{ $restaurant->name }}</h4>
                <span class="text-muted small">Masa Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.restaurants.index') }}">Restoranlar</a></li>
                <li class="breadcrumb-item active">{{ $restaurant->name }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    <div class="row g-3 align-items-start">
        {{-- Sol: Bilgi + Masa Ekle --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Restoran Bilgisi</h6>
                    @if(auth()->user()->hasPermission('restaurant_settings','edit'))
                    <a href="{{ route('orders.restaurants.edit', $restaurant) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">Ad:</span> <strong>{{ $restaurant->name }}</strong></div>
                    <div class="mb-2"><span class="text-muted">Şube:</span> {{ $restaurant->branch?->name ?? '—' }}</div>
                    <div class="mb-2"><span class="text-muted">QR Menü:</span>
                        @if($restaurant->qrMenu)
                            <span class="badge" style="background:#c19b77">{{ $restaurant->qrMenu->name }}</span>
                        @else
                            <span class="text-warning small">Atanmamış</span>
                        @endif
                    </div>
                    <div><span class="text-muted">Masa Sayısı:</span> <strong>{{ $restaurant->tables->count() }}</strong></div>
                </div>
            </div>

            @if(auth()->user()->hasPermission('restaurant_settings','create'))
            <div class="card shadow-sm">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs card-header-tabs border-0 px-3" id="addTableTabs">
                        <li class="nav-item">
                            <button class="nav-link active px-3 py-2 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-tek" type="button">Tek Ekle</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link px-3 py-2 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-toplu" type="button">
                                Toplu Ekle
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body tab-content pt-3">

                    {{-- Tek masa --}}
                    <div class="tab-pane fade show active" id="tab-tek">
                        <form method="POST" action="{{ route('orders.restaurants.tables.store', $restaurant) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Masa Adı <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       class="form-control form-control-sm @error('name') is-invalid @enderror"
                                       placeholder="Ör: Masa 1">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Sıra</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                                       class="form-control form-control-sm" min="0">
                            </div>
                            <button type="submit" class="btn btn-sm text-white w-100" style="background:#c19b77">
                                <i class="fa fa-plus me-1"></i> Ekle
                            </button>
                        </form>
                    </div>

                    {{-- Toplu masa --}}
                    <div class="tab-pane fade" id="tab-toplu">
                        {{-- Otomatik oluştur --}}
                        <form method="POST" action="{{ route('orders.restaurants.tables.store-bulk', $restaurant) }}" id="form-auto">
                            @csrf
                            <input type="hidden" name="bulk_mode" value="auto">
                            <p class="small text-muted mb-2">Önek + numara aralığı girerek otomatik oluştur.</p>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Önek</label>
                                <input type="text" name="prefix" value="{{ old('prefix', 'Masa') }}"
                                       class="form-control form-control-sm @error('prefix') is-invalid @enderror"
                                       placeholder="Ör: Masa">
                                @error('prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col">
                                    <label class="form-label small fw-semibold">Başlangıç</label>
                                    <input type="number" name="from" value="{{ old('from', 1) }}"
                                           class="form-control form-control-sm @error('from') is-invalid @enderror"
                                           min="1" max="999">
                                    @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col">
                                    <label class="form-label small fw-semibold">Bitiş</label>
                                    <input type="number" name="to" value="{{ old('to', 10) }}"
                                           class="form-control form-control-sm @error('to') is-invalid @enderror"
                                           min="1" max="999">
                                    @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="text-muted small mb-3" id="auto-preview">
                                &rarr; <em>Masa 1, Masa 2, …, Masa 10</em> oluşturulacak
                            </div>
                            <button type="submit" class="btn btn-sm text-white w-100 mb-3" style="background:#c19b77">
                                <i class="fa fa-layer-group me-1"></i> Otomatik Oluştur
                            </button>
                        </form>

                        <hr class="my-2">

                        {{-- Manuel liste --}}
                        <form method="POST" action="{{ route('orders.restaurants.tables.store-bulk', $restaurant) }}">
                            @csrf
                            <input type="hidden" name="bulk_mode" value="manual">
                            <label class="form-label small fw-semibold">Manuel Liste <span class="text-muted fw-normal">(her satır bir masa)</span></label>
                            <textarea name="names" rows="5"
                                      class="form-control form-control-sm @error('names') is-invalid @enderror"
                                      placeholder="Veranda 1&#10;Veranda 2&#10;Bahçe 1&#10;VIP 1">{{ old('names') }}</textarea>
                            @error('names')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-sm text-white w-100 mt-2" style="background:#c19b77">
                                <i class="fa fa-list me-1"></i> Listeden Ekle
                            </button>
                        </form>
                    </div>

                </div>
            </div>
            @endif
        </div>

        {{-- Sağ: Masalar + Yazıcı Atamaları --}}
        <div class="col-lg-8">
            {{-- Tab navigasyon --}}
            <ul class="nav nav-tabs mb-0" id="restaurantTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-masalar" data-bs-toggle="tab" data-bs-target="#panel-masalar" type="button">
                        <i class="fa fa-th me-1"></i> Masalar
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-yazicilar" data-bs-toggle="tab" data-bs-target="#panel-yazicilar" type="button">
                        <i class="fa fa-print me-1"></i> Yazıcı Atamaları
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm">

                {{-- Masalar paneli --}}
                <div class="tab-pane fade show active p-3" id="panel-masalar">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('orders.take', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm text-white" style="background:#c19b77">
                            <i class="fa fa-concierge-bell me-1"></i> Sipariş Al
                        </a>
                    </div>
                    @forelse($restaurant->tables as $table)
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $table->name }}</span>
                            <span class="badge bg-secondary small">Sıra: {{ $table->sort_order }}</span>
                        </div>
                        @if(auth()->user()->hasPermission('restaurant_settings','delete'))
                        <form method="POST" action="{{ route('orders.restaurants.tables.destroy', [$restaurant, $table]) }}"
                              onsubmit="return confirm('{{ $table->name }} masasını silmek istediğinize emin misiniz?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                    @empty
                    <div class="text-muted text-center py-4">Henüz masa eklenmemiş.</div>
                    @endforelse
                </div>

                {{-- Yazıcı atamaları paneli --}}
                <div class="tab-pane fade p-3" id="panel-yazicilar">
                    @if(!$restaurant->qrMenu)
                        <div class="alert alert-warning">Bu restorana henüz menü atanmamış. Önce menü atayın.</div>
                    @elseif($categories->isEmpty())
                        <div class="alert alert-info">Menüde aktif kategori veya ürün bulunamadı.</div>
                    @elseif($printers->isEmpty())
                        <div class="alert alert-warning">Sistemde aktif yazıcı tanımlı değil.</div>
                    @else
                    @if(auth()->user()->hasPermission('restaurant_settings','edit'))
                    <form method="POST" action="{{ route('orders.restaurants.printers.save', $restaurant) }}">
                        @csrf
                        <p class="text-muted small mb-3">Her menü ürünü için hangi yazıcıdan çıktı alınacağını seçin. Boş bırakılan ürünler yazıcısız kalır.</p>

                        @foreach($categories as $category)
                        <div class="mb-4" data-cat-index="{{ $loop->index }}">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                <h6 class="fw-bold mb-0" style="color:#c19b77">
                                    {{ $category->getTitle('tr') }}
                                    <span class="badge rounded-pill ms-1" style="background:#f3ede6;color:#c19b77;font-size:0.7rem;font-weight:600">{{ $category->items->count() }}</span>
                                </h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted" style="font-size:0.75rem">Tümüne uygula:</span>
                                    <select class="form-select form-select-sm cat-printer-bulk" style="max-width:175px" data-cat-index="{{ $loop->index }}">
                                        <option value="">— Yazıcı Seç —</option>
                                        @foreach($printers as $printer)
                                        <option value="{{ $printer->id }}">{{ $printer->name }}</option>
                                        @endforeach
                                        <option value="__clear__">✕ Atamayı Kaldır</option>
                                    </select>
                                </div>
                            </div>
                            @foreach($category->items as $item)
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->image)
                                    <img src="{{ asset('uploads/'.$item->image) }}" alt=""
                                         class="rounded" style="width:36px;height:36px;object-fit:cover;flex-shrink:0">
                                    @else
                                    <div class="rounded d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:#f3ede6;flex-shrink:0;font-size:1rem">🍽</div>
                                    @endif
                                    <span class="small fw-semibold">{{ $item->getTitle('tr') }}</span>
                                </div>
                                <select name="printers[{{ $item->id }}]" class="form-select form-select-sm" style="max-width:200px">
                                    <option value="">— Yazıcı Seç —</option>
                                    @foreach($printers as $printer)
                                    <option value="{{ $printer->id }}" {{ ($printerMap[$item->id] ?? null) == $printer->id ? 'selected' : '' }}>
                                        {{ $printer->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endforeach
                        </div>
                        @endforeach

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-sm text-white px-4" style="background:#c19b77">
                                <i class="fa fa-save me-1"></i> Kaydet
                            </button>
                        </div>
                    </form>
                    @else
                        <div class="alert alert-info">Yazıcı atamalarını görüntüleme yetkiniz var ancak düzenleme yetkiniz yok.</div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function(){
    const prefixEl = document.querySelector('#form-auto [name="prefix"]');
    const fromEl   = document.querySelector('#form-auto [name="from"]');
    const toEl     = document.querySelector('#form-auto [name="to"]');
    const preview  = document.getElementById('auto-preview');

    function updatePreview(){
        const prefix = (prefixEl?.value ?? 'Masa').trim();
        const from   = parseInt(fromEl?.value) || 1;
        const to     = parseInt(toEl?.value)   || 1;
        const lo = Math.min(from, to);
        const hi = Math.max(from, to);
        const count = hi - lo + 1;
        if (count > 200) {
            preview.innerHTML = '<span class="text-danger">En fazla 200 masa eklenebilir.</span>';
            return;
        }
        const p = prefix ? prefix + ' ' : '';
        if (count === 1) {
            preview.innerHTML = `&rarr; <em>${p}${lo}</em> oluşturulacak`;
        } else if (count <= 4) {
            const names = Array.from({length: count}, (_, i) => p + (lo + i)).join(', ');
            preview.innerHTML = `&rarr; <em>${names}</em> oluşturulacak`;
        } else {
            preview.innerHTML = `&rarr; <em>${p}${lo}, ${p}${lo+1}, …, ${p}${hi}</em> — toplam <strong>${count}</strong> masa`;
        }
    }

    [prefixEl, fromEl, toEl].forEach(el => el?.addEventListener('input', updatePreview));
    updatePreview();
})();

// Kategori toplu yazıcı atama
(function(){
    document.querySelectorAll('.cat-printer-bulk').forEach(function(bulkSelect) {
        bulkSelect.addEventListener('change', function() {
            const catIndex = this.dataset.catIndex;
            const val      = this.value;
            const catDiv   = document.querySelector('[data-cat-index="' + catIndex + '"]');
            if (!catDiv) return;

            catDiv.querySelectorAll('select[name^="printers["]').forEach(function(itemSelect) {
                itemSelect.value = (val === '__clear__') ? '' : val;
            });

            // Bulk select'i sıfırla (placeholder'a döndür)
            this.value = '';
        });
    });
})();
</script>
@endpush
@endsection
