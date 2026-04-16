@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Silinen Dosyalar')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Bilgi İşlem — Ajan Envanter — Silinen Dosyalar</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
                <li class="breadcrumb-item active">Silinen Dosyalar</li>
            </ol>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Ara (dosya adı / yol)</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="rapor.xlsx, documents ...">
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
                    <a href="{{ route('it.agent.deletions', $agentComputer) }}"
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
                <i class="fas fa-file-excel me-2 text-danger"></i>
                Silinen Dosyalar
                <span class="badge bg-secondary rounded-pill ms-2 fw-normal">{{ $deletions->total() }}</span>
            </h6>
            <span class="small text-muted">Sayfa {{ $deletions->currentPage() }} / {{ $deletions->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="ps-4 py-2 text-muted">SİLİNME ZAMANI</th>
                            <th class="py-2 text-muted">DOSYA ADI</th>
                            <th class="py-2 text-muted">KLASÖR</th>
                            <th class="py-2 text-muted">BOYUT</th>
                            <th class="py-2 text-muted">SON DEĞİŞTİRME</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletions as $del)
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <span class="text-muted">{{ $del->deleted_at->format('d.m.Y') }}</span>
                                <br>
                                <strong>{{ $del->deleted_at->format('H:i:s') }}</strong>
                            </td>
                            <td style="max-width:260px;word-break:break-all;">
                                @if($del->name)
                                    <i class="fas fa-file me-1 text-danger" style="font-size:.75rem"></i>
                                    <span class="fw-semibold text-danger">{{ $del->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width:380px;word-break:break-all;">
                                @if($del->directory)
                                    <span class="text-muted" style="font-size:.78rem">{{ $del->directory }}</span>
                                @elseif($del->path)
                                    @php
                                        $parts = explode('\\', $del->path);
                                        array_pop($parts);
                                        $dir = implode('\\', $parts);
                                    @endphp
                                    <span class="text-muted" style="font-size:.78rem">{{ $dir }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($del->size !== null)
                                    <span class="badge bg-light text-dark border">{{ $del->human_size }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-muted" style="font-size:.78rem">
                                @if($del->modified_at)
                                    {{ $del->modified_at->format('d.m.Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                                Kayıt bulunamadı.
                                @if(request()->hasAny(['search','from','to']))
                                    <br><small>Filtreleri temizlemeyi deneyin.</small>
                                @else
                                    <br><small>Agent henüz silinen dosya verisi göndermedi.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deletions->hasPages())
        <div class="card-footer bg-white py-2 px-4">
            {{ $deletions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
