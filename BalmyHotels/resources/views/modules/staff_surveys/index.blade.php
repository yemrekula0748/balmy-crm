@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-clipboard-list me-2 text-primary"></i>Personel Anketleri</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Personel Anketleri</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-muted">{{ $surveys->count() }} anket</h5>
        <a href="{{ route('staff-surveys.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Yeni Anket
        </a>
    </div>

    @if($surveys->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <div style="font-size:56px;opacity:.2">📋</div>
            <h5 class="text-muted mt-3">Henüz personel anketi oluşturulmamış</h5>
            <a href="{{ route('staff-surveys.create') }}" class="btn btn-primary mt-3">
                İlk Anketi Oluştur
            </a>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($surveys as $survey)
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 @if(!$survey->is_active) border-secondary opacity-75 @else border-primary border-opacity-25 @endif">
                <div class="card-body p-4">

                    {{-- Başlık + durum --}}
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-0">{{ $survey->getTitle() }}</h6>
                            @if($survey->branch)
                            <span class="text-muted small">📍 {{ $survey->branch->name }}</span>
                            @endif
                        </div>
                        <span @class(['badge', 'bg-success' => $survey->is_active, 'bg-secondary' => !$survey->is_active])>
                            {{ $survey->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>

                    {{-- Diller --}}
                    <div class="mb-2">
                        @foreach($survey->languages as $lang)
                        <span class="badge bg-light text-dark border me-1">
                            {{ \App\Models\StaffSurvey::AVAILABLE_LANGUAGES[$lang]['flag'] ?? '' }}
                            {{ \App\Models\StaffSurvey::AVAILABLE_LANGUAGES[$lang]['name'] ?? $lang }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Meta bilgiler --}}
                    <div class="d-flex flex-wrap gap-3 mb-3 text-muted small">
                        <span>📊 <strong class="text-dark">{{ $survey->responses_count }}</strong> yanıt</span>
                        <span>❓ {{ $survey->questions_count ?? '—' }} soru</span>
                        @if($survey->is_anonymous)
                        <span title="Anonim">🕶 Anonim</span>
                        @endif
                        @if($survey->show_dept_field)
                        <span title="Departman alanı">🏢 Dept.</span>
                        @endif
                    </div>

                    {{-- Link kopyala --}}
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" readonly
                               value="{{ $survey->publicUrl() }}" id="url_{{ $survey->id }}">
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="copyUrl('url_{{ $survey->id }}', this)" title="Kopyala">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    {{-- Tarih --}}
                    <div class="text-muted small mb-3">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ $survey->created_at->format('d.m.Y H:i') }}
                        @if($survey->creator)
                        — {{ $survey->creator->name }}
                        @endif
                    </div>

                    {{-- Butonlar --}}
                    <div class="d-flex gap-1 pt-2 border-top flex-wrap">
                        <a href="{{ route('staff-surveys.show', $survey) }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="fas fa-chart-bar me-1"></i>Sonuçlar
                        </a>
                        <a href="{{ route('staff-surveys.edit', $survey) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('staff-surveys.toggle', $survey) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm @if($survey->is_active) btn-outline-warning @else btn-outline-success @endif"
                                    title="{{ $survey->is_active ? 'Pasife Al' : 'Aktif Et' }}">
                                <i class="fas @if($survey->is_active) fa-pause @else fa-play @endif"></i>
                            </button>
                        </form>
                        <form action="{{ route('staff-surveys.destroy', $survey) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Anketi ve tüm yanıtları silmek istediğinizden emin misiniz?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyUrl(inputId, btn) {
    const el = document.getElementById(inputId);
    navigator.clipboard.writeText(el.value).then(() => {
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(() => btn.innerHTML = old, 1500);
    });
}
</script>
@endpush
