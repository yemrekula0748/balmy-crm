@extends('layouts.default')
@section('content')

@php
    $isEdit     = isset($item);
    $formAction = $isEdit
        ? route('qrmenus.item.update', [$menu, $category, $item])
        : route('qrmenus.item.store', [$menu, $category]);
    $pageTitle  = $isEdit ? 'Ürün Düzenle' : 'Ürün Ekle';

    $badgeColors = [
        'Vegan'      => '#2d6a4f',
        'Vejeteryan' => '#40916c',
        'Glutensiz'  => '#e9c46a',
        'Laktozsuz'  => '#f4a261',
        'Acılı'      => '#e63946',
        'Önerilen'   => '#c19b77',
        'Yeni'       => '#457b9d',
        'Popüler'    => '#9b2226',
    ];
@endphp

<style>
.badge-chip {
    display: inline-block;
    padding: 6px 16px;
    border: 2px solid var(--chip-color);
    border-radius: 50px;
    cursor: pointer;
    font-size: .82rem;
    user-select: none;
    transition: .2s;
    color: var(--chip-color);
}
.badge-chip.selected {
    background: var(--chip-color);
    color: #fff;
}
</style>

<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $pageTitle }}</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.show', $menu) }}">{{ $menu->getTitle() }}</a></li>
                <li class="breadcrumb-item active">{{ $pageTitle }}</li>
            </ol>
        </div>
    </div>

    {{-- Hatalar --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bağlam çubuğu --}}
    <div class="alert alert-light border-start border-4 py-2 mb-3" style="border-color:#c19b77 !important">
        <small class="text-muted">
            Kategori: <strong>{{ $category->getTitle() }}</strong>
            &nbsp;·&nbsp;
            Menü: <strong>{{ $menu->getTitle() }}</strong>
        </small>
    </div>

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row justify-content-center align-items-start">
            <div class="col-lg-10">

                {{-- Ürün Bilgileri --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Ürün Bilgileri</h4>
                    </div>
                    <div class="card-body">

                        {{-- Her dil için başlık + açıklama --}}
                        @foreach($menu->languages as $lang)
                        <div class="mb-4 @if(!$loop->last) pb-4 border-bottom @endif">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">{{ $lang->flag }}</span>
                                <strong>{{ $lang->name }}</strong>
                                @if($lang->is_default)
                                    <span class="badge bg-light text-dark border">Varsayılan</span>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                                    <input type="text" name="title_{{ $lang->code }}" class="form-control"
                                           value="{{ old('title_'.$lang->code, $isEdit ? $item->getTitle($lang->code) : '') }}"
                                           placeholder="{{ $lang->name }} dilinde ürün adı">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Açıklama</label>
                                    <textarea name="description_{{ $lang->code }}" class="form-control" rows="2"
                                              placeholder="{{ $lang->name }} dilinde kısa açıklama">{{ old('description_'.$lang->code, $isEdit ? $item->getDescription($lang->code) : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{-- Fiyat, Sıra, Durum --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Fiyat ({{ $menu->currency_symbol ?? $menu->currency }})
                                </label>
                                <input type="number" name="price" class="form-control"
                                       min="0" step="0.01"
                                       value="{{ old('price', $isEdit ? $item->price : '') }}"
                                       placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sıra No</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                       value="{{ old('sort_order', $isEdit ? $item->sort_order : 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold d-block">&nbsp;</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           value="1" id="is_active"
                                           @checked(old('is_active', $isEdit ? $item->is_active : true))>
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>

                        {{-- Bardak / Şişe Fiyatlandırması --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h6 class="mb-0">🍾 Bardak / Şişe Fiyatlandırması</h6>
                                <small class="text-muted">(opsiyonel, normal fiyattan bağımsız)</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Bardak Fiyatı</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $menu->currency_symbol ?? $menu->currency }}</span>
                                        <input type="number" name="price_glass" class="form-control"
                                               min="0" step="0.01"
                                               value="{{ old('price_glass', $isEdit ? $item->price_glass : '') }}"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Bardak CL</label>
                                    <div class="input-group">
                                        <input type="number" name="cl_glass" class="form-control"
                                               min="0" step="1"
                                               value="{{ old('cl_glass', $isEdit ? $item->cl_glass : '') }}"
                                               placeholder="30">
                                        <span class="input-group-text">cl</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Şişe Fiyatı</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $menu->currency_symbol ?? $menu->currency }}</span>
                                        <input type="number" name="price_bottle" class="form-control"
                                               min="0" step="0.01"
                                               value="{{ old('price_bottle', $isEdit ? $item->price_bottle : '') }}"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Şişe CL</label>
                                    <div class="input-group">
                                        <input type="number" name="cl_bottle" class="form-control"
                                               min="0" step="1"
                                               value="{{ old('cl_bottle', $isEdit ? $item->cl_bottle : '') }}"
                                               placeholder="70">
                                        <span class="input-group-text">cl</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Öne çıkan --}}
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_featured"
                                   value="1" id="is_featured"
                                   @checked(old('is_featured', $isEdit && $item->is_featured))>
                            <label class="form-check-label" for="is_featured">
                                ⭐ Öne Çıkan Ürün
                                <small class="text-muted">(menü sayfasında üstte gösterilir)</small>
                            </label>
                        </div>

                    </div>
                </div>

                {{-- Alt Başlık / Ayraç --}}
                <div class="card mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <h4 class="card-title mb-0">Alt Başlık / Ayraç</h4>
                        <small class="text-muted">(bu üründen önce kategori içinde bölüm başlığı gösterir)</small>
                    </div>
                    <div class="card-body">
                        @foreach($menu->languages as $lang)
                        <div class="mb-3 @if(!$loop->last) border-bottom pb-3 @endif">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span>{{ $lang->flag }}</span>
                                <strong class="small">{{ $lang->name }}</strong>
                            </div>
                            <input type="text" name="sub_heading_{{ $lang->code }}" class="form-control"
                                   value="{{ old('sub_heading_'.$lang->code, $isEdit ? $item->getSubHeading($lang->code) : '') }}"
                                   placeholder="Örn: Viskiler, Biralar, Kokteyller…">
                        </div>
                        @endforeach
                        <small class="text-muted">Boş bırakılabilir. Girilen değer halka açık menüde bu ürün grubunun üstünde bölüm başlığı olarak görünür.</small>
                    </div>
                </div>

                {{-- Rozetler --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Rozetler / Etiketler</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2" id="badge-chips">
                            @foreach($badgeOptions as $badge)
                            @php
                                $color      = $badgeColors[$badge] ?? '#6c757d';
                                $checked    = in_array($badge, old('badges', $isEdit ? ($item->badges ?? []) : []));
                            @endphp
                            <span class="badge-chip {{ $checked ? 'selected' : '' }}"
                                  data-badge="{{ $badge }}"
                                  style="--chip-color:{{ $color }}">
                                <input type="checkbox" name="badges[]" value="{{ $badge }}"
                                       class="d-none" @checked($checked)>
                                {{ $badge }}
                            </span>
                            @endforeach
                        </div>
                        <small class="text-muted mt-2 d-block">Ürün özelliklerini seçin (çoklu seçim yapılabilir)</small>
                    </div>
                </div>

                {{-- Ürün Görseli --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Ürün Görseli</h4>
                    </div>
                    <div class="card-body">
                        @if($isEdit && $item->image)
                            <div class="mb-3 text-center">
                                <img src="{{ asset('uploads/'.$item->image) }}" alt="görsel"
                                     class="rounded" style="max-height:140px;object-fit:cover">
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Önerilen: 400×300px, kare veya 4:3</small>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 mb-4">
                    <button type="submit" class="btn text-white" style="background:#c19b77">
                        <i class="fa fa-save me-1"></i>
                        {{ $isEdit ? 'Değişiklikleri Kaydet' : 'Ürün Ekle' }}
                    </button>
                    <a href="{{ route('qrmenus.show', $menu) }}" class="btn btn-secondary">İptal</a>
                </div>

            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
(function () {
    document.getElementById('badge-chips').addEventListener('click', function (e) {
        var chip = e.target.closest('.badge-chip');
        if (!chip) return;
        var cb = chip.querySelector('input[type=checkbox]');
        chip.classList.toggle('selected');
        cb.checked = chip.classList.contains('selected');
    });
})();
</script>
@endpush
@endsection
