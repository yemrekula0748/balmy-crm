@extends('layouts.default')

@section('title', 'Bilgisayarlar')

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Bilgisayarlar</h4>
                <span>Bilgi İşlem — Cihaz Envanteri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Bilgi İşlem</span></li>
                <li class="breadcrumb-item active">Bilgisayarlar</li>
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

    {{-- Header + Ekle Butonu --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <div>
            <h5 class="mb-0 fw-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="#4361ee" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" class="me-2" style="vertical-align:-3px">
                    <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                Cihaz Listesi
                <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size:13px">{{ $computers->count() }}</span>
            </h5>
        </div>
        @if(auth()->user()->hasPermission('it_computers', 'create'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addComputerModal">
            <i class="fas fa-plus me-1"></i> Yeni Bilgisayar Ekle
        </button>
        @endif
    </div>

    {{-- Arama + Filtre --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                               placeholder="Ad, IP, konum veya kullanıcı ara...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="branchFilter" class="form-select form-select-sm">
                        <option value="">Tüm Şubeler</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->name }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 ms-auto text-end">
                    <span class="text-muted" style="font-size:13px" id="resultCount">{{ $computers->count() }} kayıt</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="computersTable">
                    <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff);">
                        <tr>
                            <th class="ps-4 py-3" style="font-size:12px;font-weight:700;color:#555;width:40px">#</th>
                            <th style="font-size:12px;font-weight:700;color:#555">BİLGİSAYAR ADI</th>
                            <th style="font-size:12px;font-weight:700;color:#555">IP ADRESİ</th>
                            <th style="font-size:12px;font-weight:700;color:#555">KONUM</th>
                            <th style="font-size:12px;font-weight:700;color:#555">KULLANICI</th>
                            <th style="font-size:12px;font-weight:700;color:#555">ŞUBE</th>
                            <th style="font-size:12px;font-weight:700;color:#555">ÖZELLİKLER</th>
                            <th class="text-center pe-4" style="font-size:12px;font-weight:700;color:#555">İŞLEM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($computers as $i => $pc)
                        <tr class="computer-row"
                            data-name="{{ strtolower($pc->name) }}"
                            data-ip="{{ strtolower($pc->ip_address ?? '') }}"
                            data-location="{{ strtolower($pc->location ?? '') }}"
                            data-user="{{ strtolower($pc->assigned_user ?? '') }}"
                            data-branch="{{ $pc->branch?->name ?? '' }}">
                            <td class="ps-4 text-muted" style="font-size:13px">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4361ee,#7b8cde);
                                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                             fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                            <line x1="8" y1="21" x2="16" y2="21"></line>
                                            <line x1="12" y1="17" x2="12" y2="21"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:14px">{{ $pc->name }}</div>
                                        @if($pc->notes)
                                        <div class="text-muted" style="font-size:11px">{{ Str::limit($pc->notes, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($pc->ip_address)
                                <span class="badge rounded-pill px-3 py-1"
                                      style="background:rgba(67,97,238,.08);color:#4361ee;font-size:12px;font-family:monospace;">
                                    {{ $pc->ip_address }}
                                </span>
                                @else
                                <span class="text-muted" style="font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pc->location)
                                <div class="d-flex align-items-center gap-1">
                                    <i class="fas fa-map-marker-alt text-muted" style="font-size:11px"></i>
                                    <span style="font-size:13px">{{ $pc->location }}</span>
                                </div>
                                @else
                                <span class="text-muted" style="font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pc->assigned_user)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#10b981,#6ee7b7);
                                                display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0;">
                                        {{ strtoupper(mb_substr($pc->assigned_user, 0, 1)) }}
                                    </div>
                                    <span style="font-size:13px">{{ $pc->assigned_user }}</span>
                                </div>
                                @else
                                <span class="text-muted" style="font-size:12px">Atanmamış</span>
                                @endif
                            </td>
                            <td>
                                @if($pc->branch)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:12px">
                                    {{ $pc->branch->name }}
                                </span>
                                @else
                                <span class="text-muted" style="font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pc->specs)
                                <span class="text-muted" style="font-size:12px">{{ Str::limit($pc->specs, 40) }}</span>
                                @else
                                <span class="text-muted" style="font-size:12px">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-1">
                                    @if(auth()->user()->hasPermission('it_computers', 'edit'))
                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                            style="border-radius:8px;font-size:12px;padding:3px 10px"
                                            data-bs-toggle="modal" data-bs-target="#editComputerModal"
                                            data-id="{{ $pc->id }}"
                                            data-name="{{ $pc->name }}"
                                            data-ip="{{ $pc->ip_address }}"
                                            data-location="{{ $pc->location }}"
                                            data-user="{{ $pc->assigned_user }}"
                                            data-specs="{{ $pc->specs }}"
                                            data-notes="{{ $pc->notes }}"
                                            data-branch="{{ $pc->branch_id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('it_computers', 'delete'))
                                    <form action="{{ route('it.computers.destroy', $pc) }}" method="POST"
                                          onsubmit="return confirm('Bu bilgisayarı silmek istediğinizden emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                style="border-radius:8px;font-size:12px;padding:3px 10px">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                         fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round"
                                         stroke-linejoin="round" class="mb-2">
                                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                    </svg>
                                    <p class="mt-2 mb-0" style="font-size:14px">Henüz bilgisayar eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ADD MODAL --}}
@if(auth()->user()->hasPermission('it_computers', 'create'))
<div class="modal fade" id="addComputerModal" tabindex="-1" aria-labelledby="addComputerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#4361ee,#7b8cde);">
                <h5 class="modal-title text-white fw-bold" id="addComputerModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Yeni Bilgisayar Ekle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('it.computers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Bilgisayar Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="örn. PC-RESEPSIYON-01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">IP Adresi</label>
                            <input type="text" name="ip_address" class="form-control" placeholder="örn. 192.168.1.100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Nerede Bulunuyor?</label>
                            <input type="text" name="location" class="form-control" placeholder="örn. Resepsiyon masası, 1. kat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Kimin Kullandığı</label>
                            <input type="text" name="assigned_user" class="form-control" placeholder="örn. Ahmet Yılmaz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Şube</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Şube Seçin</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Teknik Özellikler</label>
                            <input type="text" name="specs" class="form-control" placeholder="örn. i5-10400, 16GB RAM, 512GB SSD">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px">Notlar</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Ek notlar..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- EDIT MODAL --}}
@if(auth()->user()->hasPermission('it_computers', 'edit'))
<div class="modal fade" id="editComputerModal" tabindex="-1" aria-labelledby="editComputerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#f97316,#fdba74);">
                <h5 class="modal-title text-white fw-bold" id="editComputerModalLabel">
                    <i class="fas fa-edit me-2"></i>Bilgisayar Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Bilgisayar Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">IP Adresi</label>
                            <input type="text" name="ip_address" id="edit_ip" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Nerede Bulunuyor?</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Kimin Kullandığı</label>
                            <input type="text" name="assigned_user" id="edit_user" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Şube</label>
                            <select name="branch_id" id="edit_branch" class="form-select">
                                <option value="">Şube Seçin</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px">Teknik Özellikler</label>
                            <input type="text" name="specs" id="edit_specs" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px">Notlar</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// ── Arama / Filtre ──────────────────────────────────────────────────────────
const searchInput  = document.getElementById('searchInput');
const branchFilter = document.getElementById('branchFilter');
const rows         = document.querySelectorAll('.computer-row');
const resultCount  = document.getElementById('resultCount');

function filterRows() {
    const q      = searchInput.value.toLowerCase().trim();
    const branch = branchFilter.value;
    let visible  = 0;
    rows.forEach(row => {
        const matchQ = !q ||
            row.dataset.name.includes(q) ||
            row.dataset.ip.includes(q) ||
            row.dataset.location.includes(q) ||
            row.dataset.user.includes(q);
        const matchB = !branch || row.dataset.branch === branch;
        const show   = matchQ && matchB;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    resultCount.textContent = visible + ' kayıt';
}

searchInput.addEventListener('input', filterRows);
branchFilter.addEventListener('change', filterRows);

// ── Edit Modal ──────────────────────────────────────────────────────────────
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('editForm').action = '/bilgi-islem/bilgisayarlar/' + id;
        document.getElementById('edit_name').value     = this.dataset.name     || '';
        document.getElementById('edit_ip').value       = this.dataset.ip       || '';
        document.getElementById('edit_location').value = this.dataset.location || '';
        document.getElementById('edit_user').value     = this.dataset.user     || '';
        document.getElementById('edit_specs').value    = this.dataset.specs    || '';
        document.getElementById('edit_notes').value    = this.dataset.notes    || '';
        const branchSel = document.getElementById('edit_branch');
        branchSel.value = this.dataset.branch || '';
    });
});
</script>
@endpush

@endsection
