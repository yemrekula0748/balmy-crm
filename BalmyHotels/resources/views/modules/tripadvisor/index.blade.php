@extends('layouts.default')

@push('styles')
<style>
/* ── Genel ── */
.ta-page-header {
    background: linear-gradient(135deg, #00aa6c 0%, #007a4d 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 28px;
}
.ta-page-header h4 { font-weight: 800; margin: 0 0 4px; font-size: 1.5rem; }
.ta-page-header p  { opacity: .85; margin: 0; font-size: 14px; }

/* ── Canlı Kart ── */
.ta-live-card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 20px rgba(0,170,108,.12);
    border-left: 5px solid #00aa6c;
    transition: transform .15s ease, box-shadow .15s ease;
}
.ta-live-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(0,170,108,.2);
}

.ta-big-score {
    font-size: 64px;
    font-weight: 900;
    line-height: 1;
    color: #00aa6c;
}
.ta-bubbles { display: flex; gap: 6px; flex-wrap: wrap; }
.ta-bubble-full  { width: 18px; height: 18px; border-radius: 50%; background: #00aa6c; }
.ta-bubble-half  { width: 18px; height: 18px; border-radius: 50%; background: linear-gradient(90deg,#00aa6c 50%,#d4f0e3 50%); }
.ta-bubble-empty { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #00aa6c; background: #fff; }

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

/* ── Özet ── */
.ta-summary-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ─── Başlık ─── --}}
    <div class="ta-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4>
                <i class="fas fa-star me-2"></i>TripAdvisor Puanları
            </h4>
            <p>Günlük puan & yorum takibi — Balmy Hotel Grubu</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            {{-- TripAdvisor Logo --}}
            <img src="https://static.tacdn.com/img2/brand_refresh/Tripadvisor_lockup_horizontal_secondary_registered.svg"
                 alt="TripAdvisor" style="height:28px;filter:brightness(0) invert(1)">

            {{-- Manuel güncelle butonu --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('tripadvisor_report', 'create'))
            <form method="POST" action="{{ route('reports.tripadvisor.snapshot') }}">
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
            $full  = floor($hotel['rating'] ?? 0);
            $half  = (($hotel['rating'] ?? 0) - $full) >= 0.5 ? 1 : 0;
            $empty = 5 - $full - $half;
        @endphp
        <div class="col-12 col-lg-6">
            <div class="card ta-live-card h-100">
                <div class="card-body p-4">

                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1" style="color:#1a1a1a">
                                <i class="fas fa-hotel me-2" style="color:#00aa6c"></i>{{ $hotel['name'] }}
                            </h5>
                            @if($hotel['ranking'])
                            <span class="ta-summary-badge" style="background:#fff8e1;color:#e67e22">
                                <i class="fas fa-trophy"></i>{{ $hotel['ranking'] }}
                            </span>
                            @endif
                        </div>
                        <a href="{{ $hotel['url'] }}" target="_blank"
                           class="btn btn-sm fw-semibold"
                           style="background:#00aa6c;color:#fff;white-space:nowrap">
                            <i class="fas fa-external-link-alt me-1"></i>TripAdvisor'da Gör
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-5 flex-wrap">

                        {{-- Büyük puan --}}
                        <div class="text-center">
                            <div class="ta-big-score">{{ number_format($hotel['rating'] ?? 0, 1) }}</div>
                            <div class="text-muted small mt-1">/ 5.0 Puan</div>
                        </div>

                        {{-- Yorum + Balonlar --}}
                        <div>
                            <div class="ta-bubbles mb-2">
                                @for($i=0;$i<$full;$i++)<div class="ta-bubble-full"></div>@endfor
                                @if($half)<div class="ta-bubble-half"></div>@endif
                                @for($i=0;$i<$empty;$i++)<div class="ta-bubble-empty"></div>@endfor
                            </div>
                            <div class="fw-bold" style="font-size:18px;color:#333">
                                <i class="fas fa-comment-dots me-1" style="color:#00aa6c"></i>
                                {{ number_format($hotel['num_reviews'] ?? 0) }} yorum
                            </div>
                            @if($hotel['rating_image_url'])
                            <img src="{{ $hotel['rating_image_url'] }}" alt="TA Badge"
                                 style="height:22px;margin-top:8px">
                            @endif
                        </div>

                        {{-- DB Özeti --}}
                        @php
                            $locId = $hotel['location_id'];
                            $sum   = $summaries[$locId] ?? null;
                        @endphp
                        @if($sum && $sum['snapshot_count'] > 1)
                        <div class="ms-auto">
                            <div class="small text-muted mb-1">Son kayıttan bu yana</div>
                            <span class="ta-summary-badge" style="background:#e6f9f2;color:#00aa6c">
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
                TripAdvisor API'den veri alınamadı. Lütfen "Şimdi Güncelle" butonuna tıklayın.
            </div>
        </div>
        @endforelse
    </div>

    {{-- ─── Grafikler ─── --}}
    @foreach($chartData as $locationId => $data)
    @if(count($data['labels']) > 1)
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h6 class="fw-bold text-muted mb-3">
                <i class="fas fa-chart-line me-2" style="color:#00aa6c"></i>{{ $data['name'] }} — Geçmiş Trend
            </h6>
        </div>

        {{-- Puan Grafiği --}}
        <div class="col-12 col-xl-6">
            <div class="card chart-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="fw-bold">Günlük Ortalama Puan</span>
                    <span class="badge ms-auto" style="background:#e6f9f2;color:#00aa6c">Son 60 Gün</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="ratingChart_{{ $locationId }}" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- Günlük Yeni Yorum Grafiği --}}
        <div class="col-12 col-xl-6">
            <div class="card chart-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="fw-bold">Günlük Yeni Yorum Sayısı</span>
                    <span class="badge ms-auto" style="background:#e6f9f2;color:#00aa6c">Son 60 Gün</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="reviewsChart_{{ $locationId }}" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    @if(collect($chartData)->every(fn($d) => count($d['labels']) <= 1))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-chart-bar fa-3x mb-3 d-block" style="color:#d4f0e3"></i>
            <h6 class="fw-semibold">Henüz yeterli grafik verisi yok</h6>
            <p class="small mb-0">Her gün otomatik kaydedilir. İlk veriler oluşmaya başladıkça grafikler burada görünecek.</p>
            <p class="small text-muted">Şimdi "Şimdi Güncelle" butonuna basarak ilk kaydı alabilirsiniz.</p>
        </div>
    </div>
    @endif

    <div class="text-end mb-4">
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>Her sabah 06:00'da otomatik güncellenir ·
            <i class="fas fa-database me-1 ms-2"></i>Kaynak: TripAdvisor Content API
        </small>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";

@foreach($chartData as $locationId => $data)
@if(count($data['labels']) > 1)
(function() {
    const labels   = @json($data['labels']);
    const ratings  = @json($data['ratings']);
    const dailyNew = @json($data['dailyNew']);

    /* ── Puan Grafiği ── */
    new Chart(document.getElementById('ratingChart_{{ $locationId }}'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ortalama Puan',
                data: ratings,
                borderColor: '#00aa6c',
                backgroundColor: 'rgba(0,170,108,.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#00aa6c',
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

    /* ── Günlük Yeni Yorum Grafiği ── */
    new Chart(document.getElementById('reviewsChart_{{ $locationId }}'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Yeni Yorum',
                data: dailyNew,
                backgroundColor: 'rgba(0,170,108,.75)',
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
@endif
@endforeach
</script>
@endpush
