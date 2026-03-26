@extends('layouts.default')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={corePlugins:{preflight:false}}</script>
@endpush

@section('content')
<div class="pb-6">

    {{-- BAŞLIK --}}
    <div class="rounded-2xl p-6 mb-5 text-white" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="text-xl font-bold mb-1 flex items-center gap-2">
                    <i class="fas fa-clipboard-list"></i> Bildirdiklerim
                </h3>
                <nav class="text-sm" style="color:rgba(255,255,255,.7)">
                    <a href="{{ url('/') }}" style="color:rgba(255,255,255,.7);text-decoration:none">Anasayfa</a>
                    <span class="opacity-50 mx-1">/</span>
                    <a href="{{ route('faults.index') }}" style="color:rgba(255,255,255,.7);text-decoration:none">Teknik Arıza</a>
                    <span class="opacity-50 mx-1">/</span>
                    <span>Bildirdiklerim</span>
                </nav>
            </div>
            <a href="{{ route('faults.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold"
               style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);color:#fff;text-decoration:none">
                <i class="fas fa-plus"></i> Yeni Arıza Bildir
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 mb-4 rounded-xl text-sm font-medium"
         style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- DURUM FİLTRESİ --}}
    <div class="bg-white rounded-2xl shadow-sm mb-5 px-5 py-3 flex items-center gap-2 flex-wrap"
         style="border:1px solid #f1f5f9">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Durum</span>
        <a href="{{ route('faults.my-reports') }}"
           class="px-4 py-1 rounded-full text-xs font-bold"
           style="{{ !request('status') ? 'background:#4f46e5;color:#fff' : 'background:#f1f5f9;color:#64748b' }}">
            Tümü ({{ $faults->total() }})
        </a>
        @php
            $palette = [
                'open'        => ['#dc2626', '#fef2f2'],
                'in_progress' => ['#d97706', '#fffbeb'],
                'resolved'    => ['#16a34a', '#f0fdf4'],
                'closed'      => ['#6b7280', '#f1f5f9'],
            ];
        @endphp
        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
        @php [$fg, $bg] = $palette[$val] ?? ['#6b7280','#f1f5f9']; @endphp
        <a href="{{ route('faults.my-reports', ['status'=>$val]) }}"
           class="px-4 py-1 rounded-full text-xs font-bold"
           style="{{ request('status') === $val ? "background:{$fg};color:#fff" : "background:{$bg};color:{$fg}" }}">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    {{-- ARIZA LİSTESİ --}}
    @php
        $stripCol  = ['open'=>'#dc2626','in_progress'=>'#f59e0b','resolved'=>'#22c55e','closed'=>'#94a3b8'];
        $badgeBg   = ['open'=>'#fef2f2','in_progress'=>'#fffbeb','resolved'=>'#f0fdf4','closed'=>'#f1f5f9'];
        $badgeFg   = ['open'=>'#dc2626','in_progress'=>'#d97706','resolved'=>'#15803d','closed'=>'#64748b'];
    @endphp

    @forelse($faults as $fault)
    @php $isMine = $fault->reported_by === auth()->id(); @endphp
    <div class="bg-white rounded-2xl shadow-sm mb-3 flex overflow-hidden"
         style="border:1px solid #f1f5f9;border-left:4px solid {{ $stripCol[$fault->status] ?? '#94a3b8' }}">

        {{-- İÇERİK --}}
        <div class="flex-1 p-5">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                      style="background:{{ $badgeBg[$fault->status] ?? '#f1f5f9' }};color:{{ $badgeFg[$fault->status] ?? '#64748b' }}">
                    {{ \App\Models\Fault::STATUSES[$fault->status] }}
                </span>
                @if($isMine)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold"
                      style="background:#eff6ff;color:#2563eb">
                    <i class="fas fa-user" style="font-size:0.6rem"></i> Ben bildirdim
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                      style="background:#f8fafc;color:#94a3b8">Departmanım</span>
                @endif
                <span class="text-xs text-gray-400">#{{ $fault->id }} &middot; {{ $fault->created_at->format('d.m.Y H:i') }}</span>
            </div>

            <h5 class="font-bold text-gray-800 mb-2">
                <a href="{{ route('faults.show', $fault) }}" style="text-decoration:none;color:inherit">
                    {{ $fault->title }}
                </a>
            </h5>

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400 mb-2">
                @if($fault->branch)
                <span><i class="fas fa-building mr-1"></i>{{ $fault->branch->name }}</span>
                @endif
                @if($fault->department)
                <span><i class="fas fa-users mr-1"></i>{{ $fault->department->name }}</span>
                @endif
                @if($fault->faultLocation)
                <span>
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $fault->faultLocation->name }}
                    {{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}
                </span>
                @endif
                @if($fault->faultType)
                <span><i class="fas fa-tag mr-1"></i>{{ $fault->faultType->name }}</span>
                @endif
            </div>

            <p class="text-sm text-gray-500 leading-relaxed">{{ Str::limit($fault->description, 140) }}</p>
        </div>

        {{-- EYLEMLER --}}
        <div class="flex flex-col justify-center p-4 gap-2" style="min-width:210px;border-left:1px solid #f1f5f9">
            @if($isMine && $fault->status !== 'closed')
            <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Güncelle</p>
                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                    @csrf
                    <select name="status" class="w-full text-sm rounded-lg px-2 py-1.5 mb-2"
                            style="border:1px solid #e2e8f0;outline:none">
                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                        @if($val !== $fault->status)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endif
                        @endforeach
                    </select>
                    <textarea name="note" rows="2" required placeholder="Not yazın..."
                              class="w-full text-sm rounded-lg px-2 py-1.5 mb-2 resize-none"
                              style="border:1px solid #e2e8f0;outline:none"></textarea>
                    <button class="w-full py-1.5 rounded-lg text-xs font-bold text-white"
                            style="background:#4f46e5">Güncelle</button>
                </form>
            </div>
            @elseif($fault->status === 'closed')
            <div class="text-center py-2">
                <i class="fas fa-check-circle text-2xl block mb-1" style="color:#94a3b8"></i>
                <span class="text-xs text-gray-400">Kapalı · {{ $fault->closed_at?->format('d.m.Y') }}</span>
            </div>
            @endif
            <a href="{{ route('faults.show', $fault) }}"
               class="flex items-center justify-center gap-1 py-1.5 rounded-lg text-xs font-bold"
               style="background:#f1f5f9;color:#374151;text-decoration:none">
                <i class="fas fa-eye"></i> Detay
            </a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm py-16 text-center" style="border:1px solid #f1f5f9">
        <i class="fas fa-clipboard-list text-5xl block mb-4" style="color:#e2e8f0"></i>
        <h5 class="font-semibold text-gray-400 mb-3">Henüz bildirim yapmadınız.</h5>
        <a href="{{ route('faults.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-bold text-white"
           style="background:#4f46e5;text-decoration:none">
            <i class="fas fa-plus"></i> İlk Arızayı Bildir
        </a>
    </div>
    @endforelse

    @if($faults->hasPages())
    <div class="mt-4">{{ $faults->links() }}</div>
    @endif

</div>
@endsection