@extends('layouts.default')

@section('content')
<div class="container-fluid">
    {{-- Başlık --}}
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
    <div class="row g-3 mb-4">
        @php
        $statCards = [
            ['label'=>'Toplam',  'value'=>$stats['total'],       'icon'=>'fa-clipboard-list','color'=>'#4361ee','bg'=>'#eef0ff'],
            ['label'=>'Açık',    'value'=>$stats['open'],        'icon'=>'fa-exclamation-circle','color'=>'#dc3545','bg'=>'#fdecea'],
            ['label'=>'İşlemde', 'value'=>$stats['in_progress'], 'icon'=>'fa-tools','color'=>'#f97316','bg'=>'#fff3e0'],
            ['label'=>'Kapalı',  'value'=>$stats['closed'],      'icon'=>'fa-check-double','color'=>'#10b981','bg'=>'#e8f5e9'],
        ];
        @endphp
        @foreach($statCards as $sc)
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 h-100" style="box-shadow:0 2px 12px rgba(0,0,0,.07);border-bottom:3px solid {{ $sc['color'] }} !important">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:{{ $sc['bg'] }}">
                        <i class="fas {{ $sc['icon'] }}" style="color:{{ $sc['color'] }};font-size:20px"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.6rem;color:{{ $sc['color'] }};line-height:1.1">{{ $sc['value'] }}</div>
                        <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.04em">{{ $sc['label'] }}</div>
                    </div>
                    @if($sc['label'] === 'Toplam' && $stats['avg_hours'] !== null)
                    <div class="ms-auto text-end">
                        <div class="text-muted" style="font-size:10px">Ort. Süre</div>
                        <div class="fw-semibold small text-muted">{{ $stats['avg_hours'] }} sa</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre & Arama --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Başlık veya açıklama ara..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:150px">
                        <option value="">Tüm Durumlar</option>
                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Filtrele
                    </button>
                    @if(request('search') || request('status'))
                    <a href="{{ route('faults.incoming') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Temizle
                    </a>
                    @endif
                </div>
                <div class="col-auto ms-md-auto text-muted small">
                    {{ $faults->total() }} kayıt
                </div>
            </form>
        </div>
    </div>

    {{-- Arıza Listesi --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @forelse($faults as $fault)
            @php
                $statusColor = \App\Models\Fault::STATUS_COLORS[$fault->status] ?? 'secondary';
                $statusBorderColors = ['danger'=>'#dc3545','warning'=>'#f97316','info'=>'#0ea5e9','success'=>'#10b981','secondary'=>'#6c757d','primary'=>'#4361ee'];
                $borderHex = $statusBorderColors[$statusColor] ?? '#6c757d';
                $isOld = $fault->created_at->diffInHours(now()) > 24;
                $prioMap = [
                    'low'      => ['Normal',  '#10b981', '#f0fdf4', '🟢'],
                    'medium'   => ['Orta',    '#d97706', '#fffbeb', '🟡'],
                    'high'     => ['Acil',    '#dc2626', '#fef2f2', '🔴'],
                    'critical' => ['Kritik',  '#7c3aed', '#f5f3ff', '🔴'],
                ];
                [$prioLabel, $prioFg, $prioBg, $prioEmoji] = $prioMap[$fault->priority] ?? ['—','#6b7280','#f1f5f9','⚪'];
                $statusLabel   = \App\Models\Fault::STATUSES[$fault->status] ?? $fault->status;
                $locationStr   = $fault->faultLocation?->name ?? '';
                $areaStr       = $fault->faultArea?->name ?? '';
                $fullLocation  = $locationStr . ($areaStr ? ' / ' . $areaStr : '');
                $branchStr     = $fault->branch?->name ?? '';
                $reporterStr   = $fault->reporter?->name ?? '';
                $typeStr       = $fault->faultType?->name ?? '';
                $dateStr       = $fault->created_at->format('d.m.Y H:i');

                $copyText = implode("\n", array_filter([
                    '🔧 *ARIZA BİLDİRİMİ*',
                    '─────────────────────────',
                    '*#' . $fault->id . ' — ' . $fault->title . '*',
                    '',
                    '📊 Durum: ' . $statusLabel,
                    '⚡ Öncelik: ' . mb_strtoupper($prioLabel),
                    $typeStr       ? '🔩 Tür: ' . $typeStr       : '',
                    $fullLocation  ? '📍 Konum: ' . $fullLocation : '',
                    $branchStr     ? '🏨 Tesis: ' . $branchStr    : '',
                    $reporterStr   ? '👤 Bildiren: ' . $reporterStr : '',
                    '🕐 Tarih: ' . $dateStr,
                    $fault->description ? "\n📝 Açıklama:\n" . $fault->description : '',
                ]));
            @endphp

            {{-- Kart --}}
            <div class="{{ $loop->last ? '' : 'border-bottom' }} fault-row"
                 style="border-left:4px solid {{ $borderHex }};transition:background .15s">
                <div class="p-3 p-md-4">
                    <div class="row align-items-start g-3">

                        {{-- Sol: Bilgiler --}}
                        <div class="col-12 col-md-7 col-lg-8">

                            {{-- Üst meta satırı --}}
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                {{-- Durum --}}
                                <span class="badge rounded-pill px-3" style="background:{{ $borderHex }};font-size:.75rem">
                                    {{ $statusLabel }}
                                </span>
                                {{-- Öncelik --}}
                                <span class="badge rounded-pill px-3" style="background:{{ $prioBg }};color:{{ $prioFg }};font-size:.75rem;border:1px solid {{ $prioFg }}22">
                                    <i class="fas fa-flag me-1" style="font-size:.6rem"></i>{{ $prioLabel }}
                                </span>
                                {{-- Tür --}}
                                @if($fault->faultType)
                                <span class="badge bg-light text-dark border px-2" style="font-size:.75rem">
                                    <i class="fas fa-wrench me-1 text-muted" style="font-size:.65rem"></i>{{ $fault->faultType->name }}
                                </span>
                                @endif
                                {{-- ID & Zaman --}}
                                <span class="text-muted px-2 py-1 rounded-1 ms-auto d-flex align-items-center gap-2"
                                      style="background:#f8f9fa;font-size:.74rem">
                                    <span class="fw-semibold" style="color:#4361ee">#{{ $fault->id }}</span>
                                    <span class="text-muted" style="opacity:.4">|</span>
                                    <span title="{{ $fault->created_at->format('d.m.Y H:i') }}">
                                        <i class="fas fa-clock me-1 {{ $isOld ? 'text-danger' : 'text-muted' }}" style="font-size:.7rem"></i>{{ $fault->created_at->diffForHumans() }}
                                    </span>
                                </span>
                            </div>

                            {{-- Başlık --}}
                            <h6 class="fw-bold mb-2" style="font-size:1rem;line-height:1.4">
                                <a href="{{ route('faults.show', $fault) }}"
                                   class="text-decoration-none"
                                   style="color:#1e293b">
                                    {{ $fault->title }}
                                </a>
                            </h6>

                            {{-- Açıklama --}}
                            @if($fault->description)
                            <p class="mb-3" style="font-size:0.83rem;color:#475569;line-height:1.6;border-left:3px solid {{ $borderHex }}33;padding-left:.6rem;background:{{ $borderHex }}08;border-radius:0 4px 4px 0;padding:.4rem .6rem">
                                {{ Str::limit($fault->description, 200) }}
                            </p>
                            @endif

                            {{-- Meta çizgisi: konum / tesis / bildiren / fotoğraf --}}
                            <div class="d-flex flex-wrap gap-2 align-items-center" style="font-size:0.8rem">
                                @if($fault->faultLocation)
                                <span class="d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1"
                                      style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
                                    <i class="fas fa-map-marker-alt" style="font-size:.65rem"></i>
                                    {{ $fault->faultLocation->name }}
                                    @if($fault->faultArea)
                                        <span style="opacity:.5">/</span>{{ $fault->faultArea->name }}
                                    @endif
                                </span>
                                @endif

                                @if($fault->branch)
                                <span class="d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1"
                                      style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                    <i class="fas fa-building" style="font-size:.65rem"></i>
                                    {{ $fault->branch->name }}
                                </span>
                                @endif

                                @if($fault->reporter)
                                <span class="d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1"
                                      style="background:#faf5ff;color:#7e22ce;border:1px solid #e9d5ff">
                                    <i class="fas fa-user" style="font-size:.65rem"></i>
                                    {{ $fault->reporter->name }}
                                </span>
                                @endif

                                @if($fault->image_path)
                                <a href="{{ Storage::url($fault->image_path) }}" target="_blank"
                                   class="d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1 text-decoration-none"
                                   style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa">
                                    <i class="fas fa-camera" style="font-size:.65rem"></i>
                                    Fotoğraf
                                </a>
                                @endif
                            </div>

                            {{-- Aksiyon butonları --}}
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('faults.show', $fault) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detay
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-success copy-fault-btn"
                                        data-copy="{{ e($copyText) }}"
                                        title="Arıza bilgilerini panoya kopyala">
                                    <i class="fas fa-copy me-1"></i>Kopyala
                                </button>
                            </div>

                        </div>

                        {{-- Sağ: Durum Güncelle --}}
                        <div class="col-12 col-md-5 col-lg-4">
                            @if($canUpdate && $fault->status !== 'closed')
                            <div class="rounded-3 p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                                <div class="fw-semibold small mb-3" style="color:#475569">
                                    <i class="fas fa-edit me-1" style="color:#4361ee"></i>Durum Güncelle
                                </div>
                                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm mb-2" required
                                            style="border-color:#cbd5e1;font-size:.82rem">
                                        <option value="" disabled selected>Yeni durum seçin…</option>
                                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                            @if($val !== $fault->status && $val !== 'resolved')
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <textarea name="note" class="form-control form-control-sm mb-2" rows="2"
                                              placeholder="Açıklama (zorunlu)…" required
                                              style="border-color:#cbd5e1;font-size:.82rem;resize:none"></textarea>
                                    <button class="btn btn-primary btn-sm w-100" style="font-size:.82rem">
                                        <i class="fas fa-save me-1"></i>Kaydet
                                    </button>
                                </form>
                            </div>

                            @elseif($fault->status === 'closed')
                            <div class="text-center py-3">
                                <span class="badge py-2 px-3 rounded-pill"
                                      style="background:#dcfce7;color:#15803d;font-size:0.8rem;border:1px solid #bbf7d0">
                                    <i class="fas fa-check-double me-1"></i>Kapalı
                                </span>
                                <div class="text-muted mt-2" style="font-size:.75rem">
                                    {{ $fault->closed_at ? $fault->closed_at->format('d.m.Y H:i') : '' }}
                                </div>
                            </div>

                            @else
                            <div class="text-muted text-center small py-3" style="font-size:.8rem">
                                <i class="fas fa-lock me-1 d-block mb-1" style="font-size:1.1rem;opacity:.4"></i>
                                Güncelleme yetkiniz yok
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:#d1d5db"></i>
                <h5 class="text-muted">Arıza kaydı bulunamadı.</h5>
                @if(request('search') || request('status'))
                <p class="text-muted small">Filtreleri temizleyerek tüm kayıtları görebilirsiniz.</p>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    @if($faults->hasPages())
    <div class="mt-3">{{ $faults->appends(request()->query())->links() }}</div>
    @endif

</div>

{{-- Kopyala bildirimi --}}
<div id="copy-toast"
     style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
            background:#1e293b;color:#fff;padding:.6rem 1.1rem;
            border-radius:8px;font-size:.85rem;box-shadow:0 4px 20px rgba(0,0,0,.25);
            align-items:center;gap:.5rem">
    <i class="fas fa-check-circle text-success"></i>
    <span>Arıza bilgileri panoya kopyalandı!</span>
</div>

@push('scripts')
<script>
document.querySelectorAll('.copy-fault-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var text = this.getAttribute('data-copy');
        var self = this;

        navigator.clipboard.writeText(text).then(function() {
            // Geçici başarı göstergesi
            var icon = self.querySelector('i');
            var origHtml = self.innerHTML;
            self.innerHTML = '<i class="fas fa-check me-1"></i>Kopyalandı!';
            self.classList.remove('btn-outline-success');
            self.classList.add('btn-success');
            self.disabled = true;

            var toast = document.getElementById('copy-toast');
            toast.style.display = 'flex';

            setTimeout(function() {
                self.innerHTML = origHtml;
                self.classList.add('btn-outline-success');
                self.classList.remove('btn-success');
                self.disabled = false;
                toast.style.display = 'none';
            }, 2000);
        }).catch(function() {
            // Fallback: eski tarayıcılar için
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);

            var toast = document.getElementById('copy-toast');
            toast.style.display = 'flex';
            setTimeout(function() { toast.style.display = 'none'; }, 2000);
        });
    });
});

// Hover efekti
document.querySelectorAll('.fault-row').forEach(function(row) {
    row.addEventListener('mouseenter', function() {
        this.style.background = '#fafbff';
    });
    row.addEventListener('mouseleave', function() {
        this.style.background = '';
    });
});
</script>
@endpush
@endsection
