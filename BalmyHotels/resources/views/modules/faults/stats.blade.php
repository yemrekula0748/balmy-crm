@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={corePlugins:{preflight:false}}</script>
@endpush

@section('content')
<div class="pb-6">

    {{-- BAŞLIK --}}
    <div class="rounded-2xl p-6 mb-5 text-white" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%)">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="text-xl font-bold mb-1 flex items-center gap-2">
                    <i class="fas fa-chart-bar" style="color:#f97316"></i> İstatistikler &amp; Skor Tablosu
                </h3>
                <nav class="text-sm" style="color:rgba(255,255,255,.6)">
                    <a href="{{ url('/') }}" style="color:rgba(255,255,255,.6);text-decoration:none">Anasayfa</a>
                    <span class="opacity-50 mx-1">/</span>
                    <a href="{{ route('faults.index') }}" style="color:rgba(255,255,255,.6);text-decoration:none">Teknik Arıza</a>
                    <span class="opacity-50 mx-1">/</span>
                    <span>İstatistikler</span>
                </nav>
            </div>
        </div>
    </div>

    {{-- DÖNEM FİLTRESİ --}}
    <div class="bg-white rounded-2xl shadow-sm mb-5 px-5 py-3 flex items-center gap-2 flex-wrap"
         style="border:1px solid #f1f5f9">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Dönem</span>
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            @foreach(['7'=>'Son 7 Gün','30'=>'Son 30 Gün','90'=>'Son 90 Gün','365'=>'Son 1 Yıl','all'=>'Tüm Zamanlar'] as $val=>$lbl)
            <label class="cursor-pointer">
                <input type="radio" name="period" value="{{ $val }}" class="sr-only"
                       @checked($period === $val) onchange="this.form.submit()">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-bold cursor-pointer"
                      style="{{ $period === $val ? 'background:#0f172a;color:#fff' : 'background:#f1f5f9;color:#64748b' }}">
                    {{ $lbl }}
                </span>
            </label>
            @endforeach
        </form>
    </div>

    {{-- ÖZET KARTLAR --}}
    @php
        $summaryCards = [
            ['icon'=>'layer-group',       'color'=>'#6366f1','bg'=>'#eef2ff','label'=>'Toplam',    'val'=>$summary['total']],
            ['icon'=>'circle-exclamation','color'=>'#dc2626','bg'=>'#fef2f2','label'=>'Açık',      'val'=>$summary['open']],
            ['icon'=>'clock',             'color'=>'#d97706','bg'=>'#fffbeb','label'=>'İşlemde',   'val'=>$summary['in_progress']],
            ['icon'=>'circle-check',      'color'=>'#16a34a','bg'=>'#f0fdf4','label'=>'Kapalı',    'val'=>$summary['closed']],
            ['icon'=>'hourglass-half',    'color'=>'#0891b2','bg'=>'#ecfeff','label'=>'Ort. Süre', 'val'=>$summary['avg_hours'] !== null ? $summary['avg_hours'].' s' : '—'],
            ['icon'=>'shield-check',      'color'=>'#7c3aed','bg'=>'#faf5ff','label'=>'SLA',       'val'=>$summary['sla_pct'] !== null ? '%'.$summary['sla_pct'] : '—'],
        ];
    @endphp
    <div class="grid gap-4 mb-5" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
        @foreach($summaryCards as $card)
        <div class="bg-white rounded-2xl p-4 flex items-center gap-3 shadow-sm" style="border:1px solid #f1f5f9">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:{{ $card['bg'] }}">
                <i class="fas fa-{{ $card['icon'] }}" style="color:{{ $card['color'] }}"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold leading-tight">{{ $card['label'] }}</p>
                <p class="text-xl font-bold text-gray-800 leading-tight">{{ $card['val'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- DEPARTMAN SCOREBOARD --}}
    @if($deptScoreboard->count())
    <div class="bg-white rounded-2xl shadow-sm mb-5 overflow-hidden" style="border:1px solid #f1f5f9">
        <div class="px-5 py-3 flex items-center gap-2" style="background:#0f172a">
            <i class="fas fa-trophy" style="color:#f59e0b"></i>
            <span class="font-bold text-white text-sm">Departman Scoreboard</span>
        </div>
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">#</th>
                        <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Departman</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Toplam</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Açık</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">İşlemde</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Kapalı</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Ort. Süre</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">SLA %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deptScoreboard as $i => $row)
                    <tr style="border-top:1px solid #f1f5f9{{ $i===0 ? ';background:#fffbeb' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-400 font-semibold">{{ $i+1 }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">
                            @if($i===0)<i class="fas fa-crown mr-1" style="color:#f59e0b"></i>@endif
                            {{ $row['dept']?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-700">{{ $row['total'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fffbeb;color:#d97706">{{ $row['in_progress'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].' s' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if($row['sla_pct'] !== null)
                            @php
                                $slaBg  = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                                $slaFg  = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                                  style="background:{{ $slaBg }};color:{{ $slaFg }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ARIZA TÜRÜ PERFORMANSI --}}
    @if($typeStats->count())
    <div class="bg-white rounded-2xl shadow-sm mb-5 overflow-hidden" style="border:1px solid #f1f5f9">
        <div class="px-5 py-3 flex items-center gap-2" style="background:#1e40af">
            <i class="fas fa-tags" style="color:#bfdbfe"></i>
            <span class="font-bold text-white text-sm">Arıza Türü Performansı</span>
        </div>
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Tür</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">SLA Hedef</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Toplam</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Açık</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Ort. Süre</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">SLA %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeStats as $row)
                    <tr style="border-top:1px solid #f1f5f9">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ $row['type_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $row['target_hours'] }} s</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-700">{{ $row['total'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500">
                            {{ $row['avg_hours'] !== null ? $row['avg_hours'].' s' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if($row['sla_pct'] !== null)
                            @php
                                $slaBg = $row['sla_pct'] >= 80 ? '#f0fdf4' : ($row['sla_pct'] >= 50 ? '#fffbeb' : '#fef2f2');
                                $slaFg = $row['sla_pct'] >= 80 ? '#16a34a' : ($row['sla_pct'] >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                                  style="background:{{ $slaBg }};color:{{ $slaFg }}">
                                %{{ $row['sla_pct'] }}
                            </span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- KONUM & ALAN --}}
    <div class="grid gap-4 mb-5" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">

        {{-- Konum Dağılımı --}}
        @if($locationStats->count())
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #f1f5f9">
            <div class="px-5 py-3 flex items-center gap-2" style="background:#065f46">
                <i class="fas fa-map-marker-alt" style="color:#6ee7b7"></i>
                <span class="font-bold text-white text-sm">Konum Bazında (Top 10)</span>
            </div>
            <div>
                @foreach($locationStats as $row)
                <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid #f8fafc">
                    <span class="text-sm text-gray-700 font-medium">{{ $row['name'] }}</span>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-400">{{ $row['open'] }} açık</span>
                        <span class="px-2 py-0.5 rounded-full font-bold" style="background:#f1f5f9;color:#374151">
                            {{ $row['total'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Alan Top 10 --}}
        @if($areaStats->count())
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #f1f5f9">
            <div class="px-5 py-3 flex items-center gap-2" style="background:#7c2d12">
                <i class="fas fa-door-open" style="color:#fca5a5"></i>
                <span class="font-bold text-white text-sm">Alan Top 10</span>
            </div>
            <div>
                @foreach($areaStats as $row)
                <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid #f8fafc">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">{{ $row['area_name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $row['loc_name'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-400">{{ $row['open'] }} açık</span>
                        <span class="px-2 py-0.5 rounded-full font-bold" style="background:#f1f5f9;color:#374151">
                            {{ $row['total'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- AYLIK TREND --}}
    @if($monthlyTrend->count())
    <div class="bg-white rounded-2xl shadow-sm mb-5 overflow-hidden" style="border:1px solid #f1f5f9">
        <div class="px-5 py-3 flex items-center gap-2" style="background:#4338ca">
            <i class="fas fa-chart-line" style="color:#c7d2fe"></i>
            <span class="font-bold text-white text-sm">Aylık Trend</span>
        </div>
        <div class="p-4" style="height:260px">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ŞUBE KARŞILAŞTIRMA --}}
    @if($branchStats && $branchStats->count())
    <div class="bg-white rounded-2xl shadow-sm mb-5 overflow-hidden" style="border:1px solid #f1f5f9">
        <div class="px-5 py-3 flex items-center gap-2" style="background:#374151">
            <i class="fas fa-hotel" style="color:#d1d5db"></i>
            <span class="font-bold text-white text-sm">Şube Karşılaştırma</span>
        </div>
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Şube</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Toplam</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Açık</th>
                        <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-400">Kapalı</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchStats as $row)
                    <tr style="border-top:1px solid #f1f5f9">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-700">{{ $row['total'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fef2f2;color:#dc2626">{{ $row['open'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#f0fdf4;color:#16a34a">{{ $row['closed'] }}</span>
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
    const trendData = @json($monthlyTrend);
    if (!trendData.length) return;
    const months = ['Oca','\u015eub','Mar','Nis','May','Haz','Tem','\u0102u','\u015eyl','Eki','Kas','Ara'];
    const labels = trendData.map(function(r) {
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
                    backgroundColor: 'rgba(99,102,241,0.75)',
                    borderRadius: 6
                },
                {
                    label: 'Kapal\u0131',
                    data: trendData.map(function(r) { return r.closed; }),
                    backgroundColor: 'rgba(34,197,94,0.75)',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } }
            }
        }
    });
})();
</script>
@endpush