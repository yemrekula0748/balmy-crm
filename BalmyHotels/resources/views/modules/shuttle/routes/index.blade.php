@extends('layouts.default')
@section('title', 'Güzergah Tanımları')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Güzergah Tanımları</h4>
                <span>Servis güzergahları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="#">Servis Takip</a></li>
                <li class="breadcrumb-item active">Güzergahlar</li>
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
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('shuttle.routes.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Şube</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">— Tüm Şubeler —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1">Ara</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Güzergah adı...">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i> Filtrele
                            </button>
                            <a href="{{ route('shuttle.routes.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                                <i class="fas fa-times me-1"></i> Temizle
                            </a>
                        </div>
                        <div class="col-auto ms-auto">
                            @if(auth()->user()->hasPermission('shuttle_routes', 'create'))
                            <a href="{{ route('shuttle.routes.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Yeni Güzergah
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-route me-2"></i> Güzergah Listesi
                        <span class="badge bg-secondary ms-2">{{ $routes->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($routes->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-route fa-3x mb-3"></i>
                            <p>Henüz güzergah tanımlanmamış.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Güzergah Adı</th>
                                        <th>Açıklama</th>
                                        <th>Şube</th>
                                        <th>Durum</th>
                                        <th>Eklenme</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routes as $route)
                                    <tr>
                                        <td class="text-muted small">{{ $route->id }}</td>
                                        <td><strong>{{ $route->name }}</strong></td>
                                        <td class="text-muted small">{{ $route->description ?: '—' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $route->branch->name }}</span>
                                        </td>
                                        <td>
                                            @if($route->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $route->created_at->format('d.m.Y') }}</td>
                                        <td class="text-end">
                                            @if(auth()->user()->hasPermission('shuttle_routes', 'edit'))
                                            <a href="{{ route('shuttle.routes.edit', $route) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('shuttle_routes', 'delete'))
                                            <form action="{{ route('shuttle.routes.destroy', $route) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bu güzergahı silmek istiyor musunuz?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
