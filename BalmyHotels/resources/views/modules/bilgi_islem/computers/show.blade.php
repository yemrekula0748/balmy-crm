@extends('layouts.default')

@section('title', $computer->name . ' — Güvenlik Detayı')

@section('content')
<div class="container-fluid pb-4">

{{-- Breadcrumb --}}
<div class="row page-titles mx-0">
    <div class="col-sm-6 p-md-0">
        <div class="welcome-text">
            <h4>{{ $computer->name }}</h4>
            <span>Bilgi İşlem — Bilgisayarlar — Detay</span>
        </div>
    </div>
    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ route('it.computers.index') }}">Bilgisayarlar</a></li>
            <li class="breadcrumb-item active">{{ $computer->name }}</li>
        </ol>
    </div>
</div>

{{-- Header kartı --}}
<div class="card border-0 shadow-sm mb-4"
     style="background:linear-gradient(135deg,#f8f9ff 0%,#eef0ff 100%);">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:#4361ee20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                         fill="none" stroke="#4361ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
            </div>
            <div class="col">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0 fw-bold">{{ $computer->name }}</h5>
                    @if($latestSnapshot && ($latestSnapshot->alert_count ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $latestSnapshot->alert_count }} Güvenlik Uyarısı
                        </span>
                    @elseif($latestSnapshot)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                            <i class="fas fa-shield-alt me-1"></i>Tehdit Yok
                        </span>
                    @endif
                </div>
                <div class="mt-1 d-flex flex-wrap gap-3">
                    @if($computer->ip_address)
                        <span class="text-muted small">
                            <i class="fas fa-network-wired me-1"></i>
                            <code style="background:#eef0ff;padding:1px 5px;border-radius:4px">{{ $computer->ip_address }}</code>
                        </span>
                    @endif
                    @if($computer->location)
                        <span class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>{{ $computer->location }}</span>
                    @endif
                    @if($computer->assigned_user)
                        <span class="text-muted small"><i class="fas fa-user me-1"></i>{{ $computer->assigned_user }}</span>
                    @endif
                    @if($computer->branch)
                        <span class="text-muted small"><i class="fas fa-building me-1"></i>{{ $computer->branch->name }}</span>
                    @endif
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                @if(auth()->user()->hasPermission('it_computers', 'edit'))
                <button class="btn btn-sm btn-outline-warning"
                        data-bs-toggle="modal" data-bs-target="#editComputerModal"
                        data-id="{{ $computer->id }}"
                        data-name="{{ $computer->name }}"
                        data-ip="{{ $computer->ip_address }}"
                        data-location="{{ $computer->location }}"
                        data-user="{{ $computer->assigned_user }}"
                        data-specs="{{ $computer->specs }}"
                        data-notes="{{ $computer->notes }}"
                        data-branch="{{ $computer->branch_id }}">
                    <i class="fas fa-edit me-1"></i>Düzenle
                </button>
                @endif
                <a href="{{ route('it.computers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Listeye Dön
                </a>
            </div>
        </div>
    </div>
</div>
{{-- /header --}}

{{-- Sekmeler --}}
<ul class="nav nav-tabs mb-3" id="computerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info"
                type="button" role="tab">
            <i class="fas fa-info-circle me-1"></i>Genel Bilgiler
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security"
                type="button" role="tab">
            <i class="fas fa-shield-virus me-1"></i>
            Güvenlik Tehditleri
            @if($latestSnapshot && ($latestSnapshot->alert_count ?? 0) > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $latestSnapshot->alert_count }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" id="computerTabsContent">

    {{-- ====== TAB: Genel Bilgiler ====== --}}
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-desktop me-2 text-primary"></i>Cihaz Bilgileri
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-normal">Bilgisayar Adı</dt>
                            <dd class="col-7 fw-semibold mb-2">{{ $computer->name }}</dd>

                            <dt class="col-5 text-muted fw-normal">IP Adresi</dt>
                            <dd class="col-7 mb-2">
                                @if($computer->ip_address)
                                    <code style="background:#eef0ff;color:#4361ee;padding:2px 7px;border-radius:4px">{{ $computer->ip_address }}</code>
                                @else <span class="text-muted">—</span>
                                @endif
                            </dd>

                            <dt class="col-5 text-muted fw-normal">Konum</dt>
                            <dd class="col-7 mb-2">{{ $computer->location ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Kullanıcı</dt>
                            <dd class="col-7 mb-2">{{ $computer->assigned_user ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Şube</dt>
                            <dd class="col-7 mb-2">{{ $computer->branch?->name ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Teknik Özellikler</dt>
                            <dd class="col-7 mb-2">{{ $computer->specs ?? '—' }}</dd>

                            @if($computer->notes)
                            <dt class="col-5 text-muted fw-normal">Notlar</dt>
                            <dd class="col-7 mb-0">{{ $computer->notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Güvenlik Özeti
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        @if(!$latestSnapshot)
                            <div class="text-center py-3">
                                <i class="fas fa-satellite-dish text-muted mb-2" style="font-size:2rem"></i>
                                <p class="text-muted small mb-0">Henüz güvenlik taraması alınmadı.<br>
                                Windows ajanının bu bilgisayarın IP adresiyle eşleşmesi gereklidir.</p>
                            </div>
                        @else
                            @php $snap = $latestSnapshot; @endphp
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                     style="width:52px;height:52px;background:{{ ($snap->alert_count??0)>0 ? '#fee2e2' : '#dcfce7' }}">
                                    <i class="fas fa-shield-{{ ($snap->alert_count??0)>0 ? 'virus' : 'alt' }}"
                                       style="font-size:1.4rem;color:{{ ($snap->alert_count??0)>0 ? '#dc2626' : '#16a34a' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:1.4rem;color:{{ ($snap->alert_count??0)>0 ? '#dc2626' : '#16a34a' }}">
                                        {{ $snap->alert_count ?? 0 }} Uyarı
                                    </div>
                                    <div class="text-muted small">Son güncelleme: {{ $snap->reported_at?->diffForHumans() ?? '—' }}</div>
                                </div>
                            </div>
                            <dl class="row mb-0 small">
                                <dt class="col-7 text-muted fw-normal">Defender Tehditleri</dt>
                                <dd class="col-5 mb-1">
                                    @php $defCount = count($snap->defender_threats ?? []); @endphp
                                    <span class="badge {{ $defCount > 0 ? 'bg-danger' : 'bg-success' }} bg-opacity-10 {{ $defCount > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $defCount }}
                                    </span>
                                </dd>
                                <dt class="col-7 text-muted fw-normal">Shadow Kopya</dt>
                                <dd class="col-5 mb-1">
                                    <span class="badge {{ ($snap->shadow_copy_count??0) == 0 ? 'bg-warning text-dark' : 'bg-success bg-opacity-10 text-success' }}">
                                        {{ $snap->shadow_copy_count ?? 0 }}
                                    </span>
                                </dd>
                                <dt class="col-7 text-muted fw-normal">Başarısız Giriş (1s)</dt>
                                <dd class="col-5 mb-1">
                                    <span class="badge {{ ($snap->failed_logins_1h??0) >= 10 ? 'bg-warning text-dark' : 'bg-light text-secondary border' }}">
                                        {{ $snap->failed_logins_1h ?? 0 }}
                                    </span>
                                </dd>
                                <dt class="col-7 text-muted fw-normal">Şüpheli Process</dt>
                                <dd class="col-5 mb-1">
                                    <span class="badge {{ count($snap->suspicious_processes ?? []) > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-secondary border' }}">
                                        {{ count($snap->suspicious_processes ?? []) }}
                                    </span>
                                </dd>
                                <dt class="col-7 text-muted fw-normal">Beklenmedik Port</dt>
                                <dd class="col-5 mb-1">
                                    @php $unknownPorts = collect($snap->open_ports ?? [])->where('is_well_known', false)->count(); @endphp
                                    <span class="badge {{ $unknownPorts > 0 ? 'bg-warning text-dark' : 'bg-light text-secondary border' }}">
                                        {{ $unknownPorts }}
                                    </span>
                                </dd>
                                <dt class="col-7 text-muted fw-normal">Yeni Servisler (24s)</dt>
                                <dd class="col-5 mb-0">
                                    @php $suspServices = collect($snap->new_services_24h ?? [])->where('suspicious', true)->count(); @endphp
                                    <span class="badge {{ $suspServices > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-secondary border' }}">
                                        {{ count($snap->new_services_24h ?? []) }}
                                        @if($suspServices > 0) <i class="fas fa-exclamation-triangle ms-1"></i> @endif
                                    </span>
                                </dd>
                            </dl>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- /tab: Genel --}}

    {{-- ====== TAB: Güvenlik Tehditleri ====== --}}
    <div class="tab-pane fade" id="security" role="tabpanel">

        @if(!$latestSnapshot)
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle me-2"></i>
            Bu bilgisayar için henüz güvenlik taraması verisi bulunmuyor.
            Windows ajanı <code>{{ $computer->ip_address ?? 'N/A' }}</code> IP'si üzerinden
            raporlama yaptığında bu sekme otomatik dolacaktır.
        </div>
        @else
        @php $snap = $latestSnapshot; @endphp

        {{-- Üst durum şeridi --}}
        <div class="card border-0 shadow-sm mb-4"
             style="border-left:4px solid {{ ($snap->alert_count??0) > 0 ? '#dc2626' : '#16a34a' }}!important">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if(($snap->alert_count ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size:13px">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $snap->alert_count }} Aktif Uyarı
                        </span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="font-size:13px">
                            <i class="fas fa-check-circle me-1"></i>Tehdit Tespit Edilmedi
                        </span>
                    @endif
                    <span class="text-muted small">
                        <i class="fas fa-clock me-1"></i>Son güncelleme: {{ $snap->reported_at?->format('d.m.Y H:i:s') ?? '—' }}
                        ({{ $snap->reported_at?->diffForHumans() ?? '' }})
                    </span>
                    @if($snap->agentComputer)
                        <a href="{{ route('it.agent.show', $snap->agentComputer) }}" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="fas fa-robot me-1"></i>Ajan Detayı: {{ $snap->agentComputer->hostname }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── 1. Defender Tehditleri ── --}}
        @if(!empty($snap->defender_threats))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="fas fa-virus me-2"></i>Defender Tehditleri
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->defender_threats) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">TEHDİT ADI</th>
                                <th class="py-2 small text-muted">ŞİDDET</th>
                                <th class="py-2 small text-muted">DURUM</th>
                                <th class="py-2 small text-muted">KAYNAK</th>
                                <th class="py-2 small text-muted pe-4">ETKİLENEN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->defender_threats as $threat)
                            @php
                                $sev = strtolower($threat['severity'] ?? '');
                                $sevColor = in_array($sev, ['critical','high']) ? 'danger' : ($sev === 'medium' ? 'warning' : 'secondary');
                            @endphp
                            <tr class="{{ ($threat['is_active'] ?? false) ? 'table-danger' : '' }}">
                                <td class="ps-4 small fw-semibold">
                                    <i class="fas fa-skull-crossbones me-1 text-danger"></i>{{ $threat['threat_name'] ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sevColor }} bg-opacity-10 text-{{ $sevColor }} border border-{{ $sevColor }} border-opacity-25">
                                        {{ $threat['severity'] ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($threat['is_active'] ?? false)
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Aktif</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">Temizlendi</span>
                                    @endif
                                    @if(!($threat['action_success'] ?? true))
                                        <span class="badge bg-warning bg-opacity-10 text-warning ms-1">İşlem Başarısız</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $threat['detection_source'] ?? '—' }}</td>
                                <td class="small text-muted pe-4"
                                    style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $threat['resources'] ?? '' }}">
                                    {{ $threat['resources'] ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 2 cols: Shadow / Başarısız giriş ── --}}
        <div class="row g-3 mb-3">
            {{-- Shadow Copy --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-copy me-2 text-primary"></i>Shadow Kopyalar
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        @if(($snap->shadow_copy_count ?? 0) == 0)
                            <div class="alert alert-warning border-0 py-2 px-3 mb-0 small">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Shadow kopya bulunamadı — Ransomware riski yüksek!
                            </div>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                <i class="fas fa-check me-1"></i>{{ $snap->shadow_copy_count }} kopya mevcut
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Başarısız Girişler --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-key me-2 text-warning"></i>Başarısız Girişler (son 1s)
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        @php $fl = $snap->failed_logins_1h ?? 0; @endphp
                        <div class="mb-2">
                            <span class="badge px-3 py-2 {{ $fl >= 10 ? 'bg-warning text-dark' : 'bg-light text-secondary border' }}" style="font-size:14px">
                                {{ $fl }} başarısız giriş
                            </span>
                        </div>
                        {{-- Top targets (stored inside full JSON if available) --}}
                        @if(!empty($snap->account_lockouts_1h))
                            <p class="small text-muted mb-0">Kilitlenme olayları aşağıda listeleniyor.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. Hesap Kilitlenmeleri ── --}}
        @if(!empty($snap->account_lockouts_1h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="fas fa-lock me-2"></i>Hesap Kilitlenmeleri (son 1s)
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->account_lockouts_1h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">KİLİTLENEN KULLANICI</th>
                                <th class="py-2 small text-muted pe-4">KAYNAK PC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->account_lockouts_1h as $lockout)
                            <tr>
                                <td class="ps-4 small text-nowrap">{{ $lockout['time'] ?? '—' }}</td>
                                <td class="small fw-semibold text-danger">{{ $lockout['locked_user'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $lockout['caller_pc'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 4. Şüpheli Processler ── --}}
        @if(!empty($snap->suspicious_processes))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="fas fa-bug me-2"></i>Şüpheli Processler
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->suspicious_processes) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">PID</th>
                                <th class="py-2 small text-muted">PROCESS ADI</th>
                                <th class="py-2 small text-muted">DOSYA YOLU</th>
                                <th class="py-2 small text-muted">KULLANICI</th>
                                <th class="py-2 small text-muted pe-4">BAŞLANGIÇ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->suspicious_processes as $proc)
                            <tr class="table-danger">
                                <td class="ps-4 small"><code>{{ $proc['pid'] ?? '—' }}</code></td>
                                <td class="small fw-semibold text-danger">
                                    <i class="fas fa-microchip me-1"></i>{{ $proc['name'] ?? '—' }}
                                </td>
                                <td class="small text-muted pe-4"
                                    style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $proc['exe'] ?? '' }}">{{ $proc['exe'] ?? '—' }}</td>
                                <td class="small">{{ $proc['username'] ?? '—' }}</td>
                                <td class="small text-muted text-nowrap pe-4">{{ $proc['started_at'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 5. Açık Portlar ── --}}
        @if(!empty($snap->open_ports))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-plug me-2 text-warning"></i>Açık Portlar
                </h6>
                @php $unknownCount = collect($snap->open_ports)->where('is_well_known', false)->count(); @endphp
                @if($unknownCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $unknownCount }} beklenmedik</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">PORT</th>
                                <th class="py-2 small text-muted">ADRES</th>
                                <th class="py-2 small text-muted">PID</th>
                                <th class="py-2 small text-muted">PROCESS</th>
                                <th class="py-2 small text-muted pe-4">DURUM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->open_ports as $port)
                            <tr class="{{ !($port['is_well_known'] ?? true) ? 'table-warning' : '' }}">
                                <td class="ps-4">
                                    <code class="fw-bold {{ !($port['is_well_known'] ?? true) ? 'text-danger' : '' }}">
                                        {{ $port['port'] ?? '—' }}
                                    </code>
                                </td>
                                <td class="small text-muted">{{ $port['address'] ?? '—' }}</td>
                                <td class="small"><code>{{ $port['pid'] ?? '—' }}</code></td>
                                <td class="small fw-semibold">{{ $port['process'] ?? '—' }}</td>
                                <td class="pe-4">
                                    @if(!($port['is_well_known'] ?? true))
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Beklenmedik
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Bilinen Port</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 6. USB Geçmişi ── --}}
        @if(!empty($snap->usb_history))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-usb me-2 text-secondary"></i>USB Geçmişi
                </h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill">{{ count($snap->usb_history) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">AYGIT ADI</th>
                                <th class="py-2 small text-muted">AYGIT ID</th>
                                <th class="py-2 small text-muted pe-4">TÜR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->usb_history as $usb)
                            <tr>
                                <td class="ps-4 small fw-semibold">
                                    <i class="fas fa-usb me-1 text-muted"></i>{{ $usb['friendly_name'] ?? '—' }}
                                </td>
                                <td class="small text-muted" style="font-size:.7rem">{{ $usb['device_id'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $usb['type'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 2 cols: SMB + Windows Update ── --}}
        <div class="row g-3 mb-3">
            {{-- SMB Signing --}}
            @if($snap->smb_signing)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-share-alt me-2 text-info"></i>SMB İmzalama
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        @php
                            $smb     = $snap->smb_signing;
                            $smbRisk = strtolower($smb['risk'] ?? '');
                            $smbColor = (str_contains($smbRisk,'yüksek') || str_contains($smbRisk,'yuksek'))
                                        ? 'danger' : (str_contains($smbRisk,'orta') ? 'warning' : 'success');
                        @endphp
                        <dl class="row mb-0 small">
                            <dt class="col-7 text-muted fw-normal">Sunucu İmzalama</dt>
                            <dd class="col-5 mb-2">
                                <span class="badge {{ ($smb['server_signing_required']??false) ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                    {{ ($smb['server_signing_required']??false) ? 'Zorunlu' : 'Zorunlu Değil' }}
                                </span>
                            </dd>
                            <dt class="col-7 text-muted fw-normal">İstemci İmzalama</dt>
                            <dd class="col-5 mb-2">
                                <span class="badge {{ ($smb['client_signing_required']??false) ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                    {{ ($smb['client_signing_required']??false) ? 'Zorunlu' : 'Zorunlu Değil' }}
                                </span>
                            </dd>
                            @if(isset($smb['risk']))
                            <dt class="col-7 text-muted fw-normal">Risk</dt>
                            <dd class="col-5 mb-0">
                                <span class="badge bg-{{ $smbColor }} bg-opacity-10 text-{{ $smbColor }} border border-{{ $smbColor }} border-opacity-25">
                                    {{ $smb['risk'] }}
                                </span>
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            @endif

            {{-- Windows Update --}}
            @if($snap->windows_update)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-0">
                            <i class="fab fa-windows me-2 text-primary"></i>Windows Update
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        @php
                            $wu      = $snap->windows_update;
                            $pending = $wu['pending_updates'] ?? 0;
                            $wuColor = $pending >= 30 ? 'danger' : ($pending >= 10 ? 'warning' : 'success');
                        @endphp
                        <dl class="row mb-0 small">
                            <dt class="col-7 text-muted fw-normal">Son Başarılı Kurulum</dt>
                            <dd class="col-5 mb-2">{{ $wu['last_success_install'] ?? '—' }}</dd>
                            <dt class="col-7 text-muted fw-normal">Bekleyen Güncelleme</dt>
                            <dd class="col-5 mb-2">
                                <span class="badge bg-{{ $wuColor }} bg-opacity-10 text-{{ $wuColor }} border border-{{ $wuColor }} border-opacity-25">
                                    {{ $pending }}
                                </span>
                            </dd>
                            @if(isset($wu['risk']))
                            <dt class="col-7 text-muted fw-normal">Risk</dt>
                            <dd class="col-5 mb-0">
                                <span class="text-{{ $wuColor }} fw-semibold">{{ $wu['risk'] }}</span>
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── 7. Yerel Adminler ── --}}
        @if(!empty($snap->local_admins))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-user-shield me-2 text-warning"></i>Yerel Adminler
                </h6>
                <span class="badge bg-warning text-dark rounded-pill">{{ count($snap->local_admins) }}</span>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($snap->local_admins as $admin)
                    <span class="badge px-3 py-2 {{ ($admin['is_domain']??false) ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' }}">
                        <i class="fas fa-user me-1"></i>{{ $admin['name'] ?? '—' }}
                        @if($admin['is_domain']??false)
                            <span class="ms-1 opacity-75">(Domain)</span>
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── 8. Yeni Servisler (24s) ── --}}
        @if(!empty($snap->new_services_24h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-cogs me-2 text-secondary"></i>Yeni Kurulu Servisler (son 24s)
                </h6>
                @php $suspSvc = collect($snap->new_services_24h)->where('suspicious', true)->count(); @endphp
                @if($suspSvc > 0)
                    <span class="badge bg-danger rounded-pill">{{ $suspSvc }} şüpheli</span>
                @endif
                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill">{{ count($snap->new_services_24h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">SERVİS ADI</th>
                                <th class="py-2 small text-muted">DOSYA YOLU</th>
                                <th class="py-2 small text-muted">TÜR</th>
                                <th class="py-2 small text-muted">HESAP</th>
                                <th class="py-2 small text-muted pe-4">DURUM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->new_services_24h as $svc)
                            <tr class="{{ ($svc['suspicious']??false) ? 'table-danger' : '' }}">
                                <td class="ps-4 small text-nowrap">{{ $svc['time'] ?? '—' }}</td>
                                <td class="small fw-semibold {{ ($svc['suspicious']??false) ? 'text-danger' : '' }}">
                                    {{ $svc['service_name'] ?? '—' }}
                                </td>
                                <td class="small text-muted"
                                    style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $svc['image_path'] ?? '' }}">{{ $svc['image_path'] ?? '—' }}</td>
                                <td class="small text-muted">{{ $svc['service_type'] ?? '—' }}</td>
                                <td class="small">{{ $svc['account'] ?? '—' }}</td>
                                <td class="pe-4">
                                    @if($svc['suspicious']??false)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Şüpheli
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 9. Hesap Değişiklikleri (24s) ── --}}
        @if(!empty($snap->account_changes_24h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-user-edit me-2 text-warning"></i>Hesap Değişiklikleri (son 24s)
                </h6>
                <span class="badge bg-warning text-dark rounded-pill">{{ count($snap->account_changes_24h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">OLAY ID</th>
                                <th class="py-2 small text-muted">AKSİYON</th>
                                <th class="py-2 small text-muted">HEDEF KULLANICI</th>
                                <th class="py-2 small text-muted pe-4">YAPAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->account_changes_24h as $change)
                            @php
                                $critical = in_array($change['event_id'] ?? 0, [4720, 4728, 4732, 4756]);
                            @endphp
                            <tr class="{{ $critical ? 'table-warning' : '' }}">
                                <td class="ps-4 small text-nowrap">{{ $change['time'] ?? '—' }}</td>
                                <td>
                                    <code class="{{ $critical ? 'text-danger fw-bold' : '' }}">{{ $change['event_id'] ?? '—' }}</code>
                                </td>
                                <td class="small fw-semibold {{ $critical ? 'text-danger' : '' }}">{{ $change['action'] ?? '—' }}</td>
                                <td class="small">{{ $change['target_user'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $change['subject_user'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 10. Beklenmedik Kapanmalar ── --}}
        @if(!empty($snap->unexpected_shutdowns))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-power-off me-2 text-danger"></i>Beklenmedik Kapanmalar
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->unexpected_shutdowns) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted pe-4">MESAJ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->unexpected_shutdowns as $sd)
                            <tr>
                                <td class="ps-4 small text-nowrap">{{ $sd['time'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $sd['message'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 11. RDP Olayları (24s) ── --}}
        @if($snap->rdp_events_24h)
        @php
            $rdp      = $snap->rdp_events_24h;
            $rdpAttempts = $rdp['attempts'] ?? [];
            $rdpSessions = $rdp['sessions'] ?? [];
        @endphp
        @if(!empty($rdpAttempts) || !empty($rdpSessions))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-desktop me-2 text-info"></i>RDP Olayları (son 24s)
                </h6>
                @if(!empty($rdpAttempts))
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill">
                        {{ count($rdpAttempts) }} deneme
                    </span>
                @endif
                @if(!empty($rdpSessions))
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">
                        {{ count($rdpSessions) }} oturum
                    </span>
                @endif
            </div>
            <div class="card-body px-4 py-3">
                @if(!empty($rdpAttempts))
                <p class="small fw-semibold text-muted text-uppercase mb-2">
                    <i class="fas fa-sign-in-alt me-1"></i>Giriş Denemeleri
                </p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-3 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">KULLANICI</th>
                                <th class="py-2 small text-muted">DOMAIN</th>
                                <th class="py-2 small text-muted pe-3">KAYNAK IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rdpAttempts as $att)
                            @php
                                $isExternal = !str_starts_with($att['source_ip'] ?? '', '192.168.')
                                           && !str_starts_with($att['source_ip'] ?? '', '10.')
                                           && !str_starts_with($att['source_ip'] ?? '', '172.');
                            @endphp
                            <tr class="{{ $isExternal ? 'table-warning' : '' }}">
                                <td class="ps-3 small text-nowrap">{{ $att['time'] ?? '—' }}</td>
                                <td class="small fw-semibold">{{ $att['user'] ?? '—' }}</td>
                                <td class="small text-muted">{{ $att['domain'] ?? '—' }}</td>
                                <td class="pe-3">
                                    <code class="small {{ $isExternal ? 'text-warning fw-bold' : '' }}">{{ $att['source_ip'] ?? '—' }}</code>
                                    @if($isExternal)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Dış IP</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if(!empty($rdpSessions))
                <p class="small fw-semibold text-muted text-uppercase mb-2">
                    <i class="fas fa-user-check me-1"></i>Oturumlar
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-3 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">DURUM</th>
                                <th class="py-2 small text-muted">KULLANICI</th>
                                <th class="py-2 small text-muted pe-3">KAYNAK IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rdpSessions as $sess)
                            <tr>
                                <td class="ps-3 small text-nowrap">{{ $sess['time'] ?? '—' }}</td>
                                <td class="small">{{ $sess['action'] ?? '—' }}</td>
                                <td class="small fw-semibold">{{ $sess['user'] ?? '—' }}</td>
                                <td class="small text-muted pe-3">{{ $sess['source_ip'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endif

        {{-- ── 12. Scheduled Tasks (24s) ── --}}
        @if(!empty($snap->scheduled_tasks_24h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-clock me-2 text-secondary"></i>Yeni Zamanlanmış Görevler (son 24s)
                </h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill">{{ count($snap->scheduled_tasks_24h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">OLAY ID</th>
                                <th class="py-2 small text-muted">GÖREV ADI</th>
                                <th class="py-2 small text-muted">AKSİYON</th>
                                <th class="py-2 small text-muted pe-4">KULLANICI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->scheduled_tasks_24h as $task)
                            <tr>
                                <td class="ps-4 small text-nowrap">{{ $task['time'] ?? '—' }}</td>
                                <td><code class="small">{{ $task['event_id'] ?? '—' }}</code></td>
                                <td class="small fw-semibold">{{ $task['task_name'] ?? '—' }}</td>
                                <td class="small">{{ $task['action'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $task['user'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 13. Admin Girişleri (1s) ── --}}
        @if(!empty($snap->admin_logins_1h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-user-lock me-2 text-danger"></i>Admin Girişleri (son 1s)
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->admin_logins_1h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">KULLANICI</th>
                                <th class="py-2 small text-muted pe-4">DOMAIN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->admin_logins_1h as $login)
                            <tr>
                                <td class="ps-4 small text-nowrap">{{ $login['time'] ?? '—' }}</td>
                                <td class="small fw-semibold text-danger">{{ $login['user'] ?? '—' }}</td>
                                <td class="small text-muted pe-4">{{ $login['domain'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ── 14. Servis Çökmeleri (24s) ── --}}
        @if(!empty($snap->service_crashes_24h))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-bomb me-2 text-danger"></i>Servis Çökmeleri (son 24s)
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($snap->service_crashes_24h) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-4 py-2 small text-muted">ZAMAN</th>
                                <th class="py-2 small text-muted">SERVİS ADI</th>
                                <th class="py-2 small text-muted pe-4">ÇÖKME SAYISI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snap->service_crashes_24h as $crash)
                            <tr>
                                <td class="ps-4 small text-nowrap">{{ $crash['time'] ?? '—' }}</td>
                                <td class="small fw-semibold">{{ $crash['service_name'] ?? '—' }}</td>
                                <td class="pe-4">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                        {{ $crash['crash_count'] ?? '—' }}x
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @endif
        {{-- /if latestSnapshot --}}

    </div>
    {{-- /tab: Güvenlik Tehditleri --}}

</div>
{{-- /tab-content --}}

</div>
{{-- /container-fluid --}}

{{-- EDIT MODAL (show sayfasında da kullanılabilir) --}}
@if(auth()->user()->hasPermission('it_computers', 'edit'))
<div class="modal fade" id="editComputerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#f97316,#fdba74);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-edit me-2"></i>Bilgisayar Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Bilgisayar Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">IP Adresi</label>
                            <input type="text" name="ip_address" id="edit_ip" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Konum</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Kullanıcı</label>
                            <input type="text" name="assigned_user" id="edit_user" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Şube</label>
                            <select name="branch_id" id="edit_branch" class="form-select">
                                <option value="">Şube Seçin</option>
                                @foreach($computer->branch ? [$computer->branch] : [] as $b)
                                    <option value="{{ $b->id }}" selected>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Teknik Özellikler</label>
                            <input type="text" name="specs" id="edit_specs" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px">Notlar</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i>Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editForm').action = '/bilgi-islem/bilgisayarlar/' + this.dataset.id;
        document.getElementById('edit_name').value     = this.dataset.name     || '';
        document.getElementById('edit_ip').value       = this.dataset.ip       || '';
        document.getElementById('edit_location').value = this.dataset.location || '';
        document.getElementById('edit_user').value     = this.dataset.user     || '';
        document.getElementById('edit_specs').value    = this.dataset.specs    || '';
        document.getElementById('edit_notes').value    = this.dataset.notes    || '';
        document.getElementById('edit_branch').value  = this.dataset.branch   || '';
    });
});
</script>
@endpush

@endsection
