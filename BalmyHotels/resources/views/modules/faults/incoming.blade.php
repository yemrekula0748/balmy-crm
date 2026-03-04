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
            @endphp
            <div class="{{ $loop->last ? '' : 'border-bottom' }}" style="border-left:4px solid {{ $borderHex }}">
                <div class="p-3 p-md-4">
                    <div class="row align-items-start g-3">

                        {{-- Sol: Bilgiler --}}
                        <div class="col-12 col-md-7">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge" style="background:{{ $borderHex }}">
                                    {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                </span>
                                @if($fault->faultType)
                                <span class="badge bg-light text-dark border" style="font-weight:500">
                                    {{ $fault->faultType->name }}
                                </span>
                                @endif
                                <span class="text-muted" style="font-size:0.75rem">
                                    <i class="fas fa-hashtag me-1"></i>{{ $fault->id }}
                                </span>
                                <span class="text-muted ms-auto" style="font-size:0.75rem"
                                      title="{{ $fault->created_at->format('d.m.Y H:i') }}">
                                    <i class="fas fa-clock me-1 {{ $isOld ? 'text-danger' : '' }}"></i>
                                    {{ $fault->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <h6 class="fw-semibold mb-2">
                                <a href="{{ route('faults.show', $fault) }}" class="text-dark text-decoration-none">
                                    {{ $fault->title }}
                                </a>
                            </h6>

                            @if($fault->description)
                            <p class="text-muted mb-2" style="font-size:0.82rem;line-height:1.5">
                                {{ Str::limit($fault->description, 150) }}
                            </p>
                            @endif

                            <div class="d-flex flex-wrap gap-3 text-muted" style="font-size:0.78rem">
                                @if($fault->faultLocation)
                                <span>
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                    {{ $fault->faultLocation->name }}
                                    @if($fault->faultArea)
                                        <span> / {{ $fault->faultArea->name }}</span>
                                    @endif
                                </span>
                                @endif
                                @if($fault->branch)
                                <span><i class="fas fa-building me-1 text-secondary"></i>{{ $fault->branch->name }}</span>
                                @endif
                                @if($fault->reporter)
                                <span><i class="fas fa-user me-1 text-secondary"></i>{{ $fault->reporter->name }}</span>
                                @endif
                                @if($fault->image_path)
                                <a href="{{ Storage::url($fault->image_path) }}" target="_blank" class="text-info text-decoration-none">
                                    <i class="fas fa-image me-1"></i>Fotoğraf
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Sağ: Güncelle --}}
                        <div class="col-12 col-md-5">
                            @if($canUpdate && $fault->status !== 'closed')
                            <div class="rounded-2 p-3" style="background:#f8f9fa;border:1px solid #e9ecef">
                                <div class="fw-semibold small mb-2 text-dark">
                                    <i class="fas fa-edit me-1 text-primary"></i>Durum Güncelle
                                </div>
                                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm mb-2" required>
                                        <option value="" disabled selected>Yeni durum seçin…</option>
                                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                            @if($val !== $fault->status && $val !== 'resolved')
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <textarea name="note" class="form-control form-control-sm mb-2" rows="2"
                                              placeholder="Açıklama (zorunlu)…" required></textarea>
                                    <button class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-save me-1"></i>Kaydet
                                    </button>
                                </form>
                            </div>
                            @elseif($fault->status === 'closed')
                            <div class="text-center py-3">
                                <span class="badge py-2 px-3" style="background:#e8f5e9;color:#10b981;font-size:0.82rem">
                                    <i class="fas fa-check-double me-1"></i>Kapalı
                                </span>
                            </div>
                            @else
                            <div class="text-muted text-center small py-3">
                                <i class="fas fa-lock me-1"></i>Güncelleme yetkiniz yok
                            </div>
                            @endif

                            <div class="mt-2 text-end">
                                <a href="{{ route('faults.show', $fault) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detay
                                </a>
                            </div>
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
@endsection
