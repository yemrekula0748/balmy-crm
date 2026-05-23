@extends('layouts.default')

@section('title', 'Etkinlik Takip')

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.event-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;padding:18px;box-shadow:0 5px 20px rgba(15,23,42,.05);height:100%; }
.event-card h5 { font-weight:850;color:#172033;margin:0 0 8px; }
.mini-stat { display:inline-flex;align-items:center;gap:7px;background:#f8fafc;border:1px solid #e8eef5;border-radius:999px;padding:6px 10px;color:#475569;font-size:.78rem;font-weight:800; }
.empty-state { text-align:center;padding:48px 18px;color:#64748b; }
.empty-state i { font-size:2rem;color:#94a3b8;margin-bottom:10px; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Etkinlik Takip</h4>
                <span class="text-muted" style="font-size:.8rem">Günlük show/etkinlik giriş işlemleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.index') }}">Kapı Giriş/Çıkış</a></li>
                <li class="breadcrumb-item active">Etkinlik Takip</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="soft-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }} Etkinlikleri</h4>
            <p>Seçili gün için planlanan etkinlikleri görüntüleyin ve gelen katılımcıları işaretleyin.</p>
        </div>
        <form method="GET" action="{{ route('door-logs.event-tracking.index') }}" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date }}" class="form-control">
            <button class="btn btn-primary">Göster</button>
        </form>
    </div>

    @if($eventDates->isEmpty())
    <div class="module-card empty-state">
        <i class="fas fa-calendar-day"></i>
        <h5 class="fw-bold mb-1">Bu güne ait etkinlik yok</h5>
        <p class="mb-0">Animasyon modülünden etkinlik oluşturulduğunda ilgili tarihte burada görünecek.</p>
    </div>
    @else
    <div class="row g-3">
        @foreach($eventDates as $eventDate)
        @php
            $expected = $eventDate->event->participants->count();
            $present = $eventDate->attendances->count();
            $missing = max(0, $expected - $present);
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="event-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <h5>{{ $eventDate->event->name }}</h5>
                        <div class="text-muted small">{{ $eventDate->event->branch?->name ?? '-' }}</div>
                    </div>
                    <span class="badge bg-light text-dark border align-self-start">{{ $eventDate->event_date->format('d.m.Y') }}</span>
                </div>

                <div class="d-flex flex-wrap gap-2 my-3">
                    <span class="mini-stat"><i class="fas fa-users"></i> Beklenen: {{ $expected }}</span>
                    <span class="mini-stat"><i class="fas fa-check"></i> Gelen: {{ $present }}</span>
                    <span class="mini-stat"><i class="fas fa-user-clock"></i> Bekleyen: {{ $missing }}</span>
                </div>

                <a href="{{ route('door-logs.event-tracking.show', $eventDate) }}" class="btn btn-primary w-100">
                    Etkinlik Giriş İşlemleri
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
