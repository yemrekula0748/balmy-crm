@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sipariş Al</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Sipariş</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    @if($restaurants->isEmpty())
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fa fa-store fa-3x mb-3 d-block opacity-25"></i>
            Henüz restoran tanımlanmamış.
            @if(auth()->user()->hasPermission('restaurant_settings','create'))
            <div class="mt-3"><a href="{{ route('orders.restaurants.create') }}" class="btn btn-sm text-white" style="background:#c19b77">Restoran Oluştur</a></div>
            @endif
        </div></div>
    @else

    {{-- Restoran Seçimi --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body pb-0">
            <div class="d-flex flex-wrap gap-2 pb-3">
                @foreach($restaurants as $r)
                <a href="{{ route('orders.take', ['restaurant_id' => $r->id]) }}"
                   class="btn btn-sm @if($selectedRestaurant?->id === $r->id) text-white @else btn-outline-secondary @endif"
                   style="{{ $selectedRestaurant?->id === $r->id ? 'background:#c19b77;border-color:#c19b77' : '' }}">
                    {{ $r->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($selectedRestaurant)
    {{-- Başlık bandı --}}
    <div class="card border-0 mb-4" style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%);border-radius:12px">
        <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:46px;height:46px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                         fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2h18M3 22h18M8 6h8M8 10h8M8 14h4"/>
                        <rect x="3" y="2" width="18" height="20" rx="2"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white fw-semibold fs-5">{{ $selectedRestaurant->name }}</div>
                    <div style="color:rgba(255,255,255,.5);font-size:.82rem">
                        {{ $tables->count() }} masa
                        @if($selectedRestaurant->qrMenu)
                            &nbsp;·&nbsp; Menü: {{ $selectedRestaurant->qrMenu->name }}
                        @else
                            &nbsp;·&nbsp; <span class="text-warning">Menü atanmamış</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-success px-3 py-2">
                    {{ $tables->where('activeSession', '!=', null)->count() }} Açık
                </span>
                <span class="badge bg-secondary px-3 py-2">
                    {{ $tables->where('activeSession', null)->count() }} Kapalı
                </span>
            </div>
        </div>
    </div>

    {{-- Masa Grid --}}
    @if($tables->isEmpty())
        <div class="alert alert-warning">
            Bu restorana henüz masa eklenmemiş.
            @if(auth()->user()->hasPermission('restaurant_settings','create'))
            <a href="{{ route('orders.restaurants.show', $selectedRestaurant) }}" class="alert-link">Masa ekle</a>
            @endif
        </div>
    @else
    <div class="row g-3">
        @foreach($tables as $table)
        @php $session = $table->activeSession; $isOpen = !is_null($session); @endphp
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0"
                 style="border-top:4px solid {{ $isOpen ? '#28a745' : '#6c757d' }} !important;border-radius:10px">
                <div class="card-body p-3 d-flex flex-column">
                    {{-- Masa adı + durum --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="fw-bold fs-6">{{ $table->name }}</span>
                        @if($isOpen)
                            <span class="badge bg-success">Açık</span>
                        @else
                            <span class="badge bg-secondary">Kapalı</span>
                        @endif
                    </div>

                    {{-- Açık masa: bilgi --}}
                    @if($isOpen)
                    <div class="text-muted small mb-3 flex-grow-1">
                        <div><i class="fa fa-user me-1"></i>{{ $session->opener?->name ?? '—' }}</div>
                        <div><i class="fa fa-clock me-1"></i>{{ $session->opened_at->format('H:i') }}</div>
                        <div class="text-success fw-semibold mt-1">
                            <i class="fa fa-hourglass-half me-1"></i>{{ $session->durationFormatted() }}
                        </div>
                    </div>
                    <a href="{{ route('orders.session', $session) }}"
                       class="btn btn-sm btn-success w-100">
                        <i class="fa fa-eye me-1"></i> Görüntüle / Sipariş Ekle
                    </a>
                    @else
                    <div class="flex-grow-1"></div>
                    <form method="POST" action="{{ route('orders.open-table', $table) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                            <i class="fa fa-lock-open me-1"></i> Masayı Aç
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif
    @endif
</div>
@endsection
