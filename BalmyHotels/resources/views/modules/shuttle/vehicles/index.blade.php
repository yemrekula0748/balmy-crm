@extends('layouts.default')
@section('title', 'Servis Araçları')

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Araçları</h4>
                <span>Servis takip araç tanımları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item">Servis Takip</li>
                <li class="breadcrumb-item active">Araçlar</li>
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%);border-radius:12px">
                <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13" rx="2"/>
                                <path d="M16 8h4l3 5v3h-7V8z"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-white fw-semibold fs-5">Araç Envanteri</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:.82rem">Toplam {{ $vehicles->total() }} araç kayıtlı</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="GET" action="{{ route('shuttle.vehicles.index') }}"
                              class="d-flex align-items-center gap-2 flex-wrap">
                            <select name="branch_id" class="form-select form-select-sm"
                                    style="min-width:180px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                    onchange="this.form.submit()">
                                <option value="" style="color:#333;background:#fff">— Tüm Şubeler —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id) style="color:#333;background:#fff">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group input-group-sm" style="max-width:220px">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="form-control"
                                       style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                       placeholder="Ad veya plaka ara...">
                                <button type="submit" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @if(request('search') || request('branch_id'))
                            <a href="{{ route('shuttle.vehicles.index') }}"
                               class="btn btn-sm"
                               style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </form>
                        @if(auth()->user()->hasPermission('shuttle_vehicles', 'create'))
                        <a href="{{ route('shuttle.vehicles.create') }}"
                           class="btn btn-sm fw-semibold px-3"
                           style="background:#fff;color:#1e2d3d;border:none;border-radius:7px">
                            <i class="fas fa-plus me-1"></i> Yeni Araç
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Araç Kartları --}}
    @if($vehicles->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                     fill="none" stroke="#ced4da" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                     class="mb-3 d-block mx-auto">
                    <rect x="1" y="3" width="15" height="13" rx="2"/>
                    <path d="M16 8h4l3 5v3h-7V8z"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                <p class="mb-0">Kayıtlı araç bulunamadı.</p>
                @if(auth()->user()->hasPermission('shuttle_vehicles', 'create'))
                <a href="{{ route('shuttle.vehicles.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-1"></i> İlk Aracı Ekle
                </a>
                @endif
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($vehicles as $v)
            @php
                $typeStyle = match($v->type) {
                    'minibus' => ['accent' => '#3d6b9e', 'light' => '#eef3f9'],
                    'midibus' => ['accent' => '#3d7a5e', 'light' => '#eef6f2'],
                    'otobus'  => ['accent' => '#7a5c3d', 'light' => '#f6f0ea'],
                    default   => ['accent' => '#5a5a6e', 'light' => '#f0f0f4'],
                };
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card border-0 h-100"
                     style="border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.07);
                            border-top:3px solid {{ $typeStyle['accent'] }} !important;transition:box-shadow .2s,transform .15s"
                     onmouseover="this.style.boxShadow='0 6px 22px rgba(0,0,0,0.12)';this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.boxShadow='0 2px 10px rgba(0,0,0,0.07)';this.style.transform='translateY(0)'">
                    <div class="card-body p-4">

                        {{-- Araç adı + durum --}}
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <div class="fw-bold text-dark" style="font-size:.95rem;line-height:1.4;max-width:75%">
                                {{ $v->name }}
                            </div>
                            @if($v->is_active)
                                <span style="background:#eef6f0;color:#2e7d52;font-size:.7rem;font-weight:600;
                                             padding:3px 9px;border-radius:20px;white-space:nowrap">Aktif</span>
                            @else
                                <span style="background:#f2f2f2;color:#888;font-size:.7rem;font-weight:600;
                                             padding:3px 9px;border-radius:20px;white-space:nowrap">Pasif</span>
                            @endif
                        </div>

                        {{-- Şube --}}
                        <div class="text-muted mb-3" style="font-size:.8rem">{{ $v->branch->name }}</div>

                        {{-- Plaka --}}
                        @if($v->plate)
                        <div class="mb-3">
                            <span style="display:inline-block;background:#1e2d3d;color:#fff;
                                         font-family:'Courier New',monospace;font-size:.82rem;font-weight:700;
                                         padding:4px 12px;border-radius:6px;letter-spacing:2px">
                                {{ $v->plate }}
                            </span>
                        </div>
                        @else
                        <div class="mb-3">
                            <span style="font-size:.8rem;color:#bbb">Plaka girilmemiş</span>
                        </div>
                        @endif

                        <hr style="border-color:#f0f0f0;margin:0 0 12px">

                        {{-- Tür + Kapasite --}}
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="background:{{ $typeStyle['light'] }};color:{{ $typeStyle['accent'] }};
                                         border:1px solid {{ $typeStyle['accent'] }}30;
                                         font-size:.75rem;font-weight:600;padding:3px 11px;border-radius:20px">
                                {{ $v->type_name }}
                            </span>
                            <div style="font-size:.82rem;color:#555;display:flex;align-items:center;gap:5px">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                     fill="none" stroke="#888" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <strong style="color:#1e2d3d">{{ $v->capacity }}</strong>&nbsp;kişi
                            </div>
                        </div>

                        {{-- İşlem Butonları --}}
                        @if(auth()->user()->hasPermission('shuttle_vehicles','edit') || auth()->user()->hasPermission('shuttle_vehicles','delete'))
                        <div class="d-flex gap-2 mt-3">
                            @if(auth()->user()->hasPermission('shuttle_vehicles','edit'))
                            <a href="{{ route('shuttle.vehicles.edit', $v) }}"
                               class="btn btn-sm flex-fill"
                               style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.8rem;font-weight:500">
                                <i class="fas fa-edit me-1"></i> Düzenle
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('shuttle_vehicles','delete'))
                            <form action="{{ route('shuttle.vehicles.destroy', $v) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ $v->name }} aracını silmek istiyor musunuz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm"
                                        style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0;font-size:.8rem">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $vehicles->withQueryString()->links() }}
        </div>
    @endif

</div>
@endsection