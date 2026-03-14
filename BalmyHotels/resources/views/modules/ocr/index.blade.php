@extends('layouts.default')

@section('title', 'Yazıya Çevir — OCR')

@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-1 font-w700 text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="#c19b77" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="me-2" style="vertical-align:middle">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Yazıya Çevir (OCR)
                    </h2>
                    <p class="text-muted mb-0">Taranmış PDF ve görüntü dosyalarındaki metni tanıyın</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tesseract kurulu değil uyarısı ──────────────────────────── --}}
    @if(!($tesseractOk ?? false))
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="#f39c12" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" style="flex-shrink:0;margin-top:2px">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div>
                <strong>Tesseract OCR kurulu değil veya yapılandırılmamış</strong>
                <p class="mb-2 mt-1 small">Bu modül metin tanıma için <strong>Tesseract OCR</strong> gerektirir. Kurulum adımları:</p>
                <ol class="small mb-2">
                    <li><a href="https://github.com/UB-Mannheim/tesseract/wiki" target="_blank" class="fw-semibold">github.com/UB-Mannheim/tesseract/wiki</a>'den Windows kurucusunu indirin ve kurun.</li>
                    <li>Kurulum sırasında <strong>Türkçe (tur)</strong> dil paketini seçin.</li>
                    <li><code>.env</code> dosyasına yolu ekleyin:<br>
                        <code class="bg-white px-2 py-1 rounded d-inline-block mt-1">TESSERACT_PATH="C:\Program Files\Tesseract-OCR\tesseract.exe"</code>
                    </li>
                    <li><code>php artisan config:clear</code> komutunu çalıştırın.</li>
                </ol>
                @if($tesseractPath ?? false)
                <p class="mb-0 small text-danger">Mevcut yol: <code>{{ $tesseractPath }}</code> — Dosya bulunamadı.</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ── pdftoppm uyarısı (PDF için) ─────────────────────────────── --}}
    @if(($tesseractOk ?? false) && !($pdftoppmOk ?? false))
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="#17a2b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="flex-shrink:0;margin-top:2px">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div class="small">
                <strong>PDF desteği için pdftoppm (Poppler) gerekli</strong> — Görüntü dosyaları (JPG, PNG, TIFF…) şu anda çalışıyor. PDF yüklemek için:
                <ol class="mb-1 mt-1">
                    <li><strong>Windows:</strong> <a href="https://github.com/oschwartz10612/poppler-windows/releases" target="_blank" class="fw-semibold">Poppler Windows</a> sayfasından son sürümü indirin, bir klasöre çıkartın (örn. <code>C:\poppler</code>).</li>
                    <li><code>.env</code> dosyasına ekleyin:<br>
                        <code class="bg-white px-2 py-1 rounded d-inline-block mt-1">PDFTOPPM_PATH="C:\poppler\Library\bin\pdftoppm.exe"</code>
                    </li>
                    <li><code>php artisan config:clear</code></li>
                </ol>
                <p class="mb-0 text-muted">Linux: <code>sudo apt install poppler-utils</code> &nbsp;→&nbsp; <code>PDFTOPPM_PATH=/usr/bin/pdftoppm</code></p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Hata mesajı ─────────────────────────────────────────────── --}}
    @if(!empty($ocr_error))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-times-circle me-2"></i>{{ $ocr_error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- ── Sol sütun: Yükleme formu ─────────────────────────────── --}}
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form action="{{ route('ocr.extract') }}" method="POST"
                          enctype="multipart/form-data" id="ocrForm">
                        @csrf

                        {{-- Drag & drop alanı --}}
                        <div id="dropZone"
                             class="border rounded-3 p-4 text-center mb-4 position-relative"
                             style="border-style:dashed !important;border-color:#c19b77 !important;background:#fffaf5;cursor:pointer;transition:.2s">
                            <input type="file" id="ocrFile" name="file"
                                   accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.tif,.webp"
                                   class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                   style="cursor:pointer" required>
                            <div id="dropIcon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                     fill="none" stroke="#c19b77" stroke-width="1.5" stroke-linecap="round"
                                     stroke-linejoin="round" class="mb-2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <p class="mb-0 fw-semibold" style="color:#c19b77">Dosyayı buraya sürükleyin</p>
                                <p class="text-muted small mb-0">veya tıklayarak seçin</p>
                            </div>
                            <div id="dropSelected" class="d-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                     fill="none" stroke="#5c9e6e" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="mb-1">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <p id="dropFileName" class="mb-0 fw-semibold text-success small"></p>
                                <p id="dropFileSize" class="text-muted small mb-0"></p>
                            </div>
                        </div>

                        {{-- Desteklenen formatlar --}}
                        <p class="text-muted small text-center mb-3">
                            Desteklenen: <span class="fw-semibold">PDF, JPG, PNG, TIFF, BMP, GIF, WEBP</span>
                            &nbsp;·&nbsp; Maks. <strong>50 MB</strong>
                        </p>

                        {{-- Dil seçimi --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">OCR Dili</label>
                            <div class="d-flex gap-3 flex-wrap">
                                @php $selLang = $selected_lang ?? 'tur+eng'; @endphp
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lang" id="lang_both" value="tur+eng" {{ $selLang === 'tur+eng' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lang_both">Türkçe + İngilizce <span class="badge bg-secondary ms-1">Önerilen</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lang" id="lang_tur" value="tur" {{ $selLang === 'tur' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lang_tur">Yalnızca Türkçe</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lang" id="lang_eng" value="eng" {{ $selLang === 'eng' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lang_eng">Yalnızca İngilizce</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="ocrSubmitBtn" class="btn w-100 text-white fw-semibold" style="background:#c19b77">
                            <span id="ocrBtnText"><i class="fa fa-search me-2"></i>Metni Tanı</span>
                            <span id="ocrBtnLoader" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                İşleniyor...
                            </span>
                        </button>
                    </form>

                    {{-- Durum ikonları --}}
                    <div class="d-flex gap-3 justify-content-center mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center gap-1 small">
                            @if($tesseractOk ?? false)
                            <span class="text-success"><i class="fa fa-check-circle"></i> Tesseract</span>
                            @else
                            <span class="text-danger"><i class="fa fa-times-circle"></i> Tesseract</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 small">
                            @if($pdftoppmOk ?? false)
                            <span class="text-success"><i class="fa fa-check-circle"></i> pdftoppm (PDF)</span>
                            @else
                            <span class="text-muted"><i class="fa fa-circle-o"></i> pdftoppm (PDF)</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Sağ sütun: Sonuç ────────────────────────────────────── --}}
        <div class="col-xl-7 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#fffaf5">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0">Tanınan Metin</h6>
                        @if(!empty($ocr_result))
                        <span class="badge bg-success">
                            {{ isset($page_count) && $page_count > 1 ? $page_count . ' sayfa' : 'Tamamlandı' }}
                        </span>
                        @if(isset($original_name))
                        <span class="badge bg-light text-dark border small">{{ $original_name }}</span>
                        @endif
                        @endif
                    </div>
                    @if(!empty($ocr_result))
                    <div class="d-flex gap-2">
                        <button type="button" id="copyBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-copy me-1"></i>Kopyala
                        </button>
                        <button type="button" id="downloadBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-download me-1"></i>İndir
                        </button>
                        <button type="button" id="clearBtn" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash me-1"></i>Temizle
                        </button>
                    </div>
                    @endif
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    @if(empty($ocr_result))
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 text-muted flex-grow-1" style="min-height:300px">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                             stroke-linejoin="round" class="mb-3 opacity-25">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                            <line x1="3" y1="15" x2="21" y2="15"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                            <line x1="15" y1="3" x2="15" y2="21"/>
                        </svg>
                        <p class="mb-0">Bir dosya yükleyin ve <strong>Metni Tanı</strong> butonuna tıklayın.</p>
                        <p class="small">Sonuçlar burada görüntülenecek.</p>
                    </div>
                    @else
                    <div class="p-2 bg-light border-bottom d-flex justify-content-between align-items-center small text-muted">
                        <span id="charCountLabel">
                            <i class="fa fa-font me-1"></i>
                            <span id="charCount">{{ mb_strlen($ocr_result) }}</span> karakter
                            &nbsp;·&nbsp;
                            <span id="wordCount">{{ str_word_count(mb_convert_encoding($ocr_result, 'UTF-8', 'UTF-8')) }}</span> kelime
                        </span>
                        <span class="text-muted fst-italic" style="font-size:.75rem">Düzenlenebilir alan</span>
                    </div>
                    <textarea id="ocrResult" class="form-control border-0 rounded-0 font-monospace"
                              style="min-height:520px;resize:vertical;font-size:.82rem;line-height:1.6;padding:1.25rem"
                              spellcheck="false">{{ $ocr_result }}</textarea>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {

    // ── Drag & drop görsel geri bildirim ──────────────────────────────
    const zone  = document.getElementById('dropZone');
    const input = document.getElementById('ocrFile');
    const icon  = document.getElementById('dropIcon');
    const sel   = document.getElementById('dropSelected');

    function showSelected(file) {
        if (!file) return;
        icon.classList.add('d-none');
        sel.classList.remove('d-none');
        document.getElementById('dropFileName').textContent = file.name;
        const kb = (file.size / 1024).toFixed(1);
        const mb = (file.size / 1024 / 1024).toFixed(2);
        document.getElementById('dropFileSize').textContent = kb < 1024 ? kb + ' KB' : mb + ' MB';
    }

    if (input) {
        input.addEventListener('change', () => showSelected(input.files[0]));
    }

    if (zone) {
        ['dragover', 'dragenter'].forEach(ev => {
            zone.addEventListener(ev, e => {
                e.preventDefault();
                zone.style.background = '#fff0e0';
                zone.style.borderColor = '#e68a3c';
            });
        });
        ['dragleave', 'drop'].forEach(ev => {
            zone.addEventListener(ev, e => {
                e.preventDefault();
                zone.style.background = '#fffaf5';
                zone.style.borderColor = '#c19b77';
                if (ev === 'drop' && e.dataTransfer.files[0]) {
                    // DataTransfer dosyasını input'a atayamayız doğrudan,
                    // bu yüzden formu programatik olarak submit edebiliriz
                    const dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    input.files = dt.files;
                    showSelected(e.dataTransfer.files[0]);
                }
            });
        });
    }

    // ── Form gönderilirken loader ─────────────────────────────────────
    const form = document.getElementById('ocrForm');
    if (form) {
        form.addEventListener('submit', function () {
            document.getElementById('ocrBtnText').classList.add('d-none');
            document.getElementById('ocrBtnLoader').classList.remove('d-none');
            document.getElementById('ocrSubmitBtn').disabled = true;
        });
    }

    // ── Canlı karakter / kelime sayacı ───────────────────────────────
    const ta = document.getElementById('ocrResult');
    if (ta) {
        ta.addEventListener('input', function () {
            const text = ta.value;
            document.getElementById('charCount').textContent = text.length;
            const words = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            document.getElementById('wordCount').textContent = words;
        });
    }

    // ── Kopyala ───────────────────────────────────────────────────────
    const copyBtn = document.getElementById('copyBtn');
    if (copyBtn && ta) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(ta.value).then(() => {
                const orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fa fa-check me-1"></i>Kopyalandı!';
                copyBtn.classList.replace('btn-outline-secondary', 'btn-success');
                setTimeout(() => {
                    copyBtn.innerHTML = orig;
                    copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
                }, 2000);
            });
        });
    }

    // ── İndir (.txt) ─────────────────────────────────────────────────
    const dlBtn = document.getElementById('downloadBtn');
    if (dlBtn && ta) {
        dlBtn.addEventListener('click', function () {
            const blob = new Blob([ta.value], {type: 'text/plain;charset=utf-8'});
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = 'ocr_sonuc_' + Date.now() + '.txt';
            a.click();
            URL.revokeObjectURL(url);
        });
    }

    // ── Temizle ───────────────────────────────────────────────────────
    const clearBtn = document.getElementById('clearBtn');
    if (clearBtn && ta) {
        clearBtn.addEventListener('click', function () {
            if (confirm('Sonucu temizlemek istediğinize emin misiniz?')) {
                ta.value = '';
                document.getElementById('charCount').textContent = '0';
                document.getElementById('wordCount').textContent = '0';
            }
        });
    }

})();
</script>
@endpush
