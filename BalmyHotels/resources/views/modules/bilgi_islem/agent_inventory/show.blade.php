@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Detay')

@push('styles')
<script>tailwind = { corePlugins: { preflight: false } }</script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  .tw-card        { background:#fff; border-radius:1rem; box-shadow:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.06); overflow:hidden; }
  .tw-card-header { padding:1rem 1.5rem .75rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:.5rem; }
  .tw-card-body   { padding:1.25rem 1.5rem; }
  .tw-table       { width:100%; border-collapse:collapse; font-size:.8125rem; }
  .tw-table th    { padding:.6rem 1rem; text-transform:uppercase; font-size:.7rem; letter-spacing:.06em; font-weight:600; color:#94a3b8; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
  .tw-table td    { padding:.65rem 1rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; color:#374151; }
  .tw-table tr:last-child td { border-bottom:0; }
  .tw-table tr:hover td      { background:#f8fafc; }
  .tw-dl          { display:grid; grid-template-columns:auto 1fr; gap:.25rem .75rem; font-size:.8125rem; }
  .tw-dl dt       { color:#94a3b8; white-space:nowrap; align-self:start; padding-top:.1rem; }
  .tw-dl dd       { font-weight:500; color:#1e293b; margin:0; }
  .tw-chip        { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:500; line-height:1.4; }
  .tw-chip-green  { background:#dcfce7; color:#16a34a; }
  .tw-chip-red    { background:#fee2e2; color:#dc2626; }
  .tw-chip-yellow { background:#fef9c3; color:#b45309; }
  .tw-chip-blue   { background:#dbeafe; color:#2563eb; }
  .tw-chip-gray   { background:#f1f5f9; color:#64748b; }
  .tw-chip-purple { background:#ede9fe; color:#7c3aed; }
  .tw-progress    { height:6px; background:#e2e8f0; border-radius:9999px; overflow:hidden; }
  .tw-progress-bar{ height:100%; border-radius:9999px; }
  .tw-btn-primary { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem 1rem; background:#2563eb; color:#fff!important; border-radius:.5rem; font-size:.8125rem; font-weight:500; text-decoration:none!important; border:none; cursor:pointer; }
  .tw-btn-ghost   { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .875rem; background:transparent; color:#475569!important; border-radius:.5rem; font-size:.8125rem; font-weight:500; text-decoration:none!important; border:1px solid #e2e8f0; cursor:pointer; }
  .tw-section-title { font-size:.75rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; padding-bottom:.5rem; border-bottom:1px solid #f1f5f9; margin-bottom:.875rem; display:flex; align-items:center; gap:.375rem; }
  .tw-hero        { border-radius:1.25rem; overflow:hidden; background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#1e3a5f 100%); color:#fff; padding:2rem; margin-bottom:1.5rem; }
  .tw-metric      { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:.75rem; padding:.75rem 1.25rem; min-width:100px; }
</style>
@endpush

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

{{-- ====== HERO ====== --}}
@php
    $minsAgo = $agentComputer->last_seen_at?->diffInMinutes(now()) ?? 9999;
    $isOnline = $minsAgo < 10;
    $isRecent = $minsAgo < 1440;
    $hw = $agentComputer->hardware;
@endphp

<div class="tw-hero">
    <div class="d-flex align-items-start gap-4 flex-wrap">
        {{-- Ikon --}}
        <div style="width:64px;height:64px;background:rgba(255,255,255,.1);border-radius:1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="rgba(255,255,255,.85)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>

        {{-- Kimlik --}}
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h2 class="mb-0" style="font-size:1.625rem;font-weight:700;color:#fff;letter-spacing:-.02em">
                    {{ $agentComputer->hostname }}
                </h2>

                @if($isOnline)
                    <span class="tw-chip" style="background:rgba(34,197,94,.2);color:#86efac;border:1px solid rgba(34,197,94,.3)">
                        <span style="width:7px;height:7px;border-radius:9999px;background:#22c55e;display:inline-block"></span>Çevrimiçi
                    </span>
                @elseif($isRecent)
                    <span class="tw-chip" style="background:rgba(234,179,8,.2);color:#fde047;border:1px solid rgba(234,179,8,.3)">
                        <span style="width:7px;height:7px;border-radius:9999px;background:#eab308;display:inline-block"></span>
                        {{ $agentComputer->last_seen_at?->diffForHumans() ?? 'Bilinmiyor' }}
                    </span>
                @else
                    <span class="tw-chip" style="background:rgba(239,68,68,.2);color:#fca5a5;border:1px solid rgba(239,68,68,.3)">
                        <span style="width:7px;height:7px;border-radius:9999px;background:#ef4444;display:inline-block"></span>
                        {{ $agentComputer->last_seen_at?->diffForHumans() ?? 'Hiç Bağlanmadı' }}
                    </span>
                @endif

                @if($agentComputer->is_domain_joined)
                    <span class="tw-chip" style="background:rgba(37,99,235,.25);color:#93c5fd;border:1px solid rgba(59,130,246,.3)">
                        <i class="fas fa-network-wired" style="font-size:.65rem"></i>{{ $agentComputer->domain_name }}
                    </span>
                @else
                    <span class="tw-chip" style="background:rgba(255,255,255,.1);color:#94a3b8;border:1px solid rgba(255,255,255,.15)">
                        Workgroup{{ $agentComputer->workgroup_name ? ': '.$agentComputer->workgroup_name : '' }}
                    </span>
                @endif
            </div>

            <p class="mb-3" style="color:#94a3b8;font-size:.9rem">
                {{ $agentComputer->os_product_name ?? 'İşletim sistemi bilinmiyor' }}
                @if($agentComputer->os_build_number)
                    <span style="opacity:.6"> • Build {{ $agentComputer->os_build_number }}</span>
                @endif
                <span style="opacity:.5"> • Agent {{ $agentComputer->agent_version ?? 'N/A' }}</span>
            </p>

            {{-- Butonlar --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('it.agent.programs', $agentComputer) }}"
                   class="tw-btn-primary" style="background:rgba(37,99,235,.8);border:1px solid rgba(59,130,246,.4)">
                    <i class="fas fa-list" style="font-size:.75rem"></i>Kurulu Programlar
                    <span style="background:rgba(255,255,255,.2);border-radius:9999px;padding:.05rem .45rem;font-size:.7rem">{{ $programCount }}</span>
                </a>
                <a href="{{ route('it.agent.file-events', $agentComputer) }}"
                   class="tw-btn-ghost" style="color:#fca5a5!important;border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1)">
                    <i class="fas fa-trash-alt" style="font-size:.75rem"></i>Dosya Silme Logları
                </a>
                <a href="{{ route('it.agent.browser-history', $agentComputer) }}"
                   class="tw-btn-ghost" style="color:#7dd3fc!important;border-color:rgba(14,165,233,.3);background:rgba(14,165,233,.1)">
                    <i class="fas fa-globe" style="font-size:.75rem"></i>Tarayıcı Geçmişi
                </a>
                <a href="{{ route('it.agent.index') }}"
                   class="tw-btn-ghost" style="color:#cbd5e1!important;border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.08)">
                    <i class="fas fa-arrow-left" style="font-size:.75rem"></i>Listeye Dön
                </a>
            </div>
        </div>
    </div>

    {{-- Metrik şerit --}}
    @if($hw)
    @php
        $maxDisk  = $agentComputer->disks->max('usage_percent') ?? 0;
        $primaryIp = $agentComputer->networkAdapters->where('is_active', true)->first()?->ip_address;
    @endphp
    <div class="row g-3 mt-2">
        @foreach([
            ['fas fa-microchip','CPU',  $hw->cpu_usage_percent  !== null ? round($hw->cpu_usage_percent).'%'  : '—', $hw->cpu_usage_percent  ?? 0],
            ['fas fa-memory',   'RAM',  $hw->ram_usage_percent  !== null ? round($hw->ram_usage_percent).'%'  : '—', $hw->ram_usage_percent  ?? 0],
            ['fas fa-hdd',      'Disk', $maxDisk > 0 ? round($maxDisk).'%' : '—', $maxDisk],
        ] as [$icon, $label, $val, $pct])
        <div class="col-auto">
            <div class="tw-metric">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem">
                    <i class="{{ $icon }} me-1"></i>{{ $label }}
                </div>
                <div style="font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:.35rem">{{ $val }}</div>
                <div class="tw-progress" style="width:80px">
                    <div class="tw-progress-bar"
                         style="width:{{ min((int)$pct,100) }}%;background:{{ $pct > 85 ? '#ef4444' : ($pct > 65 ? '#f59e0b' : '#22c55e') }}">
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($primaryIp)
        <div class="col-auto">
            <div class="tw-metric">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem">
                    <i class="fas fa-network-wired me-1"></i>IP Adresi
                </div>
                <div style="font-size:1rem;font-weight:600;color:#fff;font-family:monospace">{{ $primaryIp }}</div>
            </div>
        </div>
        @endif

        @if($agentComputer->securitySnapshot && ($agentComputer->securitySnapshot->alert_count ?? 0) > 0)
        <div class="col-auto">
            <div class="tw-metric" style="border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.15)">
                <div style="font-size:.7rem;color:#fca5a5;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem">
                    <i class="fas fa-shield-virus me-1"></i>Güvenlik
                </div>
                <div style="font-size:1.25rem;font-weight:700;color:#fca5a5">
                    {{ $agentComputer->securitySnapshot->alert_count }} Uyarı
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
{{-- /hero --}}

<div class="row g-4">

{{-- ========== İŞLETİM SİSTEMİ ========== --}}
<div class="col-md-6">
    <div class="tw-card h-100">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-desktop" style="color:#3b82f6;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">İşletim Sistemi</span>
        </div>
        <div class="tw-card-body">
            @php
                $osRows = [
                    'Ürün Adı'          => $agentComputer->os_product_name,
                    'Versiyon'          => $agentComputer->os_version,
                    'Sürüm'             => $agentComputer->os_release,
                    'Build'             => $agentComputer->os_build_number,
                    'Mimari'            => $agentComputer->os_architecture,
                    'Kurulum Tarihi'    => $agentComputer->os_install_date?->format('d.m.Y'),
                    'Kayıtlı Kullanıcı' => $agentComputer->os_registered_owner,
                    'Seri No'           => $agentComputer->os_serial_number,
                    'Son Açılış'        => $agentComputer->last_boot_time?->format('d.m.Y H:i'),
                    'Çalışma Süresi'    => $agentComputer->uptime_display,
                ];
            @endphp
            <dl class="tw-dl">
                @foreach($osRows as $label => $value)
                @if($value)
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                @endif
                @endforeach
                @if($agentComputer->current_users)
                    <dt>Aktif Kullanıcılar</dt>
                    <dd>
                        @foreach($agentComputer->current_users as $u)
                            <span class="tw-chip tw-chip-blue me-1 mb-1" style="font-size:.75rem">{{ $u }}</span>
                        @endforeach
                    </dd>
                @endif
            </dl>
        </div>
    </div>
</div>

{{-- ========== DONANIM ========== --}}
<div class="col-md-6">
    <div class="tw-card h-100">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-microchip" style="color:#f59e0b;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Donanım</span>
        </div>
        <div class="tw-card-body">
            @if($agentComputer->hardware)
            @php $hw = $agentComputer->hardware; @endphp
            <dl class="tw-dl">
                <dt>İşlemci</dt>
                <dd>{{ $hw->cpu_name ?? '—' }}</dd>

                <dt>Çekirdek</dt>
                <dd>{{ $hw->cpu_cores_physical ?? '—' }} Fiziksel / {{ $hw->cpu_cores_logical ?? '—' }} Mantıksal</dd>

                <dt>CPU Hız</dt>
                <dd>{{ $hw->cpu_speed_mhz ? number_format($hw->cpu_speed_mhz).' MHz' : '—' }}</dd>

                <dt>CPU Kullanım</dt>
                <dd>
                    @if($hw->cpu_usage_percent !== null)
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <div class="tw-progress" style="width:80px">
                            <div class="tw-progress-bar"
                                 style="width:{{ $hw->cpu_usage_percent }}%;background:{{ $hw->cpu_usage_percent > 80 ? '#ef4444' : '#3b82f6' }}"></div>
                        </div>
                        <span>{{ round($hw->cpu_usage_percent) }}%</span>
                    </div>
                    @else —
                    @endif
                </dd>

                <dt>RAM</dt>
                <dd>
                    {{ $hw->total_ram_gb ? round($hw->total_ram_gb, 1).' GB' : '—' }}
                    @if($hw->ram_usage_percent !== null)
                    <div style="display:flex;align-items:center;gap:.5rem;margin-top:.2rem">
                        <div class="tw-progress" style="width:80px">
                            <div class="tw-progress-bar"
                                 style="width:{{ $hw->ram_usage_percent }}%;background:{{ $hw->ram_usage_percent > 80 ? '#ef4444' : '#8b5cf6' }}"></div>
                        </div>
                        <span style="font-size:.8rem;color:#64748b">{{ round($hw->ram_usage_percent) }}%</span>
                    </div>
                    @endif
                </dd>

                @if(!empty($hw->ram_slots))
                <dt>RAM Slotlar</dt>
                <dd>
                    @foreach($hw->ram_slots as $slot)
                        <span class="tw-chip tw-chip-gray me-1 mb-1" style="font-size:.7rem">
                            {{ $slot['capacity_gb'] ?? '' }}GB
                            @if(!empty($slot['speed_mhz'])) {{ $slot['speed_mhz'] }}MHz @endif
                            @if(!empty($slot['manufacturer'])) — {{ $slot['manufacturer'] }} @endif
                        </span>
                    @endforeach
                </dd>
                @endif

                <dt>Anakart</dt>
                <dd>{{ $hw->motherboard ?? '—' }}</dd>

                <dt>BIOS</dt>
                <dd>{{ $hw->bios_version ?? '—' }}{{ $hw->bios_date ? ' ('.$hw->bios_date.')' : '' }}</dd>
            </dl>
            @else
                <p style="color:#94a3b8;font-size:.875rem">Donanım bilgisi henüz alınmadı.</p>
            @endif
        </div>
    </div>
</div>

{{-- ========== AĞ ADAPTÖRLERI ========== --}}
<div class="col-12">
    <div class="tw-card">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#ecfeff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-network-wired" style="color:#06b6d4;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Ağ Adaptörleri</span>
            <span class="tw-chip tw-chip-gray" style="margin-left:auto">{{ $agentComputer->networkAdapters->count() }} adaptör</span>
        </div>
        <div style="overflow-x:auto">
            <table class="tw-table">
                <thead>
                    <tr>
                        <th>ADAPTÖR</th><th>MAC</th><th>IP (v4)</th><th>IP (v6)</th>
                        <th>GATEWAY</th><th>DNS</th><th>DHCP</th><th>DURUM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentComputer->networkAdapters as $adapter)
                    <tr>
                        <td style="font-weight:500">{{ $adapter->adapter_name }}</td>
                        <td><code style="font-size:.73rem;background:#f1f5f9;padding:2px 5px;border-radius:4px">{{ $adapter->mac_address ?? '—' }}</code></td>
                        <td>
                            @if($adapter->ip_address)
                                <code style="font-size:.73rem;background:#eff6ff;color:#1d4ed8;padding:2px 7px;border-radius:4px">{{ $adapter->ip_address }}</code>
                            @else <span style="color:#cbd5e1">—</span>
                            @endif
                        </td>
                        <td style="color:#94a3b8;font-size:.75rem">{{ $adapter->ip_address_v6 ?? '—' }}</td>
                        <td>{{ $adapter->gateway ?? '—' }}</td>
                        <td>
                            @if($adapter->dns_servers)
                                @foreach($adapter->dns_servers as $dns)
                                    <span class="tw-chip tw-chip-gray me-1" style="font-size:.7rem">{{ $dns }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            <span class="tw-chip {{ $adapter->dhcp_enabled ? 'tw-chip-green' : 'tw-chip-gray' }}">
                                {{ $adapter->dhcp_enabled ? 'DHCP' : 'Statik' }}
                            </span>
                        </td>
                        <td>
                            <span class="tw-chip {{ $adapter->is_active ? 'tw-chip-green' : 'tw-chip-gray' }}">
                                <span style="width:6px;height:6px;border-radius:9999px;background:{{ $adapter->is_active ? '#16a34a' : '#94a3b8' }};display:inline-block"></span>
                                {{ $adapter->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:2rem">Ağ adaptörü bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========== DİSKLER ========== --}}
<div class="col-md-6">
    <div class="tw-card h-100">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-hdd" style="color:#22c55e;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Disk Sürücüleri</span>
        </div>
        <div class="tw-card-body">
            @forelse($agentComputer->disks as $disk)
            @php
                $dpct   = $disk->usage_percent ?? 0;
                $dcolor = $dpct > 85 ? '#ef4444' : ($dpct > 70 ? '#f59e0b' : '#22c55e');
            @endphp
            <div style="margin-bottom:1.25rem">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.375rem">
                    <span style="font-weight:600;font-size:.875rem;color:#1e293b">
                        {{ $disk->drive_letter }}
                        @if($disk->label)
                            <span style="color:#94a3b8;font-weight:400">({{ $disk->label }})</span>
                        @endif
                    </span>
                    <span style="font-size:.8rem;color:{{ $dcolor }}">
                        {{ $disk->free_space_gb !== null ? round($disk->free_space_gb,1).' GB boş' : '' }}
                        / {{ $disk->total_space_gb !== null ? round($disk->total_space_gb,1).' GB' : '?' }}
                    </span>
                </div>
                <div class="tw-progress">
                    <div class="tw-progress-bar" style="width:{{ $dpct }}%;background:{{ $dcolor }}"></div>
                </div>
                <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem">
                    {{ $disk->filesystem }} • %{{ $disk->usage_percent !== null ? round($disk->usage_percent) : '?' }} kullanımda
                </div>
            </div>
            @empty
                <p style="color:#94a3b8;font-size:.875rem">Disk bilgisi yok.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ========== GÜVENLİK / AV ========== --}}
<div class="col-md-6">
    <div class="tw-card h-100">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-shield-alt" style="color:#ef4444;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Güvenlik</span>
        </div>
        <div class="tw-card-body">
            @if($agentComputer->security)
            @php $s = $agentComputer->security; @endphp
            <div class="d-flex flex-wrap gap-2 mb-3">
                @php
                    $checks = [
                        ['fa-desktop',    'RDP',               $s->rdp_enabled,      false],
                        ['fa-user-shield','UAC',               $s->uac_enabled,      true],
                        ['fa-fire',       'FW Domain',          $s->firewall_domain,  true],
                        ['fa-fire',       'FW Private',         $s->firewall_private, true],
                        ['fa-fire',       'FW Public',          $s->firewall_public,  true],
                    ];
                @endphp
                @foreach($checks as [$icon, $label, $val, $goodWhenTrue])
                @if($val !== null)
                    <span class="tw-chip {{ ($goodWhenTrue ? $val : !$val) ? 'tw-chip-green' : 'tw-chip-red' }}">
                        <i class="fas {{ $icon }}" style="font-size:.65rem"></i>{{ $label }}: {{ $val ? 'Açık' : 'Kapalı' }}
                    </span>
                @endif
                @endforeach
            </div>
            <dl class="tw-dl" style="font-size:.8125rem">
                @php $uaLabels = [1=>'Devre Dışı',2=>'Bildir',3=>'İndir',4=>'Otomatik Kur']; @endphp
                <dt>Windows Update</dt>
                <dd>{{ $uaLabels[$s->auto_update] ?? '?' }}</dd>
                <dt>Son WU</dt>
                <dd>{{ $s->last_windows_update ?? '—' }}</dd>
                @if($s->bitlocker)
                <dt>BitLocker</dt>
                <dd>
                    @foreach($s->bitlocker as $drive => $bl)
                        <span class="tw-chip {{ ($bl['is_encrypted'] ?? false) ? 'tw-chip-green' : 'tw-chip-gray' }} me-1"
                              style="font-size:.7rem">
                            {{ $drive }}: {{ ($bl['is_encrypted'] ?? false) ? 'Şifreli' : 'Şifresiz' }}
                        </span>
                    @endforeach
                </dd>
                @endif
            </dl>
            @else
                <p style="color:#94a3b8;font-size:.875rem">Güvenlik bilgisi henüz alınmadı.</p>
            @endif

            {{-- Antivirüs --}}
            <div style="border-top:1px solid #f1f5f9;margin-top:1rem;padding-top:1rem">
                <div class="tw-section-title">
                    <i class="fas fa-shield-virus" style="color:#ef4444"></i>ANTİVİRÜS
                </div>
                @forelse($agentComputer->antivirus as $av)
                <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem">
                    <span style="width:9px;height:9px;border-radius:9999px;background:{{ $av->is_enabled ? '#22c55e' : '#ef4444' }};display:inline-block;flex-shrink:0"></span>
                    <span style="font-weight:500;font-size:.875rem;color:#1e293b">{{ $av->product_name }}</span>
                    @if(!$av->is_up_to_date)
                        <span class="tw-chip tw-chip-yellow" style="font-size:.7rem">Güncelleme Yok</span>
                    @endif
                </div>
                @empty
                    <p style="color:#94a3b8;font-size:.875rem">Antivirüs bilgisi yok.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ========== MAİL / OUTLOOK ========== --}}
@if($agentComputer->mail || $agentComputer->mailAccounts->isNotEmpty())
<div class="col-12">
    <div class="tw-card">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-envelope" style="color:#3b82f6;font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Mail / Outlook</span>
        </div>
        <div class="tw-card-body">
            @if($agentComputer->mail)
            @php $m = $agentComputer->mail; @endphp
            <div class="d-flex flex-wrap gap-3 mb-3">
                @if($m->default_mail_client)
                <div>
                    <span style="font-size:.75rem;color:#94a3b8">Varsayılan İstemci:</span>
                    <span style="font-weight:500;font-size:.875rem;color:#1e293b;margin-left:.3rem">{{ $m->default_mail_client }}</span>
                </div>
                @endif
                @if($m->outlook_version)
                <div>
                    <span style="font-size:.75rem;color:#94a3b8">Outlook Sürümü:</span>
                    <span style="font-weight:500;font-size:.875rem;color:#1e293b;margin-left:.3rem">{{ $m->outlook_version }}</span>
                </div>
                @endif
                @if($m->is_new_outlook !== null)
                    <span class="tw-chip {{ $m->is_new_outlook ? 'tw-chip-blue' : 'tw-chip-gray' }}">
                        <i class="fas fa-envelope" style="font-size:.65rem"></i>
                        {{ $m->is_new_outlook ? 'Yeni Outlook' : 'Klasik Outlook' }}
                    </span>
                @endif
            </div>
            @endif

            @if($agentComputer->mailAccounts->isNotEmpty())
            <div style="overflow-x:auto">
                <table class="tw-table">
                    <thead>
                        <tr>
                            <th>E-POSTA ADRESİ</th><th>GÖRÜNEN AD</th><th>HESAP TÜRÜ</th>
                            <th>EXCHANGE SUNUCU</th><th>KAYNAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentComputer->mailAccounts as $account)
                        <tr>
                            <td style="font-weight:500">
                                <i class="fas fa-at me-1" style="color:#94a3b8;font-size:.75rem"></i>{{ $account->smtp_address }}
                            </td>
                            <td>{{ $account->display_name ?? '—' }}</td>
                            <td>
                                @if($account->account_type)
                                    <span class="tw-chip tw-chip-blue" style="font-size:.7rem">{{ $account->account_type }}</span>
                                @else —
                                @endif
                            </td>
                            <td style="color:#94a3b8">{{ $account->exchange_server ?? '—' }}</td>
                            <td style="color:#94a3b8">{{ $account->source ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p style="color:#94a3b8;font-size:.875rem">Mail hesabı kaydı bulunamadı.</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ========== GÜVENLİK TEHDİTLERİ ========== --}}
@if($agentComputer->securitySnapshot)
@php $snap = $agentComputer->securitySnapshot; @endphp
<div class="col-12">
    <div class="tw-card" style="border-left:4px solid {{ ($snap->alert_count ?? 0) > 0 ? '#ef4444' : '#22c55e' }}">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:{{ ($snap->alert_count ?? 0) > 0 ? '#fef2f2' : '#f0fdf4' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-shield-virus" style="color:{{ ($snap->alert_count ?? 0) > 0 ? '#ef4444' : '#22c55e' }};font-size:.9rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Güvenlik Tehditleri</span>
            @if(($snap->alert_count ?? 0) > 0)
                <span class="tw-chip tw-chip-red">{{ $snap->alert_count }} uyarı</span>
            @else
                <span class="tw-chip tw-chip-green">Tehdit Yok</span>
            @endif
            @if($snap->reported_at)
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8">
                    Son güncelleme: {{ $snap->reported_at->diffForHumans() }}
                </span>
            @endif
        </div>
        <div class="tw-card-body">

            {{-- Defender Tehditleri --}}
            @if(!empty($snap->defender_threats))
            <div style="margin-bottom:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-virus" style="color:#ef4444"></i>Defender Tehditleri
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead>
                            <tr><th>TEHDİT ADI</th><th>ŞİDDET</th><th>DURUM</th><th>KAYNAK</th><th>ETKİLENEN</th></tr>
                        </thead>
                        <tbody>
                            @foreach($snap->defender_threats as $threat)
                            @php
                                $sev    = strtolower($threat['severity'] ?? '');
                                $tchip  = in_array($sev, ['critical','high']) ? 'tw-chip-red' : ($sev === 'medium' ? 'tw-chip-yellow' : 'tw-chip-gray');
                            @endphp
                            <tr style="{{ ($threat['is_active'] ?? false) ? 'background:#fff1f2' : '' }}">
                                <td style="font-weight:500">
                                    <i class="fas fa-skull-crossbones me-1" style="color:#ef4444;font-size:.75rem"></i>
                                    {{ $threat['threat_name'] ?? '—' }}
                                </td>
                                <td><span class="tw-chip {{ $tchip }}" style="font-size:.7rem">{{ $threat['severity'] ?? '—' }}</span></td>
                                <td>
                                    @if($threat['is_active'] ?? false)
                                        <span class="tw-chip tw-chip-red" style="font-size:.7rem">Aktif</span>
                                    @else
                                        <span class="tw-chip tw-chip-green" style="font-size:.7rem">Temizlendi</span>
                                    @endif
                                    @if(!($threat['action_success'] ?? true))
                                        <span class="tw-chip tw-chip-yellow ms-1" style="font-size:.7rem">İşlem Başarısız</span>
                                    @endif
                                </td>
                                <td style="color:#94a3b8">{{ $threat['detection_source'] ?? '—' }}</td>
                                <td style="color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $threat['resources'] ?? '' }}">{{ $threat['resources'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="row g-4">
                {{-- Sol sütun --}}
                <div class="col-md-6">

                    {{-- Shadow Copy --}}
                    <div style="margin-bottom:1.25rem">
                        <div class="tw-section-title">
                            <i class="fas fa-copy" style="color:#3b82f6"></i>Shadow Kopyalar
                        </div>
                        @if(($snap->shadow_copy_count ?? 0) == 0)
                            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.625rem;padding:.75rem 1rem;display:flex;align-items:center;gap:.5rem;font-size:.875rem;color:#92400e">
                                <i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i>
                                Shadow kopya bulunamadı! Ransomware riski yüksek.
                            </div>
                        @else
                            <span class="tw-chip tw-chip-green">
                                <i class="fas fa-check" style="font-size:.65rem"></i>{{ $snap->shadow_copy_count }} kopya mevcut
                            </span>
                        @endif
                    </div>

                    {{-- Başarısız Girişler --}}
                    <div style="margin-bottom:1.25rem">
                        <div class="tw-section-title">
                            <i class="fas fa-key" style="color:#f59e0b"></i>Başarısız Girişler (son 1s)
                        </div>
                        @php $failedCount = $snap->failed_logins_1h ?? 0; @endphp
                        <span class="tw-chip {{ $failedCount >= 10 ? 'tw-chip-yellow' : 'tw-chip-gray' }}">
                            <i class="fas fa-sign-in-alt" style="font-size:.65rem"></i>{{ $failedCount }} başarısız giriş
                        </span>
                    </div>

                    {{-- SMB Signing --}}
                    @if($snap->smb_signing)
                    <div style="margin-bottom:1.25rem">
                        <div class="tw-section-title">
                            <i class="fas fa-share-alt" style="color:#06b6d4"></i>SMB İmzalama
                        </div>
                        @php
                            $smb     = $snap->smb_signing;
                            $smbRisk = strtolower($smb['risk'] ?? '');
                            $smbChip = (str_contains($smbRisk,'yüksek') || str_contains($smbRisk,'yuksek'))
                                       ? 'tw-chip-red' : (str_contains($smbRisk,'orta') ? 'tw-chip-yellow' : 'tw-chip-green');
                        @endphp
                        <dl class="tw-dl" style="font-size:.8125rem">
                            <dt>Sunucu İmzalama</dt>
                            <dd>
                                <span class="tw-chip {{ ($smb['server_signing_required']??false) ? 'tw-chip-green' : 'tw-chip-red' }}" style="font-size:.7rem">
                                    {{ ($smb['server_signing_required']??false) ? 'Zorunlu' : 'Zorunlu Değil' }}
                                </span>
                            </dd>
                            <dt>İstemci İmzalama</dt>
                            <dd>
                                <span class="tw-chip {{ ($smb['client_signing_required']??false) ? 'tw-chip-green' : 'tw-chip-red' }}" style="font-size:.7rem">
                                    {{ ($smb['client_signing_required']??false) ? 'Zorunlu' : 'Zorunlu Değil' }}
                                </span>
                            </dd>
                            @if(isset($smb['risk']))
                            <dt>Risk</dt>
                            <dd><span class="tw-chip {{ $smbChip }}" style="font-size:.7rem">{{ $smb['risk'] }}</span></dd>
                            @endif
                        </dl>
                    </div>
                    @endif

                    {{-- Windows Update --}}
                    @if($snap->windows_update)
                    <div>
                        <div class="tw-section-title">
                            <i class="fab fa-windows" style="color:#3b82f6"></i>Windows Update
                        </div>
                        @php
                            $wu      = $snap->windows_update;
                            $pending = $wu['pending_updates'] ?? 0;
                            $wuChip  = $pending >= 30 ? 'tw-chip-red' : ($pending >= 10 ? 'tw-chip-yellow' : 'tw-chip-green');
                        @endphp
                        <dl class="tw-dl" style="font-size:.8125rem">
                            <dt>Son Başarılı Kurulum</dt>
                            <dd>{{ $wu['last_success_install'] ?? '—' }}</dd>
                            <dt>Bekleyen Güncellemeler</dt>
                            <dd><span class="tw-chip {{ $wuChip }}" style="font-size:.7rem">{{ $pending }} güncelleme</span></dd>
                            @if(isset($wu['risk']))
                            <dt>Risk</dt>
                            <dd><span class="tw-chip {{ $wuChip }}" style="font-size:.7rem">{{ $wu['risk'] }}</span></dd>
                            @endif
                        </dl>
                    </div>
                    @endif

                </div>
                {{-- Sağ sütun --}}
                <div class="col-md-6">

                    {{-- Yerel Adminler --}}
                    @if(!empty($snap->local_admins))
                    <div style="margin-bottom:1.25rem">
                        <div class="tw-section-title">
                            <i class="fas fa-user-shield" style="color:#f59e0b"></i>Yerel Adminler
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($snap->local_admins as $admin)
                            <span class="tw-chip {{ ($admin['is_domain']??false) ? 'tw-chip-blue' : 'tw-chip-yellow' }}">
                                <i class="fas fa-user" style="font-size:.65rem"></i>{{ $admin['name'] ?? '—' }}
                                @if($admin['is_domain']??false)
                                    <span style="opacity:.65;font-size:.7rem">(Domain)</span>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Hesap Kilitlenmeleri --}}
                    @if(!empty($snap->account_lockouts_1h))
                    <div>
                        <div class="tw-section-title">
                            <i class="fas fa-lock" style="color:#ef4444"></i>Hesap Kilitlenmeleri (son 1s)
                        </div>
                        <div style="overflow-x:auto">
                            <table class="tw-table">
                                <thead><tr><th>ZAMAN</th><th>KİLİTLENEN</th><th>KAYNAK PC</th></tr></thead>
                                <tbody>
                                    @foreach($snap->account_lockouts_1h as $lockout)
                                    <tr>
                                        <td style="white-space:nowrap;font-size:.8rem">{{ $lockout['time'] ?? '—' }}</td>
                                        <td style="font-weight:500;color:#dc2626">{{ $lockout['locked_user'] ?? '—' }}</td>
                                        <td style="color:#94a3b8">{{ $lockout['caller_pc'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            {{-- /row tehditleri --}}

            {{-- Şüpheli Processler --}}
            @if(!empty($snap->suspicious_processes))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-bug" style="color:#ef4444"></i>Şüpheli Processler
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>PID</th><th>PROCESS ADI</th><th>DOSYA YOLU</th><th>KULLANICI</th><th>BAŞLANGIÇ</th></tr></thead>
                        <tbody>
                            @foreach($snap->suspicious_processes as $proc)
                            <tr style="background:#fff1f2">
                                <td><code style="font-size:.75rem">{{ $proc['pid'] ?? '—' }}</code></td>
                                <td style="font-weight:500;color:#dc2626">
                                    <i class="fas fa-microchip me-1" style="font-size:.7rem"></i>{{ $proc['name'] ?? '—' }}
                                </td>
                                <td style="color:#94a3b8;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $proc['exe'] ?? '' }}">{{ $proc['exe'] ?? '—' }}</td>
                                <td>{{ $proc['username'] ?? '—' }}</td>
                                <td style="color:#94a3b8;white-space:nowrap;font-size:.8rem">{{ $proc['started_at'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Açık Portlar --}}
            @if(!empty($snap->open_ports))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-plug" style="color:#f59e0b"></i>Beklenmedik Açık Portlar
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>PORT</th><th>ADRES</th><th>PID</th><th>PROCESS</th><th>DURUM</th></tr></thead>
                        <tbody>
                            @foreach($snap->open_ports as $portEntry)
                            <tr style="{{ !($portEntry['is_well_known']??true) ? 'background:#fffbeb' : '' }}">
                                <td>
                                    <code style="font-weight:700;color:{{ !($portEntry['is_well_known']??true) ? '#dc2626' : '#1e293b' }}">
                                        {{ $portEntry['port'] ?? '—' }}
                                    </code>
                                </td>
                                <td style="color:#94a3b8">{{ $portEntry['address'] ?? '—' }}</td>
                                <td><code style="font-size:.75rem">{{ $portEntry['pid'] ?? '—' }}</code></td>
                                <td style="font-weight:500">{{ $portEntry['process'] ?? '—' }}</td>
                                <td>
                                    @if(!($portEntry['is_well_known']??true))
                                        <span class="tw-chip tw-chip-yellow" style="font-size:.7rem">
                                            <i class="fas fa-exclamation-triangle" style="font-size:.65rem"></i>Beklenmedik
                                        </span>
                                    @else
                                        <span class="tw-chip tw-chip-gray" style="font-size:.7rem">Bilinen Port</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- USB Geçmişi --}}
            @if(!empty($snap->usb_history))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-usb" style="color:#64748b"></i>USB Geçmişi
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>AYGIT ADI</th><th>AYGIT ID</th><th>TÜR</th></tr></thead>
                        <tbody>
                            @foreach($snap->usb_history as $usb)
                            <tr>
                                <td style="font-weight:500">
                                    <i class="fas fa-usb me-1" style="color:#94a3b8;font-size:.75rem"></i>{{ $usb['friendly_name'] ?? '—' }}
                                </td>
                                <td style="color:#94a3b8;font-size:.7rem">{{ $usb['device_id'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $usb['type'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Yeni Servisler (24s) --}}
            @if(!empty($snap->new_services_24h))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-cogs" style="color:#64748b"></i>Yeni Kurulu Servisler (son 24s)
                    @php $suspSvc = collect($snap->new_services_24h)->where('suspicious', true)->count(); @endphp
                    @if($suspSvc > 0)
                        <span class="tw-chip tw-chip-red" style="font-size:.7rem">{{ $suspSvc }} şüpheli</span>
                    @endif
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>SERVİS ADI</th><th>DOSYA YOLU</th><th>TÜR</th><th>HESAP</th><th>DURUM</th></tr></thead>
                        <tbody>
                            @foreach($snap->new_services_24h as $svc)
                            <tr style="{{ ($svc['suspicious']??false) ? 'background:#fff1f2' : '' }}">
                                <td style="white-space:nowrap;font-size:.8rem">{{ $svc['time'] ?? '—' }}</td>
                                <td style="font-weight:500;color:{{ ($svc['suspicious']??false) ? '#dc2626' : '#1e293b' }}">{{ $svc['service_name'] ?? '—' }}</td>
                                <td style="color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $svc['image_path'] ?? '' }}">{{ $svc['image_path'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $svc['service_type'] ?? '—' }}</td>
                                <td>{{ $svc['account'] ?? '—' }}</td>
                                <td>
                                    @if($svc['suspicious']??false)
                                        <span class="tw-chip tw-chip-red" style="font-size:.7rem">
                                            <i class="fas fa-exclamation-triangle" style="font-size:.65rem"></i>Şüpheli
                                        </span>
                                    @else
                                        <span class="tw-chip tw-chip-gray" style="font-size:.7rem">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Hesap Değişiklikleri (24s) --}}
            @if(!empty($snap->account_changes_24h))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-user-edit" style="color:#f59e0b"></i>Hesap Değişiklikleri (son 24s)
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>OLAY ID</th><th>AKSİYON</th><th>HEDEF KULLANICI</th><th>YAPAN</th></tr></thead>
                        <tbody>
                            @foreach($snap->account_changes_24h as $change)
                            @php $critical = in_array($change['event_id'] ?? 0, [4720, 4728, 4732, 4756]); @endphp
                            <tr style="{{ $critical ? 'background:#fffbeb' : '' }}">
                                <td style="white-space:nowrap;font-size:.8rem">{{ $change['time'] ?? '—' }}</td>
                                <td><code style="font-size:.75rem;color:{{ $critical ? '#dc2626' : '#1e293b' }}">{{ $change['event_id'] ?? '—' }}</code></td>
                                <td style="font-weight:500;color:{{ $critical ? '#dc2626' : '#1e293b' }}">{{ $change['action'] ?? '—' }}</td>
                                <td>{{ $change['target_user'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $change['subject_user'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Beklenmedik Kapanmalar --}}
            @if(!empty($snap->unexpected_shutdowns))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-power-off" style="color:#ef4444"></i>Beklenmedik Kapanmalar
                    <span class="tw-chip tw-chip-red" style="font-size:.7rem">{{ count($snap->unexpected_shutdowns) }}</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>MESAJ</th></tr></thead>
                        <tbody>
                            @foreach($snap->unexpected_shutdowns as $sd)
                            <tr>
                                <td style="white-space:nowrap;font-size:.8rem">{{ $sd['time'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $sd['message'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- RDP Olayları (24s) --}}
            @if($snap->rdp_events_24h)
            @php
                $rdpAttempts = $snap->rdp_events_24h['attempts'] ?? [];
                $rdpSessions = $snap->rdp_events_24h['sessions'] ?? [];
            @endphp
            @if(!empty($rdpAttempts) || !empty($rdpSessions))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-desktop" style="color:#06b6d4"></i>RDP Olayları (son 24s)
                    @if(!empty($rdpAttempts))
                        <span class="tw-chip tw-chip-blue" style="font-size:.7rem">{{ count($rdpAttempts) }} deneme</span>
                    @endif
                </div>
                @if(!empty($rdpAttempts))
                <p style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Giriş Denemeleri</p>
                <div style="overflow-x:auto;margin-bottom:1rem">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>KULLANICI</th><th>DOMAIN</th><th>KAYNAK IP</th></tr></thead>
                        <tbody>
                            @foreach($rdpAttempts as $att)
                            @php
                                $isExternal = !str_starts_with($att['source_ip'] ?? '', '192.168.')
                                           && !str_starts_with($att['source_ip'] ?? '', '10.')
                                           && !str_starts_with($att['source_ip'] ?? '', '172.');
                            @endphp
                            <tr style="{{ $isExternal ? 'background:#fffbeb' : '' }}">
                                <td style="white-space:nowrap;font-size:.8rem">{{ $att['time'] ?? '—' }}</td>
                                <td style="font-weight:500">{{ $att['user'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $att['domain'] ?? '—' }}</td>
                                <td>
                                    <code style="font-size:.75rem;color:{{ $isExternal ? '#dc2626' : '#1e293b' }}">{{ $att['source_ip'] ?? '—' }}</code>
                                    @if($isExternal)
                                        <span class="tw-chip tw-chip-yellow ms-1" style="font-size:.65rem">Dış IP</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @if(!empty($rdpSessions))
                <p style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Oturumlar</p>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>DURUM</th><th>KULLANICI</th><th>KAYNAK IP</th></tr></thead>
                        <tbody>
                            @foreach($rdpSessions as $sess)
                            <tr>
                                <td style="white-space:nowrap;font-size:.8rem">{{ $sess['time'] ?? '—' }}</td>
                                <td>{{ $sess['action'] ?? '—' }}</td>
                                <td style="font-weight:500">{{ $sess['user'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $sess['source_ip'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif
            @endif

            {{-- Zamanlanmış Görevler (24s) --}}
            @if(!empty($snap->scheduled_tasks_24h))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-clock" style="color:#64748b"></i>Yeni Zamanlanmış Görevler (son 24s)
                    <span class="tw-chip tw-chip-gray" style="font-size:.7rem">{{ count($snap->scheduled_tasks_24h) }}</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>OLAY ID</th><th>GÖREV ADI</th><th>AKSİYON</th><th>KULLANICI</th></tr></thead>
                        <tbody>
                            @foreach($snap->scheduled_tasks_24h as $task)
                            <tr>
                                <td style="white-space:nowrap;font-size:.8rem">{{ $task['time'] ?? '—' }}</td>
                                <td><code style="font-size:.75rem">{{ $task['event_id'] ?? '—' }}</code></td>
                                <td style="font-weight:500">{{ $task['task_name'] ?? '—' }}</td>
                                <td>{{ $task['action'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $task['user'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Admin Girişleri (1s) --}}
            @if(!empty($snap->admin_logins_1h))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-user-lock" style="color:#ef4444"></i>Admin Girişleri (son 1s)
                    <span class="tw-chip tw-chip-red" style="font-size:.7rem">{{ count($snap->admin_logins_1h) }}</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>KULLANICI</th><th>DOMAIN</th></tr></thead>
                        <tbody>
                            @foreach($snap->admin_logins_1h as $login)
                            <tr>
                                <td style="white-space:nowrap;font-size:.8rem">{{ $login['time'] ?? '—' }}</td>
                                <td style="font-weight:500;color:#dc2626">{{ $login['user'] ?? '—' }}</td>
                                <td style="color:#94a3b8">{{ $login['domain'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Servis Çökmeleri (24s) --}}
            @if(!empty($snap->service_crashes_24h))
            <div style="margin-top:1.5rem">
                <div class="tw-section-title">
                    <i class="fas fa-bomb" style="color:#ef4444"></i>Servis Çökmeleri (son 24s)
                    <span class="tw-chip tw-chip-red" style="font-size:.7rem">{{ count($snap->service_crashes_24h) }}</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="tw-table">
                        <thead><tr><th>ZAMAN</th><th>SERVİS ADI</th><th>ÇÖKME SAYISI</th></tr></thead>
                        <tbody>
                            @foreach($snap->service_crashes_24h as $crash)
                            <tr>
                                <td style="white-space:nowrap;font-size:.8rem">{{ $crash['time'] ?? '—' }}</td>
                                <td style="font-weight:500">{{ $crash['service_name'] ?? '—' }}</td>
                                <td>
                                    <span class="tw-chip tw-chip-red" style="font-size:.7rem">{{ $crash['crash_count'] ?? '—' }}x</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif
{{-- /securitySnapshot --}}

{{-- ========== UZAK KOMUT ========== --}}
<div class="col-12">

    @if(session('cmd_success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.75rem;padding:.875rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.625rem;color:#15803d;font-size:.875rem">
        <i class="fas fa-check-circle"></i>{{ session('cmd_success') }}
    </div>
    @endif
    @if(session('cmd_error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.75rem;padding:.875rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.625rem;color:#b91c1c;font-size:.875rem">
        <i class="fas fa-exclamation-triangle"></i>{{ session('cmd_error') }}
    </div>
    @endif

    <div class="tw-card">
        <div class="tw-card-header">
            <div style="width:36px;height:36px;border-radius:.625rem;background:#1e293b;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-terminal" style="color:#94a3b8;font-size:.85rem"></i>
            </div>
            <span style="font-weight:600;font-size:.9375rem;color:#1e293b">Uzak Komut</span>
            <button onclick="toggleCmdForm()" id="cmdToggleBtn" class="tw-btn-ghost" style="margin-left:auto">
                <i class="fas fa-plus" style="font-size:.7rem"></i>Komut Gönder
            </button>
        </div>

        {{-- Form --}}
        <div id="cmdFormPanel" style="display:none;border-bottom:1px solid #f1f5f9;padding:1.25rem 1.5rem;background:#f8fafc">
            <form method="POST" action="{{ route('it.agent.send-command', $agentComputer) }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label style="font-size:.8rem;font-weight:500;color:#475569;display:block;margin-bottom:.3rem">Komut Türü</label>
                        <select name="type" id="cmdType" class="form-select form-select-sm"
                                onchange="updateCmdForm(this.value)" required
                                style="border-radius:.5rem;border-color:#e2e8f0">
                            <option value="">— Seçin —</option>
                            <option value="shutdown">⏻ Kapat (Shutdown)</option>
                            <option value="restart">🔄 Yeniden Başlat</option>
                            <option value="logoff">🚪 Kullanıcı Çıkışı</option>
                            <option value="cmd">💻 Komut Çalıştır (CMD)</option>
                            <option value="msgbox">💬 Mesaj Göster</option>
                        </select>
                    </div>
                    <div class="col-md-5" id="payloadGroup" style="display:none">
                        <label style="font-size:.8rem;font-weight:500;color:#475569;display:block;margin-bottom:.3rem" id="payloadLabel">Komut / Mesaj</label>
                        <textarea name="payload" id="payload" class="form-control form-control-sm" rows="2"
                                  style="border-radius:.5rem;border-color:#e2e8f0;font-family:monospace"></textarea>
                    </div>
                    <div class="col-md-2" id="delayGroup" style="display:none">
                        <label style="font-size:.8rem;font-weight:500;color:#475569;display:block;margin-bottom:.3rem">Gecikme (sn)</label>
                        <input type="number" name="delay_seconds" class="form-control form-control-sm"
                               min="0" max="3600" value="0" style="border-radius:.5rem;border-color:#e2e8f0">
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:.8rem;font-weight:500;color:#475569;display:block;margin-bottom:.3rem">Zaman Aşımı (dk)</label>
                        <input type="number" name="expires_in" class="form-control form-control-sm"
                               min="1" max="10080" value="60" style="border-radius:.5rem;border-color:#e2e8f0">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="tw-btn-primary">
                            <i class="fas fa-paper-plane" style="font-size:.75rem"></i>Gönder
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Son komutlar --}}
        <div style="padding:0">
            @if($recentCommands->isEmpty())
                <p style="text-align:center;color:#94a3b8;padding:2rem;font-size:.875rem">Henüz komut gönderilmedi.</p>
            @else
            <div style="overflow-x:auto">
                <table class="tw-table">
                    <thead>
                        <tr><th>ZAMAN</th><th>TÜR</th><th>PAYLOAD</th><th>GÖNDEREN</th><th>DURUM</th><th>ÇIKTI</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentCommands as $cmd)
                        @php
                            $statusColor = \App\Models\AgentComputerCommand::STATUS_COLORS[$cmd->status] ?? 'secondary';
                            $statusLabel = \App\Models\AgentComputerCommand::STATUS_LABELS[$cmd->status] ?? $cmd->status;
                            $statusChipMap = [
                                'success'  => 'tw-chip-green',
                                'danger'   => 'tw-chip-red',
                                'warning'  => 'tw-chip-yellow',
                                'primary'  => 'tw-chip-blue',
                                'secondary'=> 'tw-chip-gray',
                                'info'     => 'tw-chip-blue',
                            ];
                            $statusChip = $statusChipMap[$statusColor] ?? 'tw-chip-gray';
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;font-size:.8rem">
                                {{ $cmd->created_at->format('d.m.Y H:i') }}
                                @if($cmd->executed_at)
                                    <br><span style="color:#94a3b8;font-size:.7rem">
                                        <i class="fas fa-check me-1"></i>{{ $cmd->executed_at->format('H:i:s') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="display:inline-block;padding:.25rem .6rem;background:#1e293b;color:#fff;border-radius:4px;font-size:.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.04em">
                                    {{ $cmd->type }}
                                </span>
                            </td>
                            <td style="color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem">
                                {{ $cmd->payload ?? '—' }}
                            </td>
                            <td style="font-size:.875rem">{{ $cmd->createdBy?->name ?? '<sistem>' }}</td>
                            <td>
                                <span class="tw-chip {{ $statusChip }}" style="font-size:.75rem">{{ $statusLabel }}</span>
                                @if($cmd->isExpired() && $cmd->status === 'pending')
                                    <br><span class="tw-chip tw-chip-gray" style="font-size:.7rem;margin-top:.25rem">Süresi Doldu</span>
                                @endif
                            </td>
                            <td>
                                @if($cmd->output)
                                    <button onclick="toggleOutput('out{{ $cmd->id }}')"
                                            style="background:none;border:none;color:#64748b;font-size:.8rem;cursor:pointer;padding:0;display:flex;align-items:center;gap:.3rem">
                                        <i class="fas fa-chevron-down" style="font-size:.65rem"></i>Çıktı
                                    </button>
                                    <div id="out{{ $cmd->id }}" style="display:none;margin-top:.375rem">
                                        <pre style="background:#0f172a;color:#e2e8f0;border-radius:.5rem;padding:.75rem;font-size:.7rem;max-height:150px;overflow-y:auto;margin:0;white-space:pre-wrap;word-break:break-all">{{ $cmd->output }}</pre>
                                    </div>
                                @else
                                    <span style="color:#cbd5e1">—</span>
                                @endif
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
{{-- /uzak komut --}}

</div>
{{-- /row g-4 --}}

</div>
{{-- /container-fluid --}}

@push('scripts')
<script>
function updateCmdForm(type) {
    const payloadGroup = document.getElementById('payloadGroup');
    const delayGroup   = document.getElementById('delayGroup');
    const payloadLabel = document.getElementById('payloadLabel');
    const payloadTA    = document.getElementById('payload');

    payloadGroup.style.display = 'none';
    delayGroup.style.display   = 'none';
    payloadTA.required         = false;

    if (type === 'cmd') {
        payloadGroup.style.display = '';
        payloadLabel.textContent   = 'Komut Satırı';
        payloadTA.placeholder      = 'Örn: ipconfig /all';
        payloadTA.required         = true;
    } else if (type === 'msgbox') {
        payloadGroup.style.display = '';
        payloadLabel.textContent   = 'Gösterilecek Mesaj';
        payloadTA.placeholder      = 'Örn: Sisteminiz 5 dakika içinde yeniden başlatılacak.';
        payloadTA.required         = true;
    } else if (type === 'shutdown' || type === 'restart') {
        delayGroup.style.display = '';
    }
}

function toggleCmdForm() {
    const panel = document.getElementById('cmdFormPanel');
    const btn   = document.getElementById('cmdToggleBtn');
    if (panel.style.display === 'none') {
        panel.style.display = '';
        btn.innerHTML = '<i class="fas fa-times" style="font-size:.7rem"></i>İptal';
    } else {
        panel.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-plus" style="font-size:.7rem"></i>Komut Gönder';
    }
}

function toggleOutput(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
@endpush

@endsection
