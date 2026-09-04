@extends('layouts.default')

@section('title', 'Oltalama Farkındalık Testleri')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-user-shield me-2 text-primary"></i>Oltalama Farkındalık Testleri</h4>
                <span class="text-muted">Kişiye özel bağlantılar ve güvenli davranış ölçümü</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 d-flex justify-content-sm-end align-items-center mt-2 mt-sm-0 gap-2">
            @if(auth()->user()->hasPermission('it_phishing_tests', 'create'))
                <a href="{{ route('it.phishing.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Yeni kampanya
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info border-0 shadow-sm">
        <div class="d-flex gap-3">
            <i class="fas fa-lock mt-1"></i>
            <div>
                <strong>Güvenli ölçüm:</strong> Bu modül kullanıcı adı veya parola toplamaz.
                Yalnızca kişisel bağlantının açıldığını ve bilgi gönderme butonuna basıldığını kaydeder.
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Toplam kampanya', 'value' => $stats['campaigns'], 'icon' => 'fa-bullseye', 'color' => '#4361ee'],
            ['label' => 'Aktif kampanya', 'value' => $stats['active_campaigns'], 'icon' => 'fa-play-circle', 'color' => '#16a34a'],
            ['label' => 'Toplam hedef', 'value' => $stats['targets'], 'icon' => 'fa-users', 'color' => '#0ea5e9'],
            ['label' => 'Bilgi gönderme denemesi', 'value' => $stats['attempted'], 'icon' => 'fa-exclamation-triangle', 'color' => '#dc2626'],
        ] as $card)
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:46px;height:46px;background:{{ $card['color'] }}18;color:{{ $card['color'] }}">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="fw-bold fs-5">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">Kampanyalar</h5>
        </div>
        <div class="card-body p-0">
            @if($campaigns->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-user-shield fa-3x text-muted opacity-25 mb-3"></i>
                    <p class="text-muted mb-0">Henüz kampanya oluşturulmadı.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kampanya</th>
                                <th>Durum</th>
                                <th class="text-center">Hedef</th>
                                <th class="text-center">Açtı</th>
                                <th class="text-center">Bilgi denedi</th>
                                <th>Oluşturan</th>
                                <th>Tarih</th>
                                <th class="text-end pe-4">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $campaign->name }}</td>
                                    <td>
                                        <span class="badge {{ $campaign->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $campaign->status === 'active' ? 'Aktif' : 'Kapalı' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $campaign->targets_count }}</td>
                                    <td class="text-center text-warning fw-semibold">{{ $campaign->clicked_count }}</td>
                                    <td class="text-center text-danger fw-semibold">{{ $campaign->attempted_count }}</td>
                                    <td>{{ $campaign->creator?->name ?? '—' }}</td>
                                    <td class="small text-muted">{{ $campaign->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('it.phishing.show', $campaign) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-chart-bar me-1"></i>Rapor
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($campaigns->hasPages())
                    <div class="px-4 py-3 border-top">{{ $campaigns->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
