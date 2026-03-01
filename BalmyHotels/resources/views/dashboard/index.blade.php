@extends('layouts.default')

@section('content')
@php
    $statusLabels = ['open' => 'Açık', 'in_progress' => 'İşlemde', 'resolved' => 'Çözüldü', 'closed' => 'Kapalı'];
    $statusColors = ['open' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
@endphp

<div class="container-fluid px-4">

    {{-- ═══════════════════════════════════════════
         HOŞGELDİN BANNER
    ════════════════════════════════════════════ --}}
    <div class="welcome-banner rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                Hoş geldin, <span style="color:#c19b77">{{ explode(' ', $user->name)[0] }}</span> 👋
            </h3>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @foreach($user->userRoles as $ur)
                    @php $roleObj = \App\Models\Role::where('name', $ur->role_name)->first(); @endphp
                    <span class="badge rounded-pill px-3 py-1"
                          style="background:{{ $roleObj->color ?? '#6c757d' }};font-size:12px">
                        {{ $roleObj->display_name ?? $ur->role_name }}
                    </span>
                @endforeach
                @if($user->branch)
                    <span class="text-muted small"><i class="fas fa-building me-1"></i>{{ $user->branch->name }}</span>
                @endif
                @if($user->department)
                    <span class="text-muted small"><i class="fas fa-sitemap me-1"></i>{{ $user->department->name }}</span>
                @endif
            </div>
        </div>
        <div class="text-end">
            <div class="fw-semibold text-muted small">{{ now()->format('d F Y, l') }}</div>
            <div id="clock" class="h5 fw-bold mb-0 text-dark" style="letter-spacing:2px"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         STAT KARTLARI
    ════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Arıza — açık --}}
        @if($user->hasPermission('faults', 'index') && isset($stats['faults_open']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#fff0f0;color:#dc3545">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-val text-danger">{{ $stats['faults_open'] }}</div>
                <div class="stat-lbl">Açık Arıza</div>
            </div>
        </div>

        {{-- Arıza — işlemde --}}
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#fff8e1;color:#fd7e14">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-val text-warning">{{ $stats['faults_inprogress'] }}</div>
                <div class="stat-lbl">İşlemdeki Arıza</div>
            </div>
        </div>

        {{-- Arıza — bugün çözülen --}}
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#e8f5e9;color:#198754">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-val text-success">{{ $stats['faults_resolved_today'] }}</div>
                <div class="stat-lbl">Bugün Çözülen</div>
            </div>
        </div>

        {{-- Arıza — toplam --}}
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#e3f2fd;color:#0d6efd">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-val text-primary">{{ $stats['faults_total'] }}</div>
                <div class="stat-lbl">Toplam Arıza</div>
            </div>
        </div>
        @endif

        {{-- Kapı Giriş --}}
        @if(!is_null($stats['door_today']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#f3e5f5;color:#9c27b0">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-val" style="color:#9c27b0">{{ $stats['door_today'] }}</div>
                <div class="stat-lbl">Bugün Giriş/Çıkış</div>
            </div>
        </div>
        @endif

        {{-- Misafir --}}
        @if(!is_null($stats['guest_today']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#e8f4f8;color:#0288d1">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-val" style="color:#0288d1">{{ $stats['guest_today'] }}</div>
                <div class="stat-lbl">Bugün Ziyaretçi</div>
            </div>
        </div>
        @endif

        {{-- Personel --}}
        @if(!is_null($stats['users_total']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-val" style="color:#2e7d32">{{ $stats['users_total'] }}</div>
                <div class="stat-lbl">Toplam Personel</div>
            </div>
        </div>
        @endif

        {{-- Araç --}}
        @if(!is_null($stats['vehicles_total']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#fce4ec;color:#c2185b">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-val" style="color:#c2185b">{{ $stats['vehicles_total'] }}</div>
                <div class="stat-lbl">Toplam Araç</div>
            </div>
        </div>
        @endif

        {{-- Demirbaş --}}
        @if(!is_null($stats['assets_total']))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="stat-val" style="color:#7b1fa2">{{ $stats['assets_total'] }}</div>
                <div class="stat-lbl">Toplam Demirbaş</div>
            </div>
        </div>
        @endif

        {{-- KENDİ arızaları — her kullanıcıya --}}
        @if($myFaultsTotal > 0 || $user->hasPermission('faults', 'create'))
        <div class="col-6 col-md-3">
            <div class="stat-card h-100" style="border-left: 3px solid #c19b77">
                <div class="stat-icon" style="background:#fdf5ec;color:#c19b77">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-val" style="color:#c19b77">{{ $myFaultsTotal }}</div>
                <div class="stat-lbl">Bildirdiğim Arıza</div>
            </div>
        </div>
        @endif

    </div>{{-- /stats row --}}

    <div class="row g-4">

        {{-- ═══════════════════════════════════════════
             BEKLEYEN / AÇIK ARIZALAR TABLOSU
        ════════════════════════════════════════════ --}}
        @if($user->hasPermission('faults', 'index') && $recentFaults->count() > 0)
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-bolt text-warning me-2"></i>Bekleyen Arızalar
                    </h6>
                    <a href="{{ route('faults.index') }}" class="btn btn-sm btn-outline-secondary">
                        Tümünü Gör <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Başlık</th>
                                    <th>Tür</th>
                                    <th>Şube</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFaults as $f)
                                <tr>
                                    <td class="ps-3 fw-semibold" style="max-width:180px">
                                        <span class="text-truncate d-block" style="max-width:180px" title="{{ $f->title }}">
                                            {{ $f->title }}
                                        </span>
                                        @if($f->reporter)
                                        <small class="text-muted">{{ $f->reporter->name }}</small>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ optional($f->faultType)->name ?? '—' }}</small></td>
                                    <td><small>{{ optional($f->branch)->name ?? '—' }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$f->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$f->status] ?? $f->status }}
                                        </span>
                                    </td>
                                    <td><small class="text-muted">{{ $f->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <a href="{{ route('faults.show', $f) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:11px">Gör</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════
             BİLDİRDİKLERİM + HIZLI İŞLEMLER
        ════════════════════════════════════════════ --}}
        <div class="col-12 col-xl-{{ ($user->hasPermission('faults','index') && $recentFaults->count() > 0) ? '4' : '6 mx-auto' }}">
            <div class="row g-4 h-100">

                {{-- Bildirdiklerim mini tablo --}}
                @if($myRecentFaults->count() > 0)
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-paper-plane me-2" style="color:#c19b77"></i>Son Bildirdiklerim
                            </h6>
                            <a href="{{ route('faults.my-reports') }}" class="btn btn-sm btn-outline-secondary">
                                Tümü <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($myRecentFaults as $f)
                                <li class="list-group-item px-3 py-2 d-flex align-items-center justify-content-between gap-2">
                                    <div style="min-width:0">
                                        <div class="fw-semibold text-truncate" style="font-size:13px">{{ $f->title }}</div>
                                        <small class="text-muted">{{ optional($f->faultType)->name ?? '' }} • {{ $f->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-{{ $statusColors[$f->status] ?? 'secondary' }} flex-shrink-0" style="font-size:10px">
                                        {{ $statusLabels[$f->status] ?? $f->status }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Hızlı İşlemler --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-rocket me-2 text-primary"></i>Hızlı İşlemler</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                @if($user->hasPermission('faults', 'create'))
                                <a href="{{ route('faults.create') }}" class="quick-btn" style="--qc:#dc3545">
                                    <i class="fas fa-plus-circle"></i> Arıza Bildir
                                </a>
                                @endif
                                @if($user->hasPermission('faults', 'index'))
                                <a href="{{ route('faults.index') }}" class="quick-btn" style="--qc:#fd7e14">
                                    <i class="fas fa-list"></i> Tüm Arızalar
                                </a>
                                @endif
                                @if($user->hasPermission('users', 'create'))
                                <a href="{{ route('users.create') }}" class="quick-btn" style="--qc:#198754">
                                    <i class="fas fa-user-plus"></i> Personel Ekle
                                </a>
                                @endif
                                @if($user->hasPermission('door_logs', 'index'))
                                <a href="{{ route('door-logs.index') }}" class="quick-btn" style="--qc:#9c27b0">
                                    <i class="fas fa-door-open"></i> Kapı Logları
                                </a>
                                @endif
                                @if($user->hasPermission('guest_logs', 'index'))
                                <a href="{{ route('guest-logs.index') }}" class="quick-btn" style="--qc:#0288d1">
                                    <i class="fas fa-user-friends"></i> Ziyaretçiler
                                </a>
                                @endif
                                @if($user->hasPermission('assets', 'index'))
                                <a href="{{ route('assets.index') }}" class="quick-btn" style="--qc:#7b1fa2">
                                    <i class="fas fa-cube"></i> Demirbaşlar
                                </a>
                                @endif
                                @if($user->hasPermission('vehicles', 'index'))
                                <a href="{{ route('vehicles.index') }}" class="quick-btn" style="--qc:#c2185b">
                                    <i class="fas fa-car"></i> Araçlar
                                </a>
                                @endif
                                @if($user->hasPermission('surveys', 'index'))
                                <a href="{{ route('surveys.index') }}" class="quick-btn" style="--qc:#f57c00">
                                    <i class="fas fa-poll"></i> Anketler
                                </a>
                                @endif
                                @if($user->hasPermission('qrmenus', 'index'))
                                <a href="{{ route('qrmenus.index') }}" class="quick-btn" style="--qc:#00796b">
                                    <i class="fas fa-qrcode"></i> QR Menü
                                </a>
                                @endif
                                @if($user->isSuperAdmin())
                                <a href="{{ route('roles.index') }}" class="quick-btn" style="--qc:#455a64">
                                    <i class="fas fa-lock"></i> Yetki Yönetimi
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- /main row --}}
</div>
@endsection

@push('styles')
<style>
/* ─── Welcome Banner ─── */
.welcome-banner {
    background: linear-gradient(135deg, #fff9f4 0%, #fef3e8 100%);
    border: 1px solid #f0e0d0;
}

/* ─── Stat Cards ─── */
.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: transform .2s, box-shadow .2s;
    border: 1px solid #f0f0f0;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.1);
}
.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 12px;
}
.stat-val {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.stat-lbl {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .5px;
}

/* ─── Quick Buttons ─── */
.quick-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--qc);
    background: color-mix(in srgb, var(--qc) 10%, white);
    border: 1.5px solid color-mix(in srgb, var(--qc) 25%, white);
    text-decoration: none;
    transition: all .15s ease;
}
.quick-btn:hover {
    background: var(--qc);
    color: #fff;
    border-color: var(--qc);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px color-mix(in srgb, var(--qc) 35%, transparent);
}

/* ─── Table tweak ─── */
.table > :not(caption) > * > * {
    padding-top: 10px;
    padding-bottom: 10px;
}
</style>
@endpush

@push('scripts')
<script>
(function tick() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('clock');
    if (el) el.textContent = `${h}:${m}:${s}`;
    setTimeout(tick, 1000);
})();
</script>
@endpush
