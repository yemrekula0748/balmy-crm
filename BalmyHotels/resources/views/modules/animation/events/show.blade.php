@extends('layouts.default')

@section('title', $event->name)

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.metric-card { background:#f8fafc;border:1px solid #e8eef5;border-radius:14px;padding:15px 16px; }
.metric-label { color:#64748b;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em; }
.metric-value { font-size:1.5rem;font-weight:850;color:#172033;line-height:1;margin-top:7px; }
.table thead th { background:#f8fafc;color:#64748b;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e8eef5; }
.table td { vertical-align:middle;font-size:.86rem; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">{{ $event->name }}</h4>
                <span class="text-muted" style="font-size:.8rem">Etkinlik detayları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('animation.events.index') }}">Animasyon</a></li>
                <li class="breadcrumb-item active">Detay</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="soft-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4>{{ $event->name }}</h4>
            <p>{{ $event->branch?->name ?? '-' }} · Oluşturan: {{ $event->creator?->name ?? '-' }}</p>
        </div>
        <a href="{{ route('animation.events.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Planlı Tarih</div>
                <div class="metric-value">{{ $event->dates->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Katılımcı</div>
                <div class="metric-value">{{ $event->participants->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Durum</div>
                <div class="metric-value" style="font-size:1.05rem">{{ $event->is_active ? 'Aktif' : 'Pasif' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="module-card p-3 h-100">
                <h5 class="fw-bold mb-3">Katılımcılar</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ad Soyad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($event->participants as $participant)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $participant->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="module-card p-3 h-100">
                <h5 class="fw-bold mb-3">Etkinlik Tarihleri</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th class="text-center">Gelen</th>
                                <th class="text-center">Beklenen</th>
                                <th class="text-end">Giriş İşlemi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($event->dates as $date)
                            <tr>
                                <td class="fw-bold">{{ $date->event_date->format('d.m.Y') }}</td>
                                <td class="text-center">{{ $date->attendances->count() }}</td>
                                <td class="text-center">{{ $event->participants->count() }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->hasPermission('event_tracking', 'index'))
                                    <a href="{{ route('door-logs.event-tracking.show', $date) }}" class="btn btn-sm btn-outline-primary">
                                        Etkinlik Giriş İşlemleri
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
