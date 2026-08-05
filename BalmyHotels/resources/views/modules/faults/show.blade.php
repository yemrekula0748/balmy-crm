@extends('layouts.default')

@push('styles')
<style>
.fault-card{background:#fff;border-radius:1rem;border:1px solid #e8ecf0;box-shadow:0 1px 8px rgba(15,23,42,.06)}
.fault-sec-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9}
.fault-badge{display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:700;padding:.3rem .75rem;border-radius:9999px;line-height:1.2}
.fault-update-image{display:block;margin-top:.65rem;max-width:260px;border-radius:.75rem;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc}
.fault-update-image img{display:block;width:100%;max-height:190px;object-fit:cover;transition:transform .2s ease}
.fault-update-image:hover img{transform:scale(1.02)}
.fault-image-upload{margin-bottom:.75rem;padding:.75rem;border:1px dashed #cbd5e1;border-radius:.75rem;background:#f8fafc}
.fault-image-upload .form-control{font-size:.78rem;background:#fff}
.fault-image-preview{display:none;margin-top:.65rem;align-items:center;gap:.65rem}
.fault-image-preview.is-visible{display:flex}
.fault-image-preview img{width:64px;height:64px;object-fit:cover;border-radius:.6rem;border:1px solid #e2e8f0}
</style>
@endpush

@section('content')
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
    'open'             => ['stripe'=>'#ef4444','badgeBg'=>'#fee2e2','badgeFg'=>'#b91c1c','dotColor'=>'#ef4444','label'=>'Açık',            'icon'=>'fa-circle-exclamation'],
    'in_progress'      => ['stripe'=>'#f59e0b','badgeBg'=>'#fef3c7','badgeFg'=>'#b45309','dotColor'=>'#f59e0b','label'=>'İşlemde',         'icon'=>'fa-rotate'],
    'winter_plan'      => ['stripe'=>'#0ea5e9','badgeBg'=>'#e0f2fe','badgeFg'=>'#0369a1','dotColor'=>'#0ea5e9','label'=>'Kış Planı',       'icon'=>'fa-snowflake'],
    'waiting_material' => ['stripe'=>'#7c3aed','badgeBg'=>'#ede9fe','badgeFg'=>'#6d28d9','dotColor'=>'#7c3aed','label'=>'Malzeme Bekliyor','icon'=>'fa-box'],
    'resolved'         => ['stripe'=>'#10b981','badgeBg'=>'#d1fae5','badgeFg'=>'#047857','dotColor'=>'#10b981','label'=>'Çözüldü',         'icon'=>'fa-check-circle'],
    'closed'           => ['stripe'=>'#64748b','badgeBg'=>'#f1f5f9','badgeFg'=>'#475569','dotColor'=>'#94a3b8','label'=>'Kapalı',          'icon'=>'fa-lock'],
];
$priorityMeta = [
    'low'      => ['icon'=>'fa-circle-check',        'stripe'=>'#10b981','badgeBg'=>'#d1fae5','badgeFg'=>'#047857','label'=>'Normal'],
    'medium'   => ['icon'=>'fa-triangle-exclamation','stripe'=>'#f59e0b','badgeBg'=>'#fef3c7','badgeFg'=>'#b45309','label'=>'Orta'],
    'high'     => ['icon'=>'fa-bolt',                'stripe'=>'#ef4444','badgeBg'=>'#fee2e2','badgeFg'=>'#b91c1c','label'=>'Acil'],
    'critical' => ['icon'=>'fa-skull-crossbones',    'stripe'=>'#1e293b','badgeBg'=>'#1e293b','badgeFg'=>'#ffffff','label'=>'Kritik'],
];
$sm       = $statusMeta[$fault->status]     ?? $statusMeta['open'];
$pm       = $priorityMeta[$fault->priority] ?? $priorityMeta['medium'];
$isClosed = in_array($fault->status, ['resolved','closed']);
@endphp

<div class="container-fluid pb-4">

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
        <i class="fas fa-check-circle"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
        <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
    </div>
    @endif

    {{-- ══ HERO ══ --}}
    <div style="border-radius:1rem;overflow:hidden;margin-bottom:1.5rem;background:#0f172a;box-shadow:0 4px 24px rgba(0,0,0,.18)">
        <div style="height:4px;background:{{ $sm['stripe'] }}"></div>
        <div style="padding:1.5rem">
            <div class="d-flex flex-wrap align-items-start justify-content-between" style="gap:1rem">
                <div style="flex:1;min-width:0">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin-bottom:.5rem">
                        Arıza Kaydı &nbsp;·&nbsp; <span style="color:#94a3b8">#{{ $fault->id }}</span>
                    </div>
                    <h1 style="font-size:1.25rem;font-weight:700;line-height:1.3;color:#f1f5f9;margin-bottom:.75rem">{{ $fault->title }}</h1>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="fault-badge" style="background:{{ $pm['badgeBg'] }};color:{{ $pm['badgeFg'] }}">
                            <i class="fas {{ $pm['icon'] }}" style="font-size:.62rem"></i>{{ $pm['label'] }} Öncelik
                        </span>
                        <span class="fault-badge" style="background:{{ $sm['badgeBg'] }};color:{{ $sm['badgeFg'] }}">
                            <span style="width:.45rem;height:.45rem;border-radius:50%;background:{{ $sm['dotColor'] }};display:inline-block;flex-shrink:0"></span>{{ $sm['label'] }}
                        </span>
                        @if($fault->faultType)
                        <span class="fault-badge" style="background:#ede9fe;color:#6d28d9">
                            <i class="fas fa-tag" style="font-size:.62rem"></i>{{ $fault->faultType->name }}
                        </span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap" style="gap:.35rem 1.5rem;font-size:.76rem;color:#94a3b8">
                        <span><i class="fas fa-user me-1"></i><span style="color:#64748b">Bildiren:</span> <strong style="color:#cbd5e1">{{ $fault->reporter->name ?? '—' }}</strong></span>
                        @if($fault->department)<span><i class="fas fa-users me-1"></i><span style="color:#64748b">Dept:</span> <strong style="color:#cbd5e1">{{ $fault->department->name }}</strong></span>@endif
                        @if($fault->branch)<span><i class="fas fa-building me-1"></i><strong style="color:#cbd5e1">{{ $fault->branch->name }}</strong></span>@endif
                        @if($fault->faultLocation)<span><i class="fas fa-map-marker-alt me-1"></i><strong style="color:#cbd5e1">{{ $fault->faultLocation->name }}{{ $fault->faultArea ? ' / '.$fault->faultArea->name : '' }}</strong></span>@endif
                        <span><i class="fas fa-calendar me-1"></i><span style="color:#64748b">Kayıt:</span> <strong style="color:#cbd5e1">{{ $fault->created_at->format('d.m.Y H:i') }}</strong></span>
                        @if($fault->resolved_at)<span style="color:#10b981"><i class="fas fa-check-double me-1"></i>Çözüldü: <strong>{{ \Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i') }}</strong> ({{ $fault->resolutionTimeLabel() }})</span>@endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ $backUrl }}"
                       style="display:inline-flex;align-items:center;gap:.45rem;font-size:.84rem;font-weight:500;padding:.45rem 1rem;border-radius:.75rem;background:rgba(255,255,255,.08);color:#cbd5e1;text-decoration:none">
                        <i class="fas fa-arrow-left" style="font-size:.72rem"></i>Geri
                    </a>
                    @if(!$isClosed)
                    <form action="{{ route('faults.destroy', $fault) }}" method="POST" id="deleteForm">
                        @csrf @method('DELETE')
                        <button type="button" id="deleteBtn"
                                style="display:inline-flex;align-items:center;gap:.45rem;font-size:.84rem;font-weight:500;padding:.45rem 1rem;border-radius:.75rem;background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3);cursor:pointer">
                            <i class="fas fa-trash" style="font-size:.72rem"></i>Sil
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="row g-3 align-items-start">

        {{-- LEFT --}}
        <div class="col-lg-7">
            <div class="d-flex flex-column gap-3">

                {{-- Description --}}
                <div class="fault-card">
                    <div class="fault-sec-head">
                        <div style="width:36px;height:36px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-align-left" style="color:#4f46e5;font-size:.85rem"></i>
                        </div>
                        <span style="font-weight:600;font-size:.85rem;color:#1e293b">Arıza Açıklaması</span>
                    </div>
                    <div style="padding:1rem 1.5rem">
                        <p style="font-size:.875rem;line-height:1.65;margin:0;white-space:pre-wrap;color:#475569">{{ $fault->description ?: '—' }}</p>
                    </div>
                </div>

                {{-- Photo --}}
                @if($fault->image_path)
                <div class="fault-card">
                    <div class="fault-sec-head">
                        <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-image" style="color:#2563eb;font-size:.85rem"></i>
                        </div>
                        <span style="font-weight:600;font-size:.85rem;color:#1e293b">Arıza Fotoğrafı</span>
                    </div>
                    <div style="padding:1rem;text-align:center">
                        <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank">
                            <img src="{{ asset('uploads/'.$fault->image_path) }}"
                                 style="border-radius:.75rem;border:1px solid #e8ecf0;object-fit:contain;width:100%;max-height:280px"
                                 alt="Arıza fotoğrafı">
                        </a>
                        <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:.25rem;font-size:.75rem;text-decoration:none;margin-top:.5rem;color:#94a3b8">
                            <i class="fas fa-external-link-alt" style="font-size:.7rem"></i>Tam boyutta aç
                        </a>
                    </div>
                </div>
                @endif

                {{-- Timeline --}}
                <div class="fault-card">
                    <div class="fault-sec-head">
                        <div style="width:36px;height:36px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-history" style="color:#d97706;font-size:.85rem"></i>
                        </div>
                        <span style="font-weight:600;font-size:.85rem;color:#1e293b">Güncelleme Geçmişi</span>
                        <span style="margin-left:auto;font-size:.72rem;font-weight:700;padding:.125rem .5rem;border-radius:9999px;background:#f1f5f9;color:#64748b">{{ $fault->updates->count() }}</span>
                    </div>
                    <div>
                        @forelse($fault->updates as $update)
                        @php
                            $hasChange = $update->status_from && $update->status_to && $update->status_from !== $update->status_to;
                            $fromMeta  = $statusMeta[$update->status_from] ?? null;
                            $toMeta    = $statusMeta[$update->status_to]   ?? null;
                        @endphp
                        <div style="display:flex;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid #f8fafc">
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $hasChange ? '#f59e0b' : '#cbd5e1' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr($update->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.25rem">
                                    <div style="display:flex;align-items:center;gap:.4rem;min-width:0;flex-wrap:wrap">
                                        <span style="font-size:.85rem;font-weight:600;color:#1e293b">{{ $update->user?->name ?? 'Sistem' }}</span>
                                        @if($update->user?->department)
                                        <span style="font-size:.62rem;font-weight:700;padding:.15rem .45rem;border-radius:9999px;background:#eef2ff;color:#4f46e5">
                                            {{ $update->user->department->name }}
                                        </span>
                                        @endif
                                    </div>
                                    <span style="font-size:.72rem;flex-shrink:0;color:#94a3b8">{{ $update->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                                @if($hasChange && $fromMeta && $toMeta)
                                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem">
                                    <span class="fault-badge" style="background:{{ $fromMeta['badgeBg'] }};color:{{ $fromMeta['badgeFg'] }}">{{ $fromMeta['label'] }}</span>
                                    <i class="fas fa-arrow-right" style="font-size:.7rem;color:#cbd5e1"></i>
                                    <span class="fault-badge" style="background:{{ $toMeta['badgeBg'] }};color:{{ $toMeta['badgeFg'] }}">{{ $toMeta['label'] }}</span>
                                </div>
                                @endif
                                @if($update->note)<p style="font-size:.84rem;margin:0;color:#64748b">{{ $update->note }}</p>@endif
                                @if($update->image_path)
                                <a href="{{ asset('uploads/'.$update->image_path) }}" target="_blank" rel="noopener"
                                   class="fault-update-image" title="Görseli tam boyutta aç">
                                    <img src="{{ asset('uploads/'.$update->image_path) }}"
                                         alt="Güncelleme görseli" loading="lazy">
                                </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div style="padding:2.5rem 1.5rem;text-align:center;font-size:.85rem;color:#94a3b8">
                            <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:.3"></i>
                            Henüz güncelleme kaydı yok.
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-3">

                {{-- Status update / closed badge --}}
                @if($canUpdate && !$isClosed)
                <div class="fault-card overflow-hidden">
                    <div style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem">
                        <div style="width:36px;height:36px;background:rgba(245,158,11,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-sync-alt" style="color:#fbbf24;font-size:.85rem"></i>
                        </div>
                        <span style="font-size:.875rem;font-weight:600;color:#fff">Durum Güncelle</span>
                    </div>
                    <div style="padding:1.25rem 1.5rem">
                        <form action="{{ route('faults.updateStatus', $fault) }}" method="POST"
                              enctype="multipart/form-data" id="statusUpdateForm">
                            @csrf
                            <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.5rem">Yeni Durum Seç</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem">
                                @foreach($statusMeta as $val => $meta)
                                @if($val !== $fault->status && !in_array($val, ['resolved']))
                                <label class="status-option" data-stripe="{{ $meta['stripe'] }}"
                                       style="display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-radius:.75rem;border:2px solid #e8ecf0;background:#f8fafc;cursor:pointer;transition:border-color .2s,background-color .2s">
                                    <input type="radio" name="status" value="{{ $val }}"
                                           @checked(old('status') === $val) class="status-radio" style="display:none">
                                    <i class="fas {{ $meta['icon'] }}" style="color:{{ $meta['stripe'] }};font-size:.75rem;flex-shrink:0"></i>
                                    <span style="font-size:.75rem;font-weight:600;color:#374151">{{ $meta['label'] }}</span>
                                </label>
                                @endif
                                @endforeach
                            </div>
                            <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.5rem">
                                Not / Açıklama <span style="color:#ef4444">*</span>
                            </label>
                            <textarea name="note" id="statusNote" rows="3"
                                      style="width:100%;border:1px solid #e2e8f0;border-radius:.75rem;padding:.6rem .75rem;font-size:.85rem;outline:none;resize:none;margin-bottom:.75rem;box-sizing:border-box"
                                      placeholder="Durum değişikliği hakkında not...">{{ old('note') }}</textarea>
                            @error('note')<p style="font-size:.75rem;margin-bottom:.5rem;color:#ef4444">{{ $message }}</p>@enderror
                            <div class="fault-image-upload">
                                <label for="statusImage" style="display:block;font-size:.72rem;font-weight:700;color:#475569;margin-bottom:.4rem">
                                    <i class="fas fa-camera me-1" style="color:#f59e0b"></i> Görsel Ekle
                                    <span style="font-weight:400;color:#94a3b8">(isteğe bağlı)</span>
                                </label>
                                <input type="file" name="status_image" id="statusImage"
                                       class="form-control @error('status_image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp"
                                       data-fault-image-input data-preview-target="statusImagePreview">
                                <div style="font-size:.68rem;color:#94a3b8;margin-top:.3rem">JPG, PNG veya WebP · En fazla 4 MB</div>
                                @error('status_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="fault-image-preview" id="statusImagePreview">
                                    <img src="" alt="Seçilen görsel önizlemesi">
                                    <span style="font-size:.72rem;color:#64748b">Seçilen görsel güncelleme geçmişinde gösterilecek.</span>
                                </div>
                            </div>
                            <button type="submit"
                                    style="width:100%;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;padding:.65rem;border-radius:.75rem;font-weight:700;font-size:.875rem;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem">
                                <i class="fas fa-check" style="font-size:.75rem"></i> Durumu Güncelle
                            </button>
                        </form>
                    </div>
                </div>
                @elseif($isClosed)
                <div class="fault-card">
                    <div style="padding:2rem 1.5rem;text-align:center">
                        <div style="width:60px;height:60px;border-radius:50%;background:{{ $sm['stripe'] }}18;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                            <i class="fas {{ $sm['icon'] }}" style="font-size:1.5rem;color:{{ $sm['stripe'] }}"></i>
                        </div>
                        <div style="font-weight:700;font-size:1rem;color:#1e293b">{{ $sm['label'] }}</div>
                        @if($fault->resolved_at)
                        <p style="font-size:.85rem;margin-top:.25rem;margin-bottom:0;color:#64748b">
                            Çözüm süresi: <strong style="color:#10b981">{{ $fault->resolutionTimeLabel() }}</strong>
                        </p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Comment --}}
                @if($canUpdate)
                <div class="fault-card">
                    <div class="fault-sec-head">
                        <div style="width:36px;height:36px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-comment-dots" style="color:#0284c7;font-size:.85rem"></i>
                        </div>
                        <span style="font-weight:600;font-size:.875rem;color:#1e293b">Yorum / Not Ekle</span>
                    </div>
                    <div style="padding:1rem 1.5rem">
                        <form action="{{ route('faults.addComment', $fault) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <textarea name="note" rows="3"
                                       style="width:100%;border:1px solid #e2e8f0;border-radius:.75rem;padding:.6rem .75rem;font-size:.85rem;outline:none;resize:none;margin-bottom:.75rem;box-sizing:border-box"
                                       placeholder="Arıza hakkında notunuzu yazın..."></textarea>
                            <div class="fault-image-upload">
                                <label for="commentImage" style="display:block;font-size:.72rem;font-weight:700;color:#475569;margin-bottom:.4rem">
                                    <i class="fas fa-camera me-1" style="color:#0284c7"></i> Görsel Ekle
                                    <span style="font-weight:400;color:#94a3b8">(isteğe bağlı)</span>
                                </label>
                                <input type="file" name="comment_image" id="commentImage"
                                       class="form-control @error('comment_image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp"
                                       data-fault-image-input data-preview-target="commentImagePreview">
                                <div style="font-size:.68rem;color:#94a3b8;margin-top:.3rem">JPG, PNG veya WebP · En fazla 4 MB</div>
                                @error('comment_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="fault-image-preview" id="commentImagePreview">
                                    <img src="" alt="Seçilen görsel önizlemesi">
                                    <span style="font-size:.72rem;color:#64748b">Seçilen görsel güncelleme geçmişinde gösterilecek.</span>
                                </div>
                            </div>
                            <button type="submit"
                                    style="width:100%;background:#0284c7;border:none;padding:.65rem;border-radius:.75rem;font-weight:700;font-size:.875rem;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem">
                                <i class="fas fa-paper-plane" style="font-size:.75rem"></i> Yorum Ekle
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Summary --}}
                <div class="fault-card overflow-hidden">
                    <div style="background:#1e293b;padding:.75rem 1.25rem;display:flex;align-items:center;gap:.5rem">
                        <i class="fas fa-clipboard-list" style="color:#94a3b8;font-size:.75rem"></i>
                        <span style="color:#94a3b8;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Özet Bilgiler</span>
                    </div>
                    @php
                    $infoRows = [
                        ['label'=>'Arıza No',    'value'=>'#'.$fault->id,  'type'=>'mono'],
                        ['label'=>'Öncelik',     'value'=>$pm['label'],    'type'=>'badge','bg'=>$pm['badgeBg'],'fg'=>$pm['badgeFg']],
                        ['label'=>'Durum',       'value'=>$sm['label'],    'type'=>'badge','bg'=>$sm['badgeBg'],'fg'=>$sm['badgeFg']],
                        ['label'=>'Şube',        'value'=>$fault->branch->name ?? '—'],
                        ['label'=>'Departman',   'value'=>$fault->department->name ?? '—'],
                        ['label'=>'Konum',       'value'=>$fault->faultLocation ? ($fault->faultLocation->name.($fault->faultArea ? ' / '.$fault->faultArea->name : '')) : '—'],
                        ['label'=>'Bildiren',    'value'=>$fault->reporter->name ?? '—'],
                        ['label'=>'Kayıt',       'value'=>$fault->created_at->format('d.m.Y H:i')],
                    ];
                    if ($fault->resolved_at) {
                        $infoRows[] = ['label'=>'Çözüm Tarihi','value'=>\Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i')];
                        $infoRows[] = ['label'=>'Çözüm Süresi','value'=>$fault->resolutionTimeLabel(),'type'=>'green'];
                    }
                    $infoRows[] = ['label'=>'Güncellemeler','value'=>$fault->updates->count().' adet'];
                    @endphp
                    @foreach($infoRows as $row)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 1.25rem;border-bottom:1px solid #f1f5f9">
                        <span style="font-size:.75rem;font-weight:500;color:#94a3b8">{{ $row['label'] }}</span>
                        @if(($row['type'] ?? '') === 'badge')
                        <span class="fault-badge" style="background:{{ $row['bg'] }};color:{{ $row['fg'] }}">{{ $row['value'] }}</span>
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
</div>

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('[data-fault-image-input]').forEach(function(input) {
    input.addEventListener('change', function() {
        const preview = document.getElementById(this.dataset.previewTarget);
        const image = preview?.querySelector('img');
        const file = this.files?.[0];

        if (!preview || !image) return;
        if (!file) {
            image.removeAttribute('src');
            preview.classList.remove('is-visible');
            return;
        }

        if (image.dataset.objectUrl) URL.revokeObjectURL(image.dataset.objectUrl);
        const objectUrl = URL.createObjectURL(file);
        image.src = objectUrl;
        image.dataset.objectUrl = objectUrl;
        preview.classList.add('is-visible');
    });
});

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

// Kapatma onayı — "Kapalı" seçilip gönderildiğinde SweetAlert ile not al
document.getElementById('statusUpdateForm')?.addEventListener('submit', function (e) {
    const selectedStatus = document.querySelector('#statusUpdateForm input[name="status"]:checked')?.value;
    if (selectedStatus !== 'closed') return; // diğer durumlar normal devam etsin

    e.preventDefault();
    const form = this;
    const existingNote = document.getElementById('statusNote').value.trim();

    Swal.fire({
        title: 'Arıza Kapatılıyor',
        html: `<p style="margin:0 0 .75rem;color:#64748b;font-size:.9rem">Bu arızayı <strong>kapatmak</strong> istediğinizden emin misiniz?</p>
               <label style="display:block;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.35rem">
                   Not / Açıklama <span style="color:#ef4444">*</span>
               </label>
               <textarea id="swal-close-note" rows="4"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:.6rem;padding:.6rem .75rem;font-size:.85rem;box-sizing:border-box;resize:none;outline:none;font-family:inherit"
                   placeholder="Kapatma sebebini yazınız...">${existingNote}</textarea>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#64748b',
        cancelButtonColor: '#94a3b8',
        cancelButtonText: 'İptal',
        confirmButtonText: '<i class="fas fa-lock" style="margin-right:.3rem"></i> Evet, Kapat',
        focusConfirm: false,
        didOpen: () => {
            document.getElementById('swal-close-note').focus();
        },
        preConfirm: () => {
            const val = document.getElementById('swal-close-note').value.trim();
            if (!val) {
                Swal.showValidationMessage('Not / Açıklama alanı zorunludur!');
                return false;
            }
            return val;
        }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('statusNote').value = result.value;
            form.submit();
        }
    });
});

// Sunucu tarafı doğrulama hatalarını SweetAlert ile göster
@if($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: 'Hata',
        html: '{!! implode("<br>", array_map("htmlspecialchars", $errors->all())) !!}',
        icon: 'error',
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#ef4444'
    });
});
@endif
</script>
@endpush
@endsection
