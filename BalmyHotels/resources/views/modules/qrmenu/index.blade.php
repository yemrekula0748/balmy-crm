@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>QR Menü Yönetimi</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">QR Menüler</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    {{-- İSTATİSTİK --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 h-100" style="border-color:#c19b77!important">
                <div class="card-body py-3">
                    <div class="text-muted small">Toplam Menü</div>
                    <div class="fw-bold fs-3">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-success h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Aktif</div>
                    <div class="fw-bold fs-3 text-success">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-secondary h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Pasif</div>
                    <div class="fw-bold fs-3 text-secondary">{{ $stats['inactive'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-info h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Toplam Ürün</div>
                    <div class="fw-bold fs-3 text-info">{{ $stats['total_items'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Tüm QR Menüler</h5>
        <a href="{{ route('qrmenus.create') }}" class="btn btn-sm text-white" style="background:#c19b77">
            <i class="fa fa-plus me-1"></i> Yeni Menü Oluştur
        </a>
    </div>

    @forelse($menus as $menu)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center g-3">
                {{-- Logo --}}
                <div class="col-auto">
                    @if($menu->logo)
                        <img src="{{ asset('uploads/'.$menu->logo) }}" alt="logo" class="rounded" style="width:56px;height:56px;object-fit:cover">
                    @else
                        <div class="rounded d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                             style="width:56px;height:56px;background:#c19b77">
                            {{ strtoupper(substr($menu->name,0,1)) }}
                        </div>
                    @endif
                </div>
                {{-- Bilgi --}}
                <div class="col">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h6 class="mb-0">{{ $menu->getTitle() }}</h6>
                        @if($menu->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Pasif</span>
                        @endif
                        @foreach($menu->languages as $lang)
                            <span class="badge" style="background:#f0e6d9;color:#8b6a4f">{{ $lang->flag }} {{ $lang->code }}</span>
                        @endforeach
                    </div>
                    <div class="text-muted small mt-1">
                        <span class="me-3"><i class="fa fa-th-large me-1"></i>{{ $menu->categories_count ?? 0 }} Kategori</span>
                        <span class="me-3"><i class="fa fa-list me-1"></i>{{ $menu->items_count ?? 0 }} Ürün</span>
                        <span><i class="fa fa-globe me-1"></i>/menu/{{ $menu->name }}</span>
                    </div>
                </div>
                {{-- Aksiyonlar --}}
                <div class="col-auto d-flex gap-2 flex-wrap">
                    <a href="{{ route('qrmenu.show', $menu->name) }}" target="_blank"
                       class="btn btn-sm btn-outline-secondary" title="Müşteri görünümü">
                        <i class="fa fa-qrcode"></i>
                    </a>
                    <a href="{{ route('qrmenus.show', $menu) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-eye"></i> Yönet
                    </a>
                    <a href="{{ route('qrmenus.edit', $menu) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('qrmenus.toggle', $menu) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $menu->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                title="{{ $menu->is_active ? 'Pasife al' : 'Aktife al' }}">
                            <i class="fa fa-{{ $menu->is_active ? 'pause' : 'play' }}"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('qrmenus.destroy', $menu) }}" class="d-inline"
                          onsubmit="return confirm('Bu menüyü silmek istediğinize emin misiniz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-5">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                 fill="none" stroke="#c19b77" stroke-width="1.5" class="mb-3">
                <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="3" height="3"></rect>
            </svg>
            <p class="text-muted mb-3">Henüz QR menü oluşturmadınız.</p>
            <a href="{{ route('qrmenus.create') }}" class="btn text-white" style="background:#c19b77">İlk Menüyü Oluştur</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
