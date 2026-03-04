@extends('layouts.default')

@push('styles')
<style>
.metric-card { border-radius:12px; text-align:center; padding:18px 14px; height:100%; }
.metric-card .metric-value { font-size:1.6rem; font-weight:800; line-height:1.1; }
.metric-card .metric-label { font-size:0.78rem; margin-top:4px; opacity:.75; }
.scope-bar-wrap { height:22px; border-radius:6px; overflow:hidden; display:flex; }
.scope-bar-1 { background:#e74c3c; }
.scope-bar-2 { background:#f39c12; }
.scope-bar-3 { background:#27ae60; }
.hcmi-gauge { width:100px; height:100px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-direction:column; font-weight:800; border:6px solid; margin:0 auto; }
.entry-table { font-size:.85rem; }
.category-group-header { background:linear-gradient(90deg,#f8f9fa,#fff); font-weight:700; border-left:4px solid; padding:6px 12px; }
.standard-badge { border-radius:20px; padding:3px 12px; font-size:.75rem; font-weight:600; border:1px solid; }
.timeline-item { border-left:3px solid #e9ecef; padding-left:16px; padding-bottom:16px; position:relative; }
.timeline-item::before { content:''; position:absolute; left:-6px; top:4px; width:10px; height:10px; border-radius:50%; background:#6c757d; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-7 p-md-0">
            <div class="welcome-text">
                <h4>🌿 {{ $carbon->title }}</h4>
                <span>
                    {{ $carbon->period_start->format('d.m.Y') }} — {{ $carbon->period_end->format('d.m.Y') }}
                    &nbsp;|&nbsp; {{ $carbon->branch?->name ?? 'Genel' }}
                    &nbsp;|&nbsp; {!! $carbon->status_badge !!}
                </span>
            </div>
        </div>
        <div class="col-sm-5 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-start gap-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carbon.index') }}">Karbon</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($carbon->title,30) }}</li>
            </ol>
        </div>
    </div>

    {{-- Aksiyon Butonları --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @if(auth()->user()->hasPermission('carbon_footprint', 'edit') && $carbon->status !== 'verified')
        <a href="{{ route('carbon.edit', $carbon) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Düzenle
        </a>
        @endif
        @if($carbon->status === 'draft' && auth()->user()->hasPermission('carbon_footprint', 'edit'))
        <form method="POST" action="{{ route('carbon.finalize', $carbon) }}" class="d-inline"
              onsubmit="return confirm('Rapor finalize edilecek. Devam edilsin mi?')">
            @csrf
            <button class="btn btn-success btn-sm">
                <i class="fas fa-check-circle me-1"></i> Finalize Et
            </button>
        </form>
        @endif
        <a href="{{ route('carbon.pdf', $carbon) }}" class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> PDF Rapor İndir
        </a>
        @if(auth()->user()->hasPermission('carbon_footprint', 'delete'))
        <form method="POST" action="{{ route('carbon.destroy', $carbon) }}" class="d-inline ms-auto"
              onsubmit="return confirm('Bu raporu silmek istediğinizden emin misiniz?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash me-1"></i> Sil
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ============================================================
         ÜST METRİK KARTLARI
    ============================================================ --}}
    @php
        $metTotal      = max(1, $carbon->total_co2_total);
        $metPct1       = round($carbon->total_co2_scope1 / $metTotal * 100, 1);
        $metPct2       = round($carbon->total_co2_scope2 / $metTotal * 100, 1);
        $metPct3       = round($carbon->total_co2_scope3 / $metTotal * 100, 1);
        $metRatingLabels = ['A+'=>'Mükemmel','A'=>'Çok İyi','B'=>'İyi','C'=>'Ortalama','D'=>'Zayıf','E'=>'Yetersiz'];
        $metRatingScale  = ['A+'=>'#1a6b3c','A'=>'#27ae60','B'=>'#f39c12','C'=>'#e67e22','D'=>'#e74c3c','E'=>'#8e44ad'];
        $avgSector     = 30; // kg/oda-gece sektör ortalaması
        $perRoomMet    = $carbon->co2_per_room_night;
        $vsSector      = $perRoomMet > 0 ? round(($perRoomMet - $avgSector) / $avgSector * 100, 1) : null;
        $rnColor       = $perRoomMet > 0 ? ($perRoomMet < 15 ? '#27ae60' : ($perRoomMet < 40 ? '#e67e22' : '#e74c3c')) : '#3498db';
    @endphp
    <div class="row g-3 mb-4">

        {{-- Kart 1: Toplam CO2 --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-top:4px solid #e74c3c!important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-semibold text-uppercase" style="font-size:.68rem;color:#999;letter-spacing:.06em">Toplam Karbon Ayak İzi</div>
                            <div class="text-muted" style="font-size:.7rem">Scope 1 + 2 + 3</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:#fdecea;">
                            <i class="fas fa-smog" style="color:#e74c3c;font-size:.95rem"></i>
                        </div>
                    </div>
                    <div style="line-height:1.05">
                        <span style="font-size:2.1rem;font-weight:800;color:#2c3e50">{{ number_format($carbon->total_co2_total/1000, 2) }}</span>
                        <span class="text-muted ms-1" style="font-size:.9rem">tCO₂e</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.73rem">{{ number_format($carbon->total_co2_total, 0) }} kgCO₂e toplam</div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.67rem;color:#bbb">
                            <span><span style="color:#e74c3c">●</span> S1 {{ $metPct1 }}%</span>
                            <span><span style="color:#f39c12">●</span> S2 {{ $metPct2 }}%</span>
                            <span><span style="color:#27ae60">●</span> S3 {{ $metPct3 }}%</span>
                        </div>
                        <div style="height:7px;border-radius:99px;overflow:hidden;display:flex;background:#f0f0f0">
                            @if($metPct1 > 0)<div style="width:{{ $metPct1 }}%;background:#e74c3c"></div>@endif
                            @if($metPct2 > 0)<div style="width:{{ $metPct2 }}%;background:#f39c12"></div>@endif
                            @if($metPct3 > 0)<div style="width:{{ $metPct3 }}%;background:#27ae60"></div>@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kart 2: kgCO₂e / Oda-Gece --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-top:4px solid {{ $rnColor }}!important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-semibold text-uppercase" style="font-size:.68rem;color:#999;letter-spacing:.06em">Oda-Gece Başına</div>
                            <div class="text-muted" style="font-size:.7rem">HCMI Ana Metriği</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:{{ $rnColor }}18;">
                            <i class="fas fa-bed" style="color:{{ $rnColor }};font-size:.95rem"></i>
                        </div>
                    </div>
                    <div style="line-height:1.05">
                        <span style="font-size:2.1rem;font-weight:800;color:{{ $rnColor }}">{{ number_format($perRoomMet, 2) }}</span>
                        <span class="text-muted ms-1" style="font-size:.9rem">kgCO₂e</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.73rem">{{ number_format($carbon->occupied_rooms) }} satılan oda-gece</div>
                    @if($vsSector !== null)
                    <div class="mt-3 p-2 rounded d-flex align-items-center gap-1" style="background:{{ $perRoomMet < $avgSector ? '#e8f5e9' : '#fdecea' }};font-size:.72rem">
                        <i class="fas fa-arrow-{{ $perRoomMet < $avgSector ? 'down' : 'up' }}" style="color:{{ $perRoomMet < $avgSector ? '#1a6b3c' : '#c0392b' }}"></i>
                        <span>Sektör ort. ({{ $avgSector }} kg): <strong style="color:{{ $perRoomMet < $avgSector ? '#1a6b3c' : '#c0392b' }}">{{ $perRoomMet < $avgSector ? '-' : '+' }}{{ abs($vsSector) }}%</strong></span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kart 3: kgCO₂e / Misafir --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-top:4px solid #3498db!important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-semibold text-uppercase" style="font-size:.68rem;color:#999;letter-spacing:.06em">Misafir Başına</div>
                            <div class="text-muted" style="font-size:.7rem">GRI 305 / CSRD</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:#e8f4fd;">
                            <i class="fas fa-user" style="color:#3498db;font-size:.95rem"></i>
                        </div>
                    </div>
                    <div style="line-height:1.05">
                        <span style="font-size:2.1rem;font-weight:800;color:#2980b9">{{ number_format($carbon->co2_per_guest, 2) }}</span>
                        <span class="text-muted ms-1" style="font-size:.9rem">kgCO₂e</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.73rem">{{ number_format($carbon->total_guests) }} misafir</div>
                    <div class="mt-3 d-flex gap-2" style="font-size:.72rem">
                        <div class="p-2 rounded flex-fill text-center" style="background:#f0f8ff;border:1px solid #c5e3f7">
                            <div class="text-muted">m²</div>
                            <div class="fw-bold" style="color:#2980b9">{{ number_format($carbon->co2_per_sqm, 2) }} kg</div>
                        </div>
                        <div class="p-2 rounded flex-fill text-center" style="background:#f0f8ff;border:1px solid #c5e3f7">
                            <div class="text-muted">Personel</div>
                            <div class="fw-bold" style="color:#2980b9">{{ number_format($carbon->co2_per_staff, 2) }} kg</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kart 4: HCMI Rating — Tam Skala (A+ → E) --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-top:4px solid {{ $carbon->rating_color ?? '#888' }}!important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-semibold text-uppercase" style="font-size:.68rem;color:#999;letter-spacing:.06em">HCMI Performans</div>
                            <div class="text-muted" style="font-size:.7rem">Hotel Carbon Measurement</div>
                        </div>
                        @if($carbon->hcmi_rating)
                        <div class="fw-bold rounded d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:38px;height:38px;background:{{ $carbon->rating_color }}20;color:{{ $carbon->rating_color }};font-size:1.25rem;border:2px solid {{ $carbon->rating_color }}">
                            {{ $carbon->hcmi_rating }}
                        </div>
                        @endif
                    </div>
                    @if($carbon->hcmi_rating)
                    {{-- Tam performans skalası: A+ → E --}}
                    <div class="d-flex gap-1 mb-1">
                        @foreach($metRatingScale as $rl => $rc)
                        @php $isCur = ($carbon->hcmi_rating === $rl); @endphp
                        <div class="flex-fill text-center rounded fw-bold"
                             style="padding:4px 2px;background:{{ $isCur ? $rc : '#f5f5f5' }};color:{{ $isCur ? '#fff' : '#bbb' }};font-size:.75rem;border:1.5px solid {{ $isCur ? $rc : '#e9ecef' }};{{ $isCur ? 'box-shadow:0 2px 5px '.$rc.'44' : '' }}">
                            {{ $rl }}
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between px-1 mb-2" style="font-size:.6rem;color:#ccc">
                        <span>En İyi</span><span>En Kötü</span>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold" style="color:{{ $carbon->rating_color }};font-size:.95rem">{{ $metRatingLabels[$carbon->hcmi_rating] ?? '' }}</div>
                        <div class="text-muted" style="font-size:.7rem">Skor: {{ number_format($carbon->hcmi_score, 0) }}/100 &nbsp;·&nbsp; {{ number_format($perRoomMet, 1) }} kg/oda-gece</div>
                    </div>
                    @else
                    <div class="text-muted text-center pt-2">
                        <i class="fas fa-calculator fa-2x mb-2 d-block"></i>
                        <span style="font-size:.82rem">Oda-gece verisi girilince hesaplanır</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================
         SCOPE DAĞILIMI
    ============================================================ --}}
    @php
        $sdTotal  = max(1, $carbon->total_co2_total);
        $pct1     = round($carbon->total_co2_scope1 / $sdTotal * 100, 1);
        $pct2     = round($carbon->total_co2_scope2 / $sdTotal * 100, 1);
        $pct3     = round($carbon->total_co2_scope3 / $sdTotal * 100, 1);

        // En yüksek kategori her scope için
        $allCatsDefs  = \App\Models\CarbonFootprintReport::CATEGORIES;
        $top1    = $scope1Entries->where('co2_kg', '>', 0)->sortByDesc('co2_kg')->first();
        $top2    = $scope2Entries->where('co2_kg', '>', 0)->sortByDesc('co2_kg')->first();
        $top3e   = $scope3Entries->where('co2_kg', '>', 0)->sortByDesc('co2_kg');
        $top3a   = $top3e->first();
        $top3b   = $top3e->skip(1)->first();

        $top1Label = $top1 ? ($allCatsDefs['scope1'][$top1->category]['label'] ?? $top1->category) : null;
        $top2Label = $top2 ? ($allCatsDefs['scope2'][$top2->category]['label'] ?? $top2->category) : null;
        $top3aLabel = $top3a ? ($allCatsDefs['scope3'][$top3a->category]['label'] ?? $top3a->category) : null;
        $top3bLabel = $top3b ? ($allCatsDefs['scope3'][$top3b->category]['label'] ?? $top3b->category) : null;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex align-items-center gap-2">
                    <h6 class="mb-0 fw-bold">GHG Protocol — Scope Dağılımı</h6>
                    <span class="text-muted ms-auto" style="font-size:.75rem">Toplam: {{ number_format($carbon->total_co2_total/1000, 2) }} tCO₂e</span>
                </div>
                <div class="card-body">
                    {{-- Stacked bar --}}
                    <div class="mb-1" style="font-size:.72rem;color:#aaa">
                        Emisyon dağılımı (Scope 1 + 2 + 3 = %100)
                    </div>
                    <div class="scope-bar-wrap mb-1" style="height:28px;border-radius:6px;">
                        @if($pct1 > 0)<div class="scope-bar-1" style="width:{{ $pct1 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct1 > 6 ? $pct1.'%' : '' }}</div>@endif
                        @if($pct2 > 0)<div class="scope-bar-2" style="width:{{ $pct2 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct2 > 6 ? $pct2.'%' : '' }}</div>@endif
                        @if($pct3 > 0)<div class="scope-bar-3" style="width:{{ $pct3 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct3 > 6 ? $pct3.'%' : '' }}</div>@endif
                    </div>
                    <div class="d-flex gap-3 mb-3" style="font-size:.72rem;color:#888">
                        <span><span style="color:#e74c3c">■</span> Scope 1 — {{ $pct1 }}%</span>
                        <span><span style="color:#f39c12">■</span> Scope 2 — {{ $pct2 }}%</span>
                        <span><span style="color:#27ae60">■</span> Scope 3 — {{ $pct3 }}%</span>
                    </div>
                    <div class="row g-3">
                        {{-- Scope 1 --}}
                        <div class="col-md-4">
                            <div class="p-3 rounded h-100" style="background:#ffeaea;border-left:4px solid #e74c3c">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-bold text-danger" style="font-size:.85rem">Scope 1 — Doğrudan</div>
                                    <span class="badge" style="background:#e74c3c;font-size:.7rem">{{ $pct1 }}%</span>
                                </div>
                                <div class="fw-bold" style="font-size:1.25rem;color:#c0392b">{{ number_format($carbon->total_co2_scope1/1000, 3) }} <span style="font-size:.8rem;font-weight:400">tCO₂e</span></div>
                                <div class="text-muted" style="font-size:.72rem">{{ number_format($carbon->total_co2_scope1, 0) }} kgCO₂e</div>
                                <hr class="my-2">
                                <div style="font-size:.72rem;color:#888">
                                    <i class="fas fa-fire-alt me-1" style="color:#e74c3c"></i>Yakıt yanması & soğutucu gaz kaçakları
                                </div>
                                @if($top1)
                                <div class="mt-2 p-1 rounded" style="background:#fff0f0;font-size:.71rem">
                                    <span class="text-danger fw-semibold">↑ En yüksek:</span>
                                    {{ Str::limit($top1Label, 28) }}<br>
                                    <span class="fw-bold">{{ number_format($top1->co2_kg, 1) }} kg</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Scope 2 --}}
                        <div class="col-md-4">
                            <div class="p-3 rounded h-100" style="background:#fff8e1;border-left:4px solid #f39c12">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-bold" style="color:#b7770d;font-size:.85rem">Scope 2 — Enerji</div>
                                    <span class="badge" style="background:#f39c12;font-size:.7rem">{{ $pct2 }}%</span>
                                </div>
                                <div class="fw-bold" style="font-size:1.25rem;color:#b7770d">{{ number_format($carbon->total_co2_scope2/1000, 3) }} <span style="font-size:.8rem;font-weight:400">tCO₂e</span></div>
                                <div class="text-muted" style="font-size:.72rem">{{ number_format($carbon->total_co2_scope2, 0) }} kgCO₂e</div>
                                <hr class="my-2">
                                <div style="font-size:.72rem;color:#888">
                                    <i class="fas fa-bolt me-1" style="color:#f39c12"></i>Satın alınan elektrik & ısı/soğutma
                                </div>
                                @if($top2)
                                <div class="mt-2 p-1 rounded" style="background:#fffbf0;font-size:.71rem">
                                    <span style="color:#b7770d" class="fw-semibold">↑ En yüksek:</span>
                                    {{ Str::limit($top2Label, 28) }}<br>
                                    <span class="fw-bold">{{ number_format($top2->co2_kg, 1) }} kg</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Scope 3 --}}
                        <div class="col-md-4">
                            <div class="p-3 rounded h-100" style="background:#e8f5e9;border-left:4px solid #27ae60">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-bold text-success" style="font-size:.85rem">Scope 3 — Değer Zinciri</div>
                                    <span class="badge" style="background:#27ae60;font-size:.7rem">{{ $pct3 }}%</span>
                                </div>
                                <div class="fw-bold" style="font-size:1.25rem;color:#1a6b3c">{{ number_format($carbon->total_co2_scope3/1000, 3) }} <span style="font-size:.8rem;font-weight:400">tCO₂e</span></div>
                                <div class="text-muted" style="font-size:.72rem">{{ number_format($carbon->total_co2_scope3, 0) }} kgCO₂e</div>
                                <hr class="my-2">
                                <div style="font-size:.72rem;color:#888">
                                    <i class="fas fa-recycle me-1" style="color:#27ae60"></i>Su, atık, gıda, ulaşım, tedarik
                                </div>
                                @if($top3a)
                                <div class="mt-2 p-1 rounded" style="background:#f0fff4;font-size:.71rem">
                                    <span class="text-success fw-semibold">↑ En yüksek:</span>
                                    {{ Str::limit($top3aLabel, 28) }} — <span class="fw-bold">{{ number_format($top3a->co2_kg, 1) }} kg</span>
                                    @if($top3b)
                                    <br><span class="text-success fw-semibold">↑ 2. sıra:</span>
                                    {{ Str::limit($top3bLabel, 28) }} — <span class="fw-bold">{{ number_format($top3b->co2_kg, 1) }} kg</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yoğunluk Metrikleri --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2">
                    <h6 class="mb-0 fw-bold">Yoğunluk Metrikleri</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted small ps-3">kgCO₂e / Oda-Gece</td>
                                <td class="fw-bold text-end pe-3" style="color:{{ $rnColor }}">{{ number_format($carbon->co2_per_room_night, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-3">kgCO₂e / Misafir</td>
                                <td class="fw-bold text-end pe-3">{{ number_format($carbon->co2_per_guest, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-3">kgCO₂e / m²</td>
                                <td class="fw-bold text-end pe-3">{{ number_format($carbon->co2_per_sqm, 3) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-3">kgCO₂e / Personel</td>
                                <td class="fw-bold text-end pe-3">{{ number_format($carbon->co2_per_staff, 2) }}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="text-muted small ps-3">Su (m³/oda-gece)</td>
                                <td class="fw-bold text-end pe-3">{{ number_format($carbon->water_intensity, 3) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-3">Yenilenebilir Enerji</td>
                                <td class="fw-bold text-end pe-3 text-success">%{{ number_format($carbon->renewable_energy_pct, 1) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-3">Atık Geri Dönüşüm</td>
                                <td class="fw-bold text-end pe-3">%{{ number_format($carbon->waste_recycling_rate, 1) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- Kısa benchmark referansı --}}
                    <div class="px-3 py-2" style="background:#f8f9fa;border-top:1px solid #eee;font-size:.72rem;color:#888">
                        <strong>Sektör Referansları (HCMI 2023):</strong><br>
                        A+ ≤5 kg &nbsp;·&nbsp; A 5–15 &nbsp;·&nbsp; B 15–25 &nbsp;·&nbsp; C 25–40 &nbsp;·&nbsp; D 40–60 &nbsp;·&nbsp; E >60
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         DİNAMİK AKSİYON ÖNERİLERİ
    ============================================================ --}}
    @php
        // En yüksek emisyon yaratan 5 kalem
        $dynRecommendations = [
            'energy_gas'              => ['icon'=>'fas fa-fire',            'color'=>'#e74c3c', 'oneri'=>'Doğal gaz tüketimi en büyük Scope 1 kaynağınız. Gaz sayaç okumalarını aylık takip edin, HVAC bakım sıklığını artırın ve bölgesel ısı geri kazanım sistemleri değerlendirin.'],
            'energy_lng'              => ['icon'=>'fas fa-fire-alt',         'color'=>'#e74c3c', 'oneri'=>'LNG tüketimini azaltmak için operasyonel saatleri optimize edin. Mümkünse ısı pompası veya biyokütle ile kısmen ikame edilebilir.'],
            'energy_fuel_oil'         => ['icon'=>'fas fa-oil-can',          'color'=>'#c0392b', 'oneri'=>'Fuel oil yüksek emisyon faktörlü bir yakıttır. Doğal gaz veya LNG\'ye geçiş %25–40 azaltım sağlayabilir. Acil öneri: kazanlarda yakma optimizasyonu ve yıllık baca analizi.'],
            'energy_lpg'              => ['icon'=>'fas fa-fire',             'color'=>'#e67e22', 'oneri'=>'LPG tüketimi düşük ama takip edilmeli. Menu/mutfak ekipmanlarında enerji verimli cihazlara geçiş değerlendirilebilir.'],
            'energy_coal'             => ['icon'=>'fas fa-industry',         'color'=>'#8e44ad', 'oneri'=>'Kömür kullanımı en yüksek emisyon faktörlü yakıttır. AB Taksonomi ve CSRD uyumu için kömür transitize planı hazırlanması kritik önem taşır.'],
            'energy_electricity'      => ['icon'=>'fas fa-bolt',             'color'=>'#f39c12', 'oneri'=>'Elektrik tüketimi en büyük Scope 2 kaynağınız. LED aydınlatmaya geçiş, enerji yönetim sistemi (BMS/EMS) kurulumu ve boş odalar için akıllı termostata öncelik verin.'],
            'energy_electricity_re'   => ['icon'=>'fas fa-leaf',             'color'=>'#27ae60', 'oneri'=>'Düşük EF\'li yenilenebilir elektrik kullanıyorsunuz, iyi! I-REC veya YEK-G sertifikasıyla Market-Based sıfır EF bildirilebilir.'],
            'refrigerant_r410a'       => ['icon'=>'fas fa-snowflake',        'color'=>'#3498db', 'oneri'=>'R-410A yüksek GWP\'li soğutucu gazdır (GWP=2088). Bakım kayıtları ile kaçak tespiti yapın. Yenileme döneminde R-32 veya R-454B\'ye (düşük GWP) geçiş planlayın.'],
            'refrigerant_r32'         => ['icon'=>'fas fa-snowflake',        'color'=>'#3498db', 'oneri'=>'R-32 görece düşük GWP\'lidir (GWP=675). Kaçak kontrol sıklığını yılda 2 kez yapın, kayıpları kayıt altına alın.'],
            'refrigerant_r134a'       => ['icon'=>'fas fa-snowflake',        'color'=>'#2980b9', 'oneri'=>'R-134a (GWP=1430) yüksek etkilidir. Kaçak tespiti için electronic leak detector kullanın, bakım sırasında gaz geri kazanımı zorunlu tutun.'],
            'food_beef'               => ['icon'=>'fas fa-drumstick-bite',   'color'=>'#c0392b', 'oneri'=>'Sığır eti en yüksek emisyon faktörlü gıdadır (27 kgCO₂e/kg). Menüde bitki bazlı ve balık alternatifleri artırın. %20 sığır eti azaltımı Scope 3 gıda emisyonlarını önemli ölçüde düşürür.'],
            'food_dairy'              => ['icon'=>'fas fa-cheese',           'color'=>'#e67e22', 'oneri'=>'Süt ürünleri de yüksek emisyon taşır. Yerel/organik tedarikçilere geçiş ve porsiyon optimizasyonu değerlendirin.'],
            'waste_general'           => ['icon'=>'fas fa-trash',            'color'=>'#8e44ad', 'oneri'=>'Genel atık miktarı yüksek. Katı atık azaltma planı oluşturun: organik atık kompostlama, gıda artığı yönetimi ve tedarikçilerle ambalaj azaltımı anlaşmaları yapın.'],
            'water_municipal'         => ['icon'=>'fas fa-tint',             'color'=>'#3498db', 'oneri'=>'Su tüketiminiz yüksek. Akıllı sulama sistemleri, düşük debili musluklar ve duş başlıkları kurulumu ile %20–30 tasarruf sağlanabilir. Yağmur suyu toplama sistemleri değerlendirilebilir.'],
            'transport_staff'         => ['icon'=>'fas fa-bus',              'color'=>'#2c3e50', 'oneri'=>'Personel ulaşımı için servis güzergahlarını optimize edin. Elektrikli veya hibrit araç filosu oluşturun, toplu taşıma teşvik programı başlatın.'],
            'business_travel_air'     => ['icon'=>'fas fa-plane',            'color'=>'#8e44ad', 'oneri'=>'İş amaçlı uçuş emisyonları yüksek. Video konferans alternatiflerini teşvik edin, kısa mesafe uçuşları tren ile ikame edin, gerekli uçuşlarda karbon dengeleme (offsetting) programına katılın.'],
            'district_heating'        => ['icon'=>'fas fa-thermometer-half', 'color'=>'#e74c3c', 'oneri'=>'Bölgesel ısıtma emisyonları bölgenin enerji karmasına bağlıdır. EF\'yi belediyeden/TETAŞ\'tan güncel olarak talep edin; yenilenebilir kaynaklı bölgesel ısıtma sistemine geçiş planı isteyin.'],
            'laundry_external'        => ['icon'=>'fas fa-tshirt',           'color'=>'#7f8c8d', 'oneri'=>'Dış çamaşırhane emisyonları Scope 3\'ün önemli bir parçası olabilir. Yeşil sertifikalı çamaşırhane tedarikçileri değerlendirin ya da yenilenebilir enerji kullanan tesis içi laundry yatırımı yapın.'],
            'procurement_linen'       => ['icon'=>'fas fa-bed',              'color'=>'#7f8c8d', 'oneri'=>'Çarşaf/tekstil tedarikinde organik pamuk veya geri dönüştürülmüş elyaf kullanan tedarikçilere geçiş Scope 3\'ü azaltır. Uzun ömürlü ve onarılabilir ürün politikası benimseyin.'],
        ];
        $allEntries     = $carbon->entries->where('co2_kg', '>', 0)->sortByDesc('co2_kg')->take(5);
        $hasDynRecs     = $allEntries->count() > 0;
    @endphp
    @if($hasDynRecs)
    <div class="card shadow-sm mb-4" style="border-left:4px solid #e67e22">
        <div class="card-header py-2 d-flex align-items-center gap-2" style="background:#fff8f0">
            <i class="fas fa-lightbulb" style="color:#e67e22"></i>
            <h6 class="mb-0 fw-bold" style="color:#7d4e00">Yüksek Emisyon Kaynakları & Aksiyon Önerileri</h6>
            <span class="ms-auto badge bg-warning text-dark" style="font-size:.72rem">Otomatik — Verilerinize Göre</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php $recCounter = 0; @endphp
                @foreach($allEntries as $recEntry)
                @php
                    $scopeKey = 'scope'.$recEntry->scope;
                    $recLabel = $allCatsDefs[$scopeKey][$recEntry->category]['label'] ?? $recEntry->category;
                    $recDef   = $dynRecommendations[$recEntry->category] ?? null;
                    $recBadgeColors = [1=>'#e74c3c', 2=>'#f39c12', 3=>'#27ae60'];
                    $recBadgeColor  = $recBadgeColors[$recEntry->scope] ?? '#888';
                    $recCounter++;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 rounded h-100" style="background:#fffdf8;border:1px solid #f0e0c0;border-left:3px solid {{ $recBadgeColor }}">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:30px;height:30px;background:{{ $recBadgeColor }}18;font-size:.75rem;font-weight:800;color:{{ $recBadgeColor }}">
                                {{ $recCounter }}
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:.83rem;color:#2c3e50">{{ $recLabel }}</div>
                                <div style="font-size:.7rem">
                                    <span class="badge" style="background:{{ $recBadgeColor }};font-size:.65rem">S{{ $recEntry->scope }}</span>
                                    <span class="text-muted ms-1">{{ number_format($recEntry->co2_kg, 1) }} kgCO₂e</span>
                                    <span class="text-muted">&nbsp;·&nbsp; {{ number_format($recEntry->co2_kg / $sdTotal * 100, 1) }}% toplam</span>
                                </div>
                            </div>
                        </div>
                        @if($recDef)
                        <div style="font-size:.78rem;color:#555;line-height:1.45">
                            <i class="{{ $recDef['icon'] }} me-1" style="color:{{ $recDef['color'] }}"></i>{{ $recDef['oneri'] }}
                        </div>
                        @else
                        <div style="font-size:.78rem;color:#888">
                            Bu kategori için tüketimi düşürmeye yönelik iyileştirme planı hazırlanmalıdır.
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-3 text-muted" style="font-size:.72rem">
                <i class="fas fa-info-circle me-1"></i>
                Öneriler verilerinizdeki en yüksek emisyon kaynaklı kategorilere göre otomatik oluşturulmuştur ({{ $allEntries->count() }} kategori, toplam emisyonun {{ number_format($allEntries->sum('co2_kg') / $sdTotal * 100, 0) }}%).
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         HESAPLAMA METODOLOJİSİ — DENETİM PANELİ
    ============================================================ --}}
    <div class="card shadow-sm mb-4" style="border-left:4px solid #3498db">
        <div class="card-header py-2 d-flex align-items-center gap-2" style="background:#eaf4fb">
            <i class="fas fa-calculator" style="color:#2980b9"></i>
            <h6 class="mb-0 fw-bold" style="color:#1a5276">Hesaplama Metodolojisi — Denetim Referansı</h6>
            <button class="btn btn-sm btn-outline-secondary ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#methodologyPanel">
                <i class="fas fa-chevron-down"></i> Göster / Gizle
            </button>
        </div>
        <div id="methodologyPanel" class="collapse show">
            <div class="card-body" style="font-size:.85rem">
                <div class="row g-4">

                    {{-- Temel Formül --}}
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#eaf4fb;border:1px solid #aed6f1">
                            <div class="fw-bold mb-2" style="color:#1a5276">📐 Temel Hesaplama Formülü</div>
                            <div class="text-center py-2" style="font-size:1rem;font-weight:700;color:#2c3e50;background:#fff;border-radius:6px;padding:10px">
                                CO₂e (kg) = <span style="color:#e74c3c">Miktar</span> × <span style="color:#27ae60">Emisyon Faktörü</span>
                            </div>
                            <div class="mt-2 text-muted" style="font-size:.78rem">
                                <strong>GHG Protocol Corporate Standard</strong> ve <strong>ISO 14064-1:2018</strong> kapsamında Scope 1, 2 ve 3 için ayrı ayrı hesaplanır; toplamları CO₂e (karbon dioksit eşdeğeri) cinsinden raporlanır.
                            </div>
                        </div>
                    </div>

                    {{-- Scope Tanımları --}}
                    <div class="col-md-4">
                        <div class="fw-bold mb-2" style="color:#1a5276">📋 Kapsam (Scope) Tanımları</div>
                        <div class="mb-2 p-2 rounded" style="background:#ffeaea;font-size:.8rem">
                            <strong style="color:#c0392b">🔴 Scope 1 — Doğrudan</strong><br>
                            Tesiste doğrudan yakılan yakıtlardan (doğal gaz, LNG, fuel oil, LPG) ve soğutucu gaz kaçaklarından kaynaklanan emisyonlar.<br>
                            <em>Standart: ISO 14064-1 §5.2, GHG Protocol §4</em>
                        </div>
                        <div class="mb-2 p-2 rounded" style="background:#fff8e1;font-size:.8rem">
                            <strong style="color:#b7770d">🟡 Scope 2 — Enerji</strong><br>
                            Satın alınan elektrik, ısı, soğutma. <strong>Location-Based</strong> (şebeke EF) veya <strong>Market-Based</strong> (I-REC/YEK-G/GoO sertifikalı veya tesis içi GES = 0 EF) yöntemi seçilebilir.<br>
                            <em>Standart: ISO 14064-1 §5.3, GHG Protocol §6</em>
                        </div>
                        <div class="p-2 rounded" style="background:#e8f5e9;font-size:.8rem">
                            <strong style="color:#1a6b3c">🟢 Scope 3 — Değer Zinciri</strong><br>
                            Su tüketimi, atıklar, gıda, ulaşım, tedarik zinciri, çamaşırhane vb. dolaylı emisyonlar.<br>
                            <em>Standart: GHG Protocol Scope 3 Standard, ISO 14064-1 §5.4</em>
                        </div>
                    </div>

                    {{-- Emisyon Faktörü Kaynakları --}}
                    <div class="col-md-4">
                        <div class="fw-bold mb-2" style="color:#1a5276">📚 Emisyon Faktörü Kaynakları</div>
                        <table class="table table-bordered table-sm mb-2" style="font-size:.78rem">
                            <thead class="table-light"><tr><th>Kaynak</th><th>Kapsam</th></tr></thead>
                            <tbody>
                                <tr><td><strong>IPCC AR6 2023</strong></td><td>Yakıtlar, GWP100</td></tr>
                                <tr><td><strong>DEFRA 2023</strong></td><td>Ulaşım, su, atık, LNG</td></tr>
                                <tr><td><strong>IEA Turkey 2023</strong></td><td>Türkiye şebeke elektriği</td></tr>
                                <tr><td><strong>FAO / IPCC 2023</strong></td><td>Gıda tüketimi</td></tr>
                                <tr><td><strong>Ecoinvent 3.9</strong></td><td>Tedarik zinciri, çamaşırhane</td></tr>
                                <tr><td><strong>ICAO 2023</strong></td><td>Hava taşımacılığı</td></tr>
                                <tr><td><strong>ISO 14064-1 Market-Based</strong></td><td>I-REC/GoO, tesis içi GES</td></tr>
                            </tbody>
                        </table>
                        <div class="text-muted" style="font-size:.75rem">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            Tüm değerler <strong>kgCO₂e</strong> (GWP100, AR6 7. Değerlendirme Raporu) cinsindedir. Market-Based Scope 2 için sertifikalı yenilenebilir kaynak EF = 0,0 kgCO₂e/kWh (RE100 / GHG Protocol §6.3.3).
                        </div>
                    </div>

                    {{-- Yoğunluk Metrikleri Formülleri --}}
                    <div class="col-12">
                        <div class="fw-bold mb-2" style="color:#1a5276">📊 Yoğunluk Metrikleri Hesaplama Yöntemi (HCMI / CSRD)</div>
                        <div class="row g-2">
                            @php
                                $total = $carbon->total_co2_total;
                                $waterEntries = $carbon->entries->whereIn('category', ['water_municipal','water_wastewater']);
                                $totalWaterM3 = $waterEntries->where('unit','m³')->sum('quantity') + $waterEntries->where('unit','m3')->sum('quantity');
                            @endphp
                            <div class="col-md-3">
                                <div class="p-2 rounded text-center" style="background:#f8f9fa;border:1px solid #dee2e6;font-size:.78rem">
                                    <div class="fw-bold text-danger">CO₂e / Oda-Gece</div>
                                    <div style="font-family:monospace">{{ number_format($total,0) }} ÷ {{ $carbon->occupied_rooms }}</div>
                                    <div class="fw-bold">= {{ number_format($carbon->co2_per_room_night,3) }} kg</div>
                                    <div class="text-muted" style="font-size:.7rem">HCMI ana metriği</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 rounded text-center" style="background:#f8f9fa;border:1px solid #dee2e6;font-size:.78rem">
                                    <div class="fw-bold text-primary">CO₂e / Misafir</div>
                                    <div style="font-family:monospace">{{ number_format($total,0) }} ÷ {{ $carbon->total_guests }}</div>
                                    <div class="fw-bold">= {{ number_format($carbon->co2_per_guest,3) }} kg</div>
                                    <div class="text-muted" style="font-size:.7rem">GRI 305 / CSRD</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 rounded text-center" style="background:#f8f9fa;border:1px solid #dee2e6;font-size:.78rem">
                                    <div class="fw-bold" style="color:#8e44ad">CO₂e / m²</div>
                                    <div style="font-family:monospace">{{ number_format($total,0) }} ÷ {{ number_format($carbon->total_area_sqm,0) }}</div>
                                    <div class="fw-bold">= {{ number_format($carbon->co2_per_sqm,3) }} kg</div>
                                    <div class="text-muted" style="font-size:.7rem">ISO 50001 / EU Taxonomy</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 rounded text-center" style="background:#f8f9fa;border:1px solid #dee2e6;font-size:.78rem">
                                    <div class="fw-bold" style="color:#1a6b3c">Su / Oda-Gece</div>
                                    <div style="font-family:monospace">{{ number_format($totalWaterM3,1) }} m³ ÷ {{ $carbon->occupied_rooms }}</div>
                                    <div class="fw-bold">= {{ number_format($carbon->water_intensity,3) }} m³</div>
                                    <div class="text-muted" style="font-size:.7rem">HCMI Su Yoğunluğu</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         PERFORMANS DEĞERLENDİRMESİ — BENCHMARK KARŞILAŞTIRMA
    ============================================================ --}}
    @if($carbon->co2_per_room_night > 0)
    <div class="card shadow-sm mb-4" style="border-left:4px solid {{ $carbon->rating_color }}">
        <div class="card-header py-2 d-flex align-items-center gap-2" style="background:{{ $carbon->rating_color }}12">
            <i class="fas fa-chart-bar" style="color:{{ $carbon->rating_color }}"></i>
            <h6 class="mb-0 fw-bold">Performans Değerlendirmesi — Sektör Benchmark Karşılaştırması</h6>
        </div>
        <div class="card-body">
            <div class="row g-4 align-items-start">

                {{-- Ana Rating Göstergesi --}}
                <div class="col-md-3 text-center">
                    @php
                        $ratingLabels = ['A+'=>'Mükemmel','A'=>'Çok İyi','B'=>'İyi','C'=>'Ortalama','D'=>'Zayıf','E'=>'Yetersiz'];
                        $perRoom = $carbon->co2_per_room_night;
                        $ratingLabel = $ratingLabels[$carbon->hcmi_rating] ?? '';
                    @endphp
                    <div class="hcmi-gauge" style="border-color:{{ $carbon->rating_color }};color:{{ $carbon->rating_color }};background:{{ $carbon->rating_color }}18;width:110px;height:110px;font-size:2.2rem;margin-bottom:10px">
                        {{ $carbon->hcmi_rating }}
                    </div>
                    <div class="fw-bold fs-5" style="color:{{ $carbon->rating_color }}">{{ $ratingLabel }}</div>
                    <div class="text-muted small">HCMI Skor: {{ number_format($carbon->hcmi_score,0) }}/100</div>
                    <div class="mt-2 fw-bold" style="font-size:1.1rem">{{ number_format($perRoom,2) }} kg CO₂e</div>
                    <div class="text-muted small">oda-gece başına</div>
                </div>

                {{-- Benchmark Tablosu --}}
                <div class="col-md-5">
                    <div class="fw-bold mb-2 small">🏨 Otel Sektörü Benchmark (kgCO₂e/oda-gece)</div>
                    <table class="table table-sm table-bordered mb-0" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr><th>Performans Seviyesi</th><th class="text-center">Değer Aralığı</th><th class="text-center">Rating</th><th class="text-center">Durum</th></tr>
                        </thead>
                        <tbody>
                            @php
                                $benchmarks = [
                                    ['label'=>'En İyi Uygulama (Best in Class)',      'range'=>'< 5 kg',     'rating'=>'A+', 'color'=>'#1a6b3c', 'min'=>0,  'max'=>5],
                                    ['label'=>'Çok İyi — Paris Hedefi 2050',          'range'=>'5 – 15 kg',  'rating'=>'A',  'color'=>'#27ae60', 'min'=>5,  'max'=>15],
                                    ['label'=>'İyi — AB Taksonomi Uyumlu',            'range'=>'15 – 25 kg', 'rating'=>'B',  'color'=>'#f39c12', 'min'=>15, 'max'=>25],
                                    ['label'=>'Ortalama — AB/Küresel Sektör Ort.',    'range'=>'25 – 40 kg', 'rating'=>'C',  'color'=>'#e67e22', 'min'=>25, 'max'=>40],
                                    ['label'=>'Zayıf — İyileştirme Gerekli',          'range'=>'40 – 60 kg', 'rating'=>'D',  'color'=>'#e74c3c', 'min'=>40, 'max'=>60],
                                    ['label'=>'Yetersiz — Acil Aksiyon Gerekli',      'range'=>'> 60 kg',    'rating'=>'E',  'color'=>'#8e44ad', 'min'=>60, 'max'=>9999],
                                ];
                            @endphp
                            @foreach($benchmarks as $bm)
                            @php $isCurrent = ($perRoom >= $bm['min'] && $perRoom < $bm['max']); @endphp
                            <tr @if($isCurrent) style="background:{{ $bm['color'] }}22;font-weight:700" @endif>
                                <td>
                                    @if($isCurrent)<i class="fas fa-arrow-right me-1" style="color:{{ $bm['color'] }}"></i>@endif
                                    {{ $bm['label'] }}
                                </td>
                                <td class="text-center">{{ $bm['range'] }}</td>
                                <td class="text-center"><span class="badge" style="background-color:{{ $bm['color'] }}">{{ $bm['rating'] }}</span></td>
                                <td class="text-center">
                                    @if($isCurrent)
                                    <span class="badge bg-dark">✓ Sizin Konumunuz</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-muted mt-1" style="font-size:.72rem">
                        Kaynak: HCMI (Hotel Carbon Measurement Initiative), IHG/Hilton 2023 Sustainability Reports, EU Green Deal Hotel Benchmarks, CDP Hospitality Sector 2023
                    </div>
                </div>

                {{-- Yorum ve Öneriler --}}
                <div class="col-md-4">
                    <div class="fw-bold mb-2 small">💡 Değerlendirme & Aksiyon Önerileri</div>
                    @php
                        $r = $carbon->hcmi_rating;
                        $nextTarget = match(true) {
                            $perRoom < 5   => null,
                            $perRoom < 15  => ['hedef'=>'< 5 kg', 'yuzde'=> round((1 - 5/$perRoom)*100)],
                            $perRoom < 25  => ['hedef'=>'< 15 kg','yuzde'=> round((1 - 15/$perRoom)*100)],
                            $perRoom < 40  => ['hedef'=>'< 25 kg','yuzde'=> round((1 - 25/$perRoom)*100)],
                            $perRoom < 60  => ['hedef'=>'< 40 kg','yuzde'=> round((1 - 40/$perRoom)*100)],
                            default        => ['hedef'=>'< 60 kg','yuzde'=> round((1 - 60/$perRoom)*100)],
                        };
                    @endphp

                    @if($r === 'A+')
                    <div class="alert alert-success py-2 px-3 mb-2" style="font-size:.82rem">
                        <strong>🏆 Mükemmel Performans!</strong> Otel sektörünün en iyi %5'indesiniz. Paris Anlaşması 2050 hedefini şimdiden karşılıyorsunuz. CSRD, CDP ve EU Taxonomy raporlama gerekliliklerinizi bu performansla kolayca karşılarsınız.
                    </div>
                    @elseif($r === 'A')
                    <div class="alert alert-success py-2 px-3 mb-2" style="font-size:.82rem">
                        <strong>✅ Çok İyi!</strong> Paris Anlaşması 2050 hedefi doğrultusundasınız. HCMI ve CSRD açısından olumlu konumdaki. A+ için Scope 2'de I-REC sertifikalı yenilenebilir elektriğe geçiş ve tesis içi GES değerlendirilebilir.
                    </div>
                    @elseif($r === 'B')
                    <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:.82rem">
                        <strong>👍 İyi Seviye.</strong> AB Taksonomi uyumlu aralıkta. Bir sonraki seviyeye geçmek için <strong>~%{{ $nextTarget['yuzde'] ?? 0 }} azaltım</strong> ({{ $nextTarget['hedef'] ?? '' }}) gerekiyor. Enerji verimliliği yatırımları, LED aydınlatma ve soğutucu gaz yönetimi öncelikli alanlar.
                    </div>
                    @elseif($r === 'C')
                    <div class="alert py-2 px-3 mb-2" style="background:#fff3cd;border-left:4px solid #e67e22;font-size:.82rem">
                        <strong>⚠️ Ortalama Seviye.</strong> AB ve küresel sektör ortalamasındasınız. CSRD raporlama yükümlülüğü kapsamında iyileştirme planı gerekebilir. Öncelik: Scope 2 yenilenebilir enerji, Scope 1 yakıt optimizasyonu, gıda atığı azaltımı.
                    </div>
                    @elseif($r === 'D')
                    <div class="alert alert-danger py-2 px-3 mb-2" style="font-size:.82rem">
                        <strong>🔴 Zayıf Performans.</strong> Sektör ortalamasının altındasınız. CDP/CSRD açısından risk alanında. Acil öncelikler: Enerji denetimi yaptırın, yenilenebilir enerji sözleşmesi (PPA/I-REC), LED ve HVAC yükseltmesi.
                    </div>
                    @else
                    <div class="alert alert-danger py-2 px-3 mb-2" style="font-size:.82rem">
                        <strong>🚨 Acil İyileştirme Gerekli.</strong> Yatırımcı ve AB regülasyonu baskısı yüksek. Kapsamlı bir Enerji & Karbon Yönetim Planı hazırlanması önerilir. Net Sıfır yol haritası ve kısa vadeli hedefler belirlenmelidir.
                    </div>
                    @endif

                    <div style="font-size:.78rem">
                        <div class="fw-bold mb-1">📋 Uluslararası Geçerlilik (Bu rapor için):</div>
                        <ul class="mb-0 ps-3 text-muted">
                            <li>GHG Protocol Corporate Standard ✓</li>
                            <li>ISO 14064-1:2018 metodolojisi ✓</li>
                            <li>HCMI (Hotel Carbon Measurement Initiative) ✓</li>
                            <li>CSRD / ESRS E1 — Emisyon yoğunluğu metrikleri ✓</li>
                            <li>GRI 305 — Emisyonlar ✓</li>
                            @if($carbon->co2_per_room_night < 25)
                            <li class="text-success">EU Taxonomy Climate Mitigation ✓ Uyumlu</li>
                            @else
                            <li class="text-warning">EU Taxonomy — İyileştirme planı hazırlanmalı</li>
                            @endif
                        </ul>
                    </div>
                    @if($nextTarget)
                    <div class="mt-2 p-2 rounded" style="background:#f0f8ff;border:1px solid #aed6f1;font-size:.78rem">
                        <strong>🎯 Sonraki Hedef:</strong> {{ $nextTarget['hedef'] }}<br>
                        Mevcut değerden <strong>%{{ $nextTarget['yuzde'] }}</strong> azaltım ile bir üst dereceye ulaşılır.
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         DETAYLI EMİSYON TABLOSU
    ============================================================ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Detaylı Emisyon Dökümü</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 entry-table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kapsam</th>
                            <th>Kategori</th>
                            <th class="text-end">Miktar</th>
                            <th class="text-center">Birim</th>
                            <th class="text-end">Emisyon Faktörü</th>
                            <th class="text-end">CO₂e (kg)</th>
                            <th class="text-end pe-3 text-muted small">Kaynak</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- SCOPE 1 --}}
                        @if($scope1Entries->count())
                        <tr><td colspan="7" class="category-group-header" style="border-left-color:#e74c3c">
                            🔴 Scope 1 — Doğrudan Emisyonlar
                        </td></tr>
                        @foreach($scope1Entries as $e)
                        @php
                            $allCats = \App\Models\CarbonFootprintReport::CATEGORIES;
                            $catLabel = $allCats['scope1'][$e->category]['label'] ?? $e->category;
                        @endphp
                        @if($e->co2_kg > 0 || $e->quantity > 0)
                        <tr>
                            <td class="ps-3"><span class="{{ $e->scope_badge_class }}">{{ $e->scope_label }}</span></td>
                            <td>{{ $catLabel }}</td>
                            <td class="text-end">{{ number_format($e->quantity, 3) }}</td>
                            <td class="text-center text-muted">{{ $e->unit }}</td>
                            <td class="text-end text-muted">{{ $e->emission_factor }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($e->co2_kg, 3) }}</td>
                            <td class="text-end pe-3 text-muted" style="font-size:.72rem">{{ $e->ef_source }}</td>
                        </tr>
                        @endif
                        @endforeach
                        <tr class="table-danger table-sm">
                            <td colspan="5" class="ps-3 fw-bold small text-end pe-3">Scope 1 Toplam:</td>
                            <td class="fw-bold text-danger text-end">{{ number_format($scope1Total, 3) }} kg</td>
                            <td></td>
                        </tr>
                        @endif

                        {{-- SCOPE 2 --}}
                        @if($scope2Entries->count())
                        <tr><td colspan="7" class="category-group-header" style="border-left-color:#f39c12">
                            🟡 Scope 2 — Dolaylı Enerji Emisyonları
                        </td></tr>
                        @foreach($scope2Entries as $e)
                        @php $catLabel = $allCats['scope2'][$e->category]['label'] ?? $e->category; @endphp
                        @if($e->co2_kg > 0 || $e->quantity > 0)
                        <tr>
                            <td class="ps-3"><span class="{{ $e->scope_badge_class }}">{{ $e->scope_label }}</span></td>
                            <td>{{ $catLabel }}</td>
                            <td class="text-end">{{ number_format($e->quantity, 3) }}</td>
                            <td class="text-center text-muted">{{ $e->unit }}</td>
                            <td class="text-end text-muted">{{ $e->emission_factor }}</td>
                            <td class="text-end fw-bold" style="color:#b7770d">{{ number_format($e->co2_kg, 3) }}</td>
                            <td class="text-end pe-3 text-muted" style="font-size:.72rem">{{ $e->ef_source }}</td>
                        </tr>
                        @endif
                        @endforeach
                        <tr class="table-warning">
                            <td colspan="5" class="ps-3 fw-bold small text-end pe-3">Scope 2 Toplam:</td>
                            <td class="fw-bold text-end" style="color:#b7770d">{{ number_format($scope2Total, 3) }} kg</td>
                            <td></td>
                        </tr>
                        @endif

                        {{-- SCOPE 3 --}}
                        @if($scope3Entries->count())
                        <tr><td colspan="7" class="category-group-header" style="border-left-color:#27ae60">
                            🟢 Scope 3 — Diğer Dolaylı Emisyonlar
                        </td></tr>
                        @foreach($scope3Entries as $e)
                        @php $catLabel = $allCats['scope3'][$e->category]['label'] ?? $e->category; @endphp
                        @if($e->co2_kg > 0 || $e->quantity > 0)
                        <tr>
                            <td class="ps-3"><span class="{{ $e->scope_badge_class }}">{{ $e->scope_label }}</span></td>
                            <td>{{ $catLabel }}</td>
                            <td class="text-end">{{ number_format($e->quantity, 3) }}</td>
                            <td class="text-center text-muted">{{ $e->unit }}</td>
                            <td class="text-end text-muted">{{ $e->emission_factor }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($e->co2_kg, 3) }}</td>
                            <td class="text-end pe-3 text-muted" style="font-size:.72rem">{{ $e->ef_source }}</td>
                        </tr>
                        @endif
                        @endforeach
                        <tr class="table-success">
                            <td colspan="5" class="ps-3 fw-bold small text-end pe-3">Scope 3 Toplam:</td>
                            <td class="fw-bold text-success text-end">{{ number_format($scope3Total, 3) }} kg</td>
                            <td></td>
                        </tr>
                        @endif

                        {{-- GENEL TOPLAM --}}
                        <tr class="table-dark">
                            <td colspan="5" class="ps-3 fw-bold text-end pe-3">GENEL TOPLAM CO₂e:</td>
                            <td class="fw-bold fs-6 text-end">{{ number_format($carbon->total_co2_total, 3) }} kg</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================
         STANDARTLAR & NOTLAR
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Uygulanan Standartlar & Çerçeveler</h6>
                </div>
                <div class="card-body">
                    @if($carbon->standards_applied && count($carbon->standards_applied))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($carbon->standards_applied as $s)
                                <span class="standard-badge" style="background:#e8f5e9;color:#1a6b3c;border-color:#a8d5b5">
                                    ✓ {{ $s }}
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            @foreach($carbon->standards_applied as $s)
                                @if(isset($standards[$s]))
                                <div class="timeline-item">
                                    <div class="fw-semibold small">{{ $s }}</div>
                                    <div class="text-muted" style="font-size:.78rem">{{ $standards[$s] }}</div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Standart belirtilmemiş.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Metodoloji & İyileştirme Notları</h6>
                </div>
                <div class="card-body">
                    @if($carbon->methodology_notes)
                    <div class="mb-3">
                        <div class="fw-semibold text-primary mb-1">📋 Metodoloji</div>
                        <p class="text-muted small">{{ $carbon->methodology_notes }}</p>
                    </div>
                    @endif
                    @if($carbon->improvement_notes)
                    <div>
                        <div class="fw-semibold text-success mb-1">🎯 İyileştirme Önerileri</div>
                        <p class="text-muted small">{{ $carbon->improvement_notes }}</p>
                    </div>
                    @endif
                    @if(!$carbon->methodology_notes && !$carbon->improvement_notes)
                        <p class="text-muted">Not eklenmemiş.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer bilgi --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2 d-flex flex-wrap gap-4 align-items-center" style="font-size:.8rem;">
            <span class="text-muted"><strong>Oluşturan:</strong> {{ $carbon->user->name ?? '—' }}</span>
            <span class="text-muted"><strong>Oluşturma:</strong> {{ $carbon->created_at->format('d.m.Y H:i') }}</span>
            @if($carbon->finalized_at)
            <span class="text-muted"><strong>Finalize:</strong> {{ $carbon->finalized_at->format('d.m.Y H:i') }}</span>
            @endif
            @if($carbon->pdf_path)
            <span class="text-success"><i class="fas fa-check-circle me-1"></i>PDF oluşturulmuş</span>
            @endif
        </div>
    </div>

</div>
@endsection
