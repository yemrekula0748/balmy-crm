@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Menü Vitrinleri</h4>
                <span class="text-muted small">Public misafir görünüm sayfaları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item active">Vitrinler</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold">Vitrinler <span class="badge bg-secondary ms-1">{{ $showcases->count() }}</span></h5>
        @if(auth()->user()->hasPermission('qrmenus','create'))
        <a href="{{ route('qrmenus.showcases.create') }}" class="btn btn-sm text-white" style="background:#c19b77">
            <i class="fa fa-plus me-1"></i> Yeni Vitrin
        </a>
        @endif
    </div>

    @forelse($showcases as $showcase)
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:44px;height:44px;background:{{ $showcase->accent_color }};flex-shrink:0;font-size:1.1rem">
                    {{ mb_substr($showcase->title, 0, 1) }}
                </div>
                <div>
                    <div class="fw-semibold">{{ $showcase->title }}</div>
                    @if($showcase->subtitle)
                    <div class="text-muted small">{{ $showcase->subtitle }}</div>
                    @endif
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge {{ $showcase->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $showcase->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                        <span class="text-muted small">
                            {{ $showcase->items->whereNotNull('qr_menu_id')->count() }} menü
                            · {{ $showcase->items->whereNotNull('survey_id')->count() }} anket
                        </span>
                        <a href="{{ $showcase->publicUrl() }}" target="_blank"
                           class="text-muted small d-flex align-items-center gap-1">
                            <i class="fa fa-external-link-alt" style="font-size:.7rem"></i>
                            /vitrin/{{ $showcase->slug }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $showcase->publicUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Önizle">
                    <i class="fa fa-eye"></i>
                </a>
                @if(auth()->user()->hasPermission('qrmenus','edit'))
                <a href="{{ route('qrmenus.showcases.edit', $showcase) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-pencil-alt me-1"></i> Düzenle
                </a>
                @endif
                @if(auth()->user()->hasPermission('qrmenus','delete'))
                <form method="POST" action="{{ route('qrmenus.showcases.destroy', $showcase) }}"
                      onsubmit="return confirm('\"{{ $showcase->title }}\" vitrinini silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fa fa-layer-group fa-3x mb-3 opacity-25 d-block"></i>
            Henüz vitrin oluşturulmamış.
            <a href="{{ route('qrmenus.showcases.create') }}" class="d-block mt-2 fw-semibold" style="color:#c19b77">Yeni Vitrin Oluştur</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
