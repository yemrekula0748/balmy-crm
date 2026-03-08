@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Görev Başlat</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle-trips.index') }}">Araç Görevleri</a></li>
                <li class="breadcrumb-item active">Görev Başlat</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-primary">
                    <h5 class="card-title mb-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
                        </svg>
                        Yeni Araç Görevi
                    </h5>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($vehicles->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            Şu anda görevde olmayan müsait araç bulunmuyor.
                        </div>
                    @else
                    <form action="{{ route('vehicle-trips.store') }}" method="POST" enctype="multipart/form-data" id="tripForm">
                        @csrf

                        {{-- Araç Seç --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Araç Seçin <span class="text-danger">*</span></label>
                            <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                                <option value="">-- Müsait araç seçin --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" @selected(old('vehicle_id') == $v->id)
                                            data-km="{{ $v->current_km }}">
                                        {{ $v->plate }} — {{ $v->brand }} {{ $v->model }}
                                        (Güncel: {{ number_format($v->current_km) }} km)
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Kilometre fotoğrafı --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Kilometre Fotoğrafı <span class="text-danger">*</span>
                                <span class="badge bg-warning text-dark ms-1 small">Sadece Kamera</span>
                            </label>
                            <p class="text-muted small mb-2">
                                <i class="fa fa-camera me-1"></i>
                                Aracın kilometre göstergesinin fotoğrafını çekin. Galeriden seçim yapılamaz.
                            </p>
                            <div class="border rounded p-3 text-center bg-light" id="photoBox" style="cursor:pointer;" onclick="document.getElementById('kmPhotoInput').click()">
                                <img id="photoPreview" src="" alt="" class="img-fluid rounded mb-2 d-none" style="max-height:200px;">
                                <div id="photoPlaceholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#aaa" class="mb-2" viewBox="0 0 16 16">
                                        <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                                        <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                    </svg>
                                    <p class="text-muted mb-0">Fotoğraf çekmek için dokunun</p>
                                </div>
                            </div>
                            {{-- capture="environment" ile her zaman kamera açılır, galeri seçimi engellenir --}}
                            <input type="file" id="kmPhotoInput" name="start_km_photo"
                                   accept="image/*" capture="environment"
                                   class="d-none @error('start_km_photo') is-invalid @enderror"
                                   required>
                            @error('start_km_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Başlangıç km --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Başlangıç KM <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="start_km" id="startKm"
                                       class="form-control @error('start_km') is-invalid @enderror"
                                       placeholder="Örn: 45230" min="0" required value="{{ old('start_km') }}">
                                <span class="input-group-text">km</span>
                            </div>
                            <div class="form-text text-muted" id="kmHint"></div>
                            @error('start_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Gidilecek yer --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Gidilecek Yer / Görev Tanımı <span class="text-danger">*</span></label>
                            <input type="text" name="destination"
                                   class="form-control @error('destination') is-invalid @enderror"
                                   placeholder="Örn: İstanbul Havalimanı — VIP Transfer"
                                   required maxlength="200" value="{{ old('destination') }}">
                            @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg" id="submitBtn">
                            <i class="fa fa-play me-2"></i>Görevi Başlat
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Araç seçilince güncel km ipucunu göster
document.querySelector('select[name="vehicle_id"]')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const km  = opt.dataset.km;
    const hint = document.getElementById('kmHint');
    if (km !== undefined) {
        document.getElementById('startKm').min = km;
        hint.textContent = 'Bu aracın güncel km değeri: ' + Number(km).toLocaleString('tr-TR') + ' km — bu değerden küçük girilemez.';
    } else {
        hint.textContent = '';
    }
});

// Fotoğraf önizleme
document.getElementById('kmPhotoInput')?.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endpush
