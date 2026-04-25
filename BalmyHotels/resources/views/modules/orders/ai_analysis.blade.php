@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="d-flex align-items-center gap-2">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:700;flex-shrink:0;">AI</span>
                    Stratejik Sipariş Analizi
                </h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex align-items-center">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.take') }}">Sipariş</a></li>
                <li class="breadcrumb-item active">Stratejik Analiz</li>
            </ol>
        </div>
    </div>

    {{-- Filtre --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('orders.ai-analysis') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Restoran</label>
                    <select name="restaurant_id" class="form-select form-select-sm">
                        <option value="">— Tüm Restoranlar —</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" @selected($restaurantId == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>
                        Analiz Et
                    </button>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(7)">Son 7 Gün</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(30)">Son 30</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setRange(90)">Son 90</button>
                </div>
            </form>
        </div>
    </div>

    @if($paidRevenue == 0 && $totalSessions == 0)
    <div class="alert alert-info">
        Seçilen tarih aralığında kayıtlı veri bulunamadı. Lütfen farklı bir dönem seçin.
    </div>
    @else

    {{-- ░░ RAPOR BAŞLIĞI ░░ --}}
    <div class="ai-report-wrapper" style="font-family:'Segoe UI',system-ui,sans-serif;color:#1e293b;">

        {{-- Rapor Kapak Bandı --}}
        <div class="card mb-4 border-0 overflow-hidden" style="background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);">
            <div class="card-body py-4 px-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div style="width:3px;height:40px;background:linear-gradient(#a78bfa,#818cf8);border-radius:2px;"></div>
                            <div>
                                <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;opacity:.65;margin-bottom:2px;">Yapay Zeka Destekli Stratejik Analiz Raporu</div>
                                <div style="font-size:22px;font-weight:700;letter-spacing:-0.3px;">
                                    {{ $restaurantId ? $restaurants->find($restaurantId)->name : 'Tüm Restoranlar' }}
                                </div>
                            </div>
                        </div>
                        <div style="font-size:13px;opacity:.7;">
                            Analiz Dönemi: <strong style="opacity:1;">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
                            &nbsp;·&nbsp; {{ $dayCount }} günlük dönem &nbsp;·&nbsp; {{ $activeDays }} aktif iş günü
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div style="font-size:11px;opacity:.5;letter-spacing:1px;text-transform:uppercase;">Toplam Hasılat</div>
                        <div style="font-size:36px;font-weight:800;letter-spacing:-1px;">{{ number_format($paidRevenue, 2, ',', '.') }}</div>
                        <div style="font-size:13px;opacity:.6;">TL</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BÖLÜM 1: YÖNETİCİ ÖZETİ ──────────────────────────────────────── --}}
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#6366f1;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">01</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Yönetici Özeti</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $revenueLevel = $paidRevenue > 100000 ? 'güçlü' : ($paidRevenue > 30000 ? 'orta düzeyde' : 'sınırlı');
                        $growthText   = is_null($weeklyGrowth) ? 'hesaplanamadı' : (
                            $weeklyGrowth > 5  ? 'belirgin büyüme (+'.abs($weeklyGrowth).'%)' :
                            ($weeklyGrowth > 0  ? 'hafif büyüme (+'.abs($weeklyGrowth).'%)' :
                            ($weeklyGrowth == 0 ? 'yatay seyir' :
                            'gerileme (-'.abs($weeklyGrowth).'%)')));
                        $sessionDensity = $activeDays > 0 ? round($totalSessions / $activeDays, 1) : 0;
                        $orderDensity   = $activeDays > 0 ? round($totalOrders / $activeDays, 1) : 0;
                    @endphp
                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} ile {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }} tarihleri arasında kapsanan
                        <strong>{{ $dayCount }}-günlük dönemde</strong>, analiz kapsamındaki operasyon
                        <strong class="text-primary">{{ number_format($paidRevenue, 2, ',', '.') }} TL</strong> ücretli hasılat kaydetti.
                        Bu gelir, toplam <strong>{{ number_format($totalSessions, 0, ',', '.') }} masa oturumu</strong> ve
                        <strong>{{ number_format($totalOrders, 0, ',', '.') }} sipariş işlemi</strong> üzerinden gerçekleşti.
                        Operasyon, dönem boyunca <strong>{{ $activeDays }} aktif iş günü</strong> ile
                        @if($activeDays < $dayCount)
                            potansiyel {{ $dayCount - $activeDays }} günlük kesinti veya kapanış kaydetti —
                            bu durum dikkat gerektiren operasyonel bir veri noktasıdır.
                        @else
                            {{ $dayCount }} günün tamamında kesintisiz devam etti.
                        @endif
                    </p>
                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Günlük ortalama hasılat <strong>{{ number_format($avgDailyRev, 2, ',', '.') }} TL</strong> olarak gerçekleşirken,
                        masa başına ortalama harcama <strong>{{ number_format($avgSessionRev, 2, ',', '.') }} TL</strong> seviyesinde kaldı.
                        Sipariş başına ortalama <strong>{{ $avgQtyPerOrder }} kalem</strong> ürün düşmekte olup bu değer,
                        @if($avgQtyPerOrder >= 5)
                            misafir başına çoklu ürün tercihinin güçlü bir biçimde yerleştiğine işaret etmektedir.
                        @elseif($avgQtyPerOrder >= 3)
                            operasyon için sağlıklı bir sipariş derinliğini temsil etmektedir.
                        @else
                            sipariş derinliğini artırmaya yönelik müdahalelerin değerlendirilebileceğine işaret etmektedir.
                        @endif
                        @if(!is_null($weeklyGrowth))
                            Son 7 günlük periyot, önceki haftayla karşılaştırıldığında
                            <strong @class(['text-success' => $weeklyGrowth > 0, 'text-danger' => $weeklyGrowth < 0])>{{ $growthText }}</strong>
                            trendi ortaya koymaktadır.
                        @endif
                    </p>

                    {{-- KPI Kartları --}}
                    <div class="row g-3 mt-1">
                        @php
                            $kpis = [
                                ['label' => 'Toplam Hasılat',        'val' => number_format($paidRevenue,2,',','.').' TL',          'icon' => '₺', 'color' => '#6366f1'],
                                ['label' => 'Masa Oturumu',          'val' => number_format($totalSessions,0,',','.'),              'icon' => '⬜','color' => '#8b5cf6'],
                                ['label' => 'Sipariş Sayısı',        'val' => number_format($totalOrders,0,',','.'),                'icon' => '📋','color' => '#0ea5e9'],
                                ['label' => 'Satılan Kalem',         'val' => number_format($totalQty,0,',','.'),                   'icon' => '🍽','color' => '#10b981'],
                                ['label' => 'Günlük Ort. Hasılat',   'val' => number_format($avgDailyRev,2,',','.').' TL',          'icon' => '📈','color' => '#f59e0b'],
                                ['label' => 'Masa Başı Harcama',     'val' => number_format($avgSessionRev,2,',','.').' TL',        'icon' => '💰','color' => '#ef4444'],
                                ['label' => 'Günlük Ort. Sipariş',   'val' => number_format($avgOrdersPerDay,1,',','.'),            'icon' => '📊','color' => '#14b8a6'],
                                ['label' => 'Sipariş/Kalem Ort.',    'val' => $avgQtyPerOrder,                                      'icon' => '🔢','color' => '#a855f7'],
                            ];
                        @endphp
                        @foreach($kpis as $kpi)
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border-left:3px solid {{ $kpi['color'] }};">
                                <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">{{ $kpi['label'] }}</div>
                                <div style="font-size:18px;font-weight:700;color:{{ $kpi['color'] }};">{{ $kpi['val'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BÖLÜM 2: RESTORAN PERFORMANS ANALİZİ ─────────────────────────── --}}
        @if($restoStats->count() > 0)
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#0ea5e9;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">02</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Restoran Performans Analizi</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $topRevShare = ($paidRevenue > 0 && $topResto) ? round($topResto->revenue / $paidRevenue * 100, 1) : 0;
                        $bottomRevShare = ($paidRevenue > 0 && $bottomResto && $bottomResto->restaurant_id != ($topResto->restaurant_id ?? null)) ? round($bottomResto->revenue / $paidRevenue * 100, 1) : 0;
                    @endphp

                    @if($restoStats->count() == 1)
                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Analiz, tek restoran perspektifinde <strong>{{ $restoStats->first()->restaurant_name }}</strong> üzerine yapılmaktadır.
                        Dönem boyunca bu lokasyon <strong>{{ number_format($restoStats->first()->revenue, 2, ',', '.') }} TL</strong> hasılat ile
                        <strong>{{ number_format($restoStats->first()->sessions, 0, ',', '.') }}</strong> masa oturumu gerçekleştirdi.
                        Günlük ortalama hasılat <strong>{{ $restoStats->first()->active_days > 0 ? number_format($restoStats->first()->revenue / $restoStats->first()->active_days, 2, ',', '.') : '0' }} TL</strong>
                        olarak hesaplanmaktadır.
                    </p>
                    @else
                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Toplam <strong>{{ $restoStats->count() }} lokasyon</strong> analiz kapsamındadır.
                        @if($topResto)
                        <strong>{{ $topResto->restaurant_name }}</strong>, <strong>{{ number_format($topResto->revenue, 2, ',', '.') }} TL</strong> hasılat ile
                        toplam gelirin <strong class="text-primary">%{{ $topRevShare }}</strong>'ini tek başına üstlenerek operasyonun açık ara lider lokasyonu konumundadır.
                        @endif
                        @if($bottomResto && $bottomResto->restaurant_id !== ($topResto->restaurant_id ?? null))
                        Buna karşın <strong>{{ $bottomResto->restaurant_name }}</strong>,
                        <strong>{{ number_format($bottomResto->revenue, 2, ',', '.') }} TL</strong> hasılat ile toplam pastanın
                        yalnızca <strong>%{{ $bottomRevShare }}</strong>'ini oluşturmakta; bu lokasyonun kapasite kullanımı, ürün çeşitliliği ve teşvik mekanizmaları açısından kapsamlı biçimde gözden geçirilmesi stratejik bir öncelik taşımaktadır.
                        @endif
                    </p>
                    @endif

                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        @if($restoStats->count() > 1)
                        @php
                            $avgRestoRev = $restoStats->count() > 0 ? round($restoStats->avg('revenue'), 2) : 0;
                            $above = $restoStats->filter(fn($r) => $r->revenue > $avgRestoRev)->count();
                            $below = $restoStats->filter(fn($r) => $r->revenue < $avgRestoRev)->count();
                        @endphp
                        Portföy genelinde lokasyon başına ortalama hasılat <strong>{{ number_format($avgRestoRev, 2, ',', '.') }} TL</strong> olarak hesaplanmaktadır.
                        {{ $above }} lokasyon bu ortalamanın üzerinde, {{ $below }} lokasyon ise ortalamanın altında seyretti.
                        Lokasyonlar arası bu hasılat makasının daralması, zincir performansının homojenleşmesi açısından kritik bir hedef olarak değerlendirilmelidir.
                        @else
                        Dönem boyunca tek lokasyon, {{ $activeDays }} aktif günde minimum {{ $activeDays > 0 ? number_format($paidRevenue / $activeDays, 2, ',', '.') : 0 }} TL/gün hasılat üretmiştir.
                        @endif
                    </p>

                    {{-- Restoran tablosu --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle" style="font-size:13px;">
                            <thead style="background:#f1f5f9;">
                                <tr>
                                    <th style="font-weight:600;color:#475569;">Restoran</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Hasılat</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Pay %</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Oturum</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Sipariş</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Masa Başı</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Aktif Gün</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restoStats as $rs)
                                @php
                                    $share = $paidRevenue > 0 ? round($rs->revenue / $paidRevenue * 100, 1) : 0;
                                    $perSession = $rs->sessions > 0 ? round($rs->revenue / $rs->sessions, 2) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold" style="color:#1e293b;">{{ $rs->restaurant_name }}</div>
                                        <div style="width:{{ max(2,(int)$share) }}%;height:3px;background:linear-gradient(to right,#6366f1,#a78bfa);border-radius:2px;margin-top:4px;"></div>
                                    </td>
                                    <td class="text-end fw-semibold" style="color:#15803d;">{{ number_format($rs->revenue,2,',','.') }} ₺</td>
                                    <td class="text-end" style="color:#475569;">%{{ $share }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($rs->sessions,0,',','.') }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($rs->orders,0,',','.') }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($perSession,2,',','.') }} ₺</td>
                                    <td class="text-end" style="color:#475569;">{{ $rs->active_days }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── BÖLÜM 3: ÜRÜN PORTFÖYü ANALİZİ ─────────────────────────────── --}}
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#10b981;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">03</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Ürün Portföyü ve Menü Performansı</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $topProduct    = $topPaidProducts->first();
                        $top3Revenue   = $topPaidProducts->take(3)->sum('revenue');
                        $top3Share     = $paidRevenue > 0 ? round($top3Revenue / $paidRevenue * 100, 1) : 0;
                        $top5Revenue   = $topPaidProducts->take(5)->sum('revenue');
                        $top5Share     = $paidRevenue > 0 ? round($top5Revenue / $paidRevenue * 100, 1) : 0;
                        $freeRatio     = $totalQty > 0 ? round($freeQtyTotal / $totalQty * 100, 1) : 0;
                    @endphp

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Analiz dönemi boyunca ücretli menüde toplam <strong>{{ $distinctPaidItems }} farklı kalem</strong>,
                        ücretsiz/ikram kategorisinde ise <strong>{{ $distinctFreeItems }} farklı ürün</strong> servis edilmiştir.
                        @if($topProduct)
                        Ücretli menünün en yüksek hasılat yaratan ürünü, toplam
                        <strong>{{ number_format($topProduct->revenue, 2, ',', '.') }} TL</strong> geliriyle
                        <strong class="text-primary">{{ $topProduct->item_name }}</strong>'dır.
                        @endif
                    </p>

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        @if($topPaidProducts->count() >= 3)
                        İlk 3 ürün, toplam hasılatın <strong>%{{ $top3Share }}</strong>'ini oluşturmakta; ilk 5 ürün ise
                        <strong>%{{ $top5Share }}</strong>'lik hasılat yoğunlaşması sergilemektedir.
                        @if($top5Share > 60)
                        Bu oran, menünün <em>kripto-ürün yoğunlaşması</em> yaşadığına — yani gelirlerin dar bir ürün yelpazesine aşırı bağımlı olduğuna — işaret eder.
                        Menü genişletme ve alternatif akış ürünleri geliştirme stratejik öncelik taşımalıdır.
                        @elseif($top5Share > 40)
                        Bu sağlıklı bir konsantrasyon seviyesini temsil eder; portföy genişliği korunmalı ve alt-listedeki ürünler teşvik edilmelidir.
                        @else
                        Bu, menü ürünleri arasında sağlıklı bir dağılımı işaret etmektedir.
                        @endif
                        @endif
                    </p>

                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        Toplam servis edilen kayıt içinde ikram ve ücretsiz kalemler
                        <strong>%{{ $freeQtyPct }}</strong>'lik bir yer tutmaktadır.
                        @if($freeRatio > 30)
                        Bu oran, operasyonel maliyet yönetimi açısından dikkatle izlenmeli; ikram politikalarının gözden geçirilmesi ve sınırlandırılması değerlendirilmelidir.
                        @elseif($freeRatio > 15)
                        Bu oran misafir memnuniyeti açısından olumlu katkı sunabilmekle birlikte, kontrollü tutulması gerekmektedir.
                        @else
                        Bu oran, ikram dengesinin sağlıklı biçimde yönetildiğini göstermektedir.
                        @endif
                    </p>

                    <div class="row g-4">
                        {{-- Top 10 Ücretli Ürün --}}
                        <div class="col-md-6">
                            <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">⭐ En Yüksek Hasılatlı Ürünler</div>
                            @php $maxRev = $topPaidProducts->max('revenue') ?: 1; @endphp
                            @foreach($topPaidProducts->take(10) as $i => $prod)
                            @php $barW = $maxRev > 0 ? (int)(($prod->revenue / $maxRev) * 100) : 0; @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:12.5px;color:#1e293b;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $prod->item_name }}">
                                        <span style="color:#94a3b8;font-size:11px;margin-right:4px;">{{ $i+1 }}.</span>{{ $prod->item_name }}
                                    </span>
                                    <span style="font-size:12px;font-weight:600;color:#15803d;">{{ number_format($prod->revenue,2,',','.') }} ₺</span>
                                </div>
                                <div style="height:4px;background:#f1f5f9;border-radius:2px;">
                                    <div style="width:{{ $barW }}%;height:100%;background:linear-gradient(to right,#10b981,#34d399);border-radius:2px;"></div>
                                </div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ number_format($prod->qty,0,',','.') }} adet · Ort. {{ number_format($prod->avg_price,2,',','.') }} ₺</div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Premium Ürünler (yüksek fiyat) --}}
                        <div class="col-md-6">
                            <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">💎 En Yüksek Ortalama Fiyatlı Ürünler</div>
                            @php $maxPrice = $premiumProducts->max('avg_price') ?: 1; @endphp
                            @foreach($premiumProducts->take(10) as $i => $prod)
                            @php $barW = $maxPrice > 0 ? (int)(($prod->avg_price / $maxPrice) * 100) : 0; @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:12.5px;color:#1e293b;max-width:65%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $prod->item_name }}">
                                        <span style="color:#94a3b8;font-size:11px;margin-right:4px;">{{ $i+1 }}.</span>{{ $prod->item_name }}
                                    </span>
                                    <span style="font-size:12px;font-weight:600;color:#7c3aed;">{{ number_format($prod->avg_price,2,',','.') }} ₺</span>
                                </div>
                                <div style="height:4px;background:#f1f5f9;border-radius:2px;">
                                    <div style="width:{{ $barW }}%;height:100%;background:linear-gradient(to right,#8b5cf6,#a78bfa);border-radius:2px;"></div>
                                </div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ number_format($prod->qty,0,',','.') }} adet satıldı</div>
                            </div>
                            @endforeach

                            @if($topFreeProducts->count() > 0)
                            <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-top:20px;margin-bottom:12px;">🎁 En Çok Servis Edilen İkramlar</div>
                            @foreach($topFreeProducts->take(5) as $i => $prod)
                            <div class="d-flex justify-content-between mb-1" style="font-size:12.5px;">
                                <span style="color:#1e293b;max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $prod->item_name }}">
                                    <span style="color:#94a3b8;font-size:11px;margin-right:4px;">{{ $i+1 }}.</span>{{ $prod->item_name }}
                                </span>
                                <span style="color:#f59e0b;font-weight:600;">{{ number_format($prod->qty,0,',','.') }} adet</span>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BÖLÜM 4: OPERASYONEL ZAMAN ANALİZİ ──────────────────────────── --}}
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#f59e0b;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">04</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Operasyonel Zaman ve Yoğunluk Analizi</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $dominantPeriod = collect([
                            'Sabah (06–11)' => $morningPct,
                            'Öğlen (12–14)' => $lunchPct,
                            'Öğleden Sonra (15–17)' => $afternoonPct,
                            'Akşam (18–23)' => $eveningPct,
                        ])->sortDesc()->keys()->first();
                    @endphp

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Sipariş trafiği incelendiğinde, operasyonun <strong>{{ $peakHour }}:00</strong> saatinde günün en yoğun noktasına ulaştığı görülmektedir.
                        Bu saatte kayıtlı sipariş hacmi <strong>{{ number_format($peakHourCnt, 0, ',', '.') }}</strong>'e erişmiştir.
                        Gün içi zaman dilimlerine göre dağılım, <strong class="text-warning">{{ $dominantPeriod }}</strong> periyodunun toplam trafiğin
                        önemli bir bölümünü barındırdığını ortaya koymaktadır.
                    </p>

                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        Periyot analizi: sabah trafiği toplam siparişin <strong>%{{ $morningPct }}</strong>'ine,
                        öğlen servisi <strong>%{{ $lunchPct }}</strong>'ine, öğleden sonra <strong>%{{ $afternoonPct }}</strong>'ini,
                        akşam servisi ise <strong>%{{ $eveningPct }}</strong>'ini oluşturmaktadır.
                        @if($eveningPct > 50)
                        Operasyon ağırlıklı olarak <em>akşam ekonomisine</em> dayalı bir yapı sergilemekte; gün boyu gelir dengesini sağlamak için öğle saatlerinde kampanya ve çekici tekliflerin devreye alınması güçlü bir strateji olabilir.
                        @elseif($lunchPct > 40)
                        Operasyon, öğle hizmetine olan güçlü yatkınlığı ile değerlendirilir; akşam trafiğinin güçlendirilmesi hasılat tavanını yükseltecektir.
                        @else
                        Gün içi dağılımın görece dengeli olması, esneklik açısından olumlu; ancak belirgin bir zirve saati oluşturulamaması için menü zamanlaması ve promosyon takvimlerinin gözden geçirilmesi önerilir.
                        @endif
                    </p>

                    {{-- Saatlik görsel --}}
                    <div class="mb-4">
                        <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">📊 Saatlik Sipariş Dağılımı</div>
                        @php $maxHourlyCnt = $hourlyData->max() ?: 1; @endphp
                        <div class="d-flex align-items-end gap-1" style="height:60px;">
                            @for($h = 0; $h <= 23; $h++)
                            @php
                                $cnt    = $hourlyData->get($h, 0);
                                $hpct   = $maxHourlyCnt > 0 ? (int)(($cnt / $maxHourlyCnt) * 100) : 0;
                                $isPeak = $h == $peakHour;
                            @endphp
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;">
                                <div title="{{ $h }}:00 — {{ $cnt }} sipariş"
                                     style="width:100%;background:{{ $isPeak ? '#f59e0b' : '#6366f1' }};height:{{ max(4, (int)(($cnt / $maxHourlyCnt) * 58)) }}px;border-radius:2px 2px 0 0;opacity:{{ $cnt == 0 ? '.2' : '1' }};transition:height .3s;cursor:default;"></div>
                                <div style="font-size:9px;color:#94a3b8;">{{ $h }}</div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Haftalık dağılım --}}
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">📅 Hafta İçi / Hafta Sonu Dağılımı</div>
                        @php
                            $wkTotal = $weekdayData->sum() ?: 1;
                            $weekdayColors = ['Pazartesi'=>'#6366f1','Salı'=>'#8b5cf6','Çarşamba'=>'#0ea5e9','Perşembe'=>'#10b981','Cuma'=>'#f59e0b','Cumartesi'=>'#ef4444','Pazar'=>'#ec4899'];
                        @endphp
                        <div class="row g-2">
                            @foreach($weekdayData as $day => $cnt)
                            @php $pct = $wkTotal > 0 ? round($cnt / $wkTotal * 100, 1) : 0; $color = $weekdayColors[$day] ?? '#6366f1'; @endphp
                            <div class="col">
                                <div class="text-center" style="font-size:11px;color:#64748b;margin-bottom:4px;">{{ mb_substr($day,0,3,'UTF-8') }}</div>
                                <div style="height:50px;background:#f1f5f9;border-radius:4px;position:relative;overflow:hidden;">
                                    <div style="width:100%;height:{{ max(4,(int)($pct/100*50)) }}px;background:{{ $color }};position:absolute;bottom:0;left:0;border-radius:4px 4px 0 0;opacity:.85;"></div>
                                </div>
                                <div class="text-center" style="font-size:10px;font-weight:600;color:{{ $color }};margin-top:2px;">%{{ $pct }}</div>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-3 mb-0" style="font-size:13.5px;color:#475569;">
                            Haftanın en yoğun günü <strong>{{ $busiestDay }}</strong> olarak tespit edilmiştir.
                            @if($quietestDay && $quietestDay !== '—')
                                En düşük aktivite ise <strong>{{ $quietestDay }}</strong> gününde gözlemlenmiştir.
                                Bu günlerin müdahale odaklı bir perspektifle değerlendirilmesi, toplam haftalık hasılatı yukarı çekme fırsatı sunabilir.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BÖLÜM 5: MİSAFİR BEKLEME VE KALMA SÜRELERİ ────────────────── --}}
        @if($sessionDurRaw->count() > 0)
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#14b8a6;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">05</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Misafir Kalma Süresi ve Masa Verimliliği</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $avgDurH   = intdiv((int)$overallAvgDur, 60);
                        $avgDurM   = (int)$overallAvgDur % 60;
                        $durLabel  = ($avgDurH > 0 ? $avgDurH.'s ' : '') . $avgDurM . 'dk';

                        $durComment = $overallAvgDur > 120 ? 'çok uzun (>2 saat)' :
                                      ($overallAvgDur > 75  ? 'uzun (>1,5 saat)' :
                                      ($overallAvgDur > 45  ? 'orta (45–75 dk)' :
                                      ($overallAvgDur > 20  ? 'kısa-orta (20–45 dk)' : 'kısa (<20 dk)')));

                        $durStrategy = $overallAvgDur > 90 ?
                            'Bu oran masa devir hızını düşürmekte; yoğun dönemlerde kapasite kısıtına yol açabilir. Verimli servis süreç optimizasyonu ile süreli menü teşvikleri değerlendirilmelidir.' :
                            ($overallAvgDur > 50 ?
                            'Bu süre, rahat bir yemek deneyimi ile operasyonel verimlilik arasındaki optimum bölgeye denk gelmektedir.' :
                            'Hızlı servis profili, yüksek hacimli operasyon için avantaj sunar; misafir deneyimi derinleştirilebilir.');
                    @endphp

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Kapalı masa oturumları üzerinden hesaplanan genel ortalama masa süresi <strong>{{ $durLabel }}</strong>
                        olarak tespit edilmiştir — bu, <em>{{ $durComment }}</em> kategorisine karşılık gelmektedir.
                        {{ $durStrategy }}
                    </p>

                    @if($longestStayResto && $sessionDurRaw->count() > 1)
                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        En yüksek ortalama masa süresi <strong>{{ $longestStayResto->restaurant_name }}</strong>'da
                        (<strong>{{ round($longestStayResto->avg_min, 1) }} dk</strong>) gözlemlenmiştir.
                        @if($shortestStayResto && $shortestStayResto->restaurant_id !== $longestStayResto->restaurant_id)
                        Buna karşın en hızlı sirkülasyon <strong>{{ $shortestStayResto->restaurant_name }}</strong>'da
                        (<strong>{{ round($shortestStayResto->avg_min, 1) }} dk</strong>) gerçekleşmiştir.
                        Bu iki lokasyon arasındaki süre farkı, hizmet konsepti ve müşteri profilindeki farklılaşmanın somut bir yansımasıdır.
                        @endif
                    </p>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle" style="font-size:13px;">
                            <thead style="background:#f1f5f9;">
                                <tr>
                                    <th style="font-weight:600;color:#475569;">Restoran</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Ort. Süre</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Min.</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Maks.</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Oturum Sayısı</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessionDurRaw as $sd)
                                @php
                                    $sdH = intdiv((int)$sd->avg_min, 60);
                                    $sdM = (int)$sd->avg_min % 60;
                                @endphp
                                <tr>
                                    <td style="font-weight:500;color:#1e293b;">{{ $sd->restaurant_name }}</td>
                                    <td class="text-end" style="font-weight:600;color:#0f766e;">{{ ($sdH > 0 ? $sdH.'s ' : '') }}{{ $sdM }}dk</td>
                                    <td class="text-end" style="color:#475569;">{{ (int)$sd->min_min }}dk</td>
                                    <td class="text-end" style="color:#475569;">{{ (int)$sd->max_min }}dk</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($sd->session_count,0,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── BÖLÜM 6: PERSONEL / GARSON PERFORMANSI ─────────────────────── --}}
        @if($waiterStats->count() > 0)
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#ec4899;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">06</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Servis Personeli Performans Analizi</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $topWaiterShare = ($totalOrders > 0 && $topWaiter) ? round($topWaiter->order_count / $totalOrders * 100, 1) : 0;
                        $topWaiterRevShare = ($paidRevenue > 0 && $topWaiter) ? round($topWaiter->revenue / $paidRevenue * 100, 1) : 0;
                    @endphp

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        Analiz döneminde <strong>{{ $waiterCount }} servis personeli</strong> aktif olarak görev almıştır.
                        @if($topWaiter)
                        <strong>{{ $topWaiter->waiter_name }}</strong>, toplam <strong>{{ number_format($topWaiter->order_count, 0, ',', '.') }} sipariş</strong>
                        ve <strong>{{ number_format($topWaiter->revenue, 2, ',', '.') }} TL</strong> hasılatla lider konumundadır.
                        Bu, toplam siparişlerin <strong>%{{ $topWaiterShare }}</strong>'ini ve toplam hasılatın
                        <strong>%{{ $topWaiterRevShare }}</strong>'ini tek bir çalışanın üstlendiğini göstermektedir.
                        @endif
                    </p>

                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        Bir çalışan başına ortalama <strong>{{ $avgOrdersPerWaiter }} sipariş</strong> düşmektedir.
                        @if($topWaiter && $topWaiter->order_count > $avgOrdersPerWaiter * 2)
                        Lider çalışanın sipariş yükü, ortalamayı aşırı biçimde geçmektedir — bu durum iş yükü dengesizliğine ve
                        uzun vadede hizmet kalitesi riskine yol açabilecek bir tablo ortaya koymaktadır.
                        Görev dağılımının yeniden değerlendirilmesi önerilir.
                        @else
                        Çalışanlar arasındaki yük dağılımı genel olarak dengeli bir tablo sergilemektedir.
                        @endif
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle" style="font-size:13px;">
                            <thead style="background:#f1f5f9;">
                                <tr>
                                    <th style="font-weight:600;color:#475569;">Personel</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Sipariş</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Kalem</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Oturum</th>
                                    <th class="text-end" style="font-weight:600;color:#475569;">Hasılat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($waiterStats as $w)
                                <tr>
                                    <td style="font-weight:500;color:#1e293b;">{{ $w->waiter_name }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($w->order_count,0,',','.') }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($w->item_qty,0,',','.') }}</td>
                                    <td class="text-end" style="color:#475569;">{{ number_format($w->session_count,0,',','.') }}</td>
                                    <td class="text-end fw-semibold" style="color:#15803d;">{{ number_format($w->revenue,2,',','.') }} ₺</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── BÖLÜM 7: GELİR TRENDİ VE BÜYÜME ANALİZİ ───────────────────── --}}
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#f97316;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">07</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Gelir Trendi ve Büyüme Analizi</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $peakDayFormatted = $peakDayDate ? \Carbon\Carbon::parse($peakDayDate)->format('d M Y, l') : '—';
                        $zeroRevDays = $dayCount - $nonZeroDays->count();
                    @endphp

                    <p class="lh-lg mb-3" style="font-size:14.5px;color:#334155;">
                        @if($peakDayDate)
                        Dönemin en yüksek hasılat günü <strong>{{ $peakDayFormatted }}</strong> tarihi olup bu günde
                        <strong>{{ number_format($peakDayRev, 2, ',', '.') }} TL</strong> gelir elde edilmiştir.
                        @endif
                        @if($zeroRevDays > 0)
                        Dönem içinde <strong>{{ $zeroRevDays }} gün</strong> sıfır hasılatla kapanmıştır;
                        bu günler kapalı operasyon veya veri eksikliği kaynaklı olabilir ve işletme sürekliliği açısından gözden geçirilmesi gerekmektedir.
                        @endif
                    </p>

                    @if(!is_null($weeklyGrowth))
                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        Son 7 günlük periyot (<strong>{{ number_format($week2Rev, 2, ',', '.') }} TL</strong>), bir önceki 7 günle
                        (<strong>{{ number_format($week1Rev, 2, ',', '.') }} TL</strong>) kıyaslandığında
                        <strong @class(['text-success' => $weeklyGrowth >= 0, 'text-danger' => $weeklyGrowth < 0])>
                            {{ $weeklyGrowth >= 0 ? '+' : '' }}{{ $weeklyGrowth }}% haftalık değişim
                        </strong> ortaya çıkmaktadır.
                        @if($weeklyGrowth > 10)
                            Bu güçlü ivme, operasyonun ivmeli büyüme dönemine girdiğine işaret etmektedir.
                        @elseif($weeklyGrowth > 0)
                            Hafif pozitif ivme, istikrarlı büyümenin devam ettiğini göstermektedir.
                        @elseif($weeklyGrowth == 0)
                            Yatay hareket, operasyonun bir plato noktasına ulaştığını düşündürmektedir.
                        @else
                            Negatif ivme, dikkate alınması ve kökenlerinin araştırılması gereken bir trendi işaret etmektedir.
                        @endif
                    </p>
                    @endif

                    {{-- Günlük trend mini-chart --}}
                    @if($dailyRevRaw->count() > 0)
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">📈 Günlük Hasılat Trendi</div>
                        @php $maxDRev = $dailyRevRaw->max() ?: 1; $drVals = $dailyRevRaw->values()->toArray(); $drCount = count($drVals); @endphp
                        <div class="d-flex align-items-end gap-px" style="height:70px;gap:1px;">
                            @foreach($dailyRevRaw as $day => $rev)
                            @php
                                $barH  = $maxDRev > 0 ? max(3, (int)(($rev / $maxDRev) * 68)) : 3;
                                $isPeak = $day === $peakDayDate;
                            @endphp
                            <div title="{{ \Carbon\Carbon::parse($day)->format('d M') }}: {{ number_format($rev,2,',','.') }} ₺"
                                 style="flex:1;background:{{ $isPeak ? '#f97316' : ($rev > 0 ? '#6366f1' : '#e2e8f0') }};height:{{ $barH }}px;border-radius:2px 2px 0 0;min-width:2px;"
                                 class="align-self-end"></div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── BÖLÜM 8: STRATEJİK ÖNERİLER ─────────────────────────────────── --}}
        <div class="ai-section mb-4">
            <div class="section-header d-flex align-items-center gap-2 mb-3">
                <div class="section-num" style="width:28px;height:28px;border-radius:50%;background:#dc2626;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">08</div>
                <h5 class="mb-0 fw-bold" style="font-size:16px;color:#1e293b;">Stratejik Öneriler ve Aksiyon Planı</h5>
                <div style="flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent);margin-left:8px;"></div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $recommendations = [];

                        // Büyüme ivmesi
                        if(!is_null($weeklyGrowth) && $weeklyGrowth < 0)
                            $recommendations[] = ['priority' => 'Yüksek', 'color' => '#dc2626', 'text' => 'Son hafta hasılatında '.abs($weeklyGrowth).'\'lik gerileme tespit edilmiştir. Sebeplerin ivedilikle araştırılması; kampanya, promosyon veya ürün öne çıkarma aktivasyonlarının değerlendirilmesi önerilir.'];

                        // Lokasyon dengesi
                        if($restoStats->count() > 1 && $bottomResto && $topResto && $topResto->revenue > 0 && ($bottomResto->revenue / $topResto->revenue) < 0.3)
                            $recommendations[] = ['priority' => 'Yüksek', 'color' => '#dc2626', 'text' => $bottomResto->restaurant_name.' lokasyonunun hasılatı, lider lokasyonun %'.round($bottomResto->revenue/$topResto->revenue*100, 1).'\'i seviyesinde kalmaktadır. Operasyonel dönüşüm veya konsept revizyonu acilen değerlendirilmelidir.'];

                        // İkram oranı
                        if($freeQtyPct > 25)
                            $recommendations[] = ['priority' => 'Orta', 'color' => '#d97706', 'text' => 'Ücretsiz/ikram kalemlerin toplam servis içindeki oranı %'.$freeQtyPct.' olarak ölçülmüştür. İkram politikası standartlaştırılmalı ve onay mekanizmaları güçlendirilmelidir.'];

                        // Ürün yoğunlaşması
                        if($top5Share > 60 && $topPaidProducts->count() >= 5)
                            $recommendations[] = ['priority' => 'Orta', 'color' => '#d97706', 'text' => 'Hasılatın %'.$top5Share.'\'i yalnızca 5 üründen kaynaklanmaktadır. Menü çeşitliliği artırılmalı; alternatif ürünler için çapraz satış mekanizmaları devreye alınmalıdır.'];

                        // Masa süresi uzunsa
                        if($overallAvgDur > 90)
                            $recommendations[] = ['priority' => 'Orta', 'color' => '#d97706', 'text' => 'Ortalama masa süresi '.round($overallAvgDur).' dakikayı aşmaktadır. Rezervasyon sistemi, aktif masa yönetimi ve servis süreç optimizasyonu devreye alınarak masa devir hızı artırılabilir.'];

                        // Düşük aktif gün oranı
                        if($activeDays < $dayCount * 0.7)
                            $recommendations[] = ['priority' => 'Orta', 'color' => '#d97706', 'text' => $dayCount.' günlük dönemde yalnızca '.$activeDays.' gün aktif operasyon kaydedilmiştir. Kapanış nedenlerinin analiz edilmesi ve iş sürekliliği planlamasının güçlendirilmesi önerilir.'];

                        // Garson yük dengesizliği
                        if($topWaiter && $waiterCount > 1 && $topWaiter->order_count > $avgOrdersPerWaiter * 2.5)
                            $recommendations[] = ['priority' => 'Düşük', 'color' => '#2563eb', 'text' => $topWaiter->waiter_name.' adlı personel, ortalama yükün 2,5 katı üzerinde sipariş almaktadır. Görev dağılımı gözden geçirilmeli; uzun vadeli hizmet kalitesi riskinden kaçınılmalıdır.'];

                        // Saatlik boşluklar
                        $lowHourCount = $hourlyData->filter(fn($v) => $v == 0)->count();
                        if($lowHourCount > 10)
                            $recommendations[] = ['priority' => 'Düşük', 'color' => '#2563eb', 'text' => 'Günün büyük bölümünde sipariş aktivitesi sıfır olarak ölçülmüştür. Off-peak saatlerde hedefli promosyonlar, set menüler veya özel aktivasyonlar değerlendirilebilir.'];

                        // Haftalık büyüme pozitifse — olumlu öneri
                        if(!is_null($weeklyGrowth) && $weeklyGrowth > 10)
                            $recommendations[] = ['priority' => 'Fırsat', 'color' => '#15803d', 'text' => 'Son hafta güçlü büyüme ivmesi (+%'.$weeklyGrowth.') gözlemlenmiştir. Bu momentumu sürdürmek için mevcut stratejik kararlar hızlandırılmalı; başarılı ürün ve servis pratikleri standarta dönüştürülmelidir.'];

                        if(empty($recommendations))
                            $recommendations[] = ['priority' => 'Genel', 'color' => '#15803d', 'text' => 'Analiz edilen dönemde kritik bir sapma veya acil müdahale gerektiren bir alan tespit edilmemiştir. Mevcut operasyonel standartların korunması ve menü yenileme döngülerinin düzenli tutulması önerilir.'];
                    @endphp

                    <p class="lh-lg mb-4" style="font-size:14.5px;color:#334155;">
                        Aşağıdaki stratejik öneri seti, analiz dönemi verisinden elde edilen bulgulara dayanılarak üretilmiştir.
                        Her öneri öncelik seviyesiyle sınıflandırılmış olup uygulama takvimine temel teşkil edecek şekilde tasarlanmıştır.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        @foreach($recommendations as $i => $rec)
                        <div class="d-flex gap-3 p-3 rounded-3" style="background:#f8fafc;border-left:4px solid {{ $rec['color'] }};">
                            <div style="flex-shrink:0;margin-top:1px;">
                                <span class="badge" style="background:{{ $rec['color'] }};font-size:10px;">{{ $rec['priority'] }}</span>
                            </div>
                            <p class="mb-0" style="font-size:14px;color:#334155;line-height:1.6;">{{ $rec['text'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RAPOR ALTBILGI ──────────────────────────────────────────────── --}}
        <div class="text-center py-4 mt-2" style="border-top:1px solid #e2e8f0;color:#94a3b8;font-size:12px;">
            Bu rapor, <strong>{{ now()->format('d M Y H:i') }}</strong> tarihinde sistem tarafından otomatik olarak üretilmiştir.
            &nbsp;·&nbsp; Veri dönemi: {{ \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d.m.Y') }}
            &nbsp;·&nbsp; Tüm değerler Türk Lirası (TL) cinsindedir.
        </div>
    </div>

    @endif {{-- /veri var --}}

</div>
@endsection

@push('scripts')
<script>
function setRange(days) {
    const to   = new Date();
    const from = new Date();
    from.setDate(from.getDate() - (days - 1));
    const fmt = d => d.toISOString().split('T')[0];
    document.querySelector('[name="date_from"]').value = fmt(from);
    document.querySelector('[name="date_to"]').value   = fmt(to);
    document.querySelector('form').submit();
}
</script>
@endpush
