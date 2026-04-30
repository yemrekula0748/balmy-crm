@extends('layouts.default')

@push('styles')
<style>
/* ── Hero ───────────────────────────────────────────────────── */
.page-hero { background:#fff;border-radius:16px;margin-bottom:24px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden;display:flex;align-items:stretch; }
.page-hero-stripe { width:6px;flex-shrink:0;background:linear-gradient(180deg,#e11d48,#be123c);border-radius:16px 0 0 16px; }
.page-hero-icon   { width:68px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:linear-gradient(135deg,#e11d48,#be123c);font-size:1.55rem;color:#fff; }
.page-hero-body   { flex:1;padding:18px 22px;min-width:0; }
.page-hero-body h3 { font-size:1.15rem;font-weight:700;color:#1f2937;margin:0 0 3px;letter-spacing:-.01em; }
.page-hero-body p  { margin:0;font-size:.82rem;color:#6b7280; }
.page-hero-actions { display:flex;align-items:center;gap:10px;padding:0 20px;flex-shrink:0;border-left:1px solid #f3f4f6; }
/* ── Stat chips ─────────────────────────────────────────────── */
.stat-chips { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px; }
.stat-chip  { background:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);display:flex;align-items:center;gap:12px;flex:1;min-width:140px; }
.stat-chip .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.stat-chip .sc-num  { font-size:1.45rem;font-weight:700;line-height:1; }
.stat-chip .sc-lbl  { font-size:.78rem;color:#6b7280;margin-top:2px; }
/* ── Filter card ────────────────────────────────────────────── */
.filter-card { background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);margin-bottom:20px; }
.filter-card .form-control,.filter-card .form-select { border:1.5px solid #e5e7eb;border-radius:9px;font-size:.83rem;color:#1f2937;padding:8px 12px;height:auto;transition:border-color .2s,box-shadow .2s;background:#fff; }
.filter-card .form-control:focus,.filter-card .form-select:focus { border-color:#e11d48;box-shadow:0 0 0 3px rgba(225,29,72,.13);outline:none; }
/* ── Faults table ───────────────────────────────────────────── */
.faults-card { background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden; }
.faults-card thead tr { background:#f8f9fb; }
.faults-card thead th { font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;padding:13px 16px;border-bottom:1.5px solid #f3f4f6;white-space:nowrap; }
.faults-card tbody td { padding:12px 16px;vertical-align:middle;border-bottom:1px solid #f9fafb;font-size:.86rem; }
.faults-card tbody tr.fault-main:last-of-type td { border-bottom:none; }
.faults-card tbody tr.fault-main:hover { background:#fff8f8; }
.faults-card tbody tr.fault-expand { display:none; }
.faults-card tbody tr.fault-expand.open { display:table-row; }
.faults-card tbody tr.fault-expand td { padding:0;border-bottom:1px solid #f3f4f6;background:#f8fafc; }
/* ── Priority badge ─────────────────────────────────────────── */
.prio-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:700;white-space:nowrap; }
/* ── Status badge ───────────────────────────────────────────── */
.status-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:.74rem;font-weight:700;white-space:nowrap; }
/* ── Action buttons ─────────────────────────────────────────── */
.act-btn { display:inline-flex;align-items:center;justify-content:center;height:30px;padding:0 10px;gap:5px;border-radius:7px;border:none;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;white-space:nowrap; }
.act-btn-detail  { background:rgba(225,29,72,.1);color:#e11d48; }
.act-btn-detail:hover { background:#e11d48;color:#fff; }
.act-btn-update  { background:rgba(245,158,11,.1);color:#b45309; }
.act-btn-update:hover { background:#d97706;color:#fff; }
/* ── Expand form ────────────────────────────────────────────── */
.expand-form { padding:16px 20px; }
.expand-form .form-control,.expand-form .form-select { border:1.5px solid #e5e7eb;border-radius:9px;font-size:.82rem;padding:8px 12px;height:auto;background:#fff;color:#1f2937; }
.expand-form .form-control:focus,.expand-form .form-select:focus { border-color:#e11d48;box-shadow:0 0 0 3px rgba(225,29,72,.12);outline:none; }
/* ── Empty ──────────────────────────────────────────────────── */
.empty-state { padding:56px 24px;text-align:center;color:#9ca3af; }
.empty-state .es-icon { font-size:2.5rem;margin-bottom:12px;opacity:.25;display:block; }
</style>
@endpush

@section('title', 'Bildirdiklerim')

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Bildirdiklerim</h4>
                <span class="text-muted" style="font-size:.82rem">Departmanınızın arıza bildirimleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center gap-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Bildirdiklerim</li>
            </ol>
            <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm fw-semibold">
                <i class="fas fa-plus me-1"></i> Yeni Arıza Bildir
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm my-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Hero --}}
    <div class="page-hero mt-3">
        <div class="page-hero-stripe"></div>
        <div class="page-hero-icon"><i class="fas fa-paper-plane"></i></div>
        <div class="page-hero-body">
            <h3>Bildirdiklerim</h3>
            <p>Departmanınızın ilettiği arıza bildirimlerini buradan takip edebilirsiniz</p>
        </div>
    </div>

    {{-- Stat chips --}}
    @php
        $statChips = [
            'open'        => ['Açık',     '#ef4444', 'fa-circle-exclamation'],
            'in_progress' => ['İşlemde',  '#f97316', 'fa-tools'],
            'resolved'    => ['Çözüldü',  '#10b981', 'fa-check-double'],
            'closed'      => ['Kapalı',   '#6b7280', 'fa-lock'],
        ];
        $statCounts = $faults->getCollection()->groupBy('status');
    @endphp
    <div class="stat-chips">
        @foreach($statChips as $sKey => [$sLabel, $sColor, $sIcon])
        <div class="stat-chip">
            <div class="sc-icon" style="background:{{ $sColor }}1a;color:{{ $sColor }}"><i class="fas {{ $sIcon }}"></i></div>
            <div>
                <div class="sc-num" style="color:{{ $sColor }}">{{ $statCounts->get($sKey, collect())->count() }}</div>
                <div class="sc-lbl">{{ $sLabel }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filtre --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('faults.my-reports') }}" id="filter-form" class="row g-2 align-items-end">

            {{-- Tarih Aralığı --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-calendar-alt me-1"></i>Başlangıç Tarihi
                </label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-calendar-alt me-1"></i>Bitiş Tarihi
                </label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            {{-- Durum --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-circle me-1"></i>Durum
                </label>
                <select name="status" class="form-select">
                    <option value="">— Tüm Durumlar —</option>
                    @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bildirdiğim Departman --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-building me-1"></i>Bildirdiğim Departman
                </label>
                <select name="department_id" class="form-select">
                    <option value="">— Tüm Departmanlar —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Konum --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-map-marker-alt me-1"></i>Konum
                </label>
                <select name="location_id" class="form-select" id="location-select">
                    <option value="">— Tüm Konumlar —</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Alan --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-layer-group me-1"></i>Alan
                </label>
                <select name="area_id" class="form-select" id="area-select">
                    <option value="">— Tüm Alanlar —</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Butonlar --}}
            <div class="col-12 d-flex gap-2 align-items-center flex-wrap pt-1">
                <button type="submit" class="btn btn-sm"
                    style="background:#e11d48;color:#fff;border-radius:9px;padding:9px 18px;font-size:.83rem;font-weight:600;border:none">
                    <i class="fas fa-search me-1"></i>Ara
                </button>
                @if(request('date_from') || request('date_to') || request('status') || request('department_id') || request('location_id') || request('area_id'))
                    <a href="{{ route('faults.my-reports') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;padding:9px 14px">
                        <i class="fas fa-times me-1"></i>Temizle
                    </a>
                @endif
                <span class="ms-auto" style="font-size:.78rem;color:#9ca3af;white-space:nowrap">
                    {{ $faults->total() }} kayıt
                </span>
            </div>
        </form>
    </div>

    {{-- Tablo --}}
    <div class="faults-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Arıza</th>
                    <th>Öncelik</th>
                    <th>Tür · Konum · Alan</th>
                    <th>Bildirilen Departman</th>
                    <th>Bildiren</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th class="text-end" style="padding-right:20px">İşlem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($faults as $fault)
            @php
                $isMine      = $fault->reported_by === auth()->id();
                $statusColor = \App\Models\Fault::STATUS_COLORS[$fault->status] ?? 'secondary';
                $borderHexMap = ['danger'=>'#ef4444','warning'=>'#f97316','info'=>'#0ea5e9','success'=>'#10b981','primary'=>'#4361ee','secondary'=>'#94a3b8'];
                $borderHex   = $borderHexMap[$statusColor] ?? '#94a3b8';
                $isOld       = $fault->created_at->diffInHours(now()) > 24;
                $prioCfg = [
                    'low'      => ['Normal', 'rgba(16,185,129,.12)',  '#059669'],
                    'medium'   => ['Orta',   'rgba(245,158,11,.12)', '#b45309'],
                    'high'     => ['Acil',   'rgba(239,68,68,.12)',  '#dc2626'],
                    'critical' => ['Kritik', 'rgba(124,58,237,.12)', '#7c3aed'],
                ];
                [$prioLabel, $prioBg, $prioFg] = $prioCfg[$fault->priority] ?? ['—','rgba(156,163,175,.12)','#6b7280'];
                $statusLabel = \App\Models\Fault::STATUSES[$fault->status] ?? $fault->status;
                $locationStr = $fault->faultLocation?->name ?? '';
                $areaStr     = $fault->faultArea?->name ?? '';
                $fullLoc     = $locationStr . ($areaStr ? ' / ' . $areaStr : '');
                $typeStr     = $fault->faultType?->name ?? '';
                $reporterStr = $fault->reporter?->name ?? '';
                $dateStr     = $fault->created_at->format('d.m.Y H:i');
                $deptColor   = $fault->department?->color ?? '#6366f1';
            @endphp

            <tr class="fault-main" style="border-left:3px solid {{ $borderHex }}">
                {{-- ID --}}
                <td>
                    <span style="font-weight:700;color:#e11d48;font-size:.82rem">#{{ $fault->id }}</span>
                </td>
                {{-- Başlık + Açıklama --}}
                <td style="max-width:260px">
                    <div style="font-weight:700;color:#1f2937;font-size:.88rem;line-height:1.3;margin-bottom:3px">
                        <a href="{{ route('faults.show', $fault) }}" style="text-decoration:none;color:inherit">{{ $fault->title }}</a>
                    </div>
                    @if($fault->description)
                    <div style="font-size:.77rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px">
                        {{ Str::limit($fault->description, 70) }}
                    </div>
                    @endif
                    @if($isMine)
                    <span style="font-size:.68rem;background:#eef2ff;color:#4f46e5;border-radius:5px;padding:1px 6px;display:inline-block;margin-top:2px">
                        <i class="fas fa-user" style="font-size:.55rem"></i> Ben bildirdim
                    </span>
                    @endif
                </td>
                {{-- Öncelik --}}
                <td>
                    <span class="prio-pill" style="background:{{ $prioBg }};color:{{ $prioFg }}">
                        <i class="fas fa-flag" style="font-size:.6rem"></i>{{ $prioLabel }}
                    </span>
                </td>
                {{-- Tür / Konum / Alan --}}
                <td style="max-width:200px">
                    @if($typeStr)
                    <div style="font-size:.82rem;font-weight:600;color:#374151">{{ $typeStr }}</div>
                    @endif
                    @if($fullLoc)
                    <div style="font-size:.77rem;color:#9ca3af;display:flex;align-items:center;gap:3px;margin-top:2px">
                        <i class="fas fa-map-marker-alt" style="color:#e11d48;font-size:.65rem"></i>{{ $fullLoc }}
                    </div>
                    @endif
                </td>
                {{-- Bildirilen Departman --}}
                <td>
                    @if($fault->department)
                    <span style="font-size:.78rem;background:{{ $deptColor }}22;color:{{ $deptColor }};border-radius:6px;padding:2px 8px;font-weight:600;display:inline-block">
                        {{ $fault->department->name }}
                    </span>
                    @else
                    <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                {{-- Bildiren --}}
                <td>
                    @if($reporterStr)
                    <div style="font-size:.83rem;color:#374151">{{ $reporterStr }}</div>
                    @else
                    <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                {{-- Tarih --}}
                <td>
                    <div style="font-size:.82rem;color:#374151;white-space:nowrap">{{ $fault->created_at->format('d.m.Y') }}</div>
                    <div style="font-size:.75rem;color:{{ $isOld ? '#ef4444' : '#9ca3af' }};white-space:nowrap" title="{{ $dateStr }}">
                        {{ $fault->created_at->diffForHumans() }}
                    </div>
                </td>
                {{-- Durum --}}
                <td>
                    <span class="status-pill" style="background:{{ $borderHex }}1a;color:{{ $borderHex }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                {{-- İşlem --}}
                <td class="text-end" style="padding-right:16px">
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="{{ route('faults.show', $fault) }}" class="act-btn act-btn-detail" title="Detay">
                            <i class="fas fa-eye"></i><span class="d-none d-xl-inline">Detay</span>
                        </a>
                        @if($isMine && $fault->status !== 'closed')
                        <button type="button" class="act-btn act-btn-update toggle-expand"
                                data-target="expand-{{ $fault->id }}" title="Durum güncelle">
                            <i class="fas fa-pen"></i><span class="d-none d-xl-inline">Güncelle</span>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Genişletilen güncelleme satırı --}}
            @if($isMine && $fault->status !== 'closed')
            <tr class="fault-expand" id="expand-{{ $fault->id }}">
                <td colspan="9">
                    <div class="expand-form">
                        <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1" style="font-size:.76rem;font-weight:600;color:#6b7280">Yeni Durum</label>
                                    <select name="status" class="form-select" required>
                                        <option value="" disabled selected>Seçin…</option>
                                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                            @if($val !== $fault->status)
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1" style="font-size:.76rem;font-weight:600;color:#6b7280">Güncelleme Notu <span style="color:#ef4444">*</span></label>
                                    <input type="text" name="note" class="form-control" placeholder="Güncelleme notu (zorunlu)…" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm w-100"
                                            style="background:#e11d48;color:#fff;border:none;border-radius:9px;padding:9px;font-size:.83rem;font-weight:600">
                                        <i class="fas fa-save me-1"></i>Kaydet
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            @endif

            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-paper-plane es-icon"></i>
                        <div style="font-weight:600;color:#374151;margin-bottom:4px">Bildirim bulunamadı</div>
                        @if(request()->anyFilled(['date_from','date_to','status','department_id','location_id','area_id']))
                        <div style="font-size:.82rem">Filtreleri temizleyerek tüm kayıtları görebilirsiniz.</div>
                        @else
                        <a href="{{ route('faults.create') }}" class="btn btn-sm btn-danger mt-3 fw-semibold px-4">
                            <i class="fas fa-plus me-1"></i>İlk Arızayı Bildir
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>

        @if($faults->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:center">
            {{ $faults->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
// ── Genişlet / daralt satır ──────────────────────────────────
document.querySelectorAll('.toggle-expand').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var targetId = this.dataset.target;
        var row = document.getElementById(targetId);
        if (!row) return;
        var isOpen = row.classList.contains('open');
        document.querySelectorAll('.fault-expand.open').forEach(function(r) { r.classList.remove('open'); });
        document.querySelectorAll('.act-btn-update.is-open').forEach(function(b) { b.classList.remove('is-open'); });
        if (!isOpen) {
            row.classList.add('open');
            this.classList.add('is-open');
        }
    });
});

// ── Konum → Alan cascade ─────────────────────────────────────
document.getElementById('location-select').addEventListener('change', function() {
    var locationId = this.value;
    var areaSelect = document.getElementById('area-select');
    areaSelect.innerHTML = '<option value="">— Tüm Alanlar —</option>';
    if (!locationId) return;
    fetch('{{ route("faults.ajax.areas") }}?location_id=' + locationId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            data.forEach(function(area) {
                var opt = document.createElement('option');
                opt.value = area.id;
                opt.textContent = area.name;
                areaSelect.appendChild(opt);
            });
        });
});
</script>
@endpush

@endsection
