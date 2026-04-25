@extends('layouts.default')

@section('title', 'Yeni Acente Tanımla')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .form-section { border-left: 4px solid #c19b77; padding-left: 1rem; }
    .form-section-title { font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #c19b77; }
    .required-badge { font-size:.7rem; background:#fee2e2; color:#dc2626; padding:1px 5px; border-radius:4px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Acente Tanımla</h4>
                <span>Acente Yönetimi — Acente Kaydı Oluştur</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agencies.index') }}">Acenteler</a></li>
                <li class="breadcrumb-item active">Yeni Acente</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Lütfen aşağıdaki hataları düzeltin:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form id="agencyForm" action="{{ route('agencies.store') }}" method="POST" novalidate>
        @csrf
        <div class="row g-4 align-items-start">

            {{-- Sol Sütun --}}
            <div class="col-lg-8">

                {{-- Temel Bilgiler --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Temel Bilgiler</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Acenta Kodu <span class="required-badge">ZORUNLU</span>
                                </label>
                                <input type="text"
                                       name="agency_code"
                                       class="form-control @error('agency_code') is-invalid @enderror"
                                       value="{{ old('agency_code', $nextCode) }}"
                                       placeholder="ör: ACT-0001"
                                       required>
                                @error('agency_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Benzersiz bir kod giriniz</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">
                                    Acenta Tam İsmi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Acenta ticaret unvanını girin"
                                       required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Fatura Adresi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <textarea name="billing_address"
                                          class="form-control @error('billing_address') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Tam fatura adresi"
                                          required>{{ old('billing_address') }}</textarea>
                                @error('billing_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Finansal & Pazar Bilgileri --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Finansal & Pazar Bilgileri</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Para Birimi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <select name="currency"
                                        class="form-select @error('currency') is-invalid @enderror"
                                        required>
                                    <option value="">Seçiniz...</option>
                                    <option value="TL"  @selected(old('currency') === 'TL')>🇹🇷 Türk Lirası (₺)</option>
                                    <option value="GBP" @selected(old('currency') === 'GBP')>🇬🇧 Pound (£)</option>
                                    <option value="EUR" @selected(old('currency') === 'EUR')>🇪🇺 Euro (€)</option>
                                    <option value="USD" @selected(old('currency') === 'USD')>🇺🇸 Dolar ($)</option>
                                </select>
                                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Market Seçimi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <select name="market"
                                        class="form-select @error('market') is-invalid @enderror"
                                        required>
                                    <option value="">Seçiniz...</option>
                                    <option value="domestic"    @selected(old('market') === 'domestic')>İç Pazar</option>
                                    <option value="europe"      @selected(old('market') === 'europe')>Avrupa Pazarı</option>
                                    <option value="middle_east" @selected(old('market') === 'middle_east')>Orta Doğu Pazarı</option>
                                    <option value="russia"      @selected(old('market') === 'russia')>Rusya Pazarı</option>
                                </select>
                                @error('market')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Ödeme Türü <span class="required-badge">ZORUNLU</span>
                                </label>
                                <select name="payment_type"
                                        class="form-select @error('payment_type') is-invalid @enderror"
                                        required>
                                    <option value="">Seçiniz...</option>
                                    <option value="agency_pay" @selected(old('payment_type') === 'agency_pay')>Acente Ödeyecek</option>
                                    <option value="guest_pay"  @selected(old('payment_type') === 'guest_pay')>Misafir Ödeyecek</option>
                                </select>
                                @error('payment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Ödeme Tipi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <select name="payment_method"
                                        class="form-select @error('payment_method') is-invalid @enderror"
                                        required>
                                    <option value="">Seçiniz...</option>
                                    <option value="city_ledger"   @selected(old('payment_method') === 'city_ledger')>Krediye Kaldır (City Ledger)</option>
                                    <option value="cash"          @selected(old('payment_method') === 'cash')>Nakit</option>
                                    <option value="credit_card"   @selected(old('payment_method') === 'credit_card')>Kredi Kartı</option>
                                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Havale</option>
                                </select>
                                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Konaklama Bilgileri --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Konaklama Bilgileri</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Konaklama Tipi <span class="required-badge">ZORUNLU</span>
                                </label>
                                <select name="accommodation_type"
                                        class="form-select @error('accommodation_type') is-invalid @enderror"
                                        required>
                                    <option value="">Seçiniz...</option>
                                    <option value="sold"      @selected(old('accommodation_type') === 'sold')>Sold</option>
                                    <option value="comp"      @selected(old('accommodation_type') === 'comp')>Comp</option>
                                    <option value="house_use" @selected(old('accommodation_type') === 'house_use')>House Use</option>
                                </select>
                                @error('accommodation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sağ Sütun --}}
            <div class="col-lg-4">

                {{-- Uyruklar --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <p class="form-section-title mb-0">Varsayılan Uyruklar</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <label class="form-label fw-semibold">Misafir Getirilecek Ülkeler</label>
                        <select name="nationalities[]"
                                id="nationalities"
                                class="form-select select2-multi @error('nationalities') is-invalid @enderror"
                                multiple>
                            @foreach(\App\Helper\DzHelper::countries() as $code => $label)
                            <option value="{{ $code }}"
                                @if(is_array(old('nationalities')) && in_array($code, old('nationalities'))) selected @endif>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('nationalities')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Bu acentenin misafir getirdiği ülkeleri seçin. Çoklu seçim yapabilirsiniz.
                        </small>
                    </div>
                </div>

                {{-- Özet Kartı --}}
                <div class="card border-0 shadow-sm" style="border-left:4px solid #c19b77 !important">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-muted">
                            <i class="fas fa-info-circle me-2"></i>Bilgi
                        </h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Tüm alanlar zorunludur</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Acenta kodu unique olmalıdır</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Uyruk seçimi isteğe bağlıdır</li>
                            <li><i class="fas fa-check text-success me-2"></i>Kaydettikten sonra düzenleyebilirsiniz</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        {{-- Kaydet Butonu --}}
        <div class="d-flex gap-3 mt-4">
            <button type="button" id="submitBtn" class="btn btn-lg fw-semibold px-5" style="background:#c19b77;color:#fff">
                <i class="fas fa-save me-2"></i>Kaydet
            </button>
            <a href="{{ route('agencies.index') }}" class="btn btn-lg btn-secondary px-5">
                <i class="fas fa-times me-2"></i>İptal
            </a>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    // Select2 init
    $('#nationalities').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Ülke seçin...',
        allowClear: true
    });

    // SweetAlert confirm on submit
    $('#submitBtn').on('click', function () {
        Swal.fire({
            title: 'Acente kaydedilsin mi?',
            text: 'Girilen bilgiler sisteme kaydedilecek.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c19b77',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save me-1"></i> Evet, Kaydet',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#agencyForm').submit();
            }
        });
    });
});
</script>
@endpush
