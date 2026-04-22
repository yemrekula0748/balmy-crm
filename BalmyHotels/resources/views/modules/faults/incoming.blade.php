@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    corePlugins: { preflight: false }
}
</script>
<style>
.fault-card { transition: background-color .15s, box-shadow .15s; }
.fault-card:hover { background-color: #f8faff !important; box-shadow: 0 4px 24px rgba(67,97,238,.09); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Başlık / Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>
                    <i class="fas fa-inbox me-2" style="color:#4361ee"></i>
                    Gelen Arızalar
                    @if($dept)
                        <span class="badge ms-2 fw-normal" style="background:#eef0ff;color:#4361ee;font-size:0.72rem;vertical-align:middle">
                            {{ $dept->name }}
                        </span>
                    @endif
                </h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Gelen Arızalar</li>
            </ol>
        </div>
    </div>

    {{-- Özet Kartlar --}}
    @if(isset($stats))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
        $statCards = [
            ['label'=>'Toplam',  'value'=>$stats['total'],       'icon'=>'fa-clipboard-list',     'icon_c'=>'text-indigo-600', 'bg'=>'bg-indigo-50',  'val_c'=>'text-indigo-700', 'ring'=>'ring-indigo-200', 'idx'=>0],
            ['label'=>'Açık',    'value'=>$stats['open'],        'icon'=>'fa-exclamation-circle', 'icon_c'=>'text-red-500',   'bg'=>'bg-red-50',     'val_c'=>'text-red-600',    'ring'=>'ring-red-200',    'idx'=>1],
            ['label'=>'İşlemde', 'value'=>$stats['in_progress'], 'icon'=>'fa-tools',              'icon_c'=>'text-orange-500','bg'=>'bg-orange-50',  'val_c'=>'text-orange-600', 'ring'=>'ring-orange-200', 'idx'=>2],
            ['label'=>'Kapalı',  'value'=>$stats['closed'],      'icon'=>'fa-check-double',       'icon_c'=>'text-green-600', 'bg'=>'bg-green-50',   'val_c'=>'text-green-700',  'ring'=>'ring-green-200',  'idx'=>3],
        ];
        @endphp
        @foreach($statCards as $sc)
        <div class="bg-white rounded-2xl ring-1 {{ $sc['ring'] }} shadow-sm p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 {{ $sc['bg'] }} rounded-xl flex items-center justify-center">
                <i class="fas {{ $sc['icon'] }} {{ $sc['icon_c'] }} text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ $sc['label'] }}</p>
                <p class="text-3xl font-extrabold {{ $sc['val_c'] }} leading-none">{{ $sc['value'] }}</p>
            </div>
            @if($sc['idx'] === 0 && $stats['avg_hours'] !== null)
            <div class="text-right flex-shrink-0 border-l border-gray-100 pl-4">
                <p class="text-xs text-gray-400 leading-tight">Ort. Çözüm</p>
                <p class="text-sm font-bold text-gray-500 mt-1">{{ $stats['avg_hours'] }}<span class="font-normal text-xs"> sa</span></p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Başarı mesajı --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
        <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
        <span class="flex-1">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()"
                class="text-green-400 hover:text-green-600 transition-colors border-0 bg-transparent cursor-pointer p-0">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Filtre & Arama --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1" style="min-width:200px;max-width:380px">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" name="search"
                           class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition"
                           placeholder="Başlık veya açıklama ara..." value="{{ request('search') }}">
                </div>
            </div>
            <div>
                <select name="status" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition"
                        style="min-width:160px">
                    <option value="">Tüm Durumlar</option>
                    @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition border-0 cursor-pointer">
                    <i class="fas fa-filter text-xs"></i>Filtrele
                </button>
                @if(request('search') || request('status'))
                <a href="{{ route('faults.incoming') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 hover:bg-gray-50 text-gray-500 text-sm rounded-xl transition"
                   style="text-decoration:none">
                    <i class="fas fa-times text-xs"></i>Temizle
                </a>
                @endif
            </div>
            <div class="ml-auto">
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-3 py-1.5 rounded-full">
                    {{ $faults->total() }} kayıt
                </span>
            </div>
        </form>
    </div>

    {{-- Arıza Listesi --}}
    <div class="space-y-3 pb-4">
        @forelse($faults as $fault)
        @php
            $statusColor = \App\Models\Fault::STATUS_COLORS[$fault->status] ?? 'secondary';
            $borderHexMap = [
                'danger'    => '#ef4444',
                'warning'   => '#f97316',
                'info'      => '#0ea5e9',
                'success'   => '#10b981',
                'primary'   => '#4361ee',
                'secondary' => '#94a3b8',
            ];
            $borderHex = $borderHexMap[$statusColor] ?? '#94a3b8';
            $isOld = $fault->created_at->diffInHours(now()) > 24;

            $prioMap = [
                'low'      => ['Normal', 'bg-green-50 text-green-700 ring-green-200'],
                'medium'   => ['Orta',   'bg-amber-50 text-amber-700 ring-amber-200'],
                'high'     => ['Acil',   'bg-red-50 text-red-700 ring-red-200'],
                'critical' => ['Kritik', 'bg-purple-50 text-purple-700 ring-purple-200'],
            ];
            [$prioLabel, $prioClass] = $prioMap[$fault->priority] ?? ['—', 'bg-gray-100 text-gray-500 ring-gray-200'];

            $statusBadgeClass = match($statusColor) {
                'danger'    => 'bg-red-500 text-white',
                'warning'   => 'bg-orange-500 text-white',
                'info'      => 'bg-sky-500 text-white',
                'success'   => 'bg-green-500 text-white',
                'primary'   => 'bg-indigo-600 text-white',
                default     => 'bg-gray-500 text-white',
            };

            $statusLabel  = \App\Models\Fault::STATUSES[$fault->status] ?? $fault->status;
            $locationStr  = $fault->faultLocation?->name ?? '';
            $areaStr      = $fault->faultArea?->name ?? '';
            $fullLocation = $locationStr . ($areaStr ? ' / ' . $areaStr : '');
            $reporterStr  = $fault->reporter?->name ?? '';
            $typeStr      = $fault->faultType?->name ?? '';
            $dateStr      = $fault->created_at->format('d.m.Y H:i');

            // Kopyalama metni — Durum ve Tesis/Şube bilgisi dahil değil
            $copyText = implode("\n", array_filter([
                '🔧 *ARIZA BİLDİRİMİ*',
                '──────────────────────',
                '*#' . $fault->id . ' — ' . $fault->title . '*',
                '',
                '⚡ Öncelik: ' . mb_strtoupper($prioLabel),
                $typeStr      ? '🔩 Tür: '     . $typeStr      : '',
                $fullLocation ? '📍 Konum: '   . $fullLocation : '',
                $reporterStr  ? '👤 Bildiren: ' . $reporterStr  : '',
                '🕐 Tarih: '  . $dateStr,
                $fault->description ? "\n📝 Açıklama:\n" . $fault->description : '',
            ]));
        @endphp

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden fault-card"
             style="border-left:5px solid {{ $borderHex }}">
            <div class="p-4 md:p-5">
                <div class="flex flex-col lg:flex-row gap-5">

                    {{-- Sol: Bilgiler --}}
                    <div class="flex-1 min-w-0">

                        {{-- Üst meta satırı --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusBadgeClass }}">
                                {{ $statusLabel }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 {{ $prioClass }}">
                                <i class="fas fa-flag" style="font-size:.55rem"></i>{{ $prioLabel }}
                            </span>
                            @if($fault->faultType)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 ring-1 ring-gray-200">
                                <i class="fas fa-wrench" style="font-size:.55rem"></i>{{ $fault->faultType->name }}
                            </span>
                            @endif
                            <div class="ml-auto flex items-center gap-2 text-xs">
                                <span class="font-bold text-indigo-500">#{{ $fault->id }}</span>
                                <span class="text-gray-200">│</span>
                                <span class="{{ $isOld ? 'text-red-400 font-semibold' : 'text-gray-400' }}"
                                      title="{{ $fault->created_at->format('d.m.Y H:i') }}">
                                    <i class="fas fa-clock mr-1" style="font-size:.6rem"></i>{{ $fault->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Başlık --}}
                        <h6 class="font-bold text-gray-900 text-base leading-snug mb-2">
                            <a href="{{ route('faults.show', $fault) }}"
                               style="text-decoration:none;color:inherit"
                               class="hover:text-indigo-600 transition-colors">
                                {{ $fault->title }}
                            </a>
                        </h6>

                        {{-- Açıklama --}}
                        @if($fault->description)
                        <div class="text-sm text-gray-500 leading-relaxed mb-3 rounded-lg px-3 py-2.5"
                             style="background:{{ $borderHex }}0d;border-left:3px solid {{ $borderHex }}70">
                            {{ Str::limit($fault->description, 220) }}
                        </div>
                        @endif

                        {{-- Meta chip'ler --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if($fault->faultLocation)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-blue-200">
                                <i class="fas fa-map-marker-alt" style="font-size:.6rem"></i>
                                {{ $fault->faultLocation->name }}
                                @if($fault->faultArea)
                                    <span class="text-blue-300 mx-0.5">/</span>{{ $fault->faultArea->name }}
                                @endif
                            </span>
                            @endif

                            @if($fault->branch)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                <i class="fas fa-building" style="font-size:.6rem"></i>{{ $fault->branch->name }}
                            </span>
                            @endif

                            @if($fault->reporter)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-violet-50 text-violet-700 ring-1 ring-violet-200">
                                <i class="fas fa-user" style="font-size:.6rem"></i>{{ $fault->reporter->name }}
                            </span>
                            @endif

                            @if($fault->image_path)
                            <a href="{{ Storage::url($fault->image_path) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700 ring-1 ring-orange-200 hover:bg-orange-100 transition-colors"
                               style="text-decoration:none">
                                <i class="fas fa-camera" style="font-size:.6rem"></i>Fotoğraf
                            </a>
                            @endif
                        </div>

                        {{-- Aksiyonlar --}}
                        <div class="flex items-center gap-2 pt-1">
                            <a href="{{ route('faults.show', $fault) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl ring-1 ring-indigo-200 transition-colors"
                               style="text-decoration:none">
                                <i class="fas fa-eye"></i>Detay
                            </a>
                            <button type="button"
                                    class="copy-fault-btn inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl ring-1 ring-emerald-200 transition-colors cursor-pointer border-0"
                                    data-copy="{{ e($copyText) }}"
                                    title="WhatsApp grubuna ilet">
                                <i class="fas fa-copy"></i>Kopyala
                            </button>
                        </div>

                    </div>

                    {{-- Sağ: Durum Güncelle --}}
                    <div class="lg:w-60 flex-shrink-0">
                        @if($canUpdate && $fault->status !== 'closed')
                        <div class="bg-slate-50 rounded-xl ring-1 ring-slate-200 p-4">
                            <p class="text-xs font-bold text-slate-500 mb-3 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="fas fa-pen text-indigo-400"></i>Durum Güncelle
                            </p>
                            <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                @csrf
                                <select name="status" required
                                        class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white mb-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition">
                                    <option value="" disabled selected>Yeni durum seçin…</option>
                                    @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                        @if($val !== $fault->status && $val !== 'resolved')
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <textarea name="note" rows="2" required
                                          placeholder="Açıklama (zorunlu)…"
                                          class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition resize-none"></textarea>
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition border-0 cursor-pointer">
                                    <i class="fas fa-save"></i>Kaydet
                                </button>
                            </form>
                        </div>

                        @elseif($fault->status === 'closed')
                        <div class="flex flex-col items-center justify-center py-6 gap-2">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-green-50 text-green-700 ring-1 ring-green-200">
                                <i class="fas fa-check-double"></i>Kapalı
                            </span>
                            @if($fault->closed_at)
                            <span class="text-xs text-gray-400">{{ $fault->closed_at->format('d.m.Y H:i') }}</span>
                            @endif
                        </div>

                        @else
                        <div class="flex flex-col items-center justify-center py-6 gap-1.5">
                            <i class="fas fa-lock text-2xl text-gray-200"></i>
                            <span class="text-xs text-gray-400">Güncelleme yetkiniz yok</span>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 py-20 text-center">
            <i class="fas fa-inbox text-5xl text-gray-200 mb-4 block"></i>
            <p class="text-gray-400 font-semibold text-lg">Arıza kaydı bulunamadı.</p>
            @if(request('search') || request('status'))
            <p class="text-sm text-gray-400 mt-1">Filtreleri temizleyerek tüm kayıtları görebilirsiniz.</p>
            @endif
        </div>
        @endforelse
    </div>

    @if($faults->hasPages())
    <div class="mt-4">{{ $faults->appends(request()->query())->links() }}</div>
    @endif

</div>

{{-- Kopyala bildirimi --}}
<div id="copy-toast"
     class="fixed bottom-6 right-6 z-50 items-center gap-3 text-white text-sm px-5 py-3.5 rounded-2xl shadow-2xl"
     style="display:none;background:#111827">
    <i class="fas fa-check-circle text-green-400 text-base"></i>
    <span>Kopyalandı! WhatsApp'a yapıştırabilirsiniz.</span>
</div>

@push('scripts')
<script>
document.querySelectorAll('.copy-fault-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var text = this.getAttribute('data-copy');
        var self = this;
        var origHTML = self.innerHTML;

        function showSuccess() {
            self.innerHTML = '<i class="fas fa-check"></i>Kopyalandı!';
            self.classList.remove('text-emerald-700','bg-emerald-50','ring-emerald-200');
            self.classList.add('text-white','bg-emerald-600');
            self.disabled = true;
            document.getElementById('copy-toast').style.display = 'flex';
            setTimeout(function() {
                self.innerHTML = origHTML;
                self.classList.remove('text-white','bg-emerald-600');
                self.classList.add('text-emerald-700','bg-emerald-50','ring-emerald-200');
                self.disabled = false;
                document.getElementById('copy-toast').style.display = 'none';
            }, 2500);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showSuccess).catch(function() {
                fallbackCopy(text); showSuccess();
            });
        } else {
            fallbackCopy(text); showSuccess();
        }
    });
});

function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
}
</script>
@endpush
@endsection
