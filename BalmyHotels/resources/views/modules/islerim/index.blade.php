@extends('layouts.default')

@section('title', 'İşlerim')

@push('styles')
<style>
/* ─── Page variables ─────────────────────────────────────────────────── */
:root {
    --task-radius: 14px;
    --accent: #4361ee;
    --accent-light: rgba(67,97,238,.08);
}

/* ─── Hero Header ────────────────────────────────────────────────────── */
.tasks-hero {
    background: linear-gradient(135deg, #1e2a5e 0%, #4361ee 60%, #6b7de8 100%);
    border-radius: 20px;
    padding: 28px 32px 80px;
    position: relative;
    overflow: hidden;
    margin-bottom: -60px;
}
.tasks-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.tasks-hero-title { font-size: 24px; font-weight: 800; color: #fff; margin: 0 0 4px; }
.tasks-hero-sub   { color: rgba(255,255,255,.65); font-size: 13px; }

/* ─── KPI Cards ──────────────────────────────────────────────────────── */
.task-kpi-row { position: relative; z-index: 2; }
.task-kpi-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 22px;
    box-shadow: 0 4px 24px rgba(67,97,238,.12);
    display: flex; align-items: center; gap: 16px;
    border: 1px solid rgba(67,97,238,.08);
    transition: transform .2s, box-shadow .2s;
}
.task-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(67,97,238,.16); }
.task-kpi-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.task-kpi-val  { font-size: 26px; font-weight: 800; line-height: 1; }
.task-kpi-lbl  { font-size: 12px; color: #9ca3af; margin-top: 2px; font-weight: 500; }

/* ─── Filter Tabs ────────────────────────────────────────────────────── */
.filter-tabs { display: flex; gap: 6px; background: #f1f3f9; border-radius: 12px; padding: 4px; }
.filter-tab  {
    flex: 1; text-align: center; padding: 7px 14px; border-radius: 9px;
    font-size: 12px; font-weight: 600; color: #6b7280; cursor: pointer;
    border: none; background: transparent; transition: all .2s; white-space: nowrap;
}
.filter-tab.active {
    background: #fff; color: var(--accent);
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.filter-tab .tab-count {
    display: inline-block; min-width: 18px; height: 18px; border-radius: 9px;
    font-size: 10px; font-weight: 700; padding: 0 5px; line-height: 18px;
    background: rgba(67,97,238,.1); color: var(--accent); margin-left: 4px;
    vertical-align: middle;
}
.filter-tab.active .tab-count { background: var(--accent); color: #fff; }

/* ─── Task Row ───────────────────────────────────────────────────────── */
.task-row {
    background: #fff;
    border-radius: var(--task-radius);
    padding: 16px 20px;
    margin-bottom: 10px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; align-items: center; gap: 14px;
    transition: box-shadow .2s, transform .15s;
    position: relative; overflow: hidden;
}
.task-row::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
    border-radius: var(--task-radius) 0 0 var(--task-radius);
    background: var(--pcolor, #e5e7eb);
}
.task-row:hover { box-shadow: 0 6px 20px rgba(0,0,0,.09); transform: translateX(2px); }
.task-row.is-completed { opacity: .6; }

/* ─── Checkbox ───────────────────────────────────────────────────────── */
.task-check-btn {
    width: 24px; height: 24px; border-radius: 50%; border: 2px solid #d1d5db;
    background: transparent; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s; padding: 0;
    outline: none;
}
.task-check-btn:hover, .task-check-btn:focus { border-color: var(--accent); background: var(--accent-light); }
.task-check-btn.is-done { background: #10b981; border-color: #10b981; }

/* ─── Priority Dot ───────────────────────────────────────────────────── */
.priority-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}

/* ─── Status Chip ────────────────────────────────────────────────────── */
.status-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 9px; border-radius: 20px;
    font-size: 10px; font-weight: 700; letter-spacing: .3px;
}

/* ─── Task Actions ───────────────────────────────────────────────────── */
.task-actions { display: flex; gap: 6px; flex-shrink: 0; opacity: 0; transition: opacity .2s; }
.task-row:hover .task-actions { opacity: 1; }
.task-action-btn {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e5e7eb;
    background: #fff; cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 12px; color: #6b7280;
    transition: all .15s; padding: 0;
}
.task-action-btn:hover { background: var(--accent-light); border-color: var(--accent); color: var(--accent); }
.task-action-btn.danger:hover { background: rgba(239,68,68,.08); border-color: #ef4444; color: #ef4444; }

/* ─── Add Task Modal ─────────────────────────────────────────────────── */
.add-task-modal .modal-content { border-radius: 20px; overflow: hidden; }
.add-task-modal .modal-header  {
    background: linear-gradient(135deg,#1e2a5e,#4361ee);
    padding: 20px 24px; border: none;
}
.field-label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; display:block; }

/* ─── Empty State ────────────────────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 56px 24px;
    background: #fff; border-radius: 20px; border: 2px dashed #e5e7eb;
}
.empty-state-icon { width: 72px; height: 72px; border-radius: 20px; margin: 0 auto 16px;
    background: linear-gradient(135deg,#f0f4ff,#e0e7ff);
    display: flex; align-items: center; justify-content: center; }

@keyframes overduePulse {
    0%,100% { box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    50%  { box-shadow: 0 0 0 3px rgba(239,68,68,.15); }
}
.task-row.is-overdue { animation: overduePulse 2.5s infinite; }
</style>
@endpush

@section('content')
@php
    $pending      = $tasks->where('status', 'pending')->count();
    $inProgress   = $tasks->where('status', 'in_progress')->count();
    $completed    = $tasks->where('status', 'completed')->count();
    $total        = $tasks->count();
    $pct          = $total > 0 ? round($completed / $total * 100) : 0;
    $overdueCount = $tasks->filter(fn($t) => $t->due_date && $t->due_date->isPast() && $t->status !== 'completed')->count();
    $circumference = 2 * pi() * 30;
    $offset        = $circumference - ($pct / 100 * $circumference);
@endphp
<div class="container-fluid pb-5">

    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <div class="tasks-hero mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4 position-relative">

            {{-- Left: title + subtitle --}}
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px">
                    {{ now()->format('d F Y') }}
                </div>
                <div class="tasks-hero-title">İşlerim</div>
                <div class="tasks-hero-sub">Kişisel görev takibi &amp; yönetimi</div>
            </div>

            {{-- Right: progress ring + stats + button --}}
            <div class="d-flex align-items-center gap-3 flex-wrap">

                {{-- Progress ring (proper centered overlay) --}}
                <div style="position:relative;width:76px;height:76px;flex-shrink:0">
                    <svg width="76" height="76" viewBox="0 0 76 76" style="position:absolute;inset:0;transform:rotate(-90deg)">
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="7"/>
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="7"
                                stroke-linecap="round"
                                stroke-dasharray="{{ number_format(2 * pi() * 32, 2) }}"
                                stroke-dashoffset="{{ number_format(2 * pi() * 32 - ($pct / 100 * 2 * pi() * 32), 2) }}"
                                style="transition:stroke-dashoffset .6s ease"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px">
                        <span style="font-size:15px;font-weight:800;color:#fff;line-height:1">{{ $pct }}%</span>
                        <span style="font-size:8px;color:rgba(255,255,255,.6);letter-spacing:.5px;text-transform:uppercase">bitti</span>
                    </div>
                </div>

                {{-- Stat summary --}}
                <div style="border-left:1px solid rgba(255,255,255,.2);padding-left:16px;padding-right:8px">
                    <div style="font-size:10px;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Tamamlanma</div>
                    <div style="font-size:22px;font-weight:800;color:#fff;line-height:1">{{ $completed }}<span style="font-size:14px;font-weight:500;opacity:.6">/{{ $total }}</span></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:3px">görev</div>
                </div>

                {{-- Add button --}}
                <button class="btn fw-bold" data-bs-toggle="modal" data-bs-target="#addTaskModal"
                        style="background:#fff;color:#4361ee;border-radius:12px;font-size:13px;
                               padding:10px 22px;box-shadow:0 4px 16px rgba(0,0,0,.2);
                               white-space:nowrap;flex-shrink:0">
                    <i class="fas fa-plus me-2"></i>Yeni Görev
                </button>
            </div>
        </div>
    </div>

    {{-- ── KPI Cards ─────────────────────────────────────────────────────────── --}}
    <div class="row g-3 task-kpi-row mb-4">
        <div class="col-6 col-md-3">
            <div class="task-kpi-card">
                <div class="task-kpi-icon" style="background:rgba(67,97,238,.1)">
                    <i class="fas fa-layer-group" style="color:#4361ee"></i>
                </div>
                <div>
                    <div class="task-kpi-val" style="color:#1e2a5e">{{ $total }}</div>
                    <div class="task-kpi-lbl">Toplam Görev</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card">
                <div class="task-kpi-icon" style="background:rgba(249,115,22,.1)">
                    <i class="fas fa-hourglass-half" style="color:#f97316"></i>
                </div>
                <div>
                    <div class="task-kpi-val" style="color:#f97316">{{ $pending + $inProgress }}</div>
                    <div class="task-kpi-lbl">Devam Eden</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card">
                <div class="task-kpi-icon" style="background:rgba(16,185,129,.1)">
                    <i class="fas fa-check-double" style="color:#10b981"></i>
                </div>
                <div>
                    <div class="task-kpi-val" style="color:#10b981">{{ $completed }}</div>
                    <div class="task-kpi-lbl">Tamamlanan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card">
                <div class="task-kpi-icon" style="background:rgba(239,68,68,.1)">
                    <i class="fas fa-exclamation-circle" style="color:#ef4444"></i>
                </div>
                <div>
                    <div class="task-kpi-val" style="color:#ef4444">{{ $overdueCount }}</div>
                    <div class="task-kpi-lbl">Gecikmiş</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Flash ─────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 rounded-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3 rounded-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Filter Tabs + Task List ─────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="filter-tabs" style="max-width:520px;width:100%">
            <button class="filter-tab active" data-filter="all">
                Tümü <span class="tab-count">{{ $total }}</span>
            </button>
            <button class="filter-tab" data-filter="in_progress">
                Devam Ediyor <span class="tab-count">{{ $inProgress }}</span>
            </button>
            <button class="filter-tab" data-filter="pending">
                Bekliyor <span class="tab-count">{{ $pending }}</span>
            </button>
            <button class="filter-tab" data-filter="completed">
                Tamamlandı <span class="tab-count">{{ $completed }}</span>
            </button>
        </div>
        <span class="text-muted" style="font-size:12px">
            <i class="fas fa-sort-amount-down me-1"></i>En yeni önce
        </span>
    </div>

    <div id="taskList">
    @forelse($tasks as $task)
    @php
        $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
        $isDueSoon = $task->due_date && $task->due_date->isToday() && $task->status !== 'completed';
        $pColor = match($task->priority) {
            'high'   => '#ef4444',
            'medium' => '#f97316',
            'low'    => '#10b981',
            default  => '#94a3b8',
        };
        $sColor = match($task->status) {
            'pending'     => '#f59e0b',
            'in_progress' => '#4361ee',
            'completed'   => '#10b981',
            default       => '#6b7280',
        };
        $sBg = match($task->status) {
            'pending'     => 'rgba(245,158,11,.1)',
            'in_progress' => 'rgba(67,97,238,.1)',
            'completed'   => 'rgba(16,185,129,.1)',
            default       => 'rgba(107,114,128,.1)',
        };
    @endphp
    <div class="task-row {{ $isOverdue ? 'is-overdue' : '' }} {{ $task->status === 'completed' ? 'is-completed' : '' }}"
         data-status="{{ $task->status }}"
         style="--pcolor:{{ $pColor }}">

        {{-- Complete button --}}
        <form action="{{ route('islerim.complete', $task) }}" method="POST" class="flex-shrink-0">
            @csrf @method('PATCH')
            <button type="submit" class="task-check-btn {{ $task->status === 'completed' ? 'is-done' : '' }}"
                    title="{{ $task->status === 'completed' ? 'Tamamlandı' : 'Tamamlandı olarak işaretle' }}">
                @if($task->status === 'completed')
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M2 6l3 3 5-5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @endif
            </button>
        </form>

        {{-- Priority dot --}}
        <div class="priority-dot flex-shrink-0" style="background:{{ $pColor }}"></div>

        {{-- Content --}}
        <div class="flex-grow-1" style="min-width:0">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="fw-semibold {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}"
                      style="font-size:14px;color:{{ $task->status === 'completed' ? '' : '#1e293b' }}">
                    {{ $task->title }}
                </span>
                <span class="status-chip" style="background:{{ $sBg }};color:{{ $sColor }}">
                    {{ $task->status_label }}
                </span>
                @if($isOverdue)
                <span class="status-chip" style="background:rgba(239,68,68,.1);color:#ef4444">
                    <i class="fas fa-exclamation-triangle" style="font-size:9px"></i> Gecikti
                </span>
                @elseif($isDueSoon)
                <span class="status-chip" style="background:rgba(245,158,11,.15);color:#d97706">
                    <i class="fas fa-clock" style="font-size:9px"></i> Bugün
                </span>
                @endif
            </div>
            @if($task->description)
            <div class="text-muted mb-1" style="font-size:12px;line-height:1.5">
                {{ Str::limit($task->description, 140) }}
            </div>
            @endif
            <div class="d-flex align-items-center gap-3" style="font-size:11px;color:#9ca3af">
                <span><i class="fas fa-flag me-1" style="color:{{ $pColor }}"></i>{{ $task->priority_label }}</span>
                @if($task->due_date)
                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                    <i class="fas fa-calendar-alt me-1"></i>{{ $task->due_date->format('d.m.Y') }}
                </span>
                @endif
                <span><i class="fas fa-clock me-1"></i>{{ $task->created_at->diffForHumans() }}</span>
            </div>
        </div>

        {{-- Actions (visible on hover) --}}
        <div class="task-actions">
            <button type="button" class="task-action-btn edit-task-btn"
                    title="Düzenle"
                    data-bs-toggle="modal" data-bs-target="#editTaskModal"
                    data-id="{{ $task->id }}"
                    data-title="{{ $task->title }}"
                    data-description="{{ $task->description }}"
                    data-due="{{ $task->due_date?->format('Y-m-d') }}"
                    data-priority="{{ $task->priority }}"
                    data-status="{{ $task->status }}">
                <i class="fas fa-pen"></i>
            </button>
            <form action="{{ route('islerim.destroy', $task) }}" method="POST"
                  onsubmit="return confirm('Bu görevi silmek istediğinizden emin misiniz?')">
                @csrf @method('DELETE')
                <button type="submit" class="task-action-btn danger" title="Sil">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state" id="emptyState">
        <div class="empty-state-icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#4361ee" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
        </div>
        <h6 class="fw-bold mb-1" style="color:#1e293b">Henüz görev yok</h6>
        <p class="text-muted mb-4" style="font-size:13px">Yeni bir görev ekleyerek başlayın.</p>
        <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addTaskModal"
                style="border-radius:12px">
            <i class="fas fa-plus me-2"></i>İlk Görevi Ekle
        </button>
    </div>
    @endforelse
    </div>{{-- #taskList --}}
</div>

{{-- ── Add Task Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade add-task-modal" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-1">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Yeni Görev Ekle
                    </h5>
                    <div style="font-size:12px;color:rgba(255,255,255,.65)">Görev detaylarını doldurun</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('islerim.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pb-2 pt-4">
                    <div class="mb-4">
                        <label class="field-label">Görev Başlığı <span class="text-danger">*</span></label>
                        <input type="text" name="title"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               style="border-radius:10px;font-size:15px"
                               value="{{ old('title') }}"
                               placeholder="Görevi kısaca tanımlayın..."
                               required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="field-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"
                                  style="border-radius:10px;font-size:14px;resize:none"
                                  placeholder="Görev hakkında ek detaylar...">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="field-label">Öncelik</label>
                            <div class="d-flex gap-2" id="prioritySelector">
                                @foreach(['low' => ['Düşük','#10b981'], 'medium' => ['Orta','#f97316'], 'high' => ['Yüksek','#ef4444']] as $val => [$lbl, $clr])
                                <label class="priority-option flex-fill" style="cursor:pointer">
                                    <input type="radio" name="priority" value="{{ $val }}" class="d-none"
                                           {{ old('priority','medium') == $val ? 'checked' : '' }}>
                                    <div class="priority-opt-box" style="--oc:{{ $clr }};
                                         border:2px solid {{ old('priority','medium') == $val ? $clr : '#e5e7eb' }};
                                         border-radius:10px;padding:10px;text-align:center;transition:all .2s;
                                         background:{{ old('priority','medium') == $val ? $clr.'15' : '#fafafa' }}">
                                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $clr }};
                                                    margin:0 auto 6px"></div>
                                        <div style="font-size:12px;font-weight:700;color:{{ $clr }}">{{ $lbl }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="field-label">Bitiş Tarihi</label>
                            <input type="date" name="due_date" class="form-control"
                                   style="border-radius:10px;font-size:14px"
                                   value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}">
                            <div class="d-flex align-items-center gap-1 mt-2" style="font-size:11px;color:#9ca3af">
                                <i class="fas fa-bell"></i>
                                <span>1 gün önce e-posta hatırlatıcı gönderilir</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                            style="border-radius:10px">İptal</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold"
                            style="border-radius:10px;background:linear-gradient(135deg,#4361ee,#6b7de8);border:none">
                        <i class="fas fa-plus me-2"></i>Görevi Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Task Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden">
            <div class="modal-header border-0 pb-1"
                 style="background:linear-gradient(135deg,#0f172a,#1e40af);padding:20px 24px">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-pen me-2"></i>Görevi Düzenle
                    </h5>
                    <div style="font-size:12px;color:rgba(255,255,255,.55)">Değişiklikleri kaydedin</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-2 pt-4">
                    <div class="mb-3">
                        <label class="field-label">Başlık <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="et_title" class="form-control form-control-lg"
                               style="border-radius:10px;font-size:15px" required>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Açıklama</label>
                        <textarea name="description" id="et_desc" class="form-control" rows="3"
                                  style="border-radius:10px;font-size:14px;resize:none"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="field-label">Öncelik</label>
                            <select name="priority" id="et_priority" class="form-select"
                                    style="border-radius:10px;font-size:14px">
                                <option value="low">🟢 Düşük</option>
                                <option value="medium">🟠 Orta</option>
                                <option value="high">🔴 Yüksek</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Durum</label>
                            <select name="status" id="et_status" class="form-select"
                                    style="border-radius:10px;font-size:14px">
                                <option value="pending">⏳ Bekliyor</option>
                                <option value="in_progress">🔵 Devam Ediyor</option>
                                <option value="completed">✅ Tamamlandı</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Bitiş Tarihi</label>
                            <input type="date" name="due_date" id="et_due" class="form-control"
                                   style="border-radius:10px;font-size:14px">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                            style="border-radius:10px">İptal</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold"
                            style="border-radius:10px;background:linear-gradient(135deg,#1e40af,#4361ee);border:none">
                        <i class="fas fa-save me-2"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Edit Modal ────────────────────────────────────────────────────────────
document.querySelectorAll('.edit-task-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const form = document.getElementById('editTaskForm');
        form.action = '/islerim/' + this.dataset.id;
        document.getElementById('et_title').value    = this.dataset.title        || '';
        document.getElementById('et_desc').value     = this.dataset.description  || '';
        document.getElementById('et_due').value      = this.dataset.due          || '';
        document.getElementById('et_priority').value = this.dataset.priority     || 'medium';
        document.getElementById('et_status').value   = this.dataset.status       || 'pending';
    });
});

// ── Filter Tabs ───────────────────────────────────────────────────────────
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        let visible = 0;
        document.querySelectorAll('#taskList .task-row').forEach(row => {
            const show = filter === 'all' || row.dataset.status === filter;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const empty = document.getElementById('emptyState');
        if (empty) empty.style.display = visible === 0 ? '' : 'none';
    });
});

// ── Priority Selector (Add Modal) ─────────────────────────────────────────
document.querySelectorAll('#prioritySelector input[type=radio]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('#prioritySelector .priority-opt-box').forEach(box => {
            box.style.border = '2px solid #e5e7eb';
            box.style.background = '#fafafa';
        });
        const box = this.closest('label').querySelector('.priority-opt-box');
        const c = getComputedStyle(box).getPropertyValue('--oc').trim();
        box.style.borderColor = c;
        box.style.background  = c + '15';
    });
});

// ── Auto-open Add modal on validation error ───────────────────────────────
@if($errors->any())
(new bootstrap.Modal(document.getElementById('addTaskModal'))).show();
@endif
</script>
@endpush

@endsection
