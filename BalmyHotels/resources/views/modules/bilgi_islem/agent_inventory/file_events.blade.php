@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Dosya Silme Logları')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Bilgi İşlem — Ajan Envanter — Dosya Silme Logları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
                <li class="breadcrumb-item active">Dosya Silme Logları</li>
            </ol>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Ara (dosya yolu / kullanıcı / uygulama)</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="rapor.xlsx, john.doe, explorer.exe ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Başlangıç Tarihi</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Bitiş Tarihi</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('it.agent.file-events', $agentComputer) }}"
                       class="btn btn-sm btn-outline-secondary ms-1">Temizle</a>
                </div>
                <div class="col-md-auto ms-auto">
                    <a href="{{ route('it.agent.show', $agentComputer) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Bilgisayar Detayı
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-trash-alt me-2 text-danger"></i>
                Dosya Silme Olayları
                <span class="badge bg-secondary rounded-pill ms-2 fw-normal">{{ $events->total() }}</span>
            </h6>
            <span class="small text-muted">Sayfa {{ $events->currentPage() }} / {{ $events->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="ps-4 py-2 text-muted">ZAMAN</th>
                            <th class="py-2 text-muted">KULLANICI</th>
                            <th class="py-2 text-muted">DOSYA YOLU</th>
                            <th class="py-2 text-muted">UYGULAMA</th>
                            <th class="py-2 text-muted">EVENT ID</th>
                            <th class="py-2 text-muted">ACCESS MASK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $ev)
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <span class="text-muted">{{ $ev->event_time->format('d.m.Y') }}</span>
                                <br>
                                <strong>{{ $ev->event_time->format('H:i:s') }}</strong>
                            </td>
                            <td>
                                @if($ev->subject_user)
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    <span class="fw-semibold">{{ $ev->subject_user }}</span>
                                    @if($ev->subject_domain)
                                        <br><span class="text-muted" style="font-size:.75rem">{{ $ev->subject_domain }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width:380px;word-break:break-all;">
                                @if($ev->object_name)
                                    @php
                                        $parts = explode('\\', $ev->object_name);
                                        $filename = end($parts);
                                        $dir = implode('\\', array_slice($parts, 0, -1));
                                    @endphp
                                    <span class="text-muted" style="font-size:.75rem">{{ $dir }}\</span><br>
                                    <span class="fw-semibold text-danger">{{ $filename }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width:200px;word-break:break-all;">
                                @if($ev->process_name)
                                    @php $proc = basename(str_replace('\\', '/', $ev->process_name)); @endphp
                                    <span title="{{ $ev->process_name }}">{{ $proc }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $ev->event_id == 4660 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $ev->event_id }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $ev->access_mask ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                                Kayıt bulunamadı.
                                @if(request()->hasAny(['search','from','to']))
                                    <br><small>Filtreleri temizlemeyi deneyin.</small>
                                @else
                                    <br><small>GPO Object Access Auditing aktif edilmeli ve
                                    SACL klasörlere uygulanmalıdır.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($events->hasPages())
        <div class="card-footer bg-white py-2 px-4">
            {{ $events->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
