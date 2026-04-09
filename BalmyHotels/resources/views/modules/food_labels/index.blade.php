@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-utensils me-2 text-primary"></i>Yemek İsimlik</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Yemek İsimlik</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre + Aksiyon Barı --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="🔍 Yemek ara..." value="{{ request('search') }}">
                </div>
                <div class="col-sm-3">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Tüm Kategoriler</option>
                        @foreach(\App\Models\FoodLabel::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">Durum</option>
                        <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                        <option value="0" @selected(request('is_active') === '0')>Pasif</option>
                    </select>
                </div>
                <div class="col-sm-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('food-labels.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Başlık + Yeni + Çoklu Yazdır --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <span class="text-muted small">{{ $labels->total() }} isimlik bulundu
                @if($labels->hasPages())<span class="text-muted">&nbsp;·&nbsp;Sayfa {{ $labels->currentPage() }}/{{ $labels->lastPage() }}</span>@endif
            </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn" onclick="toggleSelectAll()">
                <i class="fas fa-check-square me-1"></i>Tümünü Seç
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning" id="clearSelBtn"
                    onclick="clearSelection()" style="display:none" title="Tüm sayfalardan seçimi temizle">
                <i class="fas fa-times me-1"></i>Seçimi Temizle
            </button>
            <button type="button" class="btn btn-sm btn-success" id="printSelectedBtn"
                    onclick="printSelected()" style="display:none">
                <i class="fas fa-print me-1"></i>Seçilenleri Yazdır
                <span class="badge bg-white text-success ms-1" id="selectedCount">0</span>
            </button>
            <a href="{{ route('food-labels.export', request()->only(['search','category','is_active'])) }}"
               class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Excel'e Aktar
            </a>
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#jsonImportModal">
                <i class="fas fa-file-code me-1"></i>JSON Yemek Ekle
            </button>
            <a href="{{ route('food-labels.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Yeni İsimlik
            </a>
        </div>
    </div>

    @if($labels->getCollection()->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-utensils fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <p class="text-muted mb-3">Henüz yemek isimliği eklenmemiş.</p>
            <a href="{{ route('food-labels.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>İlk İsimliği Ekle
            </a>
        </div>
    </div>
    @else

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px"></th>
                        <th>#</th>
                        <th>Yemek Adı</th>
                        <th>Kategori</th>
                        <th>Diyet / Kalori</th>
                        <th>Allerjenler</th>
                        <th>Şube</th>
                        <th>Durum</th>
                        <th>Eklenme</th>
                        <th style="width:130px" class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($labels as $label)
                <tr @if(!$label->is_active) class="table-secondary opacity-75" @endif>
                    {{-- Checkbox --}}
                    <td class="ps-3">
                        <input type="checkbox" class="label-checkbox form-check-input"
                               value="{{ $label->id }}" onchange="onCheckboxChange(this)">
                    </td>
                    {{-- # --}}
                    <td class="text-muted small">{{ $label->id }}</td>
                    {{-- Adlar --}}
                    <td>
                        <div class="fw-semibold">{{ $label->getName() }}</div>
                        @if($name_en = $label->getName('en'))
                            @if($name_en !== $label->getName('tr'))
                            <div class="text-muted small">{{ $name_en }}</div>
                            @endif
                        @endif
                    </td>
                    {{-- Kategori --}}
                    <td>
                        @php $catLabel = \App\Models\FoodLabel::CATEGORIES[$label->category] ?? $label->category; @endphp
                        <span class="badge bg-light text-dark border">{{ $catLabel }}</span>
                    </td>
                    {{-- Diyet / Kalori --}}
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($label->calories)
                            <span class="badge" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;white-space:nowrap">
                                <i class="fas fa-fire-alt me-1"></i>{{ $label->calories }} kcal
                            </span>
                            @endif
                            @if($label->is_vegan)
                            <span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #10b981">🌱 Vegan</span>
                            @endif
                            @if($label->is_vegetarian && !$label->is_vegan)
                            <span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #10b981">🥗 Vejet.</span>
                            @endif
                            @if($label->is_halal)
                            <span class="badge" style="background:#ede9fe;color:#4c1d95;border:1px solid #7c3aed">☪ Helal</span>
                            @endif
                        </div>
                    </td>
                    {{-- Allerjenler --}}
                    <td>
                        @if(!empty($label->allergens))
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($label->getAllergenList() as $key => $info)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"
                                  title="{{ $info['label'] }} (EU #{{ $info['eu'] }})">
                                {{ $info['icon'] }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    {{-- Şube --}}
                    <td class="small">{{ $label->branch?->name ?? '<span class="text-muted">Genel</span>' }}</td>
                    {{-- Durum --}}
                    <td>
                        @if($label->is_active)
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25">Aktif</span>
                        @else
                        <span class="badge bg-secondary">Pasif</span>
                        @endif
                    </td>
                    {{-- Tarih --}}
                    <td class="text-muted small" style="white-space:nowrap">
                        {{ $label->created_at->format('d.m.Y') }}
                    </td>
                    {{-- İşlemler --}}
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <button type="button" class="btn btn-xs btn-outline-success"
                                    style="font-size:11px;padding:2px 7px"
                                    onclick="showQr('{{ $label->publicUrl() }}', '{{ addslashes($label->getName()) }}')"
                                    title="QR">
                                <i class="fas fa-qrcode"></i>
                            </button>
                            <a href="{{ route('food-labels.print-single', $label) }}" target="_blank"
                               class="btn btn-xs btn-outline-secondary"
                               style="font-size:11px;padding:2px 7px" title="Yazdır">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="{{ route('food-labels.edit', $label) }}"
                               class="btn btn-xs btn-outline-primary"
                               style="font-size:11px;padding:2px 7px" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('food-labels.destroy', $label) }}" method="POST"
                                  onsubmit="return confirm('Silmek istediğinizden emin misiniz?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"
                                        style="font-size:11px;padding:2px 7px" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sayfalama --}}
    @if($labels->hasPages())
    <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
        <div class="text-muted small">{{ $labels->firstItem() }}–{{ $labels->lastItem() }} / {{ $labels->total() }}</div>
        {{ $labels->links('pagination::bootstrap-5') }}
    </div>
    @endif

    {{-- Gizli form: çoklu yazdır --}}
    <form id="bulkPrintForm" action="{{ route('food-labels.print-bulk') }}" method="POST" target="_blank">
        @csrf
        <div id="bulkPrintInputs"></div>
    </form>

    @endif

{{-- JSON Import Modal --}}
<div class="modal fade" id="jsonImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-file-code me-2 text-info"></i>JSON ile Yemek Ekle</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Tek bir yemek objesi veya <code>[{...}, {...}]</code> şeklinde dizi yapıştırabilirsiniz.</p>
                <textarea id="jsonImportInput" class="form-control font-monospace" rows="18"
                          placeholder='{ "name": { "tr": "...", "en": "..." }, "category": "main", ... }'></textarea>
                <div id="jsonImportAlert" class="mt-2" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-info text-white" id="jsonImportBtn" onclick="submitJsonImport()">
                    <i class="fas fa-upload me-1"></i>İçe Aktar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- QR Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="qrModalTitle">QR Kod</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-3">
                <p class="text-muted small mb-3" id="qrModalSubtitle"></p>
                <div id="qrCodeCanvas" class="d-flex justify-content-center mb-3"></div>
                <div class="d-flex gap-2">
                    <a id="qrPublicLink" href="#" target="_blank"
                       class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="fas fa-external-link-alt me-1"></i>Sayfaı Aç
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="downloadQr()">
                        <i class="fas fa-download me-1"></i>PNG
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// ---- localStorage selection (sayfalar arası kalıcı seçim) ----
const LS_KEY = 'foodlabel_selected_v2';

function getSelected() {
    try { return new Set(JSON.parse(localStorage.getItem(LS_KEY) || '[]')); }
    catch(e) { return new Set(); }
}
function saveSelected(set) {
    localStorage.setItem(LS_KEY, JSON.stringify([...set]));
}
function clearSelection() {
    localStorage.removeItem(LS_KEY);
    document.querySelectorAll('.label-checkbox').forEach(cb => cb.checked = false);
    refreshUI();
}

function onCheckboxChange(cb) {
    const sel = getSelected();
    cb.checked ? sel.add(cb.value) : sel.delete(cb.value);
    saveSelected(sel);
    refreshUI();
}

function toggleSelectAll() {
    const cbs  = document.querySelectorAll('.label-checkbox');
    const sel  = getSelected();
    const allPageChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => {
        cb.checked = !allPageChecked;
        cb.checked ? sel.add(cb.value) : sel.delete(cb.value);
    });
    saveSelected(sel);
    refreshUI();
}

function refreshUI() {
    const sel   = getSelected();
    const count = sel.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('printSelectedBtn').style.display = count > 0 ? 'inline-flex' : 'none';
    document.getElementById('clearSelBtn').style.display     = count > 0 ? 'inline-flex' : 'none';
    const allPageChecked = [...document.querySelectorAll('.label-checkbox')].every(cb => cb.checked);
    document.getElementById('selectAllBtn').innerHTML = allPageChecked
        ? '<i class="fas fa-square me-1"></i>Sayfayı Kaldır'
        : '<i class="fas fa-check-square me-1"></i>Tümünü Seç';
}

// Sayfa yüklenince önceki seçimleri geri yükle
document.addEventListener('DOMContentLoaded', function() {
    const sel = getSelected();
    document.querySelectorAll('.label-checkbox').forEach(cb => {
        if (sel.has(cb.value)) cb.checked = true;
    });
    refreshUI();
});

function printSelected() {
    const ids    = [...getSelected()];
    if (ids.length === 0) return;
    const inputs = document.getElementById('bulkPrintInputs');
    inputs.innerHTML = ids.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('');
    document.getElementById('bulkPrintForm').submit();
}

// ---- QR ----
let qrInstance  = null;
let currentQrUrl = '';

function showQr(url, name) {
    currentQrUrl = url;
    document.getElementById('qrModalTitle').textContent = name;
    document.getElementById('qrModalSubtitle').textContent = url;
    document.getElementById('qrPublicLink').href = url;
    const container = document.getElementById('qrCodeCanvas');
    container.innerHTML = '';
    qrInstance = new QRCode(container, {
        text: url, width: 220, height: 220,
        colorDark: '#1b2d24', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    new bootstrap.Modal(document.getElementById('qrModal')).show();
}
function downloadQr() {
    const canvas = document.querySelector('#qrCodeCanvas canvas');
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = 'qr-yemek.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// ---- JSON Import ----
function submitJsonImport() {
    const textarea  = document.getElementById('jsonImportInput');
    const alertBox  = document.getElementById('jsonImportAlert');
    const btn       = document.getElementById('jsonImportBtn');
    const jsonData  = textarea.value.trim();

    alertBox.style.display = 'none';
    alertBox.innerHTML     = '';

    if (!jsonData) {
        showImportAlert('danger', 'Lütfen JSON yapıştırın.');
        return;
    }

    btn.disabled   = true;
    btn.innerHTML  = '<i class="fas fa-spinner fa-spin me-1"></i>İşleniyor...';

    fetch('{{ route("food-labels.json-import") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ json_data: jsonData }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            showImportAlert('success', '<i class="fas fa-check me-1"></i>' + data.message);
            textarea.value = '';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('jsonImportModal')).hide();
                window.location.reload();
            }, 1200);
        } else {
            showImportAlert('danger', data.error || 'Bir hata oluştu.');
        }
    })
    .catch(() => showImportAlert('danger', 'Sunucuya ulaşılamadı.'))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-upload me-1"></i>İçe Aktar';
    });
}

function showImportAlert(type, html) {
    const box = document.getElementById('jsonImportAlert');
    box.className = 'alert alert-' + type + ' py-2 small mt-2';
    box.innerHTML = html;
    box.style.display = 'block';
}
</script>
@endpush
