@extends('layouts.default')

@section('title', 'Arıza İstatistikleri')

@section('content')
<div class="container-fluid pb-5">

    {{-- Başlık --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Arıza İstatistikleri</h4>
                <span class="text-muted" style="font-size:.82rem">Scoreboard &amp; Performans Analizi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">İstatistikler</li>
            </ol>
        </div>
    </div>

    {{-- Dönem Filtresi --}}
    <div class="card border-0 shadow-sm mb-3 mt-2" style="border-radius:12px">
        <div class="card-body py-2 px-4">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted fw-semibold" style="font-size:.8rem">Dönem:</span>
                @foreach(['7'=>'Son 7 Gün','30'=>'Son 30 Gün','90'=>'Son 90 Gün','365'=>'Son 1 Yıl','all'=>'Tüm Zamanlar'] as $val=>$lbl)
                <a href="{{ route('faults.stats', ['period'=>$val]) }}"
                   class="btn btn-sm rounded-pill {{ $period === $val ? 'btn-dark' : 'btn-outline-secondary' }}"
                   style="font-size:.75rem;padding:3px 14px">
                    {{ $lbl }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Özet Stat Kartları --}}
    <div class="row g-2 mb-3">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#6366f1,#818cf8);box-shadow:0 2px 12px rgba(99,102,241,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-layer-group text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $summary['total'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">TOPLAM</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#ef4444,#f87171);box-shadow:0 2px 12px rgba(239,68,68,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-exclamation text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $summary['open'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">AÇIK</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#f59e0b,#fbbf24);box-shadow:0 2px 12px rgba(245,158,11,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-rotate text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $summary['in_progress'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">İŞLEMDE</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#10b981,#34d399);box-shadow:0 2px 12px rgba(16,185,129,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-circle-check text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">{{ $summary['closed'] }}</div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">KAPALI</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#0891b2,#22d3ee);box-shadow:0 2px 12px rgba(8,145,178,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-hourglass-half text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">
                            {{ $summary['avg_hours'] !== null ? $summary['avg_hours'].' s' : '—' }}
                        </div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">ORT. SÜRE</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0" style="border-radius:12px;background:linear-gradient(135deg,#7c3aed,#a78bfa);box-shadow:0 2px 12px rgba(124,58,237,.22)">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:rgba(255,255,255,.18)">
                        <i class="fas fa-shield-check text-white" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold lh-1" style="font-size:1.4rem">
                            {{ $summary['sla_pct'] !== null ? '%'.$summary['sla_pct'] : '—' }}
                        </div>
                        <div class="text-white-50" style="font-size:.7rem;letter-spacing:.4px">SLA</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Aylık Trend --}}
    @if($monthlyTrend->count())
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-chart-line" style="color:#6366f1"></i>
                <span class="fw-semibold" style="font-size:.88rem">Aylık Trend</span>
            </div>
            <div style="position:relative;height:220px">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- Departman Scoreboard --}}
    @if($deptScoreboard->count())
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#1e293b">
            <i class="fas fa-trophy" style="color:#f59e0b"></i>
            <span class="fw-bold text-white" style="font-size:.88rem">Departman Scoreboard</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="border-collapse:separate;border-spacing:0">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">#</th>
                        <th style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Departman</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Toplam</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Açık</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">İşlemde</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Kapalı</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Ort. Süre</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">SLA %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deptScoreboard as $i => $row)
                    @php
                        $deptColor = $row['dept']?->color ?? '#6366f1';
                    @endphp
                    <tr style="{{ $i === 0 ? 'background:#fffbeb' : ' ' }}">
                        <td style="padding:.6rem 1rem;font-size:.8rem;color:#94a3b8;font-weight:600;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $i + 1 }}
                        </td>
                        <td style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <div class="d-flex align-items-center gap-2">
                                @if($i === 0)
                                <i class="fas fa-crown" style="color:#f59e0b;font-size:.8rem"></i>
                                @else
                                <span class="rounded-circle flex-shrink-0" style="width:8px;height:8px;background:{{ $deptColor }};display:inline-block"></span>
                                @endif
                                <span style="font-size:.82rem;font-weight:600;color:#1e293b">{{ $row['dept']?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.82rem;font-weight:700;color:#1e293b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#fffbeb;color:#d97706">{{ $row['in_progress'] }}</span>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.78rem;color:#64748b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].' s' : '—' }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            @if($row['sla_pct'] !== null)
                            @php
                                $slaBg = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                                $slaFg = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <span class="badge rounded-pill" style="font-size:.7rem;background:{{ $slaBg }};color:{{ $slaFg }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Arıza Türü Performansı --}}
    @if($typeStats->count())
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#1e40af">
            <i class="fas fa-tags" style="color:#bfdbfe"></i>
            <span class="fw-bold text-white" style="font-size:.88rem">Arıza Türü Performansı</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Tür</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">SLA Hedef</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Toplam</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Açık</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Ort. Süre</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">SLA %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeStats as $row)
                    <tr>
                        <td style="padding:.6rem 1rem;font-size:.82rem;font-weight:600;color:#1e293b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['type_name'] }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.78rem;color:#64748b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['target_hours'] }} s
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.82rem;font-weight:700;color:#1e293b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.78rem;color:#64748b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].' s' : '—' }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            @if($row['sla_pct'] !== null)
                            @php
                                $slaBg = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                                $slaFg = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <span class="badge rounded-pill" style="font-size:.7rem;background:{{ $slaBg }};color:{{ $slaFg }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Konum & Alan --}}
    <div class="row g-3 mb-3">
        @if($locationStats->count())
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#065f46">
                    <i class="fas fa-map-marker-alt" style="color:#6ee7b7"></i>
                    <span class="fw-bold text-white" style="font-size:.88rem">Konum Bazında (Top 10)</span>
                </div>
                <div class="d-flex flex-column">
                    @foreach($locationStats as $row)
                    @php
                        $locPct = $locationStats->first()['total'] > 0 ? round($row['total'] / $locationStats->first()['total'] * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center justify-content-between px-4 py-2"
                         style="border-bottom:1px solid #f8fafc">
                        <div style="min-width:0;flex:1" class="pe-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-truncate fw-semibold" style="font-size:.78rem;color:#1e293b">{{ $row['name'] }}</span>
                                <span style="font-size:.72rem;color:#94a3b8;flex-shrink:0;margin-left:8px">{{ $row['open'] }} açık</span>
                            </div>
                            <div class="rounded-pill" style="height:4px;background:#f1f5f9">
                                <div class="rounded-pill h-100" style="width:{{ $locPct }}%;background:#10b981"></div>
                            </div>
                        </div>
                        <span class="badge rounded-pill flex-shrink-0" style="font-size:.72rem;background:#f1f5f9;color:#374151;min-width:28px">
                            {{ $row['total'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($areaStats->count())
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#7c2d12">
                    <i class="fas fa-door-open" style="color:#fca5a5"></i>
                    <span class="fw-bold text-white" style="font-size:.88rem">Alan Top 10</span>
                </div>
                <div class="d-flex flex-column">
                    @foreach($areaStats as $row)
                    <div class="d-flex align-items-center justify-content-between px-4 py-2"
                         style="border-bottom:1px solid #f8fafc">
                        <div style="min-width:0">
                            <div class="fw-semibold text-truncate" style="font-size:.78rem;color:#1e293b">{{ $row['area_name'] }}</div>
                            <div class="text-truncate" style="font-size:.68rem;color:#94a3b8">{{ $row['loc_name'] }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                            <span style="font-size:.7rem;color:#94a3b8">{{ $row['open'] }} açık</span>
                            <span class="badge rounded-pill" style="font-size:.72rem;background:#f1f5f9;color:#374151;min-width:28px">
                                {{ $row['total'] }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Şube Karşılaştırma --}}
    @if($branchStats && $branchStats->count())
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3" style="background:#374151">
            <i class="fas fa-hotel" style="color:#d1d5db"></i>
            <span class="fw-bold text-white" style="font-size:.88rem">Şube Karşılaştırma</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Şube</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Toplam</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Açık</th>
                        <th class="text-end" style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;padding:.65rem 1rem;border-bottom:2px solid #f1f5f9">Kapalı</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchStats as $row)
                    <tr>
                        <td style="padding:.6rem 1rem;font-size:.82rem;font-weight:600;color:#1e293b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['name'] }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;font-size:.82rem;font-weight:700;color:#1e293b;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="text-end" style="padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle">
                            <span class="badge rounded-pill" style="font-size:.7rem;background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var trendData = @json($monthlyTrend);
    if (!trendData.length) return;
    var months = ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'];
    var labels = trendData.map(function(r) {
        var parts = r.month.split('-');
        return months[parseInt(parts[1]) - 1] + ' ' + parts[0].slice(2);
    });
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Toplam',
                    data: trendData.map(function(r) { return r.total; }),
                    backgroundColor: 'rgba(99,102,241,.75)',
                    borderRadius: 5, borderWidth: 0
                },
                {
                    label: 'Kapalı',
                    data: trendData.map(function(r) { return r.closed; }),
                    backgroundColor: 'rgba(16,185,129,.75)',
                    borderRadius: 5, borderWidth: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 11 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }
            }
        }
    });
})();
</script>
@endpush
