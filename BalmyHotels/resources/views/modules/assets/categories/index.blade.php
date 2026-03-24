@extends('layouts.default')

@push('styles')
<style>
/* ─── Page header ─────────────────────────────────────────── */
.cat-hero {
    background: #fff;
    border-radius: 16px;
    padding: 0;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
    display: flex;
    align-items: stretch;
}
.cat-hero-stripe {
    width: 6px;
    flex-shrink: 0;
    background: linear-gradient(180deg, #c19b77, #a07850);
    border-radius: 16px 0 0 16px;
}
.cat-hero-icon {
    width: 68px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: linear-gradient(135deg, #c19b77, #a07850);
    font-size: 1.6rem;
    color: #fff;
}
.cat-hero-body {
    flex: 1;
    padding: 18px 22px;
    min-width: 0;
}
.cat-hero-body h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 3px;
    letter-spacing: -.01em;
}
.cat-hero-body p {
    margin: 0;
    font-size: .82rem;
    color: #6b7280;
}
.cat-hero-actions {
    display: flex;
    align-items: center;
    padding: 0 20px;
    gap: 8px;
    flex-shrink: 0;
    border-left: 1px solid #f3f4f6;
}

/* ─── Stat chips ──────────────────────────────────────────── */
.stat-chip {
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    height: 100%;
}
.stat-chip .stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.stat-chip .stat-val  { font-size: 1.5rem; font-weight: 700; line-height: 1; }
.stat-chip .stat-lbl  { font-size: .75rem; color: #6b7280; margin-top: 2px; }

/* ─── Search bar ──────────────────────────────────────────── */
.cat-searchbox {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.cat-searchbox input {
    border: none;
    outline: none;
    flex: 1;
    font-size: .94rem;
    background: transparent;
    color: #1f2937;
}
.cat-searchbox input::placeholder { color: #9ca3af; }
.cat-searchbox .search-icon { color: #9ca3af; font-size: 1rem; }

/* ─── Category cards ──────────────────────────────────────── */
.cat-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    margin-bottom: 12px;
    overflow: hidden;
    transition: box-shadow .2s;
    border: 1px solid rgba(0,0,0,.05);
}
.cat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.11); }
.cat-card .cat-header {
    display: flex;
    align-items: center;
    padding: 0;
    cursor: pointer;
    user-select: none;
}
.cat-color-bar {
    width: 5px;
    align-self: stretch;
    flex-shrink: 0;
    border-radius: 14px 0 0 14px;
    min-height: 56px;
}
.cat-icon-wrap {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin: 10px 14px 10px 16px;
    opacity: .88;
}
.cat-name-block { flex: 1; padding: 12px 0; min-width: 0; }
.cat-name-block .cat-title {
    font-size: .97rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cat-name-block .cat-desc {
    font-size: .78rem;
    color: #6b7280;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cat-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 18px;
    flex-shrink: 0;
}
.cat-badge-pill {
    font-size: .72rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
}
.cat-actions {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px 16px;
    border-left: 1px solid #f3f4f6;
    flex-shrink: 0;
}
.cat-actions .btn-icon {
    width: 30px; height: 30px;
    padding: 0;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    transition: all .15s;
    border: 1px solid transparent;
}
.cat-chevron {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    color: #9ca3af;
    flex-shrink: 0;
    margin-right: 10px;
    transition: transform .25s, background .15s;
    background: #f9fafb;
}
.cat-card.open .cat-chevron { transform: rotate(90deg); background: #f3f4f6; }

/* ─── Subcategory panel ───────────────────────────────────── */
.sub-panel {
    border-top: 1px solid #f3f4f6;
    padding: 16px 20px 18px 36px;
    display: none;
    background: #fafafa;
}
.cat-card.open .sub-panel { display: block; }
.sub-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sub-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 5px 10px 5px 8px;
    font-size: .8rem;
    color: #374151;
    transition: box-shadow .15s, border-color .15s;
    text-decoration: none;
}
.sub-chip:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); border-color: #d1d5db; color: #111827; }
.sub-chip .sub-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.sub-chip .sub-count {
    background: #f3f4f6;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: .68rem;
    color: #6b7280;
    font-weight: 600;
}
.sub-chip .sub-actions {
    display: none;
    align-items: center;
    gap: 3px;
    margin-left: 2px;
}
.sub-chip:hover .sub-actions { display: flex; }
.sub-chip .sub-act-btn {
    width: 18px; height: 18px;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem;
    border: none;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    text-decoration: none;
    transition: background .15s;
}
.sub-actions .edit-btn  { background: rgba(67,97,238,.12); color: #4361ee; }
.sub-actions .del-btn   { background: rgba(220,53,69,.1);  color: #dc3545; }
.sub-actions .edit-btn:hover { background: rgba(67,97,238,.22); }
.sub-actions .del-btn:hover  { background: rgba(220,53,69,.2); }

/* ─── Empty / no-result state ─────────────────────────────── */
.cat-empty {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
.cat-empty i { font-size: 3rem; margin-bottom: 12px; display: block; }

/* ─── Filter highlight ────────────────────────────────────── */
.cat-card.hidden { display: none; }
mark.hl { background: #fef9c3; border-radius: 2px; padding: 0 1px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Breadcrumb ── --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Demirbaş Kategorileri</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Demirbaş</a></li>
                <li class="breadcrumb-item active">Kategoriler</li>
            </ol>
        </div>
    </div>

    {{-- ── Alerts ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Hero header ── --}}
    @php
        $totalSubs    = $categories->sum(fn($c) => $c->children->count());
        $totalAssets  = $categories->sum(fn($c) => $c->assets_count + $c->children->sum('assets_count'));
    @endphp
    <div class="cat-hero">
        <div class="cat-hero-stripe"></div>
        <div class="cat-hero-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="cat-hero-body">
            <h3>Demirbaş Kategorileri</h3>
            <p>Ana kategoriler ve alt kategoriler &mdash; toplam
                <strong style="color:#c19b77">{{ $categories->count() }}</strong> ana,
                <strong style="color:#4361ee">{{ $totalSubs }}</strong> alt kategori
            </p>
        </div>
        <div class="cat-hero-actions">
            <a href="{{ route('asset-categories.create') }}" class="btn btn-sm"
               style="background:linear-gradient(135deg,#c19b77,#a07850);color:#fff;border:0;box-shadow:0 2px 8px rgba(193,155,119,.4);border-radius:8px;font-size:.82rem;font-weight:600;padding:8px 16px;white-space:nowrap">
                <i class="fas fa-plus me-1"></i>Yeni Kategori
            </a>
        </div>
    </div>

    {{-- ── Stat chips ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-chip">
                <div class="stat-icon" style="background:rgba(193,155,119,.15);color:#c19b77">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $categories->count() }}</div>
                    <div class="stat-lbl">Ana Kategori</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-chip">
                <div class="stat-icon" style="background:rgba(67,97,238,.12);color:#4361ee">
                    <i class="fas fa-folder"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalSubs }}</div>
                    <div class="stat-lbl">Alt Kategori</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-chip">
                <div class="stat-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalAssets }}</div>
                    <div class="stat-lbl">Toplam Demirbaş</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Toolbar ── --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <div class="cat-searchbox flex-grow-1 me-auto" style="max-width:380px;margin-bottom:0">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="catSearch" placeholder="Kategori ara...">
            <button id="clearSearch" class="btn btn-sm text-muted p-0 border-0 bg-transparent d-none" title="Temizle">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
        <button class="btn btn-sm btn-outline-secondary" id="expandAll" title="Tümünü Aç">
            <i class="fas fa-expand-alt me-1"></i>Tümünü Aç
        </button>
        <button class="btn btn-sm btn-outline-secondary" id="collapseAll" title="Tümünü Kapat">
            <i class="fas fa-compress-alt me-1"></i>Kapat
        </button>
    </div>

    {{-- ── Category list ── --}}
    <div id="catList">
        @forelse($categories as $cat)
        @php
            $subCount    = $cat->children->count();
            $assetTotal  = $cat->assets_count + $cat->children->sum('assets_count');
            $colorHex    = $cat->color;
            // Build light tint for icon bg
            $r = hexdec(substr(ltrim($colorHex,'#'),0,2));
            $g = hexdec(substr(ltrim($colorHex,'#'),2,2));
            $b = hexdec(substr(ltrim($colorHex,'#'),4,2));
            $tintBg  = "rgba({$r},{$g},{$b},.13)";
        @endphp

        <div class="cat-card{{ $subCount > 0 ? '' : '' }}" data-name="{{ strtolower($cat->name) }} {{ strtolower($cat->description ?? '') }} {{ $cat->children->pluck('name')->map(fn($n)=>strtolower($n))->join(' ') }}">
            {{-- Header (click to expand) --}}
            <div class="cat-header" onclick="toggleCard(this.closest('.cat-card'))">
                <div class="cat-color-bar" style="background:{{ $colorHex }}"></div>
                <div class="cat-icon-wrap" style="background:{{ $tintBg }};color:{{ $colorHex }}">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="cat-name-block">
                    <div class="cat-title">{{ $cat->name }}</div>
                    @if($cat->description)
                        <div class="cat-desc">{{ $cat->description }}</div>
                    @endif
                </div>
                <div class="cat-meta">
                    @if($subCount > 0)
                        <span class="cat-badge-pill" style="background:{{ $tintBg }};color:{{ $colorHex }}">
                            <i class="fas fa-folder me-1"></i>{{ $subCount }} alt
                        </span>
                    @endif
                    @if($assetTotal > 0)
                        <a href="{{ route('assets.index', ['category_id' => $cat->id]) }}"
                           class="cat-badge-pill text-decoration-none"
                           style="background:rgba(16,185,129,.1);color:#059669"
                           onclick="event.stopPropagation()">
                            <i class="fas fa-boxes me-1"></i>{{ $assetTotal }}
                        </a>
                    @else
                        <span class="cat-badge-pill" style="background:#f3f4f6;color:#9ca3af">
                            <i class="fas fa-inbox me-1"></i>0
                        </span>
                    @endif
                    @if($cat->field_definitions && count($cat->field_definitions) > 0)
                        <span class="cat-badge-pill" style="background:rgba(139,92,246,.1);color:#7c3aed"
                              title="{{ collect($cat->field_definitions)->pluck('label')->join(', ') }}">
                            <i class="fas fa-list-alt me-1"></i>{{ count($cat->field_definitions) }} alan
                        </span>
                    @endif
                </div>
                <div class="cat-actions" onclick="event.stopPropagation()">
                    <a href="{{ route('asset-categories.create', ['parent_id' => $cat->id]) }}"
                       class="btn-icon text-decoration-none"
                       style="background:rgba(67,97,238,.1);color:#4361ee;border-color:rgba(67,97,238,.2)"
                       title="Alt Kategori Ekle">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="{{ route('asset-categories.edit', $cat) }}"
                       class="btn-icon text-decoration-none"
                       style="background:#f3f4f6;color:#6b7280;border-color:#e5e7eb"
                       title="Düzenle">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form action="{{ route('asset-categories.destroy', $cat) }}" method="POST" class="d-inline" data-delete>
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon"
                                style="background:rgba(220,53,69,.08);color:#dc3545;border-color:rgba(220,53,69,.15)"
                                title="Sil">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                @if($subCount > 0)
                    <div class="cat-chevron"><i class="fas fa-chevron-right"></i></div>
                @else
                    <div style="width:44px;flex-shrink:0"></div>
                @endif
            </div>

            {{-- Subcategory panel --}}
            @if($subCount > 0)
            <div class="sub-panel">
                <div class="sub-grid">
                    @foreach($cat->children as $sub)
                    @php
                        $sr = hexdec(substr(ltrim($sub->color,'#'),0,2));
                        $sg = hexdec(substr(ltrim($sub->color,'#'),2,2));
                        $sb = hexdec(substr(ltrim($sub->color,'#'),4,2));
                    @endphp
                    <div class="sub-chip" data-sub-name="{{ strtolower($sub->name) }}">
                        <span class="sub-dot" style="background:{{ $sub->color }}"></span>
                        <span>{{ $sub->name }}</span>
                        @if($sub->assets_count > 0)
                            <a href="{{ route('assets.index', ['category_id' => $sub->id]) }}"
                               class="sub-count text-decoration-none text-inherit"
                               style="color:#6b7280">{{ $sub->assets_count }}</a>
                        @endif
                        <span class="sub-actions">
                            <a href="{{ route('asset-categories.edit', $sub) }}"
                               class="sub-act-btn edit-btn text-decoration-none" title="Düzenle">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('asset-categories.destroy', $sub) }}" method="POST" class="d-inline" data-delete>
                                @csrf @method('DELETE')
                                <button type="submit" class="sub-act-btn del-btn" title="Sil">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="cat-empty">
            <i class="fas fa-folder-open"></i>
            <p class="mb-3">Henüz hiç kategori eklenmemiş.</p>
            <a href="{{ route('asset-categories.create') }}" class="btn btn-sm"
               style="background:#c19b77;color:#fff">
                <i class="fas fa-plus me-1"></i>İlk Kategoriyi Ekle
            </a>
        </div>
        @endforelse
    </div>

    <div id="noResult" class="cat-empty" style="display:none">
        <i class="fas fa-search"></i>
        <p>Aramanızla eşleşen kategori bulunamadı.</p>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
/* ── Delete confirm ── */
document.querySelectorAll('[data-delete]').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem geri alınamaz.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'İptal',
            borderRadius: '12px',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});

/* ── Toggle open/close ── */
function toggleCard(card) {
    card.classList.toggle('open');
}

/* ── Expand / collapse all ── */
document.getElementById('expandAll')?.addEventListener('click', () => {
    document.querySelectorAll('.cat-card').forEach(c => c.classList.add('open'));
});
document.getElementById('collapseAll')?.addEventListener('click', () => {
    document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('open'));
});

/* ── Live search ── */
const searchInput  = document.getElementById('catSearch');
const clearBtn     = document.getElementById('clearSearch');
const noResult     = document.getElementById('noResult');

function doSearch(q) {
    q = q.toLowerCase().trim();
    clearBtn.classList.toggle('d-none', !q);

    let visible = 0;
    document.querySelectorAll('.cat-card').forEach(card => {
        const name = card.dataset.name || '';
        const match = !q || name.includes(q);
        card.classList.toggle('hidden', !match);
        if (match) {
            visible++;
            if (q) card.classList.add('open'); // auto-expand on search
        }
    });
    noResult.style.display = (visible === 0 && q) ? 'block' : 'none';
}

searchInput.addEventListener('input', () => doSearch(searchInput.value));
clearBtn.addEventListener('click', () => { searchInput.value = ''; doSearch(''); searchInput.focus(); });
</script>
@endpush

