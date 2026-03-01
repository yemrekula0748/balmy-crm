@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-id-badge me-2 text-primary"></i>Ziyaretçi Detayı</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('guest-logs.index') }}">Ziyaretçi Kayıtları</a></li>
                <li class="breadcrumb-item active">Detay</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $guestLog->visitor_name }}</h5>
                    <div class="d-flex gap-2">
                        @if($guestLog->isInside())
                        <form action="{{ route('guest-logs.checkout', $guestLog) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" onclick="return confirm('Çıkış kaydedilsin mi?')">
                                <i class="fas fa-sign-out-alt me-1"></i> Çıkış Yap
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('guest-logs.edit', $guestLog) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-edit me-1"></i> Düzenle
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Durum Banner --}}
                    @if($guestLog->isInside())
                    <div class="alert alert-warning py-2 mb-4">
                        <i class="fas fa-door-open me-2"></i> Ziyaretçi şu an içeride.
                        Giriş: <strong>{{ $guestLog->check_in_at->format('d.m.Y H:i') }}</strong>
                    </div>
                    @else
                    <div class="alert alert-success py-2 mb-4">
                        <i class="fas fa-check-circle me-2"></i> Ziyaret tamamlandı.
                        Süre: <strong>
                            @php $dur = $guestLog->durationMinutes() @endphp
                            {{ intdiv($dur,60) > 0 ? intdiv($dur,60).' sa ' : '' }}{{ $dur%60 }} dk
                        </strong>
                    </div>
                    @endif

                    <div class="row g-3">
                        {{-- Ziyaretçi --}}
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Ad Soyad</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->visitor_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Telefon</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->visitor_phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">TC / Pasaport</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->visitor_id_no ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Kurum / Şirket</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->visitor_company ?? '—' }}</p>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Ziyaret --}}
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Kime Geldi</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->host?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Departman</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->department?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Şube</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->branch?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Amaç</p>
                            <span class="badge bg-{{ \App\Models\GuestLog::PURPOSE_COLORS[$guestLog->purpose] }}">
                                {{ \App\Models\GuestLog::PURPOSES[$guestLog->purpose] }}
                            </span>
                            @if($guestLog->purpose_note)
                                <span class="text-muted small ms-1">{{ $guestLog->purpose_note }}</span>
                            @endif
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Zaman --}}
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Giriş</p>
                            <p class="fw-semibold mb-0">{{ $guestLog->check_in_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Çıkış</p>
                            <p class="fw-semibold mb-0">
                                {{ $guestLog->check_out_at?->format('d.m.Y H:i') ?? '—' }}
                            </p>
                        </div>

                        @if($guestLog->notes)
                        <div class="col-12">
                            <p class="text-muted small mb-1">Notlar</p>
                            <p class="mb-0">{{ $guestLog->notes }}</p>
                        </div>
                        @endif

                        @if($guestLog->createdBy)
                        <div class="col-12">
                            <small class="text-muted">Kaydeden: {{ $guestLog->createdBy->name }} — {{ $guestLog->created_at->format('d.m.Y H:i') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('guest-logs.index') }}" class="btn btn-outline-secondary btn-sm">← Listeye Dön</a>
                    <form action="{{ route('guest-logs.destroy', $guestLog) }}" method="POST"
                          onsubmit="return confirm('Bu kayıt kalıcı olarak silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i> Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
