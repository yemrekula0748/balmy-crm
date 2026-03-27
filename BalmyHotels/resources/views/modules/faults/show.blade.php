@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
{{-- Bootstrap breadcrumb --}}
<div class="container-fluid">
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Detayı <span style="color:#94a3b8;font-size:.85rem">#{{ $fault->id }}</span></h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">#{{ $fault->id }}</li>
            </ol>
        </div>
    </div>
</div>

@php
    $statusMeta = [
        'open'        => ['bg'=>'bg-red-100','text'=>'text-red-700','border'=>'border-red-300','dot'=>'bg-red-500','label'=>'Açık'],
        'in_progress' => ['bg'=>'bg-amber-100','text'=>'text-amber-700','border'=>'border-amber-300','dot'=>'bg-amber-500','label'=>'Devam Ediyor'],
        'resolved'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','border'=>'border-emerald-300','dot'=>'bg-emerald-500','label'=>'Çözüldü'],
        'closed'      => ['bg'=>'bg-slate-100','text'=>'text-slate-600','border'=>'border-slate-300','dot'=>'bg-slate-400','label'=>'Kapalı'],
    ];
    $priorityMeta = [
        'low'      => ['bg'=>'bg-sky-100','text'=>'text-sky-700','label'=>'Düşük'],
        'medium'   => ['bg'=>'bg-amber-100','text'=>'text-amber-700','label'=>'Orta'],
        'high'     => ['bg'=>'bg-orange-100','text'=>'text-orange-700','label'=>'Yüksek'],
        'critical' => ['bg'=>'bg-red-100','text'=>'text-red-700','label'=>'Kritik'],
    ];
    $sm = $statusMeta[$fault->status] ?? $statusMeta['open'];
    $pm = $priorityMeta[$fault->priority] ?? $priorityMeta['medium'];
    $isClosed = in_array($fault->status, ['resolved', 'closed']);
@endphp

<div class="px-4 pb-10" style="font-family:'Inter',system-ui,-apple-system,sans-serif;">

    {{-- Session alerts --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3 mb-4">
        <i class="fas fa-check-circle text-emerald-500"></i>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3 mb-4">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- Hero Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-5 overflow-hidden">
        {{-- Status stripe --}}
        <div class="h-1 {{ $sm['dot'] }} w-full"></div>
        <div class="p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $pm['bg'] }} {{ $pm['text'] }}">
                            <i class="fas fa-flag text-xs"></i>
                            {{ $pm['label'] }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $sm['bg'] }} {{ $sm['text'] }} border {{ $sm['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sm['dot'] }} inline-block"></span>
                            {{ $sm['label'] }}
                        </span>
                        @if($fault->faultType)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-violet-100 text-violet-700">
                            <i class="fas fa-tag text-xs"></i>
                            {{ $fault->faultType->name }}
                        </span>
                        @endif
                    </div>
                    {{-- Title --}}
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight leading-snug mb-3">{{ $fault->title }}</h1>
                    {{-- Meta row --}}
                    <div class="flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-user text-gray-300 text-xs"></i>
                            <span class="text-gray-400">Bildiren:</span>
                            <span class="font-semibold text-gray-700">{{ $fault->reporter->name ?? '—' }}</span>
                        </span>
                        @if($fault->department)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-users text-gray-300 text-xs"></i>
                            <span class="text-gray-400">Departman:</span>
                            <span class="font-semibold text-gray-700">{{ $fault->department->name }}</span>
                        </span>
                        @endif
                        @if($fault->branch)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-building text-gray-300 text-xs"></i>
                            <span class="text-gray-400">Şube:</span>
                            <span class="font-semibold text-gray-700">{{ $fault->branch->name }}</span>
                        </span>
                        @endif
                        @if($fault->faultLocation)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-map-marker-alt text-gray-300 text-xs"></i>
                            <span class="font-semibold text-gray-700">{{ $fault->faultLocation->name }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}</span>
                        </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-calendar text-gray-300 text-xs"></i>
                            <span class="text-gray-400">Kayıt:</span>
                            <span class="font-medium text-gray-600">{{ $fault->created_at->format('d.m.Y H:i') }}</span>
                        </span>
                        @if($fault->resolved_at)
                        <span class="flex items-center gap-1.5 text-emerald-600">
                            <i class="fas fa-check-circle text-xs"></i>
                            <span>Çözüldü: {{ \Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i') }}</span>
                            <span class="font-semibold">({{ round($fault->resolutionTimeHours(), 1) }} sa.)</span>
                        </span>
                        @endif
                    </div>
                </div>
                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('faults.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition-colors no-underline">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Listeye Dön
                    </a>
                    @if(!$isClosed)
                    <form action="{{ route('faults.destroy', $fault) }}" method="POST" id="deleteForm">
                        @csrf @method('DELETE')
                        <button type="button" id="deleteBtn"
                                class="inline-flex items-center gap-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-colors border border-red-200">
                            <i class="fas fa-trash text-xs"></i>
                            Sil
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- LEFT column --}}
        <div class="lg:col-span-7 flex flex-col gap-5">

            {{-- Description --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-align-left text-indigo-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Arıza Açıklaması</h2>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $fault->description ?: '—' }}</p>
                </div>
            </div>

            {{-- Photo --}}
            @if($fault->image_path)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-image text-blue-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Arıza Fotoğrafı</h2>
                </div>
                <div class="p-4 flex flex-col items-center gap-3">
                    <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-gray-100">
                        <img src="{{ asset('uploads/'.$fault->image_path) }}"
                             class="max-h-80 object-contain w-full"
                             alt="Arıza fotoğrafı">
                    </a>
                    <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 text-xs text-gray-500 hover:text-gray-700 no-underline">
                        <i class="fas fa-external-link-alt text-xs"></i>Tam boyutta aç
                    </a>
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-history text-amber-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Güncelleme Geçmişi</h2>
                    <span class="ml-auto text-xs bg-gray-100 text-gray-500 font-semibold px-2 py-0.5 rounded-full">
                        {{ $fault->updates->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($fault->updates as $update)
                    @php
                        $hasChange = $update->status_from && $update->status_to && $update->status_from !== $update->status_to;
                        $avatarBg  = $hasChange ? '#f59e0b' : '#94a3b8';
                        $fromMeta  = $statusMeta[$update->status_from] ?? null;
                        $toMeta    = $statusMeta[$update->status_to] ?? null;
                    @endphp
                    <div class="flex gap-4 px-6 py-4 hover:bg-gray-50/50 transition-colors">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5"
                             style="background:{{ $avatarBg }}">
                            {{ strtoupper(substr($update->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-800">{{ $update->user?->name ?? 'Sistem' }}</span>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $update->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            @if($hasChange && $fromMeta && $toMeta)
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $fromMeta['bg'] }} {{ $fromMeta['text'] }}">
                                    {{ $fromMeta['label'] }}
                                </span>
                                <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                                <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $toMeta['bg'] }} {{ $toMeta['text'] }}">
                                    {{ $toMeta['label'] }}
                                </span>
                            </div>
                            @endif
                            @if($update->note)
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $update->note }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-10 text-center text-gray-400 text-sm">
                        <i class="fas fa-inbox text-2xl opacity-30 block mb-2"></i>
                        Henüz güncelleme kaydı yok.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT column --}}
        <div class="lg:col-span-5 flex flex-col gap-5">

            {{-- Status update / resolved badge --}}
            @if(!$isClosed)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-sync-alt text-orange-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Durum Güncelle</h2>
                </div>
                <div class="px-6 py-5">
                    <form action="{{ route('faults.updateStatus', $fault) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Yeni Durum</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                @if($val !== $fault->status && $val !== 'resolved')
                                @php $opt = $statusMeta[$val] ?? []; @endphp
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all
                                             {{ old('status') === $val ? 'border-blue-500 '.$opt['bg'] : 'border-gray-100 hover:border-gray-200' }}">
                                    <input type="radio" name="status" value="{{ $val }}"
                                           @checked(old('status') === $val)
                                           class="accent-blue-600">
                                    <span class="text-xs font-semibold {{ $opt['text'] ?? 'text-gray-700' }}">{{ $lbl }}</span>
                                </label>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                Not / Açıklama <span class="text-red-400">*</span>
                            </label>
                            <textarea name="note" rows="3"
                                      class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 resize-none @error('note') border-red-400 @enderror"
                                      placeholder="Durum değişikliği hakkında not...">{{ old('note') }}</textarea>
                            @error('note')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-sm">
                            <i class="fas fa-check text-xs"></i>
                            Durumu Güncelle
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden">
                <div class="flex flex-col items-center justify-center py-8 px-6 text-center">
                    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-check-double text-xl text-emerald-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-base">{{ \App\Models\Fault::STATUSES[$fault->status] }}</h3>
                    @if($fault->resolved_at)
                    <p class="text-sm text-gray-500 mt-1">
                        Çözüm süresi: <strong class="text-emerald-600">{{ round($fault->resolutionTimeHours(), 1) }} saat</strong>
                    </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Add comment --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-sky-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-comment-dots text-sky-600 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Yorum / Not Ekle</h2>
                </div>
                <div class="px-6 py-5">
                    <form action="{{ route('faults.addComment', $fault) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="note" rows="3"
                                  class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 resize-none"
                                  placeholder="Arıza hakkında notunuzu yazın..."></textarea>
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            <i class="fas fa-paper-plane text-xs"></i>
                            Yorum Ekle
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-50">
                    <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-gray-500 text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-800">Özet Bilgiler</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @php
                    $rows = [
                        ['label'=>'Arıza No',  'value'=>'#'.$fault->id, 'mono'=>true],
                        ['label'=>'Öncelik',   'value'=>$pm['label'], 'badge'=>$pm['bg'].' '.$pm['text']],
                        ['label'=>'Durum',     'value'=>$sm['label'],  'badge'=>$sm['bg'].' '.$sm['text']],
                        ['label'=>'Şube',      'value'=>$fault->branch->name ?? '—'],
                        ['label'=>'Departman', 'value'=>$fault->department->name ?? '—'],
                        ['label'=>'Bildiren',  'value'=>$fault->reporter->name ?? '—'],
                        ['label'=>'Kayıt',     'value'=>$fault->created_at->format('d.m.Y H:i')],
                    ];
                    if($fault->resolved_at) {
                        $rows[] = ['label'=>'Çözüm Tarihi','value'=>\Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i')];
                        $rows[] = ['label'=>'Çözüm Süresi','value'=>round($fault->resolutionTimeHours(),1).' saat','green'=>true];
                    }
                    $rows[] = ['label'=>'Güncellemeler','value'=>$fault->updates->count().' adet'];
                    @endphp
                    @foreach($rows as $row)
                    <div class="flex items-center justify-between px-6 py-3">
                        <span class="text-xs text-gray-400 font-medium">{{ $row['label'] }}</span>
                        @if(isset($row['badge']))
                        <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full {{ $row['badge'] }}">{{ $row['value'] }}</span>
                        @elseif(isset($row['mono']))
                        <span class="font-mono text-sm font-bold text-gray-700">{{ $row['value'] }}</span>
                        @elseif(isset($row['green']))
                        <span class="text-sm font-bold text-emerald-600">{{ $row['value'] }}</span>
                        @else
                        <span class="text-sm font-medium text-gray-700">{{ $row['value'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('deleteBtn')?.addEventListener('click', function () {
    Swal.fire({
        title: 'Arızayı sil?',
        text: 'Bu işlem geri alınamaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'İptal',
        confirmButtonText: 'Evet, sil!'
    }).then(result => {
        if (result.isConfirmed) document.getElementById('deleteForm').submit();
    });
});
// Highlight selected status radio
document.querySelectorAll('input[name="status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="status"]').forEach(r => {
            r.closest('label').classList.remove('border-blue-500');
        });
        this.closest('label').classList.add('border-blue-500');
    });
});
</script>
@endpush
@endsection
