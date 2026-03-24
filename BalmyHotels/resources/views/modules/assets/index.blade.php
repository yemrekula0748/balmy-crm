@extends('layouts.default')

@push('styles')
<style>
/* ── Hero ───────────────────────────────────────────────────── */
.page-hero {
    background:#fff;border-radius:16px;margin-bottom:24px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);
    overflow:hidden;display:flex;align-items:stretch;
}
.page-hero-stripe { width:6px;flex-shrink:0;background:linear-gradient(180deg,#c19b77,#a07850);border-radius:16px 0 0 16px; }
.page-hero-icon   { width:68px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:linear-gradient(135deg,#c19b77,#a07850);font-size:1.55rem;color:#fff; }
.page-hero-body   { flex:1;padding:18px 22px;min-width:0; }
.page-hero-body h3 { font-size:1.15rem;font-weight:700;color:#1f2937;margin:0 0 3px;letter-spacing:-.01em; }
.page-hero-body p  { margin:0;font-size:.82rem;color:#6b7280; }
.page-hero-actions { display:flex;align-items:center;gap:10px;padding:0 20px;flex-shrink:0;border-left:1px solid #f3f4f6; }
/* ── Stat chips ─────────────────────────────────────────────── */
.stat-chips { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px; }
.stat-chip { background:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);display:flex;align-items:center;gap:12px;flex:1;min-width:130px; }
.stat-chip .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.stat-chip .sc-num  { font-size:1.45rem;font-weight:700;line-height:1; }
.stat-chip .sc-lbl  { font-size:.78rem;color:#6b7280;margin-top:2px; }
/* ── Filter card ────────────────────────────────────────────── */
.filter-card { background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);margin-bottom:20px; }
.filter-card .form-control,.filter-card .form-select { border:1.5px solid #e5e7eb;border-radius:9px;font-size:.83rem;color:#1f2937;padding:8px 12px;height:auto;transition:border-color .2s,box-shadow .2s;background:#fff; }
.filter-card .form-control:focus,.filter-card .form-select:focus { border-color:#c19b77;box-shadow:0 0 0 3px rgba(193,155,119,.15);outline:none; }
/* ── Assets card ────────────────────────────────────────────── */
.assets-card { background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden; }
.assets-card thead tr { background:#f8f9fb; }
.assets-card thead th { font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;padding:13px 16px;border-bottom:1.5px solid #f3f4f6;white-space:nowrap; }
.assets-card tbody td { padding:12px 16px;vertical-align:middle;border-bottom:1px solid #f9fafb;font-size:.86rem; }
.assets-card tbody tr:last-child td { border-bottom:none; }
.assets-card tbody tr:hover { background:#fffdf9; }
/* ── Asset thumb ────────────────────────────────────────────── */
.a-thumb { width:38px;height:38px;border-radius:9px;overflow:hidden;flex-shrink:0;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#9ca3af; }
.a-thumb img { width:38px;height:38px;object-fit:cover; }
/* ── Code chip ──────────────────────────────────────────────── */
.code-chip { display:inline-block;font-family:monospace;font-size:.78rem;font-weight:700;background:#f3f4f6;color:#374151;border-radius:6px;padding:2px 8px;white-space:nowrap; }
/* ── Status pill ────────────────────────────────────────────── */
.status-pill { display:inline-flex;align-items:center;gap:4px;font-size:.77rem;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
/* ── Action buttons ─────────────────────────────────────────── */
.act-btn { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;border:none;font-size:.78rem;cursor:pointer;transition:all .15s;text-decoration:none; }
.act-btn-show   { background:rgba(16,185,129,.1);color:#059669; }
.act-btn-show:hover { background:#059669;color:#fff; }
.act-btn-qr     { background:rgba(193,155,119,.12);color:#a07850; }
.act-btn-qr:hover { background:#c19b77;color:#fff; }
.act-btn-edit   { background:rgba(67,97,238,.1);color:#4361ee; }
.act-btn-edit:hover { background:#4361ee;color:#fff; }
.act-btn-delete { background:rgba(220,53,69,.1);color:#dc3545; }
.act-btn-delete:hover { background:#dc3545;color:#fff; }
.btn-add { background:linear-gradient(135deg,#c19b77,#a07850);color:#fff;border:none;border-radius:9px;padding:9px 18px;font-size:.84rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;box-shadow:0 3px 10px rgba(193,155,119,.4);transition:opacity .15s;text-decoration:none; }
.btn-add:hover { opacity:.88;color:#fff; }
.empty-state { padding:56px 24px;text-align:center;color:#9ca3af; }
.empty-state .es-icon { font-size:2.5rem;margin-bottom:12px;opacity:.25;display:block; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Demirbaş Yönetimi</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Demirbaşlar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="page-hero">
        <div class="page-hero-stripe"></div>
        <div class="page-hero-icon"><i class="fas fa-boxes"></i></div>
        <div class="page-hero-body">
            <h3>Demirbaş Envanteri</h3>
            <p>Tüm şubelerdeki demirbaş varlıklarını yönetin, takip edin ve QR kodlarını yazdırın</p>
        </div>
        <div class="page-hero-actions">
            <a href="{{ route('assets.create') }}" class="btn-add">
                <i class="fas fa-plus"></i>Yeni Ekle
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stat-chips">
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(193,155,119,.12);color:#c19b77"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="sc-num">{{ $stats['total'] }}</div>
                <div class="sc-lbl">Toplam</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="sc-num" style="color:#059669">{{ $stats['available'] }}</div>
                <div class="sc-lbl">Mevcut</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(245,158,11,.12);color:#d97706"><i class="fas fa-arrow-circle-right"></i></div>
            <div>
                <div class="sc-num" style="color:#d97706">{{ $stats['in_use'] }}</div>
                <div class="sc-lbl">Kullanımda</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="fas fa-tools"></i></div>
            <div>
                <div class="sc-num" style="color:#3b82f6">{{ $stats['maintenance'] }}</div>
                <div class="sc-lbl">Bakımda</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(156,163,175,.12);color:#9ca3af"><i class="fas fa-ban"></i></div>
            <div>
                <div class="sc-num" style="color:#9ca3af">{{ $stats['retired'] }}</div>
                <div class="sc-lbl">Hizmet Dışı</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('assets.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-search me-1"></i>Arama
                </label>
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="Ad, kod, seri no, konum…">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-building me-1"></i>Şube
                </label>
                <select name="branch_id" class="form-select">
                    <option value="">— Tüm Şubeler —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-tag me-1"></i>Kategori
                </label>
                <select name="category_id" class="form-select">
                    <option value="">— Tüm Kategoriler —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-circle me-1"></i>Durum
                </label>
                <select name="status" class="form-select">
                    <option value="">— Tüm Durumlar —</option>
                    @foreach(\App\Models\Asset::STATUSES as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm"
                    style="background:#c19b77;color:#fff;border-radius:9px;padding:9px 18px;font-size:.83rem;font-weight:600;border:none">
                    <i class="fas fa-search me-1"></i>Ara
                </button>
                @if(request()->hasAny(['branch_id','category_id','status','search']))
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;padding:9px 14px">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <span class="ms-auto" style="font-size:.78rem;color:#9ca3af;white-space:nowrap">
                    {{ $assets->total() }} sonuç
                </span>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="assets-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:46px"></th>
                    <th>Demirbaş</th>
                    <th>Kategori</th>
                    <th>Şube / Konum</th>
                    <th>Durum</th>
                    <th>Garanti</th>
                    <th class="text-end" style="padding-right:20px">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    @php
                        $statusStyles = [
                            'available'   => ['bg'=>'rgba(16,185,129,.1)', 'color'=>'#059669', 'icon'=>'fa-check-circle'],
                            'in_use'      => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'#d97706', 'icon'=>'fa-arrow-circle-right'],
                            'maintenance' => ['bg'=>'rgba(59,130,246,.1)', 'color'=>'#3b82f6', 'icon'=>'fa-tools'],
                            'retired'     => ['bg'=>'rgba(156,163,175,.1)','color'=>'#9ca3af', 'icon'=>'fa-ban'],
                        ];
                        $ss = $statusStyles[$asset->status] ?? $statusStyles['retired'];
                    @endphp
                    <tr>
                        <td>
                            <div class="a-thumb">
                                @if($asset->photo)
                                    <img src="{{ asset('storage/'.$asset->photo) }}" alt="{{ $asset->name }}">
                                @else
                                    <i class="fas fa-box"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('assets.show', $asset) }}"
                               class="text-decoration-none"
                               style="font-weight:700;color:#1f2937;font-size:.88rem">{{ $asset->name }}</a>
                            <div class="mt-1">
                                <span class="code-chip">{{ $asset->asset_code }}</span>
                                @if($asset->serial_no)
                                    <span style="font-size:.75rem;color:#9ca3af;margin-left:4px">S/N: {{ $asset->serial_no }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($asset->category)
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:.78rem;font-weight:600;padding:3px 9px;border-radius:6px;background:{{ ($asset->category->color ?? '#6b7280') }}1a;color:{{ $asset->category->color ?? '#6b7280' }};border:1px solid {{ ($asset->category->color ?? '#6b7280') }}33">
                                    {{ $asset->category->name }}
                                </span>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:.83rem;font-weight:600;color:#374151">{{ optional($asset->branch)->name ?? '—' }}</div>
                            @if($asset->location)
                                <div style="font-size:.77rem;color:#9ca3af"><i class="fas fa-map-marker-alt me-1"></i>{{ $asset->location }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="status-pill"
                                  style="background:{{ $ss['bg'] }};color:{{ $ss['color'] }}">
                                <i class="fas {{ $ss['icon'] }}" style="font-size:.7rem"></i>
                                {{ \App\Models\Asset::STATUSES[$asset->status] }}
                            </span>
                        </td>
                        <td>
                            @if($asset->warranty_until)
                                @if($asset->isWarrantyExpired())
                                    <span style="font-size:.8rem;color:#dc3545;font-weight:600">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $asset->warranty_until->format('d.m.Y') }}
                                    </span>
                                    <div style="font-size:.72rem;color:#dc3545">Süresi doldu</div>
                                @else
                                    <span style="font-size:.8rem;color:#059669;font-weight:600">
                                        <i class="fas fa-shield-alt me-1"></i>{{ $asset->warranty_until->format('d.m.Y') }}
                                    </span>
                                @endif
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td class="text-end" style="padding-right:16px">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('assets.show', $asset) }}" class="act-btn act-btn-show" title="Detay">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($asset->qr_token)
                                    <a href="{{ route('assets.qrPrint', $asset) }}" target="_blank"
                                       class="act-btn act-btn-qr" title="QR Yazdır">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                @endif
                                <a href="{{ route('assets.edit', $asset) }}" class="act-btn act-btn-edit" title="Düzenle">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST"
                                      class="d-inline" data-asset-del="{{ $asset->name }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-btn-delete" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-boxes es-icon"></i>
                                <div style="font-weight:600;color:#374151;margin-bottom:4px">Demirbaş bulunamadı</div>
                                <div style="font-size:.82rem">Filtreleri değiştirin veya yeni demirbaş ekleyin.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($assets->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:center">
                {{ $assets->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('[data-asset-del]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Demirbaşı sil?',
            html: `<b>${this.dataset.assetDel}</b> silinecek. Bu işlem geri alınamaz.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Evet, Sil',
            cancelButtonText: 'İptal',
        }).then(r => { if (r.isConfirmed) this.submit(); });
    });
});
</script>
@endpush
