@extends('layouts.default')

@section('content')
<div class="container-fluid">

    {{-- BAŞLIK --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Ziyaretçi Kayıtları</h4>
                <span>Departman müdürlerine gelen ziyaretçi yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Ziyaretçi Kayıtları</li>
            </ol>
        </div>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- OZET KARTLAR --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-12">
            <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 2px 16px rgba(67,97,238,.12);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:50px;height:50px;background:rgba(67,97,238,.12);">
                            <i class="fas fa-id-badge" style="font-size:20px;color:#4361ee;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:rgba(67,97,238,.1);color:#4361ee;font-size:11px;font-weight:600;">
                            {{ $dateFrom === $dateTo ? 'Bugün' : 'Dönem' }}
                        </span>
                    </div>
                    <div style="font-size:32px;font-weight:900;color:#1e293b;line-height:1;">{{ $totalToday }}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500;">Toplam Ziyaret</div>
                    <div style="height:3px;border-radius:2px;background:linear-gradient(90deg,#4361ee,#7c98ff);margin-top:16px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 2px 16px rgba(249,115,22,.12);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:50px;height:50px;background:rgba(249,115,22,.12);">
                            <i class="fas fa-door-open" style="font-size:20px;color:#f97316;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:rgba(249,115,22,.1);color:#f97316;font-size:11px;font-weight:600;">Aktif</span>
                    </div>
                    <div style="font-size:32px;font-weight:900;color:#1e293b;line-height:1;">{{ $stillInside }}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500;">Şu An İçeride</div>
                    <div style="height:3px;border-radius:2px;background:linear-gradient(90deg,#f97316,#fdba74);margin-top:16px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 2px 16px rgba(16,185,129,.12);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:50px;height:50px;background:rgba(16,185,129,.12);">
                            <i class="fas fa-sign-out-alt" style="font-size:20px;color:#10b981;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:rgba(16,185,129,.1);color:#10b981;font-size:11px;font-weight:600;">Tamamlandı</span>
                    </div>
                    <div style="font-size:32px;font-weight:900;color:#1e293b;line-height:1;">{{ $leftCount }}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500;">Çıkış Yaptı</div>
                    <div style="height:3px;border-radius:2px;background:linear-gradient(90deg,#10b981,#6ee7b7);margin-top:16px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ICERIDE / DISARIDA --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-6 col-12">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background:linear-gradient(135deg,#b45309,#f97316);border-radius:14px 14px 0 0;border:none;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:34px;height:34px;background:rgba(255,255,255,.2);">
                        <i class="fas fa-building" style="font-size:14px;color:#fff;"></i>
                    </div>
                    <span class="text-white fw-semibold fs-6">
                        İçeridekiler
                        <span class="badge bg-white ms-1" style="color:#f97316;">{{ $insideNow->count() }}</span>
                    </span>
                    <small class="text-white opacity-75 ms-auto">Şu an binada</small>
                </div>
                <div class="card-body p-0">
                    @if($insideNow->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50 d-block"></i>
                            <p class="mb-0 small">Şu an içeride ziyaretçi yok</p>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                            <table class="table table-hover table-sm mb-0 align-middle">
                                <thead style="position:sticky;top:0;z-index:2;background:#fff3e0;">
                                    <tr>
                                        <th class="ps-3" style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;">Ziyaretçi</th>
                                        <th style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;">Müdür / Dept.</th>
                                        <th style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;">Giriş</th>
                                        <th style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;">Süre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insideNow as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold" style="font-size:13px;">{{ $log->visitor_name }}</div>
                                            @if($log->visitor_company)
                                                <div class="text-muted" style="font-size:11px;">{{ $log->visitor_company }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold" style="font-size:13px;">{{ $log->host?->name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size:11px;">{{ $log->host?->department?->name ?? $log->department?->name ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill"
                                                  style="background:rgba(249,115,22,.12);color:#ea580c;font-size:11px;font-weight:600;padding:4px 8px;">
                                                {{ $log->check_in_at->format('H:i') }}
                                            </span>
                                            <div class="text-muted" style="font-size:10px;">{{ $log->check_in_at->format('d.m') }}</div>
                                        </td>
                                        <td>
                                            @php $mins = (int) now()->diffInMinutes($log->check_in_at); @endphp
                                            <small class="text-muted">{{ intdiv($mins,60) > 0 ? intdiv($mins,60).'sa ' : '' }}{{ $mins%60 }}dk</small>
                                        </td>
                                        <td class="pe-2">
                                            <button type="button" class="btn btn-sm btn-checkout"
                                                    style="background:rgba(16,185,129,.1);color:#059669;border:none;border-radius:20px;font-size:11px;padding:3px 10px;font-weight:600;"
                                                    data-id="{{ $log->id }}" data-name="{{ $log->visitor_name }}">
                                                <i class="fas fa-sign-out-alt me-1"></i>Çıkış
                                            </button>
                                            <form id="checkout-form-{{ $log->id }}"
                                                  action="{{ route('guest-logs.checkout', $log) }}"
                                                  method="POST" class="d-none">@csrf</form>
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

        <div class="col-xl-6 col-12">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background:linear-gradient(135deg,#0f766e,#10b981);border-radius:14px 14px 0 0;border:none;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:34px;height:34px;background:rgba(255,255,255,.18);">
                        <i class="fas fa-user-check" style="font-size:14px;color:#fff;"></i>
                    </div>
                    <span class="text-white fw-semibold fs-6">
                        Müsait Müdürler
                        <span class="badge bg-white ms-1" style="color:#0f766e;">{{ $outsideManagers->count() + $busyManagers->count() }}</span>
                    </span>
                    <small class="text-white opacity-75 ms-auto">Binada — ziyaretçi beklenebilir</small>
                </div>
                <div class="card-body p-0">
                    @if($outsideManagers->isEmpty() && $busyManagers->isEmpty() && $absentManagers->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                            <p class="mb-0 small">Tüm müdürlerin yanında ziyaretçi var</p>
                        </div>
                    @else
                        <div style="max-height:320px;overflow-y:auto;">

                            {{-- MÜSAİT: binada + ziyaretçisiz --}}
                            @foreach($outsideManagers as $mgr)
                            @php
                                $mgrLastLog = \App\Models\GuestLog::where('host_user_id', $mgr->id)
                                    ->whereDate('check_in_at', today())
                                    ->latest('check_in_at')->first();
                            @endphp
                            <div class="d-flex align-items-center gap-3 px-3 py-2"
                                 style="border-bottom:1px solid #f1f5f9;">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                      style="width:34px;height:34px;font-size:13px;background:#10b981;">
                                    {{ strtoupper(substr($mgr->name, 0, 1)) }}
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:13px;color:#1e293b;">{{ $mgr->name }}</div>
                                    @if($mgr->department)
                                        <span class="badge rounded-pill" style="font-size:10px;background:{{ $mgr->department->color ?? '#6b7280' }}20;color:{{ $mgr->department->color ?? '#6b7280' }};">
                                            {{ $mgr->department->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-end flex-shrink-0">
                                    @if($mgrLastLog)
                                        <div class="text-muted" style="font-size:10px;">Son ziyaret</div>
                                        <span class="badge rounded-pill" style="background:rgba(16,185,129,.1);color:#059669;font-size:11px;">
                                            {{ $mgrLastLog->check_in_at->format('H:i') }}
                                        </span>
                                    @endif
                                    <div class="mt-1">
                                        <a href="{{ route('guest-logs.create') }}?host_user_id={{ $mgr->id }}"
                                           class="btn btn-sm"
                                           style="background:rgba(67,97,238,.1);color:#4361ee;border:none;border-radius:20px;font-size:11px;padding:2px 10px;font-weight:600;">
                                            <i class="fas fa-plus me-1"></i>Ziyaretçi
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @if($outsideManagers->isEmpty() && $busyManagers->isEmpty())
                            <div class="text-center text-muted py-3">
                                <p class="mb-0 small">Binada müsait müdür yok</p>
                            </div>
                            @endif

                            {{-- MEŞGUL: aktif ziyaretçisi var --}}
                            @if($busyManagers->isNotEmpty())
                            <div class="px-3 pt-2 pb-1" style="background:#fef2f2;border-top:2px solid #fecaca;">
                                <small style="font-size:11px;color:#b91c1c;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="fas fa-user-friends me-1"></i>Ziyaretçisi Var
                                </small>
                            </div>
                            @foreach($busyManagers as $mgr)
                            @php
                                $visitorCount = \App\Models\GuestLog::where('host_user_id', $mgr->id)->whereNull('check_out_at')->count();
                            @endphp
                            <div class="d-flex align-items-center gap-3 px-3 py-2"
                                 style="border-bottom:1px solid #f1f5f9;background:#fff5f5;">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                      style="width:34px;height:34px;font-size:13px;background:#ef4444;">
                                    {{ strtoupper(substr($mgr->name, 0, 1)) }}
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:13px;color:#1e293b;">{{ $mgr->name }}</div>
                                    @if($mgr->department)
                                        <span class="badge rounded-pill" style="font-size:10px;background:{{ $mgr->department->color ?? '#6b7280' }}20;color:{{ $mgr->department->color ?? '#6b7280' }};">
                                            {{ $mgr->department->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <span class="badge rounded-pill" style="background:rgba(239,68,68,.1);color:#b91c1c;font-size:11px;padding:4px 8px;">
                                        <i class="fas fa-users me-1"></i>{{ $visitorCount }} ziyaretçi
                                    </span>
                                    <div class="mt-1">
                                        <a href="{{ route('guest-logs.create') }}?host_user_id={{ $mgr->id }}"
                                           class="btn btn-sm"
                                           style="background:rgba(67,97,238,.1);color:#4361ee;border:none;border-radius:20px;font-size:11px;padding:2px 10px;font-weight:600;">
                                            <i class="fas fa-plus me-1"></i>Ziyaretçi
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif

                            {{-- BİNADA DEĞİL --}}
                            @if($absentManagers->isNotEmpty())
                            <div class="px-3 pt-2 pb-1" style="background:#fef9f0;border-top:2px solid #fed7aa;">
                                <small style="font-size:11px;color:#b45309;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="fas fa-door-closed me-1"></i>Binada Değil
                                </small>
                            </div>
                            @foreach($absentManagers as $mgr)
                            <div class="d-flex align-items-center gap-3 px-3 py-2"
                                 style="border-bottom:1px solid #f1f5f9;background:#fffbf5;">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                      style="width:34px;height:34px;font-size:13px;background:#d1d5db;">
                                    {{ strtoupper(substr($mgr->name, 0, 1)) }}
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:13px;color:#9ca3af;">{{ $mgr->name }}</div>
                                    @if($mgr->department)
                                        <span class="badge rounded-pill" style="font-size:10px;background:#f3f4f6;color:#9ca3af;">
                                            {{ $mgr->department->name }}
                                        </span>
                                    @endif
                                </div>
                                <span class="badge rounded-pill flex-shrink-0"
                                      style="background:rgba(245,158,11,.1);color:#b45309;font-size:11px;padding:4px 8px;">
                                    <i class="fas fa-sign-out-alt me-1"></i>Çıktı
                                </span>
                            </div>
                            @endforeach
                            @endif

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRE + LISTE --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header d-flex justify-content-between align-items-center py-3"
             style="border-radius:14px 14px 0 0;background:#fff;border-bottom:1px solid #f1f5f9;">
            <h5 class="mb-0 fw-bold" style="color:#1e293b;">
                Ziyaretçi Listesi
                <small class="text-muted fw-normal ms-2" style="font-size:13px;">
                    {{ \Carbon\Carbon::parse($dateFrom)->locale('tr')->isoFormat('D MMMM YYYY') }}
                    @if($dateFrom !== $dateTo) — {{ \Carbon\Carbon::parse($dateTo)->locale('tr')->isoFormat('D MMMM YYYY') }} @endif
                </small>
            </h5>
            <a href="{{ route('guest-logs.create') }}" class="btn btn-primary btn-sm" style="border-radius:20px;padding:6px 16px;">
                <i class="fas fa-plus me-1"></i> Ziyaretçi Ekle
            </a>
        </div>
        <div class="card-body border-bottom py-3" style="background:#f8fafc;">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                @if(count($branches) > 1)
                <div class="col-md-2">
                    <label class="form-label small mb-1">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small mb-1">Amaç</label>
                    <select name="purpose" class="form-select form-select-sm">
                        <option value="">Tüm Amaçlar</option>
                        @foreach(\App\Models\GuestLog::PURPOSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('purpose') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tüm Durum</option>
                        <option value="inside" @selected(request('status') == 'inside')>İçeride</option>
                        <option value="left"   @selected(request('status') == 'left')>Çıkış Yaptı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Ara</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="İsim, telefon, şirket..." value="{{ request('search') }}">
                </div>
                <div class="col-auto d-flex gap-1">
                    <button class="btn btn-primary btn-sm">Filtrele</button>
                    @if(request()->hasAny(['search','status','purpose','branch_id']) || request('date_from') !== today()->format('Y-m-d'))
                        <a href="{{ route('guest-logs.index') }}" class="btn btn-outline-secondary btn-sm">Temizle</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th class="ps-4 border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Ziyaretçi</th>
                            <th class="border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Müdür / Dept.</th>
                            <th class="border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Amaç</th>
                            <th class="border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Giriş</th>
                            <th class="border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Çıkış</th>
                            <th class="border-0" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">Süre</th>
                            <th class="border-0 text-end pe-4" style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 8px;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                          style="width:36px;height:36px;font-size:13px;background:#4361ee;">
                                        {{ strtoupper(substr($log->visitor_name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold" style="font-size:13px;color:#1e293b;">{{ $log->visitor_name }}</div>
                                        @if($log->visitor_company)
                                            <div class="text-muted" style="font-size:11px;"><i class="fas fa-building me-1 opacity-50"></i>{{ $log->visitor_company }}</div>
                                        @endif
                                        @if($log->visitor_phone)
                                            <div class="text-muted" style="font-size:11px;"><i class="fas fa-phone me-1 opacity-50"></i>{{ $log->visitor_phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;color:#334155;">{{ $log->host?->name ?? '-' }}</div>
                                @if($log->department)
                                    <span class="badge rounded-pill" style="font-size:10px;background:{{ $log->department->color ?? '#6b7280' }}20;color:{{ $log->department->color ?? '#6b7280' }};">
                                        {{ $log->department->name }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pStyle = ['meeting'=>['rgba(67,97,238,.1)','#4361ee'],'delivery'=>['rgba(245,158,11,.1)','#b45309'],'interview'=>['rgba(6,182,212,.1)','#0891b2'],'official'=>['rgba(16,185,129,.1)','#059669'],'other'=>['rgba(107,114,128,.1)','#6b7280']];
                                    [$pbg,$pc] = $pStyle[$log->purpose] ?? $pStyle['other'];
                                @endphp
                                <span class="badge rounded-pill" style="background:{{ $pbg }};color:{{ $pc }};font-size:11px;font-weight:600;padding:4px 10px;">
                                    {{ \App\Models\GuestLog::PURPOSES[$log->purpose] }}
                                </span>
                                @if($log->purpose_note)
                                    <div class="text-muted" style="font-size:11px;">{{ Str::limit($log->purpose_note, 35) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold" style="font-size:13px;color:#334155;">{{ $log->check_in_at->format('H:i') }}</span>
                                <div class="text-muted" style="font-size:11px;">{{ $log->check_in_at->format('d.m.Y') }}</div>
                            </td>
                            <td>
                                @if($log->check_out_at)
                                    <span class="fw-semibold" style="font-size:13px;color:#334155;">{{ $log->check_out_at->format('H:i') }}</span>
                                    <div class="text-muted" style="font-size:11px;">{{ $log->check_out_at->format('d.m.Y') }}</div>
                                @else
                                    <span class="badge rounded-pill"
                                          style="background:rgba(249,115,22,.12);color:#ea580c;font-size:11px;font-weight:600;padding:4px 10px;">
                                        İçeride
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($log->durationMinutes() !== null)
                                    @php $dur = $log->durationMinutes(); @endphp
                                    <span style="font-size:13px;color:#475569;">
                                        {{ intdiv($dur,60) > 0 ? intdiv($dur,60).'sa ' : '' }}{{ $dur%60 }}dk
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    @if($log->isInside())
                                    <button type="button" class="btn btn-sm btn-checkout"
                                            style="background:rgba(16,185,129,.1);color:#059669;border:none;border-radius:8px;font-size:11px;padding:4px 10px;"
                                            data-id="{{ $log->id }}" data-name="{{ $log->visitor_name }}" title="Çıkış Yap">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                    <form id="checkout-form-{{ $log->id }}" action="{{ route('guest-logs.checkout', $log) }}" method="POST" class="d-none">@csrf</form>
                                    @endif
                                    <a href="{{ route('guest-logs.show', $log) }}"
                                       class="btn btn-sm" style="background:rgba(67,97,238,.1);color:#4361ee;border:none;border-radius:8px;padding:4px 10px;" title="Detay">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('guest-logs.edit', $log) }}"
                                       class="btn btn-sm" style="background:rgba(100,116,139,.1);color:#475569;border:none;border-radius:8px;padding:4px 10px;" title="Duzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-delete"
                                            style="background:rgba(239,68,68,.1);color:#dc2626;border:none;border-radius:8px;padding:4px 10px;"
                                            data-id="{{ $log->id }}" data-name="{{ $log->visitor_name }}" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $log->id }}" action="{{ route('guest-logs.destroy', $log) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-id-badge fa-3x mb-3 d-block opacity-10"></i>
                                <p class="text-muted mb-0">Bu tarih aralığında ziyaretçi kaydı yok.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-transparent">{{ $logs->links() }}</div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('.btn-checkout').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id, name = this.dataset.name;
        Swal.fire({
            title: 'Çıkış Kaydedilsin mi?',
            html: `<b>${name}</b> için çıkış saati şimdi olarak kaydedilecek.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Çıkış Yap',
            cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) document.getElementById('checkout-form-' + id)?.submit(); });
    });
});
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id, name = this.dataset.name;
        Swal.fire({
            title: 'Kayıt Silinsin mi?',
            html: `<b>${name}</b> adlı ziyaretçinin kaydı kalıcı olarak silinecek.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id)?.submit(); });
    });
});
</script>
@endpush
