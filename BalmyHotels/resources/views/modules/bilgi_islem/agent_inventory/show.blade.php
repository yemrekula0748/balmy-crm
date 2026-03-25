@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Detay')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Bilgi İşlem — Ajan Envanter — Detay</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item active">{{ $agentComputer->hostname }}</li>
            </ol>
        </div>
    </div>

    {{-- Üst bilgi kartı --}}
    <div class="card border-0 shadow-sm mb-4"
         style="background:linear-gradient(135deg,#f8f9ff 0%,#eef0ff 100%);">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:#4361ee20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="#4361ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                </div>
                <div class="col">
                    <h5 class="mb-0 fw-bold">{{ $agentComputer->hostname }}</h5>
                    <span class="text-muted small">{{ $agentComputer->os_product_name ?? 'İşletim sistemi bilinmiyor' }}
                        @if($agentComputer->os_build_number)
                            <span class="text-muted">(Build {{ $agentComputer->os_build_number }})</span>
                        @endif
                    </span>
                </div>
                <div class="col-auto d-flex gap-2 flex-wrap">
                    @if($agentComputer->is_domain_joined)
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="fas fa-network-wired me-1"></i>{{ $agentComputer->domain_name }}
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                            Workgroup{{ $agentComputer->workgroup_name ? ': '.$agentComputer->workgroup_name : '' }}
                        </span>
                    @endif
                    @if($agentComputer->last_seen_at)
                        @php $minsAgo = $agentComputer->last_seen_at->diffInMinutes(now()); @endphp
                        <span class="badge px-3 py-2 {{ $minsAgo < 10 ? 'bg-success bg-opacity-10 text-success' : ($minsAgo < 1440 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger') }}">
                            <i class="fas fa-circle me-1" style="font-size:8px"></i>
                            Son: {{ $agentComputer->last_seen_at->diffForHumans() }}
                        </span>
                    @endif
                    <span class="badge bg-light text-muted px-3 py-2">
                        Agent {{ $agentComputer->agent_version ?? 'N/A' }}
                    </span>
                </div>
                <div class="col-auto">
                    <a href="{{ route('it.agent.programs', $agentComputer) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Kurulu Programlar
                        <span class="badge bg-primary rounded-pill ms-1">{{ $programCount }}</span>
                    </a>
                    <a href="{{ route('it.agent.file-events', $agentComputer) }}"
                       class="btn btn-sm btn-outline-danger ms-1">
                        <i class="fas fa-trash-alt me-1"></i>Dosya Silme Logları
                    </a>
                    <a href="{{ route('it.agent.browser-history', $agentComputer) }}"
                       class="btn btn-sm btn-outline-info ms-1">
                        <i class="fas fa-globe me-1"></i>Tarayıcı Geçmişi
                    </a>
                    <a href="{{ route('it.agent.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                        <i class="fas fa-arrow-left me-1"></i>Listeye Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ---- İşletim Sistemi ---- --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-desktop me-2 text-primary"></i>İşletim Sistemi
                    </h6>
                </div>
                <div class="card-body px-4 py-3">
                    @php
                        $osRows = [
                            'Ürün Adı'        => $agentComputer->os_product_name,
                            'Versiyon'        => $agentComputer->os_version,
                            'Sürüm'           => $agentComputer->os_release,
                            'Build'           => $agentComputer->os_build_number,
                            'Mimari'          => $agentComputer->os_architecture,
                            'Kurulum Tarihi'  => $agentComputer->os_install_date?->format('d.m.Y'),
                            'Kayıtlı Kullanıcı' => $agentComputer->os_registered_owner,
                            'Seri No'         => $agentComputer->os_serial_number,
                            'Son Açılış'      => $agentComputer->last_boot_time?->format('d.m.Y H:i'),
                        ];
                    @endphp
                    <dl class="row mb-0 small">
                        @foreach($osRows as $label => $value)
                        @if($value)
                        <dt class="col-5 text-muted fw-normal">{{ $label }}</dt>
                        <dd class="col-7 fw-semibold mb-1">{{ $value }}</dd>
                        @endif
                        @endforeach
                        @if($agentComputer->current_users)
                        <dt class="col-5 text-muted fw-normal">Aktif Kullanıcılar</dt>
                        <dd class="col-7 mb-1">
                            @foreach($agentComputer->current_users as $u)
                                <span class="badge bg-light text-dark border me-1">{{ $u }}</span>
                            @endforeach
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- ---- Donanım ---- --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-microchip me-2 text-warning"></i>Donanım
                    </h6>
                </div>
                <div class="card-body px-4 py-3">
                    @if($agentComputer->hardware)
                    @php $hw = $agentComputer->hardware; @endphp
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">İşlemci</dt>
                        <dd class="col-7 fw-semibold mb-1">{{ $hw->cpu_name ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Çekirdek (F/L)</dt>
                        <dd class="col-7 mb-1">{{ $hw->cpu_cores_physical ?? '—' }} Fiziksel / {{ $hw->cpu_cores_logical ?? '—' }} Mantıksal</dd>

                        <dt class="col-5 text-muted fw-normal">CPU Hız</dt>
                        <dd class="col-7 mb-1">{{ $hw->cpu_speed_mhz ? number_format($hw->cpu_speed_mhz).' MHz' : '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">CPU Kullanım</dt>
                        <dd class="col-7 mb-1">
                            @if($hw->cpu_usage_percent !== null)
                            <div class="progress" style="height:8px;width:80px;display:inline-flex">
                                <div class="progress-bar {{ $hw->cpu_usage_percent > 80 ? 'bg-danger' : 'bg-primary' }}"
                                     style="width:{{ $hw->cpu_usage_percent }}%"></div>
                            </div>
                            <span class="ms-1">{{ round($hw->cpu_usage_percent) }}%</span>
                            @endif
                        </dd>

                        <dt class="col-5 text-muted fw-normal">RAM</dt>
                        <dd class="col-7 mb-1">
                            {{ $hw->total_ram_gb ? round($hw->total_ram_gb, 1).' GB' : '—' }}
                            @if($hw->ram_usage_percent !== null)
                                <span class="text-muted">/ {{ round($hw->ram_usage_percent) }}% kullanımda</span>
                            @endif
                        </dd>

                        @if($hw->ram_slots)
                        <dt class="col-5 text-muted fw-normal">RAM Slotlar</dt>
                        <dd class="col-7 mb-1">
                            @foreach($hw->ram_slots as $slot)
                                <span class="badge bg-light text-dark border me-1 mb-1">
                                    {{ $slot['capacity_gb'] ?? '' }}GB
                                    @if(!empty($slot['speed_mhz'])) {{ $slot['speed_mhz'] }}MHz @endif
                                    @if(!empty($slot['manufacturer'])) — {{ $slot['manufacturer'] }} @endif
                                </span>
                            @endforeach
                        </dd>
                        @endif

                        <dt class="col-5 text-muted fw-normal">Anakart</dt>
                        <dd class="col-7 mb-1">{{ $hw->motherboard ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">BIOS</dt>
                        <dd class="col-7 mb-1">{{ $hw->bios_version ?? '—' }}{{ $hw->bios_date ? ' ('.$hw->bios_date.')' : '' }}</dd>
                    </dl>
                    @else
                        <p class="text-muted small mb-0">Donanım bilgisi henüz alınmadı.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ---- Ağ Adaptörleri ---- --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-network-wired me-2 text-info"></i>Ağ Adaptörleri
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="ps-4 py-2 small text-muted">ADAPTÖR</th>
                                    <th class="py-2 small text-muted">MAC</th>
                                    <th class="py-2 small text-muted">IP (v4)</th>
                                    <th class="py-2 small text-muted">IP (v6)</th>
                                    <th class="py-2 small text-muted">GATEWAY</th>
                                    <th class="py-2 small text-muted">DNS</th>
                                    <th class="py-2 small text-muted">DHCP</th>
                                    <th class="py-2 small text-muted">DURUM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agentComputer->networkAdapters as $adapter)
                                <tr>
                                    <td class="ps-4 small fw-semibold">{{ $adapter->adapter_name }}</td>
                                    <td><code class="small">{{ $adapter->mac_address ?? '—' }}</code></td>
                                    <td>
                                        @if($adapter->ip_address)
                                            <code class="small" style="background:#f1f5f9;padding:2px 6px;border-radius:4px">{{ $adapter->ip_address }}</code>
                                        @else —
                                        @endif
                                    </td>
                                    <td><span class="small text-muted">{{ $adapter->ip_address_v6 ?? '—' }}</span></td>
                                    <td><span class="small">{{ $adapter->gateway ?? '—' }}</span></td>
                                    <td>
                                        @if($adapter->dns_servers)
                                            @foreach($adapter->dns_servers as $dns)
                                                <span class="badge bg-light text-dark border me-1">{{ $dns }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $adapter->dhcp_enabled ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                            {{ $adapter->dhcp_enabled ? 'DHCP' : 'Statik' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $adapter->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $adapter->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-3 small">Ağ adaptörü bulunamadı.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ---- Diskler ---- --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-hdd me-2 text-success"></i>Disk Sürücüleri
                    </h6>
                </div>
                <div class="card-body py-3 px-4">
                    @forelse($agentComputer->disks as $disk)
                    @php $danger = ($disk->usage_percent ?? 0) > 85; $warn = ($disk->usage_percent ?? 0) > 70; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">
                                {{ $disk->drive_letter }}
                                @if($disk->label) <span class="text-muted">({{ $disk->label }})</span> @endif
                            </span>
                            <span class="small {{ $danger ? 'text-danger' : ($warn ? 'text-warning' : 'text-muted') }}">
                                {{ $disk->free_space_gb !== null ? round($disk->free_space_gb, 1).' GB boş' : '' }}
                                / {{ $disk->total_space_gb !== null ? round($disk->total_space_gb, 1).' GB' : '?' }}
                            </span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar {{ $danger ? 'bg-danger' : ($warn ? 'bg-warning' : 'bg-success') }}"
                                 style="width:{{ $disk->usage_percent ?? 0 }}%"></div>
                        </div>
                        <span class="small text-muted">{{ $disk->filesystem }} • {{ $disk->usage_percent !== null ? round($disk->usage_percent).'%' : '' }} kullanımda</span>
                    </div>
                    @empty
                        <p class="text-muted small mb-0">Disk bilgisi yok.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ---- Güvenlik ---- --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-shield-alt me-2 text-danger"></i>Güvenlik
                    </h6>
                </div>
                <div class="card-body px-4 py-3">
                    @if($agentComputer->security)
                    @php $s = $agentComputer->security; @endphp
                    <div class="row g-2 mb-3">
                        @php
                            $checks = [
                                ['fa-desktop', 'RDP', $s->rdp_enabled, false],
                                ['fa-user-shield', 'UAC', $s->uac_enabled, true],
                                ['fa-fire', 'Firewall (Domain)', $s->firewall_domain, true],
                                ['fa-fire', 'Firewall (Private)', $s->firewall_private, true],
                                ['fa-fire', 'Firewall (Public)', $s->firewall_public, true],
                            ];
                        @endphp
                        @foreach($checks as [$icon, $label, $val, $goodWhenTrue])
                        @if($val !== null)
                        <div class="col-auto">
                            <span class="badge px-3 py-2
                                {{ ($goodWhenTrue ? $val : !$val) ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                <i class="fas {{ $icon }} me-1"></i>{{ $label }}:
                                {{ $val ? 'Açık' : 'Kapalı' }}
                            </span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <dl class="row small mb-0">
                        <dt class="col-6 text-muted fw-normal">Windows Update</dt>
                        <dd class="col-6 mb-1">
                            @php
                                $uaLabels = [1=>'Devre Dışı',2=>'Bildir',3=>'İndir',4=>'Otomatik Kur'];
                            @endphp
                            {{ $uaLabels[$s->auto_update] ?? '?' }}
                        </dd>
                        <dt class="col-6 text-muted fw-normal">Son Win. Güncelleme</dt>
                        <dd class="col-6 mb-1">{{ $s->last_windows_update ?? '—' }}</dd>
                        @if($s->bitlocker)
                        <dt class="col-6 text-muted fw-normal">BitLocker</dt>
                        <dd class="col-6 mb-1">
                            @foreach($s->bitlocker as $drive => $bl)
                                <span class="badge {{ ($bl['is_encrypted'] ?? false) ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }} me-1">
                                    {{ $drive }}: {{ ($bl['is_encrypted'] ?? false) ? 'Şifreli' : 'Şifresiz' }}
                                </span>
                            @endforeach
                        </dd>
                        @endif
                    </dl>
                    @else
                        <p class="text-muted small mb-0">Güvenlik bilgisi henüz alınmadı.</p>
                    @endif

                    {{-- Antivirüs --}}
                    <hr class="my-3">
                    <h6 class="small fw-semibold mb-2 text-muted">ANTİVİRÜS</h6>
                    @forelse($agentComputer->antivirus as $av)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $av->is_enabled ? 'bg-success' : 'bg-danger' }} rounded-circle p-1" style="width:10px;height:10px"></span>
                        <span class="small fw-semibold">{{ $av->product_name }}</span>
                        @if(!$av->is_up_to_date)
                            <span class="badge bg-warning bg-opacity-10 text-warning small">Güncelleme Yok</span>
                        @endif
                    </div>
                    @empty
                        <p class="text-muted small mb-0">Antivirüs bilgisi yok.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ---- Mail / Outlook ---- --}}
        @if($agentComputer->mail || $agentComputer->mailAccounts->isNotEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>Mail / Outlook
                    </h6>
                </div>
                <div class="card-body px-4 py-3">

                    @if($agentComputer->mail)
                    @php $m = $agentComputer->mail; @endphp
                    <div class="row g-3 mb-3">
                        @if($m->default_mail_client)
                        <div class="col-auto">
                            <span class="text-muted small">Varsayılan İstemci:</span>
                            <span class="fw-semibold ms-1 small">{{ $m->default_mail_client }}</span>
                        </div>
                        @endif
                        @if($m->outlook_version)
                        <div class="col-auto">
                            <span class="text-muted small">Outlook Sürümü:</span>
                            <span class="fw-semibold ms-1 small">{{ $m->outlook_version }}</span>
                        </div>
                        @endif
                        @if($m->is_new_outlook !== null)
                        <div class="col-auto">
                            <span class="badge {{ $m->is_new_outlook ? 'bg-info bg-opacity-10 text-info' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                <i class="fas fa-envelope me-1"></i>
                                {{ $m->is_new_outlook ? 'Yeni Outlook' : 'Klasik Outlook' }}
                            </span>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($agentComputer->mailAccounts->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="ps-3 py-2 small text-muted">E-POSTA ADRESİ</th>
                                    <th class="py-2 small text-muted">GÖRÜNEN AD</th>
                                    <th class="py-2 small text-muted">HESAP TÜRÜ</th>
                                    <th class="py-2 small text-muted">EXCHANGE SUNUCU</th>
                                    <th class="py-2 small text-muted">KAYNAK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agentComputer->mailAccounts as $account)
                                <tr>
                                    <td class="ps-3 small fw-semibold">
                                        <i class="fas fa-at me-1 text-muted"></i>{{ $account->smtp_address }}
                                    </td>
                                    <td class="small">{{ $account->display_name ?? '—' }}</td>
                                    <td class="small">
                                        @if($account->account_type)
                                            <span class="badge bg-light text-dark border">{{ $account->account_type }}</span>
                                        @else —
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $account->exchange_server ?? '—' }}</td>
                                    <td class="small text-muted">{{ $account->source ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted small mb-0">Mail hesabı kaydı bulunamadı.</p>
                    @endif

                </div>
            </div>
        </div>
        @endif

    </div>{{-- /row --}}
</div>
@endsection
