@extends('layouts.default')

@section('content')
<div class="container-fluid">
    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-balance-scale me-2" style="color:#4361ee"></i>
                    {{ $contract->title ?: 'Karşılaştırma #' . $contract->id }}
                </h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Sözleşme Karşılaştırma</a></li>
                <li class="breadcrumb-item active">#{{ $contract->id }}</li>
            </ol>
        </div>
    </div>

    @php
        $sim      = $contract->similarity;
        $simColor = $sim >= 80 ? '#10b981' : ($sim >= 50 ? '#f97316' : '#dc3545');
        $diff     = $contract->diff_json ?? [];
        $total    = $contract->lines_added + $contract->lines_removed + $contract->lines_equal + collect($diff)->where('type','change')->count();
    @endphp

    {{-- ÖZET BAŞLIK KARTI --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">

                {{-- Dosya isimleri --}}
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="file-chip file-chip-a">
                            <i class="fas fa-file-{{ $contract->file_a_type === 'pdf' ? 'pdf' : 'word' }} me-2"></i>
                            {{ $contract->file_a_name }}
                        </div>
                        <div class="text-muted fw-bold">↔</div>
                        <div class="file-chip file-chip-b">
                            <i class="fas fa-file-{{ $contract->file_b_type === 'pdf' ? 'pdf' : 'word' }} me-2"></i>
                            {{ $contract->file_b_name }}
                        </div>
                    </div>
                    <div class="text-muted small mt-2">
                        <i class="fas fa-user me-1"></i>{{ $contract->user->name ?? '—' }}
                        &nbsp;•&nbsp;
                        <i class="fas fa-clock me-1"></i>{{ $contract->created_at->format('d.m.Y H:i') }}
                    </div>
                </div>

                {{-- İstatistikler --}}
                <div class="col-lg-5">
                    <div class="row g-3">
                        {{-- Benzerlik gauge --}}
                        <div class="col-sm-4 text-center">
                            <svg viewBox="0 0 36 36" class="similarity-circle" style="width:80px;height:80px">
                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                <path class="circle-fg" stroke="{{ $simColor }}"
                                      stroke-dasharray="{{ $sim }}, 100"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                <text x="18" y="20.35" class="circle-text" fill="{{ $simColor }}">%{{ $sim }}</text>
                            </svg>
                            <div class="text-muted small mt-1">Benzerlik</div>
                        </div>
                        {{-- Sayı kartları --}}
                        <div class="col-sm-8">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="mini-stat" style="background:#e8f5e9;border-left:3px solid #10b981">
                                        <div class="fw-bold" style="color:#10b981;font-size:1.3rem">+{{ $contract->lines_added }}</div>
                                        <div class="text-muted small">Eklenen</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mini-stat" style="background:#fdecea;border-left:3px solid #dc3545">
                                        <div class="fw-bold" style="color:#dc3545;font-size:1.3rem">−{{ $contract->lines_removed }}</div>
                                        <div class="text-muted small">Silinen</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mini-stat" style="background:#fff8e1;border-left:3px solid #f97316">
                                        <div class="fw-bold" style="color:#f97316;font-size:1.3rem">~{{ collect($diff)->where('type','change')->count() }}</div>
                                        <div class="text-muted small">Değişen</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mini-stat" style="background:#f3f4f6;border-left:3px solid #9ca3af">
                                        <div class="fw-bold" style="color:#6b7280;font-size:1.3rem">{{ $contract->lines_equal }}</div>
                                        <div class="text-muted small">Eşit</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- FİLTRE + KONTROL --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small fw-semibold me-1">Göster:</span>
            <button class="btn btn-sm filter-btn active" data-filter="all" onclick="filterDiff('all',this)">
                Tümü
            </button>
            <button class="btn btn-sm filter-btn" data-filter="insert" onclick="filterDiff('insert',this)">
                <span class="badge me-1" style="background:#10b981">+</span> Eklenen
            </button>
            <button class="btn btn-sm filter-btn" data-filter="delete" onclick="filterDiff('delete',this)">
                <span class="badge me-1" style="background:#dc3545">−</span> Silinen
            </button>
            <button class="btn btn-sm filter-btn" data-filter="change" onclick="filterDiff('change',this)">
                <span class="badge me-1" style="background:#f97316">~</span> Değişen
            </button>
            <button class="btn btn-sm filter-btn" data-filter="equal" onclick="filterDiff('equal',this)">
                <span class="badge me-1 bg-secondary">=</span> Eşit
            </button>

            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Geri
                </a>
                <form action="{{ route('contracts.destroy', $contract) }}" method="POST" id="deleteForm">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-sm btn-outline-danger" id="deleteBtn">
                        <i class="fas fa-trash me-1"></i>Sil
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- DIFF GÖRÜNÜMÜ --}}
    @if(count($diff) === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-double fa-3x mb-3 d-block text-success"></i>
                <h5>İki dosya tamamen aynı!</h5>
            </div>
        </div>
    @else
    <div class="card border-0 shadow-sm" id="diffCard">

        {{-- Sütun Başlıkları --}}
        <div class="diff-header-row">
            <div class="diff-gutter-header"></div>
            <div class="diff-col-header diff-col-a">
                <i class="fas fa-file-{{ $contract->file_a_type === 'pdf' ? 'pdf text-danger' : 'word text-primary' }} me-2"></i>
                {{ $contract->file_a_name }}
            </div>
            <div class="diff-gutter-header"></div>
            <div class="diff-col-header diff-col-b">
                <i class="fas fa-file-{{ $contract->file_b_type === 'pdf' ? 'pdf text-danger' : 'word text-primary' }} me-2"></i>
                {{ $contract->file_b_name }}
            </div>
        </div>

        {{-- Satırlar --}}
        <div id="diffBody">
            @php $lineNoA = 0; $lineNoB = 0; @endphp

            @foreach($diff as $item)
            @php
                $type = $item['type'];
                if ($type === 'equal')  { $lineNoA++; $lineNoB++; }
                if ($type === 'delete') { $lineNoA++; }
                if ($type === 'insert') { $lineNoB++; }
                if ($type === 'change') { $lineNoA++; $lineNoB++; }
            @endphp
            <div class="diff-row diff-{{ $type }}" data-type="{{ $type }}">

                {{-- A tarafı satır numarası --}}
                <div class="diff-gutter">
                    @if($type !== 'insert'){{ $lineNoA }}@endif
                </div>

                {{-- A içeriği --}}
                <div class="diff-content diff-content-a">
                    @if($type === 'equal')
                        <span>{{ $item['a'] }}</span>
                    @elseif($type === 'delete')
                        <span>{{ $item['a'] }}</span>
                    @elseif($type === 'change')
                        @foreach($item['words_a'] as $w)
                            @if($w['type'] === 'delete')
                                <mark class="diff-word-del">{{ $w['word'] }}</mark>
                            @else
                                <span>{{ $w['word'] }}</span>
                            @endif
                        @endforeach
                    @else
                        {{-- insert: A tarafı boş --}}
                    @endif
                </div>

                {{-- B tarafı satır numarası --}}
                <div class="diff-gutter">
                    @if($type !== 'delete'){{ $lineNoB }}@endif
                </div>

                {{-- B içeriği --}}
                <div class="diff-content diff-content-b">
                    @if($type === 'equal')
                        <span>{{ $item['b'] }}</span>
                    @elseif($type === 'insert')
                        <span>{{ $item['b'] }}</span>
                    @elseif($type === 'change')
                        @foreach($item['words_b'] as $w)
                            @if($w['type'] === 'insert')
                                <mark class="diff-word-ins">{{ $w['word'] }}</mark>
                            @else
                                <span>{{ $w['word'] }}</span>
                            @endif
                        @endforeach
                    @else
                        {{-- delete: B tarafı boş --}}
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ── Dosya Chip ── */
.file-chip {
    display: inline-flex;
    align-items: center;
    padding: .4rem .9rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: .82rem;
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.file-chip-a { background: #fdecea; color: #c0392b; }
.file-chip-b { background: #e8f5e9; color: #1a7a4a; }

/* ── Benzerlik çemberi ── */
.similarity-circle { display:block; margin: 0 auto; }
.circle-bg  { fill:none; stroke:#e9ecef; stroke-width:3.8; }
.circle-fg  { fill:none; stroke-width:3.8; stroke-linecap:round; transform:rotate(-90deg); transform-origin:50% 50%; }
.circle-text { font-size:8px; font-weight:700; text-anchor:middle; }

/* ── Mini stat ── */
.mini-stat {
    padding: .5rem .75rem;
    border-radius: 8px;
    border-left: 3px solid;
}

/* ── Diff tablo ── */
#diffCard { overflow: hidden; }
.diff-header-row {
    display: grid;
    grid-template-columns: 48px 1fr 48px 1fr;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 10;
}
.diff-gutter-header {
    background: #e9ecef;
    border-right: 1px solid #dee2e6;
}
.diff-col-header {
    padding: .6rem 1rem;
    font-weight: 600;
    font-size: .82rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.diff-col-a { border-right: 2px solid #dee2e6; }

.diff-row {
    display: grid;
    grid-template-columns: 48px 1fr 48px 1fr;
    border-bottom: 1px solid #f1f3f5;
    font-size: .82rem;
    line-height: 1.6;
    font-family: 'SFMono-Regular', Consolas, monospace;
}
.diff-row:last-child { border-bottom: 0; }

.diff-gutter {
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    color: #adb5bd;
    text-align: right;
    padding: .35rem .5rem;
    font-size: .72rem;
    user-select: none;
    min-width: 48px;
}
.diff-content {
    padding: .35rem .85rem;
    white-space: pre-wrap;
    word-break: break-word;
    min-height: 28px;
}
.diff-content-a { border-right: 2px solid #dee2e6; }

/* Renk kodları */
.diff-equal   .diff-content { background: #fff; }
.diff-equal   .diff-gutter  { background: #f8f9fa; }

.diff-insert  .diff-content-b { background: #e8f5e9; }
.diff-insert  .diff-content-a { background: #f8f9fa; }
.diff-insert  .diff-gutter    { background: #d4edda; }

.diff-delete  .diff-content-a { background: #fdecea; }
.diff-delete  .diff-content-b { background: #f8f9fa; }
.diff-delete  .diff-gutter    { background: #f5c6cb; }

.diff-change  .diff-content-a { background: #fff8e1; }
.diff-change  .diff-content-b { background: #e8f5e9; }
.diff-change  .diff-gutter    { background: #ffeaa7; }

/* Kelime vurguları */
.diff-word-del { background: #ffb3b3; color: #800000; border-radius: 3px; padding: 0 2px; }
.diff-word-ins { background: #b3f0c8; color: #004d1a; border-radius: 3px; padding: 0 2px; }

/* Filtre butonları */
.filter-btn { background: #f8f9fa; border: 1px solid #dee2e6; color: #495057; border-radius: 20px; }
.filter-btn:hover { background: #e9ecef; }
.filter-btn.active { background: #4361ee; color: #fff; border-color: #4361ee; }

/* Gizli satır */
.diff-row.d-none { display: none !important; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
function filterDiff(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.diff-row').forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.classList.remove('d-none');
        } else {
            row.classList.add('d-none');
        }
    });
}

document.getElementById('deleteBtn')?.addEventListener('click', function () {
    Swal.fire({
        title: 'Karşılaştırmayı sil?',
        text: 'Bu işlem geri alınamaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'İptal',
        confirmButtonText: 'Evet, sil!'
    }).then(r => { if (r.isConfirmed) document.getElementById('deleteForm').submit(); });
});
</script>
@endpush
