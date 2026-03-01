@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Düzenle</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">{{ $asset->name }}</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('assets.update', $asset) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Demirbaş Kodu <span class="text-danger">*</span></label>
                                <input type="text" name="asset_code" class="form-control @error('asset_code') is-invalid @enderror"
                                       value="{{ old('asset_code', $asset->asset_code) }}">
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" id="categorySelect" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Seçin...</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" @selected(old('category_id', $asset->category_id) == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $asset->branch_id) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Demirbaş Adı <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $asset->name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Durum</label>
                                <select name="status" class="form-select">
                                    @foreach(\App\Models\Asset::STATUSES as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', $asset->status) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konum</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $asset->location) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Seri No</label>
                                <input type="text" name="serial_no" class="form-control" value="{{ old('serial_no', $asset->serial_no) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alış Tarihi</label>
                                <input type="date" name="purchase_date" class="form-control"
                                       value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alış Fiyatı (₺)</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01" min="0"
                                       value="{{ old('purchase_price', $asset->purchase_price) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Garanti Bitiş</label>
                                <input type="date" name="warranty_until" class="form-control"
                                       value="{{ old('warranty_until', $asset->warranty_until?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $asset->description) }}</textarea>
                            </div>

                            {{-- Mevcut kategori özel alanları --}}
                            @if($asset->category && $asset->category->field_definitions)
                                <div class="col-12">
                                    <hr>
                                    <h6 class="mb-3 text-muted">Kategori Özel Alanları</h6>
                                    <div class="row g-3" id="dynamicFields">
                                        @foreach($asset->category->field_definitions as $field)
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">
                                                    {{ $field['label'] }}
                                                    @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                                </label>
                                                @if(($field['type'] ?? 'text') === 'select' && !empty($field['options']))
                                                    <select name="prop_{{ $field['name'] }}" class="form-select form-select-sm">
                                                        <option value="">Seçin...</option>
                                                        @foreach($field['options'] as $opt)
                                                            <option value="{{ trim($opt) }}"
                                                                @selected(old('prop_'.$field['name'], ($asset->properties[$field['name']] ?? '')) === trim($opt))>
                                                                {{ trim($opt) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                                    <textarea name="prop_{{ $field['name'] }}" class="form-control form-control-sm" rows="2">{{ old('prop_'.$field['name'], $asset->properties[$field['name']] ?? '') }}</textarea>
                                                @else
                                                    <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text') }}"
                                                           name="prop_{{ $field['name'] }}" class="form-control form-control-sm"
                                                           value="{{ old('prop_'.$field['name'], $asset->properties[$field['name']] ?? '') }}"
                                                           @if($field['required'] ?? false) required @endif>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn" style="background:#c19b77;color:#fff">Güncelle</button>
                            <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
