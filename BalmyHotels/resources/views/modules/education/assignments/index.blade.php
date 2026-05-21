@extends('layouts.default')
@section('title', 'Egitim Atamalari')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Toplu Egitim Atama</h4>
                <span>Dil ve hafta bazli ogrenene egitim atama</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Yeni Atama</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-7">
                            <select name="language" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($languages as $code => $label)
                                    <option value="{{ $code }}" @selected($language === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                    </form>

                    <form method="POST" action="{{ route('education.assignments.store') }}">
                        @csrf
                        <input type="hidden" name="language" value="{{ $language }}">
                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                        <div class="mb-3">
                            <label class="form-label">Egitim</label>
                            <select name="education_course_id" class="form-select" required>
                                <option value="">Seciniz</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" @selected(old('education_course_id') == $course->id)>{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Son Tarih</label>
                            <input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at') }}">
                        </div>
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Ogrenenler</label>
                            <button class="btn btn-xs btn-outline-secondary" type="button" onclick="toggleLearners(true)">Tumunu sec</button>
                        </div>
                        <select name="user_ids[]" id="learnerSelect" class="form-select" multiple size="12" required>
                            @foreach($learners as $learner)
                                <option value="{{ $learner->id }}" @selected(in_array($learner->id, old('user_ids', [])))>
                                    {{ $learner->name }}{{ $learner->branch ? ' - '.$learner->branch->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Ctrl/Cmd ile coklu secim yapabilirsin.</small>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-paper-plane me-1"></i>Toplu Ata
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">{{ $weekStart->format('d.m.Y') }} Haftasi Atamalari</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ogrenen</th>
                                    <th>Egitim</th>
                                    <th class="text-center">Dil</th>
                                    <th class="text-center">Ilerleme</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-end">Islem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $assignment->learner->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $assignment->learner->branch->name ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->course->title ?? '-' }}</td>
                                        <td class="text-center">{{ strtoupper($assignment->language) }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height:7px">
                                                <div class="progress-bar" style="width:{{ $assignment->progress_percent }}%"></div>
                                            </div>
                                            <small class="text-muted">%{{ number_format($assignment->progress_percent, 1) }}</small>
                                        </td>
                                        <td class="text-center">{{ $assignment->status_label }}</td>
                                        <td class="text-end">
                                            @if(auth()->user()->hasPermission('education_assignments','delete'))
                                                <form method="POST" action="{{ route('education.assignments.destroy', $assignment) }}" onsubmit="return confirm('Bu atamayi kaldirmak istiyor musunuz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Bu hafta icin atama yok.</td>
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
function toggleLearners(selected) {
    document.querySelectorAll('#learnerSelect option').forEach((option) => {
        option.selected = selected;
    });
}
</script>
@endpush
