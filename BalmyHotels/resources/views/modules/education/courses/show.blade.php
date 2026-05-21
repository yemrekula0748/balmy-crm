@extends('layouts.default')
@section('title', $course->title)

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $course->title }}</h4>
                <span>{{ $course->language_label }} egitim icerigi</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <video controls preload="metadata" style="width:100%;max-height:430px;border-radius:8px;background:#111">
                        <source src="{{ asset('storage/'.$course->video_path) }}">
                    </video>
                    <p class="mt-3 mb-0 text-muted">{{ $course->description ?: 'Aciklama girilmemis.' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-body">
                    <div class="small text-muted">Egitmen</div>
                    <div class="fw-semibold mb-3">{{ $course->trainer->name ?? '-' }}</div>
                    <div class="small text-muted">Dil</div>
                    <div class="fw-semibold mb-3">{{ $course->language_label }}</div>
                    <div class="small text-muted">Atama Sayisi</div>
                    <div class="fw-semibold mb-3">{{ $course->assignments->count() }}</div>
                    <div class="small text-muted">Durum</div>
                    <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-light text-dark' }}">{{ $course->is_active ? 'Aktif' : 'Pasif' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
