@extends('layouts.default')

@section('title', 'Yeni Rezervasyon')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .form-section { border-left: 4px solid #c19b77; padding-left: 1rem; }
    .form-section-title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #c19b77; }
    .guest-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .75rem; }
    .guest-card .guest-header { background: linear-gradient(135deg, #c19b77, #a07855); color: #fff; border-radius: .75rem .75rem 0 0; }
    .guest-primary-badge { background: rgba(255,255,255,.25); font-size: .7rem; padding: 2px 8px; border-radius: 20px; }
    .step-badge { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Rezervasyon</h4>
                <span>Önbüro — Rezervasyon Oluştur</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('frontdesk.reservations.index') }}">Rezervasyonlar</a></li>
                <li class="breadcrumb-item active">Yeni Rezervasyon</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Hatalar:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('frontdesk.reservations.store') }}" method="POST" id="reservationForm">
        @csrf
        <div class="row g-4">

            {{-- ═══ SOL KOLON ═══ --}}
            <div class="col-lg-8">

                {{-- BÖLÜM 1: Acenta & Tarih --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <span class="step-badge me-2" style="background:#c19b77;color:#fff">1</span>
                            <span class="form-section-title">Acente & Tarih Bilgileri</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Acente <span class="text-danger">*</span></label>
                                <select name="agency_id" id="agency_select"
                                        class="form-select @error('agency_id') is-invalid @enderror select2-agency"
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kontrat Kodu</label>
                                <select name="agency_contract_id" id="contract_select"
                                        class="form-select @error('agency_contract_id') is-invalid @enderror">
                                    <option value="">— Önce acente seçin —</option>
                                    @foreach($contracts as $c)
                                    <option value="{{ $c->id }}"
                                        data-agency="{{ $c->agency_id }}"
                                        @selected(old('agency_contract_id') == $c->id)>
                                        {{ $c->contract_code }} — {{ $c->roomType->name ?? '' }}
                                        ({{ $c->start_date->format('d.m.Y') }} – {{ $c->end_date->format('d.m.Y') }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('agency_contract_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Giriş Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="check_in_date"
                                       class="form-control @error('check_in_date') is-invalid @enderror"
                                       value="{{ old('check_in_date') }}"
                                       required>
                                @error('check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Çıkış Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="check_out_date"
                                       class="form-control @error('check_out_date') is-invalid @enderror"
                                       value="{{ old('check_out_date') }}"
                                       required>
                                @error('check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Giriş Saati</label>
                                <input type="time" name="check_in_time"
                                       class="form-control"
                                       value="{{ old('check_in_time', '14:00') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Çıkış Saati</label>
                                <input type="time" name="check_out_time"
                                       class="form-control"
                                       value="{{ old('check_out_time', '12:00') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BÖLÜM 2: Oda & Kişi Bilgileri --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="form-section">
                            <span class="step-badge me-2" style="background:#c19b77;color:#fff">2</span>
                            <span class="form-section-title">Oda & Kişi Bilgileri</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Oda Tipi <span class="text-danger">*</span></label>
                                <select name="room_type_id" id="room_type_select"
                                        class="form-select @error('room_type_id') is-invalid @enderror select2-rt"
                                        required>
                                    <option value="">— Oda Tipi Seçin —</option>
                                    @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}" @selected(old('room_type_id') == $rt->id)>
                                        [{{ $rt->code }}] {{ $rt->name }} (Max {{ $rt->max_adults }} yetişkin)
                                    </option>
                                    @endforeach
                                </select>
                                @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Oda No <span class="text-danger">*</span></label>
                                <select name="room_id" id="room_select"
                                        class="form-select @error('room_id') is-invalid @enderror select2-room"
                                        required>
                                    <option value="">— Oda Seçin —</option>
                                    @foreach($rooms as $room)
                                    <option value="{{ $room->id }}"
                                        data-type="{{ $room->room_type_id }}"
                                        @selected(old('room_id') == $room->id)>
                                        {{ $room->room_number }}
                                        @if($room->floor) ({{ $room->floor }}. Kat)@endif
                                        @if($room->roomType) [{{ $room->roomType->code }}]@endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Yetişkin Sayısı <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="number" name="adults" id="adults_count"
                                           class="form-control @error('adults') is-invalid @enderror"
                                           value="{{ old('adults', 1) }}" min="1" max="10" required>
                                    @error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Çocuk Sayısı</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-child"></i></span>
                                    <input type="number" name="children" id="children_count"
                                           class="form-control"
                                           value="{{ old('children', 0) }}" min="0" max="10">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bebek Sayısı</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-baby"></i></span>
                                    <input type="number" name="babies" id="babies_count"
                                           class="form-control"
                                           value="{{ old('babies', 0) }}" min="0" max="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Uyruk</label>
                                <select name="nationality"
                                        class="form-select select2-nationality">
                                    <option value="">— Seçin —</option>
                                    @foreach(\App\Helper\DzHelper::countries() as $code => $label)
                                    <option value="{{ $label }}" @selected(old('nationality') === $label)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Voucher No</label>
                                <input type="text" name="voucher_no"
                                       class="form-control"
                                       value="{{ old('voucher_no') }}"
                                       placeholder="Voucher / referans numarası">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BÖLÜM 3: Misafir Bilgileri --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="form-section d-flex align-items-center">
                            <span class="step-badge me-2" style="background:#c19b77;color:#fff">3</span>
                            <span class="form-section-title">Misafir Bilgileri</span>
                        </div>
                        <button type="button" id="addGuestBtn" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Misafir Ekle
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Toplam konuklara göre (<span id="total_guest_count">1</span> kişi) misafir bilgilerini girin.
                            İlk eklenen misafir <strong>baş misafir</strong> kabul edilir.
                        </p>
                        <div id="guestsContainer">
                            {{-- JS ile dinamik eklenir, bir tane başlangıç kartı --}}
                        </div>
                    </div>
                </div>

            </div>

            {{-- ═══ SAĞ KOLON ═══ --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 1rem">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-list me-2 text-muted"></i>Özet</h6>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">Acente:</dt>
                            <dd class="col-7 fw-semibold" id="sum_agency">—</dd>
                            <dt class="col-5 text-muted">Kontrat:</dt>
                            <dd class="col-7" id="sum_contract">—</dd>
                            <dt class="col-5 text-muted">Giriş:</dt>
                            <dd class="col-7" id="sum_checkin">—</dd>
                            <dt class="col-5 text-muted">Çıkış:</dt>
                            <dd class="col-7" id="sum_checkout">—</dd>
                            <dt class="col-5 text-muted">Gece:</dt>
                            <dd class="col-7 fw-bold" id="sum_nights">—</dd>
                            <dt class="col-5 text-muted">Oda:</dt>
                            <dd class="col-7" id="sum_room">—</dd>
                            <dt class="col-5 text-muted">Kişi:</dt>
                            <dd class="col-7" id="sum_guests">—</dd>
                        </dl>
                    </div>
                    <div class="card-footer bg-white border-top p-4">
                        <button type="submit" class="btn w-100 fw-bold py-2" style="background:#c19b77;color:#fff">
                            <i class="fas fa-save me-2"></i>Rezervasyonu Kaydet
                        </button>
                        <a href="{{ route('frontdesk.reservations.index') }}" class="btn btn-secondary w-100 mt-2">
                            <i class="fas fa-times me-1"></i>İptal
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- Guest Card Template (hidden) --}}
<template id="guestCardTemplate">
    <div class="guest-card mb-3 guest-entry" data-index="__IDX__">
        <div class="guest-header p-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold">
                <i class="fas fa-user me-2"></i>
                <span class="guest-num">__NUM__</span>. Misafir
                <span class="guest-primary-badge ms-2" style="display:__PRIMARY_DISP__">Baş Misafir</span>
            </span>
            <button type="button" class="btn btn-sm btn-link text-white remove-guest-btn" style="display:__REMOVE_DISP__">
                <i class="fas fa-times"></i> Kaldır
            </button>
        </div>
        <div class="p-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Ad <span class="text-danger">*</span></label>
                    <input type="text" name="guests[__IDX__][first_name]" class="form-control form-control-sm" placeholder="Ad" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Soyad <span class="text-danger">*</span></label>
                    <input type="text" name="guests[__IDX__][last_name]" class="form-control form-control-sm" placeholder="Soyad" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Uyruk</label>
                    <select name="guests[__IDX__][nationality]" class="form-select form-select-sm">
                        <option value="">Seçin...</option>
                        @foreach(\App\Helper\DzHelper::countries() as $code => $label)
                        <option value="{{ $label }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Cinsiyet</label>
                    <select name="guests[__IDX__][gender]" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="male">Erkek</option>
                        <option value="female">Kadın</option>
                        <option value="other">Diğer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Doğum Tarihi</label>
                    <input type="date" name="guests[__IDX__][birth_date]" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">TC Kimlik No</label>
                    <input type="text" name="guests[__IDX__][id_no]" class="form-control form-control-sm" placeholder="TC / ID No" maxlength="50">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Pasaport No</label>
                    <input type="text" name="guests[__IDX__][passport_no]" class="form-control form-control-sm" placeholder="Pasaport No" maxlength="50">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Telefon</label>
                    <input type="tel" name="guests[__IDX__][phone]" class="form-control form-control-sm" placeholder="+90 5xx xxx xx xx">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">E-posta</label>
                    <input type="email" name="guests[__IDX__][email]" class="form-control form-control-sm" placeholder="email@örnek.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Araç Plakası</label>
                    <input type="text" name="guests[__IDX__][vehicle_plate]" class="form-control form-control-sm" placeholder="34 AA 001" style="text-transform:uppercase">
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {

    // All rooms data for filtering
    const allRooms = @json($rooms->map(fn($r) => ['id' => $r->id, 'room_number' => $r->room_number, 'room_type_id' => $r->room_type_id, 'floor' => $r->floor, 'code' => optional($r->roomType)->code]));

    // Select2 inits
    $('.select2-agency').select2({ theme:'bootstrap-5', width:'100%', placeholder:'Acente seçin...' });
    $('.select2-rt').select2({ theme:'bootstrap-5', width:'100%', placeholder:'Oda tipi seçin...' });
    $('.select2-room').select2({ theme:'bootstrap-5', width:'100%', placeholder:'Oda seçin...' });
    $('.select2-nationality').select2({ theme:'bootstrap-5', width:'100%', placeholder:'Uyruk seçin...', allowClear:true });

    // Filter contract list based on selected agency
    $('#agency_select').on('change', function () {
        const agencyId = this.value;
        const sumEl = document.getElementById('sum_agency');
        sumEl.textContent = $(this).find('option:selected').text().replace(/\[.*?\]\s*/, '') || '—';

        $('#contract_select option').each(function () {
            if (!$(this).data('agency')) { $(this).show(); return; }
            $(this).toggle(!agencyId || $(this).data('agency') == agencyId);
        });
        $('#contract_select').val('').trigger('change.select2');
    });

    // Contract summary
    $('#contract_select').on('change', function () {
        document.getElementById('sum_contract').textContent = $(this).find('option:selected').text().split('—')[0] || '—';
    });

    // Filter rooms based on selected room type
    $('#room_type_select').on('change', function () {
        const typeId = parseInt(this.value);
        const $roomSelect = $('#room_select');
        $roomSelect.find('option').each(function () {
            const roomTypeId = parseInt($(this).data('type'));
            if (!typeId || roomTypeId === typeId) {
                $(this).show().prop('disabled', false);
            } else {
                $(this).hide().prop('disabled', true);
            }
        });
        $roomSelect.val('').trigger('change.select2');
        document.getElementById('sum_room').textContent = '—';
    });

    $('#room_select').on('change', function () {
        document.getElementById('sum_room').textContent = $(this).find('option:selected').text() || '—';
    });

    // Date summary
    function updateNights() {
        const ci = document.querySelector('[name=check_in_date]').value;
        const co = document.querySelector('[name=check_out_date]').value;
        document.getElementById('sum_checkin').textContent = ci || '—';
        document.getElementById('sum_checkout').textContent = co || '—';
        if (ci && co) {
            const nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
            document.getElementById('sum_nights').textContent = nights > 0 ? nights + ' Gece' : '?';
        }
    }
    document.querySelector('[name=check_in_date]').addEventListener('change', updateNights);
    document.querySelector('[name=check_out_date]').addEventListener('change', updateNights);

    // Total guest count
    function updateGuestCount() {
        const a = parseInt(document.getElementById('adults_count').value) || 0;
        const c = parseInt(document.getElementById('children_count').value) || 0;
        const b = parseInt(document.getElementById('babies_count').value) || 0;
        document.getElementById('total_guest_count').textContent = a + c + b;
        document.getElementById('sum_guests').textContent = a + 'Y ' + (c > 0 ? c+'Ç ' : '') + (b > 0 ? b+'B' : '');
    }
    ['adults_count','children_count','babies_count'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateGuestCount);
    });
    updateGuestCount();

    // ── Dynamic Guest Cards ──
    let guestCount = 0;
    const container = document.getElementById('guestsContainer');
    const template  = document.getElementById('guestCardTemplate').innerHTML;

    function addGuest() {
        const idx  = guestCount;
        const num  = guestCount + 1;
        const isFirst = guestCount === 0;
        let html = template
            .replace(/__IDX__/g, idx)
            .replace(/__NUM__/g, num)
            .replace(/__PRIMARY_DISP__/g, isFirst ? 'inline' : 'none')
            .replace(/__REMOVE_DISP__/g, isFirst ? 'none' : 'inline-block');
        container.insertAdjacentHTML('beforeend', html);
        guestCount++;
    }

    // Remove guest
    $(document).on('click', '.remove-guest-btn', function () {
        $(this).closest('.guest-entry').remove();
        // re-number remaining
        container.querySelectorAll('.guest-entry').forEach((el, i) => {
            el.querySelector('.guest-num').textContent = i + 1;
        });
    });

    document.getElementById('addGuestBtn').addEventListener('click', addGuest);

    // Add first guest card on load
    addGuest();

    // Validate at least 1 guest
    document.getElementById('reservationForm').addEventListener('submit', function (e) {
        if (container.querySelectorAll('.guest-entry').length === 0) {
            e.preventDefault();
            Swal.fire({ title:'Misafir gerekli!', text:'En az 1 misafir bilgisi girilmelidir.', icon:'warning' });
        }
    });
});
</script>
@endpush
