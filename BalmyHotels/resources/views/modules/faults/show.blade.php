@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
.tw-card{background:#fff;border-radius:1rem;border:1px solid #e8ecf0;box-shadow:0 1px 6px rgba(15,23,42,.06)}
.tw-section-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9}
</style>
@endpush

@section('content')
{{-- Breadcrumb --}}
<div class="container-fluid">
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-tools text-danger" style="font-size:1rem"></i>
                    Arıza Detayı
                    <span style="color:#94a3b8;font-size:.8rem;font-weight:400">#{{ $fault->id }}</span>
                </h4>
            </div>
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
    'open'             => ['stripe'=>'#ef4444','bg'=>'bg-red-100','text'=>'text-red-700','dot'=>'bg-red-500','label'=>'Açık',            'icon'=>'fa-circle-exclamation'],
    'in_progress'      => ['stripe'=>'#f59e0b','bg'=>'bg-amber-100','text'=>'text-amber-700','dot'=>'bg-amber-500','label'=>'İşlemde',       'icon'=>'fa-rotate'],
    'winter_plan'      => ['stripe'=>'#0ea5e9','bg'=>'bg-sky-100','text'=>'text-sky-700','dot'=>'bg-sky-500','label'=>'Kış Planı',       'icon'=>'fa-snowflake'],
    'waiting_material' => ['stripe'=>'#7c3aed','bg'=>'bg-violet-100','text'=>'text-violet-700','dot'=>'bg-violet-500','label'=>'Malzeme Bekliyor','icon'=>'fa-box'],
    'resolved'         => ['stripe'=>'#10b981','bg'=>'bg-emerald-100','text'=>'text-emerald-700','dot'=>'bg-emerald-500','label'=>'Çözüldü',         'icon'=>'fa-check-circle'],
    'closed'           => ['stripe'=>'#64748b','bg'=>'bg-slate-100','text'=>'text-slate-600','dot'=>'bg-slate-400','label'=>'Kapalı',          'icon'=>'fa-lock'],
];
$priorityMeta = [
    'low'      => ['icon'=>'fa-circle-check',       'stripe'=>'#10b981','bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Normal'],
    'medium'   => ['icon'=>'fa-triangle-exclamation','stripe'=>'#f59e0b','bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'label'=>'Orta'],
    'high'     => ['icon'=>'fa-bolt',               'stripe'=>'#ef4444','bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Acil'],
    'critical' => ['icon'=>'fa-skull-crossbones',   'stripe'=>'#1e293b','bg'=>'bg-slate-800',  'text'=>'text-white',      'label'=>'Kritik'],
];
$sm       = $statusMeta[$fault->status]    ?? $statusMeta['open'];
$pm       = $priorityMeta[$fault->priority] ?? $priorityMeta['medium'];
$isClosed = in_array($fault->status, ['resolved','closed']);
@endphp

<div class="px-3 pb-10" style="font-family:'Inter',system-ui,-apple-system,sans-serif">

    @if(session('success'))
    <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 mb-4">
        <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 mb-4">
        <i class="fas fa-exclamation-circle text-red-500"></i>{{ session('error') }}
    </div>
    @endif

    {{-- ══ HERO ══ --}}
    <div class="rounded-2xl overflow-hidden mb-5" style="background:#0f172a;box-shadow:0 4px 24px rgba(0,0,0,.18)">
        <div style="height:4px;background:{{ $sm['stripe'] }}"></div>
        <div class="p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#64748b">
                        Arıza Kaydı &nbsp;·&nbsp; <span style="color:#94a3b8">#{{ $fault->id }}</span>
                    </div>
                    <h1 class="font-bold leading-tight mb-3" style="font-size:1.3rem;color:#f1f5f9">{{ $fault->title }}</h1>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full {{ $pm['bg'] }} {{ $pm['text'] }}">
                            <i class="fas {{ $pm['icon'] }} text-xs"></i>{{ $pm['label'] }} Öncelik
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full {{ $sm['bg'] }} {{ $sm['text'] }}">
                            <span class="w-2 h-2 rounded-full {{ $sm['dot'] }} inline-block"></span>{{ $sm['label'] }}
                        </span>
                        @if($fault->faultType)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-violet-100 text-violet-700">
                            <i class="fas fa-tag text-xs"></i>{{ $fault->faultType->name }}
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-6 gap-y-2" style="font-size:.76rem;color:#94a3b8">
                        <span><i class="fas fa-user me-1"></i><span style="color:#64748b">Bildiren:</span> <strong style="color:#cbd5e1">{{ $fault->reporter->name ?? '—' }}</strong></span>
                        @if($fault->department)<span><i class="fas fa-users me-1"></i><span style="color:#64748b">Dept:</span> <strong style="color:#cbd5e1">{{ $fault->department->name }}</strong></span>@endif
                        @if($fault->branch)<span><i class="fas fa-building me-1"></i><strong style="color:#cbd5e1">{{ $fault->branch->name }}</strong></span>@endif
                        @if($fault->faultLocation)<span><i class="fas fa-map-marker-alt me-1"></i><strong style="color:#cbd5e1">{{ $fault->faultLocation->name }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}</strong></span>@endif
                        <span><i class="fas fa-calendar me-1"></i><span style="color:#64748b">Kayıt:</span> <strong style="color:#cbd5e1">{{ $fault->created_at->format('d.m.Y H:i') }}</strong></span>
                        @if($fault->resolved_at)<span style="color:#10b981"><i class="fas fa-check-double me-1"></i>Çözüldü: <strong>{{ \Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i') }}</strong> ({{ round($fault->resolutionTimeHours(),1) }} sa.)</span>@endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('faults.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-xl no-underline transition-colors"
                       style="background:rgba(255,255,255,.08);color:#cbd5e1">
                        <i class="fas fa-arrow-left text-xs"></i>Geri
                    </a>
                    @if(!$isClosed)
                    <form action="{{ route('faults.destroy', $fault) }}" method="POST" id="deleteForm">
                        @csrf @method('DELETE')
                        <button type="button" id="deleteBtn"
                                class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-xl border"
                                style="background:rgba(239,68,68,.15);color:#fca5a5;border-color:rgba(239,68,68,.3)">
                            <i class="fas fa-trash text-xs"></i>Sil
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div style="display:grid;grid-template-columns:minmax(0,7fr) minmax(0,5fr);gap:1.25rem;align-items:start">

        {{-- LEFT --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Description --}}
            <div class="tw-card">
                <div class="tw-section-head">
                    <div style="width:36px;height:36px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-align-left text-sm" style="color:#4f46e5"></i>
                    </div>
                    <span class="font-semibold text-sm" style="color:#1e293b">Arıza Açıklaması</span>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm leading-relaxed m-0 whitespace-pre-wrap" style="color:#475569">{{ $fault->description ?: '—' }}</p>
                </div>
            </div>

            {{-- Photo --}}
            @if($fault->image_path)
            <div class="tw-card">
                <div class="tw-section-head">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-image text-sm" style="color:#2563eb"></i>
                    </div>
                    <span class="font-semibold text-sm" style="color:#1e293b">Arıza Fotoğrafı</span>
                </div>
                <div class="p-4 text-center">
                    <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank">
                        <img src="{{ asset('uploads/'.$fault->image_path) }}"
                             class="rounded-xl border object-contain w-full" style="max-height:280px"
                             alt="Arıza fotoğrafı">
                    </a>
                    <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank"
                       class="inline-flex items-center gap-1 text-xs no-underline mt-2" style="color:#94a3b8">
                        <i class="fas fa-external-link-alt text-xs"></i>Tam boyutta aç
                    </a>
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="tw-card">
                <div class="tw-section-head">
                    <div style="width:36px;height:36px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-history text-sm" style="color:#d97706"></i>
                    </div>
                    <span class="font-semibold text-sm" style="color:#1e293b">Güncelleme Geçmişi</span>
                    <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#f1f5f9;color:#64748b">{{ $fault->updates->count() }}</span>
                </div>
                <div>
                    @forelse($fault->updates as $update)
                    @php
                        $hasChange = $update->status_from && $update->status_to && $update->status_from !== $update->status_to;
                        $fromMeta  = $statusMeta[$update->status_from] ?? null;
                        $toMeta    = $statusMeta[$update->status_to]   ?? null;
                    @endphp
                    <div class="flex gap-4 px-6 py-4" style="border-bottom:1px solid #f8fafc">
                        <div class="flex-shrink-0 flex items-center justify-center text-white text-xs font-bold"
                             style="width:36px;height:36px;border-radius:50%;background:{{ $hasChange ? '#f59e0b' : '#cbd5e1' }}">
                            {{ strtoupper(substr($update->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-semibold" style="color:#1e293b">{{ $update->user?->name ?? 'Sistem' }}</span>
                                <span class="text-xs flex-shrink-0" style="color:#94a3b8">{{ $update->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            @if($hasChange && $fromMeta && $toMeta)
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full {{ $fromMeta['bg'] }} {{ $fromMeta['text'] }}">{{ $fromMeta['label'] }}</span>
                                <i class="fas fa-arrow-right text-xs" style="color:#cbd5e1"></i>
                                <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full {{ $toMeta['bg'] }} {{ $toMeta['text'] }}">{{ $toMeta['label'] }}</span>
                            </div>
                            @endif
                            @if($update->note)<p class="text-sm m-0" style="color:#64748b">{{ $update->note }}</p>@endif
                        </div>
                    </div>
                    @empty
                    <div class="py-10 text-center text-sm" style="color:#94a3b8">
                        <i class="fas fa-inbox d-block mb-2 opacity-30" style="font-size:1.5rem"></i>
                        Henüz güncelleme kaydı yok.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Status update / closed badge --}}
            @if(!$isClosed)
            <div class="tw-card overflow-hidden">
                <div style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem">
                    <div style="width:36px;height:36px;background:rgba(245,158,11,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-sync-alt text-sm" style="color:#fbbf24"></i>
                    </div>
                    <span class="text-sm font-semibold text-white">Durum Güncelle</span>
                </div>
                <div class="px-5 py-5">
                    <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                        @csrf
                        <label class="d-block text-xs font-bold uppercase tracking-wider mb-2" style="color:#94a3b8">Yeni Durum Seç</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem">
                            @foreach($statusMeta as $val => $meta)
                            @if($val !== $fault->status && !in_array($val, ['resolved']))
                            <label class="status-option d-flex align-items-center gap-2 p-2 rounded-xl cursor-pointer"
                                   style="border:2px solid #e8ecf0;background:#f8fafc;transition:all .2s;cursor:pointer"
                                   data-stripe="{{ $meta['stripe'] }}">
                                <input type="radio" name="status" value="{{ $val }}"
                                       @checked(old('status') === $val) class="status-radio d-none">
                                <i class="fas {{ $meta['icon'] }} text-xs flex-shrink-0" style="color:{{ $meta['stripe'] }}"></i>
                                <span style="font-size:.75rem;font-weight:600;color:#374151">{{ $meta['label'] }}</span>
                            </label>
                            @endif
                            @endforeach
                        </div>
                        <label class="d-block text-xs font-bold uppercase tracking-wider mb-2" style="color:#94a3b8">
                            Not / Açıklama <span style="color:#ef4444">*</span>
                        </label>
                        <textarea name="note" rows="3"
                                  class="w-full text-sm rounded-xl px-3 py-2.5 resize-none @error('note') border-red-400 @enderror"
                                  style="width:100%;border:1px solid #e2e8f0;border-radius:.75rem;padding:.6rem .75rem;font-size:.85rem;outline:none;resize:none;margin-bottom:.75rem;box-sizing:border-box"
                                  placeholder="Durum değişikliği hakkında not...">{{ old('note') }}</textarea>
                        @error('note')<p class="text-xs mb-2" style="color:#ef4444">{{ $message }}</p>@enderror
                        <button type="submit"
                                class="d-flex align-items-center justify-content-center gap-2 text-sm font-bold py-2.5 rounded-xl text-white"
                                style="width:100%;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;padding:.65rem;border-radius:.75rem;font-weight:700;font-size:.85rem;color:#fff;cursor:pointer">
                            <i class="fas fa-check text-xs"></i> Durumu Güncelle
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="tw-card">
                <div style="padding:2rem 1.5rem;text-align:center">
                    <div style="width:60px;height:60px;border-radius:50%;background:{{ $sm['stripe'] }}18;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                        <i class="fas {{ $sm['icon'] }}" style="font-size:1.5rem;color:{{ $sm['stripe'] }}"></i>
                    </div>
                    <div class="font-bold text-base" style="color:#1e293b">{{ $sm['label'] }}</div>
                    @if($fault->resolved_at)
                    <p class="text-sm mt-1 m-0" style="color:#64748b">
                        Çözüm süresi: <strong style="color:#10b981">{{ round($fault->resolutionTimeHours(), 1) }} saat</strong>
                    </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Comment --}}
            <div class="tw-card">
                <div class="tw-section-head">
                    <div style="width:36px;height:36px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-comment-dots text-sm" style="color:#0284c7"></i>
                    </div>
                    <span class="font-semibold text-sm" style="color:#1e293b">Yorum / Not Ekle</span>
                </div>
                <div class="px-5 py-4">
                    <form action="{{ route('faults.addComment', $fault) }}" method="POST">
                        @csrf
                        <textarea name="note" rows="3"
                                  style="width:100%;border:1px solid #e2e8f0;border-radius:.75rem;padding:.6rem .75rem;font-size:.85rem;outline:none;resize:none;margin-bottom:.75rem;box-sizing:border-box"
                                  placeholder="Arıza hakkında notunuzu yazın..."></textarea>
                        <button type="submit"
                                style="width:100%;background:#0284c7;border:none;padding:.65rem;border-radius:.75rem;font-weight:700;font-size:.85rem;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem">
                            <i class="fas fa-paper-plane text-xs"></i> Yorum Ekle
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="tw-card overflow-hidden">
                <div style="background:#1e293b;padding:.75rem 1.25rem;display:flex;align-items:center;gap:.5rem">
                    <i class="fas fa-clipboard-list" style="color:#94a3b8;font-size:.75rem"></i>
                    <span style="color:#94a3b8;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Özet Bilgiler</span>
                </div>
                @php
                $infoRows = [
                    ['label'=>'Arıza No',    'value'=>'#'.$fault->id,                  'type'=>'mono'],
                    ['label'=>'Öncelik',     'value'=>$pm['label'],                    'type'=>'badge','cls'=>$pm['bg'].' '.$pm['text']],
                    ['label'=>'Durum',       'value'=>$sm['label'],                    'type'=>'badge','cls'=>$sm['bg'].' '.$sm['text']],
                    ['label'=>'Şube',        'value'=>$fault->branch->name ?? '—'],
                    ['label'=>'Departman',   'value'=>$fault->department->name ?? '—'],
                    ['label'=>'Konum',       'value'=>$fault->faultLocation ? ($fault->faultLocation->name.($fault->faultArea ? ' / '.$fault->faultArea->name : '')) : '—'],
                    ['label'=>'Bildiren',    'value'=>$fault->reporter->name ?? '—'],
                    ['label'=>'Kayıt',       'value'=>$fault->created_at->format('d.m.Y H:i')],
                ];
                if ($fault->resolved_at) {
                    $infoRows[] = ['label'=>'Çözüm Tarihi','value'=>\Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i')];
                    $infoRows[] = ['label'=>'Çözüm Süresi','value'=>round($fault->resolutionTimeHours(),1).' saat','type'=>'green'];
                }
                $infoRows[] = ['label'=>'Güncellemeler','value'=>$fault->updates->count().' adet'];
                @endphp
                @foreach($infoRows as $row)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 1.25rem;border-bottom:1px solid #f1f5f9">
                    <span style="font-size:.75rem;font-weight:500;color:#94a3b8">{{ $row['label'] }}</span>
                    @if(($row['type'] ?? '') === 'badge')
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $row['cls'] }}">{{ $row['value'] }}</span>
                    @elseif(($row['type'] ?? '') === 'mono')
                    <span style="font-family:monospace;font-size:.85rem;font-weight:700;color:#1e293b">{{ $row['value'] }}</span>
                    @elseif(($row['type'] ?? '') === 'green')
                    <span style="font-size:.85rem;font-weight:700;color:#10b981">{{ $row['value'] }}</span>
                    @else
                    <span style="font-size:.82rem;font-weight:500;color:#334155">{{ $row['value'] }}</span>
                    @endif
                </div>
                @endforeach
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
document.querySelectorAll('.status-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.status-option').forEach(function(opt) {
            opt.style.borderColor = '#e8ecf0';
            opt.style.background  = '#f8fafc';
        });
        var label = this.closest('.status-option');
        if (label) {
            label.style.borderColor = label.dataset.stripe;
            label.style.background  = label.dataset.stripe + '18';
        }
    });
    if (this.checked) this.dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection

