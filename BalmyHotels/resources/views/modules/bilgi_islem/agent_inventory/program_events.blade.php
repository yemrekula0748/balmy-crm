@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Program Değişiklikleri')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Bilgi İşlem — Ajan Envanter — Program Değişiklikleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
                <li class="breadcrumb-item active">Program Değişiklikleri</li>
            </ol>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Ara (program adı / yayıncı)</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Chrome, Adobe, VideoLAN ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Olay Türü</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="installed" {{ request('type') === 'installed' ? 'selected' : '' }}>✅ Kuruldu</option>
                        <option value="removed"   {{ request('type') === 'removed'   ? 'selected' : '' }}>🗑️ Kaldırıldı</option>
                    </select>
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
                    <a href="{{ route('it.agent.program-events', $agentComputer) }}"
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
                <i class="fas fa-boxes me-2 text-primary"></i>
                Program Değişiklikleri
                <span class="badge bg-secondary rounded-pill ms-2 fw-normal">{{ $events->total() }}</span>
            </h6>
            <span class="small text-muted">Sayfa {{ $events->currentPage() }} / {{ $events->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="ps-4 py-2 text-muted">TESPİT ZAMANI</th>
                            <th class="py-2 text-muted">OLAY</th>
                            <th class="py-2 text-muted">PROGRAM ADI</th>
                            <th class="py-2 text-muted">VERSİYON</th>
                            <th class="py-2 text-muted">YAYINCI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $ev)
                        <tr style="{{ $ev->event_type === 'installed' ? 'background:#f0fdf4' : 'background:#fff1f2' }}">
                            <td class="ps-4 text-nowrap">
                                <span class="text-muted">{{ $ev->detected_at->format('d.m.Y') }}</span>
                                <br>
                                <strong>{{ $ev->detected_at->format('H:i:s') }}</strong>
                            </td>
                            <td>
                                @if($ev->event_type === 'installed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-plus-circle me-1"></i>Kuruldu
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-minus-circle me-1"></i>Kaldırıldı
                                    </span>
                                @endif
                            </td>
                            <td style="font-weight:600;max-width:300px;word-break:break-word">
                                {{ $ev->program_name }}
                            </td>
                            <td class="text-muted">{{ $ev->version ?? '—' }}</td>
                            <td class="text-muted">{{ $ev->publisher ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                                Kayıt bulunamadı.
                                @if(request()->hasAny(['search','from','to','type']))
                                    <br><small>Filtreleri temizlemeyi deneyin.</small>
                                @else
                                    <br><small>Değişiklikler bir sonraki raporla birlikte tespit edilir.</small>
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
