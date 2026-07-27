@extends('layouts.default')
@section('title', $course->exists ? 'Egitim Duzenle' : 'Yeni Egitim')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $course->exists ? 'Egitim Duzenle' : 'Yeni Egitim' }}</h4>
                <span>Video, dil ve konu basligi</span>
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
            <form method="POST" action="{{ $course->exists ? route('education.courses.update', $course) : route('education.courses.store') }}" enctype="multipart/form-data">
                @csrf
                @if($course->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Konu Basligi <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dil <span class="text-danger">*</span></label>
                        <select name="language" class="form-select" required>
                            @foreach($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('language', $course->language ?: 'tr') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Aciklama</label>
                        <textarea name="description" rows="4" class="form-control" maxlength="5000">{{ old('description', $course->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Video {{ $course->exists ? '' : '*' }}</label>
                        <input type="file" name="video" class="form-control" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg" {{ $course->exists ? '' : 'required' }}>
                        <small class="text-muted">MP4/WebM/MOV/AVI/MPEG. Uygulama tarafinda MB siniri uygulanmaz; sunucu ve disk kapasitesi gecerlidir. {{ $serverUploadLimitText ?? '' }}</small>
                        @error('video')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahmini Sure (saniye)</label>
                        <input type="number" name="duration_seconds" class="form-control" min="0" max="86400" value="{{ old('duration_seconds', $course->duration_seconds ?? 0) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $course->exists ? $course->is_active : true))>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                    @if($course->exists && $course->video_path)
                        <div class="col-12">
                            <video controls preload="metadata" style="width:100%;max-height:360px;border-radius:8px;background:#111">
                                <source src="{{ route('education.courses.video', $course) }}">
                            </video>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i>Kaydet</button>
                    <a href="{{ route('education.courses.index') }}" class="btn btn-outline-secondary">Vazgec</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
