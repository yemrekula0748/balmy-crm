@extends('layouts.default')

@section('title', 'Misafir Kontrol')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.css') }}">
<style>
    .gc-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .08);
        overflow: hidden;
    }
    .gc-hero {
        position: relative;
        color: #fff;
        background: linear-gradient(135deg, #c19b77 0%, #a07855 55%, #7c5a3f 100%);
    }
    .gc-hero::before,
    .gc-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,.10);
        filter: blur(4px);
    }
    .gc-hero::before {
        width: 160px;
        height: 160px;
        top: -55px;
        right: -25px;
    }
    .gc-hero::after {
        width: 110px;
        height: 110px;
        bottom: -40px;
        left: 24px;
    }
    .gc-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.16);
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .gc-label {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(255,255,255,.78);
    }
    .gc-room-wrap {
        border-radius: .95rem;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.18);
        padding: .35rem;
    }
    .gc-room-wrap .input-group-text {
        border: 0;
        background: #fff;
        color: #a07855;
        border-radius: .75rem 0 0 .75rem;
    }
    .gc-room-wrap .form-control {
        border: 0;
        min-height: 52px;
        font-weight: 700;
        color: #3b2a1d;
        border-radius: 0 .75rem .75rem 0;
    }
    .gc-room-wrap .form-control:focus {
        box-shadow: none;
    }
    .gc-btn-theme {
        border: 0;
        background: #fff;
        color: #7c5a3f;
        font-weight: 700;
        min-height: 52px;
        border-radius: .9rem;
        box-shadow: 0 10px 25px rgba(0,0,0,.08);
    }
    .gc-btn-theme:hover {
        color: #7c5a3f;
        background: #fdf7f1;
    }
    .gc-btn-soft {
        min-height: 52px;
        border-radius: .9rem;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.10);
        color: #fff;
        font-weight: 700;
    }
    .gc-btn-soft:hover {
        color: #fff;
        background: rgba(255,255,255,.16);
    }
    .gc-stat-card {
        border: 1px solid #ece3d8;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        height: 100%;
    }
    .gc-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: .9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(193,155,119,.14);
        color: #a07855;
        font-size: 1rem;
    }
    .gc-side-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .08);
    }
    .gc-side-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f0e7dc;
        padding: 1rem 1.25rem;
    }
    .gc-side-card .card-body {
        padding: 1.25rem;
    }
    .gc-step-item {
        display: flex;
        gap: .9rem;
        align-items: flex-start;
        padding: .95rem 0;
    }
    .gc-step-item + .gc-step-item {
        border-top: 1px solid #f2ebe2;
    }
    .gc-step-badge {
        width: 34px;
        height: 34px;
        border-radius: .85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(135deg,#c19b77,#a07855);
        color: #fff;
        font-weight: 800;
        font-size: .85rem;
    }
    .gc-status-card {
        border-left: 4px solid #c19b77;
    }
    .gc-mini-note {
        border-radius: .85rem;
        background: #faf6f1;
        border: 1px solid #efe4d7;
        padding: .85rem .95rem;
    }
    .guest-alert-popup {
        width: min(920px, calc(100vw - 22px)) !important;
        border-radius: 28px !important;
        padding: 0 !important;
        overflow: hidden !important;
        box-shadow: 0 28px 70px rgba(15, 23, 42, .20) !important;
    }
    .guest-alert-popup .swal2-title {
        text-align: left !important;
        padding: 1.45rem 1.45rem .75rem !important;
        font-size: 1.3rem !important;
        font-weight: 800 !important;
        color: #1f2937 !important;
    }
    .guest-alert-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
    }
    .guest-alert-shell {
        display: flex;
        flex-direction: column;
        max-height: min(80vh, 760px);
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(193, 155, 119, .12), transparent 26%),
            linear-gradient(180deg, #fffdfb, #fff);
    }
    .guest-alert-scroll {
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0 1.45rem 1rem;
    }
    .guest-alert-head {
        position: sticky;
        top: 0;
        z-index: 2;
        margin: 0 -1.45rem 1rem;
        padding: 1rem 1.45rem .95rem;
        background: linear-gradient(180deg, rgba(255, 251, 247, .98), rgba(255, 255, 255, .94));
        border-bottom: 1px solid #eee3d7;
        backdrop-filter: blur(8px);
    }
    .guest-alert-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: .8rem;
    }
    .guest-alert-toolbar button {
        border: 1px solid #e6d8c7;
        border-radius: 999px;
        background: #fff;
        color: #6b5b4b;
        font-size: .76rem;
        font-weight: 700;
        padding: .45rem .8rem;
    }
    .guest-alert-toolbar button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }
    .guest-alert-counter {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        padding: .4rem .8rem;
        border-radius: 999px;
        background: #fbf4ec;
        color: #8d6844;
        font-size: .76rem;
        font-weight: 800;
    }
    .guest-alert-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: .9rem;
        padding-bottom: .5rem;
    }
    .guest-alert-item {
        display: block;
        border: 1px solid #ece3d8;
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(180deg, #fff, #fdfaf6);
        box-shadow: 0 10px 24px rgba(148, 112, 72, .06);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .guest-alert-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(148, 112, 72, .10);
        border-color: #dcc3a7;
    }
    .guest-alert-item .form-check-input {
        width: 1.08rem;
        height: 1.08rem;
        margin-top: .15rem;
        border-color: #c19b77;
    }
    .guest-alert-item .form-check-input:checked {
        background-color: #c19b77;
        border-color: #c19b77;
    }
    .guest-alert-name {
        font-size: 1rem;
        font-weight: 800;
        color: #1f2937;
    }
    .guest-alert-subtext {
        font-size: .8rem;
        color: #8a735d;
        margin-top: .15rem;
    }
    .guest-alert-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .8rem;
    }
    .guest-alert-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .38rem .7rem;
        background: #f6efe7;
        color: #6f5a47;
        font-size: .75rem;
        font-weight: 700;
    }
    .guest-alert-actions {
        display: flex;
        align-items: stretch;
        gap: .75rem;
        padding: 1rem 1.45rem 1.35rem;
        border-top: 1px solid #eee3d7;
        background: linear-gradient(180deg, rgba(255,255,255,.96), #fff);
        box-shadow: 0 -10px 24px rgba(15, 23, 42, .06);
    }
    .guest-alert-btn {
        border: 0;
        border-radius: 16px;
        min-height: 50px;
        padding: .9rem 1.1rem;
        font-size: .92rem;
        font-weight: 800;
        letter-spacing: .01em;
        transition: transform .18s ease, opacity .18s ease, box-shadow .18s ease;
    }
    .guest-alert-btn:hover { transform: translateY(-1px); }
    .guest-alert-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .guest-alert-btn-close {
        flex: 1.05;
        background: #f5f5f4;
        color: #44403c;
        border: 1px solid #e7e5e4;
    }
    .guest-alert-btn-out {
        flex: 1;
        background: linear-gradient(135deg, #f7c873, #d49b36);
        color: #4b2e09;
        box-shadow: 0 10px 24px rgba(245, 158, 11, .18);
    }
    .guest-alert-btn-in {
        flex: 1;
        background: linear-gradient(135deg, #c19b77, #9b6d45);
        color: #fff;
        box-shadow: 0 10px 24px rgba(160, 120, 85, .18);
    }
    .guest-alert-empty-note {
        margin-top: .85rem;
        border-radius: 14px;
        background: #fcf7f1;
        border: 1px dashed #e4d5c3;
        color: #8b7158;
        padding: .85rem .95rem;
        font-size: .8rem;
    }
    @media (max-width: 640px) {
        .guest-alert-popup {
            width: calc(100vw - 12px) !important;
            border-radius: 22px !important;
        }
        .guest-alert-popup .swal2-title {
            padding: 1.1rem 1rem .7rem !important;
            font-size: 1.08rem !important;
        }
        .guest-alert-scroll {
            padding: 0 1rem .85rem;
        }
        .guest-alert-head {
            margin: 0 -1rem .85rem;
            padding: .9rem 1rem .85rem;
        }
        .guest-alert-actions {
            position: sticky;
            bottom: 0;
            z-index: 3;
            flex-wrap: wrap;
            gap: .6rem;
            padding: .85rem 1rem calc(1rem + env(safe-area-inset-bottom));
        }
        .guest-alert-btn {
            flex: 1 1 calc(50% - .3rem);
            min-height: 48px;
        }
        .guest-alert-btn-close {
            flex-basis: 100%;
            order: 3;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Misafir Kontrol</h4>
                <span>Balmy Foresta oda bazli misafir sorgu ve toplu giris/cikis kaydi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Onburo</span></li>
                <li class="breadcrumb-item active">Misafir Kontrol</li>
            </ol>
        </div>
    </div>

    @if(!$integrationReady)
        <div class="alert alert-danger border-0 shadow-sm">
            <strong>HotelAdvisor entegrasyonu hazir degil.</strong>
            <span class="d-block mt-1">Lutfen `.env` icinde Foresta API bilgilerini tanimlayin.</span>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card gc-card gc-hero mb-4">
                <div class="card-body p-4 p-lg-5 position-relative">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="gc-chip"><i class="fas fa-hotel"></i>{{ $hotel['name'] ?? 'Balmy Foresta' }}</span>
                        <span class="gc-chip"><i class="fas fa-hashtag"></i>Hotel ID {{ $hotel['hotel_id'] ?? '-' }}</span>
                        <span class="gc-chip"><i class="fas fa-users"></i>Misafir Kontrol</span>
                    </div>

                    <div class="row align-items-end g-4">
                        <div class="col-lg-7">
                            <h3 class="fw-bold mb-2">Oda bazli sorgu ile misafir giris ve cikis kaydini yonet.</h3>
                            <p class="mb-0 text-white-50">
                                Oda numarasini gir, HotelAdvisor uzerinden guncel misafirleri cek ve popup icinden
                                toplu giris veya cikis kaydini kolayca olustur.
                            </p>
                        </div>
                        <div class="col-lg-5">
                            <form id="guest-control-query-form" class="row g-3">
                                <div class="col-12">
                                    <label for="room_no" class="gc-label mb-2 d-block">Oda Numarasi</label>
                                    <div class="gc-room-wrap">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                            <input
                                                type="text"
                                                id="room_no"
                                                name="room_no"
                                                class="form-control"
                                                placeholder="Orn: 682"
                                                maxlength="20"
                                                @disabled(!$integrationReady)
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-{{ auth()->user()->hasPermission('guest_control_history', 'index') ? '6' : '12' }}">
                                    <button
                                        type="submit"
                                        id="guest-control-query-btn"
                                        class="btn gc-btn-theme w-100"
                                        @disabled(!$integrationReady)
                                    >
                                        <i class="fas fa-search me-2"></i>Sorgula
                                    </button>
                                </div>
                                @if(auth()->user()->hasPermission('guest_control_history', 'index'))
                                    <div class="col-sm-6">
                                        <a href="{{ route('frontdesk.guest-control.history') }}" class="btn gc-btn-soft w-100">
                                            <i class="fas fa-table me-2"></i>Kayitlar
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="gc-stat-card p-4">
                        <div class="gc-stat-icon mb-3">
                            <i class="fas fa-wave-square"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1">Canli API Sorgusu</div>
                        <small class="text-muted d-block">Foresta odasindaki guncel misafir listesi anlik olarak gelir.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gc-stat-card p-4">
                        <div class="gc-stat-icon mb-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1">Toplu Islem</div>
                        <small class="text-muted d-block">Ayni odadaki birden cok misafiri checkbox ile tek seferde isleyebilirsin.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gc-stat-card p-4">
                        <div class="gc-stat-icon mb-3">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1">Mobil Kolaylik</div>
                        <small class="text-muted d-block">Popup alt aksiyonlari mobilde sabit kalir, liste ustten kayar.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card gc-side-card gc-status-card mb-4">
                <div class="card-header">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-shield-alt me-2" style="color:#c19b77"></i>Entegrasyon Durumu
                    </h6>
                </div>
                <div class="card-body">
                    <div class="fw-bold text-dark mb-2">
                        {{ $integrationReady ? 'Entegrasyon kullanima hazir' : 'Entegrasyon bilgisi eksik' }}
                    </div>
                    <p class="text-muted small mb-3">
                        {{ $integrationReady
                            ? 'Foresta API bilgileri tanimliysa sorgu ve kayit aksiyonlari bu ekrandan dogrudan calisir.'
                            : 'Sorgu acilmaz. Once .env icinde gerekli HotelAdvisor alanlarini doldurmalisin.' }}
                    </p>

                    <div class="gc-mini-note mb-3">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Otel</div>
                        <div class="fw-semibold text-dark">{{ $hotel['name'] ?? 'Balmy Foresta' }}</div>
                    </div>
                    <div class="gc-mini-note">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Kaynak</div>
                        <div class="fw-semibold text-dark">HotelAdvisor Hotspot API</div>
                    </div>
                </div>
            </div>

            <div class="card gc-side-card">
                <div class="card-header">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-compass me-2" style="color:#c19b77"></i>Calisma Akisi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="gc-step-item pt-0">
                        <span class="gc-step-badge">1</span>
                        <div>
                            <div class="fw-bold text-dark">Odayi sorgula</div>
                            <small class="text-muted">Oda numarasini gir ve sistemin guncel misafir listesini cekmesini bekle.</small>
                        </div>
                    </div>
                    <div class="gc-step-item">
                        <span class="gc-step-badge">2</span>
                        <div>
                            <div class="fw-bold text-dark">Misafirleri sec</div>
                            <small class="text-muted">Popup icinden kayda dahil edecegin misafirleri checkbox ile isaretle.</small>
                        </div>
                    </div>
                    <div class="gc-step-item pb-0">
                        <span class="gc-step-badge">3</span>
                        <div>
                            <div class="fw-bold text-dark">Aksiyonu kaydet</div>
                            <small class="text-muted">Giris Yapti veya Cikis Yapti ile kaydi sisteme ekle, sonra gecmisten izle.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
(() => {
    const form = document.getElementById('guest-control-query-form');
    if (!form) {
        return;
    }

    const roomInput = document.getElementById('room_no');
    const queryButton = document.getElementById('guest-control-query-btn');
    const lookupUrl = @json(route('frontdesk.guest-control.lookup'));
    const storeUrl = @json(route('frontdesk.guest-control.store'));
    const canCreate = @json(auth()->user()->hasPermission('guest_control', 'create'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderGuestCards(guests) {
        const cards = guests.map((guest) => `
            <label class="guest-alert-item d-block">
                <div class="d-flex align-items-start gap-3">
                    <input class="form-check-input guest-choice flex-shrink-0" type="checkbox" value="${escapeHtml(guest.selection_key)}" checked>
                    <div class="flex-grow-1">
                        <div class="guest-alert-name">${escapeHtml(guest.full_name || '-')}</div>
                        <div class="guest-alert-subtext">${escapeHtml(guest.email || guest.phone || 'Misafir bilgisi hazir')}</div>
                        <div class="guest-alert-meta">
                            <span class="guest-alert-pill"><i class="fas fa-door-open"></i>Oda ${escapeHtml(guest.room_no || '-')}</span>
                            <span class="guest-alert-pill"><i class="fas fa-calendar-plus"></i>Check-in ${escapeHtml(guest.checkin || '-')}</span>
                            <span class="guest-alert-pill"><i class="fas fa-calendar-minus"></i>Check-out ${escapeHtml(guest.checkout || '-')}</span>
                            ${(guest.phone ? `<span class="guest-alert-pill"><i class="fas fa-phone"></i>${escapeHtml(guest.phone)}</span>` : '')}
                            ${(guest.nationality ? `<span class="guest-alert-pill"><i class="fas fa-flag"></i>${escapeHtml(guest.nationality)}</span>` : '')}
                        </div>
                    </div>
                </div>
            </label>
        `).join('');

        return `
            <div class="guest-alert-wrap">
                <div class="guest-alert-shell">
                    <div class="guest-alert-scroll">
                        <div class="guest-alert-head">
                            <div>
                                <div class="fw-bold text-dark">Secilecek misafirleri belirle</div>
                                <small class="text-muted">Mobilde aksiyonlar altta sabit kalir, misafir listesi ise ust tarafta kayar.</small>
                            </div>
                            <span class="guest-alert-counter" id="guest-selected-count">
                                <i class="fas fa-check-circle"></i>${guests.length} secili
                            </span>
                            <div class="guest-alert-toolbar">
                                <button type="button" id="select-all-guests">Tumunu sec</button>
                                <button type="button" id="clear-all-guests">Secimi temizle</button>
                            </div>
                        </div>
                        <div class="guest-alert-list">${cards}</div>
                        ${!canCreate ? '<div class="guest-alert-empty-note"><i class="fas fa-lock me-2"></i>Bu popupu goruntuleme yetkin var. Kayit aksiyonlari icin olusturma yetkisi gerekiyor.</div>' : ''}
                    </div>
                    <div class="guest-alert-actions">
                        ${canCreate ? `
                            <button type="button" class="guest-alert-btn guest-alert-btn-out guest-alert-command" data-action="check_out">
                                <i class="fas fa-sign-out-alt me-2"></i>Cikis Yapti
                            </button>
                            <button type="button" class="guest-alert-btn guest-alert-btn-in guest-alert-command" data-action="check_in">
                                <i class="fas fa-sign-in-alt me-2"></i>Giris Yapti
                            </button>
                        ` : ''}
                        <button type="button" class="guest-alert-btn guest-alert-btn-close guest-alert-command" data-action="close">
                            <i class="fas fa-times me-2"></i>Kapat
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function selectedKeys() {
        return Array.from(document.querySelectorAll('.guest-choice:checked')).map((item) => item.value);
    }

    function updateSelectedBadge() {
        const selectedCount = selectedKeys().length;
        const badge = document.getElementById('guest-selected-count');
        if (badge) {
            badge.innerHTML = `<i class="fas fa-check-circle"></i>${selectedCount} secili`;
        }
    }

    function syncSelectAll() {
        const checkboxes = Array.from(document.querySelectorAll('.guest-choice'));
        const selectAll = document.getElementById('select-all-guests');
        const clearAll = document.getElementById('clear-all-guests');

        if (!checkboxes.length || !selectAll || !clearAll) {
            return;
        }

        const allSelected = checkboxes.every((item) => item.checked);
        const noneSelected = checkboxes.every((item) => !item.checked);
        selectAll.disabled = allSelected;
        clearAll.disabled = noneSelected;
        updateSelectedBadge();
    }

    function setActionButtonsLoading(isLoading, activeAction = null) {
        document.querySelectorAll('.guest-alert-command').forEach((button) => {
            const action = button.dataset.action;
            button.disabled = isLoading;

            if (!button.dataset.originalLabel) {
                button.dataset.originalLabel = button.innerHTML;
            }

            if (isLoading && activeAction && action === activeAction) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kaydediliyor';
            } else {
                button.innerHTML = button.dataset.originalLabel;
            }
        });
    }

    async function saveAction(roomNo, actionType) {
        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                room_no: roomNo,
                action_type: actionType,
                selection_keys: selectedKeys(),
            }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Kayit sirasinda bir hata olustu.');
        }

        return data;
    }

    async function handleGuestAction(roomNo, actionType) {
        const selectionKeys = selectedKeys();

        if (!selectionKeys.length) {
            Swal.showValidationMessage('En az bir misafir secmelisin.');
            return;
        }

        try {
            setActionButtonsLoading(true, actionType);
            const data = await saveAction(roomNo, actionType);
            Swal.close();
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Kaydedildi',
                    text: data.message,
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#9b6d45',
                });
            }, 120);
        } catch (error) {
            Swal.showValidationMessage(error.message);
            setActionButtonsLoading(false);
        }
    }

    function openGuestAlert(roomNo, guests) {
        Swal.fire({
            titleText: `Oda ${roomNo} - Balmy Foresta`,
            html: renderGuestCards(guests),
            customClass: {
                popup: 'guest-alert-popup',
            },
            padding: 0,
            showConfirmButton: false,
            showCancelButton: false,
            buttonsStyling: false,
            heightAuto: false,
            allowOutsideClick: () => !Swal.isLoading(),
            allowEscapeKey: () => !Swal.isLoading(),
            didOpen: () => {
                const selectAll = document.getElementById('select-all-guests');
                const clearAll = document.getElementById('clear-all-guests');
                const checkboxes = document.querySelectorAll('.guest-choice');
                const actionButtons = document.querySelectorAll('.guest-alert-command');

                selectAll?.addEventListener('click', () => {
                    checkboxes.forEach((item) => {
                        item.checked = true;
                    });
                    syncSelectAll();
                });

                clearAll?.addEventListener('click', () => {
                    checkboxes.forEach((item) => {
                        item.checked = false;
                    });
                    syncSelectAll();
                });

                checkboxes.forEach((item) => {
                    item.addEventListener('change', syncSelectAll);
                });

                actionButtons.forEach((button) => {
                    button.addEventListener('click', async () => {
                        const action = button.dataset.action;

                        if (action === 'close') {
                            Swal.close();
                            return;
                        }

                        await handleGuestAction(roomNo, action);
                    });
                });

                syncSelectAll();
            },
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const roomNo = roomInput.value.trim();
        if (!roomNo) {
            Swal.fire({
                icon: 'warning',
                title: 'Oda numarasi gerekli',
                text: 'Lutfen once oda numarasini gir.',
            });
            return;
        }

        const originalText = queryButton.innerHTML;
        queryButton.disabled = true;
        queryButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sorgulaniyor';

        try {
            const response = await fetch(lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ room_no: roomNo }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Sorgu sirasinda bir hata olustu.');
            }

            openGuestAlert(data.room_no, data.guests || []);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Sorgu basarisiz',
                text: error.message,
                confirmButtonText: 'Tamam',
            });
        } finally {
            queryButton.disabled = false;
            queryButton.innerHTML = originalText;
        }
    });
})();
</script>
@endpush
