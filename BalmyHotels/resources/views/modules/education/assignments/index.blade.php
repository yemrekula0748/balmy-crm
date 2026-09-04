@extends('layouts.default')
@section('title', 'Eğitim Atamaları')

@php
    $oldLearnerIds = collect(old('user_ids', []))->map(fn ($id) => (int) $id)->all();
    $hasLearnersWithoutDepartment = $learners->contains(fn ($learner) => ! $learner->department_id);
    $localizedLanguageLabels = ['tr' => 'Türkçe', 'en' => 'İngilizce', 'ru' => 'Rusça'];
    $languageLabel = $localizedLanguageLabels[$language] ?? ($languages[$language] ?? strtoupper($language));
    $hasAssignmentFilters = $assignmentSearch !== '' || $assignmentDepartment !== '';
    $foreignLearnerCount = (int) ($learnerOriginCounts['foreign'] ?? 0);
    $domesticLearnerCount = (int) ($learnerOriginCounts['domestic'] ?? 0);
    $unknownOriginLearnerCount = (int) ($learnerOriginCounts['unknown'] ?? 0);
@endphp

@push('styles')
<style>
    .assignment-page .card {
        border-radius: 12px;
    }
    .assignment-page .section-kicker {
        color: #6c757d;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .assignment-page .planning-bar {
        background: #f8fafc;
        border: 1px solid #e9eef5;
        border-radius: 10px;
        padding: 12px;
    }
    .assignment-page .learner-toolbar {
        background: #f8fafc;
        border: 1px solid #e9eef5;
        border-radius: 10px 10px 0 0;
        padding: 12px;
    }
    .assignment-page .learner-list {
        border: 1px solid #e9eef5;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        max-height: 430px;
        min-height: 180px;
        overflow-y: auto;
    }
    .assignment-page .learner-option {
        align-items: center;
        border-bottom: 1px solid #eef1f5;
        cursor: pointer;
        display: flex;
        gap: 10px;
        margin: 0;
        padding: 10px 12px;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .assignment-page .learner-option:last-of-type {
        border-bottom: 0;
    }
    .assignment-page .learner-option:hover {
        background: #f8fbff;
    }
    .assignment-page .learner-option.is-selected {
        background: #eef6ff;
        box-shadow: inset 3px 0 0 #2f74d0;
    }
    .assignment-page .learner-checkbox {
        accent-color: #2f74d0;
        cursor: pointer;
        flex: 0 0 auto;
        height: 18px;
        width: 18px;
    }
    .assignment-page .learner-avatar {
        align-items: center;
        background: #e8eef7;
        border-radius: 50%;
        color: #35516f;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: .72rem;
        font-weight: 700;
        height: 34px;
        justify-content: center;
        width: 34px;
    }
    .assignment-page .learner-details {
        min-width: 0;
    }
    .assignment-page .learner-name {
        color: #273240;
        font-size: .9rem;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .assignment-page .learner-origin-badge {
        flex: 0 0 auto;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .assignment-page .learner-meta {
        color: #7b8794;
        font-size: .76rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .assignment-page .selection-summary {
        align-items: center;
        background: #eef6ff;
        border: 1px solid #d6e8fb;
        border-radius: 8px;
        color: #315d88;
        display: flex;
        font-size: .82rem;
        justify-content: space-between;
        padding: 8px 10px;
    }
    .assignment-page .assignment-table td,
    .assignment-page .assignment-table th {
        vertical-align: middle;
    }
    .assignment-page .progress {
        background: #e9edf2;
        min-width: 86px;
    }
    .assignment-page .empty-filter-state {
        color: #7b8794;
        padding: 34px 16px;
        text-align: center;
    }
    @media (min-width: 1200px) {
        .assignment-page .assignment-form-card {
            position: sticky;
            top: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid assignment-page">
    <div class="row page-titles mx-0 align-items-center">
        <div class="col-md-8 p-md-0">
            <div class="welcome-text">
                <h4>Toplu Eğitim Atama</h4>
                <span>Eğitimi seçin, öğrenenleri filtreleyin ve tek adımda atayın.</span>
            </div>
        </div>
        <div class="col-md-4 p-md-0 mt-2 mt-md-0 text-md-end">
            <div class="d-inline-flex flex-wrap justify-content-md-end gap-2">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="fas fa-user-graduate me-1 text-primary"></i>{{ $learners->count() }} aktif öğrenen
                </span>
                <span class="badge bg-info-subtle text-info border px-3 py-2">
                    <i class="fas fa-globe me-1"></i>{{ $foreignLearnerCount }} yabancı personel
                </span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm assignment-form-card">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="section-kicker mb-1">Yeni planlama</div>
                            <h5 class="mb-1">Eğitim ve öğrenen seçimi</h5>
                            <small class="text-muted">Seçimleriniz filtre değiştirildiğinde korunur.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $languageLabel }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="planning-bar row g-2 mb-3" id="assignmentPeriodForm">
                        <div class="col-sm-6">
                            <label for="assignmentLanguage" class="form-label small fw-semibold mb-1">Eğitim dili</label>
                            <select name="language" id="assignmentLanguage" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($languages as $code => $label)
                                    <option value="{{ $code }}" @selected($language === $code)>{{ $localizedLanguageLabels[$code] ?? $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="assignmentWeek" class="form-label small fw-semibold mb-1">Hafta başlangıcı</label>
                            <input type="date" name="week_start" id="assignmentWeek" value="{{ $weekStart->toDateString() }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                    </form>

                    <form method="POST" action="{{ route('education.assignments.store') }}" id="assignmentForm">
                        @csrf
                        <input type="hidden" name="language" value="{{ $language }}">
                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                        <div class="row g-2 mb-3">
                            <div class="col-sm-7">
                                <label for="educationCourse" class="form-label fw-semibold">Eğitim</label>
                                <select name="education_course_id" id="educationCourse" class="form-select" required>
                                    <option value="">Eğitim seçiniz</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @selected(old('education_course_id') == $course->id)>{{ $course->title }}</option>
                                    @endforeach
                                </select>
                                @if($courses->isEmpty())
                                    <small class="text-warning d-block mt-1">Bu dilde aktif eğitim bulunmuyor.</small>
                                @endif
                            </div>
                            <div class="col-sm-5">
                                <label for="assignmentDueAt" class="form-label fw-semibold">Son tarih</label>
                                <input type="datetime-local" name="due_at" id="assignmentDueAt" class="form-control" value="{{ old('due_at') }}">
                                <small class="text-muted">İsteğe bağlı</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <label class="form-label fw-semibold mb-0">Öğrenenler</label>
                                <div class="small text-muted">Kutuları işaretleyerek seçim yapın.</div>
                            </div>
                            <span class="badge bg-light text-dark border"><span id="learnerVisibleCount">{{ $learners->count() }}</span> kişi gösteriliyor</span>
                        </div>

                        <div class="learner-toolbar">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label for="learnerSearch" class="visually-hidden">Ad veya soyad ara</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                        <input type="search" id="learnerSearch" class="form-control" placeholder="Ad veya soyad ara..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="learnerDepartmentFilter" class="visually-hidden">Departman</label>
                                    <select id="learnerDepartmentFilter" class="form-select form-select-sm">
                                        <option value="">Tüm departmanlar</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                        @if($hasLearnersWithoutDepartment)
                                            <option value="__none__">Departmanı olmayanlar</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="learnerPersonnelOriginFilter" class="visually-hidden">Personel türü</label>
                                    <select id="learnerPersonnelOriginFilter" class="form-select form-select-sm">
                                        <option value="">Tüm personeller</option>
                                        <option value="foreign">Yabancı ({{ $foreignLearnerCount }})</option>
                                        <option value="domestic">Türk ({{ $domesticLearnerCount }})</option>
                                        @if($unknownOriginLearnerCount > 0)
                                            <option value="unknown">Uyruk bilgisi yok ({{ $unknownOriginLearnerCount }})</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-info-circle me-1"></i>Personel türü Elektra'daki uyruk ve bordro bilgisine göre belirlenir; eğitim dilinden bağımsızdır.
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="showSelectedLearnersOnly">
                                    <label class="form-check-label small" for="showSelectedLearnersOnly">Sadece seçilenler</label>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="resetLearnerFilters">
                                    <i class="fas fa-undo-alt me-1"></i>Filtreleri temizle
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button class="btn btn-sm btn-outline-primary" type="button" id="selectVisibleLearners">
                                    <i class="fas fa-check-double me-1"></i>Görünenleri seç
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="unselectVisibleLearners">
                                    Görünen seçimi kaldır
                                </button>
                                <button class="btn btn-sm btn-link text-danger text-decoration-none ms-sm-auto" type="button" id="clearLearnerSelection">
                                    Tümünü temizle
                                </button>
                            </div>
                        </div>

                        <div class="learner-list" id="learnerList">
                            @forelse($learners as $learner)
                                @php
                                    $initials = collect(preg_split('/\s+/u', trim($learner->name)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                        ->implode('');
                                @endphp
                                <label class="learner-option" data-learner-item data-learner-name="{{ $learner->name }}" data-department-id="{{ $learner->department_id ?? '' }}" data-personnel-origin="{{ $learner->education_personnel_origin }}">
                                    <input
                                        type="checkbox"
                                        name="user_ids[]"
                                        value="{{ $learner->id }}"
                                        class="learner-checkbox"
                                        @checked(in_array((int) $learner->id, $oldLearnerIds, true))
                                    >
                                    <span class="learner-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
                                    <span class="learner-details flex-grow-1">
                                        <span class="learner-name d-flex align-items-center gap-1">
                                            <span class="text-truncate">{{ $learner->name }}</span>
                                            @if($learner->education_personnel_origin === 'foreign')
                                                <span class="badge bg-info-subtle text-info learner-origin-badge">Yabancı</span>
                                            @endif
                                        </span>
                                        <span class="learner-meta d-block">
                                            {{ $learner->department->name ?? 'Departman belirtilmemiş' }}
                                            @if($learner->branch)
                                                · {{ $learner->branch->name }}
                                            @endif
                                            @if($learner->education_personnel_origin === 'foreign' && $learner->education_nationality)
                                                · {{ $learner->education_nationality }}
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <div class="empty-filter-state">
                                    <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                    Aktif öğrenen bulunamadı.
                                </div>
                            @endforelse
                            <div class="empty-filter-state" id="learnerFilterEmpty" hidden>
                                <i class="fas fa-filter fa-2x mb-2 d-block"></i>
                                Bu filtrelere uyan öğrenen bulunamadı.
                            </div>
                        </div>

                        <div class="selection-summary mt-2" aria-live="polite">
                            <span><i class="fas fa-users me-1"></i>Atama yapılacak öğrenen</span>
                            <strong><span id="learnerSelectedCount">{{ count($oldLearnerIds) }}</span> kişi</strong>
                        </div>
                        <div class="text-danger small mt-2" id="learnerSelectionError" role="alert" hidden>
                            En az bir öğrenen seçmelisiniz.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3" id="assignmentSubmitButton" @disabled($courses->isEmpty() || $learners->isEmpty())>
                            <i class="fas fa-paper-plane me-1"></i><span>Seçilenlere eğitimi ata</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="section-kicker mb-1">Haftalık görünüm</div>
                        <h5 class="mb-0">Atadıklarım</h5>
                        <small class="text-muted">{{ $weekStart->format('d.m.Y') }} haftası · {{ $languageLabel }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border">{{ $assignments->total() }} sonuç</span>
                    </div>
                </div>
                <div class="px-3 pb-3">
                    <form method="GET" class="planning-bar row g-2 align-items-end" id="assignedTrainingFilterForm">
                        <input type="hidden" name="language" value="{{ $language }}">
                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                        <div class="col-md-6">
                            <label for="assignmentSearch" class="form-label small fw-semibold mb-1">Atanan öğrenen ara</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input
                                    type="search"
                                    name="assignment_search"
                                    id="assignmentSearch"
                                    class="form-control"
                                    value="{{ $assignmentSearch }}"
                                    placeholder="Ad veya soyad..."
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="assignmentDepartment" class="form-label small fw-semibold mb-1">Departman</label>
                            <select name="assignment_department" id="assignmentDepartment" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tüm departmanlar</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected($assignmentDepartment === (string) $department->id)>{{ $department->name }}</option>
                                @endforeach
                                @if($hasLearnersWithoutDepartment)
                                    <option value="__none__" @selected($assignmentDepartment === '__none__')>Departmanı olmayanlar</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Ara</button>
                        </div>
                        @if($hasAssignmentFilters)
                            <div class="col-12 text-end">
                                <a href="{{ route('education.assignments.index', ['language' => $language, 'week_start' => $weekStart->toDateString()]) }}" class="small text-decoration-none">
                                    <i class="fas fa-times me-1"></i>Atama filtrelerini temizle
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 assignment-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Öğrenen</th>
                                    <th>Eğitim</th>
                                    <th class="text-center">İlerleme</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    @php
                                        $statusClass = match($assignment->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'info',
                                            default => 'secondary',
                                        };
                                        $statusLabel = match($assignment->status) {
                                            'completed' => 'Tamamlandı',
                                            'in_progress' => 'Devam ediyor',
                                            default => 'Başlamadı',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $assignment->learner->name ?? '-' }}</div>
                                            <small class="text-muted d-block">
                                                {{ $assignment->learner->department->name ?? 'Departman yok' }}
                                                @if($assignment->learner?->branch)
                                                    · {{ $assignment->learner->branch->name }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div>{{ $assignment->course->title ?? '-' }}</div>
                                            <small class="text-muted">
                                                {{ strtoupper($assignment->language) }}
                                                @if($assignment->due_at)
                                                    · Son: {{ $assignment->due_at->format('d.m.Y H:i') }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height:7px" title="%{{ number_format($assignment->progress_percent, 1) }}">
                                                <div class="progress-bar {{ $assignment->progress_percent >= 100 ? 'bg-success' : '' }}" style="width:{{ min(100, $assignment->progress_percent) }}%"></div>
                                            </div>
                                            <small class="text-muted">%{{ number_format($assignment->progress_percent, 1) }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if(auth()->user()->hasPermission('education_assignments','delete'))
                                                <form method="POST" action="{{ route('education.assignments.destroy', $assignment) }}" onsubmit="return confirm('Bu atamayı kaldırmak istiyor musunuz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Atamayı kaldır" aria-label="Atamayı kaldır">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas {{ $hasAssignmentFilters ? 'fa-search' : 'fa-calendar-plus' }} fa-2x mb-2 d-block"></i>
                                            {{ $hasAssignmentFilters ? 'Arama ölçütlerine uyan atama bulunamadı.' : 'Bu hafta ve dil için henüz atama yok.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($assignments->hasPages())
                    <div class="card-footer bg-white">{{ $assignments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('assignmentForm');
    const list = document.getElementById('learnerList');
    const searchInput = document.getElementById('learnerSearch');
    const departmentFilter = document.getElementById('learnerDepartmentFilter');
    const personnelOriginFilter = document.getElementById('learnerPersonnelOriginFilter');
    const selectedOnlyInput = document.getElementById('showSelectedLearnersOnly');
    const resetFiltersButton = document.getElementById('resetLearnerFilters');
    const selectVisibleButton = document.getElementById('selectVisibleLearners');
    const unselectVisibleButton = document.getElementById('unselectVisibleLearners');
    const clearSelectionButton = document.getElementById('clearLearnerSelection');
    const visibleCount = document.getElementById('learnerVisibleCount');
    const selectedCount = document.getElementById('learnerSelectedCount');
    const emptyState = document.getElementById('learnerFilterEmpty');
    const selectionError = document.getElementById('learnerSelectionError');
    const submitButton = document.getElementById('assignmentSubmitButton');
    const items = list ? Array.from(list.querySelectorAll('[data-learner-item]')) : [];

    if (!form || !list) {
        return;
    }

    const normalizeText = (value) => String(value || '')
        .toLocaleLowerCase('tr-TR')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/ı/g, 'i');

    const checkboxFor = (item) => item.querySelector('.learner-checkbox');
    const selectedItems = () => items.filter((item) => checkboxFor(item).checked);
    const visibleItems = () => items.filter((item) => !item.hidden);

    const syncItemState = (item) => {
        item.classList.toggle('is-selected', checkboxFor(item).checked);
    };

    const applyFilters = () => {
        const search = normalizeText(searchInput.value.trim());
        const departmentId = departmentFilter.value;
        const personnelOrigin = personnelOriginFilter.value;
        const selectedOnly = selectedOnlyInput.checked;
        let shown = 0;

        items.forEach((item) => {
            const checkbox = checkboxFor(item);
            const matchesName = !search || normalizeText(item.dataset.learnerName).includes(search);
            const matchesDepartment = !departmentId
                || (departmentId === '__none__'
                    ? !item.dataset.departmentId
                    : item.dataset.departmentId === departmentId);
            const matchesPersonnelOrigin = !personnelOrigin
                || item.dataset.personnelOrigin === personnelOrigin;
            const matchesSelection = !selectedOnly || checkbox.checked;
            const matches = matchesName && matchesDepartment && matchesPersonnelOrigin && matchesSelection;

            item.hidden = !matches;
            syncItemState(item);
            if (matches) {
                shown += 1;
            }
        });

        const selected = selectedItems().length;
        visibleCount.textContent = shown;
        selectedCount.textContent = selected;
        emptyState.hidden = shown !== 0 || items.length === 0;
        selectVisibleButton.disabled = shown === 0;
        unselectVisibleButton.disabled = shown === 0;
        clearSelectionButton.disabled = selected === 0;
        if (selected > 0) {
            selectionError.hidden = true;
        }
    };

    const setVisibleSelection = (checked) => {
        visibleItems().forEach((item) => {
            checkboxFor(item).checked = checked;
        });
        applyFilters();
    };

    searchInput.addEventListener('input', applyFilters);
    departmentFilter.addEventListener('change', applyFilters);
    personnelOriginFilter.addEventListener('change', applyFilters);
    selectedOnlyInput.addEventListener('change', applyFilters);
    items.forEach((item) => checkboxFor(item).addEventListener('change', applyFilters));

    selectVisibleButton.addEventListener('click', () => setVisibleSelection(true));
    unselectVisibleButton.addEventListener('click', () => setVisibleSelection(false));
    clearSelectionButton.addEventListener('click', () => {
        items.forEach((item) => {
            checkboxFor(item).checked = false;
        });
        applyFilters();
    });
    resetFiltersButton.addEventListener('click', () => {
        searchInput.value = '';
        departmentFilter.value = '';
        personnelOriginFilter.value = '';
        selectedOnlyInput.checked = false;
        applyFilters();
        searchInput.focus();
    });

    form.addEventListener('submit', (event) => {
        if (selectedItems().length === 0) {
            event.preventDefault();
            selectionError.hidden = false;
            selectedOnlyInput.checked = false;
            applyFilters();
            list.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        submitButton.disabled = true;
        submitButton.querySelector('span').textContent = 'Atamalar hazırlanıyor...';
    });

    applyFilters();
})();
</script>
@endpush
