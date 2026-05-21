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

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div class="small text-muted">Egitmen</div>
                    <div class="fw-semibold mb-3">{{ $event->trainer->name ?? '-' }}</div>
                    <div class="small text-muted">Konum</div>
                    <div class="fw-semibold mb-3">{{ $event->location ?: '-' }}</div>
                    <div class="small text-muted">Aciklama</div>
                    <p class="mb-3">{{ $event->description ?: '-' }}</p>
                    <div class="row text-center g-2">
                        <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-success">{{ $attending }}</div><small>Katilacak</small></div></div>
                        <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-danger">{{ $declined }}</div><small>Katilmiyor</small></div></div>
                        <div class="col-4"><div class="border rounded p-2"><div class="fw-bold text-muted">{{ $pending }}</div><small>Bekliyor</small></div></div>
                    </div>
                </div>
            </div>

            @if($myResponse)
                <div class="card border-0 shadow-sm mt-3" style="border-radius:8px">
                    <div class="card-header bg-white border-0"><h5 class="mb-0">Katilim Cevabim</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('education.events.respond', $event) }}">
                            @csrf
                            <div class="mb-3">
                                <select name="status" class="form-select" required>
                                    <option value="attending" @selected(old('status', $myResponse->status) === 'attending')>Katilacagim</option>
                                    <option value="declined" @selected(old('status', $myResponse->status) === 'declined')>Katilamayacagim</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Aciklama</label>
                                <textarea name="note" class="form-control" rows="3" maxlength="1000">{{ old('note', $myResponse->note) }}</textarea>
                                <small class="text-muted">Katilamayacak kisiler icin aciklama zorunludur.</small>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Cevabi Kaydet</button>
                        </form>
                    </div>
                </div>
            @elseif(auth()->user()->hasAnyRole(['ogrenen']))
                <div class="alert alert-warning mt-3 mb-0">
                    Bu yuz yuze egitim henuz hesabina atanmamis. Oylama yapabilmen icin egitmenin duyuru duzenleme ekraninda seni ogrenen listesine eklemesi gerekir.
                </div>
            @endif
        </div>
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
    </div>
</div>
@endsection
