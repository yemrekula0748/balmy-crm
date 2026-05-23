@extends('layouts.default')

@section('title', 'Arıza Bazlı Rapor')

@push('styles')
<style>
.fault-report-hero {
    background: #fff;
    color: #1e293b;
    border: 1px solid #e8eef5;
    border-left: 5px solid #b45309;
    border-radius: 18px;
    padding: 26px 30px;
    margin-bottom: 22px;
    box-shadow: 0 6px 22px rgba(15,23,42,.06);
}
.fault-report-hero small { color: #64748b; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.fault-report-hero h3 { margin: 7px 0 8px; font-weight: 850; letter-spacing: -.02em; }
.fault-report-hero p { margin: 0; color: #64748b; max-width: 780px; line-height: 1.65; }
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
.type-shortcut {
    display: block;
    border: 1px solid #e8eef5;
    border-radius: 14px;
    padding: 14px 15px;
    text-decoration: none;
    color: inherit;
    transition: all .15s ease;
    background: #fff;
}
.type-shortcut:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(15,23,42,.08); border-color: #fed7aa; color: inherit; }
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
@endphp

<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Arıza Bazlı Rapor</h4>
                <span class="text-muted" style="font-size:.8rem">Bir arıza türünün hangi oda/alanlarda tekrar ettiğini gör</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Arıza Bazlı Rapor</li>
            </ol>
        </div>
    </div>

    <div class="fault-report-hero">
        <small>Teknik Arıza · Tür Analizi</small>
        <h3>Seçilen arıza türü hangi odalarda yoğunlaşıyor?</h3>
        <p>
            Arıza türü seçerek bu problemin hangi oda, konum ve şubelerde kayda geçtiğini görebilir,
            odalar arası tekrar yoğunluğunu ve ilgili arıza detaylarını tek listeden açabilirsiniz.
        </p>
    </div>

    <form method="GET" action="{{ route('faults.type-report') }}" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold small">Arıza Türü</label>
                <select name="fault_type_id" class="form-select">
                    <option value="">Arıza türü seç</option>
                    @foreach($faultTypes as $type)
                    <option value="{{ $type->id }}" @selected((string) request('fault_type_id') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold small">Şube</label>
                <select name="branch_id" class="form-select">
                    <option value="">Tüm şubeler</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
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
            <div class="col-lg-1 col-md-6">
                <label class="form-label fw-semibold small">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold small">Başlangıç</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold small">Bitiş</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('faults.type-report') }}" class="btn btn-outline-secondary">Temizle</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Raporla
                </button>
            </div>
        </div>
    </form>

    @if(!$selectedType)
    <div class="report-card p-3">
        <div class="section-title">
            <h5>En Sık Görülen Arıza Türleri</h5>
            <span class="muted-pill">Hızlı seçim</span>
        </div>
        @if($topTypes->count() > 0)
        <div class="row g-3">
            @foreach($topTypes as $item)
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a class="type-shortcut" href="{{ route('faults.type-report', array_merge(request()->except('page'), ['fault_type_id' => $item['type']?->id])) }}">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="fw-bold" style="color:#111827">{{ $item['name'] }}</div>
                        <span class="badge bg-warning text-dark">{{ $item['total'] }}</span>
                    </div>
                    <div class="small text-muted mt-2">{{ $item['room_count'] }} farklı oda/alan</div>
                    @if($item['last_fault'])
                    <div class="small text-muted">Son: {{ $item['last_fault']->created_at?->format('d.m.Y H:i') }}</div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-chart-simple"></i>
            <h5 class="fw-bold mb-1">Henüz arıza türü verisi yok</h5>
            <p class="mb-0">Arıza türü seçtiğinizde oda bazlı yoğunluk burada oluşacak.</p>
        </div>
        @endif
    </div>
    @else
    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-label">Seçilen Arıza</div>
            <div class="metric-value" style="font-size:1.05rem;line-height:1.25">{{ $selectedType->name }}</div>
            <div class="metric-sub">raporlanan tür</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Toplam Kayıt</div>
            <div class="metric-value">{{ $summary['total'] }}</div>
            <div class="metric-sub">seçilen filtrelerde</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Etkilenen Oda</div>
            <div class="metric-value" style="color:#b45309">{{ $summary['room_count'] }}</div>
            <div class="metric-sub">farklı oda/alan</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">En Yoğun Oda</div>
            <div class="metric-value" style="font-size:1.05rem;line-height:1.25">
                {{ $summary['top_room']['room_name'] ?? '-' }}
            </div>
            <div class="metric-sub">
                @if($summary['top_room'] ?? null)
                    {{ $summary['top_room']['total'] }} kayıt · {{ $summary['top_room']['location_name'] }}
                @else
                    kayıt yok
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-5">
            <div class="report-card p-3 h-100">
                <div class="section-title">
                    <h5>Hangi Odalarda Görüldü?</h5>
                    <span class="muted-pill">{{ $roomStats->count() }} oda/alan</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Oda / Alan</th>
                                <th>Konum</th>
                                <th class="text-center">Toplam</th>
                                <th class="text-center">Açık</th>
                                <th>Son Kayıt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roomStats as $room)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $room['room_name'] }}</div>
                                    <div class="small text-muted">{{ $room['branch_name'] }}</div>
                                </td>
                                <td>{{ $room['location_name'] }}</td>
                                <td class="text-center fw-bold">{{ $room['total'] }}</td>
                                <td class="text-center text-danger fw-bold">{{ $room['open'] }}</td>
                                <td>
                                    @if($room['last_fault'])
                                    <a href="{{ route('faults.show', $room['last_fault']) }}" class="fw-semibold text-decoration-none">
                                        #{{ $room['last_fault']->id }}
                                    </a>
                                    <div class="small text-muted">{{ $room['last_fault']->created_at?->format('d.m.Y H:i') }}</div>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Bu arıza türü için oda kaydı bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="report-card p-3 h-100">
                <div class="section-title">
                    <h5>Arıza Detayları</h5>
                    <span class="muted-pill">{{ $faults->total() }} kayıt</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Oda / Konum</th>
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
                            <tr><td colspan="6" class="text-center text-muted py-4">Bu filtreye uygun arıza bulunamadı.</td></tr>
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
