@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Detayı</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">{{ $asset->asset_code }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}</div>
    @endif

    {{-- BAŞLIK KARTI --}}
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex gap-2 mb-1">
                        <code class="text-dark fs-6">{{ $asset->asset_code }}</code>
                        @if($asset->category)
                            <span class="badge" style="background:{{ $asset->category->color }}">{{ $asset->category->name }}</span>
                        @endif
                        <span class="badge bg-{{ \App\Models\Asset::STATUS_COLORS[$asset->status] }}">
                            {{ \App\Models\Asset::STATUS_ICONS[$asset->status] }} {{ \App\Models\Asset::STATUSES[$asset->status] }}
                        </span>
                    </div>
                    <h2 class="mb-1">{{ $asset->name }}</h2>
                    <div class="text-muted small d-flex flex-wrap gap-3">
                        <span>Şube: <strong>{{ $asset->branch->name ?? '—' }}</strong></span>
                        @if($asset->location)<span>Konum: <strong>{{ $asset->location }}</strong></span>@endif
                        @if($asset->serial_no)<span>Seri: <strong>{{ $asset->serial_no }}</strong></span>@endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('assets.qrPrint', $asset) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-qrcode me-1"></i>QR Yazdır
                    </a>
                    <a href="{{ route('asset-exits.create', ['asset_id' => $asset->id]) }}" class="btn btn-sm btn-warning">
                        → Çıkış Formu
                    </a>
                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary">← Geri</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        {{-- Sol: Bilgiler --}}
        <div class="col-md-5">

            {{-- Fotoğraf --}}
            @if($asset->photo)
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <img src="{{ asset('uploads/' . $asset->photo) }}" alt="{{ $asset->name }}"
                             style="width:100%;max-height:260px;object-fit:cover;border-radius:calc(var(--bs-card-border-radius) - 1px)">
                    </div>
                </div>
            @endif

            {{-- QR Kodu --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-qrcode me-2 text-muted"></i>QR Kodu</h5>
                    <a href="{{ route('assets.qrPrint', $asset) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-1"></i>Yazdır
                    </a>
                </div>
                <div class="card-body text-center py-4">
                    <canvas id="qrCanvas"></canvas>
                    <div class="mt-2" style="font-size:.72rem;color:#9ca3af;word-break:break-all">
                        {{ $asset->publicQrUrl() }}
                    </div>
                </div>
            </div>

            {{-- Temel Bilgiler --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-title mb-0">Temel Bilgiler</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="text-muted" style="width:45%">Demirbaş Kodu</td><td><code>{{ $asset->asset_code }}</code></td></tr>
                            <tr><td class="text-muted">Kategori</td><td>{{ $asset->category->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Şube</td><td>{{ $asset->branch->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Konum</td><td>{{ $asset->location ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Seri No</td><td>{{ $asset->serial_no ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Alış Tarihi</td><td>{{ $asset->purchase_date?->format('d.m.Y') ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Alış Fiyatı</td><td>{{ $asset->purchase_price ? '₺' . number_format($asset->purchase_price, 2) : '—' }}</td></tr>
                            <tr>
                                <td class="text-muted">Garanti Bitiş</td>
                                <td>
                                    @if($asset->warranty_until)
                                        <span class="{{ $asset->isWarrantyExpired() ? 'text-danger fw-semibold' : 'text-success' }}">
                                            {{ $asset->warranty_until->format('d.m.Y') }}
                                            @if($asset->isWarrantyExpired()) (Süresi Doldu) @endif
                                        </span>
                                    @else — @endif
                                </td>
                            </tr>
                            <tr><td class="text-muted">Kayıt Tarihi</td><td>{{ $asset->created_at->format('d.m.Y H:i') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Açıklama --}}
            @if($asset->description)
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Açıklama</h5></div>
                    <div class="card-body"><p class="mb-0">{{ $asset->description }}</p></div>
                </div>
            @endif

            {{-- Özel Alanlar --}}
            @if($asset->category && $asset->category->field_definitions && $asset->properties)
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Kategori Bilgileri</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($asset->category->field_definitions as $field)
                                    <tr>
                                        <td class="text-muted" style="width:45%">{{ $field['label'] }}</td>
                                        <td>{{ $asset->properties[$field['name']] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Per-asset custom fields --}}
            @if($asset->custom_fields && count($asset->custom_fields) > 0)
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-sliders-h me-2" style="color:#6366f1"></i>Ek Özellikler</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($asset->custom_fields as $cf)
                                    <tr>
                                        <td class="text-muted" style="width:45%">{{ $cf['label'] }}</td>
                                        <td><strong>{{ $cf['value'] }}{{ $cf['unit'] ? ' '.$cf['unit'] : '' }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sağ: Çıkış Geçmişi --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Çıkış Geçmişi</h5>
                    <a href="{{ route('asset-exits.create', ['asset_id' => $asset->id]) }}" class="btn btn-sm btn-outline-warning">
                        + Çıkış Formu
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($asset->exits as $exit)
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex flex-wrap gap-2 mb-1">
                                    <span class="badge bg-{{ \App\Models\AssetExit::STATUS_COLORS[$exit->status] }}">
                                        {{ \App\Models\AssetExit::STATUSES[$exit->status] }}
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        {{ \App\Models\AssetExit::TAKER_TYPES[$exit->taker_type] }}
                                    </span>
                                    @if($exit->isOverdue())
                                        <span class="badge bg-danger">⚠ Gecikmiş</span>
                                    @endif
                                </div>
                                <div class="fw-semibold small">{{ $exit->takerName() }}</div>
                                <div class="text-muted small">{{ $exit->purpose }}</div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ $exit->taken_at->format('d.m.Y H:i') }}
                                    @if($exit->returned_at)
                                        → İade: {{ $exit->returned_at->format('d.m.Y H:i') }}
                                    @elseif($exit->expected_return_at)
                                        → Beklenen: {{ $exit->expected_return_at->format('d.m.Y H:i') }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('asset-exits.show', $exit) }}" class="btn btn-xs btn-outline-secondary">Detay</a>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">Henüz çıkış kaydı yok.</div>
                    @endforelse
                </div>
            </div>

            {{-- Değişiklik Geçmişi --}}
            <div class="card mt-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-history text-muted"></i>
                    <h5 class="card-title mb-0">Değişiklik Geçmişi</h5>
                </div>
                <div class="card-body" style="padding:0">
                    @if($asset->histories->isEmpty())
                        <div class="p-4 text-center" style="color:#9ca3af;font-size:.83rem">
                            <i class="fas fa-history me-1" style="opacity:.3"></i>Henüz değişiklik kaydı yok.
                        </div>
                    @else
                        <div style="position:relative;padding:8px 20px">
                            <div style="position:absolute;left:36px;top:0;bottom:0;width:2px;background:#f3f4f6"></div>
                            @foreach($asset->histories as $h)
                                @php
                                    if ($h->action === 'created') {
                                        $hColor = '#10b981'; $hIcon = 'fa-plus';
                                    } elseif ($h->field === 'status') {
                                        $hColor = '#3b82f6'; $hIcon = 'fa-sync-alt';
                                    } elseif ($h->field === 'location') {
                                        $hColor = '#f59e0b'; $hIcon = 'fa-map-marker-alt';
                                    } elseif ($h->field === 'branch_id') {
                                        $hColor = '#8b5cf6'; $hIcon = 'fa-building';
                                    } elseif ($h->field === 'photo') {
                                        $hColor = '#c19b77'; $hIcon = 'fa-camera';
                                    } else {
                                        $hColor = '#6b7280'; $hIcon = 'fa-edit';
                                    }
                                @endphp
                                <div style="position:relative;padding:12px 0 12px 32px;border-bottom:1px solid #f9fafb">
                                    <div style="position:absolute;left:-4px;top:50%;transform:translateY(-50%);width:22px;height:22px;border-radius:50%;background:{{ $hColor }};display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.15);z-index:1">
                                        <i class="fas {{ $hIcon }}" style="font-size:.5rem;color:#fff"></i>
                                    </div>
                                    @if($h->action === 'created')
                                        <div style="font-size:.84rem;font-weight:700;color:#1f2937">Demirbaş oluşturuldu</div>
                                        @if($h->note)<div style="font-size:.77rem;color:#9ca3af">{{ $h->note }}</div>@endif
                                    @else
                                        <div style="font-size:.84rem;font-weight:700;color:#1f2937">
                                            {{ \App\Models\AssetHistory::FIELD_LABELS[$h->field] ?? ($h->field ?? 'Alan') }} güncellendi
                                        </div>
                                        @if($h->old_value !== null || $h->new_value !== null)
                                            <div style="font-size:.78rem;color:#6b7280;margin-top:2px;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                                <span style="color:#ef4444;text-decoration:line-through">{{ $h->old_value ?? '—' }}</span>
                                                <i class="fas fa-long-arrow-alt-right" style="font-size:.65rem;color:#9ca3af"></i>
                                                <span style="color:#059669;font-weight:600">{{ $h->new_value ?? '—' }}</span>
                                            </div>
                                        @endif
                                        @if($h->note)<div style="font-size:.77rem;color:#9ca3af;margin-top:1px">{{ $h->note }}</div>@endif
                                    @endif
                                    <div style="font-size:.73rem;color:#9ca3af;margin-top:4px">
                                        @if($h->user)
                                            <strong style="color:#6b7280">{{ $h->user->name }}</strong>
                                            <span style="margin:0 3px">·</span>
                                        @endif
                                        {{ $h->created_at->format('d.m.Y · H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
QRCode.toCanvas(document.getElementById('qrCanvas'), '{{ $asset->publicQrUrl() }}', {
    width: 160, margin: 1,
    color: { dark: '#1f2937', light: '#ffffff' },
    errorCorrectionLevel: 'M'
});
</script>
@endpush
