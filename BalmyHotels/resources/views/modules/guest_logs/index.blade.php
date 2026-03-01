@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-id-badge me-2 text-primary"></i>Ziyaretçi Kayıtları</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Ziyaretçi Kayıtları</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Özet Kartlar --}}
    <div class="row g-3 mb-4">

        {{-- TOPLAM ZİYARET --}}
        <div class="col-sm-4">
            <div class="card border-0 overflow-hidden mb-0" style="border-radius:16px;box-shadow:0 4px 24px rgba(67,97,238,.13)">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,#4361ee 0%,#3a0ca3 100%);min-height:110px;position:relative;padding:20px 22px 16px">
                        {{-- Arka plan dekoratif daire --}}
                        <div style="position:absolute;right:-18px;top:-18px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.08)"></div>
                        <div style="position:absolute;right:18px;bottom:-30px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.06)"></div>

                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color:rgba(255,255,255,.75);font-size:12px;font-weight:600;letter-spacing:.6px;text-transform:uppercase">Toplam Ziyaret</p>
                                <h2 class="fw-bold mb-0 text-white" style="font-size:2.6rem;line-height:1">{{ $totalToday }}</h2>
                                <small style="color:rgba(255,255,255,.6)">Bugünkü toplam</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:52px;height:52px;background:rgba(255,255,255,.18);backdrop-filter:blur(6px)">
                                <i class="fas fa-id-badge text-white" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                    <div style="background:#fff;padding:10px 22px 12px">
                        @php $pct = $totalToday > 0 ? 100 : 0; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;border-radius:4px">
                                <div class="progress-bar" style="width:{{ $pct }}%;background:#4361ee;border-radius:4px"></div>
                            </div>
                            <span style="font-size:11px;color:#888;white-space:nowrap">Giriş + Çıkış</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- İÇERİDE --}}
        <div class="col-sm-4">
            <div class="card border-0 overflow-hidden mb-0" style="border-radius:16px;box-shadow:0 4px 24px rgba(255,160,0,.15)">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,#f9a825 0%,#f57f17 100%);min-height:110px;position:relative;padding:20px 22px 16px">
                        <div style="position:absolute;right:-18px;top:-18px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.08)"></div>
                        <div style="position:absolute;right:18px;bottom:-30px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.06)"></div>

                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color:rgba(255,255,255,.85);font-size:12px;font-weight:600;letter-spacing:.6px;text-transform:uppercase">Şu An İçeride</p>
                                <h2 class="fw-bold mb-0 text-white" style="font-size:2.6rem;line-height:1">{{ $stillInside }}</h2>
                                <small style="color:rgba(255,255,255,.75)">Henüz çıkış yapmadı</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:52px;height:52px;background:rgba(255,255,255,.22);backdrop-filter:blur(6px)">
                                <i class="fas fa-door-open text-white" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                    <div style="background:#fff;padding:10px 22px 12px">
                        @php $insidePct = $totalToday > 0 ? round(($stillInside / $totalToday) * 100) : 0; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;border-radius:4px">
                                <div class="progress-bar" style="width:{{ $insidePct }}%;background:#f9a825;border-radius:4px"></div>
                            </div>
                            <span style="font-size:11px;color:#888;white-space:nowrap">% {{ $insidePct }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ÇIKIŞ YAPTI --}}
        <div class="col-sm-4">
            <div class="card border-0 overflow-hidden mb-0" style="border-radius:16px;box-shadow:0 4px 24px rgba(16,185,129,.13)">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);min-height:110px;position:relative;padding:20px 22px 16px">
                        <div style="position:absolute;right:-18px;top:-18px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.08)"></div>
                        <div style="position:absolute;right:18px;bottom:-30px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.06)"></div>

                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color:rgba(255,255,255,.85);font-size:12px;font-weight:600;letter-spacing:.6px;text-transform:uppercase">Çıkış Yaptı</p>
                                <h2 class="fw-bold mb-0 text-white" style="font-size:2.6rem;line-height:1">{{ $leftCount }}</h2>
                                <small style="color:rgba(255,255,255,.75)">Ziyareti tamamladı</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:52px;height:52px;background:rgba(255,255,255,.22);backdrop-filter:blur(6px)">
                                <i class="fas fa-sign-out-alt text-white" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                    <div style="background:#fff;padding:10px 22px 12px">
                        @php $leftPct = $totalToday > 0 ? round(($leftCount / $totalToday) * 100) : 0; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;border-radius:4px">
                                <div class="progress-bar" style="width:{{ $leftPct }}%;background:#10b981;border-radius:4px"></div>
                            </div>
                            <span style="font-size:11px;color:#888;white-space:nowrap">% {{ $leftPct }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- İçeridekiler / Dışarıdakiler --}}
    <div class="row g-3 mb-3">
        {{-- İÇERİDEKİLER --}}
        <div class="col-xl-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2 py-2"
                     style="background:linear-gradient(135deg,#fff3cd 0%,#fff9e6 100%);">
                    <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:34px;height:34px">
                        <i class="fas fa-building text-white small"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark">İçeridekiler</span>
                        <span class="ms-2 badge bg-warning text-dark rounded-pill">{{ $insideNow->count() }}</span>
                    </div>
                    <small class="text-muted ms-auto">Şu an binada</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead style="background:#fffbf0">
                                <tr>
                                    <th class="ps-3">Ziyaretçi</th>
                                    <th>Müdür / Departman</th>
                                    <th>Giriş</th>
                                    <th class="text-end pe-3">Süre</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($insideNow as $log)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">{{ $log->visitor_name }}</div>
                                        @if($log->visitor_company)
                                            <small class="text-muted"><i class="fas fa-building me-1 opacity-50"></i>{{ $log->visitor_company }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->host?->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $log->host?->department?->name ?? $log->department?->name ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $log->check_in_at->format('H:i') }}</span>
                                        <br><small class="text-muted">{{ $log->check_in_at->format('d.m') }}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        @php $mins = now()->diffInMinutes($log->check_in_at); @endphp
                                        <small class="text-muted">
                                            {{ intdiv($mins,60) > 0 ? intdiv($mins,60).'sa ' : '' }}{{ $mins%60 }}dk
                                        </small>
                                    </td>
                                    <td>
                                        <form action="{{ route('guest-logs.checkout', $log) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2"
                                                    title="Çıkış Yap"
                                                    onclick="return confirm('{{ $log->visitor_name }} için çıkış kaydedilsin mi?')">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                                        Şu an içeride kimse yok
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- DIŞARIDAKILER --}}
        <div class="col-xl-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2 py-2"
                     style="background:linear-gradient(135deg,#f0f0f0 0%,#fafafa 100%);">
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:34px;height:34px">
                        <i class="fas fa-user-clock text-white small"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark">Dışarıdakiler</span>
                        <span class="ms-2 badge bg-secondary rounded-pill">{{ $outsideManagers->count() }}</span>
                    </div>
                    <small class="text-muted ms-auto">Dept. Müdürleri</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead style="background:#f8f8f8">
                                <tr>
                                    <th class="ps-3">Ad Soyad</th>
                                    <th>Departman</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-end pe-3">Hızlı Kayıt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outsideManagers as $mgr)
                                @php
                                    $mgrLastLog = \App\Models\GuestLog::where('host_user_id', $mgr->id)
                                        ->whereDate('check_in_at', today())
                                        ->latest('check_in_at')->first();
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-secondary bg-opacity-15 d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width:32px;height:32px">
                                                <i class="fas fa-user text-secondary" style="font-size:12px"></i>
                                            </div>
                                            <span class="fw-semibold">{{ $mgr->name }}</span>
                                        </div>
                                    </td>
                                    <td><small class="text-muted">{{ $mgr->department?->name ?? '—' }}</small></td>
                                    <td class="text-center">
                                        @if($mgrLastLog)
                                            <span class="badge bg-success bg-opacity-75">
                                                <i class="fas fa-history me-1"></i>Son: {{ $mgrLastLog->check_in_at->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-50">Bugün girişi yok</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('guest-logs.create') }}?host_user_id={{ $mgr->id }}"
                                           class="btn btn-sm btn-outline-primary py-0 px-2" title="Bu Müdür İçin Ziyaretçi Ekle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-users text-warning fa-lg me-2"></i>
                                        Tüm müdürlerin yanında ziyaretçi var
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-auto"><span class="text-muted">—</span></div>
                <div class="col-auto">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                @if(count($branches) > 1)
                <div class="col-auto">
                    <select name="branch_id" class="form-select form-select-sm" style="min-width:130px">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto">
                    <select name="purpose" class="form-select form-select-sm" style="min-width:130px">
                        <option value="">Tüm Amaçlar</option>
                        @foreach(\App\Models\GuestLog::PURPOSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('purpose') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" style="min-width:120px">
                        <option value="">Tüm Durum</option>
                        <option value="inside" @selected(request('status') == 'inside')>İçeride</option>
                        <option value="left"   @selected(request('status') == 'left')>Çıkış Yaptı</option>
                    </select>
                </div>
                <div class="col">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="İsim, telefon, şirket..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm">Filtrele</button>
                    @if(request()->hasAny(['search','status','purpose','branch_id']) || request('date_from') !== today()->format('Y-m-d'))
                        <a href="{{ route('guest-logs.index') }}" class="btn btn-outline-secondary btn-sm">Temizle</a>
                    @endif
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('guest-logs.create') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-plus me-1"></i> Ziyaretçi Ekle
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ziyaretçi</th>
                            <th>Kime / Departman</th>
                            <th>Amaç</th>
                            <th>Giriş</th>
                            <th>Çıkış</th>
                            <th>Süre</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $log->visitor_name }}</div>
                                @if($log->visitor_company)
                                    <small class="text-muted"><i class="fas fa-building me-1"></i>{{ $log->visitor_company }}</small>
                                @endif
                                @if($log->visitor_phone)
                                    <small class="text-muted d-block"><i class="fas fa-phone me-1"></i>{{ $log->visitor_phone }}</small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $log->host?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $log->department?->name ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ \App\Models\GuestLog::PURPOSE_COLORS[$log->purpose] }}">
                                    {{ \App\Models\GuestLog::PURPOSES[$log->purpose] }}
                                </span>
                                @if($log->purpose_note)
                                    <small class="text-muted d-block">{{ Str::limit($log->purpose_note, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $log->check_in_at->format('H:i') }}<br><small class="text-muted">{{ $log->check_in_at->format('d.m.Y') }}</small></td>
                            <td>
                                @if($log->check_out_at)
                                    {{ $log->check_out_at->format('H:i') }}
                                @else
                                    <span class="badge bg-warning text-dark">İçeride</span>
                                @endif
                            </td>
                            <td>
                                @if($log->durationMinutes() !== null)
                                    @php $dur = $log->durationMinutes(); @endphp
                                    <small>{{ intdiv($dur,60) > 0 ? intdiv($dur,60).'sa ' : '' }}{{ $dur%60 }}dk</small>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($log->isInside())
                                <form action="{{ route('guest-logs.checkout', $log) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="Çıkış Yap" onclick="return confirm('Çıkış kaydedilsin mi?')">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('guest-logs.show', $log) }}" class="btn btn-sm btn-outline-primary" title="Detay">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('guest-logs.edit', $log) }}" class="btn btn-sm btn-outline-secondary" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('guest-logs.destroy', $log) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Bu kayıt silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Sil"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-id-badge fa-3x mb-3 d-block text-muted opacity-25"></i>
                            Bu tarih aralığında ziyaretçi kaydı yok.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="mt-3">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
