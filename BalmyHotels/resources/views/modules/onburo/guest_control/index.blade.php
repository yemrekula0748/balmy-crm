@extends('layouts.default')

@section('title', 'Misafir Kontrol')

@push('styles')
<script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
        prefix: 'tw-',
        corePlugins: {
            preflight: false,
        },
        theme: {
            extend: {
                colors: {
                    sand: '#c19b77',
                    clay: '#a07855',
                    latte: '#efe4d7',
                    ink: '#1f2937',
                    fog: '#f8fafc',
                    stonewarm: '#7c6a58',
                },
                boxShadow: {
                    'soft-xl': '0 22px 55px rgba(15, 23, 42, 0.10)',
                    'soft-lg': '0 12px 30px rgba(15, 23, 42, 0.08)',
                },
            },
        },
    };
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.css') }}">
<style>
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

    <div class="tw-relative tw-overflow-hidden tw-rounded-[30px] tw-bg-gradient-to-br tw-from-[#fbf7f2] tw-via-white tw-to-[#f8fafc] tw-shadow-soft-xl">
        <div class="tw-pointer-events-none tw-absolute tw--left-20 tw-top-10 tw-h-52 tw-w-52 tw-rounded-full tw-bg-[#f2e1cf] tw-blur-3xl"></div>
        <div class="tw-pointer-events-none tw-absolute tw-right-0 tw-top-0 tw-h-60 tw-w-60 tw-rounded-full tw-bg-[#f7e9dc] tw-blur-3xl"></div>
        <div class="tw-relative tw-grid tw-gap-6 tw-p-4 md:tw-p-6 xl:tw-grid-cols-[1.4fr,.9fr] xl:tw-gap-8 xl:tw-p-8">
            <section class="tw-overflow-hidden tw-rounded-[28px] tw-border tw-border-white/70 tw-bg-white/70 tw-shadow-soft-lg tw-backdrop-blur">
                <div class="tw-relative tw-overflow-hidden tw-rounded-[28px] tw-bg-gradient-to-br tw-from-[#2f241a] tw-via-[#574232] tw-to-[#876247] tw-p-6 tw-text-white md:tw-p-8">
                    <div class="tw-absolute tw-right-0 tw-top-0 tw-h-36 tw-w-36 tw-rounded-full tw-bg-white/10 tw-blur-2xl"></div>
                    <div class="tw-absolute tw-bottom-0 tw-left-8 tw-h-28 tw-w-28 tw-rounded-full tw-bg-[#f6d5aa]/20 tw-blur-2xl"></div>

                    <div class="tw-relative">
                        <div class="tw-mb-4 tw-flex tw-flex-wrap tw-gap-2">
                            <span class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-border tw-border-white/15 tw-bg-white/10 tw-px-3 tw-py-1.5 tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.18em]">
                                <i class="fas fa-hotel tw-text-[#f3cf9f]"></i>{{ $hotel['name'] ?? 'Balmy Foresta' }}
                            </span>
                            <span class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-border tw-border-white/15 tw-bg-white/10 tw-px-3 tw-py-1.5 tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.18em]">
                                <i class="fas fa-hashtag tw-text-[#f3cf9f]"></i>Hotel ID {{ $hotel['hotel_id'] ?? '-' }}
                            </span>
                            <span class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-border tw-border-white/15 tw-bg-white/10 tw-px-3 tw-py-1.5 tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.18em]">
                                <i class="fas fa-layer-group tw-text-[#f3cf9f]"></i>Misafir Kontrol
                            </span>
                        </div>

                        <div class="tw-max-w-2xl">
                            <h3 class="tw-text-2xl tw-font-black tw-tracking-[-0.03em] md:tw-text-[2.15rem]">
                                Oda numarasiyla misafiri sorgula, toplu giris-cikis kaydini tek ekrandan yonet.
                            </h3>
                            <p class="tw-mt-3 tw-max-w-xl tw-text-sm tw-leading-7 tw-text-white/78 md:tw-text-[15px]">
                                Eski yesil yapinin yerine, Onburo temasiyla uyumlu daha temiz ve modern bir sorgu karti
                                hazirladim. Oda bilgisi geldikten sonra aksiyonlar mobil popup altinda sabit kalir.
                            </p>
                        </div>

                        <form id="guest-control-query-form" class="tw-mt-7 tw-grid tw-gap-3 md:tw-grid-cols-[1fr,auto,auto] md:tw-items-end">
                            <div class="tw-relative">
                                <label for="room_no" class="tw-mb-2 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.16em] tw-text-white/70">
                                    Oda Numarasi
                                </label>
                                <div class="tw-relative">
                                    <span class="tw-pointer-events-none tw-absolute tw-inset-y-0 tw-left-0 tw-flex tw-items-center tw-pl-4 tw-text-[#8b6b51]">
                                        <i class="fas fa-door-open"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="room_no"
                                        name="room_no"
                                        class="tw-block tw-h-14 tw-w-full tw-rounded-2xl tw-border-0 tw-bg-white/95 tw-pl-11 tw-pr-4 tw-text-base tw-font-semibold tw-text-[#2f241a] tw-shadow-lg placeholder:tw-text-[#a8927c] focus:tw-ring-2 focus:tw-ring-[#f3cf9f]"
                                        placeholder="Orn: 682"
                                        maxlength="20"
                                        @disabled(!$integrationReady)
                                        required
                                    >
                                </div>
                            </div>
                            <button
                                type="submit"
                                id="guest-control-query-btn"
                                class="tw-inline-flex tw-h-14 tw-items-center tw-justify-center tw-gap-2 tw-rounded-2xl tw-bg-white tw-px-5 tw-text-sm tw-font-extrabold tw-text-[#5c3c22] tw-shadow-lg tw-transition hover:tw--translate-y-0.5 hover:tw-bg-[#fff7ef]"
                                @disabled(!$integrationReady)
                            >
                                <i class="fas fa-search"></i>Sorgula
                            </button>
                            @if(auth()->user()->hasPermission('guest_control_history', 'index'))
                                <a href="{{ route('frontdesk.guest-control.history') }}"
                                   class="tw-inline-flex tw-h-14 tw-items-center tw-justify-center tw-gap-2 tw-rounded-2xl tw-border tw-border-white/20 tw-bg-white/10 tw-px-5 tw-text-sm tw-font-bold tw-text-white tw-backdrop-blur tw-transition hover:tw-bg-white/16">
                                    <i class="fas fa-table"></i>Kayitlari Ac
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="tw-grid tw-gap-4 tw-p-4 md:tw-grid-cols-3 md:tw-p-6">
                    <div class="tw-rounded-3xl tw-border tw-border-[#ede2d6] tw-bg-[#fffdfb] tw-p-4 tw-shadow-[0_8px_24px_rgba(168,120,85,0.06)]">
                        <div class="tw-mb-3 tw-inline-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#f5ebe0] tw-text-[#9b6d45]">
                            <i class="fas fa-wave-square"></i>
                        </div>
                        <div class="tw-text-sm tw-font-extrabold tw-text-[#31261d]">Canli API Sorgusu</div>
                        <p class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Foresta odasindaki guncel misafir listesi anlik olarak geliyor.</p>
                    </div>
                    <div class="tw-rounded-3xl tw-border tw-border-[#ede2d6] tw-bg-[#fffdfb] tw-p-4 tw-shadow-[0_8px_24px_rgba(168,120,85,0.06)]">
                        <div class="tw-mb-3 tw-inline-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#f5ebe0] tw-text-[#9b6d45]">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="tw-text-sm tw-font-extrabold tw-text-[#31261d]">Toplu Secim</div>
                        <p class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Ayni odadaki birden cok misafiri checkbox ile tek seferde isleyebilirsin.</p>
                    </div>
                    <div class="tw-rounded-3xl tw-border tw-border-[#ede2d6] tw-bg-[#fffdfb] tw-p-4 tw-shadow-[0_8px_24px_rgba(168,120,85,0.06)]">
                        <div class="tw-mb-3 tw-inline-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#f5ebe0] tw-text-[#9b6d45]">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="tw-text-sm tw-font-extrabold tw-text-[#31261d]">Mobil Dostu Popup</div>
                        <p class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Kapat, giris ve cikis butonlari popup altinda sabit kalir; son kullanici zorlanmaz.</p>
                    </div>
                </div>
            </section>

            <aside class="tw-grid tw-gap-4">
                <div class="tw-rounded-[28px] tw-border tw-border-white/70 tw-bg-white/80 tw-p-5 tw-shadow-soft-lg tw-backdrop-blur md:tw-p-6">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-inline-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#f5ebe0] tw-text-[#9b6d45]">
                            <i class="fas fa-compass"></i>
                        </div>
                        <div>
                            <h5 class="tw-text-lg tw-font-black tw-text-[#2f241a]">Calisma Akisi</h5>
                            <p class="tw-mt-1 tw-text-sm tw-text-[#7c6a58]">Kisa, net ve personelin hizli kullanabilecegi adimlar.</p>
                        </div>
                    </div>

                    <div class="tw-mt-5 tw-space-y-3">
                        <div class="tw-flex tw-gap-3 tw-rounded-2xl tw-border tw-border-[#eee4da] tw-bg-[#fffdfb] tw-p-3">
                            <div class="tw-flex tw-h-9 tw-w-9 tw-flex-shrink-0 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#2f241a] tw-text-sm tw-font-black tw-text-white">1</div>
                            <div>
                                <div class="tw-text-sm tw-font-extrabold tw-text-[#2f241a]">Odayi Sorgula</div>
                                <div class="tw-mt-1 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Oda numarasini yaz, sistem HotelAdvisor uzerinden listeyi ceksin.</div>
                            </div>
                        </div>
                        <div class="tw-flex tw-gap-3 tw-rounded-2xl tw-border tw-border-[#eee4da] tw-bg-[#fffdfb] tw-p-3">
                            <div class="tw-flex tw-h-9 tw-w-9 tw-flex-shrink-0 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#2f241a] tw-text-sm tw-font-black tw-text-white">2</div>
                            <div>
                                <div class="tw-text-sm tw-font-extrabold tw-text-[#2f241a]">Misafirleri Isle</div>
                                <div class="tw-mt-1 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Popup icinde checkbox ile sec, sonra giris ya da cikis aksiyonunu ver.</div>
                            </div>
                        </div>
                        <div class="tw-flex tw-gap-3 tw-rounded-2xl tw-border tw-border-[#eee4da] tw-bg-[#fffdfb] tw-p-3">
                            <div class="tw-flex tw-h-9 tw-w-9 tw-flex-shrink-0 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-[#2f241a] tw-text-sm tw-font-black tw-text-white">3</div>
                            <div>
                                <div class="tw-text-sm tw-font-extrabold tw-text-[#2f241a]">Kayitlari Izle</div>
                                <div class="tw-mt-1 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">Tum hareketler ayri kayit ekraninda rapor gibi listelenir.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-rounded-[28px] tw-border tw-border-[#eaded0] tw-bg-gradient-to-br tw-from-[#fffaf5] tw-to-[#f8fafc] tw-p-5 tw-shadow-soft-lg md:tw-p-6">
                    <div class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-bg-[#f5ebe0] tw-px-3 tw-py-1 tw-text-[11px] tw-font-extrabold tw-uppercase tw-tracking-[0.18em] tw-text-[#8d6844]">
                        <i class="fas fa-shield-alt"></i>Durum
                    </div>
                    <div class="tw-mt-4 tw-text-lg tw-font-black tw-text-[#2f241a]">
                        {{ $integrationReady ? 'Entegrasyon kullanima hazir' : 'Entegrasyon bilgisi eksik' }}
                    </div>
                    <p class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-[#7c6a58]">
                        {{ $integrationReady
                            ? 'Foresta API bilgileri tanimliysa sorgu ve kayit aksiyonlari dogrudan calisacak.'
                            : 'Bu ekranda sorgu acilmaz. Once .env icinde gerekli HotelAdvisor alanlarini doldurmalisin.' }}
                    </p>
                    <div class="tw-mt-4 tw-grid tw-gap-3">
                        <div class="tw-rounded-2xl tw-border tw-border-[#eee4da] tw-bg-white/80 tw-p-3">
                            <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.16em] tw-text-[#a38b73]">Otel</div>
                            <div class="tw-mt-1 tw-text-sm tw-font-extrabold tw-text-[#2f241a]">{{ $hotel['name'] ?? 'Balmy Foresta' }}</div>
                        </div>
                        <div class="tw-rounded-2xl tw-border tw-border-[#eee4da] tw-bg-white/80 tw-p-3">
                            <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.16em] tw-text-[#a38b73]">Varsayilan Kaynak</div>
                            <div class="tw-mt-1 tw-text-sm tw-font-extrabold tw-text-[#2f241a]">HotelAdvisor Hotspot API</div>
                        </div>
                    </div>
                </div>
            </aside>
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
