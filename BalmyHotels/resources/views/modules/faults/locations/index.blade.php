@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Konumları</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Konumlar</li>
            </ol>
        </div>
    </div>

    <div class="row align-items-start g-4">
        {{-- Sol: Liste --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Konum Listesi</h5>
                    <a href="{{ route('faults.locations.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Yeni Konum
                    </a>
                </div>

                {{-- Filtre --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-md-6">
                            <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="accordion" id="locAccordion">
                        @forelse($locations as $loc)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#loc{{ $loc->id }}">
                                    <span class="fw-semibold me-2">{{ $loc->name }}</span>
                                    <span class="badge bg-secondary me-2">{{ $loc->areas->count() }} alan</span>
                                    <span class="badge {{ $loc->is_active ? 'bg-success' : 'bg-danger' }} me-auto">
                                        {{ $loc->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                    <small class="text-muted me-3">{{ $loc->branch->name ?? '—' }}</small>
                                </button>
                            </h2>
                            <div id="loc{{ $loc->id }}" class="accordion-collapse collapse">
                                <div class="accordion-body pt-2">

                                    {{-- Alan ekleme formu --}}
                                    <form action="{{ route('faults.locations.areas.store', $loc) }}" method="POST"
                                          class="d-flex gap-2 mb-3">
                                        @csrf
                                        <input type="text" name="name" class="form-control form-control-sm"
                                               placeholder="Alan adı ekle (örn: 101)" required>
                                        <button class="btn btn-success btn-sm text-nowrap">
                                            <i class="fas fa-plus"></i> Ekle
                                        </button>
                                    </form>

                                    {{-- Alanlar listesi --}}
                                    @if($loc->areas->count())
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach($loc->areas as $area)
                                        <div class="d-flex align-items-center gap-1 p-1 px-2 border rounded bg-light">
                                            <span class="small">{{ $area->name }}</span>
                                            <form action="{{ route('faults.locations.areas.destroy', $area) }}"
                                                  method="POST" class="mb-0"
                                                  onsubmit="return confirm('{{ $area->name }} alanını sil?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-link btn-sm text-danger p-0 ms-1" title="Sil">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                        <p class="text-muted small">Henüz alan eklenmedi.</p>
                                    @endif

                                    {{-- Konum aksiyonları --}}
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('faults.locations.edit', $loc) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i> Düzenle
                                        </a>
                                        <form action="{{ route('faults.locations.destroy', $loc) }}" method="POST"
                                              onsubmit="return confirm('Bu konumu ve tüm alanlarını silmek istiyor musunuz?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash me-1"></i> Sil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-map-marker-alt fa-2x mb-2 d-block"></i>
                            Henüz konum eklenmemiş.
                        </div>
                        @endforelse
                    </div>
                </div>

                @if($locations->hasPages())
                <div class="card-footer">{{ $locations->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Sağ: Hızlı ekle --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">Hızlı Konum Ekle</h6></div>
                <div class="card-body">
                    <form action="{{ route('faults.locations.store') }}" method="POST">
                        @csrf
                        @if($errors->any())
                            <div class="alert alert-danger py-2 small">
                                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Seçin...</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konum Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                   placeholder="Örn: Odalar, Resepsiyon..." required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ekle</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
