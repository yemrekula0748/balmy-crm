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
    <div class="row g-3 mb-4">
        {{-- Toplam CO2 --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="metric-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff">
                    <div class="metric-value">{{ number_format($carbon->total_co2_total/1000, 2) }}</div>
                    <div class="metric-label">tCO₂e Toplam Emisyon</div>
                    <div style="font-size:.75rem;opacity:.85;margin-top:4px">{{ number_format($carbon->total_co2_total, 0) }} kgCO₂e</div>
                </div>
            </div>
        </div>
        {{-- Per Room Night --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="metric-card" style="background:linear-gradient(135deg,#e67e22,#d35400);color:#fff">
                    <div class="metric-value">{{ number_format($carbon->co2_per_room_night, 2) }}</div>
                    <div class="metric-label">kgCO₂e / Oda-Gece</div>
                    <div style="font-size:.75rem;opacity:.85;margin-top:4px">HCMI Referans Metriği</div>
                </div>
            </div>
        </div>
        {{-- Per Guest --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="metric-card" style="background:linear-gradient(135deg,#3498db,#2980b9);color:#fff">
                    <div class="metric-value">{{ number_format($carbon->co2_per_guest, 2) }}</div>
                    <div class="metric-label">kgCO₂e / Misafir</div>
                    <div style="font-size:.75rem;opacity:.85;margin-top:4px">{{ $carbon->total_guests }} misafir</div>
                </div>
            </div>
        </div>
        {{-- HCMI Score --}}
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-center flex-column gap-2">
                    @if($carbon->hcmi_rating)
                        <div class="hcmi-gauge" style="border-color:{{ $carbon->rating_color }};color:{{ $carbon->rating_color }};background:{{ $carbon->rating_color }}18">
                            <div style="font-size:1.8rem;line-height:1">{{ $carbon->hcmi_rating }}</div>
                            <div style="font-size:.7rem;font-weight:400">{{ number_format($carbon->hcmi_score, 0) }}p</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" style="color:{{ $carbon->rating_color }}">HCMI Rating</div>
                            <div class="text-muted small">Hotel Carbon Measurement Initiative</div>
                        </div>
                    @else
                        <div class="text-muted text-center">
                            <i class="fas fa-chart-pie fa-2x mb-2"></i><br>
                            Oda-gece verisi girince hesaplanır
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         SCOPE DAĞILIMI
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">GHG Protocol — Scope Dağılımı</h6>
                </div>
                <div class="card-body">
                    @php
                        $total = max(1, $carbon->total_co2_total);
                        $pct1  = round($carbon->total_co2_scope1 / $total * 100, 1);
                        $pct2  = round($carbon->total_co2_scope2 / $total * 100, 1);
                        $pct3  = round($carbon->total_co2_scope3 / $total * 100, 1);
                    @endphp
                    <div class="scope-bar-wrap mb-3" style="height:32px;">
                        @if($pct1 > 0) <div class="scope-bar-1" style="width:{{ $pct1 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct1 }}%</div> @endif
                        @if($pct2 > 0) <div class="scope-bar-2" style="width:{{ $pct2 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct2 }}%</div> @endif
                        @if($pct3 > 0) <div class="scope-bar-3" style="width:{{ $pct3 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ $pct3 }}%</div> @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#ffeaea;border-left:4px solid #e74c3c">
                                <div class="fw-bold text-danger">Scope 1 — Doğrudan</div>
                                <div class="fs-5 fw-bold">{{ number_format($carbon->total_co2_scope1/1000, 3) }} tCO₂e</div>
                                <div class="text-muted small">{{ number_format($carbon->total_co2_scope1, 1) }} kg | %{{ $pct1 }}</div>
                                <div class="text-muted" style="font-size:.72rem;margin-top:4px">Yakıt + Soğutucu gazlar</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#fff8e1;border-left:4px solid #f39c12">
                                <div class="fw-bold" style="color:#b7770d">Scope 2 — Enerji</div>
                                <div class="fs-5 fw-bold">{{ number_format($carbon->total_co2_scope2/1000, 3) }} tCO₂e</div>
                                <div class="text-muted small">{{ number_format($carbon->total_co2_scope2, 1) }} kg | %{{ $pct2 }}</div>
                                <div class="text-muted" style="font-size:.72rem;margin-top:4px">Satın alınan elektrik/ısı</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#e8f5e9;border-left:4px solid #27ae60">
                                <div class="fw-bold text-success">Scope 3 — Değer Zinciri</div>
                                <div class="fs-5 fw-bold">{{ number_format($carbon->total_co2_scope3/1000, 3) }} tCO₂e</div>
                                <div class="text-muted small">{{ number_format($carbon->total_co2_scope3, 1) }} kg | %{{ $pct3 }}</div>
                                <div class="text-muted" style="font-size:.72rem;margin-top:4px">Su, atık, gıda, ulaşım</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yoğunluk Metrikleri --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Yoğunluk Metrikleri</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td class="text-muted small">Oda-Gece Başına</td>
                                <td class="fw-bold text-end">{{ number_format($carbon->co2_per_room_night, 3) }} kg</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Misafir Başına</td>
                                <td class="fw-bold text-end">{{ number_format($carbon->co2_per_guest, 3) }} kg</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">m² Başına</td>
                                <td class="fw-bold text-end">{{ number_format($carbon->co2_per_sqm, 3) }} kg</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Personel Başına</td>
                                <td class="fw-bold text-end">{{ number_format($carbon->co2_per_staff, 3) }} kg</td>
                            </tr>
                            <tr class="table-light">
                                <td class="text-muted small">Su Yoğunluğu</td>
                                <td class="fw-bold text-end">{{ number_format($carbon->water_intensity, 3) }} m³/oda</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Yenilenebilir Enerji</td>
                                <td class="fw-bold text-end text-success">%{{ number_format($carbon->renewable_energy_pct, 1) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Atık Geri Dönüşüm</td>
                                <td class="fw-bold text-end">%{{ number_format($carbon->waste_recycling_rate, 1) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
