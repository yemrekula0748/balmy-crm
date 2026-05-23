@extends('layouts.default')

@section('title', 'Oda Bazlı Arıza Raporu')

@push('styles')
<style>
.fault-report-hero {
    background: #fff;
    color: #1e293b;
    border: 1px solid #e8eef5;
    border-left: 5px solid #1e3a5f;
    border-radius: 18px;
    padding: 26px 30px;
    margin-bottom: 22px;
    box-shadow: 0 6px 22px rgba(15,23,42,.06);
}
.fault-report-hero small { color: #64748b; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.fault-report-hero h3 { margin: 7px 0 8px; font-weight: 850; letter-spacing: -.02em; }
.fault-report-hero p { margin: 0; color: #64748b; max-width: 760px; line-height: 1.65; }
.filter-card, .report-card {
    background: #fff;
    border: 1px solid #e8eef5;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(15,23,42,.05);
}
.filter-card { padding: 18px; margin-bottom: 20px; }
.metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px; margin-bottom: 20px; }
.metric-card {
    background: #fff;
    border: 1px solid #e8eef5;
    border-radius: 15px;
    padding: 16px 18px;
    box-shadow: 0 5px 18px rgba(15,23,42,.05);
}
.metric-label { color: #64748b; font-size: .72rem; font-weight: 750; text-transform: uppercase; letter-spacing: .06em; }
.metric-value { font-size: 1.65rem; font-weight: 850; color: #0f172a; line-height: 1; margin-top: 8px; }
.metric-sub { color: #94a3b8; font-size: .72rem; margin-top: 5px; }
.section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.section-title h5 { margin: 0; font-weight: 800; color: #111827; }
.muted-pill { background: #f1f5f9; color: #475569; border-radius: 999px; padding: 5px 10px; font-size: .72rem; font-weight: 750; }
.table thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e8eef5; }
.table td { vertical-align: middle; font-size: .84rem; }
.empty-state { text-align: center; padding: 42px 18px; color: #64748b; }
.empty-state i { font-size: 2rem; color: #94a3b8; margin-bottom: 10px; }
@media (max-width: 991px) { .metric-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
@media (max-width: 575px) { .fault-report-hero { padding: 20px; } .metric-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $statusLabels = \App\Models\Fault::STATUSES;
    $statusColors = \App\Models\Fault::STATUS_COLORS;
    $priorityLabels = \App\Models\Fault::PRIORITIES;
    $priorityColors = \App\Models\Fault::PRIORITY_COLORS;
    $hasFilter = request()->filled('branch_id')
        || request()->filled('fault_location_id')
        || request()->filled('fault_area_id')
        || request()->filled('fault_type_id')
        || request()->filled('status')
        || request()->filled('date_from')
        || request()->filled('date_to');
@endphp

<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Oda Bazlı Arıza Raporu</h4>
                <span class="text-muted" style="font-size:.8rem">Oda/alan seç, tüm arıza geçmişini ve kategori kırılımını gör</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Oda Bazlı Rapor</li>
            </ol>
        </div>
    </div>

    <div class="fault-report-hero">
        <small>Teknik Arıza · Oda Analizi</small>
        <h3>Bir odanın tüm arıza hafızası tek ekranda</h3>
        <p>
            Kayıtlı konum alanlarından oda/alan seçerek o noktada açılmış tüm arızaları listeleyebilir,
            arıza türü bazında yoğunluğu ve son kayıtları hızlıca inceleyebilirsiniz.
        </p>
    </div>

    <form method="GET" action="{{ route('faults.room-report') }}" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold small">Şube</label>
                <select name="branch_id" class="form-select">
                    <option value="">Tüm şubeler</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold small">Konum</label>
                <select name="fault_location_id" class="form-select">
                    <option value="">Tüm konumlar</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) request('fault_location_id') === (string) $location->id)>
                        {{ $location->branch?->name }} · {{ $location->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label fw-semibold small">Oda / Alan</label>
                <select name="fault_area_id" class="form-select">
                    <option value="">Oda/alan seç</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) request('fault_area_id') === (string) $area->id)>
                        {{ $area->location?->branch?->name }} · {{ $area->location?->name }} · {{ $area->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold small">Arıza Türü</label>
                <select name="fault_type_id" class="form-select">
                    <option value="">Tüm türler</option>
                    @foreach($faultTypes as $type)
                    <option value="{{ $type->id }}" @selected((string) request('fault_type_id') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <label class="form-label fw-semibold small">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <label class="form-label fw-semibold small">Başlangıç</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-1 col-md-4">
                <label class="form-label fw-semibold small">Bitiş</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('faults.room-report') }}" class="btn btn-outline-secondary">Temizle</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Raporla
                </button>
            </div>
        </div>
    </form>

    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-label">Toplam Arıza</div>
            <div class="metric-value">{{ $summary['total'] }}</div>
            <div class="metric-sub">{{ $selectedArea ? $selectedArea->name . ' odası' : 'seçili filtre' }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Açık / Devam Eden</div>
            <div class="metric-value" style="color:#dc2626">{{ $summary['open'] }}</div>
            <div class="metric-sub">kapanmamış kayıt</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Çözülen / Kapalı</div>
            <div class="metric-value" style="color:#059669">{{ $summary['resolved'] }}</div>
            <div class="metric-sub">tamamlanan kayıt</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Kategori Sayısı</div>
            <div class="metric-value" style="color:#2563eb">{{ $summary['type_count'] }}</div>
            <div class="metric-sub">farklı arıza türü</div>
        </div>
    </div>

    @if(!$hasFilter)
    <div class="report-card empty-state">
        <i class="fas fa-door-open"></i>
        <h5 class="fw-bold mb-1">Rapor için oda veya filtre seçin</h5>
        <p class="mb-0">Oda/alan seçtiğinizde o noktanın tüm arızaları ve kategori kırılımı burada listelenecek.</p>
    </div>
    @else
    <div class="row g-3">
        <div class="col-xl-5">
            <div class="report-card p-3 h-100">
                <div class="section-title">
                    <h5>Kategorisel Kırılım</h5>
                    <span class="muted-pill">{{ $typeStats->count() }} kategori</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Arıza Türü</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Açık</th>
                                <th class="text-center">Çözülen</th>
                                <th>Son Kayıt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($typeStats as $stat)
                            <tr>
                                <td class="fw-semibold">{{ $stat['name'] }}</td>
                                <td class="text-center fw-bold">{{ $stat['total'] }}</td>
                                <td class="text-center text-danger fw-bold">{{ $stat['open'] }}</td>
                                <td class="text-center text-success fw-bold">{{ $stat['resolved'] }}</td>
                                <td>
                                    @if($stat['last_fault'])
                                    <a href="{{ route('faults.show', $stat['last_fault']) }}" class="text-decoration-none fw-semibold">
                                        #{{ $stat['last_fault']->id }}
                                    </a>
                                    <div class="small text-muted">{{ $stat['last_fault']->created_at?->format('d.m.Y H:i') }}</div>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Bu filtrede arıza kaydı yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="report-card p-3 h-100">
                <div class="section-title">
                    <h5>Arıza Listesi</h5>
                    <span class="muted-pill">{{ $faults->total() }} kayıt</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Oda / Konum</th>
                                <th>Arıza</th>
                                <th>Departman</th>
                                <th>Durum</th>
                                <th>Öncelik</th>
                                <th class="text-end">Detay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faults as $fault)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $fault->created_at?->format('d.m.Y') }}</div>
                                    <div class="small text-muted">{{ $fault->created_at?->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $fault->faultArea?->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $fault->faultLocation?->name ?? '-' }} · {{ $fault->branch?->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $fault->faultType?->name ?? $fault->title }}</div>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($fault->description), 54) }}</div>
                                </td>
                                <td>{{ $fault->department?->name ?? '-' }}</td>
                                <td><span class="badge bg-{{ $statusColors[$fault->status] ?? 'secondary' }}">{{ $statusLabels[$fault->status] ?? $fault->status }}</span></td>
                                <td><span class="badge bg-{{ $priorityColors[$fault->priority] ?? 'secondary' }}">{{ $priorityLabels[$fault->priority] ?? $fault->priority }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('faults.show', $fault) }}" class="btn btn-sm btn-outline-primary">
                                        Gör
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Bu filtreye uygun arıza bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $faults->links() }}</div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
