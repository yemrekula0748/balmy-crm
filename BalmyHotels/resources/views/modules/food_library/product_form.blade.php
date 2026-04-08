@extends('layouts.default')
@section('title', isset($product) ? 'Ürün Düzenle' : 'Yeni Ürün')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ isset($product) ? 'Ürün Düzenle' : 'Yeni Ürün' }}</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('food-library.index') }}">Yemek Kütüphanesi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('food-library.products') }}">Ürünler</a></li>
                <li class="breadcrumb-item active">{{ isset($product) ? 'Düzenle' : 'Yeni' }}</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #1e2d3d!important">
                <div class="card-header border-0 px-4 py-3" style="background:#1e2d3d;border-radius:12px 12px 0 0">
                    <span class="text-white fw-semibold">{{ isset($product) ? 'Ürün Bilgilerini Düzenle' : 'Yeni Ürün Oluştur' }}</span>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(!isset($product))
                    {{-- Başka Üründen Doldur --}}
                    <div class="mb-4 p-3 rounded" style="background:#f0f4ff;border:1.5px dashed #b0bedd">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-clone" style="color:#1e2d3d"></i>
                            <span class="fw-semibold" style="font-size:.9rem;color:#1e2d3d">Başka Üründen Doldur</span>
                            <span class="text-muted" style="font-size:.78rem">(opsiyonel — mevcut bir ürünü şablon olarak kullan)</span>
                        </div>
                        <div class="d-flex gap-2 align-items-start flex-wrap">
                            <div class="flex-grow-1" style="position:relative;min-width:220px">
                                <input type="text" id="productSearchInput" class="form-control form-control-sm"
                                       placeholder="Ürün adını yazın..." autocomplete="off">
                                <div id="productSearchDropdown"
                                     style="display:none;position:absolute;top:100%;left:0;right:0;z-index:999;background:#fff;border:1px solid #d0d8ef;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,.12);max-height:220px;overflow-y:auto">
                                </div>
                            </div>
                            <button type="button" id="fillFromProductBtn" class="btn btn-sm" disabled
                                    style="background:#1e2d3d;color:#fff;border-radius:6px;white-space:nowrap">
                                <i class="fas fa-fill-drip me-1"></i> Formu Doldur
                            </button>
                        </div>
                        <div id="selectedProductInfo" class="mt-2" style="display:none">
                            <span class="badge" style="background:#d6e4ff;color:#1e2d3d;font-size:.78rem;padding:4px 10px;border-radius:20px">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="selectedProductName"></span>
                            </span>
                        </div>
                    </div>
                    @endif

                    <form method="POST"
                          action="{{ isset($product) ? route('food-library.product.update', $product) : route('food-library.product.store') }}"
                          enctype="multipart/form-data">
                        @csrf
                        @if(isset($product)) @method('PUT') @endif

                        <div class="row g-3">

                            {{-- Şube --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branchSelect" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">— Şube seçin —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $product->branch_id ?? '') == $b->id)>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Kategori --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="food_category_id" id="categorySelect" class="form-select @error('food_category_id') is-invalid @enderror">
                                    <option value="">— Kategori seçin —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                                data-branch="{{ $cat->branch_id }}"
                                                @selected(old('food_category_id', $product->food_category_id ?? '') == $cat->id)>
                                            {{ $cat->icon }} {{ $cat->getTitle('tr') }}
                                            ({{ $cat->branch->name ?? 'Genel' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('food_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Yazıcı --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-print me-1 text-muted"></i>Mutfak Yazıcısı
                                </label>
                                <select name="printer_id" class="form-select">
                                    <option value="">— Yazıcı seçin —</option>
                                    @foreach($printers as $printer)
                                        <option value="{{ $printer->id }}"
                                                @selected(old('printer_id', $product->printer_id ?? '') == $printer->id)>
                                            {{ $printer->name }} — {{ optional($printer->branch)->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Sipariş verildiğinde bu ürün seçili yazıcıya çıktı gönderir.</div>
                            </div>

                            {{-- Çok Dilli Ürün Adı --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ürün Adı <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇹🇷 TR</span>
                                            <input type="text" name="title_tr" class="form-control @error('title_tr') is-invalid @enderror"
                                                   placeholder="Izgara Tavuk"
                                                   value="{{ old('title_tr', $product->title['tr'] ?? '') }}" required>
                                            @error('title_tr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇬🇧 EN</span>
                                            <input type="text" name="title_en" class="form-control"
                                                   placeholder="Grilled Chicken"
                                                   value="{{ old('title_en', $product->title['en'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇩🇪 DE</span>
                                            <input type="text" name="title_de" class="form-control"
                                                   placeholder="Gegrilltes Hähnchen"
                                                   value="{{ old('title_de', $product->title['de'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇷🇺 RU</span>
                                            <input type="text" name="title_ru" class="form-control"
                                                   placeholder="Курица гриль"
                                                   value="{{ old('title_ru', $product->title['ru'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇫🇷 FR</span>
                                            <input type="text" name="title_fr" class="form-control"
                                                   placeholder="Poulet grillé"
                                                   value="{{ old('title_fr', $product->title['fr'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇸🇦 AR</span>
                                            <input type="text" name="title_ar" class="form-control" dir="rtl"
                                                   placeholder="دجاج مشوي"
                                                   value="{{ old('title_ar', $product->title['ar'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Türkçe zorunlu — diğer diller opsiyoneldir.</small>
                            </div>

                            {{-- Çok Dilli Açıklama --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇹🇷 TR</span>
                                            <textarea name="description_tr" class="form-control" rows="2" placeholder="Ürün açıklaması...">{{ old('description_tr', $product->description['tr'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇬🇧 EN</span>
                                            <textarea name="description_en" class="form-control" rows="2" placeholder="Product description...">{{ old('description_en', $product->description['en'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇩🇪 DE</span>
                                            <textarea name="description_de" class="form-control" rows="2" placeholder="Produktbeschreibung...">{{ old('description_de', $product->description['de'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇷🇺 RU</span>
                                            <textarea name="description_ru" class="form-control" rows="2" placeholder="Описание продукта...">{{ old('description_ru', $product->description['ru'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇫🇷 FR</span>
                                            <textarea name="description_fr" class="form-control" rows="2" placeholder="Description du produit...">{{ old('description_fr', $product->description['fr'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">🇸🇦 AR</span>
                                            <textarea name="description_ar" class="form-control" rows="2" dir="rtl" placeholder="وصف المنتج...">{{ old('description_ar', $product->description['ar'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Çok Dilli İçindekiler --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">İçindekiler <span class="text-muted fw-normal" style="font-size:.8rem">(opsiyonel)</span></label>
                                <div class="row g-2">
                                    @foreach([
                                        'tr' => ['flag'=>'🇹🇷','ph'=>'Su, un, yumurta, tuz...'],
                                        'en' => ['flag'=>'🇬🇧','ph'=>'Water, flour, eggs, salt...'],
                                        'de' => ['flag'=>'🇩🇪','ph'=>'Wasser, Mehl, Eier, Salz...'],
                                        'ru' => ['flag'=>'🇷🇺','ph'=>'Вода, мука, яйца, соль...'],
                                        'fr' => ['flag'=>'🇫🇷','ph'=>'Eau, farine, œufs, sel...'],
                                        'ar' => ['flag'=>'🇸🇦','ph'=>'ماء، دقيق، بيض، ملح...'],
                                    ] as $l => $meta)
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm align-items-start">
                                            <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600;height:auto;align-self:stretch">{{ $meta['flag'] }} {{ strtoupper($l) }}</span>
                                            <textarea name="ingredients_{{ $l }}" class="form-control" rows="2"
                                                      placeholder="{{ $meta['ph'] }}"
                                                      @if($l==='ar') dir="rtl" @endif>{{ old('ingredients_'.$l, $product->ingredients[$l] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Fiyat --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fiyat (₺) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="price" step="0.01" min="0"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="0.00"
                                           value="{{ old('price', $product->price ?? '') }}" required>
                                    <span class="input-group-text">₺</span>
                                </div>
                                @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                        </div>

                        {{-- Bardak / Şişe Fiyatlandırması --}}
                        <hr class="my-3">
                        <h6 class="fw-bold text-dark mb-1">🍾 Bardak / Şişe Fiyatlandırması <span class="text-muted fw-normal" style="font-size:.8rem">(opsiyonel — ayrı bardak/şişe fiyatı olan içecekler için)</span></h6>
                        <div class="row g-2 mt-1">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">🥃 Bardak Fiyatı</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="price_glass" step="0.01" min="0" class="form-control"
                                           placeholder="0.00" value="{{ old('price_glass', $product->price_glass ?? '') }}">
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">📏 Bardak CL</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="cl_glass" step="1" min="0" class="form-control"
                                           placeholder="30" value="{{ old('cl_glass', $product->cl_glass ?? '') }}">
                                    <span class="input-group-text">cl</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">🍾 Şişe Fiyatı</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="price_bottle" step="0.01" min="0" class="form-control"
                                           placeholder="0.00" value="{{ old('price_bottle', $product->price_bottle ?? '') }}">
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">📏 Şişe CL</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="cl_bottle" step="1" min="0" class="form-control"
                                           placeholder="70" value="{{ old('cl_bottle', $product->cl_bottle ?? '') }}">
                                    <span class="input-group-text">cl</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">

                            {{-- Sıralama --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sıralama</label>
                                <input type="number" name="sort_order" class="form-control"
                                       value="{{ old('sort_order', $product->sort_order ?? 0) }}" min="0">
                            </div>

                            @if(isset($product))
                            {{-- Durum --}}
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive"
                                           value="1" @checked(old('is_active', $product->is_active ?? true))>
                                    <label class="form-check-label fw-semibold" for="isActive">Aktif</label>
                                </div>
                            </div>
                            @endif

                        </div>

                        {{-- Besin Değerleri --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-1">Besin Değerleri <span class="text-muted fw-normal" style="font-size:.8rem">(100g / porsiyon başına, opsiyonel)</span></h6>
                        <div class="row g-2 mt-1">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">🔥 Kalori</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="calories" step="0.1" min="0" class="form-control"
                                           placeholder="0" value="{{ old('calories', $product->calories ?? '') }}">
                                    <span class="input-group-text">kcal</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">💪 Protein</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="protein" step="0.1" min="0" class="form-control"
                                           placeholder="0" value="{{ old('protein', $product->protein ?? '') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">🌾 Karbonhidrat</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="carbs" step="0.1" min="0" class="form-control"
                                           placeholder="0" value="{{ old('carbs', $product->carbs ?? '') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">🫒 Yağ</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="fat" step="0.1" min="0" class="form-control"
                                           placeholder="0" value="{{ old('fat', $product->fat ?? '') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                            </div>
                        </div>

                        {{-- Görsel --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3">Ürün Görseli</h6>
                        @if(isset($product) && $product->image)
                        <div class="mb-3">
                            <img src="{{ asset('uploads/'.$product->image) }}" alt="" id="imagePreview"
                                 style="max-height:160px;border-radius:8px;object-fit:cover;border:2px solid #eee">
                        </div>
                        @else
                        <div class="mb-2">
                            <img id="imagePreview" src="" alt="" style="max-height:160px;border-radius:8px;display:none">
                        </div>
                        @endif
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                               id="imageInput" accept="image/*"
                               onchange="previewImg(this)">
                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">JPG/PNG/WebP — Max 2MB</small>

                        {{-- Alerjenler --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-1">Alerjenler <span class="text-muted fw-normal" style="font-size:.8rem">(14 AB Alerjeni)</span></h6>
                        <p class="text-muted small mb-3">İçeriğinde bulunan alerjenler için kutuları işaretleyin.</p>
                        <div class="row g-2">
                            @foreach(\App\Models\FoodProduct::ALLERGENS as $key => $allergen)
                            @php $checkedA = in_array($key, old('allergens', isset($product) ? ($product->allergens ?? []) : [])); @endphp
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="allergen-toggle d-flex align-items-center gap-2 p-2 rounded border w-100"
                                       style="cursor:pointer;border-color:#dde3ef!important;background:#fafbfd;transition:background .15s,border-color .15s"
                                       data-checked="{{ $checkedA ? '1' : '0' }}">
                                    <input type="checkbox" name="allergens[]" value="{{ $key }}" class="d-none allergen-cb" @checked($checkedA)>
                                    <span style="font-size:1.3rem;line-height:1">{{ $allergen['emoji'] }}</span>
                                    <span class="allergen-label" style="font-size:.78rem;font-weight:500;color:#333;line-height:1.2">{{ $allergen['tr'] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- Badges --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3">Etiketler</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Models\FoodProduct::BADGE_OPTIONS as $badge)
                            @php $checked = in_array($badge, old('badges', isset($product) ? ($product->badges ?? []) : [])); @endphp
                            <label class="badge-toggle" style="cursor:pointer">
                                <input type="checkbox" name="badges[]" value="{{ $badge }}" class="d-none badge-cb" @checked($checked)>
                                <span class="badge-chip"
                                      style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:600;border:2px solid transparent;user-select:none;
                                             background:{{ \App\Models\FoodProduct::BADGE_COLORS[$badge]['bg'] ?? '#f0f0f0' }};
                                             color:{{ \App\Models\FoodProduct::BADGE_COLORS[$badge]['text'] ?? '#333' }}">
                                    {{ $badge }}
                                </span>
                            </label>
                            @endforeach
                        </div>

                        {{-- Dinamik Opsiyonlar --}}
                        <hr class="my-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0">Dinamik Opsiyonlar</h6>
                            <button type="button" id="addOption" class="btn btn-sm"
                                    style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                                <i class="fas fa-plus me-1"></i> Opsiyon Ekle
                            </button>
                        </div>
                        <div id="optionsContainer">
                            @if(isset($product) && !empty($product->options))
                                @foreach($product->options as $opt)
                                @php
                                $labelArr = is_array($opt['label'] ?? null) ? $opt['label'] : ['tr' => $opt['label'] ?? ''];
                                @endphp
                                <div class="option-row card mb-2" style="background:#f8f9fc;border:1px solid #dde3ef">
                                    <div class="card-body p-2">
                                        <div class="d-flex gap-1 flex-wrap align-items-center mb-1">
                                            <input type="text" name="opt_label_tr[]" class="form-control form-control-sm"
                                                   placeholder="🇹🇷 TR" value="{{ $labelArr['tr'] ?? '' }}" style="max-width:120px">
                                            <input type="text" name="opt_label_en[]" class="form-control form-control-sm"
                                                   placeholder="🇬🇧 EN" value="{{ $labelArr['en'] ?? '' }}" style="max-width:120px">
                                            <input type="text" name="opt_label_de[]" class="form-control form-control-sm"
                                                   placeholder="🇩🇪 DE" value="{{ $labelArr['de'] ?? '' }}" style="max-width:110px">
                                            <input type="text" name="opt_label_ru[]" class="form-control form-control-sm"
                                                   placeholder="🇷🇺 RU" value="{{ $labelArr['ru'] ?? '' }}" style="max-width:110px">
                                            <button type="button" class="btn btn-sm remove-option ms-auto"
                                                    style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select name="opt_type[]" class="form-select form-select-sm" style="max-width:120px">
                                                @foreach(\App\Models\FoodProduct::OPTION_TYPES as $key => $label)
                                                    <option value="{{ $key }}" @selected(($opt['type'] ?? 'text') === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="opt_value[]" class="form-control form-control-sm"
                                                   placeholder="Değer" value="{{ $opt['value'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <small class="text-muted">Örn: Alerjenler → tags → Gluten,Süt &nbsp;|&nbsp; Alkol Oranı → text → 13%</small>

                        {{-- Submit --}}
                        <div class="d-flex gap-2 pt-4 mt-2 border-top">
                            <button type="submit" class="btn px-4 fw-semibold"
                                    style="background:linear-gradient(135deg,#1e2d3d,#2c3e50);color:#fff;border-radius:8px">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($product) ? 'Güncelle' : 'Kaydet' }}
                            </button>
                            <a href="{{ route('food-library.products') }}" class="btn btn-light px-4">İptal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!isset($product))
    {{-- JSON Toplu Ürün Aktarımı --}}
    <div class="row justify-content-center mt-4">
        <div class="col-xl-8 col-lg-10">
            <div class="card border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #2e7d32!important">
                <div class="card-header border-0 px-4 py-3" style="background:#2e7d32;border-radius:12px 12px 0 0">
                    <span class="text-white fw-semibold"><i class="fas fa-file-code me-2"></i>JSON ile Toplu Ürün Aktar</span>
                </div>
                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <p class="text-muted small mb-3">
                        Aşağıya JSON dizisi yapıştırın. Her ürün için <code>title.tr</code> zorunludur.
                        Aynı şube + Türkçe ad kombinasyonu zaten varsa o satır atlanır.
                    </p>

                    <details class="mb-3">
                        <summary class="text-secondary small" style="cursor:pointer">Örnek JSON formatı</summary>
                        <pre class="mt-2 p-3 rounded" style="background:#f8f9fa;font-size:.78rem;overflow-x:auto">[
  {
    "branch_id": 2,
    "food_category_id": 5,
    "title": { "tr": "Izgara Somon", "en": "Grilled Salmon", "de": "Gegrillter Lachs", "ru": "Жареный лосось" },
    "description": { "tr": "Taze somon fileto", "en": "Fresh salmon fillet" },
    "ingredients": { "tr": "Somon, zeytinyağı, limon" },
    "price": 280.00,
    "badges": ["Önerilen", "Glutensiz"],
    "allergens": ["fish"],
    "calories": 320,
    "protein": 34,
    "carbs": 2,
    "fat": 18,
    "sort_order": 1
  }
]</pre>
                    </details>

                    <form method="POST" action="{{ route('food-library.product.json-import') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">JSON Verisi</label>
                            <textarea name="json_data" rows="12"
                                      class="form-control font-monospace @error('json_data') is-invalid @enderror"
                                      placeholder='[{"branch_id":2,"food_category_id":5,"title":{"tr":"Ürün Adı"},"price":100}]'
                                      style="font-size:.83rem">{{ old('json_data') }}</textarea>
                            @error('json_data')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn px-4 fw-semibold"
                                style="background:linear-gradient(135deg,#2e7d32,#388e3c);color:#fff;border-radius:8px">
                            <i class="fas fa-upload me-1"></i> Aktar
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
function previewImg(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

// Badge toggle styling
document.querySelectorAll('.badge-toggle').forEach(label => {
    const cb = label.querySelector('.badge-cb');
    const chip = label.querySelector('.badge-chip');
    function updateChip() {
        chip.style.opacity = cb.checked ? '1' : '0.45';
        chip.style.outline = cb.checked ? '2px solid #1e2d3d' : 'none';
    }
    updateChip();
    label.addEventListener('click', () => { setTimeout(updateChip, 10); });
});

// Allergen toggle styling
document.querySelectorAll('.allergen-toggle').forEach(label => {
    const cb = label.querySelector('.allergen-cb');
    function updateAllergen() {
        if (cb.checked) {
            label.style.background = '#fff8e6';
            label.style.borderColor = '#e8a020!important';
            label.style.borderColor = '#e8a020';
            label.style.outline = '2px solid #e8a020';
        } else {
            label.style.background = '#fafbfd';
            label.style.borderColor = '#dde3ef';
            label.style.outline = 'none';
        }
    }
    updateAllergen();
    label.addEventListener('click', () => { setTimeout(updateAllergen, 10); });
});

// Dynamic options
const OPTION_TYPES = @json(\App\Models\FoodProduct::OPTION_TYPES);

function buildOptionRow(labelTr, labelEn, labelDe, labelRu, type, value) {
    const div = document.createElement('div');
    div.className = 'option-row card mb-2';
    div.style.cssText = 'background:#f8f9fc;border:1px solid #dde3ef';

    let typeOptions = '';
    for (const [key, lbl] of Object.entries(OPTION_TYPES)) {
        typeOptions += `<option value="${key}" ${key === (type || 'text') ? 'selected' : ''}>${lbl}</option>`;
    }

    div.innerHTML = `
        <div class="card-body p-2">
            <div class="d-flex gap-1 flex-wrap align-items-center mb-1">
                <input type="text" name="opt_label_tr[]" class="form-control form-control-sm" placeholder="🇹🇷 TR" value="${labelTr || ''}" style="max-width:120px">
                <input type="text" name="opt_label_en[]" class="form-control form-control-sm" placeholder="🇬🇧 EN" value="${labelEn || ''}" style="max-width:120px">
                <input type="text" name="opt_label_de[]" class="form-control form-control-sm" placeholder="🇩🇪 DE" value="${labelDe || ''}" style="max-width:110px">
                <input type="text" name="opt_label_ru[]" class="form-control form-control-sm" placeholder="🇷🇺 RU" value="${labelRu || ''}" style="max-width:110px">
                <button type="button" class="btn btn-sm remove-option ms-auto"
                        style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <select name="opt_type[]" class="form-select form-select-sm" style="max-width:120px">${typeOptions}</select>
                <input type="text" name="opt_value[]" class="form-control form-control-sm" placeholder="Değer" value="${value || ''}">
            </div>
        </div>
    `;

    div.querySelector('.remove-option').addEventListener('click', () => div.remove());
    return div;
}

document.getElementById('addOption').addEventListener('click', () => {
    document.getElementById('optionsContainer').appendChild(buildOptionRow());
});

document.querySelectorAll('.remove-option').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.option-row').remove());
});

// Branch filter for category select
document.getElementById('branchSelect')?.addEventListener('change', function() {
    const branchId = this.value;
    document.querySelectorAll('#categorySelect option[data-branch]').forEach(opt => {
        opt.hidden = branchId && opt.dataset.branch !== branchId;
    });
    const sel = document.getElementById('categorySelect');
    if (sel.options[sel.selectedIndex]?.hidden) sel.value = '';
});
// trigger on load
document.getElementById('branchSelect')?.dispatchEvent(new Event('change'));

// ──────────────────────────────────────────────
// Başka Üründen Doldur
// ──────────────────────────────────────────────
(function () {
    const searchInput    = document.getElementById('productSearchInput');
    if (!searchInput) return; // sadece create modunda çalışır

    const dropdown       = document.getElementById('productSearchDropdown');
    const fillBtn        = document.getElementById('fillFromProductBtn');
    const infoBox        = document.getElementById('selectedProductInfo');
    const selectedName   = document.getElementById('selectedProductName');
    const apiSearchUrl   = '{{ route("food-library.api.products") }}';
    const apiProductBase = '{{ url("yemek-kutuphane/api/urunler") }}';

    let selectedProductId = null;
    let searchTimer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }
        searchTimer = setTimeout(() => fetchProducts(q), 300);
    });

    searchInput.addEventListener('blur', function () {
        setTimeout(() => { dropdown.style.display = 'none'; }, 200);
    });

    function fetchProducts(q) {
        fetch(`${apiSearchUrl}?search=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                const products = data.products ?? [];
                dropdown.innerHTML = '';
                if (!products.length) {
                    dropdown.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:.82rem">Sonuç bulunamadı</div>';
                } else {
                    products.forEach(p => {
                        const item = document.createElement('div');
                        item.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:.83rem;border-bottom:1px solid #f0f0f0';
                        item.textContent = p.title_tr + (p.category_name ? ` — ${p.category_name}` : '');
                        item.addEventListener('mousedown', () => selectProduct(p.id, p.title_tr));
                        item.addEventListener('mouseover', () => item.style.background = '#f0f4ff');
                        item.addEventListener('mouseout',  () => item.style.background = '');
                        dropdown.appendChild(item);
                    });
                }
                dropdown.style.display = 'block';
            });
    }

    function selectProduct(id, name) {
        selectedProductId = id;
        searchInput.value = name;
        selectedName.textContent = name;
        infoBox.style.display = 'block';
        fillBtn.disabled = false;
        dropdown.style.display = 'none';
    }

    fillBtn.addEventListener('click', function () {
        if (!selectedProductId) return;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Dolduruluyor...';

        fetch(`${apiProductBase}/${selectedProductId}`)
            .then(r => r.json())
            .then(p => {
                // Şube & Kategori
                setSelectValue('branchSelect', p.branch_id);
                document.getElementById('branchSelect').dispatchEvent(new Event('change'));
                setTimeout(() => setSelectValue('categorySelect', p.food_category_id), 100);

                // Yazıcı
                setSelectValue(document.querySelector('select[name="printer_id"]'), p.printer_id);

                // Çok dilli başlık
                const langs = ['tr','en','de','ru','fr','ar'];
                langs.forEach(l => {
                    setField(`title_${l}`, (p.title ?? {})[l] ?? '');
                    setField(`description_${l}`, (p.description ?? {})[l] ?? '');
                    setField(`ingredients_${l}`, (p.ingredients ?? {})[l] ?? '');
                });

                // Fiyat & Sıralama
                setField('price',      p.price ?? '');
                setField('sort_order', p.sort_order ?? 0);

                // Bardak / Şişe fiyatlandırması
                setField('price_glass',  p.price_glass  ?? '');
                setField('cl_glass',     p.cl_glass     ?? '');
                setField('price_bottle', p.price_bottle ?? '');
                setField('cl_bottle',    p.cl_bottle    ?? '');

                // Besin değerleri
                setField('calories', p.calories ?? '');
                setField('protein',  p.protein  ?? '');
                setField('carbs',    p.carbs    ?? '');
                setField('fat',      p.fat      ?? '');

                // Alerjenler — hepsini sıfırla, sonra işaretle
                document.querySelectorAll('input[name="allergens[]"]').forEach(cb => {
                    cb.checked = (p.allergens ?? []).includes(cb.value);
                    cb.closest('.allergen-toggle')?.dispatchEvent(new Event('mousedown'));
                });
                document.querySelectorAll('.allergen-toggle').forEach(label => {
                    const cb = label.querySelector('.allergen-cb');
                    if (cb.checked) {
                        label.style.background = '#fff8e6';
                        label.style.borderColor = '#e8a020';
                        label.style.outline = '2px solid #e8a020';
                    } else {
                        label.style.background = '#fafbfd';
                        label.style.borderColor = '#dde3ef';
                        label.style.outline = 'none';
                    }
                });

                // Badges — hepsini sıfırla, sonra işaretle
                document.querySelectorAll('input[name="badges[]"]').forEach(cb => {
                    cb.checked = (p.badges ?? []).includes(cb.value);
                });
                document.querySelectorAll('.badge-toggle').forEach(label => {
                    const cb = label.querySelector('.badge-cb');
                    const chip = label.querySelector('.badge-chip');
                    chip.style.opacity = cb.checked ? '1' : '0.45';
                    chip.style.outline = cb.checked ? '2px solid #1e2d3d' : 'none';
                });

                // Opsiyonlar
                const container = document.getElementById('optionsContainer');
                container.innerHTML = '';
                (p.options ?? []).forEach(opt => {
                    const label = opt.label ?? {};
                    container.appendChild(buildOptionRow(
                        label.tr ?? '', label.en ?? '', label.de ?? '', label.ru ?? '',
                        opt.type ?? 'text', opt.value ?? ''
                    ));
                });

                fillBtn.innerHTML = '<i class="fas fa-check me-1"></i> Dolduruldu';
                fillBtn.style.background = '#2ecc71';
                setTimeout(() => {
                    fillBtn.innerHTML = '<i class="fas fa-fill-drip me-1"></i> Formu Doldur';
                    fillBtn.style.background = '#1e2d3d';
                    fillBtn.disabled = false;
                }, 2000);
            })
            .catch(() => {
                fillBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Hata';
                fillBtn.style.background = '#e74c3c';
                setTimeout(() => {
                    fillBtn.innerHTML = '<i class="fas fa-fill-drip me-1"></i> Formu Doldur';
                    fillBtn.style.background = '#1e2d3d';
                    fillBtn.disabled = false;
                }, 2000);
            });
    });

    function setField(nameOrEl, val) {
        const el = typeof nameOrEl === 'string'
            ? document.querySelector(`[name="${nameOrEl}"]`)
            : nameOrEl;
        if (el) el.value = val ?? '';
    }

    function setSelectValue(idOrEl, val) {
        const el = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
        if (el && val != null) el.value = val;
    }
})();
</script>
@endsection
