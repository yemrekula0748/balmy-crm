@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Tailwind reset için Bootstrap çakışmalarını önle */
    .tw-scope { all: initial; display: block; }
    .tw-scope *, .tw-scope *::before, .tw-scope *::after { box-sizing: border-box; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb (Bootstrap) --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Departmanım
                    @if($dept)<small class="text-muted fs-6"> — {{ $dept->name }}</small>@endif
                </h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Departmanım</li>
            </ol>
        </div>
    </div>

    @if(!$dept)
    <div class="alert alert-warning mt-3">Herhangi bir departmana atanmamışsınız.</div>
    @else
    @endif
</div>

{{-- Main content in Tailwind --}}
@if($dept)
<div class="px-4 pb-8 font-sans" style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl mb-6 shadow-lg"
         style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #3b82f6 100%);">
        <div class="absolute inset-0 opacity-10"
             style="background-image: url('data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 100 100&quot;><circle cx=&quot;80&quot; cy=&quot;20&quot; r=&quot;40&quot; fill=&quot;white&quot;/><circle cx=&quot;10&quot; cy=&quot;80&quot; r=&quot;30&quot; fill=&quot;white&quot;/></svg>');">
        </div>
        <div class="relative flex items-center justify-between px-8 py-6">
            <div>
                <p class="text-blue-200 text-sm font-medium tracking-widest uppercase mb-1">Teknik Arıza Merkezi</p>
                <h1 class="text-white text-2xl font-bold tracking-tight">{{ $dept->name }}</h1>
                <p class="text-blue-200 text-sm mt-1">Departman performans özeti ve istatistikleri</p>
            </div>
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('faults.incoming') }}"
                   class="flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all duration-200 backdrop-blur-sm border border-white/20 no-underline">
                    <i class="fas fa-inbox text-xs"></i>
                    Gelen Arızalar
                </a>
                <a href="{{ route('faults.create') }}"
                   class="flex items-center gap-2 bg-white text-blue-700 hover:bg-blue-50 text-sm font-medium px-4 py-2.5 rounded-xl transition-all duration-200 shadow no-underline">
                    <i class="fas fa-plus text-xs"></i>
                    Arıza Bildir
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
        $kpis = [
            [
                'label'   => 'Açık',
                'key'     => 'open',
                'icon'    => 'fa-exclamation-circle',
                'bg'      => 'bg-red-50',
                'icon_bg' => 'bg-red-100',
                'icon_cl' => 'text-red-500',
                'num_cl'  => 'text-red-600',
                'border'  => 'border-red-200',
            ],
            [
                'label'   => 'Devam Eden',
                'key'     => 'in_progress',
                'icon'    => 'fa-tools',
                'bg'      => 'bg-amber-50',
                'icon_bg' => 'bg-amber-100',
                'icon_cl' => 'text-amber-500',
                'num_cl'  => 'text-amber-600',
                'border'  => 'border-amber-200',
            ],
            [
                'label'   => 'Çözüldü',
                'key'     => 'resolved',
                'icon'    => 'fa-check-circle',
                'bg'      => 'bg-emerald-50',
                'icon_bg' => 'bg-emerald-100',
                'icon_cl' => 'text-emerald-500',
                'num_cl'  => 'text-emerald-600',
                'border'  => 'border-emerald-200',
            ],
            [
                'label'   => 'Kapalı',
                'key'     => 'closed',
                'icon'    => 'fa-check-double',
                'bg'      => 'bg-slate-50',
                'icon_bg' => 'bg-slate-100',
                'icon_cl' => 'text-slate-500',
                'num_cl'  => 'text-slate-600',
                'border'  => 'border-slate-200',
            ],
        ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="rounded-2xl border {{ $kpi['bg'] }} {{ $kpi['border'] }} p-5 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="w-12 h-12 rounded-xl {{ $kpi['icon_bg'] }} flex items-center justify-center flex-shrink-0">
                <i class="fas {{ $kpi['icon'] }} text-lg {{ $kpi['icon_cl'] }}"></i>
            </div>
            <div>
                <div class="text-2xl font-bold {{ $kpi['num_cl'] }} leading-none">{{ $totals[$kpi['key']] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-medium mt-1">{{ $kpi['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Avg resolution time banner --}}
    @if($avgResolutionHours ?? null)
    <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 mb-6">
        <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-blue-500 text-sm"></i>
        </div>
        <span class="text-sm text-blue-700">
            Kapalı arızalarda ortalama çözüm süresi:
            <strong class="font-semibold">{{ \App\Models\Fault::formatHours($avgResolutionHours) }}</strong>
        </span>
    </div>
    @endif

    {{-- Main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- Performance table --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-tachometer-alt text-indigo-600 text-sm"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-800 tracking-tight">Arıza Türü Performansı</h2>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Arıza Türü</th>
                                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 py-3">Toplam</th>
                                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 py-3">Hedef (sa)</th>
                                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 py-3">Ort. Süre</th>
                                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3" style="min-width:130px">Zamanında</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($typePerformance as $row)
                            @php
                                $pct      = $row['on_time_pct'] ?? 0;
                                $barCl    = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-red-400');
                                $textCl   = $pct >= 80 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-500');
                                $overTime = isset($row['avg_hours']) && $row['avg_hours'] > $row['target_hours'];
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors duration-100">
                                <td class="px-5 py-3.5">
                                    <span class="font-medium text-gray-800 text-sm">{{ $row['type_name'] }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">{{ $row['total'] }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-center text-xs text-gray-500 font-medium">{{ $row['target_hours'] }}sa</td>
                                <td class="px-3 py-3.5 text-center">
                                    @if(isset($row['avg_hours']) && $row['avg_hours'])
                                        <span class="text-xs font-bold {{ $overTime ? 'text-red-500' : 'text-emerald-600' }}">
                                            {{ \App\Models\Fault::formatHours($row['avg_hours']) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $barCl }} h-1.5 rounded-full transition-all duration-500"
                                                 style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold {{ $textCl }} w-9 text-right">%{{ number_format($pct, 0) }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">
                                    <i class="fas fa-inbox text-2xl opacity-30 block mb-2"></i>
                                    Henüz kaydedilmiş arıza yok.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-5 flex flex-col gap-5">

            {{-- Monthly Trend Chart --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                    <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-blue-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-tight">Aylık Eğilim — Son 6 Ay</h2>
                </div>
                <div class="px-4 py-4" style="position:relative;height:200px">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            {{-- Type Distribution --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                    <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tags text-violet-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-tight">Türe Göre Dağılım</h2>
                </div>
                <div class="px-5 py-4 space-y-3">
                    @php
                        $typeMax = $byType->max('count') ?: 1;
                        $palette = ['bg-blue-500','bg-violet-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-cyan-500','bg-indigo-500'];
                    @endphp
                    @forelse($byType as $i => $typeRow)
                    @php $width = round($typeRow['count'] / $typeMax * 100); $color = $palette[$i % count($palette)]; @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-medium text-gray-600 truncate max-w-[60%]">{{ $typeRow['type_name'] }}</span>
                            <span class="text-xs font-bold text-gray-700">{{ $typeRow['count'] }}</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="{{ $color }} h-1.5 rounded-full" style="width:{{ $width }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-sm text-gray-400 py-4">Veri yok.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const monthlyData = @json($monthlyTrend);
    const labels = monthlyData.map(r => r.month);
    const counts = monthlyData.map(r => r.total);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Arıza Sayısı',
                data: counts,
                backgroundColor: 'rgba(37,99,235,0.15)',
                borderColor: '#2563eb',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });
})();
</script>
@endpush

