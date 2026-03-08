@extends('layouts.default')
@section('title', 'Yemek Kütüphanesi — Ürünler')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yemek Kütüphanesi — Ürünler</h4>
                <span>Tüm şube ürünleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('food-library.index') }}">Yemek Kütüphanesi</a></li>
                <li class="breadcrumb-item active">Ürünler</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header Bant --}}
    <div class="card border-0 mb-4" style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%);border-radius:12px">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-white fw-semibold fs-5">Ürün Listesi</div>
                        <div style="color:rgba(255,255,255,0.5);font-size:.82rem">{{ $products->total() }} ürün bulundu</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if(auth()->user()->hasPermission('food_library', 'create'))
                    <a href="{{ route('food-library.product.create') }}"
                       class="btn btn-sm fw-semibold px-3" style="background:#fff;color:#1e2d3d;border-radius:7px">
                        <i class="fas fa-plus me-1"></i> Yeni Ürün
                    </a>
                    @endif
                </div>
            </div>

            {{-- Filtreler --}}
            <form method="GET" class="mt-3 d-flex align-items-center gap-2 flex-wrap">
                <select name="branch_id" class="form-select form-select-sm"
                        style="min-width:150px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                        onchange="this.form.submit()">
                    <option value="" style="color:#333;background:#fff">— Tüm Şubeler —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id) style="color:#333;background:#fff">{{ $b->name }}</option>
                    @endforeach
                </select>
                <select name="category_id" class="form-select form-select-sm"
                        style="min-width:160px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                        onchange="this.form.submit()">
                    <option value="" style="color:#333;background:#fff">— Tüm Kategoriler —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id) style="color:#333;background:#fff">
                            {{ $cat->icon }} {{ $cat->getTitle('tr') }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group input-group-sm" style="max-width:220px">
                    <input type="text" name="search" class="form-control"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                           placeholder="Ürün ara..." value="{{ request('search') }}">
                    <button class="btn" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);color:#fff" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ürün Kartları --}}
    @if($products->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
            <p class="mb-0">Ürün bulunamadı.</p>
            @if(auth()->user()->hasPermission('food_library', 'create'))
            <a href="{{ route('food-library.product.create') }}" class="btn btn-sm btn-primary mt-3">
                <i class="fas fa-plus me-1"></i> Ürün Ekle
            </a>
            @endif
        </div>
    @else
        <div class="row g-3">
            @foreach($products as $product)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                    {{-- Ürün Resmi --}}
                    @if($product->image && file_exists(storage_path('app/public/'.$product->image)))
                        <div style="height:160px;overflow:hidden;background:#f4f6f9">
                            <img src="{{ asset('uploads/'.$product->image) }}" alt="{{ $product->getTitle('tr') }}"
                                 style="width:100%;height:100%;object-fit:cover">
                        </div>
                    @else
                        <div style="height:100px;background:linear-gradient(135deg,#f0f3f8,#e4e9f2);display:flex;align-items:center;justify-content:center">
                            <span style="font-size:2.5rem">{{ $product->foodCategory?->icon ?? '🍽️' }}</span>
                        </div>
                    @endif

                    <div class="card-body p-3">
                        {{-- Kategori badge --}}
                        <div class="mb-1">
                            <span style="font-size:.7rem;background:#eef3fb;color:#2a5298;font-weight:600;padding:2px 8px;border-radius:10px">
                                {{ $product->foodCategory?->getTitle('tr') ?? '—' }}
                            </span>
                        </div>

                        <div class="fw-semibold text-dark mb-1" style="font-size:.9rem;line-height:1.3">
                            {{ $product->getTitle('tr') }}
                        </div>

                        <div class="fw-bold mb-2" style="color:#1e2d3d;font-size:.95rem">
                            {{ number_format($product->price, 2, ',', '.') }} ₺
                        </div>

                        {{-- Badges --}}
                        @if(!empty($product->badges))
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach($product->badges as $badge)
                            <span style="font-size:.65rem;padding:2px 7px;border-radius:20px;font-weight:600;
                                background:{{ \App\Models\FoodProduct::BADGE_COLORS[$badge]['bg'] ?? '#f0f0f0' }};
                                color:{{ \App\Models\FoodProduct::BADGE_COLORS[$badge]['text'] ?? '#333' }}">
                                {{ $badge }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Opsiyonlar --}}
                        @if(!empty($product->options))
                        <div class="text-muted" style="font-size:.72rem">
                            <i class="fas fa-sliders-h me-1"></i>{{ count($product->options) }} opsiyon
                        </div>
                        @endif
                    </div>

                    <div class="card-footer border-0 bg-transparent px-3 pb-3 pt-0 d-flex gap-2">
                        @if(auth()->user()->hasPermission('food_library', 'edit'))
                        <a href="{{ route('food-library.product.edit', $product) }}"
                           class="btn btn-sm flex-fill" style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                            <i class="fas fa-edit me-1"></i> Düzenle
                        </a>
                        @endif
                        @if(auth()->user()->hasPermission('food_library', 'delete'))
                        <form action="{{ route('food-library.product.destroy', $product) }}" method="POST"
                              onsubmit="return confirm('Bu ürünü silmek istiyor musunuz?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm" style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0;font-size:.78rem">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif

</div>
@endsection
