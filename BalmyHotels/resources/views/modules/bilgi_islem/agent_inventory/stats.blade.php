@extends('layouts.default')

@section('title', 'IT İstatistikleri')

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Agent Envanter İstatistikleri</h4>
                <span>Bilgi İşlem — Genel Durum</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item active">İstatistikler</li>
            </ol>
        </div>
    </div>

    {{-- Özet Kartlar --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Toplam Bilgisayar',  'value' => $total,         'icon' => 'fa-desktop',       'color' => '#4361ee'],
                ['label' => 'Domain Üyesi',        'value' => $domain,        'icon' => 'fa-network-wired', 'color' => '#10b981'],
                ['label' => 'AV Kapalı',           'value' => $avDisabled,    'icon' => 'fa-shield-alt',    'color' => '#ef4444'],
                ['label' => 'Disk Uyarısı (>80%)', 'value' => $diskWarning,   'icon' => 'fa-hdd',           'color' => '#f59e0b'],
                ['label' => 'RDP Açık',            'value' => $rdpEnabled,    'icon' => 'fa-tv',            'color' => '#8b5cf6'],
                ['label' => 'Son 1 Saatte Görüldü','value' => $seenLastHour,  'icon' => 'fa-clock',         'color' => '#06b6d4'],
                ['label' => 'Son 24 Saatte',       'value' => $seenLast24h,   'icon' => 'fa-calendar-day',  'color' => '#64748b'],
                ['label' => 'Hiç Rapor Yok',       'value' => $neverSeen,     'icon' => 'fa-question-circle','color' => '#94a3b8'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:{{ $card['color'] }}20;flex-shrink:0">
                        <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1">{{ $card['value'] }}</div>
                        <div class="small text-muted mt-1">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- İşletim Sistemi Dağılımı --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-3 px-4">
            <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>İşletim Sistemi Dağılımı</h6>
        </div>
        <div class="card-body px-4 py-3">
            @forelse($osBreakdown as $os => $count)
            @php $pct = $total > 0 ? round($count / $total * 100) : 0; @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold">{{ $os }}</span>
                    <span class="text-muted">{{ $count }} bilgisayar ({{ $pct }}%)</span>
                </div>
                <div class="progress" style="height:10px">
                    <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @empty
                <p class="text-muted small mb-0">Veri yok.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('it.agent.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Listeye Dön
        </a>
    </div>
</div>
@endsection
