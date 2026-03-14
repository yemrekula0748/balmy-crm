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
            </div>

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
@endsection
