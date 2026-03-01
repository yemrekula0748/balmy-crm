@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-chart-bar me-2 text-primary"></i>{{ $page_title }}</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff-surveys.index') }}">Personel Anketleri</a></li>
                <li class="breadcrumb-item active">Sonuçlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3 align-items-start">

        {{-- SOL KOLON: Genel bilgi + QR + kart --}}
        <div class="col-xl-4" style="position:sticky;top:1rem">

            {{-- Stat kartlar --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="card text-white h-100" style="background:linear-gradient(135deg,#4361ee,#3a0ca3);position:relative;overflow:hidden">
                        <div class="card-body py-3 px-3">
                            <div style="position:absolute;right:-10px;bottom:-10px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.1)"></div>
                            <div class="d-flex align-items-center justify-content-center mb-1" style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;backdrop-filter:blur(4px)">
                                <i class="fas fa-users" style="font-size:14px"></i>
                            </div>
                            <div style="font-size:26px;font-weight:800;line-height:1">{{ $totalResponses }}</div>
                            <div style="font-size:11px;opacity:.85;margin-top:2px">Toplam Yanıt</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-white h-100" style="background:linear-gradient(135deg,#f9a825,#f57f17);position:relative;overflow:hidden">
                        <div class="card-body py-3 px-3">
                            <div style="position:absolute;right:-10px;bottom:-10px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.1)"></div>
                            <div class="d-flex align-items-center justify-content-center mb-1" style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;backdrop-filter:blur(4px)">
                                <i class="fas fa-calendar-week" style="font-size:14px"></i>
                            </div>
                            <div style="font-size:26px;font-weight:800;line-height:1">{{ array_sum(array_slice($dailyCounts, -7)) }}</div>
                            <div style="font-size:11px;opacity:.85;margin-top:2px">Son 7 Gün</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-white h-100" style="background:linear-gradient(135deg,#10b981,#059669);position:relative;overflow:hidden">
                        <div class="card-body py-3 px-3">
                            <div style="position:absolute;right:-10px;bottom:-10px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.1)"></div>
                            <div class="d-flex align-items-center justify-content-center mb-1" style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;backdrop-filter:blur(4px)">
                                <i class="fas fa-question-circle" style="font-size:14px"></i>
                            </div>
                            <div style="font-size:26px;font-weight:800;line-height:1">{{ $staffSurvey->questions->count() }}</div>
                            <div style="font-size:11px;opacity:.85;margin-top:2px">Soru Sayısı</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-white h-100" style="background:linear-gradient(135deg,#7c3aed,#4c1d95);position:relative;overflow:hidden">
                        <div class="card-body py-3 px-3">
                            <div style="position:absolute;right:-10px;bottom:-10px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.1)"></div>
                            <div class="d-flex align-items-center justify-content-center mb-1" style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;backdrop-filter:blur(4px)">
                                <i class="fas fa-building" style="font-size:14px"></i>
                            </div>
                            <div style="font-size:26px;font-weight:800;line-height:1">{{ count($deptBreakdown) }}</div>
                            <div style="font-size:11px;opacity:.85;margin-top:2px">Departman</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Anket Bilgisi --}}
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Anket Bilgisi</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">{{ $staffSurvey->getDescription() ?: 'Açıklama yok.' }}</p>
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1"><i class="fas fa-circle @if($staffSurvey->is_active) text-success @else text-secondary @endif me-1"></i>
                            {{ $staffSurvey->is_active ? 'Aktif' : 'Pasif' }}
                        </li>
                        @if($staffSurvey->is_anonymous)<li class="mb-1"><i class="fas fa-user-secret text-muted me-1"></i> Anonim yanıt</li>@endif
                        @if($staffSurvey->show_dept_field)<li class="mb-1"><i class="fas fa-building text-muted me-1"></i> Departman alanı açık</li>@endif
                        @if($staffSurvey->allow_multiple)<li class="mb-1"><i class="fas fa-redo text-muted me-1"></i> Birden fazla doldurulamaz</li>@endif
                    </ul>

                    {{-- Link --}}
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control" readonly value="{{ $staffSurvey->publicUrl() }}" id="surveyUrl">
                        <button class="btn btn-outline-secondary" onclick="copyUrl('surveyUrl', this)">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    {{-- QR Kodu --}}
                    <div class="text-center">
                        <div id="qrcode" class="d-inline-block p-2 border rounded bg-white"></div>
                        <div class="mt-1">
                            <button class="btn btn-xs btn-outline-secondary" onclick="downloadQr()" style="font-size:11px">
                                <i class="fas fa-download me-1"></i>QR İndir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 d-flex gap-1">
                    <a href="{{ route('staff-surveys.edit', $staffSurvey) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="fas fa-edit me-1"></i>Düzenle
                    </a>
                    <form action="{{ route('staff-surveys.toggle', $staffSurvey) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm @if($staffSurvey->is_active) btn-outline-warning @else btn-outline-success @endif">
                            <i class="fas @if($staffSurvey->is_active) fa-pause @else fa-play @endif"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Departman Dağılımı --}}
            @if(!empty($deptBreakdown))
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-primary"></i>Departman Dağılımı</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($deptBreakdown as $dept => $cnt)
                    @php $pct = $totalResponses > 0 ? round($cnt / $totalResponses * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $dept }}</span>
                            <span class="fw-bold">{{ $cnt }} <span class="text-muted">({{ $pct }}%)</span></span>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Son 14 gün --}}
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Son 14 Gün</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="dailyChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- SAĞ KOLON: Soru istatistikleri + Son yanıtlar --}}
        <div class="col-xl-8">

            {{-- Soru istatistikleri --}}
            @forelse($staffSurvey->questions as $q)
            @php $stat = $stats[$q->id] ?? null; @endphp
            <div class="card mb-3">
                <div class="card-header py-3 d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill">{{ $loop->iteration }}</span>
                    <span class="fw-semibold flex-grow-1">{{ $q->getTitle() }}</span>
                    <span class="badge bg-light text-dark border">{{ strtoupper($q->type) }}</span>
                    @if($q->hasCondition())
                    <span class="badge bg-warning text-dark" title="Koşullu soru">
                        <i class="fas fa-code-branch"></i> Koşullu
                    </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if(!$stat || (isset($stat['count']) && $stat['count'] == 0) || (isset($stat['data']) && empty($stat['data'])))
                        <p class="text-muted small mb-0">Henüz cevap yok.</p>
                    @elseif($stat['type'] === 'rating')
                        {{-- Rating --}}
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div style="font-size:36px;font-weight:800;color:#4361ee">{{ $stat['avg'] }}</div>
                            <div>
                                <div style="color:#f9a825;font-size:20px">
                                    @for($i=1;$i<=5;$i++)
                                        <i class="{{ $i <= round($stat['avg']) ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                                <div class="text-muted small">{{ $stat['count'] }} değerlendirme</div>
                            </div>
                        </div>
                        @foreach($stat['dist'] as $star => $cnt)
                        @php $pct = $stat['count'] > 0 ? round($cnt / $stat['count'] * 100) : 0; @endphp
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span style="width:14px;font-size:12px;text-align:right">{{ $star }}</span>
                            <i class="fas fa-star text-warning" style="font-size:11px"></i>
                            <div class="progress flex-grow-1" style="height:8px">
                                <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="small text-muted" style="width:35px">{{ $cnt }}</span>
                        </div>
                        @endforeach

                    @elseif($stat['type'] === 'nps')
                        {{-- NPS --}}
                        @php
                            $npsVal = $stat['nps'];
                            $npsColor = $npsVal >= 50 ? '#10b981' : ($npsVal >= 0 ? '#f9a825' : '#ef4444');
                        @endphp
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <div>
                                <div style="font-size:40px;font-weight:900;color:{{ $npsColor }};line-height:1">
                                    {{ $npsVal !== null ? ($npsVal > 0 ? '+' : '') . $npsVal : '—' }}
                                </div>
                                <div class="text-muted small">NPS Skoru</div>
                            </div>
                            <div class="text-muted small">
                                <div>Ort: <strong>{{ $stat['avg'] }}</strong>/10</div>
                                <div>{{ $stat['count'] }} yanıt</div>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach($stat['dist'] as $score => $cnt)
                            @php
                                $c = $score >= 9 ? 'success' : ($score >= 7 ? 'warning' : 'danger');
                            @endphp
                            <div class="text-center">
                                <div class="badge bg-{{ $c }} bg-opacity-20 text-{{ $c }} border border-{{ $c }} border-opacity-25"
                                     style="width:30px;font-size:11px">{{ $cnt }}</div>
                                <div style="font-size:9px;color:#9ca3af">{{ $score }}</div>
                            </div>
                            @endforeach
                        </div>

                    @elseif(in_array($stat['type'], ['radio', 'yesno', 'checkbox']))
                        {{-- Radio/Checkbox/Evet-Hayır --}}
                        @php $total = array_sum($stat['data']); @endphp
                        @foreach($stat['data'] as $answer => $cnt)
                        @php $pct = $total > 0 ? round($cnt / $total * 100) : 0; @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $answer }}</span>
                                <span class="fw-bold">{{ $cnt }} <span class="text-muted">({{ $pct }}%)</span></span>
                            </div>
                            <div class="progress" style="height:10px">
                                <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach

                    @else
                        {{-- Metin cevaplar --}}
                        <div class="d-flex flex-column gap-1" style="max-height:200px;overflow-y:auto">
                            @foreach($stat['data'] as $txt)
                            <div class="border rounded px-3 py-2 small bg-light">{{ $txt }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Bu ankette soru bulunmuyor.</div></div>
            @endforelse

            {{-- Son Yanıtlar --}}
            @if($recentResponses->isNotEmpty())
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Son Yanıtlar</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                @if(!$staffSurvey->is_anonymous)<th>Ad</th>@endif
                                @if($staffSurvey->show_dept_field)<th>Departman</th>@endif
                                <th>Dil</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentResponses as $r)
                            <tr>
                                <td class="text-muted">{{ $r->id }}</td>
                                @if(!$staffSurvey->is_anonymous)<td>{{ $r->respondent_name ?: '—' }}</td>@endif
                                @if($staffSurvey->show_dept_field)<td>{{ $r->respondent_dept ?: '—' }}</td>@endif
                                <td><span class="badge bg-light text-dark border">{{ strtoupper($r->lang) }}</span></td>
                                <td>{{ $r->submitted_at?->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Günlük trend
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: @json($dailyLabels),
        datasets: [{
            label: 'Yanıt',
            data: @json($dailyCounts),
            backgroundColor: 'rgba(67,97,238,.25)',
            borderColor: '#4361ee',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            x: { ticks: { font: { size: 9 } } }
        }
    }
});

// QR kod
new QRCode(document.getElementById('qrcode'), {
    text: '{{ $staffSurvey->publicUrl() }}',
    width: 160,
    height: 160,
    colorDark: '#1a1a2e',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
});

function downloadQr() {
    const canvas = document.querySelector('#qrcode canvas');
    if (!canvas) return;
    const a = document.createElement('a');
    a.download = 'personel-anket-qr.png';
    a.href = canvas.toDataURL();
    a.click();
}

function copyUrl(id, btn) {
    navigator.clipboard.writeText(document.getElementById(id).value).then(() => {
        const o = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(() => btn.innerHTML = o, 1500);
    });
}
</script>
@endpush
