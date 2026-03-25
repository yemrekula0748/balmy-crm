@extends('layouts.default')

@section('title', 'Ajan Envanter')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Ajan Envanter</h4>
                <span>Bilgi İşlem — Windows Agent Bilgisayarları</span>
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
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <form method="GET" id="filterForm">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Hostname veya IP ara...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="domain" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm Domain'ler</option>
                            @foreach($domains as $d)
                                <option value="{{ $d }}" @selected(request('domain') == $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="os" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tüm İşletim Sistemleri</option>
                            @foreach($osList as $os)
                                <option value="{{ $os }}" @selected(request('os') == $os)>{{ $os }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="av_disabled" value="1"
                                   id="avFilter" @checked(request('av_disabled')) onchange="this.form.submit()">
                            <label class="form-check-label small text-danger" for="avFilter">AV Kapalı</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="disk_warning" value="1"
                                   id="diskFilter" @checked(request('disk_warning')) onchange="this.form.submit()">
                            <label class="form-check-label small text-warning" for="diskFilter">Disk Uyarısı</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="rdp_enabled" value="1"
                                   id="rdpFilter" @checked(request('rdp_enabled')) onchange="this.form.submit()">
                            <label class="form-check-label small text-info" for="rdpFilter">RDP Açık</label>
                        </div>
                    </div>
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('it.agent.stats') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-chart-bar me-1"></i>İstatistikler
                        </a>
                        @if(request()->hasAny(['search','domain','os','av_disabled','disk_warning','rdp_enabled']))
                            <a href="{{ route('it.agent.index') }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i>Temizle
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff);">
                        <tr>
                            <th class="ps-4 py-3" style="font-size:12px;font-weight:700;color:#555">#</th>
                            <th style="font-size:12px;font-weight:700;color:#555">HOSTNAME</th>
                            <th style="font-size:12px;font-weight:700;color:#555">İŞLETİM SİSTEMİ</th>
                            <th style="font-size:12px;font-weight:700;color:#555">IP ADRESİ</th>
                            <th style="font-size:12px;font-weight:700;color:#555">DOMAIN</th>
                            <th style="font-size:12px;font-weight:700;color:#555">DURUM</th>
                            <th style="font-size:12px;font-weight:700;color:#555">SON GÖRÜLME</th>
                            <th class="text-end pe-4" style="font-size:12px;font-weight:700;color:#555">İŞLEM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($computers as $c)
                        @php
                            $activeIp  = $c->networkAdapters->where('is_active', true)->first()?->ip_address ?? '—';
                            $avOk      = $c->antivirus->where('is_enabled', true)->isNotEmpty();
                            $diskWarn  = $c->disks->contains(fn($d) => ($d->usage_percent ?? 0) > 85);
                            $isOnline  = $c->last_seen_at && $c->last_seen_at->diffInMinutes(now()) < 10;
                            $isRecent  = $c->last_seen_at && $c->last_seen_at->diffInHours(now()) < 24;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted" style="font-size:13px">{{ $computers->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block"
                                          style="width:8px;height:8px;background:{{ $isOnline ? '#10b981' : ($isRecent ? '#f59e0b' : '#cbd5e1') }}"></span>
                                    <a href="{{ route('it.agent.show', $c) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $c->hostname }}
                                    </a>
                                </div>
                            </td>
                            <td style="font-size:13px">{{ $c->os_product_name ?? '—' }}</td>
                            <td>
                                @if($activeIp !== '—')
                                    <code style="font-size:12px;background:#f1f5f9;padding:2px 7px;border-radius:5px">{{ $activeIp }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="font-size:13px">
                                @if($c->is_domain_joined)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $c->domain_name }}</span>
                                @else
                                    <span class="text-muted small">Workgroup</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @if($c->antivirus->isNotEmpty())
                                        <span class="badge {{ $avOk ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}"
                                              title="Antivirüs">
                                            <i class="fas fa-shield-alt me-1"></i>{{ $avOk ? 'AV OK' : 'AV Kapalı' }}
                                        </span>
                                    @endif
                                    @if($diskWarn)
                                        <span class="badge bg-warning bg-opacity-10 text-warning" title="Disk dolu">
                                            <i class="fas fa-hdd me-1"></i>Disk
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-size:12px;color:#64748b">
                                @if($c->last_seen_at)
                                    <span title="{{ $c->last_seen_at->format('d.m.Y H:i') }}">
                                        {{ $c->last_seen_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('it.agent.show', $c) }}"
                                   class="btn btn-sm btn-outline-primary py-1 px-2" title="Detay">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasPermission('it_agent_inventory', 'delete'))
                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2"
                                        onclick="confirmDelete({{ $c->id }}, '{{ $c->hostname }}')" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-desktop fa-2x mb-2 d-block opacity-25"></i>
                                Henüz kayıtlı bilgisayar yok.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($computers->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $computers->links() }}
            </div>
            @endif
        </div>
    </div>
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
</script>
@endpush
@endsection
