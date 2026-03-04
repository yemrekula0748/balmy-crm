@extends('layouts.default')

@push('styles')
<style>
.scope-section { border-radius:12px; border:2px solid; margin-bottom:1.5rem; }
.scope-section.scope-1 { border-color:#e74c3c; }
.scope-section.scope-2 { border-color:#f39c12; }
.scope-section.scope-3 { border-color:#27ae60; }
.scope-section .scope-header { padding:12px 18px; border-radius:10px 10px 0 0; font-weight:700; display:flex; align-items:center; gap:10px; }
.scope-section.scope-1 .scope-header { background:#ffeaea; color:#c0392b; }
.scope-section.scope-2 .scope-header { background:#fff8e1; color:#b7770d; }
.scope-section.scope-3 .scope-header { background:#e8f5e9; color:#1a6b3c; }
.entry-row { background:#f9f9f9; border-radius:8px; padding:12px; margin-bottom:8px; border:1px solid #e9ecef; }
.entry-row:hover { background:#f0f4ff; }
.ef-tag { font-size:0.72rem; background:#e3e3e3; border-radius:4px; padding:1px 6px; }
.std-checkbox label { cursor:pointer; user-select:none; }
.std-checkbox input:checked + label { background:#e8f5e9; border-color:#27ae60; color:#1a6b3c; }
.std-checkbox label { border:1px solid #dee2e6; border-radius:6px; padding:4px 10px; font-size:0.8rem; transition:.2s; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>🌿 Yeni Karbon Ayak İzi Raporu</h4>
                <span>ISO 14064 · HCMI · GHG Protocol Scope 1/2/3 · CSRD</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carbon.index') }}">Karbon Raporları</a></li>
                <li class="breadcrumb-item active">Yeni Rapor</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Lütfen hataları düzeltin:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('carbon.store') }}" id="carbonForm">
        @csrf

        {{-- ============================================================
             BÖLÜM 1: GENEL BİLGİLER
        ============================================================ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">
                    <span class="badge bg-primary me-2">1</span> Genel Rapor Bilgileri
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Rapor Adı <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="Örn: Ocak 2026 Aylık Karbon Ayak İzi Raporu" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Şube</label>
                        <select name="branch_id" class="form-select">
                            <option value="">— Genel / Tüm Otel —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rapor Tipi <span class="text-danger">*</span></label>
                        <select name="report_type" class="form-select" required>
                            <option value="monthly"   @selected(old('report_type','monthly')=='monthly')>Aylık</option>
                            <option value="quarterly" @selected(old('report_type')=='quarterly')>Çeyreklik (3 Ay)</option>
                            <option value="annual"    @selected(old('report_type')=='annual')>Yıllık</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dönem Başlangıcı <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control @error('period_start') is-invalid @enderror"
                               value="{{ old('period_start') }}" required>
                        @error('period_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dönem Sonu <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control @error('period_end') is-invalid @enderror"
                               value="{{ old('period_end') }}" required>
                        @error('period_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             BÖLÜM 2: OPERASYONELVERİLER
        ============================================================ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">
                    <span class="badge bg-warning text-dark me-2">2</span>
                    Operasyonel Veriler
                    <span class="text-muted fw-normal small ms-2">(Yoğunluk metrikleri için gereklidir - HCMI)</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary py-2 px-3 mb-3" style="font-size:.8rem">
                    <i class="fas fa-info-circle text-primary me-1"></i>
                    Bu veriler emisyon <strong>yoğunluk metriklerini</strong> hesaplamak için kullanılır. Örnek: <strong>kgCO₂e / oda-gece</strong> = Toplam CO₂e ÷ Oda-Gece Sayısı. Bu metrikler HCMI, CSRD ve GRI 305 raporlamalarının temelidir.
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Toplam Misafir Sayısı <span class="text-danger">*</span></label>
                        <input type="number" name="total_guests" class="form-control" value="{{ old('total_guests',0) }}" min="0" required>
                        <div class="form-text"><strong>Neden?</strong> Misafir başına CO₂e hesabı için gereklidir.<br>Formül: Toplam CO₂e ÷ Misafir = kgCO₂e/misafir (GRI 305)</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Oda-Gece Sayısı <span class="text-danger">*</span></label>
                        <input type="number" name="occupied_rooms" class="form-control" value="{{ old('occupied_rooms',0) }}" min="0" required>
                        <div class="form-text"><strong>Neden?</strong> HCMI'nın ana performans metriği.<br>Formül: Toplam CO₂e ÷ Oda-Gece = kgCO₂e/oda-gece</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Toplam Oda Kapasitesi</label>
                        <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms',0) }}" min="0">
                        <div class="form-text"><strong>Neden?</strong> Doluluk oranı (%) hesabı için.<br>Formül: Oda-Gece ÷ (Oda Sayısı × Gün) × 100</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Personel Sayısı</label>
                        <input type="number" name="staff_count" class="form-control" value="{{ old('staff_count',0) }}" min="0">
                        <div class="form-text"><strong>Neden?</strong> Çalışan başına emisyon yoğunluğu.<br>Formül: Toplam CO₂e ÷ Personel (CSRD/ESRS)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Toplam Alan (m²)</label>
                        <input type="number" name="total_area_sqm" class="form-control" value="{{ old('total_area_sqm',0) }}" min="0" step="0.01">
                        <div class="form-text"><strong>Neden?</strong> Alan başına enerji yoğunluğu (EU Taxonomy / ISO 50001).<br>Formül: Toplam CO₂e ÷ m² = kgCO₂e/m²</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Yenilenebilir Enerji Oranı (%)</label>
                        <input type="number" name="renewable_energy_pct" class="form-control" value="{{ old('renewable_energy_pct',0) }}" min="0" max="100" step="0.1">
                        <div class="form-text"><strong>Neden?</strong> ISO 14001 ve EU Taxonomy yeşil sınıflandırması gerektirir.<br>Hesap: Yenilenebilir kWh ÷ Toplam kWh × 100</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Atık Geri Dönüşüm Oranı (%)</label>
                        <input type="number" name="waste_recycling_rate" class="form-control" value="{{ old('waste_recycling_rate',0) }}" min="0" max="100" step="0.1">
                        <div class="form-text"><strong>Neden?</strong> SDG 12 (Sorumlu Tüketim) ve GRI 306 raporlaması için.<br>Hesap: Geri Dönüşüm kg ÷ Toplam Atık kg × 100</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             BÖLÜM 3: EMİSYON VERİLERİ — SCOPE 1 / 2 / 3
        ============================================================ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">
                    <span class="badge bg-danger me-2">3</span>
                    Emisyon Verileri — GHG Protocol Scope 1 / 2 / 3
                </h6>
            </div>
            <div class="card-body">

                <div class="alert alert-info mb-4 py-2 px-3" style="font-size:.85rem">
                    <strong>📋 Bilgi:</strong>
                    <strong>Scope 1</strong> = Tesiste doğrudan yakılan yakıtlar (doğal gaz, <strong>LNG</strong>, fuel oil, LPG), soğutucu gaz kaçakları.
                    <strong>Scope 2</strong> = Satın alınan elektrik/ısı/soğutma — <em>Location-Based</em> (şebeke EF) veya <em>Market-Based</em> (I-REC / YEK-G / GoO sertifikalı ya da tesis içi GES, EF=0).
                    <strong>Scope 3</strong> = Değer zinciri: su, atık, gıda tüketimi (et/tavuk/balık/süt/bitkisel), çamaşırhane, ulaşım, tedarik.
                    <br><span class="text-muted">Kullanmadığınız kalemler için <strong>0</strong> giriniz.</span>
                </div>

                {{-- SCOPE 1 --}}
                <div class="scope-section scope-1">
                    <div class="scope-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9z"/></svg>
                        Scope 1 — Doğrudan Emisyonlar (yakıt, soğutucu gazlar)
                    </div>
                    <div class="p-3">
                        <div id="scope1-entries">
                            @foreach($categories['scope1'] as $catKey => $catDef)
                            <div class="entry-row" id="entry-s1-{{ $loop->index }}">
                                <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][scope]" value="1">
                                <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][category]" value="{{ $catKey }}">
                                <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                                <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                                <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                        <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }} — {{ $catDef['ef_source'] }}</span>
                                        @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }}) <span class="text-danger">*</span></label>
                                        <input type="number" name="entries[{{ 's1_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                               value="{{ old('entries.s1_'.$loop->index.'.quantity', 0) }}" min="0" step="any"
                                               data-ef="{{ $catDef['ef'] }}" data-unit="{{ $catDef['unit'] }}">
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <label class="form-label small mb-1">CO₂e (kg)</label>
                                        <div class="fw-bold text-danger co2-result small">0.000</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Notlar</label>
                                        <input type="text" name="entries[{{ 's1_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                               placeholder="İsteğe bağlı açıklama" value="{{ old('entries.s1_'.$loop->index.'.notes') }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SCOPE 2 --}}
                <div class="scope-section scope-2">
                    <div class="scope-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                        Scope 2 — Dolaylı Enerji Emisyonları (elektrik, merkezi ısıtma/soğutma)
                        <span style="font-size:.72rem;font-weight:400;margin-left:auto;opacity:.8">ISO 14064-1 §8.3: Şebeke (Location-Based) veya Sertifikalı Yenilenebilir / GES (Market-Based, EF=0) seçilebilir</span>
                    </div>
                    <div class="p-3">
                        <div id="scope2-entries">
                            @foreach($categories['scope2'] as $catKey => $catDef)
                            <div class="entry-row" id="entry-s2-{{ $loop->index }}">
                                <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][scope]" value="2">
                                <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][category]" value="{{ $catKey }}">
                                <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                                <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                                <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                        <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }} — {{ $catDef['ef_source'] }}</span>
                                        @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }}) <span class="text-danger">*</span></label>
                                        <input type="number" name="entries[{{ 's2_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                               value="{{ old('entries.s2_'.$loop->index.'.quantity', 0) }}" min="0" step="any"
                                               data-ef="{{ $catDef['ef'] }}" data-unit="{{ $catDef['unit'] }}">
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <label class="form-label small mb-1">CO₂e (kg)</label>
                                        <div class="fw-bold text-warning co2-result small">0.000</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Notlar</label>
                                        <input type="text" name="entries[{{ 's2_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                               placeholder="İsteğe bağlı" value="{{ old('entries.s2_'.$loop->index.'.notes') }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SCOPE 3 --}}
                <div class="scope-section scope-3">
                    <div class="scope-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        Scope 3 — Diğer Dolaylı Emisyonlar (su, atık, gıda, ulaşım, tedarik zinciri)
                    </div>
                    <div class="p-3">
                        <div id="scope3-entries">
                            @foreach($categories['scope3'] as $catKey => $catDef)
                            <div class="entry-row" id="entry-s3-{{ $loop->index }}">
                                <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][scope]" value="3">
                                <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][category]" value="{{ $catKey }}">
                                <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                                <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                                <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                        <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }} — {{ $catDef['ef_source'] }}</span>
                                        @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }}) <span class="text-danger">*</span></label>
                                        <input type="number" name="entries[{{ 's3_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                               value="{{ old('entries.s3_'.$loop->index.'.quantity', 0) }}" min="0" step="any"
                                               data-ef="{{ $catDef['ef'] }}" data-unit="{{ $catDef['unit'] }}">
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <label class="form-label small mb-1">CO₂e (kg)</label>
                                        <div class="fw-bold text-success co2-result small">0.000</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Notlar</label>
                                        <input type="text" name="entries[{{ 's3_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                               placeholder="İsteğe bağlı" value="{{ old('entries.s3_'.$loop->index.'.notes') }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Canlı Özet --}}
                <div class="card border-success mt-3">
                    <div class="card-body py-2">
                        <div class="row text-center g-2">
                            <div class="col-md-3">
                                <div class="text-muted small">Scope 1</div>
                                <div class="fw-bold text-danger" id="sum-scope1">0.000 kg</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small">Scope 2</div>
                                <div class="fw-bold text-warning" id="sum-scope2">0.000 kg</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small">Scope 3</div>
                                <div class="fw-bold text-success" id="sum-scope3">0.000 kg</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small fw-bold">TOPLAM CO₂e</div>
                                <div class="fw-bold text-primary fs-5" id="sum-total">0.000 kg</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================
             BÖLÜM 4: STANDARTLAR & NOTLAR
        ============================================================ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">
                    <span class="badge bg-info text-dark me-2">4</span>
                    Uygulanan Standartlar & Metodoloji
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem">
                    <i class="fas fa-question-circle text-primary me-1"></i>
                    <strong>Bu seçim ne işe yarar?</strong> Raporunuzun hangi uluslararası metodoloji ve çerçeveler kapsamında hazırlandığını belirtir. Seçtiğiniz standartlar; denetçilere, bankalara ve yeşil sertifika kurumlarına (LEED, Green Key vb.) sunulan resmi raporunuza dahil edilir. <strong>ISO 14064-1 ve GHG Protocol</strong> tüm raporlar için önerilir.
                </div>
                <label class="form-label fw-semibold mb-2">Uygulanan Standartlar</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @php $defaultStandards = old('standards_applied', ['ISO 14064-1', 'GHG_Protocol', 'HCMI']); @endphp
                    @foreach($standards as $key => $stdLabel)
                    <div class="std-checkbox">
                        <input type="checkbox" name="standards_applied[]" value="{{ $key }}"
                               id="std_{{ $key }}" class="d-none"
                               @checked(in_array($key, $defaultStandards))>
                        <label for="std_{{ $key }}" title="{{ $stdLabel }}">{{ $key }}</label>
                    </div>
                    @endforeach
                </div>
                <div class="mb-3" style="font-size:.78rem">
                    @foreach($standards as $key => $stdLabel)
                    <span id="std-desc-{{ $key }}" class="std-desc text-muted d-none me-3">✓ {{ $stdLabel }}</span>
                    @endforeach
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Metodoloji Notları & Varsayımlar</label>
                        <textarea name="methodology_notes" class="form-control" rows="4"
                                  placeholder="Örnek notlar:&#10;- Doğalgaz sayıcı okuması alınamadı, fatura verisi kullanıldı&#10;- Sobanın günde ortalama 8 saat çalıştığı varsayıldı&#10;- Servis aracı km kaydı tutulmadığından tahmini güzergâh mesafesi kullanıldı">{{ old('methodology_notes') }}</textarea>
                        <div class="form-text">Veri eksikliği, tahmin yöntemi veya birşey kesin bilinmiyorsa burada açıklayın. Denetim sırasında bu notlar röportaj sorularını yanıtlar.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">İyileştirme Önerileri & Hedefler</label>
                        <textarea name="improvement_notes" class="form-control" rows="4"
                                  placeholder="Örnek:&#10;- Bir sonraki dönem kâğıt geri dönüşümü başlatılacak&#10;- 2027 sonuna kadar çatıya GES kurulması planlanıyor&#10;- LED dönüşümü ile Scope 2 emisyonunun %15 düşürülmesi hedefleniyor">{{ old('improvement_notes') }}</textarea>
                        <div class="form-text">Bir sonraki dönem için alınan veya planlanan aksiyonlar. Denetim raporunda “iyileştirme planı” olarak görünür.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save me-1"></i> Raporu Kaydet (Taslak)
            </button>
            <a href="{{ route('carbon.index') }}" class="btn btn-outline-secondary">İptal</a>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Canlı CO2 hesaplama
    function recalcAll() {
        let s1 = 0, s2 = 0, s3 = 0;

        document.querySelectorAll('.qty-input').forEach(inp => {
            const qty = parseFloat(inp.value) || 0;
            const ef  = parseFloat(inp.dataset.ef) || 0;
            const co2 = qty * ef;

            const row = inp.closest('.entry-row');
            if (row) {
                const resultEl = row.querySelector('.co2-result');
                if (resultEl) resultEl.textContent = co2.toFixed(3) + ' kg';
            }

            const hiddenScope = inp.closest('.entry-row')?.querySelector('input[name$="[scope]"]');
            if (hiddenScope) {
                const scope = parseInt(hiddenScope.value);
                if (scope === 1) s1 += co2;
                else if (scope === 2) s2 += co2;
                else if (scope === 3) s3 += co2;
            }
        });

        document.getElementById('sum-scope1').textContent = s1.toFixed(3) + ' kg  (' + (s1/1000).toFixed(3) + ' t)';
        document.getElementById('sum-scope2').textContent = s2.toFixed(3) + ' kg  (' + (s2/1000).toFixed(3) + ' t)';
        document.getElementById('sum-scope3').textContent = s3.toFixed(3) + ' kg  (' + (s3/1000).toFixed(3) + ' t)';
        const total = s1+s2+s3;
        document.getElementById('sum-total').textContent  = total.toFixed(3) + ' kg  (' + (total/1000).toFixed(3) + ' t)';
    }

    document.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('input', recalcAll);
    });

    recalcAll();

    // Std checkbox toggle + açıklama gösterimi
    function updateStdDescs() {
        document.querySelectorAll('.std-checkbox input').forEach(cb => {
            const desc = document.getElementById('std-desc-' + cb.value);
            if (desc) desc.classList.toggle('d-none', !cb.checked);
        });
    }
    document.querySelectorAll('.std-checkbox input').forEach(cb => {
        cb.addEventListener('change', updateStdDescs);
    });
    updateStdDescs();
});
</script>
@endpush
