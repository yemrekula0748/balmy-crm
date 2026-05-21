@extends('layouts.default')
@section('title', $event->exists ? 'Duyuru Duzenle' : 'Yeni Yuz Yuze Egitim')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $event->exists ? 'Duyuru Duzenle' : 'Yeni Yuz Yuze Egitim' }}</h4>
                <span>Katilim cevabi toplanacak egitim duyurusu</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-body">
            <form method="POST" action="{{ $event->exists ? route('education.events.update', $event) : route('education.events.store') }}">
                @csrf
                @if($event->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Baslik <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dil <span class="text-danger">*</span></label>
                        <select name="language" class="form-select" required>
                            @foreach($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('language', $event->language ?: 'tr') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Aciklama</label>
                        <textarea name="description" rows="3" class="form-control" maxlength="5000">{{ old('description', $event->description) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Baslangic <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bitis</label>
                        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cevap Son Tarihi</label>
                        <input type="datetime-local" name="response_deadline_at" class="form-control" value="{{ old('response_deadline_at', $event->response_deadline_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Konum</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $event->location) }}" maxlength="255">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $event->exists ? $event->is_active : true))>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Ogrenenler <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectEventLearners()">Tumunu sec</button>
                        </div>
                        <select name="user_ids[]" id="eventLearners" class="form-select" multiple size="12" required>
                            @foreach($learners as $learner)
                                <option value="{{ $learner->id }}" @selected(in_array($learner->id, old('user_ids', $selectedLearnerIds)))>
                                    {{ $learner->name }}{{ $learner->branch ? ' - '.$learner->branch->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i>Kaydet</button>
                    <a href="{{ route('education.events.index') }}" class="btn btn-outline-secondary">Vazgec</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectEventLearners() {
    document.querySelectorAll('#eventLearners option').forEach((option) => {
        option.selected = true;
    });
}
</script>
@endpush
