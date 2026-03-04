@extends('layouts.default')

@section('title', 'PDF Birleştirici')

@section('content')
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div>
                    <h2 class="mb-1 font-w700 text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                             fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="me-2" style="vertical-align:middle">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        PDF Birleştirici
                    </h2>
                    <p class="text-muted mb-0">Birden fazla PDF dosyasını sıraya göre tek belgede birleştirin</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Hata:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('pdf-merger.merge') }}" method="POST"
                          enctype="multipart/form-data" id="mergerForm">
                        @csrf

                        {{-- Çıktı adı --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Birleştirilmiş Dosya Adı</label>
                            <div class="input-group">
                                <input type="text" name="output_name" class="form-control"
                                       placeholder="birlestirilmis-belge"
                                       value="{{ old('output_name') }}">
                                <span class="input-group-text text-muted">.pdf</span>
                            </div>
                            <div class="form-text">Boş bırakılırsa otomatik isim oluşturulur.</div>
                        </div>

                        {{-- Dosya sürükle-bırak alanı --}}
                        <label class="form-label fw-semibold">PDF Dosyaları <span class="text-danger">*</span></label>
                        <div class="upload-zone-multi" id="dropZone">
                            <input type="file" id="pdfFiles" name="pdf_files[]"
                                   class="upload-input-multi" accept=".pdf" multiple>
                            <div id="dropPlaceholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24"
                                     fill="none" stroke="#e74c3c" stroke-width="1.5" stroke-linecap="round"
                                     stroke-linejoin="round" class="mb-2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <p class="mb-1 fw-semibold">PDF dosyalarını buraya sürükleyin</p>
                                <p class="text-muted small mb-2">veya seçmek için tıklayın</p>
                                <span class="badge bg-light text-secondary px-3 py-2" style="font-size:.78rem">
                                    Min. 2 · Maks. 20 dosya · Her dosya maks. 50 MB
                                </span>
                            </div>
                        </div>

                        {{-- Dosya listesi + sıralama --}}
                        <div id="fileListWrapper" class="d-none mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-semibold text-muted small text-uppercase" style="letter-spacing:.5px">
                                    Sıra &nbsp;·&nbsp; <span id="fileCount">0</span> dosya seçildi
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFiles">
                                    Temizle
                                </button>
                            </div>
                            <ul class="list-group" id="fileList"></ul>
                            <p class="form-text mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="me-1">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                Sıralamayı değiştirmek için satırları yukarı/aşağı ok butonlarıyla taşıyın.
                            </p>
                        </div>

                        {{-- Birleştir butonu --}}
                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-danger btn-lg fw-bold" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="me-2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                <span id="submitText">PDF Dosyalarını Birleştir</span>
                            </button>
                        </div>

                        <div class="mt-3 d-none" id="progressDiv">
                            <div class="progress" style="height:8px">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                                     style="width:100%"></div>
                            </div>
                            <p class="text-muted text-center small mt-2">Birleştiriliyor, lütfen bekleyin...</p>
                        </div>

                    </form>
                </div>
            </div>

            {{-- Bilgi --}}
            <div class="card border-0 bg-light mt-3">
                <div class="card-body px-4 py-3">
                    <h6 class="fw-bold mb-2 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" class="me-1">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Önemli Notlar
                    </h6>
                    <ul class="mb-0 small text-muted ps-3">
                        <li>Dosyalar listede göründüğü sırayla birleştirilir; sıralamayı ok butonlarıyla değiştirebilirsiniz.</li>
                        <li>Birleştirme, PDF 1.4 ve önceki sürümleri tam destekler. Bazı PDF 1.5+ dosyaları (sıkıştırılmış referans tablosu) birleştirilirken hata verebilir.</li>
                        <li>Şifreli veya kısıtlı PDF'ler birleştirilemez.</li>
                        <li>Yüklenen dosyalar sunucuda saklanmaz.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.upload-zone-multi {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 36px 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #fafafa;
}
.upload-zone-multi:hover,
.upload-zone-multi.drag-over { border-color: #e74c3c; background: #fff5f5; }
.upload-input-multi {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.file-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; }
.file-item .file-order {
    width: 26px; height: 26px; border-radius: 50%;
    background: #e74c3c; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; flex-shrink: 0;
}
.file-item .file-name { flex: 1; font-size: .9rem; word-break: break-all; }
.file-item .file-size { font-size: .78rem; color: #aaa; white-space: nowrap; }
.file-item .btn-move { padding: 2px 6px; line-height: 1; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone    = document.getElementById('dropZone');
    const input       = document.getElementById('pdfFiles');
    const placeholder = document.getElementById('dropPlaceholder');
    const listWrapper = document.getElementById('fileListWrapper');
    const fileList    = document.getElementById('fileList');
    const fileCount   = document.getElementById('fileCount');
    const clearBtn    = document.getElementById('clearFiles');
    const form        = document.getElementById('mergerForm');
    const submitBtn   = document.getElementById('submitBtn');
    const submitTxt   = document.getElementById('submitText');
    const progressDiv = document.getElementById('progressDiv');

    let files = []; // {file, name, size}

    function formatBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
        return (b/1048576).toFixed(1) + ' MB';
    }

    function renderList() {
        fileList.innerHTML = '';
        fileCount.textContent = files.length;
        files.forEach((f, i) => {
            const li = document.createElement('li');
            li.className = 'list-group-item p-0';
            li.innerHTML = `
                <div class="file-item">
                    <div class="file-order">${i + 1}</div>
                    <div class="file-name">${f.name}</div>
                    <div class="file-size">${formatBytes(f.size)}</div>
                    <div class="d-flex gap-1">
                        ${i > 0 ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-move" data-action="up" data-idx="${i}">↑</button>` : ''}
                        ${i < files.length - 1 ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-move" data-action="down" data-idx="${i}">↓</button>` : ''}
                        <button type="button" class="btn btn-sm btn-outline-danger btn-move" data-action="remove" data-idx="${i}">✕</button>
                    </div>
                </div>`;
            fileList.appendChild(li);
        });

        listWrapper.classList.toggle('d-none', files.length === 0);
        placeholder.style.display = files.length === 0 ? '' : 'none';

        // hidden input'ları yenile
        syncInputFiles();
    }

    function syncInputFiles() {
        // DataTransfer ile input.files güncellenemez doğrudan
        // Form submit sırasında files dizisini FormData'ya ekliyoruz
        form.querySelectorAll('input[name="pdf_order[]"]').forEach(e => e.remove());
        files.forEach((f, i) => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'pdf_order[]';
            inp.value = i;
            form.appendChild(inp);
        });
    }

    function addFiles(newFiles) {
        Array.from(newFiles).forEach(f => {
            if (f.type === 'application/pdf') files.push({file: f, name: f.name, size: f.size});
        });
        if (files.length > 20) files = files.slice(0, 20);
        renderList();
    }

    input.addEventListener('change', function () {
        addFiles(this.files);
        this.value = ''; // sıfırla — tekrar aynı dosya seçilebilsin
    });

    ['dragenter', 'dragover'].forEach(ev => {
        dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    });
    ['dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
    });
    dropZone.addEventListener('drop', e => {
        addFiles(e.dataTransfer.files);
    });

    fileList.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const idx    = parseInt(btn.dataset.idx);
        const action = btn.dataset.action;
        if (action === 'up' && idx > 0) {
            [files[idx - 1], files[idx]] = [files[idx], files[idx - 1]];
        } else if (action === 'down' && idx < files.length - 1) {
            [files[idx], files[idx + 1]] = [files[idx + 1], files[idx]];
        } else if (action === 'remove') {
            files.splice(idx, 1);
        }
        renderList();
    });

    clearBtn.addEventListener('click', () => {
        files = [];
        renderList();
    });

    // Form submit: hidden input dosya gönderimi yerine FormData ile gönder
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (files.length < 2) {
            alert('Lütfen en az 2 PDF dosyası seçin.');
            return;
        }

        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        const outputName = document.querySelector('input[name="output_name"]').value;
        if (outputName) formData.append('output_name', outputName);
        files.forEach(f => formData.append('pdf_files[]', f.file, f.name));

        submitBtn.disabled = true;
        submitTxt.textContent = 'Birleştiriliyor...';
        progressDiv.classList.remove('d-none');

        fetch(form.action, { method: 'POST', body: formData })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(html => {
                        // Laravel validation error HTML içinden mesajı çıkar
                        const parser = new DOMParser();
                        const doc    = parser.parseFromString(html, 'text/html');
                        const err    = doc.querySelector('.invalid-feedback, .alert-danger, .text-danger');
                        throw new Error(err ? err.textContent.trim() : 'Sunucu hatası');
                    });
                }
                const disposition = res.headers.get('Content-Disposition') || '';
                const match       = disposition.match(/filename="?([^";\n]+)"?/);
                const filename    = match ? match[1] : 'birlestirilmis.pdf';
                return res.blob().then(blob => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                const url = URL.createObjectURL(blob);
                const a   = document.createElement('a');
                a.href    = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 1000);
            })
            .catch(err => {
                alert('Hata: ' + err.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitTxt.textContent = 'PDF Dosyalarını Birleştir';
                progressDiv.classList.add('d-none');
            });
    });
});
</script>
@endpush
