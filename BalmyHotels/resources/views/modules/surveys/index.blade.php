@extends('layouts.default')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-poll me-2 text-primary"></i>Misafir Anketleri</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Misafir Anketleri</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('surveys.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Yeni Anket Oluştur
        </a>
    </div>

    @forelse($surveys as $survey)
    <div class="card mb-3 border-0 shadow-sm" style="border-radius:14px;overflow:hidden">
        <div class="card-body p-0">
            <div class="row g-0 align-items-center">

                {{-- Sol renk şeridi --}}
                <div class="col-auto" style="width:6px;background:{{ $survey->is_active ? '#4361ee' : '#adb5bd' }};align-self:stretch"></div>

                {{-- İçerik --}}
                <div class="col p-3">
                    <div class="d-flex flex-wrap align-items-start gap-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="mb-0 fw-bold">{{ $survey->getTitle('tr') ?: $survey->getTitle('en') }}</h5>
                                @if($survey->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Pasif</span>
                                @endif
                            </div>

                            {{-- Diller --}}
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($survey->languages as $lang)
                                    @php $info = \App\Models\Survey::AVAILABLE_LANGUAGES[$lang] ?? ['name'=>strtoupper($lang),'flag'=>'🌐']; @endphp
                                    <span class="badge bg-light text-dark border">{{ $info['flag'] }} {{ $info['name'] }}</span>
                                @endforeach
                            </div>

                            {{-- Bağlı şube --}}
                            @if($survey->branch)
                                <small class="text-muted"><i class="fas fa-building me-1"></i>{{ $survey->branch->name }}</small>
                            @else
                                <small class="text-muted"><i class="fas fa-globe me-1"></i>Tüm Şubeler</small>
                            @endif
                        </div>

                        {{-- Yanıt sayısı --}}
                        <div class="text-center px-4 border-start">
                            <div class="fw-bold" style="font-size:2rem;color:#4361ee;line-height:1">{{ $survey->responses_count }}</div>
                            <small class="text-muted">Yanıt</small>
                        </div>
                    </div>

                    {{-- Link & QR --}}
                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                        <code class="text-muted small bg-light px-2 py-1 rounded flex-grow-1" style="word-break:break-all">
                            {{ $survey->publicUrl() }}
                        </code>
                        <button class="btn btn-sm btn-outline-secondary" title="Linki Kopyala"
                                onclick="navigator.clipboard.writeText('{{ $survey->publicUrl() }}');this.innerHTML='<i class=\'fas fa-check\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',1500)">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- İşlemler --}}
                <div class="col-auto pe-3 d-flex flex-column gap-2">
                    <a href="{{ route('surveys.show', $survey) }}" class="btn btn-sm btn-primary" title="Sonuçlar">
                        <i class="fas fa-chart-bar me-1"></i>Sonuçlar
                    </a>
                    <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-outline-secondary" title="Düzenle">
                        <i class="fas fa-edit me-1"></i>Düzenle
                    </a>
                    <form action="{{ route('surveys.toggle', $survey) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm w-100 {{ $survey->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="Durum Değiştir">
                            <i class="fas fa-{{ $survey->is_active ? 'pause' : 'play' }} me-1"></i>
                            {{ $survey->is_active ? 'Durdur' : 'Başlat' }}
                        </button>
                    </form>
                    <form action="{{ route('surveys.destroy', $survey) }}" method="POST"
                          onsubmit="return confirm('Bu anketi ve tüm yanıtlarını silmek istediğinizden emin misiniz?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100" title="Sil">
                            <i class="fas fa-trash me-1"></i>Sil
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="fas fa-poll fa-4x text-muted opacity-25 mb-3"></i>
            <h5 class="text-muted">Henüz anket oluşturulmadı</h5>
            <a href="{{ route('surveys.create') }}" class="btn btn-primary mt-2">İlk Anketi Oluştur</a>
        </div>
    </div>
    @endforelse

</div>
@endsection
