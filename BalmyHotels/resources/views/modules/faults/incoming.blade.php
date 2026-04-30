@extends('layouts.default')

@push('styles')
<style>
/* ── Hero ───────────────────────────────────────────────────── */
.page-hero { background:#fff;border-radius:16px;margin-bottom:24px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden;display:flex;align-items:stretch; }
.page-hero-stripe { width:6px;flex-shrink:0;background:linear-gradient(180deg,#4361ee,#3a0ca3);border-radius:16px 0 0 16px; }
.page-hero-icon   { width:68px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:linear-gradient(135deg,#4361ee,#3a0ca3);font-size:1.55rem;color:#fff; }
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
.filter-card .form-control:focus,.filter-card .form-select:focus { border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.15);outline:none; }
/* ── Faults table ───────────────────────────────────────────── */
.faults-card { background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden; }
.faults-card thead tr { background:#f8f9fb; }
.faults-card thead th { font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;padding:13px 16px;border-bottom:1.5px solid #f3f4f6;white-space:nowrap; }
.faults-card tbody td { padding:12px 16px;vertical-align:middle;border-bottom:1px solid #f9fafb;font-size:.86rem; }
.faults-card tbody tr.fault-main:last-of-type td { border-bottom:none; }
.faults-card tbody tr.fault-main:hover { background:#fafbff; }
.faults-card tbody tr.fault-expand { display:none; }
.faults-card tbody tr.fault-expand.open { display:table-row; }
.faults-card tbody tr.fault-expand td { padding:0;border-bottom:1px solid #f3f4f6;background:#f8fafc; }
/* ── Priority badge ─────────────────────────────────────────── */
.prio-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:700;white-space:nowrap; }
/* ── Status badge ───────────────────────────────────────────── */
.status-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:.74rem;font-weight:700;white-space:nowrap; }
/* ── Action buttons ─────────────────────────────────────────── */
.act-btn { display:inline-flex;align-items:center;justify-content:center;height:30px;padding:0 10px;gap:5px;border-radius:7px;border:none;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;white-space:nowrap; }
.act-btn-detail  { background:rgba(67,97,238,.1);color:#4361ee; }
.act-btn-detail:hover { background:#4361ee;color:#fff; }
.act-btn-copy    { background:rgba(16,185,129,.1);color:#059669; }
.act-btn-copy:hover { background:#059669;color:#fff; }
.act-btn-update  { background:rgba(245,158,11,.1);color:#b45309; }
.act-btn-update:hover { background:#d97706;color:#fff; }
.act-btn-update.is-open { background:#d97706;color:#fff; }
/* ── Expand form ────────────────────────────────────────────── */
.expand-form { padding:16px 20px; }
.expand-form .form-control,.expand-form .form-select { border:1.5px solid #e5e7eb;border-radius:9px;font-size:.82rem;padding:8px 12px;height:auto;background:#fff;color:#1f2937; }
.expand-form .form-control:focus,.expand-form .form-select:focus { border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.12);outline:none; }
/* ── Empty ──────────────────────────────────────────────────── */
.empty-state { padding:56px 24px;text-align:center;color:#9ca3af; }
.empty-state .es-icon { font-size:2.5rem;margin-bottom:12px;opacity:.25;display:block; }
/* ── New-fault toast ────────────────────────────────────────── */
@keyframes nfSlideIn { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:translateX(0)} }
.nf-toast { animation:nfSlideIn .3s ease;transition:opacity .3s,transform .3s; }
.fault-new-highlight { background:#fff9e6 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Gelen Arızalar</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Gelen Arızalar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="page-hero">
        <div class="page-hero-stripe"></div>
        <div class="page-hero-icon"><i class="fas fa-inbox"></i></div>
        <div class="page-hero-body">
            <h3>Gelen Arızalar @if($dept)<span style="font-weight:400;color:#6b7280;font-size:.9rem">— {{ $dept->name }}</span>@endif</h3>
            <p>Departmanınıza iletilen arıza bildirimlerini buradan takip edip güncelleyebilirsiniz</p>
        </div>
        @if(isset($stats) && $stats['avg_hours'] !== null)
        <div class="page-hero-actions">
            <div style="text-align:center">
                <div style="font-size:1.5rem;font-weight:800;color:#4361ee;line-height:1">{{ $stats['avg_hours'] }}<span style="font-size:.85rem;font-weight:500;color:#6b7280"> sa</span></div>
                <div style="font-size:.72rem;color:#9ca3af;margin-top:2px">Ort. Çözüm</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Stat chips --}}
    @if(isset($stats))
    <div class="stat-chips">
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(67,97,238,.12);color:#4361ee"><i class="fas fa-clipboard-list"></i></div>
            <div><div class="sc-num" style="color:#4361ee">{{ $stats['total'] }}</div><div class="sc-lbl">Toplam</div></div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="fas fa-exclamation-circle"></i></div>
            <div><div class="sc-num" style="color:#ef4444">{{ $stats['open'] }}</div><div class="sc-lbl">Açık</div></div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(249,115,22,.12);color:#f97316"><i class="fas fa-tools"></i></div>
            <div><div class="sc-num" style="color:#f97316">{{ $stats['in_progress'] }}</div><div class="sc-lbl">İşlemde</div></div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fas fa-check-double"></i></div>
            <div><div class="sc-num" style="color:#059669">{{ $stats['closed'] }}</div><div class="sc-lbl">Kapalı</div></div>
        </div>
    </div>
    @endif

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('faults.incoming') }}" class="row g-2 align-items-end" id="filter-form">
            {{-- Tarih --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-calendar-alt me-1"></i>Başlangıç Tarihi</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-calendar-alt me-1"></i>Bitiş Tarihi</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            {{-- Durum --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-circle me-1"></i>Durum</label>
                <select name="status" class="form-select">
                    <option value="">— Tüm Durumlar —</option>
                    @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Arıza Türü --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-wrench me-1"></i>Arıza Türü</label>
                <select name="fault_type" class="form-select">
                    <option value="">— Tüm Türler —</option>
                    @foreach($faultTypes as $ft)
                        <option value="{{ $ft->id }}" @selected(request('fault_type') == $ft->id)>{{ $ft->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Konum --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-map-marker-alt me-1"></i>Konum</label>
                <select name="location_id" class="form-select" id="location-select">
                    <option value="">— Tüm Konumlar —</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Alan --}}
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-layer-group me-1"></i>Alan</label>
                <select name="area_id" class="form-select" id="area-select">
                    <option value="">— Tüm Alanlar —</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Arama --}}
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280"><i class="fas fa-search me-1"></i>Arama</label>
                <input type="text" name="search" class="form-control" placeholder="Başlık veya açıklama ara…" value="{{ request('search') }}">
            </div>
            <div class="col-md-9 d-flex gap-2 align-items-end flex-wrap">
                <button type="submit" class="btn btn-sm"
                    style="background:#4361ee;color:#fff;border-radius:9px;padding:9px 18px;font-size:.83rem;font-weight:600;border:none">
                    <i class="fas fa-search me-1"></i>Ara
                </button>
                @if(request('search') || request('status') || request('fault_type') || request('location_id') || request('area_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('faults.incoming') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;padding:9px 14px">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <button type="button" id="print-btn"
                    style="background:rgba(124,58,237,.1);color:#7c3aed;border:none;border-radius:9px;padding:9px 16px;font-size:.83rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px">
                    <i class="fas fa-print"></i><span class="d-none d-sm-inline">Yazdır</span>
                </button>
                <span class="ms-auto" style="font-size:.78rem;color:#9ca3af;white-space:nowrap">
                    {{ $faults->total() }} kayıt
                </span>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="faults-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Arıza</th>
                    <th>Öncelik</th>
                    <th>Tür · Konum</th>
                    <th>Bildiren</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th class="text-end" style="padding-right:20px">İşlem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($faults as $fault)
            @php
                $statusColor = \App\Models\Fault::STATUS_COLORS[$fault->status] ?? 'secondary';
                $borderHexMap = ['danger'=>'#ef4444','warning'=>'#f97316','info'=>'#0ea5e9','success'=>'#10b981','primary'=>'#4361ee','secondary'=>'#94a3b8'];
                $borderHex = $borderHexMap[$statusColor] ?? '#94a3b8';
                $isOld = $fault->created_at->diffInHours(now()) > 24;

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

                $copyText = implode("\n", array_filter([
                    '� ' . $dateStr,
                    $typeStr ? '🔩 ' . $typeStr : '',
                    $fullLoc  ? '📍 ' . $fullLoc : '',
                    $fault->description ? '📝 ' . $fault->description : '',
                ]));
            @endphp

            {{-- Ana satır --}}
            <tr class="fault-main" style="border-left:3px solid {{ $borderHex }}">
                {{-- ID --}}
                <td>
                    <span style="font-weight:700;color:#4361ee;font-size:.82rem">#{{ $fault->id }}</span>
                </td>
                {{-- Başlık + Açıklama --}}
                <td style="max-width:280px">
                    <div style="font-weight:700;color:#1f2937;font-size:.88rem;line-height:1.3;margin-bottom:3px">
                        <a href="{{ route('faults.show', $fault) }}" style="text-decoration:none;color:inherit">{{ $fault->title }}</a>
                    </div>
                    @if($fault->description)
                    <div style="font-size:.77rem;color:#9ca3af;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px">
                        {{ Str::limit($fault->description, 80) }}
                    </div>
                    @endif
                    @if($fault->image_path)
                    <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank"
                       style="font-size:.72rem;color:#f97316;text-decoration:none;display:inline-flex;align-items:center;gap:3px;margin-top:3px">
                        <i class="fas fa-camera"></i> Fotoğraf
                    </a>
                    @endif
                </td>
                {{-- Öncelik --}}
                <td>
                    <span class="prio-pill" style="background:{{ $prioBg }};color:{{ $prioFg }}">
                        <i class="fas fa-flag" style="font-size:.6rem"></i>{{ $prioLabel }}
                    </span>
                </td>
                {{-- Tür / Konum --}}
                <td style="max-width:200px">
                    @if($typeStr)
                    <div style="font-size:.82rem;font-weight:600;color:#374151">{{ $typeStr }}</div>
                    @endif
                    @if($fullLoc)
                    <div style="font-size:.77rem;color:#9ca3af;display:flex;align-items:center;gap:3px;margin-top:2px">
                        <i class="fas fa-map-marker-alt" style="color:#4361ee;font-size:.65rem"></i>{{ $fullLoc }}
                    </div>
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
                        <button type="button" class="act-btn act-btn-copy copy-fault-btn"
                                data-copy="{{ e($copyText) }}" title="WhatsApp için kopyala">
                            <i class="fas fa-copy"></i><span class="d-none d-xl-inline">Kopyala</span>
                        </button>
                        @if($canUpdate && $fault->status !== 'closed')
                        <button type="button" class="act-btn act-btn-update toggle-expand"
                                data-target="expand-{{ $fault->id }}" title="Durum güncelle">
                            <i class="fas fa-pen"></i><span class="d-none d-xl-inline">Güncelle</span>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Genişletilen güncelleme satırı --}}
            @if($canUpdate && $fault->status !== 'closed')
            <tr class="fault-expand" id="expand-{{ $fault->id }}">
                <td colspan="8">
                    <div class="expand-form">
                        <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1" style="font-size:.76rem;font-weight:600;color:#6b7280">Yeni Durum</label>
                                    <select name="status" class="form-select" required>
                                        <option value="" disabled selected>Seçin…</option>
                                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                            @if($val !== $fault->status && $val !== 'resolved')
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1" style="font-size:.76rem;font-weight:600;color:#6b7280">Açıklama <span style="color:#ef4444">*</span></label>
                                    <input type="text" name="note" class="form-control" placeholder="Güncelleme notu (zorunlu)…" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm w-100"
                                            style="background:#4361ee;color:#fff;border:none;border-radius:9px;padding:9px;font-size:.83rem;font-weight:600">
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
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-inbox es-icon"></i>
                        <div style="font-weight:600;color:#374151;margin-bottom:4px">Arıza kaydı bulunamadı</div>
                        @if(request('search') || request('status'))
                        <div style="font-size:.82rem">Filtreleri temizleyerek tüm kayıtları görebilirsiniz.</div>
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

{{-- Kopyala toast --}}
<div id="copy-toast"
     style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:#111827;color:#fff;
            padding:.65rem 1.2rem;border-radius:10px;font-size:.84rem;box-shadow:0 4px 20px rgba(0,0,0,.28);
            align-items:center;gap:.6rem">
    <i class="fas fa-check-circle" style="color:#10b981"></i>
    <span>Kopyalandı! WhatsApp'a yapıştırabilirsiniz.</span>
</div>

{{-- Yeni arıza toast kapsayıcısı --}}
<div id="new-fault-toasts"
     style="position:fixed;bottom:5rem;right:1.5rem;z-index:9998;
            display:flex;flex-direction:column;gap:10px;max-width:340px;pointer-events:auto"></div>

@push('scripts')
<script>
// ── Konum → Alan cascade ─────────────────────────────────────────────────────
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

// ── Yazdırma ────────────────────────────────────────────────────────────────
document.getElementById('print-btn').addEventListener('click', function() {
    var deptName = @json($dept->name ?? 'Departman');
    var today    = new Date().toLocaleString('tr-TR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});

    var filterParts = [];
    var searchEl = document.querySelector('[name="search"]');
    var statusEl = document.querySelector('[name="status"]');
    var typeEl   = document.querySelector('[name="fault_type"]');
    if (searchEl && searchEl.value) filterParts.push('Arama: \u201c' + searchEl.value + '\u201d');
    if (statusEl && statusEl.value) filterParts.push('Durum: ' + statusEl.options[statusEl.selectedIndex].text);
    if (typeEl   && typeEl.value)   filterParts.push('T\u00fcr: '   + typeEl.options[typeEl.selectedIndex].text);

    var rows = document.querySelectorAll('.faults-card tbody tr.fault-main');
    if (!rows.length) { alert('Yazd\u0131r\u0131lacak kay\u0131t yok.'); return; }

    var tableRows = '';
    rows.forEach(function(tr) {
        var cells = tr.querySelectorAll('td');
        if (cells.length < 7) return;
        var borderColor = tr.style.borderLeftColor || '#94a3b8';
        var id         = cells[0].textContent.trim();
        var titleA     = cells[1].querySelector('a');
        var title      = titleA ? titleA.textContent.trim() : cells[1].textContent.trim();
        var descDivs   = cells[1].querySelectorAll('div');
        var desc       = descDivs[1] ? descDivs[1].textContent.trim() : '';
        var prioPill   = cells[2].querySelector('.prio-pill');
        var prioBg     = prioPill ? prioPill.style.background   : 'rgba(156,163,175,.12)';
        var prioFg     = prioPill ? prioPill.style.color        : '#6b7280';
        var prioText   = prioPill ? prioPill.textContent.trim() : '\u2014';
        var typeDivs   = cells[3].querySelectorAll('div');
        var typeText   = typeDivs[0] ? typeDivs[0].textContent.trim() : '';
        var locText    = typeDivs[1] ? typeDivs[1].textContent.trim() : '';
        var reporter   = cells[4].textContent.trim();
        var dateEl     = cells[5].querySelector('div');
        var dateText   = dateEl ? dateEl.textContent.trim() : '';
        var statusPill = cells[6].querySelector('.status-pill');
        var statusBg   = statusPill ? statusPill.style.background   : '';
        var statusFg   = statusPill ? statusPill.style.color        : '#1f2937';
        var statusText = statusPill ? statusPill.textContent.trim() : '';

        tableRows += '<tr style="border-left:3px solid ' + borderColor + ';page-break-inside:avoid">'
            + '<td style="font-weight:700;color:#4361ee;white-space:nowrap;font-size:9pt">' + id + '</td>'
            + '<td><div style="font-weight:600;font-size:9.5pt">' + title + '</div>'
                + (desc ? '<div style="font-size:8pt;color:#6b7280;margin-top:1px">' + desc + '</div>' : '')
            + '</td>'
            + '<td><span style="display:inline-block;padding:2px 8px;border-radius:5px;font-size:8pt;font-weight:700;background:' + prioBg + ';color:' + prioFg + '">' + prioText + '</span></td>'
            + '<td>' + (typeText ? '<div style="font-weight:600;font-size:9pt">' + typeText + '</div>' : '')
                     + (locText  ? '<div style="font-size:8pt;color:#6b7280;margin-top:1px">' + locText + '</div>' : '')
            + '</td>'
            + '<td style="white-space:nowrap;font-size:9pt">' + reporter + '</td>'
            + '<td style="white-space:nowrap;font-size:9pt">' + dateText + '</td>'
            + '<td><span style="display:inline-block;padding:2px 8px;border-radius:5px;font-size:8pt;font-weight:700;background:' + statusBg + ';color:' + statusFg + '">' + statusText + '</span></td>'
            + '</tr>';
    });

    var html = '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8">'
        + '<title>Gelen Ar\u0131zalar \u2014 ' + deptName + '</title>'
        + '<style>'
        + '*{box-sizing:border-box;margin:0;padding:0}'
        + 'body{font-family:"Segoe UI",Arial,sans-serif;font-size:10pt;color:#1f2937;background:#fff}'
        + '@page{margin:1.5cm 2cm;size:A4 landscape}'
        + '.ph{display:flex;justify-content:space-between;align-items:flex-end;padding-bottom:12px;border-bottom:3px solid #4361ee;margin-bottom:18px}'
        + '.ph h1{font-size:15pt;color:#1f2937;font-weight:800}'
        + '.ph-sub{font-size:9pt;color:#6b7280;margin-top:3px}'
        + '.ph-filters{font-size:8.5pt;color:#7c3aed;margin-top:4px;font-weight:600}'
        + '.ph-right{text-align:right;font-size:8.5pt;color:#6b7280;line-height:1.6}'
        + 'table{width:100%;border-collapse:collapse}'
        + 'thead tr{background:#f1f5f9}'
        + 'th{font-size:7.5pt;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;white-space:nowrap}'
        + 'td{padding:7px 10px;vertical-align:top;border-bottom:1px solid #f0f2f5}'
        + 'tbody tr:nth-child(even) td{background:#fafbff}'
        + 'tbody tr:last-child td{border-bottom:none}'
        + '.footer{margin-top:14px;font-size:8pt;color:#9ca3af;text-align:right;border-top:1px solid #e5e7eb;padding-top:8px}'
        + '</style></head><body>'
        + '<div class="ph">'
        +   '<div><h1>\uD83D\uDD27 Gelen Ar\u0131zalar</h1>'
        +   '<div class="ph-sub">' + deptName + '</div>'
        +   (filterParts.length ? '<div class="ph-filters">Filtre: ' + filterParts.join(' &middot; ') + '</div>' : '')
        +   '</div>'
        +   '<div class="ph-right">Yazd\u0131r\u0131lma: ' + today + '<br/><strong>' + rows.length + '</strong> kay\u0131t</div>'
        + '</div>'
        + '<table><thead><tr>'
        + '<th>#</th><th>Ar\u0131za</th><th>\u00d6ncelik</th><th>T\u00fcr &middot; Konum</th><th>Bildiren</th><th>Tarih</th><th>Durum</th>'
        + '</tr></thead><tbody>' + tableRows + '</tbody></table>'
        + '<div class="footer">Toplam ' + rows.length + ' kay\u0131t g\u00f6sterilmektedir \u2014 ' + today + '</div>'
        + '</body></html>';

    var w = window.open('', '_blank', 'width=1100,height=700');
    if (!w) { alert('L\u00fctfen popup engelleyicisini kapat\u0131n ve tekrar deneyin.'); return; }
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(function() { w.print(); }, 600);
});

// ── Güncelle satır aç/kapat
document.querySelectorAll('.toggle-expand').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id  = this.getAttribute('data-target');
        var row = document.getElementById(id);
        var open = row.classList.toggle('open');
        this.classList.toggle('is-open', open);
        if (open) {
            var input = row.querySelector('input[name="note"],select[name="status"]');
            if (input) input.focus();
        }
    });
});

// Kopyala
function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
}

function bindCopyBtn(btn) {
    if (btn._bound) return;
    btn._bound = true;
    btn.addEventListener('click', function() {
        var text = this.getAttribute('data-copy');
        var self = this;
        var origHTML = self.innerHTML;

        function showSuccess() {
            self.innerHTML = '<i class="fas fa-check"></i>';
            self.style.background = '#059669';
            self.style.color = '#fff';
            self.disabled = true;
            var toast = document.getElementById('copy-toast');
            toast.style.display = 'flex';
            setTimeout(function() {
                self.innerHTML = origHTML;
                self.style.background = '';
                self.style.color = '';
                self.disabled = false;
                toast.style.display = 'none';
            }, 2500);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showSuccess).catch(function() { fallbackCopy(text); showSuccess(); });
        } else {
            fallbackCopy(text); showSuccess();
        }
    });
}
document.querySelectorAll('.copy-fault-btn').forEach(bindCopyBtn);

// ── Otomatik arıza yenileme (polling) ────────────────────────
(function() {
    var lastId    = {{ $faults->isNotEmpty() ? $faults->max('id') : 0 }};
    var pollUrl   = "{{ route('faults.ajax.new-incoming') }}";
    var canUpdate = {{ $canUpdate ? 'true' : 'false' }};
    var filtered  = {{ (request('search') || request('status')) ? 'true' : 'false' }};

    var STATUS_HEX = {
        'danger':'#ef4444','warning':'#f97316','info':'#0ea5e9',
        'success':'#10b981','primary':'#4361ee','secondary':'#94a3b8'
    };
    var STATUS_LABELS = {
        'open':'Açık','in_progress':'İşlemde','winter_plan':'Kış Planı',
        'waiting_material':'Malzeme Bekliyor','resolved':'Çözüldü','closed':'Kapalı'
    };
    var PRIO = {
        'low':     ['Normal','rgba(16,185,129,.12)','#059669'],
        'medium':  ['Orta',  'rgba(245,158,11,.12)','#b45309'],
        'high':    ['Acil',  'rgba(239,68,68,.12)', '#dc2626'],
        'critical':['Kritik','rgba(124,58,237,.12)','#7c3aed']
    };

    function esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s || ''));
        return d.innerHTML;
    }

    function showNewFaultToast(fault) {
        var container = document.getElementById('new-fault-toasts');
        if (!container) return;
        var hex   = STATUS_HEX[fault.status_color] || '#94a3b8';
        var loc   = fault.location_name + (fault.area_name ? ' / ' + fault.area_name : '');
        var meta  = [fault.type_name, loc].filter(Boolean).join(' · ');
        var toast = document.createElement('div');
        toast.className = 'nf-toast';
        toast.style.cssText = 'background:#fff;border-radius:12px;border-left:4px solid '+hex+';box-shadow:0 4px 24px rgba(0,0,0,.18);padding:14px 16px;position:relative;';
        toast.innerHTML =
            '<div style="display:flex;align-items:flex-start;gap:10px">'
            + '<div style="width:32px;height:32px;background:rgba(239,68,68,.12);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#ef4444"><i class="fas fa-bell"></i></div>'
            + '<div style="flex:1;min-width:0">'
                + '<div style="font-size:.68rem;font-weight:700;color:#ef4444;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Yeni Arıza #'+esc(String(fault.id))+'</div>'
                + '<div style="font-size:.86rem;font-weight:700;color:#1f2937;line-height:1.3;margin-bottom:4px">'+esc(fault.title)+'</div>'
                + (meta ? '<div style="font-size:.76rem;color:#6b7280">'+esc(meta)+'</div>' : '')
                + '<div style="font-size:.72rem;color:#9ca3af;margin-top:2px">'+esc(fault.created_at)+'</div>'
                + '<a href="'+esc(fault.show_url)+'" style="display:inline-block;margin-top:8px;font-size:.76rem;font-weight:600;color:#4361ee;text-decoration:none;background:rgba(67,97,238,.1);padding:4px 10px;border-radius:6px">Görüntüle →</a>'
            + '</div>'
            + '<button onclick="this.closest(\'.nf-toast\').remove()" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.2rem;padding:0;line-height:1;flex-shrink:0;margin-left:4px">×</button>'
            + '</div>';
        container.appendChild(toast);
        setTimeout(function() {
            if (!toast.parentNode) return;
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(30px)';
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
        }, 8000);
    }

    function renderFaultRow(fault) {
        var hex  = STATUS_HEX[fault.status_color] || '#94a3b8';
        var p    = PRIO[fault.priority] || ['—','rgba(156,163,175,.12)','#6b7280'];
        var slbl = STATUS_LABELS[fault.status] || fault.status;
        var loc  = fault.location_name + (fault.area_name ? ' / ' + fault.area_name : '');
        var photo = fault.image_url
            ? '<a href="'+esc(fault.image_url)+'" target="_blank" style="font-size:.72rem;color:#f97316;text-decoration:none;display:inline-flex;align-items:center;gap:3px;margin-top:3px"><i class="fas fa-camera"></i> Fotoğraf</a>'
            : '';
        var updateBtn = (canUpdate && fault.status !== 'closed')
            ? '<button type="button" class="act-btn act-btn-update toggle-expand" data-target="expand-'+fault.id+'" title="Durum güncelle"><i class="fas fa-pen"></i><span class="d-none d-xl-inline">Güncelle</span></button>'
            : '';
        var copyParts = ['\u{1F550} ' + fault.created_at];
        if (fault.type_name) copyParts.push('\uD83D\uDD29 ' + fault.type_name);
        if (loc) copyParts.push('\uD83D\uDCCD ' + loc);
        if (fault.description) copyParts.push('\uD83D\uDCDD ' + fault.description);
        var copyBtn = '<button type="button" class="act-btn act-btn-copy copy-fault-btn" data-copy="'+esc(copyParts.join('\n'))+'" title="WhatsApp için kopyala"><i class="fas fa-copy"></i><span class="d-none d-xl-inline">Kopyala</span></button>';
        return '<tr class="fault-main fault-new-highlight" style="border-left:3px solid '+hex+'">'
            + '<td><span style="font-weight:700;color:#4361ee;font-size:.82rem">#'+esc(String(fault.id))+'</span></td>'
            + '<td style="max-width:280px">'
                + '<div style="font-weight:700;color:#1f2937;font-size:.88rem;line-height:1.3;margin-bottom:3px"><a href="'+esc(fault.show_url)+'" style="text-decoration:none;color:inherit">'+esc(fault.title)+'</a></div>'
                + (fault.description ? '<div style="font-size:.77rem;color:#9ca3af;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px">'+esc(fault.description.substring(0,80))+'</div>' : '')
                + photo
            + '</td>'
            + '<td><span class="prio-pill" style="background:'+p[1]+';color:'+p[2]+'"><i class="fas fa-flag" style="font-size:.6rem"></i>'+esc(p[0])+'</span></td>'
            + '<td style="max-width:200px">'
                + (fault.type_name ? '<div style="font-size:.82rem;font-weight:600;color:#374151">'+esc(fault.type_name)+'</div>' : '')
                + (loc ? '<div style="font-size:.77rem;color:#9ca3af;display:flex;align-items:center;gap:3px;margin-top:2px"><i class="fas fa-map-marker-alt" style="color:#4361ee;font-size:.65rem"></i>'+esc(loc)+'</div>' : '')
            + '</td>'
            + '<td><div style="font-size:.83rem;color:#374151">'+(fault.reporter_name ? esc(fault.reporter_name) : '<span style="color:#d1d5db">—</span>')+'</div></td>'
            + '<td><div style="font-size:.82rem;color:#374151;white-space:nowrap">'+esc(fault.created_at.split(' ')[0])+'</div><div style="font-size:.75rem;color:#9ca3af;white-space:nowrap">'+esc(fault.created_at)+'</div></td>'
            + '<td><span class="status-pill" style="background:'+hex+'1a;color:'+hex+'">'+esc(slbl)+'</span></td>'
            + '<td class="text-end" style="padding-right:16px"><div class="d-flex gap-1 justify-content-end"><a href="'+esc(fault.show_url)+'" class="act-btn act-btn-detail" title="Detay"><i class="fas fa-eye"></i><span class="d-none d-xl-inline">Detay</span></a>'+copyBtn+updateBtn+'</div></td>'
            + '</tr>';
    }

    function playBeep() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.type = 'sine'; osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.25, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.5);
        } catch(e) {}
    }

    function bindNewRows(tbody) {
        tbody.querySelectorAll('.toggle-expand').forEach(function(btn) {
            if (btn._bound) return;
            btn._bound = true;
            btn.addEventListener('click', function() {
                var row = document.getElementById(this.getAttribute('data-target'));
                if (!row) return;
                var open = row.classList.toggle('open');
                this.classList.toggle('is-open', open);
                if (open) { var inp = row.querySelector('input,select'); if (inp) inp.focus(); }
            });
        });
        tbody.querySelectorAll('.copy-fault-btn').forEach(bindCopyBtn);
    }

    function updateChip(idx, delta) {
        var chips = document.querySelectorAll('.stat-chip .sc-num');
        if (chips[idx]) chips[idx].textContent = (parseInt(chips[idx].textContent) || 0) + delta;
    }

    function poll() {
        fetch(pollUrl + '?last_id=' + lastId, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { if (!r.ok) throw 0; return r.json(); })
        .then(function(data) {
            if (!data.faults || !data.faults.length) return;
            playBeep();
            data.faults.forEach(function(fault) {
                if (fault.id > lastId) lastId = fault.id;
                showNewFaultToast(fault);
                if (!filtered) {
                    var tbody = document.querySelector('.faults-card tbody');
                    if (tbody) {
                        var empty = tbody.querySelector('.empty-state');
                        if (empty) empty.closest('tr').remove();
                        var tmp = document.createElement('tbody');
                        tmp.innerHTML = renderFaultRow(fault);
                        var newTr = tmp.firstElementChild;
                        newTr.style.transition = 'background 2s ease';
                        tbody.insertBefore(newTr, tbody.firstChild);
                        bindNewRows(tbody);
                        setTimeout(function() { newTr.classList.remove('fault-new-highlight'); }, 3000);
                    }
                }
                updateChip(0, 1); // Toplam
                if (fault.status === 'open') updateChip(1, 1); // Açık
            });
        })
        .catch(function() {})
        .finally(function() { setTimeout(poll, 30000); });
    }

    setTimeout(poll, 30000);
})();
</script>
@endpush
@endsection