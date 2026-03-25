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
    <div class="row g-3 mb-4 mt-1">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#6366f1,#818cf8);box-shadow:0 4px 20px rgba(99,102,241,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-desktop text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['total'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">TOPLAM CİHAZ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#10b981,#34d399);box-shadow:0 4px 20px rgba(16,185,129,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-circle-check text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['online'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">ÇEVRİMİÇİ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#f59e0b,#fbbf24);box-shadow:0 4px 20px rgba(245,158,11,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-clock text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['recent'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">SON 24 SAAT</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#ef4444,#f87171);box-shadow:0 4px 20px rgba(239,68,68,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-shield-virus text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['av_issue'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">AV SORUNU</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#8b5cf6,#a78bfa);box-shadow:0 4px 20px rgba(139,92,246,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-hdd text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['disk_warn'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">DİSK UYARISI</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 h-100" style="border-radius:14px;background:linear-gradient(135deg,#0ea5e9,#38bdf8);box-shadow:0 4px 20px rgba(14,165,233,.25)">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,255,255,.2)">
                        <i class="fas fa-network-wired text-white" style="font-size:1.3rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold" style="font-size:1.75rem;line-height:1">{{ $stats['rdp_open'] }}</div>
                        <div class="text-white-50" style="font-size:.78rem;letter-spacing:.5px">RDP AÇIK</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre Kartı --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden">
        <div class="card-body p-4">
            <form method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-search me-1"></i>Arama
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-desktop text-muted"></i>
                            </span>
                            <input type="text" name="search" id="searchInput"
                                   value="{{ request('search') }}"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Hostname veya IP adresi...">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-sitemap me-1"></i>Domain
                        </label>
                        <select name="domain" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm Domain'ler</option>
                            @foreach($domains as $d)
                                <option value="{{ $d }}" @selected(request('domain') == $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fab fa-windows me-1"></i>İşletim Sistemi
                        </label>
                        <select name="os" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm İşletim Sistemleri</option>
                            @foreach($osList as $os)
                                <option value="{{ $os }}" @selected(request('os') == $os)>{{ $os }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <label class="form-label fw-semibold small mb-1 text-muted d-block">
                            <i class="fas fa-filter me-1"></i>Hızlı Filtreler
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="av_disabled" value="1"
                                       id="avFilter" @checked(request('av_disabled')) onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-danger" for="avFilter">
                                    <i class="fas fa-shield-virus me-1"></i>AV Kapalı
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="disk_warning" value="1"
                                       id="diskFilter" @checked(request('disk_warning')) onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-warning" for="diskFilter">
                                    <i class="fas fa-hdd me-1"></i>Disk Uyarısı
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="rdp_enabled" value="1"
                                       id="rdpFilter" @checked(request('rdp_enabled')) onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-info" for="rdpFilter">
                                    <i class="fas fa-network-wired me-1"></i>RDP Açık
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-search me-1"></i>Filtrele
                            </button>
                            @if(request()->hasAny(['search','domain','os','av_disabled','disk_warning','rdp_enabled']))
                            <a href="{{ route('it.agent.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Temizle
                            </a>
                            @endif
                        </div>
                        <a href="{{ route('it.agent.stats') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>İstatistikler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste Başlığı --}}
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold" style="font-size:1.05rem">
                <i class="fas fa-list me-2 text-primary"></i>Bilgisayar Listesi
            </span>
            <span class="badge rounded-pill bg-secondary fw-normal">
                {{ number_format($computers->total()) }} kayıt
            </span>
        </div>
        <span class="text-muted small">
            Sayfa {{ $computers->currentPage() }} / {{ $computers->lastPage() }}
            &nbsp;·&nbsp; Sayfada {{ $computers->count() }} kayıt
        </span>
    </div>

    {{-- Bilgisayar Kartları --}}
    <div class="d-flex flex-column gap-2">

        @forelse($computers as $c)
        @php
            $activeIp  = $c->networkAdapters->where('is_active', true)->first()?->ip_address ?? null;
            $avOk      = $c->antivirus->where('is_enabled', true)->isNotEmpty();
            $avExists  = $c->antivirus->isNotEmpty();
            $diskWarn  = $c->disks->contains(fn($d) => ($d->usage_percent ?? 0) > 85);
            $diskCount = $c->disks->count();

            $isOnline  = $c->last_seen_at && $c->last_seen_at->diffInMinutes(now()) < 10;
            $isRecent  = $c->last_seen_at && $c->last_seen_at->diffInHours(now()) < 24;

            if ($isOnline) {
                $dotColor = '#10b981'; $dotLabel = 'Çevrimiçi';
            } elseif ($isRecent) {
                $dotColor = '#f59e0b'; $dotLabel = 'Son 24 Saat';
            } else {
                $dotColor = '#cbd5e1'; $dotLabel = 'Çevrimdışı';
            }

            $os = $c->os_product_name ?? '';
            if (str_contains($os, '11'))       { $osIcon = '🪟'; $osColor = '#0078D4'; }
            elseif (str_contains($os, '10'))   { $osIcon = '🪟'; $osColor = '#0078D4'; }
            elseif (str_contains($os, 'Server')){ $osIcon = '🖥️'; $osColor = '#6366f1'; }
            else                               { $osIcon = '💻'; $osColor = '#64748b'; }
        @endphp

        <div class="card border-0 shadow-sm" style="border-radius:12px;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 4px 20px rgba(0,0,0,.10)'"
             onmouseleave="this.style.boxShadow=''">
            <div class="card-body py-3 px-4">
                <div class="row align-items-center g-3">

                    {{-- Durum göstergesi + Hostname --}}
                    <div class="col-lg-3 col-md-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative flex-shrink-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center bg-light"
                                     style="width:44px;height:44px">
                                    <i class="fas fa-desktop" style="font-size:1.1rem;color:{{ $osColor }}"></i>
                                </div>
                                <span class="position-absolute bottom-0 end-0 rounded-circle border border-white"
                                      style="width:11px;height:11px;background:{{ $dotColor }}"></span>
                            </div>
                            <div style="min-width:0">
                                <a href="{{ route('it.agent.show', $c) }}"
                                   class="fw-bold text-dark text-decoration-none d-block text-truncate"
                                   style="font-size:.92rem" title="{{ $c->hostname }}">
                                    {{ $c->hostname }}
                                </a>
                                <span class="text-muted" style="font-size:.72rem">{{ $dotLabel }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- İşletim Sistemi --}}
                    <div class="col-lg-3 col-md-4 d-none d-md-block">
                        <div class="text-muted" style="font-size:.7rem;letter-spacing:.4px;text-transform:uppercase">İşletim Sistemi</div>
                        <div style="font-size:.82rem;font-weight:500">
                            {{ $osIcon }} {{ $os ?: '—' }}
                        </div>
                    </div>

                    {{-- IP + Domain --}}
                    <div class="col-lg-2 d-none d-lg-block">
                        <div class="text-muted" style="font-size:.7rem;letter-spacing:.4px;text-transform:uppercase">IP / Domain</div>
                        @if($activeIp)
                            <code style="font-size:.78rem;background:#f1f5f9;padding:1px 6px;border-radius:5px;color:#334155">{{ $activeIp }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                        @if($c->is_domain_joined)
                            <div class="mt-1">
                                <span class="badge" style="background:#eff6ff;color:#3b82f6;border:1px solid #bfdbfe;font-size:.68rem">
                                    {{ $c->domain_name }}
                                </span>
                            </div>
                        @else
                            <div class="text-muted" style="font-size:.72rem">Workgroup</div>
                        @endif
                    </div>

                    {{-- Durum Rozetleri --}}
                    <div class="col-lg-2 d-none d-lg-flex align-items-center gap-1 flex-wrap">
                        @if($avExists)
                            @if($avOk)
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;font-size:.7rem">
                                    <i class="fas fa-shield-alt me-1"></i>AV Aktif
                                </span>
                            @else
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:.7rem">
                                    <i class="fas fa-shield-virus me-1"></i>AV Kapalı
                                </span>
                            @endif
                        @endif
                        @if($diskWarn)
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;font-size:.7rem">
                                <i class="fas fa-hdd me-1"></i>Disk Dolu
                            </span>
                        @endif
                    </div>

                    {{-- Son görülme --}}
                    <div class="col-lg-1 d-none d-lg-block text-center">
                        <div class="text-muted" style="font-size:.7rem;letter-spacing:.4px;text-transform:uppercase">Son Görülme</div>
                        <div style="font-size:.78rem;font-weight:500">
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
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('it.agent.show', $c) }}"
                           class="btn btn-sm d-flex align-items-center gap-1"
                           style="background:#f8faff;border:1px solid #e0e7ff;color:#4f46e5;border-radius:8px;font-size:.78rem;padding:5px 12px">
                            <i class="fas fa-eye"></i>
                            <span class="d-none d-sm-inline">Detay</span>
                        </a>
                        @if(auth()->user()->hasPermission('it_agent_inventory', 'delete'))
                        <button type="button"
                                class="btn btn-sm d-flex align-items-center"
                                style="background:#fff5f5;border:1px solid #fecaca;color:#dc2626;border-radius:8px;font-size:.78rem;padding:5px 10px"
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
        <div class="card border-0 shadow-sm" style="border-radius:14px">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size:3rem;opacity:.2">🖥️</div>
                <p class="text-muted mb-1 fw-semibold">Kayıt bulunamadı</p>
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

// Enter ile form submit
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); this.closest('form').submit(); }
});
</script>
@endpush
@endsection