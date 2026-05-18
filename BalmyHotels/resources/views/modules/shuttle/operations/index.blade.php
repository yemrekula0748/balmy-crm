@extends('layouts.default')
@section('title', 'Servis Operasyonu')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Operasyonu</h4>
                <span>{{ $date->format('d.m.Y') }} - Gunluk sefer takibi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item">Servis Takip</li>
                <li class="breadcrumb-item active">Operasyon</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            <div class="text-white fw-semibold fs-5">Sefer Takibi</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:.82rem">{{ $date->format('d.m.Y') }} - Toplam {{ $totalTrips }} sefer</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="GET" action="{{ route('shuttle.operations.index') }}"
                              class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                            <select name="branch_id" class="form-select form-select-sm"
                                    style="min-width:160px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                    onchange="this.form.submit()">
                                <option value="" style="color:#333;background:#fff">- Tum Subeler -</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id) style="color:#333;background:#fff">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" onclick="changeDate(-1)" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;width:32px">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <input type="date" name="date" value="{{ $date->toDateString() }}"
                                       class="form-control form-control-sm text-center"
                                       style="width:135px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff"
                                       onchange="this.form.submit()">
                                <button type="button" onclick="changeDate(1)" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;width:32px">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <button type="button" onclick="setToday()" class="btn btn-sm"
                                        style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;font-size:.75rem;padding:4px 10px">
                                    Bugun
                                </button>
                            </div>
                        </form>
                        @if(auth()->user()->hasPermission('shuttle_operations', 'create'))
                            <button type="button" class="btn btn-sm fw-semibold px-3"
                                    style="background:#fff;color:#1e2d3d;border:none;border-radius:7px"
                                    data-bs-toggle="modal" data-bs-target="#addTripModal">
                                <i class="fas fa-plus me-1"></i> Sefer Ekle
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3d6b9e;border-radius:10px">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:44px;height:44px;background:#eef3f9;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="#3d6b9e" stroke-width="2.5" stroke-linecap="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e2d3d">{{ $totalArrival }}</div>
                        <div class="text-muted small mt-1">Gelen Personel</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3d7a5e;border-radius:10px">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:44px;height:44px;background:#eef6f2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="#3d7a5e" stroke-width="2.5" stroke-linecap="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e2d3d">{{ $totalDeparture }}</div>
                        <div class="text-muted small mt-1">Donen Personel</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #7a5c3d;border-radius:10px">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:44px;height:44px;background:#f6f0ea;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="#7a5c3d" stroke-width="2.5" stroke-linecap="round">
                            <rect x="1" y="3" width="15" height="13" rx="2"/>
                            <path d="M16 8h4l3 5v3h-7V8z"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e2d3d">{{ $totalTrips }}</div>
                        <div class="text-muted small mt-1">Toplam Sefer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
        <div class="card-header border-0 d-flex align-items-center justify-content-between px-4 py-3"
             style="background:linear-gradient(135deg,#1e2d3d 0%,#2c3e50 100%)">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round">
                    <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                </svg>
                <span class="text-white fw-semibold">{{ $date->format('d.m.Y') }} Seferleri</span>
            </div>
            <span style="background:rgba(255,255,255,0.12);color:#fff;font-size:.75rem;padding:3px 10px;border-radius:20px">
                {{ $totalTrips }} sefer
            </span>
        </div>
        <div class="card-body p-0">
            @if($trips->isEmpty())
                <div class="text-center py-5 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                         fill="none" stroke="#ced4da" stroke-width="1.5" class="mb-3 d-block mx-auto">
                        <rect x="1" y="3" width="15" height="13" rx="2"/>
                        <path d="M16 8h4l3 5v3h-7V8z"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <p class="mb-0 text-muted">Bu tarih icin sefer kaydi bulunmuyor.</p>
                    @if(auth()->user()->hasPermission('shuttle_operations', 'create'))
                        <button type="button" class="btn btn-sm btn-primary mt-3"
                                data-bs-toggle="modal" data-bs-target="#addTripModal">
                            <i class="fas fa-plus me-1"></i> Ilk Seferi Ekle
                        </button>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff;white-space:nowrap">Vardiya</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Arac</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Guzergah</th>
                                <th class="py-3 fw-semibold border-0 text-center" style="background:#1e2d3d;color:#fff">
                                    <span style="color:#93b8e8">↑</span> Gelis
                                </th>
                                <th class="py-3 fw-semibold border-0 text-center" style="background:#1e2d3d;color:#fff">
                                    <span style="color:#7ec8a0">↓</span> Donus
                                </th>
                                <th class="py-3 fw-semibold border-0 text-center" style="background:#1e2d3d;color:#fff">Doluluk</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Durum / Not</th>
                                <th class="py-3 fw-semibold border-0" style="background:#1e2d3d;color:#fff">Ekleyen</th>
                                <th class="pe-4 py-3 fw-semibold border-0 text-end" style="background:#1e2d3d;color:#fff">Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastShift = null; @endphp
                            @foreach($trips as $trip)
                                @if($lastShift !== $trip->shift)
                                    <tr>
                                        <td colspan="9" class="py-1 ps-4"
                                            style="background:#f5f7fa;border-top:2px solid #e2e8f0">
                                            <small class="fw-bold text-uppercase" style="color:#1e2d3d;letter-spacing:.5px;font-size:.7rem">
                                                <i class="fas fa-clock me-1 opacity-60"></i>{{ $trip->shift }}
                                            </small>
                                        </td>
                                    </tr>
                                    @php $lastShift = $trip->shift; @endphp
                                @endif
                                @php
                                    $cap = $trip->vehicle->capacity;
                                    $arrPct = ($cap > 0) ? round($trip->arrival_count / $cap * 100) : null;
                                    $depPct = ($cap > 0) ? round($trip->departure_count / $cap * 100) : null;
                                    $pickupMovements = $trip->branchMovements->where('movement_type', 'pickup');
                                    $dropoffMovements = $trip->branchMovements->where('movement_type', 'dropoff');
                                @endphp
                                <tr style="border-bottom:1px solid #f0f2f5">
                                    <td class="ps-4">
                                        <span style="background:#eef2f7;color:#2a4a6b;font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px;white-space:nowrap">
                                            {{ $trip->shift }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="line-height:1.3">{{ $trip->vehicle->name }}</div>
                                        @if($trip->vehicle->plate)
                                            <span style="background:#1e2d3d;color:#fff;font-family:'Courier New',monospace;font-size:.7rem;font-weight:700;padding:1px 7px;border-radius:4px;letter-spacing:1px">
                                                {{ $trip->vehicle->plate }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:.82rem">{{ $trip->route->name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($trip->arrival_time)
                                            <div class="text-muted" style="font-size:.72rem">{{ substr($trip->arrival_time, 0, 5) }}</div>
                                        @endif
                                        <span style="background:#eef3f9;color:#2a5298;font-weight:700;font-size:.9rem;padding:2px 10px;border-radius:6px">
                                            {{ $trip->arrival_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($trip->departure_time || $trip->departure_count > 0)
                                            @if($trip->departure_time)
                                                <div class="text-muted" style="font-size:.72rem">{{ substr($trip->departure_time, 0, 5) }}</div>
                                            @endif
                                            <span style="background:#eef6f2;color:#2e7d52;font-weight:700;font-size:.9rem;padding:2px 10px;border-radius:6px">
                                                {{ $trip->departure_count }}
                                            </span>
                                            @if(auth()->user()->hasPermission('shuttle_operations','edit'))
                                                <button type="button"
                                                        class="btn btn-sm d-block mx-auto mt-1"
                                                        style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.7rem;padding:1px 7px"
                                                        onclick="openDepartureModal({{ $trip->id }}, '{{ substr($trip->departure_time ?? '', 0, 5) }}', {{ $trip->departure_count }})">
                                                    <i class="fas fa-edit"></i> Guncelle
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted" style="font-size:.78rem">-</span>
                                            @if(auth()->user()->hasPermission('shuttle_operations','edit'))
                                                <button type="button"
                                                        class="btn btn-sm d-block mx-auto mt-1"
                                                        style="background:#eef6f2;color:#2e7d52;border:1px solid #c3e6cb;font-size:.72rem;padding:2px 9px;font-weight:600"
                                                        onclick="openDepartureModal({{ $trip->id }}, '', 0)">
                                                    <i class="fas fa-plus me-1"></i>Donus Ekle
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($arrPct !== null)
                                            <div style="font-size:.78rem">
                                                <span style="color:#2a5298">↑{{ $arrPct }}%</span>
                                                <span class="text-muted mx-1">/</span>
                                                <span style="color:#2e7d52">↓{{ $depPct }}%</span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem;max-width:250px">
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if($trip->arrived_with_different_vehicle)
                                                <span class="badge" style="background:#fff1f1;color:#b03a3a;border:1px solid #f0d0d0">Farkli aracla geldi</span>
                                            @endif
                                            @if($trip->is_transfer)
                                                <span class="badge" style="background:#fff7e7;color:#9b6a11;border:1px solid #f1ddb2">Aktarim yapildi</span>
                                            @endif
                                        </div>
                                        @if($pickupMovements->isNotEmpty())
                                            <div class="text-muted mb-1">
                                                <strong>Alinan:</strong>
                                                {{ $pickupMovements->map(fn ($movement) => ($movement->branch->name ?? '-') . ' ' . $movement->headcount)->implode(', ') }}
                                            </div>
                                        @endif
                                        @if($dropoffMovements->isNotEmpty())
                                            <div class="text-muted mb-1">
                                                <strong>Indirilen:</strong>
                                                {{ $dropoffMovements->map(fn ($movement) => ($movement->branch->name ?? '-') . ' ' . $movement->headcount)->implode(', ') }}
                                            </div>
                                        @endif
                                        <div class="text-muted">{{ $trip->notes ? \Illuminate\Support\Str::limit($trip->notes, 65) : '-' }}</div>
                                    </td>
                                    <td style="font-size:.8rem">
                                        <div class="fw-semibold text-dark">{{ $trip->creator->name ?? '-' }}</div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if(auth()->user()->hasPermission('shuttle_operations', 'edit'))
                                                <a href="{{ route('shuttle.operations.edit', $trip) }}"
                                                   class="btn btn-sm"
                                                   style="background:#f4f6fb;color:#1e2d3d;border:1px solid #dde3ef;font-size:.78rem">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('shuttle_operations', 'delete'))
                                                <form action="{{ route('shuttle.operations.destroy', $trip) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Bu seferi silmek istiyor musunuz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm"
                                                            style="background:#fdf4f4;color:#b03030;border:1px solid #f0d0d0;font-size:.78rem">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f5f7fa;border-top:2px solid #e2e8f0">
                                <td colspan="3" class="ps-4 py-2 fw-bold text-muted text-uppercase" style="font-size:.75rem">Toplam</td>
                                <td class="text-center">
                                    <span style="background:#eef3f9;color:#2a5298;font-weight:700;font-size:.9rem;padding:2px 10px;border-radius:6px">
                                        {{ $totalArrival }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span style="background:#eef6f2;color:#2e7d52;font-weight:700;font-size:.9rem;padding:2px 10px;border-radius:6px">
                                        {{ $totalDeparture }}
                                    </span>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

@if(auth()->user()->hasPermission('shuttle_operations', 'create'))
    @php
        $oldBranchMovements = old('branch_movements', []);
    @endphp
    <div class="modal fade" id="addTripModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3"
                     style="background:linear-gradient(135deg,#1e2d3d,#2c3e50)">
                    <h5 class="modal-title text-white fw-semibold">
                        <i class="fas fa-plus me-2"></i>Yeni Sefer Ekle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shuttle.operations.store') }}" method="POST" id="createTripForm">
                    @csrf
                    <input type="hidden" name="departure_count" value="{{ old('departure_count', 0) }}">
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Sube <span class="text-danger">*</span></label>
                                <select name="branch_id" id="createTripBranchId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $branchId) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Arac <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id" id="createTripVehicleId" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach($vehicles as $v)
                                        <option
                                            value="{{ $v->id }}"
                                            data-branch-id="{{ $v->branch_id }}"
                                            data-route-ids="{{ $v->routes->pluck('id')->implode(',') }}"
                                            @selected(old('shuttle_vehicle_id') == $v->id)
                                        >
                                            {{ $v->name }}@if($v->plate) ({{ $v->plate }})@endif - Kap: {{ $v->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Guzergah</label>
                                <select name="route_id" id="createTripRouteId" class="form-select">
                                    <option value="">- Arac secildikten sonra listelenir -</option>
                                    @foreach($routes as $r)
                                        <option value="{{ $r->id }}" @selected(old('route_id') == $r->id)>
                                            {{ $r->name }} - {{ $r->branch->name ?? 'Sube yok' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1" id="createTripRouteHelp">
                                    Secilen aracin gorevli oldugu guzergahlar listelenir.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Vardiya <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select" required>
                                    <option value="">- Seciniz -</option>
                                    @foreach(\App\Models\ShuttleTrip::SHIFTS as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift') === $shift)>{{ $shift }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Tarih <span class="text-danger">*</span></label>
                                <input type="date" name="trip_date" value="{{ old('trip_date', $date->toDateString()) }}"
                                       class="form-control" required>
                            </div>

                            <div class="col-12">
                                <div id="createTripFlagsBox" class="p-3 rounded {{ old('shuttle_vehicle_id') ? '' : 'd-none' }}"
                                     style="background:#fff4f4;border:1px solid #f0d0d0">
                                    <div class="fw-semibold small mb-2" style="color:#a94442">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Operasyon Istisnalari
                                    </div>
                                    <div class="small text-muted mb-3">
                                        Planlanan arac disinda gerceklesen gelis veya transfer durumlarini isaretle.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="hidden" name="arrived_with_different_vehicle" value="0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                       name="arrived_with_different_vehicle" id="createDifferentVehicle"
                                                       @checked(old('arrived_with_different_vehicle'))>
                                                <label class="form-check-label fw-semibold" for="createDifferentVehicle">
                                                    Farkli arac ile geldi
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="hidden" name="is_transfer" value="0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                       name="is_transfer" id="createIsTransfer"
                                                       @checked(old('is_transfer'))>
                                                <label class="form-check-label fw-semibold" for="createIsTransfer">
                                                    Aktarim yapildi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 rounded" style="background:#f0f4ff;border:1px solid #d0ddf5">
                                    <div class="fw-semibold small mb-2" style="color:#2a5298">
                                        <i class="fas fa-arrow-right me-1"></i>Gelis Bilgileri
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Gelis Saati</label>
                                            <input type="time" name="arrival_time" value="{{ old('arrival_time') }}"
                                                   class="form-control form-control-sm" data-auto-time-picker="1">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Gelen Kisi <span class="text-danger">*</span></label>
                                            <input type="number" name="arrival_count" value="{{ old('arrival_count', 0) }}"
                                                   min="0" max="500" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                @include('modules.shuttle.operations._branch_movements', [
                                    'branches' => $branches,
                                    'movementValues' => $oldBranchMovements,
                                ])
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Not</label>
                                <textarea name="notes" rows="2" class="form-control"
                                          placeholder="Istege bagli not..." maxlength="500">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Iptal</button>
                        <button type="submit" class="btn fw-semibold px-4"
                                style="background:#1e2d3d;color:#fff;border-radius:8px">
                            <i class="fas fa-save me-1"></i> Seferi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if(auth()->user()->hasPermission('shuttle_operations', 'edit'))
    <div class="modal fade" id="departureModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow" style="border-radius:12px;overflow:hidden">
                <div class="modal-header border-0 px-4 py-3"
                     style="background:linear-gradient(135deg,#2e7d52,#3d7a5e)">
                    <h5 class="modal-title text-white fw-semibold" style="font-size:.95rem">
                        <i class="fas fa-arrow-left me-2"></i>Donus Bilgisi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="departureForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Donus Saati</label>
                            <input type="time" name="departure_time" id="depTime" class="form-control" data-auto-time-picker="1">
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold small">Donen Kisi Sayisi <span class="text-danger">*</span></label>
                            <input type="number" name="departure_count" id="depCount" value="0"
                                   min="0" max="500" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Iptal</button>
                        <button type="submit" class="btn btn-sm fw-semibold px-4"
                                style="background:#2e7d52;color:#fff;border-radius:8px">
                            <i class="fas fa-save me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
function changeDate(delta) {
    const input = document.querySelector('input[name="date"]');
    const d = new Date(input.value + 'T00:00:00');
    d.setDate(d.getDate() + delta);
    input.value = d.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}

function setToday() {
    const input = document.querySelector('input[name="date"]');
    const now = new Date();
    input.value = now.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}

function tryShowPicker(input) {
    if (!input || typeof input.showPicker !== 'function') {
        return;
    }

    try {
        input.showPicker();
    } catch (error) {
        // Browser desteklemiyorsa sessizce gec.
    }
}

function bindAutoTimePickers(scope = document) {
    scope.querySelectorAll('input[data-auto-time-picker="1"]').forEach((input) => {
        input.addEventListener('focus', () => tryShowPicker(input));
        input.addEventListener('click', () => tryShowPicker(input));
    });
}

function wireTripForm(branchSelector, vehicleSelector, routeSelector, flagsSelector, helpSelector, currentRouteId = '', legacyVehicleId = '') {
    const branchSelect = document.querySelector(branchSelector);
    const vehicleSelect = document.querySelector(vehicleSelector);
    const routeSelect = document.querySelector(routeSelector);
    const flagsBox = document.querySelector(flagsSelector);
    const helpBox = document.querySelector(helpSelector);

    if (!branchSelect || !vehicleSelect || !routeSelect) {
        return;
    }

    const syncVehicles = () => {
        const branchId = branchSelect.value;

        Array.from(vehicleSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matches = !branchId || option.dataset.branchId === branchId;
            option.hidden = !matches;
            option.disabled = !matches;
        });

        if (vehicleSelect.selectedOptions[0] && vehicleSelect.selectedOptions[0].disabled) {
            vehicleSelect.value = '';
        }

        syncRoutes();
    };

    const syncRoutes = () => {
        const selectedVehicle = vehicleSelect.selectedOptions[0];
        const vehicleId = selectedVehicle?.value || '';
        const allowedRouteIds = new Set(
            ((selectedVehicle?.dataset.routeIds || '')
                .split(',')
                .map((value) => value.trim())
                .filter(Boolean))
        );

        if (flagsBox) {
            flagsBox.classList.toggle('d-none', !vehicleId);
        }

        Array.from(routeSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const allowed = vehicleId && (
                allowedRouteIds.has(option.value)
                || (option.value === currentRouteId && vehicleId === legacyVehicleId)
            );
            option.hidden = !allowed;
            option.disabled = !allowed;
        });

        if (routeSelect.selectedOptions[0] && routeSelect.selectedOptions[0].disabled) {
            routeSelect.value = '';
        }

        routeSelect.disabled = !vehicleId || (allowedRouteIds.size === 0 && !(currentRouteId && vehicleId === legacyVehicleId));

        if (helpBox) {
            if (!vehicleId) {
                helpBox.textContent = 'Secilen aracin gorevli oldugu guzergahlar listelenir.';
            } else if (allowedRouteIds.size === 0) {
                helpBox.textContent = 'Bu araca henuz guzergah atamasi yapilmamis.';
            } else {
                helpBox.textContent = 'Yalnizca secilen aracin gorevli oldugu guzergahlar gorunuyor.';
            }
        }
    };

    branchSelect.addEventListener('change', syncVehicles);
    vehicleSelect.addEventListener('change', syncRoutes);

    syncVehicles();
}

function openDepartureModal(tripId, depTime, depCount) {
    const baseUrl = '{{ url("servis-takip/operasyon") }}';
    document.getElementById('departureForm').action = baseUrl + '/' + tripId + '/donus';
    document.getElementById('depTime').value = depTime;
    document.getElementById('depCount').value = depCount;
    const modalElement = document.getElementById('departureModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    modalElement.addEventListener('shown.bs.modal', function handleShown() {
        const depTimeInput = document.getElementById('depTime');
        depTimeInput?.focus();
        tryShowPicker(depTimeInput);
        modalElement.removeEventListener('shown.bs.modal', handleShown);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindAutoTimePickers(document);
    wireTripForm(
        '#createTripBranchId',
        '#createTripVehicleId',
        '#createTripRouteId',
        '#createTripFlagsBox',
        '#createTripRouteHelp'
    );

    @if($errors->any() && auth()->user()->hasPermission('shuttle_operations', 'create'))
        const addTripModal = document.getElementById('addTripModal');
        if (addTripModal) {
            new bootstrap.Modal(addTripModal).show();
        }
    @endif
});
</script>
@endpush
@endsection
