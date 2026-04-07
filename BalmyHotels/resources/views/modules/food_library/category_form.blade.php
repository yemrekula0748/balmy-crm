@extends('layouts.default')
@section('title', isset($category) ? 'Kategori Düzenle' : 'Yeni Kategori')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ isset($category) ? 'Kategori Düzenle' : 'Yeni Kategori' }}</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('food-library.index') }}">Yemek Kütüphanesi</a></li>
                <li class="breadcrumb-item active">{{ isset($category) ? 'Düzenle' : 'Yeni Kategori' }}</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">
            <div class="card border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #1e2d3d!important">
                <div class="card-header border-0 px-4 py-3" style="background:#1e2d3d;border-radius:12px 12px 0 0">
                    <span class="text-white fw-semibold">
                        {{ isset($category) ? 'Kategori Bilgilerini Düzenle' : 'Yeni Kategori Oluştur' }}
                    </span>
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

                    <form method="POST"
                          action="{{ isset($category) ? route('food-library.categories.update', $category) : route('food-library.categories.store') }}">
                        @csrf
                        @if(isset($category)) @method('PUT') @endif

                        {{-- Şube --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">— Şube seçin —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id', $category->branch_id ?? '') == $b->id)>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Çok Dilli Kategori Adı --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Kategori Adı <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇹🇷 TR</span>
                                        <input type="text" name="title_tr" class="form-control @error('title_tr') is-invalid @enderror"
                                               placeholder="Örn: Ana Yemekler"
                                               value="{{ old('title_tr', $category->title['tr'] ?? '') }}" required>
                                        @error('title_tr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇬🇧 EN</span>
                                        <input type="text" name="title_en" class="form-control"
                                               placeholder="Örn: Main Courses"
                                               value="{{ old('title_en', $category->title['en'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇩🇪 DE</span>
                                        <input type="text" name="title_de" class="form-control"
                                               placeholder="Örn: Hauptgerichte"
                                               value="{{ old('title_de', $category->title['de'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇷🇺 RU</span>
                                        <input type="text" name="title_ru" class="form-control"
                                               placeholder="Örn: Основные блюда"
                                               value="{{ old('title_ru', $category->title['ru'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇫🇷 FR</span>
                                        <input type="text" name="title_fr" class="form-control"
                                               placeholder="Örn: Plats principaux"
                                               value="{{ old('title_fr', $category->title['fr'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background:#f0f4ff;border-color:#dde3ef;min-width:48px;justify-content:center;font-size:.8rem;font-weight:600">🇸🇦 AR</span>
                                        <input type="text" name="title_ar" class="form-control" dir="rtl"
                                               placeholder="مثال: الأطباق الرئيسية"
                                               value="{{ old('title_ar', $category->title['ar'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">Türkçe zorunlu — diğer diller opsiyoneldir.</small>
                        </div>

                        {{-- İkon (emoji) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">İkon (Emoji)</label>
                            <input type="text" name="icon" class="form-control" style="max-width:100px;font-size:1.4rem;text-align:center"
                                   placeholder="🍴" value="{{ old('icon', $category->icon ?? '') }}" maxlength="5">
                            <small class="text-muted">Opsiyonel — kategoriyi temsil eden bir emoji yazabilirsiniz.</small>
                        </div>

                        {{-- Sıralama --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Sıralama</label>
                            <input type="number" name="sort_order" class="form-control" style="max-width:130px"
                                   value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
                        </div>

                        @if(isset($category))
                        {{-- Durum --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Durum</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive"
                                       value="1" @checked(old('is_active', $category->is_active ?? true))>
                                <label class="form-check-label" for="isActive">Aktif</label>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex gap-2 pt-2">
                            <button type="submit" class="btn px-4 fw-semibold"
                                    style="background:linear-gradient(135deg,#1e2d3d,#2c3e50);color:#fff;border-radius:8px">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($category) ? 'Güncelle' : 'Kaydet' }}
                            </button>
                            <a href="{{ route('food-library.index') }}" class="btn btn-light px-4">İptal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @if(!isset($category))
    {{-- JSON Toplu Kategori Aktarımı --}}
    <div class="row justify-content-center mt-4">
        <div class="col-xl-7 col-lg-9">
            <div class="card border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #2e7d32!important">
                <div class="card-header border-0 px-4 py-3" style="background:#2e7d32;border-radius:12px 12px 0 0">
                    <span class="text-white fw-semibold"><i class="fas fa-file-code me-2"></i>JSON ile Toplu Kategori Aktar</span>
                </div>
                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <p class="text-muted small mb-3">
                        Aşağıya JSON dizisi yapıştırın. Her kategori için <code>title.tr</code> zorunludur.
                        Aynı şube + Türkçe ad kombinasyonu zaten varsa o satır atlanır.
                    </p>

                    <details class="mb-3">
                        <summary class="text-secondary small" style="cursor:pointer">Örnek JSON formatı</summary>
                        <pre class="mt-2 p-3 rounded" style="background:#f8f9fa;font-size:.78rem;overflow-x:auto">[
  {
    "branch_id": 1,
    "title": { "tr": "Ana Yemekler", "en": "Main Courses", "de": "Hauptgerichte" },
    "icon": "🍽️",
    "sort_order": 1
  },
  {
    "branch_id": 1,
    "title": { "tr": "İçecekler", "en": "Beverages" },
    "icon": "🥤",
    "sort_order": 2
  }
]</pre>
                    </details>

                    <form method="POST" action="{{ route('food-library.categories.json-import') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">JSON Verisi</label>
                            <textarea name="json_data" rows="10"
                                      class="form-control font-monospace @error('json_data') is-invalid @enderror"
                                      placeholder='[{"branch_id":1,"title":{"tr":"Örnek Kategori"},"icon":"🍴","sort_order":0}]'
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
@endsection
