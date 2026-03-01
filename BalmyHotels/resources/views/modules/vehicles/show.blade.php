@extends('layouts.default')

@section('content')
<div class="container-fluid">
    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $vehicle->plate }}</h4>
                <span>{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Araçlar</a></li>
                <li class="breadcrumb-item active">{{ $vehicle->plate }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Araç Bilgi Kartı --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-car me-2 text-primary"></i>
                        {{ $vehicle->plate }}
                        @if($vehicle->is_active)
                            <span class="badge bg-success ms-2">Aktif</span>
                        @else
                            <span class="badge bg-secondary ms-2">Pasif</span>
                        @endif
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit me-1"></i> Düzenle
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2 col-sm-4">
                            <small class="text-muted d-block">Şube</small>
                            <strong>{{ $vehicle->branch->name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <small class="text-muted d-block">Marka / Model</small>
                            <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong>
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <small class="text-muted d-block">Yıl</small>
                            <strong>{{ $vehicle->year }}</strong>
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <small class="text-muted d-block">Renk</small>
                            <strong>{{ $vehicle->color ?? '—' }}</strong>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <small class="text-muted d-block">Güncel KM</small>
                            <strong>{{ number_format($vehicle->current_km) }} km</strong>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <small class="text-muted d-block">Muayene</small>
                            <strong>
                                @if($vehicle->license_expiry)
                                    @php $expDays = now()->diffInDays($vehicle->license_expiry, false); @endphp
                                    <span class="{{ $expDays < 0 ? 'text-danger' : ($expDays < 30 ? 'text-warning' : 'text-success') }}">
                                        {{ $vehicle->license_expiry->format('d.m.Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <small class="text-muted d-block">Şasi / Motor No</small>
                            <small>{{ $vehicle->chassis_no ?? '—' }} / {{ $vehicle->engine_no ?? '—' }}</small>
                        </div>
                        @if($vehicle->notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notlar</small>
                            <span>{{ $vehicle->notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sekmeler --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs" id="vehicleTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-ops">
                                <i class="fas fa-exchange-alt me-1"></i> Operasyonlar
                                <span class="badge bg-secondary ms-1">{{ $vehicle->operations->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-maint">
                                <i class="fas fa-tools me-1"></i> Bakımlar
                                <span class="badge bg-secondary ms-1">{{ $vehicle->maintenances->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ins">
                                <i class="fas fa-shield-alt me-1"></i> Sigorta / Kasko
                                <span class="badge bg-secondary ms-1">{{ $vehicle->insurances->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        {{-- OPERASYONLAR --}}
                        <div class="tab-pane fade show active" id="tab-ops">
                            <div class="row align-items-start">
                                {{-- Operasyon Ekleme Formu --}}
                                <div class="col-xl-4 col-lg-5 mb-4 mb-lg-0">
                                    <div class="card border shadow-none">
                                        <div class="card-header py-2 bg-light">
                                            <h6 class="mb-0"><i class="fas fa-plus me-1 text-success"></i> Yeni Operasyon</h6>
                                        </div>
                                        <div class="card-body">
                                            @if($errors->hasBag('operation') || $errors->has('type'))
                                                <div class="alert alert-danger py-2 small">
                                                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                                                </div>
                                            @endif
                                            <form action="{{ route('vehicles.operasyonlar.store', $vehicle) }}" method="POST">
                                                @csrf
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">İşlem Türü *</label>
                                                    <select name="type" class="form-select form-select-sm" required>
                                                        <option value="">— Seç —</option>
                                                        @foreach(['giris'=>'Garaj Giriş','cikis'=>'Garaj Çıkış','goreve_gidis'=>'Göreve Gidiş','gorevden_gelis'=>'Görevden Geliş'] as $k=>$l)
                                                            <option value="{{ $k }}" @selected(old('type') == $k)>{{ $l }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Tarih/Saat *</label>
                                                    <input type="datetime-local" name="operation_at"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('operation_at', now()->format('Y-m-d\TH:i')) }}" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">KM *</label>
                                                    <input type="number" name="km"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('km', $vehicle->current_km) }}"
                                                        min="{{ $vehicle->current_km }}" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Destinasyon / Güzergah</label>
                                                    <input type="text" name="destination"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('destination') }}" placeholder="Opsiyonel">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label mb-1">Not</label>
                                                    <textarea name="notes" rows="2" class="form-control form-control-sm">{{ old('notes') }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="fas fa-save me-1"></i> Kaydet
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Operasyon Listesi --}}
                                <div class="col-xl-8 col-lg-7">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tarih</th>
                                                    <th>Tür</th>
                                                    <th>KM</th>
                                                    <th>Güzergah</th>
                                                    <th>Kaydeden</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($vehicle->operations->sortByDesc('operation_at') as $op)
                                                    @php
                                                        $opLabels = ['giris'=>['Garaj Giriş','success'],'cikis'=>['Garaj Çıkış','danger'],'goreve_gidis'=>['Göreve Gidiş','warning'],'gorevden_gelis'=>['Görevden Geliş','info']];
                                                        [$opLabel, $opColor] = $opLabels[$op->type] ?? [$op->type, 'secondary'];
                                                    @endphp
                                                    <tr>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($op->operation_at)->format('d.m.Y H:i') }}</td>
                                                        <td><span class="badge bg-{{ $opColor }}">{{ $opLabel }}</span></td>
                                                        <td>{{ number_format($op->km) }}</td>
                                                        <td>{{ $op->destination ?? '—' }}</td>
                                                        <td>{{ optional($op->user)->name ?? '—' }}</td>
                                                        <td>
                                                            <form action="{{ route('vehicles.operasyonlar.destroy', [$vehicle, $op]) }}"
                                                                  method="POST" onsubmit="return confirm('Sil?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="6" class="text-center text-muted">Operasyon kaydı yok.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BAKIMLAR --}}
                        <div class="tab-pane fade" id="tab-maint">
                            <div class="row">
                                <div class="col-xl-4 col-lg-5 mb-4 mb-lg-0">
                                    <div class="card border shadow-none">
                                        <div class="card-header py-2 bg-light">
                                            <h6 class="mb-0"><i class="fas fa-plus me-1 text-success"></i> Yeni Bakım</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('vehicles.bakimlar.store', $vehicle) }}" method="POST">
                                                @csrf
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Bakım Türü *</label>
                                                    <select name="type" class="form-select form-select-sm" required>
                                                        <option value="">— Seç —</option>
                                                        @foreach(['servis'=>'Periyodik Servis','lastik'=>'Lastik','yag'=>'Yağ Değişimi','egzoz'=>'Egzoz','fren'=>'Fren','diger'=>'Diğer'] as $k=>$l)
                                                            <option value="{{ $k }}">{{ $l }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Tarih *</label>
                                                    <input type="date" name="maintenance_at"
                                                        class="form-control form-control-sm"
                                                        value="{{ now()->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">KM *</label>
                                                    <input type="number" name="km"
                                                        class="form-control form-control-sm"
                                                        value="{{ $vehicle->current_km }}" min="0" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Servis Adı</label>
                                                    <input type="text" name="service_name" class="form-control form-control-sm" placeholder="Opsiyonel">
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-6">
                                                        <label class="form-label mb-1">Maliyet (₺)</label>
                                                        <input type="number" name="cost" step="0.01" class="form-control form-control-sm" min="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label mb-1">Sonraki KM</label>
                                                        <input type="number" name="next_km" class="form-control form-control-sm" min="0">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Sonraki Bakım Tarihi</label>
                                                    <input type="date" name="next_date" class="form-control form-control-sm">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label mb-1">Not</label>
                                                    <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="fas fa-save me-1"></i> Kaydet
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-8 col-lg-7">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tarih</th>
                                                    <th>Tür</th>
                                                    <th>KM</th>
                                                    <th>Servis</th>
                                                    <th>Maliyet</th>
                                                    <th>Sonraki</th>
                                                    <th>Kaydeden</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($vehicle->maintenances->sortByDesc('maintenance_at') as $m)
                                                    @php
                                                        $mTypes = ['servis'=>['Periyodik','primary'],'lastik'=>['Lastik','info'],'yag'=>['Yağ','warning'],'egzoz'=>['Egzoz','dark'],'fren'=>['Fren','danger'],'diger'=>['Diğer','secondary']];
                                                        [$mLabel, $mColor] = $mTypes[$m->type] ?? [$m->type, 'secondary'];
                                                    @endphp
                                                    <tr>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($m->maintenance_at)->format('d.m.Y') }}</td>
                                                        <td><span class="badge bg-{{ $mColor }}">{{ $mLabel }}</span></td>
                                                        <td>{{ number_format($m->km) }}</td>
                                                        <td>{{ $m->service_name ?? '—' }}</td>
                                                        <td>{{ $m->cost ? '₺'.number_format($m->cost, 2) : '—' }}</td>
                                                        <td class="small">
                                                            @if($m->next_km) {{ number_format($m->next_km) }} km @endif
                                                            @if($m->next_date) <br>{{ \Carbon\Carbon::parse($m->next_date)->format('d.m.Y') }} @endif
                                                            @if(!$m->next_km && !$m->next_date) — @endif
                                                        </td>
                                                        <td>{{ optional($m->user)->name ?? '—' }}</td>
                                                        <td>
                                                            <form action="{{ route('vehicles.bakimlar.destroy', [$vehicle, $m]) }}"
                                                                  method="POST" onsubmit="return confirm('Sil?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="8" class="text-center text-muted">Bakım kaydı yok.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SİGORTA / KASKO --}}
                        <div class="tab-pane fade" id="tab-ins">
                            <div class="row">
                                <div class="col-xl-4 col-lg-5 mb-4 mb-lg-0">
                                    <div class="card border shadow-none">
                                        <div class="card-header py-2 bg-light">
                                            <h6 class="mb-0"><i class="fas fa-plus me-1 text-success"></i> Yeni Sigorta / Kasko</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('vehicles.sigortalar.store', $vehicle) }}" method="POST">
                                                @csrf
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Tür *</label>
                                                    <select name="type" class="form-select form-select-sm" required>
                                                        <option value="trafik">Trafik Sigortası</option>
                                                        <option value="kasko">Kasko</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Sigorta Şirketi *</label>
                                                    <input type="text" name="company" class="form-control form-control-sm" placeholder="Axa, Allianz..." required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Poliçe No *</label>
                                                    <input type="text" name="policy_no" class="form-control form-control-sm" required>
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-6">
                                                        <label class="form-label mb-1">Başlangıç *</label>
                                                        <input type="date" name="start_date" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label mb-1">Bitiş *</label>
                                                        <input type="date" name="end_date" class="form-control form-control-sm" required>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label mb-1">Prim Tutarı (₺)</label>
                                                    <input type="number" name="cost" step="0.01" class="form-control form-control-sm" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label mb-1">Not</label>
                                                    <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="fas fa-save me-1"></i> Kaydet
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-8 col-lg-7">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tür</th>
                                                    <th>Şirket</th>
                                                    <th>Poliçe No</th>
                                                    <th>Başlangıç</th>
                                                    <th>Bitiş</th>
                                                    <th>Prim</th>
                                                    <th>Durum</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($vehicle->insurances->sortByDesc('end_date') as $ins)
                                                    @php
                                                        $insExpired = now()->gt($ins->end_date);
                                                        $insExpiring = !$insExpired && now()->diffInDays($ins->end_date, false) < 30;
                                                        $statusClass = $insExpired ? 'danger' : ($insExpiring ? 'warning' : 'success');
                                                        $statusLabel = $insExpired ? 'Süresi Doldu' : ($insExpiring ? 'Yaklaşıyor' : 'Geçerli');
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-{{ $ins->type === 'kasko' ? 'primary' : 'info' }}">
                                                                {{ $ins->type === 'kasko' ? 'Kasko' : 'Trafik' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $ins->company }}</td>
                                                        <td><small>{{ $ins->policy_no }}</small></td>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($ins->start_date)->format('d.m.Y') }}</td>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($ins->end_date)->format('d.m.Y') }}</td>
                                                        <td>{{ $ins->cost ? '₺'.number_format($ins->cost, 2) : '—' }}</td>
                                                        <td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                                                        <td>
                                                            <form action="{{ route('vehicles.sigortalar.destroy', [$vehicle, $ins]) }}"
                                                                  method="POST" onsubmit="return confirm('Sil?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="8" class="text-center text-muted">Sigorta/Kasko kaydı yok.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if(session('success'))
    toastr.success("{{ session('success') }}");
@endif
</script>
@endpush
