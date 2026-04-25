@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Kapı Giriş/Çıkış</h4>
                <span>Personel giriş çıkış kayıtları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Kapı Giriş/Çıkış</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- İÇERİDE / DIŞARIDA TABLOSU --}}
    <div class="row mb-4">
        {{-- İçeridekiler --}}
        <div class="col-xl-6 col-12 mb-3 mb-xl-0">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background:linear-gradient(135deg,#1e7e34,#28a745);border-radius:.5rem .5rem 0 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                         stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span class="text-white fw-semibold fs-6">
                        İçeridekiler
                        <span class="badge bg-white text-success ms-1">{{ $insideUsers->count() }}</span>
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($insideUsers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none"
                                 stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mb-2 opacity-50">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            <p class="mb-0">Şu an içeride personel yok</p>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                            <table class="table table-hover table-sm mb-0 align-middle">
                                <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                                    <tr>
                                        <th class="ps-3">Personel</th>
                                        <th>Departman</th>
                                        <th>Giriş Saati</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insideUsers as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                                      style="width:30px;height:30px;font-size:12px;background:#28a745;flex-shrink:0;">
                                                    {{ strtoupper(substr(optional($log->user)->name ?? '?', 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div class="fw-semibold" style="font-size:13px;">{{ optional($log->user)->name ?? '-' }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ optional($log->user->branch)->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:13px;">{{ optional($log->user->department)->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-success light" style="font-size:11px;">
                                                {{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}
                                            </span>
                                            <div class="text-muted" style="font-size:10px;">
                                                {{ \Carbon\Carbon::parse($log->logged_at)->format('d.m.Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning btn-hizli-islem"
                                                    style="font-size:11px;padding:3px 10px;border-radius:20px;"
                                                    data-user-id="{{ optional($log->user)->id }}"
                                                    data-user-name="{{ optional($log->user)->name }}"
                                                    data-user-title="{{ optional($log->user)->title ?? '' }}"
                                                    data-type="cikis">
                                                ↓ Çıkış
                                            </button>
                                            <form id="hizli-form-cikis-{{ optional($log->user)->id }}"
                                                  action="{{ route('door-logs.quick') }}" method="POST" class="d-none">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ optional($log->user)->id }}">
                                                <input type="hidden" name="type" value="cikis">
                                            </form>
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

        {{-- Dışarıdakiler --}}
        <div class="col-xl-6 col-12">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background:linear-gradient(135deg,#b02a37,#dc3545);border-radius:.5rem .5rem 0 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                         stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2H9"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="text-white fw-semibold fs-6">
                        Dışarıdakiler
                        <span class="badge bg-white text-danger ms-1">{{ $outsideUsers->count() + $neverLoggedUsers->count() }}</span>
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($outsideUsers->isEmpty() && $neverLoggedUsers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none"
                                 stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mb-2 opacity-50">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            <p class="mb-0">Kayıtlı çıkış yok</p>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                            <table class="table table-hover table-sm mb-0 align-middle">
                                <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                                    <tr>
                                        <th class="ps-3">Personel</th>
                                        <th>Departman</th>
                                        <th>Çıkış Saati</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outsideUsers as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                                      style="width:30px;height:30px;font-size:12px;background:#dc3545;flex-shrink:0;">
                                                    {{ strtoupper(substr(optional($log->user)->name ?? '?', 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div class="fw-semibold" style="font-size:13px;">{{ optional($log->user)->name ?? '-' }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ optional($log->user->branch)->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:13px;">{{ optional($log->user->department)->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-danger light" style="font-size:11px;">
                                                {{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}
                                            </span>
                                            <div class="text-muted" style="font-size:10px;">
                                                {{ \Carbon\Carbon::parse($log->logged_at)->format('d.m.Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success btn-hizli-islem"
                                                    style="font-size:11px;padding:3px 10px;border-radius:20px;"
                                                    data-user-id="{{ optional($log->user)->id }}"
                                                    data-user-name="{{ optional($log->user)->name }}"
                                                    data-user-title="{{ optional($log->user)->title ?? '' }}"
                                                    data-type="giris">
                                                ↑ Giriş
                                            </button>
                                            <form id="hizli-form-giris-{{ optional($log->user)->id }}"
                                                  action="{{ route('door-logs.quick') }}" method="POST" class="d-none">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ optional($log->user)->id }}">
                                                <input type="hidden" name="type" value="giris">
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @foreach($neverLoggedUsers as $user)
                                    <tr class="table-light">
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                                      style="width:30px;height:30px;font-size:12px;background:#adb5bd;flex-shrink:0;">
                                                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div class="fw-semibold" style="font-size:13px;">{{ $user->name }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ optional($user->branch)->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:13px;">{{ optional($user->department)->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary" style="font-size:11px;">Bugün giriş yok</span>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success btn-hizli-islem"
                                                    style="font-size:11px;padding:3px 10px;border-radius:20px;"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-title="{{ $user->title ?? '' }}"
                                                    data-type="giris">
                                                ↑ Giriş
                                            </button>
                                            <form id="hizli-form-giris-{{ $user->id }}"
                                                  action="{{ route('door-logs.quick') }}" method="POST" class="d-none">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="type" value="giris">
                                            </form>
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
    </div>

    {{-- HIZ KAYIT PANELİ --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Hızlı Kayıt — Anlık
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('door-logs.quick') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6" style="position:relative;z-index:10;">
                        <label class="form-label fw-semibold">Personel</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Personel seçin...</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->name }}
                                    @if($manager->department)— {{ $manager->department->name }}@endif
                                    ({{ $manager->branch->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" style="position:relative;z-index:10;">
                        <label class="form-label fw-semibold">İşlem</label>
                        <select name="type" class="form-select" required>
                            <option value="giris">🟢 Giriş</option>
                            <option value="cikis">🔴 Çıkış</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Şimdi Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KAYIT LİSTESİ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Kayıtlar
                <small class="text-muted fw-normal" style="font-size:13px;">— kişi / gün bazında gruplu</small>
            </h4>
            <a href="{{ route('door-logs.create') }}" class="btn btn-secondary btn-sm">
                + Manuel Kayıt
            </a>
        </div>
        <div class="card-body">

            {{-- FİLTRELER --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-2">
                    <label class="form-label small">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Personel</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tüm Personel</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(request('user_id') == $manager->id)>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranchId == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tip</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="giris" @selected(request('type') === 'giris')>Giriş</option>
                        <option value="cikis" @selected(request('type') === 'cikis')>Çıkış</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" style="border-collapse:separate;border-spacing:0 4px;">
                    <thead>
                        <tr style="background:transparent;">
                            <th class="border-0 ps-3" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Personel</th>
                            <th class="border-0" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Departman</th>
                            <th class="border-0" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Tarih</th>
                            <th class="border-0" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Giriş Saatleri</th>
                            <th class="border-0" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Çıkış Saatleri</th>
                            <th class="border-0 text-center" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Log</th>
                            <th class="border-0 text-end pe-3" style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Detay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $group)
                        @php $rowId = 'row-' . optional($group->user)->id . '-' . str_replace('-', '', $group->date); @endphp

                        {{-- ANA SATIR --}}
                        <tr style="background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.06);border-radius:10px;">
                            <td class="ps-3" style="border-radius:10px 0 0 10px;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                          style="width:36px;height:36px;font-size:13px;background:#4361ee;">
                                        {{ strtoupper(substr(optional($group->user)->name ?? '?', 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold" style="font-size:13px;color:#1e293b;">{{ optional($group->user)->name ?? '-' }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ optional($group->branch)->name ?? optional(optional($group->user)->branch)->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if(optional($group->user)->department)
                                    <span class="badge rounded-pill" style="background:{{ $group->user->department->color }};font-size:11px;">
                                        {{ $group->user->department->name }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;color:#334155;">
                                    {{ \Carbon\Carbon::parse($group->date)->locale('tr')->isoFormat('D MMMM YYYY') }}
                                </div>
                                <div style="font-size:11px;color:#94a3b8;">
                                    {{ \Carbon\Carbon::parse($group->date)->locale('tr')->isoFormat('dddd') }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($group->entries as $entry)
                                        <span class="badge rounded-pill"
                                              style="background:rgba(16,185,129,.12);color:#059669;font-size:11px;font-weight:600;padding:4px 8px;">
                                            ↑ {{ \Carbon\Carbon::parse($entry->logged_at)->format('H:i') }}
                                        </span>
                                    @empty
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($group->exits as $exit)
                                        <span class="badge rounded-pill"
                                              style="background:rgba(249,115,22,.12);color:#ea580c;font-size:11px;font-weight:600;padding:4px 8px;">
                                            ↓ {{ \Carbon\Carbon::parse($exit->logged_at)->format('H:i') }}
                                        </span>
                                    @empty
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:#e2e8f0;color:#475569;font-size:11px;font-weight:700;">
                                    {{ $group->all->count() }}
                                </span>
                            </td>
                            <td class="text-end pe-3" style="border-radius:0 10px 10px 0;">
                                <button class="btn btn-sm btn-outline-secondary toggler-btn"
                                        style="font-size:11px;padding:3px 10px;border-radius:20px;"
                                        data-target="{{ $rowId }}">
                                    <span class="toggle-label">Detay</span>
                                </button>
                            </td>
                        </tr>

                        {{-- DETAY SATIRI (gizli) --}}
                        <tr id="{{ $rowId }}" class="detail-row d-none">
                            <td colspan="7" style="background:#f8fafc;padding:0;border-radius:0 0 10px 10px;">
                                <div style="padding:12px 16px;">
                                    <table class="table table-sm mb-0" style="font-size:12px;">
                                        <thead>
                                            <tr style="color:#94a3b8;">
                                                <th class="border-0 ps-2" style="font-weight:600;">#</th>
                                                <th class="border-0" style="font-weight:600;">Tip</th>
                                                <th class="border-0" style="font-weight:600;">Saat</th>
                                                <th class="border-0" style="font-weight:600;">Not</th>
                                                <th class="border-0 text-end" style="font-weight:600;">Sil</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group->all as $log)
                                            <tr>
                                                <td class="ps-2 text-muted">{{ $log->id }}</td>
                                                <td>
                                                    @if($log->type === 'giris')
                                                        <span style="color:#059669;font-weight:600;">↑ Giriş</span>
                                                    @else
                                                        <span style="color:#ea580c;font-weight:600;">↓ Çıkış</span>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}</td>
                                                <td class="text-muted">{{ $log->notes ?? '—' }}</td>
                                                <td class="text-end">
                                                    <button type="button"
                                                            class="btn btn-danger btn-xs btn-sil"
                                                            data-id="{{ $log->id }}"
                                                            data-name="{{ optional($log->user)->name ?? '' }}">
                                                        Sil
                                                    </button>
                                                    <form id="form-sil-{{ $log->id }}"
                                                          action="{{ route('door-logs.destroy', $log) }}"
                                                          method="POST" class="d-none">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    Bu tarih aralığında kayıt bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3" style="font-size:0.8rem">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        @if ($logs->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $logs->previousPageUrl() }}">&laquo;</a></li>
                        @endif

                        @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            <li class="page-item {{ $page == $logs->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        @if ($logs->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $logs->nextPageUrl() }}">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
                <small class="text-muted ms-3 align-self-center">
                    Toplam {{ $logs->total() }} kayıt, sayfa {{ $logs->currentPage() }}/{{ $logs->lastPage() }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
// Detay satırı aç/kapat
document.querySelectorAll('.toggler-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id  = this.dataset.target;
        const row = document.getElementById(id);
        if (!row) return;
        const isOpen = !row.classList.contains('d-none');
        row.classList.toggle('d-none', isOpen);
        this.querySelector('.toggle-label').textContent = isOpen ? 'Detay' : 'Kapat';
        this.classList.toggle('btn-outline-secondary', isOpen);
        this.classList.toggle('btn-secondary', !isOpen);
    });
});

// Silme onayı
document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        Swal.fire({
            title: 'Emin misiniz?',
            text: `${name} adlı kişinin bu kaydı silinecek!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-sil-' + id).submit();
            }
        });
    });
});

// Hızlı Giriş / Çıkış onayı
document.querySelectorAll('.btn-hizli-islem').forEach(btn => {
    btn.addEventListener('click', function () {
        const userId   = this.dataset.userId;
        const name     = this.dataset.userName;
        const title    = this.dataset.userTitle;
        const type     = this.dataset.type;
        const label    = type === 'giris' ? 'Giriş' : 'Çıkış';
        const color    = type === 'giris' ? '#198754' : '#f97316';
        const icon     = type === 'giris' ? 'question' : 'warning';
        const formId   = 'hizli-form-' + type + '-' + userId;
        const fullName = title ? title + ' ' + name : name;

        Swal.fire({
            title: 'Hızlı ' + label,
            html: `<div style="font-size:15px;font-weight:600;margin-bottom:6px;">${fullName}</div>`
                + `<div style="font-size:13px;color:#64748b;">${label} kaydedilecek, onaylıyor musunuz?</div>`,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Kaydet',
            cancelButtonText: 'İptal'
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    });
});
</script>
@endpush
