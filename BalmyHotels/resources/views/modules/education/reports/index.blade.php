@extends('layouts.default')
@section('title', 'Eğitim Raporları')

@php
    $localizedLanguages = ['tr' => 'Türkçe', 'en' => 'İngilizce', 'ru' => 'Rusça'];
    $statusOptions = [
        'approved' => 'Eğitim onaylı',
        'completed' => 'Videosu tamamlanan',
        'quiz_passed' => 'Quizi başarılı',
        'in_progress' => 'Devam eden',
        'not_started' => 'Başlamayan',
        'overdue' => 'Süresi geçen',
    ];
    $reportQuery = request()->except('page');
    $selectedDepartmentLabel = $filters['department_id'] === '__none__'
        ? 'Departman belirtilmemiş'
        : ($selectedDepartment?->name ?? null);
    $selectedBranch = $filters['branch_id'] ? $branches->firstWhere('id', $filters['branch_id']) : null;
    $reportTitle = $selectedCourse?->title ?? 'Genel Eğitim Raporu';
@endphp

@push('styles')
<style>
    .education-report-page {
        --report-primary: #315b83;
        --report-primary-dark: #234461;
        --report-border: #e6ebf0;
        --report-muted: #6f7d8b;
        --report-bg: #f7f9fb;
    }
    .education-report-page .report-hero {
        align-items: center;
        background: linear-gradient(135deg, #203a54 0%, #315b83 58%, #4c789f 100%);
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(32,58,84,.18);
        color: #fff;
        display: flex;
        gap: 18px;
        justify-content: space-between;
        margin-bottom: 20px;
        overflow: hidden;
        padding: 22px 24px;
        position: relative;
    }
    .education-report-page .report-hero::after {
        background: rgba(255,255,255,.07);
        border-radius: 50%;
        content: '';
        height: 180px;
        position: absolute;
        right: -55px;
        top: -85px;
        width: 180px;
    }
    .education-report-page .report-hero-copy,
    .education-report-page .report-hero-actions { position: relative; z-index: 1; }
    .education-report-page .report-kicker {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .1em;
        opacity: .72;
        text-transform: uppercase;
    }
    .education-report-page .report-hero h4 { color: #fff; font-size: 1.35rem; margin: 4px 0; }
    .education-report-page .report-hero p { font-size: .84rem; margin: 0; opacity: .78; }
    .education-report-page .report-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .education-report-page .report-hero-actions .btn { border-radius: 9px; font-size: .8rem; font-weight: 600; }
    .education-report-page .report-panel {
        background: #fff;
        border: 1px solid var(--report-border);
        border-radius: 14px;
        box-shadow: 0 3px 14px rgba(31,49,68,.06);
    }
    .education-report-page .filter-panel { padding: 17px; }
    .education-report-page .filter-panel .form-label {
        color: #5d6c79;
        font-size: .72rem;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .education-report-page .filter-panel .form-control,
    .education-report-page .filter-panel .form-select {
        border-color: #dfe6ed;
        border-radius: 8px;
        font-size: .8rem;
        min-height: 38px;
    }
    .education-report-page .context-bar {
        align-items: center;
        background: #eef4f9;
        border: 1px solid #d9e5ef;
        border-radius: 11px;
        color: #38566f;
        display: flex;
        flex-wrap: wrap;
        font-size: .8rem;
        gap: 8px;
        justify-content: space-between;
        margin: 14px 0 18px;
        padding: 10px 12px;
    }
    .education-report-page .context-pill {
        background: #fff;
        border: 1px solid #d9e5ef;
        border-radius: 999px;
        display: inline-flex;
        gap: 5px;
        padding: 4px 9px;
    }
    .education-report-page .metric-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(7, minmax(125px, 1fr));
        margin-bottom: 20px;
    }
    .education-report-page .metric-card {
        background: #fff;
        border: 1px solid var(--report-border);
        border-radius: 12px;
        min-width: 0;
        padding: 13px 14px;
    }
    .education-report-page .metric-card .metric-icon {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        font-size: .76rem;
        height: 28px;
        justify-content: center;
        margin-bottom: 8px;
        width: 28px;
    }
    .education-report-page .metric-card strong { color: #263746; display: block; font-size: 1.3rem; line-height: 1; }
    .education-report-page .metric-card small { color: var(--report-muted); display: block; font-size: .7rem; margin-top: 5px; }
    .education-report-page .section-heading {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
        padding: 16px 18px 12px;
    }
    .education-report-page .section-heading h5 { color: #293947; font-size: 1rem; margin: 0; }
    .education-report-page .section-heading p { color: var(--report-muted); font-size: .76rem; margin: 3px 0 0; }
    .education-report-page .course-toolbar { max-width: 310px; width: 100%; }
    .education-report-page .course-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        padding: 4px 18px 18px;
    }
    .education-report-page .course-card {
        background: #fff;
        border: 1px solid var(--report-border);
        border-radius: 11px;
        color: inherit;
        display: flex;
        flex-direction: column;
        min-height: 190px;
        padding: 14px;
        text-decoration: none;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .education-report-page .course-card:hover {
        border-color: #9fb8ce;
        box-shadow: 0 8px 20px rgba(49,91,131,.11);
        color: inherit;
        transform: translateY(-2px);
    }
    .education-report-page .course-card.is-selected {
        background: #f3f8fc;
        border-color: var(--report-primary);
        box-shadow: inset 0 0 0 1px var(--report-primary);
    }
    .education-report-page .course-title {
        color: #283b4d;
        font-size: .88rem;
        font-weight: 700;
        line-height: 1.3;
        margin: 10px 0 3px;
    }
    .education-report-page .course-trainer { color: var(--report-muted); font-size: .7rem; min-height: 18px; }
    .education-report-page .course-metrics {
        display: grid;
        gap: 6px;
        grid-template-columns: repeat(3, 1fr);
        margin-top: auto;
        padding-top: 12px;
        text-align: center;
    }
    .education-report-page .course-metric { background: #f7f9fb; border-radius: 7px; padding: 7px 3px; }
    .education-report-page .course-metric strong { color: #31475a; display: block; font-size: .85rem; }
    .education-report-page .course-metric span { color: #81909c; font-size: .62rem; }
    .education-report-page .course-progress { background: #e7edf2; border-radius: 99px; height: 5px; margin-top: 10px; overflow: hidden; }
    .education-report-page .course-progress span { background: linear-gradient(90deg,#315b83,#5f8eb5); display: block; height: 100%; }
    .education-report-page .report-table { margin: 0; }
    .education-report-page .report-table th {
        background: #f6f8fa;
        border-bottom: 1px solid var(--report-border);
        color: #687887;
        font-size: .67rem;
        font-weight: 700;
        letter-spacing: .025em;
        padding: 10px 11px;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }
    .education-report-page .report-table td {
        border-bottom: 1px solid #f0f3f6;
        color: #344554;
        font-size: .75rem;
        padding: 10px 11px;
        vertical-align: middle;
    }
    .education-report-page .report-table tbody tr:last-child td { border-bottom: 0; }
    .education-report-page .report-table tbody tr:hover { background: #fafcff; }
    .education-report-page .rate-cell { min-width: 110px; }
    .education-report-page .rate-track { background: #e8edf1; border-radius: 99px; height: 5px; overflow: hidden; }
    .education-report-page .rate-track span { background: #2f8d63; display: block; height: 100%; }
    .education-report-page .detail-filter {
        background: var(--report-bg);
        border-bottom: 1px solid var(--report-border);
        border-top: 1px solid var(--report-border);
        padding: 12px 18px;
    }
    .education-report-page .progress-mini { background: #e8edf1; border-radius: 99px; height: 6px; min-width: 80px; overflow: hidden; }
    .education-report-page .progress-mini span { background: #497da8; display: block; height: 100%; }
    .education-report-page .status-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: .66rem;
        font-weight: 700;
        padding: 3px 7px;
        white-space: nowrap;
    }
    .education-report-page .empty-report { color: #8a97a3; padding: 40px 18px; text-align: center; }
    .education-report-page .print-report-header { display: none; }
    @media (max-width: 1399px) {
        .education-report-page .metric-grid { grid-template-columns: repeat(4, minmax(125px, 1fr)); }
    }
    @media (max-width: 991px) {
        .education-report-page .course-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .education-report-page .metric-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); }
    }
    @media (max-width: 767px) {
        .education-report-page .report-hero { align-items: flex-start; flex-direction: column; }
        .education-report-page .report-hero-actions { width: 100%; }
        .education-report-page .report-hero-actions .btn { flex: 1; }
        .education-report-page .course-grid { grid-template-columns: 1fr; }
        .education-report-page .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .education-report-page .section-heading { align-items: flex-start; flex-direction: column; }
        .education-report-page .course-toolbar { max-width: none; }
    }
    @media print {
        @page { margin: 9mm; size: A4 landscape; }
        body * { visibility: hidden !important; }
        #educationReportPrintArea,
        #educationReportPrintArea * { visibility: visible !important; }
        #educationReportPrintArea {
            background: #fff !important;
            left: 0;
            position: absolute;
            top: 0;
            width: 100%;
        }
        #educationReportPrintArea .no-print,
        #educationReportPrintArea .course-browser,
        #educationReportPrintArea .event-report,
        #educationReportPrintArea nav { display: none !important; }
        #educationReportPrintArea .print-report-header { display: flex !important; }
        #educationReportPrintArea .report-panel,
        #educationReportPrintArea .metric-card { box-shadow: none !important; break-inside: avoid; }
        #educationReportPrintArea .metric-grid { grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 9px; }
        #educationReportPrintArea .metric-card { padding: 7px; }
        #educationReportPrintArea .metric-card .metric-icon { display: none; }
        #educationReportPrintArea .metric-card strong { font-size: 12pt; }
        #educationReportPrintArea .metric-card small { font-size: 6.5pt; }
        #educationReportPrintArea .context-bar { margin: 7px 0; padding: 6px; }
        #educationReportPrintArea .report-table th { font-size: 6pt; padding: 5px; }
        #educationReportPrintArea .report-table td { font-size: 6.5pt; padding: 5px; }
        #educationReportPrintArea .section-heading { padding: 8px 6px; }
        #educationReportPrintArea .detail-filter { display: none !important; }
        #educationReportPrintArea .department-report { margin-bottom: 9px !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid education-report-page" id="educationReportPrintArea">
    <div class="print-report-header align-items-center justify-content-between border-bottom pb-2 mb-2">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/logo.svg') }}" alt="Balmy Hotels" style="height:38px;max-width:150px;filter:grayscale(1)">
            <div>
                <div class="fw-bold" style="font-size:14pt">{{ $reportTitle }}</div>
                <div style="font-size:8pt;color:#666">Eğitim ve Gelişim Raporu</div>
            </div>
        </div>
        <div class="text-end" style="font-size:7pt;color:#555">
            Dönem: {{ $filters['from']->format('d.m.Y') }} – {{ $filters['to']->format('d.m.Y') }}<br>
            {{ $selectedBranch?->name ?? 'Tüm şubeler' }} · {{ $selectedDepartmentLabel ?? 'Tüm departmanlar' }}<br>
            Hazırlayan: {{ auth()->user()->name }} · {{ now()->format('d.m.Y H:i') }}
        </div>
    </div>

    <div class="row page-titles mx-0 mb-0 no-print">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Eğitim Raporları</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Eğitim Raporları</li>
            </ol>
        </div>
    </div>

    <div class="no-print">@include('modules.education._tabs')</div>

    <div class="report-hero no-print">
        <div class="report-hero-copy">
            <div class="report-kicker">Eğitim ve gelişim · yönetim raporu</div>
            <h4>{{ $reportTitle }}</h4>
            <p>Eğitim modülü, departman ve çalışan bazında tamamlama durumlarını tek ekrandan inceleyin.</p>
        </div>
        <div class="report-hero-actions">
            <a href="{{ route('education.reports.export', $reportQuery) }}" class="btn btn-light text-success">
                <i class="fas fa-file-excel me-1"></i>Excel / CSV
            </a>
            <button type="button" class="btn btn-outline-light" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Yazdır / PDF
            </button>
        </div>
    </div>

    <div class="report-panel filter-panel no-print">
        <form method="GET" action="{{ route('education.reports.index') }}" class="row g-2 align-items-end">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportFrom" class="form-label"><i class="fas fa-calendar-alt me-1"></i>Başlangıç</label>
                <input type="date" name="from" id="reportFrom" value="{{ $filters['from']->toDateString() }}" class="form-control">
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportTo" class="form-label"><i class="fas fa-calendar-check me-1"></i>Bitiş</label>
                <input type="date" name="to" id="reportTo" value="{{ $filters['to']->toDateString() }}" class="form-control">
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportLanguage" class="form-label"><i class="fas fa-language me-1"></i>Eğitim dili</label>
                <select name="language" id="reportLanguage" class="form-select">
                    <option value="">Tüm diller</option>
                    @foreach($languages as $code => $label)
                        <option value="{{ $code }}" @selected($filters['language'] === $code)>{{ $localizedLanguages[$code] ?? $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportBranch" class="form-label"><i class="fas fa-hotel me-1"></i>Şube</label>
                <select name="branch_id" id="reportBranch" class="form-select">
                    <option value="">Tüm şubeler</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) $filters['branch_id'] === (int) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportDepartment" class="form-label"><i class="fas fa-sitemap me-1"></i>Departman</label>
                <select name="department_id" id="reportDepartment" class="form-select">
                    <option value="">Tüm departmanlar</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>
                            {{ $department->name }}{{ $filters['branch_id'] ? '' : ' · '.($department->branch->name ?? '-') }}
                        </option>
                    @endforeach
                    <option value="__none__" @selected($filters['department_id'] === '__none__')>Departman belirtilmemiş</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="reportCourse" class="form-label"><i class="fas fa-play-circle me-1"></i>Eğitim modülü</label>
                <select name="course_id" id="reportCourse" class="form-select">
                    <option value="">Tüm eğitimler</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) $filters['course_id'] === (int) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                <label for="reportStatus" class="form-label"><i class="fas fa-tasks me-1"></i>Detay durumu</label>
                <select name="status" id="reportStatus" class="form-select">
                    <option value="">Tüm durumlar</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-4 col-md-5 col-sm-6">
                <label for="reportSearch" class="form-label"><i class="fas fa-search me-1"></i>Personel veya eğitim ara</label>
                <input type="search" name="search" id="reportSearch" value="{{ $filters['search'] }}" class="form-control" placeholder="Ad, soyad veya eğitim adı...">
            </div>
            <div class="col-xl-5 col-md-3 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" style="min-height:38px;border-radius:8px">
                    <i class="fas fa-filter me-1"></i>Raporu Uygula
                </button>
                <a href="{{ route('education.reports.index') }}" class="btn btn-outline-secondary" style="min-height:38px;border-radius:8px">
                    <i class="fas fa-undo-alt me-1"></i>Temizle
                </a>
            </div>
        </form>
    </div>

    <div class="context-bar">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <strong><i class="fas fa-chart-bar me-1"></i>Rapor kapsamı:</strong>
            <span class="context-pill"><i class="fas fa-calendar"></i>{{ $filters['from']->format('d.m.Y') }} – {{ $filters['to']->format('d.m.Y') }}</span>
            <span class="context-pill"><i class="fas fa-hotel"></i>{{ $selectedBranch?->name ?? 'Tüm şubeler' }}</span>
            <span class="context-pill"><i class="fas fa-sitemap"></i>{{ $selectedDepartmentLabel ?? 'Tüm departmanlar' }}</span>
            <span class="context-pill"><i class="fas fa-play-circle"></i>{{ $selectedCourse?->title ?? 'Tüm eğitimler' }}</span>
        </div>
        @if($selectedCourse || $selectedDepartmentLabel)
            <a href="{{ route('education.reports.index', request()->except(['course_id','department_id','status','search','page'])) }}" class="small text-decoration-none no-print">
                <i class="fas fa-times me-1"></i>Detay seçimini kaldır
            </a>
        @endif
    </div>

    <div class="metric-grid">
        @foreach([
            ['label' => 'Toplam atama', 'value' => $stats['assigned'], 'icon' => 'fa-clipboard-list', 'color' => '#315b83', 'bg' => '#eaf1f7'],
            ['label' => 'Atanan personel', 'value' => $stats['learners'], 'icon' => 'fa-users', 'color' => '#5c4c9b', 'bg' => '#f0edfa'],
            ['label' => 'Eğitim onaylı', 'value' => $stats['approved'], 'icon' => 'fa-check-double', 'color' => '#237554', 'bg' => '#e8f5ef'],
            ['label' => 'Video tamamlandı', 'value' => $stats['video_completed'], 'icon' => 'fa-play-circle', 'color' => '#18829b', 'bg' => '#e6f5f8'],
            ['label' => 'Devam ediyor', 'value' => $stats['in_progress'], 'icon' => 'fa-spinner', 'color' => '#b26a1f', 'bg' => '#fff3e5'],
            ['label' => 'Başlamadı', 'value' => $stats['not_started'], 'icon' => 'fa-hourglass-start', 'color' => '#727e89', 'bg' => '#f0f2f4'],
            ['label' => 'Tamamlama oranı', 'value' => '%'.$stats['completion_rate'], 'icon' => 'fa-chart-line', 'color' => '#237554', 'bg' => '#e8f5ef'],
        ] as $metric)
            <div class="metric-card">
                <span class="metric-icon" style="color:{{ $metric['color'] }};background:{{ $metric['bg'] }}"><i class="fas {{ $metric['icon'] }}"></i></span>
                <strong>{{ $metric['value'] }}</strong>
                <small>{{ $metric['label'] }}</small>
            </div>
        @endforeach
    </div>

    <div class="report-panel mb-3 course-browser no-print">
        <div class="section-heading">
            <div>
                <h5><i class="fas fa-th-large me-2 text-primary"></i>Eğitim Modülleri</h5>
                <p>Bir eğitime basarak departman ve personel sonuçlarını ayrıntılı inceleyin.</p>
            </div>
            <div class="course-toolbar">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" class="form-control" id="courseReportSearch" placeholder="Eğitim modülü ara..." autocomplete="off">
                </div>
            </div>
        </div>
        <div class="course-grid" id="courseReportGrid">
            @forelse($courseStats as $row)
                @php
                    $course = $row['course'];
                    $courseUrl = route('education.reports.index', array_merge(
                        request()->except(['page','status','search']),
                        ['course_id' => $course->id]
                    )) . '#department-report';
                @endphp
                <a href="{{ $courseUrl }}" class="course-card {{ (int) $filters['course_id'] === (int) $course->id ? 'is-selected' : '' }}" data-course-report-card data-course-title="{{ $course->title }}">
                    <div class="d-flex flex-wrap justify-content-between gap-1">
                        <span class="badge bg-primary-subtle text-primary">{{ $localizedLanguages[$course->language] ?? strtoupper($course->language) }}</span>
                        <div class="d-flex gap-1">
                            @if($course->quiz_questions_count > 0)
                                <span class="badge bg-info-subtle text-info">Quiz</span>
                            @endif
                            @unless($course->is_active)
                                <span class="badge bg-light text-muted border">Pasif</span>
                            @endunless
                        </div>
                    </div>
                    <div class="course-title">{{ $course->title }}</div>
                    <div class="course-trainer">Eğitimci: {{ $course->trainer->name ?? 'Belirtilmemiş' }}</div>
                    <div class="course-metrics">
                        <div class="course-metric"><strong>{{ $row['assigned'] }}</strong><span>Atama</span></div>
                        <div class="course-metric"><strong>{{ $row['approved'] }}</strong><span>Onaylı</span></div>
                        <div class="course-metric"><strong>%{{ $row['completion_rate'] }}</strong><span>Tamamlama</span></div>
                    </div>
                    <div class="course-progress"><span style="width:{{ min(100, $row['completion_rate']) }}%"></span></div>
                </a>
            @empty
                <div class="empty-report" style="grid-column:1/-1">Bu filtrelerde eğitim modülü bulunamadı.</div>
            @endforelse
            <div class="empty-report" id="courseReportEmpty" style="grid-column:1/-1" hidden>Aramanızla eşleşen eğitim modülü bulunamadı.</div>
        </div>
    </div>

    <div class="report-panel mb-3 department-report" id="department-report">
        <div class="section-heading">
            <div>
                <h5><i class="fas fa-sitemap me-2 text-primary"></i>Departman Bazlı Eğitim Özeti</h5>
                <p>{{ $selectedCourse ? $selectedCourse->title.' eğitiminin departman sonuçları' : 'Tüm eğitimlerin departmanlara göre karşılaştırması' }}</p>
            </div>
            <span class="badge bg-light text-dark border">{{ $departmentStats->count() }} departman</span>
        </div>
        <div class="table-responsive">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Departman</th>
                        <th class="text-center">Personel</th>
                        <th class="text-center">Atama</th>
                        <th class="text-center">Onaylı</th>
                        <th class="text-center">Devam</th>
                        <th class="text-center">Başlamadı</th>
                        <th class="text-center">Geciken</th>
                        <th>Ort. ilerleme</th>
                        <th>Tamamlama</th>
                        <th class="text-end no-print">Detay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departmentStats as $row)
                        @php
                            $departmentUrl = route('education.reports.index', array_merge(
                                request()->except(['page','status','search','department_id']),
                                ['department_id' => $row['department_id']]
                            )) . '#personnel-detail';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $row['name'] }}</div>
                                <small class="text-muted">{{ $row['branch_name'] ?? 'Şube belirtilmemiş' }}</small>
                            </td>
                            <td class="text-center fw-semibold">{{ $row['learners'] }}</td>
                            <td class="text-center">{{ $row['assigned'] }}</td>
                            <td class="text-center text-success fw-bold">{{ $row['approved'] }}</td>
                            <td class="text-center text-warning fw-bold">{{ $row['in_progress'] }}</td>
                            <td class="text-center text-muted">{{ $row['not_started'] }}</td>
                            <td class="text-center {{ $row['overdue'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $row['overdue'] }}</td>
                            <td class="rate-cell">
                                <div class="d-flex justify-content-between mb-1"><span>%{{ $row['avg_progress'] }}</span></div>
                                <div class="rate-track"><span style="width:{{ min(100, $row['avg_progress']) }}%"></span></div>
                            </td>
                            <td class="rate-cell">
                                <div class="d-flex justify-content-between mb-1"><strong>%{{ $row['completion_rate'] }}</strong></div>
                                <div class="rate-track"><span style="width:{{ min(100, $row['completion_rate']) }}%"></span></div>
                            </td>
                            <td class="text-end no-print">
                                <a href="{{ $departmentUrl }}" class="btn btn-sm btn-outline-primary" style="border-radius:7px;white-space:nowrap">İncele</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="empty-report"><i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>Bu kapsamda departman verisi bulunamadı.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="report-panel mb-3" id="personnel-detail">
        <div class="section-heading">
            <div>
                <h5><i class="fas fa-users me-2 text-primary"></i>Personel Eğitim Detayı</h5>
                <p>Kim tamamladı, kim devam ediyor, quiz ve son izleme bilgileri.</p>
            </div>
            <span class="badge bg-light text-dark border">{{ $assignments->total() }} kayıt</span>
        </div>
        <form method="GET" action="{{ route('education.reports.index') }}#personnel-detail" class="detail-filter row g-2 align-items-end no-print">
            <input type="hidden" name="from" value="{{ $filters['from']->toDateString() }}">
            <input type="hidden" name="to" value="{{ $filters['to']->toDateString() }}">
            <input type="hidden" name="language" value="{{ $filters['language'] }}">
            <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
            <input type="hidden" name="department_id" value="{{ $filters['department_id'] }}">
            <input type="hidden" name="course_id" value="{{ $filters['course_id'] }}">
            <div class="col-lg-4 col-md-5">
                <label for="detailSearch" class="form-label small fw-semibold mb-1">Personel veya eğitim ara</label>
                <input type="search" name="search" id="detailSearch" value="{{ $filters['search'] }}" class="form-control form-control-sm" placeholder="Ad, soyad veya eğitim...">
            </div>
            <div class="col-lg-3 col-md-4">
                <label for="detailStatus" class="form-label small fw-semibold mb-1">Durum</label>
                <select name="status" id="detailStatus" class="form-select form-select-sm">
                    <option value="">Tüm durumlar</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 d-grid">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Listele</button>
            </div>
            @if($filters['status'] || $filters['search'])
                <div class="col-lg-3 text-lg-end">
                    <a href="{{ route('education.reports.index', request()->except(['status','search','page'])) }}#personnel-detail" class="small text-decoration-none">
                        <i class="fas fa-times me-1"></i>Detay filtresini temizle
                    </a>
                </div>
            @endif
        </form>
        <div class="table-responsive">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Personel</th>
                        <th>Eğitim / Atama</th>
                        <th>İlerleme</th>
                        <th>Video durumu</th>
                        <th>Quiz</th>
                        <th>Eğitim onayı</th>
                        <th>Son işlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php
                            $latestAttempt = $assignment->latestQuizAttempt;
                            $quizScore = $latestAttempt && $latestAttempt->total_questions > 0
                                ? round($latestAttempt->correct_answers / $latestAttempt->total_questions * 100, 1)
                                : null;
                            $statusLabel = match($assignment->status) {
                                'completed' => 'Tamamlandı',
                                'in_progress' => 'Devam ediyor',
                                default => 'Başlamadı',
                            };
                            $statusStyle = match($assignment->status) {
                                'completed' => 'color:#237554;background:#e8f5ef',
                                'in_progress' => 'color:#a05c18;background:#fff2df',
                                default => 'color:#6d7882;background:#eef1f3',
                            };
                            $hasQuiz = $assignment->course?->has_quiz ?? false;
                            $isOverdue = $assignment->due_at && $assignment->due_at->isPast() && ! $assignment->video_completed;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $assignment->learner->name ?? '-' }}</div>
                                <small class="text-muted d-block">{{ $assignment->learner?->department?->name ?? 'Departman belirtilmemiş' }}</small>
                                <small class="text-muted">{{ $assignment->learner?->branch?->name ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $assignment->course->title ?? '-' }}</div>
                                <small class="text-muted d-block">Atama: {{ $assignment->assigned_week_start->format('d.m.Y') }} · {{ strtoupper($assignment->language) }}</small>
                                @if($assignment->due_at)
                                    <small class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">Son: {{ $assignment->due_at->format('d.m.Y H:i') }}{{ $isOverdue ? ' · Gecikti' : '' }}</small>
                                @endif
                            </td>
                            <td style="min-width:105px">
                                <div class="d-flex justify-content-between mb-1"><strong>%{{ number_format($assignment->progress_percent, 1) }}</strong></div>
                                <div class="progress-mini"><span style="width:{{ min(100, $assignment->progress_percent) }}%"></span></div>
                            </td>
                            <td>
                                <span class="status-badge" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                                @if($assignment->completed_at)
                                    <small class="text-muted d-block mt-1">{{ $assignment->completed_at->format('d.m.Y H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                @if(! $hasQuiz)
                                    <span class="status-badge" style="color:#6d7882;background:#eef1f3">Quiz yok</span>
                                @elseif($assignment->quiz_passed)
                                    <span class="status-badge" style="color:#237554;background:#e8f5ef">Başarılı</span>
                                @elseif($latestAttempt)
                                    <span class="status-badge" style="color:#b33b46;background:#fdecef">Tekrar gerekli</span>
                                @elseif($assignment->video_completed)
                                    <span class="status-badge" style="color:#a05c18;background:#fff2df">Quiz bekliyor</span>
                                @else
                                    <span class="status-badge" style="color:#6d7882;background:#eef1f3">Video bekleniyor</span>
                                @endif
                                @if($quizScore !== null)
                                    <small class="text-muted d-block mt-1">Son puan: %{{ $quizScore }} · {{ $assignment->quiz_attempts_count }} deneme</small>
                                @endif
                            </td>
                            <td>
                                @if($assignment->training_approved)
                                    <span class="status-badge" style="color:#237554;background:#e8f5ef"><i class="fas fa-check me-1"></i>Onaylandı</span>
                                @else
                                    <span class="status-badge" style="color:#7b6a3f;background:#f5f1e7">Bekliyor</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $assignment->last_watched_at?->format('d.m.Y H:i') ?? '-' }}</div>
                                @if($assignment->started_at)
                                    <small class="text-muted d-block">Başlama: {{ $assignment->started_at->format('d.m.Y H:i') }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-report"><i class="fas fa-user-clock fa-2x mb-2 d-block"></i>Bu filtrelere uyan personel eğitim kaydı bulunamadı.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div class="p-3 border-top no-print">{{ $assignments->links() }}</div>
        @endif
    </div>

    <div class="report-panel event-report no-print">
        <div class="section-heading">
            <div>
                <h5><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Yüz Yüze Eğitim Katılımı</h5>
                <p>Seçilen dönem ve departman kapsamındaki katılım cevapları.</p>
            </div>
            <span class="badge bg-light text-dark border">{{ $events->count() }} etkinlik</span>
        </div>
        <div class="table-responsive">
            <table class="table report-table">
                <thead><tr><th>Eğitim</th><th>Tarih / Yer</th><th>Eğitimci</th><th class="text-center">Katılacak</th><th class="text-center">Katılmayacak</th><th class="text-center">Bekliyor</th><th class="text-end">Detay</th></tr></thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td><div class="fw-semibold">{{ $event->title }}</div><small class="text-muted">{{ $localizedLanguages[$event->language] ?? strtoupper($event->language) }}</small></td>
                            <td><div>{{ $event->starts_at->format('d.m.Y H:i') }}</div><small class="text-muted">{{ $event->location ?? 'Yer belirtilmemiş' }}</small></td>
                            <td>{{ $event->trainer->name ?? '-' }}</td>
                            <td class="text-center text-success fw-bold">{{ $event->responses->where('status', 'attending')->count() }}</td>
                            <td class="text-center text-danger fw-bold">{{ $event->responses->where('status', 'declined')->count() }}</td>
                            <td class="text-center text-muted fw-bold">{{ $event->responses->where('status', 'pending')->count() }}</td>
                            <td class="text-end"><a href="{{ route('education.events.show', $event) }}" class="btn btn-sm btn-outline-primary">İncele</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-report">Bu dönemde yüz yüze eğitim kaydı bulunamadı.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const search = document.getElementById('courseReportSearch');
    const cards = Array.from(document.querySelectorAll('[data-course-report-card]'));
    const emptyState = document.getElementById('courseReportEmpty');

    if (!search || cards.length === 0) {
        return;
    }

    const normalize = (value) => String(value || '')
        .toLocaleLowerCase('tr-TR')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/ı/g, 'i');

    search.addEventListener('input', () => {
        const term = normalize(search.value.trim());
        let visible = 0;

        cards.forEach((card) => {
            const matches = !term || normalize(card.dataset.courseTitle).includes(term);
            card.hidden = !matches;
            if (matches) visible += 1;
        });

        emptyState.hidden = visible !== 0;
    });
})();
</script>
@endpush
