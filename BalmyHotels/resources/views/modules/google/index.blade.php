@extends('layouts.default')

@push('styles')
<style>
/* ── Sayfa başlığı ── */
.google-page-header {
    background: linear-gradient(135deg, #4285F4 0%, #1a73e8 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 28px;
}
.google-page-header h4 { font-weight: 800; margin: 0 0 4px; font-size: 1.5rem; }
.google-page-header p  { opacity: .85; margin: 0; font-size: 14px; }

/* ── Canlı kart ── */
.google-live-card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 20px rgba(66,133,244,.12);
    border-left: 5px solid #4285F4;
    transition: transform .15s ease, box-shadow .15s ease;
}
.google-live-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(66,133,244,.22);
}

/* ── Puan renkleri (Google 5 yıldız) ── */
.google-big-score { font-size: 64px; font-weight: 900; line-height: 1; color: #4285F4; }

/* ── Google yıldızlar ── */
.g-stars { display: flex; gap: 4px; font-size: 22px; }

/* ── Grafik kart ── */
.chart-card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.chart-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 14px 14px 0 0;
    padding: 14px 20px;
}

/* ── Google G ikonu (renk çemberi) ── */
.google-g-badge {
    width: 44px; height: 44px; border-radius: 50%;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 22px;
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg,#EA4335 0%,#FBBC05 33%,#34A853 66%,#4285F4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.google-summary-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 13px; font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ─── Başlık ─── --}}
    <div class="google-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4>
                <i class="fab fa-google me-2"></i>Google Puanları
            </h4>
            <p>Günlük puan & yorum takibi — Balmy Hotel Grubu</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            {{-- Google Logo --}}
            <svg viewBox="0 0 272 92" style="height:32px" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M115.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18C71.25 34.32 81.24 25 93.5 25s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44S80.99 39.2 80.99 47.18c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
                <path fill="#FBBC05" d="M163.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18c0-12.85 9.99-22.18 22.25-22.18s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44s-12.51 5.46-12.51 13.44c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
                <path fill="#4285F4" d="M209.75 26.34v39.82c0 16.38-9.66 23.07-21.08 23.07-10.75 0-17.22-7.19-19.66-13.07l8.48-3.53c1.51 3.61 5.21 7.87 11.17 7.87 7.31 0 11.84-4.51 11.84-13v-3.19h-.34c-2.18 2.69-6.38 5.04-11.68 5.04-11.09 0-21.25-9.66-21.25-22.09 0-12.52 10.16-22.26 21.25-22.26 5.29 0 9.49 2.35 11.68 4.96h.34v-3.61h9.25zm-8.56 20.92c0-7.81-5.21-13.52-11.84-13.52-6.72 0-12.35 5.71-12.35 13.52 0 7.73 5.63 13.36 12.35 13.36 6.63 0 11.84-5.63 11.84-13.36z"/>
                <path fill="#34A853" d="M225 3v65h-9.5V3h9.5z"/>
                <path fill="#EA4335" d="M262.02 54.48l7.56 5.04c-2.44 3.61-8.32 9.83-18.48 9.83-12.6 0-22.01-9.74-22.01-22.18 0-13.19 9.49-22.18 20.92-22.18 11.51 0 17.14 9.16 18.98 14.11l1.01 2.52-29.65 12.28c2.27 4.45 5.8 6.72 10.75 6.72 4.96 0 8.4-2.44 10.92-6.14zm-23.27-7.98l19.82-8.23c-1.09-2.77-4.37-4.7-8.23-4.7-4.95 0-11.84 4.37-11.59 12.93z"/>
                <path fill="#4285F4" d="M35.29 41.41V32h31.77c.31 1.64.47 3.58.47 5.68 0 7.06-1.93 15.79-8.15 22.01-6.05 6.3-13.78 9.66-24.03 9.66C16.32 69.35.36 53.89.36 34.79.36 15.69 16.32.23 35.34.23c10.5 0 17.98 4.12 23.6 9.49l-6.64 6.64c-4.03-3.78-9.49-6.72-16.97-6.72-13.86 0-24.7 11.17-24.7 25.03 0 13.86 10.84 25.03 24.7 25.03 8.99 0 14.11-3.61 17.39-6.89 2.66-2.66 4.41-6.46 5.1-11.65l-22.53.25z"/>
            </svg>
            {{-- Manuel güncelle butonu --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('google_report', 'create'))
            <form method="POST" action="{{ route('reports.google.snapshot') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-light fw-semibold">
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
        @endphp
        <div class="col-12 col-lg-6">
            <div class="card google-live-card h-100">
                <div class="card-body p-4">

                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1" style="color:#1a1a1a">
                                <i class="fab fa-google me-2" style="color:#4285F4"></i>{{ $hotel['name'] }}
                            </h5>
                        </div>
                        <a href="{{ $hotel['maps_url'] }}" target="_blank"
                           class="btn btn-sm fw-semibold"
                           style="background:#4285F4;color:#fff;white-space:nowrap">
                            <i class="fas fa-map-marker-alt me-1"></i>Haritada Gör
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-5 flex-wrap">

                        {{-- Büyük puan --}}
                        <div class="text-center">
                            <div class="google-big-score">{{ number_format($hotel['rating'] ?? 0, 1) }}</div>
                            <div class="text-muted small mt-1">/ 5.0 Puan</div>
                        </div>

                        {{-- Yıldızlar + Yorum --}}
                        <div>
                            <div class="g-stars mb-2">
                                @for($i=0;$i<$rFull;$i++)
                                    <i class="fas fa-star" style="color:#FBBC05"></i>
                                @endfor
                                @if($rHalf)
                                    <i class="fas fa-star-half-alt" style="color:#FBBC05"></i>
                                @endif
                                @for($i=0;$i<$rEmpty;$i++)
                                    <i class="far fa-star" style="color:#ccc"></i>
                                @endfor
                            </div>
                            <div class="fw-bold" style="font-size:18px;color:#333">
                                <i class="fas fa-comment-dots me-1" style="color:#4285F4"></i>
                                {{ number_format($hotel['user_ratings_total'] ?? 0) }} yorum
                            </div>
                        </div>

                        {{-- DB Özeti --}}
                        @php
                            $sum = $summaries[$hotel['place_id']] ?? null;
                        @endphp
                        @if($sum && $sum['snapshot_count'] > 1)
                        <div class="ms-auto">
                            <div class="small text-muted mb-1">Son kayıttan bu yana</div>
                            <span class="google-summary-badge" style="background:#e8f0fe;color:#4285F4">
                                <i class="fas fa-plus-circle"></i>+{{ $sum['total_new'] }} yeni yorum
                            </span>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-database me-1"></i>{{ $sum['snapshot_count'] }} günlük kayıt
                            </div>
                        </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Google Places API'den veri alınamadı. Lütfen "Şimdi Güncelle" butonuna tıklayın.
            </div>
        </div>
        @endforelse
    </div>

    {{-- ─── Grafikler ─── --}}
    @foreach($chartData as $placeId => $data)
    @if(count($data['labels']) > 1)
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h6 class="fw-bold text-muted mb-3">
                <i class="fas fa-chart-line me-2" style="color:#4285F4"></i>{{ $data['name'] }} — Geçmiş Trend
            </h6>
        </div>

        {{-- Puan Grafiği --}}
        <div class="col-12 col-xl-6">
            <div class="card chart-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="fw-bold">Günlük Ortalama Puan</span>
                    <span class="badge ms-auto" style="background:#e8f0fe;color:#4285F4">Son 60 Gün</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="ratingChart_{{ $loop->index }}" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- Günlük Yeni Yorum --}}
        <div class="col-12 col-xl-6">
            <div class="card chart-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="fw-bold">Günlük Yeni Yorum Sayısı</span>
                    <span class="badge ms-auto" style="background:#e8f0fe;color:#4285F4">Son 60 Gün</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="reviewsChart_{{ $loop->index }}" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    @if(collect($chartData)->every(fn($d) => count($d['labels']) <= 1))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="fab fa-google fa-3x mb-3 d-block" style="color:#e8f0fe"></i>
            <h6 class="fw-semibold">Henüz yeterli grafik verisi yok</h6>
            <p class="small mb-0">Her gün otomatik kaydedilir. İlk veriler oluşmaya başladıkça grafikler burada görünecek.</p>
        </div>
    </div>
    @endif

    <div class="text-end mb-4">
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>Her sabah 06:10'da otomatik güncellenir ·
            <i class="fas fa-database me-1 ms-2"></i>Kaynak: Google Places API
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

    /* ── Puan Grafiği ── */
    new Chart(document.getElementById('ratingChart_{{ $idx }}'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ortalama Puan',
                data: ratings,
                borderColor: '#4285F4',
                backgroundColor: 'rgba(66,133,244,.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#4285F4',
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
                    grid: { color: '#f0f0f0' }
                },
                x: {
                    ticks: { color: '#6c757d', font: { size: 11 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });

    /* ── Günlük Yeni Yorum ── */
    new Chart(document.getElementById('reviewsChart_{{ $idx }}'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Yeni Yorum',
                data: dailyNew,
                backgroundColor: 'rgba(66,133,244,.75)',
                borderRadius: 5,
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
                    grid: { color: '#f0f0f0' }
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
