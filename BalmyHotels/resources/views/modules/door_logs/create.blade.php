@extends('layouts.default')

@section('content')
<div class="container-fluid">
    {{-- Sayfa başlığı --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-door-open me-2 text-primary"></i>Manuel Giriş/Çıkış Kaydı</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.index') }}">Kapı Giriş/Çıkış</a></li>
                <li class="breadcrumb-item active">Manuel Kayıt</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background:linear-gradient(135deg,#2c3e7a,#4a6cf7);border-radius:.5rem .5rem 0 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                         stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span class="text-white fw-semibold fs-6">Kayıt Bilgileri</span>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('door-logs.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            {{-- ŞUBE (multi-branch support) --}}
                            @if($branches->count() > 1)
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-building me-1 text-muted"></i>Şube
                                </label>
                                <select id="branchFilter" class="form-select">
                                    <option value="">— Tüm şubeler —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            {{-- PERSONEL --}}
                            <div class="col-12">
                                <label for="userSelect" class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-muted"></i>Personel <span class="text-danger">*</span>
                                </label>
                                <select id="userSelect" name="user_id"
                                        class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Personel seçin...</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}"
                                                data-branch="{{ $manager->branch_id }}"
                                                @selected(old('user_id') == $manager->id)>
                                            {{ $manager->name }}
                                            @if($manager->department) — {{ $manager->department->name }}@endif
                                            ({{ $manager->branch->name ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- İŞLEM TİPİ --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-exchange-alt me-1 text-muted"></i>İşlem Tipi <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type"
                                               id="typeGiris" value="giris"
                                               @checked(old('type', 'giris') === 'giris')>
                                        <label class="form-check-label fw-semibold text-success" for="typeGiris">
                                            <i class="fas fa-sign-in-alt me-1"></i>Giriş
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type"
                                               id="typeCikis" value="cikis"
                                               @checked(old('type') === 'cikis')>
                                        <label class="form-check-label fw-semibold text-danger" for="typeCikis">
                                            <i class="fas fa-sign-out-alt me-1"></i>Çıkış
                                        </label>
                                    </div>
                                </div>
                                @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- TARİH & SAAT --}}
                            <div class="col-12">
                                <label for="loggedAt" class="form-label fw-semibold">
                                    <i class="fas fa-clock me-1 text-muted"></i>Tarih & Saat <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" id="loggedAt" name="logged_at"
                                       class="form-control @error('logged_at') is-invalid @enderror"
                                       value="{{ old('logged_at', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('logged_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- NOT --}}
                            <div class="col-12">
                                <label for="notesInput" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>Not
                                    <span class="text-muted fw-normal small">(opsiyonel)</span>
                                </label>
                                <input type="text" id="notesInput" name="notes"
                                       class="form-control @error('notes') is-invalid @enderror"
                                       value="{{ old('notes') }}"
                                       placeholder="Kısa açıklama...">
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>{{-- /row --}}

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i>Kaydet
                            </button>
                            <a href="{{ route('door-logs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>İptal
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const branchFilter = document.getElementById('branchFilter');
    const userSelect   = document.getElementById('userSelect');
    if (!branchFilter || !userSelect) return;

    const allOptions = Array.from(userSelect.options).filter(o => o.value !== '');

    branchFilter.addEventListener('change', function () {
        const selectedBranch = this.value;

        // Tüm personel seçeneklerini kaldır
        Array.from(userSelect.options).forEach(o => { if (o.value !== '') o.remove(); });

        allOptions.forEach(o => {
            if (!selectedBranch || o.dataset.branch === selectedBranch) {
                userSelect.appendChild(o);
            }
        });

        // Seçili kişi artık listede yoksa sıfırla
        if (userSelect.value && ![...userSelect.options].some(o => o.value === userSelect.value)) {
            userSelect.value = '';
        }
    });
})();
</script>
@endpush
