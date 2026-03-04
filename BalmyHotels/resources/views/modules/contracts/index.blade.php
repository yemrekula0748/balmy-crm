@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-file-contract me-2" style="color:#4361ee"></i>Sözleşme Karşılaştırma</h4>
                <span class="text-muted small">İki PDF veya DOCX dosyasının farklılıklarını inceleyin</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Sözleşme Karşılaştırma</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- YÜKLENİYOR GÖSTERGESİ --}}
    <div id="loadingBar" class="d-none mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width:3rem;height:3rem"></div>
                <h5 class="text-muted">Dosyalar analiz ediliyor…</h5>
                <p class="text-muted small mb-0">Bu işlem dosya boyutuna göre birkaç saniye sürebilir.</p>
            </div>
        </div>
    </div>

    {{-- YÜKLEME FORMU --}}
    <div class="card border-0 shadow-sm mb-4" id="uploadCard">
        <div class="card-header py-3" style="background:linear-gradient(135deg,#4361ee,#3a4fe0);border:0">
            <h5 class="card-title mb-0 text-white">
                <i class="fas fa-upload me-2"></i>Yeni Karşılaştırma
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('contracts.compare') }}" method="POST"
                  enctype="multipart/form-data" id="compareForm">
                @csrf

                {{-- Başlık --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase" style="letter-spacing:.04em">
                        Karşılaştırma Başlığı <span class="text-muted fw-normal">(isteğe bağlı)</span>
                    </label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           placeholder="Örn: Kira Sözleşmesi v1 – v2" value="{{ old('title') }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-4">
                    {{-- Dosya A --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted text-uppercase" style="letter-spacing:.04em">
                            Dosya A <span class="text-danger">*</span>
                        </label>
                        <div class="upload-zone @error('file_a') border-danger @enderror"
                             id="zoneA" onclick="document.getElementById('fileA').click()"
                             ondragover="dragOver(event)" ondragleave="dragLeave(event,'zoneA')"
                             ondrop="dropFile(event,'fileA','zoneA','previewA')">
                            <input type="file" id="fileA" name="file_a" class="d-none"
                                   accept=".pdf,.docx"
                                   onchange="previewFile(this,'previewA','zoneA')">
                            <div id="previewA" class="upload-placeholder">
                                <i class="fas fa-file-alt fa-2x text-muted mb-2 d-block"></i>
                                <div class="fw-semibold text-muted">Tıkla veya sürükle</div>
                                <div class="text-muted small mt-1">PDF, DOCX • Maks 20MB</div>
                            </div>
                        </div>
                        @error('file_a')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Dosya B --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted text-uppercase" style="letter-spacing:.04em">
                            Dosya B <span class="text-danger">*</span>
                        </label>
                        <div class="upload-zone @error('file_b') border-danger @enderror"
                             id="zoneB" onclick="document.getElementById('fileB').click()"
                             ondragover="dragOver(event)" ondragleave="dragLeave(event,'zoneB')"
                             ondrop="dropFile(event,'fileB','zoneB','previewB')">
                            <input type="file" id="fileB" name="file_b" class="d-none"
                                   accept=".pdf,.docx"
                                   onchange="previewFile(this,'previewB','zoneB')">
                            <div id="previewB" class="upload-placeholder">
                                <i class="fas fa-file-alt fa-2x text-muted mb-2 d-block"></i>
                                <div class="fw-semibold text-muted">Tıkla veya sürükle</div>
                                <div class="text-muted small mt-1">PDF, DOCX • Maks 20MB</div>
                            </div>
                        </div>
                        @error('file_b')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary px-5 py-2" id="compareBtn">
                        <i class="fas fa-balance-scale me-2"></i>Karşılaştır
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- GEÇMİŞ --}}
    @if($comparisons->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="fas fa-history text-primary"></i>
            <h5 class="card-title mb-0">Önceki Karşılaştırmalar</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Başlık / Dosyalar</th>
                            <th class="text-center" style="min-width:110px">Benzerlik</th>
                            <th class="text-center">Eklenen</th>
                            <th class="text-center">Silinen</th>
                            <th class="text-center">Eşit</th>
                            <th class="text-center">Tarih</th>
                            <th class="text-center" style="width:100px">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisons as $row)
                        @php
                            $sim   = $row->similarity;
                            $simColor = $sim >= 80 ? 'success' : ($sim >= 50 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $row->title ?: 'Karşılaştırma #' . $row->id }}
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-file me-1"></i>{{ $row->file_a_name }}
                                    </span>
                                    <i class="fas fa-arrows-alt-h text-muted" style="font-size:.7rem"></i>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-file me-1"></i>{{ $row->file_b_name }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <div class="progress flex-fill" style="height:6px;max-width:60px">
                                        <div class="progress-bar bg-{{ $simColor }}" style="width:{{ $sim }}%"></div>
                                    </div>
                                    <span class="fw-semibold text-{{ $simColor }} small">%{{ $sim }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($row->lines_added > 0)
                                    <span class="badge" style="background:#e8f5e9;color:#10b981">+{{ $row->lines_added }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->lines_removed > 0)
                                    <span class="badge" style="background:#fdecea;color:#dc3545">−{{ $row->lines_removed }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">{{ $row->lines_equal }}</span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $row->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('contracts.show', $row) }}"
                                       class="btn btn-sm btn-outline-primary" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('contracts.destroy', $row) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger deleteBtn" title="Sil">
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
        @if($comparisons->hasPages())
        <div class="card-footer bg-white">{{ $comparisons->links() }}</div>
        @endif
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #f8f9fa;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.upload-zone:hover,
.upload-zone.drag-over {
    border-color: #4361ee;
    background: #eef0ff;
}
.upload-zone.has-file {
    border-color: #10b981;
    background: #f0fbf7;
}
.upload-zone.border-danger {
    border-color: #dc3545 !important;
}
.upload-placeholder {
    pointer-events: none;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
function dragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}
function dragLeave(e, zoneId) {
    document.getElementById(zoneId).classList.remove('drag-over');
}
function dropFile(e, inputId, zoneId, previewId) {
    e.preventDefault();
    document.getElementById(zoneId).classList.remove('drag-over');
    const dt    = e.dataTransfer;
    const input = document.getElementById(inputId);
    if (dt.files.length) {
        const transfer = new DataTransfer();
        transfer.items.add(dt.files[0]);
        input.files = transfer.files;
        previewFile(input, previewId, zoneId);
    }
}
function previewFile(input, previewId, zoneId) {
    const zone    = document.getElementById(zoneId);
    const preview = document.getElementById(previewId);
    if (!input.files.length) return;
    const file  = input.files[0];
    const ext   = file.name.split('.').pop().toLowerCase();
    const icon  = ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
    const size  = file.size > 1024 * 1024
        ? (file.size / 1024 / 1024).toFixed(1) + ' MB'
        : (file.size / 1024).toFixed(0) + ' KB';
    preview.innerHTML = `
        <i class="fas ${icon} fa-2x mb-2 d-block"></i>
        <div class="fw-semibold" style="font-size:.9rem;word-break:break-all">${file.name}</div>
        <div class="text-muted small mt-1">${size}</div>
    `;
    zone.classList.add('has-file');
    zone.classList.remove('drag-over');
}
document.getElementById('compareForm').addEventListener('submit', function () {
    document.getElementById('uploadCard').classList.add('d-none');
    document.getElementById('loadingBar').classList.remove('d-none');
});

// Silme onayı
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Kaydı sil?',
            text: 'Bu işlem geri alınamaz.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'İptal',
            confirmButtonText: 'Evet, sil!'
        }).then(r => { if (r.isConfirmed) this.closest('form').submit(); });
    });
});
</script>
@endpush
