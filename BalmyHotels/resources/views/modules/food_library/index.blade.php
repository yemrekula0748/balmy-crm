@extends('layouts.default')
@section('title', 'Yemek Kütüphanesi — Kategoriler')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yemek Kütüphanesi</h4>
                <span>Şube bazlı ürün ve kategori havuzu</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Yemek Kütüphanesi</li>
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
        <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white fw-semibold fs-5">Kategoriler</div>
                    <div style="color:rgba(255,255,255,0.5);font-size:.82rem">{{ $categories->count() }} kategori tanımlı</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <select name="branch_id" class="form-select form-select-sm"
                            style="min-width:160px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                            onchange="this.form.submit()">
                        <option value="" style="color:#333;background:#fff">— Tüm Şubeler —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected($branchId == $b->id) style="color:#333;background:#fff">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('food-library.products') }}{{ $branchId ? '?branch_id='.$branchId : '' }}"
                   class="btn btn-sm" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff">
                    <i class="fas fa-box me-1"></i> Ürünler
                </a>
                @if(auth()->user()->hasPermission('food_library', 'create'))
                <a href="{{ route('food-library.categories.create') }}"
                   class="btn btn-sm fw-semibold px-3" style="background:#fff;color:#1e2d3d;border-radius:7px">
                    <i class="fas fa-plus me-1"></i> Kategori Ekle
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Kategori Tablosu --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
        <div class="card-header border-0 px-4 py-3" style="background:#1e2d3d">
            <span class="text-white fw-semibold">Kategoriler</span>
        </div>
        <div class="card-body p-0">
            @if($categories->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-layer-group fa-2x mb-3 opacity-25"></i>
                    <p class="mb-0">Henüz kategori tanımlanmamış.</p>
                    @if(auth()->user()->hasPermission('food_library', 'create'))
                    <a href="{{ route('food-library.categories.create') }}" class="btn btn-sm btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i> İlk Kategoriyi Ekle
                    </a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">İkon</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Kategori Adı</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Şube</th>
                                <th class="py-3 fw-semibold border-0 text-center" style="background:#1e2d3d;color:#fff">Ürün Sayısı</th>
                                <th class="py-3 fw-semibold border-0 text-center" style="background:#1e2d3d;color:#fff">Durum</th>
                                <th class="pe-4 py-3 fw-semibold border-0 text-end" style="background:#1e2d3d;color:#fff">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                            <tr style="border-bottom:1px solid #f0f2f5">
                                <td class="ps-4" style="font-size:1.3rem">{{ $cat->icon ?: '—' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $cat->getTitle('tr') }}</div>
                                    @if(($cat->title['en'] ?? '') !== '')
                                        <small class="text-muted">{{ $cat->title['en'] }}</small>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $cat->branch->name ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('food-library.products') }}?category_id={{ $cat->id }}"
                                       style="background:#eef3f9;color:#2a5298;font-weight:700;padding:2px 12px;border-radius:6px;text-decoration:none;font-size:.85rem">
                                        {{ $cat->products->count() }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if($cat->is_active)
                                        <span style="background:#eef6f0;color:#2e7d52;font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px">Aktif</span>
                                    @else
                                        <span style="background:#f2f2f2;color:#888;font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px">Pasif</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if(auth()->user()->hasPermission('food_library', 'edit'))
                                        <a href="{{ route('food-library.categories.edit', $cat) }}"
                                           class="btn btn-sm" style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('food_library', 'delete'))
                                        <form action="{{ route('food-library.categories.destroy', $cat) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ $cat->getTitle('tr') }} kategorisini silmek istiyor musunuz?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm" style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0;font-size:.78rem">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
