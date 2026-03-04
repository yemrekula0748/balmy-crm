@extends('layouts.default')

@section('title', 'PDF → Word Çevirici')

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-1 font-w700 text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="me-2" style="vertical-align:middle">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        PDF → Word Çevirici
                    </h2>
                    <p class="text-muted mb-0">PDF dosyalarınızı düzenlenebilir Word (.docx) formatına dönüştürün</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">

            {{-- LibreOffice kurulu değil uyarısı --}}
            @if(!($isConfigured ?? false))
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <div class="d-flex align-items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="#f39c12" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" style="flex-shrink:0;margin-top:2px">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <div>
                        <strong>LibreOffice kurulu değil veya yapılandırılmamış</strong>
                        <p class="mb-2 mt-1 small">Bu modül PDF → Word dönüşümü için <strong>LibreOffice</strong>'i kullanır. Kurulum adımları:</p>
                        <ol class="small mb-2">
                            <li><a href="https://www.libreoffice.org/download/libreoffice/" target="_blank" class="fw-semibold">libreoffice.org</a>'dan ücretsiz indirin ve kurun.</li>
                            <li><code>.env</code> dosyasında yolu ayarlayın:<br>
                                <code class="bg-white px-2 py-1 rounded d-inline-block mt-1">LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"</code>
                            </li>
                            <li><code>php artisan config:clear</code> komutunu çalıştırın.</li>
                        </ol>
                        @if($libreOfficePath ?? false)
                        <p class="mb-0 small text-danger">Mevcut yol: <code>{{ $libreOfficePath }}</code> — Dosya bulunamadı.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Ana Kart --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    {{-- Upload Alanı --}}
                    <form action="{{ route('pdf-converter.convert') }}" method="POST"
                          enctype="multipart/form-data" id="converterForm">
                        @csrf

                        <div class="upload-zone" id="uploadZone">
                            <input type="file" id="pdf_file" name="pdf_file" accept=".pdf"
                                   class="upload-input" required>
                            <div class="upload-content" id="uploadContent">
                                <div class="upload-icon mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52"
                                         viewBox="0 0 24 24" fill="none" stroke="#e74c3c"
                                         stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <path d="M12 12v6"/><path d="M9 15l3-3 3 3"/>
                                    </svg>
                                </div>
                                <h5 class="mb-1 fw-bold">PDF dosyasını buraya sürükleyin</h5>
                                <p class="text-muted mb-3">veya dosya seçmek için tıklayın</p>
                                <span class="badge bg-light text-secondary px-3 py-2"
                                      style="font-size:0.8rem;">Maks. 20 MB · Yalnızca .pdf</span>
                            </div>
                            <div class="upload-preview d-none" id="uploadPreview">
                                <div class="file-icon mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                         viewBox="0 0 24 24" fill="none" stroke="#27ae60"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                                <p class="mb-1 fw-bold text-success" id="fileName">dosya.pdf</p>
                                <p class="text-muted small mb-2" id="fileSize">0 KB</p>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        id="changeFile">Dosyayı Değiştir</button>
                            </div>
                        </div>

                        {{-- Dönüştür Butonu --}}
                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-danger btn-lg fw-bold" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="me-2">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                </svg>
                                <span id="submitText">PDF'i Word'e Dönüştür</span>
                            </button>
                        </div>

                        {{-- Progress --}}
                        <div class="mt-3 d-none" id="progressDiv">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                                     role="progressbar" style="width:100%"></div>
                            </div>
                            <p class="text-muted text-center small mt-2">
                                PDF dönüştürülüyor, lütfen bekleyin...
                            </p>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Bilgi Kartı --}}
            <div class="card border-0 bg-light mt-3">
                <div class="card-body px-4 py-3">
                    <h6 class="fw-bold mb-2 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="me-1">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Nasıl çalışır?
                    </h6>
                    <ul class="mb-0 small text-muted ps-3">
                        <li>PDF dosyasındaki metin içeriği ayıklanır ve Word belgesine aktarılır.</li>
                        <li>Büyük harfli kısa satırlar <strong>başlık</strong> olarak biçimlendirilir.</li>
                        <li>Her PDF sayfası ayrı bir bölüm olarak oluşturulur.</li>
                        <li>Karmaşık grafikler, tablolar veya özel fontlar tam olarak korunamayabilir.</li>
                        <li>Belge sunucuda saklanmaz; indirme sonrası silinir.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.upload-zone {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 48px 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    background: #fafafa;
}
.upload-zone:hover,
.upload-zone.drag-over {
    border-color: #e74c3c;
    background: #fff5f5;
}
.upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const zone      = document.getElementById('uploadZone');
    const input     = document.getElementById('pdf_file');
    const content   = document.getElementById('uploadContent');
    const preview   = document.getElementById('uploadPreview');
    const fileName  = document.getElementById('fileName');
    const fileSize  = document.getElementById('fileSize');
    const changeBtn = document.getElementById('changeFile');
    const form      = document.getElementById('converterForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitTxt = document.getElementById('submitText');
    const progressDiv = document.getElementById('progressDiv');

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function showPreview(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        content.classList.add('d-none');
        preview.classList.remove('d-none');
        zone.style.borderColor = '#27ae60';
        zone.style.background  = '#f0fff8';
    }

    input.addEventListener('change', function () {
        if (this.files.length) showPreview(this.files[0]);
    });

    changeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        input.value = '';
        content.classList.remove('d-none');
        preview.classList.add('d-none');
        zone.style.borderColor = '';
        zone.style.background  = '';
    });

    // Drag & drop
    ['dragenter','dragover'].forEach(ev => {
        zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(ev => {
        zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag-over'); });
    });
    zone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        if (dt.files.length) {
            const file = dt.files[0];
            if (file.type !== 'application/pdf') {
                alert('Yalnızca PDF dosyası yükleyebilirsiniz.');
                return;
            }
            // Assign to input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            showPreview(file);
        }
    });

    // Submit
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitTxt.textContent = 'Dönüştürülüyor...';
        progressDiv.classList.remove('d-none');
    });
});
</script>
@endpush
