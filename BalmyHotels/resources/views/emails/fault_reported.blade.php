<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Arıza Bildirimi</title>
    <style>
        body { margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; }
        .wrapper { max-width:580px; margin:36px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.09); }

        /* Header */
        .header { background:linear-gradient(135deg,#dc2626,#ef4444); padding:30px 36px 26px; }
        .header-top { display:flex; align-items:center; gap:14px; }
        .header-icon { width:44px; height:44px; background:rgba(255,255,255,.22); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
        .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; letter-spacing:-.3px; line-height:1.25; }
        .header p  { margin:6px 0 0; color:rgba(255,255,255,.82); font-size:12.5px; }

        /* Priority badge */
        .priority-bar { background:rgba(0,0,0,.15); padding:10px 36px; font-size:12px; color:#fff; display:flex; align-items:center; gap:8px; }
        .priority-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }

        /* Body */
        .body { padding:26px 36px 20px; }
        .section-label { font-size:11px; font-weight:700; color:#94a3b8; letter-spacing:.6px; text-transform:uppercase; margin-bottom:6px; }

        /* Fault card */
        .fault-card { border:1.5px solid #fee2e2; border-radius:12px; padding:18px 20px; background:#fff8f8; margin-bottom:20px; }
        .fault-title { font-size:17px; font-weight:700; color:#1e293b; margin:0 0 12px; line-height:1.3; }

        /* Meta grid */
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; }
        .meta-item { }
        .meta-key   { font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
        .meta-value { font-size:13px; color:#334155; font-weight:500; }

        /* Description */
        .desc-box { background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0; padding:14px 16px; margin-top:16px; }
        .desc-text { font-size:13.5px; color:#475569; line-height:1.65; margin:0; }

        /* CTA */
        .cta { text-align:center; margin:22px 0 10px; }
        .cta a { background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; text-decoration:none; padding:13px 34px; border-radius:30px; font-weight:700; font-size:13.5px; display:inline-block; letter-spacing:.2px; }

        /* Photo */
        .photo-wrap { text-align:center; margin:16px 0; }
        .photo-wrap img { max-width:100%; max-height:260px; border-radius:10px; border:1px solid #e2e8f0; object-fit:cover; }

        /* Footer */
        .footer { background:#f8fafc; padding:16px 36px; text-align:center; font-size:11px; color:#94a3b8; border-top:1px solid #f0f4f8; }

        /* Priority colors */
        .p-critical { color:#dc2626; } .dot-critical { background:#dc2626; }
        .p-high     { color:#ea580c; } .dot-high     { background:#ea580c; }
        .p-medium   { color:#d97706; } .dot-medium   { background:#d97706; }
        .p-low      { color:#16a34a; } .dot-low      { background:#16a34a; }
    </style>
</head>
<body>
@php
    $priorityLabels = ['critical'=>'Kritik','high'=>'Acil','medium'=>'Orta','low'=>'Normal'];
    $priorityLabel  = $priorityLabels[$fault->priority] ?? $fault->priority;
    $priorityClass  = 'p-' . $fault->priority;
    $dotClass       = 'dot-' . $fault->priority;
@endphp
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div class="header-icon">🔧</div>
            <div>
                <h1>Yeni Arıza Bildirimi</h1>
                <p>{{ config('app.name') }} — Teknik Arıza Takip Sistemi</p>
            </div>
        </div>
    </div>

    {{-- Priority bar --}}
    <div class="priority-bar">
        <span class="priority-dot {{ $dotClass }}"></span>
        <strong>Öncelik:</strong> {{ $priorityLabel }}
        &nbsp;·&nbsp;
        <strong>Durum:</strong> Açık
        &nbsp;·&nbsp;
        <strong>Bildirim No:</strong> #{{ $fault->id }}
    </div>

    {{-- Body --}}
    <div class="body">
        <p style="color:#374151;font-size:14px;margin-top:0;margin-bottom:18px">
            Departmanınıza yeni bir arıza bildirimi iletildi. Lütfen en kısa sürede inceleyiniz.
        </p>

        <div class="fault-card">
            <div class="fault-title">{{ $fault->title }}</div>

            <div class="meta-grid">
                <div class="meta-item">
                    <div class="meta-key">Şube</div>
                    <div class="meta-value">{{ $fault->branch?->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-key">Departman</div>
                    <div class="meta-value">{{ $fault->department?->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-key">Arıza Türü</div>
                    <div class="meta-value">{{ $fault->faultType?->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-key">Konum</div>
                    <div class="meta-value">
                        {{ $fault->faultLocation?->name ?? '—' }}
                        @if($fault->faultArea) / {{ $fault->faultArea->name }} @endif
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-key">Bildiren</div>
                    <div class="meta-value">{{ $fault->reporter?->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-key">Bildirim Tarihi</div>
                    <div class="meta-value">{{ $fault->created_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>

            @if($fault->description)
            <div class="desc-box">
                <p class="desc-text">{{ $fault->description }}</p>
            </div>
            @endif
        </div>

        @if($fault->image_path)
        <div class="photo-wrap">
            <div class="section-label" style="margin-bottom:8px;">Arıza Fotoğrafı</div>
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($fault->image_path) }}" target="_blank">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($fault->image_path) }}"
                     alt="Arıza fotoğrafı">
            </a>
        </div>
        @endif

        <div class="cta">
            <a href="{{ route('faults.show', $fault) }}">Arızayı Görüntüle →</a>
        </div>

        <p style="color:#94a3b8;font-size:11.5px;text-align:center;margin-bottom:0">
            Bu e-posta, {{ config('app.name') }} sistemi tarafından otomatik gönderilmiştir.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }} &nbsp;·&nbsp; Tüm hakları saklıdır.
    </div>
</div>
</body>
</html>
