@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Görevi Bitir</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle-trips.index') }}">Araç Görevleri</a></li>
                <li class="breadcrumb-item active">Görevi Bitir</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-danger bg-opacity-10 border-danger">
                    <h5 class="card-title mb-0 text-danger">
                        <i class="fa fa-stop-circle me-2"></i>Dönüş Bilgilerini Girin
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

                    <form action="{{ route('vehicle-trips.update', $vehicleTrip) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        {{-- Bitiş km fotoğrafı --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Dönüş KM Fotoğrafı <span class="text-danger">*</span>
                                <span class="badge bg-warning text-dark ms-1 small">Sadece Kamera</span>
                            </label>
                            <p class="text-muted small mb-2">
                                <i class="fa fa-camera me-1"></i>
                                Dönüşte aracın kilometre göstergesini fotoğraflayın.
                            </p>
                            <div class="border rounded p-3 text-center bg-light" id="photoBox" style="cursor:pointer;" onclick="document.getElementById('endKmPhotoInput').click()">
                                <img id="photoPreview" src="" alt="" class="img-fluid rounded mb-2 d-none" style="max-height:200px;">
                                <div id="photoPlaceholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#aaa" class="mb-2" viewBox="0 0 16 16">
                                        <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                                        <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                    </svg>
                                    <p class="text-muted mb-0">Fotoğraf çekmek için dokunun</p>
                                </div>
                            </div>
                            <input type="file" id="endKmPhotoInput" name="end_km_photo"
                                   accept="image/*" capture="environment"
                                   class="d-none @error('end_km_photo') is-invalid @enderror"
                                   required>
                            @error('end_km_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Bitiş km --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Dönüş KM <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="end_km"
                                       class="form-control @error('end_km') is-invalid @enderror"
                                       placeholder="Dönüşteki km değerini girin"
                                       min="{{ $vehicleTrip->start_km }}" required
                                       value="{{ old('end_km') }}"
                                       id="endKmInput">
                                <span class="input-group-text">km</span>
                            </div>
                            <div class="form-text" id="kmDiff"></div>
                            @error('end_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Görev notu --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Görev Notu <span class="text-muted small">(isteğe bağlı)</span></label>
                            <textarea name="notes" rows="3"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Göreve ilişkin not eklemek isterseniz yazabilirsiniz...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fa fa-check-circle me-2"></i>Görevi Tamamla ve Kaydet
                            </button>
                            <a href="{{ route('vehicle-trips.my') }}" class="btn btn-outline-secondary">
                                ← Geri Dön
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Görev özeti --}}
            <div class="card border-success mb-3">
                <div class="card-header bg-success bg-opacity-10">
                    <h6 class="mb-0 text-success fw-semibold">Görev #{{ $vehicleTrip->id }} — {{ $vehicleTrip->vehicle->plate }}</h6>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-muted small">Gidilen Yer</div>
                            <div class="fw-semibold">{{ $vehicleTrip->destination }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Başlangıç KM</div>
                            <div class="fw-semibold">{{ number_format($vehicleTrip->start_km) }} km</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fotoğraf önizleme
document.getElementById('endKmPhotoInput')?.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photoPreview').src = e.target.result;
            document.getElementById('photoPreview').classList.remove('d-none');
            document.getElementById('photoPlaceholder').classList.add('d-none');
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Km farkını hesapla
const startKm = {{ $vehicleTrip->start_km }};
document.getElementById('endKmInput')?.addEventListener('input', function () {
    const endKm = parseInt(this.value);
    const diff  = endKm - startKm;
    const hint  = document.getElementById('kmDiff');
    if (!isNaN(diff) && diff >= 0) {
        hint.textContent = `Bu görevde kullanılacak km: ${diff.toLocaleString('tr-TR')} km`;
        hint.className = 'form-text text-success fw-semibold';
    } else if (!isNaN(diff) && diff < 0) {
        hint.textContent = 'Dönüş km değeri başlangıç km değerinden küçük olamaz!';
        hint.className = 'form-text text-danger';
    } else {
        hint.textContent = '';
    }
});
</script>
@endpush
