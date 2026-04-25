@extends('layouts.default')

@section('title', 'Yeni Kontrat Ekle')

@push('styles')
<style>
    .form-section { border-left: 4px solid #c19b77; padding-left: 1rem; }
    .form-section-title { font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #c19b77; }
    .price-input-group .input-group-text { background: #f8f9fa; font-weight: 600; min-width: 36px; justify-content: center; }
    .price-card { background: linear-gradient(135deg,#f8fafc,#f1f5f9); border-radius: .75rem; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Kontrat Ekle</h4>
                <span>Acente Yönetimi — Kontrat Oluştur</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agencies.index') }}">Acenteler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agencies.contracts.index') }}">Kontratlar</a></li>
                <li class="breadcrumb-item active">Yeni Kontrat</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Lütfen hataları düzeltin:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('agencies.contracts.store') }}" method="POST" id="contractForm">
        @csrf
        <div class="row g-4">

            {{-- Sol: Temel Bilgiler --}}
            <div class="col-lg-5">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Sözleşme Bilgileri</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Kontrat Kodu
                                    <span class="badge bg-secondary ms-1">Otomatik</span>
                                </label>
                                <input type="text" class="form-control" value="{{ $nextCode }}" readonly disabled>
                                <small class="text-muted">Kontrat kaydedildiğinde otomatik verilir</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Acente Kodu <span class="text-danger">*</span>
                                </label>
                                <select name="agency_id"
                                        class="form-select @error('agency_id') is-invalid @enderror"
                                        required>
                                    <option value="">— Acente Seçin —</option>
                                    @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}" @selected(old('agency_id') == $agency->id)>
                                        [{{ $agency->agency_code }}] {{ $agency->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('agency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Oda Tipi <span class="text-danger">*</span>
                                </label>
                                <select name="room_type_id"
                                        class="form-select @error('room_type_id') is-invalid @enderror"
                                        required>
                                    <option value="">— Oda Tipi Seçin —</option>
                                    @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}" @selected(old('room_type_id') == $rt->id)>
                                        [{{ $rt->code }}] {{ $rt->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($roomTypes->isEmpty())
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Önce <a href="{{ route('frontdesk.room-types.index') }}">oda tipi tanımlaması</a> yapın.
                                </small>
                                @endif
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">
                                    Başlangıç Tarihi <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}"
                                       required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">
                                    Bitiş Tarihi <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="end_date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}"
                                       required>
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Sağ: Fiyat Girişi --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Fiyat Bilgileri</p>
                        </div>
                    </div>
                    <div class="card-body p-4">

                        {{-- Kişi Sayısına Göre Fiyatlar --}}
                        <h6 class="fw-semibold text-muted mb-3 small text-uppercase">
                            <i class="fas fa-users me-2"></i>Konuklara Göre Fiyatlar
                        </h6>
                        <div class="row g-3 mb-4">
                            @php
                                $priceFields = [
                                    'price_single' => ['label' => 'Tek Kişi', 'icon' => '1', 'color' => 'primary'],
                                    'price_double' => ['label' => 'Çift Kişi', 'icon' => '2', 'color' => 'success'],
                                    'price_triple' => ['label' => '3 Kişi',   'icon' => '3', 'color' => 'info'],
                                    'price_quad'   => ['label' => '4 Kişi',   'icon' => '4', 'color' => 'warning'],
                                ];
                            @endphp
                            @foreach($priceFields as $fieldName => $fieldInfo)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    {{ $fieldInfo['label'] }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group price-input-group">
                                    <span class="input-group-text badge bg-{{ $fieldInfo['color'] }} rounded-0"
                                          style="min-width:32px;font-size:.85rem">
                                        {{ $fieldInfo['icon'] }}
                                    </span>
                                    <input type="number"
                                           name="{{ $fieldName }}"
                                           class="form-control @error($fieldName) is-invalid @enderror"
                                           value="{{ old($fieldName, 0) }}"
                                           min="0" step="0.01"
                                           placeholder="0.00"
                                           required>
                                    <span class="input-group-text">₺</span>
                                    @error($fieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Bebek & Çocuk Fiyatları --}}
                        <h6 class="fw-semibold text-muted mb-3 small text-uppercase">
                            <i class="fas fa-baby me-2"></i>Bebek & Çocuk Fiyatları
                        </h6>
                        <div class="row g-3">
                            @php
                                $childFields = [
                                    'price_baby1'  => ['label' => '1. Bebek',  'color' => 'danger'],
                                    'price_baby2'  => ['label' => '2. Bebek',  'color' => 'danger'],
                                    'price_child1' => ['label' => '1. Çocuk', 'color' => 'warning'],
                                    'price_child2' => ['label' => '2. Çocuk', 'color' => 'warning'],
                                ];
                            @endphp
                            @foreach($childFields as $fieldName => $fieldInfo)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    {{ $fieldInfo['label'] }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group price-input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-baby text-{{ $fieldInfo['color'] }}" style="font-size:.75rem"></i>
                                    </span>
                                    <input type="number"
                                           name="{{ $fieldName }}"
                                           class="form-control @error($fieldName) is-invalid @enderror"
                                           value="{{ old($fieldName, 0) }}"
                                           min="0" step="0.01"
                                           placeholder="0.00"
                                           required>
                                    <span class="input-group-text">₺</span>
                                    @error($fieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- Aksiyon Butonları --}}
        <div class="d-flex gap-3 mt-4">
            <button type="submit" class="btn btn-lg fw-semibold px-5" style="background:#c19b77;color:#fff">
                <i class="fas fa-save me-2"></i>Kontrat Kaydet
            </button>
            <a href="{{ route('agencies.contracts.index') }}" class="btn btn-lg btn-secondary px-5">
                <i class="fas fa-times me-2"></i>İptal
            </a>
        </div>

    </form>
</div>
@endsection
