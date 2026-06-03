@extends('layouts.default')
@section('title', 'Guzergah Tanimlari')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Guzergah Tanimlari</h4>
                <span>Servisler icin ortak guzergah listesi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="#">Servis Takip</a></li>
                <li class="breadcrumb-item active">Guzergahlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:12px">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('shuttle.routes.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label mb-1">Ara</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Guzergah adi...">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                <i class="fas fa-search me-1"></i> Filtrele
                            </button>
                            @if(request('search'))
                                <a href="{{ route('shuttle.routes.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                                    <i class="fas fa-times me-1"></i> Temizle
                                </a>
                            @endif
                        </div>
                        <div class="col-auto ms-auto">
                            @if((auth()->user()->isSuperAdmin() || auth()->user()->isHumanResources()) && auth()->user()->hasPermission('shuttle_routes', 'create'))
                                <a href="{{ route('shuttle.routes.create') }}" class="btn btn-sm" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                    <i class="fas fa-plus me-1"></i> Yeni Guzergah
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert border-0 mb-0" style="background:#fbf6ef;color:#7a5c3d;border-radius:12px">
                <i class="fas fa-info-circle me-1"></i>
                Guzergahlar Beach / Foresta ayrimi olmadan ortak kullanilir. Hangi aracin hangi guzergahlarda calisacagi arac kartindan secilir.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-route me-2" style="color:#c19b77"></i> Guzergah Listesi
                        <span class="badge bg-secondary ms-2">{{ $routes->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($routes->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-route fa-3x mb-3"></i>
                            <p>Henuz guzergah tanimlanmamis.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Guzergah Adi</th>
                                        <th>Aciklama</th>
                                        <th>Durum</th>
                                        <th>Eklenme</th>
                                        <th class="text-end">Islem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routes as $route)
                                        <tr>
                                            <td class="text-muted small">{{ $route->id }}</td>
                                            <td><strong>{{ $route->name }}</strong></td>
                                            <td class="text-muted small">{{ $route->description ?: '-' }}</td>
                                            <td>
                                                @if($route->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Pasif</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $route->created_at->format('d.m.Y') }}</td>
                                            <td class="text-end">
                                                @if((auth()->user()->isSuperAdmin() || auth()->user()->isHumanResources()) && auth()->user()->hasPermission('shuttle_routes', 'edit'))
                                                    <a href="{{ route('shuttle.routes.edit', $route) }}"
                                                       class="btn btn-sm"
                                                       style="background:#fbf6ef;color:#8f6d4f;border:1px solid #eadcc9">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if((auth()->user()->isSuperAdmin() || auth()->user()->isHumanResources()) && auth()->user()->hasPermission('shuttle_routes', 'delete'))
                                                    <form action="{{ route('shuttle.routes.destroy', $route) }}" method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Bu guzergahi silmek istiyor musunuz?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm" style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $routes->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
