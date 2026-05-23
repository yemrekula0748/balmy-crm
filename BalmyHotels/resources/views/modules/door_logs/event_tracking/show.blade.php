@extends('layouts.default')

@section('title', 'Etkinlik Giriş İşlemleri')

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.participant-grid { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px; }
.participant-check { border:1px solid #e8eef5;border-radius:13px;padding:13px 14px;background:#fff;display:flex;align-items:center;gap:10px;min-height:54px; }
.participant-check:hover { background:#f8fafc; }
.participant-check .form-check-input { width:18px;height:18px;margin:0; }
.metric-card { background:#f8fafc;border:1px solid #e8eef5;border-radius:14px;padding:14px 15px; }
.metric-label { color:#64748b;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em; }
.metric-value { font-size:1.45rem;font-weight:850;color:#172033;line-height:1;margin-top:7px; }
@media (max-width: 991px) { .participant-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width: 575px) { .participant-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
@php
    $participants = $eventDate->event->participants;
    $presentCount = count($presentIds);
    $expectedCount = $participants->count();
@endphp

<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Etkinlik Giriş İşlemleri</h4>
                <span class="text-muted" style="font-size:.8rem">{{ $eventDate->event->name }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.event-tracking.index', ['date' => $eventDate->event_date->toDateString()]) }}">Etkinlik Takip</a></li>
                <li class="breadcrumb-item active">Giriş İşlemi</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="soft-hero">
        <h4>{{ $eventDate->event->name }}</h4>
        <p>{{ $eventDate->event->branch?->name ?? '-' }} · {{ $eventDate->event_date->format('d.m.Y') }} tarihli etkinliğe gelen katılımcıları seçip kaydedin.</p>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Beklenen</div>
                <div class="metric-value">{{ $expectedCount }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Gelen</div>
                <div class="metric-value" style="color:#059669">{{ $presentCount }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-label">Gelmedi</div>
                <div class="metric-value" style="color:#dc2626">{{ max(0, $expectedCount - $presentCount) }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('door-logs.event-tracking.store', $eventDate) }}" class="module-card p-4">
        @csrf
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h5 class="fw-bold mb-0">Katılımcılar</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">Tümünü Seç</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all">Temizle</button>
            </div>
        </div>

        <div class="participant-grid">
            @foreach($participants as $participant)
            <label class="participant-check">
                <input class="form-check-input participant-input" type="checkbox" name="participant_ids[]" value="{{ $participant->id }}" @checked(in_array($participant->id, $presentIds))>
                <span class="fw-semibold">{{ $participant->name }}</span>
            </label>
            @endforeach
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('door-logs.event-tracking.index', ['date' => $eventDate->event_date->toDateString()]) }}" class="btn btn-outline-secondary">Geri</a>
            @if(auth()->user()->hasPermission('event_tracking', 'create'))
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Kaydet
            </button>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.participant-input');
    document.getElementById('select-all').addEventListener('click', function () {
        inputs.forEach(input => input.checked = true);
    });
    document.getElementById('clear-all').addEventListener('click', function () {
        inputs.forEach(input => input.checked = false);
    });
});
</script>
@endpush
