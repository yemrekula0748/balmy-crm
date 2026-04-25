@extends('layouts.default')

@section('title', 'Veritabanı Yedekleme')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Veritabanı Yedekleme</h4>
                <span>Bilgi İşlem — DB Yedek Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Bilgi İşlem</span></li>
                <li class="breadcrumb-item active">Yedekleme</li>
            </ol>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Sol: Yedek Oluştur --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3" style="background:linear-gradient(135deg,#4361ee,#7b8cde);">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                        <i class="fas fa-database"></i> Yeni Yedek Oluştur
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('it.backup.run') }}" method="POST" id="backupForm">
                        @csrf

                        {{-- Yedek Tipi --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size:13px">Yedekleme Türü</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="backup-type-card d-block cursor-pointer" for="typeAll">
                                        <input type="radio" name="backup_type" value="full" id="typeAll"
                                               class="backup-type-radio d-none" checked>
                                        <div class="backup-type-box p-3 rounded-3 border-2 text-center selected-type"
                                             style="border:2px solid #4361ee;background:rgba(67,97,238,.06);">
                                            <i class="fas fa-database fa-2x mb-2" style="color:#4361ee"></i>
                                            <div class="fw-semibold" style="font-size:13px">Tam Veritabanı</div>
                                            <div class="text-muted" style="font-size:11px">Tüm tablolar</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="backup-type-card d-block cursor-pointer" for="typeSelect">
                                        <input type="radio" name="backup_type" value="tables" id="typeSelect"
                                               class="backup-type-radio d-none">
                                        <div class="backup-type-box p-3 rounded-3 border-2 text-center"
                                             style="border:2px solid #dee2e6;background:#f8f9fa;">
                                            <i class="fas fa-table fa-2x mb-2 text-muted"></i>
                                            <div class="fw-semibold" style="font-size:13px">Seçili Tablolar</div>
                                            <div class="text-muted" style="font-size:11px">Belirli tablolar</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Tablo Seçimi --}}
                        <div id="tableSelectSection" class="d-none mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center justify-content-between" style="font-size:13px">
                                <span>Tablolar</span>
                                <span>
                                    <a href="#" id="selectAll" class="text-primary text-decoration-none me-2" style="font-size:12px">Tümünü Seç</a>
                                    <a href="#" id="deselectAll" class="text-muted text-decoration-none" style="font-size:12px">Temizle</a>
                                </span>
                            </label>
                            <div class="border rounded-3 p-2" style="max-height:280px;overflow-y:auto;background:#fafafa;">
                                @foreach($tables as $table)
                                <div class="form-check py-1 px-3">
                                    <input class="form-check-input table-checkbox" type="checkbox"
                                           name="tables[]" value="{{ $table }}" id="tbl_{{ $loop->index }}">
                                    <label class="form-check-label" for="tbl_{{ $loop->index }}"
                                           style="font-size:12px;font-family:monospace">{{ $table }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="runBackupBtn">
                                <i class="fas fa-play-circle me-2"></i> Yedeklemeyi Başlat
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Bilgi Kartı --}}
                <div class="card-footer bg-white border-top pt-3 pb-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle text-primary mt-1"></i>
                        <small class="text-muted lh-sm">
                            Yedekler <strong>storage/app/backups/</strong> klasörüne kaydedilir.
                            Yedek dosyaları <strong>.sql</strong> formatındadır; sağ taraftan indirip silebilirsiniz.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sağ: Mevcut Yedekler --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="fas fa-archive text-success"></i> Mevcut Yedekler
                    </h6>
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:12px">
                        {{ count($files) }} dosya
                    </span>
                </div>
                <div class="card-body p-0">
                    @if(empty($files))
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3" style="color:#ddd"></i>
                        <p class="mb-0" style="font-size:14px">Henüz yedek dosyası yok.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="font-size:12px">DOSYA ADI</th>
                                    <th class="text-center" style="font-size:12px">BOYUT</th>
                                    <th class="text-center" style="font-size:12px">TARİH</th>
                                    <th class="text-center pe-4" style="font-size:12px">İŞLEM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $file)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-file-code text-success" style="font-size:18px"></i>
                                            <div>
                                                <div class="fw-semibold" style="font-size:12px;font-family:monospace;word-break:break-all">
                                                    {{ $file['name'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:12px">
                                            {{ $file['size'] }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="font-size:12px;color:#666">
                                        {{ $file['created_at'] }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('it.backup.download', $file['name']) }}"
                                               class="btn btn-sm btn-outline-success"
                                               style="border-radius:8px;font-size:12px;padding:3px 10px"
                                               title="İndir">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('it_backup', 'delete'))
                                            <form action="{{ route('it.backup.delete', $file['name']) }}" method="POST"
                                                  onsubmit="return confirm('Bu yedek dosyasını silmek istediğinizden emin misiniz?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        style="border-radius:8px;font-size:12px;padding:3px 10px"
                                                        title="Sil">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
const typeAll    = document.getElementById('typeAll');
const typeSelect = document.getElementById('typeSelect');
const tableSection = document.getElementById('tableSelectSection');
const cards = document.querySelectorAll('.backup-type-box');

function updateBackupType() {
    if (typeSelect.checked) {
        tableSection.classList.remove('d-none');
        document.querySelectorAll('.backup-type-card')[1].querySelector('.backup-type-box').style.cssText =
            'border:2px solid #4361ee;background:rgba(67,97,238,.06);';
        document.querySelectorAll('.backup-type-card')[0].querySelector('.backup-type-box').style.cssText =
            'border:2px solid #dee2e6;background:#f8f9fa;';
    } else {
        tableSection.classList.add('d-none');
        document.querySelectorAll('.backup-type-card')[0].querySelector('.backup-type-box').style.cssText =
            'border:2px solid #4361ee;background:rgba(67,97,238,.06);';
        document.querySelectorAll('.backup-type-card')[1].querySelector('.backup-type-box').style.cssText =
            'border:2px solid #dee2e6;background:#f8f9fa;';
    }
}

document.querySelectorAll('.backup-type-radio').forEach(r => r.addEventListener('change', updateBackupType));
document.querySelectorAll('.backup-type-card').forEach(card => {
    card.addEventListener('click', function () {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        updateBackupType();
    });
});

document.getElementById('selectAll').addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('.table-checkbox').forEach(c => c.checked = true);
});
document.getElementById('deselectAll').addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('.table-checkbox').forEach(c => c.checked = false);
});

document.getElementById('backupForm').addEventListener('submit', function () {
    const btn = document.getElementById('runBackupBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Yedekleniyor...';
});
</script>
@endpush

@endsection
