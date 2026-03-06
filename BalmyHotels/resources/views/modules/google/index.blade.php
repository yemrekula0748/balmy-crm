@extends('layouts.default')

@push('styles')
<style>
/* ── Sayfa başlığı — kurumsal koyu ── */
.gr-header {
    background: linear-gradient(135deg, #1e2d3d 0%, #2c3e50 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 28px;
}
.gr-header h4 { font-weight: 800; margin: 0 0 4px; font-size: 1.45rem; }
.gr-header p  { opacity: .7; margin: 0; font-size: 13px; }

/* ── Puan kart ── */
.gr-card {
    border-radius: 14px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: box-shadow .15s ease, transform .15s ease;
    background: #fff;
}
.gr-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0,0,0,.1);
}
.gr-card-accent {
    height: 4px;
    border-radius: 14px 14px 0 0;
    background: linear-gradient(90deg, #5f6368 0%, #80868b 100%);
}

/* ── Büyük puan ── */
.gr-big-score {
    font-size: 60px;
    font-weight: 900;
    line-height: 1;
    color: #1e2d3d;
    letter-spacing: -2px;
}

/* ── Yıldızlar ── */
.gr-stars { display: flex; gap: 3px; font-size: 20px; }

/* ── Google Maps bağlantı butonu ── */
.gr-maps-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #5f6368;
    background: #f8f9fa;
    border: 1.5px solid #e0e0e0;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.gr-maps-btn:hover {
    background: #1e2d3d;
    color: #fff;
    border-color: #1e2d3d;
}

/* ── İstatistik rozeti ── */
.gr-stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #f0f4f8;
    color: #2c3e50;
    border: 1px solid #dce3ea;
}

/* ── Grafik kart ── */
.gr-chart-card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    background: #fff;
}
.gr-chart-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
    padding: 13px 18px;
}

/* ── Bölüm başlığı ── */
.gr-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #5f6368;
    text-transform: uppercase;
    letter-spacing: .7px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 16px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ─── Başlık ─── --}}
    <div class="gr-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4>
                {{-- Küçük Google G simgesi --}}
                <svg viewBox="0 0 18 18" width="22" height="22" style="vertical-align:-3px;margin-right:8px" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/>
                    <path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"/>
                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.163 6.656 3.58 9 3.58z"/>
                </svg>
                Google Puanları
            </h4>
            <p>Günlük puan &amp; yorum takibi — Balmy Hotel Grubu</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('google_report', 'create'))
            <form method="POST" action="{{ route('reports.google.snapshot') }}">
                @csrf
                <button type="submit"
                        class="btn btn-sm fw-semibold"
                        style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.25)">
                    <i class="fas fa-sync-alt me-1"></i> Şimdi Güncelle
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ─── Canlı Puan Kartları ─── --}}
    <div class="row g-4 mb-4">
        @forelse($liveStats as $hotel)
        @php
            $rFull  = floor($hotel['rating'] ?? 0);
            $rHalf  = (($hotel['rating'] ?? 0) - $rFull) >= 0.3 ? 1 : 0;
            $rEmpty = 5 - $rFull - $rHalf;
            $sum    = $summaries[$hotel['place_id']] ?? null;
        @endphp
        <div class="col-12 col-lg-6">
            <div class="card gr-card h-100">
                <div class="gr-card-accent"></div>
                <div class="card-body p-4">

                    {{-- Otel başlık --}}
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-4">
                        <div>
                            <div class="small fw-semibold mb-1"
                                 style="text-transform:uppercase;letter-spacing:.5px;color:#80868b">
                                Google Maps Değerlendirmesi
                            </div>
                            <h5 class="fw-bold mb-0" style="color:#1e2d3d">{{ $hotel['name'] }}</h5>
                        </div>
                        <a href="{{ $hotel['maps_url'] }}" target="_blank" class="gr-maps-btn">
                            <i class="fas fa-map-marker-alt"></i>Haritada Gör
                        </a>
                    </div>

                    {{-- Puan satırı --}}
                    <div class="d-flex align-items-center gap-4 flex-wrap mb-4">
                        <div>
                            <div class="gr-big-score">{{ number_format($hotel['rating'] ?? 0, 1) }}</div>
                            <div class="text-muted small mt-1">5.0 üzerinden</div>
                        </div>
                        <div>
                            <div class="gr-stars mb-2">
                                @for($i=0;$i<$rFull;$i++)
                                    <i class="fas fa-star" style="color:#FBBC05"></i>
                                @endfor
                                @if($rHalf)
                                    <i class="fas fa-star-half-alt" style="color:#FBBC05"></i>
                                @endif
                                @for($i=0;$i<$rEmpty;$i++)
                                    <i class="far fa-star" style="color:#dee2e6"></i>
                                @endfor
                            </div>
                            <div class="fw-semibold" style="font-size:16px;color:#2c3e50">
                                {{ number_format($hotel['user_ratings_total'] ?? 0) }}
                                <span class="fw-normal text-muted" style="font-size:14px">yorum</span>
                            </div>
                        </div>
                    </div>

                    {{-- Alt istatistikler --}}
                    @if($sum)
                    <div class="d-flex flex-wrap gap-2 pt-3" style="border-top:1px solid #f0f0f0">
                        @if($sum['snapshot_count'] > 1)
                        <span class="gr-stat-badge">
                            <i class="fas fa-plus-circle" style="color:#34A853"></i>
                            +{{ $sum['total_new'] }} yeni yorum (takip başından beri)
                        </span>
                        @endif
                        <span class="gr-stat-badge">
                            <i class="fas fa-calendar-check"></i>
                            {{ $sum['snapshot_count'] }} günlük kayıt
                        </span>
                        @if($sum['latest'])
                        <span class="gr-stat-badge">
                            <i class="fas fa-clock"></i>
                            Son güncelleme: {{ $sum['latest']->snapshot_date->format('d M Y') }}
                        </span>
                        @endif
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning rounded-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Google Places API'den veri alınamadı. "Şimdi Güncelle" butonuna tıklayın.
            </div>
        </div>
        @endforelse
    </div>

    {{-- ─── Grafikler ─── --}}
    @php $idx = 0; @endphp
    @foreach($chartData as $placeId => $data)
    @if(count($data['labels']) > 1)
    <div class="mb-4">
        <div class="gr-section-title">
            <i class="fas fa-chart-area me-2"></i>{{ $data['name'] }} — Geçmiş Trend
        </div>
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card gr-chart-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold" style="font-size:14px;color:#2c3e50">
                            <i class="fas fa-star me-2" style="color:#FBBC05"></i>Günlük Ortalama Puan
                        </span>
                        <span class="badge" style="background:#f0f4f8;color:#5f6368;font-weight:600;font-size:11px">Son 60 Gün</span>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="ratingChart_{{ $idx }}" style="max-height:260px"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card gr-chart-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold" style="font-size:14px;color:#2c3e50">
                            <i class="fas fa-comment-dots me-2" style="color:#80868b"></i>Günlük Yeni Yorum Sayısı
                        </span>
                        <span class="badge" style="background:#f0f4f8;color:#5f6368;font-weight:600;font-size:11px">Son 60 Gün</span>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="reviewsChart_{{ $idx }}" style="max-height:260px"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php $idx++; @endphp
    @endif
    @endforeach

    @if(collect($chartData)->every(fn($d) => count($d['labels']) <= 1))
    <div class="card border mb-4" style="border-color:#e9ecef!important;border-radius:12px">
        <div class="card-body text-center py-5">
            <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;
                        display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-chart-bar" style="font-size:22px;color:#80868b"></i>
            </div>
            <h6 class="fw-semibold mb-1" style="color:#1e2d3d">Henüz yeterli grafik verisi yok</h6>
            <p class="small text-muted mb-0">
                Veriler her gün otomatik olarak kaydedilmektedir.<br>
                Birkaç günlük kayıt oluştuğunda grafikler burada görünecek.
            </p>
        </div>
    </div>
    @endif

    <div class="text-end mb-4">
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>Her sabah 06:10'da otomatik güncellenir &nbsp;·&nbsp;
            <i class="fas fa-database me-1"></i>Kaynak: Google Places API
        </small>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";

@php $idx = 0; @endphp
@foreach($chartData as $placeId => $data)
@if(count($data['labels']) > 1)
(function() {
    const labels   = @json($data['labels']);
    const ratings  = @json($data['ratings']);
    const dailyNew = @json($data['dailyNew']);

    new Chart(document.getElementById('ratingChart_{{ $idx }}'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ortalama Puan',
                data: ratings,
                borderColor: '#2c3e50',
                backgroundColor: 'rgba(44,62,80,.06)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#2c3e50',
                fill: true,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    min: 3.5, max: 5.0,
                    ticks: { stepSize: 0.5, color: '#6c757d', font: { size: 11 } },
                    grid: { color: '#f4f4f4' }
                },
                x: {
                    ticks: { color: '#6c757d', font: { size: 11 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('reviewsChart_{{ $idx }}'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Yeni Yorum',
                data: dailyNew,
                backgroundColor: 'rgba(95,99,104,.6)',
                hoverBackgroundColor: 'rgba(44,62,80,.85)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#6c757d', font: { size: 11 } },
                    grid: { color: '#f4f4f4' }
                },
                x: {
                    ticks: { color: '#6c757d', font: { size: 11 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });
})();
@php $idx++; @endphp
@endif
@endforeach
</script>
@endpush
