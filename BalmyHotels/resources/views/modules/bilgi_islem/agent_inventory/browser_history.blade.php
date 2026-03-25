@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Tarayıcı Geçmişi')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Bilgi İşlem — Ajan Envanter — Tarayıcı Geçmişi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
                <li class="breadcrumb-item active">Tarayıcı Geçmişi</li>
            </ol>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">URL / Başlık</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="google.com, youtube ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Kullanıcı</label>
                    <select name="username" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($users as $u)
                            <option value="{{ $u }}" {{ request('username') == $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Tarayıcı</label>
                    <select name="browser" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($browsers as $b)
                            <option value="{{ $b }}" {{ request('browser') == $b ? 'selected' : '' }}>{{ ucfirst($b) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Başlangıç</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Bitiş</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('it.agent.browser-history', $agentComputer) }}"
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
                <i class="fas fa-globe me-2 text-info"></i>
                Tarayıcı Geçmişi
                <span class="badge bg-secondary rounded-pill ms-2 fw-normal">{{ $history->total() }}</span>
            </h6>
            <span class="small text-muted">Sayfa {{ $history->currentPage() }} / {{ $history->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="ps-4 py-2 text-muted">ZAMAN</th>
                            <th class="py-2 text-muted">KULLANICI</th>
                            <th class="py-2 text-muted">TARAYICI</th>
                            <th class="py-2 text-muted">BAŞLIK / URL</th>
                            <th class="py-2 text-muted text-center">ZİYARET</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $row)
                        @php
                            $browserColors = [
                                'chrome'   => '#4285F4',
                                'edge'     => '#0078D4',
                                'firefox'  => '#FF7139',
                                'brave'    => '#FB542B',
                                'opera'    => '#FF1B2D',
                                'vivaldi'  => '#EF3939',
                            ];
                            $color = $browserColors[$row->browser] ?? '#6c757d';
                            $domain = parse_url($row->url, PHP_URL_HOST) ?: $row->url;
                        @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <span class="text-muted">{{ $row->visit_time->format('d.m.Y') }}</span><br>
                                <strong>{{ $row->visit_time->format('H:i:s') }}</strong>
                            </td>
                            <td>
                                <i class="fas fa-user me-1 text-muted"></i>
                                <span class="fw-semibold">{{ $row->username }}</span>
                                @if($row->profile && $row->profile !== 'Default')
                                    <br><span class="text-muted" style="font-size:.75rem">{{ $row->profile }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background:{{ $color }}20;color:{{ $color }};border:1px solid {{ $color }}40">
                                    {{ ucfirst($row->browser) }}
                                </span>
                            </td>
                            <td style="max-width:420px;">
                                @if($row->title)
                                    <div class="fw-semibold text-truncate" style="max-width:400px" title="{{ $row->title }}">
                                        {{ $row->title }}
                                    </div>
                                @endif
                                <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-muted text-truncate d-block" style="max-width:400px;font-size:.75rem"
                                   title="{{ $row->url }}">
                                    {{ $domain }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $row->visit_count }}x</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-globe fa-2x mb-2 d-block opacity-25"></i>
                                Kayıt bulunamadı.
                                @if(request()->hasAny(['search','username','browser','from','to']))
                                    <br><small>Filtreleri temizlemeyi deneyin.</small>
                                @else
                                    <br><small>Agent henüz tarayıcı geçmişi göndermemiş.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($history->hasPages())
        <div class="card-footer bg-white py-2 px-4">
            {{ $history->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
