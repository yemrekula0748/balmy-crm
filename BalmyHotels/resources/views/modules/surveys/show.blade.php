@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-chart-bar me-2 text-primary"></i>Anket Sonuçları</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">Anketler</a></li>
                <li class="breadcrumb-item active">Sonuçlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3 align-items-start">

        {{-- SOL KOLON: Anket Bilgisi + QR --}}
        <div class="col-xl-4" style="position:sticky;top:1rem">

            {{-- Anket bilgisi --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $survey->getTitle('tr') ?: $survey->getTitle() }}</h5>
                            @if($survey->branch)
                                <small class="text-muted"><i class="fas fa-building me-1"></i>{{ $survey->branch->name }}</small>
                            @endif
                        </div>
                        @if($survey->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Pasif</span>
                        @endif
                    </div>

                    {{-- Diller --}}
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach($survey->languages as $lang)
                            @php $info = \App\Models\Survey::AVAILABLE_LANGUAGES[$lang] ?? ['name'=>strtoupper($lang),'flag'=>'🌐']; @endphp
                            <span class="badge bg-light text-dark border">{{ $info['flag'] }} {{ $info['name'] }}</span>
                        @endforeach
                    </div>

                    {{-- İşlem butonları --}}
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Düzenle
                        </a>
                        <form action="{{ route('surveys.toggle', $survey) }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-sm {{ $survey->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                <i class="fas fa-{{ $survey->is_active ? 'pause' : 'play' }} me-1"></i>
                                {{ $survey->is_active ? 'Durdur' : 'Başlat' }}
                            </button>
                        </form>
                        <a href="{{ $survey->publicUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt me-1"></i>Önizle
                        </a>
                    </div>
                </div>
            </div>

            {{-- QR & Link --}}
            <div class="card mb-3">
                <div class="card-header py-3"><h6 class="mb-0 fw-bold"><i class="fas fa-qrcode me-2 text-primary"></i>Paylaşım</h6></div>
                <div class="card-body">
                    {{-- Link --}}
                    <label class="form-label small fw-semibold text-muted">Anket Linki</label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" id="surveyLink" value="{{ $survey->publicUrl() }}" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyLink()"><i class="fas fa-copy"></i></button>
                    </div>

                    {{-- Per-language links --}}
                    @if(count($survey->languages) > 1)
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Dil Bazlı Linkler</label>
                        @foreach($survey->languages as $lang)
                            @php $info = \App\Models\Survey::AVAILABLE_LANGUAGES[$lang] ?? ['name'=>strtoupper($lang),'flag'=>'🌐']; @endphp
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text">{{ $info['flag'] }}</span>
                                <input type="text" class="form-control" value="{{ $survey->publicUrl($lang) }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $survey->publicUrl($lang) }}')"><i class="fas fa-copy"></i></button>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- QR Kod --}}
                    <label class="form-label small fw-semibold text-muted">QR Kod</label>
                    <div id="qrCodeDiv" class="mx-auto mb-3 text-center" style="width:180px;height:180px"></div>
                    <a id="qrDownload" class="btn btn-sm btn-outline-primary w-100" download="anket-qr.png">
                        <i class="fas fa-download me-1"></i>QR İndir
                    </a>
                </div>
            </div>

            {{-- Dil Dağılımı --}}
            @if(!empty($langBreakdown) && $totalResponses > 0)
            <div class="card mb-3">
                <div class="card-header py-3"><h6 class="mb-0 fw-bold"><i class="fas fa-language me-2 text-primary"></i>Dil Dağılımı</h6></div>
                <div class="card-body p-0">
                    @foreach($langBreakdown as $lang => $cnt)
                        @php
                            $info = \App\Models\Survey::AVAILABLE_LANGUAGES[$lang] ?? ['name'=>strtoupper($lang),'flag'=>'🌐'];
                            $pct  = $totalResponses > 0 ? round(($cnt/$totalResponses)*100) : 0;
                        @endphp
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ $info['flag'] }} {{ $info['name'] }}</span>
                                <span class="small fw-bold">{{ $cnt }} (%{{ $pct }})</span>
                            </div>
                            <div class="progress" style="height:5px">
                                <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- SAĞ KOLON: İstatistikler --}}
        <div class="col-xl-8">

            {{-- Özet istatistik kartları --}}
            @php
                $qCount  = $survey->questions->count();
                $langCnt = count($survey->languages);
                $last7   = array_sum(array_slice($dailyCounts ?? [], -7));
            @endphp
            <div class="row g-3 mb-3">
                {{-- Toplam Yanıt --}}
                <div class="col-6 col-lg-3">
                    <div class="card border-0 mb-0 h-100 overflow-hidden" style="border-radius:16px;background:linear-gradient(135deg,#4361ee 0%,#3a0ca3 100%);color:#fff">
                        <div class="card-body p-3 position-relative">
                            <div style="position:absolute;right:-14px;top:-14px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.08)"></div>
                            <div class="mb-2" style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center">
                                <i class="fas fa-paper-plane" style="font-size:15px"></i>
                            </div>
                            <div class="fw-bold" style="font-size:1.9rem;line-height:1">{{ $totalResponses }}</div>
                            <div class="small mt-1" style="opacity:.75">Toplam Yanıt</div>
                        </div>
                    </div>
                </div>
                {{-- Son 7 Gün --}}
                <div class="col-6 col-lg-3">
                    <div class="card border-0 mb-0 h-100 overflow-hidden" style="border-radius:16px;background:linear-gradient(135deg,#f9a825 0%,#f57f17 100%);color:#fff">
                        <div class="card-body p-3 position-relative">
                            <div style="position:absolute;right:-14px;top:-14px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.08)"></div>
                            <div class="mb-2" style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center">
                                <i class="fas fa-calendar-week" style="font-size:15px"></i>
                            </div>
                            <div class="fw-bold" style="font-size:1.9rem;line-height:1">{{ $last7 }}</div>
                            <div class="small mt-1" style="opacity:.75">Son 7 Gün</div>
                        </div>
                    </div>
                </div>
                {{-- Soru Sayısı --}}
                <div class="col-6 col-lg-3">
                    <div class="card border-0 mb-0 h-100 overflow-hidden" style="border-radius:16px;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff">
                        <div class="card-body p-3 position-relative">
                            <div style="position:absolute;right:-14px;top:-14px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.08)"></div>
                            <div class="mb-2" style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center">
                                <i class="fas fa-list-ul" style="font-size:15px"></i>
                            </div>
                            <div class="fw-bold" style="font-size:1.9rem;line-height:1">{{ $qCount }}</div>
                            <div class="small mt-1" style="opacity:.75">Soru</div>
                        </div>
                    </div>
                </div>
                {{-- Dil Sayısı --}}
                <div class="col-6 col-lg-3">
                    <div class="card border-0 mb-0 h-100 overflow-hidden" style="border-radius:16px;background:linear-gradient(135deg,#7c3aed 0%,#4c1d95 100%);color:#fff">
                        <div class="card-body p-3 position-relative">
                            <div style="position:absolute;right:-14px;top:-14px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.08)"></div>
                            <div class="mb-2" style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center">
                                <i class="fas fa-language" style="font-size:15px"></i>
                            </div>
                            <div class="fw-bold" style="font-size:1.9rem;line-height:1">{{ $langCnt }}</div>
                            <div class="small mt-1" style="opacity:.75">{{ $langCnt > 1 ? 'Desteklenen Dil' : 'Desteklenen Dil' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Günlük Trend --}}
            @if($totalResponses > 0)
            <div class="card mb-3">
                <div class="card-header py-3"><h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Son 14 Günde Yanıtlar</h6></div>
                <div class="card-body">
                    <canvas id="trendChart" height="80"></canvas>
                </div>
            </div>
            @endif

            {{-- Per-soru sonuçlar --}}
            @foreach($survey->questions as $i => $q)
            @php $stat = $stats[$q->id] ?? null; @endphp
            <div class="card mb-3">
                <div class="card-header py-2 d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill">{{ $i+1 }}</span>
                    <span class="fw-semibold">{{ $q->getText('tr') ?: $q->getText('en') ?: '(Çevirisiz Soru)' }}</span>
                    <span class="badge bg-light text-dark border ms-auto small">{{ \App\Models\SurveyQuestion::TYPES[$q->type]['label'] ?? $q->type }}</span>
                </div>
                <div class="card-body">
                    @if(!$stat || empty($stat['data'] ?? $stat['dist'] ?? null) && empty($stat['avg'] ?? null))
                        <p class="text-muted small mb-0">Henüz bu soruya yanıt yok.</p>
                    @elseif($stat['type'] === 'radio' || $stat['type'] === 'checkbox')
                        @php $total = array_sum($stat['data']); @endphp
                        @foreach($stat['data'] as $answer => $cnt)
                            @php $pct = $total > 0 ? round(($cnt/$total)*100) : 0; @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">{{ $answer }}</span>
                                    <span class="small fw-bold">{{ $cnt }} (%{{ $pct }})</span>
                                </div>
                                <div class="progress" style="height:8px;border-radius:4px">
                                    <div class="progress-bar" style="width:{{ $pct }}%;background:#4361ee;border-radius:4px"></div>
                                </div>
            </div>
                        @endforeach
                    @elseif($stat['type'] === 'rating')
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="text-center">
                                <div class="fw-bold" style="font-size:2.5rem;color:#f9a825;line-height:1">{{ number_format($stat['avg'],1) }}</div>
                                <small class="text-muted">/ 5 Ortalama</small>
                            </div>
                            <div class="flex-grow-1">
                                @for($s=5;$s>=1;$s--)
                                    @php $cnt = $stat['dist'][$s] ?? 0; $pct = $stat['count']>0 ? round(($cnt/$stat['count'])*100) : 0; @endphp
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="small" style="width:16px">{{ $s }}</span>
                                        <i class="fas fa-star text-warning" style="font-size:10px"></i>
                                        <div class="progress flex-grow-1" style="height:8px;border-radius:4px">
                                            <div class="progress-bar bg-warning" style="width:{{ $pct }}%;border-radius:4px"></div>
                                        </div>
                                        <span class="small text-muted" style="width:32px">{{ $cnt }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @elseif($stat['type'] === 'nps')
                        <div class="row g-3 mb-3">
                            <div class="col-4 text-center">
                                <div class="fw-bold" style="font-size:2.5rem;color:{{ $stat['nps'] >= 0 ? '#10b981' : '#ef4444' }};line-height:1">{{ $stat['nps'] ?? '—' }}</div>
                                <small class="text-muted">NPS Skoru</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fw-bold" style="font-size:2rem;line-height:1">{{ number_format($stat['avg'],1) }}</div>
                                <small class="text-muted">Ortalama</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fw-bold" style="font-size:2rem;line-height:1">{{ $stat['count'] }}</div>
                                <small class="text-muted">Yanıt</small>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            @for($n=0;$n<=10;$n++)
                                @php
                                    $cnt = $stat['dist'][$n] ?? 0;
                                    $color = $n >= 9 ? '#10b981' : ($n >= 7 ? '#f9a825' : '#ef4444');
                                @endphp
                                <div class="text-center flex-grow-1">
                                    <div class="rounded mb-1" style="height:{{ max(4,$cnt*6) }}px;background:{{ $color }};min-height:4px"></div>
                                    <small style="font-size:10px">{{ $n }}</small>
                                </div>
                            @endfor
                        </div>
                    @else
                        {{-- text/textarea --}}
                        <div class="list-group list-group-flush">
                            @foreach($stat['data'] as $answer)
                            <div class="list-group-item px-0 py-1 border-0 border-bottom">
                                <small>{{ $answer }}</small>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// QR Kod
const surveyUrl = '{{ $survey->publicUrl() }}';
const qr = new QRCode(document.getElementById('qrCodeDiv'), {
    text: surveyUrl,
    width: 180,
    height: 180,
    colorDark: '#1a1a2e',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});
setTimeout(() => {
    const img = document.querySelector('#qrCodeDiv img');
    if (img) document.getElementById('qrDownload').href = img.src;
}, 500);

function copyLink() {
    navigator.clipboard.writeText(document.getElementById('surveyLink').value);
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check text-success"></i>';
    setTimeout(() => btn.innerHTML = orig, 1500);
}

// Trend Chart
@if($totalResponses > 0)
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: @json($dailyLabels),
        datasets: [{
            label: 'Yanıt Sayısı',
            data: @json($dailyCounts),
            backgroundColor: 'rgba(67,97,238,0.15)',
            borderColor: '#4361ee',
            borderWidth: 2,
            borderRadius: 6,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
@endif
</script>
@endpush
