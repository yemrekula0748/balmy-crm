@extends('layouts.default')

@section('title', 'MikroTik Dashboard')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>MikroTik</h4>
                <span>Bilgi İşlem — Ağ & Hotspot Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Bilgi İşlem</span></li>
                <li class="breadcrumb-item active">MikroTik</li>
            </ol>
        </div>
    </div>

    {{-- Bağlantı Hatası --}}
    @if(isset($data['error']))
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong>MikroTik bağlantısı kurulamadı</strong><br>
            <small>{{ $data['error'] }}</small><br>
            <small class="text-muted">
                Host: <code>{{ config('mikrotik.host') }}</code> — Port: <code>{{ config('mikrotik.port') }}</code>
            </small>
        </div>
    </div>
    @else

    {{-- Başlık satırı --}}
    @php
        $identity  = $data['identity']  ?? [];
        $resources = $data['resources'] ?? [];
        $hostname  = $identity['name']  ?? config('mikrotik.host');
        $version   = $resources['version'] ?? '—';
        $board     = $resources['board-name'] ?? '—';
        $uptime    = $resources['uptime'] ?? '—';
        $cpuLoad   = $resources['cpu-load'] ?? null;
        $totalMem  = isset($resources['total-memory']) ? (int)$resources['total-memory'] : 0;
        $freeMem   = isset($resources['free-memory'])  ? (int)$resources['free-memory']  : 0;
        $usedMem   = $totalMem > 0 ? $totalMem - $freeMem : 0;
        $memPct    = $totalMem > 0 ? round($usedMem / $totalMem * 100) : 0;
        $totalHdd  = isset($resources['total-hdd-space'])  ? (int)$resources['total-hdd-space']  : 0;
        $freeHdd   = isset($resources['free-hdd-space'])   ? (int)$resources['free-hdd-space']   : 0;
        $usedHdd   = $totalHdd > 0 ? $totalHdd - $freeHdd : 0;
        $hddPct    = $totalHdd > 0 ? round($usedHdd / $totalHdd * 100) : 0;

        $hotspotActive = $data['hotspot_active'] ?? [];
        $hotspotUsers  = $data['hotspot_users']  ?? [];
        $dhcpLeases    = $data['dhcp_leases']    ?? [];
        $interfaces    = $data['interfaces']     ?? [];
        $logs          = $data['logs']           ?? [];

        function formatBytes(int $bytes): string {
            if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
            if ($bytes >= 1048576)    return round($bytes / 1048576, 1)    . ' MB';
            if ($bytes >= 1024)       return round($bytes / 1024, 1)       . ' KB';
            return $bytes . ' B';
        }
    @endphp

    {{-- Cihaz Kimlik Bandı --}}
    <div class="card border-0 shadow-sm mb-4"
         style="background:linear-gradient(135deg,#1a237e,#283593);color:white;border-radius:16px;">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.15);
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none"
                         stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="8" rx="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-0 fw-bold" style="font-size:20px">{{ $hostname }}</h5>
                    <small style="opacity:.75">{{ $board }} — RouterOS v{{ $version }}</small>
                </div>
                <div class="d-flex gap-4 flex-wrap">
                    <div class="text-center">
                        <div style="font-size:22px;font-weight:700">{{ count($hotspotActive) }}</div>
                        <div style="font-size:11px;opacity:.75">Aktif Hotspot</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:22px;font-weight:700">{{ count($dhcpLeases) }}</div>
                        <div style="font-size:11px;opacity:.75">DHCP Lease</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:22px;font-weight:700">{{ count($interfaces) }}</div>
                        <div style="font-size:11px;opacity:.75">Arayüz</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:18px;font-weight:700">{{ $uptime }}</div>
                        <div style="font-size:11px;opacity:.75">Uptime</div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-sm" onclick="location.reload()"
                            style="background:rgba(255,255,255,.15);color:white;border-radius:8px;border:1px solid rgba(255,255,255,.3)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        Yenile
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Sistem Kaynakları --}}
    <div class="row g-3 mb-4">
        {{-- CPU --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-0 text-muted" style="font-size:12px;font-weight:600;letter-spacing:.5px">CPU KULLANIMI</p>
                            <h3 class="mb-0 fw-bold" style="font-size:32px">
                                {{ $cpuLoad !== null ? $cpuLoad . '%' : '—' }}
                            </h3>
                        </div>
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(67,97,238,.1);
                                    display:flex;align-items:center;justify-content:center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                                 stroke="#4361ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                                <rect x="9" y="9" width="6" height="6"></rect>
                                <line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line>
                                <line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line>
                                <line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line>
                                <line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line>
                            </svg>
                        </div>
                    </div>
                    @if($cpuLoad !== null)
                    <div class="progress" style="height:6px;border-radius:4px">
                        <div class="progress-bar {{ $cpuLoad > 80 ? 'bg-danger' : ($cpuLoad > 50 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ $cpuLoad }}%"></div>
                    </div>
                    <small class="text-muted">{{ $resources['cpu-count'] ?? '?' }} çekirdek — {{ $resources['cpu-frequency'] ?? '?' }} MHz</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- RAM --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-0 text-muted" style="font-size:12px;font-weight:600;letter-spacing:.5px">RAM KULLANIMI</p>
                            <h3 class="mb-0 fw-bold" style="font-size:32px">{{ $memPct }}%</h3>
                        </div>
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,.1);
                                    display:flex;align-items:center;justify-content:center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                                 stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M4 4h16v8H4z"></path><path d="M4 12h16v8H4z"></path>
                                <line x1="8" y1="4" x2="8" y2="12"></line><line x1="12" y1="4" x2="12" y2="12"></line>
                                <line x1="16" y1="4" x2="16" y2="12"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="progress mb-1" style="height:6px;border-radius:4px">
                        <div class="progress-bar {{ $memPct > 80 ? 'bg-danger' : ($memPct > 60 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ $memPct }}%"></div>
                    </div>
                    <small class="text-muted">{{ formatBytes($usedMem) }} / {{ formatBytes($totalMem) }}</small>
                </div>
            </div>
        </div>

        {{-- Disk --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-0 text-muted" style="font-size:12px;font-weight:600;letter-spacing:.5px">FLASH / DISK</p>
                            <h3 class="mb-0 fw-bold" style="font-size:32px">{{ $hddPct }}%</h3>
                        </div>
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(245,158,11,.1);
                                    display:flex;align-items:center;justify-content:center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                                 stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="progress mb-1" style="height:6px;border-radius:4px">
                        <div class="progress-bar {{ $hddPct > 80 ? 'bg-danger' : 'bg-warning' }}"
                             style="width:{{ $hddPct }}%"></div>
                    </div>
                    <small class="text-muted">{{ formatBytes($usedHdd) }} / {{ formatBytes($totalHdd) }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Hotspot Aktif Oturumlar --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px">
        <div class="card-header border-0 pb-0 pt-3 px-4" style="background:transparent">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block;
                                 box-shadow:0 0 0 3px rgba(16,185,129,.2)"></span>
                    Aktif Hotspot Oturumları
                    <span class="badge bg-success bg-opacity-10 text-success ms-1">{{ count($hotspotActive) }}</span>
                </h6>
                <small class="text-muted">{{ now()->format('H:i:s') }} itibarıyla</small>
            </div>
        </div>
        <div class="card-body p-0 pt-2">
            @if(count($hotspotActive) === 0)
            <p class="text-muted text-center py-4 mb-0" style="font-size:14px">
                Şu an aktif hotspot oturumu yok.
            </p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px">
                    <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff)">
                        <tr>
                            <th class="ps-4 py-3" style="font-size:11px;font-weight:700;color:#555">#</th>
                            <th style="font-size:11px;font-weight:700;color:#555">KULLANICI</th>
                            <th style="font-size:11px;font-weight:700;color:#555">IP ADRESİ</th>
                            <th style="font-size:11px;font-weight:700;color:#555">MAC ADRESİ</th>
                            <th style="font-size:11px;font-weight:700;color:#555">UPTIME</th>
                            <th style="font-size:11px;font-weight:700;color:#555">İNDİRME</th>
                            <th class="pe-4" style="font-size:11px;font-weight:700;color:#555">YÜKLEME</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotspotActive as $i => $s)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4361ee,#7b8cde);
                                                display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0">
                                        {{ strtoupper(mb_substr($s['user'] ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $s['user'] ?? '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3"
                                      style="background:rgba(67,97,238,.08);color:#4361ee;font-family:monospace;font-size:12px">
                                    {{ $s['address'] ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <code style="font-size:12px;color:#555">{{ $s['mac-address'] ?? '—' }}</code>
                            </td>
                            <td>
                                <span class="text-success fw-semibold">{{ $s['uptime'] ?? '—' }}</span>
                            </td>
                            <td>
                                @php $bytesIn = isset($s['bytes-in']) ? (int)$s['bytes-in'] : 0; @endphp
                                <span class="text-primary">{{ formatBytes($bytesIn) }}</span>
                            </td>
                            <td class="pe-4">
                                @php $bytesOut = isset($s['bytes-out']) ? (int)$s['bytes-out'] : 0; @endphp
                                <span class="text-warning">{{ formatBytes($bytesOut) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         Veri Kullanımı — Dönem / Client Bazlı
         ===================================================================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px" id="usageCard">
        <div class="card-header border-0 pb-0 pt-3 px-4" style="background:transparent">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    Veri Kullanımı
                </h6>
                {{-- Dönem butonları --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group btn-group-sm" role="group" id="periodBtns">
                        <button type="button" class="btn btn-outline-primary active" data-period="daily">Bugün</button>
                        <button type="button" class="btn btn-outline-primary" data-period="weekly">Bu Hafta</button>
                        <button type="button" class="btn btn-outline-primary" data-period="monthly">Bu Ay</button>
                        <button type="button" class="btn btn-outline-primary" data-period="custom">Özel</button>
                    </div>
                    {{-- Özel tarih aralığı (başlangıçta gizli) --}}
                    <div id="customRangeWrap" class="d-none d-flex align-items-center gap-1">
                        <input type="date" id="usageStart" class="form-control form-control-sm" style="width:140px">
                        <span class="text-muted">–</span>
                        <input type="date" id="usageEnd" class="form-control form-control-sm" style="width:140px">
                        <button class="btn btn-sm btn-primary" id="applyCustomRange">Uygula</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-3 pb-2 px-4">
            {{-- Özet kartlar --}}
            <div class="row g-3 mb-3" id="usageSummary">
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center" style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.12)">
                        <div class="text-muted mb-1" style="font-size:11px;font-weight:600;letter-spacing:.5px">TOPLAM İNDİRME</div>
                        <div class="fw-bold text-primary" style="font-size:20px" id="sumIn">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.12)">
                        <div class="text-muted mb-1" style="font-size:11px;font-weight:600;letter-spacing:.5px">TOPLAM YÜKLEME</div>
                        <div class="fw-bold text-warning" style="font-size:20px" id="sumOut">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.12)">
                        <div class="text-muted mb-1" style="font-size:11px;font-weight:600;letter-spacing:.5px">TOPLAM TRAFİK</div>
                        <div class="fw-bold text-success" style="font-size:20px" id="sumTotal">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center" style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.12)">
                        <div class="text-muted mb-1" style="font-size:11px;font-weight:600;letter-spacing:.5px">BENZERSİZ KULLANICI</div>
                        <div class="fw-bold" style="font-size:20px;color:#6366f1" id="sumUsers">—</div>
                    </div>
                </div>
            </div>

            {{-- Kullanıcı tablosu / yükleniyor --}}
            <div id="usageTableWrap">
                <div class="text-center py-4 text-muted" id="usageLoading" style="display:none">
                    <div class="spinner-border spinner-border-sm me-2"></div> Yükleniyor…
                </div>
                <div id="usageNoData" class="text-center py-4 text-muted" style="display:none;font-size:13px">
                    Bu dönemde kayıtlı veri yok.<br>
                    <small>Scheduler her 5 dakikada bir otomatik kayıt yapar.</small>
                </div>
                <div class="table-responsive" id="usageTableContainer" style="display:none">
                    <table class="table table-hover align-middle mb-0" style="font-size:13px">
                        <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff)">
                            <tr>
                                <th class="ps-3 py-2" style="font-size:11px;font-weight:700;color:#555">#</th>
                                <th style="font-size:11px;font-weight:700;color:#555">KULLANICI</th>
                                <th style="font-size:11px;font-weight:700;color:#555">İNDİRME</th>
                                <th style="font-size:11px;font-weight:700;color:#555">YÜKLEME</th>
                                <th style="font-size:11px;font-weight:700;color:#555">TOPLAM</th>
                                <th style="font-size:11px;font-weight:700;color:#555">OTURUM</th>
                                <th class="pe-3" style="font-size:11px;font-weight:700;color:#555">SON BAĞLANTI</th>
                                <th class="pe-3" style="font-size:11px;font-weight:700;color:#555"></th>
                            </tr>
                        </thead>
                        <tbody id="usageTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Client Detay Modal --}}
    <div class="modal fade" id="clientDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow" style="border-radius:16px">
                <div class="modal-header border-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="clientDetailTitle">Kullanıcı Detayı</h5>
                        <small class="text-muted" id="clientDetailRange"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    {{-- Tarih aralığı seçici --}}
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                        <div class="btn-group btn-group-sm" id="clientPeriodBtns">
                            <button type="button" class="btn btn-outline-secondary" data-days="7">Son 7 Gün</button>
                            <button type="button" class="btn btn-outline-secondary active" data-days="30">Son 30 Gün</button>
                            <button type="button" class="btn btn-outline-secondary" data-days="90">Son 90 Gün</button>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <input type="date" id="clientStart" class="form-control form-control-sm" style="width:140px">
                            <span class="text-muted">–</span>
                            <input type="date" id="clientEnd" class="form-control form-control-sm" style="width:140px">
                            <button class="btn btn-sm btn-primary" id="applyClientRange">Uygula</button>
                        </div>
                    </div>

                    {{-- Özet --}}
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="rounded-3 p-3 text-center" style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.12)">
                                <div class="text-muted mb-1" style="font-size:11px;font-weight:600">İNDİRME</div>
                                <div class="fw-bold text-primary" style="font-size:18px" id="clientSumIn">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="rounded-3 p-3 text-center" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.12)">
                                <div class="text-muted mb-1" style="font-size:11px;font-weight:600">YÜKLEME</div>
                                <div class="fw-bold text-warning" style="font-size:18px" id="clientSumOut">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="rounded-3 p-3 text-center" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.12)">
                                <div class="text-muted mb-1" style="font-size:11px;font-weight:600">TOPLAM</div>
                                <div class="fw-bold text-success" style="font-size:18px" id="clientSumTotal">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- Günlük tablo --}}
                    <div id="clientLoading" class="text-center py-4 text-muted" style="display:none">
                        <div class="spinner-border spinner-border-sm me-2"></div> Yükleniyor…
                    </div>
                    <div id="clientNoData" class="text-center py-4 text-muted" style="display:none;font-size:13px">
                        Bu aralıkta veri bulunamadı.
                    </div>
                    <div class="table-responsive" id="clientTableContainer" style="display:none">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px">
                            <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff)">
                                <tr>
                                    <th class="ps-3 py-2" style="font-size:11px;font-weight:700;color:#555">TARİH</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">İNDİRME</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">YÜKLEME</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">TOPLAM</th>
                                    <th class="pe-3" style="font-size:11px;font-weight:700;color:#555">OTURUM SAYISI</th>
                                </tr>
                            </thead>
                            <tbody id="clientTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- İkili Satır: Hotspot Kullanıcıları + Arayüzler --}}
    <div class="row g-3 mb-4">
        {{-- Hotspot Kullanıcıları --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                <div class="card-header border-0 pb-0 pt-3 px-4" style="background:transparent">
                    <h6 class="mb-0 fw-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                             stroke="#4361ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             viewBox="0 0 24 24" class="me-2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        Hotspot Kullanıcıları
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ count($hotspotUsers) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0 pt-2">
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px">
                            <thead style="background:#f8f9ff;position:sticky;top:0">
                                <tr>
                                    <th class="ps-4 py-2" style="font-size:11px;font-weight:700;color:#555">KULLANICI</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">PROFİL</th>
                                    <th class="pe-4" style="font-size:11px;font-weight:700;color:#555">DURUM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hotspotUsers as $u)
                                @php
                                    $isActive = collect($hotspotActive)->contains('user', $u['name'] ?? '');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:28px;height:28px;border-radius:50%;
                                                        background:{{ $isActive ? 'linear-gradient(135deg,#10b981,#6ee7b7)' : '#e9ecef' }};
                                                        display:flex;align-items:center;justify-content:center;
                                                        font-size:11px;font-weight:700;color:{{ $isActive ? 'white' : '#999' }}">
                                                {{ strtoupper(mb_substr($u['name'] ?? '?', 0, 1)) }}
                                            </div>
                                            <span>{{ $u['name'] ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:11px">
                                            {{ $u['profile'] ?? 'default' }}
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        @if($isActive)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size:11px">
                                            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;margin-right:4px"></span>Çevrimiçi
                                        </span>
                                        @else
                                        <span class="text-muted" style="font-size:12px">Çevrimdışı</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Kullanıcı bulunamadı.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Arayüzler --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                <div class="card-header border-0 pb-0 pt-3 px-4" style="background:transparent">
                    <h6 class="mb-0 fw-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                             stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             viewBox="0 0 24 24" class="me-2">
                            <rect x="2" y="2" width="20" height="8" rx="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                        Ağ Arayüzleri
                        <span class="badge" style="background:rgba(245,158,11,.1);color:#f59e0b;font-size:11px">{{ count($interfaces) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0 pt-2">
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px">
                            <thead style="background:#f8f9ff;position:sticky;top:0">
                                <tr>
                                    <th class="ps-4 py-2" style="font-size:11px;font-weight:700;color:#555">ARAYÜZ</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">TİP</th>
                                    <th style="font-size:11px;font-weight:700;color:#555">TX</th>
                                    <th class="pe-4" style="font-size:11px;font-weight:700;color:#555">RX</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($interfaces as $iface)
                                @php
                                    $isUp     = ($iface['running'] ?? 'false') === 'true';
                                    $txBytes  = isset($iface['tx-byte']) ? (int)$iface['tx-byte'] : 0;
                                    $rxBytes  = isset($iface['rx-byte']) ? (int)$iface['rx-byte'] : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="width:8px;height:8px;border-radius:50%;
                                                         background:{{ $isUp ? '#10b981' : '#e5e7eb' }};flex-shrink:0"></span>
                                            <span class="fw-semibold">{{ $iface['name'] ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:12px">{{ $iface['type'] ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-warning" style="font-size:12px">{{ formatBytes($txBytes) }}</span>
                                    </td>
                                    <td class="pe-4">
                                        <span class="text-primary" style="font-size:12px">{{ formatBytes($rxBytes) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Arayüz bulunamadı.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DHCP Leases --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px">
        <div class="card-header border-0 pb-0 pt-3 px-4" style="background:transparent">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         viewBox="0 0 24 24" class="me-2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    DHCP Leases (IP–MAC)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ count($dhcpLeases) }}</span>
                </h6>
                <div class="input-group input-group-sm" style="width:220px">
                    <span class="input-group-text bg-white border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                             stroke="#999" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" id="dhcpSearch" class="form-control border-start-0 ps-0"
                           placeholder="IP, MAC veya hostname ara...">
                </div>
            </div>
        </div>
        <div class="card-body p-0 pt-2">
            <div class="table-responsive" style="max-height:360px;overflow-y:auto">
                <table class="table table-hover align-middle mb-0" style="font-size:13px" id="dhcpTable">
                    <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff);position:sticky;top:0">
                        <tr>
                            <th class="ps-4 py-2" style="font-size:11px;font-weight:700;color:#555">#</th>
                            <th style="font-size:11px;font-weight:700;color:#555">IP ADRESİ</th>
                            <th style="font-size:11px;font-weight:700;color:#555">MAC ADRESİ</th>
                            <th style="font-size:11px;font-weight:700;color:#555">HOSTNAME</th>
                            <th style="font-size:11px;font-weight:700;color:#555">SERVER</th>
                            <th class="pe-4" style="font-size:11px;font-weight:700;color:#555">DURUM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dhcpLeases as $i => $lease)
                        <tr class="dhcp-row"
                            data-ip="{{ strtolower($lease['address'] ?? '') }}"
                            data-mac="{{ strtolower($lease['mac-address'] ?? '') }}"
                            data-host="{{ strtolower($lease['host-name'] ?? '') }}">
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <span class="badge rounded-pill px-3"
                                      style="background:rgba(99,102,241,.08);color:#6366f1;font-family:monospace;font-size:12px">
                                    {{ $lease['address'] ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <code style="font-size:12px;color:#555">{{ $lease['mac-address'] ?? '—' }}</code>
                            </td>
                            <td>{{ $lease['host-name'] ?? '—' }}</td>
                            <td>
                                <span class="text-muted" style="font-size:12px">{{ $lease['server'] ?? '—' }}</span>
                            </td>
                            <td class="pe-4">
                                @php $status = $lease['status'] ?? ''; @endphp
                                @if($status === 'bound')
                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:11px">Aktif</span>
                                @elseif($status === 'waiting')
                                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:11px">Bekliyor</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:11px">{{ $status ?: '—' }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">DHCP lease bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sistem Logları --}}
    <div class="card border-0 shadow-sm" style="border-radius:16px">
        <div class="card-header border-0 pb-0 pt-3 px-4 d-flex align-items-center justify-content-between" style="background:transparent">
            <h6 class="mb-0 fw-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     viewBox="0 0 24 24" class="me-2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Sistem Logları
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Son {{ count($logs) }}</span>
            </h6>
        </div>
        <div class="card-body p-0 pt-2">
            <div style="max-height:300px;overflow-y:auto;font-family:monospace;font-size:12px" class="px-4 pb-3">
                @forelse($logs as $log)
                @php
                    $topics = $log['topics'] ?? '';
                    $color  = str_contains($topics, 'error')    ? '#ef4444'
                            : (str_contains($topics, 'warning') ? '#f59e0b'
                            : (str_contains($topics, 'info')    ? '#3b82f6'
                            : '#6b7280'));
                @endphp
                <div class="py-1 border-bottom border-light d-flex gap-2" style="line-height:1.4">
                    <span style="color:#9ca3af;white-space:nowrap;min-width:110px">{{ $log['time'] ?? '' }}</span>
                    <span style="color:{{ $color }};min-width:80px;white-space:nowrap">{{ $topics }}</span>
                    <span style="color:#374151">{{ $log['message'] ?? '' }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-4 mb-0">Log kaydı bulunamadı.</p>
                @endforelse
            </div>
        </div>
    </div>

    @endif {{-- end not error --}}

</div>

@push('scripts')
<script>
// DHCP arama filtresi
document.getElementById('dhcpSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#dhcpTable .dhcp-row').forEach(row => {
        const ip   = row.dataset.ip   || '';
        const mac  = row.dataset.mac  || '';
        const host = row.dataset.host || '';
        row.style.display = (ip.includes(q) || mac.includes(q) || host.includes(q)) ? '' : 'none';
    });
});

/* =====================================================================
   Veri Kullanımı — Dönem Filtresi
   ===================================================================== */
(function () {
    // ---------- yardımcılar ----------
    function fmtBytes(b) {
        b = parseInt(b) || 0;
        if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
        if (b >= 1048576)    return (b / 1048576).toFixed(1)    + ' MB';
        if (b >= 1024)       return (b / 1024).toFixed(1)       + ' KB';
        return b + ' B';
    }

    function todayStr() {
        return new Date().toISOString().slice(0, 10);
    }

    // ---------- Dönem tablosu ----------
    let currentPeriod = 'daily';

    function loadUsage(period, start, end) {
        const params = new URLSearchParams({ period });
        if (period === 'custom' && start && end) { params.append('start', start); params.append('end', end); }

        document.getElementById('usageLoading').style.display = '';
        document.getElementById('usageNoData').style.display = 'none';
        document.getElementById('usageTableContainer').style.display = 'none';
        ['sumIn','sumOut','sumTotal','sumUsers'].forEach(id => document.getElementById(id).textContent = '—');

        fetch('{{ route("it.mikrotik.kullanim") }}?' + params)
            .then(r => r.json())
            .then(data => {
                document.getElementById('usageLoading').style.display = 'none';
                document.getElementById('sumIn').textContent    = fmtBytes(data.total_in);
                document.getElementById('sumOut').textContent   = fmtBytes(data.total_out);
                document.getElementById('sumTotal').textContent = fmtBytes((data.total_in||0) + (data.total_out||0));
                document.getElementById('sumUsers').textContent = data.user_count || 0;

                const tbody = document.getElementById('usageTableBody');
                tbody.innerHTML = '';

                if (!data.users || data.users.length === 0) {
                    document.getElementById('usageNoData').style.display = '';
                    return;
                }

                data.users.forEach((u, i) => {
                    const total = (u.total_in||0) + (u.total_out||0);
                    const pct   = data.total_in + data.total_out > 0
                        ? Math.round(total / (data.total_in + data.total_out) * 100) : 0;
                    const lastSeen = u.last_seen ? u.last_seen.slice(0,16).replace('T',' ') : '—';
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="ps-3 text-muted">${i+1}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#a5b4fc);
                                                display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0">
                                        ${(u.username||'?')[0].toUpperCase()}
                                    </div>
                                    <span class="fw-semibold">${u.username||'—'}</span>
                                </div>
                            </td>
                            <td><span class="text-primary">${fmtBytes(u.total_in)}</span></td>
                            <td><span class="text-warning">${fmtBytes(u.total_out)}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width:100px">
                                    <span class="fw-semibold">${fmtBytes(total)}</span>
                                    <div class="progress flex-grow-1" style="height:4px;border-radius:4px;min-width:40px">
                                        <div class="progress-bar bg-success" style="width:${pct}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">${u.session_count}</span></td>
                            <td class="text-muted pe-0" style="font-size:12px">${lastSeen}</td>
                            <td class="pe-3">
                                <button class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:11px"
                                        onclick="openClientDetail('${u.username}')">
                                    Detay
                                </button>
                            </td>
                        </tr>
                    `);
                });

                document.getElementById('usageTableContainer').style.display = '';
            })
            .catch(() => {
                document.getElementById('usageLoading').style.display = 'none';
                document.getElementById('usageNoData').style.display = '';
            });
    }

    // Dönem butonları
    document.querySelectorAll('#periodBtns button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#periodBtns button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            const wrap = document.getElementById('customRangeWrap');
            if (currentPeriod === 'custom') {
                wrap.classList.remove('d-none');
                wrap.classList.add('d-flex');
                const today = todayStr();
                document.getElementById('usageStart').value = today;
                document.getElementById('usageEnd').value   = today;
            } else {
                wrap.classList.add('d-none');
                wrap.classList.remove('d-flex');
                loadUsage(currentPeriod);
            }
        });
    });

    document.getElementById('applyCustomRange').addEventListener('click', function () {
        const s = document.getElementById('usageStart').value;
        const e = document.getElementById('usageEnd').value;
        if (s && e) loadUsage('custom', s, e);
    });

    // Sayfa açılışında günlük yükle
    loadUsage('daily');

    /* =====================================================================
       Client Detay Modal
       ===================================================================== */
    let activeClientUsername = '';

    window.openClientDetail = function (username) {
        activeClientUsername = username;
        document.getElementById('clientDetailTitle').textContent = username + ' — Veri Kullanımı';
        // varsayılan: son 30 gün
        const end   = todayStr();
        const start = new Date(Date.now() - 29 * 86400000).toISOString().slice(0,10);
        document.getElementById('clientStart').value = start;
        document.getElementById('clientEnd').value   = end;
        document.querySelectorAll('#clientPeriodBtns button').forEach(b => {
            b.classList.toggle('active', b.dataset.days === '30');
        });
        loadClientUsage(username, start, end);
        new bootstrap.Modal(document.getElementById('clientDetailModal')).show();
    };

    function loadClientUsage(username, start, end) {
        document.getElementById('clientLoading').style.display = '';
        document.getElementById('clientNoData').style.display = 'none';
        document.getElementById('clientTableContainer').style.display = 'none';
        document.getElementById('clientDetailRange').textContent = start + ' – ' + end;
        ['clientSumIn','clientSumOut','clientSumTotal'].forEach(id => document.getElementById(id).textContent = '—');

        const params = new URLSearchParams({ username, start, end });
        fetch('{{ route("it.mikrotik.client-kullanim") }}?' + params)
            .then(r => r.json())
            .then(data => {
                document.getElementById('clientLoading').style.display = 'none';
                document.getElementById('clientSumIn').textContent    = fmtBytes(data.total_in);
                document.getElementById('clientSumOut').textContent   = fmtBytes(data.total_out);
                document.getElementById('clientSumTotal').textContent = fmtBytes((data.total_in||0)+(data.total_out||0));

                const tbody = document.getElementById('clientTableBody');
                tbody.innerHTML = '';

                if (!data.daily || data.daily.length === 0) {
                    document.getElementById('clientNoData').style.display = '';
                    return;
                }

                // En yüksek günlük trafiği bul (progress bar için)
                const maxTotal = Math.max(...data.daily.map(d => (d.total_in||0)+(d.total_out||0)), 1);

                data.daily.forEach(d => {
                    const total = (d.total_in||0) + (d.total_out||0);
                    const pct   = Math.round(total / maxTotal * 100);
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="ps-3 fw-semibold">${d.date}</td>
                            <td><span class="text-primary">${fmtBytes(d.total_in)}</span></td>
                            <td><span class="text-warning">${fmtBytes(d.total_out)}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>${fmtBytes(total)}</span>
                                    <div class="progress flex-grow-1" style="height:4px;border-radius:4px;min-width:60px">
                                        <div class="progress-bar bg-success" style="width:${pct}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="pe-3 text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">${d.session_count}</span>
                            </td>
                        </tr>
                    `);
                });

                document.getElementById('clientTableContainer').style.display = '';
            })
            .catch(() => {
                document.getElementById('clientLoading').style.display = 'none';
                document.getElementById('clientNoData').style.display = '';
            });
    }

    // Client modal — hızlı dönem butonları
    document.querySelectorAll('#clientPeriodBtns button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#clientPeriodBtns button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const days  = parseInt(this.dataset.days);
            const end   = todayStr();
            const start = new Date(Date.now() - (days-1) * 86400000).toISOString().slice(0,10);
            document.getElementById('clientStart').value = start;
            document.getElementById('clientEnd').value   = end;
            loadClientUsage(activeClientUsername, start, end);
        });
    });

    document.getElementById('applyClientRange').addEventListener('click', function () {
        const s = document.getElementById('clientStart').value;
        const e = document.getElementById('clientEnd').value;
        if (s && e) loadClientUsage(activeClientUsername, s, e);
    });
})();
</script>
@endpush

@endsection
