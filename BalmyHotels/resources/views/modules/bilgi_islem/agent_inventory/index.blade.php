@extends('layouts.default')

@section('title', 'Ajan Envanter')

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Ajan Envanter</h4>
                <span class="text-muted" style="font-size:.82rem">Bilgi İşlem — Windows Agent Bilgisayarları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Bilgi İşlem</span></li>
                <li class="breadcrumb-item active">Ajan Envanter</li>
            </ol>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 mt-2" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Özet Kartlar --}}
    <div class="row g-2 mb-3 mt-1">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#6366f1,#818cf8);box-shadow:0 2px 12px rgba(99,102,241,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-desktop text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['total'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">TOPLAM CİHAZ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#10b981,#34d399);box-shadow:0 2px 12px rgba(16,185,129,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-check text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['online'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">ÇEVRİMİÇİ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#f59e0b,#fbbf24);box-shadow:0 2px 12px rgba(245,158,11,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-clock text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['recent'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">SON 24 SAAT</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#ef4444,#f87171);box-shadow:0 2px 12px rgba(239,68,68,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-shield-virus text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['av_issue'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">AV SORUNU</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#a78bfa);box-shadow:0 2px 12px rgba(139,92,246,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-hdd text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['disk_warn'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">DİSK UYARISI</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#38bdf8);box-shadow:0 2px 12px rgba(14,165,233,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-network-wired text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $stats['rdp_open'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">RDP AÇIK</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre Kartı --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
        <div class="card-body py-3 px-4">
            <form method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted" style="font-size:.8rem">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" id="searchInput"
                                   value="{{ request('search') }}"
                                   class="form-control form-control-sm border-start-0 ps-0"
                                   placeholder="Hostname veya IP...">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <select name="domain" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm Domain'ler</option>
                            @foreach($domains as $d)
                                <option value="{{ $d }}" @selected(request('domain') == $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6">
                        <select name="os" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm İşletim Sistemleri</option>
                            @foreach($osList as $os)
                                <option value="{{ $os }}" @selected(request('os') == $os)>{{ $os }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12 d-flex align-items-center flex-wrap gap-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="av_disabled" value="1"
                                   id="avFilter" @checked(request('av_disabled')) onchange="this.form.submit()">
                            <label class="form-check-label small text-danger fw-semibold" for="avFilter">AV Kapalı</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="disk_warning" value="1"
                                   id="diskFilter" @checked(request('disk_warning')) onchange="this.form.submit()">
                            <label class="form-check-label small text-warning fw-semibold" for="diskFilter">Disk Uyarısı</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="rdp_enabled" value="1"
                                   id="rdpFilter" @checked(request('rdp_enabled')) onchange="this.form.submit()">
                            <label class="form-check-label small text-info fw-semibold" for="rdpFilter">RDP Açık</label>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fas fa-search me-1"></i>Ara
                            </button>
                            @if(request()->hasAny(['search','domain','os','av_disabled','disk_warning','rdp_enabled']))
                            <a href="{{ route('it.agent.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                            <a href="{{ route('it.agent.stats') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chart-bar me-1"></i>İstatistikler
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste Başlığı --}}
    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold text-dark" style="font-size:.9rem">
                <i class="fas fa-list me-1 text-primary opacity-75"></i>Bilgisayar Listesi
            </span>
            <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.7rem">
                {{ number_format($computers->total()) }} kayıt
            </span>
        </div>
        <span class="text-muted" style="font-size:.72rem">
            Sayfa {{ $computers->currentPage() }} / {{ $computers->lastPage() }}
        </span>
    </div>

    {{-- Bilgisayar Kartları --}}
    <div class="d-flex flex-column gap-2">

        @forelse($computers as $c)
        @php
            $hw        = $c->hardware;
            $activeIp  = $c->networkAdapters->where('is_active', true)->first()?->ip_address ?? null;
            $avOk      = $c->antivirus->where('is_enabled', true)->isNotEmpty();
            $avExists  = $c->antivirus->isNotEmpty();
            $diskWarn  = $c->disks->contains(fn($d) => ($d->usage_percent ?? 0) > 85);

            $isOnline  = $c->last_seen_at && $c->last_seen_at->diffInMinutes(now()) < 10;
            $isRecent  = $c->last_seen_at && $c->last_seen_at->diffInHours(now()) < 24;

            if ($isOnline)      { $dotColor = '#10b981'; $dotLabel = 'Çevrimiçi'; }
            elseif ($isRecent)  { $dotColor = '#f59e0b'; $dotLabel = 'Son 24s'; }
            else                { $dotColor = '#d1d5db'; $dotLabel = 'Çevrimdışı'; }

            $cpu    = $hw?->cpu_usage_percent ?? null;
            $ram    = $hw?->ram_usage_percent ?? null;
            $ramTot = $hw?->total_ram_gb ?? null;
            $ramAv  = $hw?->available_ram_gb ?? null;

            $cpuColor = $cpu >= 90 ? '#ef4444' : ($cpu >= 70 ? '#f59e0b' : '#10b981');
            $ramColor = $ram >= 90 ? '#ef4444' : ($ram >= 70 ? '#f59e0b' : '#6366f1');

            $os = $c->os_product_name ?? '';
            if (str_contains($os, 'Server')) { $osIcon = 'fas fa-server';  $osColor = '#6366f1'; }
            elseif (str_contains($os, '11')) { $osIcon = 'fab fa-windows'; $osColor = '#0078D4'; }
            else                             { $osIcon = 'fab fa-windows'; $osColor = '#0078D4'; }
        @endphp

        <div class="card border-0 shadow-sm" style="border-radius:10px;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 3px 16px rgba(0,0,0,.09)'"
             onmouseleave="this.style.boxShadow=''">
            <div class="card-body py-2 px-3">
                <div class="row align-items-center g-2">

                    {{-- İkon + Hostname + Durum --}}
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="position-relative flex-shrink-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;background:#f8faff;border:1px solid #e0e7ff">
                                    <i class="{{ $osIcon }}" style="font-size:.9rem;color:{{ $osColor }}"></i>
                                </div>
                                <span class="position-absolute bottom-0 end-0 rounded-circle border-2 border border-white"
                                      style="width:9px;height:9px;background:{{ $dotColor }}"></span>
                            </div>
                            <div style="min-width:0">
                                <a href="{{ route('it.agent.show', $c) }}"
                                   class="fw-semibold text-dark text-decoration-none d-block text-truncate"
                                   style="font-size:.83rem" title="{{ $c->hostname }}">{{ $c->hostname }}</a>
                                <span style="font-size:.68rem;color:{{ $dotColor }}">{{ $dotLabel }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- CPU + RAM Bar --}}
                    <div class="col-xl-3 col-lg-3 d-none d-lg-block">
                        @if($hw)
                        <div class="d-flex flex-column gap-1">
                            {{-- CPU --}}
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:.66rem;width:26px;flex-shrink:0">CPU</span>
                                <div class="flex-grow-1 rounded-pill" style="height:5px;background:#f1f5f9;overflow:hidden">
                                    <div class="h-100 rounded-pill" style="width:{{ min($cpu ?? 0, 100) }}%;background:{{ $cpuColor }};transition:width .3s"></div>
                                </div>
                                <span style="font-size:.68rem;width:30px;text-align:right;color:{{ $cpuColor }};font-weight:600">
                                    {{ $cpu !== null ? $cpu.'%' : '—' }}
                                </span>
                            </div>
                            {{-- RAM --}}
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:.66rem;width:26px;flex-shrink:0">RAM</span>
                                <div class="flex-grow-1 rounded-pill" style="height:5px;background:#f1f5f9;overflow:hidden">
                                    <div class="h-100 rounded-pill" style="width:{{ min($ram ?? 0, 100) }}%;background:{{ $ramColor }};transition:width .3s"></div>
                                </div>
                                <span style="font-size:.68rem;width:30px;text-align:right;color:{{ $ramColor }};font-weight:600">
                                    {{ $ram !== null ? $ram.'%' : '—' }}
                                </span>
                            </div>
                            @if($ramTot)
                            <div style="font-size:.66rem;color:#94a3b8;padding-left:34px">
                                {{ $ramTot }} GB toplam
                                @if($ramAv) · {{ number_format($ramAv, 1) }} GB boş @endif
                            </div>
                            @endif
                        </div>
                        @else
                        <span class="text-muted" style="font-size:.72rem">Donanım verisi yok</span>
                        @endif
                    </div>

                    {{-- İşletim Sistemi --}}
                    <div class="col-xl-2 col-lg-2 d-none d-lg-block">
                        <div class="text-muted" style="font-size:.65rem;letter-spacing:.3px;text-transform:uppercase">İşletim Sistemi</div>
                        <div class="text-truncate" style="font-size:.78rem;font-weight:500;max-width:160px" title="{{ $os }}">
                            {{ $os ?: '—' }}
                        </div>
                    </div>

                    {{-- IP + Domain --}}
                    <div class="col-xl-2 col-lg-2 d-none d-lg-block">
                        <div class="text-muted" style="font-size:.65rem;letter-spacing:.3px;text-transform:uppercase">IP / Domain</div>
                        @if($activeIp)
                            <code style="font-size:.75rem;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#334155">{{ $activeIp }}</code>
                        @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                        @endif
                        <div class="mt-1">
                            @if($c->is_domain_joined)
                                <span style="font-size:.66rem;background:#eff6ff;color:#3b82f6;border:1px solid #bfdbfe;border-radius:4px;padding:1px 5px">
                                    {{ $c->domain_name }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size:.68rem">Workgroup</span>
                            @endif
                        </div>
                    </div>

                    {{-- Durum Rozetleri --}}
                    <div class="col-xl-1 col-lg-1 d-none d-xl-flex flex-column gap-1">
                        @if($avExists)
                            @if($avOk)
                                <span style="font-size:.65rem;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:5px;padding:2px 6px;white-space:nowrap">
                                    <i class="fas fa-shield-alt me-1"></i>AV Aktif
                                </span>
                            @else
                                <span style="font-size:.65rem;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:5px;padding:2px 6px;white-space:nowrap">
                                    <i class="fas fa-shield-virus me-1"></i>AV Kapalı
                                </span>
                            @endif
                        @endif
                        @if($diskWarn)
                            <span style="font-size:.65rem;background:#fffbeb;color:#d97706;border:1px solid #fde68a;border-radius:5px;padding:2px 6px;white-space:nowrap">
                                <i class="fas fa-hdd me-1"></i>Disk Dolu
                            </span>
                        @endif
                    </div>

                    {{-- Son görülme --}}
                    <div class="col-auto d-none d-xl-block">
                        <div class="text-muted" style="font-size:.65rem;letter-spacing:.3px;text-transform:uppercase">Son Görülme</div>
                        <div style="font-size:.75rem;font-weight:500;color:#475569">
                            @if($c->last_seen_at)
                                <span title="{{ $c->last_seen_at->format('d.m.Y H:i') }}">
                                    {{ $c->last_seen_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    {{-- Aksiyon --}}
                    <div class="col-auto ms-auto d-flex gap-1">
                        <a href="{{ route('it.agent.show', $c) }}"
                           class="btn btn-sm d-inline-flex align-items-center gap-1"
                           style="background:#f8faff;border:1px solid #e0e7ff;color:#4f46e5;border-radius:7px;font-size:.75rem;padding:4px 10px">
                            <i class="fas fa-eye" style="font-size:.7rem"></i>
                            <span class="d-none d-sm-inline">Detay</span>
                        </a>
                        @if(auth()->user()->hasPermission('it_agent_inventory', 'delete'))
                        <button type="button"
                                class="btn btn-sm d-inline-flex align-items-center"
                                style="background:#fff5f5;border:1px solid #fecaca;color:#dc2626;border-radius:7px;font-size:.7rem;padding:4px 8px"
                                onclick="confirmDelete({{ $c->id }}, '{{ addslashes($c->hostname) }}')"
                                title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        @empty
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body text-center py-5">
                <div class="mb-2 text-muted" style="font-size:2.5rem;opacity:.2"><i class="fas fa-desktop"></i></div>
                <p class="text-muted fw-semibold mb-1">Kayıt bulunamadı</p>
                @if(request()->hasAny(['search','domain','os','av_disabled','disk_warning','rdp_enabled']))
                    <p class="text-muted small mb-2">Filtreleri temizlemeyi deneyin.</p>
                    <a href="{{ route('it.agent.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Filtreleri Temizle
                    </a>
                @else
                    <p class="text-muted small mb-0">Henüz agent bağlanmamış.</p>
                @endif
            </div>
        </div>
        @endforelse

    </div>

    {{-- Pagination --}}
    @if($computers->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $computers->links() }}
    </div>
    @endif

</div>

{{-- Silme formu --}}
<form id="deleteForm" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(id, hostname) {
    if (!confirm('«' + hostname + '» kaydı silinecek. Emin misiniz?')) return;
    const form = document.getElementById('deleteForm');
    form.action = '/bilgi-islem/ajan-envanter/' + id;
    form.submit();
}
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); this.closest('form').submit(); }
});
</script>
@endpush
@endsection