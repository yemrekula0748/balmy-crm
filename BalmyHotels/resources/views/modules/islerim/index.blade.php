@extends('layouts.default')

@section('title', 'İşlerim')

@section('content')
<div class="container-fluid pb-5">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>İşlerim</h4>
                <span>Kişisel görev listesi &amp; takip</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">İşlerim</li>
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

    <div class="row g-4">

        {{-- Sol: Yeni Görev --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header py-3" style="background:linear-gradient(135deg,#4361ee,#7b8cde);">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Yeni Görev Ekle
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('islerim.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px">Görev Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Görevi kısaca tanımla..." required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px">Açıklama</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Detaylar...">{{ old('description') }}</textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:13px">Öncelik</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="low"    {{ old('priority') == 'low'    ? 'selected' : '' }}>Düşük</option>
                                    <option value="medium" {{ old('priority','medium') == 'medium' ? 'selected' : '' }}>Orta</option>
                                    <option value="high"   {{ old('priority') == 'high'   ? 'selected' : '' }}>Yüksek</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:13px">Bitiş Tarihi</label>
                                <input type="date" name="due_date" class="form-control form-control-sm"
                                       value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Görevi Ekle
                            </button>
                        </div>
                    </form>
                </div>

                {{-- İstatistik özeti --}}
                <div class="card-footer bg-white border-top pt-3 pb-3">
                    @php
                        $pending    = $tasks->where('status', 'pending')->count();
                        $inProgress = $tasks->where('status', 'in_progress')->count();
                        $completed  = $tasks->where('status', 'completed')->count();
                        $total      = $tasks->count();
                        $pct        = $total > 0 ? round($completed / $total * 100) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2" style="font-size:12px">
                        <span class="text-muted">Tamamlanma</span>
                        <span class="fw-bold">{{ $pct }}%</span>
                    </div>
                    <div class="progress mb-3" style="height:6px">
                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="row text-center g-0">
                        <div class="col-4">
                            <div class="fw-bold text-warning" style="font-size:18px">{{ $pending }}</div>
                            <div class="text-muted" style="font-size:11px">Bekliyor</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-primary" style="font-size:18px">{{ $inProgress }}</div>
                            <div class="text-muted" style="font-size:11px">Devam Ediyor</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success" style="font-size:18px">{{ $completed }}</div>
                            <div class="text-muted" style="font-size:11px">Tamamlandı</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sağ: Görevler --}}
        <div class="col-xl-8 col-lg-7">

            {{-- Filtre butonları --}}
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button class="btn btn-sm task-filter-btn active" data-filter="all"
                        style="border-radius:20px;font-size:13px">
                    Tümü <span class="badge bg-secondary ms-1">{{ $total }}</span>
                </button>
                <button class="btn btn-sm task-filter-btn btn-outline-warning" data-filter="pending"
                        style="border-radius:20px;font-size:13px">
                    Bekliyor <span class="badge bg-warning ms-1">{{ $pending }}</span>
                </button>
                <button class="btn btn-sm task-filter-btn btn-outline-primary" data-filter="in_progress"
                        style="border-radius:20px;font-size:13px">
                    Devam Ediyor <span class="badge bg-primary ms-1">{{ $inProgress }}</span>
                </button>
                <button class="btn btn-sm task-filter-btn btn-outline-success" data-filter="completed"
                        style="border-radius:20px;font-size:13px">
                    Tamamlandı <span class="badge bg-success ms-1">{{ $completed }}</span>
                </button>
            </div>

            {{-- Görev Kartları --}}
            @forelse($tasks as $task)
            @php
                $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
                $isDueSoon = $task->due_date && $task->due_date->isToday() && $task->status !== 'completed';
                $priorityColor = match($task->priority) {
                    'high'   => '#ef4444',
                    'medium' => '#f97316',
                    'low'    => '#10b981',
                    default  => '#6b7280',
                };
                $statusColor = match($task->status) {
                    'pending'     => '#f59e0b',
                    'in_progress' => '#4361ee',
                    'completed'   => '#10b981',
                    default       => '#6b7280',
                };
            @endphp
            <div class="card border-0 shadow-sm mb-3 task-card {{ $task->status === 'completed' ? 'task-done' : '' }}"
                 data-status="{{ $task->status }}"
                 style="border-left: 4px solid {{ $priorityColor }} !important;
                        {{ $task->status === 'completed' ? 'opacity:.7' : '' }}
                        {{ $isOverdue ? 'background:rgba(239,68,68,.03);' : '' }}">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-start gap-3">

                        {{-- Complete Checkbox --}}
                        <form action="{{ route('islerim.complete', $task) }}" method="POST" class="mt-1">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn p-0 border-0 bg-transparent"
                                    title="{{ $task->status === 'completed' ? 'Tamamlandı' : 'Tamamla' }}">
                                @if($task->status === 'completed')
                                <div style="width:22px;height:22px;border-radius:50%;background:#10b981;
                                            display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-check text-white" style="font-size:11px"></i>
                                </div>
                                @else
                                <div style="width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;
                                            background:white;transition:all .2s;"
                                     onmouseover="this.style.borderColor='#4361ee'"
                                     onmouseout="this.style.borderColor='#d1d5db'">
                                </div>
                                @endif
                            </button>
                        </form>

                        {{-- İçerik --}}
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="mb-0 fw-semibold {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}"
                                    style="font-size:14px">{{ $task->title }}</h6>

                                {{-- Priority Badge --}}
                                <span class="badge rounded-pill" style="font-size:10px;background:{{ $priorityColor }}20;color:{{ $priorityColor }}">
                                    {{ $task->priority_label }}
                                </span>

                                {{-- Status Badge --}}
                                <span class="badge rounded-pill" style="font-size:10px;background:{{ $statusColor }}20;color:{{ $statusColor }}">
                                    {{ $task->status_label }}
                                </span>

                                @if($isOverdue)
                                <span class="badge bg-danger" style="font-size:10px">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Gecikti
                                </span>
                                @elseif($isDueSoon)
                                <span class="badge bg-warning text-dark" style="font-size:10px">
                                    <i class="fas fa-clock me-1"></i>Bugün
                                </span>
                                @endif
                            </div>

                            @if($task->description)
                            <p class="text-muted mb-1 mt-1" style="font-size:12px;line-height:1.4">
                                {{ Str::limit($task->description, 120) }}
                            </p>
                            @endif

                            <div class="d-flex align-items-center gap-3 mt-1">
                                @if($task->due_date)
                                <span class="d-flex align-items-center gap-1 {{ $isOverdue ? 'text-danger' : 'text-muted' }}" style="font-size:12px">
                                    <i class="fas fa-calendar-alt" style="font-size:11px"></i>
                                    {{ $task->due_date->format('d.m.Y') }}
                                </span>
                                @endif
                                <span class="text-muted" style="font-size:11px">
                                    {{ $task->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Aksiyonlar --}}
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button class="btn btn-sm btn-outline-secondary edit-task-btn"
                                    style="border-radius:8px;font-size:11px;padding:3px 8px"
                                    data-bs-toggle="modal" data-bs-target="#editTaskModal"
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-description="{{ $task->description }}"
                                    data-due="{{ $task->due_date?->format('Y-m-d') }}"
                                    data-priority="{{ $task->priority }}"
                                    data-status="{{ $task->status }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('islerim.destroy', $task) }}" method="POST"
                                  onsubmit="return confirm('Bu görevi silmek istediğinizden emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        style="border-radius:8px;font-size:11px;padding:3px 8px">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24"
                         fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round"
                         stroke-linejoin="round" class="mb-3">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <p class="text-muted mb-0" style="font-size:14px">Henüz görev yok. Soldan yeni görev ekleyebilirsiniz.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Edit Task Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#f97316,#fdba74);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-edit me-2"></i>Görevi Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Başlık <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="et_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Açıklama</label>
                        <textarea name="description" id="et_desc" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label fw-semibold" style="font-size:13px">Öncelik</label>
                            <select name="priority" id="et_priority" class="form-select form-select-sm">
                                <option value="low">Düşük</option>
                                <option value="medium">Orta</option>
                                <option value="high">Yüksek</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold" style="font-size:13px">Durum</label>
                            <select name="status" id="et_status" class="form-select form-select-sm">
                                <option value="pending">Bekliyor</option>
                                <option value="in_progress">Devam Ediyor</option>
                                <option value="completed">Tamamlandı</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold" style="font-size:13px">Bitiş</label>
                            <input type="date" name="due_date" id="et_due" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 pt-0 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Edit Modal ──────────────────────────────────────────────────────────────
document.querySelectorAll('.edit-task-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('editTaskForm').action = '/islerim/' + id;
        document.getElementById('et_title').value    = this.dataset.title    || '';
        document.getElementById('et_desc').value     = this.dataset.description || '';
        document.getElementById('et_due').value      = this.dataset.due      || '';
        document.getElementById('et_priority').value = this.dataset.priority || 'medium';
        document.getElementById('et_status').value   = this.dataset.status   || 'pending';
    });
});

// ── Filtre Butonları ─────────────────────────────────────────────────────────
const filterBtns = document.querySelectorAll('.task-filter-btn');
const taskCards  = document.querySelectorAll('.task-card');

filterBtns.forEach(btn => {
    btn.addEventListener('click', function () {
        const filter = this.dataset.filter;

        // active state
        filterBtns.forEach(b => b.classList.remove('active', 'btn-primary', 'btn-secondary'));
        this.classList.add('active');

        taskCards.forEach(card => {
            if (filter === 'all' || card.dataset.status === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<style>
.task-filter-btn.active {
    background: #4361ee !important;
    border-color: #4361ee !important;
    color: white !important;
}
.task-card {
    transition: box-shadow .15s, transform .15s;
}
.task-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.10) !important;
    transform: translateY(-1px);
}
</style>
@endpush

@endsection
