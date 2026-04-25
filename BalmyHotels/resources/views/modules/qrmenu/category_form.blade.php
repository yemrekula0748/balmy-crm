@extends('layouts.default')
@section('content')
@php
    $isEdit = isset($category);
    $formAction = $isEdit
        ? route('qrmenus.category.update', [$menu, $category])
        : route('qrmenus.category.store', $menu);
    $pageTitle = $isEdit ? 'Kategori Düzenle' : 'Kategori Ekle';
@endphp
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>{{ $pageTitle }}</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.show', $menu) }}">{{ $menu->getTitle() }}</a></li>
                <li class="breadcrumb-item active">{{ $pageTitle }}</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header py-3"><h6 class="mb-0">Kategori Bilgileri</h6></div>
                    <div class="card-body">

                        {{-- Her dil için başlık + açıklama --}}
                        @foreach($menu->languages as $lang)
                        <div class="mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">{{ $lang->flag }}</span>
                                <strong>{{ $lang->name }}</strong>
                                @if($lang->is_default)<span class="badge bg-light text-dark border">Varsayılan</span>@endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Başlık <span class="text-danger">*</span></label>
                                <input type="text" name="title_{{ $lang->code }}" class="form-control"
                                       value="{{ old('title_'.$lang->code, $isEdit ? $category->getTitle($lang->code) : '') }}"
                                       placeholder="{{ $lang->name }} dilinde kategori adı">
                            </div>
                            <div>
                                <label class="form-label">Açıklama</label>
                                <textarea name="description_{{ $lang->code }}" class="form-control" rows="2"
                                          placeholder="{{ $lang->name }} dilinde açıklama (opsiyonel)">{{ old('description_'.$lang->code, $isEdit ? $category->getDescription($lang->code) : '') }}</textarea>
                            </div>
                        </div>
                        @endforeach

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">İkon (Emoji)</label>
                                <input type="text" name="icon" class="form-control"
                                       value="{{ old('icon', $isEdit ? $category->icon : '') }}"
                                       placeholder="🍕" maxlength="5">
                                <small class="text-muted">Emoji girin (opsiyonel)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sıra No</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                       value="{{ old('sort_order', $isEdit ? $category->sort_order : 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Durum</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="is_active" {{ old('is_active', $isEdit ? $category->is_active : true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Kategori Görseli</label>
                            @if($isEdit && $category->image)
                                <div class="mb-2">
                                    <img src="{{ asset('uploads/'.$category->image) }}" alt="görsel"
                                         class="rounded" style="height:80px;object-fit:cover">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

            {{-- Alt Gruplar / Ayraçlar --}}
                <div class="card mt-3">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Alt Gruplar / Ayraçlar</h6>
                            <small class="text-muted">Kategori içindeki ürünleri gruplara ayırmak için önceden tanımlayın (opsiyonel)</small>
                        </div>
                        <button type="button" id="addSubHeadingBtn" class="btn btn-sm" style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                            <i class="fas fa-plus me-1"></i> Grup Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="subHeadingsContainer">
                            @if($isEdit && $category->sub_headings && count($category->sub_headings) > 0)
                                @foreach($category->sub_headings as $i => $sh)
                                <div class="sub-heading-row card mb-2" style="background:#f8f9fc;border:1px solid #dde3ef">
                                    <div class="card-body p-2">
                                        <div class="d-flex gap-2 flex-wrap align-items-center">
                                            <div class="input-group input-group-sm" style="max-width:160px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇹🇷 TR <span class="text-danger ms-1">*</span></span>
                                                <input type="text" name="sub_headings[{{ $i }}][tr]" class="form-control" placeholder="Viskiler" value="{{ $sh['tr'] ?? '' }}" required>
                                            </div>
                                            <div class="input-group input-group-sm" style="max-width:150px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇬🇧 EN</span>
                                                <input type="text" name="sub_headings[{{ $i }}][en]" class="form-control" placeholder="Whiskies" value="{{ $sh['en'] ?? '' }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="max-width:150px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇩🇪 DE</span>
                                                <input type="text" name="sub_headings[{{ $i }}][de]" class="form-control" placeholder="Whisky" value="{{ $sh['de'] ?? '' }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="max-width:150px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇷🇺 RU</span>
                                                <input type="text" name="sub_headings[{{ $i }}][ru]" class="form-control" placeholder="Виски" value="{{ $sh['ru'] ?? '' }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="max-width:150px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇫🇷 FR</span>
                                                <input type="text" name="sub_headings[{{ $i }}][fr]" class="form-control" placeholder="Whiskies" value="{{ $sh['fr'] ?? '' }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="max-width:150px">
                                                <span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇸🇦 AR</span>
                                                <input type="text" name="sub_headings[{{ $i }}][ar]" class="form-control" dir="rtl" placeholder="ويسكي" value="{{ $sh['ar'] ?? '' }}">
                                            </div>
                                            <button type="button" class="btn btn-sm remove-sub-heading ms-auto"
                                                    style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <small class="text-muted">Her gruba Türkçe ad zorunludur. Grupları ürün formunda dropdown'dan seçebilirsiniz.</small>
                    </div>
                </div>
            </div>{{-- /col-lg-8 --}}

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn w-100 text-white mb-2" style="background:#c19b77">
                            <i class="fa fa-save me-1"></i> {{ $isEdit ? 'Kaydet' : 'Kategori Ekle' }}
                        </button>
                        <a href="{{ route('qrmenus.show', $menu) }}" class="btn btn-outline-secondary w-100">İptal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var counter = {{ $isEdit && $category->sub_headings ? count($category->sub_headings) : 0 }};

    function makeRow(idx) {
        return '<div class="sub-heading-row card mb-2" style="background:#f8f9fc;border:1px solid #dde3ef">' +
            '<div class="card-body p-2">' +
            '<div class="d-flex gap-2 flex-wrap align-items-center">' +
            '<div class="input-group input-group-sm" style="max-width:160px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇹🇷 TR <span class=\'text-danger ms-1\'>*</span></span><input type="text" name="sub_headings[' + idx + '][tr]" class="form-control" placeholder="Viskiler" required></div>' +
            '<div class="input-group input-group-sm" style="max-width:150px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇬🇧 EN</span><input type="text" name="sub_headings[' + idx + '][en]" class="form-control" placeholder="Whiskies"></div>' +
            '<div class="input-group input-group-sm" style="max-width:150px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇩🇪 DE</span><input type="text" name="sub_headings[' + idx + '][de]" class="form-control" placeholder="Whisky"></div>' +
            '<div class="input-group input-group-sm" style="max-width:150px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇷🇺 RU</span><input type="text" name="sub_headings[' + idx + '][ru]" class="form-control" placeholder="Виски"></div>' +
            '<div class="input-group input-group-sm" style="max-width:150px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇫🇷 FR</span><input type="text" name="sub_headings[' + idx + '][fr]" class="form-control" placeholder="Whiskies"></div>' +
            '<div class="input-group input-group-sm" style="max-width:150px"><span class="input-group-text" style="background:#f0f4ff;min-width:44px;justify-content:center;font-size:.75rem;font-weight:600">🇸🇦 AR</span><input type="text" name="sub_headings[' + idx + '][ar]" class="form-control" dir="rtl" placeholder="ويسكي"></div>' +
            '<button type="button" class="btn btn-sm remove-sub-heading ms-auto" style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0"><i class="fas fa-times"></i></button>' +
            '</div></div></div>';
    }

    document.getElementById('addSubHeadingBtn').addEventListener('click', function () {
        var html = makeRow(counter++);
        var div = document.createElement('div');
        div.innerHTML = html;
        document.getElementById('subHeadingsContainer').appendChild(div.firstChild);
    });

    document.getElementById('subHeadingsContainer').addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-sub-heading');
        if (btn) btn.closest('.sub-heading-row').remove();
    });
})();
</script>
@endpush
@endsection
