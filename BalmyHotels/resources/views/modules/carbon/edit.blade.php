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
                <h4>✏️ Raporu Düzenle</h4>
                <span>{{ $carbon->title }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carbon.index') }}">Karbon Raporları</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carbon.show', $carbon) }}">Rapor #{{ $carbon->id }}</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Hataları düzeltin:</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($carbon->status === 'verified')
    <div class="alert alert-warning">
        <i class="fas fa-lock me-2"></i> Bu rapor <strong>doğrulanmış</strong> durumda — düzenlenemez.
    </div>
    @else

    {{-- Mevcut entry değerlerini JS ile kullanmak için --}}
    @php
        $existingEntries = $carbon->entries->keyBy('category');
    @endphp

    <form method="POST" action="{{ route('carbon.update', $carbon) }}">
        @csrf @method('PUT')

        {{-- BÖLÜM 1 --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><span class="badge bg-primary me-2">1</span> Genel Bilgiler</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Rapor Adı <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $carbon->title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Şube</label>
                        <select name="branch_id" class="form-select">
                            <option value="">— Genel —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('branch_id', $carbon->branch_id) == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rapor Tipi</label>
                        <select name="report_type" class="form-select">
                            <option value="monthly"   @selected(old('report_type',$carbon->report_type)=='monthly')>Aylık</option>
                            <option value="quarterly" @selected(old('report_type',$carbon->report_type)=='quarterly')>Çeyreklik</option>
                            <option value="annual"    @selected(old('report_type',$carbon->report_type)=='annual')>Yıllık</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dönem Başlangıcı</label>
                        <input type="date" name="period_start" class="form-control" value="{{ old('period_start', $carbon->period_start->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dönem Sonu</label>
                        <input type="date" name="period_end" class="form-control" value="{{ old('period_end', $carbon->period_end->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- BÖLÜM 2 --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><span class="badge bg-warning text-dark me-2">2</span> Operasyonel Veriler</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Toplam Misafir</label>
                        <input type="number" name="total_guests" class="form-control" value="{{ old('total_guests', $carbon->total_guests) }}" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Oda-Gece</label>
                        <input type="number" name="occupied_rooms" class="form-control" value="{{ old('occupied_rooms', $carbon->occupied_rooms) }}" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Toplam Oda</label>
                        <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms', $carbon->total_rooms) }}" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Personel</label>
                        <input type="number" name="staff_count" class="form-control" value="{{ old('staff_count', $carbon->staff_count) }}" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Alan (m²)</label>
                        <input type="number" name="total_area_sqm" class="form-control" value="{{ old('total_area_sqm', $carbon->total_area_sqm) }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Yenilenebilir Enerji (%)</label>
                        <input type="number" name="renewable_energy_pct" class="form-control" value="{{ old('renewable_energy_pct', $carbon->renewable_energy_pct) }}" min="0" max="100" step="0.1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Geri Dönüşüm Oranı (%)</label>
                        <input type="number" name="waste_recycling_rate" class="form-control" value="{{ old('waste_recycling_rate', $carbon->waste_recycling_rate) }}" min="0" max="100" step="0.1">
                    </div>
                </div>
            </div>
        </div>

        {{-- BÖLÜM 3 --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><span class="badge bg-danger me-2">3</span> Emisyon Verileri</h6>
            </div>
            <div class="card-body">

                {{-- SCOPE 1 --}}
                <div class="scope-section scope-1">
                    <div class="scope-header">⚡ Scope 1 — Doğrudan Emisyonlar</div>
                    <div class="p-3">
                        @foreach($categories['scope1'] as $catKey => $catDef)
                        @php $existing = $existingEntries->get($catKey); @endphp
                        <div class="entry-row">
                            <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][scope]" value="1">
                            <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][category]" value="{{ $catKey }}">
                            <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                            <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                            <input type="hidden" name="entries[{{ 's1_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                    <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }}</span>
                                    @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }})</label>
                                    <input type="number" name="entries[{{ 's1_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                           value="{{ old('entries.s1_'.$loop->index.'.quantity', $existing?->quantity ?? 0) }}"
                                           min="0" step="any" data-ef="{{ $catDef['ef'] }}">
                                </div>
                                <div class="col-md-2 text-center">
                                    <label class="form-label small mb-1">CO₂e (kg)</label>
                                    <div class="fw-bold text-danger co2-result small">{{ number_format(($existing?->co2_kg ?? 0), 3) }} kg</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Notlar</label>
                                    <input type="text" name="entries[{{ 's1_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                           value="{{ old('entries.s1_'.$loop->index.'.notes', $existing?->notes) }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- SCOPE 2 --}}
                <div class="scope-section scope-2">
                    <div class="scope-header">
                        🔌 Scope 2 — Dolaylı Enerji Emisyonları
                        <span style="font-size:.72rem;font-weight:400;margin-left:auto;opacity:.8">ISO 14064-1 §8.3 Market-Based: I-REC / YEK-G / GoO sertifikalı veya tesis içi GES için EF=0 kullanın</span>
                    </div>
                    <div class="p-3">
                        @foreach($categories['scope2'] as $catKey => $catDef)
                        @php $existing = $existingEntries->get($catKey); @endphp
                        <div class="entry-row">
                            <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][scope]" value="2">
                            <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][category]" value="{{ $catKey }}">
                            <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                            <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                            <input type="hidden" name="entries[{{ 's2_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                    <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }}</span>
                                    @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }})</label>
                                    <input type="number" name="entries[{{ 's2_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                           value="{{ old('entries.s2_'.$loop->index.'.quantity', $existing?->quantity ?? 0) }}"
                                           min="0" step="any" data-ef="{{ $catDef['ef'] }}">
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="fw-bold text-warning co2-result small">{{ number_format(($existing?->co2_kg ?? 0), 3) }} kg</div>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="entries[{{ 's2_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                           value="{{ old('', $existing?->notes) }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- SCOPE 3 --}}
                <div class="scope-section scope-3">
                    <div class="scope-header">🌍 Scope 3 — Diğer Dolaylı Emisyonlar</div>
                    <div class="p-3">
                        @foreach($categories['scope3'] as $catKey => $catDef)
                        @php $existing = $existingEntries->get($catKey); @endphp
                        <div class="entry-row">
                            <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][scope]" value="3">
                            <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][category]" value="{{ $catKey }}">
                            <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][unit]" value="{{ $catDef['unit'] }}">
                            <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][emission_factor]" value="{{ $catDef['ef'] }}">
                            <input type="hidden" name="entries[{{ 's3_'.$loop->index }}][ef_source]" value="{{ $catDef['ef_source'] }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">{{ $catDef['label'] }}</label>
                                    <span class="ef-tag d-block">EF: {{ $catDef['ef'] }} kgCO₂e/{{ $catDef['unit'] }}</span>
                                    @if(!empty($catDef['help']))<div class="text-muted mt-1" style="font-size:.7rem;line-height:1.4">{{ $catDef['help'] }}</div>@endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Miktar ({{ $catDef['unit'] }})</label>
                                    <input type="number" name="entries[{{ 's3_'.$loop->index }}][quantity]" class="form-control form-control-sm qty-input"
                                           value="{{ old('entries.s3_'.$loop->index.'.quantity', $existing?->quantity ?? 0) }}"
                                           min="0" step="any" data-ef="{{ $catDef['ef'] }}">
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="fw-bold text-success co2-result small">{{ number_format(($existing?->co2_kg ?? 0), 3) }} kg</div>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="entries[{{ 's3_'.$loop->index }}][notes]" class="form-control form-control-sm"
                                           value="{{ $existing?->notes }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border-success mt-3">
                    <div class="card-body py-2">
                        <div class="row text-center g-2">
                            <div class="col-md-3"><div class="text-muted small">Scope 1</div><div class="fw-bold text-danger" id="sum-scope1">{{ number_format($carbon->total_co2_scope1, 3) }} kg</div></div>
                            <div class="col-md-3"><div class="text-muted small">Scope 2</div><div class="fw-bold text-warning" id="sum-scope2">{{ number_format($carbon->total_co2_scope2, 3) }} kg</div></div>
                            <div class="col-md-3"><div class="text-muted small">Scope 3</div><div class="fw-bold text-success" id="sum-scope3">{{ number_format($carbon->total_co2_scope3, 3) }} kg</div></div>
                            <div class="col-md-3"><div class="text-muted small fw-bold">TOPLAM</div><div class="fw-bold text-primary fs-5" id="sum-total">{{ number_format($carbon->total_co2_total, 3) }} kg</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BÖLÜM 4 --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><span class="badge bg-info text-dark me-2">4</span> Standartlar & Notlar</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem">
                    <i class="fas fa-question-circle text-primary me-1"></i>
                    <strong>Bu seçim ne işe yarar?</strong> Raporun hangi metodoloji ve çerçeveler kapsamında hazırlandığını belirtir. Seçilenler; denetçilere, bankalara ve yeşil sertifika kurumlarına sunulan resmi raporda yer alır.
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @php $defaultStandards = old('standards_applied', $carbon->standards_applied ?? ['ISO 14064-1', 'GHG_Protocol', 'HCMI']); @endphp
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
                    <span id="std-desc-{{ $key }}" class="std-desc text-muted @if(!in_array($key, $defaultStandards)) d-none @endif me-3">✓ {{ $stdLabel }}</span>
                    @endforeach
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Metodoloji Notları & Varsayımlar</label>
                        <textarea name="methodology_notes" class="form-control" rows="4"
                                  placeholder="Örnek notlar:&#10;- Doğalgaz sayıcı okuması alınamadı, fatura verisi kullanıldı&#10;- Servis aracı km kaydı tutulmadığından tahmini güzergâh mesafesi kullanıldı">{{ old('methodology_notes', $carbon->methodology_notes) }}</textarea>
                        <div class="form-text">Veri eksikliği, tahmin yöntemi veya birşey kesin bilinmiyorsa burada açıklayın. Denetim sırasında bu notlar röportaj sorularını yanıtlar.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">İyileştirme Önerileri & Hedefler</label>
                        <textarea name="improvement_notes" class="form-control" rows="4"
                                  placeholder="Bir sonraki dönem için alınan veya planlanan aksiyonlar...">{{ old('improvement_notes', $carbon->improvement_notes) }}</textarea>
                        <div class="form-text">Denetim raporunda “iyileştirme planı” olarak görünür.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Güncelle
            </button>
            <a href="{{ route('carbon.show', $carbon) }}" class="btn btn-outline-secondary">İptal</a>
        </div>
    </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalcAll() {
        let s1=0, s2=0, s3=0;
        document.querySelectorAll('.qty-input').forEach(inp => {
            const qty = parseFloat(inp.value)||0;
            const ef  = parseFloat(inp.dataset.ef)||0;
            const co2 = qty*ef;
            const row = inp.closest('.entry-row');
            if(row){ const r = row.querySelector('.co2-result'); if(r) r.textContent = co2.toFixed(3)+' kg'; }
            const hs = inp.closest('.entry-row')?.querySelector('input[name$="[scope]"]');
            if(hs){ const sc=parseInt(hs.value); if(sc===1)s1+=co2; else if(sc===2)s2+=co2; else s3+=co2; }
        });
        document.getElementById('sum-scope1').textContent = s1.toFixed(3)+' kg';
        document.getElementById('sum-scope2').textContent = s2.toFixed(3)+' kg';
        document.getElementById('sum-scope3').textContent = s3.toFixed(3)+' kg';
        document.getElementById('sum-total').textContent  = (s1+s2+s3).toFixed(3)+' kg';
    }
    document.querySelectorAll('.qty-input').forEach(inp => inp.addEventListener('input', recalcAll));
    recalcAll();

    // Standart açıklama toggle
    function updateStdDescs() {
        document.querySelectorAll('.std-checkbox input').forEach(cb => {
            const desc = document.getElementById('std-desc-' + cb.value);
            if (desc) desc.classList.toggle('d-none', !cb.checked);
        });
    }
    document.querySelectorAll('.std-checkbox input').forEach(cb => cb.addEventListener('change', updateStdDescs));
    updateStdDescs();
});
</script>
@endpush
