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
        </div>
    </div>
</div>
@endsection
