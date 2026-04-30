@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-list me-2 text-primary"></i>Bildirdiklerim</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Bildirdiklerim</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filtre --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" id="filterForm" class="row g-2 align-items-end">

                {{-- Durum --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Durum</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tümü</option>
                        @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Konum --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Konum</label>
                    <select name="location_id" class="form-select form-select-sm" id="locationSelect" onchange="onLocationChange()">
                        <option value="">Tümü</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Alan --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Alan</label>
                    <select name="area_id" class="form-select form-select-sm" id="areaSelect">
                        <option value="">Tümü</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Arıza Türü --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Arıza Türü</label>
                    <select name="fault_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tümü</option>
                        @foreach($faultTypes as $ft)
                            <option value="{{ $ft->id }}" @selected(request('fault_type_id') == $ft->id)>{{ $ft->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Departman --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Departman</label>
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tümü</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tarih Aralığı --}}
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                {{-- Butonlar --}}
                <div class="col-sm-6 col-md-3 col-lg-2 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Filtrele
                    </button>
                    @if(request()->hasAny(['status','location_id','area_id','fault_type_id','department_id','date_from','date_to']))
                        <a href="{{ route('faults.my-reports') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Temizle
                        </a>
                    @endif
                </div>

                {{-- Yeni Arıza (sağ tarafa) --}}
                <div class="col-auto ms-auto d-flex align-items-end">
                    <a href="{{ route('faults.create') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-plus me-1"></i> Yeni Arıza Bildir
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="row g-3">
        @forelse($faults as $fault)
        @php $isMine = $fault->reported_by === auth()->id(); @endphp
        <div class="col-12">
            <div class="card border-start border-4 border-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                <div class="card-body">
                    <div class="row align-items-start g-3">
                        {{-- Bilgiler --}}
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">
                                    {{ \App\Models\Fault::STATUSES[$fault->status] }}
                                </span>
                                @if($isMine)
                                    <span class="badge bg-primary">Ben bildirdim</span>
                                @else
                                    <span class="badge bg-secondary">Departmanım bildirdi</span>
                                @endif
                                <span class="text-muted small">#{{ $fault->id }} · {{ $fault->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <h5 class="mb-1">
                                <a href="{{ route('faults.show', $fault) }}" class="text-dark text-decoration-none">
                                    {{ $fault->title }}
                                </a>
                            </h5>

                            <div class="d-flex flex-wrap gap-2 text-muted small mb-2">
                                <span><i class="fas fa-building me-1"></i>{{ $fault->branch->name ?? '—' }}</span>
                                <span><i class="fas fa-users me-1"></i>{{ $fault->department->name ?? '—' }}</span>
                                @if($fault->faultLocation)
                                    <span><i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $fault->faultLocation->name }}@if($fault->faultArea) / {{ $fault->faultArea->name }}@endif
                                    </span>
                                @endif
                                @if($fault->faultType)
                                    <span><i class="fas fa-tag me-1"></i>{{ $fault->faultType->name }}</span>
                                @endif
                            </div>

                            <p class="text-muted small mb-0">{{ Str::limit($fault->description, 150) }}</p>
                        </div>

                        {{-- Kapatma (sadece kendi bildirdiğim ve açık olanlar) --}}
                        <div class="col-md-4">
                            @if($isMine && $fault->status !== 'closed')
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-semibold mb-2 small">Durumu Güncelle / Kapat</h6>
                                <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <select name="status" class="form-select form-select-sm" required>
                                            @foreach(\App\Models\Fault::STATUSES as $val => $lbl)
                                                @if($val !== $fault->status)
                                                <option value="{{ $val }}" @if($val === 'closed') style="font-weight:bold;color:#dc3545" @endif>
                                                    {{ $lbl }}
                                                </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="note" class="form-control form-control-sm" rows="2"
                                                  placeholder="Örn: Sorun kendiliğinden çözüldü..." required></textarea>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100">Güncelle</button>
                                </form>
                            </div>
                            @elseif($fault->status === 'closed')
                            <div class="text-center text-muted py-2">
                                <i class="fas fa-check-circle fa-2x text-secondary mb-1 d-block"></i>
                                <small>Kapalı · {{ $fault->closed_at?->format('d.m.Y') }}</small>
                            </div>
                            @endif

                            <div class="mt-2 text-end">
                                <a href="{{ route('faults.show', $fault) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Detay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-clipboard fa-3x mb-3 d-block"></i>
                    <h5>Henüz bildirim yapmadınız.</h5>
                    <a href="{{ route('faults.create') }}" class="btn btn-danger mt-2">
                        <i class="fas fa-plus me-1"></i> İlk Arızayı Bildir
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($faults->hasPages())
    <div class="mt-3">{{ $faults->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
function onLocationChange() {
    const locationId = document.getElementById('locationSelect').value;
    const areaSelect = document.getElementById('areaSelect');

    // Konum seçilmemişse alanı temizle ve formu gönder
    if (!locationId) {
        areaSelect.innerHTML = '<option value="">Tümü</option>';
        document.getElementById('filterForm').submit();
        return;
    }

    fetch(`/arizalar/ajax/alanlar?location_id=${locationId}`)
        .then(r => r.json())
        .then(data => {
            areaSelect.innerHTML = '<option value="">Tümü</option>';
            data.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.name;
                areaSelect.appendChild(opt);
            });
            document.getElementById('filterForm').submit();
        });
}
</script>
@endpush
@endsection
