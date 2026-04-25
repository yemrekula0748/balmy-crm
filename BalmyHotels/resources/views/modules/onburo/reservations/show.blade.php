@extends('layouts.default')

@section('title', 'Rezervasyon #' . $reservation->reservation_no)

@push('styles')
<style>
    .info-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #8d9297; }
    .info-value { font-size: .92rem; font-weight: 500; }
    .section-card { border: 0; box-shadow: 0 1px 8px rgba(0,0,0,.07); border-radius: .75rem; }
    .section-header { background: linear-gradient(135deg,#c19b77,#a07855); color: #fff; border-radius: .75rem .75rem 0 0; padding: .85rem 1.25rem; }
    .guest-card { border: 1px solid #e9ecef; border-radius: .6rem; transition: box-shadow .2s; }
    .guest-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .primary-badge { background: rgba(255,255,255,.25); font-size: .68rem; padding: 2px 10px; border-radius: 20px; border: 1px solid rgba(255,255,255,.4); }
    .status-strip { height: 4px; border-radius: 0 0 .75rem .75rem; }
    .timeline-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Rezervasyon Detayı</h4>
                <span>Önbüro — {{ $reservation->reservation_no }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('frontdesk.reservations.index') }}">Rezervasyonlar</a></li>
                <li class="breadcrumb-item active">{{ $reservation->reservation_no }}</li>
            </ol>
        </div>
    </div>

    {{-- Top action bar --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-dark fw-bold fs-6 px-3 py-2">{{ $reservation->reservation_no }}</span>
            @php $color = \App\Models\Reservation::STATUS_COLORS[$reservation->status] ?? 'secondary'; @endphp
            <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                {{ \App\Models\Reservation::STATUSES[$reservation->status] ?? $reservation->status }}
            </span>
        </div>
        <a href="{{ route('frontdesk.reservations.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Rezervasyonlara Dön
        </a>
    </div>

    <div class="row g-3 align-items-start">

        {{-- ── Sol: Rezervasyon Bilgileri ── --}}
        <div class="col-lg-8">

            {{-- Genel Bilgiler --}}
            <div class="section-card card mb-4">
                <div class="section-header d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-check"></i>
                    <span class="fw-bold">Rezervasyon Bilgileri</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Giriş Tarihi</div>
                            <div class="info-value text-success fw-semibold">
                                <i class="fas fa-sign-in-alt me-1 text-success opacity-75"></i>
                                {{ $reservation->check_in_date->format('d.m.Y') }}
                                @if($reservation->check_in_time)
                                <div class="text-muted small">{{ $reservation->check_in_time }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Çıkış Tarihi</div>
                            <div class="info-value text-danger fw-semibold">
                                <i class="fas fa-sign-out-alt me-1 text-danger opacity-75"></i>
                                {{ $reservation->check_out_date->format('d.m.Y') }}
                                @if($reservation->check_out_time)
                                <div class="text-muted small">{{ $reservation->check_out_time }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Konaklama Süresi</div>
                            <div class="info-value">
                                <span class="badge bg-primary fs-6">{{ $reservation->nightCount() }} Gece</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Voucher No</div>
                            <div class="info-value">{{ $reservation->voucher_no ?: '—' }}</div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Oda</div>
                            <div class="info-value fw-bold">
                                {{ $reservation->room->room_number ?? '—' }}
                                <div class="text-muted small fw-normal">{{ $reservation->roomType->name ?? '' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Misafir Sayısı</div>
                            <div class="info-value d-flex gap-1 flex-wrap">
                                <span class="badge bg-primary">{{ $reservation->adults }} Yetişkin</span>
                                @if($reservation->children > 0)
                                <span class="badge bg-warning text-dark">{{ $reservation->children }} Çocuk</span>
                                @endif
                                @if($reservation->babies > 0)
                                <span class="badge bg-danger">{{ $reservation->babies }} Bebek</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Uyruk</div>
                            <div class="info-value">{{ $reservation->nationality ?: '—' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="info-label">Oluşturan</div>
                            <div class="info-value">{{ $reservation->creator->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Misafirler --}}
            <div class="section-card card">
                <div class="section-header d-flex align-items-center gap-2">
                    <i class="fas fa-users"></i>
                    <span class="fw-bold">Misafirler</span>
                    <span class="badge primary-badge ms-auto">{{ $reservation->guests->count() }} Kişi</span>
                </div>
                <div class="card-body p-4">
                    @if($reservation->guests->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Misafir bilgisi eklenmemiş.</p>
                    @else
                    <div class="row g-3">
                        @foreach($reservation->guests as $guest)
                        <div class="col-md-6">
                            <div class="guest-card p-3">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <div class="fw-bold">{{ $guest->fullName() }}</div>
                                        @if($guest->nationality)
                                        <small class="text-muted">{{ $guest->nationality }}</small>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1">
                                        @if($guest->is_primary)
                                        <span class="badge bg-warning text-dark" style="font-size:.68rem">Ana Misafir</span>
                                        @endif
                                        @if($guest->gender)
                                        <span class="badge bg-secondary" style="font-size:.68rem">
                                            {{ \App\Models\ReservationGuest::GENDERS[$guest->gender] ?? $guest->gender }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-2 text-muted" style="font-size:.8rem">
                                    @if($guest->birth_date)
                                    <div class="col-6">
                                        <i class="fas fa-birthday-cake me-1 opacity-50"></i>
                                        {{ $guest->birth_date->format('d.m.Y') }}
                                    </div>
                                    @endif
                                    @if($guest->id_no)
                                    <div class="col-6">
                                        <i class="fas fa-id-card me-1 opacity-50"></i>
                                        {{ $guest->id_no }}
                                    </div>
                                    @endif
                                    @if($guest->passport_no)
                                    <div class="col-6">
                                        <i class="fas fa-passport me-1 opacity-50"></i>
                                        {{ $guest->passport_no }}
                                    </div>
                                    @endif
                                    @if($guest->phone)
                                    <div class="col-6">
                                        <i class="fas fa-phone me-1 opacity-50"></i>
                                        {{ $guest->phone }}
                                    </div>
                                    @endif
                                    @if($guest->email)
                                    <div class="col-12">
                                        <i class="fas fa-envelope me-1 opacity-50"></i>
                                        {{ $guest->email }}
                                    </div>
                                    @endif
                                    @if($guest->vehicle_plate)
                                    <div class="col-6">
                                        <i class="fas fa-car me-1 opacity-50"></i>
                                        {{ $guest->vehicle_plate }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /col-lg-8 --}}

        {{-- ── Sağ: Acente & Kontrat ── --}}
        <div class="col-lg-4">

            {{-- Acente Bilgisi --}}
            <div class="section-card card mb-4">
                <div class="section-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                    <i class="fas fa-building"></i>
                    <span class="fw-bold">Acente</span>
                </div>
                <div class="card-body p-4">
                    @if($reservation->agency)
                    <div class="mb-3">
                        <div class="info-label mb-1">Acente Adı</div>
                        <div class="fw-bold fs-6">{{ $reservation->agency->name }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label mb-1">Acente Kodu</div>
                        <span class="badge bg-dark">{{ $reservation->agency->agency_code }}</span>
                    </div>
                    @if($reservation->agency->billing_address)
                    <div>
                        <div class="info-label mb-1">Fatura Adresi</div>
                        <div class="small text-muted">{{ $reservation->agency->billing_address }}</div>
                    </div>
                    @endif
                    @else
                    <p class="text-muted mb-0">Acente bilgisi yok.</p>
                    @endif
                </div>
            </div>

            {{-- Kontrat Bilgisi --}}
            <div class="section-card card mb-4">
                <div class="section-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#10b981,#059669)">
                    <i class="fas fa-file-contract"></i>
                    <span class="fw-bold">Kontrat</span>
                </div>
                <div class="card-body p-4">
                    @if($reservation->contract)
                    @php $c = $reservation->contract; @endphp
                    <div class="mb-3">
                        <div class="info-label mb-1">Kontrat Kodu</div>
                        <span class="badge bg-success">{{ $c->contract_code }}</span>
                    </div>
                    <div class="mb-3">
                        <div class="info-label mb-1">Oda Tipi</div>
                        <div class="info-value">{{ $c->roomType->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="info-label mb-1">Kontrat Dönemi</div>
                        <div class="small">
                            <span class="text-success fw-semibold">{{ $c->start_date->format('d.m.Y') }}</span>
                            <span class="text-muted mx-1">→</span>
                            <span class="text-danger fw-semibold">{{ $c->end_date->format('d.m.Y') }}</span>
                        </div>
                    </div>
                    @if($c->price_single || $c->price_double)
                    <hr class="my-3">
                    <div class="info-label mb-2">Fiyatlar</div>
                    <div class="d-flex flex-wrap gap-2" style="font-size:.78rem">
                        @if($c->price_single) <span class="badge bg-light text-dark border">1Y: {{ number_format($c->price_single,2) }}</span> @endif
                        @if($c->price_double) <span class="badge bg-light text-dark border">2Y: {{ number_format($c->price_double,2) }}</span> @endif
                        @if($c->price_triple) <span class="badge bg-light text-dark border">3Y: {{ number_format($c->price_triple,2) }}</span> @endif
                        @if($c->price_quad)   <span class="badge bg-light text-dark border">4Y: {{ number_format($c->price_quad,2) }}</span>   @endif
                        @if($c->price_child1) <span class="badge bg-warning text-dark border">Ç1: {{ number_format($c->price_child1,2) }}</span> @endif
                        @if($c->price_child2) <span class="badge bg-warning text-dark border">Ç2: {{ number_format($c->price_child2,2) }}</span> @endif
                        @if($c->price_baby1)  <span class="badge bg-danger bg-opacity-75 border">B1: {{ number_format($c->price_baby1,2) }}</span> @endif
                        @if($c->price_baby2)  <span class="badge bg-danger bg-opacity-75 border">B2: {{ number_format($c->price_baby2,2) }}</span> @endif
                    </div>
                    @endif
                    @else
                    <p class="text-muted mb-0 small">Kontrat bilgisi bağlı değil.</p>
                    @endif
                </div>
            </div>

            {{-- Meta --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Oluşturulma: {{ $reservation->created_at->format('d.m.Y H:i') }}
                        </small>
                        <small class="text-muted">{{ $reservation->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}
    </div>{{-- /row --}}
</div>
@endsection
