@extends('layouts.default')
@section('title', $event->title)

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $event->title }}</h4>
                <span>{{ $event->starts_at->format('d.m.Y H:i') }} - {{ $event->language_label }}</span>
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $attending = $event->responses->where('status', 'attending')->count();
        $declined = $event->responses->where('status', 'declined')->count();
        $pending = $event->responses->where('status', 'pending')->count();
    @endphp

    @if($myResponse)
        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;border-left:4px solid #1e2d3d!important">
            <div class="card-body">
                @php
                    $responseStatus = old('status', $myResponse->status);
                @endphp
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <div class="small text-muted mb-1">Katilim Cevabim</div>
                        <h5 class="mb-1">{{ $myResponse->status_label }}</h5>
                        <small class="text-muted">Bu egitim icin katilip katilamayacagini buradan isaretleyebilirsin.</small>
                    </div>
                    <form method="POST" action="{{ route('education.events.respond', $event) }}" class="flex-grow-1" style="max-width:560px">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Aciklama</label>
                            <textarea name="note" class="form-control" rows="2" maxlength="1000" placeholder="Katilamayacaksan aciklama yazmalisin.">{{ old('note', $myResponse->note) }}</textarea>
                            <small class="text-muted">Katilamayacak kisiler icin aciklama zorunludur.</small>
                        </div>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <button
                                    class="btn btn-lg w-100 {{ $responseStatus === 'attending' ? 'btn-success' : 'btn-outline-success' }} fw-semibold"
                                    type="submit"
                                    name="status"
                                    value="attending"
                                >
                                    <i class="fas fa-check me-1"></i>Katilacagim
                                </button>
                            </div>
                            <div class="col-sm-6">
                                <button
                                    class="btn btn-lg w-100 {{ $responseStatus === 'declined' ? 'btn-danger' : 'btn-outline-danger' }} fw-semibold"
                                    type="submit"
                                    name="status"
                                    value="declined"
                                >
                                    <i class="fas fa-times me-1"></i>Katilamayacagim
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->hasAnyRole(['ogrenen']))
        <div class="alert alert-warning mb-3">
            Bu yuz yuze egitim henuz hesabina atanmamis. Oylama yapabilmen icin egitmenin duyuru duzenleme ekraninda seni ogrenen listesine eklemesi gerekir.
        </div>
    @endif

    <div class="row g-3">
        <div class="{{ $canSeeParticipationData ? 'col-lg-4' : 'col-12' }}">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div class="small text-muted">Egitmen</div>
                    <div class="fw-semibold mb-3">{{ $event->trainer->name ?? '-' }}</div>
                    <div class="small text-muted">Konum</div>
                    <div class="fw-semibold mb-3">{{ $event->location ?: '-' }}</div>
                    <div class="small text-muted">Aciklama</div>
                    <p class="mb-3">{{ $event->description ?: '-' }}</p>
                    @if($canSeeParticipationData)
                        <div class="row text-center g-2">
                            <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-success">{{ $attending }}</div><small>Katilacak</small></div></div>
                            <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-danger">{{ $declined }}</div><small>Katilmiyor</small></div></div>
                            <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-muted">{{ $pending }}</div><small>Bekliyor</small></div></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if($canSeeParticipationData)
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Katilim Listesi</h5>
                    @if(auth()->user()->hasPermission('education_events','edit'))
                        <a href="{{ route('education.events.edit', $event) }}" class="btn btn-sm btn-outline-warning">Duzenle</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ogrenen</th>
                                    <th>Departman</th>
                                    <th class="text-center">Durum</th>
                                    <th>Aciklama</th>
                                    <th class="text-center">Cevap Zamani</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($event->responses as $response)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $response->learner->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $response->learner->branch->name ?? '' }}</small>
                                        </td>
                                        <td>{{ $response->learner->department->name ?? '-' }}</td>
                                        <td class="text-center">{{ $response->status_label }}</td>
                                        <td>{{ $response->note ?: '-' }}</td>
                                        <td class="text-center">{{ $response->responded_at ? $response->responded_at->format('d.m.Y H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
