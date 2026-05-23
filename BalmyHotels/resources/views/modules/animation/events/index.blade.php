@extends('layouts.default')

@section('title', 'Animasyon Etkinlikleri')

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.table thead th { background:#f8fafc;color:#64748b;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e8eef5; }
.table td { vertical-align:middle;font-size:.86rem; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Animasyon</h4>
                <span class="text-muted" style="font-size:.8rem">Etkinlik oluşturma ve katılımcı planlama</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Animasyon</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="soft-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4>Etkinlik Tanımları</h4>
            <p>Show/animasyon etkinliklerini, katılımcı listesini ve birden fazla etkinlik tarihini buradan yönetin.</p>
        </div>
        @if(auth()->user()->hasPermission('animation_events', 'create'))
        <a href="{{ route('animation.events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Etkinlik Oluştur
        </a>
        @endif
    </div>

    <div class="module-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Etkinlik</th>
                        <th>Şube</th>
                        <th class="text-center">Tarih</th>
                        <th class="text-center">Katılımcı</th>
                        <th>Sonraki Tarih</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    @php
                        $nextDate = $event->dates->first(fn($date) => $date->event_date->gte(now()->startOfDay()));
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $event->name }}</div>
                            <div class="small text-muted">Oluşturma: {{ $event->created_at?->format('d.m.Y H:i') }}</div>
                        </td>
                        <td>{{ $event->branch?->name ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $event->dates->count() }}</td>
                        <td class="text-center fw-bold">{{ $event->participants->count() }}</td>
                        <td>
                            @if($nextDate)
                            <span class="badge bg-info">{{ $nextDate->event_date->format('d.m.Y') }}</span>
                            @else
                            <span class="text-muted">Planlı gelecek tarih yok</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('animation.events.show', $event) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                            @if(auth()->user()->hasPermission('animation_events', 'delete'))
                            <form action="{{ route('animation.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Etkinlik silinsin mi?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Henüz etkinlik oluşturulmamış.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $events->links() }}</div>
    </div>
</div>
@endsection
