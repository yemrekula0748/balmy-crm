@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Tarayıcı Geçmişi')

@section('content')
<div class="container-fluid pb-5">

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

    @php
        $browserMeta = [
            'chrome'  => ['label'=>'Chrome',  'color'=>'#4285F4', 'icon'=>'🌐'],
            'edge'    => ['label'=>'Edge',    'color'=>'#0078D4', 'icon'=>'🔷'],
            'firefox' => ['label'=>'Firefox', 'color'=>'#FF7139', 'icon'=>'🦊'],
            'brave'   => ['label'=>'Brave',   'color'=>'#FB542B', 'icon'=>'🦁'],
            'opera'   => ['label'=>'Opera',   'color'=>'#FF1B2D', 'icon'=>'🔴'],
            'vivaldi' => ['label'=>'Vivaldi', 'color'=>'#EF3939', 'icon'=>'🎵'],
        ];
    @endphp

    {{-- Filtre Kartı --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden">
        <div class="card-body p-4">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-search me-1"></i>URL veya Sayfa Başlığı
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-link text-muted"></i>
                            </span>
                            <input type="text" name="search"
                                   class="form-control border-start-0 ps-0"
                                   value="{{ request('search') }}"
                                   placeholder="google.com, youtube.com ...">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-user me-1"></i>Kullanıcı
                        </label>
                        <select name="username" class="form-select form-select-sm">
                            <option value="">Tümü</option>
                            @foreach($users as $u)
                                <option value="{{ $u }}" {{ request('username') == $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-globe me-1"></i>Tarayıcı
                        </label>
                        <select name="browser" class="form-select form-select-sm">
                            <option value="">Tümü</option>
                            @foreach($browsers as $b)
                                <option value="{{ $b }}" {{ request('browser') == $b ? 'selected' : '' }}>
                                    {{ $browserMeta[$b]['label'] ?? ucfirst($b) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-calendar me-1"></i>Başlangıç
                        </label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label fw-semibold small mb-1 text-muted">
                            <i class="fas fa-calendar me-1"></i>Bitiş
                        </label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-filter me-1"></i>Filtrele
                            </button>
                            @if(request()->hasAny(['search','username','browser','from','to']))
                            <a href="{{ route('it.agent.browser-history', $agentComputer) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Temizle
                            </a>
                            @endif
                        </div>
                        <a href="{{ route('it.agent.show', $agentComputer) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Geri Dön
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sonuç Başlığı --}}
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold" style="font-size:1.05rem">
                <i class="fas fa-history me-2 text-info"></i>Tarayıcı Geçmişi
            </span>
            <span class="badge rounded-pill bg-secondary fw-normal">
                {{ number_format($history->total()) }} kayıt
            </span>
        </div>
        <span class="text-muted small">
            Sayfa {{ $history->currentPage() }} / {{ $history->lastPage() }}
            &nbsp;·&nbsp; Her sayfada 100 kayıt
        </span>
    </div>

    {{-- Kayıt Listesi --}}
    <div class="d-flex flex-column gap-2">

        @forelse($history as $row)
        @php
            $meta       = $browserMeta[$row->browser] ?? ['label'=>ucfirst($row->browser),'color'=>'#6c757d','icon'=>'🌐'];
            $domain     = parse_url($row->url, PHP_URL_HOST) ?: $row->url;
            $faviconUrl = 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=32';
            $isToday    = $row->visit_time->isToday();
            $isYest     = $row->visit_time->isYesterday();
            $dateStr    = $isToday ? 'Bugün' : ($isYest ? 'Dün' : $row->visit_time->format('d.m.Y'));
        @endphp

        <div class="card border-0 shadow-sm" style="border-radius:12px;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 4px 20px rgba(0,0,0,.10)'"
             onmouseleave="this.style.boxShadow=''">
            <div class="card-body py-3 px-4">
                <div class="row align-items-center g-3">

                    {{-- Favicon --}}
                    <div class="col-auto">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-light"
                             style="width:42px;height:42px;flex-shrink:0">
                            <img src="{{ $faviconUrl }}"
                                 width="32" height="32"
                                 style="object-fit:contain">
                        </div>
                    </div>

                    {{-- Başlık + URL --}}
                    <div class="col">
                        <div class="fw-semibold mb-0"
                             style="font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px"
                             title="{{ $row->title ?? $row->url }}">
                            {{ $row->title ?: $domain }}
                        </div>
                        <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer"
                           class="text-decoration-none"
                           style="font-size:.78rem;color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:520px"
                           title="{{ $row->url }}">
                            <i class="fas fa-external-link-alt me-1" style="font-size:.65rem"></i>{{ $row->url }}
                        </a>
                    </div>

                    {{-- Kullanıcı --}}
                    <div class="col-auto d-none d-md-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:32px;height:32px;background:#f1f3f5;flex-shrink:0">
                            <i class="fas fa-user text-muted" style="font-size:.7rem"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.82rem;line-height:1.2">{{ $row->username }}</div>
                            @if($row->profile && $row->profile !== 'Default')
                                <div class="text-muted" style="font-size:.72rem">{{ $row->profile }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Tarayıcı --}}
                    <div class="col-auto d-none d-lg-block">
                        <span class="badge rounded-pill px-3 py-2"
                              style="background:{{ $meta['color'] }}18;color:{{ $meta['color'] }};border:1px solid {{ $meta['color'] }}35;font-size:.75rem">
                            {{ $meta['icon'] }} {{ $meta['label'] }}
                        </span>
                    </div>

                    {{-- Zaman --}}
                    <div class="col-auto text-end" style="min-width:90px">
                        <div class="fw-semibold" style="font-size:.82rem">{{ $row->visit_time->format('H:i:s') }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $dateStr }}</div>
                        @if($row->visit_count > 1)
                            <span class="badge bg-light text-muted border mt-1" style="font-size:.68rem">
                                {{ $row->visit_count }}x ziyaret
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        @empty
        <div class="card border-0 shadow-sm" style="border-radius:14px">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size:3rem;opacity:.25">🌐</div>
                <p class="text-muted mb-1 fw-semibold">Kayıt bulunamadı</p>
                @if(request()->hasAny(['search','username','browser','from','to']))
                    <p class="text-muted small mb-2">Filtreleri temizlemeyi deneyin.</p>
                    <a href="{{ route('it.agent.browser-history', $agentComputer) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Filtreleri Temizle
                    </a>
                @else
                    <p class="text-muted small mb-0">Agent henüz tarayıcı geçmişi göndermemiş.</p>
                @endif
            </div>
        </div>
        @endforelse

    </div>

    {{-- Pagination --}}
    @if($history->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $history->links() }}
    </div>
    @endif

</div>
@endsection