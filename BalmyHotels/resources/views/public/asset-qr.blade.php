<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>{{ $asset->name }} — Demirbaş Bilgisi</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
:root {
    --gold: #c19b77;
    --gold-dark: #a07850;
    --bg: #f8f7f5;
}
* { box-sizing: border-box; }
body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: var(--bg);
    color: #1f2937;
    min-height: 100vh;
}

/* Top bar */
.top-bar {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #fff;
    padding: 14px 20px 40px;
    text-align: center;
    position: relative;
}
.top-bar .brand {
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    opacity: .8;
    margin-bottom: 6px;
}
.top-bar .asset-name {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
}
.top-bar .asset-code {
    font-size: .82rem;
    opacity: .85;
    font-family: 'Courier New', monospace;
    letter-spacing: .08em;
}

/* Status badge */
.status-float {
    position: absolute;
    bottom: -16px;
    left: 50%;
    transform: translateX(-50%);
}
.status-bubble {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 18px;
    border-radius: 50px;
    font-size: .82rem;
    font-weight: 700;
    border: 2px solid #fff;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(0,0,0,.15);
}
.status-available   { background: #10b981; color: #fff; }
.status-in_use      { background: #f59e0b; color: #fff; }
.status-maintenance { background: #3b82f6; color: #fff; }
.status-retired     { background: #9ca3af; color: #fff; }

/* Main card area */
.content-wrap {
    max-width: 480px;
    margin: 0 auto;
    padding: 32px 16px 24px;
}

/* Photo */
.asset-photo {
    width: 100%;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
    margin-bottom: 16px;
}
.asset-photo img {
    width: 100%;
    max-height: 260px;
    object-fit: cover;
    display: block;
}

/* Info card */
.info-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    margin-bottom: 14px;
    overflow: hidden;
}
.info-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}
.info-card-header .ic-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem;
    flex-shrink: 0;
}
.info-card-header h6 {
    font-size: .85rem;
    font-weight: 700;
    margin: 0;
    color: #1f2937;
}
.info-table { width: 100%; border-collapse: collapse; }
.info-table td {
    padding: 9px 16px;
    font-size: .82rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}
.info-table tr:last-child td { border-bottom: none; }
.info-table td:first-child { color: #6b7280; width: 40%; }
.info-table td:last-child { font-weight: 600; color: #1f2937; }

/* Custom fields chips */
.cf-grid { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 16px; }
.cf-chip {
    background: #f3f4f6;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: .8rem;
    display: inline-flex;
    flex-direction: column;
    gap: 1px;
}
.cf-chip .cf-chip-label { color: #9ca3af; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.cf-chip .cf-chip-val { font-weight: 700; color: #1f2937; }

/* Exits timeline */
.exit-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
}
.exit-item:last-child { border-bottom: none; }
.exit-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 600;
}
.exit-info-row { font-size: .8rem; color: #6b7280; margin-top: 3px; }

/* Footer */
.pub-footer {
    text-align: center;
    padding: 20px 16px;
    font-size: .72rem;
    color: #9ca3af;
}
</style>
</head>
<body>

{{-- Top banner --}}
<div class="top-bar">
    <div class="brand">Balmy Hotels — Demirbaş Takip</div>
    <p class="asset-name">{{ $asset->name }}</p>
    <p class="asset-code">{{ $asset->asset_code }}</p>

    <div class="status-float">
        @php
            $statusClasses = [
                'available'   => 'status-available',
                'in_use'      => 'status-in_use',
                'maintenance' => 'status-maintenance',
                'retired'     => 'status-retired',
            ];
            $statusIcons = [
                'available'   => 'fas fa-check-circle',
                'in_use'      => 'fas fa-arrow-circle-right',
                'maintenance' => 'fas fa-tools',
                'retired'     => 'fas fa-times-circle',
            ];
        @endphp
        <span class="status-bubble {{ $statusClasses[$asset->status] ?? '' }}">
            <i class="{{ $statusIcons[$asset->status] ?? 'fas fa-circle' }}"></i>
            {{ \App\Models\Asset::STATUSES[$asset->status] ?? $asset->status }}
        </span>
    </div>
</div>

<div class="content-wrap">

    {{-- Photo --}}
    @if($asset->photo)
        <div class="asset-photo">
            <img src="{{ asset('storage/' . $asset->photo) }}" alt="{{ $asset->name }}">
        </div>
    @endif

    {{-- Basic info --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="ic-icon" style="background:rgba(193,155,119,.15);color:#c19b77">
                <i class="fas fa-info-circle"></i>
            </div>
            <h6>Demirbaş Bilgileri</h6>
        </div>
        <table class="info-table">
            @if($asset->category)
                <tr><td>Kategori</td><td>{{ $asset->category->name }}</td></tr>
            @endif
            @if($asset->branch)
                <tr><td>Şube</td><td>{{ $asset->branch->name }}</td></tr>
            @endif
            @if($asset->location)
                <tr><td>Konum</td><td>{{ $asset->location }}</td></tr>
            @endif
            @if($asset->serial_no)
                <tr><td>Seri No</td><td><code style="font-size:.8rem">{{ $asset->serial_no }}</code></td></tr>
            @endif
            @if($asset->warranty_until)
                <tr>
                    <td>Garanti</td>
                    <td>
                        <span style="color:{{ $asset->isWarrantyExpired() ? '#dc3545' : '#059669' }}">
                            {{ $asset->warranty_until->format('d.m.Y') }}
                            @if($asset->isWarrantyExpired()) <small>(Doldu)</small> @endif
                        </span>
                    </td>
                </tr>
            @endif
            @if($asset->description)
                <tr><td>Açıklama</td><td>{{ $asset->description }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Category dynamic fields --}}
    @if($asset->category && $asset->category->field_definitions && $asset->properties)
        <div class="info-card">
            <div class="info-card-header">
                <div class="ic-icon" style="background:rgba(139,92,246,.12);color:#7c3aed">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h6>Kategori Bilgileri</h6>
            </div>
            <table class="info-table">
                @foreach($asset->category->field_definitions as $field)
                    @if(isset($asset->properties[$field['name']]) && $asset->properties[$field['name']] !== '')
                        <tr>
                            <td>{{ $field['label'] }}</td>
                            <td>{{ $asset->properties[$field['name']] }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>
    @endif

    {{-- Per-asset custom fields --}}
    @if($asset->custom_fields && count($asset->custom_fields) > 0)
        <div class="info-card">
            <div class="info-card-header">
                <div class="ic-icon" style="background:rgba(99,102,241,.12);color:#6366f1">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h6>Ek Özellikler</h6>
            </div>
            <div class="cf-grid">
                @foreach($asset->custom_fields as $cf)
                    <div class="cf-chip">
                        <span class="cf-chip-label">{{ $cf['label'] }}</span>
                        <span class="cf-chip-val">{{ $cf['value'] }}{{ $cf['unit'] ? ' '.$cf['unit'] : '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Exit history --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="ic-icon" style="background:rgba(245,158,11,.12);color:#d97706">
                <i class="fas fa-history"></i>
            </div>
            <h6>Çıkış Geçmişi</h6>
        </div>
        @forelse($asset->exits()->latest()->take(10)->get() as $exit)
            @php
                $exitColors = [
                    'pending'  => '#f59e0b',
                    'approved' => '#10b981',
                    'rejected' => '#dc3545',
                    'returned' => '#6b7280',
                ];
                $exitLabels = \App\Models\AssetExit::STATUSES;
            @endphp
            <div class="exit-item">
                <span class="exit-badge" style="background:{{ $exitColors[$exit->status] ?? '#6b7280' }}1a;color:{{ $exitColors[$exit->status] ?? '#6b7280' }};border:1px solid {{ $exitColors[$exit->status] ?? '#6b7280' }}44">
                    {{ $exitLabels[$exit->status] ?? $exit->status }}
                </span>
                <div class="exit-info-row">
                    {{ $exit->takerName() }} — {{ $exit->taken_at->format('d.m.Y') }}
                    @if($exit->returned_at)
                        → İade: {{ $exit->returned_at->format('d.m.Y') }}
                    @elseif($exit->expected_return_at)
                        → Beklenen: {{ $exit->expected_return_at->format('d.m.Y') }}
                    @endif
                </div>
                @if($exit->purpose)
                    <div class="exit-info-row">{{ $exit->purpose }}</div>
                @endif
            </div>
        @empty
            <div style="padding:16px;text-align:center;font-size:.82rem;color:#9ca3af">
                <i class="fas fa-inbox me-1"></i>Henüz çıkış kaydı bulunmuyor.
            </div>
        @endforelse
    </div>

</div>

<div class="pub-footer">
    Balmy Hotels — Demirbaş Takip Sistemi<br>
    <span style="font-size:.65rem">{{ $asset->asset_code }} · {{ now()->format('d.m.Y H:i') }}</span>
</div>

</body>
</html>
