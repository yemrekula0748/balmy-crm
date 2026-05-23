@extends('layouts.default')

@section('title', 'Etkinlik/Show Raporları')

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.metric-grid { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px; }
.metric-card { background:#fff;border:1px solid #e8eef5;border-radius:15px;padding:16px 18px;box-shadow:0 5px 18px rgba(15,23,42,.05); }
.metric-label { color:#64748b;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em; }
.metric-value { font-size:1.6rem;font-weight:850;color:#172033;line-height:1;margin-top:8px; }
.table thead th { background:#f8fafc;color:#64748b;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e8eef5; }
.table td { vertical-align:top;font-size:.84rem; }
.name-cloud { display:flex;flex-wrap:wrap;gap:6px; }
.name-pill { display:inline-flex;border:1px solid #e8eef5;background:#f8fafc;border-radius:999px;padding:4px 9px;font-size:.75rem;font-weight:700;color:#475569; }
.name-pill.present { background:#ecfdf5;border-color:#bbf7d0;color:#047857; }
.name-pill.missing { background:#fef2f2;border-color:#fecaca;color:#b91c1c; }
.empty-state { text-align:center;padding:45px 18px;color:#64748b; }
@media (max-width: 991px) { .metric-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width: 575px) { .metric-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Etkinlik/Show Raporları</h4>
                <span class="text-muted" style="font-size:.8rem">Katılımcı gelen/gelmeyen raporu</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Genel Raporlar</li>
            </ol>
        </div>
    </div>

    <div class="soft-hero">
        <h4>Etkinlik/Show Katılım Raporu</h4>
        <p>Seçilen tarih aralığında hangi showda kaç katılımcı beklendi, kaçı geldi, kimler geldi/gelmedi bilgilerini görüntüleyin.</p>
    </div>

    <form method="GET" action="{{ route('reports.event-shows') }}" class="module-card p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold small">Şube</label>
                <select name="branch_id" class="form-select">
                    <option value="">Tüm şubeler</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold small">Etkinlik</label>
                <select name="animation_event_id" class="form-select">
                    <option value="">Tüm etkinlikler</option>
                    @foreach($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('animation_event_id') === (string) $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold small">Başlangıç</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold small">Bitiş</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <a href="{{ route('reports.event-shows') }}" class="btn btn-outline-secondary w-50">Temizle</a>
                <button class="btn btn-primary w-50">Raporla</button>
            </div>
        </div>
    </form>

    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-label">Show Sayısı</div>
            <div class="metric-value">{{ $summary['show_count'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Beklenen Katılımcı</div>
            <div class="metric-value">{{ $summary['expected_count'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Gelen</div>
            <div class="metric-value" style="color:#059669">{{ $summary['present_count'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Katılım Oranı</div>
            <div class="metric-value" style="color:#1e3a5f">%{{ $summary['attendance_rate'] }}</div>
        </div>
    </div>

    <div class="module-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Etkinlik</th>
                        <th class="text-center">Beklenen</th>
                        <th class="text-center">Gelen</th>
                        <th class="text-center">Gelmedi</th>
                        <th>Gelen İsimler</th>
                        <th>Gelmeyen İsimler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td class="fw-bold">{{ $row['event_date']->event_date->format('d.m.Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $row['event']->name }}</div>
                            <div class="small text-muted">{{ $row['event']->branch?->name ?? '-' }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ $row['expected_count'] }}</td>
                        <td class="text-center fw-bold text-success">{{ $row['present_count'] }}</td>
                        <td class="text-center fw-bold text-danger">{{ $row['missing_count'] }}</td>
                        <td>
                            <div class="name-cloud">
                                @forelse($row['present_names'] as $name)
                                <span class="name-pill present">{{ $name }}</span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <div class="name-cloud">
                                @forelse($row['missing_names'] as $name)
                                <span class="name-pill missing">{{ $name }}</span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            Seçili tarih/filtre aralığında etkinlik kaydı bulunamadı.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
