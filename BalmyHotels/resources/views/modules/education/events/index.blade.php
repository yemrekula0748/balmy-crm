@extends('layouts.default')
@section('title', 'Yuz Yuze Egitimler')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yuz Yuze Egitimler</h4>
                <span>Tarihli egitim duyurulari ve katilim cevaplari</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($pendingEventNotificationCount > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <strong>{{ $pendingEventNotificationCount }}</strong> yuz yuze egitim icin katilim cevabin bekleniyor.
            </div>
            <span class="small text-muted">Detay sayfasina girip Katilacagim veya Katilamayacagim butonuyla cevap verebilirsin.</span>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Duyurular</h5>
            @if(auth()->user()->hasPermission('education_events','create'))
                <a href="{{ route('education.events.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Yeni Duyuru
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Baslik</th>
                            <th>Tarih</th>
                            <th>Konum</th>
                            @if($canSeeParticipationData)
                                <th class="text-center">Katilacak</th>
                                <th class="text-center">Katilamayacak</th>
                                <th class="text-center">Bekliyor</th>
                            @endif
                            <th class="text-end">Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            @php
                                $attending = $event->responses->where('status', 'attending')->count();
                                $declined = $event->responses->where('status', 'declined')->count();
                                $pending = $event->responses->where('status', 'pending')->count();
                                $myResponse = $event->responses->firstWhere('user_id', auth()->id());
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    <small class="text-muted">{{ $event->language_label }} - {{ $event->trainer->name ?? '-' }}</small>
                                    @if($myResponse && $myResponse->status === \App\Models\EducationEventResponse::STATUS_PENDING)
                                        <span class="badge bg-warning text-dark ms-1">Cevap bekliyor</span>
                                    @endif
                                </td>
                                <td>{{ $event->starts_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $event->location ?: '-' }}</td>
                                @if($canSeeParticipationData)
                                    <td class="text-center text-success fw-bold">{{ $attending }}</td>
                                    <td class="text-center text-danger fw-bold">{{ $declined }}</td>
                                    <td class="text-center text-muted fw-bold">{{ $pending }}</td>
                                @endif
                                <td class="text-end">
                                    <a href="{{ route('education.events.show', $event) }}" class="btn btn-sm {{ $myResponse && $myResponse->status === \App\Models\EducationEventResponse::STATUS_PENDING ? 'btn-primary' : 'btn-outline-primary' }}">
                                        @if($myResponse && $myResponse->status === \App\Models\EducationEventResponse::STATUS_PENDING)
                                            <i class="fas fa-reply me-1"></i>Cevap Ver
                                        @else
                                            <i class="fas fa-eye"></i>
                                        @endif
                                    </a>
                                    @if($canSeeParticipationData && auth()->user()->hasPermission('education_events','edit'))
                                        <a href="{{ route('education.events.edit', $event) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canSeeParticipationData ? 7 : 4 }}" class="text-center text-muted py-4">Yuz yuze egitim duyurusu yok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($events->hasPages())
            <div class="card-footer bg-white">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection
