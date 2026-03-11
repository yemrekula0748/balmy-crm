@extends('layouts.default')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>İ.K Raporu</h4>
                <span>İnsan Kaynakları — Şube Bazlı Personel Devam & Çalışma Analizleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.index') }}">Kapı Giriş/Çıkış</a></li>
                <li class="breadcrumb-item active">İ.K Raporu</li>
            </ol>
        </div>
    </div>

    {{-- FİLTRE --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="card-title mb-0 d-flex align-items-center gap-2 fw-bold">
                <i class="fas fa-filter text-primary"></i> Filtrele
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('door-logs.hr-report') }}" class="row g-3 align-items-end" id="hrFilterForm">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px">Başlangıç Tarihi</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $filters['dateFrom'] }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px">Bitiş Tarihi</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $filters['dateTo'] }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px">Şube</label>
                    <select name="branch_id" id="branchFilter" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected($filters['branchId'] == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:13px">Kişi</label>
                    <select name="user_id" id="userFilter" class="form-select form-select-sm">
                        <option value="">Tüm Personel</option>
                        @foreach($deptManagers as $dm)
                            <option value="{{ $dm->id }}" @selected($filters['userId'] == $dm->id)>{{ $dm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('door-logs.hr-report') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-redo me-1"></i> Sıfırla
                    </a>
                </div>
                <div class="col-md-2 col-sm-6">
                    <a id="pdfBtn"
                       href="{{ route('door-logs.hr-report-pdf') }}?{{ http_build_query(request()->query()) }}"
                       target="_blank"
                       class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-file-pdf me-1"></i> PDF Rapor
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI KARTLARI --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(67,97,238,.1);color:#4361ee">
                    <i class="fas fa-users"></i>
                </div>
                <div class="hr-kpi-val text-primary">{{ $summary['total_staff'] }}</div>
                <div class="hr-kpi-lbl">Aktif Personel</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(16,185,129,.1);color:#10b981">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="hr-kpi-val" style="color:#10b981">{{ $summary['total_entries'] }}</div>
                <div class="hr-kpi-lbl">Toplam Giriş</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(249,115,22,.1);color:#f97316">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="hr-kpi-val" style="color:#f97316">{{ $summary['total_exits'] }}</div>
                <div class="hr-kpi-lbl">Toplam Çıkış</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="hr-kpi-val" style="color:#6366f1">{{ $summary['total_hours'] }}</div>
                <div class="hr-kpi-lbl">Toplam Çalışma (sa)</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(245,158,11,.1);color:#f59e0b">
                    <i class="fas fa-business-time"></i>
                </div>
                <div class="hr-kpi-val" style="color:#f59e0b">{{ $summary['total_overtime'] }}</div>
                <div class="hr-kpi-lbl">Fazla Mesai (sa)</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(239,68,68,.1);color:#ef4444">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="hr-kpi-val text-danger">{{ $summary['total_late'] }}</div>
                <div class="hr-kpi-lbl">Geç Giriş (09:00 sonrası)</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-kpi-card">
                <div class="hr-kpi-icon" style="background:rgba(20,184,166,.1);color:#14b8a6">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="hr-kpi-val" style="color:#14b8a6">{{ $summary['avg_daily'] }}</div>
                <div class="hr-kpi-lbl">Günlük Ort. (sa/personel)</div>
            </div>
        </div>
    </div>

    {{-- ANA İÇERİK SATIRI --}}
    <div class="row g-4 mb-4">

        {{-- Günlük Aktivite Grafiği --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="fas fa-chart-line text-primary"></i>
                        Günlük Toplam Çalışma Saati
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Departman Özeti --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="fas fa-sitemap" style="color:#6366f1"></i>
                        Departman Özeti
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="font-size:12px">Departman</th>
                                    <th class="text-center" style="font-size:12px">Personel</th>
                                    <th class="text-center" style="font-size:12px">Toplam (sa)</th>
                                    <th class="text-center" style="font-size:12px">Fazla (sa)</th>
                                    <th class="text-center" style="font-size:12px">Geç</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptSummary as $ds)
                                <tr>
                                    <td class="ps-3 fw-semibold" style="font-size:12px">{{ $ds['department'] }}</td>
                                    <td class="text-center" style="font-size:12px">{{ $ds['count'] }}</td>
                                    <td class="text-center" style="font-size:12px">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                            {{ $ds['total_hours'] }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="font-size:12px">
                                        @if($ds['overtime_hrs'] > 0)
                                            <span class="badge bg-warning bg-opacity-15 text-warning fw-semibold">
                                                {{ $ds['overtime_hrs'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="font-size:12px">
                                        @if($ds['late_entries'] > 0)
                                            <span class="text-danger fw-semibold">{{ $ds['late_entries'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3" style="font-size:12px">
                                        Veri bulunamadı
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- PERSONEL BAZLI ÇALIŞMA SAATLERİ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-user-clock text-success"></i>
                Personel Bazlı Çalışma Raporu
            </h6>
            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold" style="font-size:12px">
                {{ count($userStats) }} personel
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="staffTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="font-size:12px">#</th>
                            <th style="font-size:12px">Ad Soyad</th>
                            <th style="font-size:12px">Şube</th>
                            <th style="font-size:12px">Departman</th>
                            <th class="text-center" style="font-size:12px">Çalışılan Gün</th>
                            <th class="text-center" style="font-size:12px">Giriş / Çıkış</th>
                            <th class="text-center" style="font-size:12px">Toplam</th>
                            <th class="text-center" style="font-size:12px">Günlük Ort.</th>
                            <th class="text-center" style="font-size:12px">Fazla Mesai</th>
                            <th class="text-center" style="font-size:12px">Geç Giriş</th>
                            <th class="text-center" style="font-size:12px">Shift</th>
                            <th class="text-center" style="font-size:12px">İlk/Son</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userStats as $i => $s)
                        <tr>
                            <td class="ps-3 text-muted" style="font-size:12px">{{ $i + 1 }}</td>
                            <td style="font-size:13px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hr-avatar">{{ strtoupper(mb_substr(optional($s['user'])->name ?? '?', 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold" style="line-height:1.2">{{ optional($s['user'])->name ?? '-' }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ optional(optional($s['user'])->department)->name ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:12px">{{ $s['branch'] }}</td>
                            <td style="font-size:12px">{{ $s['department'] }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3"
                                      style="background:rgba(67,97,238,.1);color:#4361ee;font-size:12px;font-weight:600">
                                    {{ $s['worked_days'] }}
                                </span>
                            </td>
                            <td class="text-center" style="font-size:12px">
                                <span class="text-success fw-semibold">{{ $s['entry_count'] }}</span>
                                <span class="text-muted mx-1">/</span>
                                <span class="text-warning fw-semibold">{{ $s['exit_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold" style="font-size:13px;color:#4361ee">{{ $s['total_hours'] }} sa</span>
                            </td>
                            <td class="text-center">
                                <span style="font-size:12px;color:#6366f1">{{ $s['avg_hours'] }} sa</span>
                            </td>
                            <td class="text-center">
                                @if($s['overtime_hrs'] > 0)
                                    <span class="badge" style="background:rgba(245,158,11,.15);color:#d97706;font-size:11px">
                                        +{{ $s['overtime_hrs'] }} sa
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:11px">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s['late_entries'] > 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:11px">
                                        {{ $s['late_entries'] }} kez
                                    </span>
                                @else
                                    <span class="text-success" style="font-size:11px"><i class="fas fa-check"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge" style="
                                    font-size:10px;
                                    {{ $s['dominant_shift'] === '09-17' ? 'background:rgba(59,130,246,.12);color:#2563eb' : ($s['dominant_shift'] === '16-24' ? 'background:rgba(245,158,11,.12);color:#b45309' : 'background:rgba(139,92,246,.12);color:#7c3aed') }}
                                ">
                                    {{ $s['dominant_shift'] }}
                                </span>
                            </td>
                            <td class="text-center text-muted" style="font-size:11px">
                                {{ $s['first_entry'] }} – {{ $s['last_exit'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                Seçilen filtreler için kayıt bulunamadı.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DEVAM TAKVİMİ (sadece ≤31 günlük aralıkta) --}}
    @if(count($calDays) <= 31 && count($userStats) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-calendar-check" style="color:#8b5cf6"></i>
                Devam Takvimi
                <span class="badge bg-light text-secondary fw-normal ms-1" style="font-size:11px">
                    Günlük giriş/çıkış durumu
                </span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" style="min-width:800px">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 sticky-col" style="font-size:11px;min-width:160px;background:#f8fafc">Personel</th>
                            @foreach($calDays as $day)
                            <th class="text-center" style="font-size:10px;padding:4px 2px;min-width:32px;max-width:32px">
                                <div>{{ \Carbon\Carbon::parse($day)->format('d') }}</div>
                                <div class="text-muted" style="font-size:9px">{{ \Carbon\Carbon::parse($day)->locale('tr')->isoFormat('ddd') }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userStats as $s)
                        @php $uid = optional($s['user'])->id; @endphp
                        <tr>
                            <td class="ps-3 sticky-col fw-semibold" style="font-size:12px;background:#fff">
                                {{ optional($s['user'])->name ?? '-' }}
                                <div class="text-muted fw-normal" style="font-size:10px">{{ $s['department'] }}</div>
                            </td>
                            @foreach($calDays as $day)
                            @php $status = $attendance[$uid][$day] ?? null; @endphp
                            <td class="text-center p-0" style="height:36px">
                                @if($status === 'both')
                                    <span class="attend-dot attend-both" title="Giriş + Çıkış">✓</span>
                                @elseif($status === 'entry')
                                    <span class="attend-dot attend-entry" title="Sadece Giriş">G</span>
                                @elseif($status === 'exit')
                                    <span class="attend-dot attend-exit" title="Sadece Çıkış">Ç</span>
                                @else
                                    <span class="attend-dot attend-none">–</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2">
            <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:12px">
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="attend-dot attend-both" style="width:22px;height:22px;font-size:10px">&#10003;</span>
                    <span class="text-muted">Giriş &amp; Çıkış</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="attend-dot attend-entry" style="width:22px;height:22px;font-size:10px">G</span>
                    <span class="text-muted">Sadece Giriş</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="attend-dot attend-exit" style="width:22px;height:22px;font-size:10px">Ç</span>
                    <span class="text-muted">Sadece Çıkış</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="attend-dot attend-none" style="width:22px;height:22px;font-size:10px">–</span>
                    <span class="text-muted">Kayıt Yok</span>
                </span>
            </div>
        </div>
    </div>
    @elseif(count($calDays) > 31)
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="font-size:13px">
        <i class="fas fa-info-circle"></i>
        Devam takvimi 31 günden uzun aralıklar için gösterilmez. Daha kısa bir tarih aralığı seçin.
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* KPI Kartları */
.hr-kpi-card {
    background: #fff;
    border-radius: 14px;
    padding: 18px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    border: 1px solid #f0f0f0;
    text-align: center;
    transition: transform .2s, box-shadow .2s;
}
.hr-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.1);
}
.hr-kpi-icon {
    width: 44px; height: 44px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin: 0 auto 10px;
}
.hr-kpi-val {
    font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 4px;
}
.hr-kpi-lbl {
    font-size: 11px; color: #64748b; font-weight: 500;
    text-transform: uppercase; letter-spacing: .4px;
}

/* Avatar */
.hr-avatar {
    width: 30px; height: 30px; border-radius: 8px;
    background: linear-gradient(135deg, #4361ee, #7c98ff);
    color: #fff; font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* Devam noktalı kutucuklar */
.attend-dot {
    display: flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 6px;
    font-size: 11px; font-weight: 700;
    margin: auto;
}
.attend-both  { background: rgba(16,185,129,.15); color: #059669; }
.attend-entry { background: rgba(59,130,246,.12);  color: #2563eb; }
.attend-exit  { background: rgba(249,115,22,.12);  color: #ea580c; }
.attend-none  { background: #f1f5f9; color: #cbd5e1; }

/* Sabit ilk kolon */
.sticky-col {
    position: sticky; left: 0; z-index: 2;
    border-right: 2px solid #e2e8f0 !important;
}

/* Print */
@media print {
    .sidebar, nav, .breadcrumb, .card-header button,
    form, .btn { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .hr-kpi-card { border: 1px solid #ddd !important; }
    canvas { max-height: 200px; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Şube değişince Kişi dropdownunu güncelle ─────────────────────────
(function () {
    const branchSel = document.getElementById('branchFilter');
    const userSel   = document.getElementById('userFilter');
    const pdfBtn    = document.getElementById('pdfBtn');
    if (!branchSel || !userSel) return;

    branchSel.addEventListener('change', function () {
        const branchId = this.value;
        const url = '{{ route("door-logs.hr-report-staff") }}' + (branchId ? '?branch_id=' + branchId : '');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(users => {
                const current = userSel.value;
                userSel.innerHTML = '<option value="">Tüm Personel</option>';
                users.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    if (String(u.id) === String(current)) opt.selected = true;
                    userSel.appendChild(opt);
                });
            });
    });

    // PDF butonunu form değerleriyle güncelle
    const form = document.getElementById('hrFilterForm');
    if (form && pdfBtn) {
        form.addEventListener('change', function () {
            const params = new URLSearchParams(new FormData(form)).toString();
            pdfBtn.href = '{{ route("door-logs.hr-report-pdf") }}?' + params;
        });
    }
})();

// ── Grafik ─────────────────────────────────────────────────────────────
(function () {
    const labels  = @json($dailyLabels);
    const hours   = @json($dailyHours);

    const gradient = (ctx) => {
        const g = ctx.createLinearGradient(0, 0, 0, 300);
        g.addColorStop(0, 'rgba(67,97,238,.35)');
        g.addColorStop(1, 'rgba(67,97,238,.02)');
        return g;
    };

    const ctx = document.getElementById('dailyChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Çalışma Saati',
                data: hours,
                backgroundColor: (c) => gradient(c.chart.ctx),
                borderColor: '#4361ee',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => ` ${c.parsed.y} saat`
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { font: { size: 11 }, callback: v => v + ' sa' }
                }
            }
        }
    });
})();
</script>
@endpush
