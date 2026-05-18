@extends('layouts.default')

@section('title', 'Misafir Kontrol')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.css') }}">
<style>
    .guest-control-card { border: 0; border-radius: 18px; box-shadow: 0 18px 45px rgba(15, 23, 42, .08); }
    .guest-control-hero {
        background:
            radial-gradient(circle at top left, rgba(12, 148, 136, .18), transparent 38%),
            radial-gradient(circle at bottom right, rgba(245, 158, 11, .18), transparent 34%),
            linear-gradient(135deg, #0f766e, #134e4a);
        color: #fff;
    }
    .guest-control-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .guest-control-note {
        background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(248,250,252,.96));
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 18px;
    }
    .guest-control-stat {
        border-radius: 16px;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .16);
        box-shadow: 0 10px 20px rgba(15, 23, 42, .04);
    }
    .guest-alert-wrap { text-align: left; }
    .guest-alert-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: .9rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .guest-alert-count {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, .08);
        color: #0f766e;
        font-size: .76rem;
        font-weight: 700;
    }
    .guest-alert-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: .85rem;
        max-height: 420px;
        overflow-y: auto;
        padding-right: .2rem;
    }
    .guest-alert-item {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: .95rem 1rem;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }
    .guest-alert-item .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
        margin-top: .15rem;
    }
    .guest-alert-name {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .guest-alert-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .7rem;
    }
    .guest-alert-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .6rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        background: #eef2ff;
        color: #4338ca;
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
            <div class="card guest-control-card guest-control-hero h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="guest-control-badge">
                            <i class="fas fa-hotel"></i> {{ $hotel['name'] ?? 'Balmy Foresta' }}
                        </span>
                        <span class="guest-control-badge">
                            <i class="fas fa-hashtag"></i> HOTELID {{ $hotel['hotel_id'] ?? '-' }}
                        </span>
                    </div>

                    <h3 class="fw-bold mb-2">Oda numarasiyla misafirleri sorgula</h3>
                    <p class="mb-4 opacity-75" style="max-width: 680px;">
                        Oda numarasini yazip sorguladiginda, SweetAlert icinde odadaki misafirleri gorebilir,
                        sectiklerini toplu olarak giris yapti veya cikis yapti diye kaydedebilirsin.
                    </p>

                    <form id="guest-control-query-form" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="room_no" class="form-label fw-semibold">Oda Numarasi</label>
                            <input
                                type="text"
                                id="room_no"
                                name="room_no"
                                class="form-control form-control-lg"
                                placeholder="Orn: 682"
                                maxlength="20"
                                @disabled(!$integrationReady)
                                required
                            >
                        </div>
                        <div class="col-md-7 d-flex flex-wrap gap-2">
                            <button
                                type="submit"
                                id="guest-control-query-btn"
                                class="btn btn-light btn-lg px-4"
                                @disabled(!$integrationReady)
                            >
                                <i class="fas fa-search me-2"></i>Sorgula
                            </button>
                            @if(auth()->user()->hasPermission('guest_control_history', 'index'))
                                <a href="{{ route('frontdesk.guest-control.history') }}" class="btn btn-outline-light btn-lg px-4">
                                    <i class="fas fa-table me-2"></i>Kayitlari Ac
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card guest-control-card guest-control-note h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Calisma Sekli</h5>

                    <div class="guest-control-stat mb-3">
                        <div class="fw-semibold text-dark mb-1">1. Odayi Sorgula</div>
                        <small class="text-muted">Oda numarasi ile Foresta API'sinden aktif liste cekilir.</small>
                    </div>

                    <div class="guest-control-stat mb-3">
                        <div class="fw-semibold text-dark mb-1">2. Misafirleri Sec</div>
                        <small class="text-muted">SweetAlert icinde tum misafirler checkbox ile secilebilir.</small>
                    </div>

                    <div class="guest-control-stat">
                        <div class="fw-semibold text-dark mb-1">3. Toplu Kaydet</div>
                        <small class="text-muted">Secilenler tek seferde giris veya cikis yapti olarak sisteme yazilir.</small>
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
                <div class="guest-alert-head">
                    <div>
                        <div class="fw-bold text-dark">Secilecek misafirleri belirle</div>
                        <small class="text-muted">Tum secili misafirler toplu kayit edilir.</small>
                    </div>
                    <label class="form-check mb-0">
                        <input id="select-all-guests" class="form-check-input" type="checkbox" checked>
                        <span class="form-check-label ms-1">Tumunu sec</span>
                    </label>
                </div>
                <div class="guest-alert-count">
                    <i class="fas fa-users"></i>${guests.length} misafir bulundu
                </div>
                <div class="guest-alert-list mt-3">${cards}</div>
            </div>
        `;
    }

    function selectedKeys() {
        return Array.from(document.querySelectorAll('.guest-choice:checked')).map((item) => item.value);
    }

    function syncSelectAll() {
        const checkboxes = Array.from(document.querySelectorAll('.guest-choice'));
        const selectAll = document.getElementById('select-all-guests');

        if (!checkboxes.length || !selectAll) {
            return;
        }

        selectAll.checked = checkboxes.every((item) => item.checked);
    }

    function saveAction(roomNo, actionType) {
        const selectionKeys = selectedKeys();

        if (!selectionKeys.length) {
            Swal.showValidationMessage('En az bir misafir secmelisin.');
            return false;
        }

        return fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                room_no: roomNo,
                action_type: actionType,
                selection_keys: selectionKeys,
            }),
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Kayit sirasinda bir hata olustu.');
                }
                return data;
            })
            .catch((error) => {
                Swal.showValidationMessage(error.message);
            });
    }

    function openGuestAlert(roomNo, guests) {
        Swal.fire({
            title: `Oda ${escapeHtml(roomNo)} - Balmy Foresta`,
            html: renderGuestCards(guests),
            width: 820,
            showCancelButton: true,
            showConfirmButton: canCreate,
            showDenyButton: canCreate,
            confirmButtonText: 'Giris Yapti',
            denyButtonText: 'Cikis Yapti',
            cancelButtonText: 'Tamam',
            showLoaderOnConfirm: true,
            showLoaderOnDeny: true,
            allowOutsideClick: () => !Swal.isLoading(),
            allowEscapeKey: () => !Swal.isLoading(),
            preConfirm: () => saveAction(roomNo, 'check_in'),
            preDeny: () => saveAction(roomNo, 'check_out'),
            didOpen: () => {
                const selectAll = document.getElementById('select-all-guests');
                const checkboxes = document.querySelectorAll('.guest-choice');

                selectAll?.addEventListener('change', (event) => {
                    checkboxes.forEach((item) => {
                        item.checked = event.target.checked;
                    });
                });

                checkboxes.forEach((item) => {
                    item.addEventListener('change', syncSelectAll);
                });
            },
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                Swal.fire({
                    icon: 'success',
                    title: 'Kaydedildi',
                    text: result.value.message,
                    confirmButtonText: 'Tamam',
                });
            }
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
