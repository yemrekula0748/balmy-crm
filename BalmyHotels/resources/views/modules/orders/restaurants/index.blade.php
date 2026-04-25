@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Restoranlar</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.take') }}">Sipariş</a></li>
                <li class="breadcrumb-item active">Restoranlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Tüm Restoranlar <span class="badge bg-secondary ms-1">{{ $restaurants->count() }}</span></h5>
        @if(auth()->user()->hasPermission('restaurant_settings','create'))
        <a href="{{ route('orders.restaurants.create') }}" class="btn btn-sm text-white" style="background:#c19b77">
            <i class="fa fa-plus me-1"></i> Yeni Restoran
        </a>
        @endif
    </div>

    @forelse($restaurants as $restaurant)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="rounded d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                         style="width:52px;height:52px;background:#c19b77">
                        {{ strtoupper(substr($restaurant->name,0,1)) }}
                    </div>
                </div>
                <div class="col">
                    <div class="fw-semibold fs-6">{{ $restaurant->name }}</div>
                    <div class="text-muted small">
                        @if($restaurant->branch)
                            <i class="fa fa-building me-1"></i>{{ $restaurant->branch->name }}
                        @endif
                        @if($restaurant->qrMenu)
                            <span class="ms-2"><i class="fa fa-qrcode me-1"></i>{{ $restaurant->qrMenu->name }}</span>
                        @else
                            <span class="ms-2 text-warning"><i class="fa fa-exclamation-triangle me-1"></i>Menü atanmamış</span>
                        @endif
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">{{ $restaurant->tables->count() }} masa</span>
                    @if(auth()->user()->hasPermission('restaurant_settings','show'))
                    <a href="{{ route('orders.restaurants.show', $restaurant) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-table me-1"></i> Masalar
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('restaurant_settings','edit'))
                    <a href="{{ route('orders.restaurants.edit', $restaurant) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-pen"></i>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('restaurant_settings','delete'))
                    <form method="POST" action="{{ route('orders.restaurants.destroy', $restaurant) }}"
                          onsubmit="return confirm('Restoranı silmek istediğinize emin misiniz?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="fa fa-store fa-3x mb-3 d-block opacity-25"></i>
        Henüz restoran tanımlanmamış.
        @if(auth()->user()->hasPermission('restaurant_settings','create'))
        <div class="mt-3"><a href="{{ route('orders.restaurants.create') }}" class="btn btn-sm text-white" style="background:#c19b77">İlk Restoranı Oluştur</a></div>
        @endif
    </div></div>
    @endforelse
</div>
@endsection
